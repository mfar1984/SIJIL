<x-app-layout>
    <x-slot name="breadcrumb">
        <span>Settings</span>
        <span class="mx-2 text-gray-500">/</span>
        <span>User Management</span>
    </x-slot>

    <x-slot name="title">User Management</x-slot>

    <div class="bg-white rounded shadow-md border border-gray-300">
        <div class="p-6 border-b border-gray-200">
            <div class="flex justify-between items-start">
                <div>
                    <div class="flex items-center">
                        <span class="material-icons mr-2 text-primary-DEFAULT">manage_accounts</span>
                        <h1 class="text-2xl font-bold text-gray-800">User Management</h1>
                    </div>
                    <p class="text-sm text-gray-500 mt-1 ml-8">Manage system users and their access</p>
                </div>
                <button class="bg-gradient-to-r from-blue-600 to-blue-500 hover:from-blue-700 hover:to-blue-600 text-white px-3 py-1 rounded shadow-sm font-medium flex items-center text-xs transition-colors duration-200 ease-in-out">
                    <span class="material-icons text-xs mr-1">add_circle</span>
                    Create New User
                </button>
            </div>
        </div>
        
        <div class="p-4">
            <!-- Users Table -->
            <div class="overflow-x-auto border border-gray-200 rounded">
                <table class="min-w-full border-collapse">
                    <thead>
                        <tr class="bg-primary-light text-white text-xs uppercase">
                            <th class="py-3 px-4 text-left rounded-tl">Name</th>
                            <th class="py-3 px-4 text-left">Email</th>
                            <th class="py-3 px-4 text-left">Role</th>
                            <th class="py-3 px-4 text-left">Create Date</th>
                            <th class="py-3 px-4 text-left">Last Login</th>
                            <th class="py-3 px-4 text-left">Status</th>
                            <th class="py-3 px-4 text-center rounded-tr">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($users as $user)
                            <tr class="text-xs hover:bg-gray-50">
                                <td class="py-3 px-4 font-medium">{{ $user['name'] }}</td>
                                <td class="py-3 px-4">{{ $user['email'] }}</td>
                                <td class="py-3 px-4">{{ $user['role'] }}</td>
                                <td class="py-3 px-4">{{ $user['create_date'] }}</td>
                                <td class="py-3 px-4">{{ $user['last_login'] }}</td>
                                <td class="py-3 px-4">
                                    @if($user['status'] === 'active')
                                        <span class="bg-status-active-bg text-status-active-text px-2 py-1 rounded-full text-xs">Active</span>
                                    @elseif($user['status'] === 'inactive')
                                        <span class="bg-status-pending-bg text-status-pending-text px-2 py-1 rounded-full text-xs">Inactive</span>
                                    @elseif($user['status'] === 'banned')
                                        <span class="bg-status-inactive-bg text-status-inactive-text px-2 py-1 rounded-full text-xs">Banned</span>
                                    @endif
                                </td>
                                <td class="py-3 px-4">
                                    <div class="flex justify-center space-x-2">
                                        <button class="p-1 bg-blue-50 rounded hover:bg-blue-100 border border-blue-100" title="View">
                                            <span class="material-icons text-primary-DEFAULT text-xs">visibility</span>
                                        </button>
                                        <button class="p-1 bg-yellow-50 rounded hover:bg-yellow-100 border border-yellow-100" title="Edit">
                                            <span class="material-icons text-yellow-600 text-xs">edit</span>
                                        </button>
                                        <button class="p-1 bg-red-50 rounded hover:bg-red-100 border border-red-100" title="Delete">
                                            <span class="material-icons text-red-600 text-xs">delete</span>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout> 