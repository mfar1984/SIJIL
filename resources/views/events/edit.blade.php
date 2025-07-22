<x-app-layout>
    <x-slot name="breadcrumb">
        <span>Event Management</span>
        <span class="mx-2 text-gray-500">/</span>
        <span>Edit Event</span>
    </x-slot>

    <x-slot name="title">Edit Event</x-slot>

    <div class="bg-white rounded shadow-md border border-gray-300">
        <div class="p-6 border-b border-gray-200">
            <div class="flex items-center">
                <span class="material-icons mr-2 text-primary-DEFAULT">event_note</span>
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

            <form method="POST" action="{{ route('event.update', $event->id) }}" class="space-y-6">
                @csrf
                @method('PUT')
                
                <!-- Basic Information -->
                <div class="border-b border-gray-200 pb-5">
                    <h2 class="text-sm font-semibold text-gray-700 mb-4">Basic Information</h2>
                    
                    <!-- Event Name -->
                    <div class="mb-4">
                        <label for="name" class="flex items-center text-xs font-medium text-gray-700 mb-1">
                            <span class="material-icons text-sm mr-1 text-primary-DEFAULT">event</span>
                            Event Name
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <span class="material-icons text-[#004aad] text-base">title</span>
                            </div>
                            <input 
                                type="text" 
                                name="name" 
                                id="name" 
                                class="w-full text-xs border-gray-300 rounded-[1px] pl-12 focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50" 
                                value="{{ old('name', $event->name) }}" 
                                required
                            >
                        </div>
                        <p class="mt-1 text-[10px] text-gray-500">Enter a descriptive name for the event</p>
                    </div>
                    
                    <!-- Organizer -->
                    <div class="mb-4">
                        <label for="organizer" class="flex items-center text-xs font-medium text-gray-700 mb-1">
                            <span class="material-icons text-sm mr-1 text-primary-DEFAULT">people</span>
                            Organizer
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <span class="material-icons text-[#004aad] text-base">group</span>
                            </div>
                            <input 
                                type="text" 
                                name="organizer" 
                                id="organizer" 
                                class="w-full text-xs border-gray-300 rounded-[1px] pl-12 focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50"
                                value="{{ old('organizer', $event->organizer) }}"
                                required
                            >
                        </div>
                        <p class="mt-1 text-[10px] text-gray-500">Department or organization hosting the event</p>
                    </div>
                    
                    <!-- Description -->
                    <div class="mb-4">
                        <label for="description" class="flex items-center text-xs font-medium text-gray-700 mb-1">
                            <span class="material-icons text-sm mr-1 text-primary-DEFAULT">description</span>
                            Description
                        </label>
                        <div class="relative">
                            <div class="absolute top-3 left-0 pl-3 flex items-start pointer-events-none">
                                <span class="material-icons text-[#004aad] text-base">notes</span>
                            </div>
                            <textarea 
                                name="description" 
                                id="description" 
                                rows="3" 
                                class="w-full text-xs border-gray-300 rounded-[1px] pl-12 focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50"
                            >{{ old('description', $event->description) }}</textarea>
                        </div>
                        <p class="mt-1 text-[10px] text-gray-500">Provide a detailed description of the event</p>
                    </div>
                </div>
                
                <!-- Date and Time -->
                <div class="border-b border-gray-200 pb-5">
                    <h2 class="text-sm font-semibold text-gray-700 mb-4">Date and Time</h2>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                        <!-- Start Date -->
                        <div>
                            <label for="start_date" class="flex items-center text-xs font-medium text-gray-700 mb-1">
                                <span class="material-icons text-sm mr-1 text-primary-DEFAULT">calendar_today</span>
                                Start Date
                            </label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <span class="material-icons text-[#004aad] text-base">event</span>
                                </div>
                                <input 
                                    type="date" 
                                    name="start_date" 
                                    id="start_date" 
                                    class="w-full text-xs border-gray-300 rounded-[1px] pl-12 focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50" 
                                    value="{{ old('start_date', $event->start_date_formatted) }}" 
                                    required
                                >
                            </div>
                        </div>
                        
                        <!-- Start Time -->
                        <div>
                            <label for="start_time" class="flex items-center text-xs font-medium text-gray-700 mb-1">
                                <span class="material-icons text-sm mr-1 text-primary-DEFAULT">schedule</span>
                                Start Time
                            </label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <span class="material-icons text-[#004aad] text-base">access_time</span>
                                </div>
                                <input 
                                    type="time" 
                                    name="start_time" 
                                    id="start_time" 
                                    class="w-full text-xs border-gray-300 rounded-[1px] pl-12 focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50" 
                                    value="{{ old('start_time', $event->start_time_formatted) }}" 
                                    required
                                >
                            </div>
                        </div>
                        
                        <!-- End Date -->
                        <div>
                            <label for="end_date" class="flex items-center text-xs font-medium text-gray-700 mb-1">
                                <span class="material-icons text-sm mr-1 text-primary-DEFAULT">calendar_today</span>
                                End Date
                            </label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <span class="material-icons text-[#004aad] text-base">event</span>
                                </div>
                                <input 
                                    type="date" 
                                    name="end_date" 
                                    id="end_date" 
                                    class="w-full text-xs border-gray-300 rounded-[1px] pl-12 focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50" 
                                    value="{{ old('end_date', $event->end_date_formatted) }}" 
                                    required
                                >
                            </div>
                        </div>
                        
                        <!-- End Time -->
                        <div>
                            <label for="end_time" class="flex items-center text-xs font-medium text-gray-700 mb-1">
                                <span class="material-icons text-sm mr-1 text-primary-DEFAULT">schedule</span>
                                End Time
                            </label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <span class="material-icons text-[#004aad] text-base">access_time</span>
                                </div>
                                <input 
                                    type="time" 
                                    name="end_time" 
                                    id="end_time" 
                                    class="w-full text-xs border-gray-300 rounded-[1px] pl-12 focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50" 
                                    value="{{ old('end_time', $event->end_time_formatted) }}" 
                                    required
                                >
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Location -->
                <div class="border-b border-gray-200 pb-5">
                    <h2 class="text-sm font-semibold text-gray-700 mb-4">Location Information</h2>
                    
                    <!-- Venue Name -->
                    <div class="mb-4">
                        <label for="location" class="flex items-center text-xs font-medium text-gray-700 mb-1">
                            <span class="material-icons text-sm mr-1 text-primary-DEFAULT">location_on</span>
                            Venue Name
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <span class="material-icons text-[#004aad] text-base">business</span>
                            </div>
                            <input 
                                type="text" 
                                name="location" 
                                id="location" 
                                class="w-full text-xs border-gray-300 rounded-[1px] pl-12 focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50" 
                                value="{{ old('location', $event->location) }}" 
                                required
                            >
                        </div>
                        <p class="mt-1 text-[10px] text-gray-500">Name of the venue where the event will be held</p>
                    </div>
                    
                    <!-- Address -->
                    <div class="mb-4">
                        <label for="address" class="flex items-center text-xs font-medium text-gray-700 mb-1">
                            <span class="material-icons text-sm mr-1 text-primary-DEFAULT">home</span>
                            Complete Address
                        </label>
                        <div class="relative">
                            <div class="absolute top-3 left-0 pl-3 flex items-start pointer-events-none">
                                <span class="material-icons text-[#004aad] text-base">place</span>
                            </div>
                            <textarea 
                                name="address" 
                                id="address" 
                                rows="2" 
                                class="w-full text-xs border-gray-300 rounded-[1px] pl-12 focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50"
                            >{{ old('address', $event->address) }}</textarea>
                        </div>
                        <p class="mt-1 text-[10px] text-gray-500">Full address of the venue including city and postcode</p>
                    </div>
                </div>
                
                <!-- Participant Information -->
                <div class="border-b border-gray-200 pb-5">
                    <h2 class="text-sm font-semibold text-gray-700 mb-4">Participant Information</h2>
                    
                    <!-- Max Participants -->
                    <div class="mb-4">
                        <label for="max_participants" class="flex items-center text-xs font-medium text-gray-700 mb-1">
                            <span class="material-icons text-sm mr-1 text-primary-DEFAULT">groups</span>
                            Maximum Participants
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <span class="material-icons text-[#004aad] text-base">person_add</span>
                            </div>
                            <input 
                                type="number" 
                                name="max_participants" 
                                id="max_participants" 
                                class="w-full text-xs border-gray-300 rounded-[1px] pl-12 focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50" 
                                value="{{ old('max_participants', $event->max_participants) }}" 
                                min="1"
                                required
                            >
                        </div>
                        <p class="mt-1 text-[10px] text-gray-500">Maximum number of participants allowed</p>
                    </div>
                </div>
                
                <!-- Contact Information -->
                <div class="border-b border-gray-200 pb-5">
                    <h2 class="text-sm font-semibold text-gray-700 mb-4">Contact Information</h2>
                    
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <!-- Contact Person -->
                        <div>
                            <label for="contact_person" class="flex items-center text-xs font-medium text-gray-700 mb-1">
                                <span class="material-icons text-sm mr-1 text-primary-DEFAULT">person</span>
                                Contact Person
                            </label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <span class="material-icons text-[#004aad] text-base">person_outline</span>
                                </div>
                                <input 
                                    type="text" 
                                    name="contact_person" 
                                    id="contact_person" 
                                    class="w-full text-xs border-gray-300 rounded-[1px] pl-12 focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50" 
                                    value="{{ old('contact_person', $event->contact_person ?? '') }}"
                                >
                            </div>
                        </div>
                        
                        <!-- Contact Email -->
                        <div>
                            <label for="contact_email" class="flex items-center text-xs font-medium text-gray-700 mb-1">
                                <span class="material-icons text-sm mr-1 text-primary-DEFAULT">email</span>
                                Contact Email
                            </label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <span class="material-icons text-[#004aad] text-base">alternate_email</span>
                                </div>
                                <input 
                                    type="email" 
                                    name="contact_email" 
                                    id="contact_email" 
                                    class="w-full text-xs border-gray-300 rounded-[1px] pl-12 focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50" 
                                    value="{{ old('contact_email', $event->contact_email ?? '') }}"
                                >
                            </div>
                        </div>
                        
                        <!-- Contact Phone -->
                        <div>
                            <label for="contact_phone" class="flex items-center text-xs font-medium text-gray-700 mb-1">
                                <span class="material-icons text-sm mr-1 text-primary-DEFAULT">phone</span>
                                Contact Phone
                            </label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <span class="material-icons text-[#004aad] text-base">call</span>
                                </div>
                                <input 
                                    type="tel" 
                                    name="contact_phone" 
                                    id="contact_phone" 
                                    class="w-full text-xs border-gray-300 rounded-[1px] pl-12 focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50" 
                                    value="{{ old('contact_phone', $event->contact_phone ?? '') }}"
                                    placeholder="+60123456789"
                                >
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Status -->
                <div class="pb-5">
                    <h2 class="text-sm font-semibold text-gray-700 mb-4">Status</h2>
                    
                    <div>
                        <label for="status" class="flex items-center text-xs font-medium text-gray-700 mb-1">
                            <span class="material-icons text-sm mr-1 text-primary-DEFAULT">toggle_on</span>
                            Event Status
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <span class="material-icons text-[#004aad] text-base">shield</span>
                            </div>
                            <select 
                                name="status" 
                                id="status" 
                                class="w-full text-xs border-gray-300 rounded-[1px] pl-12 focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50" 
                                required
                            >
                                <option value="pending" {{ old('status', $event->status) == 'pending' ? 'selected' : '' }}>Pending</option>
                                <option value="active" {{ old('status', $event->status) == 'active' ? 'selected' : '' }}>Active</option>
                                <option value="completed" {{ old('status', $event->status) == 'completed' ? 'selected' : '' }}>Completed</option>
                            </select>
                        </div>
                        <p class="mt-1 text-[10px] text-gray-500">Current status of the event</p>
                    </div>
                </div>
                
                <div class="border-t border-gray-200 pt-4 mt-6 flex justify-end space-x-3">
                    <a 
                        href="{{ route('event.show', $event->id) }}" 
                        class="px-3 py-1 bg-gradient-to-r from-red-600 to-red-500 hover:from-red-700 hover:to-red-600 text-white rounded shadow-sm text-xs font-medium transition-colors duration-200 ease-in-out flex items-center"
                    >
                        <span class="material-icons text-xs mr-1">cancel</span>
                        Cancel
                    </a>
                    <button 
                        type="submit" 
                        class="px-3 py-1 bg-gradient-to-r from-blue-600 to-blue-500 hover:from-blue-700 hover:to-blue-600 text-white rounded shadow-sm text-xs font-medium transition-colors duration-200 ease-in-out flex items-center"
                    >
                        <span class="material-icons text-xs mr-1">save</span>
                        Update Event
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout> 