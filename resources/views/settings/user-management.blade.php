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
                        <h1 class="text-xl font-bold text-gray-800">User Management</h1>
                    </div>
                    <p class="text-xs text-gray-500 mt-1 ml-8">Manage system users and their access</p>
                </div>
                <a href="{{ route('user.create') }}" class="bg-gradient-to-r from-blue-600 to-blue-500 hover:from-blue-700 hover:to-blue-600 text-white px-3 py-1 rounded shadow-sm font-medium flex items-center text-xs transition-colors duration-200 ease-in-out">
                    <span class="material-icons text-xs mr-1">add_circle</span>
                    Create New User
                </a>
            </div>
        </div>
        
        <div class="p-4">
            @if (session('success'))
                <div class="mb-4 p-3 bg-green-50 border border-green-200 text-green-700 rounded text-xs">
                    {{ session('success') }}
                </div>
            @endif
            
            @if (session('error'))
                <div class="mb-4 p-3 bg-red-50 border border-red-200 text-red-700 rounded text-xs">
                    {{ session('error') }}
                </div>
            @endif
            
            <!-- Users Table -->
            <div class="overflow-visible border border-gray-200 rounded">
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
                                <td class="py-3 px-4 font-medium">{{ $user->name }}</td>
                                <td class="py-3 px-4">{{ $user->email }}</td>
                                <td class="py-3 px-4">
                                    @if($user->roles && $user->roles->count() > 0)
                                        {{ $user->getRoleNames()->first() }}
                                    @else
                                        No Role
                                    @endif
                                </td>
                                <td class="py-3 px-4">{{ $user->created_at instanceof \DateTime ? $user->created_at->format('d M Y - H:i:s') : ($user->created_at ?? 'N/A') }}</td>
                                <td class="py-3 px-4">{{ $user->last_login_at instanceof \DateTime ? $user->last_login_at->format('d M Y - H:i:s') : ($user->last_login_at ?? 'Never') }}</td>
                                <td class="py-3 px-4">
                                    @if($user->status === 'active')
                                        <span class="bg-status-active-bg text-status-active-text px-2 py-1 rounded-full text-xs">Active</span>
                                    @elseif($user->status === 'inactive')
                                        <span class="bg-status-pending-bg text-status-pending-text px-2 py-1 rounded-full text-xs">Inactive</span>
                                    @elseif($user->status === 'banned')
                                        <span class="bg-status-inactive-bg text-status-inactive-text px-2 py-1 rounded-full text-xs">Banned</span>
                                    @endif
                                </td>
                                <td class="py-3 px-4">
                                    <div class="flex justify-center space-x-2">
                                        <a href="{{ route('user.show', $user->id) }}" class="p-1 bg-blue-50 rounded hover:bg-blue-100 border border-blue-100" title="View">
                                            <span class="material-icons text-primary-DEFAULT text-xs">visibility</span>
                                        </a>
                                        <a href="{{ route('user.edit', $user->id) }}" class="p-1 bg-yellow-50 rounded hover:bg-yellow-100 border border-yellow-100" title="Edit">
                                            <span class="material-icons text-yellow-600 text-xs">edit</span>
                                        </a>
                                        
                                        <div class="relative" x-data="{ statusDropdownOpen{{ $user->id }}: false }">
                                            <button @click="statusDropdownOpen{{ $user->id }} = !statusDropdownOpen{{ $user->id }}" class="p-1 bg-purple-50 rounded hover:bg-purple-100 border border-purple-100" title="Status Actions">
                                                <span class="material-icons text-purple-600 text-xs">settings</span>
                                            </button>
                                            <div x-show="statusDropdownOpen{{ $user->id }}" @click.outside="statusDropdownOpen{{ $user->id }} = false" class="absolute right-0 mt-2 z-50 w-48 bg-white rounded-md shadow-lg">
                                                <div class="py-1 border border-gray-200 rounded-md">
                                                    <form method="POST" action="{{ route('user.status', ['user' => $user->id, 'status' => 'active']) }}">
                                                        @csrf
                                                        @method('PATCH')
                                                        <button type="submit" class="block w-full text-left px-4 py-2 text-xs text-gray-700 hover:bg-gray-100 {{ $user->status === 'active' ? 'bg-gray-100' : '' }}">
                                                            <span class="inline-block w-3 h-3 rounded-full bg-green-500 mr-2"></span>
                                                            Set Active
                                                        </button>
                                                    </form>
                                                    
                                                    <form method="POST" action="{{ route('user.status', ['user' => $user->id, 'status' => 'inactive']) }}">
                                                        @csrf
                                                        @method('PATCH')
                                                        <button type="submit" class="block w-full text-left px-4 py-2 text-xs text-gray-700 hover:bg-gray-100 {{ $user->status === 'inactive' ? 'bg-gray-100' : '' }}">
                                                            <span class="inline-block w-3 h-3 rounded-full bg-yellow-500 mr-2"></span>
                                                            Set Inactive
                                                        </button>
                                                    </form>
                                                    
                                                    <form method="POST" action="{{ route('user.status', ['user' => $user->id, 'status' => 'banned']) }}">
                                                        @csrf
                                                        @method('PATCH')
                                                        <button type="submit" class="block w-full text-left px-4 py-2 text-xs text-gray-700 hover:bg-gray-100 {{ $user->status === 'banned' ? 'bg-gray-100' : '' }}">
                                                            <span class="inline-block w-3 h-3 rounded-full bg-red-500 mr-2"></span>
                                                            Set Banned
                                                        </button>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>

                                        <form method="POST" action="{{ route('user.destroy', $user->id) }}" onsubmit="return confirm('Are you sure you want to delete this user?')" class="inline-block">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="p-1 bg-red-50 rounded hover:bg-red-100 border border-red-100" title="Delete">
                                                <span class="material-icons text-red-600 text-xs">delete</span>
                                            </button>
                                        </form>
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