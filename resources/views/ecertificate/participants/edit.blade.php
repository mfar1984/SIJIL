<x-app-layout>
    <x-slot name="breadcrumb">
        <span>PWA Management</span>
        <span class="mx-2 text-gray-500">/</span>
        <a href="{{ route('pwa.participants') }}" class="text-indigo-600 hover:text-indigo-800">Participants</a>
        <span class="mx-2 text-gray-500">/</span>
        <a href="{{ route('pwa.participants.show', $participant) }}" class="text-indigo-600 hover:text-indigo-800">{{ $participant->name }}</a>
        <span class="mx-2 text-gray-500">/</span>
        <span>Edit</span>
    </x-slot>
    <x-slot name="title">Edit PWA Participant</x-slot>

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
                <span class="material-icons-outlined mr-2 text-primary-DEFAULT">edit</span>
                <h1 class="text-xl font-bold text-gray-800">Edit PWA Participant: {{ $participant->name }}</h1>
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
            <form method="POST" action="{{ route('pwa.participants.update', $participant) }}" class="space-y-2">@method('PUT')
                @csrf
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
                                <label for="name" class="text-xs font-medium text-gray-700 md:w-48 shrink-0 flex items-center gap-1">
                                    Full Name <span class="text-red-500">*</span>
                                    <div class="tooltip-wrapper" x-data="{ show: false }">
                                        <span class="material-icons-outlined text-gray-400 text-sm cursor-help" 
                                              @mouseenter="show = true" 
                                              @mouseleave="show = false">
                                            help_outline
                                        </span>
                                        <div x-show="show" x-transition class="tooltip-content">
                                            Enter participant's full name as it appears on official documents
                                        </div>
                                    </div>
                                </label>
                                <div class="flex-1">
                                    <div class="relative">
                                        <input type="text" name="name" id="name" class="w-full h-9 text-xs border-gray-300 rounded px-3 focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50" value="{{ old('name', $participant->name) }}" required>
                                    </div>
                                </div>
                            </div>

                            <!-- Email -->
                            <div class="flex flex-col md:flex-row md:items-center gap-3">
                                <label for="email" class="text-xs font-medium text-gray-700 md:w-48 shrink-0 flex items-center gap-1">
                                    Email Address <span class="text-red-500">*</span>
                                    <div class="tooltip-wrapper" x-data="{ show: false }">
                                        <span class="material-icons-outlined text-gray-400 text-sm cursor-help" 
                                              @mouseenter="show = true" 
                                              @mouseleave="show = false">
                                            help_outline
                                        </span>
                                        <div x-show="show" x-transition class="tooltip-content">
                                            This email will be used for notifications and communications
                                        </div>
                                    </div>
                                </label>
                                <div class="flex-1">
                                    <div class="relative">
                                        <input type="email" name="email" id="email" class="w-full h-9 text-xs border-gray-300 rounded px-3 focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50" value="{{ old('email', $participant->email) }}" required>
                                    </div>
                                </div>
                            </div>

                            <!-- Phone -->
                            <div class="flex flex-col md:flex-row md:items-center gap-3">
                                <label for="phone" class="text-xs font-medium text-gray-700 md:w-48 shrink-0 flex items-center gap-1">
                                    Phone Number
                                    <div class="tooltip-wrapper" x-data="{ show: false }">
                                        <span class="material-icons-outlined text-gray-400 text-sm cursor-help" 
                                              @mouseenter="show = true" 
                                              @mouseleave="show = false">
                                            help_outline
                                        </span>
                                        <div x-show="show" x-transition class="tooltip-content">
                                            Select country code and enter phone number
                                        </div>
                                    </div>
                                </label>
                                <div class="flex-1">
                                    <input type="tel" name="phone" id="phone" class="phone-input w-full text-xs border-gray-300 rounded focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50" value="{{ old('phone', preg_match('/^60/', $participant->phone) ? substr($participant->phone, 2) : $participant->phone) }}">
                                </div>
                            </div>

                            {{--
                                The read-only username display was removed. update() never wrote to
                                the column and sign-in is by email address, so the field showed a
                                value that looked like a credential but was not one.
                            --}}

                            <!-- Organization -->
                            <div class="flex flex-col md:flex-row md:items-center gap-3">
                                <label for="organization" class="text-xs font-medium text-gray-700 md:w-48 shrink-0">
                                    Organization
                                </label>
                                <div class="flex-1">
                                    <input type="text" name="organization" id="organization" class="w-full h-9 text-xs border-gray-300 rounded focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50" value="{{ old('organization', $participant->organization) }}">
                                </div>
                            </div>

                            <!-- IC/Passport (dropdown + toggle fields) -->
                            <div class="flex flex-col md:flex-row md:items-start gap-3">
                                <label for="id_type" class="text-xs font-medium text-gray-700 md:w-48 shrink-0 pt-2">
                                    Identity Card / Passport No.
                                </label>
                                <div class="flex-1">
                                    <div class="mb-2">
                                        <select name="id_type" id="id_type" class="w-full h-9 text-xs border-gray-300 rounded focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50" onchange="toggleIdFields()">
                                            <option value="">-- Select IC / Passport --</option>
                                            <option value="ic" {{ old('id_type', ($participant->identity_card ? 'ic' : ($participant->id_passport && stripos($participant->id_passport, '-') !== false ? 'ic' : ''))) == 'ic' ? 'selected' : '' }}>Identity Card</option>
                                            <option value="passport" {{ old('id_type', ($participant->passport_no ? 'passport' : ($participant->id_passport && stripos($participant->id_passport, '-') === false && $participant->id_passport ? 'passport' : ''))) == 'passport' ? 'selected' : '' }}>Passport</option>
                                        </select>
                                    </div>
                                    <div id="ic_field" class="relative hidden">
                                        <input type="text" name="identity_card" id="organization_ic" placeholder="000000-00-0000" class="w-full h-9 text-xs border-gray-300 rounded px-3 focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50" value="{{ old('identity_card', $participant->identity_card ?: $participant->id_passport) }}" maxlength="14" oninput="formatIC(this)">
                                    </div>
                                    <div id="passport_field" class="relative hidden">
                                        <input type="text" name="passport_no" id="organization_passport" placeholder="A00000000" class="w-full h-9 text-xs border-gray-300 rounded px-3 focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50" value="{{ old('passport_no', $participant->passport_no ?: $participant->id_passport) }}">
                                    </div>
                                </div>
                            </div>

                            <!-- Address -->
                            <div class="flex flex-col md:flex-row md:items-start gap-3">
                                <label class="text-xs font-medium text-gray-700 md:w-48 shrink-0 pt-2">
                                    Address
                                </label>
                                <div class="flex-1">
                                    @php
                                        $address1 = $participant->address1 ?? '';
                                        $address2 = $participant->address2 ?? '';
                                        $city = $participant->city ?? '';
                                        $state = $participant->state ?? '';
                                        $postcode = $participant->postcode ?? '';
                                        $country = $participant->country ?? 'Malaysia';
                                    @endphp
                                    <div class="grid grid-cols-2 gap-2 mb-2">
                                        <div class="relative">
                                            <input type="text" name="address1" id="address1" placeholder="Address Line 1" class="w-full h-9 text-xs border-gray-300 rounded px-3 focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50" value="{{ old('address1', $address1) }}">
                                        </div>
                                        <div class="relative">
                                            <input type="text" name="address2" id="address2" placeholder="Address Line 2" class="w-full h-9 text-xs border-gray-300 rounded px-3 focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50" value="{{ old('address2', $address2) }}">
                                        </div>
                                    </div>
                                    <div class="grid grid-cols-4 gap-2">
                                        <div>
                                            <label for="state" class="block text-xs font-medium text-gray-700 mb-1">State</label>
                                            <select name="state" id="state" class="w-full h-9 text-xs border-gray-300 rounded focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50" onchange="handleStateChange()" data-old-value="{{ old('state', $state) }}">
                                                <option value="">-- Select State --</option>
                                            </select>
                                        </div>
                                        <div>
                                            <label for="city" class="block text-xs font-medium text-gray-700 mb-1">City</label>
                                            <select name="city" id="city" class="w-full h-9 text-xs border-gray-300 rounded focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50" disabled data-old-value="{{ old('city', $city) }}">
                                                <option value="">-- Select City --</option>
                                            </select>
                                        </div>
                                        <div>
                                            <label for="postcode" class="block text-xs font-medium text-gray-700 mb-1">Postcode</label>
                                            <select name="postcode" id="postcode" class="w-full h-9 text-xs border-gray-300 rounded focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50" disabled data-old-value="{{ old('postcode', $postcode) }}">
                                                <option value="">-- Select Postcode --</option>
                                            </select>
                                        </div>
                                        <div>
                                            <label for="country" class="block text-xs font-medium text-gray-700 mb-1">Country</label>
                                            <select name="country" id="country" class="w-full h-9 text-xs border-gray-300 rounded focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50" data-old-value="{{ old('country', $country) }}">
                                                <!-- Dropdown will be filled by JavaScript -->
                                            </select>
                                        </div>
                                    </div>
                                    <div id="manual-address-fields" class="hidden mt-4">
                                        <div class="grid grid-cols-4 gap-2">
                                            <div>
                                                <label for="manual_state" class="block text-xs font-medium text-gray-700 mb-1">State (Manual)</label>
                                                <div class="relative">
                                                    <input type="text" name="manual_state" id="manual_state" class="w-full h-9 text-xs border-gray-300 rounded px-3 focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50" placeholder="Enter state manually" value="{{ old('manual_state', $state) }}">
                                                </div>
                                            </div>
                                            <div>
                                                <label for="manual_city" class="block text-xs font-medium text-gray-700 mb-1">City (Manual)</label>
                                                <div class="relative">
                                                    <input type="text" name="manual_city" id="manual_city" class="w-full h-9 text-xs border-gray-300 rounded px-3 focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50" placeholder="Enter city manually" value="{{ old('manual_city', $city) }}">
                                                </div>
                                            </div>
                                            <div>
                                                <label for="manual_postcode" class="block text-xs font-medium text-gray-700 mb-1">Postcode (Manual)</label>
                                                <div class="relative">
                                                    <input type="text" name="manual_postcode" id="manual_postcode" class="w-full h-9 text-xs border-gray-300 rounded px-3 focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50" placeholder="Enter postcode manually" value="{{ old('manual_postcode', $postcode) }}">
                                                </div>
                                            </div>
                                            <div></div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Gender -->
                            <div class="flex flex-col md:flex-row md:items-center gap-3">
                                <label for="gender" class="text-xs font-medium text-gray-700 md:w-48 shrink-0">
                                    Gender
                                </label>
                                <div class="flex-1">
                                    <select name="gender" id="gender" class="w-full h-9 text-xs border-gray-300 rounded focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50">
                                        <option value="">-- Select Gender --</option>
                                        <option value="male" {{ old('gender', $participant->gender) == 'male' ? 'selected' : '' }}>Male</option>
                                        <option value="female" {{ old('gender', $participant->gender) == 'female' ? 'selected' : '' }}>Female</option>
                                        <option value="other" {{ old('gender', $participant->gender) == 'other' ? 'selected' : '' }}>Other</option>
                                    </select>
                                </div>
                            </div>

                            <!-- Race -->
                            <div class="flex flex-col md:flex-row md:items-center gap-3">
                                <label for="race" class="text-xs font-medium text-gray-700 md:w-48 shrink-0">
                                    Race
                                </label>
                                <div class="flex-1">
                                    <x-race-select :selected="old('race', $participant->race)" />
                                </div>
                            </div>

                            <!-- Date of Birth -->
                            <div class="flex flex-col md:flex-row md:items-center gap-3">
                                <label for="date_of_birth" class="text-xs font-medium text-gray-700 md:w-48 shrink-0">
                                    Date of Birth
                                </label>
                                <div class="flex-1">
                                    <input type="date" name="date_of_birth" id="date_of_birth" class="w-full h-9 text-xs border-gray-300 rounded focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50" value="{{ old('date_of_birth', $participant->date_of_birth ? (is_string($participant->date_of_birth) ? $participant->date_of_birth : $participant->date_of_birth->format('Y-m-d')) : '') }}">
                                </div>
                            </div>

                            <!-- Job Title -->
                            <div class="flex flex-col md:flex-row md:items-center gap-3">
                                <label for="job_title" class="text-xs font-medium text-gray-700 md:w-48 shrink-0">
                                    Job Title
                                </label>
                                <div class="flex-1">
                                    <input type="text" name="job_title" id="job_title" class="w-full h-9 text-xs border-gray-300 rounded focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50" value="{{ old('job_title', $participant->job_title) }}">
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
                                    Select Events <span class="text-red-500">*</span>
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
                                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3 max-h-48 overflow-y-auto border border-gray-200 rounded p-3">
                                    @forelse($events as $event)
                                        <label class="flex items-center space-x-2 cursor-pointer">
                                            <input type="checkbox" name="event_ids[]" value="{{ $event->id }}" 
                                                   {{ in_array($event->id, old('event_ids', $participant->events->pluck('id')->toArray())) ? 'checked' : '' }}
                                                   class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                            <span class="text-xs text-gray-700">{{ $event->title ?? $event->name }}</span>
                                        </label>
                                    @empty
                                        <div class="col-span-full text-center text-gray-500 text-xs py-4">
                                            <span class="material-icons-outlined text-gray-300 text-2xl mb-2">event</span>
                                            <p>No events available</p>
                                            <p class="mt-1">Create an event first to assign participants</p>
                                        </div>
                                    @endforelse
                                </div>
                                @error('event_ids')
                                    <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Notes -->
                <div class="bg-white border border-gray-200 rounded-md shadow-sm">
                    <div class="bg-gray-50 border-b border-gray-200 px-4 py-3">
                        <h2 class="text-sm font-semibold text-gray-700 flex items-center">
                            <span class="material-icons-outlined text-primary-DEFAULT mr-2">notes</span>
                            Notes
                        </h2>
                    </div>
                    
                    <div class="p-4">
                        <div class="relative">
                            <textarea name="notes" id="notes" rows="3" class="w-full text-xs border-gray-300 rounded px-3 py-2 min-h-[60px] focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50" placeholder="Any additional notes...">{{ old('notes', $participant->notes) }}</textarea>
                        </div>
                    </div>
                </div>
                <!-- Account Information (Read-only) -->
                <div class="bg-white border border-gray-200 rounded-lg p-4">
                    <h3 class="text-xs font-semibold text-gray-800 mb-4">Account Information (Read-only)</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-medium text-gray-500 mb-1">Account Created</label>
                            <p class="text-xs text-gray-800">{{ $participant->created_at->format('M d, Y \a\t H:i') }}</p>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-500 mb-1">Last Updated</label>
                            <p class="text-xs text-gray-800">{{ $participant->updated_at->format('M d, Y \a\t H:i') }}</p>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-500 mb-1">Last Login</label>
                            <p class="text-xs text-gray-800">
                                @if($participant->last_login_at)
                                    {{ $participant->last_login_at->format('M d, Y \a\t H:i') }}
                                @else
                                    Never logged in
                                @endif
                            </p>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-500 mb-1">Password Changed</label>
                            <p class="text-xs text-gray-800">
                                @if($participant->password_changed_at)
                                    {{ $participant->password_changed_at->format('M d, Y \a\t H:i') }}
                                @else
                                    Never changed
                                @endif
                            </p>
                        </div>
                    </div>
                </div>
                <!-- Action Buttons -->
                <div class="flex justify-end space-x-3 pt-4">
                    <a href="{{ route('pwa.participants.show', $participant) }}" class="px-3 h-[36px] bg-gradient-to-r from-red-600 to-red-500 hover:from-red-700 hover:to-red-600 text-white rounded shadow-sm text-xs font-medium transition-colors duration-200 ease-in-out flex items-center">
                        <span class="material-icons-outlined text-xs mr-1">cancel</span>
                        Cancel
                    </a>
                    <button type="submit" class="px-3 h-[36px] bg-gradient-to-r from-green-600 to-green-500 hover:from-green-700 hover:to-green-600 text-white rounded shadow-sm text-xs font-medium transition-colors duration-200 ease-in-out flex items-center">
                        <span class="material-icons-outlined text-xs mr-1">save</span>
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
    let value = input.value.replace(/\D/g, '');
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
document.addEventListener('DOMContentLoaded', function() {
    toggleIdFields();
    const idType = document.getElementById('id_type').value;
    if (idType) {
        toggleIdFields();
    }
    const stateSelect = document.getElementById('state');
    if (stateSelect) {
        try {
            loadStates();
            if (stateSelect.value === 'others' || !stateSelect.value && '{{ $participant->address_state }}') {
                const manualFields = document.getElementById('manual-address-fields');
                if (manualFields) {
                    const checkStateLoaded = setInterval(function() {
                        if (stateSelect.options.length > 1) {
                            let stateExists = false;
                            for (let i = 0; i < stateSelect.options.length; i++) {
                                if (stateSelect.options[i].value === '{{ $participant->address_state }}') {
                                    stateExists = true;
                                    stateSelect.value = '{{ $participant->address_state }}';
                                    break;
                                }
                            }
                            if (!stateExists && '{{ $participant->address_state }}') {
                                stateSelect.value = 'others';
                            }
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
document.addEventListener('DOMContentLoaded', function() {
    document.querySelector('form').addEventListener('submit', function() {
        // No longer needed as address is not combined
    });
});
</script> 
