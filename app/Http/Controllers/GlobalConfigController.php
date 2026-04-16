<?php

namespace App\Http\Controllers;

use App\Models\GlobalConfig;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class GlobalConfigController extends Controller
{
    /**
     * Display the global configuration page
     */
    public function index()
    {
        $config = GlobalConfig::getConfig();
        return view('settings.global-config', compact('config'));
    }

    /**
     * Update global configuration
     */
    public function update(Request $request)
    {
        // GlobalConfig update called
        
        $config = GlobalConfig::getConfig();
        
        // Validate the request
        $validator = Validator::make($request->all(), [
            // Organization Settings
            'org_name' => 'required|string|max:255',
            'org_email' => 'required|email|max:255',
            'timezone' => 'required|string|max:100',
            'date_format' => 'required|string|max:20',
            
            // System Settings
            'maintenance_mode' => 'nullable|boolean',
            'debug_mode' => 'nullable|boolean',
            'cache_lifetime' => 'nullable|integer|min:0|max:1440',
            'pagination' => 'nullable|integer|min:5|max:1000',
            'enable_error_reporting' => 'nullable|boolean',
            'enable_activity_logging' => 'nullable|boolean',
            
            // Event Settings
            'event_expiry' => 'nullable|integer|min:1|max:720',
            'default_event_status' => 'nullable|string|in:draft,published,archived',
            'registration_message' => 'nullable|string|max:1000',
            'allow_multiple_registrations' => 'nullable|boolean',
            'auto_send_confirmation_emails' => 'nullable|boolean',
            
            // Security Settings
            'min_password_length' => 'nullable|integer|min:6|max:32',
            'password_expiry' => 'nullable|integer|min:0|max:365',
            'require_special_chars' => 'nullable|boolean',
            'require_numbers' => 'nullable|boolean',
            'require_uppercase' => 'nullable|boolean',
            'max_login_attempts' => 'nullable|integer|min:1|max:20',
            'lockout_duration' => 'nullable|integer|min:1|max:1440',
            'session_timeout' => 'nullable|integer|min:5|max:1440',
            'enable_2fa' => 'nullable|boolean',
            'force_ssl' => 'nullable|boolean',
            'log_failed_logins' => 'nullable|boolean',
            'log_password_changes' => 'nullable|boolean',
            'log_permission_changes' => 'nullable|boolean',
            'enable_security_alerts' => 'nullable|boolean',
            
            // Appearance Settings
            'primary_color' => 'required|string|regex:/^#[0-9A-F]{6}$/i',
            'secondary_color' => 'required|string|regex:/^#[0-9A-F]{6}$/i',
            'default_theme' => 'nullable|string|in:light,dark,system',
            'font_family' => 'nullable|string|in:inter,roboto,poppins,opensans,system',
            'allow_user_theme_choice' => 'nullable|boolean',
            'sidebar_default' => 'nullable|string|in:expanded,collapsed,remember',
            'table_density' => 'nullable|string|in:compact,default,comfortable',
            'show_welcome_message' => 'nullable|boolean',
            'show_help_icons' => 'nullable|boolean',
            'custom_css' => 'nullable|string|max:5000',
            
            // Notification Settings
            'email_new_user_registration' => 'nullable|boolean',
            'email_event_registration' => 'nullable|boolean',
            'email_event_reminder' => 'nullable|boolean',
            'email_certificate_generated' => 'nullable|boolean',
            'email_password_reset' => 'nullable|boolean',
            'sms_event_registration' => 'nullable|boolean',
            'sms_event_reminder' => 'nullable|boolean',
            'sms_reminder_hours' => 'nullable|integer|min:1|max:72',
            'admin_system_errors' => 'nullable|boolean',
            'admin_new_registrations' => 'nullable|boolean',
            'admin_security_alerts' => 'nullable|boolean',
            'telegram_event_registration' => 'nullable|boolean',
            'admin_notification_email' => 'required|email|max:255',
            
            // API Settings
            'api_status' => 'nullable|string|in:enabled,disabled',
            'api_rate_limit' => 'nullable|integer|min:10|max:1000',
            'enable_api_keys' => 'nullable|boolean',
            'enable_oauth' => 'nullable|boolean',
            'api_cors_enabled' => 'nullable|boolean',
            'cors_domains' => 'nullable|string|max:1000',
            
            // Integration Settings
            'google_calendar_enabled' => 'nullable|boolean',
            'microsoft_teams_enabled' => 'nullable|boolean',
            'stripe_enabled' => 'nullable|boolean',
            'zoom_enabled' => 'nullable|boolean',
            
            // Webhook Settings
            'enable_webhooks' => 'nullable|boolean',
            'webhook_events' => 'nullable|string|max:1000',
            
            // Telegram Settings
            'telegram_bot_token' => 'nullable|string|max:255',
            'telegram_bot_username' => 'nullable|string|max:100',
            'telegram_channel_id' => 'nullable|string|max:100',
            'telegram_owner_user_id' => 'nullable|string|max:100',
            'telegram_owner_username' => 'nullable|string|max:100',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            // GlobalConfig validation passed
            
            // Handle checkboxes - set to 0 if not present in request
            $booleanFields = [
                'maintenance_mode', 'debug_mode', 'enable_error_reporting', 'enable_activity_logging',
                'allow_multiple_registrations', 'auto_send_confirmation_emails',
                'require_special_chars', 'require_numbers', 'require_uppercase', 'enable_2fa', 'force_ssl',
                'log_failed_logins', 'log_password_changes', 'log_permission_changes', 'enable_security_alerts',
                'allow_user_theme_choice', 'show_welcome_message', 'show_help_icons',
                'email_new_user_registration', 'email_event_registration', 'email_event_reminder',
                'email_certificate_generated', 'email_password_reset', 'sms_event_registration',
                'sms_event_reminder', 'admin_system_errors', 'admin_new_registrations', 'admin_security_alerts',
                'telegram_event_registration',
                'enable_api_keys', 'enable_oauth', 'api_cors_enabled',
                'google_calendar_enabled', 'microsoft_teams_enabled', 'stripe_enabled', 'zoom_enabled',
                'enable_webhooks'
            ];
            
            // Handle file uploads
            $data = $request->except(['org_logo', 'favicon', 'login_background']);
            
            // Set boolean fields to 0 if not present
            foreach ($booleanFields as $field) {
                if (!isset($data[$field])) {
                    $data[$field] = 0;
                }
            }
            
            // Handle logo upload
            if ($request->hasFile('org_logo')) {
                $logo = $request->file('org_logo');
                if ($logo->isValid()) {
                    $logoName = 'logo_' . time() . '.' . $logo->getClientOriginalExtension();
                    $logoPath = $logo->storeAs('public/logos', $logoName);
                    $data['org_logo'] = Storage::url($logoPath);
                    
                    // Delete old logo if exists
                    if ($config->org_logo && Storage::exists(str_replace('/storage/', 'public/', $config->org_logo))) {
                        Storage::delete(str_replace('/storage/', 'public/', $config->org_logo));
                    }
                }
            }
            
            // Handle favicon upload
            if ($request->hasFile('favicon')) {
                $favicon = $request->file('favicon');
                if ($favicon->isValid()) {
                    $faviconName = 'favicon_' . time() . '.' . $favicon->getClientOriginalExtension();
                    $faviconPath = $favicon->storeAs('public/favicons', $faviconName);
                    $data['favicon'] = Storage::url($faviconPath);
                    
                    // Delete old favicon if exists
                    if ($config->favicon && Storage::exists(str_replace('/storage/', 'public/', $config->favicon))) {
                        Storage::delete(str_replace('/storage/', 'public/', $config->favicon));
                    }
                }
            }
            
            // Handle login background upload
            if ($request->hasFile('login_background')) {
                $background = $request->file('login_background');
                if ($background->isValid()) {
                    $bgName = 'login_bg_' . time() . '.' . $background->getClientOriginalExtension();
                    $bgPath = $background->storeAs('public/backgrounds', $bgName);
                    $data['login_background'] = Storage::url($bgPath);
                    
                    // Delete old background if exists
                    if ($config->login_background && Storage::exists(str_replace('/storage/', 'public/', $config->login_background))) {
                        Storage::delete(str_replace('/storage/', 'public/', $config->login_background));
                    }
                }
            }
            
            // Generate webhook secret if not exists
            if ($request->input('enable_webhooks') && !$config->webhook_secret) {
                $data['webhook_secret'] = 'wh_sec_' . Str::random(32);
            }
            
            // Update configuration
            $config->update($data);
            
            // Clear cache
            GlobalConfig::clearCache();
            
            return response()->json([
                'success' => true,
                'message' => 'Configuration updated successfully',
                'config' => $config->fresh()
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update configuration: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Regenerate webhook secret
     */
    public function regenerateWebhookSecret()
    {
        try {
            $config = GlobalConfig::getConfig();
            $config->update(['webhook_secret' => 'wh_sec_' . Str::random(32)]);
            GlobalConfig::clearCache();
            
            return response()->json([
                'success' => true,
                'message' => 'Webhook secret regenerated successfully',
                'webhook_secret' => $config->webhook_secret
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to regenerate webhook secret: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get configuration as JSON (for API)
     */
    public function getConfig()
    {
        $config = GlobalConfig::getConfig();
        return response()->json($config);
    }

    /**
     * Reset configuration to defaults
     */
    public function reset()
    {
        try {
            $config = GlobalConfig::getConfig();
            
            // Delete uploaded files
            if ($config->org_logo) {
                Storage::delete(str_replace('/storage/', 'public/', $config->org_logo));
            }
            if ($config->favicon) {
                Storage::delete(str_replace('/storage/', 'public/', $config->favicon));
            }
            if ($config->login_background) {
                Storage::delete(str_replace('/storage/', 'public/', $config->login_background));
            }
            
            // Delete the config and recreate
            $config->delete();
            $newConfig = GlobalConfig::createDefault();
            
            return response()->json([
                'success' => true,
                'message' => 'Configuration reset to defaults successfully',
                'config' => $newConfig
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to reset configuration: ' . $e->getMessage()
            ], 500);
        }
    }
}
