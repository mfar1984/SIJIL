<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PwaSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'scope',
        'user_id',
        'settings',
        'created_by',
        'updated_by'
    ];

    protected $casts = [
        'settings' => 'array',
    ];

    /**
     * Get the organizer who owns these settings
     */
    public function organizer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Get the user who created these settings
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the user who last updated these settings
     */
    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Scope to get global settings
     */
    public function scopeGlobal($query)
    {
        return $query->where('scope', 'global');
    }

    /**
     * Scope to get organizer settings
     */
    public function scopeOrganizer($query)
    {
        return $query->where('scope', 'organizer');
    }

    /**
     * The settings the application actually honours, with their fallbacks.
     *
     * Anything not listed here is not read by any code, so it must not be
     * offered as a setting.
     */
    public const DEFAULTS = [
        // Account creation
        'auto_create_accounts' => true,
        'force_password_change' => true,
        'checkbox_label' => 'Enable E-Certificate Online mobile access',
        'checkbox_default_state' => 'checked',

        // Generated passwords
        'password_length' => 8,
        'include_uppercase' => true,
        'include_lowercase' => true,
        'include_numbers' => true,
        'include_special_chars' => false,

        // Emails
        'send_welcome_email' => true,
        'include_app_link' => true,
        // Where participants sign in, which is the app and not the admin site.
        // This used to default to the admin host, so welcome emails sent people
        // to the staff login page, where a participant's credentials cannot work:
        // PWA accounts live in pwa_participants, not in users.
        'pwa_app_link' => 'https://user.e-certificate.com.my',
        'support_email' => 'support@e-certificate.com.my',
    ];

    /**
     * Resolve the effective settings for a user.
     *
     * An organizer's own row wins, then the global row, then the defaults, so a
     * missing key never breaks a caller.
     *
     * @return array<string, mixed>
     */
    public static function resolveFor(?User $user = null): array
    {
        $global = static::global()->first()?->settings ?? [];
        $own = [];

        if ($user && !$user->hasRole('Administrator')) {
            $own = static::where('scope', 'organizer')->where('user_id', $user->id)->first()?->settings ?? [];
        }

        return array_merge(static::DEFAULTS, is_array($global) ? $global : [], is_array($own) ? $own : []);
    }

    /**
     * Read one effective setting for a user.
     */
    public static function valueFor(string $key, ?User $user = null, mixed $default = null): mixed
    {
        $resolved = static::resolveFor($user);

        return $resolved[$key] ?? $default ?? (static::DEFAULTS[$key] ?? null);
    }

    /**
     * Get a specific setting value
     */
    public function getSetting($key, $default = null)
    {
        return $this->settings[$key] ?? $default;
    }

    /**
     * Set a specific setting value
     */
    public function setSetting($key, $value)
    {
        $settings = $this->settings;
        $settings[$key] = $value;
        $this->settings = $settings;
        return $this;
    }
}
