<?php

namespace App\Providers;

use App\Helpers\RolePermission;
use App\Models\GlobalConfig;
use App\Support\ApiSurface;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\RateLimiter;
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

        $this->configureRateLimiting();
        $this->configureCors();
        $this->configureSecurity();
        $this->configureGeneral();
    }

    /**
     * Apply the General tab settings that belong to configuration.
     *
     * The timezone and the activity logging switch were both stored and read by
     * nothing: dates rendered in whatever config/app.php said, and unticking
     * "Enable activity logging" did not stop a single entry being written.
     */
    private function configureGeneral(): void
    {
        $timezone = \App\Support\SystemSettings::timezone();

        if ($timezone !== config('app.timezone')) {
            config(['app.timezone' => $timezone]);
            date_default_timezone_set($timezone);
        }

        config(['activitylog.enabled' => \App\Support\SystemSettings::activityLoggingEnabled()]);

        /*
         * Spatie decides whether to log through Support\ActivityLogStatus, which
         * copies activitylog.enabled into a property in its constructor and never
         * looks at the config again. Setting the config alone therefore only works
         * while that instance has not been created yet, which happens to be true
         * during a normal request and is not something to rely on.
         *
         * The class is registered with scoped(), so dropping the instance makes it
         * re-read on next use and the switch takes effect regardless of ordering.
         */
        $statusClass = \Spatie\Activitylog\Support\ActivityLogStatus::class;

        if (class_exists($statusClass) && app()->resolved($statusClass)) {
            app()->forgetInstance($statusClass);
        }
    }

    /**
     * Apply the Security tab settings that live in configuration rather than in
     * a validation rule: session lifetime, forced HTTPS, and the password rule
     * every framework-supplied Auth controller reaches for.
     */
    private function configureSecurity(): void
    {
        // Password::defaults() was never configured, so the three Auth
        // controllers that use it enforced only a length of 8 and none of the
        // character requirements on the Security tab.
        \Illuminate\Validation\Rules\Password::defaults(
            fn () => \App\Support\SecurityPolicy::passwordRule()
        );

        // Session Timeout was stored and never read; the real lifetime came from
        // SESSION_LIFETIME in the environment.
        config(['session.lifetime' => \App\Support\SecurityPolicy::sessionTimeoutMinutes()]);

        if (\App\Support\SecurityPolicy::forceSsl()) {
            // Force SSL/HTTPS was stored and never read either. Generated URLs
            // and the session cookie both have to follow, otherwise links stay
            // http:// and the cookie is still sent in clear.
            \Illuminate\Support\Facades\URL::forceScheme('https');
            config(['session.secure' => true]);
        }
    }

    /**
     * Register the named rate limiters the API routes use.
     *
     * None existed. app/Http/Kernel.php declared an api group throttled with
     * "throttle:api", but that file is not read by this version of Laravel and
     * the "api" limiter was never defined anywhere, so nothing was limited and
     * the Rate Limit setting on the API tab had no effect at all.
     */
    private function configureRateLimiting(): void
    {
        // Ordinary API traffic. Keyed per authenticated participant, per API key,
        // then per IP, so one busy client cannot consume another's allowance.
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute($this->configuredApiRateLimit())
                ->by($this->clientKey($request));
        });

        // Unauthenticated reads that are cheap to call and expensive to serve.
        // Certificate verification is the one worth protecting: numbers are
        // guessable enough that an unlimited endpoint invites walking them.
        RateLimiter::for('api-public', function (Request $request) {
            return Limit::perMinute(30)->by($request->ip());
        });
    }

    private function configuredApiRateLimit(): int
    {
        try {
            $configured = (int) (GlobalConfig::getConfig()->api_rate_limit ?? 60);
        } catch (\Throwable $e) {
            // A limiter that throws would take down every API route, so an
            // unreadable setting has to fall back rather than fail.
            return 60;
        }

        return max(10, min(1000, $configured));
    }

    private function clientKey(Request $request): string
    {
        if ($key = $request->attributes->get('api_key')) {
            return 'key:' . $key->getKey();
        }

        if ($user = $request->user()) {
            return 'user:' . $user->getAuthIdentifier();
        }

        return 'ip:' . $request->ip();
    }

    /**
     * Apply the CORS allow-list from the API tab.
     *
     * config/cors.php is hard-coded to allow every origin. The tab has always
     * shown a domain list and a "Allow CORS" switch that config never read, so
     * the restriction it described was not in force. Overriding here rather than
     * querying the database inside config/cors.php keeps config:cache usable.
     */
    private function configureCors(): void
    {
        try {
            $config = GlobalConfig::getConfig();
        } catch (\Throwable $e) {
            return;
        }

        if (! (bool) ($config->api_cors_enabled ?? false)) {
            return;
        }

        $origins = ApiSurface::originList($config->cors_domains);

        if (! $origins) {
            // An empty list would otherwise block every browser client,
            // including the participant app. Leave the default alone instead.
            return;
        }

        $exact = [];
        $patterns = [];

        foreach ($origins as $origin) {
            if (str_contains($origin, '*')) {
                // "https://*.example.com" becomes an anchored regex rather than a
                // substring match, so "https://evil.com/?x=.example.com" cannot pass.
                $patterns[] = '#^' . str_replace('\*', '[^.]+', preg_quote($origin, '#')) . '$#i';
            } else {
                $exact[] = rtrim($origin, '/');
            }
        }

        config([
            'cors.allowed_origins' => $exact,
            'cors.allowed_origins_patterns' => $patterns,
        ]);
    }
}
