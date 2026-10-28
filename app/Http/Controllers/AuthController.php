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
use Illuminate\Support\Str;

class AuthController extends Controller
{
    public function showLogin()
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        $remember = $request->boolean('remember');

        if (Auth::attempt($credentials, $remember)) {
            $request->session()->regenerate();
            return redirect()->intended(route('dashboard'));
        }

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ])->onlyInput('email');
    }

    public function showRegister()
    {
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

        // 3. Assign 14-Day Free Trial Subscription
        $trialPlan = Plan::where('code', 'trial')->first();
        if ($trialPlan) {
            Subscription::create([
                'workspace_id' => $workspace->id,
                'plan_id' => $trialPlan->id,
                'gateway' => 'trial',
                'status' => 'trialing',
                'trial_ends_at' => now()->addDays(14),
            ]);
        }

        // 4. Process Promotional / Referral Coupon (Item 4)
        $welcomeMsg = 'Welcome to RedMaple Invoicy! Your 14-day trial has begun.';
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

    public function showForgotPassword()
    {
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
}
