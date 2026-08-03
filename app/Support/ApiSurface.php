<?php

namespace App\Support;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;

/**
 * Describes the API as it actually is, by reading the router and the database
 * rather than by reading settings that claim what it ought to be.
 *
 * Every figure on the API & Integrations tab comes from here. The tab used to
 * describe an API that did not exist: it offered API keys with no table behind
 * them, OAuth with no package installed, a rate limit no middleware read, and a
 * CORS allow-list that config/cors.php ignored in favour of "*". Reading the
 * live router is what stops that drift happening again.
 */
class ApiSurface
{
    /**
     * Endpoints whose limit is deliberately tighter than the global setting.
     * Listed here only so the tab can explain why they differ.
     */
    private const HARDENED = [
        'api/participant/login',
        'api/participant/reset-password',
        'api/participant/lookup',
        'api/participant/verify',
        'api/participant/register',
        'api/participant/reset-password-for-account',
    ];

    /**
     * @return array<string, mixed>
     */
    public static function payload(): array
    {
        $endpoints = static::endpoints();

        return [
            'endpoints' => $endpoints,
            'summary' => static::summary($endpoints),
            'tokens' => static::tokens(),
            'keys' => static::keys(),
            'webhooks' => static::webhooks(),
            'deliveries' => static::deliveries(),
            'events' => ApiEvents::all(),
            'integration_endpoints' => static::integrationEndpoints(),
            'abilities' => \App\Models\ApiKey::availableAbilities(),
            'auth_header' => \App\Http\Middleware\AuthenticateApiKey::HEADER,
            'signature_header' => \App\Services\WebhookDispatcher::SIGNATURE_HEADER,
            'base_url' => rtrim(config('app.url'), '/') . '/api',
            'cors_effective' => static::corsEffective(),
            'storage_ready' => static::storageReady(),
            'queue_connection' => config('queue.default'),
        ];
    }

    /**
     * True once the migration that creates the API key and webhook tables has run.
     * The panels render their real layout either way, but nothing pretends to
     * work before the storage exists.
     */
    public static function storageReady(): bool
    {
        return Schema::hasTable('api_keys')
            && Schema::hasTable('webhook_endpoints')
            && Schema::hasTable('webhook_deliveries');
    }

    /**
     * Every route under /api, with the guard and limit that genuinely apply.
     *
     * @return array<int, array<string, mixed>>
     */
    public static function endpoints(): array
    {
        $rows = [];

        /** @var \Illuminate\Routing\Router $router */
        $router = app('router');

        foreach (Route::getRoutes() as $route) {
            $uri = $route->uri();

            if (! str_starts_with($uri, 'api/')) {
                continue;
            }

            // gatherRouteMiddleware() expands groups and aliases; gatherMiddleware()
            // returns the bare group name. Reading the latter made every route in
            // the api group look unauthenticated and unthrottled, which is exactly
            // the sort of wrong-but-confident reporting this page exists to end.
            $middleware = array_values(array_filter($router->gatherRouteMiddleware($route), 'is_string'));

            $methods = array_values(array_diff($route->methods(), ['HEAD']));

            // Resolved once each. Calling these inline per column meant authLabel
            // ran twice and limitLabel twice for every route, and limitLabel reads
            // the settings row, so a 35 route table produced around seventy
            // lookups of the same cached value.
            $auth = static::authLabel($middleware);
            $limit = static::limitLabel($middleware);

            $rows[] = [
                'method' => implode(', ', $methods),
                'uri' => '/' . $uri,
                'auth' => $auth,
                'is_public' => $auth === 'Public',
                'limit' => $limit,
                'has_limit' => $limit !== 'None',
                'hardened' => in_array($uri, self::HARDENED, true),
                'group' => static::groupFor($uri),
            ];
        }

        usort($rows, function ($a, $b) {
            return [$a['group'], $a['uri']] <=> [$b['group'], $b['uri']];
        });

        return $rows;
    }

    /**
     * @param array<int, string> $middleware
     */
    private static function authLabel(array $middleware): string
    {
        foreach ($middleware as $m) {
            if (str_starts_with($m, 'auth:sanctum')) {
                return 'Sanctum token';
            }
        }

        foreach ($middleware as $m) {
            // Matches both the alias and the resolved class name.
            if (str_contains($m, 'AuthenticateApiKey') || str_starts_with($m, 'api.key')) {
                return 'API key';
            }
        }

        foreach ($middleware as $m) {
            if (str_contains($m, 'CheckPermission')) {
                return 'Session + permission';
            }
        }

        foreach ($middleware as $m) {
            if ($m === 'auth' || str_contains($m, 'Middleware\Authenticate')) {
                return 'Session';
            }
        }

        return 'Public';
    }

    /**
     * @param array<int, string> $middleware
     */
    /**
     * The configured rate limit, read once per request.
     *
     * GlobalConfig::getConfig() caches through the cache store, which on the
     * database driver is a query every time. Reading it per route turned the
     * endpoint table into dozens of identical lookups.
     */
    private static ?int $rateLimit = null;

    private static function rateLimit(): int
    {
        if (static::$rateLimit === null) {
            try {
                static::$rateLimit = (int) (\App\Models\GlobalConfig::getConfig()->api_rate_limit ?? 60);
            } catch (\Throwable $e) {
                static::$rateLimit = 60;
            }
        }

        return static::$rateLimit;
    }

    private static function limitLabel(array $middleware): string
    {
        $labels = [];

        foreach ($middleware as $m) {
            // "throttle:8,1" before resolution, the ThrottleRequests class after.
            if (! str_starts_with($m, 'throttle') && ! str_contains($m, 'ThrottleRequests')) {
                continue;
            }

            $argument = str_contains($m, ':') ? explode(':', $m, 2)[1] : '';

            // A named limiter, e.g. throttle:api. The number lives in the limiter
            // definition rather than in the route, so report the setting it reads.
            if ($argument === '' || ! str_contains($argument, ',')) {
                // A named limiter. "api" follows the setting on this tab;
                // "api-public" is a fixed tighter limit for open endpoints.
                $labels[] = $argument === 'api-public'
                    ? '30/min'
                    : static::rateLimit() . '/min';

                continue;
            }

            [$attempts, $minutes] = array_pad(explode(',', $argument, 2), 2, '1');

            $labels[] = ((int) $minutes === 1)
                ? ((int) $attempts) . '/min'
                : ((int) $attempts) . ' per ' . ((int) $minutes) . ' min';
        }

        if (! $labels) {
            return 'None';
        }

        // A route can carry both the group limit and its own tighter one; both
        // apply, and the smaller is what the caller actually meets.
        $labels = array_values(array_unique($labels));

        usort($labels, fn ($a, $b) => (int) $a <=> (int) $b);

        return implode(' + ', $labels);
    }

    private static function groupFor(string $uri): string
    {
        return match (true) {
            str_starts_with($uri, 'api/v1/') => 'Integration API',
            str_starts_with($uri, 'api/participant/tickets') => 'Support',
            str_starts_with($uri, 'api/participant') => 'Participant app',
            str_starts_with($uri, 'api/attendance') => 'Attendance',
            str_starts_with($uri, 'api/certificate') => 'Certificates',
            str_starts_with($uri, 'api/events') => 'Events',
            str_starts_with($uri, 'api/legal') => 'Legal content',
            default => 'Other',
        };
    }

    /**
     * @param array<int, array<string, mixed>> $endpoints
     * @return array<string, int>
     */
    private static function summary(array $endpoints): array
    {
        $exposed = array_filter(
            $endpoints,
            fn ($e) => $e['is_public'] && ! $e['has_limit']
        );

        return [
            'endpoints' => count($endpoints),
            'public' => count(array_filter($endpoints, fn ($e) => $e['is_public'])),
            'unlimited_public' => count($exposed),
            'active_keys' => static::storageReady()
                ? DB::table('api_keys')->whereNull('revoked_at')->count()
                : 0,
            'expiring_keys' => static::storageReady()
                ? DB::table('api_keys')
                    ->whereNull('revoked_at')
                    ->whereNotNull('expires_at')
                    ->where('expires_at', '<=', now()->addDays(14))
                    ->count()
                : 0,
            'active_webhooks' => static::storageReady()
                ? DB::table('webhook_endpoints')->where('is_active', true)->whereNull('disabled_at')->count()
                : 0,
            'event_types' => count(ApiEvents::all()),
            'failures_24h' => static::storageReady()
                ? DB::table('webhook_deliveries')
                    ->where('succeeded', false)
                    ->where('created_at', '>=', now()->subDay())
                    ->count()
                : 0,
            'disabled_webhooks' => static::storageReady()
                ? DB::table('webhook_endpoints')->whereNotNull('disabled_at')->count()
                : 0,
        ];
    }

    /**
     * Sanctum personal access tokens, which are what the participant app signs
     * in with. Surfaced because config/sanctum.php sets no expiry, so every
     * token ever issued is still valid.
     *
     * @return array<string, mixed>
     */
    public static function tokens(): array
    {
        if (! Schema::hasTable('personal_access_tokens')) {
            return ['total' => 0, 'recent' => 0, 'expiring' => 0, 'never_used' => 0, 'expiry_configured' => false];
        }

        return [
            'total' => DB::table('personal_access_tokens')->count(),
            'recent' => DB::table('personal_access_tokens')
                ->where('last_used_at', '>=', now()->subDays(30))
                ->count(),
            'never_used' => DB::table('personal_access_tokens')->whereNull('last_used_at')->count(),
            'expiring' => Schema::hasColumn('personal_access_tokens', 'expires_at')
                ? DB::table('personal_access_tokens')->whereNotNull('expires_at')->count()
                : 0,
            'expiry_configured' => config('sanctum.expiration') !== null,
        ];
    }

    /**
     * Rows for the API keys table, already formatted for display.
     *
     * These are handed to the browser as JSON so the table can be updated
     * without a page reload, which means the shape must contain no secret and no
     * hash. Selecting whole rows here would have put key_hash into the page
     * source for no reason.
     *
     * @return array<int, array<string, mixed>>
     */
    public static function keys(): array
    {
        if (! static::storageReady()) {
            return [];
        }

        return \App\Models\ApiKey::with('creator:id,name')
            ->orderByDesc('created_at')
            ->limit(50)
            ->get()
            ->map(fn (\App\Models\ApiKey $key) => static::keyRow($key))
            ->all();
    }

    /**
     * @return array<string, mixed>
     */
    public static function keyRow(\App\Models\ApiKey $key): array
    {
        return [
            'id' => $key->id,
            'name' => $key->name,
            'prefix' => $key->key_prefix,
            'abilities' => $key->abilities ?: [],
            'last_used' => $key->last_used_at ? $key->last_used_at->diffForHumans() : 'Never',
            'requests' => (int) $key->request_count,
            'expires' => $key->expires_at ? $key->expires_at->format('d M Y') : 'Never',
            'status' => match (true) {
                $key->isRevoked() => 'Revoked',
                $key->hasExpired() => 'Expired',
                default => 'Active',
            },
            'active' => $key->isActive(),
            'creator' => $key->creator?->name,
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public static function webhooks(): array
    {
        if (! static::storageReady()) {
            return [];
        }

        return \App\Models\WebhookEndpoint::orderBy('name')
            ->get()
            ->map(fn (\App\Models\WebhookEndpoint $e) => static::webhookRow($e))
            ->all();
    }

    /**
     * @return array<string, mixed>
     */
    public static function webhookRow(\App\Models\WebhookEndpoint $endpoint): array
    {
        return [
            'id' => $endpoint->id,
            'name' => $endpoint->name,
            'url' => $endpoint->url,
            // Never the secret. This object is serialised into the page.
            'events' => $endpoint->events ?: [],
            'event_count' => count($endpoint->events ?: []),
            'is_active' => (bool) $endpoint->is_active,
            'disabled' => $endpoint->isDisabled(),
            'failures' => (int) $endpoint->consecutive_failures,
            'last_status' => $endpoint->last_status_code,
            'last_delivery' => $endpoint->last_delivery_at
                ? $endpoint->last_delivery_at->diffForHumans()
                : null,
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public static function deliveries(int $limit = 15): array
    {
        if (! static::storageReady()) {
            return [];
        }

        return \App\Models\WebhookDelivery::with('endpoint:id,name')
            ->orderByDesc('created_at')
            ->limit($limit)
            ->get()
            ->map(fn (\App\Models\WebhookDelivery $d) => static::deliveryRow($d))
            ->all();
    }

    /**
     * @return array<string, mixed>
     */
    public static function deliveryRow(\App\Models\WebhookDelivery $delivery): array
    {
        return [
            'id' => $delivery->id,
            'time' => $delivery->created_at->format('d M H:i:s'),
            'event' => $delivery->event,
            'endpoint' => $delivery->endpoint?->name ?? '—',
            'attempt' => (int) $delivery->attempt,
            'status_code' => $delivery->status_code,
            'succeeded' => (bool) $delivery->succeeded,
            'duration_ms' => (int) $delivery->duration_ms,
            'error' => $delivery->error,
        ];
    }

    /**
     * The v1 endpoints an API key can call, and what each one needs.
     *
     * Shown on the tab because a generated credential with no documentation is
     * not usable: the first question after "Generate" is what to call with it.
     *
     * @return array<int, array<string, string>>
     */
    public static function integrationEndpoints(): array
    {
        return [
            ['method' => 'GET', 'path' => '/api/v1/whoami', 'ability' => '—',
                'description' => 'Confirms the key works and reports its abilities.'],
            ['method' => 'GET', 'path' => '/api/v1/events', 'ability' => 'events.read',
                'description' => 'Events, newest first. Filters: status, since, per_page.'],
            ['method' => 'GET', 'path' => '/api/v1/events/{id}', 'ability' => 'events.read',
                'description' => 'One event, with participant and certificate counts.'],
            ['method' => 'GET', 'path' => '/api/v1/participants', 'ability' => 'participants.read',
                'description' => 'Registrations. Filters: event_id, since, per_page.'],
            ['method' => 'GET', 'path' => '/api/v1/certificates', 'ability' => 'certificates.read',
                'description' => 'Issued certificates with their verification URLs.'],
        ];
    }

    /**
     * What CORS is doing right now, as opposed to what the setting says.
     *
     * @return array<string, mixed>
     */
    private static function corsEffective(): array
    {
        $origins = (array) config('cors.allowed_origins', []);

        return [
            'origins' => $origins,
            'wide_open' => in_array('*', $origins, true),
            'patterns' => (array) config('cors.allowed_origins_patterns', []),
            'paths' => (array) config('cors.paths', []),
        ];
    }

    /**
     * Split the stored comma or newline separated origin list into lines.
     *
     * @return array<int, string>
     */
    public static function originList(?string $stored): array
    {
        if (blank($stored)) {
            return [];
        }

        $parts = preg_split('/[\r\n,]+/', $stored) ?: [];

        return array_values(array_filter(array_map('trim', $parts), fn ($v) => $v !== ''));
    }
}
