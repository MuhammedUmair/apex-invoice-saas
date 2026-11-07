<?php

namespace App\Http\Controllers;

use App\Models\Plan;
use App\Models\Subscription;
use App\Models\User;
use App\Models\Workspace;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    /**
     * Ensure authentication routes strictly resolve on the official SaaS subdomain
     * (https://invoicy.redmapleagency.com) rather than the main marketing domain.
     */
    protected function ensureInvoicySubdomain(Request $request)
    {
        $host = strtolower($request->getHost());
        if (in_array($host, ['redmapleagency.com', 'www.redmapleagency.com'])) {
            return redirect()->away('https://invoicy.redmapleagency.com' . $request->getRequestUri(), 301);
        }
        return null;
    }

    public function showLogin(Request $request)
    {
        if ($redirect = $this->ensureInvoicySubdomain($request)) {
            return $redirect;
        }

        if (Auth::check()) {
            return redirect()->route('dashboard');
        }

        $ipThrottleKey = 'login_ip_locked:' . $request->ip();
        $isLocked = false;
        $lockoutSeconds = 0;

        if (RateLimiter::tooManyAttempts($ipThrottleKey, 1)) {
            $isLocked = true;
            $lockoutSeconds = RateLimiter::availableIn($ipThrottleKey);
        } elseif (session()->has('lockout_seconds')) {
            $isLocked = true;
            $lockoutSeconds = (int) session('lockout_seconds');
        }

        return view('auth.login', compact('isLocked', 'lockoutSeconds'));
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        $ip = $request->ip();
        $email = Str::lower(trim($credentials['email']));
        $ipThrottleKey = 'login_ip_locked:' . $ip;
        $emailThrottleKey = 'login_attempt:' . $email . '|' . $ip;

        // 1. Check if IP or Account is currently locked out (Item 1 & 2)
        if (RateLimiter::tooManyAttempts($ipThrottleKey, 1) || RateLimiter::tooManyAttempts($emailThrottleKey, 3)) {
            $seconds = max(
                RateLimiter::availableIn($ipThrottleKey),
                RateLimiter::availableIn($emailThrottleKey)
            );
            if ($seconds <= 0) {
                $seconds = 3600;
            }
            $minutes = max(1, (int) ceil($seconds / 60));

            return back()->with('lockout_seconds', $seconds)
                ->withErrors([
                    'lockout' => "Account locked: Too many failed login attempts. Your login is blocked for {$minutes} minute(s). Please try again later.",
                ])->setStatusCode(429);
        }

        $remember = $request->boolean('remember');

        if (Auth::attempt(['email' => $email, 'password' => $credentials['password']], $remember)) {
            RateLimiter::clear($emailThrottleKey);
            RateLimiter::clear($ipThrottleKey);
            $request->session()->regenerate();

            \App\Services\AuditService::log('auth.login_success', Auth::user());

            return redirect()->intended(route('dashboard'));
        }

        // 2. Increment failed attempt counter with 3600-second (60 minutes) decay window
        RateLimiter::hit($emailThrottleKey, 3600);
        $remaining = RateLimiter::remaining($emailThrottleKey, 3);

        if ($remaining <= 0) {
            // Lock out IP and Account for 60 minutes
            RateLimiter::hit($ipThrottleKey, 3600);
            $seconds = RateLimiter::availableIn($ipThrottleKey);
            if ($seconds <= 0) {
                $seconds = 3600;
            }
            $minutes = max(1, (int) ceil($seconds / 60));

            \App\Services\AuditService::log('auth.login_locked', null, null, [
                'email' => $email,
                'ip' => $ip,
                'lockout_minutes' => 60,
            ]);

            return back()->with('lockout_seconds', $seconds)
                ->withErrors([
                    'lockout' => "Account locked: Too many failed login attempts. Your login is blocked for {$minutes} minute(s). Please try again later.",
                ])->setStatusCode(429);
        }

        $errorMessage = "The provided credentials do not match our records. (Warning: {$remaining} attempt(s) remaining before a 60-minute account lockout).";

        \App\Services\AuditService::log('auth.login_failed', null, null, ['email' => $email, 'remaining' => $remaining]);

        return back()->withErrors([
            'email' => $errorMessage,
        ])->onlyInput('email');
    }

    public function showRegister(Request $request)
    {
        if ($redirect = $this->ensureInvoicySubdomain($request)) {
            return $redirect;
        }

        if (Auth::check()) {
            return redirect()->route('dashboard');
        }
        return view('auth.register');
    }

    public function register(Request $request)
    {
        $data = $request->validate([
            'company_name' => 'required|string|max:255',
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email',
            'password' => [
                'required',
                'string',
                'confirmed',
                \Illuminate\Validation\Rules\Password::min(8)
                    ->letters()
                    ->mixedCase()
                    ->numbers()
                    ->symbols(),
            ],
            'country_code' => 'required|string|size:2',
            'currency' => 'required|string|size:3',
            'coupon_code' => 'nullable|string|max:50',
        ]);

        // 1. Create Workspace for Tenant
        $slug = Str::slug($data['company_name']) . '-' . Str::lower(Str::random(5));
        $workspace = Workspace::create([
            'name' => $data['company_name'],
            'slug' => $slug,
            'country_code' => $data['country_code'],
            'currency' => $data['currency'],
        ]);

        // 2. Create Workspace Owner User
        $user = User::create([
            'workspace_id' => $workspace->id,
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'role' => 'owner',
        ]);

        // 3. Assign Free Trial Subscription (Configurable Days)
        $trialDays = (int) \App\Models\CmsContent::getVal('trial_duration_days', 14);
        $trialPlan = Plan::where('code', 'trial')->first();
        $sub = null;
        if ($trialPlan) {
            $sub = Subscription::create([
                'workspace_id' => $workspace->id,
                'plan_id' => $trialPlan->id,
                'gateway' => 'trial',
                'status' => 'trialing',
                'trial_ends_at' => now()->addDays($trialDays),
                'current_period_ends_at' => now()->addDays($trialDays),
            ]);

            // Dispatch Super Admin Signup Alert (Item 4)
            app(\App\Services\SubscriptionAlertService::class)->notifySignup($user, $workspace, $trialPlan, $sub);
        }

        // 4. Process Promotional / Referral Coupon (Item 4)
        $welcomeMsg = "Welcome to RedMaple Invoicy! Your {$trialDays}-day trial has begun.";
        if (!empty($data['coupon_code'])) {
            try {
                $couponService = app(\App\Services\CouponService::class);
                $check = $couponService->validate($data['coupon_code'], 'signup', 0, $data['currency'], $workspace);
                if ($check['valid']) {
                    $couponService->applyToSignup($check['coupon'], $workspace, $user);
                    $offerName = $check['coupon']->name ?: $check['coupon']->code;
                    $welcomeMsg = "Welcome! Promotional offer '{$offerName}' has been successfully applied to your account.";
                }
            } catch (\Throwable $e) {
                // Graceful fallback if coupon process fails
            }
        }

        Auth::login($user);

        return redirect()->route('dashboard')->with('success', $welcomeMsg);
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }

    public function logoutBeacon(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return response()->json(['status' => 'logged_out', 'message' => 'Session terminated on browser/tab close.']);
    }

    public function showForgotPassword(Request $request)
    {
        if ($redirect = $this->ensureInvoicySubdomain($request)) {
            return $redirect;
        }

        return view('auth.forgot-password');
    }

    public function sendResetLink(Request $request)
    {
        $request->validate(['email' => 'required|email']);
        $email = $request->input('email');

        $user = User::where('email', $email)->first();
        if (!$user) {
            return back()->withErrors(['email' => 'We could not find an account with that email address.']);
        }

        $token = Str::random(60);
        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $email],
            [
                'token' => Hash::make($token),
                'created_at' => now(),
            ]
        );

        $resetUrl = route('password.reset', ['token' => $token, 'email' => $email]);

        try {
            Mail::send('emails.invoice-sent', [
                'invoice' => (object)[
                    'invoice_number' => 'PASSWORD-RESET',
                    'workspace' => (object)['name' => 'Invoicy Security Desk', 'email' => 'support@redmapleagency.com'],
                    'client' => (object)['name' => $user->name],
                    'issue_date' => now(),
                    'due_date' => null,
                    'due_cents' => 0,
                    'currency' => '',
                    'notes' => "You requested a password reset. Click below to securely reset your password. This link is valid for 60 minutes.",
                ],
                'portalUrl' => $resetUrl,
                'pdfUrl' => $resetUrl,
            ], function ($message) use ($email) {
                $message->to($email)->subject('Reset Your Invoicy by RedMaple Password');
            });
            $status = 'A password reset link has been dispatched to your email address.';
        } catch (\Throwable $e) {
            $status = 'Password reset token generated! (Email delivery notice: ' . $e->getMessage() . ')';
        }

        return back()->with('status', $status)->with('direct_reset_url', $resetUrl);
    }

    public function showResetPassword(string $token, Request $request)
    {
        if ($redirect = $this->ensureInvoicySubdomain($request)) {
            return $redirect;
        }

        return view('auth.reset-password', [
            'token' => $token,
            'email' => $request->query('email'),
        ]);
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => [
                'required',
                'string',
                'confirmed',
                \Illuminate\Validation\Rules\Password::min(8)
                    ->letters()
                    ->mixedCase()
                    ->numbers()
                    ->symbols(),
            ],
        ]);

        $record = DB::table('password_reset_tokens')->where('email', $request->email)->first();

        if (!$record || !Hash::check($request->token, $record->token)) {
            return back()->withErrors(['email' => 'This password reset token is invalid or has expired.']);
        }

        $user = User::where('email', $request->email)->first();
        if (!$user) {
            return back()->withErrors(['email' => 'User account not found.']);
        }

        $user->password = Hash::make($request->password);
        $user->save();

        DB::table('password_reset_tokens')->where('email', $request->email)->delete();

        return redirect()->route('login')->with('success', 'Your password has been successfully reset. Please log in with your new password.');
    }

    /**
     * Resend the account email verification link to authenticated user.
     */
    public function resendVerificationEmail(Request $request)
    {
        $user = $request->user();
        if ($user->hasVerifiedEmail()) {
            return back()->with('message', 'Your account email address is already verified.');
        }

        $verifyUrl = \Illuminate\Support\Facades\URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            ['id' => $user->id, 'hash' => sha1($user->email)]
        );

        try {
            $ws = $user->workspace;
            if ($ws && !empty($ws->smtp_settings_encrypted)) {
                $smtp = $ws->smtp_settings_encrypted;
                if (!empty($smtp['host']) && !empty($smtp['username'])) {
                    config([
                        'mail.mailers.smtp.host' => $smtp['host'],
                        'mail.mailers.smtp.port' => $smtp['port'] ?? 465,
                        'mail.mailers.smtp.encryption' => $smtp['encryption'] ?? 'ssl',
                        'mail.mailers.smtp.username' => $smtp['username'],
                        'mail.mailers.smtp.password' => $smtp['password'] ?? '',
                        'mail.from.address' => $smtp['from_address'] ?? $smtp['username'],
                        'mail.from.name' => $smtp['from_name'] ?? 'Invoicy by RedMaple',
                    ]);
                }
            }

            Mail::raw(
                "Hello {$user->name},\n\nPlease click the secure link below to verify your email address on RedMaple Invoicy:\n\n{$verifyUrl}\n\nThis verification link expires in 60 minutes.\n\nThank you,\nRedMaple Invoicy Team",
                function ($message) use ($user) {
                    $message->to($user->email)
                        ->subject('Verify Your Invoicy Account Email Address');
                }
            );

            return back()->with('success', 'A verification link has been sent to ' . $user->email . '. Please check your inbox.');
        } catch (\Throwable $e) {
            \Log::error("Failed to send verification email: " . $e->getMessage());
            return back()->with('warning', 'Could not send verification email: ' . $e->getMessage());
        }
    }

    /**
     * Mark the user's email address as verified from signed URL.
     */
    public function verifyEmail(Request $request, $id, $hash)
    {
        $user = User::findOrFail($id);

        if (!hash_equals((string) $hash, sha1($user->email))) {
            abort(403, 'Invalid or expired email verification signature.');
        }

        if (!$user->hasVerifiedEmail()) {
            $user->markEmailAsVerified();
            \App\Services\AuditService::log('user.email_verified', $user, null, ['email' => $user->email]);
        }

        return redirect()->route('dashboard')->with('success', 'Your account email address has been successfully verified! You can now dispatch invoices and manage team accounts.');
    }
}
