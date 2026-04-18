<div x-show="activeTab === 'notifications'" class="space-y-2">
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
                <span class="material-icons-outlined text-primary-DEFAULT mr-2">email</span>
                Email Notifications
            </h2>
        </div>
        
        <div class="p-4">
        <div class="grid grid-cols-1 gap-3">
            <div class="flex justify-between items-center py-2 border-b border-gray-100">
                <div class="flex items-center gap-2">
                    <span class="material-icons-outlined text-primary-DEFAULT text-sm">person_add</span>
                    <p class="text-xs font-medium text-gray-700 flex items-center gap-1">
                        New User Registration
                        <div class="tooltip-wrapper" x-data="{ show: false }">
                            <span class="material-icons-outlined text-gray-400 text-sm cursor-help" 
                                  @mouseenter="show = true" 
                                  @mouseleave="show = false">
                                help_outline
                            </span>
                            <div x-show="show" x-transition class="tooltip-content">
                                Send email when a new user registers
                            </div>
                        </div>
                    </p>
                </div>
                <div>
                    <input type="hidden" name="email_new_user_registration" value="0">
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" name="email_new_user_registration" value="1" class="sr-only peer" {{ (old('email_new_user_registration', $config->email_new_user_registration ?? true)) ? 'checked' : '' }}>
                        <div class="w-11 h-6 bg-gray-200 rounded-full peer peer-checked:bg-blue-600 peer-focus:ring-4 peer-focus:ring-blue-300 after:content-[''] after:absolute after:top-0.5 after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:after:translate-x-full peer-checked:after:border-white"></div>
                    </label>
                </div>
            </div>
            
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
                            <div x-show="show" x-transition class="tooltip-content">
                                Send confirmation email after event registration
                            </div>
                        </div>
                    </p>
                </div>
                <div>
                    <input type="hidden" name="email_event_registration" value="0">
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" name="email_event_registration" value="1" class="sr-only peer" {{ (old('email_event_registration', $config->email_event_registration ?? true)) ? 'checked' : '' }}>
                        <div class="w-11 h-6 bg-gray-200 rounded-full peer peer-checked:bg-blue-600 peer-focus:ring-4 peer-focus:ring-blue-300 after:content-[''] after:absolute after:top-0.5 after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:after:translate-x-full peer-checked:after:border-white"></div>
                    </label>
                </div>
            </div>
            
            <div class="flex justify-between items-center py-2 border-b border-gray-100">
                <div class="flex items-center gap-2">
                    <span class="material-icons-outlined text-primary-DEFAULT text-sm">notifications</span>
                    <p class="text-xs font-medium text-gray-700 flex items-center gap-1">
                        Event Reminder
                        <div class="tooltip-wrapper" x-data="{ show: false }">
                            <span class="material-icons-outlined text-gray-400 text-sm cursor-help" 
                                  @mouseenter="show = true" 
                                  @mouseleave="show = false">
                                help_outline
                            </span>
                            <div x-show="show" x-transition class="tooltip-content">
                                Send reminder email before event starts
                            </div>
                        </div>
                    </p>
                </div>
                <div>
                    <input type="hidden" name="email_event_reminder" value="0">
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" name="email_event_reminder" value="1" class="sr-only peer" {{ (old('email_event_reminder', $config->email_event_reminder ?? true)) ? 'checked' : '' }}>
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
                            <div x-show="show" x-transition class="tooltip-content">
                                Send email when a certificate is generated
                            </div>
                        </div>
                    </p>
                </div>
                <div>
                    <input type="hidden" name="email_certificate_generated" value="0">
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" name="email_certificate_generated" value="1" class="sr-only peer" {{ (old('email_certificate_generated', $config->email_certificate_generated ?? true)) ? 'checked' : '' }}>
                        <div class="w-11 h-6 bg-gray-200 rounded-full peer peer-checked:bg-blue-600 peer-focus:ring-4 peer-focus:ring-blue-300 after:content-[''] after:absolute after:top-0.5 after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:after:translate-x-full peer-checked:after:border-white"></div>
                    </label>
                </div>
            </div>
            
            <div class="flex justify-between items-center py-2 border-b border-gray-100">
                <div class="flex items-center gap-2">
                    <span class="material-icons-outlined text-primary-DEFAULT text-sm">password</span>
                    <p class="text-xs font-medium text-gray-700 flex items-center gap-1">
                        Password Reset
                        <div class="tooltip-wrapper" x-data="{ show: false }">
                            <span class="material-icons-outlined text-gray-400 text-sm cursor-help" 
                                  @mouseenter="show = true" 
                                  @mouseleave="show = false">
                                help_outline
                            </span>
                            <div x-show="show" x-transition class="tooltip-content">
                                Send email for password reset requests
                            </div>
                        </div>
                    </p>
                </div>
                <div>
                    <input type="hidden" name="email_password_reset" value="0">
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" name="email_password_reset" value="1" class="sr-only peer" {{ (old('email_password_reset', $config->email_password_reset ?? true)) ? 'checked' : '' }}>
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
                <span class="material-icons-outlined text-primary-DEFAULT mr-2">sms</span>
                SMS Notifications
            </h2>
        </div>
        
        <div class="p-4">
        <div class="grid grid-cols-1 gap-3 mb-4">
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
                            <div x-show="show" x-transition class="tooltip-content">
                                Send SMS confirmation after registration
                            </div>
                        </div>
                    </p>
                </div>
                <div>
                    <input type="hidden" name="sms_event_registration" value="0">
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" name="sms_event_registration" value="1" class="sr-only peer" {{ (old('sms_event_registration', $config->sms_event_registration ?? false)) ? 'checked' : '' }}>
                        <div class="w-11 h-6 bg-gray-200 rounded-full peer peer-checked:bg-blue-600 peer-focus:ring-4 peer-focus:ring-blue-300 after:content-[''] after:absolute after:top-0.5 after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:after:translate-x-full peer-checked:after:border-white"></div>
                    </label>
                </div>
            </div>
            
            <div class="flex justify-between items-center py-2 border-b border-gray-100">
                <div class="flex items-center gap-2">
                    <span class="material-icons-outlined text-primary-DEFAULT text-sm">notifications</span>
                    <p class="text-xs font-medium text-gray-700 flex items-center gap-1">
                        Event Reminder
                        <div class="tooltip-wrapper" x-data="{ show: false }">
                            <span class="material-icons-outlined text-gray-400 text-sm cursor-help" 
                                  @mouseenter="show = true" 
                                  @mouseleave="show = false">
                                help_outline
                            </span>
                            <div x-show="show" x-transition class="tooltip-content">
                                Send SMS reminder before event starts
                            </div>
                        </div>
                    </p>
                </div>
                <div>
                    <input type="hidden" name="sms_event_reminder" value="0">
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" name="sms_event_reminder" value="1" class="sr-only peer" {{ (old('sms_event_reminder', $config->sms_event_reminder ?? false)) ? 'checked' : '' }}>
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
                            <div x-show="show" x-transition class="tooltip-content">
                                Send SMS when a certificate is generated
                            </div>
                        </div>
                    </p>
                </div>
                <div>
                    <input type="hidden" name="sms_certificate_generated" value="0">
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" name="sms_certificate_generated" value="1" class="sr-only peer" {{ (old('sms_certificate_generated', $config->sms_certificate_generated ?? false)) ? 'checked' : '' }}>
                        <div class="w-11 h-6 bg-gray-200 rounded-full peer peer-checked:bg-blue-600 peer-focus:ring-4 peer-focus:ring-blue-300 after:content-[''] after:absolute after:top-0.5 after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:after:translate-x-full peer-checked:after:border-white"></div>
                    </label>
                </div>
            </div>
        </div>
        
        <div class="space-y-3">
            <div class="flex flex-col md:flex-row md:items-center gap-3">
                <label for="sms_reminder_hours" class="text-xs font-medium text-gray-700 md:w-40 flex items-center gap-1">
                    SMS Reminder Time (hours before event)
                    <div class="tooltip-wrapper" x-data="{ show: false }">
                        <span class="material-icons-outlined text-gray-400 text-sm cursor-help" 
                              @mouseenter="show = true" 
                              @mouseleave="show = false">
                            help_outline
                        </span>
                        <div x-show="show" x-transition class="tooltip-content">
                            How many hours before the event to send SMS reminders
                        </div>
                    </div>
                </label>
                <div class="flex-1">
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <span class="material-icons-outlined text-[#004aad] text-base">access_time</span>
                        </div>
                        <input 
                            type="number" 
                            id="sms_reminder_hours" 
                            name="sms_reminder_hours" 
                            value="{{ old('sms_reminder_hours', $config->sms_reminder_hours ?? 24) }}" 
                            min="1" 
                            max="72" 
                            class="w-full text-xs border-gray-300 rounded-[1px] pl-12 focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50"
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
                <span class="material-icons-outlined text-primary-DEFAULT mr-2">admin_panel_settings</span>
                Admin Notifications
            </h2>
        </div>
        
        <div class="p-4">
        <div class="grid grid-cols-1 gap-3 mb-4">
            <div class="flex justify-between items-center py-2 border-b border-gray-100">
                <div class="flex items-center gap-2">
                    <span class="material-icons-outlined text-primary-DEFAULT text-sm">warning</span>
                    <p class="text-xs font-medium text-gray-700 flex items-center gap-1">
                        System Errors
                        <div class="tooltip-wrapper" x-data="{ show: false }">
                            <span class="material-icons-outlined text-gray-400 text-sm cursor-help" 
                                  @mouseenter="show = true" 
                                  @mouseleave="show = false">
                                help_outline
                            </span>
                            <div x-show="show" x-transition class="tooltip-content">
                                Notify admins about system errors
                            </div>
                        </div>
                    </p>
                </div>
                <div>
                    <input type="hidden" name="admin_system_errors" value="0">
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" name="admin_system_errors" value="1" class="sr-only peer" {{ (old('admin_system_errors', $config->admin_system_errors ?? true)) ? 'checked' : '' }}>
                        <div class="w-11 h-6 bg-gray-200 rounded-full peer peer-checked:bg-blue-600 peer-focus:ring-4 peer-focus:ring-blue-300 after:content-[''] after:absolute after:top-0.5 after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:after:translate-x-full peer-checked:after:border-white"></div>
                    </label>
                </div>
            </div>
            
            <div class="flex justify-between items-center py-2 border-b border-gray-100">
                <div class="flex items-center gap-2">
                    <span class="material-icons-outlined text-primary-DEFAULT text-sm">new_releases</span>
                    <p class="text-xs font-medium text-gray-700 flex items-center gap-1">
                        New Registrations
                        <div class="tooltip-wrapper" x-data="{ show: false }">
                            <span class="material-icons-outlined text-gray-400 text-sm cursor-help" 
                                  @mouseenter="show = true" 
                                  @mouseleave="show = false">
                                help_outline
                            </span>
                            <div x-show="show" x-transition class="tooltip-content">
                                Notify admins about new user registrations
                            </div>
                        </div>
                    </p>
                </div>
                <div>
                    <input type="hidden" name="admin_new_registrations" value="0">
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" name="admin_new_registrations" value="1" class="sr-only peer" {{ (old('admin_new_registrations', $config->admin_new_registrations ?? false)) ? 'checked' : '' }}>
                        <div class="w-11 h-6 bg-gray-200 rounded-full peer peer-checked:bg-blue-600 peer-focus:ring-4 peer-focus:ring-blue-300 after:content-[''] after:absolute after:top-0.5 after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:after:translate-x-full peer-checked:after:border-white"></div>
                    </label>
                </div>
            </div>
            
            <div class="flex justify-between items-center py-2 border-b border-gray-100">
                <div class="flex items-center gap-2">
                    <span class="material-icons-outlined text-primary-DEFAULT text-sm">security</span>
                    <p class="text-xs font-medium text-gray-700 flex items-center gap-1">
                        Security Alerts
                        <div class="tooltip-wrapper" x-data="{ show: false }">
                            <span class="material-icons-outlined text-gray-400 text-sm cursor-help" 
                                  @mouseenter="show = true" 
                                  @mouseleave="show = false">
                                help_outline
                            </span>
                            <div x-show="show" x-transition class="tooltip-content">
                                Notify admins about security-related events
                            </div>
                        </div>
                    </p>
                </div>
                <div>
                    <input type="hidden" name="admin_security_alerts" value="0">
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" name="admin_security_alerts" value="1" class="sr-only peer" {{ (old('admin_security_alerts', $config->admin_security_alerts ?? true)) ? 'checked' : '' }}>
                        <div class="w-11 h-6 bg-gray-200 rounded-full peer peer-checked:bg-blue-600 peer-focus:ring-4 peer-focus:ring-blue-300 after:content-[''] after:absolute after:top-0.5 after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:after:translate-x-full peer-checked:after:border-white"></div>
                    </label>
                </div>
            </div>
        </div>
        
        <div class="space-y-3">
            <div class="flex flex-col md:flex-row md:items-center gap-3">
                <label for="admin_notification_email" class="text-xs font-medium text-gray-700 md:w-40 flex items-center gap-1">
                    Admin Notification Email
                    <div class="tooltip-wrapper" x-data="{ show: false }">
                        <span class="material-icons-outlined text-gray-400 text-sm cursor-help" 
                              @mouseenter="show = true" 
                              @mouseleave="show = false">
                            help_outline
                        </span>
                        <div x-show="show" x-transition class="tooltip-content">
                            Email address for admin notifications
                        </div>
                    </div>
                </label>
                <div class="flex-1">
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <span class="material-icons-outlined text-[#004aad] text-base">email</span>
                        </div>
                        <input 
                            type="email" 
                            id="admin_notification_email" 
                            name="admin_notification_email" 
                            value="{{ old('admin_notification_email', $config->admin_notification_email ?? '') }}" 
                            class="w-full text-xs border-gray-300 rounded-[1px] pl-12 focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50"
                        >
                    </div>
                </div>
            </div>
        </div>
        </div>
    </div>
</div>
