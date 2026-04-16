<div x-show="activeTab === 'security'" class="space-y-2">
    <div class="bg-white border border-gray-200 rounded-md shadow-sm">
        <div class="bg-gray-50 border-b border-gray-200 px-4 py-3">
            <h2 class="text-sm font-semibold text-gray-700 flex items-center">
                <span class="material-icons text-primary-DEFAULT mr-2">password</span>
                Password Policies
            </h2>
        </div>
        
        <div class="p-4">
        <div class="space-y-3">
            <!-- Minimum Password Length -->
            <div class="flex flex-col md:flex-row md:items-start gap-1">
                <label for="min_password_length" class="text-xs font-medium text-gray-700 md:w-40 pt-2">
                    Minimum Password Length
                </label>
                <div class="flex-1">
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <span class="material-icons text-[#004aad] text-base">123</span>
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
                    <p class="mt-1 text-[10px] text-gray-500">Minimum number of characters required for passwords</p>
                </div>
            </div>
            
            <!-- Password Expiry -->
            <div class="flex flex-col md:flex-row md:items-start gap-1">
                <label for="password_expiry" class="text-xs font-medium text-gray-700 md:w-40 pt-2">
                    Password Expiry (days)
                </label>
                <div class="flex-1">
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <span class="material-icons text-[#004aad] text-base">calendar_today</span>
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
                    <p class="mt-1 text-[10px] text-gray-500">Days before password expires (0 for never)</p>
                </div>
            </div>

            <div class="flex flex-col md:flex-row md:items-start gap-1">
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
            
            <div class="flex flex-col md:flex-row md:items-start gap-1">
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
            
            <div class="flex flex-col md:flex-row md:items-start gap-1">
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
                <span class="material-icons text-primary-DEFAULT mr-2">login</span>
                Login Security
            </h2>
        </div>
        
        <div class="p-4">
        <div class="space-y-3">
            <!-- Max Login Attempts -->
            <div class="flex flex-col md:flex-row md:items-start gap-1">
                <label for="max_login_attempts" class="text-xs font-medium text-gray-700 md:w-40 pt-2">
                    Max Login Attempts
                </label>
                <div class="flex-1">
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <span class="material-icons text-[#004aad] text-base">pin</span>
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
                    <p class="mt-1 text-[10px] text-gray-500">Maximum login attempts before temporary lockout</p>
                </div>
            </div>
            
            <!-- Lockout Duration -->
            <div class="flex flex-col md:flex-row md:items-start gap-1">
                <label for="lockout_duration" class="text-xs font-medium text-gray-700 md:w-40 pt-2">
                    Lockout Duration (minutes)
                </label>
                <div class="flex-1">
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <span class="material-icons text-[#004aad] text-base">lock_clock</span>
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
                    <p class="mt-1 text-[10px] text-gray-500">Duration of account lockout after failed attempts</p>
                </div>
            </div>
            
            <!-- Session Timeout -->
            <div class="flex flex-col md:flex-row md:items-start gap-1">
                <label for="session_timeout" class="text-xs font-medium text-gray-700 md:w-40 pt-2">
                    Session Timeout (minutes)
                </label>
                <div class="flex-1">
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <span class="material-icons text-[#004aad] text-base">timelapse</span>
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
                    <p class="mt-1 text-[10px] text-gray-500">Inactive time before automatic logout</p>
                </div>
            </div>
            
            <div class="flex flex-col md:flex-row md:items-start gap-1">
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
            
            <div class="flex flex-col md:flex-row md:items-start gap-1">
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
                <span class="material-icons text-primary-DEFAULT mr-2">gpp_maybe</span>
                Security Auditing
            </h2>
        </div>
        
        <div class="p-4">
        <div class="space-y-3">
            <div class="flex flex-col md:flex-row md:items-start gap-1">
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
            
            <div class="flex flex-col md:flex-row md:items-start gap-1">
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
            
            <div class="flex flex-col md:flex-row md:items-start gap-1">
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
            
            <div class="flex flex-col md:flex-row md:items-start gap-1">
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
