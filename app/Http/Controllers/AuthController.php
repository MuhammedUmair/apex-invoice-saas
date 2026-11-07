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

    /**
     * Dedicated Control Panel Login for Platform Super Administrators (/cp).
     */
    public function showCpLogin(Request $request)
    {
        if ($redirect = $this->ensureInvoicySubdomain($request)) {
            return $redirect;
        }

        if (Auth::check()) {
            return redirect()->route('dashboard');
        }

        $ipThrottleKey = 'cp_ip_locked:' . $request->ip();
        $isLocked = false;
        $lockoutSeconds = 0;

        if (RateLimiter::tooManyAttempts($ipThrottleKey, 1)) {
            $isLocked = true;
            $lockoutSeconds = RateLimiter::availableIn($ipThrottleKey);
        } elseif (session()->has('lockout_seconds')) {
            $isLocked = true;
            $lockoutSeconds = (int) session('lockout_seconds');
        }

        return view('auth.cp-login', compact('isLocked', 'lockoutSeconds'));
    }

    /**
     * Authenticate Super Admin via Control Panel (/cp) and dispatch Email OTP.
     */
    public function cpLogin(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        $ip = $request->ip();
        $email = Str::lower(trim($credentials['email']));
        $ipThrottleKey = 'cp_ip_locked:' . $ip;
        $emailThrottleKey = 'cp_attempt:' . $email . '|' . $ip;

        // Enforce 60-minute lockout on 3 failed attempts
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

        $user = User::whereRaw('LOWER(email) = ?', [$email])->first();

        if ($user && Hash::check($credentials['password'], $user->password)) {
            if (!$user->isSuperAdmin()) {
                RateLimiter::hit($emailThrottleKey, 3600);
                \App\Services\AuditService::log('auth.cp_unauthorized_attempt', null, null, [
                    'email' => $email,
                    'ip' => $ip,
                ]);

                return back()->withErrors([
                    'email' => 'Access Denied: This Control Panel is restricted exclusively to Platform Super Administrators.',
                ])->onlyInput('email');
            }

            // Generate 6-digit OTP passcode
            $otp = (string) random_int(100000, 999999);

            // Store in session (expires in 10 minutes)
            $request->session()->put('cp_otp_user_id', $user->id);
            $request->session()->put('cp_otp_hash', Hash::make($otp));
            $request->session()->put('cp_otp_expires_at', now()->addMinutes(10)->timestamp);
            $request->session()->put('cp_otp_email', $user->email);
            $request->session()->put('cp_otp_attempts', 0);

            // Dispatch OTP email
            try {
                Mail::to($user->email)->send(new \App\Mail\SuperAdminOtpMailable($otp, $user));
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::error('SuperAdmin OTP email dispatch failed: ' . $e->getMessage());
            }

            \App\Services\AuditService::log('auth.cp_otp_dispatched', $user, null, [
                'email' => $user->email,
                'ip' => $ip,
            ]);

            return redirect()->route('cp.otp');
        }

        // Increment failed attempt counter with 3600-second (60 minutes) decay window
        RateLimiter::hit($emailThrottleKey, 3600);
        $remaining = RateLimiter::remaining($emailThrottleKey, 3);

        if ($remaining <= 0) {
            RateLimiter::hit($ipThrottleKey, 3600);
            $seconds = RateLimiter::availableIn($ipThrottleKey);
            if ($seconds <= 0) {
                $seconds = 3600;
            }
            $minutes = max(1, (int) ceil($seconds / 60));

            \App\Services\AuditService::log('auth.cp_login_locked', null, null, [
                'email' => $email,
                'ip' => $ip,
                'lockout_minutes' => 60,
            ]);

            return back()->with('lockout_seconds', $seconds)
                ->withErrors([
                    'lockout' => "Account locked: Too many failed login attempts. Your login is blocked for {$minutes} minute(s). Please try again later.",
                ])->setStatusCode(429);
        }

        $errorMessage = "The provided Super Admin credentials do not match our records. (Warning: {$remaining} attempt(s) remaining before a 60-minute account lockout).";

        \App\Services\AuditService::log('auth.cp_login_failed', null, null, ['email' => $email, 'remaining' => $remaining]);

        return back()->withErrors([
            'email' => $errorMessage,
        ])->onlyInput('email');
    }

    /**
     * Show Super Admin OTP Verification Screen (/cp/otp).
     */
    public function showCpOtp(Request $request)
    {
        if ($redirect = $this->ensureInvoicySubdomain($request)) {
            return $redirect;
        }

        if (Auth::check()) {
            return redirect()->route('dashboard');
        }

        $userId = session('cp_otp_user_id');
        $expiresAt = (int) session('cp_otp_expires_at', 0);

        if (!$userId || now()->timestamp > $expiresAt) {
            $request->session()->forget(['cp_otp_user_id', 'cp_otp_hash', 'cp_otp_expires_at', 'cp_otp_email', 'cp_otp_attempts']);
            return redirect()->route('cp.login')->withErrors([
                'email' => 'Your verification session has expired. Please sign in again.',
            ]);
        }

        $email = session('cp_otp_email', 'superadmin@redmapleagency.com');
        $parts = explode('@', $email);
        $maskedEmail = substr($parts[0], 0, 3) . '***@' . ($parts[1] ?? 'redmapleagency.com');
        $remainingSeconds = max(0, $expiresAt - now()->timestamp);

        return view('auth.cp-otp', compact('maskedEmail', 'remainingSeconds'));
    }

    /**
     * Verify Super Admin 6-digit OTP passcode.
     */
    public function verifyCpOtp(Request $request)
    {
        $request->validate([
            'otp' => 'required|digits:6',
        ]);

        $userId = session('cp_otp_user_id');
        $otpHash = session('cp_otp_hash');
        $expiresAt = (int) session('cp_otp_expires_at', 0);

        if (!$userId || !$otpHash || now()->timestamp > $expiresAt) {
            $request->session()->forget(['cp_otp_user_id', 'cp_otp_hash', 'cp_otp_expires_at', 'cp_otp_email', 'cp_otp_attempts']);
            return redirect()->route('cp.login')->withErrors([
                'email' => 'Your verification session has expired. Please sign in again.',
            ]);
        }

        $attempts = (int) session('cp_otp_attempts', 0) + 1;
        session(['cp_otp_attempts' => $attempts]);

        if ($attempts > 5) {
            $request->session()->forget(['cp_otp_user_id', 'cp_otp_hash', 'cp_otp_expires_at', 'cp_otp_email', 'cp_otp_attempts']);
            return redirect()->route('cp.login')->withErrors([
                'email' => 'Too many invalid verification attempts. Security session terminated.',
            ]);
        }

        if (!Hash::check($request->otp, $otpHash)) {
            $rem = 5 - $attempts;
            return back()->withErrors([
                'otp' => "Invalid verification passcode. ($rem attempt(s) remaining before session reset)",
            ]);
        }

        $user = User::findOrFail($userId);
        if (!$user->isSuperAdmin()) {
            $request->session()->forget(['cp_otp_user_id', 'cp_otp_hash', 'cp_otp_expires_at', 'cp_otp_email', 'cp_otp_attempts']);
            return redirect()->route('cp.login')->withErrors([
                'email' => 'Access Denied: Account is not authorized for Control Panel.',
            ]);
        }

        // Clean OTP session state
        $request->session()->forget(['cp_otp_user_id', 'cp_otp_hash', 'cp_otp_expires_at', 'cp_otp_email', 'cp_otp_attempts']);

        // Clear IP & email rate limiters
        $ip = $request->ip();
        $email = Str::lower(trim($user->email));
        RateLimiter::clear('cp_ip_locked:' . $ip);
        RateLimiter::clear('cp_attempt:' . $email . '|' . $ip);

        // Authenticate Super Admin session
        Auth::login($user, false);
        $request->session()->regenerate();

        \App\Services\AuditService::log('auth.cp_login_success', $user, null, [
            'mfa' => 'email_otp',
            'ip' => $ip,
        ]);

        return redirect()->intended(route('dashboard'));
    }

    /**
     * Resend Super Admin OTP passcode.
     */
    public function resendCpOtp(Request $request)
    {
        $userId = session('cp_otp_user_id');
        if (!$userId) {
            return redirect()->route('cp.login');
        }

        $throttleKey = 'cp_otp_resend:' . $userId;
        if (RateLimiter::tooManyAttempts($throttleKey, 1)) {
            $sec = RateLimiter::availableIn($throttleKey);
            return back()->withErrors([
                'otp' => "Please wait {$sec} second(s) before requesting another code.",
            ]);
        }
        RateLimiter::hit($throttleKey, 60);

        $user = User::find($userId);
        if (!$user) {
            return redirect()->route('cp.login');
        }

        $otp = (string) random_int(100000, 999999);
        $request->session()->put('cp_otp_hash', Hash::make($otp));
        $request->session()->put('cp_otp_expires_at', now()->addMinutes(10)->timestamp);

        try {
            Mail::to($user->email)->send(new \App\Mail\SuperAdminOtpMailable($otp, $user));
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('SuperAdmin OTP resend failed: ' . $e->getMessage());
        }

        \App\Services\AuditService::log('auth.cp_otp_resent', $user);

        return back()->with('status', 'A fresh verification code has been dispatched to your email.');
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
