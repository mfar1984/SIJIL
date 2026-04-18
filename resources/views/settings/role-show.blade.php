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
                    <span class="material-icons-outlined mr-2 text-primary-DEFAULT">admin_panel_settings</span>
                    <h1 class="text-xl font-bold text-gray-800">Role Details</h1>
                </div>
                <div class="flex space-x-3">
                    @can('roles.update')
                    <a href="{{ route('role.edit', $role->id) }}" class="bg-gradient-to-r from-yellow-500 to-yellow-400 hover:from-yellow-600 hover:to-yellow-500 text-white px-3 h-[36px] rounded shadow-sm font-medium flex items-center text-xs transition-colors duration-200 ease-in-out">
                        <span class="material-icons-outlined text-xs mr-1">edit</span>
                        Edit Role
                    </a>
                    @endcan
                    
                    @can('roles.delete')
                        @if(!in_array($role->name, ['Administrator', 'Organizer']))
                        <form method="POST" action="{{ route('role.destroy', $role->id) }}" onsubmit="return confirm('Are you sure you want to delete this role?');" class="inline-block">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="bg-gradient-to-r from-red-500 to-red-400 hover:from-red-600 hover:to-red-500 text-white px-3 h-[36px] rounded shadow-sm font-medium flex items-center text-xs transition-colors duration-200 ease-in-out">
                                <span class="material-icons-outlined text-xs mr-1">delete</span>
                                Delete Role
                            </button>
                        </form>
                        @endif
                    @endcan
                    
                    <a href="{{ route('role.management') }}" class="bg-gradient-to-r from-gray-500 to-gray-400 hover:from-gray-600 hover:to-gray-500 text-white px-3 h-[36px] rounded shadow-sm font-medium flex items-center text-xs transition-colors duration-200 ease-in-out">
                        <span class="material-icons-outlined text-xs mr-1">arrow_back</span>
                        Back to List
                    </a>
                </div>
            </div>
            <p class="text-xs text-gray-500 mt-1 ml-8">View detailed information about this role</p>
        </div>
        
        <div class="p-6 space-y-2">
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
                            <label class="text-xs font-medium text-gray-700 md:w-40">
                                Role Name
                            </label>
                            <div class="flex-1">
                                <div class="w-full text-xs border-gray-200 bg-gray-50 rounded-[1px] px-3 py-2 border">
                                    {{ $role->name }}
                                </div>
                            </div>
                        </div>
                        
                        <!-- Role Description -->
                        <div class="flex flex-col md:flex-row md:items-center gap-3">
                            <label class="text-xs font-medium text-gray-700 md:w-40">
                                Role Description
                            </label>
                            <div class="flex-1">
                                <div class="w-full text-xs border-gray-200 bg-gray-50 rounded-[1px] px-3 py-2 border">
                                    {{ $role->description ?? 'No description provided' }}
                                </div>
                            </div>
                        </div>
                        
                        <!-- Status -->
                        <div class="flex flex-col md:flex-row md:items-center gap-3">
                            <label class="text-xs font-medium text-gray-700 md:w-40">
                                Status
                            </label>
                            <div class="flex-1">
                                <div class="w-full text-xs border-gray-200 bg-gray-50 rounded-[1px] px-3 py-2 border flex items-center">
                                    @if($role->status === 'active')
                                        <span class="bg-status-active-bg text-status-active-text px-2 py-0.5 rounded-full text-xs">Active</span>
                                    @else
                                        <span class="bg-status-inactive-bg text-status-inactive-text px-2 py-0.5 rounded-full text-xs">Inactive</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                        
                        <!-- Created Date -->
                        <div class="flex flex-col md:flex-row md:items-center gap-3">
                            <label class="text-xs font-medium text-gray-700 md:w-40">
                                Created Date
                            </label>
                            <div class="flex-1">
                                <div class="w-full text-xs border-gray-200 bg-gray-50 rounded-[1px] px-3 py-2 border">
                                    {{ $role->created_at->format('d M Y - H:i:s') }}
                                </div>
                            </div>
                        </div>
                        
                        <!-- Last Modified -->
                        <div class="flex flex-col md:flex-row md:items-center gap-3">
                            <label class="text-xs font-medium text-gray-700 md:w-40">
                                Last Modified
                            </label>
                            <div class="flex-1">
                                <div class="w-full text-xs border-gray-200 bg-gray-50 rounded-[1px] px-3 py-2 border">
                                    {{ $role->updated_at->format('d M Y - H:i:s') }}
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
                    <x-settings.partials.permissions-matrix :permissions="$permissions" :checkedPermissionNames="$rolePermissions" mode="show" />
                </div>
            </div>
            
            <!-- Users with this Role -->
            <div class="bg-white border border-gray-200 rounded-md shadow-sm">
                <div class="bg-gray-50 border-b border-gray-200 px-4 py-3">
                    <h2 class="text-sm font-semibold text-gray-700 flex items-center">
                        <span class="material-icons-outlined text-primary-DEFAULT mr-2">group</span>
                        Users with this Role
                    </h2>
                </div>
                
                <div class="p-4">
                    <div class="bg-blue-50 border border-blue-200 rounded p-4 flex items-center">
                        <span class="material-icons-outlined text-blue-600 mr-2">info</span>
                        <p class="text-xs text-blue-700">
                            There are currently <span class="font-semibold">{{ $role->users_count }}</span> users assigned with the {{ $role->name }} role.
                        </p>
                    </div>
                </div>
            </div>
            
            <!-- Removed "Back to Role List" button since it already exists at the top right -->
        </div>
    </div>
</x-app-layout> 