<div x-show="activeTab === 'api'" class="space-y-2">
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
                <span class="material-icons-outlined text-primary-DEFAULT mr-2">api</span>
                API Settings
            </h2>
        </div>
        
        <div class="p-4">
        <div class="space-y-3">
            <!-- API Status -->
            <div class="flex flex-col md:flex-row md:items-center gap-3">
                <label class="text-xs font-medium text-gray-700 md:w-40 flex items-center gap-1">
                    API Status
                    <div class="tooltip-wrapper" x-data="{ show: false }">
                        <span class="material-icons-outlined text-gray-400 text-sm cursor-help" 
                              @mouseenter="show = true" 
                              @mouseleave="show = false">
                            help_outline
                        </span>
                        <div x-show="show" x-transition class="tooltip-content">
                            Enable/disable the REST API
                        </div>
                    </div>
                </label>
                <div class="flex-1">
                    <div class="flex items-center">
                        <label class="inline-flex items-center mr-4">
                            <input 
                                type="radio" 
                                name="api_status" 
                                value="enabled" 
                                class="text-primary-DEFAULT focus:ring-primary-light" 
                                {{ (old('api_status', $config->api_status ?? 'enabled') == 'enabled') ? 'checked' : '' }}
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
                            >
                            <span class="ml-2 text-xs text-gray-700">Disabled</span>
                        </label>
                    </div>
                </div>
            </div>
            
            <!-- Rate Limiting -->
            <div class="flex flex-col md:flex-row md:items-center gap-3">
                <label for="api_rate_limit" class="text-xs font-medium text-gray-700 md:w-40 flex items-center gap-1">
                    Rate Limit (requests per minute)
                    <div class="tooltip-wrapper" x-data="{ show: false }">
                        <span class="material-icons-outlined text-gray-400 text-sm cursor-help" 
                              @mouseenter="show = true" 
                              @mouseleave="show = false">
                            help_outline
                        </span>
                        <div x-show="show" x-transition class="tooltip-content">
                            Maximum number of API requests per minute per client
                        </div>
                    </div>
                </label>
                <div class="flex-1">
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <span class="material-icons-outlined text-[#004aad] text-base">data_usage</span>
                        </div>
                        <input 
                            type="number" 
                            id="api_rate_limit" 
                            name="api_rate_limit" 
                            value="{{ old('api_rate_limit', $config->api_rate_limit ?? 60) }}" 
                            min="10" 
                            class="w-full text-xs border-gray-300 rounded-[1px] pl-12 focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50"
                        >
                    </div>
                </div>
            </div>
            
            <div class="flex flex-col md:flex-row md:items-center gap-3">
                <div class="md:w-40"></div>
                <div class="flex-1">
                    <label class="flex items-center">
                        <input type="hidden" name="enable_api_keys" value="0">
                        <input 
                            type="checkbox" 
                            name="enable_api_keys"
                            value="1"
                            class="rounded border-gray-300 text-primary-DEFAULT focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50" 
                            {{ (old('enable_api_keys', $config->enable_api_keys ?? true)) ? 'checked' : '' }}
                        >
                        <span class="ml-2 text-xs text-gray-700">Require API keys for access</span>
                    </label>
                </div>
            </div>
            
            <div class="flex flex-col md:flex-row md:items-center gap-3">
                <div class="md:w-40"></div>
                <div class="flex-1">
                    <label class="flex items-center">
                        <input type="hidden" name="enable_oauth" value="0">
                        <input 
                            type="checkbox" 
                            name="enable_oauth"
                            value="1"
                            class="rounded border-gray-300 text-primary-DEFAULT focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50" 
                            {{ (old('enable_oauth', $config->enable_oauth ?? true)) ? 'checked' : '' }}
                        >
                        <span class="ml-2 text-xs text-gray-700">Enable OAuth 2.0 authorization</span>
                    </label>
                </div>
            </div>
            
            <div class="flex flex-col md:flex-row md:items-center gap-3">
                <div class="md:w-40"></div>
                <div class="flex-1">
                    <label class="flex items-center">
                        <input type="hidden" name="api_cors_enabled" value="0">
                        <input 
                            type="checkbox" 
                            name="api_cors_enabled"
                            value="1"
                            class="rounded border-gray-300 text-primary-DEFAULT focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50" 
                            {{ (old('api_cors_enabled', $config->api_cors_enabled ?? true)) ? 'checked' : '' }}
                        >
                        <span class="ml-2 text-xs text-gray-700">Allow CORS for API requests</span>
                    </label>
                </div>
            </div>
            
            <!-- CORS Domains -->
            <div class="flex flex-col md:flex-row md:items-start gap-3">
                <label for="cors_domains" class="text-xs font-medium text-gray-700 md:w-40 pt-2 flex items-center gap-1">
                    CORS Allowed Domains
                    <div class="tooltip-wrapper" x-data="{ show: false }">
                        <span class="material-icons-outlined text-gray-400 text-sm cursor-help" 
                              @mouseenter="show = true" 
                              @mouseleave="show = false">
                            help_outline
                        </span>
                        <div x-show="show" x-transition class="tooltip-content">
                            Domains allowed to make cross-origin API requests (comma separated)
                        </div>
                    </div>
                </label>
                <div class="flex-1">
                    <textarea 
                        id="cors_domains" 
                        name="cors_domains" 
                        rows="2" 
                        class="w-full text-xs border-gray-300 rounded focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50"
                    >{{ old('cors_domains', $config->cors_domains ?? 'https://example.com, https://*.sijilevents.com') }}</textarea>
                </div>
            </div>
        </div>
        </div>
    </div>
    
    <div class="bg-white border border-gray-200 rounded-md shadow-sm">
        <div class="bg-gray-50 border-b border-gray-200 px-4 py-3">
            <h2 class="text-sm font-semibold text-gray-700 flex items-center">
                <span class="material-icons-outlined text-primary-DEFAULT mr-2">integration_instructions</span>
                Third-Party Integrations
            </h2>
        </div>
        
        <div class="p-4">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-4">
            <!-- Google Calendar Integration -->
            <div class="flex justify-between items-center py-2 border border-gray-200 rounded-md px-3">
                <div class="flex items-center">
                    <svg class="w-5 h-5 mr-2" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" preserveAspectRatio="xMidYMid">
                        <path d="M21.5 5.25H17.5V3.75C17.5 3.34 17.16 3 16.75 3C16.34 3 16 3.34 16 3.75V5.25H8V3.75C8 3.34 7.66 3 7.25 3C6.84 3 6.5 3.34 6.5 3.75V5.25H2.5C1.12 5.25 0 6.37 0 7.75V19.75C0 21.13 1.12 22.25 2.5 22.25H21.5C22.88 22.25 24 21.13 24 19.75V7.75C24 6.37 22.88 5.25 21.5 5.25ZM22.5 19.75C22.5 20.3 22.05 20.75 21.5 20.75H2.5C1.95 20.75 1.5 20.3 1.5 19.75V10H22.5V19.75ZM22.5 8.5H1.5V7.75C1.5 7.2 1.95 6.75 2.5 6.75H6.5V8.25C6.5 8.66 6.84 9 7.25 9C7.66 9 8 8.66 8 8.25V6.75H16V8.25C16 8.66 16.34 9 16.75 9C17.16 9 17.5 8.66 17.5 8.25V6.75H21.5C22.05 6.75 22.5 7.2 22.5 7.75V8.5Z" fill="#4285F4"/>
                    </svg>
                    <div>
                        <p class="text-xs font-medium text-gray-700">Google Calendar</p>
                        <p class="text-[10px] text-gray-500">Sync events with Google Calendar</p>
                    </div>
                </div>
                <div>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" class="sr-only peer">
                        <div class="w-11 h-6 bg-gray-200 rounded-full peer peer-checked:bg-blue-600 peer-focus:ring-4 peer-focus:ring-blue-300 after:content-[''] after:absolute after:top-0.5 after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:after:translate-x-full peer-checked:after:border-white"></div>
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
                        <input type="checkbox" class="sr-only peer">
                        <div class="w-11 h-6 bg-gray-200 rounded-full peer peer-checked:bg-blue-600 peer-focus:ring-4 peer-focus:ring-blue-300 after:content-[''] after:absolute after:top-0.5 after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:after:translate-x-full peer-checked:after:border-white"></div>
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
                        <input type="checkbox" checked class="sr-only peer">
                        <div class="w-11 h-6 bg-gray-200 rounded-full peer peer-checked:bg-blue-600 peer-focus:ring-4 peer-focus:ring-blue-300 after:content-[''] after:absolute after:top-0.5 after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:after:translate-x-full peer-checked:after:border-white"></div>
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
                        <input type="checkbox" checked class="sr-only peer">
                        <div class="w-11 h-6 bg-gray-200 rounded-full peer peer-checked:bg-blue-600 peer-focus:ring-4 peer-focus:ring-blue-300 after:content-[''] after:absolute after:top-0.5 after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:after:translate-x-full peer-checked:after:border-white"></div>
                    </label>
                </div>
            </div>
        </div>
        </div>
    </div>
    
    <div class="bg-white border border-gray-200 rounded-md shadow-sm">
        <div class="bg-gray-50 border-b border-gray-200 px-4 py-3">
            <h2 class="text-sm font-semibold text-gray-700 flex items-center">
                <span class="material-icons-outlined text-primary-DEFAULT mr-2">webhook</span>
                Webhooks
            </h2>
        </div>
        
        <div class="p-4">
        <div class="space-y-3">
            <div class="flex flex-col md:flex-row md:items-center gap-3">
                <div class="md:w-40"></div>
                <div class="flex-1">
                    <label class="flex items-center gap-1">
                        <input type="hidden" name="enable_webhooks" value="0">
                        <input 
                            type="checkbox" 
                            name="enable_webhooks"
                            value="1"
                            class="rounded border-gray-300 text-primary-DEFAULT focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50" 
                            {{ (old('enable_webhooks', $config->enable_webhooks ?? true)) ? 'checked' : '' }}
                        >
                        <span class="ml-2 text-xs text-gray-700">Enable webhooks</span>
                        <div class="tooltip-wrapper" x-data="{ show: false }">
                            <span class="material-icons-outlined text-gray-400 text-sm cursor-help" 
                                  @mouseenter="show = true" 
                                  @mouseleave="show = false">
                                help_outline
                            </span>
                            <div x-show="show" x-transition class="tooltip-content">
                                Allow external systems to receive event notifications
                            </div>
                        </div>
                    </label>
                </div>
            </div>
            
            <!-- Webhook Secret -->
            <div class="flex flex-col md:flex-row md:items-center gap-3">
                <label for="webhook_secret" class="text-xs font-medium text-gray-700 md:w-40 flex items-center gap-1">
                    Webhook Secret
                    <div class="tooltip-wrapper" x-data="{ show: false }">
                        <span class="material-icons-outlined text-gray-400 text-sm cursor-help" 
                              @mouseenter="show = true" 
                              @mouseleave="show = false">
                            help_outline
                        </span>
                        <div x-show="show" x-transition class="tooltip-content">
                            Secret key used to validate webhook requests
                        </div>
                    </div>
                </label>
                <div class="flex-1">
                    <div class="flex">
                        <div class="relative flex-1">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <span class="material-icons-outlined text-[#004aad] text-base">password</span>
                            </div>
                            <input 
                                type="password" 
                                id="webhook_secret" 
                                name="webhook_secret" 
                                value="{{ old('webhook_secret', $config->webhook_secret ?? 'wh_sec_1a2b3c4d5e6f') }}" 
                                class="w-full text-xs border-gray-300 rounded-[1px] pl-12 focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50"
                            >
                        </div>
                        <button 
                            type="button"
                            class="ml-2 bg-gray-100 hover:bg-gray-200 text-xs text-gray-700 px-3 rounded"
                        >
                            Regenerate
                        </button>
                    </div>
                </div>
            </div>
            
            <!-- Webhook Events -->
            <div class="flex flex-col md:flex-row md:items-start gap-3">
                <label for="webhook_events" class="text-xs font-medium text-gray-700 md:w-40 pt-2 flex items-center gap-1">
                    Webhook Events
                    <div class="tooltip-wrapper" x-data="{ show: false }">
                        <span class="material-icons-outlined text-gray-400 text-sm cursor-help" 
                              @mouseenter="show = true" 
                              @mouseleave="show = false">
                            help_outline
                        </span>
                        <div x-show="show" x-transition class="tooltip-content">
                            Events that will trigger webhook notifications (comma separated)
                        </div>
                    </div>
                </label>
                <div class="flex-1">
                    <textarea 
                        id="webhook_events" 
                        name="webhook_events" 
                        rows="3" 
                        class="w-full text-xs border-gray-300 rounded focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50"
                    >{{ old('webhook_events', $config->webhook_events ?? 'event.created, event.updated, registration.completed, certificate.generated, attendance.recorded') }}</textarea>
                </div>
            </div>
        </div>
        </div>
    </div>
</div>
