<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('X-Frame-Options', 'DENY');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->headers->set('Permissions-Policy', 'camera=(), microphone=(), geolocation=(self), focus-without-user-activation=()');
        $response->headers->set('Cross-Origin-Opener-Policy', 'same-origin');
        $response->headers->set('Cross-Origin-Resource-Policy', 'same-origin');

        if ($request->isSecure()) {
            $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
        }

        $scriptSrc = "'self' 'unsafe-eval'";
        $styleSrc = "'self' 'unsafe-inline' https://fonts.bunny.net";
        $connectSrc = "'self'";

        if (app()->isLocal() && file_exists(public_path('hot'))) {
            $hotUrl = trim((string) file_get_contents(public_path('hot')));
            if ($hotUrl !== '') {
                $wsUrl = preg_replace('/^http/', 'ws', $hotUrl);
                $scriptSrc .= " {$hotUrl}";
                $styleSrc .= " {$hotUrl}";
                $connectSrc .= " {$hotUrl} {$wsUrl}";
            }
        }

        $csp = implode('; ', [
            "default-src 'self'",
            "script-src {$scriptSrc}",
            "style-src {$styleSrc}",
            "font-src 'self' https://fonts.bunny.net",
            "img-src 'self' data:",
            "connect-src {$connectSrc}",
            "form-action 'self'",
            "base-uri 'self'",
            "object-src 'none'",
            "frame-ancestors 'none'",
        ]);

        $response->headers->set('Content-Security-Policy', $csp);

        return $response;
    }
}
