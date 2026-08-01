<x-app-layout>
    <x-slot name="breadcrumb">
        <span>Certificate</span>
        <span class="mx-2 text-gray-500">/</span>
        <span><a href="{{ route('certificates.index') }}" class="text-primary-DEFAULT hover:underline">Manage Certificates</a></span>
        <span class="mx-2 text-gray-500">/</span>
        <span>Generate Certificates</span>
    </x-slot>

    <x-slot name="title">Generate Certificates</x-slot>

    <div class="bg-white rounded shadow-md border border-gray-300">
        <div class="p-6 border-b border-gray-200">
            <div class="flex items-center">
                <span class="material-icons-outlined mr-2 text-primary-DEFAULT">workspace_premium</span>
                <h1 class="text-xl font-bold text-gray-800">Generate Certificates</h1>
            </div>
            <p class="text-xs text-gray-500 mt-1 ml-8">Create certificates for participants</p>
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

            <form id="certificateForm" action="{{ route('certificates.store') }}" method="POST" class="space-y-2">
                @csrf
                
                <!-- Step 1: Select Event -->
                <div class="bg-white border border-gray-200 rounded-md shadow-sm">
                    <div class="bg-gray-50 border-b border-gray-200 px-4 py-3">
                        <h2 class="text-sm font-semibold text-gray-700 flex items-center">
                            <span class="material-icons-outlined text-primary-DEFAULT mr-2">event</span>
                            Step 1: Select Event
                        </h2>
                    </div>
                    
                    <div class="p-4">
                        <div class="flex flex-col md:flex-row md:items-start gap-3">
                            <label for="event_id" class="text-xs font-medium text-gray-700 md:w-48 shrink-0 md:pt-2">
                                Event <span class="text-red-500">*</span>
                            </label>
                            <div class="flex-1">
                                <select id="event_id" name="event_id" class="w-full h-9 text-xs border-gray-300 rounded px-3 focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50" required>
                                    <option value="">-- Select Event --</option>
                                    @foreach($events as $event)
                                        <option value="{{ $event->id }}" {{ old('event_id') == $event->id ? 'selected' : '' }}>{{ $event->name }}</option>
                                    @endforeach
                                </select>
                                <p class="text-xs text-gray-500 mt-1">
                                    Participants who already hold a certificate for this event are left out of the list below.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Step 2: Select Template -->
                <div class="bg-white border border-gray-200 rounded-md shadow-sm">
                    <div class="bg-gray-50 border-b border-gray-200 px-4 py-3">
                        <h2 class="text-sm font-semibold text-gray-700 flex items-center">
                            <span class="material-icons-outlined text-primary-DEFAULT mr-2">design_services</span>
                            Step 2: Select Certificate Template
                        </h2>
                    </div>
                    
                    <div class="p-4">
                        <div class="flex flex-col md:flex-row md:items-start gap-3">
                            <label for="template_id" class="text-xs font-medium text-gray-700 md:w-48 shrink-0 md:pt-2">
                                Template <span class="text-red-500">*</span>
                            </label>
                            <div class="flex-1">
                                <select id="template_id" name="template_id" class="w-full h-9 text-xs border-gray-300 rounded px-3 focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50" required>
                                    <option value="">-- Select Template --</option>
                                    @foreach($templates as $template)
                                        <option value="{{ $template->id }}" {{ old('template_id') == $template->id ? 'selected' : '' }}>{{ $template->name }}</option>
                                    @endforeach
                                </select>
                                <p class="text-xs text-gray-500 mt-1">
                                    The template decides the layout. You can check it with Preview before generating.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Step 3: Select Participants -->
                <div class="bg-white border border-gray-200 rounded-md shadow-sm">
                    <div class="bg-gray-50 border-b border-gray-200 px-4 py-3">
                        <h2 class="text-sm font-semibold text-gray-700 flex items-center">
                            <span class="material-icons-outlined text-primary-DEFAULT mr-2">people</span>
                            Step 3: Select Participants
                        </h2>
                    </div>
                    
                    <div class="p-4 space-y-4">
                        <!-- Data Source -->
                        <div class="flex flex-col md:flex-row md:items-start gap-3">
                            <label class="text-xs font-medium text-gray-700 md:w-48 shrink-0 md:pt-1">Who is eligible</label>
                            <div class="flex-1 space-y-2">
                                <label class="flex items-start gap-2 cursor-pointer">
                                    <input type="radio" name="data_source" value="participants" class="mt-0.5 shrink-0 text-primary-DEFAULT" checked>
                                    <span>
                                        <span class="block text-xs text-gray-800">Everyone registered for the event</span>
                                        <span class="block text-xs text-gray-500">Attendance is not taken into account.</span>
                                    </span>
                                </label>
                                <label class="flex items-start gap-2 cursor-pointer">
                                    <input type="radio" name="data_source" value="attendance" class="mt-0.5 shrink-0 text-primary-DEFAULT">
                                    <span>
                                        <span class="block text-xs text-gray-800">Only those marked present in attendance</span>
                                        <span class="block text-xs text-gray-500">Requires an attendance session with scanned records.</span>
                                    </span>
                                </label>
                            </div>
                        </div>

                        <!-- Registration Type Filter -->
                        <div class="flex flex-col md:flex-row md:items-start gap-3">
                            <label class="text-xs font-medium text-gray-700 md:w-48 shrink-0 md:pt-1">Registration type</label>
                            <div class="flex-1 flex flex-wrap gap-x-6 gap-y-2">
                                <label class="flex items-center gap-2 cursor-pointer">
                                    <input type="radio" name="registration_filter" value="all" class="shrink-0 text-primary-DEFAULT" checked>
                                    <span class="text-xs text-gray-800">All types</span>
                                </label>
                                <label class="flex items-center gap-2 cursor-pointer">
                                    <input type="radio" name="registration_filter" value="verified" class="shrink-0 text-primary-DEFAULT">
                                    <span class="text-xs text-gray-800">Verified only</span>
                                </label>
                                <label class="flex items-center gap-2 cursor-pointer">
                                    <input type="radio" name="registration_filter" value="simplified" class="shrink-0 text-primary-DEFAULT">
                                    <span class="text-xs text-gray-800">Quick registration only</span>
                                </label>
                            </div>
                        </div>

                        <!-- Search -->
                        <div class="flex flex-col md:flex-row md:items-center gap-3">
                            <label for="search" class="text-xs font-medium text-gray-700 md:w-48 shrink-0">Find a participant</label>
                            <div class="flex-1 flex flex-wrap items-center gap-2">
                                <input type="text" id="search" placeholder="Search name or organization..."
                                       class="flex-1 min-w-[12rem] h-9 text-xs border-gray-300 rounded px-3 focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50">
                                <button type="button" id="selectAll" class="h-9 px-3 border border-gray-300 rounded text-xs text-gray-700 hover:bg-gray-50 shrink-0">Select all</button>
                                <button type="button" id="deselectAll" class="h-9 px-3 border border-gray-300 rounded text-xs text-gray-700 hover:bg-gray-50 shrink-0">Clear</button>
                            </div>
                        </div>

                        <!-- Participants List -->
                        <div class="flex flex-col md:flex-row md:items-start gap-3">
                            <label class="text-xs font-medium text-gray-700 md:w-48 shrink-0 md:pt-2">
                                Participants <span class="text-red-500">*</span>
                            </label>
                            <div class="flex-1">
                                <div class="border border-gray-200 rounded overflow-hidden">
                                    <div class="max-h-60 overflow-y-auto divide-y divide-gray-100" id="participantsContainer">
                                        <div class="flex items-center justify-center h-20 text-xs text-gray-500">
                                            <span class="material-icons-outlined text-gray-300 mr-2 text-sm">info</span>
                                            Select an event first
                                        </div>
                                    </div>
                                </div>
                                <p class="text-xs text-gray-500 mt-1" id="selectionSummary">No participant selected yet.</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Preview Certificate -->
                <div class="bg-white border border-gray-200 rounded-md shadow-sm">
                    <div class="bg-gray-50 border-b border-gray-200 px-4 py-3">
                        <h2 class="text-sm font-semibold text-gray-700 flex items-center">
                            <span class="material-icons-outlined text-primary-DEFAULT mr-2">preview</span>
                            Preview Certificate
                        </h2>
                    </div>
                    
                    <div class="p-4">
                        <div class="flex flex-col md:flex-row md:items-start gap-3">
                            <span class="text-xs font-medium text-gray-700 md:w-48 shrink-0 md:pt-1">Check the layout</span>
                            <div class="flex-1 space-y-3">
                                <button type="button" id="previewBtn" class="h-9 px-3 border border-gray-300 rounded text-xs text-gray-700 hover:bg-gray-50 flex items-center disabled:opacity-50 disabled:cursor-not-allowed" disabled>
                                    <span class="material-icons-outlined text-xs mr-1">visibility</span>
                                    Preview certificate
                                </button>
                                <p class="text-xs text-gray-500">
                                    Uses the first selected participant. Choose an event, a template and at least one
                                    participant to enable this.
                                </p>
                            <div id="previewContainer" class="hidden">
                                <div class="border rounded-md p-4 bg-gray-50">
                                    <div class="flex justify-between items-center mb-2">
                                        <h3 class="text-sm font-medium text-gray-700">Certificate Preview</h3>
                                        <button type="button" id="expandPreviewBtn" class="text-xs bg-blue-50 hover:bg-blue-100 text-blue-700 px-2 py-1 rounded flex items-center">
                                            <span class="material-icons-outlined text-xs mr-1">fullscreen</span>
                                            Expand
                                        </button>
                                    </div>
                                    <div id="regularPreview" class="bg-white border">
                                        <iframe id="previewFrame" class="w-full" style="height: 500px;"></iframe>
                                    </div>
                                    <!-- Fullscreen Modal Preview -->
                                    <div id="fullscreenPreview" class="fixed inset-0 modal-backdrop-glass hidden z-50 flex items-center justify-center p-4">
                                        <div class="bg-white rounded-lg w-11/12 h-5/6 flex flex-col">
                                            <div class="flex justify-between items-center p-4 border-b">
                                                <h3 class="font-medium">Certificate Preview</h3>
                                                <button id="closeFullscreenBtn" class="text-gray-500 hover:text-gray-700">
                                                    <span class="material-icons">close</span>
                                                </button>
                                            </div>
                                            <div class="flex-1 overflow-auto p-4">
                                                <iframe id="fullscreenFrame" class="w-full h-full border-0"></iframe>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Submit Button -->
                <div class="flex justify-end space-x-3 pt-4">
                    <a href="{{ route('certificates.index') }}" class="px-3 h-[36px] bg-gradient-to-r from-red-600 to-red-500 hover:from-red-700 hover:to-red-600 text-white rounded shadow-sm text-xs font-medium transition-colors duration-200 ease-in-out flex items-center">
                        <span class="material-icons-outlined text-xs mr-1">cancel</span>
                        Cancel
                    </a>
                    <button type="submit" id="generateBtn" class="px-3 h-[36px] bg-gradient-to-r from-green-600 to-green-500 hover:from-green-700 hover:to-green-600 text-white rounded shadow-sm text-xs font-medium transition-colors duration-200 ease-in-out flex items-center" disabled>
                        <span class="material-icons-outlined text-xs mr-1">workspace_premium</span>
                        Generate Certificates
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const eventSelect = document.getElementById('event_id');
            const templateSelect = document.getElementById('template_id');
            const dataSourceRadios = document.querySelectorAll('input[name="data_source"]');
            const registrationFilterRadios = document.querySelectorAll('input[name="registration_filter"]');
            const searchInput = document.getElementById('search');
            const participantsContainer = document.getElementById('participantsContainer');
            const selectAllBtn = document.getElementById('selectAll');
            const deselectAllBtn = document.getElementById('deselectAll');
            const previewBtn = document.getElementById('previewBtn');
            const generateBtn = document.getElementById('generateBtn');
            const previewContainer = document.getElementById('previewContainer');
            const previewFrame = document.getElementById('previewFrame');
            const expandPreviewBtn = document.getElementById('expandPreviewBtn');
            const fullscreenPreview = document.getElementById('fullscreenPreview');
            const closeFullscreenBtn = document.getElementById('closeFullscreenBtn');
            const fullscreenFrame = document.getElementById('fullscreenFrame');
            
            let participants = [];
            
            // Load participants when event changes.
            // These listeners used to be registered twice, which fired two
            // requests for every change.
            eventSelect.addEventListener('change', loadParticipants);
            
            // Reload participants when data source changes
            dataSourceRadios.forEach(radio => {
                radio.addEventListener('change', loadParticipants);
            });
            
            // Filter participants when registration type filter changes
            registrationFilterRadios.forEach(radio => {
                radio.addEventListener('change', renderParticipants);
            });
            
            // Filter participants on search
            searchInput.addEventListener('input', filterParticipants);
            
            // Select/deselect all participants
            selectAllBtn.addEventListener('click', selectAllParticipants);
            deselectAllBtn.addEventListener('click', deselectAllParticipants);
            
            // Preview certificate
            previewBtn.addEventListener('click', previewCertificate);
            
            // Fullscreen preview handlers
            expandPreviewBtn.addEventListener('click', function() {
                fullscreenPreview.classList.remove('hidden');
                document.body.style.overflow = 'hidden'; // Prevent scrolling behind modal
            });
            
            closeFullscreenBtn.addEventListener('click', function() {
                fullscreenPreview.classList.add('hidden');
                document.body.style.overflow = ''; // Restore scrolling
            });
            
            // Check form validity
            function checkFormValidity() {
                const eventSelected = eventSelect.value !== '';
                const templateSelected = templateSelect.value !== '';
                const checkedCount = document.querySelectorAll('input[name="participants[]"]:checked').length;
                const ready = eventSelected && templateSelected && checkedCount > 0;

                previewBtn.disabled = !ready;
                generateBtn.disabled = !ready;

                const summary = document.getElementById('selectionSummary');
                if (summary) {
                    const total = document.querySelectorAll('input[name="participants[]"]').length;
                    summary.textContent = checkedCount === 0
                        ? (total === 0 ? 'No participant selected yet.' : `0 of ${total} selected.`)
                        : `${checkedCount} of ${total} selected — ${checkedCount} certificate${checkedCount === 1 ? '' : 's'} will be generated.`;
                }
            }
            
            // Load participants based on selected event and data source
            function loadParticipants() {
                const eventId = eventSelect.value;
                if (!eventId) {
                    participantsContainer.innerHTML = `
                        <div class="flex items-center justify-center h-20 text-xs text-gray-500">
                            <span class="material-icons-outlined text-gray-300 mr-2 text-sm">info</span>
                            Select an event first
                        </div>
                    `;
                    participants = [];
                    checkFormValidity();
                    return;
                }
                
                const dataSource = document.querySelector('input[name="data_source"]:checked').value;
                
                participantsContainer.innerHTML = `
                    <div class="flex items-center justify-center h-20">
                        <div class="inline-block animate-spin rounded-full h-4 w-4 border-t-2 border-b-2 border-primary-DEFAULT"></div>
                        <span class="ml-2 text-xs text-gray-600">Loading participants...</span>
                    </div>
                `;
                
                fetch(`/api/certificates/participants?event_id=${eventId}&source=${dataSource}`)
                    .then(response => response.json())
                    .then(data => {
                        participants = data;
                        renderParticipants();
                    })
                    .catch(error => {
                        console.error('Error loading participants:', error);
                        participantsContainer.innerHTML = `
                            <div class="flex items-center justify-center h-20 text-xs text-red-600">
                                <span class="material-icons-outlined text-red-400 mr-2 text-sm">error</span>
                                Could not load participants. Please try again.
                            </div>
                        `;
                    });
            }
            
            // Render participants list
            function renderParticipants() {
                if (participants.length === 0) {
                    const source = document.querySelector('input[name="data_source"]:checked').value;
                    const hint = source === 'attendance'
                        ? 'Nobody has been marked present for this event yet.'
                        : 'Everyone in this event already has a certificate, or the event has no participants.';
                    participantsContainer.innerHTML = `
                        <div class="flex flex-col items-center justify-center h-20 text-xs text-gray-500 text-center px-4">
                            <span class="material-icons-outlined text-gray-300 text-sm mb-1">people</span>
                            ${hint}
                        </div>
                    `;
                    checkFormValidity();
                    return;
                }
                
                const searchTerm = searchInput.value.toLowerCase();
                const registrationFilter = document.querySelector('input[name="registration_filter"]:checked').value;
                
                // Apply search filter
                let filteredParticipants = searchTerm 
                    ? participants.filter(p => 
                        p.name.toLowerCase().includes(searchTerm) || 
                        (p.organization && p.organization.toLowerCase().includes(searchTerm))
                      )
                    : participants;
                
                // Apply registration type filter
                if (registrationFilter === 'verified') {
                    filteredParticipants = filteredParticipants.filter(p => p.registration_type === 'verified');
                } else if (registrationFilter === 'simplified') {
                    filteredParticipants = filteredParticipants.filter(p => p.registration_type === 'simplified');
                }
                
                if (filteredParticipants.length === 0) {
                    participantsContainer.innerHTML = `
                        <div class="flex items-center justify-center h-20 text-xs text-gray-500">
                            <span class="material-icons-outlined text-gray-300 mr-2 text-sm">search</span>
                            No participants match your filters
                        </div>
                    `;
                    checkFormValidity();
                    return;
                }
                
                participantsContainer.innerHTML = filteredParticipants.map(p => {
                    // Determine badge based on registration type
                    const badge = p.registration_type === 'simplified' 
                        ? '<span class="ml-2 px-2 py-0.5 text-[10px] font-medium bg-blue-100 text-blue-700 rounded">Quick registration</span>'
                        : '<span class="ml-2 px-2 py-0.5 text-[10px] font-medium bg-green-100 text-green-700 rounded">Verified</span>';
                    
                    return `
                        <label for="participant_${p.id}" class="flex items-center gap-2 px-3 py-2 cursor-pointer hover:bg-gray-50">
                            <input type="checkbox" name="participants[]" value="${p.id}" id="participant_${p.id}" class="participant-checkbox shrink-0 h-4 w-4 text-primary-DEFAULT focus:ring-primary-light rounded">
                            <span class="flex-1 min-w-0">
                                <span class="block text-xs font-medium text-gray-800 truncate">
                                    ${p.name}
                                    ${badge}
                                </span>
                                <span class="block text-xs text-gray-500 truncate">${p.organization || '—'}</span>
                            </span>
                        </label>
                    `;
                }).join('');
                
                // Add event listeners to checkboxes
                document.querySelectorAll('.participant-checkbox').forEach(checkbox => {
                    checkbox.addEventListener('change', checkFormValidity);
                });
                
                checkFormValidity();
            }
            
            // Filter participants based on search input
            function filterParticipants() {
                renderParticipants();
            }
            
            // Select all participants
            function selectAllParticipants() {
                document.querySelectorAll('input[name="participants[]"]').forEach(checkbox => {
                    checkbox.checked = true;
                });
                checkFormValidity();
            }
            
            // Deselect all participants
            function deselectAllParticipants() {
                document.querySelectorAll('input[name="participants[]"]').forEach(checkbox => {
                    checkbox.checked = false;
                });
                checkFormValidity();
            }
            
            // Preview certificate
            function previewCertificate() {
                const eventId = eventSelect.value;
                const templateId = templateSelect.value;
                const participantCheckboxes = document.querySelectorAll('input[name="participants[]"]:checked');
                
                if (!eventId || !templateId || participantCheckboxes.length === 0) {
                    return;
                }
                
                // Use the first selected participant for preview
                const participantId = participantCheckboxes[0].value;
                
                previewBtn.disabled = true;
                previewBtn.innerHTML = `
                    <div class="inline-block animate-spin rounded-full h-3 w-3 border-t-2 border-b-2 border-gray-500 mr-1"></div>
                    Generating preview...
                `;
                
                fetch('/certificates/preview', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({
                        event_id: eventId,
                        template_id: templateId,
                        participant_id: participantId
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        previewFrame.src = data.preview_url;
                        fullscreenFrame.src = data.preview_url;
                        previewContainer.classList.remove('hidden');
                        
                        // Scroll to the preview
                        previewContainer.scrollIntoView({ behavior: 'smooth', block: 'start' });
                    } else {
                        alert('Error generating preview: ' + data.error);
                    }
                })
                .catch(error => {
                    console.error('Error generating preview:', error);
                    alert('Error generating preview. Please try again.');
                })
                .finally(() => {
                    previewBtn.disabled = false;
                    previewBtn.innerHTML = `
                        <span class="material-icons-outlined text-xs mr-1">visibility</span>
                        Preview certificate
                    `;
                });
            }
            
            // Make checkFormValidity available globally for the onchange handler
            window.checkFormValidity = checkFormValidity;
        });
    </script>
</x-app-layout> 