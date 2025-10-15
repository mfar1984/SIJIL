<x-app-layout>
    <x-slot name="breadcrumb">
        <span>PWA Management</span>
        <span class="mx-2">/</span>
        <span>Email Templates</span>
    </x-slot>

    <x-slot name="title">PWA Email Templates</x-slot>

    <div class="bg-white rounded shadow-md border border-gray-300">
        <div class="p-6 border-b border-gray-200">
            <div class="flex justify-between items-start">
                <div>
                    <div class="flex items-center">
                        <span class="material-icons mr-2 text-indigo-500">email</span>
                        <h1 class="text-xl font-bold text-gray-800">PWA Email Templates</h1>
                    </div>
                    <p class="text-xs text-gray-500 mt-1 ml-8">Customize welcome and password reset emails for PWA participants</p>
                </div>
                <button class="bg-gradient-to-r from-indigo-600 to-indigo-500 hover:from-indigo-700 hover:to-indigo-600 text-white px-3 py-1 rounded shadow-sm font-medium flex items-center text-xs transition-colors duration-200 ease-in-out">
                    <span class="material-icons text-xs mr-1">add</span>
                    New Template
                </button>
            </div>
        </div>
        
        <div class="p-4">
            <!-- Template Categories -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 mb-6">
                <!-- Welcome Email Template -->
                <div class="bg-white border border-gray-200 rounded-lg p-4 hover:shadow-md transition-shadow">
                    <div class="flex items-center justify-between mb-3">
                        <h3 class="text-sm font-semibold text-gray-800">Welcome Email</h3>
                        <span class="bg-green-100 text-green-800 text-xs px-2 py-1 rounded">Active</span>
                    </div>
                    <p class="text-xs text-gray-600 mb-3">Sent to new participants when they first register for PWA access</p>
                    <div class="space-y-2 mb-3">
                        <div class="flex items-center text-xs text-gray-500">
                            <span class="material-icons text-xs mr-1">schedule</span>
                            Last updated: 2 days ago
                        </div>
                        <div class="flex items-center text-xs text-gray-500">
                            <span class="material-icons text-xs mr-1">send</span>
                            Sent 156 times this month
                        </div>
                    </div>
                    <div class="flex gap-2">
                        <button class="flex-1 bg-blue-500 text-white px-2 py-1 rounded text-xs font-medium">Edit</button>
                        <button class="flex-1 bg-gray-100 text-gray-700 px-2 py-1 rounded text-xs font-medium">Preview</button>
                        <button class="bg-gray-100 text-gray-700 px-2 py-1 rounded text-xs">
                            <span class="material-icons text-xs">more_vert</span>
                        </button>
                    </div>
                </div>

                <!-- Password Reset Template -->
                <div class="bg-white border border-gray-200 rounded-lg p-4 hover:shadow-md transition-shadow">
                    <div class="flex items-center justify-between mb-3">
                        <h3 class="text-sm font-semibold text-gray-800">Password Reset</h3>
                        <span class="bg-green-100 text-green-800 text-xs px-2 py-1 rounded">Active</span>
                    </div>
                    <p class="text-xs text-gray-600 mb-3">Sent when participants request password reset</p>
                    <div class="space-y-2 mb-3">
                        <div class="flex items-center text-xs text-gray-500">
                            <span class="material-icons text-xs mr-1">schedule</span>
                            Last updated: 1 week ago
                        </div>
                        <div class="flex items-center text-xs text-gray-500">
                            <span class="material-icons text-xs mr-1">send</span>
                            Sent 23 times this month
                        </div>
                    </div>
                    <div class="flex gap-2">
                        <button class="flex-1 bg-blue-500 text-white px-2 py-1 rounded text-xs font-medium">Edit</button>
                        <button class="flex-1 bg-gray-100 text-gray-700 px-2 py-1 rounded text-xs font-medium">Preview</button>
                        <button class="bg-gray-100 text-gray-700 px-2 py-1 rounded text-xs">
                            <span class="material-icons text-xs">more_vert</span>
                        </button>
                    </div>
                </div>

                <!-- Event Reminder Template -->
                <div class="bg-white border border-gray-200 rounded-lg p-4 hover:shadow-md transition-shadow">
                    <div class="flex items-center justify-between mb-3">
                        <h3 class="text-sm font-semibold text-gray-800">Event Reminder</h3>
                        <span class="bg-yellow-100 text-yellow-800 text-xs px-2 py-1 rounded">Draft</span>
                    </div>
                    <p class="text-xs text-gray-600 mb-3">Remind participants about upcoming events</p>
                    <div class="space-y-2 mb-3">
                        <div class="flex items-center text-xs text-gray-500">
                            <span class="material-icons text-xs mr-1">schedule</span>
                            Last updated: 3 days ago
                        </div>
                        <div class="flex items-center text-xs text-gray-500">
                            <span class="material-icons text-xs mr-1">send</span>
                            Not sent yet
                        </div>
                    </div>
                    <div class="flex gap-2">
                        <button class="flex-1 bg-blue-500 text-white px-2 py-1 rounded text-xs font-medium">Edit</button>
                        <button class="flex-1 bg-gray-100 text-gray-700 px-2 py-1 rounded text-xs font-medium">Preview</button>
                        <button class="bg-gray-100 text-gray-700 px-2 py-1 rounded text-xs">
                            <span class="material-icons text-xs">more_vert</span>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Template Editor Section -->
            <div class="bg-white border border-gray-200 rounded-lg">
                <div class="p-4 border-b border-gray-200">
                    <h3 class="text-sm font-semibold text-gray-800">Welcome Email Template</h3>
                    <p class="text-xs text-gray-600">Edit the welcome email template for new PWA participants</p>
                </div>
                
                <div class="p-4">
                    <!-- Template Variables -->
                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-4">
                        <h4 class="text-xs font-semibold text-blue-800 mb-2">Available Variables</h4>
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-2 text-xs">
                            <code class="bg-blue-100 text-blue-800 px-2 py-1 rounded">@{{name}}</code>
                            <code class="bg-blue-100 text-blue-800 px-2 py-1 rounded">@{{email}}</code>
                            <code class="bg-blue-100 text-blue-800 px-2 py-1 rounded">@{{password}}</code>
                            <code class="bg-blue-100 text-blue-800 px-2 py-1 rounded">@{{pwa_link}}</code>
                            <code class="bg-blue-100 text-blue-800 px-2 py-1 rounded">@{{event_name}}</code>
                            <code class="bg-blue-100 text-blue-800 px-2 py-1 rounded">@{{organization}}</code>
                            <code class="bg-blue-100 text-blue-800 px-2 py-1 rounded">@{{login_url}}</code>
                            <code class="bg-blue-100 text-blue-800 px-2 py-1 rounded">@{{support_email}}</code>
                        </div>
                    </div>

                    <!-- Email Subject -->
                    <div class="mb-4">
                        <label class="block text-xs font-medium text-gray-700 mb-1">Email Subject</label>
                        <input type="text" value="Welcome to E-Certificate Online - Your PWA Access" class="w-full px-2 py-1 text-xs border border-gray-300 rounded focus:ring focus:ring-indigo-200 focus:border-indigo-300">
                    </div>

                    <!-- Email Content -->
                    <div class="mb-4">
                        <label class="block text-xs font-medium text-gray-700 mb-1">Email Content</label>
                        <div class="border border-gray-300 rounded">
                            <div class="bg-gray-50 px-3 py-2 border-b border-gray-300">
                                <div class="flex items-center justify-between">
                                    <span class="text-xs text-gray-600">Rich Text Editor</span>
                                    <div class="flex gap-1">
                                        <button class="p-1 hover:bg-gray-200 rounded text-xs">
                                            <span class="material-icons text-xs">format_bold</span>
                                        </button>
                                        <button class="p-1 hover:bg-gray-200 rounded text-xs">
                                            <span class="material-icons text-xs">format_italic</span>
                                        </button>
                                        <button class="p-1 hover:bg-gray-200 rounded text-xs">
                                            <span class="material-icons text-xs">link</span>
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <div class="p-4 min-h-64">
                                <div class="prose prose-sm max-w-none">
                                    <p><strong>Dear @{{name}},</strong></p>
                                    <p>Welcome to <strong>E-Certificate Online</strong>! Your account has been successfully created and you now have access to our mobile application.</p>
                                    
                                    <div class="bg-gray-50 p-3 rounded my-4">
                                        <p class="text-xs font-medium mb-2">Your Login Credentials:</p>
                                        <p class="text-xs"><strong>Email:</strong> @{{email}}</p>
                                        <p class="text-xs"><strong>Temporary Password:</strong> @{{password}}</p>
                                    </div>
                                    
                                    <p><strong>Important:</strong> For security reasons, you will be required to change your password on your first login.</p>
                                    
                                    <div class="bg-blue-50 p-3 rounded my-4">
                                        <p class="text-xs font-medium mb-2">Getting Started:</p>
                                        <ol class="text-xs list-decimal list-inside space-y-1">
                                            <li>Download our mobile app or visit: @{{pwa_link}}</li>
                                            <li>Login with your email and temporary password</li>
                                            <li>Change your password when prompted</li>
                                            <li>Start exploring your events and certificates!</li>
                                        </ol>
                                    </div>
                                    
                                    <p>If you have any questions or need assistance, please contact us at @{{support_email}}.</p>
                                    
                                    <p>Best regards,<br>
                                    <strong>E-Certificate Online Team</strong></p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="flex justify-between items-center">
                        <div class="flex gap-2">
                            <button class="bg-indigo-500 text-white px-3 py-1 rounded text-xs font-medium">Save Template</button>
                            <button class="bg-gray-100 text-gray-700 px-3 py-1 rounded text-xs font-medium">Preview Email</button>
                            <button class="bg-gray-100 text-gray-700 px-3 py-1 rounded text-xs font-medium">Send Test</button>
                        </div>
                        <button class="text-red-600 text-xs font-medium">Reset to Default</button>
                    </div>
                </div>
            </div>

            <!-- Email Statistics -->
            <div class="mt-6 grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="bg-white border border-gray-200 rounded-lg p-4">
                    <h4 class="text-xs font-semibold text-gray-800 mb-3">Email Performance</h4>
                    <div class="space-y-3">
                        <div class="flex justify-between text-xs">
                            <span>Open Rate</span>
                            <span class="font-medium text-green-600">78.5%</span>
                        </div>
                        <div class="flex justify-between text-xs">
                            <span>Click Rate</span>
                            <span class="font-medium text-blue-600">23.2%</span>
                        </div>
                        <div class="flex justify-between text-xs">
                            <span>Bounce Rate</span>
                            <span class="font-medium text-red-600">2.1%</span>
                        </div>
                    </div>
                </div>

                <div class="bg-white border border-gray-200 rounded-lg p-4">
                    <h4 class="text-xs font-semibold text-gray-800 mb-3">Recent Activity</h4>
                    <div class="space-y-2 text-xs">
                        <div class="flex justify-between">
                            <span>Welcome emails sent</span>
                            <span class="font-medium">156</span>
                        </div>
                        <div class="flex justify-between">
                            <span>Password resets</span>
                            <span class="font-medium">23</span>
                        </div>
                        <div class="flex justify-between">
                            <span>Failed deliveries</span>
                            <span class="font-medium text-red-600">3</span>
                        </div>
                    </div>
                </div>

                <div class="bg-white border border-gray-200 rounded-lg p-4">
                    <h4 class="text-xs font-semibold text-gray-800 mb-3">Quick Actions</h4>
                    <div class="space-y-2">
                        <button class="w-full bg-green-500 text-white px-2 py-1 rounded text-xs font-medium">Send Welcome Email</button>
                        <button class="w-full bg-blue-500 text-white px-2 py-1 rounded text-xs font-medium">Export Templates</button>
                        <button class="w-full bg-purple-500 text-white px-2 py-1 rounded text-xs font-medium">Bulk Email</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout> 