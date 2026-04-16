<div x-show="activeTab === 'general'" class="space-y-2">
    <div class="bg-white border border-gray-200 rounded-md shadow-sm">
        <div class="bg-gray-50 border-b border-gray-200 px-4 py-3">
            <h2 class="text-sm font-semibold text-gray-700 flex items-center">
                <span class="material-icons text-primary-DEFAULT mr-2">business</span>
                Organization Settings
            </h2>
        </div>
        
        <div class="p-4">
        <div class="space-y-3">
            <!-- Organization Name -->
            <div class="flex flex-col md:flex-row md:items-start gap-1">
                <label for="org_name" class="text-xs font-medium text-gray-700 md:w-40 pt-2">
                    Organization Name
                </label>
                <div class="flex-1">
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <span class="material-icons text-[#004aad] text-base">business</span>
                        </div>
                        <input 
                            type="text" 
                            id="org_name" 
                            name="org_name" 
                            value="{{ old('org_name', $config->org_name ?? '') }}" 
                            class="w-full text-xs border-gray-300 rounded-[1px] pl-12 focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50"
                            :class="{'bg-gray-50': !isEditing}"
                            :disabled="!isEditing"
                        >
                    </div>
                    <p class="mt-1 text-[10px] text-gray-500">Official name of your organization</p>
                </div>
            </div>
            
            <!-- Contact Email -->
            <div class="flex flex-col md:flex-row md:items-start gap-1">
                <label for="org_email" class="text-xs font-medium text-gray-700 md:w-40 pt-2">
                    Contact Email
                </label>
                <div class="flex-1">
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <span class="material-icons text-[#004aad] text-base">email</span>
                        </div>
                        <input 
                            type="email" 
                            id="org_email" 
                            name="org_email" 
                            value="{{ old('org_email', $config->org_email ?? '') }}" 
                            class="w-full text-xs border-gray-300 rounded-[1px] pl-12 focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50"
                            :class="{'bg-gray-50': !isEditing}"
                            :disabled="!isEditing"
                        >
                    </div>
                    <p class="mt-1 text-[10px] text-gray-500">Primary contact email address</p>
                </div>
            </div>
            
            <!-- Default Timezone -->
            <div class="flex flex-col md:flex-row md:items-start gap-1">
                <label for="timezone" class="text-xs font-medium text-gray-700 md:w-40 pt-2">
                    Default Timezone
                </label>
                <div class="flex-1">
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <span class="material-icons text-[#004aad] text-base">public</span>
                        </div>
                        <select 
                            id="timezone" 
                            name="timezone" 
                            class="w-full text-xs border-gray-300 rounded-[1px] pl-12 focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50"
                            :class="{'bg-gray-50': !isEditing}"
                            :disabled="!isEditing"
                        >
                            <option value="UTC" {{ (old('timezone', $config->timezone ?? '') == 'UTC') ? 'selected' : '' }}>UTC</option>
                            <option value="Asia/Kuala_Lumpur" {{ (old('timezone', $config->timezone ?? '') == 'Asia/Kuala_Lumpur') ? 'selected' : '' }}>Asia/Kuala Lumpur (UTC+8)</option>
                            <option value="Asia/Singapore" {{ (old('timezone', $config->timezone ?? '') == 'Asia/Singapore') ? 'selected' : '' }}>Asia/Singapore (UTC+8)</option>
                            <option value="Asia/Jakarta" {{ (old('timezone', $config->timezone ?? '') == 'Asia/Jakarta') ? 'selected' : '' }}>Asia/Jakarta (UTC+7)</option>
                        </select>
                    </div>
                    <p class="mt-1 text-[10px] text-gray-500">System default timezone for dates and times</p>
                </div>
            </div>
            
            <!-- Date Format -->
            <div class="flex flex-col md:flex-row md:items-start gap-1">
                <label for="date_format" class="text-xs font-medium text-gray-700 md:w-40 pt-2">
                    Date Format
                </label>
                <div class="flex-1">
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <span class="material-icons text-[#004aad] text-base">today</span>
                        </div>
                        <select 
                            id="date_format" 
                            name="date_format" 
                            class="w-full text-xs border-gray-300 rounded-[1px] pl-12 focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50"
                            :class="{'bg-gray-50': !isEditing}"
                            :disabled="!isEditing"
                        >
                            <option value="Y-m-d" {{ (old('date_format', $config->date_format ?? 'd/m/Y') == 'Y-m-d') ? 'selected' : '' }}>YYYY-MM-DD (e.g. 2023-06-15)</option>
                            <option value="d/m/Y" {{ (old('date_format', $config->date_format ?? 'd/m/Y') == 'd/m/Y') ? 'selected' : '' }}>DD/MM/YYYY (e.g. 15/06/2023)</option>
                            <option value="m/d/Y" {{ (old('date_format', $config->date_format ?? 'd/m/Y') == 'm/d/Y') ? 'selected' : '' }}>MM/DD/YYYY (e.g. 06/15/2023)</option>
                            <option value="d-M-Y" {{ (old('date_format', $config->date_format ?? 'd/m/Y') == 'd-M-Y') ? 'selected' : '' }}>DD-Mon-YYYY (e.g. 15-Jun-2023)</option>
                        </select>
                    </div>
                    <p class="mt-1 text-[10px] text-gray-500">Format for displaying dates throughout the system</p>
                </div>
            </div>
            
            <!-- Organization Logo -->
            <div class="flex flex-col md:flex-row md:items-start gap-1">
                <label for="org_logo" class="text-xs font-medium text-gray-700 md:w-40 pt-2">
                    Organization Logo
                </label>
                <div class="flex-1">
                    <div class="flex items-center space-x-4">
                        <div class="w-16 h-16 border border-gray-300 rounded flex items-center justify-center bg-gray-50">
                            <img src="/images/logo.png" alt="Logo" class="max-w-full max-h-full p-1">
                        </div>
                        <div>
                            <label class="bg-white border border-gray-300 text-xs text-gray-700 hover:bg-gray-50 px-3 py-2 rounded cursor-pointer"
                                :class="{'opacity-50 cursor-not-allowed': !isEditing}"
                                :disabled="!isEditing"
                            >
                                <span class="material-icons text-xs mr-1 inline-block align-text-bottom">upload</span>
                                Upload New Logo
                                <input type="file" name="org_logo" class="hidden" :disabled="!isEditing">
                            </label>
                            <p class="text-[10px] text-gray-500 mt-1">Recommended size: 200x200px, max 1MB</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        </div>
    </div>
    
    <div class="bg-white border border-gray-200 rounded-md shadow-sm">
        <div class="bg-gray-50 border-b border-gray-200 px-4 py-3">
            <h2 class="text-sm font-semibold text-gray-700 flex items-center">
                <span class="material-icons text-primary-DEFAULT mr-2">tune</span>
                System Settings
            </h2>
        </div>
        
        <div class="p-4">
        <div class="space-y-3">
            <!-- Maintenance Mode -->
            <div class="flex flex-col md:flex-row md:items-start gap-1">
                <label class="text-xs font-medium text-gray-700 md:w-40 pt-2">
                    Maintenance Mode
                </label>
                <div class="flex-1">
                    <div class="flex items-center mt-1">
                        <label class="inline-flex items-center mr-4">
                            <input 
                                type="radio" 
                                name="maintenance_mode" 
                                value="0" 
                                class="text-primary-DEFAULT focus:ring-primary-light" 
                                {{ (old('maintenance_mode', $config->maintenance_mode ?? 0) == 0) ? 'checked' : '' }}
                                :disabled="!isEditing"
                            >
                            <span class="ml-2 text-xs text-gray-700">Off</span>
                        </label>
                        <label class="inline-flex items-center">
                            <input 
                                type="radio" 
                                name="maintenance_mode" 
                                value="1" 
                                class="text-primary-DEFAULT focus:ring-primary-light"
                                {{ (old('maintenance_mode', $config->maintenance_mode ?? 0) == 1) ? 'checked' : '' }}
                                :disabled="!isEditing"
                            >
                            <span class="ml-2 text-xs text-gray-700">On</span>
                        </label>
                    </div>
                    <p class="mt-1 text-[10px] text-gray-500">Enable maintenance mode to temporarily disable the site</p>
                </div>
            </div>
            
            <!-- Debug Mode -->
            <div class="flex flex-col md:flex-row md:items-start gap-1">
                <label class="text-xs font-medium text-gray-700 md:w-40 pt-2">
                    Debug Mode
                </label>
                <div class="flex-1">
                    <div class="flex items-center mt-1">
                        <label class="inline-flex items-center mr-4">
                            <input 
                                type="radio" 
                                name="debug_mode" 
                                value="0" 
                                class="text-primary-DEFAULT focus:ring-primary-light" 
                                {{ (old('debug_mode', $config->debug_mode ?? 0) == 0) ? 'checked' : '' }}
                                :disabled="!isEditing"
                            >
                            <span class="ml-2 text-xs text-gray-700">Off</span>
                        </label>
                        <label class="inline-flex items-center">
                            <input 
                                type="radio" 
                                name="debug_mode" 
                                value="1" 
                                class="text-primary-DEFAULT focus:ring-primary-light"
                                {{ (old('debug_mode', $config->debug_mode ?? 0) == 1) ? 'checked' : '' }}
                                :disabled="!isEditing"
                            >
                            <span class="ml-2 text-xs text-gray-700">On</span>
                        </label>
                    </div>
                    <p class="mt-1 text-[10px] text-gray-500">Show detailed error messages for debugging</p>
                </div>
            </div>
            
            <!-- Cache Lifetime -->
            <div class="flex flex-col md:flex-row md:items-start gap-1">
                <label for="cache_lifetime" class="text-xs font-medium text-gray-700 md:w-40 pt-2">
                    Cache Lifetime (minutes)
                </label>
                <div class="flex-1">
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <span class="material-icons text-[#004aad] text-base">schedule</span>
                        </div>
                        <input 
                            type="number" 
                            id="cache_lifetime" 
                            name="cache_lifetime" 
                            value="{{ old('cache_lifetime', $config->cache_lifetime ?? 60) }}" 
                            min="0" 
                            class="w-full text-xs border-gray-300 rounded-[1px] pl-12 focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50"
                            :class="{'bg-gray-50': !isEditing}"
                            :disabled="!isEditing"
                        >
                    </div>
                    <p class="mt-1 text-[10px] text-gray-500">How long to keep cached data (0 for no caching)</p>
                </div>
            </div>
            
            <!-- Default Pagination -->
            <div class="flex flex-col md:flex-row md:items-start gap-1">
                <label for="pagination" class="text-xs font-medium text-gray-700 md:w-40 pt-2">
                    Default Pagination
                </label>
                <div class="flex-1">
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <span class="material-icons text-[#004aad] text-base">view_list</span>
                        </div>
                        <select 
                            id="pagination" 
                            name="pagination" 
                            class="w-full text-xs border-gray-300 rounded-[1px] pl-12 focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50"
                            :class="{'bg-gray-50': !isEditing}"
                            :disabled="!isEditing"
                        >
                            <option value="10" {{ (old('pagination', $config->pagination ?? 25) == 10) ? 'selected' : '' }}>10 items per page</option>
                            <option value="25" {{ (old('pagination', $config->pagination ?? 25) == 25) ? 'selected' : '' }}>25 items per page</option>
                            <option value="50" {{ (old('pagination', $config->pagination ?? 25) == 50) ? 'selected' : '' }}>50 items per page</option>
                            <option value="100" {{ (old('pagination', $config->pagination ?? 25) == 100) ? 'selected' : '' }}>100 items per page</option>
                        </select>
                    </div>
                    <p class="mt-1 text-[10px] text-gray-500">Number of items to display per page</p>
                </div>
            </div>
            
            <div class="flex flex-col md:flex-row md:items-start gap-1">
                <div class="md:w-40"></div>
                <div class="flex-1">
                    <label class="flex items-center">
                        <input type="hidden" name="enable_error_reporting" value="0">
                        <input 
                            type="checkbox" 
                            name="enable_error_reporting"
                            value="1"
                            class="rounded border-gray-300 text-primary-DEFAULT focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50" 
                            {{ (old('enable_error_reporting', $config->enable_error_reporting ?? true)) ? 'checked' : '' }}
                            :disabled="!isEditing"
                        >
                        <span class="ml-2 text-xs text-gray-700">Enable system error reporting</span>
                    </label>
                    <p class="mt-1 text-[10px] text-gray-500">Send error reports to system administrators</p>
                </div>
            </div>
            
            <div class="flex flex-col md:flex-row md:items-start gap-1">
                <div class="md:w-40"></div>
                <div class="flex-1">
                    <label class="flex items-center">
                        <input type="hidden" name="enable_activity_logging" value="0">
                        <input 
                            type="checkbox" 
                            name="enable_activity_logging"
                            value="1"
                            class="rounded border-gray-300 text-primary-DEFAULT focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50" 
                            {{ (old('enable_activity_logging', $config->enable_activity_logging ?? true)) ? 'checked' : '' }}
                            :disabled="!isEditing"
                        >
                        <span class="ml-2 text-xs text-gray-700">Enable activity logging</span>
                    </label>
                    <p class="mt-1 text-[10px] text-gray-500">Track all user actions in the system log</p>
                </div>
            </div>
        </div>
        </div>
    </div>
    
    <div class="bg-white border border-gray-200 rounded-md shadow-sm">
        <div class="bg-gray-50 border-b border-gray-200 px-4 py-3">
            <h2 class="text-sm font-semibold text-gray-700 flex items-center">
                <span class="material-icons text-primary-DEFAULT mr-2">event</span>
                Event Settings
            </h2>
        </div>
        
        <div class="p-4">
        <div class="space-y-3">
            <!-- Registration Expiry -->
            <div class="flex flex-col md:flex-row md:items-start gap-1">
                <label for="event_expiry" class="text-xs font-medium text-gray-700 md:w-40 pt-2">
                    Registration Expiry (hours)
                </label>
                <div class="flex-1">
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <span class="material-icons text-[#004aad] text-base">hourglass_empty</span>
                        </div>
                        <input 
                            type="number" 
                            id="event_expiry" 
                            name="event_expiry" 
                            value="{{ old('event_expiry', $config->event_expiry ?? 48) }}" 
                            min="1" 
                            class="w-full text-xs border-gray-300 rounded-[1px] pl-12 focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50"
                            :class="{'bg-gray-50': !isEditing}"
                            :disabled="!isEditing"
                        >
                    </div>
                    <p class="mt-1 text-[10px] text-gray-500">Time until registration links expire</p>
                </div>
            </div>
            
            <!-- Default Event Status -->
            <div class="flex flex-col md:flex-row md:items-start gap-1">
                <label for="default_event_status" class="text-xs font-medium text-gray-700 md:w-40 pt-2">
                    Default Event Status
                </label>
                <div class="flex-1">
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <span class="material-icons text-[#004aad] text-base">label</span>
                        </div>
                        <select 
                            id="default_event_status" 
                            name="default_event_status" 
                            class="w-full text-xs border-gray-300 rounded-[1px] pl-12 focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50"
                            :class="{'bg-gray-50': !isEditing}"
                            :disabled="!isEditing"
                        >
                            <option value="draft" {{ (old('default_event_status', $config->default_event_status ?? 'published') == 'draft') ? 'selected' : '' }}>Draft</option>
                            <option value="published" {{ (old('default_event_status', $config->default_event_status ?? 'published') == 'published') ? 'selected' : '' }}>Published</option>
                            <option value="archived" {{ (old('default_event_status', $config->default_event_status ?? 'published') == 'archived') ? 'selected' : '' }}>Archived</option>
                        </select>
                    </div>
                    <p class="mt-1 text-[10px] text-gray-500">Status assigned to newly created events</p>
                </div>
            </div>
            
            <!-- Registration Message -->
            <div class="flex flex-col md:flex-row md:items-start gap-1">
                <label for="registration_message" class="text-xs font-medium text-gray-700 md:w-40 pt-2">
                    Default Registration Message
                </label>
                <div class="flex-1">
                    <textarea 
                        id="registration_message" 
                        name="registration_message" 
                        rows="3" 
                        class="w-full text-xs border-gray-300 rounded focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50"
                        :class="{'bg-gray-50': !isEditing}"
                        :disabled="!isEditing"
                    >{{ old('registration_message', $config->registration_message ?? 'Thank you for registering for this event. Please check your email for confirmation details.') }}</textarea>
                    <p class="mt-1 text-[10px] text-gray-500">Message shown after successful registration</p>
                </div>
            </div>
            
            <div class="flex flex-col md:flex-row md:items-start gap-1">
                <div class="md:w-40"></div>
                <div class="flex-1">
                    <label class="flex items-center">
                        <input type="hidden" name="allow_multiple_registrations" value="0">
                        <input 
                            type="checkbox" 
                            name="allow_multiple_registrations"
                            value="1"
                            class="rounded border-gray-300 text-primary-DEFAULT focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50" 
                            {{ (old('allow_multiple_registrations', $config->allow_multiple_registrations ?? true)) ? 'checked' : '' }}
                            :disabled="!isEditing"
                        >
                        <span class="ml-2 text-xs text-gray-700">Allow multiple registrations per email</span>
                    </label>
                </div>
            </div>
            
            <div class="flex flex-col md:flex-row md:items-start gap-1">
                <div class="md:w-40"></div>
                <div class="flex-1">
                    <label class="flex items-center">
                        <input type="hidden" name="auto_send_confirmation_emails" value="0">
                        <input 
                            type="checkbox" 
                            name="auto_send_confirmation_emails"
                            value="1"
                            class="rounded border-gray-300 text-primary-DEFAULT focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50" 
                            {{ (old('auto_send_confirmation_emails', $config->auto_send_confirmation_emails ?? true)) ? 'checked' : '' }}
                            :disabled="!isEditing"
                        >
                        <span class="ml-2 text-xs text-gray-700">Automatically send confirmation emails</span>
                    </label>
                </div>
            </div>
        </div>
        </div>
    </div>
</div>
