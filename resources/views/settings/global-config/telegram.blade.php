<div x-show="activeTab === 'telegram'" class="space-y-2">
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
                <span class="material-icons-outlined text-primary-DEFAULT mr-2">smart_toy</span>
                Bot Configuration
            </h2>
        </div>
        
        <div class="p-4">
        <div class="space-y-3">
            <!-- Bot API Token -->
            <div class="flex flex-col md:flex-row md:items-center gap-3">
                <label for="telegram_bot_token" class="text-xs font-medium text-gray-700 md:w-40 flex items-center gap-1">
                    Bot API Token
                    <div class="tooltip-wrapper" x-data="{ show: false }">
                        <span class="material-icons-outlined text-gray-400 text-sm cursor-help" 
                              @mouseenter="show = true" 
                              @mouseleave="show = false">
                            help_outline
                        </span>
                        <div x-show="show" 
                             x-transition:enter="transition ease-out duration-200"
                             x-transition:enter-start="opacity-0 scale-95"
                             x-transition:enter-end="opacity-100 scale-100"
                             x-transition:leave="transition ease-in duration-150"
                             x-transition:leave-start="opacity-100 scale-100"
                             x-transition:leave-end="opacity-0 scale-95"
                             class="tooltip-content">
                            Get from @BotFather on Telegram
                        </div>
                    </div>
                </label>
                <div class="flex-1">
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <span class="material-icons-outlined text-[#004aad] text-base">password</span>
                        </div>
                        <input 
                            type="text" 
                            id="telegram_bot_token" 
                            name="telegram_bot_token" 
                            value="{{ old('telegram_bot_token', $config->telegram_bot_token ?? '') }}" 
                            class="w-full text-xs border-gray-300 rounded-[1px] pl-12 focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50"
                            placeholder="123456789:ABCdefGHIjklMNOpqrsTUVwxyz"
                        >
                    </div>
                </div>
            </div>
            
            <!-- Bot Username -->
            <div class="flex flex-col md:flex-row md:items-center gap-3">
                <label for="telegram_bot_username" class="text-xs font-medium text-gray-700 md:w-40 flex items-center gap-1">
                    Bot Username
                    <div class="tooltip-wrapper" x-data="{ show: false }">
                        <span class="material-icons-outlined text-gray-400 text-sm cursor-help" 
                              @mouseenter="show = true" 
                              @mouseleave="show = false">
                            help_outline
                        </span>
                        <div x-show="show" 
                             x-transition
                             class="tooltip-content">
                            Your bot's username (with @)
                        </div>
                    </div>
                </label>
                <div class="flex-1">
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <span class="material-icons-outlined text-[#004aad] text-base">person</span>
                        </div>
                        <input 
                            type="text" 
                            id="telegram_bot_username" 
                            name="telegram_bot_username" 
                            value="{{ old('telegram_bot_username', $config->telegram_bot_username ?? '') }}" 
                            class="w-full text-xs border-gray-300 rounded-[1px] pl-12 focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50"
                            placeholder="@your_bot_username"
                        >
                    </div>
                </div>
            </div>
            
            <!-- Channel ID -->
            <div class="flex flex-col md:flex-row md:items-center gap-3">
                <label for="telegram_channel_id" class="text-xs font-medium text-gray-700 md:w-40 flex items-center gap-1">
                    Channel/Group ID
                    <div class="tooltip-wrapper" x-data="{ show: false }">
                        <span class="material-icons-outlined text-gray-400 text-sm cursor-help" 
                              @mouseenter="show = true" 
                              @mouseleave="show = false">
                            help_outline
                        </span>
                        <div x-show="show" 
                             x-transition
                             class="tooltip-content">
                            Channel or group ID where notifications will be sent
                        </div>
                    </div>
                </label>
                <div class="flex-1">
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <span class="material-icons-outlined text-[#004aad] text-base">numbers</span>
                        </div>
                        <input 
                            type="text" 
                            id="telegram_channel_id" 
                            name="telegram_channel_id" 
                            value="{{ old('telegram_channel_id', $config->telegram_channel_id ?? '') }}" 
                            class="w-full text-xs border-gray-300 rounded-[1px] pl-12 focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50"
                            placeholder="-1001234567890"
                        >
                    </div>
                </div>
            </div>
            
            <!-- Owner User ID -->
            <div class="flex flex-col md:flex-row md:items-center gap-3">
                <label for="telegram_owner_user_id" class="text-xs font-medium text-gray-700 md:w-40 flex items-center gap-1">
                    Owner User ID
                    <div class="tooltip-wrapper" x-data="{ show: false }">
                        <span class="material-icons-outlined text-gray-400 text-sm cursor-help" 
                              @mouseenter="show = true" 
                              @mouseleave="show = false">
                            help_outline
                        </span>
                        <div x-show="show" 
                             x-transition
                             class="tooltip-content">
                            Your Telegram user ID (optional)
                        </div>
                    </div>
                </label>
                <div class="flex-1">
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <span class="material-icons-outlined text-[#004aad] text-base">badge</span>
                        </div>
                        <input 
                            type="text" 
                            id="telegram_owner_user_id" 
                            name="telegram_owner_user_id" 
                            value="{{ old('telegram_owner_user_id', $config->telegram_owner_user_id ?? '') }}" 
                            class="w-full text-xs border-gray-300 rounded-[1px] pl-12 focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50"
                            placeholder="123456789"
                        >
                    </div>
                </div>
            </div>
            
            <!-- Owner Username -->
            <div class="flex flex-col md:flex-row md:items-center gap-3">
                <label for="telegram_owner_username" class="text-xs font-medium text-gray-700 md:w-40 flex items-center gap-1">
                    Owner Username
                    <div class="tooltip-wrapper" x-data="{ show: false }">
                        <span class="material-icons-outlined text-gray-400 text-sm cursor-help" 
                              @mouseenter="show = true" 
                              @mouseleave="show = false">
                            help_outline
                        </span>
                        <div x-show="show" 
                             x-transition
                             class="tooltip-content">
                            Your Telegram username (optional)
                        </div>
                    </div>
                </label>
                <div class="flex-1">
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <span class="material-icons-outlined text-[#004aad] text-base">person_outline</span>
                        </div>
                        <input 
                            type="text" 
                            id="telegram_owner_username" 
                            name="telegram_owner_username" 
                            value="{{ old('telegram_owner_username', $config->telegram_owner_username ?? '') }}" 
                            class="w-full text-xs border-gray-300 rounded-[1px] pl-12 focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50"
                            placeholder="@your_username"
                        >
                    </div>
                </div>
            </div>
        </div>
        </div>
    </div>
    
    <div class="bg-white border border-gray-200 rounded-md shadow-sm">
        <div class="bg-gray-50 border-b border-gray-200 px-4 py-3">
            <h2 class="text-sm font-semibold text-gray-700 flex items-center">
                <span class="material-icons-outlined text-primary-DEFAULT mr-2">notifications_active</span>
                Telegram Notifications
            </h2>
        </div>
        
        <div class="p-4">
        <div class="grid grid-cols-1 gap-3">
            <div class="flex justify-between items-center py-2 border-b border-gray-100">
                <div class="flex items-center gap-2">
                    <span class="material-icons-outlined text-primary-DEFAULT text-sm">event_available</span>
                    <p class="text-xs font-medium text-gray-700 flex items-center gap-1">
                        Event Registration
                        <div class="tooltip-wrapper" x-data="{ show: false }">
                            <span class="material-icons-outlined text-gray-400 text-sm cursor-help" 
                                  @mouseenter="show = true" 
                                  @mouseleave="show = false">
                                help_outline
                            </span>
                            <div x-show="show" 
                                 x-transition
                                 class="tooltip-content">
                                Send Telegram notification when someone registers for an event
                            </div>
                        </div>
                    </p>
                </div>
                <div>
                    <input type="hidden" name="telegram_event_registration" value="0">
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input 
                            type="checkbox" 
                            name="telegram_event_registration" 
                            value="1"
                            class="sr-only peer" 
                            {{ (old('telegram_event_registration', $config->telegram_event_registration ?? false)) ? 'checked' : '' }}
                        >
                        <div class="w-11 h-6 bg-gray-200 rounded-full peer peer-checked:bg-blue-600 peer-focus:ring-4 peer-focus:ring-blue-300 after:content-[''] after:absolute after:top-0.5 after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:after:translate-x-full peer-checked:after:border-white"></div>
                    </label>
                </div>
            </div>
            
            <div class="flex justify-between items-center py-2 border-b border-gray-100">
                <div class="flex items-center gap-2">
                    <span class="material-icons-outlined text-primary-DEFAULT text-sm">workspace_premium</span>
                    <p class="text-xs font-medium text-gray-700 flex items-center gap-1">
                        Certificate Generated
                        <div class="tooltip-wrapper" x-data="{ show: false }">
                            <span class="material-icons-outlined text-gray-400 text-sm cursor-help" 
                                  @mouseenter="show = true" 
                                  @mouseleave="show = false">
                                help_outline
                            </span>
                            <div x-show="show" 
                                 x-transition
                                 class="tooltip-content">
                                Send Telegram notification when a certificate is generated
                            </div>
                        </div>
                    </p>
                </div>
                <div>
                    <input type="hidden" name="telegram_certificate_generated" value="0">
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input 
                            type="checkbox" 
                            name="telegram_certificate_generated" 
                            value="1"
                            class="sr-only peer" 
                            {{ (old('telegram_certificate_generated', $config->telegram_certificate_generated ?? false)) ? 'checked' : '' }}
                        >
                        <div class="w-11 h-6 bg-gray-200 rounded-full peer peer-checked:bg-blue-600 peer-focus:ring-4 peer-focus:ring-blue-300 after:content-[''] after:absolute after:top-0.5 after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:after:translate-x-full peer-checked:after:border-white"></div>
                    </label>
                </div>
            </div>
        </div>
        </div>
    </div>
</div>
