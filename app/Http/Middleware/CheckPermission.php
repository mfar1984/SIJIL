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
        
        // Debug logging
        \Log::info('CheckPermission middleware', [
            'permission' => $permission,
            'user_id' => $user ? $user->id : null,
            'user_name' => $user ? $user->name : null,
            'has_permission' => $user ? $user->hasPermissionTo($permission) : false,
            'all_permissions' => $user ? $user->getAllPermissions()->pluck('name')->toArray() : [],
        ]);
        
        if (! $user || ! $user->hasPermissionTo($permission)) {
            abort(403, 'Unauthorized action. You do not have the necessary permissions to access this page.');
        }

        return $next($request);
    }
}
