<?php

namespace App\Providers;

use App\Helpers\RolePermission;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;
use Illuminate\Pagination\Paginator;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Add blade directives for role and permission checking
        Blade::if('role', function ($role) {
            return Auth::check() && Auth::user()->role === $role;
        });
        
        Blade::if('permission', function ($permission) {
            return RolePermission::hasPermission($permission);
        });
        
        Blade::if('owns', function ($resource) {
            return RolePermission::ownsResource($resource);
        });

        // Enable Tailwind pagination
        Paginator::useTailwind();

        // Make sure Google credentials env is set for FCM HTTP v1
        $credPath = config('services.firebase.credentials');
        if ($credPath) {
            $full = base_path($credPath);
            if (file_exists($full)) {
                putenv('GOOGLE_APPLICATION_CREDENTIALS='.$full);
            }
        }
    }
}
