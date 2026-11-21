<?php

namespace App\Http\Middleware;

use App\Support\PaymentPermissions;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Lets only holders of the Administrator role through.
 *
 * Used on the Global Config > Payment routes, which handle the CHIP secret key.
 * A permission check alone is not enough there: a permission row can be
 * attached to any role, and this lock must hold even if one were.
 */
class EnsureAdministrator
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! PaymentPermissions::isAdministrator($request->user())) {
            abort(403, 'Only an Administrator can access this page.');
        }

        return $next($request);
    }
}
