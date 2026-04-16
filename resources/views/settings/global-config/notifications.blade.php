<div x-show="activeTab === 'notifications'" class="space-y-2">
    <div class="bg-white border border-gray-200 rounded-md shadow-sm">
        <div class="bg-gray-50 border-b border-gray-200 px-4 py-3">
            <h2 class="text-sm font-semibold text-gray-700 flex items-center">
                <span class="material-icons text-primary-DEFAULT mr-2">email</span>
                Email Notifications
            </h2>
        </div>
        
        <div class="p-4">
        <div class="grid grid-cols-1 gap-3">
            <div class="flex justify-between items-center py-2 border-b border-gray-100">
                <div class="flex items-center">
                    <span class="material-icons text-primary-DEFAULT mr-2 text-sm">person_add</span>
                    <div>
                        <p class="text-xs font-medium text-gray-700">New User Registration</p>
                        <p class="text-[10px] text-gray-500">Send email when a new user registers</p>
                    </div>
                </div>
                <div>
                    <input type="hidden" name="email_new_user_registration" value="0">
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" name="email_new_user_registration" value="1" class="sr-only peer" {{ (old('email_new_user_registration', $config->email_new_user_registration ?? true)) ? 'checked' : '' }} :disabled="!isEditing">
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
                    <input type="hidden" name="email_event_registration" value="0">
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" name="email_event_registration" value="1" class="sr-only peer" {{ (old('email_event_registration', $config->email_event_registration ?? true)) ? 'checked' : '' }} :disabled="!isEditing">
                        <div class="w-9 h-5 bg-gray-200 peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-primary-light rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-primary-DEFAULT"></div>
                    </label>
                </div>
            </div>
            
            <div class="flex justify-between items-center py-2 border-b border-gray-100">
                <div class="flex items-center">
                    <span class="material-icons text-primary-DEFAULT mr-2 text-sm">notifications</span>
                    <div>
                        <p class="text-xs font-medium text-gray-700">Event Reminder</p>
                        <p class="text-[10px] text-gray-500">Send reminder email before event starts</p>
                    </div>
                </div>
                <div>
                    <input type="hidden" name="email_event_reminder" value="0">
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" name="email_event_reminder" value="1" class="sr-only peer" {{ (old('email_event_reminder', $config->email_event_reminder ?? true)) ? 'checked' : '' }} :disabled="!isEditing">
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
                    <input type="hidden" name="email_certificate_generated" value="0">
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" name="email_certificate_generated" value="1" class="sr-only peer" {{ (old('email_certificate_generated', $config->email_certificate_generated ?? true)) ? 'checked' : '' }} :disabled="!isEditing">
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
                    <input type="hidden" name="email_password_reset" value="0">
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" name="email_password_reset" value="1" class="sr-only peer" {{ (old('email_password_reset', $config->email_password_reset ?? true)) ? 'checked' : '' }} :disabled="!isEditing">
                        <div class="w-9 h-5 bg-gray-200 peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-primary-light rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-primary-DEFAULT"></div>
                    </label>
                </div>
            </div>
        </div>
        </div>
    </div>
    
    <div class="bg-white border border-gray-200 rounded-md shadow-sm">
        <div class="bg-gray-50 border-b border-gray-200 px-4 py-3">
            <h2 class="text-sm font-semibold text-gray-700 flex items-center">
                <span class="material-icons text-primary-DEFAULT mr-2">sms</span>
                SMS Notifications
            </h2>
        </div>
        
        <div class="p-4">
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
                    <input type="hidden" name="sms_event_registration" value="0">
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" name="sms_event_registration" value="1" class="sr-only peer" {{ (old('sms_event_registration', $config->sms_event_registration ?? false)) ? 'checked' : '' }} :disabled="!isEditing">
                        <div class="w-9 h-5 bg-gray-200 peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-primary-light rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-primary-DEFAULT"></div>
                    </label>
                </div>
            </div>
            
            <div class="flex justify-between items-center py-2 border-b border-gray-100">
                <div class="flex items-center">
                    <span class="material-icons text-primary-DEFAULT mr-2 text-sm">notifications</span>
                    <div>
                        <p class="text-xs font-medium text-gray-700">Event Reminder</p>
                        <p class="text-[10px] text-gray-500">Send SMS reminder before event starts</p>
                    </div>
                </div>
                <div>
                    <input type="hidden" name="sms_event_reminder" value="0">
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" name="sms_event_reminder" value="1" class="sr-only peer" {{ (old('sms_event_reminder', $config->sms_event_reminder ?? false)) ? 'checked' : '' }} :disabled="!isEditing">
                        <div class="w-9 h-5 bg-gray-200 peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-primary-light rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-primary-DEFAULT"></div>
                    </label>
                </div>
            </div>
            
            <div class="flex justify-between items-center py-2 border-b border-gray-100">
                <div class="flex items-center">
                    <span class="material-icons text-primary-DEFAULT mr-2 text-sm">workspace_premium</span>
                    <div>
                        <p class="text-xs font-medium text-gray-700">Certificate Generated</p>
                        <p class="text-[10px] text-gray-500">Send SMS when a certificate is generated</p>
                    </div>
                </div>
                <div>
                    <input type="hidden" name="sms_certificate_generated" value="0">
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" name="sms_certificate_generated" value="1" class="sr-only peer" {{ (old('sms_certificate_generated', $config->sms_certificate_generated ?? false)) ? 'checked' : '' }} :disabled="!isEditing">
                        <div class="w-9 h-5 bg-gray-200 peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-primary-light rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-primary-DEFAULT"></div>
                    </label>
                </div>
            </div>
        </div>
        
        <div class="space-y-3">
            <div class="flex flex-col md:flex-row md:items-start gap-1">
                <label for="sms_reminder_hours" class="text-xs font-medium text-gray-700 md:w-40 pt-2">
                    SMS Reminder Time (hours before event)
                </label>
                <div class="flex-1">
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
        </div>
        </div>
    </div>
    
    <div class="bg-white border border-gray-200 rounded-md shadow-sm">
        <div class="bg-gray-50 border-b border-gray-200 px-4 py-3">
            <h2 class="text-sm font-semibold text-gray-700 flex items-center">
                <span class="material-icons text-primary-DEFAULT mr-2">admin_panel_settings</span>
                Admin Notifications
            </h2>
        </div>
        
        <div class="p-4">
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
                    <input type="hidden" name="admin_system_errors" value="0">
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" name="admin_system_errors" value="1" class="sr-only peer" {{ (old('admin_system_errors', $config->admin_system_errors ?? true)) ? 'checked' : '' }} :disabled="!isEditing">
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
                    <input type="hidden" name="admin_new_registrations" value="0">
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" name="admin_new_registrations" value="1" class="sr-only peer" {{ (old('admin_new_registrations', $config->admin_new_registrations ?? false)) ? 'checked' : '' }} :disabled="!isEditing">
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
                    <input type="hidden" name="admin_security_alerts" value="0">
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" name="admin_security_alerts" value="1" class="sr-only peer" {{ (old('admin_security_alerts', $config->admin_security_alerts ?? true)) ? 'checked' : '' }} :disabled="!isEditing">
                        <div class="w-9 h-5 bg-gray-200 peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-primary-light rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-primary-DEFAULT"></div>
                    </label>
                </div>
            </div>
        </div>
        
        <div class="space-y-3">
            <div class="flex flex-col md:flex-row md:items-start gap-1">
                <label for="admin_notification_email" class="text-xs font-medium text-gray-700 md:w-40 pt-2">
                    Admin Notification Email
                </label>
                <div class="flex-1">
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
        </div>
    </div>
</div>
