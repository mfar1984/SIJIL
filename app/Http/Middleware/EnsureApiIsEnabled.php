<?php

namespace App\Http\Middleware;

use App\Models\GlobalConfig;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Honours the API kill switch on the API & Integrations tab.
 *
 * The switch has existed for a long time and nothing ever read it, so turning
 * the API "off" changed nothing at all.
 */
class EnsureApiIsEnabled
{
    /**
     * Paths that stay reachable even when the API is switched off.
     *
     * Certificate verification is how a third party confirms a certificate that
     * was handed to them is genuine. Blocking it turns every previously shared
     * verification link into an error, which is not what an operator switching
     * off "the API" is asking for.
     *
     * @var array<int, string>
     */
    private const ALWAYS_AVAILABLE = [
        'api/certificate/verify',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        if ($this->isExempt($request)) {
            return $next($request);
        }

        if ($this->apiIsEnabled()) {
            return $next($request);
        }

        return response()->json([
            'success' => false,
            'message' => 'The API is currently disabled by an administrator.',
        ], 503, ['Retry-After' => '3600']);
    }

    private function isExempt(Request $request): bool
    {
        foreach (self::ALWAYS_AVAILABLE as $path) {
            if ($request->is($path)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Defaults to enabled. If the settings row or its table cannot be read, the
     * safe answer is to keep serving: a database hiccup must not look like an
     * intentional shutdown of the participant app.
     */
    private function apiIsEnabled(): bool
    {
        try {
            return (bool) (GlobalConfig::getConfig()->api_enabled ?? true);
        } catch (\Throwable $e) {
            return true;
        }
    }
}
