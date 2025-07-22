@extends('layouts.event-registration')

@section('content')
<div class="min-h-screen bg-gray-50 py-6 px-3 sm:px-4">
    <div class="max-w-6xl mx-auto">
        <!-- Event Header -->
        <div class="bg-white shadow rounded-lg overflow-hidden mb-6">
            <div class="bg-gradient-to-r from-blue-500 to-indigo-600 px-4 py-3">
                <div class="flex items-center">
                    <div class="flex-shrink-0 mr-3">
                        <span class="material-icons text-white text-xl">event</span>
                    </div>
                    <h1 class="text-white text-lg font-semibold leading-tight">
                        {{ $event->name }}
                    </h1>
                </div>
            </div>
        </div>
        
        <div class="flex flex-col md:flex-row gap-6">
            <!-- Registration Form - Left Side -->
            <div class="w-full md:w-1/2">
                <div class="bg-white shadow rounded-lg overflow-hidden mb-6">
                    <div class="bg-gradient-to-r from-green-500 to-green-600 px-4 py-3">
                        <h2 class="text-white text-base font-semibold flex items-center">
                            <span class="material-icons text-white text-sm mr-2">app_registration</span>
                            Registration Form
                        </h2>
                    </div>
                    
                    <!-- Registration Timeline -->
                    <div class="px-4 py-3 bg-gray-50 border-b">
                        <div class="flex items-center text-xs">
                            <div class="flex-1 text-center">
                                <div class="w-6 h-6 rounded-full bg-green-500 text-white flex items-center justify-center mx-auto">
                                    <span class="material-icons text-xs">person</span>
                                </div>
                                <p class="mt-1 font-medium text-gray-700">Personal Info</p>
                            </div>
                            <div class="w-12 h-1 bg-gray-300"></div>
                            <div class="flex-1 text-center">
                                <div class="w-6 h-6 rounded-full bg-gray-300 text-white flex items-center justify-center mx-auto">
                                    <span class="material-icons text-xs">check_circle</span>
                                </div>
                                <p class="mt-1 font-medium text-gray-500">Confirmation</p>
                            </div>
                            <div class="w-12 h-1 bg-gray-300"></div>
                            <div class="flex-1 text-center">
                                <div class="w-6 h-6 rounded-full bg-gray-300 text-white flex items-center justify-center mx-auto">
                                    <span class="material-icons text-xs">done_all</span>
                                </div>
                                <p class="mt-1 font-medium text-gray-500">Complete</p>
                            </div>
                        </div>
                    </div>

                    <div class="p-4">
                        @if(session('success'))
                        <div class="bg-green-50 border border-green-200 text-green-700 px-3 py-2 rounded mb-4 flex items-center text-xs">
                            <span class="material-icons text-sm mr-2">check_circle</span>
                            {{ session('success') }}
                        </div>
                        @endif

                        @if(session('error'))
                        <div class="bg-red-50 border border-red-200 text-red-700 px-3 py-2 rounded mb-4 flex items-center text-xs">
                            <span class="material-icons text-sm mr-2">error</span>
                            {{ session('error') }}
                        </div>
                        @endif

                        <form method="POST" action="{{ route('event.register.submit', $event->registration_link) }}" class="space-y-4">
                            @csrf
                            <input type="hidden" name="registration_type" value="new">
                            
                            <!-- Name -->
                            <div>
                                <label for="name" class="block text-xs font-medium text-gray-700 mb-1 flex items-center">
                                    <span class="material-icons text-blue-600 mr-1 text-sm">person</span>
                                    Full Name
                                </label>
                                <input type="text" name="name" id="name" 
                                    class="w-full px-3 py-2 text-xs border border-gray-300 rounded focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500 transition-colors" 
                                    required value="{{ old('name') }}"
                                    placeholder="Enter your full name">
                                @error('name')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                            
                            <!-- Email -->
                            <div>
                                <label for="email" class="block text-xs font-medium text-gray-700 mb-1 flex items-center">
                                    <span class="material-icons text-blue-600 mr-1 text-sm">email</span>
                                    Email Address
                                </label>
                                <input type="email" name="email" id="email" 
                                    class="w-full px-3 py-2 text-xs border border-gray-300 rounded focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500 transition-colors" 
                                    required value="{{ old('email') }}"
                                    placeholder="your.email@example.com">
                                @error('email')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                            
                            <!-- Phone -->
                            <div>
                                <label for="phone" class="block text-xs font-medium text-gray-700 mb-1 flex items-center">
                                    <span class="material-icons text-blue-600 mr-1 text-sm">phone</span>
                                    Phone Number
                                </label>
                                <input type="tel" name="phone" id="phone" 
                                    class="w-full px-3 py-2 text-xs border border-gray-300 rounded focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500 transition-colors" 
                                    value="{{ old('phone') }}"
                                    placeholder="+60 12-345 6789">
                                @error('phone')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                            
                            <!-- Identity Card / Passport No. -->
                            <div>
                                <label for="id_type" class="block text-xs font-medium text-gray-700 mb-1 flex items-center">
                                    <span class="material-icons text-blue-600 mr-1 text-sm">badge</span>
                                    Identity Card / Passport No.
                                </label>
                                <div class="mb-2">
                                    <select name="id_type" id="id_type" 
                                        class="w-full px-3 py-2 text-xs border border-gray-300 rounded focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500 transition-colors" 
                                        onchange="toggleIdFields()">
                                        <option value="">-- Select IC / Passport --</option>
                                        <option value="ic">Identity Card</option>
                                        <option value="passport">Passport</option>
                                    </select>
                                </div>
                                
                                <div id="ic_field" class="hidden">
                                    <div class="relative">
                                        <div class="absolute inset-y-0 left-0 flex items-center pl-2 pointer-events-none">
                                            <span class="material-icons text-gray-400 text-xs">credit_card</span>
                                        </div>
                                        <input type="text" name="organization_ic" id="organization_ic" 
                                            class="w-full pl-7 px-3 py-2 text-xs border border-gray-300 rounded focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500 transition-colors"
                                            placeholder="000000-00-0000">
                                    </div>
                                </div>
                                
                                <div id="passport_field" class="hidden">
                                    <div class="relative">
                                        <div class="absolute inset-y-0 left-0 flex items-center pl-2 pointer-events-none">
                                            <span class="material-icons text-gray-400 text-xs">book</span>
                                        </div>
                                        <input type="text" name="organization_passport" id="organization_passport" 
                                            class="w-full pl-7 px-3 py-2 text-xs border border-gray-300 rounded focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500 transition-colors"
                                            placeholder="Enter passport number">
                                    </div>
                                </div>
                                
                                <!-- Hidden field to store the combined value -->
                                <input type="hidden" name="organization" id="organization">
                                <p class="text-[10px] text-gray-500 mt-1">This field is optional</p>
                            </div>
                            
                            <!-- Notes -->
                            <div>
                                <label for="notes" class="block text-xs font-medium text-gray-700 mb-1 flex items-center">
                                    <span class="material-icons text-blue-600 mr-1 text-sm">notes</span>
                                    Additional Notes
                                </label>
                                <textarea name="notes" id="notes" rows="3"
                                    class="w-full px-3 py-2 text-xs border border-gray-300 rounded focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500 transition-colors"
                                    placeholder="Any additional information you want to provide">{{ old('notes') }}</textarea>
                            </div>
                            
                            <div>
                                <button type="submit" 
                                    class="w-full py-2 px-3 bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 text-white text-xs font-semibold rounded shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-all flex items-center justify-center">
                                    <span class="material-icons mr-1 text-sm">how_to_reg</span>
                                    Register for Event
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            
            <!-- Event Details - Right Side -->
            <div class="w-full md:w-1/2">
                <div class="bg-white shadow rounded-lg overflow-hidden">
                    <div class="bg-gradient-to-r from-blue-500 to-indigo-600 px-4 py-3">
                        <h2 class="text-white text-base font-semibold flex items-center">
                            <span class="material-icons text-white text-sm mr-2">info</span>
                            Event Information
                        </h2>
                    </div>
                    
                    <div class="p-4">
                        <!-- Event Timeline -->
                        <div class="relative pb-10">
                            <!-- Timeline Line -->
                            <div class="absolute h-full w-1 bg-blue-100 left-3.5"></div>
                            
                            <!-- Date & Time -->
                            <div class="relative flex items-start mb-6">
                                <div class="flex-shrink-0 w-8 h-8 rounded-full bg-blue-500 text-white flex items-center justify-center z-10">
                                    <span class="material-icons text-sm">event</span>
                                </div>
                                <div class="ml-4">
                                    <h3 class="text-xs font-semibold text-gray-800">Date & Time</h3>
                                    <div class="mt-1 text-xs text-gray-600">
                                        <div class="flex items-center">
                                            <span class="material-icons text-blue-500 text-xs mr-1">calendar_today</span>
                                            <span>Start: {{ \Carbon\Carbon::parse($event->start_date)->format('d M Y') }}
                                            {{ $event->start_time ? '- ' . substr($event->start_time, 0, 5) : '' }}</span>
                                        </div>
                                        <div class="flex items-center mt-1">
                                            <span class="material-icons text-blue-500 text-xs mr-1">event_busy</span>
                                            <span>End: {{ \Carbon\Carbon::parse($event->end_date)->format('d M Y') }}
                                            {{ $event->end_time ? '- ' . substr($event->end_time, 0, 5) : '' }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Location -->
                            <div class="relative flex items-start mb-6">
                                <div class="flex-shrink-0 w-8 h-8 rounded-full bg-green-500 text-white flex items-center justify-center z-10">
                                    <span class="material-icons text-sm">location_on</span>
                                </div>
                                <div class="ml-4">
                                    <h3 class="text-xs font-semibold text-gray-800">Location</h3>
                                    <div class="mt-1 text-xs text-gray-600">
                                        <p>{{ $event->location }}</p>
                                        @if ($event->address)
                                            <p class="mt-1">{{ $event->address }}</p>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Organizer -->
                            <div class="relative flex items-start mb-6">
                                <div class="flex-shrink-0 w-8 h-8 rounded-full bg-purple-500 text-white flex items-center justify-center z-10">
                                    <span class="material-icons text-sm">groups</span>
                                </div>
                                <div class="ml-4">
                                    <h3 class="text-xs font-semibold text-gray-800">Organizer</h3>
                                    <div class="mt-1 text-xs text-gray-600">
                                        <p>{{ $event->organizer }}</p>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Contact Information -->
                            <div class="relative flex items-start">
                                <div class="flex-shrink-0 w-8 h-8 rounded-full bg-red-500 text-white flex items-center justify-center z-10">
                                    <span class="material-icons text-sm">contact_phone</span>
                                </div>
                                <div class="ml-4">
                                    <h3 class="text-xs font-semibold text-gray-800">Contact Information</h3>
                                    <div class="mt-1 text-xs text-gray-600 space-y-1">
                                        @if ($event->contact_person)
                                            <div class="flex items-center">
                                                <span class="material-icons text-red-500 text-xs mr-1">person</span>
                                                <span>{{ $event->contact_person }}</span>
                                            </div>
                                        @endif
                                        @if ($event->contact_email)
                                            <div class="flex items-center">
                                                <span class="material-icons text-red-500 text-xs mr-1">email</span>
                                                <span>{{ $event->contact_email }}</span>
                                            </div>
                                        @endif
                                        @if ($event->contact_phone)
                                            <div class="flex items-center">
                                                <span class="material-icons text-red-500 text-xs mr-1">phone</span>
                                                <span>{{ $event->contact_phone }}</span>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    function toggleIdFields() {
        const idType = document.getElementById('id_type').value;
        const icField = document.getElementById('ic_field');
        const passportField = document.getElementById('passport_field');
        
        // Hide both fields first
        icField.classList.add('hidden');
        passportField.classList.add('hidden');
        
        // Show the appropriate field based on selection
        if (idType === 'ic') {
            icField.classList.remove('hidden');
        } else if (idType === 'passport') {
            passportField.classList.remove('hidden');
        }
    }
    
    // Format IC number as 000000-00-0000
    document.getElementById('organization_ic').addEventListener('input', function(e) {
        let value = e.target.value.replace(/[^\d]/g, '').substring(0, 12);
        if (value.length > 6) {
            value = value.substring(0, 6) + '-' + value.substring(6);
        }
        if (value.length > 9) {
            value = value.substring(0, 9) + '-' + value.substring(9);
        }
        e.target.value = value;
    });
    
    document.querySelector('form').addEventListener('submit', function() {
        const idType = document.getElementById('id_type').value;
        
        if (idType === 'ic') {
            document.getElementById('organization').value = 'IC: ' + document.getElementById('organization_ic').value;
        } else if (idType === 'passport') {
            document.getElementById('organization').value = 'Passport: ' + document.getElementById('organization_passport').value;
        } else {
            document.getElementById('organization').value = '';
        }
    });
</script>
@endsection 