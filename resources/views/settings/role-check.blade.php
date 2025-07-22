<x-app-layout>
    <x-slot name="breadcrumb">
        <span>Settings</span>
        <span class="mx-2 text-gray-500">/</span>
        <span>Role Check</span>
    </x-slot>

    <x-slot name="title">Role & Permission Check</x-slot>

    <div class="bg-white rounded shadow-md border border-gray-300">
        <div class="p-6 border-b border-gray-200">
            <div class="flex items-center justify-between">
                <div class="flex items-center">
                    <span class="material-icons mr-2 text-primary-DEFAULT">security</span>
                    <h1 class="text-xl font-bold text-gray-800">Role & Permission Check</h1>
                </div>
                <div class="bg-indigo-100 text-indigo-700 px-3 py-1 rounded-full text-xs font-medium">
                    Your Role: {{ $role }}
                </div>
            </div>
            <p class="text-xs text-gray-500 mt-1 ml-8">View your current role permissions and access status</p>
        </div>
        
        <div class="p-6 space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- User Information -->
                <div class="border border-gray-200 rounded-lg overflow-hidden">
                    <div class="bg-gray-50 px-4 py-3 border-b border-gray-200">
                        <h3 class="text-sm font-medium text-gray-700">User Information</h3>
                    </div>
                    <div class="p-4">
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <p class="text-xs text-gray-500">Name</p>
                                <p class="text-sm font-medium">{{ $user->name }}</p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500">Email</p>
                                <p class="text-sm font-medium">{{ $user->email }}</p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500">Role</p>
                                <p class="text-sm font-medium">{{ $role }}</p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500">Data Access</p>
                                <p class="text-sm font-medium">{{ $role === 'Administrator' ? 'All Data' : 'Own Data Only' }}</p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Module Access -->
                <div class="border border-gray-200 rounded-lg overflow-hidden">
                    <div class="bg-gray-50 px-4 py-3 border-b border-gray-200">
                        <h3 class="text-sm font-medium text-gray-700">Module Access</h3>
                    </div>
                    <div class="p-4">
                        <div class="space-y-3">
                            @foreach($modules as $module => $hasAccess)
                                <div class="flex items-center justify-between">
                                    <span class="text-sm">{{ $module }}</span>
                                    @if($hasAccess)
                                        <span class="bg-green-100 text-green-700 px-2 py-1 rounded text-xs flex items-center">
                                            <span class="material-icons text-xs mr-1">check_circle</span>
                                            Access Granted
                                        </span>
                                    @else
                                        <span class="bg-red-100 text-red-700 px-2 py-1 rounded text-xs flex items-center">
                                            <span class="material-icons text-xs mr-1">block</span>
                                            No Access
                                        </span>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Detailed Permissions -->
            <div class="border border-gray-200 rounded-lg overflow-hidden">
                <div class="bg-gray-50 px-4 py-3 border-b border-gray-200">
                    <h3 class="text-sm font-medium text-gray-700">Your Permissions</h3>
                </div>
                <div class="p-4">
                    @if($role === 'Administrator')
                        <div class="bg-indigo-50 border border-indigo-100 rounded p-4">
                            <p class="text-sm text-indigo-700">
                                <span class="font-medium">Administrator Role:</span> You have full access to all features and data in the system.
                            </p>
                        </div>
                    @else
                        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-3">
                            @foreach($permissions as $permission)
                                <div class="bg-green-50 border border-green-100 rounded p-2 text-xs text-green-700">
                                    {{ str_replace('_', ' ', ucfirst($permission)) }}
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
            
            <!-- Data Access Rules -->
            <div class="bg-blue-50 border border-blue-100 rounded-lg p-4">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <span class="material-icons text-blue-600">info</span>
                    </div>
                    <div class="ml-3">
                        <h3 class="text-sm font-medium text-blue-800">Data Access Policy</h3>
                        <div class="mt-2 text-sm text-blue-700">
                            @if($role === 'Administrator')
                                <p>As an <strong>Administrator</strong>, you have access to view and manage all data within the system, including data created by all Organizers.</p>
                            @else
                                <p>As an <strong>Organizer</strong>, you can only access and manage data that you've created or that has been assigned to you. You cannot view or edit data owned by other Organizers.</p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout> 