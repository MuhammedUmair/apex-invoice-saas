<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class EnsureSuperAdmin
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, ?string $permission = null): Response
    {
        $superAdminUser = null;

        // 1. Check dedicated 'superadmin' guard
        if (Auth::guard('superadmin')->check()) {
            Auth::shouldUse('superadmin');
            $superAdminUser = Auth::guard('superadmin')->user();
        } elseif (session()->has('superadmin_authenticated_id')) {
            $user = \App\Models\User::find(session('superadmin_authenticated_id'));
            if ($user && $user->isSuperAdmin()) {
                Auth::guard('superadmin')->login($user, false);
                Auth::shouldUse('superadmin');
                $superAdminUser = $user;
            }
        } elseif (Auth::guard('web')->check() && Auth::guard('web')->user()->isSuperAdmin()) {
            // Backward-compatibility fallback for pre-existing active session
            Auth::guard('superadmin')->login(Auth::guard('web')->user(), false);
            Auth::shouldUse('superadmin');
            $superAdminUser = Auth::guard('web')->user();
        }

        if (!$superAdminUser || !$superAdminUser->isSuperAdmin()) {
            if ($request->expectsJson()) {
                abort(403, 'Unauthorized access: Platform Super Admin privileges are required.');
            }
            return redirect()->route('cp.login')->withErrors([
                'email' => 'Please authenticate into the Super Admin Control Panel (/cp) to access this suite.',
            ]);
        }

        // Synchronize authenticated Super Admin context into guards and views
        Auth::shouldUse('superadmin');
        Auth::guard('superadmin')->setUser($superAdminUser);
        view()->share('currentSuperAdmin', $superAdminUser);

        if ($permission && !$superAdminUser->hasAdminPermission($permission)) {
            abort(403, "Access Denied: Your administrator role does not have permission to access '{$permission}'.");
        }

        // Two-Factor Authentication Check for Super Admins
        if ($superAdminUser->hasTwoFactorEnabled() && !session('super_admin_2fa_verified')) {
            if (!$request->is('admin/2fa*')) {
                return response()->view('auth.admin-2fa-challenge');
            }
        }

        return $next($request);
    }
}
