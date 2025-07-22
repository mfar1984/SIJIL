<x-app-layout>
    <x-slot name="breadcrumb">
        <span>Settings</span>
        <span class="mx-2 text-gray-500">/</span>
        <span>Role Management</span>
        <span class="mx-2 text-gray-500">/</span>
        <span>Create Role</span>
    </x-slot>

    <x-slot name="title">Create New Role</x-slot>

    <div class="bg-white rounded shadow-md border border-gray-300">
        <div class="p-6 border-b border-gray-200">
            <div class="flex items-center">
                <span class="material-icons mr-2 text-primary-DEFAULT">admin_panel_settings</span>
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

            <form method="POST" action="{{ route('role.store') }}" class="space-y-6">
                @csrf
                
                <!-- Basic Information -->
                <div class="border-b border-gray-200 pb-5">
                    <h2 class="text-sm font-semibold text-gray-700 mb-4">Basic Information</h2>
                    
                    <!-- Role Name -->
                    <div class="mb-4">
                        <label for="role_name" class="flex items-center text-xs font-medium text-gray-700 mb-1">
                            <span class="material-icons text-sm mr-1 text-primary-DEFAULT">badge</span>
                            Role Name
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <span class="material-icons text-[#004aad] text-base">supervised_user_circle</span>
                            </div>
                            <input 
                                type="text" 
                                name="role_name" 
                                id="role_name" 
                                class="w-full text-xs border-gray-300 rounded-[1px] pl-12 focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50" 
                                value="{{ old('role_name') }}" 
                                required
                            >
                        </div>
                        <p class="mt-1 text-[10px] text-gray-500">Enter a unique name for this role (e.g., Manager, Editor, Viewer)</p>
                    </div>
                    
                    <!-- Role Description -->
                    <div class="mb-4">
                        <label for="role_description" class="flex items-center text-xs font-medium text-gray-700 mb-1">
                            <span class="material-icons text-sm mr-1 text-primary-DEFAULT">description</span>
                            Role Description
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <span class="material-icons text-[#004aad] text-base">notes</span>
                            </div>
                            <input 
                                type="text" 
                                name="role_description" 
                                id="role_description" 
                                class="w-full text-xs border-gray-300 rounded-[1px] pl-12 focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50"
                                value="{{ old('role_description') }}"
                            >
                        </div>
                        <p class="mt-1 text-[10px] text-gray-500">Provide a brief description of this role's purpose and responsibilities</p>
                    </div>
                    
                    <!-- Status -->
                    <div>
                        <label for="status" class="flex items-center text-xs font-medium text-gray-700 mb-1">
                            <span class="material-icons text-sm mr-1 text-primary-DEFAULT">toggle_on</span>
                            Status
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <span class="material-icons text-[#004aad] text-base">shield</span>
                            </div>
                            <select 
                                name="status" 
                                id="status" 
                                class="w-full text-xs border-gray-300 rounded-[1px] pl-12 focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50" 
                                required
                            >
                                <option value="active" {{ old('status') == 'active' ? 'selected' : '' }}>Active</option>
                                <option value="inactive" {{ old('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                            </select>
                        </div>
                        <p class="mt-1 text-[10px] text-gray-500">Inactive roles cannot be assigned to users</p>
                    </div>
                </div>
                
                <!-- Permissions -->
                <div class="pb-5">
                    <h2 class="text-sm font-semibold text-gray-700 mb-4 flex items-center">
                        <span class="material-icons text-base mr-1 text-primary-DEFAULT">verified_user</span>
                        Role Permissions
                    </h2>
                    <p class="text-xs text-gray-500 mb-4">Select the permissions this role will have in the system</p>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        @foreach($permissions as $group => $permission)
                            <div class="border border-gray-200 rounded p-4">
                                <h3 class="text-sm font-medium text-gray-700 mb-2">{{ $permission['title'] }}</h3>
                                <div class="space-y-2">
                                    @foreach($permission['items'] as $key => $label)
                                        @php
                                            $permissionId = App\Models\Permission::where('name', $key)->value('id');
                                        @endphp
                                        <div class="flex items-center">
                                            <input 
                                                type="checkbox" 
                                                name="permissions[]" 
                                                id="permission_{{ $key }}" 
                                                value="{{ $permissionId }}"
                                                class="rounded border-gray-300 text-primary-DEFAULT focus:ring-primary-light h-4 w-4"
                                                {{ old("permissions") && in_array($permissionId, old("permissions")) ? 'checked' : '' }}
                                            >
                                            <label for="permission_{{ $key }}" class="ml-2 text-xs text-gray-700">{{ $label }}</label>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>
                    
                    <div class="mt-6 bg-blue-50 border border-blue-100 rounded p-4">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <span class="material-icons text-blue-600">info</span>
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
                
                <div class="border-t border-gray-200 pt-4 mt-6 flex justify-end space-x-3">
                    <a 
                        href="{{ route('role.management') }}" 
                        class="px-3 py-1 bg-gradient-to-r from-red-600 to-red-500 hover:from-red-700 hover:to-red-600 text-white rounded shadow-sm text-xs font-medium transition-colors duration-200 ease-in-out flex items-center"
                    >
                        <span class="material-icons text-xs mr-1">cancel</span>
                        Cancel
                    </a>
                    <button 
                        type="submit" 
                        class="px-3 py-1 bg-gradient-to-r from-blue-600 to-blue-500 hover:from-blue-700 hover:to-blue-600 text-white rounded shadow-sm text-xs font-medium transition-colors duration-200 ease-in-out flex items-center"
                    >
                        <span class="material-icons text-xs mr-1">save</span>
                        Save Role
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout> 