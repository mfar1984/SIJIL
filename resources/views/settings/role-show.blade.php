<x-app-layout>
    <x-slot name="breadcrumb">
        <span>Settings</span>
        <span class="mx-2 text-gray-500">/</span>
        <span>Role Management</span>
        <span class="mx-2 text-gray-500">/</span>
        <span>View Role</span>
    </x-slot>

    <x-slot name="title">Role Details</x-slot>

    <div class="bg-white rounded shadow-md border border-gray-300">
        <div class="p-6 border-b border-gray-200">
            <div class="flex items-center justify-between">
                <div class="flex items-center">
                    <span class="material-icons mr-2 text-primary-DEFAULT">admin_panel_settings</span>
                    <h1 class="text-xl font-bold text-gray-800">Role Details</h1>
                </div>
                <div class="flex space-x-3">
                    @can('edit_roles')
                    <a href="{{ route('role.edit', $role->id) }}" class="bg-gradient-to-r from-yellow-500 to-yellow-400 hover:from-yellow-600 hover:to-yellow-500 text-white px-3 py-1 rounded shadow-sm font-medium flex items-center text-xs transition-colors duration-200 ease-in-out">
                        <span class="material-icons text-xs mr-1">edit</span>
                        Edit Role
                    </a>
                    @endcan
                    
                    @can('delete_roles')
                        @if(!in_array($role->name, ['Administrator', 'Organizer']))
                        <form method="POST" action="{{ route('role.destroy', $role->id) }}" onsubmit="return confirm('Are you sure you want to delete this role?');" class="inline-block">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="bg-gradient-to-r from-red-500 to-red-400 hover:from-red-600 hover:to-red-500 text-white px-3 py-1 rounded shadow-sm font-medium flex items-center text-xs transition-colors duration-200 ease-in-out">
                                <span class="material-icons text-xs mr-1">delete</span>
                                Delete Role
                            </button>
                        </form>
                        @endif
                    @endcan
                    
                    <a href="{{ route('role.management') }}" class="bg-gradient-to-r from-gray-500 to-gray-400 hover:from-gray-600 hover:to-gray-500 text-white px-3 py-1 rounded shadow-sm font-medium flex items-center text-xs transition-colors duration-200 ease-in-out">
                        <span class="material-icons text-xs mr-1">arrow_back</span>
                        Back to List
                    </a>
                </div>
            </div>
            <p class="text-xs text-gray-500 mt-1 ml-8">View detailed information about this role</p>
        </div>
        
        <div class="p-6 space-y-6">
            <!-- Basic Information -->
            <div class="border-b border-gray-200 pb-5">
                <h2 class="text-sm font-semibold text-gray-700 mb-4">Basic Information</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-4">
                    <!-- Role Name -->
                    <div>
                        <label class="flex items-center text-xs font-medium text-gray-700 mb-1">
                            <span class="material-icons text-sm mr-1 text-primary-DEFAULT">badge</span>
                            Role Name
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <span class="material-icons text-[#004aad] text-base">supervised_user_circle</span>
                            </div>
                            <div class="w-full text-xs border-gray-200 bg-gray-50 rounded-[1px] pl-12 py-2 border">
                                {{ $role->name }}
                            </div>
                        </div>
                    </div>
                    
                    <!-- Role Description -->
                    <div>
                        <label class="flex items-center text-xs font-medium text-gray-700 mb-1">
                            <span class="material-icons text-sm mr-1 text-primary-DEFAULT">description</span>
                            Role Description
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <span class="material-icons text-[#004aad] text-base">notes</span>
                            </div>
                            <div class="w-full text-xs border-gray-200 bg-gray-50 rounded-[1px] pl-12 py-2 border">
                                {{ $role->description ?? 'No description provided' }}
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <!-- Status -->
                    <div>
                        <label class="flex items-center text-xs font-medium text-gray-700 mb-1">
                            <span class="material-icons text-sm mr-1 text-primary-DEFAULT">toggle_on</span>
                            Status
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <span class="material-icons text-[#004aad] text-base">shield</span>
                            </div>
                            <div class="w-full text-xs border-gray-200 bg-gray-50 rounded-[1px] pl-12 py-2 border flex items-center">
                                @if($role->status === 'active')
                                    <span class="bg-status-active-bg text-status-active-text px-2 py-0.5 rounded-full text-xs">Active</span>
                                @else
                                    <span class="bg-status-inactive-bg text-status-inactive-text px-2 py-0.5 rounded-full text-xs">Inactive</span>
                                @endif
                            </div>
                        </div>
                    </div>
                    
                    <!-- Created Date -->
                    <div>
                        <label class="flex items-center text-xs font-medium text-gray-700 mb-1">
                            <span class="material-icons text-xs mr-1 text-primary-DEFAULT">calendar_today</span>
                            Created Date
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <span class="material-icons text-[#004aad] text-base">event</span>
                            </div>
                            <div class="w-full text-xs border-gray-200 bg-gray-50 rounded-[1px] pl-12 py-2 border">
                                {{ $role->created_at->format('d M Y - H:i:s') }}
                            </div>
                        </div>
                    </div>
                    
                    <!-- Last Modified -->
                    <div>
                        <label class="flex items-center text-xs font-medium text-gray-700 mb-1">
                            <span class="material-icons text-xs mr-1 text-primary-DEFAULT">schedule</span>
                            Last Modified
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <span class="material-icons text-[#004aad] text-base">update</span>
                            </div>
                            <div class="w-full text-xs border-gray-200 bg-gray-50 rounded-[1px] pl-12 py-2 border">
                                {{ $role->updated_at->format('d M Y - H:i:s') }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Permissions -->
            <div class="pb-5">
                <h2 class="text-sm font-semibold text-gray-700 mb-4 flex items-center">
                    <span class="material-icons text-base mr-1 text-primary-DEFAULT">verified_user</span>
                    Role Permissions
                </h2>
                
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
                                                    @if(in_array(Str::slug($main) . '.' . $action, $rolePermissions))
                                                        <span class="material-icons text-green-600 text-base">check_circle</span>
                                                    @else
                                                        <span class="material-icons text-red-600 text-base">cancel</span>
                                                    @endif
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
                                                        @if(in_array(Str::slug($subName) . '.' . $action, $rolePermissions))
                                                            <span class="material-icons text-green-600 text-base">check_circle</span>
                                                        @else
                                                            <span class="material-icons text-red-600 text-base">cancel</span>
                                                        @endif
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
            </div>
            
            <!-- Users with this role -->
            <div class="border-t border-gray-200 pt-5">
                <h2 class="text-sm font-semibold text-gray-700 mb-4 flex items-center">
                    <span class="material-icons text-base mr-1 text-primary-DEFAULT">group</span>
                    Users with this Role
                </h2>
                
                <div class="bg-blue-50 border border-blue-200 rounded p-4 flex items-center">
                    <span class="material-icons text-blue-600 mr-2">info</span>
                    <p class="text-xs text-blue-700">
                        There are currently <span class="font-semibold">{{ $role->users_count }}</span> users assigned with the {{ $role->name }} role.
                    </p>
                </div>
            </div>
            
            <!-- Removed "Back to Role List" button since it already exists at the top right -->
        </div>
    </div>
</x-app-layout> 