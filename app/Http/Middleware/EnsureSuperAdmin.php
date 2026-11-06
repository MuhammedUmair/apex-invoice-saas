<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureSuperAdmin
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!auth()->check() || !auth()->user()->isSuperAdmin()) {
            abort(403, 'Unauthorized access: Platform Super Admin privileges are required to access this area.');
        }

        // Two-Factor Authentication Check for Super Admins (Item 9 & 11)
        if (auth()->user()->hasTwoFactorEnabled() && !session('super_admin_2fa_verified')) {
            if (!$request->is('admin/2fa*')) {
                return response()->view('auth.admin-2fa-challenge');
            }
        }

        return $next($request);
    }
}
