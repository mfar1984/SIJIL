<?php

namespace App\Http\Middleware;

use App\Models\ApiKey;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Authenticates a server-to-server caller by API key.
 *
 * Applied only to the /api/v1 integration surface. It is deliberately NOT
 * applied to the participant endpoints: those are used by the app at
 * user.e-certificate.com.my, which authenticates with a Sanctum bearer token,
 * and requiring a key there would lock every participant out.
 */
class AuthenticateApiKey
{
    public const HEADER = 'X-API-Key';

    /**
     * @param string ...$abilities Abilities the key must hold, all of them.
     */
    public function handle(Request $request, Closure $next, string ...$abilities): Response
    {
        $presented = $request->header(self::HEADER)
            ?? $request->bearerToken()
            ?? '';

        $key = ApiKey::findUsable((string) $presented);

        if (! $key) {
            // One message for missing, wrong, expired and revoked alike. Telling
            // a caller that a key "has expired" confirms the key was real.
            return response()->json([
                'success' => false,
                'message' => 'Invalid or missing API key.',
            ], 401, ['WWW-Authenticate' => 'ApiKey']);
        }

        // Recorded before the ability check, so "last used" means the last time
        // this key was presented and accepted. A key repeatedly reaching for an
        // ability it was never granted is worth being visible rather than
        // invisible, which is what recording only permitted calls would give.
        $key->recordUse($request->ip());

        foreach ($abilities as $ability) {
            if (! $key->can($ability)) {
                return response()->json([
                    'success' => false,
                    'message' => 'This API key does not have the "' . $ability . '" ability.',
                ], 403);
            }
        }

        // Downstream code and the rate limiter both need to know which key this is.
        $request->attributes->set('api_key', $key);

        return $next($request);
    }
}
