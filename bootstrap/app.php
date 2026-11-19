<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // Trusted Proxies Configuration for Cloudflare and Hostinger CDN (Item 7)
        $middleware->trustProxies(at: '*', headers: Request::HEADER_X_FORWARDED_FOR | Request::HEADER_X_FORWARDED_HOST | Request::HEADER_X_FORWARDED_PORT | Request::HEADER_X_FORWARDED_PROTO);

        // Global Firewall & Security Headers across all requests
        $middleware->prepend([
            \App\Http\Middleware\FirewallMiddleware::class,
            \App\Http\Middleware\SecurityHeadersMiddleware::class,
        ]);

        $middleware->web(append: [
            \App\Http\Middleware\SetLocale::class,
        ]);
        $middleware->alias([
            'super_admin' => \App\Http\Middleware\EnsureSuperAdmin::class,
        ]);
        $middleware->validateCsrfTokens(except: [
            'webhooks/*',
            'logout-beacon',
            'api/ai/copilot',
            '*/api/ai/copilot',
            '*/api/*',
            'api/*',
            'membership/cancel-request/*',
            '*/membership/cancel-request/*',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->render(function (\Symfony\Component\HttpKernel\Exception\NotFoundHttpException $e, Request $request) {
            return response()->view('errors.404', [], 404);
        });

        $exceptions->render(function (\Illuminate\Session\TokenMismatchException $e, Request $request) {
            return response()->view('errors.419', [], 419);
        });

        $exceptions->render(function (MethodNotAllowedHttpException $e, Request $request) {
            if ($request->is('logout') || $request->is('*/logout')) {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();
                return redirect()->route('login');
            }

            if (Auth::check()) {
                return redirect()->route('dashboard')->with('error', 'The requested action method was invalid. Redirected to dashboard.');
            }

            return redirect()->route('login');
        });
    })->create();
