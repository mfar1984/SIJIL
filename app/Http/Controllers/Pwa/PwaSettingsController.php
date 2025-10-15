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
    public function update(Request $request)
    {
        $user = Auth::user();
        
        $request->validate([
            'enable_pwa_access' => 'boolean',
            'auto_create_accounts' => 'boolean',
            'force_password_change' => 'boolean',
            'default_pwa_access' => 'required|in:enabled,disabled,ask',
            'checkbox_label' => 'required|string|max:255',
            'checkbox_default_state' => 'required|in:checked,unchecked',
            'password_length' => 'required|integer|min:6|max:16',
            'include_uppercase' => 'boolean',
            'include_lowercase' => 'boolean',
            'include_numbers' => 'boolean',
            'include_special_chars' => 'boolean',
            'password_expiry' => 'required|in:never,30,60,90,180',
            'send_welcome_email' => 'boolean',
            'include_app_link' => 'boolean',
            'pwa_app_link' => 'required|url',
            'support_email' => 'required|email',
            'real_time_sync' => 'boolean',
            'sync_name' => 'boolean',
            'sync_email' => 'boolean',
            'sync_phone' => 'boolean',
            'sync_organization' => 'boolean',
            'sync_address' => 'boolean',
            'session_timeout' => 'required|in:30,60,120,240,480,1440',
            'max_login_attempts' => 'required|integer|min:3|max:10',
            'lockout_duration' => 'required|in:15,30,60,120,1440,manual'
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

        // Update settings
        $settings->update([
            'settings' => $request->all(),
            'updated_by' => $user->id
        ]);

        return redirect()->route('pwa.settings')->with('success', 'PWA settings updated successfully.');
    }

    /**
     * Get default PWA settings
     */
    private function getDefaultSettings()
    {
        return [
            'enable_pwa_access' => true,
            'auto_create_accounts' => true,
            'force_password_change' => true,
            'default_pwa_access' => 'enabled',
            'checkbox_label' => 'Enable E-Certificate Online mobile access',
            'checkbox_default_state' => 'checked',
            'password_length' => 8,
            'include_uppercase' => true,
            'include_lowercase' => true,
            'include_numbers' => true,
            'include_special_chars' => false,
            'password_expiry' => 'never',
            'send_welcome_email' => true,
            'include_app_link' => true,
            'pwa_app_link' => 'https://apps.e-certificate.com.my',
            'support_email' => 'support@e-certificate.com.my',
            'real_time_sync' => true,
            'sync_name' => true,
            'sync_email' => true,
            'sync_phone' => true,
            'sync_organization' => true,
            'sync_address' => false,
            'session_timeout' => '60',
            'max_login_attempts' => 5,
            'lockout_duration' => '30'
        ];
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