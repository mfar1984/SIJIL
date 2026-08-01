<x-app-layout>
    <x-slot name="breadcrumb">
        <span>Settings</span>
        <span class="mx-2 text-gray-500">/</span>
        <span>Role Management</span>
    </x-slot>

    <x-slot name="title">Role Management</x-slot>

    <div class="bg-white rounded shadow-md border border-gray-300">
        <div class="p-6 border-b border-gray-200">
            <div class="flex justify-between items-start">
                <div>
                    <div class="flex items-center">
                        <span class="material-icons-outlined mr-2 text-primary-DEFAULT">admin_panel_settings</span>
                        <h1 class="text-xl font-bold text-gray-800">Role Management</h1>
                    </div>
                    <p class="text-xs text-gray-500 mt-1 ml-8">Manage user roles and their permissions</p>
                </div>
                @can('roles.create')
                <a href="{{ route('role.create') }}" class="bg-gradient-to-r from-blue-600 to-blue-500 hover:from-blue-700 hover:to-blue-600 text-white px-3 h-[36px] rounded shadow-sm font-medium flex items-center text-xs transition-colors duration-200 ease-in-out">
                    <span class="material-icons-outlined text-xs mr-1">add_circle</span>
                    Create New Role
                </a>
                @endcan
            </div>
        </div>
        
        <div class="p-4">
            {{-- Search takes the remaining space; the filters keep their own width. --}}
            <form method="GET" action="{{ route('role.management') }}" class="flex flex-wrap items-center gap-2 mb-4">
                <input type="text" name="search" id="searchInput" value="{{ request('search') }}"
                       placeholder="Search role name, description..."
                       class="flex-1 min-w-[12rem] h-9 text-xs border-gray-300 rounded px-3 focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50">

                <select name="status" onchange="this.form.submit()"
                        class="h-9 text-xs border-gray-300 rounded pl-3 pr-8 w-[9rem] shrink-0 focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50">
                    <option value="">All Status</option>
                    <option value="active" @if(request('status') == 'active') selected @endif>Active</option>
                    <option value="inactive" @if(request('status') == 'inactive') selected @endif>Inactive</option>
                </select>

                <button type="submit"
                        class="h-9 px-3 bg-primary-DEFAULT hover:bg-primary-dark text-white rounded text-xs flex items-center shrink-0 transition-colors duration-200 ease-in-out" title="Search">
                    <span class="material-icons-outlined text-xs">search</span>
                </button>

                @if(request('search') || request('status'))
                    <a href="{{ route('role.management') }}" class="text-xs text-gray-500 underline shrink-0">Reset</a>
                @endif
            </form>
            
            <!-- Search Results Summary -->
            @if(request('search') || request('status'))
                <div class="bg-blue-50 border border-blue-200 text-blue-700 px-4 py-2 rounded mb-4 text-xs">
                    <span class="font-medium">Search Results:</span>
                    @if(request('search'))
                        <span class="ml-2">Searching for "{{ request('search') }}"</span>
                    @endif
                    @if(request('status'))
                        <span class="ml-2">Status: {{ ucfirst(request('status')) }}</span>
                    @endif
                    <span class="ml-2">({{ $roles->total() }} results)</span>
                </div>
            @endif
            
            <!-- Roles Table -->
            <div class="overflow-x-auto border border-gray-200 rounded">
                <table class="min-w-full border-collapse">
                    <thead>
                        <tr class="bg-primary-light text-white text-xs uppercase">
                            <th class="py-3 px-4 text-left rounded-tl">Role Name</th>
                            <th class="py-3 px-4 text-left">Description</th>
                            <th class="py-3 px-4 text-left">Permissions</th>
                            <th class="py-3 px-4 text-left">Users</th>
                            <th class="py-3 px-4 text-left">Status</th>
                            <th class="py-3 px-4 text-center rounded-tr">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($roles as $role)
                            <tr class="text-xs hover:bg-gray-50">
                                <td class="py-3 px-4 font-medium">{{ $role->name }}</td>
                                <td class="py-3 px-4">{{ $role->description ?? 'No description available' }}</td>
                                <td class="py-3 px-4">
                                    @if($role->name === 'Administrator')
                                        <span class="bg-indigo-100 text-indigo-700 px-2 py-1 rounded-full text-xs">All Permissions</span>
                                    @else
                                        <div class="flex flex-wrap gap-1">
                                            @php
                                                $permissions = $role->permissions()->get();
                                                $permissionNames = $permissions->pluck('display_name', 'name')
                                                    ->map(function($displayName, $name) {
                                                        return $displayName ?? $name;
                                                    })
                                                    ->values()
                                                    ->toArray();
                                            @endphp
                                            
                                            @if(count($permissionNames) > 0)
                                                @foreach(array_slice($permissionNames, 0, 2) as $permission)
                                                    <span class="bg-blue-100 text-blue-700 px-2 py-0.5 rounded-full text-xs">{{ $permission }}</span>
                                                @endforeach
                                                
                                                @if(count($permissionNames) > 2)
                                                    <span class="text-primary-DEFAULT cursor-pointer" title="{{ implode(', ', array_slice($permissionNames, 2)) }}">
                                                        +{{ count($permissionNames) - 2 }} more
                                                    </span>
                                                @endif
                                            @else
                                                <span class="text-gray-500 text-xs">No permissions assigned</span>
                                            @endif
                                        </div>
                                    @endif
                                </td>
                                <td class="py-3 px-4">{{ $role->users()->count() }}</td>
                                <td class="py-3 px-4">
                                    @if($role->status === 'active')
                                        <span class="bg-status-active-bg text-status-active-text px-2 py-1 rounded-full text-xs">Active</span>
                                    @else
                                        <span class="bg-status-inactive-bg text-status-inactive-text px-2 py-1 rounded-full text-xs">Inactive</span>
                                    @endif
                                </td>
                                <td class="py-3 px-4">
                                    <div class="flex justify-center space-x-2">
                                        @can('roles.read')
                                        <a href="{{ route('role.show', $role->id) }}" class="p-1 bg-blue-50 rounded hover:bg-blue-100 border border-blue-100" title="View">
                                            <span class="material-icons-outlined text-primary-DEFAULT text-xs">visibility</span>
                                        </a>
                                        @endcan
                                        
                                        @can('roles.update')
                                        <a href="{{ route('role.edit', $role->id) }}" class="p-1 bg-yellow-50 rounded hover:bg-yellow-100 border border-yellow-100" title="Edit">
                                            <span class="material-icons-outlined text-yellow-600 text-xs">edit</span>
                                        </a>
                                        @endcan
                                        
                                        @if($role->name !== 'Administrator' && $role->name !== 'Organizer')
                                            @can('roles.delete')
                                            <form method="POST" action="{{ route('role.destroy', $role->id) }}" class="inline-block" onsubmit="return confirm('Are you sure you want to delete this role?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="p-1 bg-red-50 rounded hover:bg-red-100 border border-red-100" title="Delete">
                                                    <span class="material-icons-outlined text-red-600 text-xs">delete</span>
                                                </button>
                                            </form>
                                            @endcan
                                        @else
                                            @can('roles.delete')
                                            <button class="p-1 bg-red-50 rounded hover:bg-red-100 border border-red-100 cursor-not-allowed opacity-50" title="System roles cannot be deleted">
                                                <span class="material-icons-outlined text-red-600 text-xs">delete</span>
                                            </button>
                                            @endcan
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination -->
            <div class="mt-6 flex flex-col sm:flex-row sm:items-center sm:justify-between">
                <div class="mb-2 sm:mb-0 text-xs text-gray-500">
                    @if($roles->total() > 0)
                        Showing <span class="font-medium">{{ $roles->firstItem() }}</span> to <span class="font-medium">{{ $roles->lastItem() }}</span> of <span class="font-medium">{{ $roles->total() }}</span> entries
                    @else
                        Showing <span class="font-medium">0</span> to <span class="font-medium">0</span> of <span class="font-medium">0</span> entries
                    @endif
                </div>
                <div class="flex justify-end">
                    {{ $roles->appends(request()->query())->links('components.pagination-modern') }}
                </div>
            </div>
        </div>
    </div>

    <!-- JavaScript for search debounce -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const searchInput = document.getElementById('searchInput');
            let searchTimeout;

            searchInput.addEventListener('input', function() {
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(() => {
                    this.form.submit();
                }, 500);
            });
        });
    </script>
</x-app-layout> 