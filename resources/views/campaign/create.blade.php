<x-app-layout>
    <x-slot name="breadcrumb">
        <span>Campaign</span>
        <span class="mx-2 text-gray-500">/</span>
        <span>Create New Campaign</span>
    </x-slot>

    <x-slot name="title">Create New Campaign</x-slot>

    <div class="bg-white rounded shadow-md border border-gray-300">
        <div class="p-6 border-b border-gray-200">
            <div class="flex items-center">
                <span class="material-icons mr-2 text-primary-DEFAULT">campaign</span>
                <h1 class="text-xl font-bold text-gray-800">Create New Campaign</h1>
            </div>
            <p class="text-xs text-gray-500 mt-1 ml-8">Create a new marketing campaign</p>
        </div>
        
        <div class="p-6">
            <form action="{{ route('campaign.store') }}" method="POST" class="space-y-6" id="campaignForm">
                @csrf
                
                @if ($errors->any())
                <div class="bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded relative mb-4" role="alert">
                    <strong class="font-bold">Error!</strong>
                    <ul class="mt-1 list-disc list-inside text-xs">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
                @endif
                
                <!-- Campaign Type Selection -->
                <div class="mb-6">
                    <h2 class="text-sm font-semibold text-gray-700 mb-4">Campaign Type</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <label class="relative flex cursor-pointer rounded-lg border bg-white p-4 shadow-sm focus:outline-none border-blue-500" id="email-campaign-label">
                            <input type="radio" name="campaign_type" value="email" class="sr-only" {{ old('campaign_type', 'email') == 'email' ? 'checked' : '' }}>
                            <span class="flex flex-1">
                                <span class="flex flex-col">
                                    <span class="flex items-center text-sm font-medium text-gray-900">
                                        <span class="material-icons text-blue-600 mr-2" id="email-icon">email</span>
                                        Email Campaign
                                    </span>
                                    <span class="mt-1 flex items-center text-xs text-gray-500">
                                        Send emails to participants
                                    </span>
                                </span>
                            </span>
                            <span class="material-icons text-blue-600 shrink-0" id="email-check-icon">check_circle</span>
                        </label>
                        
                        <label class="relative flex cursor-pointer rounded-lg border bg-white p-4 shadow-sm focus:outline-none hover:border-blue-500" id="sms-campaign-label">
                            <input type="radio" name="campaign_type" value="sms" class="sr-only" {{ old('campaign_type') == 'sms' ? 'checked' : '' }}>
                            <span class="flex flex-1">
                                <span class="flex flex-col">
                                    <span class="flex items-center text-sm font-medium text-gray-900">
                                        <span class="material-icons text-gray-600 mr-2" id="sms-icon">sms</span>
                                        SMS Campaign
                                    </span>
                                    <span class="mt-1 flex items-center text-xs text-gray-500">
                                        Send SMS messages to participants
                                    </span>
                                </span>
                            </span>
                            <span class="material-icons text-blue-600 shrink-0 hidden" id="sms-check-icon">check_circle</span>
                        </label>
                    </div>
                </div>
                
                <!-- Campaign Details -->
                <div class="mb-6">
                    <h2 class="text-sm font-semibold text-gray-700 mb-4">Campaign Details</h2>
                    <div class="space-y-4">
                        <div>
                            <label for="campaign_name" class="flex items-center text-xs font-medium text-gray-700 mb-1">
                                <span class="material-icons text-sm mr-1 text-primary-DEFAULT">campaign</span>
                                Campaign Name <span class="text-red-500">*</span>
                            </label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <span class="material-icons text-[#004aad] text-base">drive_file_rename_outline</span>
                                </div>
                                <input type="text" id="campaign_name" name="campaign_name" class="w-full text-xs border-gray-300 rounded-[1px] pl-12 focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50" placeholder="Enter campaign name" value="{{ old('campaign_name') }}" required>
                            </div>
                            <p class="mt-1 text-[10px] text-gray-500">Enter a descriptive name for your campaign</p>
                        </div>
                        
                        <div>
                            <label for="campaign_description" class="flex items-center text-xs font-medium text-gray-700 mb-1">
                                <span class="material-icons text-sm mr-1 text-primary-DEFAULT">description</span>
                                Description
                            </label>
                            <div class="relative">
                                <textarea id="campaign_description" name="campaign_description" rows="3" class="w-full text-xs border-gray-300 rounded-[1px] py-3 focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50" placeholder="Enter campaign description">{{ old('campaign_description') }}</textarea>
                            </div>
                            <p class="mt-1 text-[10px] text-gray-500">Provide a brief description of your campaign's purpose</p>
                        </div>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label for="start_date" class="flex items-center text-xs font-medium text-gray-700 mb-1">
                                    <span class="material-icons text-sm mr-1 text-primary-DEFAULT">event_available</span>
                                    Start Date <span class="text-red-500">*</span>
                                </label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <span class="material-icons text-[#004aad] text-base">calendar_today</span>
                                    </div>
                                    <input type="date" id="start_date" name="start_date" class="w-full text-xs border-gray-300 rounded-[1px] pl-12 focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50" value="{{ old('start_date') }}" required>
                                </div>
                                <p class="mt-1 text-[10px] text-gray-500">Date when the campaign will start</p>
                            </div>
                            
                            <div>
                                <label for="end_date" class="flex items-center text-xs font-medium text-gray-700 mb-1">
                                    <span class="material-icons text-sm mr-1 text-primary-DEFAULT">event_busy</span>
                                    End Date
                                </label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <span class="material-icons text-[#004aad] text-base">calendar_today</span>
                                    </div>
                                    <input type="date" id="end_date" name="end_date" class="w-full text-xs border-gray-300 rounded-[1px] pl-12 focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50" value="{{ old('end_date') }}">
                                </div>
                                <p class="mt-1 text-[10px] text-gray-500">Date when the campaign will end (optional)</p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <hr class="my-6">
                
                <!-- Target Audience -->
                <div class="mb-6">
                    <h2 class="text-sm font-semibold text-gray-700 mb-4">Target Audience</h2>
                    <div class="space-y-4">
                        <div>
                            <label for="audience_type" class="flex items-center text-xs font-medium text-gray-700 mb-1">
                                <span class="material-icons text-sm mr-1 text-primary-DEFAULT">groups</span>
                                Select Audience <span class="text-red-500">*</span>
                            </label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <span class="material-icons text-[#004aad] text-base">people</span>
                                </div>
                                <select id="audience_type" name="audience_type" class="w-full text-xs border-gray-300 rounded-[1px] pl-12 focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50" required>
                                    <option value="">Select audience type</option>
                                    <option value="all_participants" {{ old('audience_type') == 'all_participants' ? 'selected' : '' }}>All Participants</option>
                                    <option value="specific_event" {{ old('audience_type') == 'specific_event' ? 'selected' : '' }}>Specific Event</option>
                                    <option value="custom_filter" {{ old('audience_type') == 'custom_filter' ? 'selected' : '' }}>Custom Filter</option>
                                    <option value="custom_emails" {{ old('audience_type') == 'custom_emails' ? 'selected' : '' }}>Custom</option>
                                </select>
                            </div>
                            <p class="mt-1 text-[10px] text-gray-500">Choose which participants will receive this campaign</p>
                        </div>
                        
                        <div id="event_selection" class="hidden">
                            <label for="event_id" class="flex items-center text-xs font-medium text-gray-700 mb-1">
                                <span class="material-icons text-sm mr-1 text-primary-DEFAULT">event</span>
                                Select Event
                            </label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <span class="material-icons text-[#004aad] text-base">event_note</span>
                                </div>
                                <select id="event_id" name="event_id" class="w-full text-xs border-gray-300 rounded-[1px] pl-12 focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50">
                                    <option value="">Select an event</option>
                                    @foreach($events as $event)
                                        <option value="{{ $event->id }}" {{ old('event_id') == $event->id ? 'selected' : '' }}>{{ $event->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <p class="mt-1 text-[10px] text-gray-500">Target participants from a specific event</p>
                        </div>
                        
                        <div id="custom_filter_options" class="hidden space-y-4">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label for="filter_age" class="block text-xs font-medium text-gray-700 mb-1">Age Range</label>
                                    <select id="filter_age" name="filter_age" class="w-full rounded-md border-gray-300 shadow-sm focus:border-primary-DEFAULT focus:ring focus:ring-primary-DEFAULT focus:ring-opacity-50 text-sm">
                                        <option value="">Any age</option>
                                        <option value="18-24" {{ old('filter_age') == '18-24' ? 'selected' : '' }}>18-24</option>
                                        <option value="25-34" {{ old('filter_age') == '25-34' ? 'selected' : '' }}>25-34</option>
                                        <option value="35-44" {{ old('filter_age') == '35-44' ? 'selected' : '' }}>35-44</option>
                                        <option value="45+" {{ old('filter_age') == '45+' ? 'selected' : '' }}>45+</option>
                                    </select>
                                </div>
                                
                                <div>
                                    <label for="filter_gender" class="block text-xs font-medium text-gray-700 mb-1">Gender</label>
                                    <select id="filter_gender" name="filter_gender" class="w-full rounded-md border-gray-300 shadow-sm focus:border-primary-DEFAULT focus:ring focus:ring-primary-DEFAULT focus:ring-opacity-50 text-sm">
                                        <option value="">Any gender</option>
                                        <option value="male" {{ old('filter_gender') == 'male' ? 'selected' : '' }}>Male</option>
                                        <option value="female" {{ old('filter_gender') == 'female' ? 'selected' : '' }}>Female</option>
                                    </select>
                                </div>
                            </div>
                            
                            <div>
                                <label for="filter_attendance" class="block text-xs font-medium text-gray-700 mb-1">Attendance Status</label>
                                <select id="filter_attendance" name="filter_attendance" class="w-full rounded-md border-gray-300 shadow-sm focus:border-primary-DEFAULT focus:ring focus:ring-primary-DEFAULT focus:ring-opacity-50 text-sm">
                                    <option value="">Any status</option>
                                    <option value="attended" {{ old('filter_attendance') == 'attended' ? 'selected' : '' }}>Attended</option>
                                    <option value="not_attended" {{ old('filter_attendance') == 'not_attended' ? 'selected' : '' }}>Not Attended</option>
                                    <option value="registered" {{ old('filter_attendance') == 'registered' ? 'selected' : '' }}>Registered Only</option>
                                </select>
                            </div>
                        </div>
                        
                        <div id="custom_emails_input" class="hidden space-y-4">
                            <div>
                                <label for="custom_emails" class="flex items-center text-xs font-medium text-gray-700 mb-1">
                                    <span class="material-icons text-sm mr-1 text-primary-DEFAULT">email</span>
                                    Custom Email Addresses <span class="text-red-500">*</span>
                                </label>
                                <div class="relative">
                                    <div class="absolute top-3 left-0 pl-3 flex items-start pointer-events-none">
                                        <span class="material-icons text-[#004aad] text-base">alternate_email</span>
                                    </div>
                                    <textarea id="custom_emails" name="custom_emails" rows="4" class="w-full text-xs border-gray-300 rounded-[1px] pl-12 py-3 focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50" placeholder="Enter email addresses separated by commas, e.g. test@example.com, user@domain.com">{{ old('custom_emails') }}</textarea>
                                </div>
                                <p class="mt-1 text-[10px] text-gray-500">Enter custom email addresses separated by commas for testing purposes</p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Email Subject (always visible) -->
                <div class="mb-6" id="email_subject_section">
                    <div class="space-y-4">
                        <div>
                            <label for="email_subject" class="flex items-center text-xs font-medium text-gray-700 mb-1">
                                <span class="material-icons text-sm mr-1 text-primary-DEFAULT">subject</span>
                                Email Subject <span class="text-red-500">*</span>
                            </label>
                            <div class="relative">
                                <input type="text" id="email_subject" name="email_subject" class="w-full text-xs border-gray-300 rounded-[1px] focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50" value="{{ old('email_subject', '') }}" required>
                            </div>
                            <p class="mt-1 text-[10px] text-gray-500">The subject line that will appear in recipients' inbox</p>
                            @error('email_subject')
                                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>
                
                <!-- Email Campaign Content (shown by default) -->
                <div id="email_content" class="mb-6">
                    <h2 class="text-sm font-semibold text-gray-700 mb-4">Email Content</h2>
                    <div class="space-y-4">                        
                        <div>
                            <label for="email_template" class="flex items-center text-xs font-medium text-gray-700 mb-1">
                                <span class="material-icons text-sm mr-1 text-primary-DEFAULT">template</span>
                                Email Template
                            </label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <span class="material-icons text-[#004aad] text-base">dashboard</span>
                                </div>
                                <select id="email_template" name="email_template" class="w-full text-xs border-gray-300 rounded-[1px] pl-12 focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50">
                                    <option value="">-- Select a Template --</option>
                                    <option value="welcome">Welcome Email</option>
                                    <option value="certificate">Certificate Ready</option>
                                    <option value="custom">Custom Template</option>
                                </select>
                            </div>
                            <p class="mt-1 text-[10px] text-gray-500">Choose a pre-designed template or create your own</p>
                        </div>
                        
                        <div>
                            <label for="email_content" class="block text-xs font-medium text-gray-700 mb-1">Email Content <span class="text-red-500">*</span></label>
                            <div class="border border-gray-300 rounded-md">
                                <textarea id="email_content" name="email_content" rows="15" class="w-full border-0 bg-gray-50 focus:ring-0 text-sm" placeholder="Compose your email content here..."></textarea>
                            </div>
                            <p class="mt-1 text-[10px] text-gray-500">Compose the body of your email with formatting options.</p>
                        </div>
                        
                        <div class="flex items-center">
                            <input type="checkbox" id="include_unsubscribe" name="include_unsubscribe" class="rounded border-gray-300 text-primary-DEFAULT shadow-sm focus:border-primary-DEFAULT focus:ring focus:ring-primary-DEFAULT focus:ring-opacity-50" checked>
                            <label for="include_unsubscribe" class="ml-2 block text-xs text-gray-700">Include unsubscribe link (recommended)</label>
                        </div>
                    </div>
                </div>
                
                <!-- SMS Campaign Content (hidden by default) -->
                <div id="sms_content" class="mb-6 hidden">
                    <h2 class="text-sm font-semibold text-gray-700 mb-4">SMS Content</h2>
                    <div class="space-y-4">
                        <div>
                            <label for="sms_message" class="flex items-center text-xs font-medium text-gray-700 mb-1">
                                <span class="material-icons text-sm mr-1 text-primary-DEFAULT">sms</span>
                                Message Content <span class="text-red-500">*</span>
                            </label>
                            <div class="relative">
                                <div class="absolute top-3 left-0 pl-3 flex items-start pointer-events-none">
                                    <span class="material-icons text-[#004aad] text-base">message</span>
                                </div>
                                <textarea id="sms_message" name="sms_message" rows="4" class="w-full text-xs border-gray-300 rounded-[1px] pl-12 py-3 focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50" placeholder="Enter SMS message (160 characters max)">{{ old('sms_message') }}</textarea>
                            </div>
                            <p class="mt-1 text-xs text-gray-500">Characters: <span id="sms_char_count">0</span>/160</p>
                        </div>
                        
                        <div>
                            <label for="include_shortlink" class="flex items-center text-xs font-medium text-gray-700 mb-1">
                                <span class="material-icons text-sm mr-1 text-primary-DEFAULT">link</span>
                                Include Short Link
                            </label>
                            <div class="flex items-center">
                                <input type="checkbox" id="include_shortlink" name="include_shortlink" class="rounded border-gray-300 text-primary-DEFAULT shadow-sm focus:border-primary-DEFAULT focus:ring focus:ring-primary-DEFAULT focus:ring-opacity-50" {{ old('include_shortlink') ? 'checked' : '' }}>
                                <label for="include_shortlink" class="ml-2 block text-xs text-gray-700">Add a shortened URL to the message</label>
                            </div>
                        </div>
                    </div>
                </div>
                

                
                <!-- Scheduling Options -->
                <div class="mb-6">
                    <h2 class="text-sm font-semibold text-gray-700 mb-4">Scheduling Options</h2>
                    <div class="space-y-4">
                        <div>
                            <label class="block text-xs font-medium text-gray-700 mb-2">When to Send</label>
                            <div class="space-y-2">
                                <div class="flex items-center">
                                    <input type="radio" id="send_now" name="schedule_type" value="now" class="focus:ring-primary-DEFAULT h-4 w-4 text-primary-DEFAULT border-gray-300" {{ old('schedule_type', 'now') == 'now' ? 'checked' : '' }}>
                                    <label for="send_now" class="ml-2 block text-sm text-gray-700">Send immediately</label>
                                </div>
                                <div class="flex items-center">
                                    <input type="radio" id="send_scheduled" name="schedule_type" value="scheduled" class="focus:ring-primary-DEFAULT h-4 w-4 text-primary-DEFAULT border-gray-300" {{ old('schedule_type') == 'scheduled' ? 'checked' : '' }}>
                                    <label for="send_scheduled" class="ml-2 block text-sm text-gray-700">Schedule for later</label>
                                </div>
                            </div>
                        </div>
                        
                        <div id="scheduled_options" class="hidden">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label for="scheduled_date" class="block text-xs font-medium text-gray-700 mb-1">Date</label>
                                    <input type="date" id="scheduled_date" name="scheduled_date" class="w-full rounded-md border-gray-300 shadow-sm focus:border-primary-DEFAULT focus:ring focus:ring-primary-DEFAULT focus:ring-opacity-50 text-sm" value="{{ old('scheduled_date') }}">
                                </div>
                                
                                <div>
                                    <label for="scheduled_time" class="block text-xs font-medium text-gray-700 mb-1">Time</label>
                                    <input type="time" id="scheduled_time" name="scheduled_time" class="w-full rounded-md border-gray-300 shadow-sm focus:border-primary-DEFAULT focus:ring focus:ring-primary-DEFAULT focus:ring-opacity-50 text-sm" value="{{ old('scheduled_time') }}">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Form Actions -->
                <div class="border-t border-gray-200 pt-4 mt-6 flex justify-end space-x-3">
                    <a href="{{ route('campaign.index') }}" class="px-3 py-1 bg-gradient-to-r from-red-600 to-red-500 hover:from-red-700 hover:to-red-600 text-white rounded-[1px] shadow-sm text-xs font-medium transition-colors duration-200 ease-in-out flex items-center">
                        <span class="material-icons text-xs mr-1">cancel</span>
                        Cancel
                    </a>
                    <button type="submit" name="save_draft" class="px-3 py-1 bg-gradient-to-r from-green-600 to-green-500 hover:from-green-700 hover:to-green-600 text-white rounded-[1px] shadow-sm text-xs font-medium transition-colors duration-200 ease-in-out flex items-center">
                        <span class="material-icons text-xs mr-1">save</span>
                        Save as Draft
                    </button>
                    <button type="submit" name="save_send" class="px-3 py-1 bg-gradient-to-r from-blue-600 to-blue-500 hover:from-blue-700 hover:to-blue-600 text-white rounded-[1px] shadow-sm text-xs font-medium transition-colors duration-200 ease-in-out flex items-center">
                        <span class="material-icons text-xs mr-1">send</span>
                        Save & Send
                    </button>
                </div>
            </form>
        </div>
    </div>
    
    <!-- JavaScript for form interactions -->
    <script src="{{ asset('js/tinymce/js/tinymce/tinymce.min.js') }}"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize TinyMCE
            tinymce.init({
                selector: '#email_content',
                plugins: 'autolink link image lists table code help wordcount preview fontsize fontfamily textcolor lineheight',
                toolbar: [
                    'fontfamily fontsize | forecolor backcolor | bold italic underline | alignleft aligncenter alignright alignjustify | lineheight',
                    'bullist numlist | link image | table | code'
                ],
                menubar: false,
                statusbar: false,
                height: 400,
                promotion: false,
                branding: false,
                convert_urls: false,
                relative_urls: false,
                remove_script_host: false,
                entity_encoding: 'raw',
                resize: false,
                skin: 'oxide',
                fontsize_formats: '8pt 10pt 12pt 14pt 16pt 18pt 24pt 36pt 48pt',
                font_family_formats: 'Arial=arial,helvetica,sans-serif; Courier New=courier new,courier,monospace; Georgia=georgia,palatino; Helvetica=helvetica; Times New Roman=times new roman,times; Trebuchet MS=trebuchet ms,geneva; Verdana=verdana,geneva',
                lineheight_formats: '1 1.1 1.2 1.3 1.4 1.5 1.6 1.8 2',
                content_style: "body { font-family: Arial, sans-serif; font-size: 14pt; }",
                init_instance_callback: function(editor) {
                    editor.setContent(''); // Mulai dengan editor kosong
                },
                setup: function (editor) {
                    editor.on('change', function () {
                        editor.save();
                    });
                }
            });
            
            // Email template selection
            const emailTemplateSelect = document.getElementById('email_template');
            emailTemplateSelect.addEventListener('change', function() {
                const selectedTemplate = this.value;
                
                if (selectedTemplate === 'welcome') {
                    const welcomeTemplate = '<h3>Welcome to Our Event!</h3><p>Dear {name},</p><p>We\'re excited to welcome you to our event. Here are the details:</p><ul><li><strong>Date:</strong> [Event Date]</li><li><strong>Time:</strong> [Event Time]</li><li><strong>Location:</strong> [Event Location]</li></ul><p>Please don\'t forget to bring your ID and registration confirmation.</p><p>We look forward to seeing you!</p><p>Best regards,<br>Event Team</p>';
                    tinymce.get('email_content').setContent(welcomeTemplate);
                    document.getElementById('email_subject').value = 'Welcome to Our Event - Important Information';
                } else if (selectedTemplate === 'certificate') {
                    const certificateTemplate = '<h3>Your Certificate is Ready!</h3><p>Dear {name},</p><p>We\'re pleased to inform you that your certificate for attending our event is now ready.</p><p>You can download your certificate by clicking the link below:</p><p><a href="#">Download Certificate</a></p><p>Thank you for your participation!</p><p>Best regards,<br>Event Team</p>';
                    tinymce.get('email_content').setContent(certificateTemplate);
                    document.getElementById('email_subject').value = 'Your Certificate is Ready for Download';
                } else if (selectedTemplate === 'custom') {
                    tinymce.get('email_content').setContent('');
                    document.getElementById('email_subject').value = '';
                }
            });
            
            // Campaign type selection
            const campaignTypeRadios = document.querySelectorAll('input[name="campaign_type"]');
            const emailContent = document.getElementById('email_content');
            const smsContent = document.getElementById('sms_content');
            const emailSubjectSection = document.getElementById('email_subject_section');
            
            // Campaign type UI elements
            const emailLabel = document.getElementById('email-campaign-label');
            const smsLabel = document.getElementById('sms-campaign-label');
            const emailIcon = document.getElementById('email-icon');
            const smsIcon = document.getElementById('sms-icon');
            const emailCheckIcon = document.getElementById('email-check-icon');
            const smsCheckIcon = document.getElementById('sms-check-icon');
            
            // Initialize campaign type based on selected value
            const selectedCampaignType = document.querySelector('input[name="campaign_type"]:checked').value;
            if (selectedCampaignType === 'email') {
                emailContent.classList.remove('hidden');
                emailSubjectSection.classList.remove('hidden');
                smsContent.classList.add('hidden');
                emailLabel.classList.add('border-blue-500');
                emailIcon.classList.add('text-blue-600');
                emailCheckIcon.classList.remove('hidden');
                smsLabel.classList.remove('border-blue-500');
                smsIcon.classList.add('text-gray-600');
                smsCheckIcon.classList.add('hidden');
            } else if (selectedCampaignType === 'sms') {
                emailContent.classList.add('hidden');
                emailSubjectSection.classList.add('hidden');
                smsContent.classList.remove('hidden');
                smsLabel.classList.add('border-blue-500');
                smsIcon.classList.add('text-blue-600');
                smsCheckIcon.classList.remove('hidden');
                emailLabel.classList.remove('border-blue-500');
                emailIcon.classList.add('text-gray-600');
                emailCheckIcon.classList.add('hidden');
            }
            
            campaignTypeRadios.forEach(radio => {
                radio.addEventListener('change', function() {
                    // Hide all content sections
                    emailContent.classList.add('hidden');
                    emailSubjectSection.classList.add('hidden');
                    smsContent.classList.add('hidden');
                    
                    // Reset all styling
                    emailLabel.classList.remove('border-blue-500');
                    smsLabel.classList.remove('border-blue-500');
                    emailIcon.classList.remove('text-blue-600');
                    emailIcon.classList.add('text-gray-600');
                    smsIcon.classList.remove('text-blue-600');
                    smsIcon.classList.add('text-gray-600');
                    emailCheckIcon.classList.add('hidden');
                    smsCheckIcon.classList.add('hidden');
                    
                    // Show selected content section and update styling
                    if (this.value === 'email') {
                        emailContent.classList.remove('hidden');
                        emailSubjectSection.classList.remove('hidden');
                        emailLabel.classList.add('border-blue-500');
                        emailIcon.classList.remove('text-gray-600');
                        emailIcon.classList.add('text-blue-600');
                        emailCheckIcon.classList.remove('hidden');
                    } else if (this.value === 'sms') {
                        smsContent.classList.remove('hidden');
                        smsLabel.classList.add('border-blue-500');
                        smsIcon.classList.remove('text-gray-600');
                        smsIcon.classList.add('text-blue-600');
                        smsCheckIcon.classList.remove('hidden');
                    }
                });
            });
            
            // Audience type selection
            const audienceTypeSelect = document.getElementById('audience_type');
            const eventSelection = document.getElementById('event_selection');
            const customFilterOptions = document.getElementById('custom_filter_options');
            const customEmailsInput = document.getElementById('custom_emails_input');
            
            // Initialize audience type based on selected value
            const selectedAudienceType = audienceTypeSelect.value;
            if (selectedAudienceType === 'specific_event') {
                eventSelection.classList.remove('hidden');
                customFilterOptions.classList.add('hidden');
                customEmailsInput.classList.add('hidden');
            } else if (selectedAudienceType === 'custom_filter') {
                eventSelection.classList.add('hidden');
                customFilterOptions.classList.remove('hidden');
                customEmailsInput.classList.add('hidden');
            } else if (selectedAudienceType === 'custom_emails') {
                eventSelection.classList.add('hidden');
                customFilterOptions.classList.add('hidden');
                customEmailsInput.classList.remove('hidden');
            } else {
                eventSelection.classList.add('hidden');
                customFilterOptions.classList.add('hidden');
                customEmailsInput.classList.add('hidden');
            }
            
            audienceTypeSelect.addEventListener('change', function() {
                eventSelection.classList.add('hidden');
                customFilterOptions.classList.add('hidden');
                customEmailsInput.classList.add('hidden');
                
                if (this.value === 'specific_event') {
                    eventSelection.classList.remove('hidden');
                } else if (this.value === 'custom_filter') {
                    customFilterOptions.classList.remove('hidden');
                } else if (this.value === 'custom_emails') {
                    customEmailsInput.classList.remove('hidden');
                }
            });
            
            // Schedule options
            const scheduleTypeRadios = document.querySelectorAll('input[name="schedule_type"]');
            const scheduledOptions = document.getElementById('scheduled_options');
            
            // Initialize schedule options based on selected value
            const selectedScheduleType = document.querySelector('input[name="schedule_type"]:checked').value;
            if (selectedScheduleType === 'scheduled') {
                scheduledOptions.classList.remove('hidden');
            } else {
                scheduledOptions.classList.add('hidden');
            }
            
            scheduleTypeRadios.forEach(radio => {
                radio.addEventListener('change', function() {
                    if (this.value === 'scheduled') {
                        scheduledOptions.classList.remove('hidden');
                    } else {
                        scheduledOptions.classList.add('hidden');
                    }
                });
            });
            
            // SMS character counter
            const smsMessageTextarea = document.getElementById('sms_message');
            const smsCharCount = document.getElementById('sms_char_count');
            
            // Initialize SMS character counter
            if (smsMessageTextarea.value) {
                const count = smsMessageTextarea.value.length;
                smsCharCount.textContent = count;
                
                if (count > 160) {
                    smsCharCount.classList.add('text-red-500');
                    smsCharCount.classList.add('font-medium');
                } else {
                    smsCharCount.classList.remove('text-red-500');
                    smsCharCount.classList.remove('font-medium');
                }
            }
            
            smsMessageTextarea.addEventListener('input', function() {
                const count = this.value.length;
                smsCharCount.textContent = count;
                
                if (count > 160) {
                    smsCharCount.classList.add('text-red-500');
                    smsCharCount.classList.add('font-medium');
                } else {
                    smsCharCount.classList.remove('text-red-500');
                    smsCharCount.classList.remove('font-medium');
                }
            });

            // Capture TinyMCE content before form submission
            document.getElementById('campaignForm').addEventListener('submit', function(event) {
                // Ensure TinyMCE content is saved to the form field before submission
                if (tinymce.get('email_content')) {
                    const emailContent = tinymce.get('email_content').getContent();
                    document.getElementById('email_content').value = emailContent;
                }
            });
        });
    </script>
</x-app-layout> 