<x-app-layout>
    <x-slot name="breadcrumb">
        <span>Event Management</span>
        <span class="mx-2 text-gray-500">/</span>
        <span>Edit Event</span>
    </x-slot>

    <x-slot name="title">Edit Event</x-slot>

    <style>
        /* Tooltip styles */
        .tooltip-wrapper {
            position: relative;
            display: inline-flex;
        }
        
        .tooltip-content {
            position: absolute;
            bottom: 100%;
            left: 50%;
            transform: translateX(-50%) translateY(-4px);
            background-color: #1f2937;
            color: white;
            padding: 6px 10px;
            border-radius: 6px;
            font-size: 11px;
            white-space: nowrap;
            z-index: 1000;
            pointer-events: none;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        }
        
        .tooltip-content::after {
            content: '';
            position: absolute;
            top: 100%;
            left: 50%;
            transform: translateX(-50%);
            border: 4px solid transparent;
            border-top-color: #1f2937;
        }
    </style>

    <div class="bg-white rounded shadow-md border border-gray-300">
        <div class="p-6 border-b border-gray-200">
            <div class="flex items-center">
                <span class="material-icons-outlined mr-2 text-primary-DEFAULT">event_note</span>
                <h1 class="text-xl font-bold text-gray-800">Edit Event: {{ $event->name }}</h1>
            </div>
            <p class="text-xs text-gray-500 mt-1 ml-8">Modify event information</p>
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

            <form method="POST" action="{{ route('event.update', $event->id) }}" class="space-y-2" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                
                <!-- Basic Information Section -->
                <div class="bg-white border border-gray-200 rounded-md shadow-sm">
                    <div class="bg-gray-50 border-b border-gray-200 px-4 py-3">
                        <h2 class="text-sm font-semibold text-gray-700 flex items-center">
                            <span class="material-icons-outlined text-primary-DEFAULT mr-2">info</span>
                            Basic Information
                        </h2>
                    </div>
                    
                    <div class="p-4">
                        <div class="space-y-3">
                            <!-- Event Name -->
                            <div class="flex flex-col md:flex-row md:items-center gap-3">
                                <label for="name" class="text-xs font-medium text-gray-700 md:w-40 flex items-center gap-1">
                                    Event Name
                                    <div class="tooltip-wrapper" x-data="{ show: false }">
                                        <span class="material-icons-outlined text-gray-400 text-sm cursor-help" 
                                              @mouseenter="show = true" 
                                              @mouseleave="show = false">
                                            help_outline
                                        </span>
                                        <div x-show="show" x-transition class="tooltip-content">
                                            Enter a descriptive name for the event
                                        </div>
                                    </div>
                                </label>
                                <div class="flex-1">
                                    <div class="relative">
                                        <input 
                                            type="text" 
                                            name="name" 
                                            id="name" 
                                            class="w-full text-xs border-gray-300 rounded-[1px] px-3 focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50" 
                                            value="{{ old('name', $event->name) }}" 
                                            required
                                        >
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Organizer -->
                            <div class="flex flex-col md:flex-row md:items-center gap-3">
                                <label for="organizer" class="text-xs font-medium text-gray-700 md:w-40 flex items-center gap-1">
                                    Organizer
                                    <div class="tooltip-wrapper" x-data="{ show: false }">
                                        <span class="material-icons-outlined text-gray-400 text-sm cursor-help" 
                                              @mouseenter="show = true" 
                                              @mouseleave="show = false">
                                            help_outline
                                        </span>
                                        <div x-show="show" x-transition class="tooltip-content">
                                            Department or organization hosting the event
                                        </div>
                                    </div>
                                </label>
                                <div class="flex-1">
                                    <div class="relative">
                                        <input 
                                            type="text" 
                                            name="organizer" 
                                            id="organizer" 
                                            class="w-full text-xs border-gray-300 rounded-[1px] px-3 focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50"
                                            value="{{ old('organizer', $event->organizer) }}"
                                            required
                                        >
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Description -->
                            <div class="flex flex-col md:flex-row md:items-start gap-3">
                                <label for="description" class="text-xs font-medium text-gray-700 md:w-40 pt-2 flex items-center gap-1">
                                    Description
                                    <div class="tooltip-wrapper" x-data="{ show: false }">
                                        <span class="material-icons-outlined text-gray-400 text-sm cursor-help" 
                                              @mouseenter="show = true" 
                                              @mouseleave="show = false">
                                            help_outline
                                        </span>
                                        <div x-show="show" x-transition class="tooltip-content">
                                            Provide a detailed description of the event
                                        </div>
                                    </div>
                                </label>
                                <div class="flex-1">
                                    <textarea 
                                        name="description" 
                                        id="description" 
                                        rows="3" 
                                        class="w-full text-xs border-gray-300 rounded focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50"
                                    >{{ old('description', $event->description) }}</textarea>
                                </div>
                            </div>
                            
                            <!-- Poster -->
                            <div class="flex flex-col md:flex-row md:items-center gap-3">
                                <label for="poster" class="text-xs font-medium text-gray-700 md:w-40 flex items-center gap-1">
                                    Poster
                                    <div class="tooltip-wrapper" x-data="{ show: false }">
                                        <span class="material-icons-outlined text-gray-400 text-sm cursor-help" 
                                              @mouseenter="show = true" 
                                              @mouseleave="show = false">
                                            help_outline
                                        </span>
                                        <div x-show="show" x-transition class="tooltip-content">
                                            JPG/PNG/WebP, max 2 MB, portrait 1200×1600 (3:4)
                                        </div>
                                    </div>
                                </label>
                                <div class="flex-1">
                                    <div class="flex gap-2">
                                        <input 
                                            type="text" 
                                            id="poster-filename" 
                                            readonly 
                                            placeholder="No file chosen"
                                            class="flex-1 h-9 text-xs border-gray-300 rounded bg-gray-50 cursor-default focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50"
                                        >
                                        <label for="poster" class="px-4 h-9 flex items-center justify-center bg-gray-100 hover:bg-gray-200 border border-gray-300 rounded text-xs font-medium text-gray-700 cursor-pointer">
                                            Browse
                                        </label>
                                        <input 
                                            type="file" 
                                            name="poster" 
                                            id="poster" 
                                            accept="image/png,image/jpeg,image/webp" 
                                            class="hidden"
                                            onchange="document.getElementById('poster-filename').value = this.files[0] ? this.files[0].name : ''"
                                        >
                                    </div>
                                    @if($event->poster)
                                        <p class="mt-1 text-[10px] text-gray-500">Current: <a href="{{ asset('storage/'.$event->poster) }}" target="_blank" class="text-blue-600 underline">view poster</a></p>
                                    @endif
                                </div>
                            </div>
                            
                            <!-- Terms & Conditions -->
                            <div class="flex flex-col md:flex-row md:items-start gap-3">
                                <label for="condition" class="text-xs font-medium text-gray-700 md:w-40 pt-2 flex items-center gap-1">
                                    Terms & Conditions
                                    <div class="tooltip-wrapper" x-data="{ show: false }">
                                        <span class="material-icons-outlined text-gray-400 text-sm cursor-help" 
                                              @mouseenter="show = true" 
                                              @mouseleave="show = false">
                                            help_outline
                                        </span>
                                        <div x-show="show" x-transition class="tooltip-content">
                                            Example: Only participants aged 18+, must bring IC
                                        </div>
                                    </div>
                                </label>
                                <div class="flex-1">
                                    {{-- Fixed height matches the TinyMCE height below so the layout does not jump while the editor loads --}}
                                    <textarea name="condition" id="condition" class="w-full text-sm border-gray-300 rounded" style="height: 380px;" placeholder="Write event terms & conditions here...">{{ old('condition', $event->condition) }}</textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Date and Time Section -->
                <div class="bg-white border border-gray-200 rounded-md shadow-sm">
                    <div class="bg-gray-50 border-b border-gray-200 px-4 py-3">
                        <h2 class="text-sm font-semibold text-gray-700 flex items-center">
                            <span class="material-icons-outlined text-primary-DEFAULT mr-2">schedule</span>
                            Date and Time
                        </h2>
                    </div>
                    
                    <div class="p-4">
                        <div class="space-y-3">
                            <!-- Start Date -->
                            <div class="flex flex-col md:flex-row md:items-center gap-3">
                                <label for="start_date" class="text-xs font-medium text-gray-700 md:w-40 flex items-center gap-1">
                                    Start Date
                                    <div class="tooltip-wrapper" x-data="{ show: false }">
                                        <span class="material-icons-outlined text-gray-400 text-sm cursor-help" 
                                              @mouseenter="show = true" 
                                              @mouseleave="show = false">
                                            help_outline
                                        </span>
                                        <div x-show="show" x-transition class="tooltip-content">
                                            Event start date
                                        </div>
                                    </div>
                                </label>
                                <div class="flex-1">
                                    <div class="relative">
                                        <input 
                                            type="date" 
                                            name="start_date" 
                                            id="start_date" 
                                            class="w-full h-9 text-xs border-gray-300 rounded-[1px] px-3 focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50" 
                                            value="{{ old('start_date', $event->start_date_formatted) }}" 
                                            required
                                        >
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Start Time -->
                            <div class="flex flex-col md:flex-row md:items-center gap-3">
                                <label for="start_time" class="text-xs font-medium text-gray-700 md:w-40 flex items-center gap-1">
                                    Start Time
                                    <div class="tooltip-wrapper" x-data="{ show: false }">
                                        <span class="material-icons-outlined text-gray-400 text-sm cursor-help" 
                                              @mouseenter="show = true" 
                                              @mouseleave="show = false">
                                            help_outline
                                        </span>
                                        <div x-show="show" x-transition class="tooltip-content">
                                            Event start time
                                        </div>
                                    </div>
                                </label>
                                <div class="flex-1">
                                    <div class="relative">
                                        <input 
                                            type="time" 
                                            name="start_time" 
                                            id="start_time" 
                                            class="w-full h-9 text-xs border-gray-300 rounded-[1px] px-3 focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50" 
                                            value="{{ old('start_time', $event->start_time_formatted) }}" 
                                            required
                                        >
                                    </div>
                                </div>
                            </div>
                            
                            <!-- End Date -->
                            <div class="flex flex-col md:flex-row md:items-center gap-3">
                                <label for="end_date" class="text-xs font-medium text-gray-700 md:w-40 flex items-center gap-1">
                                    End Date
                                    <div class="tooltip-wrapper" x-data="{ show: false }">
                                        <span class="material-icons-outlined text-gray-400 text-sm cursor-help" 
                                              @mouseenter="show = true" 
                                              @mouseleave="show = false">
                                            help_outline
                                        </span>
                                        <div x-show="show" x-transition class="tooltip-content">
                                            Event end date
                                        </div>
                                    </div>
                                </label>
                                <div class="flex-1">
                                    <div class="relative">
                                        <input 
                                            type="date" 
                                            name="end_date" 
                                            id="end_date" 
                                            class="w-full h-9 text-xs border-gray-300 rounded-[1px] px-3 focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50" 
                                            value="{{ old('end_date', $event->end_date_formatted) }}" 
                                            required
                                        >
                                    </div>
                                </div>
                            </div>
                            
                            <!-- End Time -->
                            <div class="flex flex-col md:flex-row md:items-center gap-3">
                                <label for="end_time" class="text-xs font-medium text-gray-700 md:w-40 flex items-center gap-1">
                                    End Time
                                    <div class="tooltip-wrapper" x-data="{ show: false }">
                                        <span class="material-icons-outlined text-gray-400 text-sm cursor-help" 
                                              @mouseenter="show = true" 
                                              @mouseleave="show = false">
                                            help_outline
                                        </span>
                                        <div x-show="show" x-transition class="tooltip-content">
                                            Event end time
                                        </div>
                                    </div>
                                </label>
                                <div class="flex-1">
                                    <div class="relative">
                                        <input 
                                            type="time" 
                                            name="end_time" 
                                            id="end_time" 
                                            class="w-full h-9 text-xs border-gray-300 rounded-[1px] px-3 focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50" 
                                            value="{{ old('end_time', $event->end_time_formatted) }}" 
                                            required
                                        >
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Location Section -->
                <div class="bg-white border border-gray-200 rounded-md shadow-sm">
                    <div class="bg-gray-50 border-b border-gray-200 px-4 py-3">
                        <h2 class="text-sm font-semibold text-gray-700 flex items-center">
                            <span class="material-icons-outlined text-primary-DEFAULT mr-2">location_on</span>
                            Location Information
                        </h2>
                    </div>
                    
                    <div class="p-4">
                        <div class="space-y-3">
                            <!-- Venue Name -->
                            <div class="flex flex-col md:flex-row md:items-center gap-3">
                                <label for="location" class="text-xs font-medium text-gray-700 md:w-40 flex items-center gap-1">
                                    Venue Name
                                    <div class="tooltip-wrapper" x-data="{ show: false }">
                                        <span class="material-icons-outlined text-gray-400 text-sm cursor-help" 
                                              @mouseenter="show = true" 
                                              @mouseleave="show = false">
                                            help_outline
                                        </span>
                                        <div x-show="show" x-transition class="tooltip-content">
                                            Name of the venue where event will be held
                                        </div>
                                    </div>
                                </label>
                                <div class="flex-1">
                                    <div class="relative">
                                        <input 
                                            type="text" 
                                            name="location" 
                                            id="location" 
                                            class="w-full text-xs border-gray-300 rounded-[1px] px-3 focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50" 
                                            value="{{ old('location', $event->location) }}" 
                                            required
                                        >
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Address -->
                            <div class="flex flex-col md:flex-row md:items-start gap-3">
                                <label for="address" class="text-xs font-medium text-gray-700 md:w-40 pt-2 flex items-center gap-1">
                                    Complete Address
                                    <div class="tooltip-wrapper" x-data="{ show: false }">
                                        <span class="material-icons-outlined text-gray-400 text-sm cursor-help" 
                                              @mouseenter="show = true" 
                                              @mouseleave="show = false">
                                            help_outline
                                        </span>
                                        <div x-show="show" x-transition class="tooltip-content">
                                            Full address including city and postcode
                                        </div>
                                    </div>
                                </label>
                                <div class="flex-1">
                                    <textarea 
                                        name="address" 
                                        id="address" 
                                        rows="3" 
                                        class="w-full text-xs border-gray-300 rounded focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50"
                                    >{{ old('address', $event->address) }}</textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Participant Information Section -->
                <div class="bg-white border border-gray-200 rounded-md shadow-sm">
                    <div class="bg-gray-50 border-b border-gray-200 px-4 py-3">
                        <h2 class="text-sm font-semibold text-gray-700 flex items-center">
                            <span class="material-icons-outlined text-primary-DEFAULT mr-2">groups</span>
                            Participant Information
                        </h2>
                    </div>
                    
                    <div class="p-4">
                        <div class="space-y-3">
                            <!-- Max Participants -->
                            <div class="flex flex-col md:flex-row md:items-center gap-3">
                                <label for="max_participants" class="text-xs font-medium text-gray-700 md:w-40 flex items-center gap-1">
                                    Maximum Participants
                                    <div class="tooltip-wrapper" x-data="{ show: false }">
                                        <span class="material-icons-outlined text-gray-400 text-sm cursor-help" 
                                              @mouseenter="show = true" 
                                              @mouseleave="show = false">
                                            help_outline
                                        </span>
                                        <div x-show="show" x-transition class="tooltip-content">
                                            Maximum number of participants allowed
                                        </div>
                                    </div>
                                </label>
                                <div class="flex-1">
                                    <div class="relative">
                                        <input 
                                            type="number" 
                                            name="max_participants" 
                                            id="max_participants" 
                                            class="w-full text-xs border-gray-300 rounded-[1px] px-3 focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50" 
                                            value="{{ old('max_participants', $event->max_participants) }}" 
                                            min="1"
                                            required
                                        >
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Contact Information Section -->
                <div class="bg-white border border-gray-200 rounded-md shadow-sm">
                    <div class="bg-gray-50 border-b border-gray-200 px-4 py-3">
                        <h2 class="text-sm font-semibold text-gray-700 flex items-center">
                            <span class="material-icons-outlined text-primary-DEFAULT mr-2">contact_phone</span>
                            Contact Information
                        </h2>
                    </div>
                    
                    <div class="p-4">
                        <div class="space-y-3">
                            <!-- Contact Person -->
                            <div class="flex flex-col md:flex-row md:items-center gap-3">
                                <label for="contact_person" class="text-xs font-medium text-gray-700 md:w-40 flex items-center gap-1">
                                    Contact Person
                                    <div class="tooltip-wrapper" x-data="{ show: false }">
                                        <span class="material-icons-outlined text-gray-400 text-sm cursor-help" 
                                              @mouseenter="show = true" 
                                              @mouseleave="show = false">
                                            help_outline
                                        </span>
                                        <div x-show="show" x-transition class="tooltip-content">
                                            Name of contact person for this event
                                        </div>
                                    </div>
                                </label>
                                <div class="flex-1">
                                    <div class="relative">
                                        <input 
                                            type="text" 
                                            name="contact_person" 
                                            id="contact_person" 
                                            class="w-full text-xs border-gray-300 rounded-[1px] px-3 focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50" 
                                            value="{{ old('contact_person', $event->contact_person ?? '') }}"
                                        >
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Contact Email -->
                            <div class="flex flex-col md:flex-row md:items-center gap-3">
                                <label for="contact_email" class="text-xs font-medium text-gray-700 md:w-40 flex items-center gap-1">
                                    Contact Email
                                    <div class="tooltip-wrapper" x-data="{ show: false }">
                                        <span class="material-icons-outlined text-gray-400 text-sm cursor-help" 
                                              @mouseenter="show = true" 
                                              @mouseleave="show = false">
                                            help_outline
                                        </span>
                                        <div x-show="show" x-transition class="tooltip-content">
                                            Email address for event inquiries
                                        </div>
                                    </div>
                                </label>
                                <div class="flex-1">
                                    <div class="relative">
                                        <input 
                                            type="email" 
                                            name="contact_email" 
                                            id="contact_email" 
                                            class="w-full text-xs border-gray-300 rounded-[1px] px-3 focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50" 
                                            value="{{ old('contact_email', $event->contact_email ?? '') }}"
                                        >
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Contact Phone -->
                            <div class="flex flex-col md:flex-row md:items-center gap-3">
                                <label for="contact_phone" class="text-xs font-medium text-gray-700 md:w-40 flex items-center gap-1">
                                    Contact Phone
                                    <div class="tooltip-wrapper" x-data="{ show: false }">
                                        <span class="material-icons-outlined text-gray-400 text-sm cursor-help" 
                                              @mouseenter="show = true" 
                                              @mouseleave="show = false">
                                            help_outline
                                        </span>
                                        <div x-show="show" x-transition class="tooltip-content">
                                            Phone number for event inquiries
                                        </div>
                                    </div>
                                </label>
                                <div class="flex-1">
                                    <div class="relative">
                                        <input 
                                            type="tel" 
                                            name="contact_phone" 
                                            id="contact_phone" 
                                            class="w-full text-xs border-gray-300 rounded-[1px] px-3 focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50" 
                                            value="{{ old('contact_phone', $event->contact_phone ?? '') }}"
                                            placeholder="+60123456789"
                                        >
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Status & Settings Section -->
                <div class="bg-white border border-gray-200 rounded-md shadow-sm">
                    <div class="bg-gray-50 border-b border-gray-200 px-4 py-3">
                        <h2 class="text-sm font-semibold text-gray-700 flex items-center">
                            <span class="material-icons-outlined text-primary-DEFAULT mr-2">settings</span>
                            Status & Registration Settings
                        </h2>
                    </div>
                    
                    <div class="p-4">
                        <div class="space-y-3">
                            <!-- Event Status -->
                            <div class="flex flex-col md:flex-row md:items-center gap-3">
                                <label for="status" class="text-xs font-medium text-gray-700 md:w-40 flex items-center gap-1">
                                    Event Status
                                    <div class="tooltip-wrapper" x-data="{ show: false }">
                                        <span class="material-icons-outlined text-gray-400 text-sm cursor-help" 
                                              @mouseenter="show = true" 
                                              @mouseleave="show = false">
                                            help_outline
                                        </span>
                                        <div x-show="show" x-transition class="tooltip-content">
                                            Current status of the event
                                        </div>
                                    </div>
                                </label>
                                <div class="flex-1">
                                    <div class="relative">
                                        <select 
                                            name="status" 
                                            id="status" 
                                            class="w-full text-xs border-gray-300 rounded-[1px] px-3 focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50" 
                                            required
                                        >
                                            <option value="pending" {{ old('status', $event->status) == 'pending' ? 'selected' : '' }}>Pending</option>
                                            <option value="active" {{ old('status', $event->status) == 'active' ? 'selected' : '' }}>Active</option>
                                            <option value="completed" {{ old('status', $event->status) == 'completed' ? 'selected' : '' }}>Completed</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- How registration behaves -->
                            <div class="flex flex-col md:flex-row md:items-start gap-3 pt-1">
                                <span class="text-xs font-medium text-gray-700 md:w-40 shrink-0 md:pt-3">
                                    Registration
                                </span>
                                <div class="flex-1 grid grid-cols-1 sm:grid-cols-2 gap-2">
                                    <x-event-toggle
                                        name="disable_auto_expiry"
                                        icon="event_repeat"
                                        label="Keep registration open"
                                        description="The link stays usable after the event start date instead of closing automatically."
                                        :checked="old('disable_auto_expiry', $event->disable_auto_expiry)" />

                                    <x-event-toggle
                                        name="skip_identity_verification"
                                        icon="badge"
                                        label="Skip identity verification"
                                        description="Participants register with name and email only, without an IC or passport number."
                                        :checked="old('skip_identity_verification', $event->skip_identity_verification)" />
                                </div>
                            </div>

                            <!-- What happens automatically once a participant registers -->
                            <div class="flex flex-col md:flex-row md:items-start gap-3 pt-2 border-t border-gray-100">
                                <span class="text-xs font-medium text-gray-700 md:w-40 shrink-0 md:pt-3">
                                    After participant register
                                </span>
                                <div class="flex-1 grid grid-cols-1 md:grid-cols-3 gap-2">
                                    <x-event-toggle
                                        name="auto_pwa_registration"
                                        icon="phone_iphone"
                                        label="Create mobile app account"
                                        description="Creates the participant's E-Certificate app account and emails their sign-in details straight away."
                                        note="Without this, an administrator has to create each account by hand under PWA &rsaquo; Participants."
                                        :checked="old('auto_pwa_registration', $event->auto_pwa_registration)" />

                                    <x-event-toggle
                                        name="auto_generate_certificate"
                                        icon="workspace_premium"
                                        label="Issue certificate immediately"
                                        description="Generates the certificate on registration and delivers it by email, Telegram and SMS."
                                        note="Email and SMS follow your Delivery Configuration."
                                        :checked="old('auto_generate_certificate', $event->auto_generate_certificate)">
                                        {{-- Which template to use. Only relevant while the switch is on. --}}
                                        <div x-show="on" x-transition class="mt-3 pt-3 border-t border-gray-200">
                                            <label for="certificate_template_id" class="block text-xs font-medium text-gray-700 mb-1">
                                                Certificate template
                                            </label>
                                            <select name="certificate_template_id" id="certificate_template_id"
                                                    @click.stop
                                                    class="w-full h-9 text-xs border-gray-300 rounded pl-3 pr-8 focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50">
                                                <option value="">Use my most recent template</option>
                                                @foreach($certificateTemplates as $template)
                                                    <option value="{{ $template->id }}" {{ old('certificate_template_id', $event->certificate_template_id) == $template->id ? 'selected' : '' }}>
                                                        {{ $template->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            @if($certificateTemplates->isEmpty())
                                                <p class="text-xs text-amber-600 mt-1">
                                                    You have no active templates yet, so nothing can be issued.
                                                </p>
                                            @endif
                                        </div>
                                    </x-event-toggle>

                                    @php
                                        // One attendance setup per event, so this is either set up or it is not.
                                        $attendanceSetup = $event->attendances()->with('sessions')->first();
                                    @endphp

                                    <x-event-toggle
                                        name="attendance_required"
                                        icon="how_to_reg"
                                        label="Attendance will be taken"
                                        description="States this in the participant's confirmation email and sends the check-in QR codes to the organizer."
                                        :note="$attendanceSetup ? null : 'Set the scan times below, in the same pass.'"
                                        :checked="old('attendance_required', $event->attendance_required)" />
                                </div>
                            </div>

                            {{-- Scan times: either what already exists, or a form to create it. --}}
                            <x-attendance-picker :event="$event" :existing="$attendanceSetup" />
                        </div>
                    </div>
                </div>
                
                <!-- Form Actions -->
                <div class="flex justify-end space-x-3 pt-4">
                    <a 
                        href="{{ route('event.show', $event->id) }}" 
                        class="px-3 h-[36px] bg-gradient-to-r from-red-600 to-red-500 hover:from-red-700 hover:to-red-600 text-white rounded shadow-sm text-xs font-medium transition-colors duration-200 ease-in-out flex items-center"
                    >
                        <span class="material-icons-outlined text-xs mr-1">cancel</span>
                        Cancel
                    </a>
                    <button 
                        type="submit" 
                        class="px-3 h-[36px] bg-gradient-to-r from-blue-600 to-blue-500 hover:from-blue-700 hover:to-blue-600 text-white rounded shadow-sm text-xs font-medium transition-colors duration-200 ease-in-out flex items-center"
                    >
                        <span class="material-icons-outlined text-xs mr-1">save</span>
                        Update Event
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- TinyMCE for Event T&C (Edit) -->
    <script src="{{ asset('js/tinymce/js/tinymce/tinymce.min.js') }}"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            if (window.tinymce) {
                tinymce.init({
                    selector: '#condition',
                    plugins: 'autolink link image lists table code',
                    toolbar: [
                        'blocks fontfamily fontsize | forecolor backcolor | bold italic underline | alignleft aligncenter alignright alignjustify',
                        'bullist numlist | link image | table | code'
                    ],
                    menubar: false,
                    statusbar: false,
                    height: 380,
                    promotion: false,
                    branding: false,
                    convert_urls: false,
                    relative_urls: false,
                    remove_script_host: false,
                    entity_encoding: 'raw',
                    resize: false,
                    skin: 'oxide',
                    font_size_formats: '8pt 10pt 12pt 14pt 16pt 18pt 24pt 28pt 36pt 48pt',
                    font_family_formats: 'Arial=arial,helvetica,sans-serif; Courier New=courier new,courier,monospace; Georgia=georgia,palatino; Helvetica=helvetica; Times New Roman=times new roman,times; Trebuchet MS=trebuchet ms,geneva; Verdana=verdana,geneva',
                    content_style: 'body { font-family: Arial, sans-serif; font-size: 14pt; line-height: 1.6; }',
                    placeholder: 'Write event terms & conditions here...',
                    images_upload_handler: function (blobInfo, progress) {
                        return new Promise(function(resolve, reject) {
                            const xhr = new XMLHttpRequest();
                            xhr.withCredentials = true;
                            xhr.open('POST', '{{ route('upload.tinymce.image') }}');
                            xhr.setRequestHeader('X-CSRF-TOKEN', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));
                            xhr.upload.onprogress = function (e) { progress(e.loaded / e.total * 100); };
                            xhr.onload = function() {
                                if (xhr.status < 200 || xhr.status >= 300) {
                                    reject('HTTP Error: ' + xhr.status);
                                    return;
                                }
                                try {
                                    const json = JSON.parse(xhr.responseText);
                                    if (!json || typeof json.location != 'string') {
                                        reject('Invalid JSON: ' + xhr.responseText);
                                        return;
                                    }
                                    resolve(json.location);
                                } catch (err) { reject('Invalid response'); }
                            };
                            xhr.onerror = function() { reject('Image upload failed'); };
                            const formData = new FormData();
                            formData.append('file', blobInfo.blob(), blobInfo.filename());
                            xhr.send(formData);
                        });
                    },
                    setup: function (editor) {
                        editor.on('change', function () { editor.save(); });
                    }
                });
            }
        });
    </script>
</x-app-layout>
