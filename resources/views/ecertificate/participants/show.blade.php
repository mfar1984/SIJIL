<x-app-layout>
    <x-slot name="breadcrumb">
        <span>PWA Management</span>
        <span class="mx-2 text-gray-500">/</span>
        <a href="{{ route('pwa.participants') }}" class="text-indigo-600 hover:text-indigo-800">Participants</a>
        <span class="mx-2 text-gray-500">/</span>
        <span>{{ $participant->name }}</span>
    </x-slot>
    <x-slot name="title">PWA Participant Details</x-slot>
    <div class="bg-white rounded shadow-md border border-gray-300">
        <div class="p-6 border-b border-gray-200">
            <div class="flex items-center justify-between">
                <div class="flex items-center">
                    <span class="material-icons-outlined mr-2 text-primary-DEFAULT">person</span>
                    <h1 class="text-xl font-bold text-gray-800">PWA Participant Details</h1>
                </div>
                <div class="flex space-x-3">
                    @can('pwa_participants.update')
                    <a href="{{ route('pwa.participants.edit', $participant->id) }}" class="bg-gradient-to-r from-yellow-500 to-yellow-400 hover:from-yellow-600 hover:to-yellow-500 text-white px-3 h-[36px] rounded shadow-sm font-medium flex items-center text-xs transition-colors duration-200 ease-in-out">
                        <span class="material-icons-outlined text-xs mr-1">edit</span>
                        Edit Participant
                    </a>
                    @endcan
                    @can('pwa_participants.update')
                    <form method="POST" action="{{ route('pwa.participants.reset-password', $participant->id) }}" onsubmit="return confirm('Are you sure you want to reset the password for {{ $participant->name }}?')" class="inline-block">
                        @csrf
                        <button type="submit" class="bg-purple-500 hover:bg-purple-600 text-white px-3 h-[36px] rounded shadow-sm font-medium flex items-center text-xs transition-colors duration-200 ease-in-out">
                            <span class="material-icons-outlined text-xs mr-1">lock_reset</span>
                            Reset Password
                        </button>
                    </form>
                    @endcan
                    <a href="{{ route('pwa.participants') }}" class="bg-gradient-to-r from-gray-500 to-gray-400 hover:from-gray-600 hover:to-gray-500 text-white px-3 h-[36px] rounded shadow-sm font-medium flex items-center text-xs transition-colors duration-200 ease-in-out">
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
                            <label class="text-xs font-medium text-gray-700 md:w-48 shrink-0">
                                Full Name
                            </label>
                            <div class="flex-1">
                                <div class="relative">
                                    <div class="w-full text-xs border-gray-200 bg-gray-50 rounded px-3 py-2 border">
                                        {{ $participant->name }}
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Email -->
                        <div class="flex flex-col md:flex-row md:items-center gap-3">
                            <label class="text-xs font-medium text-gray-700 md:w-48 shrink-0">
                                Email Address
                            </label>
                            <div class="flex-1">
                                <div class="relative">
                                    <div class="w-full text-xs border-gray-200 bg-gray-50 rounded px-3 py-2 border">
                                        {{ $participant->email }}
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Phone -->
                        <div class="flex flex-col md:flex-row md:items-center gap-3">
                            <label class="text-xs font-medium text-gray-700 md:w-48 shrink-0">
                                Phone Number
                            </label>
                            <div class="flex-1">
                                <div class="relative">
                                    <div class="w-full text-xs border-gray-200 bg-gray-50 rounded px-3 py-2 border">
                                        {{ $participant->phone ?? 'Not specified' }}
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Identity Card / Passport No. -->
                        <div class="flex flex-col md:flex-row md:items-center gap-3">
                            <label class="text-xs font-medium text-gray-700 md:w-48 shrink-0">
                                Identity Card / Passport No.
                            </label>
                            <div class="flex-1">
                                <div class="relative">
                                    <div class="w-full text-xs border-gray-200 bg-gray-50 rounded px-3 py-2 border">
                                        @if($participant->identity_card)
                                            {{ $participant->identity_card }} (IC)
                                        @elseif($participant->passport_no)
                                            {{ $participant->passport_no }} (Passport)
                                        @else
                                            Not specified
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Date of Birth -->
                        <div class="flex flex-col md:flex-row md:items-center gap-3">
                            <label class="text-xs font-medium text-gray-700 md:w-48 shrink-0">
                                Date of Birth
                            </label>
                            <div class="flex-1">
                                <div class="relative">
                                    <div class="w-full text-xs border-gray-200 bg-gray-50 rounded px-3 py-2 border">
                                        {{ $participant->date_of_birth ? \Carbon\Carbon::parse($participant->date_of_birth)->format('d M Y') : 'Not specified' }}
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Address -->
                        <div class="flex flex-col md:flex-row md:items-start gap-3">
                            <label class="text-xs font-medium text-gray-700 md:w-48 shrink-0 pt-2">
                                Address
                            </label>
                            <div class="flex-1">
                                <div class="w-full text-xs border-gray-200 bg-gray-50 rounded py-2 px-3 border min-h-[40px]">
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
                                        <p class="whitespace-pre-line mb-0">{{ implode("\n", $addressParts) }}</p>
                                    @elseif($participant->address)
                                        <p class="whitespace-pre-line mb-0">{{ $participant->address }}</p>
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
                        <span class="material-icons-outlined text-primary-DEFAULT mr-2">description</span>
                        Additional Information
                    </h2>
                </div>
                
                <div class="p-4">
                    <div class="space-y-3">
                        <!-- Gender -->
                        <div class="flex flex-col md:flex-row md:items-center gap-3">
                            <label class="text-xs font-medium text-gray-700 md:w-48 shrink-0">
                                Gender
                            </label>
                            <div class="flex-1">
                                <div class="relative">
                                    <div class="w-full text-xs border-gray-200 bg-gray-50 rounded px-3 py-2 border">
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

                        <!-- Race -->
                        <div class="flex flex-col md:flex-row md:items-center gap-3">
                            <label class="text-xs font-medium text-gray-700 md:w-48 shrink-0">
                                Race
                            </label>
                            <div class="flex-1">
                                <div class="w-full text-xs border-gray-200 bg-gray-50 rounded px-3 py-2 border">
                                    {{ $participant->race ?: 'Not specified' }}
                                </div>
                            </div>
                        </div>

                        <!-- Organization/Company -->
                        <div class="flex flex-col md:flex-row md:items-center gap-3">
                            <label class="text-xs font-medium text-gray-700 md:w-48 shrink-0">
                                Organization/Company
                            </label>
                            <div class="flex-1">
                                <div class="relative">
                                    <div class="w-full text-xs border-gray-200 bg-gray-50 rounded px-3 py-2 border">
                                        {{ $participant->organization ?? 'Not specified' }}
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Job Title -->
                        <div class="flex flex-col md:flex-row md:items-center gap-3">
                            <label class="text-xs font-medium text-gray-700 md:w-48 shrink-0">
                                Job Title
                            </label>
                            <div class="flex-1">
                                <div class="relative">
                                    <div class="w-full text-xs border-gray-200 bg-gray-50 rounded px-3 py-2 border">
                                        {{ $participant->job_title ?? 'Not specified' }}
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Status -->
                        <div class="flex flex-col md:flex-row md:items-center gap-3">
                            <label class="text-xs font-medium text-gray-700 md:w-48 shrink-0">
                                Status
                            </label>
                            <div class="flex-1">
                                <div class="relative">
                                    <div class="w-full text-xs border-gray-200 bg-gray-50 rounded px-3 py-2 border flex items-center">
                                        @if($status === 'active')
                                            <span class="bg-status-active-bg text-status-active-text px-2 py-0.5 rounded-full text-xs">Active</span>
                                        @else
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
                    <div class="overflow-visible border border-gray-200 rounded">
                        <table class="min-w-full border-collapse">
                            <thead>
                                <tr class="bg-primary-light text-white text-xs uppercase">
                                    <th class="py-3 px-4 text-left rounded-tl">Event Name</th>
                                    <th class="py-3 px-4 text-left">Organizer</th>
                                    <th class="py-3 px-4 text-left">Start Date</th>
                                    <th class="py-3 px-4 text-left">End Date</th>
                                    <th class="py-3 px-4 text-left">Location</th>
                                    <th class="py-3 px-4 text-left">Registered</th>
                                    <th class="py-3 px-4 text-left">Registered At</th>
                                    <th class="py-3 px-4 text-left">Checked In</th>
                                    <th class="py-3 px-4 text-left">Checked Out</th>
                                    <th class="py-3 px-4 text-left">Notes</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @forelse($eventDetails as $detail)
                                    @php $event = $detail['event']; @endphp
                                    <tr class="text-xs hover:bg-gray-50">
                                        <td class="py-3 px-4 font-medium">{{ $event->title ?? $event->name ?? '-' }}</td>
                                        <td class="py-3 px-4">{{ $event->organizer ?? '-' }}</td>
                                        <td class="py-3 px-4">{{ $event->start_date ? \Carbon\Carbon::parse($event->start_date)->format('d M Y') : '-' }}</td>
                                        <td class="py-3 px-4">{{ $event->end_date ? \Carbon\Carbon::parse($event->end_date)->format('d M Y') : '-' }}</td>
                                        <td class="py-3 px-4">{{ $event->location ?? '-' }}</td>
                                        <td class="py-3 px-4">{{ $detail['is_registered'] ? 'Yes' : 'No' }}</td>
                                        <td class="py-3 px-4">{{ $detail['registered_at'] ? \Carbon\Carbon::parse($detail['registered_at'])->format('d M Y H:i') : '-' }}</td>
                                        <td class="py-3 px-4">{{ $detail['checked_in_at'] ? \Carbon\Carbon::parse($detail['checked_in_at'])->format('d M Y H:i') : '-' }}</td>
                                        <td class="py-3 px-4">{{ $detail['checked_out_at'] ? \Carbon\Carbon::parse($detail['checked_out_at'])->format('d M Y H:i') : '-' }}</td>
                                        <td class="py-3 px-4">{{ $detail['pivot_notes'] ?? '-' }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="10" class="text-xs text-gray-500 py-3 px-4">No events assigned</td></tr>
                                @endforelse
                            </tbody>
                        </table>
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
                    <div class="w-full text-xs border-gray-200 bg-gray-50 rounded px-3 py-2 border whitespace-pre-line">
                        {{ $participant->notes }}
                    </div>
                </div>
            </div>
            @endif
            <!-- Sidebar/Stats -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-6">
                <div class="bg-white border border-gray-200 rounded-lg p-4">
                    <h3 class="text-sm font-semibold text-gray-800 mb-4 flex items-center">
                        <span class="material-icons-outlined text-xs mr-2">analytics</span>
                        Quick Stats
                    </h3>
                    <div class="space-y-3">
                        <div class="flex justify-between items-center">
                            <span class="text-xs text-gray-600">Events Assigned</span>
                            <span class="text-sm font-medium text-gray-800">{{ $participant->events->count() }}</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-xs text-gray-600">Certificates Earned</span>
                            <span class="text-sm font-medium text-gray-800">{{ $participant->certificates->count() }}</span>
                        </div>
                    </div>
                </div>
                <div class="bg-white border border-gray-200 rounded-lg p-4">
                    <h3 class="text-sm font-semibold text-gray-800 mb-4 flex items-center">
                        <span class="material-icons-outlined text-xs mr-2">security</span>
                        Security Status
                    </h3>
                    <div class="space-y-3">
                        <div class="flex items-center justify-between">
                            <span class="text-xs text-gray-600">Login Attempts</span>
                            <span class="text-sm font-medium text-gray-800">{{ $participant->login_attempts ?? 0 }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-xs text-gray-600">Account Locked</span>
                            <span class="text-sm font-medium {{ $participant->locked_until ? 'text-red-600' : 'text-green-600' }}">
                                {{ $participant->locked_until ? 'Yes' : 'No' }}
                            </span>
                        </div>
                        @if($participant->locked_until)
                            <div class="flex items-center justify-between">
                                <span class="text-xs text-gray-600">Locked Until</span>
                                <span class="text-sm font-medium text-red-600">{{ $participant->locked_until->format('M d, Y H:i') }}</span>
                            </div>
                        @endif
                    </div>
                </div>
                <div class="bg-white border border-gray-200 rounded-lg p-4">
                    <h3 class="text-sm font-semibold text-gray-800 mb-4 flex items-center">
                        <span class="material-icons-outlined text-xs mr-2">history</span>
                        Recent Activity
                    </h3>
                    <div class="space-y-2">
                        @if($participant->last_login_at)
                            <div class="text-xs text-gray-600">
                                <span class="font-medium">Last Login:</span><br>
                                {{ $participant->last_login_at->diffForHumans() }}
                            </div>
                        @endif
                        @if($participant->password_changed_at)
                            <div class="text-xs text-gray-600">
                                <span class="font-medium">Password Changed:</span><br>
                                {{ $participant->password_changed_at->diffForHumans() }}
                            </div>
                        @endif
                        <div class="text-xs text-gray-600">
                            <span class="font-medium">Account Created:</span><br>
                            {{ $participant->created_at->diffForHumans() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout> 