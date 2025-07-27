<x-app-layout>
    <x-slot name="breadcrumb">
        <span>Settings</span>
        <span class="mx-2 text-gray-500">/</span>
        <span>Role Management</span>
        <span class="mx-2 text-gray-500">/</span>
        <span>Edit Role</span>
    </x-slot>

    <x-slot name="title">Edit Role</x-slot>

    <div class="bg-white rounded shadow-md border border-gray-300">
        <div class="p-6 border-b border-gray-200">
            <div class="flex items-center">
                <span class="material-icons mr-2 text-primary-DEFAULT">admin_panel_settings</span>
                <h1 class="text-xl font-bold text-gray-800">Edit Role: {{ $role->name }}</h1>
            </div>
            <p class="text-xs text-gray-500 mt-1 ml-8">Modify role information and permissions</p>
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

            <form method="POST" action="{{ route('role.update', $role->id) }}" class="space-y-6">
                @csrf
                @method('PUT')
                
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
                                value="{{ old('role_name', $role->name) }}" 
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
                                value="{{ old('role_description', $role->description) }}"
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
                                <option value="active" {{ old('status', $role->status) == 'active' ? 'selected' : '' }}>Active</option>
                                <option value="inactive" {{ old('status', $role->status) == 'inactive' ? 'selected' : '' }}>Inactive</option>
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
                    
                    <div class="overflow-x-auto">
                        <table class="min-w-full bg-white border border-gray-200 rounded-lg">
                            <thead class="bg-gradient-to-r from-blue-600 to-blue-500 text-white">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider">Name</th>
                                    <th class="px-4 py-3 text-center text-xs font-medium uppercase tracking-wider">Create</th>
                                    <th class="px-4 py-3 text-center text-xs font-medium uppercase tracking-wider">Read</th>
                                    <th class="px-4 py-3 text-center text-xs font-medium uppercase tracking-wider">Update</th>
                                    <th class="px-4 py-3 text-center text-xs font-medium uppercase tracking-wider">Delete</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                @foreach($permissionMatrix as $main => $sub)
                                    @if(is_array($sub) && isset($sub[0]) && is_string($sub[0]))
                                        <!-- Direct permissions (like Dashboard) -->
                                        <tr class="hover:bg-gray-50">
                                            <td class="px-4 py-3 text-xs font-medium text-gray-900">
                                                {{ $main }}
                                            </td>
                                            @foreach(['create', 'read', 'update', 'delete'] as $action)
                                                <td class="px-4 py-3 text-center">
                                                    @if(in_array($action, $sub))
                                                        @php
                                                            $permissionName = Str::slug($main) . '.' . $action;
                                                            $permissionId = App\Models\Permission::where('name', $permissionName)->value('id');
                                                        @endphp
                                                        <input 
                                                            type="checkbox" 
                                                            name="permissions[]" 
                                                            value="{{ $permissionId }}"
                                                            class="rounded border-gray-300 text-primary-DEFAULT focus:ring-primary-light h-4 w-4"
                                                            {{ in_array($permissionName, $rolePermissions) ? 'checked' : '' }}
                                                        >
                                                    @else
                                                        <span class="text-gray-300 text-xs">-</span>
                                                    @endif
                                                </td>
                                            @endforeach
                                        </tr>
                                    @else
                                        <!-- Main category header -->
                                        <tr class="bg-gray-100">
                                            <td colspan="5" class="px-4 py-2 text-xs font-bold text-gray-700">
                                                {{ $main }}
                                            </td>
                                        </tr>
                                        <!-- Sub-menu permissions -->
                                        @foreach($sub as $subName => $actions)
                                            <tr class="hover:bg-gray-50">
                                                <td class="px-4 py-3 text-xs text-gray-900 pl-8">
                                                    {{ $subName }}
                                                </td>
                                                @foreach(['create', 'read', 'update', 'delete'] as $action)
                                                    <td class="px-4 py-3 text-center">
                                                        @if(in_array($action, $actions))
                                                            @php
                                                                $permissionName = Str::slug($subName) . '.' . $action;
                                                                $permissionId = App\Models\Permission::where('name', $permissionName)->value('id');
                                                            @endphp
                                                            <input 
                                                                type="checkbox" 
                                                                name="permissions[]" 
                                                                value="{{ $permissionId }}"
                                                                class="rounded border-gray-300 text-primary-DEFAULT focus:ring-primary-light h-4 w-4"
                                                                {{ in_array($permissionName, $rolePermissions) ? 'checked' : '' }}
                                                            >
                                                        @else
                                                            <span class="text-gray-300 text-xs">-</span>
                                                        @endif
                                                    </td>
                                                @endforeach
                                            </tr>
                                        @endforeach
                                    @endif
                                @endforeach
                            </tbody>
                        </table>
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
                    <a href="{{ route('role.management') }}" 
                       class="px-3 py-1 bg-gradient-to-r from-red-600 to-red-500 hover:from-red-700 hover:to-red-600 text-white rounded shadow-sm text-xs font-medium transition-colors duration-200 ease-in-out flex items-center"
                    >
                        <span class="material-icons text-base mr-1">cancel</span>
                        Cancel
                    </a>
                    <button type="submit" 
                            class="px-3 py-1 bg-gradient-to-r from-blue-600 to-blue-500 hover:from-blue-700 hover:to-blue-600 text-white rounded shadow-sm text-xs font-medium transition-colors duration-200 ease-in-out flex items-center"
                    >
                        <span class="material-icons text-base mr-1">save</span>
                        Update Role
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout> 