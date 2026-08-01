<div x-show="activeTab === 'security'" class="space-y-2">
    <style>
        /* Tooltip styles */
        .tooltip-wrapper {
            position: relative;
            display: inline-flex;
        }
        
        .tooltip-content {
            position: absolute;
            bottom: 100%;
            left: 50%;
            transform: translateX(-50%) translateY(-4px);
            background-color: #1f2937;
            color: white;
            padding: 6px 10px;
            border-radius: 6px;
            font-size: 11px;
            white-space: nowrap;
            z-index: 1000;
            pointer-events: none;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        }
        
        .tooltip-content::after {
            content: '';
            position: absolute;
            top: 100%;
            left: 50%;
            transform: translateX(-50%);
            border: 4px solid transparent;
            border-top-color: #1f2937;
        }
    </style>
    
    <div class="bg-white border border-gray-200 rounded-md shadow-sm">
        <div class="bg-gray-50 border-b border-gray-200 px-4 py-3">
            <h2 class="text-sm font-semibold text-gray-700 flex items-center">
                <span class="material-icons-outlined text-primary-DEFAULT mr-2">password</span>
                Password Policies
            </h2>
        </div>
        
        <div class="p-4">
        <div class="space-y-3">
            <!-- Minimum Password Length -->
            <div class="flex flex-col md:flex-row md:items-center gap-3">
                <label for="min_password_length" class="text-xs font-medium text-gray-700 md:w-40 flex items-center gap-1">
                    Minimum Password Length
                    <div class="tooltip-wrapper" x-data="{ show: false }">
                        <span class="material-icons-outlined text-gray-400 text-sm cursor-help" 
                              @mouseenter="show = true" 
                              @mouseleave="show = false">
                            help_outline
                        </span>
                        <div x-show="show" x-transition class="tooltip-content">
                            Minimum number of characters required for passwords
                        </div>
                    </div>
                </label>
                <div class="flex-1">
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <span class="material-icons-outlined text-[#004aad] text-base">123</span>
                        </div>
                        <input 
                            type="number" 
                            id="min_password_length" 
                            name="min_password_length" 
                            value="{{ old('min_password_length', $config->min_password_length ?? 8) }}" 
                            min="6" 
                            max="32" 
                            class="w-full text-xs border-gray-300 rounded-[1px] pl-12 focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50"
                            :class="{'bg-gray-50': !isEditing}"
                            :disabled="!isEditing"
                        >
                    </div>
                </div>
            </div>
            
            <!-- PWA password reset cooldown -->
            <div class="flex flex-col md:flex-row md:items-start gap-3">
                <label for="pwa_reset_cooldown_seconds" class="text-xs font-medium text-gray-700 md:w-40 flex items-center gap-1 md:pt-2">
                    PWA Reset Cooldown
                    <div class="tooltip-wrapper" x-data="{ show: false }">
                        <span class="material-icons-outlined text-gray-400 text-sm cursor-help"
                              @mouseenter="show = true"
                              @mouseleave="show = false">
                            help_outline
                        </span>
                        <div x-show="show" x-transition class="tooltip-content">
                            Seconds an organizer must wait between PWA password resets
                        </div>
                    </div>
                </label>
                <div class="flex-1">
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <span class="material-icons-outlined text-[#004aad] text-base">timer</span>
                        </div>
                        <input
                            type="number"
                            id="pwa_reset_cooldown_seconds"
                            name="pwa_reset_cooldown_seconds"
                            value="{{ old('pwa_reset_cooldown_seconds', $config->pwa_reset_cooldown_seconds ?? 60) }}"
                            min="0"
                            max="3600"
                            class="w-full text-xs border-gray-300 rounded-[1px] pl-12 focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50"
                            :class="{'bg-gray-50': !isEditing}"
                            :disabled="!isEditing"
                        >
                    </div>
                    <p class="text-xs text-gray-500 mt-1">
                        Each reset emails a newly generated password, so this stops a whole list being reset in one
                        sitting. Applies to organizers only &mdash; Administrators are never limited.
                        Set to <span class="font-medium">0</span> to remove the limit.
                    </p>
                </div>
            </div>

            <!-- Password Expiry -->
            <div class="flex flex-col md:flex-row md:items-center gap-3">
                <label for="password_expiry" class="text-xs font-medium text-gray-700 md:w-40 flex items-center gap-1">
                    Password Expiry (days)
                    <div class="tooltip-wrapper" x-data="{ show: false }">
                        <span class="material-icons-outlined text-gray-400 text-sm cursor-help" 
                              @mouseenter="show = true" 
                              @mouseleave="show = false">
                            help_outline
                        </span>
                        <div x-show="show" x-transition class="tooltip-content">
                            Days before password expires (0 for never)
                        </div>
                    </div>
                </label>
                <div class="flex-1">
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <span class="material-icons-outlined text-[#004aad] text-base">calendar_today</span>
                        </div>
                        <input 
                            type="number" 
                            id="password_expiry" 
                            name="password_expiry" 
                            value="{{ old('password_expiry', $config->password_expiry ?? 90) }}" 
                            min="0" 
                            class="w-full text-xs border-gray-300 rounded-[1px] pl-12 focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50"
                            :class="{'bg-gray-50': !isEditing}"
                            :disabled="!isEditing"
                        >
                    </div>
                </div>
            </div>

            <div class="flex flex-col md:flex-row md:items-center gap-3">
                <div class="md:w-40"></div>
                <div class="flex-1">
                    <label class="flex items-center">
                        <input type="hidden" name="require_special_chars" value="0">
                        <input 
                            type="checkbox" 
                            name="require_special_chars"
                            value="1"
                            class="rounded border-gray-300 text-primary-DEFAULT focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50" 
                            {{ (old('require_special_chars', $config->require_special_chars ?? true)) ? 'checked' : '' }}
                            :disabled="!isEditing"
                        >
                        <span class="ml-2 text-xs text-gray-700">Require special characters</span>
                    </label>
                </div>
            </div>
            
            <div class="flex flex-col md:flex-row md:items-center gap-3">
                <div class="md:w-40"></div>
                <div class="flex-1">
                    <label class="flex items-center">
                        <input type="hidden" name="require_numbers" value="0">
                        <input 
                            type="checkbox" 
                            name="require_numbers"
                            value="1"
                            class="rounded border-gray-300 text-primary-DEFAULT focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50" 
                            {{ (old('require_numbers', $config->require_numbers ?? true)) ? 'checked' : '' }}
                            :disabled="!isEditing"
                        >
                        <span class="ml-2 text-xs text-gray-700">Require numbers</span>
                    </label>
                </div>
            </div>
            
            <div class="flex flex-col md:flex-row md:items-center gap-3">
                <div class="md:w-40"></div>
                <div class="flex-1">
                    <label class="flex items-center">
                        <input type="hidden" name="require_uppercase" value="0">
                        <input 
                            type="checkbox" 
                            name="require_uppercase"
                            value="1"
                            class="rounded border-gray-300 text-primary-DEFAULT focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50" 
                            {{ (old('require_uppercase', $config->require_uppercase ?? true)) ? 'checked' : '' }}
                            :disabled="!isEditing"
                        >
                        <span class="ml-2 text-xs text-gray-700">Require uppercase letters</span>
                    </label>
                </div>
            </div>
        </div>
        </div>
    </div>
    
    <div class="bg-white border border-gray-200 rounded-md shadow-sm">
        <div class="bg-gray-50 border-b border-gray-200 px-4 py-3">
            <h2 class="text-sm font-semibold text-gray-700 flex items-center">
                <span class="material-icons-outlined text-primary-DEFAULT mr-2">login</span>
                Login Security
            </h2>
        </div>
        
        <div class="p-4">
        <div class="space-y-3">
            <!-- Max Login Attempts -->
            <div class="flex flex-col md:flex-row md:items-center gap-3">
                <label for="max_login_attempts" class="text-xs font-medium text-gray-700 md:w-40 flex items-center gap-1">
                    Max Login Attempts
                    <div class="tooltip-wrapper" x-data="{ show: false }">
                        <span class="material-icons-outlined text-gray-400 text-sm cursor-help" 
                              @mouseenter="show = true" 
                              @mouseleave="show = false">
                            help_outline
                        </span>
                        <div x-show="show" x-transition class="tooltip-content">
                            Maximum login attempts before temporary lockout
                        </div>
                    </div>
                </label>
                <div class="flex-1">
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <span class="material-icons-outlined text-[#004aad] text-base">pin</span>
                        </div>
                        <input 
                            type="number" 
                            id="max_login_attempts" 
                            name="max_login_attempts" 
                            value="{{ old('max_login_attempts', $config->max_login_attempts ?? 5) }}" 
                            min="1" 
                            class="w-full text-xs border-gray-300 rounded-[1px] pl-12 focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50"
                            :class="{'bg-gray-50': !isEditing}"
                            :disabled="!isEditing"
                        >
                    </div>
                </div>
            </div>
            
            <!-- Lockout Duration -->
            <div class="flex flex-col md:flex-row md:items-center gap-3">
                <label for="lockout_duration" class="text-xs font-medium text-gray-700 md:w-40 flex items-center gap-1">
                    Lockout Duration (minutes)
                    <div class="tooltip-wrapper" x-data="{ show: false }">
                        <span class="material-icons-outlined text-gray-400 text-sm cursor-help" 
                              @mouseenter="show = true" 
                              @mouseleave="show = false">
                            help_outline
                        </span>
                        <div x-show="show" x-transition class="tooltip-content">
                            Duration of account lockout after failed attempts
                        </div>
                    </div>
                </label>
                <div class="flex-1">
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <span class="material-icons-outlined text-[#004aad] text-base">lock_clock</span>
                        </div>
                        <input 
                            type="number" 
                            id="lockout_duration" 
                            name="lockout_duration" 
                            value="{{ old('lockout_duration', $config->lockout_duration ?? 15) }}" 
                            min="1" 
                            class="w-full text-xs border-gray-300 rounded-[1px] pl-12 focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50"
                            :class="{'bg-gray-50': !isEditing}"
                            :disabled="!isEditing"
                        >
                    </div>
                </div>
            </div>
            
            <!-- Session Timeout -->
            <div class="flex flex-col md:flex-row md:items-center gap-3">
                <label for="session_timeout" class="text-xs font-medium text-gray-700 md:w-40 flex items-center gap-1">
                    Session Timeout (minutes)
                    <div class="tooltip-wrapper" x-data="{ show: false }">
                        <span class="material-icons-outlined text-gray-400 text-sm cursor-help" 
                              @mouseenter="show = true" 
                              @mouseleave="show = false">
                            help_outline
                        </span>
                        <div x-show="show" x-transition class="tooltip-content">
                            Inactive time before automatic logout
                        </div>
                    </div>
                </label>
                <div class="flex-1">
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <span class="material-icons-outlined text-[#004aad] text-base">timelapse</span>
                        </div>
                        <input 
                            type="number" 
                            id="session_timeout" 
                            name="session_timeout" 
                            value="{{ old('session_timeout', $config->session_timeout ?? 120) }}" 
                            min="5" 
                            class="w-full text-xs border-gray-300 rounded-[1px] pl-12 focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50"
                            :class="{'bg-gray-50': !isEditing}"
                            :disabled="!isEditing"
                        >
                    </div>
                </div>
            </div>
            
            <div class="flex flex-col md:flex-row md:items-center gap-3">
                <div class="md:w-40"></div>
                <div class="flex-1">
                    <label class="flex items-center">
                        <input type="hidden" name="enable_2fa" value="0">
                        <input 
                            type="checkbox" 
                            name="enable_2fa"
                            value="1"
                            class="rounded border-gray-300 text-primary-DEFAULT focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50" 
                            {{ (old('enable_2fa', $config->enable_2fa ?? true)) ? 'checked' : '' }}
                            :disabled="!isEditing"
                        >
                        <span class="ml-2 text-xs text-gray-700">Enable two-factor authentication</span>
                    </label>
                </div>
            </div>
            
            <div class="flex flex-col md:flex-row md:items-center gap-3">
                <div class="md:w-40"></div>
                <div class="flex-1">
                    <label class="flex items-center">
                        <input type="hidden" name="force_ssl" value="0">
                        <input 
                            type="checkbox" 
                            name="force_ssl"
                            value="1"
                            class="rounded border-gray-300 text-primary-DEFAULT focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50" 
                            {{ (old('force_ssl', $config->force_ssl ?? true)) ? 'checked' : '' }}
                            :disabled="!isEditing"
                        >
                        <span class="ml-2 text-xs text-gray-700">Force SSL/HTTPS connections</span>
                    </label>
                </div>
            </div>
        </div>
        </div>
    </div>
    
    <div class="bg-white border border-gray-200 rounded-md shadow-sm">
        <div class="bg-gray-50 border-b border-gray-200 px-4 py-3">
            <h2 class="text-sm font-semibold text-gray-700 flex items-center">
                <span class="material-icons-outlined text-primary-DEFAULT mr-2">gpp_maybe</span>
                Security Auditing
            </h2>
        </div>
        
        <div class="p-4">
        <div class="space-y-3">
            <div class="flex flex-col md:flex-row md:items-center gap-3">
                <div class="md:w-40"></div>
                <div class="flex-1">
                    <label class="flex items-center">
                        <input type="hidden" name="log_failed_logins" value="0">
                        <input 
                            type="checkbox" 
                            name="log_failed_logins"
                            value="1"
                            class="rounded border-gray-300 text-primary-DEFAULT focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50" 
                            {{ (old('log_failed_logins', $config->log_failed_logins ?? true)) ? 'checked' : '' }}
                            :disabled="!isEditing"
                        >
                        <span class="ml-2 text-xs text-gray-700">Log failed login attempts</span>
                    </label>
                </div>
            </div>
            
            <div class="flex flex-col md:flex-row md:items-center gap-3">
                <div class="md:w-40"></div>
                <div class="flex-1">
                    <label class="flex items-center">
                        <input type="hidden" name="log_password_changes" value="0">
                        <input 
                            type="checkbox" 
                            name="log_password_changes"
                            value="1"
                            class="rounded border-gray-300 text-primary-DEFAULT focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50" 
                            {{ (old('log_password_changes', $config->log_password_changes ?? true)) ? 'checked' : '' }}
                            :disabled="!isEditing"
                        >
                        <span class="ml-2 text-xs text-gray-700">Log password changes</span>
                    </label>
                </div>
            </div>
            
            <div class="flex flex-col md:flex-row md:items-center gap-3">
                <div class="md:w-40"></div>
                <div class="flex-1">
                    <label class="flex items-center">
                        <input type="hidden" name="log_permission_changes" value="0">
                        <input 
                            type="checkbox" 
                            name="log_permission_changes"
                            value="1"
                            class="rounded border-gray-300 text-primary-DEFAULT focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50" 
                            {{ (old('log_permission_changes', $config->log_permission_changes ?? true)) ? 'checked' : '' }}
                            :disabled="!isEditing"
                        >
                        <span class="ml-2 text-xs text-gray-700">Log permission changes</span>
                    </label>
                </div>
            </div>
            
            <div class="flex flex-col md:flex-row md:items-center gap-3">
                <div class="md:w-40"></div>
                <div class="flex-1">
                    <label class="flex items-center">
                        <input type="hidden" name="enable_security_alerts" value="0">
                        <input 
                            type="checkbox" 
                            name="enable_security_alerts"
                            value="1"
                            class="rounded border-gray-300 text-primary-DEFAULT focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50" 
                            {{ (old('enable_security_alerts', $config->enable_security_alerts ?? true)) ? 'checked' : '' }}
                            :disabled="!isEditing"
                        >
                        <span class="ml-2 text-xs text-gray-700">Send security alerts to administrators</span>
                    </label>
                </div>
            </div>
        </div>
        </div>
    </div>
</div>
