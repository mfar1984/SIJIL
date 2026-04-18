<x-app-layout>
    <x-slot name="breadcrumb">
        <span>PWA Management</span>
        <span class="mx-2 text-gray-500">/</span>
        <span>Participants</span>
        <span class="mx-2 text-gray-500">/</span>
        <span>Create Participant</span>
    </x-slot>

    <x-slot name="title">Create PWA Participant</x-slot>

    <style>
        .tooltip-wrapper { position: relative; display: inline-flex; }
        .tooltip-content {
            position: absolute; bottom: 100%; left: 50%;
            transform: translateX(-50%) translateY(-4px);
            background-color: #1f2937; color: white;
            padding: 6px 10px; border-radius: 6px;
            font-size: 11px; white-space: nowrap;
            z-index: 1000; pointer-events: none;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }
        .tooltip-content::after {
            content: ''; position: absolute;
            top: 100%; left: 50%; transform: translateX(-50%);
            border: 4px solid transparent; border-top-color: #1f2937;
        }
    </style>

    <div class="bg-white rounded shadow-md border border-gray-300">
        <div class="p-6 border-b border-gray-200">
            <div class="flex items-center">
                <span class="material-icons-outlined mr-2 text-primary-DEFAULT">person_add</span>
                <h1 class="text-xl font-bold text-gray-800">Create PWA Participant</h1>
            </div>
            <p class="text-xs text-gray-500 mt-1 ml-8">Add participants with PWA access credentials</p>
        </div>

        <!-- Registration Method Selection -->
        <div class="p-6 border-b border-gray-200 bg-gray-50">
            <h2 class="text-sm font-semibold text-gray-700 mb-4">Registration Method</h2>
            <div class="grid grid-cols-3 gap-4">
                <!-- Manual Entry -->
                <div class="registration-method-card" data-method="manual">
                    <div class="bg-white p-4 rounded-lg border-2 border-gray-200 hover:border-primary-DEFAULT cursor-pointer transition-all duration-200">
                        <div class="flex items-center mb-3">
                            <span class="material-icons-outlined text-primary-DEFAULT mr-2">edit</span>
                            <h3 class="text-sm font-medium text-gray-800">Manual Entry</h3>
                        </div>
                        <p class="text-xs text-gray-600 mb-3">Create new participant with PWA access</p>
                        <div class="flex items-center text-xs text-gray-500">
                            <span class="material-icons-outlined text-xs mr-1">info</span>
                            Single participant creation
                        </div>
                    </div>
                </div>

                <!-- Auto-assign from Regular Participants -->
                <div class="registration-method-card" data-method="auto-assign">
                    <div class="bg-white p-4 rounded-lg border-2 border-gray-200 hover:border-primary-DEFAULT cursor-pointer transition-all duration-200">
                        <div class="flex items-center mb-3">
                            <span class="material-icons-outlined text-primary-DEFAULT mr-2">sync</span>
                            <h3 class="text-sm font-medium text-gray-800">Auto-assign</h3>
                        </div>
                        <p class="text-xs text-gray-600 mb-3">Convert existing participants to PWA users</p>
                        <div class="flex items-center text-xs text-gray-500">
                            <span class="material-icons-outlined text-xs mr-1">info</span>
                            Bulk conversion available
                        </div>
                    </div>
                </div>

                <!-- Bulk Import -->
                <div class="registration-method-card" data-method="bulk-import">
                    <div class="bg-white p-4 rounded-lg border-2 border-gray-200 hover:border-primary-DEFAULT cursor-pointer transition-all duration-200">
                        <div class="flex items-center mb-3">
                            <span class="material-icons-outlined text-primary-DEFAULT mr-2">upload_file</span>
                            <h3 class="text-sm font-medium text-gray-800">Bulk Import</h3>
                        </div>
                        <p class="text-xs text-gray-600 mb-3">Import multiple participants via CSV/Excel</p>
                        <div class="flex items-center text-xs text-gray-500">
                            <span class="material-icons-outlined text-xs mr-1">info</span>
                            Large-scale registration
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Manual Entry Form -->
        <div id="manual-entry-form" class="registration-form hidden">
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

                <form method="POST" action="{{ route('pwa.participants.store') }}" class="space-y-2">
                    @csrf
                    <input type="hidden" name="registration_method" value="manual">

                    <!-- Basic Information -->
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
                                        Full Name <span class="text-red-500">*</span>
                                        <div class="tooltip-wrapper" x-data="{ show: false }">
                                            <span class="material-icons-outlined text-gray-400 text-sm cursor-help" 
                                                  @mouseenter="show = true" 
                                                  @mouseleave="show = false">
                                                help_outline
                                            </span>
                                            <div x-show="show" x-transition class="tooltip-content">
                                                Enter participant's full name
                                            </div>
                                        </div>
                                    </label>
                                    <div class="flex-1">
                                        <div class="relative">
                                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                                <span class="material-icons-outlined text-[#004aad] text-base">badge</span>
                                            </div>
                                            <input type="text" name="name" id="name" class="w-full text-xs border-gray-300 rounded pl-12 focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50" value="{{ old('name') }}" required>
                                        </div>
                                    </div>
                                </div>

                                <!-- Email -->
                                <div class="flex flex-col md:flex-row md:items-center gap-3">
                                    <label for="email" class="text-xs font-medium text-gray-700 md:w-40 flex items-center gap-1">
                                        Email Address <span class="text-red-500">*</span>
                                        <div class="tooltip-wrapper" x-data="{ show: false }">
                                            <span class="material-icons-outlined text-gray-400 text-sm cursor-help" 
                                                  @mouseenter="show = true" 
                                                  @mouseleave="show = false">
                                                help_outline
                                            </span>
                                            <div x-show="show" x-transition class="tooltip-content">
                                                Used for login and notifications
                                            </div>
                                        </div>
                                    </label>
                                    <div class="flex-1">
                                        <div class="relative">
                                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                                <span class="material-icons-outlined text-[#004aad] text-base">alternate_email</span>
                                            </div>
                                            <input type="email" name="email" id="email" class="w-full text-xs border-gray-300 rounded pl-12 focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50" value="{{ old('email') }}" required>
                                        </div>
                                    </div>
                                </div>

                                <!-- Username -->
                                <div class="flex flex-col md:flex-row md:items-center gap-3">
                                    <label for="username" class="text-xs font-medium text-gray-700 md:w-40 flex items-center gap-1">
                                        Username <span class="text-red-500">*</span>
                                        <div class="tooltip-wrapper" x-data="{ show: false }">
                                            <span class="material-icons-outlined text-gray-400 text-sm cursor-help" 
                                                  @mouseenter="show = true" 
                                                  @mouseleave="show = false">
                                                help_outline
                                            </span>
                                            <div x-show="show" x-transition class="tooltip-content">
                                                Unique username for PWA login
                                            </div>
                                        </div>
                                    </label>
                                    <div class="flex-1">
                                        <div class="relative">
                                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                                <span class="material-icons-outlined text-[#004aad] text-base">person_outline</span>
                                            </div>
                                            <input type="text" name="username" id="username" class="w-full text-xs border-gray-300 rounded pl-12 focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50" value="{{ old('username') }}" required>
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
                                                Contact number for notifications
                                            </div>
                                        </div>
                                    </label>
                                    <div class="flex-1">
                                        <div class="relative">
                                            <input type="tel" name="phone" id="phone" class="w-full text-xs border-gray-300 rounded focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50" value="{{ old('phone') }}">
                                        </div>
                                    </div>
                                </div>

                                <!-- Organization -->
                                <div class="flex flex-col md:flex-row md:items-center gap-3">
                                    <label for="organization" class="text-xs font-medium text-gray-700 md:w-40 flex items-center gap-1">
                                        Organization/Company
                                        <div class="tooltip-wrapper" x-data="{ show: false }">
                                            <span class="material-icons-outlined text-gray-400 text-sm cursor-help" 
                                                  @mouseenter="show = true" 
                                                  @mouseleave="show = false">
                                                help_outline
                                            </span>
                                            <div x-show="show" x-transition class="tooltip-content">
                                                Company or organization
                                            </div>
                                        </div>
                                    </label>
                                    <div class="flex-1">
                                        <div class="relative">
                                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                                <span class="material-icons-outlined text-[#004aad] text-base">apartment</span>
                                            </div>
                                            <input type="text" name="organization" id="organization" class="w-full text-xs border-gray-300 rounded pl-12 focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50" value="{{ old('organization') }}">
                                        </div>
                                    </div>
                                </div>

                                <!-- Identity Card / Passport No. -->
                                <div class="flex flex-col md:flex-row md:items-start gap-3">
                                    <label for="id_type" class="text-xs font-medium text-gray-700 md:w-40 pt-2">
                                        Identity Card / Passport No.
                                    </label>
                                    <div class="flex-1">
                                        <div class="mb-2">
                                            <select name="id_type" id="id_type" class="w-full text-xs border-gray-300 rounded focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50" onchange="toggleIdFields()">
                                                <option value="">-- Select IC / Passport --</option>
                                                <option value="ic" {{ old('id_type') == 'ic' ? 'selected' : '' }}>Identity Card</option>
                                                <option value="passport" {{ old('id_type') == 'passport' ? 'selected' : '' }}>Passport</option>
                                            </select>
                                        </div>
                                        <div id="ic_field" class="relative hidden">
                                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                                <span class="material-icons-outlined text-[#004aad] text-base">assignment_ind</span>
                                            </div>
                                            <input type="text" name="identity_card" id="identity_card" placeholder="000000-00-0000" class="w-full text-xs border-gray-300 rounded pl-12 focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50" value="{{ old('identity_card') }}" maxlength="14" oninput="formatIC(this)">
                                        </div>
                                        <div id="passport_field" class="relative hidden">
                                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                                <span class="material-icons-outlined text-[#004aad] text-base">assignment_ind</span>
                                            </div>
                                            <input type="text" name="passport_no" id="passport_no" placeholder="A00000000" class="w-full text-xs border-gray-300 rounded pl-12 focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50" value="{{ old('passport_no') }}">
                                        </div>
                                    </div>
                                </div>

                                <!-- Date of Birth -->
                                <div class="flex flex-col md:flex-row md:items-center gap-3">
                                    <label for="date_of_birth" class="text-xs font-medium text-gray-700 md:w-40 flex items-center gap-1">
                                        Date of Birth
                                        <div class="tooltip-wrapper" x-data="{ show: false }">
                                            <span class="material-icons-outlined text-gray-400 text-sm cursor-help" 
                                                  @mouseenter="show = true" 
                                                  @mouseleave="show = false">
                                                help_outline
                                            </span>
                                            <div x-show="show" x-transition class="tooltip-content">
                                                For age verification and demographic analysis
                                            </div>
                                        </div>
                                    </label>
                                    <div class="flex-1">
                                        <div class="relative">
                                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                                <span class="material-icons-outlined text-[#004aad] text-base">calendar_today</span>
                                            </div>
                                            <input type="date" name="date_of_birth" id="date_of_birth" class="w-full text-xs border-gray-300 rounded pl-12 focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50" value="{{ old('date_of_birth') }}">
                                        </div>
                                    </div>
                                </div>

                                <!-- Gender -->
                                <div class="flex flex-col md:flex-row md:items-center gap-3">
                                    <label for="gender" class="text-xs font-medium text-gray-700 md:w-40 flex items-center gap-1">
                                        Gender
                                        <div class="tooltip-wrapper" x-data="{ show: false }">
                                            <span class="material-icons-outlined text-gray-400 text-sm cursor-help" 
                                                  @mouseenter="show = true" 
                                                  @mouseleave="show = false">
                                                help_outline
                                            </span>
                                            <div x-show="show" x-transition class="tooltip-content">
                                                Select participant's gender for demographic data
                                            </div>
                                        </div>
                                    </label>
                                    <div class="flex-1">
                                        <div class="relative">
                                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                                <span class="material-icons-outlined text-[#004aad] text-base">person</span>
                                            </div>
                                            <select name="gender" id="gender" class="w-full text-xs border-gray-300 rounded pl-12 focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50">
                                                <option value="">-- Select Gender --</option>
                                                <option value="male" {{ old('gender') == 'male' ? 'selected' : '' }}>Male</option>
                                                <option value="female" {{ old('gender') == 'female' ? 'selected' : '' }}>Female</option>
                                                <option value="other" {{ old('gender') == 'other' ? 'selected' : '' }}>Other</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <!-- Address -->
                                <div class="flex flex-col md:flex-row md:items-start gap-3">
                                    <label class="text-xs font-medium text-gray-700 md:w-40 pt-2">
                                        Address
                                    </label>
                                    <div class="flex-1">
                                        <!-- Address 1 and Address 2 in one row (2 columns) -->
                                        <div class="grid grid-cols-2 gap-2 mb-2">
                                            <!-- Address 1 -->
                                            <div class="relative">
                                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                                    <span class="material-icons-outlined text-[#004aad] text-base">location_on</span>
                                                </div>
                                                <input type="text" name="address1" id="address1" placeholder="Address Line 1" 
                                                    class="w-full text-xs border-gray-300 rounded pl-12 focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50" 
                                                    value="{{ old('address1') }}">
                                            </div>
                                            
                                            <!-- Address 2 -->
                                            <div class="relative">
                                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                                    <span class="material-icons-outlined text-[#004aad] text-base">location_on</span>
                                                </div>
                                                <input type="text" name="address2" id="address2" placeholder="Address Line 2" 
                                                    class="w-full text-xs border-gray-300 rounded pl-12 focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50" 
                                                    value="{{ old('address2') }}">
                                            </div>
                                        </div>
                                        
                                        <!-- State, City, Postcode, Country in one row (4 columns) -->
                                        <div class="grid grid-cols-4 gap-2">
                                            <!-- State -->
                                            <div>
                                                <label for="state" class="block text-xs font-medium text-gray-700 mb-1">State</label>
                                                <select name="state" id="state" 
                                                    class="w-full text-xs border-gray-300 rounded focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50"
                                                    onchange="handleStateChange()">
                                                    <option value="">-- Select State --</option>
                                                </select>
                                            </div>
                                            
                                            <!-- City -->
                                            <div>
                                                <label for="city" class="block text-xs font-medium text-gray-700 mb-1">City</label>
                                                <select name="city" id="city" 
                                                    class="w-full text-xs border-gray-300 rounded focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50" disabled>
                                                    <option value="">-- Select City --</option>
                                                </select>
                                            </div>
                                            
                                            <!-- Postcode -->
                                            <div>
                                                <label for="postcode" class="block text-xs font-medium text-gray-700 mb-1">Postcode</label>
                                                <select name="postcode" id="postcode" 
                                                    class="w-full text-xs border-gray-300 rounded focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50" disabled>
                                                    <option value="">-- Select Postcode --</option>
                                                </select>
                                            </div>
                                            
                                            <!-- Country -->
                                            <div>
                                                <label for="country" class="block text-xs font-medium text-gray-700 mb-1">Country</label>
                                                <select name="country" id="country" 
                                                    class="w-full text-xs border-gray-300 rounded focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50">
                                                    <!-- Dropdown will be filled by JavaScript -->
                                                </select>
                                            </div>
                                        </div>
                                        
                                        <!-- Manual inputs for Others option -->
                                        <div id="manual-address-fields" class="hidden mt-4">
                                            <div class="grid grid-cols-4 gap-2">
                                                <!-- Manual State -->
                                                <div>
                                                    <label for="manual_state" class="block text-xs font-medium text-gray-700 mb-1">State (Manual)</label>
                                                    <div class="relative">
                                                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                                            <span class="material-icons-outlined text-[#004aad] text-base">edit_location</span>
                                                        </div>
                                                        <input type="text" name="manual_state" id="manual_state" 
                                                            class="w-full text-xs border-gray-300 rounded pl-12 focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50"
                                                            placeholder="Enter state manually">
                                                    </div>
                                                </div>
                                                
                                                <!-- Manual City -->
                                                <div>
                                                    <label for="manual_city" class="block text-xs font-medium text-gray-700 mb-1">City (Manual)</label>
                                                    <div class="relative">
                                                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                                            <span class="material-icons-outlined text-[#004aad] text-base">edit_location</span>
                                                        </div>
                                                        <input type="text" name="manual_city" id="manual_city" 
                                                            class="w-full text-xs border-gray-300 rounded pl-12 focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50"
                                                            placeholder="Enter city manually">
                                                    </div>
                                                </div>
                                                
                                                <!-- Manual Postcode -->
                                                <div>
                                                    <label for="manual_postcode" class="block text-xs font-medium text-gray-700 mb-1">Postcode (Manual)</label>
                                                    <div class="relative">
                                                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                                            <span class="material-icons-outlined text-[#004aad] text-base">edit_location</span>
                                                        </div>
                                                        <input type="text" name="manual_postcode" id="manual_postcode" 
                                                            class="w-full text-xs border-gray-300 rounded pl-12 focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50"
                                                            placeholder="Enter postcode manually">
                                                    </div>
                                                </div>
                                                
                                                <!-- Empty column to match grid -->
                                                <div>
                                                    <!-- This is empty to match the Country column position -->
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <!-- Hidden field to store the combined address -->
                                        <input type="hidden" name="address" id="address">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Additional Information -->
                    <div class="bg-white border border-gray-200 rounded-md shadow-sm">
                        <div class="bg-gray-50 border-b border-gray-200 px-4 py-3">
                            <h2 class="text-sm font-semibold text-gray-700 flex items-center">
                                <span class="material-icons-outlined text-primary-DEFAULT mr-2">description</span>
                                Additional Information
                            </h2>
                        </div>
                        
                        <div class="p-4">
                            <div class="space-y-3">
                                <!-- Job Title -->
                                <div class="flex flex-col md:flex-row md:items-center gap-3">
                                    <label for="job_title" class="text-xs font-medium text-gray-700 md:w-40 flex items-center gap-1">
                                        Job Title
                                        <div class="tooltip-wrapper" x-data="{ show: false }">
                                            <span class="material-icons-outlined text-gray-400 text-sm cursor-help" 
                                                  @mouseenter="show = true" 
                                                  @mouseleave="show = false">
                                                help_outline
                                            </span>
                                            <div x-show="show" x-transition class="tooltip-content">
                                                Current position or role
                                            </div>
                                        </div>
                                    </label>
                                    <div class="flex-1">
                                        <div class="relative">
                                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                                <span class="material-icons-outlined text-[#004aad] text-base">badge</span>
                                            </div>
                                            <input type="text" name="job_title" id="job_title" class="w-full text-xs border-gray-300 rounded pl-12 focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50" value="{{ old('job_title') }}">
                                        </div>
                                    </div>
                                </div>

                                <!-- Notes -->
                                <div class="flex flex-col md:flex-row md:items-start gap-3">
                                    <label for="notes" class="text-xs font-medium text-gray-700 md:w-40 pt-2 flex items-center gap-1">
                                        Notes
                                        <div class="tooltip-wrapper" x-data="{ show: false }">
                                            <span class="material-icons-outlined text-gray-400 text-sm cursor-help" 
                                                  @mouseenter="show = true" 
                                                  @mouseleave="show = false">
                                                help_outline
                                            </span>
                                            <div x-show="show" x-transition class="tooltip-content">
                                                Internal notes (not visible to participant)
                                            </div>
                                        </div>
                                    </label>
                                    <div class="flex-1">
                                        <textarea id="notes" name="notes" rows="3" class="w-full text-xs border-gray-300 rounded focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50" placeholder="Any additional information about this participant">{{ old('notes') }}</textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Event Assignment -->
                    <div class="bg-white border border-gray-200 rounded-md shadow-sm">
                        <div class="bg-gray-50 border-b border-gray-200 px-4 py-3">
                            <h2 class="text-sm font-semibold text-gray-700 flex items-center">
                                <span class="material-icons-outlined text-primary-DEFAULT mr-2">event</span>
                                Event Assignment
                            </h2>
                        </div>
                        
                        <div class="p-4">
                            <div class="space-y-3">
                                <div>
                                    <label class="text-xs font-medium text-gray-700 mb-2 flex items-center gap-1">
                                        Assign to Events <span class="text-red-500">*</span>
                                        <div class="tooltip-wrapper" x-data="{ show: false }">
                                            <span class="material-icons-outlined text-gray-400 text-sm cursor-help" 
                                                  @mouseenter="show = true" 
                                                  @mouseleave="show = false">
                                                help_outline
                                            </span>
                                            <div x-show="show" x-transition class="tooltip-content">
                                                Select events this participant can access
                                            </div>
                                        </div>
                                    </label>
                                    <div class="grid grid-cols-2 gap-4 max-h-40 overflow-y-auto border border-gray-200 rounded p-3">
                                        @foreach($events as $event)
                                            <div class="flex items-center">
                                                <input type="checkbox" name="event_ids[]" id="event_{{ $event->id }}" value="{{ $event->id }}" class="rounded border-gray-300 text-primary-DEFAULT focus:ring-primary-light">
                                                <label for="event_{{ $event->id }}" class="ml-2 text-xs text-gray-700 cursor-pointer">
                                                    {{ $event->name }}
                                                    <span class="text-gray-500 block text-[10px]">{{ $event->start_date ? $event->start_date->format('M d, Y') : 'No date' }}</span>
                                                </label>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Account Settings -->
                    <div class="bg-white border border-gray-200 rounded-md shadow-sm">
                        <div class="bg-gray-50 border-b border-gray-200 px-4 py-3">
                            <h2 class="text-sm font-semibold text-gray-700 flex items-center">
                                <span class="material-icons-outlined text-primary-DEFAULT mr-2">settings</span>
                                Account Settings
                            </h2>
                        </div>
                        
                        <div class="p-4">
                            <div class="space-y-3">
                                <!-- Auto-generate Password -->
                                <div class="flex items-center">
                                    <input type="checkbox" name="auto_generate_password" id="auto_generate_password" value="1" class="rounded border-gray-300 text-primary-DEFAULT focus:ring-primary-light" checked>
                                    <label for="auto_generate_password" class="ml-2 text-xs text-gray-700 cursor-pointer">
                                        Auto-generate secure password
                                    </label>
                                </div>

                                <!-- Send Welcome Email -->
                                <div class="flex items-center">
                                    <input type="checkbox" name="send_welcome_email" id="send_welcome_email" value="1" class="rounded border-gray-300 text-primary-DEFAULT focus:ring-primary-light" checked>
                                    <label for="send_welcome_email" class="ml-2 text-xs text-gray-700 cursor-pointer">
                                        Send welcome email with login credentials
                                    </label>
                                </div>

                                <!-- Account Status -->
                                <div class="flex items-center">
                                    <input type="checkbox" name="is_active" id="is_active" value="1" class="rounded border-gray-300 text-primary-DEFAULT focus:ring-primary-light" checked>
                                    <label for="is_active" class="ml-2 text-xs text-gray-700 cursor-pointer">
                                        Account is active (can login immediately)
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Form Actions -->
                    <div class="flex justify-end space-x-3 pt-4">
                        <button type="button" class="change-method-btn px-3 h-[36px] bg-gradient-to-r from-gray-600 to-gray-500 hover:from-gray-700 hover:to-gray-600 text-white rounded shadow-sm text-xs font-medium transition-colors duration-200 ease-in-out flex items-center">
                            <span class="material-icons-outlined text-xs mr-1">swap_horiz</span>
                            Change Method
                        </button>
                        <a href="{{ route('pwa.participants') }}" class="px-3 h-[36px] bg-gradient-to-r from-red-600 to-red-500 hover:from-red-700 hover:to-red-600 text-white rounded shadow-sm text-xs font-medium transition-colors duration-200 ease-in-out flex items-center">
                            <span class="material-icons-outlined text-xs mr-1">cancel</span>
                            Cancel
                        </a>
                        <button type="submit" class="px-3 h-[36px] bg-gradient-to-r from-green-600 to-green-500 hover:from-green-700 hover:to-green-600 text-white rounded shadow-sm text-xs font-medium transition-colors duration-200 ease-in-out flex items-center">
                            <span class="material-icons-outlined text-xs mr-1">save</span>
                            Create PWA Participant
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Auto-assign Form -->
        <div id="auto-assign-form" class="registration-form hidden">
            <div class="p-6">
                <div class="mb-6">
                    <h2 class="text-sm font-semibold text-gray-700 mb-4">Select Regular Participants</h2>
                    <p class="text-xs text-gray-600 mb-4">Choose existing participants to convert to PWA users. They will receive login credentials automatically.</p>
                    
                    <!-- Search and Filter -->
                    <div class="grid grid-cols-3 gap-4 mb-4">
                        <div>
                            <label class="text-xs font-medium text-gray-700 mb-1">Search Participants</label>
                            <input type="text" id="participant-search" placeholder="Search by name, email, organization..." class="w-full text-xs border-gray-300 rounded focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50">
                        </div>
                        <div>
                            <label class="text-xs font-medium text-gray-700 mb-1">Filter by Event</label>
                            <select id="event-filter" class="w-full text-xs border-gray-300 rounded focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50">
                                <option value="">All Events</option>
                                @foreach($events as $event)
                                    <option value="{{ $event->id }}">{{ $event->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="text-xs font-medium text-gray-700 mb-1">Bulk Actions</label>
                            <div class="flex space-x-2">
                                <button type="button" id="select-all-btn" class="px-2 py-1 bg-blue-100 text-blue-700 rounded text-xs hover:bg-blue-200">Select All</button>
                                <button type="button" id="deselect-all-btn" class="px-2 py-1 bg-gray-100 text-gray-700 rounded text-xs hover:bg-gray-200">Deselect All</button>
                            </div>
                        </div>
                    </div>

                    <!-- Participants List -->
                    <div class="border border-gray-200 rounded-lg max-h-96 overflow-y-auto">
                        <div id="participants-list" class="p-4">
                            <!-- Participants will be loaded here via AJAX -->
                            <div class="text-center py-8">
                                <span class="material-icons-outlined text-gray-400 text-xs mb-2">search</span>
                                <p class="text-xs text-gray-500">Search for participants to convert to PWA users</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Account Settings for Auto-assign -->
                <div class="border-t border-gray-200 pt-4">
                    <h3 class="text-sm font-semibold text-gray-700 mb-4">Account Settings</h3>
                    <div class="grid grid-cols-2 gap-4">
                        <div class="space-y-3">
                            <div class="flex items-center">
                                <input type="checkbox" id="auto_assign_generate_password" class="rounded border-gray-300 text-primary-DEFAULT focus:ring-primary-light" checked>
                                <label class="ml-2 text-xs text-gray-700">Auto-generate passwords</label>
                            </div>
                            <div class="flex items-center">
                                <input type="checkbox" id="auto_assign_send_email" class="rounded border-gray-300 text-primary-DEFAULT focus:ring-primary-light" checked>
                                <label class="ml-2 text-xs text-gray-700">Send welcome emails</label>
                            </div>
                        </div>
                        <div class="space-y-3">
                            <div class="flex items-center">
                                <input type="checkbox" id="auto_assign_active" class="rounded border-gray-300 text-primary-DEFAULT focus:ring-primary-light" checked>
                                <label class="ml-2 text-xs text-gray-700">Set accounts as active</label>
                            </div>
                            <div class="flex items-center">
                                <input type="checkbox" id="auto_assign_force_password_change" class="rounded border-gray-300 text-primary-DEFAULT focus:ring-primary-light">
                                <label class="ml-2 text-xs text-gray-700">Force password change on first login</label>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Form Actions -->
                <div class="flex justify-end space-x-3 pt-4">
                    <button type="button" class="change-method-btn px-3 h-[36px] bg-gradient-to-r from-gray-600 to-gray-500 hover:from-gray-700 hover:to-gray-600 text-white rounded shadow-sm text-xs font-medium transition-colors duration-200 ease-in-out flex items-center">
                        <span class="material-icons-outlined text-xs mr-1">swap_horiz</span>
                        Change Method
                    </button>
                    <a href="{{ route('pwa.participants') }}" class="px-3 h-[36px] bg-gradient-to-r from-red-600 to-red-500 hover:from-red-700 hover:to-red-600 text-white rounded shadow-sm text-xs font-medium transition-colors duration-200 ease-in-out flex items-center">
                        <span class="material-icons-outlined text-xs mr-1">cancel</span>
                        Cancel
                    </a>
                    <button type="button" id="convert-participants-btn" class="px-3 h-[36px] bg-gradient-to-r from-green-600 to-green-500 hover:from-green-700 hover:to-green-600 text-white rounded shadow-sm text-xs font-medium transition-colors duration-200 ease-in-out flex items-center">
                        <span class="material-icons-outlined text-xs mr-1">sync</span>
                        Convert Selected (<span id="selected-count">0</span>)
                    </button>
                </div>
            </div>
        </div>

        <!-- Bulk Import Form -->
        <div id="bulk-import-form" class="registration-form hidden">
            <div class="p-6">
                <div class="mb-6">
                    <h2 class="text-sm font-semibold text-gray-700 mb-4">Bulk Import Participants</h2>
                    <p class="text-xs text-gray-600 mb-4">Upload a CSV or Excel file to create multiple PWA participants at once.</p>
                    
                    <!-- File Upload -->
                    <div class="border-2 border-dashed border-gray-300 rounded-lg p-6 text-center">
                        <span class="material-icons-outlined text-gray-400 text-4xl mb-2">cloud_upload</span>
                        <p class="text-sm text-gray-600 mb-2">Drag and drop your file here, or click to browse</p>
                        <p class="text-xs text-gray-500 mb-4">Supported formats: CSV, XLSX (Max 5MB)</p>
                        <input type="file" id="bulk-import-file" accept=".csv,.xlsx,.xls" class="hidden">
                        <button type="button" id="browse-file-btn" class="px-4 py-2 bg-blue-100 text-blue-700 rounded text-sm hover:bg-blue-200">
                            Browse Files
                        </button>
                    </div>

                    <!-- Template Download -->
                    <div class="mt-4 p-4 bg-blue-50 rounded-lg">
                        <div class="flex items-center justify-between">
                            <div>
                                <h4 class="text-sm font-medium text-blue-800">Download Template</h4>
                                <p class="text-xs text-blue-600">Get the correct format for your import file</p>
                            </div>
                            <button type="button" id="download-template-btn" class="px-3 py-1 bg-blue-600 text-white rounded text-xs hover:bg-blue-700">
                                Download CSV Template
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Import Settings -->
                <div class="border-t border-gray-200 pt-4">
                    <h3 class="text-sm font-semibold text-gray-700 mb-4">Import Settings</h3>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="text-xs font-medium text-gray-700 mb-1">Default Event Assignment</label>
                            <select id="bulk-import-event" class="w-full text-xs border-gray-300 rounded focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50">
                                <option value="">Select default event</option>
                                @foreach($events as $event)
                                    <option value="{{ $event->id }}">{{ $event->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="text-xs font-medium text-gray-700 mb-1">Account Status</label>
                            <select id="bulk-import-status" class="w-full text-xs border-gray-300 rounded focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50">
                                <option value="1">Active</option>
                                <option value="0">Inactive</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Form Actions -->
                <div class="flex justify-end space-x-3 pt-4">
                    <button type="button" class="change-method-btn px-3 h-[36px] bg-gradient-to-r from-gray-600 to-gray-500 hover:from-gray-700 hover:to-gray-600 text-white rounded shadow-sm text-xs font-medium transition-colors duration-200 ease-in-out flex items-center">
                        <span class="material-icons-outlined text-xs mr-1">swap_horiz</span>
                        Change Method
                    </button>
                    <a href="{{ route('pwa.participants') }}" class="px-3 h-[36px] bg-gradient-to-r from-red-600 to-red-500 hover:from-red-700 hover:to-red-600 text-white rounded shadow-sm text-xs font-medium transition-colors duration-200 ease-in-out flex items-center">
                        <span class="material-icons-outlined text-xs mr-1">cancel</span>
                        Cancel
                    </a>
                    <button type="button" id="start-import-btn" class="px-3 h-[36px] bg-gradient-to-r from-green-600 to-green-500 hover:from-green-700 hover:to-green-600 text-white rounded shadow-sm text-xs font-medium transition-colors duration-200 ease-in-out flex items-center" disabled>
                        <span class="material-icons-outlined text-xs mr-1">upload_file</span>
                        Start Import
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Registration method selection
        document.addEventListener('DOMContentLoaded', function() {
            // DOM loaded, setting up method selection
            
            const methodCards = document.querySelectorAll('.registration-method-card');
            const forms = document.querySelectorAll('.registration-form');
            const changeMethodBtns = document.querySelectorAll('.change-method-btn');

            // Found method cards and forms

            // Method selection
            methodCards.forEach(card => {
                card.addEventListener('click', function() {
                    // Method card clicked
                    const method = this.dataset.method;
                    
                    // Update card styles
                    methodCards.forEach(c => c.querySelector('div').classList.remove('border-primary-DEFAULT', 'bg-blue-50'));
                    this.querySelector('div').classList.add('border-primary-DEFAULT', 'bg-blue-50');
                    
                    // Show/hide forms
                    forms.forEach(form => {
                        form.classList.add('hidden');
                        // Hiding form
                    });
                    
                    // Map method names to form IDs
                    const formIdMap = {
                        'manual': 'manual-entry-form',
                        'auto-assign': 'auto-assign-form',
                        'bulk-import': 'bulk-import-form'
                    };
                    
                    const targetFormId = formIdMap[method];
                    const targetForm = document.getElementById(targetFormId);
                    
                    if (targetForm) {
                        targetForm.classList.remove('hidden');
                        // Showing form
                    } else {
                        console.error('Form not found:', targetFormId);
                    }
                });
            });

            // Change method button
            changeMethodBtns.forEach(btn => {
                btn.addEventListener('click', function() {
                    forms.forEach(form => form.classList.add('hidden'));
                    methodCards.forEach(c => c.querySelector('div').classList.remove('border-primary-DEFAULT', 'bg-blue-50'));
                });
            });

            // Set default selection to Manual Entry
            // Setting default selection to Manual Entry
            const manualCard = document.querySelector('[data-method="manual"]');
            const manualForm = document.getElementById('manual-entry-form');
            
            if (manualCard && manualForm) {
                manualCard.querySelector('div').classList.add('border-primary-DEFAULT', 'bg-blue-50');
                manualForm.classList.remove('hidden');
                // Default selection set
            } else {
                console.error('Manual entry card or form not found');
            }

            // Auto-load participants when auto-assign form is shown
            const autoAssignCard = document.querySelector('[data-method="auto-assign"]');
            if (autoAssignCard) {
                autoAssignCard.addEventListener('click', function() {
                    // Load participants after a short delay to ensure form is shown
                    setTimeout(() => {
                        searchParticipants();
                    }, 100);
                });
            }

            // Auto-assign functionality
            const participantSearch = document.getElementById('participant-search');
            const eventFilter = document.getElementById('event-filter');
            const selectAllBtn = document.getElementById('select-all-btn');
            const deselectAllBtn = document.getElementById('deselect-all-btn');
            const selectedCountSpan = document.getElementById('selected-count');

            // Search participants
            participantSearch.addEventListener('input', function() {
                searchParticipants();
            });

            eventFilter.addEventListener('change', function() {
                searchParticipants();
            });

            function searchParticipants() {
                const searchTerm = participantSearch.value;
                const eventId = eventFilter.value;
                
                // Searching participants
                
                // Show loading
                document.getElementById('participants-list').innerHTML = `
                    <div class="text-center py-8">
                        <span class="material-icons-outlined text-gray-400 text-4xl mb-2 animate-spin">refresh</span>
                        <p class="text-sm text-gray-500">Searching participants...</p>
                    </div>
                `;

                // AJAX call to search participants
                fetch(`/api/participants/search?search=${searchTerm}&event_id=${eventId}`, {
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json',
                        'Content-Type': 'application/json'
                    }
                })
                    .then(response => {
                        // Response status
                        return response.json();
                    })
                    .then(data => {
                        // Response data
                        if (data.success) {
                            displayParticipants(data.participants);
                        } else {
                            throw new Error(data.message || 'Search failed');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        document.getElementById('participants-list').innerHTML = `
                            <div class="text-center py-8">
                                <span class="material-icons-outlined text-red-400 text-4xl mb-2">error</span>
                                <p class="text-sm text-red-500">Error loading participants</p>
                                <p class="text-xs text-gray-400 mt-1">${error.message}</p>
                            </div>
                        `;
                    });
            }

            function displayParticipants(participants) {
                if (participants.length === 0) {
                    document.getElementById('participants-list').innerHTML = `
                        <div class="text-center py-8">
                            <span class="material-icons-outlined text-gray-400 text-4xl mb-2">search_off</span>
                            <p class="text-sm text-gray-500">No participants found</p>
                        </div>
                    `;
                    return;
                }

                const html = participants.map(participant => `
                    <div class="flex items-center justify-between p-3 border-b border-gray-100 hover:bg-gray-50">
                        <div class="flex items-center">
                            <input type="checkbox" name="selected_participants[]" value="${participant.id}" class="participant-checkbox rounded border-gray-300 text-primary-DEFAULT focus:ring-primary-light">
                            <div class="ml-3">
                                <div class="text-sm font-medium text-gray-800">${participant.name}</div>
                                <div class="text-xs text-gray-500">${participant.email}</div>
                                <div class="text-xs text-gray-400">${participant.organization || 'No organization'}</div>
                            </div>
                        </div>
                        <div class="text-xs text-gray-500">
                            ${participant.event ? participant.event.name : 'No event'}
                        </div>
                    </div>
                `).join('');

                document.getElementById('participants-list').innerHTML = html;
                updateSelectedCount();
            }

            // Select all / deselect all
            selectAllBtn.addEventListener('click', function() {
                document.querySelectorAll('.participant-checkbox').forEach(checkbox => {
                    checkbox.checked = true;
                });
                updateSelectedCount();
            });

            deselectAllBtn.addEventListener('click', function() {
                document.querySelectorAll('.participant-checkbox').forEach(checkbox => {
                    checkbox.checked = false;
                });
                updateSelectedCount();
            });

            // Update selected count
            document.addEventListener('change', function(e) {
                if (e.target.classList.contains('participant-checkbox')) {
                    updateSelectedCount();
                }
            });

            function updateSelectedCount() {
                const selected = document.querySelectorAll('.participant-checkbox:checked').length;
                selectedCountSpan.textContent = selected;
            }

            // Convert participants
            document.getElementById('convert-participants-btn').addEventListener('click', function() {
                const selectedParticipants = Array.from(document.querySelectorAll('.participant-checkbox:checked')).map(cb => cb.value);
                
                if (selectedParticipants.length === 0) {
                    alert('Please select at least one participant to convert.');
                    return;
                }

                // Show confirmation
                if (!confirm(`Convert ${selectedParticipants.length} participants to PWA users?`)) {
                    return;
                }

                // Submit conversion
                const formData = new FormData();
                formData.append('_token', '{{ csrf_token() }}');
                formData.append('registration_method', 'auto_assign');
                formData.append('participant_ids', JSON.stringify(selectedParticipants));
                formData.append('auto_generate_password', document.getElementById('auto_assign_generate_password').checked);
                formData.append('send_welcome_email', document.getElementById('auto_assign_send_email').checked);
                formData.append('is_active', document.getElementById('auto_assign_active').checked);
                formData.append('force_password_change', document.getElementById('auto_assign_force_password_change').checked);

                fetch('{{ route("pwa.participants.store") }}', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert(`Successfully converted ${data.converted_count} participants to PWA users.`);
                        window.location.href = '{{ route("pwa.participants") }}';
                    } else {
                        alert('Error: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred while converting participants.');
                });
            });

            // Bulk import functionality
            const fileInput = document.getElementById('bulk-import-file');
            const browseBtn = document.getElementById('browse-file-btn');
            const startImportBtn = document.getElementById('start-import-btn');

            browseBtn.addEventListener('click', function() {
                fileInput.click();
            });

            fileInput.addEventListener('change', function() {
                if (this.files.length > 0) {
                    startImportBtn.disabled = false;
                    startImportBtn.textContent = `Start Import (${this.files[0].name})`;
                } else {
                    startImportBtn.disabled = true;
                    startImportBtn.textContent = 'Start Import';
                }
            });

            // Download template
            document.getElementById('download-template-btn').addEventListener('click', function() {
                const csvContent = `Name,Email,Phone,Organization,Address,Event ID,Identity Card,Passport No,Gender,Date of Birth,Job Title,Notes,Address1,Address2,State,City,Postcode,Country
John Doe,john@example.com,60123456789,Company A,123 Main St Kuala Lumpur,1,851215-13-1234,,male,1985-12-15,Manager,Test participant,123 Main St,Suite 100,Kuala Lumpur,Kuala Lumpur,50000,Malaysia
Jane Smith,jane@example.com,60123456788,Company B,456 Oak Ave Petaling Jaya,1,901010-14-5678,,female,1990-10-10,Director,Another test,456 Oak Ave,Apt 200,Petaling Jaya,Petaling Jaya,46000,Malaysia
Ahmad Faizal,ahmad@example.com,60123456787,Company C,789 Pine Rd Shah Alam,2,881122-15-9012,,male,1988-11-22,Engineer,Third participant,789 Pine Rd,Block C,Shah Alam,Shah Alam,40000,Malaysia`;
                const blob = new Blob([csvContent], { type: 'text/csv' });
                const url = window.URL.createObjectURL(blob);
                const a = document.createElement('a');
                a.href = url;
                a.download = 'pwa_participants_template.csv';
                a.click();
                window.URL.revokeObjectURL(url);
            });

            // Start import
            startImportBtn.addEventListener('click', function() {
                if (!fileInput.files.length) {
                    alert('Please select a file to import.');
                    return;
                }

                const formData = new FormData();
                formData.append('_token', '{{ csrf_token() }}');
                formData.append('registration_method', 'bulk_import');
                formData.append('file', fileInput.files[0]);
                formData.append('default_event_id', document.getElementById('bulk-import-event').value);
                formData.append('is_active', document.getElementById('bulk-import-status').value);

                // Show loading
                startImportBtn.disabled = true;
                startImportBtn.innerHTML = '<span class="material-icons-outlined text-xs mr-1 animate-spin">refresh</span>Importing...';

                fetch('{{ route("pwa.participants.store") }}', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert(`Successfully imported ${data.imported_count} participants.`);
                        window.location.href = '{{ route("pwa.participants") }}';
                    } else {
                        alert('Error: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred while importing participants.');
                })
                .finally(() => {
                    startImportBtn.disabled = false;
                    startImportBtn.innerHTML = '<span class="material-icons-outlined text-xs mr-1">upload_file</span>Start Import';
                });
            });
        });

        // Address and IC formatting functions
        function toggleIdFields() {
            const idType = document.getElementById('id_type').value;
            const icField = document.getElementById('ic_field');
            const passportField = document.getElementById('passport_field');
                    
            if (idType === 'ic') {
                icField.classList.remove('hidden');
                passportField.classList.add('hidden');
            } else if (idType === 'passport') {
                icField.classList.add('hidden');
                passportField.classList.remove('hidden');
            } else {
                icField.classList.add('hidden');
                passportField.classList.add('hidden');
            }
        }
        
        function toggleManualAddressFields() {
            const stateValue = document.getElementById('state').value;
            const manualFields = document.getElementById('manual-address-fields');
            const citySelect = document.getElementById('city');
            const postcodeSelect = document.getElementById('postcode');
            
            if (stateValue === 'others') {
                manualFields.classList.remove('hidden');
                citySelect.setAttribute('disabled', true);
                postcodeSelect.setAttribute('disabled', true);
            } else {
                manualFields.classList.add('hidden');
                
                // Reset manual fields
                document.getElementById('manual_state').value = '';
                document.getElementById('manual_city').value = '';
                document.getElementById('manual_postcode').value = '';
            }
        }
        
        function handleStateChange() {
            const stateValue = document.getElementById('state').value;
            
            toggleManualAddressFields();
            
            if (stateValue !== 'others') {
                loadCities();
            }
        }
        
        function formatIC(input) {
            let value = input.value.replace(/\D/g, ''); // Remove non-digits
            let formattedValue = '';

            if (value.length > 6) {
                formattedValue = value.substring(0, 6) + '-';
                if (value.length > 8) {
                    formattedValue += value.substring(6, 8) + '-';
                    formattedValue += value.substring(8);
                } else {
                    formattedValue += value.substring(6);
                }
            } else {
                formattedValue = value;
            }

            input.value = formattedValue;
        }

        // When the form is submitted, combine address fields
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.querySelector('form');
            if (form) {
                form.addEventListener('submit', function() {
                    combineAddress();
                });
            }
            
            toggleIdFields();
            
            // Setup state dropdown
            const stateSelect = document.getElementById('state');
            if (stateSelect) {
                // Load states immediately
                try {
                    loadStates();
                } catch (e) {
                    console.error('Error loading states:', e);
                }
            }
        });
        
        function combineAddress() {
            const stateValue = document.getElementById('state').value;
            let state, city, postcode;
            
            if (stateValue === 'others') {
                state = document.getElementById('manual_state').value;
                city = document.getElementById('manual_city').value;
                postcode = document.getElementById('manual_postcode').value;
            } else {
                state = stateValue;
                city = document.getElementById('city').value;
                postcode = document.getElementById('postcode').value;
            }
            
            const address1 = document.getElementById('address1').value.trim();
            const address2 = document.getElementById('address2').value.trim();
            const country = document.getElementById('country').value.trim();
            
            // Build the combined address
            let combinedAddress = '';
            
            if (address1) combinedAddress += address1 + '\n';
            if (address2) combinedAddress += address2 + '\n';
            if (city) combinedAddress += city + '\n';
            if (state) combinedAddress += state + '\n';
            if (postcode) combinedAddress += postcode + '\n';
            if (country) combinedAddress += country;
            
            // Set the value of the hidden address field
            document.getElementById('address').value = combinedAddress.trim();
        }
    </script>
</x-app-layout> 