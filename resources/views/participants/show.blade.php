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
                    <span class="material-icons-outlined mr-2 text-primary-DEFAULT">person</span>
                    <h1 class="text-xl font-bold text-gray-800">Participant Details</h1>
                </div>
                <div class="flex space-x-3">
                    @can('participants.update')
                    <a href="{{ route('participants.edit', $participant->id) }}" class="bg-gradient-to-r from-yellow-500 to-yellow-400 hover:from-yellow-600 hover:to-yellow-500 text-white px-3 h-[36px] rounded shadow-sm font-medium flex items-center text-xs transition-colors duration-200 ease-in-out">
                        <span class="material-icons-outlined text-xs mr-1">edit</span>
                        Edit Participant
                    </a>
                    @endcan
                    @can('participants.delete')
                    <form method="POST" action="{{ route('participants.destroy', $participant->id) }}" onsubmit="return confirm('Are you sure you want to delete this participant?')" class="inline-block">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="bg-gradient-to-r from-red-500 to-red-400 hover:from-red-600 hover:to-red-500 text-white px-3 h-[36px] rounded shadow-sm font-medium flex items-center text-xs transition-colors duration-200 ease-in-out">
                            <span class="material-icons-outlined text-xs mr-1">delete</span>
                            Delete Participant
                        </button>
                    </form>
                    @endcan
                    <a href="{{ route('participants') }}" class="bg-gradient-to-r from-gray-500 to-gray-400 hover:from-gray-600 hover:to-gray-500 text-white px-3 h-[36px] rounded shadow-sm font-medium flex items-center text-xs transition-colors duration-200 ease-in-out">
                        <span class="material-icons-outlined text-xs mr-1">arrow_back</span>
                        Back to List
                    </a>
                </div>
            </div>
            <p class="text-xs text-gray-500 mt-1 ml-8">View detailed information about this participant</p>
        </div>
        
        <div class="p-6 space-y-2">
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
                        <!-- Name -->
                        <div class="flex flex-col md:flex-row md:items-center gap-3">
                            <label class="text-xs font-medium text-gray-700 md:w-40">
                                Full Name
                            </label>
                            <div class="flex-1">
                                <div class="relative">
                                    <div class="w-full text-xs border-gray-200 bg-gray-50 rounded-[1px] px-3 py-2 border">
                                        {{ $participant->name }}
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Email -->
                        <div class="flex flex-col md:flex-row md:items-center gap-3">
                            <label class="text-xs font-medium text-gray-700 md:w-40">
                                Email Address
                            </label>
                            <div class="flex-1">
                                <div class="relative">
                                    <div class="w-full text-xs border-gray-200 bg-gray-50 rounded-[1px] px-3 py-2 border">
                                        {{ $participant->email }}
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Phone -->
                        <div class="flex flex-col md:flex-row md:items-center gap-3">
                            <label class="text-xs font-medium text-gray-700 md:w-40">
                                Phone Number
                            </label>
                            <div class="flex-1">
                                <div class="relative">
                                    <div class="w-full text-xs border-gray-200 bg-gray-50 rounded-[1px] px-3 py-2 border">
                                        {{ $participant->formatted_phone ?? 'Not specified' }}
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- IC/Passport -->
                        <div class="flex flex-col md:flex-row md:items-center gap-3">
                            <label class="text-xs font-medium text-gray-700 md:w-40">
                                IC / Passport No.
                            </label>
                            <div class="flex-1">
                                <div class="relative">
                                    <div class="w-full text-xs border-gray-200 bg-gray-50 rounded-[1px] px-3 py-2 border">
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
                        </div>
                        
                        <!-- Date of Birth -->
                        <div class="flex flex-col md:flex-row md:items-center gap-3">
                            <label class="text-xs font-medium text-gray-700 md:w-40">
                                Date of Birth
                            </label>
                            <div class="flex-1">
                                <div class="relative">
                                    <div class="w-full text-xs border-gray-200 bg-gray-50 rounded-[1px] px-3 py-2 border">
                                        {{ $participant->date_of_birth ? $participant->date_of_birth->format('d M Y') : 'Not specified' }}
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Address -->
                        <div class="flex flex-col md:flex-row md:items-start gap-3">
                            <label class="text-xs font-medium text-gray-700 md:w-40 pt-2">
                                Address
                            </label>
                            <div class="flex-1">
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
                    </div>
                </div>
            </div>
            
            <!-- Additional Information -->
            <div class="bg-white border border-gray-200 rounded-md shadow-sm">
                <div class="bg-gray-50 border-b border-gray-200 px-4 py-3">
                    <h2 class="text-sm font-semibold text-gray-700 flex items-center">
                        <span class="material-icons-outlined text-primary-DEFAULT mr-2">info</span>
                        Additional Information
                    </h2>
                </div>
                
                <div class="p-4">
                    <div class="space-y-3">
                        <!-- Gender -->
                        <div class="flex flex-col md:flex-row md:items-center gap-3">
                            <label class="text-xs font-medium text-gray-700 md:w-40">
                                Gender
                            </label>
                            <div class="flex-1">
                                <div class="relative">
                                    <div class="w-full text-xs border-gray-200 bg-gray-50 rounded-[1px] px-3 py-2 border">
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
                        </div>
                        
                        <!-- Organization -->
                        <div class="flex flex-col md:flex-row md:items-center gap-3">
                            <label class="text-xs font-medium text-gray-700 md:w-40">
                                Organization/Company
                            </label>
                            <div class="flex-1">
                                <div class="relative">
                                    <div class="w-full text-xs border-gray-200 bg-gray-50 rounded-[1px] px-3 py-2 border">
                                        {{ $participant->organization ?? 'Not specified' }}
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Job Title -->
                        <div class="flex flex-col md:flex-row md:items-center gap-3">
                            <label class="text-xs font-medium text-gray-700 md:w-40">
                                Job Title
                            </label>
                            <div class="flex-1">
                                <div class="relative">
                                    <div class="w-full text-xs border-gray-200 bg-gray-50 rounded-[1px] px-3 py-2 border">
                                        {{ $participant->job_title ?? 'Not specified' }}
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Race -->
                        <div class="flex flex-col md:flex-row md:items-center gap-3">
                            <label class="text-xs font-medium text-gray-700 md:w-40">
                                Race (Bangsa)
                            </label>
                            <div class="flex-1">
                                <div class="relative">
                                    <div class="w-full text-xs border-gray-200 bg-gray-50 rounded-[1px] px-3 py-2 border">
                                        {{ $participant->race ?? 'Not specified' }}
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Status -->
                        <div class="flex flex-col md:flex-row md:items-center gap-3">
                            <label class="text-xs font-medium text-gray-700 md:w-40">
                                Status
                            </label>
                            <div class="flex-1">
                                <div class="relative">
                                    <div class="w-full text-xs border-gray-200 bg-gray-50 rounded-[1px] px-3 py-2 border flex items-center">
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
                </div>
            </div>
            
            <!-- Event Information -->
            <div class="bg-white border border-gray-200 rounded-md shadow-sm">
                <div class="bg-gray-50 border-b border-gray-200 px-4 py-3">
                    <h2 class="text-sm font-semibold text-gray-700 flex items-center">
                        <span class="material-icons-outlined text-primary-DEFAULT mr-2">event</span>
                        Event Information
                    </h2>
                </div>
                
                <div class="p-4">
                    <div class="space-y-3">
                        <!-- Event Name -->
                        <div class="flex flex-col md:flex-row md:items-center gap-3">
                            <label class="text-xs font-medium text-gray-700 md:w-40">
                                Event Name
                            </label>
                            <div class="flex-1">
                                <div class="relative">
                                    <div class="w-full text-xs border-gray-200 bg-gray-50 rounded-[1px] px-3 py-2 border">
                                        {{ $participant->event->name }}
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Event Organizer -->
                        <div class="flex flex-col md:flex-row md:items-center gap-3">
                            <label class="text-xs font-medium text-gray-700 md:w-40">
                                Event Organizer
                            </label>
                            <div class="flex-1">
                                <div class="relative">
                                    <div class="w-full text-xs border-gray-200 bg-gray-50 rounded-[1px] px-3 py-2 border">
                                        {{ $participant->event->organizer }}
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Event Date -->
                        <div class="flex flex-col md:flex-row md:items-center gap-3">
                            <label class="text-xs font-medium text-gray-700 md:w-40">
                                Event Date
                            </label>
                            <div class="flex-1">
                                <div class="relative">
                                    <div class="w-full text-xs border-gray-200 bg-gray-50 rounded-[1px] px-3 py-2 border">
                                        {{ $participant->event->start_date->format('l, d M Y') }} - {{ \Carbon\Carbon::parse($participant->event->start_time)->format('h:iA') }} to {{ $participant->event->end_date->format('l, d M Y') }} - {{ \Carbon\Carbon::parse($participant->event->end_time)->format('h:iA') }}
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Event Location -->
                        <div class="flex flex-col md:flex-row md:items-center gap-3">
                            <label class="text-xs font-medium text-gray-700 md:w-40">
                                Event Location
                            </label>
                            <div class="flex-1">
                                <div class="relative">
                                    <div class="w-full text-xs border-gray-200 bg-gray-50 rounded-[1px] px-3 py-2 border">
                                        {{ $participant->event->location }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Registration Information -->
            <div class="bg-white border border-gray-200 rounded-md shadow-sm">
                <div class="bg-gray-50 border-b border-gray-200 px-4 py-3">
                    <h2 class="text-sm font-semibold text-gray-700 flex items-center">
                        <span class="material-icons-outlined text-primary-DEFAULT mr-2">how_to_reg</span>
                        Registration Information
                    </h2>
                </div>
                
                <div class="p-4">
                    <div class="space-y-3">
                        <!-- Registration Date -->
                        <div class="flex flex-col md:flex-row md:items-center gap-3">
                            <label class="text-xs font-medium text-gray-700 md:w-40">
                                Registration Date
                            </label>
                            <div class="flex-1">
                                <div class="relative">
                                    <div class="w-full text-xs border-gray-200 bg-gray-50 rounded-[1px] px-3 py-2 border">
                                        @if($participant->registration_date)
                                            {{ \Carbon\Carbon::parse($participant->registration_date)->format('d M Y - H:i') }}
                                        @else
                                            Not recorded
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Attendance Date -->
                        <div class="flex flex-col md:flex-row md:items-center gap-3">
                            <label class="text-xs font-medium text-gray-700 md:w-40">
                                Attendance Date
                            </label>
                            <div class="flex-1">
                                <div class="relative">
                                    <div class="w-full text-xs border-gray-200 bg-gray-50 rounded-[1px] px-3 py-2 border">
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
                </div>
            </div>
            
            <!-- Notes -->
            @if($participant->notes)
            <div class="bg-white border border-gray-200 rounded-md shadow-sm">
                <div class="bg-gray-50 border-b border-gray-200 px-4 py-3">
                    <h2 class="text-sm font-semibold text-gray-700 flex items-center">
                        <span class="material-icons-outlined text-primary-DEFAULT mr-2">notes</span>
                        Notes
                    </h2>
                </div>
                
                <div class="p-4">
                    <div class="space-y-3">
                        <div class="flex flex-col md:flex-row md:items-start gap-3">
                            <label class="text-xs font-medium text-gray-700 md:w-40 pt-2">
                                Notes
                            </label>
                            <div class="flex-1">
                                <div class="w-full text-xs border-gray-200 bg-gray-50 rounded-[1px] py-2 px-3 border min-h-[60px]">
                                    {{ $participant->notes }}
                                </div>
                            </div>
                        </div>
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
                                                <span class="material-icons-outlined text-red-600 align-middle">picture_as_pdf</span>
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