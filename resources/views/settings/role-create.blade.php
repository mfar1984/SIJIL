<x-app-layout>
    <x-slot name="breadcrumb">
        <span>Settings</span>
        <span class="mx-2 text-gray-500">/</span>
        <span>Role Management</span>
        <span class="mx-2 text-gray-500">/</span>
        <span>Create Role</span>
    </x-slot>

    <x-slot name="title">Create New Role</x-slot>

    <style>
        .tooltip-wrapper { position: relative; display: inline-flex; }
        .tooltip-content {
            position: absolute; bottom: 100%; left: 50%;
            transform: translateX(-50%) translateY(-4px);
            background-color: #1f2937; color: white;
            padding: 6px 10px; border-radius: 6px;
            font-size: 11px; white-space: nowrap;
            z-index: 1000; pointer-events: none;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }
        .tooltip-content::after {
            content: ''; position: absolute;
            top: 100%; left: 50%; transform: translateX(-50%);
            border: 4px solid transparent; border-top-color: #1f2937;
        }
    </style>

    <div class="bg-white rounded shadow-md border border-gray-300">
        <div class="p-6 border-b border-gray-200">
            <div class="flex items-center">
                <span class="material-icons-outlined mr-2 text-primary-DEFAULT">admin_panel_settings</span>
                <h1 class="text-xl font-bold text-gray-800">Create New Role</h1>
            </div>
            <p class="text-xs text-gray-500 mt-1 ml-8">Add a new role with specific permissions</p>
        </div>
        
        <div class="p-6">
            @if ($errors->any())
                <div class="mb-4 p-4 bg-red-50 border border-red-200 text-red-700 rounded">
                    <ul class="list-disc pl-5">
                        @foreach ($errors->all() as $error)
                            <li class="text-xs">{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('role.store') }}" class="space-y-2">
                @csrf
                
                <!-- Basic Information -->
                <div class="bg-white border border-gray-200 rounded-md shadow-sm">
                    <div class="bg-gray-50 border-b border-gray-200 px-4 py-3">
                        <h2 class="text-sm font-semibold text-gray-700 flex items-center">
                            <span class="material-icons-outlined text-primary-DEFAULT mr-2">badge</span>
                            Basic Information
                        </h2>
                    </div>
                    
                    <div class="p-4">
                        <div class="space-y-3">
                            <!-- Role Name -->
                            <div class="flex flex-col md:flex-row md:items-center gap-3">
                                <label for="role_name" class="text-xs font-medium text-gray-700 md:w-40 flex items-center gap-1">
                                    Role Name
                                    <div class="tooltip-wrapper" x-data="{ show: false }">
                                        <span class="material-icons-outlined text-gray-400 text-sm cursor-help" 
                                              @mouseenter="show = true" 
                                              @mouseleave="show = false">
                                            help_outline
                                        </span>
                                        <div x-show="show" x-transition class="tooltip-content">
                                            Enter a unique name for this role (e.g., Manager, Editor, Viewer)
                                        </div>
                                    </div>
                                </label>
                                <div class="flex-1">
                                    <input type="text" name="role_name" id="role_name" class="w-full text-xs border-gray-300 rounded-[1px] focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50" value="{{ old('role_name') }}" required>
                                </div>
                            </div>
                    
                            <!-- Role Description -->
                            <div class="flex flex-col md:flex-row md:items-center gap-3">
                                <label for="role_description" class="text-xs font-medium text-gray-700 md:w-40 flex items-center gap-1">
                                    Role Description
                                    <div class="tooltip-wrapper" x-data="{ show: false }">
                                        <span class="material-icons-outlined text-gray-400 text-sm cursor-help" 
                                              @mouseenter="show = true" 
                                              @mouseleave="show = false">
                                            help_outline
                                        </span>
                                        <div x-show="show" x-transition class="tooltip-content">
                                            Provide a brief description of this role's purpose and responsibilities
                                        </div>
                                    </div>
                                </label>
                                <div class="flex-1">
                                    <input type="text" name="role_description" id="role_description" class="w-full text-xs border-gray-300 rounded-[1px] focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50" value="{{ old('role_description') }}">
                                </div>
                            </div>
                    
                            <!-- Status -->
                            <div class="flex flex-col md:flex-row md:items-center gap-3">
                                <label for="status" class="text-xs font-medium text-gray-700 md:w-40 flex items-center gap-1">
                                    Status
                                    <div class="tooltip-wrapper" x-data="{ show: false }">
                                        <span class="material-icons-outlined text-gray-400 text-sm cursor-help" 
                                              @mouseenter="show = true" 
                                              @mouseleave="show = false">
                                            help_outline
                                        </span>
                                        <div x-show="show" x-transition class="tooltip-content">
                                            Inactive roles cannot be assigned to users
                                        </div>
                                    </div>
                                </label>
                                <div class="flex-1">
                                    <div class="relative">
                                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                            <span class="material-icons-outlined text-[#004aad] text-base">shield</span>
                                        </div>
                                        <select name="status" id="status" class="w-full text-xs border-gray-300 rounded-[1px] pl-12 focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50" required>
                                            <option value="active" {{ old('status') == 'active' ? 'selected' : '' }}>Active</option>
                                            <option value="inactive" {{ old('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Role Permissions -->
                <div class="bg-white border border-gray-200 rounded-md shadow-sm">
                    <div class="bg-gray-50 border-b border-gray-200 px-4 py-3">
                        <h2 class="text-sm font-semibold text-gray-700 flex items-center">
                            <span class="material-icons-outlined text-primary-DEFAULT mr-2">verified_user</span>
                            Role Permissions
                        </h2>
                    </div>
                    
                    <div class="p-4">
                        <p class="text-xs text-gray-500 mb-4">Select the permissions this role will have in the system</p>
                        
                        <x-settings.partials.permissions-matrix :permissions="$permissions" :checkedPermissionNames="[]" mode="edit" />
                        
                        <div class="mt-6 bg-blue-50 border border-blue-100 rounded p-4">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <span class="material-icons-outlined text-blue-600">info</span>
                                </div>
                                <div class="ml-3">
                                    <h3 class="text-xs font-medium text-blue-800">Permission Information</h3>
                                    <div class="mt-1 text-[10px] text-blue-700">
                                        <p>Carefully review permissions before assigning them to a role. Some permissions may give users access to sensitive information or functions.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="flex justify-end space-x-3 pt-4">
                    <a href="{{ route('role.management') }}" class="px-3 h-[36px] bg-gradient-to-r from-red-600 to-red-500 hover:from-red-700 hover:to-red-600 text-white rounded shadow-sm text-xs font-medium transition-colors duration-200 ease-in-out flex items-center">
                        <span class="material-icons-outlined text-xs mr-1">cancel</span>
                        Cancel
                    </a>
                    <button type="submit" class="px-3 h-[36px] bg-gradient-to-r from-green-600 to-green-500 hover:from-green-700 hover:to-green-600 text-white rounded shadow-sm text-xs font-medium transition-colors duration-200 ease-in-out flex items-center">
                        <span class="material-icons-outlined text-xs mr-1">save</span>
                        Save Role
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout> 