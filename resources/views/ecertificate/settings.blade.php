<x-app-layout>
    <x-slot name="breadcrumb">
        <span>PWA Management</span>
        <span class="mx-2">/</span>
        <span>Event Settings</span>
    </x-slot>

    <x-slot name="title">PWA Settings</x-slot>

    <div class="bg-white rounded shadow-md border border-gray-300">
        <div class="p-6 border-b border-gray-200">
            <div class="flex justify-between items-start">
                <div>
                    <div class="flex items-center">
                        <span class="material-icons mr-2 text-indigo-500">settings</span>
                        <h1 class="text-xl font-bold text-gray-800">PWA Settings</h1>
                    </div>
                    <p class="text-xs text-gray-500 mt-1 ml-8">Configure PWA access and participant account settings</p>
                </div>
                <button class="bg-gradient-to-r from-indigo-600 to-indigo-500 hover:from-indigo-700 hover:to-indigo-600 text-white px-3 py-1 rounded shadow-sm font-medium flex items-center text-xs transition-colors duration-200 ease-in-out">
                    <span class="material-icons text-xs mr-1">save</span>
                    Save Settings
                </button>
            </div>
        </div>
        
        <div class="p-4">
            <!-- Settings Tabs -->
            <div class="border-b border-gray-200 mb-4">
                <nav class="flex space-x-6">
                    <button class="border-b-2 border-indigo-500 text-indigo-600 px-1 py-2 text-xs font-medium">General Settings</button>
                    <button class="border-b-2 border-transparent text-gray-500 hover:text-gray-700 px-1 py-2 text-xs font-medium">Event Access</button>
                    <button class="border-b-2 border-transparent text-gray-500 hover:text-gray-700 px-1 py-2 text-xs font-medium">Auto-Generation</button>
                    <button class="border-b-2 border-transparent text-gray-500 hover:text-gray-700 px-1 py-2 text-xs font-medium">Security</button>
                </nav>
            </div>

            <!-- General Settings -->
            <div class="space-y-4">
                <!-- PWA Access Control -->
                <div class="bg-white border border-gray-200 rounded-lg p-4">
                    <h3 class="text-sm font-semibold text-gray-800 mb-3">PWA Access Control</h3>
                    <div class="space-y-3">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-xs font-medium text-gray-700">Enable PWA Access</p>
                                <p class="text-xs text-gray-500">Allow participants to access the mobile application</p>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" checked class="sr-only peer">
                                <div class="w-9 h-5 bg-gray-200 peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-indigo-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[1px] after:left-[1px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-indigo-600"></div>
                            </label>
                        </div>
                        
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-xs font-medium text-gray-700">Auto-Create PWA Accounts</p>
                                <p class="text-xs text-gray-500">Automatically create PWA accounts during event registration</p>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" checked class="sr-only peer">
                                <div class="w-9 h-5 bg-gray-200 peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-indigo-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[1px] after:left-[1px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-indigo-600"></div>
                            </label>
                        </div>
                        
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-xs font-medium text-gray-700">Force Password Change</p>
                                <p class="text-xs text-gray-500">Require participants to change password on first login</p>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" checked class="sr-only peer">
                                <div class="w-9 h-5 bg-gray-200 peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-indigo-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[1px] after:left-[1px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-indigo-600"></div>
                            </label>
                        </div>
                    </div>
                </div>

                <!-- Event-Specific Settings -->
                <div class="bg-white border border-gray-200 rounded-lg p-4">
                    <h3 class="text-sm font-semibold text-gray-800 mb-3">Event-Specific Settings</h3>
                    <div class="space-y-3">
                        <div>
                            <label class="block text-xs font-medium text-gray-700 mb-1">Default PWA Access for New Events</label>
                            <select class="w-full px-2 py-1 text-xs border border-gray-300 rounded focus:ring focus:ring-indigo-200 focus:border-indigo-300 bg-white">
                                <option>Enabled by default</option>
                                <option>Disabled by default</option>
                                <option>Ask organizer during event creation</option>
                            </select>
                        </div>
                        
                        <div>
                            <label class="block text-xs font-medium text-gray-700 mb-1">PWA Access Checkbox Label</label>
                            <input type="text" value="Enable E-Certificate Online mobile access" class="w-full px-2 py-1 text-xs border border-gray-300 rounded focus:ring focus:ring-indigo-200 focus:border-indigo-300">
                            <p class="text-xs text-gray-500 mt-1">This text will appear on the event registration form</p>
                        </div>
                        
                        <div>
                            <label class="block text-xs font-medium text-gray-700 mb-1">PWA Access Checkbox Default State</label>
                            <select class="w-full px-2 py-1 text-xs border border-gray-300 rounded focus:ring focus:ring-indigo-200 focus:border-indigo-300 bg-white">
                                <option>Checked (Opt-in)</option>
                                <option>Unchecked (Opt-out)</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Password Settings -->
                <div class="bg-white border border-gray-200 rounded-lg p-4">
                    <h3 class="text-sm font-semibold text-gray-800 mb-3">Password Settings</h3>
                    <div class="space-y-3">
                        <div>
                            <label class="block text-xs font-medium text-gray-700 mb-1">Auto-Generated Password Length</label>
                            <input type="number" value="8" min="6" max="16" class="w-20 px-2 py-1 text-xs border border-gray-300 rounded focus:ring focus:ring-indigo-200 focus:border-indigo-300">
                            <p class="text-xs text-gray-500 mt-1">Number of characters for auto-generated passwords</p>
                        </div>
                        
                        <div>
                            <label class="block text-xs font-medium text-gray-700 mb-1">Password Complexity</label>
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
                        
                        <div>
                            <label class="block text-xs font-medium text-gray-700 mb-1">Password Expiry</label>
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

                <!-- Email Settings -->
                <div class="bg-white border border-gray-200 rounded-lg p-4">
                    <h3 class="text-sm font-semibold text-gray-700 mb-3">Email Settings</h3>
                    <div class="space-y-3">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-xs font-medium text-gray-700">Send Welcome Email</p>
                                <p class="text-xs text-gray-500">Send welcome email with credentials to new PWA participants</p>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" checked class="sr-only peer">
                                <div class="w-9 h-5 bg-gray-200 peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-indigo-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[1px] after:left-[1px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-indigo-600"></div>
                            </label>
                        </div>
                        
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-xs font-medium text-gray-700">Include PWA App Link</p>
                                <p class="text-xs text-gray-500">Include mobile app download link in welcome emails</p>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" checked class="sr-only peer">
                                <div class="w-9 h-5 bg-gray-200 peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-indigo-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[1px] after:left-[1px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-indigo-600"></div>
                            </label>
                        </div>
                        
                        <div>
                            <label class="block text-xs font-medium text-gray-700 mb-1">PWA App Link</label>
                            <input type="url" value="https://apps.e-certificate.com.my" class="w-full px-2 py-1 text-xs border border-gray-300 rounded focus:ring focus:ring-indigo-200 focus:border-indigo-300">
                            <p class="text-xs text-gray-500 mt-1">URL for participants to download or access the PWA</p>
                        </div>
                        
                        <div>
                            <label class="block text-xs font-medium text-gray-700 mb-1">Support Email</label>
                            <input type="email" value="support@e-certificate.com.my" class="w-full px-2 py-1 text-xs border border-gray-300 rounded focus:ring focus:ring-indigo-200 focus:border-indigo-300">
                            <p class="text-xs text-gray-500 mt-1">Email address for participant support inquiries</p>
                        </div>
                    </div>
                </div>

                <!-- Data Synchronization -->
                <div class="bg-white border border-gray-200 rounded-lg p-4">
                    <h3 class="text-sm font-semibold text-gray-800 mb-3">Data Synchronization</h3>
                    <div class="space-y-3">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-xs font-medium text-gray-700">Real-time Sync</p>
                                <p class="text-xs text-gray-500">Automatically sync data between participants and PWA participants</p>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" checked class="sr-only peer">
                                <div class="w-9 h-5 bg-gray-200 peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-indigo-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[1px] after:left-[1px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-indigo-600"></div>
                            </label>
                        </div>
                        
                        <div>
                            <label class="block text-xs font-medium text-gray-700 mb-1">Sync Fields</label>
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

                <!-- Advanced Settings -->
                <div class="bg-white border border-gray-200 rounded-lg p-4">
                    <h3 class="text-sm font-semibold text-gray-800 mb-3">Advanced Settings</h3>
                    <div class="space-y-3">
                        <div>
                            <label class="block text-xs font-medium text-gray-700 mb-1">Session Timeout</label>
                            <select class="w-full px-2 py-1 text-xs border border-gray-300 rounded focus:ring focus:ring-indigo-200 focus:border-indigo-300 bg-white">
                                <option>30 minutes</option>
                                <option>1 hour</option>
                                <option>2 hours</option>
                                <option>4 hours</option>
                                <option>8 hours</option>
                                <option>24 hours</option>
                            </select>
                        </div>
                        
                        <div>
                            <label class="block text-xs font-medium text-gray-700 mb-1">Maximum Login Attempts</label>
                            <input type="number" value="5" min="3" max="10" class="w-20 px-2 py-1 text-xs border border-gray-300 rounded focus:ring focus:ring-indigo-200 focus:border-indigo-300">
                            <p class="text-xs text-gray-500 mt-1">Number of failed login attempts before account lockout</p>
                        </div>
                        
                        <div>
                            <label class="block text-xs font-medium text-gray-700 mb-1">Account Lockout Duration</label>
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

            <!-- Action Buttons -->
            <div class="flex justify-between items-center mt-6 pt-4 border-t border-gray-200">
                <button class="bg-gray-100 text-gray-700 px-3 py-1 rounded text-xs font-medium">Reset to Defaults</button>
                <div class="flex gap-2">
                    <button class="bg-gray-100 text-gray-700 px-3 py-1 rounded text-xs font-medium">Cancel</button>
                    <button class="bg-indigo-500 text-white px-3 py-1 rounded text-xs font-medium">Save Settings</button>
                </div>
            </div>
        </div>
    </div>
</x-app-layout> 