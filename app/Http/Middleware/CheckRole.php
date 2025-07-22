<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string  $role
     * @return mixed
     */
    public function handle(Request $request, Closure $next, $role)
    {
        if (!Auth::check()) {
            return redirect('login');
        }
        
        // For demo purposes, we'll check the role from the user's data
        // In a real application, you might have a proper role relationship
        if ($request->user()->role != $role) {
            return redirect()->route('dashboard')
                ->with('error', 'You do not have permission to access this page.');
        }
        
        return $next($request);
    }
} 