<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

class GlobalConfig extends Model
{
    use HasFactory, LogsActivity;

    /**
     * Configuration changes are security relevant, so every changed attribute is
     * recorded. Secrets are excluded from the log payload.
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll()
            ->dontLogIfAttributesChangedOnly(['updated_at'])
            ->logExcept(['webhook_secret', 'telegram_bot_token'])
            ->logOnlyDirty()
            ->useLogName('security')
            ->setDescriptionForEvent(fn (string $eventName) => "Global configuration {$eventName}");
    }

    protected $fillable = [
        // Organization Settings
        'org_name', 'org_email', 'timezone', 'date_format', 'org_logo',
        
        // System Settings
        'maintenance_mode', 'debug_mode', 'cache_lifetime', 'pagination',
        'error_reporting', 'activity_logging',
        
        // Event Settings
        'event_expiry', 'default_event_status', 'registration_message',
        'allow_multiple_registrations', 'auto_confirmation_emails',
        
        // Security Settings
        'min_password_length', 'password_expiry', 'require_special_chars',
        'require_numbers', 'require_uppercase', 'max_login_attempts',
        'pwa_reset_cooldown_seconds',
        'lockout_duration', 'session_timeout', 'enable_2fa', 'force_ssl',
        'log_failed_logins', 'log_password_changes', 'log_permission_changes',
        'enable_security_alerts',
        // Participant app token lifetime and audit retention, both previously
        // unbounded: tokens never expired and the audit log never shrank.
        'api_token_lifetime_days', 'log_retention_days',
        
        // Appearance Settings
        // The five brand images live together under Branding Settings, so
        // sidebar_logo and login_logo are listed here beside the other two rather
        // than next to org_logo above.
        'primary_color', 'secondary_color', 'default_theme', 'font_family',
        'allow_user_theme_choice', 'favicon', 'sidebar_logo',
        'login_background', 'login_logo', 'custom_css',
        'sidebar_default', 'table_density', 'show_welcome_message', 'show_help_icons',
        
        // Notification Settings
        'email_new_user_registration', 'email_event_registration', 'email_event_reminder',
        'email_certificate_generated', 'email_password_reset', 'sms_event_registration',
        'sms_event_reminder', 'sms_reminder_hours', 'sms_certificate_generated', 'admin_system_errors',
        'admin_new_registrations', 'admin_security_alerts', 'admin_notification_email',
        'telegram_event_registration', 'telegram_certificate_generated',
        
        // API Settings
        'api_enabled', 'api_rate_limit', 'enable_api_keys', 'enable_oauth',
        'api_cors_enabled', 'cors_domains',
        
        // Integration Settings
        'google_calendar_enabled', 'microsoft_teams_enabled', 'stripe_enabled', 'zoom_enabled',
        
        // Webhook Settings
        'enable_webhooks', 'webhook_secret', 'webhook_events',
        
        // Telegram Settings
        'telegram_bot_token', 'telegram_bot_username', 'telegram_channel_id',
        'telegram_owner_user_id', 'telegram_owner_username',
    ];

    protected $casts = [
        'maintenance_mode' => 'boolean',
        'debug_mode' => 'boolean',
        'error_reporting' => 'boolean',
        'activity_logging' => 'boolean',
        'allow_multiple_registrations' => 'boolean',
        'auto_confirmation_emails' => 'boolean',
        'require_special_chars' => 'boolean',
        'require_numbers' => 'boolean',
        'require_uppercase' => 'boolean',
        'enable_2fa' => 'boolean',
        'force_ssl' => 'boolean',
        'log_failed_logins' => 'boolean',
        'log_password_changes' => 'boolean',
        'log_permission_changes' => 'boolean',
        'enable_security_alerts' => 'boolean',
        'allow_user_theme_choice' => 'boolean',
        'show_welcome_message' => 'boolean',
        'show_help_icons' => 'boolean',
        'email_new_user_registration' => 'boolean',
        'email_event_registration' => 'boolean',
        'email_event_reminder' => 'boolean',
        'email_certificate_generated' => 'boolean',
        'email_password_reset' => 'boolean',
        'sms_event_registration' => 'boolean',
        'sms_event_reminder' => 'boolean',
        'sms_certificate_generated' => 'boolean',
        'admin_system_errors' => 'boolean',
        'admin_new_registrations' => 'boolean',
        'admin_security_alerts' => 'boolean',
        'telegram_event_registration' => 'boolean',
        'telegram_certificate_generated' => 'boolean',
        'api_enabled' => 'boolean',
        'enable_api_keys' => 'boolean',
        'enable_oauth' => 'boolean',
        'api_cors_enabled' => 'boolean',
        'google_calendar_enabled' => 'boolean',
        'microsoft_teams_enabled' => 'boolean',
        'stripe_enabled' => 'boolean',
        'zoom_enabled' => 'boolean',
        'enable_webhooks' => 'boolean',
    ];

    /**
     * Get the first (and only) global config instance
     */
    /**
     * Cache key holding the configured lifetime, in seconds.
     *
     * Kept separate because the lifetime lives inside the row being cached: using
     * it directly would require the row in order to know how long to cache the
     * row. This entry is refreshed whenever the settings are saved.
     */
    private const TTL_KEY = 'global_config_ttl';

    public static function getConfig()
    {
        // Cache Lifetime on the General tab was stored and never read; the value
        // here was a hardcoded hour regardless of what the setting said.
        $ttl = (int) Cache::get(self::TTL_KEY, 3600);
        $ttl = max(60, min(86400, $ttl));

        return Cache::remember('global_config', $ttl, function () {
            return self::first() ?? self::createDefault();
        });
    }

    /**
     * Record the configured cache lifetime so the next read honours it.
     */
    public static function rememberCacheLifetime(?int $minutes): void
    {
        $minutes = max(1, min(1440, (int) ($minutes ?: 60)));

        Cache::put(self::TTL_KEY, $minutes * 60, now()->addDay());
    }

    /**
     * Create default configuration
     */
    public static function createDefault()
    {
        return self::create([
            'org_name' => 'Sijil Event Management',
            'org_email' => 'contact@sijilevents.com',
            'timezone' => 'Asia/Kuala_Lumpur',
            'date_format' => 'd/m/Y',
            'primary_color' => '#004aad',
            'secondary_color' => '#38bdf8',
            'default_theme' => 'light',
            'font_family' => 'inter',
            'admin_notification_email' => 'admin@sijilevents.com',
            'registration_message' => 'Thank you for registering for this event. Please check your email for confirmation details.',

            // The origin this application is served from. The previous default was
            // 'https://example.com, https://*.sijilevents.com', neither of which
            // exists, so a reset left the allow-list describing nothing real while
            // looking configured. Webhook subscriptions are now records rather than
            // a comma separated string, so no event list is seeded here.
            'cors_domains' => rtrim(config('app.url'), '/'),
        ]);
    }

    /**
     * Clear config cache
     */
    public static function clearCache()
    {
        Cache::forget('global_config');
    }

    /**
     * Get a specific config value
     */
    public static function get($key, $default = null)
    {
        $config = self::getConfig();
        return $config->$key ?? $default;
    }

    /**
     * Set a specific config value
     */
    public static function set($key, $value)
    {
        $config = self::getConfig();
        $config->update([$key => $value]);
        self::clearCache();
        return $config;
    }
}
