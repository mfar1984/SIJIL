<x-app-layout>
    <x-slot name="breadcrumb">
        <span>Settings</span>
        <span class="mx-2 text-gray-500">/</span>
        <span>User Management</span>
        <span class="mx-2 text-gray-500">/</span>
        <span>Create User</span>
    </x-slot>

    <x-slot name="title">Create New User</x-slot>

    <style>
        /* Tooltip styles */
        .tooltip-wrapper {
            position: relative;
            display: inline-flex;
        }
        
        .tooltip-content {
            position: absolute;
            bottom: 100%;
            left: 50%;
            transform: translateX(-50%) translateY(-4px);
            background-color: #1f2937;
            color: white;
            padding: 6px 10px;
            border-radius: 6px;
            font-size: 11px;
            white-space: nowrap;
            z-index: 1000;
            pointer-events: none;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        }
        
        .tooltip-content::after {
            content: '';
            position: absolute;
            top: 100%;
            left: 50%;
            transform: translateX(-50%);
            border: 4px solid transparent;
            border-top-color: #1f2937;
        }
    </style>

    <div class="bg-white rounded shadow-md border border-gray-300">
        <div class="p-6 border-b border-gray-200">
            <div class="flex items-center">
                <span class="material-icons-outlined mr-2 text-primary-DEFAULT">person_add</span>
                <h1 class="text-xl font-bold text-gray-800">Create New User</h1>
            </div>
            <p class="text-xs text-gray-500 mt-1 ml-8">Add a new user to the system</p>
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

            <form method="POST" action="{{ route('user.store') }}" class="space-y-2">
                @csrf
                
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
                                <label for="name" class="text-xs font-medium text-gray-700 md:w-40 flex items-center gap-1">
                                    Full Name
                                    <div class="tooltip-wrapper" x-data="{ show: false }">
                                        <span class="material-icons-outlined text-gray-400 text-sm cursor-help" 
                                              @mouseenter="show = true" 
                                              @mouseleave="show = false">
                                            help_outline
                                        </span>
                                        <div x-show="show" x-transition class="tooltip-content">
                                            Enter user's full name as it appears on official documents
                                        </div>
                                    </div>
                                </label>
                                <div class="flex-1">
                                    <div class="relative">
                                        <input 
                                            type="text" 
                                            name="name" 
                                            id="name" 
                                            class="w-full h-9 text-xs border-gray-300 rounded px-3 focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50" 
                                            value="{{ old('name') }}" 
                                            required
                                        >
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Email -->
                            <div class="flex flex-col md:flex-row md:items-center gap-3">
                                <label for="email" class="text-xs font-medium text-gray-700 md:w-40 flex items-center gap-1">
                                    Email Address
                                    <div class="tooltip-wrapper" x-data="{ show: false }">
                                        <span class="material-icons-outlined text-gray-400 text-sm cursor-help" 
                                              @mouseenter="show = true" 
                                              @mouseleave="show = false">
                                            help_outline
                                        </span>
                                        <div x-show="show" x-transition class="tooltip-content">
                                            This email will be used for login and notifications
                                        </div>
                                    </div>
                                </label>
                                <div class="flex-1">
                                    <div class="relative">
                                        <input 
                                            type="email" 
                                            name="email" 
                                            id="email" 
                                            class="w-full h-9 text-xs border-gray-300 rounded px-3 focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50" 
                                            value="{{ old('email') }}" 
                                            required
                                        >
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Phone -->
                            <div class="flex flex-col md:flex-row md:items-center gap-3">
                                <label for="phone" class="text-xs font-medium text-gray-700 md:w-40 flex items-center gap-1">
                                    Phone Number
                                    <div class="tooltip-wrapper" x-data="{ show: false }">
                                        <span class="material-icons-outlined text-gray-400 text-sm cursor-help" 
                                              @mouseenter="show = true" 
                                              @mouseleave="show = false">
                                            help_outline
                                        </span>
                                        <div x-show="show" x-transition class="tooltip-content">
                                            Include country code (e.g., +60 for Malaysia)
                                        </div>
                                    </div>
                                </label>
                                <div class="flex-1">
                                    <input type="tel" name="phone" id="phone" class="w-full h-9 text-xs border-gray-300 rounded px-3 phone-input focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50" value="{{ old('phone') }}" placeholder="123456789" required>
                                </div>
                            </div>
                            
                            <!-- Role -->
                            <div class="flex flex-col md:flex-row md:items-center gap-3">
                                <label for="role" class="text-xs font-medium text-gray-700 md:w-40 flex items-center gap-1">
                                    Role
                                    <div class="tooltip-wrapper" x-data="{ show: false }">
                                        <span class="material-icons-outlined text-gray-400 text-sm cursor-help" 
                                              @mouseenter="show = true" 
                                              @mouseleave="show = false">
                                            help_outline
                                        </span>
                                        <div x-show="show" x-transition class="tooltip-content">
                                            Determines user permissions and access level
                                        </div>
                                    </div>
                                </label>
                                <div class="flex-1">
                                    <div class="relative">
                                        <select 
                                            name="role_id" 
                                            id="role" 
                                            class="w-full h-9 text-xs border-gray-300 rounded px-3 focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50" 
                                            required
                                        >
                                            <option value="">Select Role</option>
                                            @foreach($roles as $role)
                                                <option value="{{ $role->id }}" {{ old('role_id') == $role->id ? 'selected' : '' }}>{{ $role->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
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
                                            Inactive users cannot login to the system
                                        </div>
                                    </div>
                                </label>
                                <div class="flex-1">
                                    <div class="relative">
                                        <select 
                                            name="status" 
                                            id="status" 
                                            class="w-full h-9 text-xs border-gray-300 rounded px-3 focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50" 
                                            required
                                        >
                                            <option value="active" {{ old('status') == 'active' ? 'selected' : '' }}>Active</option>
                                            <option value="inactive" {{ old('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                                        </select>
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
                                <label for="address_line1" class="text-xs font-medium text-gray-700 md:w-40 flex items-center gap-1">
                                    Address Line 1
                                    <div class="tooltip-wrapper" x-data="{ show: false }">
                                        <span class="material-icons-outlined text-gray-400 text-sm cursor-help" 
                                              @mouseenter="show = true" 
                                              @mouseleave="show = false">
                                            help_outline
                                        </span>
                                        <div x-show="show" x-transition class="tooltip-content">
                                            Street address, P.O. box, company name
                                        </div>
                                    </div>
                                </label>
                                <div class="flex-1">
                                    <div class="relative">
                                        <input 
                                            type="text" 
                                            name="address_line1" 
                                            id="address_line1" 
                                            class="w-full h-9 text-xs border-gray-300 rounded px-3 focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50" 
                                            value="{{ old('address_line1') }}" 
                                        >
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Address Line 2 -->
                            <div class="flex flex-col md:flex-row md:items-center gap-3">
                                <label for="address_line2" class="text-xs font-medium text-gray-700 md:w-40 flex items-center gap-1">
                                    Address Line 2
                                    <div class="tooltip-wrapper" x-data="{ show: false }">
                                        <span class="material-icons-outlined text-gray-400 text-sm cursor-help" 
                                              @mouseenter="show = true" 
                                              @mouseleave="show = false">
                                            help_outline
                                        </span>
                                        <div x-show="show" x-transition class="tooltip-content">
                                            Apartment, suite, unit, building, floor, etc.
                                        </div>
                                    </div>
                                </label>
                                <div class="flex-1">
                                    <div class="relative">
                                        <input 
                                            type="text" 
                                            name="address_line2" 
                                            id="address_line2" 
                                            class="w-full h-9 text-xs border-gray-300 rounded px-3 focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50" 
                                            value="{{ old('address_line2') }}" 
                                        >
                                    </div>
                                </div>
                            </div>
                            
                            <!-- State -->
                            <div class="flex flex-col md:flex-row md:items-center gap-3">
                                <label for="state" class="text-xs font-medium text-gray-700 md:w-40 flex items-center gap-1">
                                    State
                                    <div class="tooltip-wrapper" x-data="{ show: false }">
                                        <span class="material-icons-outlined text-gray-400 text-sm cursor-help" 
                                              @mouseenter="show = true" 
                                              @mouseleave="show = false">
                                            help_outline
                                        </span>
                                        <div x-show="show" x-transition class="tooltip-content">
                                            Select the state for your address
                                        </div>
                                    </div>
                                </label>
                                <div class="flex-1">
                                    <div class="relative">
                                        <select 
                                            name="state" 
                                            id="state" 
                                            class="w-full h-9 text-xs border-gray-300 rounded px-3 focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50" 
                                            onchange="updateCities()"
                                        >
                                            <option value="">Select State</option>
                                            <!-- States will be populated by JavaScript -->
                                        </select>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- City -->
                            <div class="flex flex-col md:flex-row md:items-center gap-3">
                                <label for="city" class="text-xs font-medium text-gray-700 md:w-40 flex items-center gap-1">
                                    City
                                    <div class="tooltip-wrapper" x-data="{ show: false }">
                                        <span class="material-icons-outlined text-gray-400 text-sm cursor-help" 
                                              @mouseenter="show = true" 
                                              @mouseleave="show = false">
                                            help_outline
                                        </span>
                                        <div x-show="show" x-transition class="tooltip-content">
                                            City or town for your address
                                        </div>
                                    </div>
                                </label>
                                <div class="flex-1">
                                    <div class="relative">
                                        <select 
                                            name="city" 
                                            id="city" 
                                            class="w-full h-9 text-xs border-gray-300 rounded px-3 focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50" 
                                            onchange="lookupPostcodesByCity()"
                                        >
                                            <option value="">Select City</option>
                                            <!-- Cities will be populated by JavaScript -->
                                        </select>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Postcode -->
                            <div class="flex flex-col md:flex-row md:items-center gap-3">
                                <label for="postcode" class="text-xs font-medium text-gray-700 md:w-40 flex items-center gap-1">
                                    Postcode
                                    <div class="tooltip-wrapper" x-data="{ show: false }">
                                        <span class="material-icons-outlined text-gray-400 text-sm cursor-help" 
                                              @mouseenter="show = true" 
                                              @mouseleave="show = false">
                                            help_outline
                                        </span>
                                        <div x-show="show" x-transition class="tooltip-content">
                                            Postal or ZIP code
                                        </div>
                                    </div>
                                </label>
                                <div class="flex-1">
                                    <div class="relative">
                                        <select 
                                            name="postcode" 
                                            id="postcode" 
                                            class="w-full h-9 text-xs border-gray-300 rounded px-3 focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50"
                                        >
                                            <option value="">Select Postcode</option>
                                            <!-- Postcodes will be populated by JavaScript -->
                                        </select>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Country -->
                            <div class="flex flex-col md:flex-row md:items-center gap-3">
                                <label for="country" class="text-xs font-medium text-gray-700 md:w-40 flex items-center gap-1">
                                    Country
                                    <div class="tooltip-wrapper" x-data="{ show: false }">
                                        <span class="material-icons-outlined text-gray-400 text-sm cursor-help" 
                                              @mouseenter="show = true" 
                                              @mouseleave="show = false">
                                            help_outline
                                        </span>
                                        <div x-show="show" x-transition class="tooltip-content">
                                            Default country is Malaysia
                                        </div>
                                    </div>
                                </label>
                                <div class="flex-1">
                                    <div class="relative">
                                        <select name="country" id="country" class="w-full h-9 text-xs border-gray-300 rounded px-3 focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50" data-old-value="{{ old('country', 'Malaysia') }}">
                                            <!-- Dropdown will be filled by JavaScript -->
                                        </select>
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
                                <label for="org_type" class="text-xs font-medium text-gray-700 md:w-40 flex items-center gap-1">
                                    Organization Type
                                    <div class="tooltip-wrapper" x-data="{ show: false }">
                                        <span class="material-icons-outlined text-gray-400 text-sm cursor-help" 
                                              @mouseenter="show = true" 
                                              @mouseleave="show = false">
                                            help_outline
                                        </span>
                                        <div x-show="show" x-transition class="tooltip-content">
                                            Select the type of organization
                                        </div>
                                    </div>
                                </label>
                                <div class="flex-1">
                                    <div class="relative">
                                        <select 
                                            name="org_type" 
                                            id="org_type" 
                                            class="w-full h-9 text-xs border-gray-300 rounded px-3 focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50"
                                        >
                                            <option value="">Select Type</option>
                                            <option value="company" {{ old('org_type') == 'company' ? 'selected' : '' }}>Company</option>
                                            <option value="government" {{ old('org_type') == 'government' ? 'selected' : '' }}>Government</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Organization Name -->
                            <div class="flex flex-col md:flex-row md:items-center gap-3">
                                <label for="org_name" class="text-xs font-medium text-gray-700 md:w-40 flex items-center gap-1">
                                    Organization Name
                                    <div class="tooltip-wrapper" x-data="{ show: false }">
                                        <span class="material-icons-outlined text-gray-400 text-sm cursor-help" 
                                              @mouseenter="show = true" 
                                              @mouseleave="show = false">
                                            help_outline
                                        </span>
                                        <div x-show="show" x-transition class="tooltip-content">
                                            Full legal name of the organization
                                        </div>
                                    </div>
                                </label>
                                <div class="flex-1">
                                    <div class="relative">
                                        <input 
                                            type="text" 
                                            name="org_name" 
                                            id="org_name" 
                                            class="w-full h-9 text-xs border-gray-300 rounded px-3 focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50" 
                                            value="{{ old('org_name') }}"
                                        >
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Organization Address Line 1 -->
                            <div class="flex flex-col md:flex-row md:items-center gap-3">
                                <label for="org_address_line1" class="text-xs font-medium text-gray-700 md:w-40 flex items-center gap-1">
                                    Org Address Line 1
                                    <div class="tooltip-wrapper" x-data="{ show: false }">
                                        <span class="material-icons-outlined text-gray-400 text-sm cursor-help" 
                                              @mouseenter="show = true" 
                                              @mouseleave="show = false">
                                            help_outline
                                        </span>
                                        <div x-show="show" x-transition class="tooltip-content">
                                            Main address of the organization (building, street)
                                        </div>
                                    </div>
                                </label>
                                <div class="flex-1">
                                    <div class="relative">
                                        <input 
                                            type="text" 
                                            name="org_address_line1" 
                                            id="org_address_line1" 
                                            class="w-full h-9 text-xs border-gray-300 rounded px-3 focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50" 
                                            value="{{ old('org_address_line1') }}" 
                                        >
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Organization Address Line 2 -->
                            <div class="flex flex-col md:flex-row md:items-center gap-3">
                                <label for="org_address_line2" class="text-xs font-medium text-gray-700 md:w-40 flex items-center gap-1">
                                    Org Address Line 2
                                    <div class="tooltip-wrapper" x-data="{ show: false }">
                                        <span class="material-icons-outlined text-gray-400 text-sm cursor-help" 
                                              @mouseenter="show = true" 
                                              @mouseleave="show = false">
                                            help_outline
                                        </span>
                                        <div x-show="show" x-transition class="tooltip-content">
                                            Additional address information (floor, unit, etc.)
                                        </div>
                                    </div>
                                </label>
                                <div class="flex-1">
                                    <div class="relative">
                                        <input 
                                            type="text" 
                                            name="org_address_line2" 
                                            id="org_address_line2" 
                                            class="w-full h-9 text-xs border-gray-300 rounded px-3 focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50" 
                                            value="{{ old('org_address_line2') }}" 
                                        >
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Organization State -->
                            <div class="flex flex-col md:flex-row md:items-center gap-3">
                                <label for="org_state" class="text-xs font-medium text-gray-700 md:w-40 flex items-center gap-1">
                                    Organization State
                                    <div class="tooltip-wrapper" x-data="{ show: false }">
                                        <span class="material-icons-outlined text-gray-400 text-sm cursor-help" 
                                              @mouseenter="show = true" 
                                              @mouseleave="show = false">
                                            help_outline
                                        </span>
                                        <div x-show="show" x-transition class="tooltip-content">
                                            State where the organization is located
                                        </div>
                                    </div>
                                </label>
                                <div class="flex-1">
                                    <div class="relative">
                                        <select 
                                            name="org_state" 
                                            id="org_state" 
                                            class="w-full h-9 text-xs border-gray-300 rounded px-3 focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50" 
                                            onchange="updateOrgCities()"
                                        >
                                            <option value="">Select State</option>
                                            <!-- States will be populated by JavaScript -->
                                        </select>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Organization City -->
                            <div class="flex flex-col md:flex-row md:items-center gap-3">
                                <label for="org_city" class="text-xs font-medium text-gray-700 md:w-40 flex items-center gap-1">
                                    Organization City
                                    <div class="tooltip-wrapper" x-data="{ show: false }">
                                        <span class="material-icons-outlined text-gray-400 text-sm cursor-help" 
                                              @mouseenter="show = true" 
                                              @mouseleave="show = false">
                                            help_outline
                                        </span>
                                        <div x-show="show" x-transition class="tooltip-content">
                                            City where the organization is located
                                        </div>
                                    </div>
                                </label>
                                <div class="flex-1">
                                    <div class="relative">
                                        <select 
                                            name="org_city" 
                                            id="org_city" 
                                            class="w-full h-9 text-xs border-gray-300 rounded px-3 focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50" 
                                            onchange="lookupOrgPostcodesByCity()"
                                        >
                                            <option value="">Select City</option>
                                            <!-- Cities will be populated by JavaScript -->
                                        </select>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Organization Postcode -->
                            <div class="flex flex-col md:flex-row md:items-center gap-3">
                                <label for="org_postcode" class="text-xs font-medium text-gray-700 md:w-40 flex items-center gap-1">
                                    Organization Postcode
                                    <div class="tooltip-wrapper" x-data="{ show: false }">
                                        <span class="material-icons-outlined text-gray-400 text-sm cursor-help" 
                                              @mouseenter="show = true" 
                                              @mouseleave="show = false">
                                            help_outline
                                        </span>
                                        <div x-show="show" x-transition class="tooltip-content">
                                            Postal code for the organization
                                        </div>
                                    </div>
                                </label>
                                <div class="flex-1">
                                    <div class="relative">
                                        <select 
                                            name="org_postcode" 
                                            id="org_postcode" 
                                            class="w-full h-9 text-xs border-gray-300 rounded px-3 focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50"
                                        >
                                            <option value="">Select Postcode</option>
                                            <!-- Postcodes will be populated by JavaScript -->
                                        </select>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Organization Country -->
                            <div class="flex flex-col md:flex-row md:items-center gap-3">
                                <label for="org_country" class="text-xs font-medium text-gray-700 md:w-40 flex items-center gap-1">
                                    Organization Country
                                    <div class="tooltip-wrapper" x-data="{ show: false }">
                                        <span class="material-icons-outlined text-gray-400 text-sm cursor-help" 
                                              @mouseenter="show = true" 
                                              @mouseleave="show = false">
                                            help_outline
                                        </span>
                                        <div x-show="show" x-transition class="tooltip-content">
                                            Default country is Malaysia
                                        </div>
                                    </div>
                                </label>
                                <div class="flex-1">
                                    <div class="relative">
                                        <select name="org_country" id="org_country" class="w-full h-9 text-xs border-gray-300 rounded px-3 focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50" data-old-value="{{ old('org_country', 'Malaysia') }}">
                                            <!-- Dropdown will be filled by JavaScript -->
                                        </select>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Organization Telephone -->
                            <div class="flex flex-col md:flex-row md:items-center gap-3">
                                <label for="org_telephone" class="text-xs font-medium text-gray-700 md:w-40 flex items-center gap-1">
                                    Organization Telephone
                                    <div class="tooltip-wrapper" x-data="{ show: false }">
                                        <span class="material-icons-outlined text-gray-400 text-sm cursor-help" 
                                              @mouseenter="show = true" 
                                              @mouseleave="show = false">
                                            help_outline
                                        </span>
                                        <div x-show="show" x-transition class="tooltip-content">
                                            Main contact number for the organization
                                        </div>
                                    </div>
                                </label>
                                <div class="flex-1">
                                    <input type="tel" name="org_telephone" id="org_telephone" class="w-full h-9 text-xs border-gray-300 rounded px-3 phone-input focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50" value="{{ old('org_telephone') }}">
                                </div>
                            </div>
                            
                            <!-- Organization Fax -->
                            <div class="flex flex-col md:flex-row md:items-center gap-3">
                                <label for="org_fax" class="text-xs font-medium text-gray-700 md:w-40 flex items-center gap-1">
                                    Organization Fax
                                    <div class="tooltip-wrapper" x-data="{ show: false }">
                                        <span class="material-icons-outlined text-gray-400 text-sm cursor-help" 
                                              @mouseenter="show = true" 
                                              @mouseleave="show = false">
                                            help_outline
                                        </span>
                                        <div x-show="show" x-transition class="tooltip-content">
                                            Fax number for the organization (if available)
                                        </div>
                                    </div>
                                </label>
                                <div class="flex-1">
                                    <div class="relative">
                                        <input 
                                            type="tel" 
                                            name="org_fax" 
                                            id="org_fax" 
                                            class="w-full h-9 text-xs border-gray-300 rounded px-3 focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50" 
                                            value="{{ old('org_fax') }}"
                                        >
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Organization Email -->
                            <div class="flex flex-col md:flex-row md:items-center gap-3">
                                <label for="org_email" class="text-xs font-medium text-gray-700 md:w-40 flex items-center gap-1">
                                    Organization Email
                                    <div class="tooltip-wrapper" x-data="{ show: false }">
                                        <span class="material-icons-outlined text-gray-400 text-sm cursor-help" 
                                              @mouseenter="show = true" 
                                              @mouseleave="show = false">
                                            help_outline
                                        </span>
                                        <div x-show="show" x-transition class="tooltip-content">
                                            Official email address for the organization
                                        </div>
                                    </div>
                                </label>
                                <div class="flex-1">
                                    <div class="relative">
                                        <input 
                                            type="email" 
                                            name="org_email" 
                                            id="org_email" 
                                            class="w-full h-9 text-xs border-gray-300 rounded px-3 focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50" 
                                            value="{{ old('org_email') }}"
                                        >
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Organization Website -->
                            <div class="flex flex-col md:flex-row md:items-center gap-3">
                                <label for="org_website" class="text-xs font-medium text-gray-700 md:w-40 flex items-center gap-1">
                                    Organization Website
                                    <div class="tooltip-wrapper" x-data="{ show: false }">
                                        <span class="material-icons-outlined text-gray-400 text-sm cursor-help" 
                                              @mouseenter="show = true" 
                                              @mouseleave="show = false">
                                            help_outline
                                        </span>
                                        <div x-show="show" x-transition class="tooltip-content">
                                            Official website URL (include https://)
                                        </div>
                                    </div>
                                </label>
                                <div class="flex-1">
                                    <div class="relative">
                                        <input 
                                            type="url" 
                                            name="org_website" 
                                            id="org_website" 
                                            class="w-full h-9 text-xs border-gray-300 rounded px-3 focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50" 
                                            value="{{ old('org_website') }}"
                                            placeholder="https://example.com"
                                        >
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Account Settings Section -->
                <div class="bg-white border border-gray-200 rounded-md shadow-sm">
                    <div class="bg-gray-50 border-b border-gray-200 px-4 py-3">
                        <h2 class="text-sm font-semibold text-gray-700 flex items-center">
                            <span class="material-icons-outlined text-primary-DEFAULT mr-2">lock</span>
                            Account Settings
                        </h2>
                    </div>
                    
                    <div class="p-4">
                        <div class="space-y-3">
                            <!-- Password -->
                            <div class="flex flex-col md:flex-row md:items-center gap-3">
                                <label for="password" class="text-xs font-medium text-gray-700 md:w-40 flex items-center gap-1">
                                    Password
                                    <div class="tooltip-wrapper" x-data="{ show: false }">
                                        <span class="material-icons-outlined text-gray-400 text-sm cursor-help" 
                                              @mouseenter="show = true" 
                                              @mouseleave="show = false">
                                            help_outline
                                        </span>
                                        <div x-show="show" x-transition class="tooltip-content">
                                            Must be at least 8 characters with letters and numbers
                                        </div>
                                    </div>
                                </label>
                                <div class="flex-1">
                                    <div class="relative">
                                        <input 
                                            type="password" 
                                            name="password" 
                                            id="password" 
                                            class="w-full h-9 text-xs border-gray-300 rounded px-3 focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50" 
                                            required
                                        >
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Confirm Password -->
                            <div class="flex flex-col md:flex-row md:items-center gap-3">
                                <label for="password_confirmation" class="text-xs font-medium text-gray-700 md:w-40 flex items-center gap-1">
                                    Confirm Password
                                    <div class="tooltip-wrapper" x-data="{ show: false }">
                                        <span class="material-icons-outlined text-gray-400 text-sm cursor-help" 
                                              @mouseenter="show = true" 
                                              @mouseleave="show = false">
                                            help_outline
                                        </span>
                                        <div x-show="show" x-transition class="tooltip-content">
                                            Re-enter your password to confirm
                                        </div>
                                    </div>
                                </label>
                                <div class="flex-1">
                                    <div class="relative">
                                        <input 
                                            type="password" 
                                            name="password_confirmation" 
                                            id="password_confirmation" 
                                            class="w-full h-9 text-xs border-gray-300 rounded px-3 focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50" 
                                            required
                                        >
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="flex justify-end space-x-3 pt-4">
                    <a 
                        href="{{ route('user.management') }}" 
                        class="px-3 h-[36px] bg-gradient-to-r from-red-600 to-red-500 hover:from-red-700 hover:to-red-600 text-white rounded shadow-sm text-xs font-medium transition-colors duration-200 ease-in-out flex items-center"
                    >
                        <span class="material-icons-outlined text-xs mr-1">cancel</span>
                        Cancel
                    </a>
                    <button 
                        type="submit" 
                        class="px-3 h-[36px] bg-gradient-to-r from-green-600 to-green-500 hover:from-green-700 hover:to-green-600 text-white rounded shadow-sm text-xs font-medium transition-colors duration-200 ease-in-out flex items-center"
                    >
                        <span class="material-icons-outlined text-xs mr-1">save</span>
                        Save User
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Malaysia Postcodes Integration -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Populate states dropdown
            populateStates();
            populateStates('org_state'); // For organization state dropdown
            
            // If there are old values, try to repopulate the form
            const oldState = "{{ old('state') }}";
            const oldCity = "{{ old('city') }}";
            const oldPostcode = "{{ old('postcode') }}";
            
            if (oldState) {
                document.getElementById('state').value = oldState;
                updateCities();
                
                if (oldCity) {
                    setTimeout(() => {
                        document.getElementById('city').value = oldCity;
                        lookupPostcodesByCity();
                        
                        if (oldPostcode) {
                            setTimeout(() => {
                                document.getElementById('postcode').value = oldPostcode;
                            }, 100);
                        }
                    }, 100);
                }
            }
            
            // For organization fields
            const oldOrgState = "{{ old('org_state') }}";
            const oldOrgCity = "{{ old('org_city') }}";
            const oldOrgPostcode = "{{ old('org_postcode') }}";
            
            if (oldOrgState) {
                document.getElementById('org_state').value = oldOrgState;
                updateOrgCities();
                
                if (oldOrgCity) {
                    setTimeout(() => {
                        document.getElementById('org_city').value = oldOrgCity;
                        lookupOrgPostcodesByCity();
                        
                        if (oldOrgPostcode) {
                            setTimeout(() => {
                                document.getElementById('org_postcode').value = oldOrgPostcode;
                            }, 100);
                        }
                    }, 100);
                }
            }
        });
        
        function populateStates(fieldId = 'state') {
            try {
                const stateSelect = document.getElementById(fieldId);
                
                // Clear existing options except the first one
                while (stateSelect.options.length > 1) {
                    stateSelect.remove(1);
                }
                
                // Check if malaysiaPostcodes is available
                if (window.malaysiaPostcodes && window.malaysiaPostcodes.getStates) {
                    const states = window.malaysiaPostcodes.getStates();
                    
                    states.forEach(state => {
                        const option = document.createElement('option');
                        option.value = state;
                        option.textContent = state;
                        stateSelect.appendChild(option);
                    });
                    
                    // States loaded
                } else {
                    console.error('malaysiaPostcodes library not available');
                    
                    // Fallback: add some states manually
                    const fallbackStates = [
                        'Johor', 'Kedah', 'Kelantan', 'Melaka', 'Negeri Sembilan', 
                        'Pahang', 'Perak', 'Perlis', 'Pulau Pinang', 'Sabah', 'Sarawak', 
                        'Selangor', 'Terengganu', 'Wilayah Persekutuan Kuala Lumpur', 
                        'Wilayah Persekutuan Labuan', 'Wilayah Persekutuan Putrajaya'
                    ];
                    
                    fallbackStates.forEach(state => {
                        const option = document.createElement('option');
                        option.value = state;
                        option.textContent = state;
                        stateSelect.appendChild(option);
                    });
                    
                    // Using fallback states list
                }
            } catch (error) {
                console.error('Error populating states for', fieldId, ':', error);
            }
        }
        
        function updateCities() {
            updateCitiesGeneric('state', 'city', 'postcode');
        }
        
        function updateOrgCities() {
            updateCitiesGeneric('org_state', 'org_city', 'org_postcode');
        }
        
        function updateCitiesGeneric(stateFieldId, cityFieldId, postcodeFieldId) {
            try {
                const stateSelect = document.getElementById(stateFieldId);
                const citySelect = document.getElementById(cityFieldId);
                const selectedState = stateSelect.value;
                
                // Clear existing options except the first one
                while (citySelect.options.length > 1) {
                    citySelect.remove(1);
                }
                
                // Reset postcode dropdown
                const postcodeSelect = document.getElementById(postcodeFieldId);
                while (postcodeSelect.options.length > 1) {
                    postcodeSelect.remove(1);
                }
                
                if (!selectedState) return;
                
                // Check if malaysiaPostcodes is available
                if (window.malaysiaPostcodes && window.malaysiaPostcodes.getCities) {
                    const cities = window.malaysiaPostcodes.getCities(selectedState);
                    
                    cities.forEach(city => {
                        const option = document.createElement('option');
                        option.value = city;
                        option.textContent = city;
                        citySelect.appendChild(option);
                    });
                    
                    // Cities loaded
                } else {
                    console.error('malaysiaPostcodes library not available');
                    
                    // If library not available, just enable the city field for manual input
                    citySelect.innerHTML = '<option value="">Enter City Manually</option>';
                }
            } catch (error) {
                console.error('Error updating cities for', stateFieldId, ':', error);
            }
        }
        
        function lookupPostcodesByCity() {
            lookupPostcodesByCityGeneric('state', 'city', 'postcode');
        }
        
        function lookupOrgPostcodesByCity() {
            lookupPostcodesByCityGeneric('org_state', 'org_city', 'org_postcode');
        }
        
        function lookupPostcodesByCityGeneric(stateFieldId, cityFieldId, postcodeFieldId) {
            try {
                const stateSelect = document.getElementById(stateFieldId);
                const citySelect = document.getElementById(cityFieldId);
                const postcodeSelect = document.getElementById(postcodeFieldId);
                
                const selectedState = stateSelect.value;
                const selectedCity = citySelect.value;
                
                // Clear existing options except the first one
                while (postcodeSelect.options.length > 1) {
                    postcodeSelect.remove(1);
                }
                
                if (!selectedState || !selectedCity) return;
                
                // Check if malaysiaPostcodes is available
                if (window.malaysiaPostcodes && window.malaysiaPostcodes.getPostcodes) {
                    const postcodes = window.malaysiaPostcodes.getPostcodes(selectedState, selectedCity);
                    
                    postcodes.forEach(postcode => {
                        const option = document.createElement('option');
                        option.value = postcode;
                        option.textContent = postcode;
                        postcodeSelect.appendChild(option);
                    });
                    
                    // Postcodes loaded
                } else {
                    console.error('malaysiaPostcodes library not available');
                    
                    // If library not available, just enable the postcode field for manual input
                    postcodeSelect.innerHTML = '<option value="">Enter Postcode Manually</option>';
                }
            } catch (error) {
                console.error('Error looking up postcodes for', cityFieldId, ':', error);
            }
        }
    </script>
</x-app-layout> 