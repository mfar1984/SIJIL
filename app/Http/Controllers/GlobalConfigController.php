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

        // The Recycle Bin is rendered as one of the Global Config tabs.
        $recycleBin = auth()->user()->can('recycle_bin.read')
            ? \App\Http\Controllers\RecycleBinController::payload()
            : null;

        // The API tab reports the live route table, the key and webhook records,
        // and the CORS configuration actually in force, rather than echoing back
        // settings that nothing reads.
        $apiPanel = \App\Support\ApiSurface::payload();

        // The Security tab reports what is actually enforced, for the same reason.
        $securityPanel = \App\Support\SecuritySurface::payload();

        // Which delivery channels can actually carry a notification.
        $notificationPanel = \App\Support\NotificationSurface::payload();

        return view('settings.global-config', compact(
            'config',
            'recycleBin',
            'apiPanel',
            'securityPanel',
            'notificationPanel'
        ));
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
            'error_reporting' => 'nullable|boolean',
            'activity_logging' => 'nullable|boolean',
            
            // Event Settings
            'event_expiry' => 'nullable|integer|min:1|max:720',
            // Was in:draft,published,archived. events.status is
            // enum('active','pending','completed'), so none of those three could
            // ever be stored on an event.
            'default_event_status' => 'nullable|string|in:pending,active,completed',
            'date_format' => 'nullable|string|in:' . implode(',', array_keys(\App\Support\SystemSettings::dateFormatOptions())),
            'registration_message' => 'nullable|string|max:1000',
            'allow_multiple_registrations' => 'nullable|boolean',
            'auto_confirmation_emails' => 'nullable|boolean',
            
            // Security Settings
            'min_password_length' => 'nullable|integer|min:6|max:32',
            'password_expiry' => 'nullable|integer|min:0|max:365',
            'require_special_chars' => 'nullable|boolean',
            'require_numbers' => 'nullable|boolean',
            'require_uppercase' => 'nullable|boolean',
            'max_login_attempts' => 'nullable|integer|min:1|max:20',
            // 0 means no limit; an hour is more than enough at the top end.
            'pwa_reset_cooldown_seconds' => 'nullable|integer|min:0|max:3600',
            'lockout_duration' => 'nullable|integer|min:1|max:1440',
            'session_timeout' => 'nullable|integer|min:5|max:1440',
            // 0 means the participant app token never expires, which is how the
            // app has always behaved.
            'api_token_lifetime_days' => 'nullable|integer|min:0|max:3650',
            // 0 keeps audit history forever.
            'log_retention_days' => 'nullable|integer|min:0|max:3650',
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

            // Read by CertificateController when a certificate is generated. These were
            // previously unvalidated and only survived because raw input was mass assigned.
            'sms_certificate_generated' => 'nullable|boolean',
            'telegram_certificate_generated' => 'nullable|boolean',
            
            // API Settings
            'api_enabled' => 'nullable|boolean',
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

            // File uploads. These land in publicly served storage, so the type and
            // size must be constrained and the extension must never come from the client.
            // SVG is deliberately excluded: it can carry scripts and would be served
            // from our own origin. ICO is excluded because the image rule cannot verify it.
            'org_logo' => 'nullable|image|mimes:jpeg,jpg,png,webp|max:2048',
            'favicon' => 'nullable|image|mimes:png,webp|max:512',
            'sidebar_logo' => 'nullable|image|mimes:jpeg,jpg,png,webp|max:2048',
            'login_background' => 'nullable|image|mimes:jpeg,jpg,png,webp|max:4096',
            'login_logo' => 'nullable|image|mimes:jpeg,jpg,png,webp|max:2048',

            // Set by the cross on each tile in the Appearance tab. These never
            // reach the model; they are read straight from the request below.
            'remove_org_logo' => 'nullable|boolean',
            'remove_favicon' => 'nullable|boolean',
            'remove_sidebar_logo' => 'nullable|boolean',
            'remove_login_background' => 'nullable|boolean',
            'remove_login_logo' => 'nullable|boolean',
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
            
            // Checkboxes that are absent from the payload mean "off".
            //
            // Only fields that actually have an input in the form belong here. The
            // integration toggles (google_calendar_enabled, microsoft_teams_enabled,
            // stripe_enabled, zoom_enabled) are deliberately excluded: no tab renders
            // them, so listing them here silently reset them to 0 on every save.
            //
            // enable_api_keys and enable_oauth are excluded for the same reason. The
            // rebuilt API tab manages keys as records rather than as one global
            // switch, and OAuth was never implemented at all - laravel/passport is
            // not installed, so the checkbox could only ever have been decorative.
            $booleanFields = [
                // debug_mode, error_reporting and auto_confirmation_emails are not
                // listed: the General tab no longer renders them. debug_mode and
                // error_reporting are governed by APP_DEBUG in the environment, and
                // auto_confirmation_emails duplicated email_event_registration on the
                // Notifications tab.
                'maintenance_mode', 'activity_logging',
                'allow_multiple_registrations',
                'require_special_chars', 'require_numbers', 'require_uppercase', 'force_ssl',
                'log_failed_logins', 'log_password_changes', 'log_permission_changes', 'enable_security_alerts',
                // allow_user_theme_choice is not listed: the Appearance tab no
                // longer renders it, because no dark styling exists for a user to
                // choose. Listing an unrendered checkbox here resets it on save.
                'show_welcome_message', 'show_help_icons',
                'email_new_user_registration', 'email_event_registration', 'email_event_reminder',
                'email_certificate_generated', 'email_password_reset', 'sms_event_registration',
                // admin_security_alerts is not listed: it duplicated
                // enable_security_alerts on the Security tab, and the Notifications
                // tab no longer renders it.
                'sms_event_reminder', 'admin_system_errors', 'admin_new_registrations',
                'telegram_event_registration', 'sms_certificate_generated', 'telegram_certificate_generated',
                'api_cors_enabled', 'enable_webhooks',
            ];

            // Use validated data only, so unvalidated input cannot be mass assigned.
            // File fields and the remove_* flags that accompany them are handled
            // separately below and must never reach the model.
            $data = collect($validator->validated())
                ->except([
                    'org_logo', 'favicon', 'sidebar_logo', 'login_background', 'login_logo',
                    'remove_org_logo', 'remove_favicon', 'remove_sidebar_logo',
                    'remove_login_background', 'remove_login_logo',
                ])
                ->all();
            
            // Set boolean fields to 0 if not present
            foreach ($booleanFields as $field) {
                if (!isset($data[$field])) {
                    $data[$field] = 0;
                }
            }
            
            // Handle image uploads. The extension is derived from the verified MIME type,
            // never from the client supplied filename.
            $uploads = [
                'org_logo' => ['dir' => 'logos', 'prefix' => 'logo'],
                'favicon' => ['dir' => 'favicons', 'prefix' => 'favicon'],
                'sidebar_logo' => ['dir' => 'logos', 'prefix' => 'sidebar_logo'],
                'login_background' => ['dir' => 'backgrounds', 'prefix' => 'login_bg'],
                'login_logo' => ['dir' => 'logos', 'prefix' => 'login_logo'],
            ];

            foreach ($uploads as $field => $meta) {
                // The cross on a tile in the Appearance tab clears the field back
                // to the built-in default. A new upload in the same save wins, so
                // this is only honoured when no file came with the request.
                if (! $request->hasFile($field) && $request->boolean('remove_' . $field)) {
                    $this->deleteStoredFile($config->{$field});
                    $data[$field] = null;
                    continue;
                }

                if (! $request->hasFile($field)) {
                    continue;
                }

                $file = $request->file($field);

                if (! $file->isValid()) {
                    continue;
                }

                $extension = $this->safeExtensionFor($file);

                if ($extension === null) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Unsupported image type for ' . $field . '.',
                    ], 422);
                }

                $filename = $meta['prefix'] . '_' . time() . '_' . Str::random(8) . '.' . $extension;
                $path = $file->storeAs($meta['dir'], $filename, 'public');

                /** @var \Illuminate\Filesystem\FilesystemAdapter $publicDisk */
                $publicDisk = Storage::disk('public');
                $data[$field] = $publicDisk->url($path);

                // Remove the file that was previously stored for this field
                $this->deleteStoredFile($config->{$field});
            }
            
            // The origin list is edited one per line but stored comma separated,
            // which is the shape the column already held. Normalising here means
            // either format can be pasted in without producing an origin with a
            // stray newline in it that would silently never match.
            if (array_key_exists('cors_domains', $data)) {
                $data['cors_domains'] = implode(
                    ', ',
                    \App\Support\ApiSurface::originList($data['cors_domains'])
                );
            }

            // Generate a webhook secret the first time webhooks are switched on.
            // The secret is never taken from the request: it is only ever generated
            // here or through regenerateWebhookSecret().
            if (! empty($data['enable_webhooks']) && ! $config->webhook_secret) {
                $data['webhook_secret'] = 'wh_sec_' . Str::random(32);
            }
            
            // A change to the security settings is itself a security event, and
            // the audit page only reads the 'security' log.
            $securityKeys = [
                'min_password_length', 'password_expiry', 'require_special_chars', 'require_numbers',
                'require_uppercase', 'max_login_attempts', 'lockout_duration', 'session_timeout',
                'force_ssl', 'log_failed_logins', 'log_password_changes', 'log_permission_changes',
                'enable_security_alerts', 'api_token_lifetime_days', 'log_retention_days',
            ];

            $securityChanges = [];

            foreach ($securityKeys as $key) {
                if (array_key_exists($key, $data) && (string) $config->{$key} !== (string) $data[$key]) {
                    $securityChanges[$key] = ['from' => $config->{$key}, 'to' => $data[$key]];
                }
            }

            // Update configuration
            $config->update($data);

            // Clear cache
            GlobalConfig::clearCache();

            // All three readers memoise the settings row for the request.
            \App\Support\Branding::flush();
            \App\Support\SystemSettings::flush();

            // The cache lifetime lives inside the row being cached, so it is kept
            // in its own entry and refreshed here.
            GlobalConfig::rememberCacheLifetime($config->fresh()->cache_lifetime);

            // The policy reader memoises the row for the request; without this a
            // save would report the old values back to the page.
            \App\Support\SecurityPolicy::flush();

            if ($securityChanges) {
                \App\Support\SecurityPolicy::audit(
                    'settings',
                    'Security settings changed: ' . implode(', ', array_keys($securityChanges)),
                    ['changes' => $securityChanges, 'ip_address' => $request->ip()],
                    auth()->user()
                );

                \App\Support\SecurityAlert::send('Security settings changed', [
                    'Changed by' => (string) auth()->user()?->email,
                    'Settings' => implode(', ', array_keys($securityChanges)),
                    'IP address' => (string) $request->ip(),
                ]);
            }
            
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
     * Map a verified MIME type to a safe file extension.
     *
     * Returns null when the type is not one we are willing to store.
     */
    private function safeExtensionFor(\Illuminate\Http\UploadedFile $file): ?string
    {
        return match ($file->getMimeType()) {
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/webp' => 'webp',
            default => null,
        };
    }

    /**
     * Delete a file previously stored on the public disk, given its public URL.
     *
     * Handles both the current "/storage/logos/x.png" layout and the older
     * "/storage/public/logos/x.png" layout produced by earlier versions.
     */
    private function deleteStoredFile(?string $url): void
    {
        if (empty($url) || ! str_contains($url, '/storage/')) {
            return;
        }

        $path = ltrim(Str::after($url, '/storage/'), '/');

        if ($path === '') {
            return;
        }

        $disk = Storage::disk('public');

        foreach ([$path, 'public/' . $path] as $candidate) {
            if ($disk->exists($candidate)) {
                $disk->delete($candidate);
            }
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
            $this->deleteStoredFile($config->org_logo);
            $this->deleteStoredFile($config->favicon);
            $this->deleteStoredFile($config->sidebar_logo);
            $this->deleteStoredFile($config->login_background);
            $this->deleteStoredFile($config->login_logo);
            
            // Delete the config and recreate
            $config->delete();

            // The cache still holds the deleted row, so it must be dropped before
            // the replacement is built and cached.
            GlobalConfig::clearCache();

            $newConfig = GlobalConfig::createDefault();

            GlobalConfig::clearCache();
            
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
