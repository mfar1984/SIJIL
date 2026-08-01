<?php

namespace App\Http\Controllers\Pwa;

use App\Http\Controllers\Controller;
use App\Models\PwaSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PwaSettingsController extends Controller
{
    /**
     * Display PWA settings
     */
    public function index()
    {
        $user = Auth::user();
        
        // Multi-tenant settings based on user role
        if ($user->hasRole('Administrator')) {
            // Administrator sees global PWA settings
            $settings = PwaSetting::global()->first();
            
            // If no global settings exist, create default
            if (!$settings) {
                $settings = PwaSetting::create([
                    'scope' => 'global',
                    'settings' => $this->getDefaultSettings(),
                    'created_by' => $user->id
                ]);
            }
        } else {
            // Organizer sees their own PWA settings
            $settings = PwaSetting::where('scope', 'organizer')
                ->where('user_id', $user->id)
                ->first();
            
            // If no organizer settings exist, create from global defaults
            if (!$settings) {
                $globalSettings = PwaSetting::global()->first();
                $defaultSettings = $globalSettings ? $globalSettings->settings : $this->getDefaultSettings();
                
                $settings = PwaSetting::create([
                    'scope' => 'organizer',
                    'user_id' => $user->id,
                    'settings' => $defaultSettings,
                    'created_by' => $user->id
                ]);
            }
        }

        return view('ecertificate.settings', compact('settings'));
    }

    /**
     * Update PWA settings
     */
    /**
     * Settings stored as true/false. An unchecked box is simply absent from the
     * request, so each one has to be resolved explicitly, otherwise turning a
     * toggle off used to drop the key instead of storing false.
     */
    private const BOOLEAN_KEYS = [
        'auto_create_accounts',
        'force_password_change',
        'include_uppercase',
        'include_lowercase',
        'include_numbers',
        'include_special_chars',
        'send_welcome_email',
        'include_app_link',
    ];

    public function update(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'auto_create_accounts' => 'nullable|boolean',
            'force_password_change' => 'nullable|boolean',
            'checkbox_label' => 'required|string|max:255',
            'checkbox_default_state' => 'required|in:checked,unchecked',
            'password_length' => 'required|integer|min:6|max:16',
            'include_uppercase' => 'nullable|boolean',
            'include_lowercase' => 'nullable|boolean',
            'include_numbers' => 'nullable|boolean',
            'include_special_chars' => 'nullable|boolean',
            'send_welcome_email' => 'nullable|boolean',
            'include_app_link' => 'nullable|boolean',
            'pwa_app_link' => 'required|url',
            'support_email' => 'required|email',
        ]);

        // Multi-tenant settings update based on user role
        if ($user->hasRole('Administrator')) {
            // Administrator updates global settings
            $settings = PwaSetting::global()->first();
            
            if (!$settings) {
                $settings = PwaSetting::create([
                    'scope' => 'global',
                    'created_by' => $user->id
                ]);
            }
        } else {
            // Organizer updates their own settings
            $settings = PwaSetting::where('scope', 'organizer')
                ->where('user_id', $user->id)
                ->first();
            
            if (!$settings) {
                $settings = PwaSetting::create([
                    'scope' => 'organizer',
                    'user_id' => $user->id,
                    'created_by' => $user->id
                ]);
            }
        }

        // Only validated keys are stored. $request->all() used to be saved
        // wholesale, which wrote the CSRF token into the settings payload.
        foreach (self::BOOLEAN_KEYS as $key) {
            $validated[$key] = $request->boolean($key);
        }

        $validated['password_length'] = (int) $validated['password_length'];

        // Merge over what is already stored so a setting handled elsewhere is
        // never wiped by this form.
        $existing = is_array($settings->settings) ? $settings->settings : [];

        $settings->update([
            'settings' => array_merge($existing, $validated),
            'updated_by' => $user->id,
        ]);

        return redirect()->route('pwa.settings')->with('success', 'PWA settings updated.');
    }

    /**
     * Get default PWA settings
     */
    private function getDefaultSettings()
    {
        // Single source of truth, shared with everything that reads a setting.
        return PwaSetting::DEFAULTS;
    }

    /**
     * Reset settings to defaults
     */
    public function resetToDefaults()
    {
        $user = Auth::user();
        
        if ($user->hasRole('Administrator')) {
            // Reset global settings
            $settings = PwaSetting::global()->first();
            
            if ($settings) {
                $settings->update([
                    'settings' => $this->getDefaultSettings(),
                    'updated_by' => $user->id
                ]);
            }
        } else {
            // Reset organizer settings to global defaults
            $globalSettings = PwaSetting::global()->first();
            $defaultSettings = $globalSettings ? $globalSettings->settings : $this->getDefaultSettings();
            
            $settings = PwaSetting::where('scope', 'organizer')
                ->where('user_id', $user->id)
                ->first();
            
            if ($settings) {
                $settings->update([
                    'settings' => $defaultSettings,
                    'updated_by' => $user->id
                ]);
            }
        }

        return redirect()->route('pwa.settings')->with('success', 'Settings reset to defaults successfully.');
    }

    /**
     * Get PWA settings for API (used by PWA app)
     */
    public function getApiSettings(Request $request)
    {
        $organizerId = $request->get('user_id');
        
        if ($organizerId) {
            // Get organizer-specific settings
            $settings = PwaSetting::where('scope', 'organizer')
                ->where('user_id', $organizerId)
                ->first();
        } else {
            // Get global settings
            $settings = PwaSetting::global()->first();
        }

        if (!$settings) {
            $settings = PwaSetting::create([
                'scope' => $organizerId ? 'organizer' : 'global',
                'user_id' => $organizerId,
                'settings' => $this->getDefaultSettings(),
                'created_by' => 1 // System user
            ]);
        }

        return response()->json($settings->settings);
    }

    /**
     * Get settings for a specific organizer (Admin only)
     */
    public function getOrganizerSettings(Request $request)
    {
        $user = Auth::user();
        
        if (!$user->hasRole('Administrator')) {
            abort(403, 'Only administrators can view organizer settings.');
        }

        $organizerId = $request->get('user_id');
        
        if (!$organizerId) {
            return response()->json(['error' => 'Organizer ID is required'], 400);
        }

        $settings = PwaSetting::where('scope', 'organizer')
            ->where('user_id', $organizerId)
            ->first();

        if (!$settings) {
            return response()->json(['error' => 'Settings not found for this organizer'], 404);
        }

        return response()->json([
            'settings' => $settings->settings,
            'user_id' => $organizerId
        ]);
    }
} 