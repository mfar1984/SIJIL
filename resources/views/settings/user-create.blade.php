<x-app-layout>
    <x-slot name="breadcrumb">
        <span>Settings</span>
        <span class="mx-2 text-gray-500">/</span>
        <span>User Management</span>
        <span class="mx-2 text-gray-500">/</span>
        <span>Create User</span>
    </x-slot>

    <x-slot name="title">Create New User</x-slot>

    <div class="bg-white rounded shadow-md border border-gray-300">
        <div class="p-6 border-b border-gray-200">
            <div class="flex items-center">
                <span class="material-icons mr-2 text-primary-DEFAULT">person_add</span>
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

            <form method="POST" action="{{ route('user.store') }}" class="space-y-6">
                @csrf
                
                <!-- Basic Information -->
                <div class="border-b border-gray-200 pb-5">
                    <h2 class="text-sm font-semibold text-gray-700 mb-4">Basic Information</h2>
                    <!-- First row: Full Name and Email -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                        <!-- Full Name -->
                        <div>
                            <label for="name" class="flex items-center text-xs font-medium text-gray-700 mb-1">
                                <span class="material-icons text-sm mr-1 text-primary-DEFAULT">person</span>
                                Full Name
                            </label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <span class="material-icons text-[#004aad] text-base">badge</span>
                                </div>
                                <input 
                                    type="text" 
                                    name="name" 
                                    id="name" 
                                    class="w-full text-xs border-gray-300 rounded-[1px] pl-12 focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50" 
                                    value="{{ old('name') }}" 
                                    required
                                >
                            </div>
                            <p class="mt-1 text-[10px] text-gray-500">Enter user's full name as it appears on official documents</p>
                        </div>
                        
                        <!-- Email -->
                        <div>
                            <label for="email" class="flex items-center text-xs font-medium text-gray-700 mb-1">
                                <span class="material-icons text-sm mr-1 text-primary-DEFAULT">email</span>
                                Email Address
                            </label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <span class="material-icons text-[#004aad] text-base">alternate_email</span>
                                </div>
                                <input 
                                    type="email" 
                                    name="email" 
                                    id="email" 
                                    class="w-full text-xs border-gray-300 rounded-[1px] pl-12 focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50" 
                                    value="{{ old('email') }}" 
                                    required
                                >
                            </div>
                            <p class="mt-1 text-[10px] text-gray-500">This email will be used for login and notifications</p>
                        </div>
                    </div>
                    
                    <!-- Second row: Phone, Role, Status in 3 columns -->
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <!-- Phone -->
                        <div>
                            <label for="phone" class="flex items-center text-xs font-medium text-gray-700 mb-1">
                                <span class="material-icons text-sm mr-1 text-primary-DEFAULT">phone</span>
                                Phone Number
                            </label>
                            <div class="relative">
                                <div class="flex items-center">
                                    <input type="tel" name="phone" id="phone" class="phone-input w-full text-xs border-gray-300 rounded-[1px] focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50" value="{{ old('phone') }}" placeholder="123456789" required>
                                </div>
                            </div>
                            <p class="mt-1 text-[10px] text-gray-500">Include country code (e.g., +60 for Malaysia)</p>
                        </div>
                        
                        <!-- Role -->
                        <div>
                            <label for="role" class="flex items-center text-xs font-medium text-gray-700 mb-1">
                                <span class="material-icons text-sm mr-1 text-primary-DEFAULT">assignment_ind</span>
                                Role
                            </label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <span class="material-icons text-[#004aad] text-base">verified_user</span>
                                </div>
                                <select 
                                    name="role_id" 
                                    id="role" 
                                    class="w-full text-xs border-gray-300 rounded-[1px] pl-12 focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50" 
                                    required
                                >
                                    <option value="">Select Role</option>
                                    @foreach($roles as $role)
                                        <option value="{{ $role->id }}" {{ old('role_id') == $role->id ? 'selected' : '' }}>{{ $role->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <p class="mt-1 text-[10px] text-gray-500">Determines user permissions and access level</p>
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
                            <p class="mt-1 text-[10px] text-gray-500">Inactive users cannot login to the system</p>
                        </div>
                    </div>
                </div>
                
                <!-- Address Information -->
                <div class="border-b border-gray-200 pb-5">
                    <h2 class="text-sm font-semibold text-gray-700 mb-4">Address Information</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <!-- Address Line 1 -->
                        <div class="md:col-span-2">
                            <label for="address_line1" class="flex items-center text-xs font-medium text-gray-700 mb-1">
                                <span class="material-icons text-sm mr-1 text-primary-DEFAULT">home</span>
                                Address Line 1
                            </label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <span class="material-icons text-[#004aad] text-base">location_on</span>
                                </div>
                                <input 
                                    type="text" 
                                    name="address_line1" 
                                    id="address_line1" 
                                    class="w-full text-xs border-gray-300 rounded-[1px] pl-9 focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50" 
                                    value="{{ old('address_line1') }}" 
                                >
                            </div>
                            <p class="mt-1 text-[10px] text-gray-500">Street address, P.O. box, company name</p>
                        </div>
                        
                        <!-- Address Line 2 -->
                        <div class="md:col-span-2">
                            <label for="address_line2" class="flex items-center text-xs font-medium text-gray-700 mb-1">
                                <span class="material-icons text-sm mr-1 text-primary-DEFAULT">apartment</span>
                                Address Line 2
                            </label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <span class="material-icons text-[#004aad] text-base">pin_drop</span>
                                </div>
                                <input 
                                    type="text" 
                                    name="address_line2" 
                                    id="address_line2" 
                                    class="w-full text-xs border-gray-300 rounded-[1px] pl-9 focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50" 
                                    value="{{ old('address_line2') }}" 
                                >
                            </div>
                            <p class="mt-1 text-[10px] text-gray-500">Apartment, suite, unit, building, floor, etc.</p>
                        </div>
                        
                        <!-- State -->
                        <div>
                            <label for="state" class="flex items-center text-xs font-medium text-gray-700 mb-1">
                                <span class="material-icons text-sm mr-1 text-primary-DEFAULT">map</span>
                                State
                            </label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <span class="material-icons text-[#004aad] text-base">location_city</span>
                                </div>
                                <select 
                                    name="state" 
                                    id="state" 
                                    class="w-full text-xs border-gray-300 rounded-[1px] pl-9 focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50" 
                                    onchange="updateCities()"
                                >
                                    <option value="">Select State</option>
                                    <!-- States will be populated by JavaScript -->
                                </select>
                            </div>
                            <p class="mt-1 text-[10px] text-gray-500">Select the state for your address</p>
                        </div>
                        
                        <!-- City -->
                        <div>
                            <label for="city" class="flex items-center text-xs font-medium text-gray-700 mb-1">
                                <span class="material-icons text-sm mr-1 text-primary-DEFAULT">location_city</span>
                                City
                            </label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <span class="material-icons text-[#004aad] text-base">apartment</span>
                                </div>
                                <select 
                                    name="city" 
                                    id="city" 
                                    class="w-full text-xs border-gray-300 rounded-[1px] pl-9 focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50" 
                                    onchange="lookupPostcodesByCity()"
                                >
                                    <option value="">Select City</option>
                                    <!-- Cities will be populated by JavaScript -->
                                </select>
                            </div>
                            <p class="mt-1 text-[10px] text-gray-500">City or town for your address</p>
                        </div>
                        
                        <!-- Postcode -->
                        <div>
                            <label for="postcode" class="flex items-center text-xs font-medium text-gray-700 mb-1">
                                <span class="material-icons text-sm mr-1 text-primary-DEFAULT">local_post_office</span>
                                Postcode
                            </label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <span class="material-icons text-[#004aad] text-base">markunread_mailbox</span>
                                </div>
                                <select 
                                    name="postcode" 
                                    id="postcode" 
                                    class="w-full text-xs border-gray-300 rounded-[1px] pl-9 focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50"
                                >
                                    <option value="">Select Postcode</option>
                                    <!-- Postcodes will be populated by JavaScript -->
                                </select>
                            </div>
                            <p class="mt-1 text-[10px] text-gray-500">Postal or ZIP code</p>
                        </div>
                        
                        <!-- Country (Address Information) -->
                        <div>
                            <label for="country" class="flex items-center text-xs font-medium text-gray-700 mb-1">
                                <span class="material-icons text-sm mr-1 text-primary-DEFAULT">public</span>
                                Country
                            </label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <span class="material-icons text-[#004aad] text-base">flag</span>
                                </div>
                                <select name="country" id="country" class="w-full text-xs border-gray-300 rounded-[1px] pl-9 focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50" data-old-value="{{ old('country', 'Malaysia') }}">
                                    <!-- Dropdown will be filled by JavaScript -->
                                </select>
                            </div>
                            <p class="mt-1 text-[10px] text-gray-500">Default country is Malaysia</p>
                        </div>
                    </div>
                </div>
                
                <!-- Organization Information -->
                <div class="border-b border-gray-200 pb-5">
                    <h2 class="text-sm font-semibold text-gray-700 mb-4">Organization Information</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <!-- Organization Type -->
                        <div>
                            <label for="org_type" class="flex items-center text-xs font-medium text-gray-700 mb-1">
                                <span class="material-icons text-sm mr-1 text-primary-DEFAULT">category</span>
                                Organization Type
                            </label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <span class="material-icons text-[#004aad] text-base">corporate_fare</span>
                                </div>
                                <select 
                                    name="org_type" 
                                    id="org_type" 
                                    class="w-full text-xs border-gray-300 rounded-[1px] pl-9 focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50"
                                >
                                    <option value="">Select Type</option>
                                    <option value="company" {{ old('org_type') == 'company' ? 'selected' : '' }}>Company</option>
                                    <option value="government" {{ old('org_type') == 'government' ? 'selected' : '' }}>Government</option>
                                </select>
                            </div>
                            <p class="mt-1 text-[10px] text-gray-500">Select the type of organization</p>
                        </div>
                        
                        <!-- Organization Name -->
                        <div>
                            <label for="org_name" class="flex items-center text-xs font-medium text-gray-700 mb-1">
                                <span class="material-icons text-sm mr-1 text-primary-DEFAULT">business</span>
                                Organization Name
                            </label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <span class="material-icons text-[#004aad] text-base">domain</span>
                                </div>
                                <input 
                                    type="text" 
                                    name="org_name" 
                                    id="org_name" 
                                    class="w-full text-xs border-gray-300 rounded-[1px] pl-9 focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50" 
                                    value="{{ old('org_name') }}"
                                >
                            </div>
                            <p class="mt-1 text-[10px] text-gray-500">Full legal name of the organization</p>
                        </div>
                        
                        <!-- Organization Address Line 1 -->
                        <div class="md:col-span-2">
                            <label for="org_address_line1" class="flex items-center text-xs font-medium text-gray-700 mb-1">
                                <span class="material-icons text-sm mr-1 text-primary-DEFAULT">home</span>
                                Organization Address Line 1
                            </label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <span class="material-icons text-[#004aad] text-base">home</span>
                                </div>
                                <input 
                                    type="text" 
                                    name="org_address_line1" 
                                    id="org_address_line1" 
                                    class="w-full text-xs border-gray-300 rounded-[1px] pl-9 focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50" 
                                    value="{{ old('org_address_line1') }}" 
                                >
                            </div>
                            <p class="mt-1 text-[10px] text-gray-500">Main address of the organization (building, street)</p>
                        </div>
                        
                        <!-- Organization Address Line 2 -->
                        <div class="md:col-span-2">
                            <label for="org_address_line2" class="flex items-center text-xs font-medium text-gray-700 mb-1">
                                <span class="material-icons text-sm mr-1 text-primary-DEFAULT">apartment</span>
                                Organization Address Line 2
                            </label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <span class="material-icons text-[#004aad] text-base">apartment</span>
                                </div>
                                <input 
                                    type="text" 
                                    name="org_address_line2" 
                                    id="org_address_line2" 
                                    class="w-full text-xs border-gray-300 rounded-[1px] pl-9 focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50" 
                                    value="{{ old('org_address_line2') }}" 
                                >
                            </div>
                            <p class="mt-1 text-[10px] text-gray-500">Additional address information (floor, unit, etc.)</p>
                        </div>
                        
                        <!-- Organization State -->
                        <div>
                            <label for="org_state" class="flex items-center text-xs font-medium text-gray-700 mb-1">
                                <span class="material-icons text-xs mr-1 text-primary-DEFAULT">map</span>
                                Organization State
                            </label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <span class="material-icons text-[#004aad] text-base">location_city</span>
                                </div>
                                <select 
                                    name="org_state" 
                                    id="org_state" 
                                    class="w-full text-xs border-gray-300 rounded-[1px] pl-9 focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50" 
                                    onchange="updateOrgCities()"
                                >
                                    <option value="">Select State</option>
                                    <!-- States will be populated by JavaScript -->
                                </select>
                            </div>
                            <p class="mt-1 text-[10px] text-gray-500">State where the organization is located</p>
                        </div>
                        
                        <!-- Organization City -->
                        <div>
                            <label for="org_city" class="flex items-center text-xs font-medium text-gray-700 mb-1">
                                <span class="material-icons text-xs mr-1 text-primary-DEFAULT">location_city</span>
                                Organization City
                            </label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <span class="material-icons text-[#004aad] text-base">apartment</span>
                                </div>
                                <select 
                                    name="org_city" 
                                    id="org_city" 
                                    class="w-full text-xs border-gray-300 rounded-[1px] pl-9 focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50" 
                                    onchange="lookupOrgPostcodesByCity()"
                                >
                                    <option value="">Select City</option>
                                    <!-- Cities will be populated by JavaScript -->
                                </select>
                            </div>
                            <p class="mt-1 text-[10px] text-gray-500">City where the organization is located</p>
                        </div>
                        
                        <!-- Organization Postcode -->
                        <div>
                            <label for="org_postcode" class="flex items-center text-xs font-medium text-gray-700 mb-1">
                                <span class="material-icons text-xs mr-1 text-primary-DEFAULT">local_post_office</span>
                                Organization Postcode
                            </label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <span class="material-icons text-[#004aad] text-base">markunread_mailbox</span>
                                </div>
                                <select 
                                    name="org_postcode" 
                                    id="org_postcode" 
                                    class="w-full text-xs border-gray-300 rounded-[1px] pl-9 focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50"
                                >
                                    <option value="">Select Postcode</option>
                                    <!-- Postcodes will be populated by JavaScript -->
                                </select>
                            </div>
                            <p class="mt-1 text-[10px] text-gray-500">Postal code for the organization</p>
                        </div>
                        
                        <!-- Organization Country -->
                        <div>
                            <label for="org_country" class="flex items-center text-xs font-medium text-gray-700 mb-1">
                                <span class="material-icons text-xs mr-1 text-primary-DEFAULT">public</span>
                                Organization Country
                            </label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <span class="material-icons text-[#004aad] text-base">flag</span>
                                </div>
                                <select name="org_country" id="org_country" class="w-full text-xs border-gray-300 rounded-[1px] pl-9 focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50" data-old-value="{{ old('org_country', 'Malaysia') }}">
                                    <!-- Dropdown will be filled by JavaScript -->
                                </select>
                            </div>
                            <p class="mt-1 text-[10px] text-gray-500">Default country is Malaysia</p>
                        </div>
                        
                        <!-- Organization Telephone -->
                        <div>
                            <label for="org_telephone" class="flex items-center text-xs font-medium text-gray-700 mb-1">
                                <span class="material-icons text-xs mr-1 text-primary-DEFAULT">phone</span>
                                Organization Telephone
                            </label>
                            <div class="relative">
                                <div class="flex items-center">
                                    <input type="tel" name="org_telephone" id="org_telephone" class="phone-input w-full text-xs border-gray-300 rounded-[1px] focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50" value="{{ old('org_telephone') }}">
                                </div>
                            </div>
                            <p class="mt-1 text-[10px] text-gray-500">Main contact number for the organization</p>
                        </div>
                        
                        <!-- Organization Fax -->
                        <div>
                            <label for="org_fax" class="flex items-center text-xs font-medium text-gray-700 mb-1">
                                <span class="material-icons text-xs mr-1 text-primary-DEFAULT">print</span>
                                Organization Fax
                            </label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <span class="material-icons text-[#004aad] text-base">fax</span>
                                </div>
                                <input 
                                    type="tel" 
                                    name="org_fax" 
                                    id="org_fax" 
                                    class="w-full text-xs border-gray-300 rounded-[1px] pl-9 focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50" 
                                    value="{{ old('org_fax') }}"
                                >
                            </div>
                            <p class="mt-1 text-[10px] text-gray-500">Fax number for the organization (if available)</p>
                        </div>
                        
                        <!-- Organization Email -->
                        <div>
                            <label for="org_email" class="flex items-center text-xs font-medium text-gray-700 mb-1">
                                <span class="material-icons text-xs mr-1 text-primary-DEFAULT">email</span>
                                Organization Email
                            </label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <span class="material-icons text-[#004aad] text-base">alternate_email</span>
                                </div>
                                <input 
                                    type="email" 
                                    name="org_email" 
                                    id="org_email" 
                                    class="w-full text-xs border-gray-300 rounded-[1px] pl-9 focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50" 
                                    value="{{ old('org_email') }}"
                                >
                            </div>
                            <p class="mt-1 text-[10px] text-gray-500">Official email address for the organization</p>
                        </div>
                        
                        <!-- Organization Website -->
                        <div>
                            <label for="org_website" class="flex items-center text-xs font-medium text-gray-700 mb-1">
                                <span class="material-icons text-xs mr-1 text-primary-DEFAULT">language</span>
                                Organization Website
                            </label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <span class="material-icons text-[#004aad] text-base">public</span>
                                </div>
                                <input 
                                    type="url" 
                                    name="org_website" 
                                    id="org_website" 
                                    class="w-full text-xs border-gray-300 rounded-[1px] pl-9 focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50" 
                                    value="{{ old('org_website') }}"
                                    placeholder="https://example.com"
                                >
                            </div>
                            <p class="mt-1 text-[10px] text-gray-500">Official website URL (include https://)</p>
                        </div>
                    </div>
                </div>
                
                <!-- Account Settings -->
                <div class="pb-5">
                    <h2 class="text-sm font-semibold text-gray-700 mb-4">Account Settings</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <!-- Password -->
                        <div>
                            <label for="password" class="flex items-center text-xs font-medium text-gray-700 mb-1">
                                <span class="material-icons text-xs mr-1 text-primary-DEFAULT">lock</span>
                                Password
                            </label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <span class="material-icons text-[#004aad] text-base">vpn_key</span>
                                </div>
                                <input 
                                    type="password" 
                                    name="password" 
                                    id="password" 
                                    class="w-full text-xs border-gray-300 rounded-[1px] pl-9 focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50" 
                                    required
                                >
                            </div>
                            <p class="mt-1 text-[10px] text-gray-500">Must be at least 8 characters with letters and numbers</p>
                        </div>
                        
                        <!-- Confirm Password -->
                        <div>
                            <label for="password_confirmation" class="flex items-center text-xs font-medium text-gray-700 mb-1">
                                <span class="material-icons text-xs mr-1 text-primary-DEFAULT">check_circle</span>
                                Confirm Password
                            </label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <span class="material-icons text-[#004aad] text-base">verified</span>
                                </div>
                                <input 
                                    type="password" 
                                    name="password_confirmation" 
                                    id="password_confirmation" 
                                    class="w-full text-xs border-gray-300 rounded-[1px] pl-9 focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50" 
                                    required
                                >
                            </div>
                            <p class="mt-1 text-[10px] text-gray-500">Re-enter your password to confirm</p>
                        </div>
                    </div>
                </div>
                
                <div class="border-t border-gray-200 pt-4 mt-6 flex justify-end space-x-3">
                    <a 
                        href="{{ route('user.management') }}" 
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
                    
                    console.log('States loaded for', fieldId, ':', states.length);
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
                    
                    console.log('Using fallback states list for', fieldId);
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
                    
                    console.log('Cities loaded for', stateFieldId, ':', cities.length);
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
                    
                    console.log('Postcodes loaded for', cityFieldId, ':', postcodes.length);
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