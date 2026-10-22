<?php

namespace App\Http\Controllers;

use App\Models\Plan;
use App\Models\Subscription;
use App\Models\User;
use App\Models\Workspace;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
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
            'password' => 'required|string|min:8|confirmed',
            'country_code' => 'required|string|size:2',
            'currency' => 'required|string|size:3',
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

        Auth::login($user);

        return redirect()->route('dashboard')->with('success', 'Welcome to RedMaple Invoice Generator! Your 14-day trial has begun.');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
