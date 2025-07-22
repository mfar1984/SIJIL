<x-app-layout>
    <x-slot name="breadcrumb">
        <span>Event Management</span>
        <span class="mx-2 text-gray-500">/</span>
        <span>View Event</span>
    </x-slot>

    <x-slot name="title">Event Details</x-slot>

    <div class="bg-white rounded shadow-md border border-gray-300">
        <div class="p-6 border-b border-gray-200">
            <div class="flex items-center justify-between">
                <div class="flex items-center">
                    <span class="material-icons mr-2 text-primary-DEFAULT">event_note</span>
                    <h1 class="text-xl font-bold text-gray-800">Event Details</h1>
                </div>
                <div class="flex space-x-3">
                    <a href="{{ route('event.edit', $event->id) }}" class="bg-gradient-to-r from-yellow-500 to-yellow-400 hover:from-yellow-600 hover:to-yellow-500 text-white px-3 py-1 rounded shadow-sm font-medium flex items-center text-xs transition-colors duration-200 ease-in-out">
                        <span class="material-icons text-xs mr-1">edit</span>
                        Edit Event
                    </a>
                    <a href="{{ route('event.management') }}" class="bg-gradient-to-r from-gray-500 to-gray-400 hover:from-gray-600 hover:to-gray-500 text-white px-3 py-1 rounded shadow-sm font-medium flex items-center text-xs transition-colors duration-200 ease-in-out">
                        <span class="material-icons text-xs mr-1">arrow_back</span>
                        Back to List
                    </a>
                </div>
            </div>
            <p class="text-xs text-gray-500 mt-1 ml-8">View detailed information about this event</p>
        </div>
        
        <div class="p-6 space-y-6">
            <!-- Event Status Banner -->
            <div class="rounded p-3 mb-6 flex items-center 
                @if($event->status === 'active')
                    bg-status-active-bg border border-status-active-text/20
                @elseif($event->status === 'pending')
                    bg-status-pending-bg border border-status-pending-text/20
                @elseif($event->status === 'completed')
                    bg-status-completed-bg border border-status-completed-text/20
                @endif
            ">
                <span class="material-icons mr-2 
                    @if($event->status === 'active')
                        text-status-active-text
                    @elseif($event->status === 'pending')
                        text-status-pending-text
                    @elseif($event->status === 'completed')
                        text-status-completed-text
                    @endif
                ">
                    @if($event->status === 'active')
                        event_available
                    @elseif($event->status === 'pending')
                        pending
                    @elseif($event->status === 'completed')
                        event_busy
                    @endif
                </span>
                <div>
                    <p class="font-medium
                        @if($event->status === 'active')
                            text-status-active-text
                        @elseif($event->status === 'pending')
                            text-status-pending-text
                        @elseif($event->status === 'completed')
                            text-status-completed-text
                        @endif
                    ">
                        This event is currently <span class="font-bold">{{ ucfirst($event->status) }}</span>
                    </p>
                    <p class="text-xs text-gray-600">
                        @if($event->status === 'active')
                            Registration is open and the event is available for participants to join.
                        @elseif($event->status === 'pending')
                            Event is in planning stage and not yet open for registration.
                        @elseif($event->status === 'completed')
                            Event has concluded. No new registrations can be accepted.
                        @endif
                    </p>
                </div>
            </div>
            
            <!-- Basic Information -->
            <div class="border-b border-gray-200 pb-5">
                <h2 class="text-sm font-semibold text-gray-700 mb-4">Basic Information</h2>
                <div class="grid grid-cols-1 gap-6">
                    <!-- Event Name -->
                    <div>
                        <label class="flex items-center text-xs font-medium text-gray-700 mb-1">
                            <span class="material-icons text-sm mr-1 text-primary-DEFAULT">event</span>
                            Event Name
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <span class="material-icons text-[#004aad] text-base">title</span>
                            </div>
                            <div class="w-full text-xs border-gray-200 bg-gray-50 rounded-[1px] pl-12 py-2 border">
                                {{ $event->name }}
                            </div>
                        </div>
                    </div>
                    
                    <!-- Organizer -->
                    <div>
                        <label class="flex items-center text-xs font-medium text-gray-700 mb-1">
                            <span class="material-icons text-sm mr-1 text-primary-DEFAULT">people</span>
                            Organizer
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <span class="material-icons text-[#004aad] text-base">business</span>
                            </div>
                            <div class="w-full text-xs border-gray-200 bg-gray-50 rounded-[1px] pl-12 py-2 border">
                                {{ $event->organizer }}
                            </div>
                        </div>
                    </div>
                    
                    <!-- Event Description -->
                    <div class="mt-4">
                        <label class="flex items-center text-xs font-medium text-gray-700 mb-1">
                            <span class="material-icons text-sm mr-1 text-primary-DEFAULT">description</span>
                            Event Description
                        </label>
                        <div class="relative">
                            <div class="w-full text-xs border-gray-200 bg-gray-50 rounded-[1px] py-2 px-3 border min-h-[80px] whitespace-pre-wrap">
                                {{ $event->description ?? 'No description provided.' }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Date and Time -->
            <div class="border-b border-gray-200 pb-5">
                <h2 class="text-sm font-semibold text-gray-700 mb-4">Date and Time</h2>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                    <!-- Start Date & Time -->
                    <div>
                        <label class="flex items-center text-xs font-medium text-gray-700 mb-1">
                            <span class="material-icons text-sm mr-1 text-primary-DEFAULT">event</span>
                            Start Date & Time
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <span class="material-icons text-[#004aad] text-base">calendar_today</span>
                            </div>
                            <div class="w-full text-xs border-gray-200 bg-gray-50 rounded-[1px] pl-12 py-2 border">
                                {{ \Carbon\Carbon::parse($event->start_date)->format('l, d F Y') }} - {{ \Carbon\Carbon::parse($event->start_time)->format('h:iA') }}
                            </div>
                        </div>
                    </div>
                    
                    <!-- End Date & Time -->
                    <div>
                        <label class="flex items-center text-xs font-medium text-gray-700 mb-1">
                            <span class="material-icons text-sm mr-1 text-primary-DEFAULT">event</span>
                            End Date & Time
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <span class="material-icons text-[#004aad] text-base">calendar_today</span>
                            </div>
                            <div class="w-full text-xs border-gray-200 bg-gray-50 rounded-[1px] pl-12 py-2 border">
                                {{ \Carbon\Carbon::parse($event->end_date)->format('l, d F Y') }} - {{ \Carbon\Carbon::parse($event->end_time)->format('h:iA') }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Location -->
            <div class="border-b border-gray-200 pb-5">
                <h2 class="text-sm font-semibold text-gray-700 mb-4">Location & Venue</h2>
                <div class="grid grid-cols-1 gap-6">
                    <!-- Location -->
                    <div>
                        <label class="flex items-center text-xs font-medium text-gray-700 mb-1">
                            <span class="material-icons text-sm mr-1 text-primary-DEFAULT">location_on</span>
                            Location
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <span class="material-icons text-[#004aad] text-base">place</span>
                            </div>
                            <div class="w-full text-xs border-gray-200 bg-gray-50 rounded-[1px] pl-12 py-2 border">
                                {{ $event->location }}
                            </div>
                        </div>
                    </div>
                    
                    <!-- Address -->
                    <div>
                        <label class="flex items-center text-xs font-medium text-gray-700 mb-1">
                            <span class="material-icons text-sm mr-1 text-primary-DEFAULT">pin_drop</span>
                            Address
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <span class="material-icons text-[#004aad] text-base">map</span>
                            </div>
                            <div class="w-full text-xs border-gray-200 bg-gray-50 rounded-[1px] pl-12 py-2 border">
                                {{ $event->address ?? 'No address provided.' }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Capacity and Registration -->
            <div class="border-b border-gray-200 pb-5">
                <h2 class="text-sm font-semibold text-gray-700 mb-4">Capacity & Registration</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Maximum Participants -->
                    <div>
                        <label class="flex items-center text-xs font-medium text-gray-700 mb-1">
                            <span class="material-icons text-sm mr-1 text-primary-DEFAULT">people</span>
                            Maximum Capacity
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <span class="material-icons text-[#004aad] text-base">people_outline</span>
                            </div>
                            <div class="w-full text-xs border-gray-200 bg-gray-50 rounded-[1px] pl-12 py-2 border">
                                {{ $event->max_participants ?? 'Not specified' }}
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Registration Status -->
                <div class="mt-4">
                    @if($event->max_participants && $event->participants->count() >= $event->max_participants)
                        <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded relative" role="alert">
                            <div class="flex">
                                <div class="py-1"><span class="material-icons text-xl mr-2">warning</span></div>
                                <div>
                                    <p class="font-bold">Event is fully booked</p>
                                    <p class="text-sm">The maximum number of participants has been reached.</p>
                                </div>
                            </div>
                        </div>
                    @elseif($event->status === 'active')
                        <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded relative" role="alert">
                            <div class="flex">
                                <div class="py-1"><span class="material-icons text-xl mr-2">check_circle</span></div>
                                <div>
                                    <p class="font-bold">Registration is open</p>
                                    <p class="text-sm">
                                        @if($event->max_participants)
                                        {{ $event->max_participants - $event->participants->count() }} spots remaining.
                                        @else
                                        Unlimited spots available.
                                        @endif
                                    </p>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
            
            <!-- Contact Information -->
            <div class="border-b border-gray-200 pb-5">
                <h2 class="text-sm font-semibold text-gray-700 mb-4">Contact Information</h2>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <!-- Contact Person -->
                    <div>
                        <label class="flex items-center text-xs font-medium text-gray-700 mb-1">
                            <span class="material-icons text-sm mr-1 text-primary-DEFAULT">person</span>
                            Contact Person
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <span class="material-icons text-[#004aad] text-base">person_outline</span>
                            </div>
                            <div class="w-full text-xs border-gray-200 bg-gray-50 rounded-[1px] pl-12 py-2 border">
                                {{ $event->contact_person ?? 'Not specified' }}
                            </div>
                        </div>
                    </div>
                    
                    <!-- Contact Email -->
                    <div>
                        <label class="flex items-center text-xs font-medium text-gray-700 mb-1">
                            <span class="material-icons text-sm mr-1 text-primary-DEFAULT">email</span>
                            Contact Email
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <span class="material-icons text-[#004aad] text-base">mail_outline</span>
                            </div>
                            <div class="w-full text-xs border-gray-200 bg-gray-50 rounded-[1px] pl-12 py-2 border">
                                @if($event->contact_email)
                                <a href="mailto:{{ $event->contact_email }}" class="text-blue-600 hover:underline">
                                    {{ $event->contact_email }}
                                </a>
                                @else
                                Not specified
                                @endif
                            </div>
                        </div>
                    </div>
                    
                    <!-- Contact Phone -->
                    <div>
                        <label class="flex items-center text-xs font-medium text-gray-700 mb-1">
                            <span class="material-icons text-sm mr-1 text-primary-DEFAULT">phone</span>
                            Contact Phone
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <span class="material-icons text-[#004aad] text-base">call</span>
                            </div>
                            <div class="w-full text-xs border-gray-200 bg-gray-50 rounded-[1px] pl-12 py-2 border">
                                @if($event->contact_phone)
                                <a href="tel:{{ $event->contact_phone }}" class="text-blue-600 hover:underline">
                                    {{ $event->contact_phone }}
                                </a>
                                @else
                                Not specified
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Administrative Information -->
            <div>
                <h2 class="text-sm font-semibold text-gray-700 mb-4">Administrative Information</h2>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <!-- Created Date -->
                    <div>
                        <label class="flex items-center text-xs font-medium text-gray-700 mb-1">
                            <span class="material-icons text-sm mr-1 text-primary-DEFAULT">today</span>
                            Created Date
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <span class="material-icons text-[#004aad] text-base">date_range</span>
                            </div>
                            <div class="w-full text-xs border-gray-200 bg-gray-50 rounded-[1px] pl-12 py-2 border">
                                {{ $event->created_at->format('d M Y - H:i') }}
                            </div>
                        </div>
                    </div>
                    
                    <!-- Updated Date -->
                    <div>
                        <label class="flex items-center text-xs font-medium text-gray-700 mb-1">
                            <span class="material-icons text-sm mr-1 text-primary-DEFAULT">update</span>
                            Updated Date
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <span class="material-icons text-[#004aad] text-base">edit_calendar</span>
                            </div>
                            <div class="w-full text-xs border-gray-200 bg-gray-50 rounded-[1px] pl-12 py-2 border">
                                {{ $event->updated_at->format('d M Y - H:i') }}
                            </div>
                        </div>
                    </div>
                    
                    <!-- Created By -->
                    <div>
                        <label class="flex items-center text-xs font-medium text-gray-700 mb-1">
                            <span class="material-icons text-sm mr-1 text-primary-DEFAULT">person</span>
                            Created By
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <span class="material-icons text-[#004aad] text-base">account_circle</span>
                            </div>
                            <div class="w-full text-xs border-gray-200 bg-gray-50 rounded-[1px] pl-12 py-2 border">
                                {{ $event->user ? $event->user->name : 'Unknown' }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout> 