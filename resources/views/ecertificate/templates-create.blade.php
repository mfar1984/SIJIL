<x-app-layout>
    <x-slot name="breadcrumb">
        <span>PWA Management</span>
        <span class="mx-2">/</span>
        <span>Email Templates</span>
        <span class="mx-2">/</span>
        <span>Create</span>
    </x-slot>

    <x-slot name="title">Create PWA Email Template</x-slot>

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
                <div class="flex items-center">
                    <span class="material-icons-outlined mr-2 text-indigo-500">email</span>
                    <h1 class="text-xl font-bold text-gray-800">Create Email Template</h1>
                </div>
                <a href="{{ route('pwa.templates') }}" class="bg-gray-100 text-gray-700 px-3 py-1 rounded text-xs font-medium">Back</a>
            </div>
        </div>

        <form method="POST" action="{{ route('pwa.templates.store') }}" class="p-6 space-y-2">
            @csrf
            
            <!-- Template Information Section -->
            <div class="bg-white border border-gray-200 rounded-md shadow-sm">
                <div class="bg-gray-50 border-b border-gray-200 px-4 py-3">
                    <h2 class="text-sm font-semibold text-gray-700 flex items-center">
                        <span class="material-icons-outlined text-primary-DEFAULT mr-2">email</span>
                        Template Information
                    </h2>
                </div>
                
                <div class="p-4">
                    <div class="space-y-3">
                        <!-- Name -->
                        <div class="flex flex-col md:flex-row md:items-center gap-3">
                            <label for="name" class="text-xs font-medium text-gray-700 md:w-48 shrink-0 flex items-center gap-1">
                                Template Name
                                <div class="tooltip-wrapper" x-data="{ show: false }">
                                    <span class="material-icons-outlined text-gray-400 text-sm cursor-help" 
                                          @mouseenter="show = true" 
                                          @mouseleave="show = false">
                                        help_outline
                                    </span>
                                    <div x-show="show" x-transition class="tooltip-content">
                                        Enter a descriptive name for the email template
                                    </div>
                                </div>
                            </label>
                            <div class="flex-1">
                                <input type="text" name="name" id="name" required class="w-full px-2 py-1 text-xs border border-gray-300 rounded focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50" />
                            </div>
                        </div>
                        
                        <!-- Type -->
                        <div class="flex flex-col md:flex-row md:items-center gap-3">
                            <label for="type" class="text-xs font-medium text-gray-700 md:w-48 shrink-0 flex items-center gap-1">
                                Template Type
                                <div class="tooltip-wrapper" x-data="{ show: false }">
                                    <span class="material-icons-outlined text-gray-400 text-sm cursor-help" 
                                          @mouseenter="show = true" 
                                          @mouseleave="show = false">
                                        help_outline
                                    </span>
                                    <div x-show="show" x-transition class="tooltip-content">
                                        Select the type of email template
                                    </div>
                                </div>
                            </label>
                            <div class="flex-1">
                                <select name="type" id="type" class="w-full px-2 py-1 text-xs border border-gray-300 rounded focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50">
                                    <option value="custom">Custom</option>
                                    <option value="welcome">Welcome</option>
                                    <option value="password_reset">Password Reset</option>
                                    <option value="event_reminder">Event Reminder</option>
                                </select>
                            </div>
                        </div>
                        
                        <!-- Subject -->
                        <div class="flex flex-col md:flex-row md:items-center gap-3">
                            <label for="subject" class="text-xs font-medium text-gray-700 md:w-48 shrink-0 flex items-center gap-1">
                                Email Subject
                                <div class="tooltip-wrapper" x-data="{ show: false }">
                                    <span class="material-icons-outlined text-gray-400 text-sm cursor-help" 
                                          @mouseenter="show = true" 
                                          @mouseleave="show = false">
                                        help_outline
                                    </span>
                                    <div x-show="show" x-transition class="tooltip-content">
                                        Subject line for the email
                                    </div>
                                </div>
                            </label>
                            <div class="flex-1">
                                <input type="text" name="subject" id="subject" required class="w-full px-2 py-1 text-xs border border-gray-300 rounded focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50" />
                            </div>
                        </div>
                        
                        <!-- Content -->
                        <div class="flex flex-col md:flex-row md:items-start gap-3">
                            <label for="content" class="text-xs font-medium text-gray-700 md:w-48 shrink-0 pt-2 flex items-center gap-1">
                                Email Content
                                <div class="tooltip-wrapper" x-data="{ show: false }">
                                    <span class="material-icons-outlined text-gray-400 text-sm cursor-help" 
                                          @mouseenter="show = true" 
                                          @mouseleave="show = false">
                                        help_outline
                                    </span>
                                    <div x-show="show" x-transition class="tooltip-content">
                                        Use variables like @{{name}} @{{email}} @{{pwa_link}}
                                    </div>
                                </div>
                            </label>
                            <div class="flex-1">
                                <textarea name="content" id="content" rows="10" class="w-full px-2 py-1 text-xs border border-gray-300 rounded focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50" placeholder="Use variables like @{{name}} @{{email}} @{{pwa_link}}"></textarea>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Form Actions -->
            <div class="flex justify-end space-x-3 pt-4">
                <a href="{{ route('pwa.templates') }}" class="px-3 h-[36px] bg-gradient-to-r from-red-600 to-red-500 hover:from-red-700 hover:to-red-600 text-white rounded shadow-sm text-xs font-medium transition-colors duration-200 ease-in-out flex items-center">
                    <span class="material-icons-outlined text-xs mr-1">cancel</span>
                    Cancel
                </a>
                <button type="submit" class="px-3 h-[36px] bg-gradient-to-r from-green-600 to-green-500 hover:from-green-700 hover:to-green-600 text-white rounded shadow-sm text-xs font-medium transition-colors duration-200 ease-in-out flex items-center">
                    <span class="material-icons-outlined text-xs mr-1">save</span>
                    Create
                </button>
            </div>
        </form>
    </div>
</x-app-layout>


