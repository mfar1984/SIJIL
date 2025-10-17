<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckPermission
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, $permission): Response
    {
        $user = $request->user();
        
        if (! $user || ! $user->hasPermissionTo($permission)) {
            abort(403, 'Unauthorized action. You do not have the necessary permissions to access this page.');
        }

        return $next($request);
    }
}
