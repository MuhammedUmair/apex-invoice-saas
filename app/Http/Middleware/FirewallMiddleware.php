<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class FirewallMiddleware
{
    /**
     * Known malicious automated scanner signatures.
     */
    protected array $blockedUserAgents = [
        'sqlmap',
        'nikto',
        'wpscan',
        'acunetix',
        'masscan',
        'dirbuster',
        'havij',
        'nessus',
        'zaproxy',
        'burpcollaborator',
        'openvas',
    ];

    /**
     * Handle an incoming request through the application-level firewall.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // 1. Block prohibited HTTP methods (RFC inspection)
        if (in_array(strtoupper($request->method()), ['TRACE', 'TRACK', 'CONNECT'])) {
            return response('Method Not Allowed by Security Firewall', 405);
        }

        // 2. Block Automated Vulnerability Scanners by User-Agent
        $userAgent = strtolower($request->header('User-Agent', ''));
        foreach ($this->blockedUserAgents as $bot) {
            if (str_contains($userAgent, $bot)) {
                return response('Automated Security Scanner Blocked', 403);
            }
        }

        // 3. Detect and block Null-Byte Injection attacks
        $rawUri = $request->getRequestUri();
        if (str_contains($rawUri, '%00') || str_contains($rawUri, "\0")) {
            return response('Bad Request: Null Byte Injection Detected', 400);
        }

        // 4. Detect Directory Traversal in URI and inputs
        if (preg_match('/\.\.[\/\\\\]/', $rawUri) || preg_match('/%2e%2e[%2f%5c]/i', $rawUri)) {
            return response('Bad Request: Directory Traversal Blocked', 400);
        }

        // 5. Inspect recursive inputs for null bytes or dangerous binary exploits
        $input = $request->all();
        if ($this->hasNullByte($input)) {
            return response('Bad Request: Invalid Character Sequence', 400);
        }

        return $next($request);
    }

    /**
     * Recursively scan inputs for null bytes.
     */
    protected function hasNullByte(mixed $value): bool
    {
        if (is_array($value)) {
            foreach ($value as $item) {
                if ($this->hasNullByte($item)) {
                    return true;
                }
            }
            return false;
        }

        if (is_string($value)) {
            return str_contains($value, "\0") || str_contains($value, '%00');
        }

        return false;
    }
}
