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
            'maintenance_mode' => 'boolean',
            'debug_mode' => 'boolean',
            'cache_lifetime' => 'integer|min:0|max:1440',
            'pagination' => 'integer|min:5|max:1000',
            'enable_error_reporting' => 'boolean',
            'enable_activity_logging' => 'boolean',
            
            // Event Settings
            'event_expiry' => 'integer|min:1|max:720',
            'default_event_status' => 'string|in:draft,published,archived',
            'registration_message' => 'nullable|string|max:1000',
            'allow_multiple_registrations' => 'boolean',
            'auto_send_confirmation_emails' => 'boolean',
            
            // Security Settings
            'min_password_length' => 'integer|min:6|max:32',
            'password_expiry' => 'integer|min:0|max:365',
            'require_special_chars' => 'boolean',
            'require_numbers' => 'boolean',
            'require_uppercase' => 'boolean',
            'max_login_attempts' => 'integer|min:1|max:20',
            'lockout_duration' => 'integer|min:1|max:1440',
            'session_timeout' => 'integer|min:5|max:1440',
            'enable_2fa' => 'boolean',
            'force_ssl' => 'boolean',
            'log_failed_logins' => 'boolean',
            'log_password_changes' => 'boolean',
            'log_permission_changes' => 'boolean',
            'enable_security_alerts' => 'boolean',
            
            // Appearance Settings
            'primary_color' => 'required|string|regex:/^#[0-9A-F]{6}$/i',
            'secondary_color' => 'required|string|regex:/^#[0-9A-F]{6}$/i',
            'default_theme' => 'string|in:light,dark,system',
            'font_family' => 'string|in:inter,roboto,poppins,opensans,system',
            'allow_user_theme_choice' => 'boolean',
            'sidebar_default' => 'string|in:expanded,collapsed,remember',
            'table_density' => 'string|in:compact,default,comfortable',
            'show_welcome_message' => 'boolean',
            'show_help_icons' => 'boolean',
            'custom_css' => 'nullable|string|max:5000',
            
            // Notification Settings
            'email_new_user_registration' => 'boolean',
            'email_event_registration' => 'boolean',
            'email_event_reminder' => 'boolean',
            'email_certificate_generated' => 'boolean',
            'email_password_reset' => 'boolean',
            'sms_event_registration' => 'boolean',
            'sms_event_reminder' => 'boolean',
            'sms_reminder_hours' => 'integer|min:1|max:72',
            'admin_system_errors' => 'boolean',
            'admin_new_registrations' => 'boolean',
            'admin_security_alerts' => 'boolean',
            'admin_notification_email' => 'required|email|max:255',
            
            // API Settings
            'api_status' => 'string|in:enabled,disabled',
            'api_rate_limit' => 'integer|min:10|max:1000',
            'enable_api_keys' => 'boolean',
            'enable_oauth' => 'boolean',
            'api_cors_enabled' => 'boolean',
            'cors_domains' => 'nullable|string|max:1000',
            
            // Integration Settings
            'google_calendar_enabled' => 'boolean',
            'microsoft_teams_enabled' => 'boolean',
            'stripe_enabled' => 'boolean',
            'zoom_enabled' => 'boolean',
            
            // Webhook Settings
            'enable_webhooks' => 'boolean',
            'webhook_events' => 'nullable|string|max:1000',
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
            
            // Handle file uploads
            $data = $request->except(['org_logo', 'favicon', 'login_background']);
            
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
