<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * CORS for the endpoints the marketing website calls (api/public/*).
 *
 * Laravel's HandleCors is told to skip these paths (see AppServiceProvider), and
 * this middleware answers instead, using config/public_api.php. It is written by
 * hand rather than on top of the CorsService that HandleCors uses, because that
 * service echoes its only allowed origin back to every caller when the list has
 * one entry, and a foreign site must receive no Access-Control-Allow-Origin at
 * all.
 *
 * The rules are deliberately narrow:
 *
 *  - Only an exact, listed origin is echoed back. "*" is never sent, and a list
 *    entry containing "*" is discarded rather than trusted.
 *  - Credentials are never allowed. These endpoints read no cookie and no token,
 *    so there is nothing a browser should be sending along.
 *  - Vary: Origin is always set, so a shared cache cannot hand the answer for one
 *    origin to another.
 */
class PublicApiCors
{
    public static function appliesTo(Request $request): bool
    {
        $paths = (array) config('public_api.cors.paths', []);

        return $paths !== [] && $request->is(...$paths);
    }

    public function handle(Request $request, Closure $next): Response
    {
        if (! static::appliesTo($request)) {
            return $next($request);
        }

        $origin = $request->headers->get('Origin');
        $allowed = is_string($origin) && in_array($origin, $this->allowedOrigins(), true);

        if ($request->isMethod('OPTIONS') && $request->headers->has('Access-Control-Request-Method')) {
            return $this->preflight($request, $allowed ? $origin : null);
        }

        $response = $next($request);

        if ($allowed) {
            $response->headers->set('Access-Control-Allow-Origin', $origin);

            // The rate limit answer carries Retry-After, and the page needs to read
            // it to tell the visitor how long to wait.
            $response->headers->set('Access-Control-Expose-Headers', 'Retry-After');
        }

        $response->setVary('Origin', false);

        return $response;
    }

    /**
     * Answer a preflight without reaching the route, which accepts GET only.
     *
     * An unlisted origin or method still gets a 204, just without the headers
     * that would let the browser proceed.
     */
    private function preflight(Request $request, ?string $origin): Response
    {
        $response = new \Illuminate\Http\Response('', 204);

        $methods = array_map('strtoupper', (array) config('public_api.cors.allowed_methods', ['GET']));
        $requested = strtoupper((string) $request->headers->get('Access-Control-Request-Method'));

        if ($origin !== null && in_array($requested, $methods, true)) {
            $response->headers->set('Access-Control-Allow-Origin', $origin);
            $response->headers->set('Access-Control-Allow-Methods', implode(', ', $methods));
            $response->headers->set(
                'Access-Control-Allow-Headers',
                implode(', ', (array) config('public_api.cors.allowed_headers', []))
            );
            $response->headers->set('Access-Control-Max-Age', (string) (int) config('public_api.cors.max_age', 0));
        }

        $response->setVary(['Origin', 'Access-Control-Request-Method', 'Access-Control-Request-Headers'], false);

        return $response;
    }

    /**
     * The configured origins, plus the local preview server while APP_ENV is local.
     *
     * Anything that is not a bare scheme://host[:port] is dropped here, so a
     * mistyped or over-broad environment value narrows access instead of widening
     * it.
     *
     * @return array<int, string>
     */
    private function allowedOrigins(): array
    {
        $origins = (array) config('public_api.cors.allowed_origins', []);

        if (app()->environment('local')) {
            $origins = array_merge($origins, (array) config('public_api.cors.local_origins', []));
        }

        $valid = [];

        foreach ($origins as $origin) {
            $origin = rtrim(trim((string) $origin), '/');

            if (! str_contains($origin, '*') && preg_match('#^https?://[A-Za-z0-9.-]+(?::\d{1,5})?$#', $origin)) {
                $valid[] = $origin;
            }
        }

        return array_values(array_unique($valid));
    }
}
