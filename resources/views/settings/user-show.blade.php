<x-app-layout>
    <x-slot name="breadcrumb">
        <span>Settings</span>
        <span class="mx-2 text-gray-500">/</span>
        <span>User Management</span>
        <span class="mx-2 text-gray-500">/</span>
        <span>View User</span>
    </x-slot>

    <x-slot name="title">User Details</x-slot>

    <div class="bg-white rounded shadow-md border border-gray-300">
        <div class="p-6 border-b border-gray-200">
            <div class="flex items-center justify-between">
                <div class="flex items-center">
                    <span class="material-icons-outlined mr-2 text-primary-DEFAULT">account_circle</span>
                    <h1 class="text-xl font-bold text-gray-800">User Details</h1>
                </div>
                <div class="flex space-x-3">
                    @can('edit_users')
                    <a href="{{ route('user.edit', $user->id) }}" class="bg-gradient-to-r from-yellow-500 to-yellow-400 hover:from-yellow-600 hover:to-yellow-500 text-white px-3 h-[36px] rounded shadow-sm font-medium flex items-center text-xs transition-colors duration-200 ease-in-out">
                        <span class="material-icons-outlined text-xs mr-1">edit</span>
                        Edit User
                    </a>
                    @endcan
                    
                    @can('delete_users')
                    <form method="POST" action="{{ route('user.destroy', $user->id) }}" onsubmit="return confirm('Are you sure you want to delete this user?');" class="inline-block">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="bg-gradient-to-r from-red-500 to-red-400 hover:from-red-600 hover:to-red-500 text-white px-3 h-[36px] rounded shadow-sm font-medium flex items-center text-xs transition-colors duration-200 ease-in-out">
                            <span class="material-icons-outlined text-xs mr-1">delete</span>
                            Delete User
                        </button>
                    </form>
                    @endcan
                    
                    <a href="{{ route('user.management') }}" class="bg-gradient-to-r from-gray-500 to-gray-400 hover:from-gray-600 hover:to-gray-500 text-white px-3 h-[36px] rounded shadow-sm font-medium flex items-center text-xs transition-colors duration-200 ease-in-out">
                        <span class="material-icons-outlined text-xs mr-1">arrow_back</span>
                        Back to List
                    </a>
                </div>
            </div>
            <p class="text-xs text-gray-500 mt-1 ml-8">View detailed information about this user</p>
        </div>
        
        <div class="p-6 space-y-2">
            <!-- Basic Information Section -->
            <div class="bg-white border border-gray-200 rounded-md shadow-sm">
                <div class="bg-gray-50 border-b border-gray-200 px-4 py-3">
                    <h2 class="text-sm font-semibold text-gray-700 flex items-center">
                        <span class="material-icons-outlined text-primary-DEFAULT mr-2">info</span>
                        Basic Information
                    </h2>
                </div>
                
                <div class="p-4">
                    <div class="space-y-3">
                        <!-- Full Name -->
                        <div class="flex flex-col md:flex-row md:items-center gap-3">
                            <label class="text-xs font-medium text-gray-700 md:w-40">
                                Full Name
                            </label>
                            <div class="flex-1">
                                <div class="w-full text-xs border-gray-200 bg-gray-50 rounded-[1px] px-3 py-2 border">
                                    {{ $user->name }}
                                </div>
                            </div>
                        </div>
                        
                        <!-- Email -->
                        <div class="flex flex-col md:flex-row md:items-center gap-3">
                            <label class="text-xs font-medium text-gray-700 md:w-40">
                                Email Address
                            </label>
                            <div class="flex-1">
                                <div class="w-full text-xs border-gray-200 bg-gray-50 rounded-[1px] px-3 py-2 border">
                                    {{ $user->email }}
                                </div>
                            </div>
                        </div>
                        
                        <!-- Phone -->
                        <div class="flex flex-col md:flex-row md:items-center gap-3">
                            <label class="text-xs font-medium text-gray-700 md:w-40">
                                Phone Number
                            </label>
                            <div class="flex-1">
                                <div class="w-full text-xs border-gray-200 bg-gray-50 rounded-[1px] px-3 py-2 border">
                                    {{ $user->phone ?? 'Not specified' }}
                                </div>
                            </div>
                        </div>
                        
                        <!-- Role -->
                        <div class="flex flex-col md:flex-row md:items-center gap-3">
                            <label class="text-xs font-medium text-gray-700 md:w-40">
                                Role
                            </label>
                            <div class="flex-1">
                                <div class="w-full text-xs border-gray-200 bg-gray-50 rounded-[1px] px-3 py-2 border">
                                    @if($user->roles && $user->roles->count() > 0)
                                        {{ $user->getRoleNames()->first() }}
                                    @else
                                        No Role
                                    @endif
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
                                    @if($user->status === 'active')
                                        <span class="bg-status-active-bg text-status-active-text px-2 py-0.5 rounded-full text-xs">Active</span>
                                    @elseif($user->status === 'inactive')
                                        <span class="bg-status-pending-bg text-status-pending-text px-2 py-0.5 rounded-full text-xs">Inactive</span>
                                    @elseif($user->status === 'banned')
                                        <span class="bg-status-inactive-bg text-status-inactive-text px-2 py-0.5 rounded-full text-xs">Banned</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Address Information Section -->
            <div class="bg-white border border-gray-200 rounded-md shadow-sm">
                <div class="bg-gray-50 border-b border-gray-200 px-4 py-3">
                    <h2 class="text-sm font-semibold text-gray-700 flex items-center">
                        <span class="material-icons-outlined text-primary-DEFAULT mr-2">home</span>
                        Address Information
                    </h2>
                </div>
                
                <div class="p-4">
                    <div class="space-y-3">
                        <!-- Address Line 1 -->
                        <div class="flex flex-col md:flex-row md:items-center gap-3">
                            <label class="text-xs font-medium text-gray-700 md:w-40">
                                Address Line 1
                            </label>
                            <div class="flex-1">
                                <div class="w-full text-xs border-gray-200 bg-gray-50 rounded-[1px] px-3 py-2 border">
                                    {{ $user->address_line1 ?? 'Not specified' }}
                                </div>
                            </div>
                        </div>
                        
                        <!-- Address Line 2 -->
                        <div class="flex flex-col md:flex-row md:items-center gap-3">
                            <label class="text-xs font-medium text-gray-700 md:w-40">
                                Address Line 2
                            </label>
                            <div class="flex-1">
                                <div class="w-full text-xs border-gray-200 bg-gray-50 rounded-[1px] px-3 py-2 border">
                                    {{ $user->address_line2 ?? 'Not specified' }}
                                </div>
                            </div>
                        </div>
                        
                        <!-- State -->
                        <div class="flex flex-col md:flex-row md:items-center gap-3">
                            <label class="text-xs font-medium text-gray-700 md:w-40">
                                State
                            </label>
                            <div class="flex-1">
                                <div class="w-full text-xs border-gray-200 bg-gray-50 rounded-[1px] px-3 py-2 border">
                                    {{ $user->state ?? 'Not specified' }}
                                </div>
                            </div>
                        </div>
                        
                        <!-- City -->
                        <div class="flex flex-col md:flex-row md:items-center gap-3">
                            <label class="text-xs font-medium text-gray-700 md:w-40">
                                City
                            </label>
                            <div class="flex-1">
                                <div class="w-full text-xs border-gray-200 bg-gray-50 rounded-[1px] px-3 py-2 border">
                                    {{ $user->city ?? 'Not specified' }}
                                </div>
                            </div>
                        </div>
                        
                        <!-- Postcode -->
                        <div class="flex flex-col md:flex-row md:items-center gap-3">
                            <label class="text-xs font-medium text-gray-700 md:w-40">
                                Postcode
                            </label>
                            <div class="flex-1">
                                <div class="w-full text-xs border-gray-200 bg-gray-50 rounded-[1px] px-3 py-2 border">
                                    {{ $user->postcode ?? 'Not specified' }}
                                </div>
                            </div>
                        </div>
                        
                        <!-- Country -->
                        <div class="flex flex-col md:flex-row md:items-center gap-3">
                            <label class="text-xs font-medium text-gray-700 md:w-40">
                                Country
                            </label>
                            <div class="flex-1">
                                <div class="w-full text-xs border-gray-200 bg-gray-50 rounded-[1px] px-3 py-2 border">
                                    {{ $user->country ?? 'Not specified' }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Organization Information Section -->
            <div class="bg-white border border-gray-200 rounded-md shadow-sm">
                <div class="bg-gray-50 border-b border-gray-200 px-4 py-3">
                    <h2 class="text-sm font-semibold text-gray-700 flex items-center">
                        <span class="material-icons-outlined text-primary-DEFAULT mr-2">business</span>
                        Organization Information
                    </h2>
                </div>
                
                <div class="p-4">
                    <div class="space-y-3">
                        <!-- Organization Type -->
                        <div class="flex flex-col md:flex-row md:items-center gap-3">
                            <label class="text-xs font-medium text-gray-700 md:w-40">
                                Organization Type
                            </label>
                            <div class="flex-1">
                                <div class="w-full text-xs border-gray-200 bg-gray-50 rounded-[1px] px-3 py-2 border">
                                    @if($user->org_type === 'company')
                                        Company
                                    @elseif($user->org_type === 'government')
                                        Government
                                    @else
                                        Not specified
                                    @endif
                                </div>
                            </div>
                        </div>
                        
                        <!-- Organization Name -->
                        <div class="flex flex-col md:flex-row md:items-center gap-3">
                            <label class="text-xs font-medium text-gray-700 md:w-40">
                                Organization Name
                            </label>
                            <div class="flex-1">
                                <div class="w-full text-xs border-gray-200 bg-gray-50 rounded-[1px] px-3 py-2 border">
                                    {{ $user->org_name ?? 'Not specified' }}
                                </div>
                            </div>
                        </div>
                        
                        <!-- Organization Address Line 1 -->
                        <div class="flex flex-col md:flex-row md:items-center gap-3">
                            <label class="text-xs font-medium text-gray-700 md:w-40">
                                Org Address Line 1
                            </label>
                            <div class="flex-1">
                                <div class="w-full text-xs border-gray-200 bg-gray-50 rounded-[1px] px-3 py-2 border">
                                    {{ $user->org_address_line1 ?? 'Not specified' }}
                                </div>
                            </div>
                        </div>
                        
                        <!-- Organization Address Line 2 -->
                        <div class="flex flex-col md:flex-row md:items-center gap-3">
                            <label class="text-xs font-medium text-gray-700 md:w-40">
                                Org Address Line 2
                            </label>
                            <div class="flex-1">
                                <div class="w-full text-xs border-gray-200 bg-gray-50 rounded-[1px] px-3 py-2 border">
                                    {{ $user->org_address_line2 ?? 'Not specified' }}
                                </div>
                            </div>
                        </div>
                        
                        <!-- Organization State -->
                        <div class="flex flex-col md:flex-row md:items-center gap-3">
                            <label class="text-xs font-medium text-gray-700 md:w-40">
                                Organization State
                            </label>
                            <div class="flex-1">
                                <div class="w-full text-xs border-gray-200 bg-gray-50 rounded-[1px] px-3 py-2 border">
                                    {{ $user->org_state ?? 'Not specified' }}
                                </div>
                            </div>
                        </div>
                        
                        <!-- Organization City -->
                        <div class="flex flex-col md:flex-row md:items-center gap-3">
                            <label class="text-xs font-medium text-gray-700 md:w-40">
                                Organization City
                            </label>
                            <div class="flex-1">
                                <div class="w-full text-xs border-gray-200 bg-gray-50 rounded-[1px] px-3 py-2 border">
                                    {{ $user->org_city ?? 'Not specified' }}
                                </div>
                            </div>
                        </div>
                        
                        <!-- Organization Postcode -->
                        <div class="flex flex-col md:flex-row md:items-center gap-3">
                            <label class="text-xs font-medium text-gray-700 md:w-40">
                                Organization Postcode
                            </label>
                            <div class="flex-1">
                                <div class="w-full text-xs border-gray-200 bg-gray-50 rounded-[1px] px-3 py-2 border">
                                    {{ $user->org_postcode ?? 'Not specified' }}
                                </div>
                            </div>
                        </div>
                        
                        <!-- Organization Country -->
                        <div class="flex flex-col md:flex-row md:items-center gap-3">
                            <label class="text-xs font-medium text-gray-700 md:w-40">
                                Organization Country
                            </label>
                            <div class="flex-1">
                                <div class="w-full text-xs border-gray-200 bg-gray-50 rounded-[1px] px-3 py-2 border">
                                    {{ $user->org_country ?? 'Not specified' }}
                                </div>
                            </div>
                        </div>
                        
                        <!-- Organization Telephone -->
                        <div class="flex flex-col md:flex-row md:items-center gap-3">
                            <label class="text-xs font-medium text-gray-700 md:w-40">
                                Organization Telephone
                            </label>
                            <div class="flex-1">
                                <div class="w-full text-xs border-gray-200 bg-gray-50 rounded-[1px] px-3 py-2 border">
                                    {{ $user->org_telephone ?? 'Not specified' }}
                                </div>
                            </div>
                        </div>
                        
                        <!-- Organization Fax -->
                        <div class="flex flex-col md:flex-row md:items-center gap-3">
                            <label class="text-xs font-medium text-gray-700 md:w-40">
                                Organization Fax
                            </label>
                            <div class="flex-1">
                                <div class="w-full text-xs border-gray-200 bg-gray-50 rounded-[1px] px-3 py-2 border">
                                    {{ $user->org_fax ?? 'Not specified' }}
                                </div>
                            </div>
                        </div>
                        
                        <!-- Organization Email -->
                        <div class="flex flex-col md:flex-row md:items-center gap-3">
                            <label class="text-xs font-medium text-gray-700 md:w-40">
                                Organization Email
                            </label>
                            <div class="flex-1">
                                <div class="w-full text-xs border-gray-200 bg-gray-50 rounded-[1px] px-3 py-2 border">
                                    {{ $user->org_email ?? 'Not specified' }}
                                </div>
                            </div>
                        </div>
                        
                        <!-- Organization Website -->
                        <div class="flex flex-col md:flex-row md:items-center gap-3">
                            <label class="text-xs font-medium text-gray-700 md:w-40">
                                Organization Website
                            </label>
                            <div class="flex-1">
                                <div class="w-full text-xs border-gray-200 bg-gray-50 rounded-[1px] px-3 py-2 border">
                                    @if($user->org_website)
                                        <a href="{{ $user->org_website }}" target="_blank" class="text-blue-600 hover:underline">
                                            {{ $user->org_website }}
                                        </a>
                                    @else
                                        Not specified
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Account Information Section -->
            <div class="bg-white border border-gray-200 rounded-md shadow-sm">
                <div class="bg-gray-50 border-b border-gray-200 px-4 py-3">
                    <h2 class="text-sm font-semibold text-gray-700 flex items-center">
                        <span class="material-icons-outlined text-primary-DEFAULT mr-2">lock</span>
                        Account Information
                    </h2>
                </div>
                
                <div class="p-4">
                    <div class="space-y-3">
                        <!-- Created Date -->
                        <div class="flex flex-col md:flex-row md:items-center gap-3">
                            <label class="text-xs font-medium text-gray-700 md:w-40">
                                Created Date
                            </label>
                            <div class="flex-1">
                                <div class="w-full text-xs border-gray-200 bg-gray-50 rounded-[1px] px-3 py-2 border">
                                    {{ $user->created_at instanceof \DateTime ? $user->created_at->format('d M Y - H:i:s') : ($user->created_at ?? 'N/A') }}
                                </div>
                            </div>
                        </div>
                        
                        <!-- Last Login -->
                        <div class="flex flex-col md:flex-row md:items-center gap-3">
                            <label class="text-xs font-medium text-gray-700 md:w-40">
                                Last Login
                            </label>
                            <div class="flex-1">
                                <div class="w-full text-xs border-gray-200 bg-gray-50 rounded-[1px] px-3 py-2 border">
                                    {{ $user->last_login_at instanceof \DateTime ? $user->last_login_at->format('d M Y - H:i:s') : ($user->last_login_at ?? 'Never') }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Removed "Back to User List" button since it already exists at the top right -->
        </div>
    </div>
</x-app-layout> 