<x-app-layout>
    <x-slot name="breadcrumb">
        <span>Participants</span>
        <span class="mx-2 text-gray-500">/</span>
        <span>Add New Participant</span>
    </x-slot>

    <x-slot name="title">Add New Participant</x-slot>

    <div class="bg-white rounded shadow-md border border-gray-300">
        <div class="p-6 border-b border-gray-200">
            <div class="flex items-center">
                <span class="material-icons mr-2 text-primary-DEFAULT">person_add</span>
                <h1 class="text-xl font-bold text-gray-800">Add New Participant</h1>
            </div>
            <p class="text-xs text-gray-500 mt-1 ml-8">Fill in the details to add a new participant</p>
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
            <form method="POST" action="{{ route('participants.store') }}" class="space-y-6">
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
                                <input type="text" name="name" id="name" class="w-full text-xs border-gray-300 rounded-[1px] pl-12 focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50" value="{{ old('name') }}" required>
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
                                <input type="email" name="email" id="email" class="w-full text-xs border-gray-300 rounded-[1px] pl-12 focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50" value="{{ old('email') }}" required>
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
                            <div class="relative">
                                <input type="tel" name="phone" id="phone" class="phone-input w-full h-9 text-xs border-gray-300 rounded-[1px] px-3 py-1.5 focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50" value="{{ old('phone') }}">
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
                                <select name="id_type" id="id_type" class="w-full h-9 text-xs border-gray-300 rounded-[1px] focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50 text-left leading-[1rem]" onchange="toggleIdFields()">
                                    <option value="">-- Select IC / Passport --</option>
                                    <option value="ic" {{ old('id_type') == 'ic' ? 'selected' : '' }}>Identity Card</option>
                                    <option value="passport" {{ old('id_type') == 'passport' ? 'selected' : '' }}>Passport</option>
                                </select>
                            </div>
                            <div id="ic_field" class="relative hidden">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <span class="material-icons text-[#004aad] text-base">assignment_ind</span>
                                </div>
                                <input type="text" name="identity_card" id="organization_ic" placeholder="000000-00-0000" class="w-full text-xs border-gray-300 rounded-[1px] pl-12 focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50" value="{{ old('identity_card') }}" maxlength="14" oninput="formatIC(this)">
                            </div>
                            <div id="passport_field" class="relative hidden">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <span class="material-icons text-[#004aad] text-base">assignment_ind</span>
                                </div>
                                <input type="text" name="passport_no" id="organization_passport" placeholder="A00000000" class="w-full text-xs border-gray-300 rounded-[1px] pl-12 focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50" value="{{ old('passport_no') }}">
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
                                <input type="date" name="date_of_birth" id="date_of_birth" class="w-full text-xs border-gray-300 rounded-[1px] pl-12 focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50" value="{{ old('date_of_birth') }}">
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
                            
                            <!-- Address 1 and Address 2 in one row (2 columns) -->
                            <div class="grid grid-cols-2 gap-2 mb-2">
                                <!-- Address 1 -->
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <span class="material-icons text-[#004aad] text-base">location_on</span>
                                    </div>
                                    <input type="text" name="address1" id="address1" placeholder="Address Line 1" 
                                        class="w-full text-xs border-gray-300 rounded-[1px] pl-12 focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50" 
                                        value="{{ old('address1') }}">
                                </div>
                                
                                <!-- Address 2 -->
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <span class="material-icons text-[#004aad] text-base">location_on</span>
                                    </div>
                                    <input type="text" name="address2" id="address2" placeholder="Address Line 2" 
                                        class="w-full text-xs border-gray-300 rounded-[1px] pl-12 focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50" 
                                        value="{{ old('address2') }}">
                                </div>
                            </div>
                            
                            <!-- State, City, Postcode, Country in one row (4 columns) -->
                            <div class="grid grid-cols-4 gap-2">
                                <!-- State -->
                                <div>
                                    <label for="state" class="block text-xs font-medium text-gray-700 mb-1">State</label>
                                    <select name="state" id="state" 
                                        class="w-full h-9 text-xs border-gray-300 rounded-[1px] focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50 text-left leading-[1rem]"
                                        onchange="handleStateChange()">
                                        <option value="">-- Select State --</option>
                                    </select>
                                </div>
                                
                                <!-- City -->
                                <div>
                                    <label for="city" class="block text-xs font-medium text-gray-700 mb-1">City</label>
                                    <select name="city" id="city" 
                                        class="w-full h-9 text-xs border-gray-300 rounded-[1px] focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50 text-left leading-[1rem]" disabled>
                                        <option value="">-- Select City --</option>
                                    </select>
                                </div>
                                
                                <!-- Postcode -->
                                <div>
                                    <label for="postcode" class="block text-xs font-medium text-gray-700 mb-1">Postcode</label>
                                    <select name="postcode" id="postcode" 
                                        class="w-full h-9 text-xs border-gray-300 rounded-[1px] focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50 text-left leading-[1rem]" disabled>
                                        <option value="">-- Select Postcode --</option>
                                    </select>
                                </div>
                                
                                <!-- Country -->
                                <div>
                                    <label for="country" class="block text-xs font-medium text-gray-700 mb-1">Country</label>
                                    <select name="country" id="country" 
                                        class="w-full h-9 text-xs border-gray-300 rounded-[1px] focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50 text-left leading-[1rem]">
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
                                                placeholder="Enter state manually">
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
                                                placeholder="Enter city manually">
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
                                <select name="gender" id="gender" class="w-full h-9 text-xs border-gray-300 rounded-[1px] pl-12 focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50 text-left leading-[1rem]">
                                    <option value="">-- Select Gender --</option>
                                    <option value="male" {{ old('gender') == 'male' ? 'selected' : '' }}>Male</option>
                                    <option value="female" {{ old('gender') == 'female' ? 'selected' : '' }}>Female</option>
                                    <option value="other" {{ old('gender') == 'other' ? 'selected' : '' }}>Other</option>
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
                                <input type="text" name="organization" id="organization" class="w-full text-xs border-gray-300 rounded-[1px] pl-12 focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50" value="{{ old('organization') }}">
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
                                <input type="text" name="job_title" id="job_title" class="w-full text-xs border-gray-300 rounded-[1px] pl-12 focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50" value="{{ old('job_title') }}">
                            </div>
                            <p class="mt-1 text-[10px] text-gray-500">Current position or role</p>
                        </div>
                        <!-- Race (Bangsa) -->
                        <div>
                            <label for="race" class="flex items-center text-xs font-medium text-gray-700 mb-1">
                                <span class="material-icons text-sm mr-1 text-primary-DEFAULT">diversity_1</span>
                                Race (Bangsa)
                            </label>
                            <select name="race" id="race" class="w-full h-9 text-xs border-gray-300 rounded-[1px] focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50 text-left leading-[1rem]">
                                <option value="">-- Select Race --</option>
                                <option value="Melayu (Semenanjung)">Melayu (Semenanjung)</option>
                                <option value="Melayu (Sarawak)">Melayu (Sarawak)</option>
                                <option value="Melayu (Sabah)">Melayu (Sabah)</option>
                                <option value="Cina Hokkien">Cina Hokkien</option>
                                <option value="Cina Kantonis">Cina Kantonis</option>
                                <option value="Cina Hakka">Cina Hakka</option>
                                <option value="Cina Teochew">Cina Teochew</option>
                                <option value="Cina Foochow">Cina Foochow</option>
                                <option value="Cina Hainan">Cina Hainan</option>
                                <option value="Cina Kwongsai">Cina Kwongsai</option>
                                <option value="Cina Henghua">Cina Henghua</option>
                                <option value="Cina lain-lain">Cina lain-lain</option>
                                <option value="India Tamil">India Tamil</option>
                                <option value="India Punjabi">India Punjabi</option>
                                <option value="India Malayalee">India Malayalee</option>
                                <option value="India Telugu">India Telugu</option>
                                <option value="India Gujerati">India Gujerati</option>
                                <option value="India Bengali">India Bengali</option>
                                <option value="India lain-lain">India lain-lain</option>
                                <option value="Iban">Iban</option>
                                <option value="Kadazan">Kadazan</option>
                                <option value="Dusun">Dusun</option>
                                <option value="Bajau">Bajau</option>
                                <option value="Sama">Sama</option>
                                <option value="Bidayuh">Bidayuh</option>
                                <option value="Melanau">Melanau</option>
                                <option value="Murut">Murut</option>
                                <option value="Orang Ulu Kayan">Orang Ulu Kayan</option>
                                <option value="Orang Ulu Kenyah">Orang Ulu Kenyah</option>
                                <option value="Orang Ulu Kelabit">Orang Ulu Kelabit</option>
                                <option value="Orang Ulu Penan">Orang Ulu Penan</option>
                                <option value="Orang Ulu Lun Bawang">Orang Ulu Lun Bawang</option>
                                <option value="Orang Ulu (lain-lain)">Orang Ulu (lain-lain)</option>
                                <option value="Orang Asli Temuan">Orang Asli Temuan</option>
                                <option value="Orang Asli Semai">Orang Asli Semai</option>
                                <option value="Orang Asli Jakun">Orang Asli Jakun</option>
                                <option value="Orang Asli Mah Meri">Orang Asli Mah Meri</option>
                                <option value="Orang Asli Negrito (Kensiu)">Orang Asli Negrito (Kensiu)</option>
                                <option value="Orang Asli Negrito (Kintaq)">Orang Asli Negrito (Kintaq)</option>
                                <option value="Orang Asli Negrito (Jahai)">Orang Asli Negrito (Jahai)</option>
                                <option value="Orang Asli Negrito (Lanoh)">Orang Asli Negrito (Lanoh)</option>
                                <option value="Orang Asli Negrito (Mendriq)">Orang Asli Negrito (Mendriq)</option>
                                <option value="Orang Asli Negrito (Batek)">Orang Asli Negrito (Batek)</option>
                                <option value="Orang Asli Senoi (Temiar)">Orang Asli Senoi (Temiar)</option>
                                <option value="Orang Asli Senoi (Semaq Beri)">Orang Asli Senoi (Semaq Beri)</option>
                                <option value="Orang Asli Senoi (Jah Hut)">Orang Asli Senoi (Jah Hut)</option>
                                <option value="Orang Asli Senoi (Che Wong)">Orang Asli Senoi (Che Wong)</option>
                                <option value="Orang Asli Proto-Malay (Temuan)">Orang Asli Proto-Malay (Temuan)</option>
                                <option value="Orang Asli Proto-Malay (Semelai)">Orang Asli Proto-Malay (Semelai)</option>
                                <option value="Orang Asli Proto-Malay (Jakun)">Orang Asli Proto-Malay (Jakun)</option>
                                <option value="Orang Asli Proto-Malay (Kanaq)">Orang Asli Proto-Malay (Kanaq)</option>
                                <option value="Orang Asli Proto-Malay (Seletar)">Orang Asli Proto-Malay (Seletar)</option>
                                <option value="Orang Asli (lain-lain)">Orang Asli (lain-lain)</option>
                                <option value="Sungai">Sungai</option>
                                <option value="Rungus">Rungus</option>
                                <option value="Lundayeh">Lundayeh</option>
                                <option value="Kedayan">Kedayan</option>
                                <option value="Bisaya">Bisaya</option>
                                <option value="Brunei">Brunei</option>
                                <option value="Bugis">Bugis</option>
                                <option value="Jawa">Jawa</option>
                                <option value="Banjar">Banjar</option>
                                <option value="Kristang/Serani">Kristang/Serani</option>
                                <option value="Sikh">Sikh</option>
                                <option value="Thai">Thai</option>
                                <option value="Peranakan/Baba Nyonya">Peranakan/Baba Nyonya</option>
                                <option value="Chitty">Chitty</option>
                                <option value="Lain-lain Warganegara">Lain-lain Warganegara</option>
                            </select>
                        </div>
                    </div>
                </div>
                
                <!-- Event Information -->
                <div class="border-b border-gray-200 pb-5">
                    <h2 class="text-sm font-semibold text-gray-700 mb-4">Event Information</h2>
                    <div class="grid grid-cols-2 gap-4">
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
                                <select name="event_id" id="event_id" class="w-full h-9 text-xs border-gray-300 rounded-[1px] pl-12 focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50 text-left leading-[1rem]" required>
                                    <option value="">-- Select Event --</option>
                                    @foreach($events as $event)
                                        <option value="{{ $event->id }}" {{ old('event_id') == $event->id ? 'selected' : '' }}>{{ $event->name }}</option>
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
                                <input type="text" id="event_organizer" class="w-full text-xs border-gray-300 bg-gray-50 rounded-[1px] pl-12 py-2 border" value="" readonly>
                            </div>
                            <p class="mt-1 text-[10px] text-gray-500">Organizer will be auto-filled based on event</p>
                        </div>
                    </div>
                </div>
                
                <!-- Notes -->
                <div>
                    <label for="notes" class="block text-xs font-medium text-gray-700 mb-1">Notes</label>
                    <textarea id="notes" name="notes" rows="3" class="w-full text-xs border-gray-300 rounded focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50" placeholder="Any additional information about this participant">{{ old('notes') }}</textarea>
                    <p class="mt-1 text-[10px] text-gray-500">Internal notes about this participant (not visible to them)</p>
                </div>
                
                <!-- Form Actions -->
                <div class="border-t border-gray-200 pt-4 mt-6 flex justify-end space-x-3">
                    <a href="{{ route('participants') }}" class="px-3 py-1 bg-gradient-to-r from-red-600 to-red-500 hover:from-red-700 hover:to-red-600 text-white rounded-[1px] shadow-sm text-xs font-medium transition-colors duration-200 ease-in-out flex items-center">
                        <span class="material-icons text-xs mr-1">cancel</span>
                        Cancel
                    </a>
                    <button type="submit" class="px-3 py-1 bg-gradient-to-r from-blue-600 to-blue-500 hover:from-blue-700 hover:to-blue-600 text-white rounded-[1px] shadow-sm text-xs font-medium transition-colors duration-200 ease-in-out flex items-center">
                        <span class="material-icons text-xs mr-1">save</span>
                        Save Participant
                    </button>
                </div>
            </form>
        </div>
    </div>

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
        
        // When the form is submitted, combine address fields
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelector('form').addEventListener('submit', function() {
                combineAddress();
            });
            
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
</script> 
</x-app-layout> 