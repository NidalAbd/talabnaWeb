<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Baseline security headers on every response (2026-10-03): HTTPS-only for
 * browsers (HSTS), no MIME sniffing, no framing by other sites, and no PHP
 * version disclosure.
 */
class SecurityHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if (function_exists("header_remove")) {
            @header_remove("X-Powered-By");
        }
        $response->headers->remove("X-Powered-By");
        $h = $response->headers;
        if ($request->secure() || $request->header("X-Forwarded-Proto") === "https") {
            $h->set("Strict-Transport-Security", "max-age=31536000");
        }
        if (!$h->has("X-Content-Type-Options")) $h->set("X-Content-Type-Options", "nosniff");
        if (!$h->has("X-Frame-Options")) $h->set("X-Frame-Options", "SAMEORIGIN");
        if (!$h->has("Referrer-Policy")) $h->set("Referrer-Policy", "strict-origin-when-cross-origin");

        return $response;
    }
}
