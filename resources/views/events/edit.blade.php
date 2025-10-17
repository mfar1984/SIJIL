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

            <form method="POST" action="{{ route('event.update', $event->id) }}" class="space-y-6" enctype="multipart/form-data">
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
                        <label for="description" class="block text-xs font-medium text-gray-700 mb-1">Description</label>
                        <textarea 
                            name="description" 
                            id="description" 
                            rows="3" 
                            class="w-full text-xs border-gray-300 rounded focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50"
                        >{{ old('description', $event->description) }}</textarea>
                        <p class="mt-1 text-[10px] text-gray-500">Provide a detailed description of the event</p>
                    </div>
                    <!-- Poster Attachment -->
                    <div class="mb-4">
                        <label for="poster" class="block text-xs font-medium text-gray-700 mb-1">Poster (Attachment)</label>
                        <input type="file" name="poster" id="poster" accept="image/png,image/jpeg,image/webp" class="w-full text-xs border border-gray-300 rounded px-2 py-1 focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50">
                        @if($event->poster)
                            <p class="mt-1 text-[10px] text-gray-500">Current: <a href="{{ asset('storage/'.$event->poster) }}" target="_blank" class="text-blue-600 underline">view poster</a></p>
                        @endif
                        <p class="mt-1 text-[10px] text-gray-500">Recommended: JPG/PNG/WebP, max 2 MB, portrait 1200×1600 (3:4)</p>
                    </div>
                    <!-- Event Terms & Conditions -->
                    <div class="mb-4">
                        <label for="condition" class="block text-xs font-medium text-gray-700 mb-1">Event Terms & Conditions</label>
                        <textarea name="condition" id="condition" rows="12" class="w-full text-sm" placeholder="Write event terms & conditions here...">{{ old('condition', $event->condition) }}</textarea>
                        <p class="text-[10px] text-gray-500 mt-1">Example: Only participants aged 18 and above, must bring IC, etc.</p>
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
                                    class="w-full h-9 text-xs border-gray-300 rounded-[1px] pl-12 focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50" 
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
                                    class="w-full h-9 text-xs border-gray-300 rounded-[1px] pl-12 focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50" 
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
                                    class="w-full h-9 text-xs border-gray-300 rounded-[1px] pl-12 focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50" 
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
                                    class="w-full h-9 text-xs border-gray-300 rounded-[1px] pl-12 focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50" 
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
                        <label for="address" class="block text-xs font-medium text-gray-700 mb-1">Complete Address</label>
                        <textarea 
                            name="address" 
                            id="address" 
                            rows="3" 
                            class="w-full text-xs border-gray-300 rounded focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50"
                        >{{ old('address', $event->address) }}</textarea>
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
                        class="px-3 h-[36px] bg-gradient-to-r from-red-600 to-red-500 hover:from-red-700 hover:to-red-600 text-white rounded shadow-sm text-xs font-medium transition-colors duration-200 ease-in-out flex items-center"
                    >
                        <span class="material-icons text-xs mr-1">cancel</span>
                        Cancel
                    </a>
                    <button 
                        type="submit" 
                        class="px-3 h-[36px] bg-gradient-to-r from-blue-600 to-blue-500 hover:from-blue-700 hover:to-blue-600 text-white rounded shadow sm text-xs font-medium transition-colors duration-200 ease-in-out flex items-center"
                    >
                        <span class="material-icons text-xs mr-1">save</span>
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
                    plugins: 'autolink link image lists table code help wordcount preview fontsize fontfamily textcolor lineheight placeholder',
                    toolbar: [
                        'fontfamily fontsize | forecolor backcolor | bold italic underline | alignleft aligncenter alignright alignjustify | lineheight',
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
                    fontsize_formats: '8pt 10pt 12pt 14pt 16pt 18pt 24pt 28pt 36pt 48pt',
                    font_family_formats: 'Arial=arial,helvetica,sans-serif; Courier New=courier new,courier,monospace; Georgia=georgia,palatino; Helvetica=helvetica; Times New Roman=times new roman,times; Trebuchet MS=trebuchet ms,geneva; Verdana=verdana,geneva',
                    lineheight_formats: '1 1.1 1.2 1.3 1.4 1.5 1.6 1.8 2',
                    content_style: 'body { font-family: Arial, sans-serif; font-size: 14pt; } .mce-content-body[data-mce-placeholder]:not(.mce-visualblocks)::before { font-size: 12px; color: #6b7280; }',
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