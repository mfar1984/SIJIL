<x-app-layout>
    <x-slot name="breadcrumb">
        <span>Participants</span>
        <span class="mx-2 text-gray-500">/</span>
        <span>Edit Participant</span>
    </x-slot>

    <x-slot name="title">Edit Participant</x-slot>

    <div class="bg-white rounded shadow-md border border-gray-300">
        <div class="p-6 border-b border-gray-200">
            <div class="flex items-center">
                <span class="material-icons mr-2 text-primary-DEFAULT">edit</span>
                <h1 class="text-xl font-bold text-gray-800">Edit Participant: {{ $participant->name }}</h1>
            </div>
            <p class="text-xs text-gray-500 mt-1 ml-8">Modify participant information</p>
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
            <form method="POST" action="{{ route('participants.update', $participant->id) }}" class="space-y-6">@method('PUT')
                @csrf
                <!-- Basic Information -->
                <div class="border-b border-gray-200 pb-5">
                    <h2 class="text-sm font-semibold text-gray-700 mb-4">Basic Information</h2>
                    <div class="grid grid-cols-2 gap-4 mb-4">
                        <!-- Full Name -->
                        <div>
                            <label for="name" class="flex items-center text-xs font-medium text-gray-700 mb-1">
                                <span class="material-icons text-sm mr-1 text-primary-DEFAULT">person</span>
                                Full Name <span class="text-red-500">*</span>
                            </label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <span class="material-icons text-[#004aad] text-base">badge</span>
                                </div>
                                <input type="text" name="name" id="name" class="w-full text-xs border-gray-300 rounded-[1px] pl-12 focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50" value="{{ old('name', $participant->name) }}" required>
                            </div>
                            <p class="mt-1 text-[10px] text-gray-500">Enter participant's full name as it appears on official documents</p>
                        </div>
                        <!-- Email -->
                        <div>
                            <label for="email" class="flex items-center text-xs font-medium text-gray-700 mb-1">
                                <span class="material-icons text-sm mr-1 text-primary-DEFAULT">email</span>
                                Email Address <span class="text-red-500">*</span>
                            </label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <span class="material-icons text-[#004aad] text-base">alternate_email</span>
                                </div>
                                <input type="email" name="email" id="email" class="w-full text-xs border-gray-300 rounded-[1px] pl-12 focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50" value="{{ old('email', $participant->email) }}" required>
                            </div>
                            <p class="mt-1 text-[10px] text-gray-500">This email will be used for notifications and communications</p>
                        </div>
                    </div>
                    <div class="grid grid-cols-3 gap-4">
                        <!-- Phone -->
                        <div>
                            <label for="phone" class="flex items-center text-xs font-medium text-gray-700 mb-1">
                                <span class="material-icons text-sm mr-1 text-primary-DEFAULT">phone</span>
                                Phone Number
                            </label>
                            <div>
                                <input type="tel" name="phone" id="phone" class="phone-input w-full text-xs border-gray-300 rounded-[1px] focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50" value="{{ old('phone', preg_match('/^60/', $participant->phone) ? substr($participant->phone, 2) : $participant->phone) }}">
                            </div>
                            <p class="mt-1 text-[10px] text-gray-500">Select country code and enter phone number</p>
                        </div>
                        <!-- Identity Card / Passport No. -->
                        <div>
                            <label for="id_type" class="flex items-center text-xs font-medium text-gray-700 mb-1">
                                <span class="material-icons text-sm mr-1 text-primary-DEFAULT">badge</span>
                                Identity Card / Passport No.
                            </label>
                            <div class="mb-2">
                                <select name="id_type" id="id_type" class="w-full text-xs border-gray-300 rounded-[1px] focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50" onchange="toggleIdFields()">
                                    <option value="">-- Select IC / Passport --</option>
                                    <option value="ic" {{ old('id_type', ($participant->identity_card ? 'ic' : ($participant->id_passport && stripos($participant->id_passport, '-') !== false ? 'ic' : ''))) == 'ic' ? 'selected' : '' }}>Identity Card</option>
                                    <option value="passport" {{ old('id_type', ($participant->passport_no ? 'passport' : ($participant->id_passport && stripos($participant->id_passport, '-') === false && $participant->id_passport ? 'passport' : ''))) == 'passport' ? 'selected' : '' }}>Passport</option>
                                </select>
                            </div>
                            <div id="ic_field" class="relative hidden">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <span class="material-icons text-[#004aad] text-base">assignment_ind</span>
                                </div>
                                <input type="text" name="identity_card" id="organization_ic" placeholder="000000-00-0000" class="w-full text-xs border-gray-300 rounded-[1px] pl-12 focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50" value="{{ old('identity_card', $participant->identity_card ?: $participant->id_passport) }}" maxlength="14" oninput="formatIC(this)">
                            </div>
                            <div id="passport_field" class="relative hidden">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <span class="material-icons text-[#004aad] text-base">assignment_ind</span>
                                </div>
                                <input type="text" name="passport_no" id="organization_passport" placeholder="A00000000" class="w-full text-xs border-gray-300 rounded-[1px] pl-12 focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50" value="{{ old('passport_no', $participant->passport_no ?: $participant->id_passport) }}">
                            </div>
                        </div>
                        <!-- Date of Birth -->
                        <div>
                            <label for="date_of_birth" class="flex items-center text-xs font-medium text-gray-700 mb-1">
                                <span class="material-icons text-sm mr-1 text-primary-DEFAULT">cake</span>
                                Date of Birth
                            </label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <span class="material-icons text-[#004aad] text-base">calendar_today</span>
                                </div>
                                <input type="date" name="date_of_birth" id="date_of_birth" class="w-full text-xs border-gray-300 rounded-[1px] pl-12 focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50" value="{{ old('date_of_birth', $participant->date_of_birth ? $participant->date_of_birth->format('Y-m-d') : '') }}">
                            </div>
                            <p class="mt-1 text-[10px] text-gray-500">For age verification and demographic analysis</p>
                        </div>
                    </div>
                    <div class="grid grid-cols-1 gap-4 mt-4">
                        <!-- Address -->
                        <div>
                            <label class="flex items-center text-xs font-medium text-gray-700 mb-1">
                                <span class="material-icons text-sm mr-1 text-primary-DEFAULT">home</span>
                                Address
                            </label>
                            
                            @php
                                // Default values
                                $address1 = '';
                                $address2 = '';
                                $city = '';
                                $state = '';
                                $postcode = '';
                                $country = 'Malaysia';
                                
                                // Try to extract address1 and address2 from the address field
                                if ($participant->address) {
                                    $addressLines = explode("\n", $participant->address);
                                    $address1 = $addressLines[0] ?? '';
                                    $address2 = isset($addressLines[1]) ? $addressLines[1] : '';
                                }
                                
                                // Try to extract city, state, postcode from the last line
                                if (!empty($addressLines)) {
                                    $lastLine = end($addressLines);
                                    
                                    // Extract postcode (5 digits)
                                    if (preg_match('/\b(\d{5})\b/', $lastLine, $matches)) {
                                        $postcode = $matches[1];
                                    }
                                    
                                    // Try to extract state
                                    $statesInMalaysia = [
                                        'Johor', 'Kedah', 'Kelantan', 'Melaka', 'Negeri Sembilan', 
                                        'Pahang', 'Perak', 'Perlis', 'Pulau Pinang', 'Sabah', 
                                        'Sarawak', 'Selangor', 'Terengganu', 'Kuala Lumpur', 
                                        'Labuan', 'Putrajaya'
                                    ];
                                    
                                    foreach ($statesInMalaysia as $possibleState) {
                                        if (stripos($lastLine, $possibleState) !== false) {
                                            $state = $possibleState;
                                            break;
                                        }
                                    }
                                    
                                    // Try to extract city
                                    if (!empty($state) && stripos($lastLine, $state) !== false) {
                                        $parts = explode($state, $lastLine);
                                        if (!empty($parts[0])) {
                                            // Clean up and remove postcode
                                            $cityPart = trim(preg_replace('/\d{5}/', '', $parts[0]));
                                            // Remove commas and any other punctuation
                                            $cityPart = trim($cityPart, ", \t\n\r\0\x0B");
                                            if (!empty($cityPart)) {
                                                $city = $cityPart;
                                            }
                                        }
                                    }
                                    
                                    // Try to extract country
                                    $countries = ['Malaysia', 'Singapore', 'Indonesia', 'Thailand', 'Philippines'];
                                    foreach ($countries as $possibleCountry) {
                                        if (stripos($lastLine, $possibleCountry) !== false) {
                                            $country = $possibleCountry;
                                            break;
                                        }
                                    }
                                }
                            @endphp
                            
                            <!-- Address 1 and Address 2 in one row (2 columns) -->
                            <div class="grid grid-cols-2 gap-2 mb-2">
                                <!-- Address 1 -->
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <span class="material-icons text-[#004aad] text-base">location_on</span>
                                    </div>
                                    <input type="text" name="address1" id="address1" placeholder="Address Line 1" 
                                        class="w-full text-xs border-gray-300 rounded-[1px] pl-12 focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50" 
                                        value="{{ old('address1', $participant->address1) }}">
                                </div>
                                
                                <!-- Address 2 -->
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <span class="material-icons text-[#004aad] text-base">location_on</span>
                                    </div>
                                    <input type="text" name="address2" id="address2" placeholder="Address Line 2" 
                                        class="w-full text-xs border-gray-300 rounded-[1px] pl-12 focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50" 
                                        value="{{ old('address2', $participant->address2) }}">
                                </div>
                            </div>
                            
                            <!-- State, City, Postcode, Country in one row (4 columns) -->
                            <div class="grid grid-cols-4 gap-2">
                                <!-- State -->
                                <div>
                                    <label for="state" class="block text-xs font-medium text-gray-700 mb-1">State</label>
                                    <select name="state" id="state" 
                                        class="w-full text-xs border-gray-300 rounded-[1px] focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50"
                                        onchange="handleStateChange()" data-old-value="{{ old('state', $participant->state) }}">
                                        <option value="">-- Select State --</option>
                                    </select>
                                </div>
                                
                                <!-- City -->
                                <div>
                                    <label for="city" class="block text-xs font-medium text-gray-700 mb-1">City</label>
                                    <select name="city" id="city" 
                                        class="w-full text-xs border-gray-300 rounded-[1px] focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50" 
                                        disabled data-old-value="{{ old('city', $participant->city) }}">
                                        <option value="">-- Select City --</option>
                                    </select>
                                </div>
                                
                                <!-- Postcode -->
                                <div>
                                    <label for="postcode" class="block text-xs font-medium text-gray-700 mb-1">Postcode</label>
                                    <select name="postcode" id="postcode" 
                                        class="w-full text-xs border-gray-300 rounded-[1px] focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50" 
                                        disabled data-old-value="{{ old('postcode', $participant->postcode) }}">
                                        <option value="">-- Select Postcode --</option>
                                    </select>
                                </div>
                                
                                <!-- Country -->
                                <div>
                                    <label for="country" class="block text-xs font-medium text-gray-700 mb-1">Country</label>
                                    <select name="country" id="country" 
                                        class="w-full text-xs border-gray-300 rounded-[1px] focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50"
                                        data-old-value="{{ old('country', $participant->country ?? 'Malaysia') }}">
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
                                                <span class="material-icons text-[#004aad] text-base">edit_location</span>
                                            </div>
                                            <input type="text" name="manual_state" id="manual_state" 
                                                class="w-full text-xs border-gray-300 rounded-[1px] pl-12 focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50"
                                                placeholder="Enter state manually" value="{{ old('manual_state', $participant->state) }}">
                                        </div>
                                    </div>
                                    
                                    <!-- Manual City -->
                                    <div>
                                        <label for="manual_city" class="block text-xs font-medium text-gray-700 mb-1">City (Manual)</label>
                                        <div class="relative">
                                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                                <span class="material-icons text-[#004aad] text-base">edit_location</span>
                                            </div>
                                            <input type="text" name="manual_city" id="manual_city" 
                                                class="w-full text-xs border-gray-300 rounded-[1px] pl-12 focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50"
                                                placeholder="Enter city manually" value="{{ old('manual_city', $participant->city) }}">
                                        </div>
                                    </div>
                                    
                                    <!-- Manual Postcode -->
                                    <div>
                                        <label for="manual_postcode" class="block text-xs font-medium text-gray-700 mb-1">Postcode (Manual)</label>
                                        <div class="relative">
                                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                                <span class="material-icons text-[#004aad] text-base">edit_location</span>
                                            </div>
                                            <input type="text" name="manual_postcode" id="manual_postcode" 
                                                class="w-full text-xs border-gray-300 rounded-[1px] pl-12 focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50"
                                                placeholder="Enter postcode manually" value="{{ old('manual_postcode', $participant->postcode) }}">
                                        </div>
                                    </div>
                                    
                                    <!-- Empty column to match grid -->
                                    <div>
                                        <!-- This is empty to match the Country column position -->
                                    </div>
                                </div>
                            </div>
                            
                            <p class="mt-1 text-[10px] text-gray-500">Enter full mailing address</p>
                        </div>
                    </div>
                </div>

                <!-- Additional Information -->
                <div class="border-b border-gray-200 pb-5">
                    <h2 class="text-sm font-semibold text-gray-700 mb-4">Additional Information</h2>
                    <div class="grid grid-cols-2 gap-4 mb-4">
                        <!-- Gender -->
                        <div>
                            <label for="gender" class="flex items-center text-xs font-medium text-gray-700 mb-1">
                                <span class="material-icons text-sm mr-1 text-primary-DEFAULT">wc</span>
                                Gender
                            </label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <span class="material-icons text-[#004aad] text-base">person</span>
                                </div>
                                <select name="gender" id="gender" class="w-full text-xs border-gray-300 rounded-[1px] pl-12 focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50">
                                    <option value="">-- Select Gender --</option>
                                    <option value="male" {{ old('gender', $participant->gender) == 'male' ? 'selected' : '' }}>Male</option>
                                    <option value="female" {{ old('gender', $participant->gender) == 'female' ? 'selected' : '' }}>Female</option>
                                    <option value="other" {{ old('gender', $participant->gender) == 'other' ? 'selected' : '' }}>Other</option>
                                </select>
                            </div>
                            <p class="mt-1 text-[10px] text-gray-500">Select participant's gender for demographic data</p>
                        </div>
                        <!-- Organization/Company -->
                        <div>
                            <label for="organization" class="flex items-center text-xs font-medium text-gray-700 mb-1">
                                <span class="material-icons text-sm mr-1 text-primary-DEFAULT">business</span>
                                Organization/Company
                            </label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <span class="material-icons text-[#004aad] text-base">apartment</span>
                                </div>
                                <input type="text" name="organization" id="organization" class="w-full text-xs border-gray-300 rounded-[1px] pl-12 focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50" value="{{ old('organization', $participant->organization) }}">
                            </div>
                            <p class="mt-1 text-[10px] text-gray-500">Company or organization the participant represents</p>
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <!-- Job Title -->
                        <div>
                            <label for="job_title" class="flex items-center text-xs font-medium text-gray-700 mb-1">
                                <span class="material-icons text-sm mr-1 text-primary-DEFAULT">work</span>
                                Job Title
                            </label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <span class="material-icons text-[#004aad] text-base">badge</span>
                                </div>
                                <input type="text" name="job_title" id="job_title" class="w-full text-xs border-gray-300 rounded-[1px] pl-12 focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50" value="{{ old('job_title', $participant->job_title) }}">
                            </div>
                            <p class="mt-1 text-[10px] text-gray-500">Current position or role</p>
                        </div>
                        <!-- Race (Bangsa) -->
                        <div>
                            <label for="race" class="flex items-center text-xs font-medium text-gray-700 mb-1">
                                <span class="material-icons text-sm mr-1 text-primary-DEFAULT">diversity_1</span>
                                Race (Bangsa)
                            </label>
                            <select name="race" id="race" class="w-full text-xs border-gray-300 rounded-[1px] focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50">
                                <option value="">-- Select Race --</option>
                                <option value="Melayu (Semenanjung)" {{ old('race', $participant->race) == 'Melayu (Semenanjung)' ? 'selected' : '' }}>Melayu (Semenanjung)</option>
                                <option value="Melayu (Sarawak)" {{ old('race', $participant->race) == 'Melayu (Sarawak)' ? 'selected' : '' }}>Melayu (Sarawak)</option>
                                <option value="Melayu (Sabah)" {{ old('race', $participant->race) == 'Melayu (Sabah)' ? 'selected' : '' }}>Melayu (Sabah)</option>
                                <option value="Cina Hokkien" {{ old('race', $participant->race) == 'Cina Hokkien' ? 'selected' : '' }}>Cina Hokkien</option>
                                <option value="Cina Kantonis" {{ old('race', $participant->race) == 'Cina Kantonis' ? 'selected' : '' }}>Cina Kantonis</option>
                                <option value="Cina Hakka" {{ old('race', $participant->race) == 'Cina Hakka' ? 'selected' : '' }}>Cina Hakka</option>
                                <option value="Cina Teochew" {{ old('race', $participant->race) == 'Cina Teochew' ? 'selected' : '' }}>Cina Teochew</option>
                                <option value="Cina Foochow" {{ old('race', $participant->race) == 'Cina Foochow' ? 'selected' : '' }}>Cina Foochow</option>
                                <option value="Cina Hainan" {{ old('race', $participant->race) == 'Cina Hainan' ? 'selected' : '' }}>Cina Hainan</option>
                                <option value="Cina Kwongsai" {{ old('race', $participant->race) == 'Cina Kwongsai' ? 'selected' : '' }}>Cina Kwongsai</option>
                                <option value="Cina Henghua" {{ old('race', $participant->race) == 'Cina Henghua' ? 'selected' : '' }}>Cina Henghua</option>
                                <option value="Cina lain-lain" {{ old('race', $participant->race) == 'Cina lain-lain' ? 'selected' : '' }}>Cina lain-lain</option>
                                <option value="India Tamil" {{ old('race', $participant->race) == 'India Tamil' ? 'selected' : '' }}>India Tamil</option>
                                <option value="India Punjabi" {{ old('race', $participant->race) == 'India Punjabi' ? 'selected' : '' }}>India Punjabi</option>
                                <option value="India Malayalee" {{ old('race', $participant->race) == 'India Malayalee' ? 'selected' : '' }}>India Malayalee</option>
                                <option value="India Telugu" {{ old('race', $participant->race) == 'India Telugu' ? 'selected' : '' }}>India Telugu</option>
                                <option value="India Gujerati" {{ old('race', $participant->race) == 'India Gujerati' ? 'selected' : '' }}>India Gujerati</option>
                                <option value="India Bengali" {{ old('race', $participant->race) == 'India Bengali' ? 'selected' : '' }}>India Bengali</option>
                                <option value="India lain-lain" {{ old('race', $participant->race) == 'India lain-lain' ? 'selected' : '' }}>India lain-lain</option>
                                <option value="Iban" {{ old('race', $participant->race) == 'Iban' ? 'selected' : '' }}>Iban</option>
                                <option value="Kadazan" {{ old('race', $participant->race) == 'Kadazan' ? 'selected' : '' }}>Kadazan</option>
                                <option value="Dusun" {{ old('race', $participant->race) == 'Dusun' ? 'selected' : '' }}>Dusun</option>
                                <option value="Bajau" {{ old('race', $participant->race) == 'Bajau' ? 'selected' : '' }}>Bajau</option>
                                <option value="Sama" {{ old('race', $participant->race) == 'Sama' ? 'selected' : '' }}>Sama</option>
                                <option value="Bidayuh" {{ old('race', $participant->race) == 'Bidayuh' ? 'selected' : '' }}>Bidayuh</option>
                                <option value="Melanau" {{ old('race', $participant->race) == 'Melanau' ? 'selected' : '' }}>Melanau</option>
                                <option value="Murut" {{ old('race', $participant->race) == 'Murut' ? 'selected' : '' }}>Murut</option>
                                <option value="Orang Ulu Kayan" {{ old('race', $participant->race) == 'Orang Ulu Kayan' ? 'selected' : '' }}>Orang Ulu Kayan</option>
                                <option value="Orang Ulu Kenyah" {{ old('race', $participant->race) == 'Orang Ulu Kenyah' ? 'selected' : '' }}>Orang Ulu Kenyah</option>
                                <option value="Orang Ulu Kelabit" {{ old('race', $participant->race) == 'Orang Ulu Kelabit' ? 'selected' : '' }}>Orang Ulu Kelabit</option>
                                <option value="Orang Ulu Penan" {{ old('race', $participant->race) == 'Orang Ulu Penan' ? 'selected' : '' }}>Orang Ulu Penan</option>
                                <option value="Orang Ulu Lun Bawang" {{ old('race', $participant->race) == 'Orang Ulu Lun Bawang' ? 'selected' : '' }}>Orang Ulu Lun Bawang</option>
                                <option value="Orang Ulu (lain-lain)" {{ old('race', $participant->race) == 'Orang Ulu (lain-lain)' ? 'selected' : '' }}>Orang Ulu (lain-lain)</option>
                                <option value="Orang Asli Temuan" {{ old('race', $participant->race) == 'Orang Asli Temuan' ? 'selected' : '' }}>Orang Asli Temuan</option>
                                <option value="Orang Asli Semai" {{ old('race', $participant->race) == 'Orang Asli Semai' ? 'selected' : '' }}>Orang Asli Semai</option>
                                <option value="Orang Asli Jakun" {{ old('race', $participant->race) == 'Orang Asli Jakun' ? 'selected' : '' }}>Orang Asli Jakun</option>
                                <option value="Orang Asli Mah Meri" {{ old('race', $participant->race) == 'Orang Asli Mah Meri' ? 'selected' : '' }}>Orang Asli Mah Meri</option>
                                <option value="Orang Asli Negrito (Kensiu)" {{ old('race', $participant->race) == 'Orang Asli Negrito (Kensiu)' ? 'selected' : '' }}>Orang Asli Negrito (Kensiu)</option>
                                <option value="Orang Asli Negrito (Kintaq)" {{ old('race', $participant->race) == 'Orang Asli Negrito (Kintaq)' ? 'selected' : '' }}>Orang Asli Negrito (Kintaq)</option>
                                <option value="Orang Asli Negrito (Jahai)" {{ old('race', $participant->race) == 'Orang Asli Negrito (Jahai)' ? 'selected' : '' }}>Orang Asli Negrito (Jahai)</option>
                                <option value="Orang Asli Negrito (Lanoh)" {{ old('race', $participant->race) == 'Orang Asli Negrito (Lanoh)' ? 'selected' : '' }}>Orang Asli Negrito (Lanoh)</option>
                                <option value="Orang Asli Negrito (Mendriq)" {{ old('race', $participant->race) == 'Orang Asli Negrito (Mendriq)' ? 'selected' : '' }}>Orang Asli Negrito (Mendriq)</option>
                                <option value="Orang Asli Negrito (Batek)" {{ old('race', $participant->race) == 'Orang Asli Negrito (Batek)' ? 'selected' : '' }}>Orang Asli Negrito (Batek)</option>
                                <option value="Orang Asli Senoi (Temiar)" {{ old('race', $participant->race) == 'Orang Asli Senoi (Temiar)' ? 'selected' : '' }}>Orang Asli Senoi (Temiar)</option>
                                <option value="Orang Asli Senoi (Semaq Beri)" {{ old('race', $participant->race) == 'Orang Asli Senoi (Semaq Beri)' ? 'selected' : '' }}>Orang Asli Senoi (Semaq Beri)</option>
                                <option value="Orang Asli Senoi (Jah Hut)" {{ old('race', $participant->race) == 'Orang Asli Senoi (Jah Hut)' ? 'selected' : '' }}>Orang Asli Senoi (Jah Hut)</option>
                                <option value="Orang Asli Senoi (Che Wong)" {{ old('race', $participant->race) == 'Orang Asli Senoi (Che Wong)' ? 'selected' : '' }}>Orang Asli Senoi (Che Wong)</option>
                                <option value="Orang Asli Proto-Malay (Temuan)" {{ old('race', $participant->race) == 'Orang Asli Proto-Malay (Temuan)' ? 'selected' : '' }}>Orang Asli Proto-Malay (Temuan)</option>
                                <option value="Orang Asli Proto-Malay (Semelai)" {{ old('race', $participant->race) == 'Orang Asli Proto-Malay (Semelai)' ? 'selected' : '' }}>Orang Asli Proto-Malay (Semelai)</option>
                                <option value="Orang Asli Proto-Malay (Jakun)" {{ old('race', $participant->race) == 'Orang Asli Proto-Malay (Jakun)' ? 'selected' : '' }}>Orang Asli Proto-Malay (Jakun)</option>
                                <option value="Orang Asli Proto-Malay (Kanaq)" {{ old('race', $participant->race) == 'Orang Asli Proto-Malay (Kanaq)' ? 'selected' : '' }}>Orang Asli Proto-Malay (Kanaq)</option>
                                <option value="Orang Asli Proto-Malay (Seletar)" {{ old('race', $participant->race) == 'Orang Asli Proto-Malay (Seletar)' ? 'selected' : '' }}>Orang Asli Proto-Malay (Seletar)</option>
                                <option value="Orang Asli (lain-lain)" {{ old('race', $participant->race) == 'Orang Asli (lain-lain)' ? 'selected' : '' }}>Orang Asli (lain-lain)</option>
                                <option value="Sungai" {{ old('race', $participant->race) == 'Sungai' ? 'selected' : '' }}>Sungai</option>
                                <option value="Rungus" {{ old('race', $participant->race) == 'Rungus' ? 'selected' : '' }}>Rungus</option>
                                <option value="Lundayeh" {{ old('race', $participant->race) == 'Lundayeh' ? 'selected' : '' }}>Lundayeh</option>
                                <option value="Kedayan" {{ old('race', $participant->race) == 'Kedayan' ? 'selected' : '' }}>Kedayan</option>
                                <option value="Bisaya" {{ old('race', $participant->race) == 'Bisaya' ? 'selected' : '' }}>Bisaya</option>
                                <option value="Brunei" {{ old('race', $participant->race) == 'Brunei' ? 'selected' : '' }}>Brunei</option>
                                <option value="Bugis" {{ old('race', $participant->race) == 'Bugis' ? 'selected' : '' }}>Bugis</option>
                                <option value="Jawa" {{ old('race', $participant->race) == 'Jawa' ? 'selected' : '' }}>Jawa</option>
                                <option value="Banjar" {{ old('race', $participant->race) == 'Banjar' ? 'selected' : '' }}>Banjar</option>
                                <option value="Kristang/Serani" {{ old('race', $participant->race) == 'Kristang/Serani' ? 'selected' : '' }}>Kristang/Serani</option>
                                <option value="Sikh" {{ old('race', $participant->race) == 'Sikh' ? 'selected' : '' }}>Sikh</option>
                                <option value="Thai" {{ old('race', $participant->race) == 'Thai' ? 'selected' : '' }}>Thai</option>
                                <option value="Peranakan/Baba Nyonya" {{ old('race', $participant->race) == 'Peranakan/Baba Nyonya' ? 'selected' : '' }}>Peranakan/Baba Nyonya</option>
                                <option value="Chitty" {{ old('race', $participant->race) == 'Chitty' ? 'selected' : '' }}>Chitty</option>
                                <option value="Lain-lain Warganegara" {{ old('race', $participant->race) == 'Lain-lain Warganegara' ? 'selected' : '' }}>Lain-lain Warganegara</option>
                            </select>
                        </div>
                    </div>

                    <!-- Status moved below -->
                    <div class="grid grid-cols-1 gap-4 mt-4">
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
                                <select name="status" id="status" class="w-full text-xs border-gray-300 rounded-[1px] pl-12 focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50">
                                    <option value="active" {{ old('status', $participant->status) == 'active' ? 'selected' : '' }}>Active</option>
                                    <option value="inactive" {{ old('status', $participant->status) == 'inactive' ? 'selected' : '' }}>Inactive</option>
                                </select>
                            </div>
                            <p class="mt-1 text-[10px] text-gray-500">Inactive participants cannot be registered for events</p>
                        </div>
                    </div>
                </div>
                <!-- Event Information -->
                <div class="border-b border-gray-200 pb-5">
                    <h2 class="text-sm font-semibold text-gray-700 mb-4">Event Information</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <!-- Event Name -->
                        <div>
                            <label for="event_id" class="flex items-center text-xs font-medium text-gray-700 mb-1">
                                <span class="material-icons text-sm mr-1 text-primary-DEFAULT">event</span>
                                Event Name
                            </label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <span class="material-icons text-[#004aad] text-base">event_note</span>
                                </div>
                                <select name="event_id" id="event_id" class="w-full text-xs border-gray-300 rounded-[1px] pl-12 focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50" required>
                                    <option value="">-- Select Event --</option>
                                    @foreach($events as $event)
                                        <option value="{{ $event->id }}" {{ old('event_id', $participant->event_id) == $event->id ? 'selected' : '' }}>{{ $event->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <p class="mt-1 text-[10px] text-gray-500">Select the event this participant will attend</p>
                        </div>
                        <!-- Event Organizer (auto-filled) -->
                        <div>
                            <label for="event_organizer" class="flex items-center text-xs font-medium text-gray-700 mb-1">
                                <span class="material-icons text-sm mr-1 text-primary-DEFAULT">people</span>
                                Event Organizer
                            </label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <span class="material-icons text-[#004aad] text-base">groups</span>
                                </div>
                                <input type="text" id="event_organizer" class="w-full text-xs border-gray-300 bg-gray-50 rounded-[1px] pl-12 py-2 border" value="{{ $participant->event ? $participant->event->organizer : '' }}" readonly>
                            </div>
                            <p class="mt-1 text-[10px] text-gray-500">Organizer will be auto-filled based on event</p>
                        </div>
                    </div>
                </div>
                <!-- Registration Information -->
                <div class="border-b border-gray-200 pb-5">
                    <h2 class="text-sm font-semibold text-gray-700 mb-4">Registration Information</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <!-- Registration Date (auto-filled) -->
                        <div>
                            <label for="registration_date" class="flex items-center text-xs font-medium text-gray-700 mb-1">
                                <span class="material-icons text-sm mr-1 text-primary-DEFAULT">event_available</span>
                                Registration Date
                            </label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <span class="material-icons text-[#004aad] text-base">calendar_today</span>
                                </div>
                                <input type="text" name="registration_date" id="registration_date" class="w-full text-xs border-gray-300 bg-gray-50 rounded-[1px] pl-12 py-2 border" value="{{ old('registration_date', $participant->registration_date ? $participant->registration_date->format('d M Y - H:i') : '') }}" readonly>
                            </div>
                            <p class="mt-1 text-[10px] text-gray-500">Date and time of registration (auto-filled)</p>
                        </div>
                        <!-- Attendance Date (optional) -->
                        <div>
                            <label for="attendance_date" class="flex items-center text-xs font-medium text-gray-700 mb-1">
                                <span class="material-icons text-sm mr-1 text-primary-DEFAULT">how_to_reg</span>
                                Attendance Date
                            </label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <span class="material-icons text-[#004aad] text-base">fact_check</span>
                                </div>
                                <input type="text" name="attendance_date" id="attendance_date" class="w-full text-xs border-gray-300 bg-gray-50 rounded-[1px] pl-12 py-2 border" value="{{ old('attendance_date', $participant->attendance_date ? $participant->attendance_date->format('d M Y - H:i') : '') }}" placeholder="(Optional)">
                            </div>
                            <p class="mt-1 text-[10px] text-gray-500">Date and time of attendance (if available)</p>
                        </div>
                    </div>
                </div>
                <!-- Notes -->
                <div>
                    <h2 class="text-sm font-semibold text-gray-700 mb-4">Notes</h2>
                    <textarea name="notes" id="notes" rows="3" class="w-full text-xs border-gray-300 bg-gray-50 rounded border focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50" placeholder="Any additional notes...">{{ old('notes', $participant->notes) }}</textarea>
                    <p class="mt-1 text-[10px] text-gray-500">Any additional information or special requirements</p>
                </div>
                <div class="border-t border-gray-200 pt-4 mt-6 flex justify-end space-x-3">
                    <a href="{{ route('participants') }}" class="px-3 py-1 bg-gradient-to-r from-red-600 to-red-500 hover:from-red-700 hover:to-red-600 text-white rounded shadow-sm text-xs font-medium transition-colors duration-200 ease-in-out flex items-center">
                        <span class="material-icons text-xs mr-1">cancel</span>
                        Cancel
                    </a>
                    <button type="submit" class="px-3 py-1 bg-gradient-to-r from-blue-600 to-blue-500 hover:from-blue-700 hover:to-blue-600 text-white rounded shadow-sm text-xs font-medium transition-colors duration-200 ease-in-out flex items-center">
                        <span class="material-icons text-xs mr-1">save</span>
                        Update Participant
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
<script>
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

// Initialize fields based on selected value
document.addEventListener('DOMContentLoaded', function() {
    toggleIdFields();
    
    // If there was a validation error, we need to re-show the correct field
    const idType = document.getElementById('id_type').value;
    if (idType) {
        toggleIdFields();
    }
    
    // Setup state dropdown
    const stateSelect = document.getElementById('state');
    
    if (stateSelect) {
        // Load states immediately
        try {
            loadStates();
            
            // Ensure we initialize the state dropdown with its manual fields if needed
            if (stateSelect.value === 'others' || !stateSelect.value && '{{ $participant->address_state }}') {
                // If state is "others" or state is empty but we have a value for address_state,
                // we should show the manual fields
                const manualFields = document.getElementById('manual-address-fields');
                if (manualFields) {
                    // Force selection of "others" after states load
                    const checkStateLoaded = setInterval(function() {
                        if (stateSelect.options.length > 1) {
                            // Check if the state value exists in the dropdown options
                            let stateExists = false;
                            for (let i = 0; i < stateSelect.options.length; i++) {
                                if (stateSelect.options[i].value === '{{ $participant->address_state }}') {
                                    stateExists = true;
                                    stateSelect.value = '{{ $participant->address_state }}';
                                    break;
                                }
                            }
                            
                            // If state doesn't exist in dropdown, select "others"
                            if (!stateExists && '{{ $participant->address_state }}') {
                                stateSelect.value = 'others';
                            }
                            
                            // Manually trigger the state change handler
                            handleStateChange();
                            clearInterval(checkStateLoaded);
                        }
                    }, 100);
                }
            }
        } catch (e) {
            console.error('Error handling initial state:', e);
        }
    }
});

// When the form is submitted, combine address fields
document.addEventListener('DOMContentLoaded', function() {
document.querySelector('form').addEventListener('submit', function() {
        // No longer needed as address is not combined
    });
});
</script> 