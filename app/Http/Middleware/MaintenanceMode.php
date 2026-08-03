<?php

namespace App\Http\Middleware;

use App\Support\SystemSettings;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Honours the Maintenance Mode switch on the General tab, which was stored and
 * read by nothing.
 *
 * Distinct from "php artisan down": that takes the whole application off the air
 * including the administrator, and is driven by a file rather than by a setting.
 * This closes the participant-facing surface while leaving an administrator able
 * to sign in and switch it back off.
 */
class MaintenanceMode
{
    /**
     * Paths that stay open while maintenance is on.
     *
     * Sign-in has to work, or the switch becomes one-way: nobody could get in to
     * turn it off. The health check is how a load balancer decides the host is
     * alive, and certificate verification is a link already handed to third
     * parties that has nothing to do with the system being worked on.
     *
     * @var array<int, string>
     */
    private const ALWAYS_OPEN = [
        '/',
        'login',
        'logout',
        'up',
        'verify',
        'verify/*',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        if (! SystemSettings::maintenanceMode()) {
            return $next($request);
        }

        foreach (self::ALWAYS_OPEN as $path) {
            if ($request->is($path)) {
                return $next($request);
            }
        }

        // An administrator keeps full access, otherwise turning this on would lock
        // out the only person who can turn it off.
        $user = $request->user();

        if ($user && method_exists($user, 'hasRole') && $user->hasRole('Administrator')) {
            return $next($request);
        }

        if ($request->expectsJson()) {
            return response()->json([
                'success' => false,
                'message' => 'The system is temporarily unavailable for maintenance.',
            ], 503, ['Retry-After' => '1800']);
        }

        return response()->view('errors.maintenance', [
            'orgName' => SystemSettings::orgName(),
        ], 503, ['Retry-After' => '1800']);
    }
}
