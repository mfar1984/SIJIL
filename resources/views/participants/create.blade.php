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
                                <input type="text" name="name" id="name" class="w-full text-xs border-gray-300 rounded-[1px] pl-12 focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50" value="{{ old('name') }}" required>
                            </div>
                            <p class="mt-1 text-[10px] text-gray-500">Enter participant's full name as it appears on official documents</p>
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
                                <input type="email" name="email" id="email" class="w-full text-xs border-gray-300 rounded-[1px] pl-12 focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50" value="{{ old('email') }}" required>
                            </div>
                            <p class="mt-1 text-[10px] text-gray-500">This email will be used for notifications</p>
                        </div>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <!-- Phone -->
                        <div>
                            <label for="phone" class="flex items-center text-xs font-medium text-gray-700 mb-1">
                                <span class="material-icons text-sm mr-1 text-primary-DEFAULT">phone</span>
                                Phone Number
                            </label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <span class="material-icons text-[#004aad] text-base">call</span>
                                </div>
                                <input type="text" name="phone" id="phone" class="w-full text-xs border-gray-300 rounded-[1px] pl-12 focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50" value="{{ old('phone') }}" placeholder="+60123456789">
                            </div>
                            <p class="mt-1 text-[10px] text-gray-500">Include country code (e.g., +60 for Malaysia)</p>
                        </div>
                        <!-- Identity Card / Passport No. -->
                        <div class="mb-4">
                            <label for="id_type" class="flex items-center text-xs font-medium text-gray-700 mb-1">
                                <span class="material-icons text-sm mr-1 text-primary-DEFAULT">badge</span>
                                Identity Card / Passport No.
                            </label>
                            <div class="mb-2">
                                <select name="id_type" id="id_type" class="w-full text-xs border-gray-300 rounded-[1px] focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50" onchange="toggleIdFields()">
                                    <option value="">-- Select IC / Passport --</option>
                                    <option value="ic">Identity Card</option>
                                    <option value="passport">Passport</option>
                                </select>
                            </div>
                            <div id="ic_field" class="relative hidden">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <span class="material-icons text-[#004aad] text-base">assignment_ind</span>
                                </div>
                                <input type="text" name="organization_ic" id="organization_ic" placeholder="000000-00-0000" class="w-full text-xs border-gray-300 rounded-[1px] pl-12 focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50">
                            </div>
                            <div id="passport_field" class="relative hidden">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <span class="material-icons text-[#004aad] text-base">assignment_ind</span>
                                </div>
                                <input type="text" name="organization_passport" id="organization_passport" placeholder="Enter passport number" class="w-full text-xs border-gray-300 rounded-[1px] pl-12 focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50">
                            </div>
                            <input type="hidden" name="organization" id="organization">
                            <p class="mt-1 text-[10px] text-gray-500">Select ID type and enter your number (optional)</p>
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
                                <select name="status" id="status" class="w-full text-xs border-gray-300 rounded-[1px] pl-12 focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50">
                                    <option value="active" {{ old('status') == 'active' ? 'selected' : '' }}>Active</option>
                                    <option value="inactive" {{ old('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
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
                                <input type="text" name="registration_date" id="registration_date" class="w-full text-xs border-gray-300 bg-gray-50 rounded-[1px] pl-12 py-2 border" value="{{ now()->format('d M Y - H:i') }}" readonly>
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
                                <input type="text" name="attendance_date" id="attendance_date" class="w-full text-xs border-gray-300 bg-gray-50 rounded-[1px] pl-12 py-2 border" value="" placeholder="(Optional)">
                            </div>
                            <p class="mt-1 text-[10px] text-gray-500">Date and time of attendance (if available)</p>
                        </div>
                    </div>
                </div>
                <!-- Notes -->
                <div>
                    <h2 class="text-sm font-semibold text-gray-700 mb-4">Notes</h2>
                    <div class="relative">
                        <div class="absolute top-3 left-0 pl-3 flex items-start pointer-events-none">
                            <span class="material-icons text-[#004aad] text-base">notes</span>
                        </div>
                        <textarea name="notes" id="notes" rows="3" class="w-full text-xs border-gray-300 bg-gray-50 rounded-[1px] pl-12 py-3 border min-h-[60px] focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50" placeholder="Any additional notes...">{{ old('notes') }}</textarea>
                    </div>
                    <p class="mt-1 text-[10px] text-gray-500">Any additional information or special requirements</p>
                </div>
                <div class="border-t border-gray-200 pt-4 mt-6 flex justify-end space-x-3">
                    <a href="{{ route('participants') }}" class="px-3 py-1 bg-gradient-to-r from-red-600 to-red-500 hover:from-red-700 hover:to-red-600 text-white rounded shadow-sm text-xs font-medium transition-colors duration-200 ease-in-out flex items-center">
                        <span class="material-icons text-xs mr-1">cancel</span>
                        Cancel
                    </a>
                    <button type="submit" class="px-3 py-1 bg-gradient-to-r from-blue-600 to-blue-500 hover:from-blue-700 hover:to-blue-600 text-white rounded shadow-sm text-xs font-medium transition-colors duration-200 ease-in-out flex items-center">
                        <span class="material-icons text-xs mr-1">save</span>
                        Add Participant
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
    icField.classList.add('hidden');
    passportField.classList.add('hidden');
    if (idType === 'ic') {
        icField.classList.remove('hidden');
    } else if (idType === 'passport') {
        passportField.classList.remove('hidden');
    }
}
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