<?php

namespace App\Support;

use App\Models\GlobalConfig;
use App\Models\PwaParticipant;
use App\Models\PwaSetting;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Spatie\Activitylog\Models\Activity;

/**
 * Reports the security posture as it actually is.
 *
 * Written for the same reason as ApiSurface: the Security tab used to echo back
 * stored values with no indication of whether anything honoured them. Reading
 * the live configuration and the database means the page cannot claim a
 * protection that is not in force.
 */
class SecuritySurface
{
    /**
     * @return array<string, mixed>
     */
    public static function payload(): array
    {
        $expiryDays = SecurityPolicy::passwordExpiryDays();

        return [
            'min_password_length' => SecurityPolicy::minPasswordLength(),
            'complexity_count' => collect([
                SecurityPolicy::requiresUppercase(),
                SecurityPolicy::requiresNumbers(),
                SecurityPolicy::requiresSymbols(),
            ])->filter()->count(),
            'max_login_attempts' => SecurityPolicy::maxLoginAttempts(),
            'lockout_minutes' => (int) round(SecurityPolicy::lockoutSeconds() / 60),
            'expiry_enabled' => SecurityPolicy::passwordExpiryEnabled(),
            'expired_passwords' => static::expiredPasswordCount($expiryDays),
            // Shown before the setting is switched on, because it applies from
            // when each password was last set rather than from today. The stored
            // value sat at 90 for a year while nothing enforced it, so turning it
            // on without saying this would lock most accounts out of everything
            // but their own profile.
            'would_expire_at_90' => static::expiredPasswordCount(90),
            'user_count' => User::count(),
            'locked_accounts' => static::lockedParticipantCount(),
            'failed_logins_24h' => static::failedLogins24h(),
            'audit_rows' => static::auditRows(),
            'participant_tokens' => static::tokenCount(),
            'stale_tokens' => static::staleTokenCount(),
            'generated_password_length' => static::generatedPasswordLength(),
            'current_scheme' => request()?->isSecure() ? 'HTTPS' : 'HTTP',
            'is_https' => (bool) request()?->isSecure(),
            'alert_recipient' => static::alertRecipient(),
            'hardening' => static::hardening(),
        ];
    }

    private static function expiredPasswordCount(int $days): int
    {
        if ($days <= 0 || ! Schema::hasColumn('users', 'password_changed_at')) {
            return 0;
        }

        $cutoff = now()->subDays($days);

        return User::where(function ($query) use ($cutoff) {
            $query->where('password_changed_at', '<', $cutoff)
                ->orWhere(function ($inner) use ($cutoff) {
                    $inner->whereNull('password_changed_at')->where('created_at', '<', $cutoff);
                });
        })->count();
    }

    private static function lockedParticipantCount(): int
    {
        if (! Schema::hasColumn('pwa_participants', 'locked_until')) {
            return 0;
        }

        return PwaParticipant::whereNotNull('locked_until')
            ->where('locked_until', '>', now())
            ->count();
    }

    private static function failedLogins24h(): int
    {
        try {
            return Activity::where('log_name', 'security')
                ->where('created_at', '>=', now()->subDay())
                ->where(function ($query) {
                    $query->where('description', 'like', '%login%')
                        ->orWhere('description', 'like', '%locked%');
                })
                ->count();
        } catch (\Throwable $e) {
            return 0;
        }
    }

    private static function auditRows(): int
    {
        try {
            return Activity::count();
        } catch (\Throwable $e) {
            return 0;
        }
    }

    private static function tokenCount(): int
    {
        return Schema::hasTable('personal_access_tokens')
            ? DB::table('personal_access_tokens')->count()
            : 0;
    }

    private static function staleTokenCount(): int
    {
        if (! Schema::hasTable('personal_access_tokens')) {
            return 0;
        }

        return DB::table('personal_access_tokens')
            ->where(function ($query) {
                $query->whereNull('last_used_at')
                    ->orWhere('last_used_at', '<', now()->subDays(90));
            })
            ->count();
    }

    /**
     * Length of the passwords generated for participant app accounts, which is
     * configured separately from the policy a person has to meet.
     */
    private static function generatedPasswordLength(): int
    {
        try {
            $value = PwaSetting::valueFor('password_length');

            return $value ? (int) $value : 10;
        } catch (\Throwable $e) {
            return 10;
        }
    }

    private static function alertRecipient(): ?string
    {
        try {
            $config = GlobalConfig::getConfig();
        } catch (\Throwable $e) {
            return null;
        }

        foreach ([$config->admin_notification_email ?? null, $config->org_email ?? null] as $candidate) {
            if (filled($candidate) && filter_var($candidate, FILTER_VALIDATE_EMAIL)) {
                return $candidate;
            }
        }

        return null;
    }

    /**
     * Protections that are not settings, so their state is visible rather than
     * assumed.
     *
     * @return array<int, array{name: string, state: string, note: string}>
     */
    private static function hardening(): array
    {
        $items = [];

        $items[] = [
            'name' => 'Password hashing',
            'state' => config('hashing.driver') === 'argon2id' ? 'On' : 'Review',
            'note' => 'Using ' . config('hashing.driver') . '. Argon2id is the strongest option available here.',
        ];

        $items[] = [
            'name' => 'Session cookie flags',
            'state' => config('session.http_only') ? 'On' : 'Off',
            'note' => 'HttpOnly ' . (config('session.http_only') ? 'on' : 'off')
                . ', SameSite ' . (config('session.same_site') ?: 'unset')
                . ', Secure ' . (config('session.secure') ? 'on' : 'off')
                . (config('session.secure') ? '' : ' (follows Force HTTPS above)'),
        ];

        $items[] = [
            'name' => 'Session regenerated on sign-in',
            'state' => 'On',
            'note' => 'Prevents a pre-set session id being reused after authentication.',
        ];

        $items[] = [
            'name' => 'Account status enforced mid-session',
            'state' => 'On',
            'note' => 'Banning or deactivating an account now ends its active sessions on the next request. '
                . 'Previously status was only checked while signing in.',
        ];

        $items[] = [
            'name' => 'Response security headers',
            'state' => 'On',
            'note' => 'X-Frame-Options, X-Content-Type-Options, Referrer-Policy and Permissions-Policy. '
                . 'No Content-Security-Policy yet; the views rely on inline scripts.',
        ];

        $items[] = [
            'name' => 'Backend password reset',
            'state' => 'Off',
            'note' => 'The forgot-password routes are commented out, so an administrator who loses their password '
                . 'has no self-service recovery and needs another administrator to reset it.',
        ];

        $items[] = [
            'name' => 'Email verification',
            'state' => 'Off',
            'note' => 'The User model does not implement MustVerifyEmail, so the "verified" middleware on the '
                . 'routes passes everyone through. Accounts are auto-verified when created.',
        ];

        $items[] = [
            'name' => 'Two-factor authentication',
            'state' => 'Off',
            'note' => 'Not implemented anywhere in the system.',
        ];

        $items[] = [
            'name' => 'Debug mode',
            'state' => config('app.debug') ? 'Review' : 'On',
            'note' => config('app.debug')
                ? 'APP_DEBUG is on. Stack traces and configuration values are shown on errors; this must be off in production.'
                : 'APP_DEBUG is off, so errors do not expose internals.',
        ];

        $items[] = [
            'name' => 'Scheduled tasks',
            'state' => 'Review',
            'note' => 'Three tasks are registered including the audit purge. They only run if the host calls '
                . '"php artisan schedule:run" every minute.',
        ];

        return $items;
    }
}
