<x-app-layout>
    <x-slot name="breadcrumb">
        <span>Participants</span>
        <span class="mx-2 text-gray-500">/</span>
        <span>View Participant</span>
    </x-slot>

    <x-slot name="title">Participant Details</x-slot>

    <div class="bg-white rounded shadow-md border border-gray-300">
        <div class="p-6 border-b border-gray-200">
            <div class="flex items-center justify-between">
                <div class="flex items-center">
                    <span class="material-icons mr-2 text-primary-DEFAULT">person</span>
                    <h1 class="text-xl font-bold text-gray-800">Participant Details</h1>
                </div>
                <div class="flex space-x-3">
                    <a href="{{ route('participants.edit', $participant->id) }}" class="bg-gradient-to-r from-yellow-500 to-yellow-400 hover:from-yellow-600 hover:to-yellow-500 text-white px-3 py-1 rounded shadow-sm font-medium flex items-center text-xs transition-colors duration-200 ease-in-out">
                        <span class="material-icons text-xs mr-1">edit</span>
                        Edit Participant
                    </a>
                    <form method="POST" action="{{ route('participants.destroy', $participant->id) }}" onsubmit="return confirm('Are you sure you want to delete this participant?')" class="inline-block">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="bg-gradient-to-r from-red-500 to-red-400 hover:from-red-600 hover:to-red-500 text-white px-3 py-1 rounded shadow-sm font-medium flex items-center text-xs transition-colors duration-200 ease-in-out">
                            <span class="material-icons text-xs mr-1">delete</span>
                            Delete Participant
                        </button>
                    </form>
                    <a href="{{ route('participants') }}" class="bg-gradient-to-r from-gray-500 to-gray-400 hover:from-gray-600 hover:to-gray-500 text-white px-3 py-1 rounded shadow-sm font-medium flex items-center text-xs transition-colors duration-200 ease-in-out">
                        <span class="material-icons text-xs mr-1">arrow_back</span>
                        Back to List
                    </a>
                </div>
            </div>
            <p class="text-xs text-gray-500 mt-1 ml-8">View detailed information about this participant</p>
        </div>
        
        <div class="p-6 space-y-6">
            <!-- Basic Info -->
            <div class="border-b border-gray-200 pb-5">
                <h2 class="text-sm font-semibold text-gray-700 mb-4">Basic Information</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-4">
                    <!-- Name -->
                    <div>
                        <label class="flex items-center text-xs font-medium text-gray-700 mb-1">
                            <span class="material-icons text-sm mr-1 text-primary-DEFAULT">person</span>
                            Full Name
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <span class="material-icons text-[#004aad] text-base">badge</span>
                            </div>
                            <div class="w-full text-xs border-gray-200 bg-gray-50 rounded-[1px] pl-12 py-2 border">
                                {{ $participant->name }}
                            </div>
                        </div>
                    </div>
                    
                    <!-- Email -->
                    <div>
                        <label class="flex items-center text-xs font-medium text-gray-700 mb-1">
                            <span class="material-icons text-sm mr-1 text-primary-DEFAULT">email</span>
                            Email Address
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <span class="material-icons text-[#004aad] text-base">alternate_email</span>
                            </div>
                            <div class="w-full text-xs border-gray-200 bg-gray-50 rounded-[1px] pl-12 py-2 border">
                                {{ $participant->email }}
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Second row -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <!-- Phone -->
                    <div>
                        <label class="flex items-center text-xs font-medium text-gray-700 mb-1">
                            <span class="material-icons text-sm mr-1 text-primary-DEFAULT">phone</span>
                            Phone Number
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <span class="material-icons text-[#004aad] text-base">call</span>
                            </div>
                            <div class="w-full text-xs border-gray-200 bg-gray-50 rounded-[1px] pl-12 py-2 border">
                                {{ $participant->formatted_phone ?? 'Not specified' }}
                            </div>
                        </div>
                    </div>
                    
                    <!-- Identity Card / Passport No. -->
                    <div>
                        <label class="flex items-center text-xs font-medium text-gray-700 mb-1">
                            <span class="material-icons text-sm mr-1 text-primary-DEFAULT">badge</span>
                            Identity Card / Passport No.
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <span class="material-icons text-[#004aad] text-base">assignment_ind</span>
                            </div>
                            <div class="w-full text-xs border-gray-200 bg-gray-50 rounded-[1px] pl-12 py-2 border">
                                @if($participant->identity_card)
                                    {{ $participant->identity_card }} (IC)
                                @elseif($participant->passport_no)
                                    {{ $participant->passport_no }} (Passport)
                                @elseif($participant->id_passport)
                                    {{ $participant->id_passport }}
                                @else
                                    Not specified
                                @endif
                            </div>
                        </div>
                    </div>
                    
                    <!-- Date of Birth -->
                    <div>
                        <label class="flex items-center text-xs font-medium text-gray-700 mb-1">
                            <span class="material-icons text-sm mr-1 text-primary-DEFAULT">cake</span>
                            Date of Birth
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <span class="material-icons text-[#004aad] text-base">calendar_today</span>
                            </div>
                            <div class="w-full text-xs border-gray-200 bg-gray-50 rounded-[1px] pl-12 py-2 border">
                                {{ $participant->date_of_birth ? $participant->date_of_birth->format('d M Y') : 'Not specified' }}
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Address Information -->
                <div class="mb-4">
                    <label class="block text-xs font-medium text-gray-700 mb-1">Address</label>
                    <div class="w-full text-xs border-gray-200 bg-gray-50 rounded-[1px] py-2 px-3 border min-h-[40px]">
                        @php
                            $addressParts = [];
                            if ($participant->address1) $addressParts[] = $participant->address1;
                            if ($participant->address2) $addressParts[] = $participant->address2;
                            $cityStatePost = [];
                            if ($participant->postcode) $cityStatePost[] = $participant->postcode;
                            if ($participant->city) $cityStatePost[] = $participant->city;
                            if ($participant->state) $cityStatePost[] = $participant->state;
                            if (count($cityStatePost)) $addressParts[] = implode(', ', $cityStatePost);
                            if ($participant->country) $addressParts[] = $participant->country;
                        @endphp
                        @if(count($addressParts))
                            @foreach($addressParts as $line)
                                <div class="mb-1">{{ $line }}</div>
                            @endforeach
                        @else
                            <span class="text-gray-400">Address not provided</span>
                        @endif
                    </div>
                </div>
            </div>
            
            <!-- Additional Information -->
            <div class="border-b border-gray-200 pb-5">
                <h2 class="text-sm font-semibold text-gray-700 mb-4">Additional Information</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-4">
                    <!-- Gender -->
                    <div>
                        <label class="flex items-center text-xs font-medium text-gray-700 mb-1">
                            <span class="material-icons text-sm mr-1 text-primary-DEFAULT">wc</span>
                            Gender
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <span class="material-icons text-[#004aad] text-base">person</span>
                            </div>
                            <div class="w-full text-xs border-gray-200 bg-gray-50 rounded-[1px] pl-12 py-2 border">
                                @if($participant->gender == 'male')
                                    Male
                                @elseif($participant->gender == 'female')
                                    Female
                                @elseif($participant->gender == 'other')
                                    Other
                                @else
                                    Not specified
                                @endif
                            </div>
                        </div>
                    </div>
                    
                    <!-- Organization/Company -->
                    <div>
                        <label class="flex items-center text-xs font-medium text-gray-700 mb-1">
                            <span class="material-icons text-sm mr-1 text-primary-DEFAULT">business</span>
                            Organization/Company
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <span class="material-icons text-[#004aad] text-base">apartment</span>
                            </div>
                            <div class="w-full text-xs border-gray-200 bg-gray-50 rounded-[1px] pl-12 py-2 border">
                                {{ $participant->organization ?? 'Not specified' }}
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Job Title -->
                    <div>
                        <label class="flex items-center text-xs font-medium text-gray-700 mb-1">
                            <span class="material-icons text-sm mr-1 text-primary-DEFAULT">work</span>
                            Job Title
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <span class="material-icons text-[#004aad] text-base">badge</span>
                            </div>
                            <div class="w-full text-xs border-gray-200 bg-gray-50 rounded-[1px] pl-12 py-2 border">
                                {{ $participant->job_title ?? 'Not specified' }}
                            </div>
                        </div>
                    </div>
                    
                    <!-- Status -->
                    <div>
                        <label class="flex items-center text-xs font-medium text-gray-700 mb-1">
                            <span class="material-icons text-sm mr-1 text-primary-DEFAULT">toggle_on</span>
                            Status
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <span class="material-icons text-[#004aad] text-base">shield</span>
                            </div>
                            <div class="w-full text-xs border-gray-200 bg-gray-50 rounded-[1px] pl-12 py-2 border flex items-center">
                                @if($participant->status === 'active')
                                    <span class="bg-status-active-bg text-status-active-text px-2 py-0.5 rounded-full text-xs">Active</span>
                                @elseif($participant->status === 'inactive')
                                    <span class="bg-status-pending-bg text-status-pending-text px-2 py-0.5 rounded-full text-xs">Inactive</span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Event Info -->
            <div class="border-b border-gray-200 pb-5">
                <h2 class="text-sm font-semibold text-gray-700 mb-4">Event Information</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Event Name -->
                    <div>
                        <label class="flex items-center text-xs font-medium text-gray-700 mb-1">
                            <span class="material-icons text-sm mr-1 text-primary-DEFAULT">event</span>
                            Event Name
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <span class="material-icons text-[#004aad] text-base">event_note</span>
                            </div>
                            <div class="w-full text-xs border-gray-200 bg-gray-50 rounded-[1px] pl-12 py-2 border">
                                {{ $participant->event->name }}
                            </div>
                        </div>
                    </div>
                    
                    <!-- Event Organizer -->
                    <div>
                        <label class="flex items-center text-xs font-medium text-gray-700 mb-1">
                            <span class="material-icons text-sm mr-1 text-primary-DEFAULT">people</span>
                            Event Organizer
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <span class="material-icons text-[#004aad] text-base">groups</span>
                            </div>
                            <div class="w-full text-xs border-gray-200 bg-gray-50 rounded-[1px] pl-12 py-2 border">
                                {{ $participant->event->organizer }}
                            </div>
                        </div>
                    </div>
                    
                    <!-- Event Date -->
                    <div>
                        <label class="flex items-center text-xs font-medium text-gray-700 mb-1">
                            <span class="material-icons text-sm mr-1 text-primary-DEFAULT">calendar_today</span>
                            Event Date
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <span class="material-icons text-[#004aad] text-base">date_range</span>
                            </div>
                            <div class="w-full text-xs border-gray-200 bg-gray-50 rounded-[1px] pl-12 py-2 border">
                                {{ $participant->event->start_date->format('l, d M Y') }} - {{ \Carbon\Carbon::parse($participant->event->start_time)->format('h:iA') }} to {{ $participant->event->end_date->format('l, d M Y') }} - {{ \Carbon\Carbon::parse($participant->event->end_time)->format('h:iA') }}
                            </div>
                        </div>
                    </div>
                    
                    <!-- Event Location -->
                    <div>
                        <label class="flex items-center text-xs font-medium text-gray-700 mb-1">
                            <span class="material-icons text-sm mr-1 text-primary-DEFAULT">location_on</span>
                            Event Location
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <span class="material-icons text-[#004aad] text-base">place</span>
                            </div>
                            <div class="w-full text-xs border-gray-200 bg-gray-50 rounded-[1px] pl-12 py-2 border">
                                {{ $participant->event->location }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Registration Info -->
            <div class="border-b border-gray-200 pb-5">
                <h2 class="text-sm font-semibold text-gray-700 mb-4">Registration Information</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Registration Date -->
                    <div>
                        <label class="flex items-center text-xs font-medium text-gray-700 mb-1">
                            <span class="material-icons text-sm mr-1 text-primary-DEFAULT">event_available</span>
                            Registration Date
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <span class="material-icons text-[#004aad] text-base">calendar_today</span>
                            </div>
                            <div class="w-full text-xs border-gray-200 bg-gray-50 rounded-[1px] pl-12 py-2 border">
                                @if($participant->registration_date)
                                    {{ \Carbon\Carbon::parse($participant->registration_date)->format('d M Y - H:i') }}
                                @else
                                    Not recorded
                                @endif
                            </div>
                        </div>
                    </div>
                    
                    <!-- Attendance Date -->
                    <div>
                        <label class="flex items-center text-xs font-medium text-gray-700 mb-1">
                            <span class="material-icons text-sm mr-1 text-primary-DEFAULT">how_to_reg</span>
                            Attendance Date
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <span class="material-icons text-[#004aad] text-base">fact_check</span>
                            </div>
                            <div class="w-full text-xs border-gray-200 bg-gray-50 rounded-[1px] pl-12 py-2 border">
                                @php
                                    $firstAttendance = isset($attendanceRecords) ? $attendanceRecords->sortBy('checkin_time')->first() : null;
                                @endphp
                                @if($firstAttendance && $firstAttendance->checkin_time)
                                    {{ \Carbon\Carbon::parse($firstAttendance->checkin_time)->format('d M Y - H:i') }}
                                @else
                                    Not recorded
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Notes -->
            @if($participant->notes)
            <div>
                <h2 class="text-sm font-semibold text-gray-700 mb-4">Notes</h2>
                <div class="relative">
                    <div class="absolute top-3 left-0 pl-3 flex items-start pointer-events-none">
                        <span class="material-icons text-[#004aad] text-base">notes</span>
                    </div>
                    <div class="w-full text-xs border-gray-200 bg-gray-50 rounded-[1px] pl-12 py-3 border min-h-[60px]">
                        {{ $participant->notes }}
                    </div>
                </div>
            </div>
            @endif

            @if(isset($attendanceRecords) && $attendanceRecords->count())
                <h3 class="mt-6 mb-2 text-base font-medium">Attendance History</h3>
                <div class="overflow-visible border border-gray-200 rounded">
                    <table class="min-w-full border-collapse">
                        <thead>
                            <tr class="bg-primary-light text-white text-xs uppercase">
                                <th class="py-3 px-4 text-left rounded-tl">Event</th>
                                <th class="py-3 px-4 text-left">Date</th>
                                <th class="py-3 px-4 text-left">Check-in</th>
                                <th class="py-3 px-4 text-left">Check-out</th>
                                <th class="py-3 px-4 text-left">Status</th>
                                <th class="py-3 px-4 text-center rounded-tr">Certificate</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach($attendanceRecords as $record)
                                <tr class="text-xs hover:bg-gray-50">
                                    <td class="py-3 px-4 font-medium">{{ $record->attendance->event->name ?? '-' }}</td>
                                    <td class="py-3 px-4">{{ $record->attendanceSession->date ?? ($record->attendance->date ?? '-') }}</td>
                                    <td class="py-3 px-4">{{ $record->checkin_time ? date('H:i', strtotime($record->checkin_time)) : '-' }}</td>
                                    <td class="py-3 px-4">{{ $record->checkout_time ? date('H:i', strtotime($record->checkout_time)) : '-' }}</td>
                                    <td class="py-3 px-4">{{ ucfirst($record->status) }}</td>
                                    <td class="py-3 px-4 text-center">
                                        @php
                                            $cert = $certificates->firstWhere('event_id', $record->attendance->event->id ?? null);
                                        @endphp
                                        @if($cert)
                                            <a href="{{ asset('storage/' . $cert->pdf_file) }}" target="_blank" title="View Certificate PDF">
                                                <span class="material-icons text-red-600 align-middle">picture_as_pdf</span>
                                            </a>
                                        @else
                                            -
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
</x-app-layout> 