<?php

namespace App\Support;

use App\Models\GlobalConfig;
use Illuminate\Validation\Rules\Password as PasswordRule;

/**
 * The single reader of the Security tab settings.
 *
 * Every setting on that tab used to be stored and never read. Password rules
 * were hard-coded "min:8" in one place and Password::defaults() in another,
 * login lockout was a literal 5 attempts with the framework's default one minute
 * decay, and the session timeout, SSL and logging switches did nothing at all.
 *
 * Centralising the reads means a change on the tab reaches every entry point,
 * and that there is one place to look when asking what a setting actually does.
 */
class SecurityPolicy
{
    /**
     * Bounds applied to stored values. A settings row edited directly in the
     * database must not be able to lock everyone out or remove the limits.
     */
    private const MIN_PASSWORD_FLOOR = 6;
    private const MIN_PASSWORD_CEILING = 64;

    private static ?GlobalConfig $config = null;

    private static function config(): ?GlobalConfig
    {
        if (static::$config === null) {
            try {
                static::$config = GlobalConfig::getConfig();
            } catch (\Throwable $e) {
                // Console commands and early boot can run before the table exists.
                return null;
            }
        }

        return static::$config;
    }

    /**
     * Clear the memoised row. Called after the settings are saved.
     */
    public static function flush(): void
    {
        static::$config = null;
    }

    private static function value(string $key, mixed $default): mixed
    {
        $config = static::config();

        if (! $config) {
            return $default;
        }

        $value = $config->{$key} ?? null;

        return $value === null ? $default : $value;
    }

    // -----------------------------------------------------------------
    // Password policy
    // -----------------------------------------------------------------

    public static function minPasswordLength(): int
    {
        return max(
            self::MIN_PASSWORD_FLOOR,
            min(self::MIN_PASSWORD_CEILING, (int) static::value('min_password_length', 8))
        );
    }

    public static function requiresUppercase(): bool
    {
        return (bool) static::value('require_uppercase', false);
    }

    public static function requiresNumbers(): bool
    {
        return (bool) static::value('require_numbers', false);
    }

    public static function requiresSymbols(): bool
    {
        return (bool) static::value('require_special_chars', false);
    }

    /**
     * The password rule every entry point should use.
     */
    public static function passwordRule(): PasswordRule
    {
        $rule = PasswordRule::min(static::minPasswordLength());

        if (static::requiresUppercase()) {
            // mixedCase is the closest the framework offers; requiring an
            // uppercase letter in practice means requiring both cases.
            $rule->mixedCase();
        }

        if (static::requiresNumbers()) {
            $rule->numbers();
        }

        if (static::requiresSymbols()) {
            $rule->symbols();
        }

        return $rule;
    }

    /**
     * Plain sentence describing the policy, for display next to a password field.
     */
    public static function describe(): string
    {
        $parts = ['at least ' . static::minPasswordLength() . ' characters'];

        if (static::requiresUppercase()) {
            $parts[] = 'upper and lower case letters';
        }

        if (static::requiresNumbers()) {
            $parts[] = 'a number';
        }

        if (static::requiresSymbols()) {
            $parts[] = 'a symbol';
        }

        if (count($parts) === 1) {
            return 'Must be ' . $parts[0] . '.';
        }

        $last = array_pop($parts);

        return 'Must contain ' . implode(', ', $parts) . ' and ' . $last . '.';
    }

    /**
     * Days before a password must be changed. Zero means never.
     */
    public static function passwordExpiryDays(): int
    {
        return max(0, (int) static::value('password_expiry', 0));
    }

    public static function passwordExpiryEnabled(): bool
    {
        return static::passwordExpiryDays() > 0;
    }

    // -----------------------------------------------------------------
    // Login security
    // -----------------------------------------------------------------

    public static function maxLoginAttempts(): int
    {
        return max(1, min(20, (int) static::value('max_login_attempts', 5)));
    }

    /**
     * Lockout length in seconds. Stored in minutes on the tab.
     */
    public static function lockoutSeconds(): int
    {
        return max(60, min(86400, (int) static::value('lockout_duration', 15) * 60));
    }

    public static function sessionTimeoutMinutes(): int
    {
        return max(5, min(1440, (int) static::value('session_timeout', 120)));
    }

    public static function forceSsl(): bool
    {
        return (bool) static::value('force_ssl', false);
    }

    /**
     * Days a participant app token stays valid. Zero means never expires, which
     * is the behaviour the app shipped with.
     */
    public static function apiTokenLifetimeDays(): int
    {
        return max(0, min(3650, (int) static::value('api_token_lifetime_days', 0)));
    }

    // -----------------------------------------------------------------
    // Auditing
    // -----------------------------------------------------------------

    public static function logsFailedLogins(): bool
    {
        return (bool) static::value('log_failed_logins', true);
    }

    public static function logsPasswordChanges(): bool
    {
        return (bool) static::value('log_password_changes', true);
    }

    public static function logsPermissionChanges(): bool
    {
        return (bool) static::value('log_permission_changes', true);
    }

    public static function sendsSecurityAlerts(): bool
    {
        return (bool) static::value('enable_security_alerts', false);
    }

    /**
     * Days of audit history to keep. Zero means keep everything.
     */
    public static function logRetentionDays(): int
    {
        return max(0, min(3650, (int) static::value('log_retention_days', 0)));
    }

    /**
     * Write a security entry only when the relevant switch is on.
     *
     * The four "log ..." checkboxes were decorative: the audit page read the
     * activity log unconditionally, so unticking one changed nothing.
     *
     * @param array<string, mixed> $properties
     */
    public static function audit(string $category, string $description, array $properties = [], mixed $causedBy = null, mixed $performedOn = null): void
    {
        $allowed = match ($category) {
            'failed_login' => static::logsFailedLogins(),
            'password' => static::logsPasswordChanges(),
            'permission' => static::logsPermissionChanges(),
            default => true,
        };

        if (! $allowed) {
            return;
        }

        try {
            $activity = activity('security')->withProperties($properties + ['category' => $category]);

            if ($causedBy) {
                $activity->causedBy($causedBy);
            }

            if ($performedOn) {
                $activity->performedOn($performedOn);
            }

            $activity->log($description);
        } catch (\Throwable $e) {
            // Auditing must never break the action being audited.
        }
    }
}
