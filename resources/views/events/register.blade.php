@extends('layouts.event-registration')

@section('content')
<div class="min-h-screen bg-gray-50 py-6 px-3 sm:px-4" x-data="{
    step: 1,
    form: {
        name: '',
        identity_card: '',
        passport_no: '',
        address1: '',
        address2: '',
        state: '',
        city: '',
        postcode: '',
        country: 'Malaysia',
        organization: '',
        job_title: '',
        email: '',
        phone: '',
        gender: '',
        date_of_birth: '',
        notes: '',
        id_type: '', // Added for IC/Passport dropdown
        manual_state: '', // Added for manual state input
        manual_city: '', // Added for manual city input
        manual_postcode: '', // Added for manual postcode input
    },
    next() { if (this.step < 5) this.step++ },
    prev() { if (this.step > 1) this.step-- },
    setField(field, value) { this.form[field] = value },
    fillOld() {
        // Fill from old() if available (for validation error)
        this.form.name = '{{ old('name') }}';
        this.form.identity_card = '{{ old('identity_card') }}';
        this.form.passport_no = '{{ old('passport_no') }}';
        this.form.address1 = '{{ old('address1') }}';
        this.form.address2 = '{{ old('address2') }}';
        this.form.state = '{{ old('state') }}';
        this.form.city = '{{ old('city') }}';
        this.form.postcode = '{{ old('postcode') }}';
        this.form.country = '{{ old('country', 'Malaysia') }}';
        this.form.organization = '{{ old('organization') }}';
        this.form.job_title = '{{ old('job_title') }}';
        this.form.email = '{{ old('email') }}';
        this.form.phone = '{{ old('phone') }}';
        this.form.gender = '{{ old('gender') }}';
        this.form.date_of_birth = '{{ old('date_of_birth') }}';
        this.form.notes = '{{ old('notes') }}';
        this.form.id_type = '{{ old('id_type') }}'; // Fill id_type
        this.form.manual_state = '{{ old('manual_state') }}'; // Fill manual_state
        this.form.manual_city = '{{ old('manual_city') }}'; // Fill manual_city
        this.form.manual_postcode = '{{ old('manual_postcode') }}'; // Fill manual_postcode
    },
    // New methods for IC/Passport formatting and state/city/postcode/country population
    formatIC(e) {
        let input = e.target;
        let value = input.value.replace(/\D/g, '');
        let formatted = '';
        if (value.length > 6) {
            formatted = value.substring(0, 6) + '-';
            if (value.length > 8) {
                formatted += value.substring(6, 8) + '-';
                formatted += value.substring(8, 12);
            } else {
                formatted += value.substring(6, 8);
            }
        } else {
            formatted = value;
        }
        input.value = formatted;
    },
    // Populate state/city/postcode/country (gunakan JS yang sama seperti participants/create)
    // ... existing code ...
}" x-init="fillOld()">
    <div class="max-w-6xl mx-auto">
        <!-- Section 1: Banner & Event Info (Selalu di atas) -->
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
            <div class="p-4 text-xs">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
                    <div>
                        <div class="mb-1"><span class="font-semibold">Date:</span> {{ \Carbon\Carbon::parse($event->start_date)->format('d M Y') }} - {{ \Carbon\Carbon::parse($event->end_date)->format('d M Y') }}</div>
                        <div class="mb-1"><span class="font-semibold">Time:</span> {{ $event->start_time ? substr($event->start_time, 0, 5) : '' }} - {{ $event->end_time ? substr($event->end_time, 0, 5) : '' }}</div>
                        <div class="mb-1"><span class="font-semibold">Location:</span> {{ $event->location }}</div>
                        @if ($event->address)
                        <div class="mb-1"><span class="font-semibold">Address:</span> {{ $event->address }}</div>
                        @endif
                    </div>
                    <div>
                        <div class="mb-1"><span class="font-semibold">Organizer:</span> {{ $event->organizer }}</div>
                        @if ($event->contact_person)
                        <div class="mb-1"><span class="font-semibold">Contact Person:</span> {{ $event->contact_person }}</div>
                        @endif
                        @if ($event->contact_email)
                        <div class="mb-1"><span class="font-semibold">Contact Email:</span> {{ $event->contact_email }}</div>
                        @endif
                        @if ($event->contact_phone)
                        <div class="mb-1"><span class="font-semibold">Contact Phone:</span> {{ $event->contact_phone }}</div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Stepper Navigation -->
        <div class="flex justify-center mb-4">
            <template x-for="n in 5" :key="n">
                <div :class="{'bg-blue-600 text-white': step === n, 'bg-gray-200 text-gray-500': step !== n}" class="w-7 h-7 flex items-center justify-center rounded-full mx-1 text-xs font-bold cursor-pointer" @click="step = n">
                    <span x-text="n"></span>
                </div>
            </template>
        </div>

        <!-- Section 2: Syarat Event -->
        <div x-show="step === 2" class="bg-white shadow rounded-lg overflow-hidden mb-6 text-xs">
            <div class="bg-gradient-to-r from-green-500 to-green-600 px-4 py-3">
                <h2 class="text-white text-base font-semibold flex items-center">
                    <span class="material-icons text-white text-sm mr-2">rule</span>
                    Syarat-syarat Program/Event
                </h2>
            </div>
            <div class="p-4">
                <div class="whitespace-pre-line">{{ $event->condition ?? '-' }}</div>
            </div>
            <div class="p-4 flex justify-end">
                <button type="button" @click="next()" class="px-4 py-1 bg-blue-600 text-white rounded text-xs">Next</button>
            </div>
        </div>

        <!-- Section 3: Particulars 1 -->
        <form x-show="step === 3" @submit.prevent="next()" class="bg-white shadow rounded-lg overflow-hidden mb-6 text-xs">
            <div class="bg-gradient-to-r from-blue-500 to-indigo-600 px-4 py-3">
                <h2 class="text-white text-base font-semibold flex items-center">
                    <span class="material-icons text-white text-sm mr-2">person</span>
                    Personal Information
                </h2>
            </div>
            <div class="p-4 space-y-3">
                <div>
                    <label class="block mb-1">Full Name</label>
                    <input type="text" x-model="form.name" name="name" class="w-full border border-gray-300 rounded px-2 py-1 text-xs" required>
                </div>
                <!-- IC/Passport Dropdown -->
                <div>
                    <label class="block mb-1">Identity Card / Passport No.</label>
                    <select x-model="form.id_type" name="id_type" id="id_type" class="w-full border border-gray-300 rounded px-2 py-1 text-xs" @change="form.identity_card='';form.passport_no='';">
                        <option value="">-- Select IC / Passport --</option>
                        <option value="ic">Identity Card</option>
                        <option value="passport">Passport</option>
                    </select>
                </div>
                <div x-show="form.id_type === 'ic'">
                    <label class="block mb-1">Identity Card (IC)</label>
                    <input type="text" x-model="form.identity_card" name="identity_card" id="identity_card" maxlength="14" class="w-full border border-gray-300 rounded px-2 py-1 text-xs" placeholder="000000-00-0000" @input="formatIC($event)">
                </div>
                <div x-show="form.id_type === 'passport'">
                    <label class="block mb-1">Passport No.</label>
                    <input type="text" x-model="form.passport_no" name="passport_no" class="w-full border border-gray-300 rounded px-2 py-1 text-xs" placeholder="A00000000">
                </div>
                <!-- Address Section (copy from participants/create) -->
                <div class="grid grid-cols-2 gap-2 mb-2">
                    <div>
                        <label class="block mb-1">Address Line 1</label>
                        <input type="text" x-model="form.address1" name="address1" id="address1" class="w-full border border-gray-300 rounded px-2 py-1 text-xs">
                    </div>
                    <div>
                        <label class="block mb-1">Address Line 2</label>
                        <input type="text" x-model="form.address2" name="address2" id="address2" class="w-full border border-gray-300 rounded px-2 py-1 text-xs">
                    </div>
                </div>
                <div class="grid grid-cols-4 gap-2">
                    <div>
                        <label class="block mb-1">State</label>
                        <select x-model="form.state" name="state" id="state" class="w-full border border-gray-300 rounded px-2 py-1 text-xs">
                            <!-- options populated by JS -->
                        </select>
                    </div>
                    <div>
                        <label class="block mb-1">City</label>
                        <select x-model="form.city" name="city" id="city" class="w-full border border-gray-300 rounded px-2 py-1 text-xs" :disabled="!form.state || form.state === 'others'" x-show="form.state !== 'others'"></select>
                    </div>
                    <div>
                        <label class="block mb-1">Postcode</label>
                        <select x-model="form.postcode" name="postcode" id="postcode" class="w-full border border-gray-300 rounded px-2 py-1 text-xs" :disabled="!form.city || form.state === 'others'" x-show="form.state !== 'others'"></select>
                    </div>
                    <div>
                        <label class="block mb-1">Country</label>
                        <select x-model="form.country" name="country" id="country" class="w-full border border-gray-300 rounded px-2 py-1 text-xs"></select>
                    </div>
                </div>
                <!-- Manual address fields if state == others -->
                <div x-show="form.state === 'others'" class="mt-2">
                    <div class="grid grid-cols-4 gap-2">
                        <div>
                            <label class="block mb-1">State (Manual)</label>
                            <input type="text" x-model="form.manual_state" name="manual_state" id="manual_state" class="w-full border border-gray-300 rounded px-2 py-1 text-xs" placeholder="Enter state manually">
                        </div>
                        <div>
                            <label class="block mb-1">City (Manual)</label>
                            <input type="text" x-model="form.manual_city" name="manual_city" id="manual_city" class="w-full border border-gray-300 rounded px-2 py-1 text-xs" placeholder="Enter city manually">
                        </div>
                        <div>
                            <label class="block mb-1">Postcode (Manual)</label>
                            <input type="text" x-model="form.manual_postcode" name="manual_postcode" id="manual_postcode" class="w-full border border-gray-300 rounded px-2 py-1 text-xs" placeholder="Enter postcode manually">
                        </div>
                        <div></div>
                    </div>
                </div>
            </div>
            <div class="p-4 flex justify-between">
                <button type="button" @click="prev()" class="px-4 py-1 bg-gray-300 text-gray-700 rounded text-xs">Back</button>
                <button type="submit" class="px-4 py-1 bg-blue-600 text-white rounded text-xs">Next</button>
            </div>
        </form>

        <!-- Section 4: Particulars 2 -->
        <form x-show="step === 4" @submit.prevent="next()" class="bg-white shadow rounded-lg overflow-hidden mb-6 text-xs">
            <div class="bg-gradient-to-r from-blue-500 to-indigo-600 px-4 py-3">
                <h2 class="text-white text-base font-semibold flex items-center">
                    <span class="material-icons text-white text-sm mr-2">work</span>
                    Organization & Contact
                </h2>
            </div>
            <div class="p-4 space-y-3">
                <div>
                    <label class="block mb-1">Company / Government</label>
                    <input type="text" x-model="form.organization" name="organization" class="w-full border border-gray-300 rounded px-2 py-1 text-xs">
                </div>
                <div>
                    <label class="block mb-1">Job Title</label>
                    <input type="text" x-model="form.job_title" name="job_title" class="w-full border border-gray-300 rounded px-2 py-1 text-xs">
                </div>
                <div>
                    <label class="block mb-1">Email</label>
                    <input type="email" x-model="form.email" name="email" class="w-full border border-gray-300 rounded px-2 py-1 text-xs" required>
                </div>
                <div>
                    <label class="block mb-1">Phone Number</label>
                    <input type="tel" name="phone" id="phone" class="phone-input w-full border border-gray-300 rounded px-2 py-1 text-xs" x-model="form.phone">
                </div>
                <div>
                    <label class="block mb-1">Gender</label>
                    <select x-model="form.gender" name="gender" class="w-full border border-gray-300 rounded px-2 py-1 text-xs">
                        <option value="">-- Select Gender --</option>
                        <option value="male">Male</option>
                        <option value="female">Female</option>
                        <option value="other">Other</option>
                    </select>
                </div>
                <div>
                    <label class="block mb-1">Date of Birth</label>
                    <input type="date" x-model="form.date_of_birth" name="date_of_birth" class="w-full border border-gray-300 rounded px-2 py-1 text-xs">
                </div>
            </div>
            <div class="p-4 flex justify-between">
                <button type="button" @click="prev()" class="px-4 py-1 bg-gray-300 text-gray-700 rounded text-xs">Back</button>
                <button type="submit" class="px-4 py-1 bg-blue-600 text-white rounded text-xs">Next</button>
            </div>
        </form>

        <!-- Section 5: Preview & Submit -->
        <form x-show="step === 5" method="POST" action="{{ route('event.register.submit', $event->registration_link) }}" class="bg-white shadow rounded-lg overflow-hidden mb-6 text-xs">
            @csrf
            <div class="bg-gradient-to-r from-blue-500 to-indigo-600 px-4 py-3">
                <h2 class="text-white text-base font-semibold flex items-center">
                    <span class="material-icons text-white text-sm mr-2">preview</span>
                    Preview & Submit
                </h2>
            </div>
            <div class="p-4 space-y-2">
                <div class="font-semibold mb-2">Please review your information before submitting:</div>
                <div class="grid grid-cols-2 gap-2">
                    <div><span class="font-semibold">Full Name:</span> <span x-text="form.name"></span></div>
                    <div><span class="font-semibold">IC:</span> <span x-text="form.identity_card"></span></div>
                    <div><span class="font-semibold">Passport:</span> <span x-text="form.passport_no"></span></div>
                    <div><span class="font-semibold">Address 1:</span> <span x-text="form.address1"></span></div>
                    <div><span class="font-semibold">Address 2:</span> <span x-text="form.address2"></span></div>
                    <div><span class="font-semibold">State:</span> <span x-text="form.state"></span></div>
                    <div><span class="font-semibold">City:</span> <span x-text="form.city"></span></div>
                    <div><span class="font-semibold">Postcode:</span> <span x-text="form.postcode"></span></div>
                    <div><span class="font-semibold">Country:</span> <span x-text="form.country"></span></div>
                    <div><span class="font-semibold">Organization:</span> <span x-text="form.organization"></span></div>
                    <div><span class="font-semibold">Job Title:</span> <span x-text="form.job_title"></span></div>
                    <div><span class="font-semibold">Email:</span> <span x-text="form.email"></span></div>
                    <div><span class="font-semibold">Phone:</span> <span x-text="form.phone"></span></div>
                    <div><span class="font-semibold">Gender:</span> <span x-text="form.gender"></span></div>
                    <div><span class="font-semibold">Date of Birth:</span> <span x-text="form.date_of_birth"></span></div>
                </div>
                <div><span class="font-semibold">Notes:</span> <span x-text="form.notes"></span></div>
            </div>
            <!-- Hidden fields for submit -->
            <template x-for="(value, key) in form" :key="key">
                <input type="hidden" :name="key" :value="value">
            </template>
            <div class="p-4 flex justify-between">
                <button type="button" @click="prev()" class="px-4 py-1 bg-gray-300 text-gray-700 rounded text-xs">Back</button>
                <button type="submit" class="px-4 py-1 bg-green-600 text-white rounded text-xs">Submit</button>
            </div>
        </form>

        <!-- Section 2: Syarat Event (jika step 2) -->
        <div x-show="step === 1" class="bg-white shadow rounded-lg overflow-hidden mb-6 text-xs">
            <div class="bg-gradient-to-r from-green-500 to-green-600 px-4 py-3">
                <h2 class="text-white text-base font-semibold flex items-center">
                    <span class="material-icons text-white text-sm mr-2">info</span>
                    Welcome! Please review the event information and click Next to proceed.
                </h2>
            </div>
            <div class="p-4 flex justify-end">
                <button type="button" @click="next()" class="px-4 py-1 bg-blue-600 text-white rounded text-xs">Next</button>
            </div>
        </div>
    </div>
</div>
@endsection 