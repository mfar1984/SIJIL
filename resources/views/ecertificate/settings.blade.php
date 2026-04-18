<x-app-layout>
    <x-slot name="breadcrumb">
        <span>PWA Management</span>
        <span class="mx-2">/</span>
        <span>Event Settings</span>
    </x-slot>

    <x-slot name="title">PWA Settings</x-slot>

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

    <div class="bg-white rounded shadow-md border border-gray-300">
        <div class="p-6 border-b border-gray-200">
            <div class="flex justify-between items-start">
                <div>
                    <div class="flex items-center">
                        <span class="material-icons-outlined mr-2 text-indigo-500">settings</span>
                        <h1 class="text-xl font-bold text-gray-800">PWA Settings</h1>
                    </div>
                    <p class="text-xs text-gray-500 mt-1 ml-8">Configure PWA access and participant account settings</p>
                </div>
                @can('pwa_settings.update')
                <button form="pwa-settings-form" type="submit" class="px-3 h-[36px] bg-gradient-to-r from-green-600 to-green-500 hover:from-green-700 hover:to-green-600 text-white rounded shadow-sm font-medium flex items-center text-xs transition-colors duration-200 ease-in-out">
                    <span class="material-icons-outlined text-xs mr-1">save</span>
                    Save Settings
                </button>
                @endcan
            </div>
        </div>
        
        @php $canUpdate = auth()->user()->can('pwa_settings.update'); @endphp
        <form id="pwa-settings-form" method="POST" action="{{ route('pwa.settings.update') }}" class="p-6" x-data="{ tab: 'general' }" x-cloak>
            @csrf
            <fieldset {{ $canUpdate ? '' : 'disabled' }}>
            <!-- Settings Tabs -->
            <div class="border-b border-gray-200 mb-4">
                <nav class="flex space-x-6">
                    <button type="button" @click="tab='general'" :class="tab==='general' ? 'border-b-2 border-indigo-500 text-indigo-600' : 'border-b-2 border-transparent text-gray-500 hover:text-gray-700'" class="px-1 py-2 text-xs font-medium">General Settings</button>
                    <button type="button" @click="tab='event'" :class="tab==='event' ? 'border-b-2 border-indigo-500 text-indigo-600' : 'border-b-2 border-transparent text-gray-500 hover:text-gray-700'" class="px-1 py-2 text-xs font-medium">Event Access</button>
                    <button type="button" @click="tab='auto'" :class="tab==='auto' ? 'border-b-2 border-indigo-500 text-indigo-600' : 'border-b-2 border-transparent text-gray-500 hover:text-gray-700'" class="px-1 py-2 text-xs font-medium">Auto-Generation</button>
                    <button type="button" @click="tab='security'" :class="tab==='security' ? 'border-b-2 border-indigo-500 text-indigo-600' : 'border-b-2 border-transparent text-gray-500 hover:text-gray-700'" class="px-1 py-2 text-xs font-medium">Security</button>
                </nav>
            </div>

            <div class="space-y-2">
                <!-- PWA Access Control -->
                <div class="bg-white border border-gray-200 rounded-md shadow-sm" x-show="tab==='general'">
                    <div class="bg-gray-50 border-b border-gray-200 px-4 py-3">
                        <h2 class="text-sm font-semibold text-gray-700 flex items-center">
                            <span class="material-icons-outlined text-primary-DEFAULT mr-2">security</span>
                            PWA Access Control
                        </h2>
                    </div>
                    
                    <div class="p-4">
                        <div class="space-y-3">
                            <div class="flex flex-col md:flex-row md:items-center gap-3">
                                <label class="text-xs font-medium text-gray-700 md:w-40 flex items-center gap-1">
                                    Enable PWA Access
                                    <div class="tooltip-wrapper" x-data="{ show: false }">
                                        <span class="material-icons-outlined text-gray-400 text-sm cursor-help" 
                                              @mouseenter="show = true" 
                                              @mouseleave="show = false">
                                            help_outline
                                        </span>
                                        <div x-show="show" x-transition class="tooltip-content">
                                            Allow participants to access the mobile application
                                        </div>
                                    </div>
                                </label>
                                <div class="flex-1">
                                    <label class="relative inline-flex items-center cursor-pointer">
                                        <input type="checkbox" name="enable_pwa_access" value="1" {{ ($settings->settings['enable_pwa_access'] ?? false) ? 'checked' : '' }} class="sr-only peer">
                                        <div class="w-9 h-5 bg-gray-200 peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-indigo-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[1px] after:left-[1px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-indigo-600"></div>
                                    </label>
                                </div>
                            </div>
                            
                            <div class="flex flex-col md:flex-row md:items-center gap-3">
                                <label class="text-xs font-medium text-gray-700 md:w-40 flex items-center gap-1">
                                    Auto-Create PWA Accounts
                                    <div class="tooltip-wrapper" x-data="{ show: false }">
                                        <span class="material-icons-outlined text-gray-400 text-sm cursor-help" 
                                              @mouseenter="show = true" 
                                              @mouseleave="show = false">
                                            help_outline
                                        </span>
                                        <div x-show="show" x-transition class="tooltip-content">
                                            Automatically create PWA accounts during event registration
                                        </div>
                                    </div>
                                </label>
                                <div class="flex-1">
                                    <label class="relative inline-flex items-center cursor-pointer">
                                        <input type="checkbox" name="auto_create_accounts" value="1" {{ ($settings->settings['auto_create_accounts'] ?? false) ? 'checked' : '' }} class="sr-only peer">
                                        <div class="w-9 h-5 bg-gray-200 peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-indigo-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[1px] after:left-[1px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-indigo-600"></div>
                                    </label>
                                </div>
                            </div>
                            
                            <div class="flex flex-col md:flex-row md:items-center gap-3">
                                <label class="text-xs font-medium text-gray-700 md:w-40 flex items-center gap-1">
                                    Force Password Change
                                    <div class="tooltip-wrapper" x-data="{ show: false }">
                                        <span class="material-icons-outlined text-gray-400 text-sm cursor-help" 
                                              @mouseenter="show = true" 
                                              @mouseleave="show = false">
                                            help_outline
                                        </span>
                                        <div x-show="show" x-transition class="tooltip-content">
                                            Require participants to change password on first login
                                        </div>
                                    </div>
                                </label>
                                <div class="flex-1">
                                    <label class="relative inline-flex items-center cursor-pointer">
                                        <input type="checkbox" name="force_password_change" value="1" {{ ($settings->settings['force_password_change'] ?? false) ? 'checked' : '' }} class="sr-only peer">
                                        <div class="w-9 h-5 bg-gray-200 peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-indigo-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[1px] after:left-[1px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-indigo-600"></div>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Event-Specific Settings -->
                <div class="bg-white border border-gray-200 rounded-md shadow-sm" x-show="tab==='event'">
                    <div class="bg-gray-50 border-b border-gray-200 px-4 py-3">
                        <h2 class="text-sm font-semibold text-gray-700 flex items-center">
                            <span class="material-icons-outlined text-primary-DEFAULT mr-2">event</span>
                            Event-Specific Settings
                        </h2>
                    </div>
                    
                    <div class="p-4">
                        <div class="space-y-3">
                            <div class="flex flex-col md:flex-row md:items-center gap-3">
                                <label class="text-xs font-medium text-gray-700 md:w-40 flex items-center gap-1">
                                    Default PWA Access
                                    <div class="tooltip-wrapper" x-data="{ show: false }">
                                        <span class="material-icons-outlined text-gray-400 text-sm cursor-help" 
                                              @mouseenter="show = true" 
                                              @mouseleave="show = false">
                                            help_outline
                                        </span>
                                        <div x-show="show" x-transition class="tooltip-content">
                                            Default PWA access setting for new events
                                        </div>
                                    </div>
                                </label>
                                <div class="flex-1">
                                    <select class="w-full px-2 py-1 text-xs border border-gray-300 rounded focus:ring focus:ring-indigo-200 focus:border-indigo-300 bg-white">
                                        <option>Enabled by default</option>
                                        <option>Disabled by default</option>
                                        <option>Ask organizer during event creation</option>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="flex flex-col md:flex-row md:items-center gap-3">
                                <label class="text-xs font-medium text-gray-700 md:w-40 flex items-center gap-1">
                                    Checkbox Label
                                    <div class="tooltip-wrapper" x-data="{ show: false }">
                                        <span class="material-icons-outlined text-gray-400 text-sm cursor-help" 
                                              @mouseenter="show = true" 
                                              @mouseleave="show = false">
                                            help_outline
                                        </span>
                                        <div x-show="show" x-transition class="tooltip-content">
                                            This text will appear on the event registration form
                                        </div>
                                    </div>
                                </label>
                                <div class="flex-1">
                                    <input type="text" value="Enable E-Certificate Online mobile access" class="w-full px-2 py-1 text-xs border border-gray-300 rounded focus:ring focus:ring-indigo-200 focus:border-indigo-300">
                                </div>
                            </div>
                            
                            <div class="flex flex-col md:flex-row md:items-center gap-3">
                                <label class="text-xs font-medium text-gray-700 md:w-40 flex items-center gap-1">
                                    Checkbox Default State
                                    <div class="tooltip-wrapper" x-data="{ show: false }">
                                        <span class="material-icons-outlined text-gray-400 text-sm cursor-help" 
                                              @mouseenter="show = true" 
                                              @mouseleave="show = false">
                                            help_outline
                                        </span>
                                        <div x-show="show" x-transition class="tooltip-content">
                                            Default state of PWA access checkbox
                                        </div>
                                    </div>
                                </label>
                                <div class="flex-1">
                                    <select class="w-full px-2 py-1 text-xs border border-gray-300 rounded focus:ring focus:ring-indigo-200 focus:border-indigo-300 bg-white">
                                        <option>Checked (Opt-in)</option>
                                        <option>Unchecked (Opt-out)</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Password Settings -->
                <div class="bg-white border border-gray-200 rounded-md shadow-sm" x-show="tab==='security'">
                    <div class="bg-gray-50 border-b border-gray-200 px-4 py-3">
                        <h2 class="text-sm font-semibold text-gray-700 flex items-center">
                            <span class="material-icons-outlined text-primary-DEFAULT mr-2">lock</span>
                            Password Settings
                        </h2>
                    </div>
                    
                    <div class="p-4">
                        <div class="space-y-3">
                            <div class="flex flex-col md:flex-row md:items-center gap-3">
                                <label class="text-xs font-medium text-gray-700 md:w-40 flex items-center gap-1">
                                    Password Length
                                    <div class="tooltip-wrapper" x-data="{ show: false }">
                                        <span class="material-icons-outlined text-gray-400 text-sm cursor-help" 
                                              @mouseenter="show = true" 
                                              @mouseleave="show = false">
                                            help_outline
                                        </span>
                                        <div x-show="show" x-transition class="tooltip-content">
                                            Number of characters for auto-generated passwords
                                        </div>
                                    </div>
                                </label>
                                <div class="flex-1">
                                    <input type="number" value="8" min="6" max="16" class="w-20 px-2 py-1 text-xs border border-gray-300 rounded focus:ring focus:ring-indigo-200 focus:border-indigo-300">
                                </div>
                            </div>
                            
                            <div class="flex flex-col md:flex-row md:items-start gap-3">
                                <label class="text-xs font-medium text-gray-700 md:w-40 pt-2 flex items-center gap-1">
                                    Password Complexity
                                    <div class="tooltip-wrapper" x-data="{ show: false }">
                                        <span class="material-icons-outlined text-gray-400 text-sm cursor-help" 
                                              @mouseenter="show = true" 
                                              @mouseleave="show = false">
                                            help_outline
                                        </span>
                                        <div x-show="show" x-transition class="tooltip-content">
                                            Select password complexity requirements
                                        </div>
                                    </div>
                                </label>
                                <div class="flex-1">
                                    <div class="space-y-1">
                                        <label class="flex items-center">
                                            <input type="checkbox" checked class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                            <span class="ml-2 text-xs text-gray-700">Include uppercase letters (A-Z)</span>
                                        </label>
                                        <label class="flex items-center">
                                            <input type="checkbox" checked class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                            <span class="ml-2 text-xs text-gray-700">Include lowercase letters (a-z)</span>
                                        </label>
                                        <label class="flex items-center">
                                            <input type="checkbox" checked class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                            <span class="ml-2 text-xs text-gray-700">Include numbers (0-9)</span>
                                        </label>
                                        <label class="flex items-center">
                                            <input type="checkbox" class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                            <span class="ml-2 text-xs text-gray-700">Include special characters (!@#$%^&*)</span>
                                        </label>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="flex flex-col md:flex-row md:items-center gap-3">
                                <label class="text-xs font-medium text-gray-700 md:w-40 flex items-center gap-1">
                                    Password Expiry
                                    <div class="tooltip-wrapper" x-data="{ show: false }">
                                        <span class="material-icons-outlined text-gray-400 text-sm cursor-help" 
                                              @mouseenter="show = true" 
                                              @mouseleave="show = false">
                                            help_outline
                                        </span>
                                        <div x-show="show" x-transition class="tooltip-content">
                                            Password expiration period
                                        </div>
                                    </div>
                                </label>
                                <div class="flex-1">
                                    <select class="w-full px-2 py-1 text-xs border border-gray-300 rounded focus:ring focus:ring-indigo-200 focus:border-indigo-300 bg-white">
                                        <option>Never expire</option>
                                        <option>30 days</option>
                                        <option>60 days</option>
                                        <option>90 days</option>
                                        <option>180 days</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Email Settings -->
                <div class="bg-white border border-gray-200 rounded-md shadow-sm" x-show="tab==='auto'">
                    <div class="bg-gray-50 border-b border-gray-200 px-4 py-3">
                        <h2 class="text-sm font-semibold text-gray-700 flex items-center">
                            <span class="material-icons-outlined text-primary-DEFAULT mr-2">email</span>
                            Email Settings
                        </h2>
                    </div>
                    
                    <div class="p-4">
                        <div class="space-y-3">
                            <div class="flex flex-col md:flex-row md:items-center gap-3">
                                <label class="text-xs font-medium text-gray-700 md:w-40 flex items-center gap-1">
                                    Send Welcome Email
                                    <div class="tooltip-wrapper" x-data="{ show: false }">
                                        <span class="material-icons-outlined text-gray-400 text-sm cursor-help" 
                                              @mouseenter="show = true" 
                                              @mouseleave="show = false">
                                            help_outline
                                        </span>
                                        <div x-show="show" x-transition class="tooltip-content">
                                            Send welcome email with credentials to new PWA participants
                                        </div>
                                    </div>
                                </label>
                                <div class="flex-1">
                                    <label class="relative inline-flex items-center cursor-pointer">
                                        <input type="checkbox" checked class="sr-only peer">
                                        <div class="w-9 h-5 bg-gray-200 peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-indigo-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[1px] after:left-[1px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-indigo-600"></div>
                                    </label>
                                </div>
                            </div>
                            
                            <div class="flex flex-col md:flex-row md:items-center gap-3">
                                <label class="text-xs font-medium text-gray-700 md:w-40 flex items-center gap-1">
                                    Include PWA App Link
                                    <div class="tooltip-wrapper" x-data="{ show: false }">
                                        <span class="material-icons-outlined text-gray-400 text-sm cursor-help" 
                                              @mouseenter="show = true" 
                                              @mouseleave="show = false">
                                            help_outline
                                        </span>
                                        <div x-show="show" x-transition class="tooltip-content">
                                            Include mobile app download link in welcome emails
                                        </div>
                                    </div>
                                </label>
                                <div class="flex-1">
                                    <label class="relative inline-flex items-center cursor-pointer">
                                        <input type="checkbox" checked class="sr-only peer">
                                        <div class="w-9 h-5 bg-gray-200 peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-indigo-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[1px] after:left-[1px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-indigo-600"></div>
                                    </label>
                                </div>
                            </div>
                            
                            <div class="flex flex-col md:flex-row md:items-center gap-3">
                                <label class="text-xs font-medium text-gray-700 md:w-40 flex items-center gap-1">
                                    PWA App Link
                                    <div class="tooltip-wrapper" x-data="{ show: false }">
                                        <span class="material-icons-outlined text-gray-400 text-sm cursor-help" 
                                              @mouseenter="show = true" 
                                              @mouseleave="show = false">
                                            help_outline
                                        </span>
                                        <div x-show="show" x-transition class="tooltip-content">
                                            URL for participants to download or access the PWA
                                        </div>
                                    </div>
                                </label>
                                <div class="flex-1">
                                    <input type="url" value="https://apps.e-certificate.com.my" class="w-full px-2 py-1 text-xs border border-gray-300 rounded focus:ring focus:ring-indigo-200 focus:border-indigo-300">
                                </div>
                            </div>
                            
                            <div class="flex flex-col md:flex-row md:items-center gap-3">
                                <label class="text-xs font-medium text-gray-700 md:w-40 flex items-center gap-1">
                                    Support Email
                                    <div class="tooltip-wrapper" x-data="{ show: false }">
                                        <span class="material-icons-outlined text-gray-400 text-sm cursor-help" 
                                              @mouseenter="show = true" 
                                              @mouseleave="show = false">
                                            help_outline
                                        </span>
                                        <div x-show="show" x-transition class="tooltip-content">
                                            Email address for participant support inquiries
                                        </div>
                                    </div>
                                </label>
                                <div class="flex-1">
                                    <input type="email" value="support@e-certificate.com.my" class="w-full px-2 py-1 text-xs border border-gray-300 rounded focus:ring focus:ring-indigo-200 focus:border-indigo-300">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Data Synchronization -->
                <div class="bg-white border border-gray-200 rounded-md shadow-sm" x-show="tab==='auto'">
                    <div class="bg-gray-50 border-b border-gray-200 px-4 py-3">
                        <h2 class="text-sm font-semibold text-gray-700 flex items-center">
                            <span class="material-icons-outlined text-primary-DEFAULT mr-2">sync</span>
                            Data Synchronization
                        </h2>
                    </div>
                    
                    <div class="p-4">
                        <div class="space-y-3">
                            <div class="flex flex-col md:flex-row md:items-center gap-3">
                                <label class="text-xs font-medium text-gray-700 md:w-40 flex items-center gap-1">
                                    Real-time Sync
                                    <div class="tooltip-wrapper" x-data="{ show: false }">
                                        <span class="material-icons-outlined text-gray-400 text-sm cursor-help" 
                                              @mouseenter="show = true" 
                                              @mouseleave="show = false">
                                            help_outline
                                        </span>
                                        <div x-show="show" x-transition class="tooltip-content">
                                            Automatically sync data between participants and PWA participants
                                        </div>
                                    </div>
                                </label>
                                <div class="flex-1">
                                    <label class="relative inline-flex items-center cursor-pointer">
                                        <input type="checkbox" checked class="sr-only peer">
                                        <div class="w-9 h-5 bg-gray-200 peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-indigo-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[1px] after:left-[1px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-indigo-600"></div>
                                    </label>
                                </div>
                            </div>
                            
                            <div class="flex flex-col md:flex-row md:items-start gap-3">
                                <label class="text-xs font-medium text-gray-700 md:w-40 pt-2 flex items-center gap-1">
                                    Sync Fields
                                    <div class="tooltip-wrapper" x-data="{ show: false }">
                                        <span class="material-icons-outlined text-gray-400 text-sm cursor-help" 
                                              @mouseenter="show = true" 
                                              @mouseleave="show = false">
                                            help_outline
                                        </span>
                                        <div x-show="show" x-transition class="tooltip-content">
                                            Select which fields to synchronize
                                        </div>
                                    </div>
                                </label>
                                <div class="flex-1">
                                    <div class="space-y-1">
                                        <label class="flex items-center">
                                            <input type="checkbox" checked class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                            <span class="ml-2 text-xs text-gray-700">Name</span>
                                        </label>
                                        <label class="flex items-center">
                                            <input type="checkbox" checked class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                            <span class="ml-2 text-xs text-gray-700">Email</span>
                                        </label>
                                        <label class="flex items-center">
                                            <input type="checkbox" checked class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                            <span class="ml-2 text-xs text-gray-700">Phone</span>
                                        </label>
                                        <label class="flex items-center">
                                            <input type="checkbox" checked class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                            <span class="ml-2 text-xs text-gray-700">Organization</span>
                                        </label>
                                        <label class="flex items-center">
                                            <input type="checkbox" class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                            <span class="ml-2 text-xs text-gray-700">Address</span>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Advanced Settings -->
                <div class="bg-white border border-gray-200 rounded-md shadow-sm" x-show="tab==='security'">
                    <div class="bg-gray-50 border-b border-gray-200 px-4 py-3">
                        <h2 class="text-sm font-semibold text-gray-700 flex items-center">
                            <span class="material-icons-outlined text-primary-DEFAULT mr-2">settings</span>
                            Advanced Settings
                        </h2>
                    </div>
                    
                    <div class="p-4">
                        <div class="space-y-3">
                            <div class="flex flex-col md:flex-row md:items-center gap-3">
                                <label class="text-xs font-medium text-gray-700 md:w-40 flex items-center gap-1">
                                    Session Timeout
                                    <div class="tooltip-wrapper" x-data="{ show: false }">
                                        <span class="material-icons-outlined text-gray-400 text-sm cursor-help" 
                                              @mouseenter="show = true" 
                                              @mouseleave="show = false">
                                            help_outline
                                        </span>
                                        <div x-show="show" x-transition class="tooltip-content">
                                            Session timeout duration
                                        </div>
                                    </div>
                                </label>
                                <div class="flex-1">
                                    <select class="w-full px-2 py-1 text-xs border border-gray-300 rounded focus:ring focus:ring-indigo-200 focus:border-indigo-300 bg-white">
                                        <option>30 minutes</option>
                                        <option>1 hour</option>
                                        <option>2 hours</option>
                                        <option>4 hours</option>
                                        <option>8 hours</option>
                                        <option>24 hours</option>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="flex flex-col md:flex-row md:items-center gap-3">
                                <label class="text-xs font-medium text-gray-700 md:w-40 flex items-center gap-1">
                                    Max Login Attempts
                                    <div class="tooltip-wrapper" x-data="{ show: false }">
                                        <span class="material-icons-outlined text-gray-400 text-sm cursor-help" 
                                              @mouseenter="show = true" 
                                              @mouseleave="show = false">
                                            help_outline
                                        </span>
                                        <div x-show="show" x-transition class="tooltip-content">
                                            Number of failed login attempts before account lockout
                                        </div>
                                    </div>
                                </label>
                                <div class="flex-1">
                                    <input type="number" value="5" min="3" max="10" class="w-20 px-2 py-1 text-xs border border-gray-300 rounded focus:ring focus:ring-indigo-200 focus:border-indigo-300">
                                </div>
                            </div>
                            
                            <div class="flex flex-col md:flex-row md:items-center gap-3">
                                <label class="text-xs font-medium text-gray-700 md:w-40 flex items-center gap-1">
                                    Lockout Duration
                                    <div class="tooltip-wrapper" x-data="{ show: false }">
                                        <span class="material-icons-outlined text-gray-400 text-sm cursor-help" 
                                              @mouseenter="show = true" 
                                              @mouseleave="show = false">
                                            help_outline
                                        </span>
                                        <div x-show="show" x-transition class="tooltip-content">
                                            Account lockout duration after max attempts
                                        </div>
                                    </div>
                                </label>
                                <div class="flex-1">
                                    <select class="w-full px-2 py-1 text-xs border border-gray-300 rounded focus:ring focus:ring-indigo-200 focus:border-indigo-300 bg-white">
                                        <option>15 minutes</option>
                                        <option>30 minutes</option>
                                        <option>1 hour</option>
                                        <option>2 hours</option>
                                        <option>24 hours</option>
                                        <option>Until manually unlocked</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="flex justify-between items-center pt-4">
                <div class="flex items-center gap-2">
                    <a href="{{ route('pwa.settings') }}" class="px-3 h-[36px] bg-gradient-to-r from-red-600 to-red-500 hover:from-red-700 hover:to-red-600 text-white rounded shadow-sm text-xs font-medium transition-colors duration-200 ease-in-out flex items-center">
                        <span class="material-icons-outlined text-xs mr-1">cancel</span>
                        Cancel
                    </a>
                    @can('pwa_settings.update')
                    <button type="submit" class="px-3 h-[36px] bg-gradient-to-r from-green-600 to-green-500 hover:from-green-700 hover:to-green-600 text-white rounded shadow-sm text-xs font-medium transition-colors duration-200 ease-in-out flex items-center">
                        <span class="material-icons-outlined text-xs mr-1">save</span>
                        Save Settings
                    </button>
                    @endcan
                </div>
                @can('pwa_settings.update')
                <form method="POST" action="{{ route('pwa.settings.reset') }}" onsubmit="return confirm('Reset to defaults?')">
                    @csrf
                    <button class="px-3 h-[36px] bg-gradient-to-r from-gray-600 to-gray-500 hover:from-gray-700 hover:to-gray-600 text-white rounded shadow-sm text-xs font-medium transition-colors duration-200 ease-in-out flex items-center">
                        <span class="material-icons-outlined text-xs mr-1">refresh</span>
                        Reset to Defaults
                    </button>
                </form>
                @endcan
            </div>
            </fieldset>
        </form>
    </div>
</x-app-layout> 