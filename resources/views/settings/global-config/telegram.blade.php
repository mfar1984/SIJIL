<div x-show="activeTab === 'telegram'" class="space-y-2">
    <style>
        /* Custom style for disabled checked toggles - show blue color even when disabled */
        input[type="checkbox"]:disabled:checked + div {
            background-color: #004aad !important;
        }
        input[type="checkbox"]:disabled:checked + div::after {
            transform: translateX(100%) !important;
        }
    </style>
    
    <div class="bg-white border border-gray-200 rounded-md shadow-sm">
        <div class="bg-gray-50 border-b border-gray-200 px-4 py-3">
            <h2 class="text-sm font-semibold text-gray-700 flex items-center">
                <span class="material-icons text-primary-DEFAULT mr-2">smart_toy</span>
                Bot Configuration
            </h2>
        </div>
        
        <div class="p-4">
        <div class="space-y-3">
            <!-- Bot API Token -->
            <div class="flex flex-col md:flex-row md:items-start gap-1">
                <label for="telegram_bot_token" class="text-xs font-medium text-gray-700 md:w-40 pt-2">
                    Bot API Token
                </label>
                <div class="flex-1">
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <span class="material-icons text-[#004aad] text-base">password</span>
                        </div>
                        <input 
                            type="text" 
                            id="telegram_bot_token" 
                            name="telegram_bot_token" 
                            value="{{ old('telegram_bot_token', $config->telegram_bot_token ?? '') }}" 
                            class="w-full text-xs border-gray-300 rounded-[1px] pl-12 focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50"
                            :class="{'bg-gray-50': !isEditing}"
                            :disabled="!isEditing"
                            placeholder="123456789:ABCdefGHIjklMNOpqrsTUVwxyz"
                        >
                    </div>
                    <p class="mt-1 text-[10px] text-gray-500">Get from @BotFather on Telegram</p>
                </div>
            </div>
            
            <!-- Bot Username -->
            <div class="flex flex-col md:flex-row md:items-start gap-1">
                <label for="telegram_bot_username" class="text-xs font-medium text-gray-700 md:w-40 pt-2">
                    Bot Username
                </label>
                <div class="flex-1">
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <span class="material-icons text-[#004aad] text-base">person</span>
                        </div>
                        <input 
                            type="text" 
                            id="telegram_bot_username" 
                            name="telegram_bot_username" 
                            value="{{ old('telegram_bot_username', $config->telegram_bot_username ?? '') }}" 
                            class="w-full text-xs border-gray-300 rounded-[1px] pl-12 focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50"
                            :class="{'bg-gray-50': !isEditing}"
                            :disabled="!isEditing"
                            placeholder="@your_bot_username"
                        >
                    </div>
                    <p class="mt-1 text-[10px] text-gray-500">Your bot's username (with @)</p>
                </div>
            </div>
            
            <!-- Channel ID -->
            <div class="flex flex-col md:flex-row md:items-start gap-1">
                <label for="telegram_channel_id" class="text-xs font-medium text-gray-700 md:w-40 pt-2">
                    Channel/Group ID
                </label>
                <div class="flex-1">
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <span class="material-icons text-[#004aad] text-base">numbers</span>
                        </div>
                        <input 
                            type="text" 
                            id="telegram_channel_id" 
                            name="telegram_channel_id" 
                            value="{{ old('telegram_channel_id', $config->telegram_channel_id ?? '') }}" 
                            class="w-full text-xs border-gray-300 rounded-[1px] pl-12 focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50"
                            :class="{'bg-gray-50': !isEditing}"
                            :disabled="!isEditing"
                            placeholder="-1001234567890"
                        >
                    </div>
                    <p class="mt-1 text-[10px] text-gray-500">Channel or group ID where notifications will be sent</p>
                </div>
            </div>
            
            <!-- Owner User ID -->
            <div class="flex flex-col md:flex-row md:items-start gap-1">
                <label for="telegram_owner_user_id" class="text-xs font-medium text-gray-700 md:w-40 pt-2">
                    Owner User ID
                </label>
                <div class="flex-1">
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <span class="material-icons text-[#004aad] text-base">badge</span>
                        </div>
                        <input 
                            type="text" 
                            id="telegram_owner_user_id" 
                            name="telegram_owner_user_id" 
                            value="{{ old('telegram_owner_user_id', $config->telegram_owner_user_id ?? '') }}" 
                            class="w-full text-xs border-gray-300 rounded-[1px] pl-12 focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50"
                            :class="{'bg-gray-50': !isEditing}"
                            :disabled="!isEditing"
                            placeholder="123456789"
                        >
                    </div>
                    <p class="mt-1 text-[10px] text-gray-500">Your Telegram user ID (optional)</p>
                </div>
            </div>
            
            <!-- Owner Username -->
            <div class="flex flex-col md:flex-row md:items-start gap-1">
                <label for="telegram_owner_username" class="text-xs font-medium text-gray-700 md:w-40 pt-2">
                    Owner Username
                </label>
                <div class="flex-1">
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <span class="material-icons text-[#004aad] text-base">person_outline</span>
                        </div>
                        <input 
                            type="text" 
                            id="telegram_owner_username" 
                            name="telegram_owner_username" 
                            value="{{ old('telegram_owner_username', $config->telegram_owner_username ?? '') }}" 
                            class="w-full text-xs border-gray-300 rounded-[1px] pl-12 focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50"
                            :class="{'bg-gray-50': !isEditing}"
                            :disabled="!isEditing"
                            placeholder="@your_username"
                        >
                    </div>
                    <p class="mt-1 text-[10px] text-gray-500">Your Telegram username (optional)</p>
                </div>
            </div>
        </div>
        </div>
    </div>
    
    <div class="bg-white border border-gray-200 rounded-md shadow-sm">
        <div class="bg-gray-50 border-b border-gray-200 px-4 py-3">
            <h2 class="text-sm font-semibold text-gray-700 flex items-center">
                <span class="material-icons text-primary-DEFAULT mr-2">notifications_active</span>
                Telegram Notifications
            </h2>
        </div>
        
        <div class="p-4">
        <div class="grid grid-cols-1 gap-3">
            <div class="flex justify-between items-center py-2 border-b border-gray-100">
                <div class="flex items-center">
                    <span class="material-icons text-primary-DEFAULT mr-2 text-sm">event_available</span>
                    <div>
                        <p class="text-xs font-medium text-gray-700">Event Registration</p>
                        <p class="text-[10px] text-gray-500">Send Telegram notification when someone registers for an event</p>
                    </div>
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
                            :disabled="!isEditing"
                        >
                        <div class="w-9 h-5 bg-gray-200 peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-primary-light rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-primary-DEFAULT"></div>
                    </label>
                </div>
            </div>
            
            <div class="flex justify-between items-center py-2 border-b border-gray-100">
                <div class="flex items-center">
                    <span class="material-icons text-primary-DEFAULT mr-2 text-sm">workspace_premium</span>
                    <div>
                        <p class="text-xs font-medium text-gray-700">Certificate Generated</p>
                        <p class="text-[10px] text-gray-500">Send Telegram notification when a certificate is generated</p>
                    </div>
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
                            :disabled="!isEditing"
                        >
                        <div class="w-9 h-5 bg-gray-200 peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-primary-light rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-primary-DEFAULT"></div>
                    </label>
                </div>
            </div>
        </div>
        </div>
    </div>
</div>

<script>
</script>
