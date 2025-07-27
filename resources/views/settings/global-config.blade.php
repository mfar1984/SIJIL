<x-app-layout>
    <x-slot name="breadcrumb">
        <span>Settings</span>
        <span class="mx-2 text-gray-500">/</span>
        <span>Global Config</span>
    </x-slot>

    <x-slot name="title">Global Config</x-slot>

    <div class="bg-white rounded shadow-md border border-gray-300" x-data="{ isEditing: false }">
        <div class="p-6 border-b border-gray-200">
            <div class="flex justify-between items-start">
                <div>
                    <div class="flex items-center">
                        <span class="material-icons mr-2 text-primary-DEFAULT">settings</span>
                        <h1 class="text-xl font-bold text-gray-800">Global Configuration</h1>
                    </div>
                    <p class="text-xs text-gray-500 mt-1 ml-8">Manage system-wide configuration settings</p>
                </div>

            </div>
        </div>
        
        <div class="p-4" x-data="{ activeTab: 'general' }">
            <!-- Configuration Tabs -->
            <div class="border-b border-gray-200 mb-4">
                <div class="flex flex-wrap -mb-px">
                    <button 
                        @click="activeTab = 'general'" 
                        :class="{'border-primary-DEFAULT text-primary-DEFAULT': activeTab === 'general', 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300': activeTab !== 'general'}"
                        class="inline-flex items-center py-3 px-4 text-xs font-medium leading-5 border-b-2 focus:outline-none transition duration-150 ease-in-out"
                    >
                        <span class="material-icons text-xs mr-2">tune</span>
                        General
                    </button>
                    <button 
                        @click="activeTab = 'security'" 
                        :class="{'border-primary-DEFAULT text-primary-DEFAULT': activeTab === 'security', 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300': activeTab !== 'security'}"
                        class="inline-flex items-center py-3 px-4 text-xs font-medium leading-5 border-b-2 focus:outline-none transition duration-150 ease-in-out"
                    >
                        <span class="material-icons text-xs mr-2">security</span>
                        Security
                    </button>
                    <button 
                        @click="activeTab = 'appearance'" 
                        :class="{'border-primary-DEFAULT text-primary-DEFAULT': activeTab === 'appearance', 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300': activeTab !== 'appearance'}"
                        class="inline-flex items-center py-3 px-4 text-xs font-medium leading-5 border-b-2 focus:outline-none transition duration-150 ease-in-out"
                    >
                        <span class="material-icons text-xs mr-2">palette</span>
                        Appearance
                    </button>
                    <button 
                        @click="activeTab = 'notifications'" 
                        :class="{'border-primary-DEFAULT text-primary-DEFAULT': activeTab === 'notifications', 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300': activeTab !== 'notifications'}"
                        class="inline-flex items-center py-3 px-4 text-xs font-medium leading-5 border-b-2 focus:outline-none transition duration-150 ease-in-out"
                    >
                        <span class="material-icons text-xs mr-2">notifications</span>
                        Notifications
                    </button>
                    <button 
                        @click="activeTab = 'api'" 
                        :class="{'border-primary-DEFAULT text-primary-DEFAULT': activeTab === 'api', 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300': activeTab !== 'api'}"
                        class="inline-flex items-center py-3 px-4 text-xs font-medium leading-5 border-b-2 focus:outline-none transition duration-150 ease-in-out"
                    >
                        <span class="material-icons text-xs mr-2">code</span>
                        API & Integrations
                    </button>
                </div>
            </div>
            
            <form method="POST" action="{{ route('settings.global-config.update') }}" enctype="multipart/form-data">
                @csrf
                <!-- General Settings Tab -->
                <div x-show="activeTab === 'general'" class="space-y-4">
                    <div class="bg-blue-50 border border-blue-100 rounded-md p-3 mb-4">
                        <div class="flex">
                            <span class="material-icons text-blue-600 mr-2">info</span>
                            <div class="text-xs text-blue-700">
                                <p class="font-medium">General Configuration</p>
                                <p class="mt-1">Configure basic system settings including organization information and system preferences.</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="border-b border-gray-200 pb-5">
                        <h2 class="text-sm font-semibold text-gray-700 mb-4 flex items-center">
                            <span class="material-icons text-primary-DEFAULT mr-2">business</span>
                            Organization Settings
                        </h2>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                            <!-- Organization Name -->
                            <div>
                                <label for="org_name" class="flex items-center text-xs font-medium text-gray-700 mb-1">
                                    <span class="material-icons text-sm mr-1 text-primary-DEFAULT">domain</span>
                                    Organization Name
                                </label>
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
                            
                            <!-- Contact Email -->
                            <div>
                                <label for="org_email" class="flex items-center text-xs font-medium text-gray-700 mb-1">
                                    <span class="material-icons text-sm mr-1 text-primary-DEFAULT">alternate_email</span>
                                    Contact Email
                                </label>
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
                            
                            <!-- Default Timezone -->
                            <div>
                                <label for="timezone" class="flex items-center text-xs font-medium text-gray-700 mb-1">
                                    <span class="material-icons text-sm mr-1 text-primary-DEFAULT">schedule</span>
                                    Default Timezone
                                </label>
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
                            
                            <!-- Date Format -->
                            <div>
                                <label for="date_format" class="flex items-center text-xs font-medium text-gray-700 mb-1">
                                    <span class="material-icons text-sm mr-1 text-primary-DEFAULT">date_range</span>
                                    Date Format
                                </label>
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
                        <div class="mt-4">
                            <label for="org_logo" class="flex items-center text-xs font-medium text-gray-700 mb-1">
                                <span class="material-icons text-sm mr-1 text-primary-DEFAULT">image</span>
                                Organization Logo
                            </label>
                            <div class="flex items-center space-x-4 mt-2">
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
                    
                    <div class="border-b border-gray-200 pb-5">
                        <h2 class="text-sm font-semibold text-gray-700 mb-4 flex items-center">
                            <span class="material-icons text-primary-DEFAULT mr-2">tune</span>
                            System Settings
                        </h2>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                            <!-- Maintenance Mode -->
                            <div>
                                <label class="flex items-center text-xs font-medium text-gray-700 mb-1">
                                    <span class="material-icons text-sm mr-1 text-primary-DEFAULT">build</span>
                                    Maintenance Mode
                                </label>
                                <div class="flex items-center mt-2">
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
                            
                            <!-- Debug Mode -->
                            <div>
                                <label class="flex items-center text-xs font-medium text-gray-700 mb-1">
                                    <span class="material-icons text-sm mr-1 text-primary-DEFAULT">bug_report</span>
                                    Debug Mode
                                </label>
                                <div class="flex items-center mt-2">
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
                            
                            <!-- Cache Lifetime -->
                            <div>
                                <label for="cache_lifetime" class="flex items-center text-xs font-medium text-gray-700 mb-1">
                                    <span class="material-icons text-sm mr-1 text-primary-DEFAULT">timer</span>
                                    Cache Lifetime (minutes)
                                </label>
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
                            
                            <!-- Default Pagination -->
                            <div>
                                <label for="pagination" class="flex items-center text-xs font-medium text-gray-700 mb-1">
                                    <span class="material-icons text-sm mr-1 text-primary-DEFAULT">format_list_numbered</span>
                                    Default Pagination
                                </label>
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
                        
                        <div class="mt-4">
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
                            <p class="mt-1 text-[10px] text-gray-500 ml-6">Send error reports to system administrators</p>
                        </div>
                        
                        <div class="mt-3">
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
                            <p class="mt-1 text-[10px] text-gray-500 ml-6">Track all user actions in the system log</p>
                        </div>
                    </div>
                    
                    <div>
                        <h2 class="text-sm font-semibold text-gray-700 mb-4 flex items-center">
                            <span class="material-icons text-primary-DEFAULT mr-2">event</span>
                            Event Settings
                        </h2>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                            <!-- Registration Expiry -->
                            <div>
                                <label for="event_expiry" class="flex items-center text-xs font-medium text-gray-700 mb-1">
                                    <span class="material-icons text-sm mr-1 text-primary-DEFAULT">timer_off</span>
                                    Registration Expiry (hours)
                                </label>
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
                            
                            <!-- Default Event Status -->
                            <div>
                                <label for="default_event_status" class="flex items-center text-xs font-medium text-gray-700 mb-1">
                                    <span class="material-icons text-sm mr-1 text-primary-DEFAULT">flag</span>
                                    Default Event Status
                                </label>
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
                        <div class="mt-4">
                            <label for="registration_message" class="flex items-center text-xs font-medium text-gray-700 mb-1">
                                <span class="material-icons text-sm mr-1 text-primary-DEFAULT">message</span>
                                Default Registration Message
                            </label>
                            <div class="relative">
                                <div class="absolute top-3 left-0 pl-3 flex items-start pointer-events-none">
                                    <span class="material-icons text-[#004aad] text-base">description</span>
                                </div>
                                <textarea 
                                    id="registration_message" 
                                    name="registration_message" 
                                    rows="3" 
                                    class="w-full text-xs border-gray-300 rounded-[1px] pl-12 py-3 focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50"
                                    :class="{'bg-gray-50': !isEditing}"
                                    :disabled="!isEditing"
                                >{{ old('registration_message', $config->registration_message ?? 'Thank you for registering for this event. Please check your email for confirmation details.') }}</textarea>
                            </div>
                            <p class="mt-1 text-[10px] text-gray-500">Message shown after successful registration</p>
                        </div>
                        
                        <div class="mt-4">
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
                        
                        <div class="mt-3">
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
                
                <!-- Security Tab -->
                <div x-show="activeTab === 'security'" class="space-y-4">
                    <div class="bg-blue-50 border border-blue-100 rounded-md p-3 mb-4">
                        <div class="flex">
                            <span class="material-icons text-blue-600 mr-2">info</span>
                            <div class="text-xs text-blue-700">
                                <p class="font-medium">Security Configuration</p>
                                <p class="mt-1">Configure security-related settings including password policies and access controls.</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="border-b border-gray-200 pb-5">
                        <h2 class="text-sm font-semibold text-gray-700 mb-4 flex items-center">
                            <span class="material-icons text-primary-DEFAULT mr-2">password</span>
                            Password Policies
                        </h2>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                            <!-- Minimum Password Length -->
                            <div>
                                <label for="min_password_length" class="flex items-center text-xs font-medium text-gray-700 mb-1">
                                    <span class="material-icons text-sm mr-1 text-primary-DEFAULT">straighten</span>
                                    Minimum Password Length
                                </label>
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
                            
                            <!-- Password Expiry -->
                            <div>
                                <label for="password_expiry" class="flex items-center text-xs font-medium text-gray-700 mb-1">
                                    <span class="material-icons text-sm mr-1 text-primary-DEFAULT">event_busy</span>
                                    Password Expiry (days)
                                </label>
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

                        <div class="mt-4">
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
                        
                        <div class="mt-3">
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
                        
                        <div class="mt-3">
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
                    
                    <div class="border-b border-gray-200 pb-5">
                        <h2 class="text-sm font-semibold text-gray-700 mb-4 flex items-center">
                            <span class="material-icons text-primary-DEFAULT mr-2">login</span>
                            Login Security
                        </h2>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                            <!-- Max Login Attempts -->
                            <div>
                                <label for="max_login_attempts" class="flex items-center text-xs font-medium text-gray-700 mb-1">
                                    <span class="material-icons text-sm mr-1 text-primary-DEFAULT">block</span>
                                    Max Login Attempts
                                </label>
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
                            
                            <!-- Lockout Duration -->
                            <div>
                                <label for="lockout_duration" class="flex items-center text-xs font-medium text-gray-700 mb-1">
                                    <span class="material-icons text-sm mr-1 text-primary-DEFAULT">timer</span>
                                    Lockout Duration (minutes)
                                </label>
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
                            
                            <!-- Session Timeout -->
                            <div>
                                <label for="session_timeout" class="flex items-center text-xs font-medium text-gray-700 mb-1">
                                    <span class="material-icons text-sm mr-1 text-primary-DEFAULT">timer_off</span>
                                    Session Timeout (minutes)
                                </label>
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
                        
                        <div class="mt-4">
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
                        
                        <div class="mt-3">
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
                    
                    <div>
                        <h2 class="text-sm font-semibold text-gray-700 mb-4 flex items-center">
                            <span class="material-icons text-primary-DEFAULT mr-2">gpp_maybe</span>
                            Security Auditing
                        </h2>
                        
                        <div class="mt-4">
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
                        
                        <div class="mt-3">
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
                        
                        <div class="mt-3">
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
                        
                        <div class="mt-3">
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
                
                <!-- Appearance Tab -->
                <div x-show="activeTab === 'appearance'" class="space-y-4">
                    <div class="bg-blue-50 border border-blue-100 rounded-md p-3 mb-4">
                        <div class="flex">
                            <span class="material-icons text-blue-600 mr-2">info</span>
                            <div class="text-xs text-blue-700">
                                <p class="font-medium">Appearance Configuration</p>
                                <p class="mt-1">Customize the look and feel of your application including themes and branding.</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="border-b border-gray-200 pb-5">
                        <h2 class="text-sm font-semibold text-gray-700 mb-4 flex items-center">
                            <span class="material-icons text-primary-DEFAULT mr-2">palette</span>
                            Theme Settings
                        </h2>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                            <!-- Primary Color -->
                            <div>
                                <label for="primary_color" class="flex items-center text-xs font-medium text-gray-700 mb-1">
                                    <span class="material-icons text-sm mr-1 text-primary-DEFAULT">format_color_fill</span>
                                    Primary Color
                                </label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <span class="material-icons text-[#004aad] text-base">colorize</span>
                                    </div>
                                    <div class="flex items-center">
                                        <input 
                                            type="text" 
                                            id="primary_color" 
                                            name="primary_color" 
                                            value="{{ old('primary_color', $config->primary_color ?? '#004aad') }}" 
                                            class="w-full text-xs border-gray-300 rounded-[1px] pl-12 focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50"
                                            :class="{'bg-gray-50': !isEditing}"
                                            :disabled="!isEditing"
                                        >
                                        <input 
                                            type="color" 
                                            value="{{ old('primary_color', $config->primary_color ?? '#004aad') }}" 
                                            class="h-[34px] w-10 border-0 p-0 ml-2"
                                        >
                                    </div>
                                </div>
                                <p class="mt-1 text-[10px] text-gray-500">Primary accent color for buttons and highlights</p>
                            </div>
                            
                            <!-- Secondary Color -->
                            <div>
                                <label for="secondary_color" class="flex items-center text-xs font-medium text-gray-700 mb-1">
                                    <span class="material-icons text-sm mr-1 text-primary-DEFAULT">format_color_fill</span>
                                    Secondary Color
                                </label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <span class="material-icons text-[#004aad] text-base">colorize</span>
                                    </div>
                                    <div class="flex items-center">
                                        <input 
                                            type="text" 
                                            id="secondary_color" 
                                            name="secondary_color" 
                                            value="{{ old('secondary_color', $config->secondary_color ?? '#38bdf8') }}" 
                                            class="w-full text-xs border-gray-300 rounded-[1px] pl-12 focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50"
                                            :class="{'bg-gray-50': !isEditing}"
                                            :disabled="!isEditing"
                                        >
                                        <input 
                                            type="color" 
                                            value="{{ old('secondary_color', $config->secondary_color ?? '#38bdf8') }}" 
                                            class="h-[34px] w-10 border-0 p-0 ml-2"
                                        >
                                    </div>
                                </div>
                                <p class="mt-1 text-[10px] text-gray-500">Secondary color for gradients and accents</p>
                            </div>
                            
                            <!-- Default Theme -->
                            <div>
                                <label for="default_theme" class="flex items-center text-xs font-medium text-gray-700 mb-1">
                                    <span class="material-icons text-sm mr-1 text-primary-DEFAULT">brightness_medium</span>
                                    Default Theme
                                </label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <span class="material-icons text-[#004aad] text-base">style</span>
                                    </div>
                                    <select 
                                        id="default_theme" 
                                        name="default_theme" 
                                        class="w-full text-xs border-gray-300 rounded-[1px] pl-12 focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50"
                                        :class="{'bg-gray-50': !isEditing}"
                                        :disabled="!isEditing"
                                    >
                                        <option value="light" {{ (old('default_theme', $config->default_theme ?? 'light') == 'light') ? 'selected' : '' }}>Light</option>
                                        <option value="dark" {{ (old('default_theme', $config->default_theme ?? 'light') == 'dark') ? 'selected' : '' }}>Dark</option>
                                        <option value="system" {{ (old('default_theme', $config->default_theme ?? 'light') == 'system') ? 'selected' : '' }}>System Default</option>
                                    </select>
                                </div>
                                <p class="mt-1 text-[10px] text-gray-500">Default color theme for new users</p>
                            </div>
                            
                            <!-- Font Family -->
                            <div>
                                <label for="font_family" class="flex items-center text-xs font-medium text-gray-700 mb-1">
                                    <span class="material-icons text-sm mr-1 text-primary-DEFAULT">text_format</span>
                                    Font Family
                                </label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <span class="material-icons text-[#004aad] text-base">font_download</span>
                                    </div>
                                    <select 
                                        id="font_family" 
                                        name="font_family" 
                                        class="w-full text-xs border-gray-300 rounded-[1px] pl-12 focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50"
                                        :class="{'bg-gray-50': !isEditing}"
                                        :disabled="!isEditing"
                                    >
                                        <option value="inter" {{ (old('font_family', $config->font_family ?? 'inter') == 'inter') ? 'selected' : '' }}>Inter</option>
                                        <option value="roboto" {{ (old('font_family', $config->font_family ?? 'inter') == 'roboto') ? 'selected' : '' }}>Roboto</option>
                                        <option value="poppins" {{ (old('font_family', $config->font_family ?? 'inter') == 'poppins') ? 'selected' : '' }}>Poppins</option>
                                        <option value="opensans" {{ (old('font_family', $config->font_family ?? 'inter') == 'opensans') ? 'selected' : '' }}>Open Sans</option>
                                        <option value="system" {{ (old('font_family', $config->font_family ?? 'inter') == 'system') ? 'selected' : '' }}>System Default</option>
                                    </select>
                                </div>
                                <p class="mt-1 text-[10px] text-gray-500">Font family for the user interface</p>
                            </div>
                        </div>
                        
                        <div class="mt-4">
                            <label class="flex items-center">
                                <input type="hidden" name="allow_user_theme_choice" value="0">
                                <input 
                                    type="checkbox" 
                                    name="allow_user_theme_choice"
                                    value="1"
                                    class="rounded border-gray-300 text-primary-DEFAULT focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50" 
                                    {{ (old('allow_user_theme_choice', $config->allow_user_theme_choice ?? true)) ? 'checked' : '' }}
                                    :disabled="!isEditing"
                                >
                                <span class="ml-2 text-xs text-gray-700">Allow users to choose their own theme</span>
                            </label>
                        </div>
                    </div>
                    
                    <div class="border-b border-gray-200 pb-5">
                        <h2 class="text-sm font-semibold text-gray-700 mb-4 flex items-center">
                            <span class="material-icons text-primary-DEFAULT mr-2">branding_watermark</span>
                            Branding Settings
                        </h2>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                            <!-- Favicon -->
                            <div>
                                <label for="favicon" class="flex items-center text-xs font-medium text-gray-700 mb-1">
                                    <span class="material-icons text-sm mr-1 text-primary-DEFAULT">bookmark</span>
                                    Favicon
                                </label>
                                <div class="flex items-center space-x-4 mt-2">
                                    <div class="w-10 h-10 border border-gray-300 rounded flex items-center justify-center bg-gray-50">
                                        <img src="/favicon.ico" alt="Favicon" class="max-w-full max-h-full p-1">
                                    </div>
                                    <div>
                                        <label class="bg-white border border-gray-300 text-xs text-gray-700 hover:bg-gray-50 px-3 py-2 rounded cursor-pointer"
                                            :class="{'opacity-50 cursor-not-allowed': !isEditing}"
                                            :disabled="!isEditing"
                                        >
                                            <span class="material-icons text-xs mr-1 inline-block align-text-bottom">upload</span>
                                            Upload Favicon
                                            <input type="file" name="favicon" class="hidden" :disabled="!isEditing">
                                        </label>
                                        <p class="text-[10px] text-gray-500 mt-1">ICO/PNG format (32x32px)</p>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Login Background -->
                            <div>
                                <label for="login_background" class="flex items-center text-xs font-medium text-gray-700 mb-1">
                                    <span class="material-icons text-sm mr-1 text-primary-DEFAULT">wallpaper</span>
                                    Login Background
                                </label>
                                <div class="mt-2">
                                    <label class="bg-white border border-gray-300 text-xs text-gray-700 hover:bg-gray-50 px-3 py-2 rounded cursor-pointer"
                                        :class="{'opacity-50 cursor-not-allowed': !isEditing}"
                                        :disabled="!isEditing"
                                    >
                                        <span class="material-icons text-xs mr-1 inline-block align-text-bottom">upload</span>
                                        Upload Background
                                        <input type="file" name="login_background" class="hidden" :disabled="!isEditing">
                                    </label>
                                    <p class="text-[10px] text-gray-500 mt-1">Recommended size: 1920x1080px, max 2MB</p>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Custom CSS -->
                        <div class="mt-4">
                            <label for="custom_css" class="flex items-center text-xs font-medium text-gray-700 mb-1">
                                <span class="material-icons text-sm mr-1 text-primary-DEFAULT">code</span>
                                Custom CSS
                            </label>
                            <div class="relative">
                                <div class="absolute top-3 left-0 pl-3 flex items-start pointer-events-none">
                                    <span class="material-icons text-[#004aad] text-base">css</span>
                                </div>
                                <textarea 
                                    id="custom_css" 
                                    name="custom_css" 
                                    rows="4" 
                                    class="w-full text-xs border-gray-300 rounded-[1px] pl-12 py-3 focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50 font-mono"
                                    :class="{'bg-gray-50': !isEditing}"
                                    :disabled="!isEditing"
                                >{{ old('custom_css', $config->custom_css ?? '/* Custom CSS code */
.custom-header {
  background: linear-gradient(to right, var(--primary-color), var(--secondary-color));
}') }}</textarea>
                            </div>
                            <p class="mt-1 text-[10px] text-gray-500">Custom CSS to apply to the application (use with caution)</p>
                        </div>
                    </div>
                    
                    <div>
                        <h2 class="text-sm font-semibold text-gray-700 mb-4 flex items-center">
                            <span class="material-icons text-primary-DEFAULT mr-2">view_quilt</span>
                            Layout Settings
                        </h2>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                            <!-- Sidebar Default State -->
                            <div>
                                <label for="sidebar_default" class="flex items-center text-xs font-medium text-gray-700 mb-1">
                                    <span class="material-icons text-sm mr-1 text-primary-DEFAULT">view_sidebar</span>
                                    Sidebar Default State
                                </label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <span class="material-icons text-[#004aad] text-base">view_sidebar</span>
                                    </div>
                                    <select 
                                        id="sidebar_default" 
                                        name="sidebar_default" 
                                        class="w-full text-xs border-gray-300 rounded-[1px] pl-12 focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50"
                                        :class="{'bg-gray-50': !isEditing}"
                                        :disabled="!isEditing"
                                    >
                                        <option value="expanded" {{ (old('sidebar_default', $config->sidebar_default ?? 'expanded') == 'expanded') ? 'selected' : '' }}>Expanded</option>
                                        <option value="collapsed" {{ (old('sidebar_default', $config->sidebar_default ?? 'expanded') == 'collapsed') ? 'selected' : '' }}>Collapsed</option>
                                        <option value="remember" {{ (old('sidebar_default', $config->sidebar_default ?? 'expanded') == 'remember') ? 'selected' : '' }}>Remember Last State</option>
                                    </select>
                                </div>
                                <p class="mt-1 text-[10px] text-gray-500">Default sidebar state when user first logs in</p>
                            </div>
                            
                            <!-- Table Row Density -->
                            <div>
                                <label for="table_density" class="flex items-center text-xs font-medium text-gray-700 mb-1">
                                    <span class="material-icons text-sm mr-1 text-primary-DEFAULT">density_medium</span>
                                    Table Row Density
                                </label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <span class="material-icons text-[#004aad] text-base">table_rows</span>
                                    </div>
                                    <select 
                                        id="table_density" 
                                        name="table_density" 
                                        class="w-full text-xs border-gray-300 rounded-[1px] pl-12 focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50"
                                        :class="{'bg-gray-50': !isEditing}"
                                        :disabled="!isEditing"
                                    >
                                        <option value="compact" {{ (old('table_density', $config->table_density ?? 'default') == 'compact') ? 'selected' : '' }}>Compact</option>
                                        <option value="default" {{ (old('table_density', $config->table_density ?? 'default') == 'default') ? 'selected' : '' }}>Default</option>
                                        <option value="comfortable" {{ (old('table_density', $config->table_density ?? 'default') == 'comfortable') ? 'selected' : '' }}>Comfortable</option>
                                    </select>
                                </div>
                                <p class="mt-1 text-[10px] text-gray-500">Spacing density for table rows</p>
                            </div>
                        </div>
                        
                        <div class="mt-4">
                            <label class="flex items-center">
                                <input type="hidden" name="show_welcome_message" value="0">
                                <input 
                                    type="checkbox" 
                                    name="show_welcome_message"
                                    value="1"
                                    class="rounded border-gray-300 text-primary-DEFAULT focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50" 
                                    {{ (old('show_welcome_message', $config->show_welcome_message ?? true)) ? 'checked' : '' }}
                                    :disabled="!isEditing"
                                >
                                <span class="ml-2 text-xs text-gray-700">Show welcome message on dashboard</span>
                            </label>
                        </div>
                        
                        <div class="mt-3">
                            <label class="flex items-center">
                                <input type="hidden" name="show_help_icons" value="0">
                                <input 
                                    type="checkbox" 
                                    name="show_help_icons"
                                    value="1"
                                    class="rounded border-gray-300 text-primary-DEFAULT focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50" 
                                    {{ (old('show_help_icons', $config->show_help_icons ?? true)) ? 'checked' : '' }}
                                    :disabled="!isEditing"
                                >
                                <span class="ml-2 text-xs text-gray-700">Show help icons beside form fields</span>
                            </label>
                        </div>
                    </div>
                </div>
                
                <!-- Notifications Tab -->
                <div x-show="activeTab === 'notifications'" class="space-y-4">
                    <div class="bg-blue-50 border border-blue-100 rounded-md p-3 mb-4">
                        <div class="flex">
                            <span class="material-icons text-blue-600 mr-2">info</span>
                            <div class="text-xs text-blue-700">
                                <p class="font-medium">Notifications Configuration</p>
                                <p class="mt-1">Configure system notifications, alerts, and reminders.</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="border-b border-gray-200 pb-5">
                        <h2 class="text-sm font-semibold text-gray-700 mb-4 flex items-center">
                            <span class="material-icons text-primary-DEFAULT mr-2">email</span>
                            Email Notifications
                        </h2>
                        
                        <div class="grid grid-cols-1 gap-3 mb-4">
                            <div class="flex justify-between items-center py-2 border-b border-gray-100">
                                <div class="flex items-center">
                                    <span class="material-icons text-primary-DEFAULT mr-2 text-sm">person_add</span>
                                    <div>
                                        <p class="text-xs font-medium text-gray-700">New User Registration</p>
                                        <p class="text-[10px] text-gray-500">Send email when a new user registers</p>
                                    </div>
                                </div>
                                <div>
                                    <label class="relative inline-flex items-center cursor-pointer">
                                        <input type="checkbox" name="email_new_user_registration" class="sr-only peer" {{ (old('email_new_user_registration', $config->email_new_user_registration ?? true)) ? 'checked' : '' }} :disabled="!isEditing">
                                        <div class="w-9 h-5 bg-gray-200 peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-primary-light rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-primary-DEFAULT"></div>
                                    </label>
                                </div>
                            </div>
                            
                            <div class="flex justify-between items-center py-2 border-b border-gray-100">
                                <div class="flex items-center">
                                    <span class="material-icons text-primary-DEFAULT mr-2 text-sm">event_available</span>
                                    <div>
                                        <p class="text-xs font-medium text-gray-700">Event Registration</p>
                                        <p class="text-[10px] text-gray-500">Send confirmation email after event registration</p>
                                    </div>
                                </div>
                                <div>
                                    <label class="relative inline-flex items-center cursor-pointer">
                                        <input type="checkbox" checked class="sr-only peer" :disabled="!isEditing">
                                        <div class="w-9 h-5 bg-gray-200 peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-primary-light rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-primary-DEFAULT"></div>
                                    </label>
                                </div>
                            </div>
                            
                            <div class="flex justify-between items-center py-2 border-b border-gray-100">
                                <div class="flex items-center">
                                    <span class="material-icons text-primary-DEFAULT mr-2 text-sm">event_upcoming</span>
                                    <div>
                                        <p class="text-xs font-medium text-gray-700">Event Reminder</p>
                                        <p class="text-[10px] text-gray-500">Send reminder email before event starts</p>
                                    </div>
                                </div>
                                <div>
                                    <label class="relative inline-flex items-center cursor-pointer">
                                        <input type="checkbox" checked class="sr-only peer" :disabled="!isEditing">
                                        <div class="w-9 h-5 bg-gray-200 peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-primary-light rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-primary-DEFAULT"></div>
                                    </label>
                                </div>
                            </div>
                            
                            <div class="flex justify-between items-center py-2 border-b border-gray-100">
                                <div class="flex items-center">
                                    <span class="material-icons text-primary-DEFAULT mr-2 text-sm">workspace_premium</span>
                                    <div>
                                        <p class="text-xs font-medium text-gray-700">Certificate Generated</p>
                                        <p class="text-[10px] text-gray-500">Send email when a certificate is generated</p>
                                    </div>
                                </div>
                                <div>
                                    <label class="relative inline-flex items-center cursor-pointer">
                                        <input type="checkbox" checked class="sr-only peer" :disabled="!isEditing">
                                        <div class="w-9 h-5 bg-gray-200 peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-primary-light rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-primary-DEFAULT"></div>
                                    </label>
                                </div>
                            </div>
                            
                            <div class="flex justify-between items-center py-2 border-b border-gray-100">
                                <div class="flex items-center">
                                    <span class="material-icons text-primary-DEFAULT mr-2 text-sm">password</span>
                                    <div>
                                        <p class="text-xs font-medium text-gray-700">Password Reset</p>
                                        <p class="text-[10px] text-gray-500">Send email for password reset requests</p>
                                    </div>
                                </div>
                                <div>
                                    <label class="relative inline-flex items-center cursor-pointer">
                                        <input type="checkbox" checked class="sr-only peer" :disabled="!isEditing">
                                        <div class="w-9 h-5 bg-gray-200 peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-primary-light rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-primary-DEFAULT"></div>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="border-b border-gray-200 pb-5">
                        <h2 class="text-sm font-semibold text-gray-700 mb-4 flex items-center">
                            <span class="material-icons text-primary-DEFAULT mr-2">sms</span>
                            SMS Notifications
                        </h2>
                        
                        <div class="grid grid-cols-1 gap-3 mb-4">
                            <div class="flex justify-between items-center py-2 border-b border-gray-100">
                                <div class="flex items-center">
                                    <span class="material-icons text-primary-DEFAULT mr-2 text-sm">event_available</span>
                                    <div>
                                        <p class="text-xs font-medium text-gray-700">Event Registration</p>
                                        <p class="text-[10px] text-gray-500">Send SMS confirmation after registration</p>
                                    </div>
                                </div>
                                <div>
                                    <label class="relative inline-flex items-center cursor-pointer">
                                        <input type="checkbox" class="sr-only peer" :disabled="!isEditing">
                                        <div class="w-9 h-5 bg-gray-200 peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-primary-light rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-primary-DEFAULT"></div>
                                    </label>
                                </div>
                            </div>
                            
                            <div class="flex justify-between items-center py-2 border-b border-gray-100">
                                <div class="flex items-center">
                                    <span class="material-icons text-primary-DEFAULT mr-2 text-sm">event_upcoming</span>
                                    <div>
                                        <p class="text-xs font-medium text-gray-700">Event Reminder</p>
                                        <p class="text-[10px] text-gray-500">Send SMS reminder before event starts</p>
                                    </div>
                                </div>
                                <div>
                                    <label class="relative inline-flex items-center cursor-pointer">
                                        <input type="checkbox" class="sr-only peer" :disabled="!isEditing">
                                        <div class="w-9 h-5 bg-gray-200 peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-primary-light rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-primary-DEFAULT"></div>
                                    </label>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mt-4">
                            <label for="sms_reminder_hours" class="flex items-center text-xs font-medium text-gray-700 mb-1">
                                <span class="material-icons text-sm mr-1 text-primary-DEFAULT">schedule</span>
                                SMS Reminder Time (hours before event)
                            </label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <span class="material-icons text-[#004aad] text-base">access_time</span>
                                </div>
                                <input 
                                    type="number" 
                                    id="sms_reminder_hours" 
                                    name="sms_reminder_hours" 
                                    value="{{ old('sms_reminder_hours', $config->sms_reminder_hours ?? 24) }}" 
                                    min="1" 
                                    max="72" 
                                    class="w-full text-xs border-gray-300 rounded-[1px] pl-12 focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50"
                                    :class="{'bg-gray-50': !isEditing}"
                                    :disabled="!isEditing"
                                >
                            </div>
                            <p class="mt-1 text-[10px] text-gray-500">How many hours before the event to send SMS reminders</p>
                        </div>
                    </div>
                    
                    <div>
                        <h2 class="text-sm font-semibold text-gray-700 mb-4 flex items-center">
                            <span class="material-icons text-primary-DEFAULT mr-2">admin_panel_settings</span>
                            Admin Notifications
                        </h2>
                        
                        <div class="grid grid-cols-1 gap-3 mb-4">
                            <div class="flex justify-between items-center py-2 border-b border-gray-100">
                                <div class="flex items-center">
                                    <span class="material-icons text-primary-DEFAULT mr-2 text-sm">warning</span>
                                    <div>
                                        <p class="text-xs font-medium text-gray-700">System Errors</p>
                                        <p class="text-[10px] text-gray-500">Notify admins about system errors</p>
                                    </div>
                                </div>
                                <div>
                                    <label class="relative inline-flex items-center cursor-pointer">
                                        <input type="checkbox" checked class="sr-only peer" :disabled="!isEditing">
                                        <div class="w-9 h-5 bg-gray-200 peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-primary-light rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-primary-DEFAULT"></div>
                                    </label>
                                </div>
                            </div>
                            
                            <div class="flex justify-between items-center py-2 border-b border-gray-100">
                                <div class="flex items-center">
                                    <span class="material-icons text-primary-DEFAULT mr-2 text-sm">new_releases</span>
                                    <div>
                                        <p class="text-xs font-medium text-gray-700">New Registrations</p>
                                        <p class="text-[10px] text-gray-500">Notify admins about new user registrations</p>
                                    </div>
                                </div>
                                <div>
                                    <label class="relative inline-flex items-center cursor-pointer">
                                        <input type="checkbox" class="sr-only peer" :disabled="!isEditing">
                                        <div class="w-9 h-5 bg-gray-200 peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-primary-light rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-primary-DEFAULT"></div>
                                    </label>
                                </div>
                            </div>
                            
                            <div class="flex justify-between items-center py-2 border-b border-gray-100">
                                <div class="flex items-center">
                                    <span class="material-icons text-primary-DEFAULT mr-2 text-sm">security</span>
                                    <div>
                                        <p class="text-xs font-medium text-gray-700">Security Alerts</p>
                                        <p class="text-[10px] text-gray-500">Notify admins about security-related events</p>
                                    </div>
                                </div>
                                <div>
                                    <label class="relative inline-flex items-center cursor-pointer">
                                        <input type="checkbox" checked class="sr-only peer" :disabled="!isEditing">
                                        <div class="w-9 h-5 bg-gray-200 peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-primary-light rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-primary-DEFAULT"></div>
                                    </label>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mt-4">
                            <label for="admin_notification_email" class="flex items-center text-xs font-medium text-gray-700 mb-1">
                                <span class="material-icons text-sm mr-1 text-primary-DEFAULT">mark_email_read</span>
                                Admin Notification Email
                            </label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <span class="material-icons text-[#004aad] text-base">email</span>
                                </div>
                                <input 
                                    type="email" 
                                    id="admin_notification_email" 
                                    name="admin_notification_email" 
                                    value="{{ old('admin_notification_email', $config->admin_notification_email ?? '') }}" 
                                    class="w-full text-xs border-gray-300 rounded-[1px] pl-12 focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50"
                                    :class="{'bg-gray-50': !isEditing}"
                                    :disabled="!isEditing"
                                >
                            </div>
                            <p class="mt-1 text-[10px] text-gray-500">Email address for admin notifications</p>
                        </div>
                    </div>
                </div>
                
                <!-- API Tab -->
                <div x-show="activeTab === 'api'" class="space-y-4">
                    <div class="bg-blue-50 border border-blue-100 rounded-md p-3 mb-4">
                        <div class="flex">
                            <span class="material-icons text-blue-600 mr-2">info</span>
                            <div class="text-xs text-blue-700">
                                <p class="font-medium">API & Integrations Configuration</p>
                                <p class="mt-1">Manage API settings and third-party integrations.</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="border-b border-gray-200 pb-5">
                        <h2 class="text-sm font-semibold text-gray-700 mb-4 flex items-center">
                            <span class="material-icons text-primary-DEFAULT mr-2">api</span>
                            API Settings
                        </h2>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                            <!-- API Status -->
                            <div>
                                <label class="flex items-center text-xs font-medium text-gray-700 mb-1">
                                    <span class="material-icons text-sm mr-1 text-primary-DEFAULT">toggle_on</span>
                                    API Status
                                </label>
                                <div class="flex items-center mt-2">
                                    <label class="inline-flex items-center mr-4">
                                        <input 
                                            type="radio" 
                                            name="api_status" 
                                            value="enabled" 
                                            class="text-primary-DEFAULT focus:ring-primary-light" 
                                            {{ (old('api_status', $config->api_status ?? 'enabled') == 'enabled') ? 'checked' : '' }}
                                            :disabled="!isEditing"
                                        >
                                        <span class="ml-2 text-xs text-gray-700">Enabled</span>
                                    </label>
                                    <label class="inline-flex items-center">
                                        <input 
                                            type="radio" 
                                            name="api_status" 
                                            value="disabled" 
                                            class="text-primary-DEFAULT focus:ring-primary-light"
                                            {{ (old('api_status', $config->api_status ?? 'enabled') == 'disabled') ? 'checked' : '' }}
                                            :disabled="!isEditing"
                                        >
                                        <span class="ml-2 text-xs text-gray-700">Disabled</span>
                                    </label>
                                </div>
                                <p class="mt-1 text-[10px] text-gray-500">Enable/disable the REST API</p>
                            </div>
                            
                            <!-- Rate Limiting -->
                            <div>
                                <label for="api_rate_limit" class="flex items-center text-xs font-medium text-gray-700 mb-1">
                                    <span class="material-icons text-sm mr-1 text-primary-DEFAULT">speed</span>
                                    Rate Limit (requests per minute)
                                </label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <span class="material-icons text-[#004aad] text-base">data_usage</span>
                                    </div>
                                                                    <input 
                                    type="number" 
                                    id="api_rate_limit" 
                                    name="api_rate_limit" 
                                    value="{{ old('api_rate_limit', $config->api_rate_limit ?? 60) }}" 
                                    min="10" 
                                    class="w-full text-xs border-gray-300 rounded-[1px] pl-12 focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50"
                                    :class="{'bg-gray-50': !isEditing}"
                                    :disabled="!isEditing"
                                >
                                </div>
                                <p class="mt-1 text-[10px] text-gray-500">Maximum number of API requests per minute per client</p>
                            </div>
                        </div>
                        
                        <div class="mt-4">
                            <label class="flex items-center">
                                <input type="hidden" name="enable_api_keys" value="0">
                                <input 
                                    type="checkbox" 
                                    name="enable_api_keys"
                                    value="1"
                                    class="rounded border-gray-300 text-primary-DEFAULT focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50" 
                                    {{ (old('enable_api_keys', $config->enable_api_keys ?? true)) ? 'checked' : '' }}
                                    :disabled="!isEditing"
                                >
                                <span class="ml-2 text-xs text-gray-700">Require API keys for access</span>
                            </label>
                        </div>
                        
                        <div class="mt-3">
                            <label class="flex items-center">
                                <input type="hidden" name="enable_oauth" value="0">
                                <input 
                                    type="checkbox" 
                                    name="enable_oauth"
                                    value="1"
                                    class="rounded border-gray-300 text-primary-DEFAULT focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50" 
                                    {{ (old('enable_oauth', $config->enable_oauth ?? true)) ? 'checked' : '' }}
                                    :disabled="!isEditing"
                                >
                                <span class="ml-2 text-xs text-gray-700">Enable OAuth 2.0 authorization</span>
                            </label>
                        </div>
                        
                        <div class="mt-3">
                            <label class="flex items-center">
                                <input type="hidden" name="api_cors_enabled" value="0">
                                <input 
                                    type="checkbox" 
                                    name="api_cors_enabled"
                                    value="1"
                                    class="rounded border-gray-300 text-primary-DEFAULT focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50" 
                                    {{ (old('api_cors_enabled', $config->api_cors_enabled ?? true)) ? 'checked' : '' }}
                                    :disabled="!isEditing"
                                >
                                <span class="ml-2 text-xs text-gray-700">Allow CORS for API requests</span>
                            </label>
                        </div>
                        
                        <div class="mt-4">
                            <label for="cors_domains" class="flex items-center text-xs font-medium text-gray-700 mb-1">
                                <span class="material-icons text-sm mr-1 text-primary-DEFAULT">public</span>
                                CORS Allowed Domains
                            </label>
                            <div class="relative">
                                <div class="absolute top-3 left-0 pl-3 flex items-start pointer-events-none">
                                    <span class="material-icons text-[#004aad] text-base">link</span>
                                </div>
                                <textarea 
                                    id="cors_domains" 
                                    name="cors_domains" 
                                    rows="2" 
                                    class="w-full text-xs border-gray-300 rounded-[1px] pl-12 py-3 focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50"
                                    :class="{'bg-gray-50': !isEditing}"
                                    :disabled="!isEditing"
                                >{{ old('cors_domains', $config->cors_domains ?? 'https://example.com, https://*.sijilevents.com') }}</textarea>
                            </div>
                            <p class="mt-1 text-[10px] text-gray-500">Domains allowed to make cross-origin API requests (comma separated)</p>
                        </div>
                    </div>
                    
                    <div class="border-b border-gray-200 pb-5">
                        <h2 class="text-sm font-semibold text-gray-700 mb-4 flex items-center">
                            <span class="material-icons text-primary-DEFAULT mr-2">integration_instructions</span>
                            Third-Party Integrations
                        </h2>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-4 mb-4">
                            <!-- Google Calendar Integration -->
                            <div class="flex justify-between items-center py-2 border border-gray-200 rounded-md px-3">
                                <div class="flex items-center">
                                    <svg class="w-5 h-5 mr-2" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" preserveAspectRatio="xMidYMid">
                                        <path d="M21.5 5.25H17.5V3.75C17.5 3.34 17.16 3 16.75 3C16.34 3 16 3.34 16 3.75V5.25H8V3.75C8 3.34 7.66 3 7.25 3C6.84 3 6.5 3.34 6.5 3.75V5.25H2.5C1.12 5.25 0 6.37 0 7.75V19.75C0 21.13 1.12 22.25 2.5 22.25H21.5C22.88 22.25 24 21.13 24 19.75V7.75C24 6.37 22.88 5.25 21.5 5.25ZM22.5 19.75C22.5 20.3 22.05 20.75 21.5 20.75H2.5C1.95 20.75 1.5 20.3 1.5 19.75V10H22.5V19.75ZM22.5 8.5H1.5V7.75C1.5 7.2 1.95 6.75 2.5 6.75H6.5V8.25C6.5 8.66 6.84 9 7.25 9C7.66 9 8 8.66 8 8.25V6.75H16V8.25C16 8.66 16.34 9 16.75 9C17.16 9 17.5 8.66 17.5 8.25V6.75H21.5C22.05 6.75 22.5 7.2 22.5 7.75V8.5Z" fill="#4285F4"/>
                                        <path d="M6 16H4.5V17.5H6V16Z" fill="#4285F4"/>
                                        <path d="M9.5 16H8V17.5H9.5V16Z" fill="#4285F4"/>
                                        <path d="M13 16H11.5V17.5H13V16Z" fill="#4285F4"/>
                                        <path d="M16.5 16H15V17.5H16.5V16Z" fill="#4285F4"/>
                                        <path d="M20 16H18.5V17.5H20V16Z" fill="#4285F4"/>
                                        <path d="M6 12.5H4.5V14H6V12.5Z" fill="#4285F4"/>
                                        <path d="M9.5 12.5H8V14H9.5V12.5Z" fill="#4285F4"/>
                                        <path d="M13 12.5H11.5V14H13V12.5Z" fill="#4285F4"/>
                                        <path d="M16.5 12.5H15V14H16.5V12.5Z" fill="#4285F4"/>
                                        <path d="M20 12.5H18.5V14H20V12.5Z" fill="#4285F4"/>
                                    </svg>
                                    <div>
                                        <p class="text-xs font-medium text-gray-700">Google Calendar</p>
                                        <p class="text-[10px] text-gray-500">Sync events with Google Calendar</p>
                                    </div>
                                </div>
                                <div>
                                    <label class="relative inline-flex items-center cursor-pointer">
                                        <input type="checkbox" class="sr-only peer" :disabled="!isEditing">
                                        <div class="w-9 h-5 bg-gray-200 peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-primary-light rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-primary-DEFAULT"></div>
                                    </label>
                                </div>
                            </div>
                            
                            <!-- Microsoft Teams Integration -->
                            <div class="flex justify-between items-center py-2 border border-gray-200 rounded-md px-3">
                                <div class="flex items-center">
                                    <svg class="w-5 h-5 mr-2" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 2228.833 2073.333">
                                        <path d="M1554.637,777.5h575.713c54.391,0,98.483,44.092,98.483,98.483c0,0,0,0,0,0v524.398c0,199.901-162.051,361.952-361.952,361.952h0h-1.711c-199.901,0.028-361.975-162-362.004-361.901c0-0.017,0-0.034,0-0.052V828.971C1503.167,800.544,1526.211,777.5,1554.637,777.5L1554.637,777.5z" fill="#5059C9"/>
                                        <circle cx="1943.75" cy="440.583" r="233.25" fill="#5059C9"/>
                                        <circle cx="1218.083" cy="336.917" r="336.917" fill="#7B83EB"/>
                                        <path d="M1667.323,777.5H717.01c-53.743,1.33-96.257,45.931-95.01,99.676v598.105c-7.505,322.519,247.657,590.16,570.167,598.053c322.51-7.893,577.671-275.534,570.167-598.053V877.176C1763.58,823.431,1721.066,778.83,1667.323,777.5z" opacity=".1"/>
                                        <path d="M1244,777.5H707.167c-54.667,0-98.5,43.833-98.5,98.5v599.667c-7.5,322.167,247.333,589.5,569.5,597.333c104.894-2.456,206.059-32.498,292.5-85.5c-256.011-62.569-435.323-295.956-435.167-558.833V828.971C1035.5,800.539,1058.539,777.5,1086.971,777.5l0,0H1244z" fill="#7B83EB"/>
                                    </svg>
                                    <div>
                                        <p class="text-xs font-medium text-gray-700">Microsoft Teams</p>
                                        <p class="text-[10px] text-gray-500">Send notifications to Teams</p>
                                    </div>
                                </div>
                                <div>
                                    <label class="relative inline-flex items-center cursor-pointer">
                                        <input type="checkbox" class="sr-only peer" :disabled="!isEditing">
                                        <div class="w-9 h-5 bg-gray-200 peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-primary-light rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-primary-DEFAULT"></div>
                                    </label>
                                </div>
                            </div>
                            
                            <!-- Stripe Payment Integration -->
                            <div class="flex justify-between items-center py-2 border border-gray-200 rounded-md px-3">
                                <div class="flex items-center">
                                    <svg class="w-5 h-5 mr-2" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24">
                                        <path d="M13.479 9.883c-1.626-.604-2.512-.931-2.512-1.5 0-.522.414-.859 1.171-.859.837 0 1.672.332 2.512.93l.393-1.404c-.672-.431-1.672-.836-2.904-.836-1.626 0-2.809.859-2.809 2.286 0 1.508 1.253 2.147 2.879 2.689 1.44.522 2.009.857 2.009 1.5 0 .567-.509.93-1.291.93-.859 0-1.833-.363-2.671-1.002l-.404 1.433c.836.604 1.947.999 3.076.999 1.903 0 3.122-.882 3.122-2.332 0-1.567-1.183-2.148-2.571-2.834zm6.521-2.883v-2h-2.606l-2.399 1.873.6.837 1.528-.95v8.24h2.878v-8zm-14 9c-3.314 0-6-2.686-6-6s2.686-6 6-6c1.537 0 2.939.585 4 1.542v-1.542h2.667v12h-2.667v-1.542c-1.061.957-2.463 1.542-4 1.542zm0-2c2.206 0 4-1.794 4-4s-1.794-4-4-4-4 1.794-4 4 1.794 4 4 4z" fill="#6772E5"/>
                                    </svg>
                                    <div>
                                        <p class="text-xs font-medium text-gray-700">Stripe Payments</p>
                                        <p class="text-[10px] text-gray-500">Process payments via Stripe</p>
                                    </div>
                                </div>
                                <div>
                                    <label class="relative inline-flex items-center cursor-pointer">
                                        <input type="checkbox" checked class="sr-only peer" :disabled="!isEditing">
                                        <div class="w-9 h-5 bg-gray-200 peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-primary-light rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-primary-DEFAULT"></div>
                                    </label>
                                </div>
                            </div>
                            
                            <!-- Zoom Integration -->
                            <div class="flex justify-between items-center py-2 border border-gray-200 rounded-md px-3">
                                <div class="flex items-center">
                                    <svg class="w-5 h-5 mr-2" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                                        <path d="M0,8.164v7.672c0,2.511,3.753,3.764,7.507,3.764V4.401C3.753,4.401,0,5.655,0,8.164z" fill="#4A8CFF"/>
                                        <path d="M16.493,4.401v15.198c3.753,0,7.507-1.254,7.507-3.764V8.164C24,5.655,20.247,4.401,16.493,4.401z" fill="#4A8CFF"/>
                                        <path d="M7.507,4.401v15.198h8.986V4.401H7.507z" fill="#4A8CFF"/>
                                    </svg>
                                    <div>
                                        <p class="text-xs font-medium text-gray-700">Zoom Meetings</p>
                                        <p class="text-[10px] text-gray-500">Create virtual event meetings</p>
                                    </div>
                                </div>
                                <div>
                                    <label class="relative inline-flex items-center cursor-pointer">
                                        <input type="checkbox" checked class="sr-only peer" :disabled="!isEditing">
                                        <div class="w-9 h-5 bg-gray-200 peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-primary-light rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-primary-DEFAULT"></div>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div>
                        <h2 class="text-sm font-semibold text-gray-700 mb-4 flex items-center">
                            <span class="material-icons text-primary-DEFAULT mr-2">webhook</span>
                            Webhooks
                        </h2>
                        
                        <div class="mt-4">
                            <label class="flex items-center">
                                <input type="hidden" name="enable_webhooks" value="0">
                                <input 
                                    type="checkbox" 
                                    name="enable_webhooks"
                                    value="1"
                                    class="rounded border-gray-300 text-primary-DEFAULT focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50" 
                                    {{ (old('enable_webhooks', $config->enable_webhooks ?? true)) ? 'checked' : '' }}
                                    :disabled="!isEditing"
                                >
                                <span class="ml-2 text-xs text-gray-700">Enable webhooks</span>
                            </label>
                            <p class="mt-1 text-[10px] ml-6 text-gray-500">Allow external systems to receive event notifications</p>
                        </div>
                        
                        <div class="mt-4">
                            <label for="webhook_secret" class="flex items-center text-xs font-medium text-gray-700 mb-1">
                                <span class="material-icons text-sm mr-1 text-primary-DEFAULT">key</span>
                                Webhook Secret
                            </label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <span class="material-icons text-[#004aad] text-base">password</span>
                                </div>
                                <div class="flex">
                                    <input 
                                        type="password" 
                                        id="webhook_secret" 
                                        name="webhook_secret" 
                                        value="{{ old('webhook_secret', $config->webhook_secret ?? 'wh_sec_1a2b3c4d5e6f') }}" 
                                        class="w-full text-xs border-gray-300 rounded-[1px] pl-12 focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50"
                                        :class="{'bg-gray-50': !isEditing}"
                                        :disabled="!isEditing"
                                    >
                                    <button 
                                        type="button"
                                        class="ml-2 bg-gray-100 hover:bg-gray-200 text-xs text-gray-700 px-3 rounded"
                                        :disabled="!isEditing"
                                    >
                                        Regenerate
                                    </button>
                                </div>
                            </div>
                            <p class="mt-1 text-[10px] text-gray-500">Secret key used to validate webhook requests</p>
                        </div>
                        
                        <div class="mt-4">
                            <label for="webhook_events" class="flex items-center text-xs font-medium text-gray-700 mb-1">
                                <span class="material-icons text-sm mr-1 text-primary-DEFAULT">event</span>
                                Webhook Events
                            </label>
                            <div class="relative">
                                <div class="absolute top-3 left-0 pl-3 flex items-start pointer-events-none">
                                    <span class="material-icons text-[#004aad] text-base">list</span>
                                </div>
                                <textarea 
                                    id="webhook_events" 
                                    name="webhook_events" 
                                    rows="3" 
                                    class="w-full text-xs border-gray-300 rounded-[1px] pl-12 py-3 focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50"
                                    :class="{'bg-gray-50': !isEditing}"
                                    :disabled="!isEditing"
                                >{{ old('webhook_events', $config->webhook_events ?? 'event.created, event.updated, registration.completed, certificate.generated, attendance.recorded') }}</textarea>
                            </div>
                            <p class="mt-1 text-[10px] text-gray-500">Events that will trigger webhook notifications (comma separated)</p>
                        </div>
                    </div>
                </div>
                
                <!-- Form Submit Buttons -->
                <div class="mt-6 flex justify-end space-x-3">
                    <button 
                        type="button" 
                        @click="isEditing = !isEditing" 
                        x-show="!isEditing"
                        class="bg-gradient-to-r from-blue-600 to-blue-500 hover:from-blue-700 hover:to-blue-600"
                        x-transition
                    >
                        <span class="text-white px-3 py-1 rounded shadow-sm font-medium flex items-center text-xs transition-colors duration-200 ease-in-out">
                            <span class="material-icons text-xs mr-1">edit</span>
                            <span>Edit Settings</span>
                        </span>
                    </button>
                    
                    <button 
                        type="submit" 
                        x-show="isEditing"
                        class="bg-gradient-to-r from-green-600 to-green-500 hover:from-green-700 hover:to-green-600"
                        x-transition
                    >
                        <span class="text-white px-3 py-1 rounded shadow-sm font-medium flex items-center text-xs transition-colors duration-200 ease-in-out">
                            <span class="material-icons text-xs mr-1">save</span>
                            <span>Save Changes</span>
                        </span>
                    </button>
                </div>
            </form>
        </div>
    </div>
    
    <script>
        document.addEventListener('alpine:init', () => {
            // Alpine.js configuration if needed
        })

        // Handle form submission
        document.addEventListener('DOMContentLoaded', function() {
            console.log('DOM loaded, looking for form...');
            const form = document.querySelector('form[action*="global-config"]');
            
            if (form) {
                console.log('Form found:', form.action);
                form.addEventListener('submit', function(e) {
                    e.preventDefault();
                    console.log('Form submitted!');
                    
                    const formData = new FormData(form);
                    
                    // Debug: Log form data
                    console.log('Form data:');
                    for (let [key, value] of formData.entries()) {
                        console.log(key + ': ' + value);
                    }
                    
                    const submitButton = document.querySelector('button[type="submit"]');
                    if (!submitButton) {
                        console.error('Submit button not found!');
                        return;
                    }
                    const originalText = submitButton.innerHTML;
                    
                    // Show loading state
                    submitButton.innerHTML = '<span class="text-white px-3 py-1 rounded shadow-sm font-medium flex items-center text-xs transition-colors duration-200 ease-in-out"><span class="material-icons text-xs mr-1">hourglass_empty</span> Saving...</span>';
                    submitButton.disabled = true;
                    
                    fetch(form.action, {
                        method: 'POST',
                        body: formData,
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    })
                    .then(response => {
                        console.log('Response status:', response.status);
                        return response.json();
                    })
                    .then(data => {
                        console.log('Response data:', data);
                        if (data.success) {
                            // Show success message
                            alert('Configuration saved successfully!');
                            
                            // Reset edit mode
                            const editButton = document.querySelector('button[onclick*="isEditing"]');
                            if (editButton) {
                                editButton.click();
                            }
                            
                            // Refresh page to show updated data
                            window.location.reload();
                        } else if (data.errors) {
                            // Show all validation errors
                            let errorMsg = 'Validation failed:\n';
                            for (const [field, messages] of Object.entries(data.errors)) {
                                errorMsg += `- ${field}: ${messages.join(', ')}\n`;
                            }
                            alert(errorMsg);
                        } else {
                            // Show error message
                            alert('Error: ' + (data.message || 'Failed to save configuration'));
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('Error: Failed to save configuration. Please try again.');
                    })
                    .finally(() => {
                        // Restore button state
                        submitButton.innerHTML = originalText;
                        submitButton.disabled = false;
                    });
                });
            }
        });
    </script>
</x-app-layout> 