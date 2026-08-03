<x-app-layout>
    <x-slot name="breadcrumb">
        <span>Profile</span>
    </x-slot>
    <x-slot name="title">Edit Profile</x-slot>
    
    <style>
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
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
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
    
    <div class="bg-white rounded shadow-md border border-gray-300 w-full mx-auto">
        <div class="p-6 border-b border-gray-200">
            <div class="flex items-center">
                <span class="material-icons-outlined mr-2 text-primary-DEFAULT">person</span>
                <h1 class="text-xl font-bold text-gray-800">Edit Profile</h1>
            </div>
            <p class="text-xs text-gray-500 mt-1 ml-8">Update your personal and organization information</p>
        </div>
        <div class="p-6">
            {{--
                The password-expiry middleware sends people here with a warning.
                Without somewhere to render it they arrived on this page with no
                explanation of why they had been moved.
            --}}
            @if(session('warning'))
                <div class="mb-4 p-3 bg-amber-50 border border-amber-200 text-amber-800 rounded text-xs">
                    {{ session('warning') }}
                </div>
            @endif

            @if(session('status') === 'password-updated')
                <div class="mb-4 p-3 bg-green-50 border border-green-200 text-green-700 rounded text-xs">
                    Password updated. Every other device has been signed out.
                </div>
            @elseif(session('status') === 'profile-updated')
                <div class="mb-4 p-3 bg-green-50 border border-green-200 text-green-700 rounded text-xs">
                    Profile updated.
                </div>
            @elseif(session('status') === 'profile-image-removed')
                <div class="mb-4 p-3 bg-green-50 border border-green-200 text-green-700 rounded text-xs">
                    Profile picture removed.
                </div>
            @endif

            @if ($errors->any())
                <div class="mb-4 p-4 bg-red-50 border border-red-200 text-red-700 rounded">
                    <ul class="list-disc pl-5">
                        @foreach ($errors->all() as $error)
                            <li class="text-xs">{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
            <form method="POST" action="{{ route('profile.update') }}" class="space-y-2" enctype="multipart/form-data">
                @csrf
                @method('patch')
                
                <!-- Profile Image Upload -->
                <div class="bg-white border border-gray-200 rounded-md shadow-sm">
                    <div class="bg-gray-50 border-b border-gray-200 px-4 py-3">
                        <h2 class="text-sm font-semibold text-gray-700 flex items-center">
                            <span class="material-icons-outlined text-primary-DEFAULT mr-2">photo_camera</span>
                            Profile Image
                        </h2>
                    </div>
                    
                    {{-- Click the picture to change it, the same way the branding
                         images work. The filename box and Browse button that used to
                         sit here are gone; selecting a file now swaps the preview,
                         which is the feedback they were standing in for. --}}
                    <div class="p-4">
                        <div class="flex items-center gap-4">
                            <label for="profile_image"
                                   class="group relative w-20 h-20 rounded-full border border-gray-300 bg-gray-100 overflow-hidden cursor-pointer shrink-0 hover:border-primary-DEFAULT transition-colors"
                                   title="Choose a picture">
                                <img id="profile-image-preview"
                                     src="{{ $user->profile_image ? asset('storage/' . $user->profile_image) : '' }}"
                                     alt="Profile picture"
                                     class="w-full h-full object-cover {{ $user->profile_image ? '' : 'hidden' }}">

                                <span id="profile-image-placeholder"
                                      class="absolute inset-0 flex items-center justify-center {{ $user->profile_image ? 'hidden' : '' }}">
                                    <span class="material-icons-outlined text-gray-300 text-4xl group-hover:text-primary-DEFAULT">person</span>
                                </span>

                                <span class="absolute inset-x-0 bottom-0 bg-gray-900/70 text-white text-[9px] text-center py-0.5 opacity-0 group-hover:opacity-100 transition-opacity">
                                    Change
                                </span>
                            </label>

                            <div class="flex-1 min-w-0">
                                <p class="text-xs text-gray-700">Profile picture</p>
                                <p class="text-[11px] text-gray-500 mt-0.5">
                                    Click the picture to choose a new one. JPEG, PNG or WebP, up to 2MB.
                                    It is saved when you submit the form below.
                                </p>
                                <p id="profile-image-chosen" class="text-[11px] text-primary-DEFAULT mt-1 hidden"></p>

                                @error('profile_image')
                                    <p class="text-[11px] text-red-600 mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            @if($user->profile_image)
                                {{-- Submits the removal form declared after the main form.
                                     A form cannot be nested inside another form, so the
                                     button reaches it by id instead. Removal is separate
                                     because it should not wait for the rest of the profile
                                     to validate. --}}
                                <button type="submit" form="removeProfileImage"
                                        class="h-9 px-3 border border-gray-300 rounded text-xs font-medium text-gray-700 hover:bg-gray-50 inline-flex items-center shrink-0">
                                    <span class="material-icons-outlined text-sm mr-1">delete</span>
                                    Remove
                                </button>
                            @endif

                            <input type="file"
                                   name="profile_image"
                                   id="profile_image"
                                   accept="image/png,image/jpeg,image/webp"
                                   class="hidden"
                                   onchange="
                                       if (this.files[0]) {
                                           const img = document.getElementById('profile-image-preview');
                                           const placeholder = document.getElementById('profile-image-placeholder');
                                           const chosen = document.getElementById('profile-image-chosen');
                                           img.src = URL.createObjectURL(this.files[0]);
                                           img.classList.remove('hidden');
                                           if (placeholder) placeholder.classList.add('hidden');
                                           if (chosen) {
                                               chosen.textContent = this.files[0].name + ' selected. Submit to save.';
                                               chosen.classList.remove('hidden');
                                           }
                                       }
                                   ">
                        </div>
                    </div>
                </div>
                
                <!-- Basic Information -->
                <div class="bg-white border border-gray-200 rounded-md shadow-sm">
                    <div class="bg-gray-50 border-b border-gray-200 px-4 py-3">
                        <h2 class="text-sm font-semibold text-gray-700 flex items-center">
                            <span class="material-icons-outlined text-primary-DEFAULT mr-2">person</span>
                            Basic Information
                        </h2>
                    </div>
                    
                    <div class="p-4">
                        <div class="space-y-3">
                            <!-- Name -->
                            <div class="flex flex-col md:flex-row md:items-center gap-3">
                                <label for="name" class="text-xs font-medium text-gray-700 md:w-40 flex items-center gap-1">
                                    Name
                                    <div class="tooltip-wrapper" x-data="{ show: false }">
                                        <span class="material-icons-outlined text-gray-400 text-sm cursor-help" 
                                              @mouseenter="show = true" 
                                              @mouseleave="show = false">
                                            help_outline
                                        </span>
                                        <div x-show="show" x-transition class="tooltip-content">
                                            Your full name
                                        </div>
                                    </div>
                                </label>
                                <div class="flex-1">
                                    <div class="relative">
                                        <input type="text" name="name" id="name" class="w-full h-9 text-xs border-gray-300 rounded px-3 focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50" value="{{ old('name', $user->name) }}" required>
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
                                            Your email address for login and notifications
                                        </div>
                                    </div>
                                </label>
                                <div class="flex-1">
                                    <div class="relative">
                                        <input type="email" name="email" id="email" class="w-full h-9 text-xs border-gray-300 rounded px-3 focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50" value="{{ old('email', $user->email) }}" required>
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
                                            Your contact phone number
                                        </div>
                                    </div>
                                </label>
                                <div class="flex-1">
                                    <input type="tel" name="phone" id="phone" class="w-full h-9 text-xs border-gray-300 rounded px-3 phone-input focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50" value="{{ old('phone', preg_replace('/^\\+?60/', '', $user->phone)) }}">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Address Information -->
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
                                        <input type="text" name="address_line1" id="address_line1" class="w-full h-9 text-xs border-gray-300 rounded px-3 focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50" value="{{ old('address_line1', $user->address_line1) }}">
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
                                            Apartment, suite, unit, building, floor
                                        </div>
                                    </div>
                                </label>
                                <div class="flex-1">
                                    <div class="relative">
                                        <input type="text" name="address_line2" id="address_line2" class="w-full h-9 text-xs border-gray-300 rounded px-3 focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50" value="{{ old('address_line2', $user->address_line2) }}">
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
                                            Select your state
                                        </div>
                                    </div>
                                </label>
                                <div class="flex-1">
                                    <div class="relative">
                                        <select name="state" id="state" class="w-full h-9 text-xs border-gray-300 rounded px-3 focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50">
                                            <option value="">Select State</option>
                                            @if(old('state', $user->state))
                                                <option value="{{ old('state', $user->state) }}" selected>{{ old('state', $user->state) }}</option>
                                            @endif
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
                                            Select your city
                                        </div>
                                    </div>
                                </label>
                                <div class="flex-1">
                                    <div class="relative">
                                        <select name="city" id="city" class="w-full h-9 text-xs border-gray-300 rounded px-3 focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50">
                                            <option value="">Select City</option>
                                            @if(old('city', $user->city))
                                                <option value="{{ old('city', $user->city) }}" selected>{{ old('city', $user->city) }}</option>
                                            @endif
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
                                            Select your postcode
                                        </div>
                                    </div>
                                </label>
                                <div class="flex-1">
                                    <div class="relative">
                                        <select name="postcode" id="postcode" class="w-full h-9 text-xs border-gray-300 rounded px-3 focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50">
                                            <option value="">Select Postcode</option>
                                            @if(old('postcode', $user->postcode))
                                                <option value="{{ old('postcode', $user->postcode) }}" selected>{{ old('postcode', $user->postcode) }}</option>
                                            @endif
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
                                            Select your country
                                        </div>
                                    </div>
                                </label>
                                <div class="flex-1">
                                    <div class="relative">
                                        <select name="country" id="country" class="w-full h-9 text-xs border-gray-300 rounded px-3 focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50" data-old-value="{{ old('country', $user->country ?? 'Malaysia') }}">
                                            <!-- Dropdown will be filled by JavaScript -->
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Organization Information -->
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
                                            Type of organization (e.g., Company, NGO, Government)
                                        </div>
                                    </div>
                                </label>
                                <div class="flex-1">
                                    <div class="relative">
                                        <input type="text" name="org_type" id="org_type" class="w-full h-9 text-xs border-gray-300 rounded px-3 focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50" value="{{ old('org_type', $user->org_type) }}">
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
                                            Official name of your organization
                                        </div>
                                    </div>
                                </label>
                                <div class="flex-1">
                                    <div class="relative">
                                        <input type="text" name="org_name" id="org_name" class="w-full h-9 text-xs border-gray-300 rounded px-3 focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50" value="{{ old('org_name', $user->org_name) }}">
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
                                            Organization street address
                                        </div>
                                    </div>
                                </label>
                                <div class="flex-1">
                                    <div class="relative">
                                        <input type="text" name="org_address_line1" id="org_address_line1" class="w-full h-9 text-xs border-gray-300 rounded px-3 focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50" value="{{ old('org_address_line1', $user->org_address_line1) }}">
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
                                            Organization building, floor, unit
                                        </div>
                                    </div>
                                </label>
                                <div class="flex-1">
                                    <div class="relative">
                                        <input type="text" name="org_address_line2" id="org_address_line2" class="w-full h-9 text-xs border-gray-300 rounded px-3 focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50" value="{{ old('org_address_line2', $user->org_address_line2) }}">
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Organization State -->
                            <div class="flex flex-col md:flex-row md:items-center gap-3">
                                <label for="org_state" class="text-xs font-medium text-gray-700 md:w-40 flex items-center gap-1">
                                    Org State
                                    <div class="tooltip-wrapper" x-data="{ show: false }">
                                        <span class="material-icons-outlined text-gray-400 text-sm cursor-help" 
                                              @mouseenter="show = true" 
                                              @mouseleave="show = false">
                                            help_outline
                                        </span>
                                        <div x-show="show" x-transition class="tooltip-content">
                                            Organization state
                                        </div>
                                    </div>
                                </label>
                                <div class="flex-1">
                                    <div class="relative">
                                        <select name="org_state" id="org_state" class="w-full h-9 text-xs border-gray-300 rounded px-3 focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50">
                                            <option value="">Select State</option>
                                            @if(old('org_state', $user->org_state))
                                                <option value="{{ old('org_state', $user->org_state) }}" selected>{{ old('org_state', $user->org_state) }}</option>
                                            @endif
                                        </select>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Organization City -->
                            <div class="flex flex-col md:flex-row md:items-center gap-3">
                                <label for="org_city" class="text-xs font-medium text-gray-700 md:w-40 flex items-center gap-1">
                                    Org City
                                    <div class="tooltip-wrapper" x-data="{ show: false }">
                                        <span class="material-icons-outlined text-gray-400 text-sm cursor-help" 
                                              @mouseenter="show = true" 
                                              @mouseleave="show = false">
                                            help_outline
                                        </span>
                                        <div x-show="show" x-transition class="tooltip-content">
                                            Organization city
                                        </div>
                                    </div>
                                </label>
                                <div class="flex-1">
                                    <div class="relative">
                                        <select name="org_city" id="org_city" class="w-full h-9 text-xs border-gray-300 rounded px-3 focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50">
                                            <option value="">Select City</option>
                                            @if(old('org_city', $user->org_city))
                                                <option value="{{ old('org_city', $user->org_city) }}" selected>{{ old('org_city', $user->org_city) }}</option>
                                            @endif
                                        </select>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Organization Postcode -->
                            <div class="flex flex-col md:flex-row md:items-center gap-3">
                                <label for="org_postcode" class="text-xs font-medium text-gray-700 md:w-40 flex items-center gap-1">
                                    Org Postcode
                                    <div class="tooltip-wrapper" x-data="{ show: false }">
                                        <span class="material-icons-outlined text-gray-400 text-sm cursor-help" 
                                              @mouseenter="show = true" 
                                              @mouseleave="show = false">
                                            help_outline
                                        </span>
                                        <div x-show="show" x-transition class="tooltip-content">
                                            Organization postcode
                                        </div>
                                    </div>
                                </label>
                                <div class="flex-1">
                                    <div class="relative">
                                        <select name="org_postcode" id="org_postcode" class="w-full h-9 text-xs border-gray-300 rounded px-3 focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50">
                                            <option value="">Select Postcode</option>
                                            @if(old('org_postcode', $user->org_postcode))
                                                <option value="{{ old('org_postcode', $user->org_postcode) }}" selected>{{ old('org_postcode', $user->org_postcode) }}</option>
                                            @endif
                                        </select>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Organization Country -->
                            <div class="flex flex-col md:flex-row md:items-center gap-3">
                                <label for="org_country" class="text-xs font-medium text-gray-700 md:w-40 flex items-center gap-1">
                                    Org Country
                                    <div class="tooltip-wrapper" x-data="{ show: false }">
                                        <span class="material-icons-outlined text-gray-400 text-sm cursor-help" 
                                              @mouseenter="show = true" 
                                              @mouseleave="show = false">
                                            help_outline
                                        </span>
                                        <div x-show="show" x-transition class="tooltip-content">
                                            Organization country
                                        </div>
                                    </div>
                                </label>
                                <div class="flex-1">
                                    <div class="relative">
                                        <select name="org_country" id="org_country" class="w-full h-9 text-xs border-gray-300 rounded px-3 focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50" data-old-value="{{ old('org_country', $user->org_country ?? 'Malaysia') }}">
                                            <!-- Dropdown will be filled by JavaScript -->
                                        </select>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Organization Telephone -->
                            <div class="flex flex-col md:flex-row md:items-center gap-3">
                                <label for="org_telephone" class="text-xs font-medium text-gray-700 md:w-40 flex items-center gap-1">
                                    Org Telephone
                                    <div class="tooltip-wrapper" x-data="{ show: false }">
                                        <span class="material-icons-outlined text-gray-400 text-sm cursor-help" 
                                              @mouseenter="show = true" 
                                              @mouseleave="show = false">
                                            help_outline
                                        </span>
                                        <div x-show="show" x-transition class="tooltip-content">
                                            Organization contact number
                                        </div>
                                    </div>
                                </label>
                                <div class="flex-1">
                                    <input type="tel" name="org_telephone" id="org_telephone" class="w-full h-9 text-xs border-gray-300 rounded px-3 phone-input focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50" value="{{ old('org_telephone', preg_replace('/^\\+?60/', '', $user->org_telephone)) }}">
                                </div>
                            </div>
                            
                            <!-- Organization Fax -->
                            <div class="flex flex-col md:flex-row md:items-center gap-3">
                                <label for="org_fax" class="text-xs font-medium text-gray-700 md:w-40 flex items-center gap-1">
                                    Org Fax
                                    <div class="tooltip-wrapper" x-data="{ show: false }">
                                        <span class="material-icons-outlined text-gray-400 text-sm cursor-help" 
                                              @mouseenter="show = true" 
                                              @mouseleave="show = false">
                                            help_outline
                                        </span>
                                        <div x-show="show" x-transition class="tooltip-content">
                                            Organization fax number
                                        </div>
                                    </div>
                                </label>
                                <div class="flex-1">
                                    <div class="relative">
                                        <input type="tel" name="org_fax" id="org_fax" class="w-full h-9 text-xs border-gray-300 rounded px-3 focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50" value="{{ old('org_fax', $user->org_fax) }}">
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Organization Email -->
                            <div class="flex flex-col md:flex-row md:items-center gap-3">
                                <label for="org_email" class="text-xs font-medium text-gray-700 md:w-40 flex items-center gap-1">
                                    Org Email
                                    <div class="tooltip-wrapper" x-data="{ show: false }">
                                        <span class="material-icons-outlined text-gray-400 text-sm cursor-help" 
                                              @mouseenter="show = true" 
                                              @mouseleave="show = false">
                                            help_outline
                                        </span>
                                        <div x-show="show" x-transition class="tooltip-content">
                                            Organization email address
                                        </div>
                                    </div>
                                </label>
                                <div class="flex-1">
                                    <div class="relative">
                                        <input type="email" name="org_email" id="org_email" class="w-full h-9 text-xs border-gray-300 rounded px-3 focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50" value="{{ old('org_email', $user->org_email) }}">
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Organization Website -->
                            <div class="flex flex-col md:flex-row md:items-center gap-3">
                                <label for="org_website" class="text-xs font-medium text-gray-700 md:w-40 flex items-center gap-1">
                                    Org Website
                                    <div class="tooltip-wrapper" x-data="{ show: false }">
                                        <span class="material-icons-outlined text-gray-400 text-sm cursor-help" 
                                              @mouseenter="show = true" 
                                              @mouseleave="show = false">
                                            help_outline
                                        </span>
                                        <div x-show="show" x-transition class="tooltip-content">
                                            Organization website URL
                                        </div>
                                    </div>
                                </label>
                                <div class="flex-1">
                                    <div class="relative">
                                        <input type="url" name="org_website" id="org_website" class="w-full h-9 text-xs border-gray-300 rounded px-3 focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50" value="{{ old('org_website', $user->org_website) }}" placeholder="https://example.com">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Change Password -->
                <div class="bg-white border border-gray-200 rounded-md shadow-sm">
                    <div class="bg-gray-50 border-b border-gray-200 px-4 py-3">
                        <h2 class="text-sm font-semibold text-gray-700 flex items-center">
                            <span class="material-icons-outlined text-primary-DEFAULT mr-2">lock</span>
                            Change Password
                        </h2>
                    </div>
                    
                    <div class="p-4">
                        <div class="space-y-3">
                            <p class="text-[11px] text-gray-500">
                                Leave these blank to keep your current password.
                                {{ \App\Support\SecurityPolicy::describe() }}
                            </p>

                            <!-- Current Password -->
                            <div class="flex flex-col md:flex-row md:items-start gap-3">
                                <label for="current_password" class="text-xs font-medium text-gray-700 md:w-40 md:pt-2">
                                    Current Password
                                </label>
                                <div class="flex-1">
                                    <input type="password" name="current_password" id="current_password"
                                           autocomplete="current-password"
                                           class="w-full h-9 text-xs border-gray-300 rounded px-3 focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50">
                                    @error('current_password')
                                        <p class="text-[11px] text-red-600 mt-1">{{ $message }}</p>
                                    @enderror
                                    <p class="text-[11px] text-gray-500 mt-1">
                                        Required only when setting a new password, so a borrowed session cannot
                                        change it.
                                    </p>
                                </div>
                            </div>

                            <!-- New Password -->
                            <div class="flex flex-col md:flex-row md:items-start gap-3">
                                <label for="password" class="text-xs font-medium text-gray-700 md:w-40 md:pt-2">
                                    New Password
                                </label>
                                <div class="flex-1">
                                    <input type="password" name="password" id="password"
                                           autocomplete="new-password"
                                           class="w-full h-9 text-xs border-gray-300 rounded px-3 focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50">
                                    @error('password')
                                        <p class="text-[11px] text-red-600 mt-1">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>

                            <!-- Confirm Password -->
                            <div class="flex flex-col md:flex-row md:items-start gap-3">
                                <label for="password_confirmation" class="text-xs font-medium text-gray-700 md:w-40 md:pt-2">
                                    Confirm Password
                                </label>
                                <div class="flex-1">
                                    <input type="password" name="password_confirmation" id="password_confirmation"
                                           autocomplete="new-password"
                                           class="w-full h-9 text-xs border-gray-300 rounded px-3 focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50">
                                </div>
                            </div>

                            <div class="bg-amber-50 border border-amber-200 rounded px-3 py-2">
                                <p class="text-[11px] text-amber-800">
                                    Changing your password signs you out of every other device.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="flex justify-end space-x-3 pt-4">
                    <a 
                        href="{{ route('dashboard') }}" 
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
                        Update Profile
                    </button>
                </div>
            </form>

            {{-- Declared outside the profile form, because a form cannot nest inside
                 another. The Remove button beside the picture submits this one by id. --}}
            @if($user->profile_image)
                <form method="POST" action="{{ route('profile.image.destroy') }}" id="removeProfileImage" class="hidden">
                    @csrf
                    @method('DELETE')
                </form>
            @endif

            <div class="border-t border-gray-200 pt-4 mt-6">
                @include('profile.partials.delete-user-form')
            </div>
        </div>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Populate states dropdown
            populateStates();
            populateStates('org_state'); // For organization state dropdown

            // If there are old values, try to repopulate the form
            const oldState = "{{ old('state', $user->state) }}";
            const oldCity = "{{ old('city', $user->city) }}";
            const oldPostcode = "{{ old('postcode', $user->postcode) }}";

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
            const oldOrgState = "{{ old('org_state', $user->org_state) }}";
            const oldOrgCity = "{{ old('org_city', $user->org_city) }}";
            const oldOrgPostcode = "{{ old('org_postcode', $user->org_postcode) }}";

            if (oldOrgState) {
                setTimeout(() => {
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
                }, 100);
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
                } else {
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
                } else {
                    postcodeSelect.innerHTML = '<option value="">Enter Postcode Manually</option>';
                }
            } catch (error) {
                console.error('Error looking up postcodes for', cityFieldId, ':', error);
            }
        }
    </script>
</x-app-layout>
