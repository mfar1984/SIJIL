<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class GlobalConfig extends Model
{
    use HasFactory;

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
        'lockout_duration', 'session_timeout', 'enable_2fa', 'force_ssl',
        'log_failed_logins', 'log_password_changes', 'log_permission_changes',
        'enable_security_alerts',
        
        // Appearance Settings
        'primary_color', 'secondary_color', 'default_theme', 'font_family',
        'allow_user_theme_choice', 'favicon', 'login_background', 'custom_css',
        'sidebar_default', 'table_density', 'show_welcome_message', 'show_help_icons',
        
        // Notification Settings
        'email_new_user_registration', 'email_event_registration', 'email_event_reminder',
        'email_certificate_generated', 'email_password_reset', 'sms_event_registration',
        'sms_event_reminder', 'sms_reminder_hours', 'admin_system_errors',
        'admin_new_registrations', 'admin_security_alerts', 'admin_notification_email',
        
        // API Settings
        'api_enabled', 'api_rate_limit', 'enable_api_keys', 'enable_oauth',
        'api_cors_enabled', 'cors_domains',
        
        // Integration Settings
        'google_calendar_enabled', 'microsoft_teams_enabled', 'stripe_enabled', 'zoom_enabled',
        
        // Webhook Settings
        'enable_webhooks', 'webhook_secret', 'webhook_events',
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
        'admin_system_errors' => 'boolean',
        'admin_new_registrations' => 'boolean',
        'admin_security_alerts' => 'boolean',
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
    public static function getConfig()
    {
        return Cache::remember('global_config', 3600, function () {
            return self::first() ?? self::createDefault();
        });
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
            'webhook_events' => 'event.created, event.updated, registration.completed, certificate.generated, attendance.recorded',
            'cors_domains' => 'https://example.com, https://*.sijilevents.com',
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
