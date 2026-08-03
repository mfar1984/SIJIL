<?php

namespace App\Http\Middleware;

use App\Support\SecurityPolicy;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Response headers the application never sent.
 *
 * Without these the admin pages could be framed by another site, browsers were
 * free to guess content types on uploaded files served from our own origin, and
 * full URLs leaked to third parties through the Referer header.
 *
 * Deliberately conservative: no Content-Security-Policy is set, because the app
 * uses inline scripts and inline event handlers throughout and a policy strict
 * enough to be worth having would break them. That is worth doing, but it is a
 * change to every view rather than a header.
 */
class SecurityHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Downloads and file streams should not have headers rewritten.
        if ($response instanceof \Symfony\Component\HttpFoundation\BinaryFileResponse
            || $response instanceof \Symfony\Component\HttpFoundation\StreamedResponse) {
            return $response;
        }

        $headers = [
            // Certificates and QR codes are shared, but the admin interface has no
            // reason to be embedded anywhere.
            'X-Frame-Options' => 'SAMEORIGIN',

            // Uploaded files are served from the same origin as the app, so a file
            // sniffed as HTML would run in our context.
            'X-Content-Type-Options' => 'nosniff',

            // Registration and verification links carry tokens in the path.
            'Referrer-Policy' => 'strict-origin-when-cross-origin',

            'X-Permitted-Cross-Domain-Policies' => 'none',

            'Permissions-Policy' => 'geolocation=(self), camera=(self), microphone=(), payment=()',
        ];

        foreach ($headers as $name => $value) {
            if (! $response->headers->has($name)) {
                $response->headers->set($name, $value);
            }
        }

        // Only meaningful over HTTPS, and actively harmful if sent while the site
        // is still reachable over HTTP: a browser that caches it will refuse the
        // plain HTTP version for the whole max-age.
        if (SecurityPolicy::forceSsl() && $request->secure()) {
            $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
        }

        return $response;
    }
}
