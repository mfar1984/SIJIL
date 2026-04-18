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
                <span class="material-icons mr-2 text-primary-DEFAULT">workspace_premium</span>
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

            <form id="certificateForm" action="{{ route('certificates.store') }}" method="POST" class="space-y-6">
                @csrf
                
                <!-- Step 1: Select Event -->
                <div class="border-b border-gray-200 pb-5">
                    <h2 class="text-sm font-semibold text-gray-700 mb-4 flex items-center">
                        <span class="material-icons text-sm mr-1 text-primary-DEFAULT">event</span>
                        Step 1: Select Event
                    </h2>
                    <div class="mb-4">
                        <label for="event_id" class="block text-xs font-medium text-gray-700 mb-1">Event</label>
                        <select id="event_id" name="event_id" class="w-full text-xs border-gray-300 rounded focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50" required>
                            <option value="">-- Select Event --</option>
                            @foreach($events as $event)
                                <option value="{{ $event->id }}" {{ old('event_id') == $event->id ? 'selected' : '' }}>{{ $event->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <!-- Step 2: Select Template -->
                <div class="border-b border-gray-200 pb-5">
                    <h2 class="text-sm font-semibold text-gray-700 mb-4 flex items-center">
                        <span class="material-icons text-sm mr-1 text-primary-DEFAULT">design_services</span>
                        Step 2: Select Certificate Template
                    </h2>
                    <div class="mb-4">
                        <label for="template_id" class="block text-xs font-medium text-gray-700 mb-1">Template</label>
                        <select id="template_id" name="template_id" class="w-full text-xs border-gray-300 rounded focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50" required>
                            <option value="">-- Select Template --</option>
                            @foreach($templates as $template)
                                <option value="{{ $template->id }}" {{ old('template_id') == $template->id ? 'selected' : '' }}>{{ $template->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <!-- Step 3: Select Participants -->
                <div class="border-b border-gray-200 pb-5">
                    <h2 class="text-sm font-semibold text-gray-700 mb-4 flex items-center">
                        <span class="material-icons text-sm mr-1 text-primary-DEFAULT">people</span>
                        Step 3: Select Participants
                    </h2>
                    
                    <!-- Data Source -->
                    <div class="mb-4">
                        <label class="block text-xs font-medium text-gray-700 mb-2">Data Source</label>
                        <div class="flex space-x-4">
                            <label class="inline-flex items-center">
                                <input type="radio" name="data_source" value="participants" class="form-radio h-4 w-4 text-primary-DEFAULT" checked>
                                <span class="ml-2 text-sm text-gray-700">All Participants</span>
                            </label>
                            <label class="inline-flex items-center">
                                <input type="radio" name="data_source" value="attendance" class="form-radio h-4 w-4 text-primary-DEFAULT">
                                <span class="ml-2 text-sm text-gray-700">Attendance Records (Present Only)</span>
                            </label>
                        </div>
                    </div>
                    
                    <!-- Search & Filter -->
                    <div class="mb-4 flex flex-col sm:flex-row sm:items-center space-y-2 sm:space-y-0 sm:space-x-2">
                        <div class="flex-1">
                            <input type="text" id="search" placeholder="Search participants..." class="w-full text-xs border-gray-300 rounded focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50">
                        </div>
                        <div>
                            <button type="button" id="selectAll" class="text-xs text-primary-DEFAULT hover:underline">Select All</button>
                            <button type="button" id="deselectAll" class="text-xs text-red-600 hover:underline ml-2">Deselect All</button>
                        </div>
                    </div>
                    
                    <!-- Participants List -->
                    <div class="border rounded-md overflow-hidden">
                        <div class="max-h-60 overflow-y-auto p-2" id="participantsContainer">
                            <div class="flex items-center justify-center h-20 text-gray-500">
                                <span class="material-icons text-gray-300 mr-2">info</span>
                                Please select an event first
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Preview Certificate -->
                <div class="border-b border-gray-200 pb-5">
                    <h2 class="text-sm font-semibold text-gray-700 mb-4 flex items-center">
                        <span class="material-icons text-sm mr-1 text-primary-DEFAULT">preview</span>
                        Preview Certificate
                    </h2>
                    <div class="mb-4">
                        <button type="button" id="previewBtn" class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-4 py-2 rounded text-xs flex items-center" disabled>
                            <span class="material-icons text-xs mr-1">visibility</span>
                            Preview Certificate
                        </button>
                        <p class="text-xs text-gray-500 mt-2">Select an event, template, and at least one participant to preview</p>
                    </div>
                    <div id="previewContainer" class="hidden">
                        <div class="border rounded-md p-4 bg-gray-50">
                            <div class="flex justify-between items-center mb-2">
                                <h3 class="text-sm font-medium text-gray-700">Certificate Preview</h3>
                                <button type="button" id="expandPreviewBtn" class="text-xs bg-blue-50 hover:bg-blue-100 text-blue-700 px-2 py-1 rounded flex items-center">
                                    <span class="material-icons text-xs mr-1">fullscreen</span>
                                    Expand
                                </button>
                            </div>
                            <div id="regularPreview" class="bg-white border">
                                <iframe id="previewFrame" class="w-full" style="height: 500px;"></iframe>
                            </div>
                            <!-- Fullscreen Modal Preview -->
                            <div id="fullscreenPreview" class="fixed inset-0 bg-black bg-opacity-75 hidden z-50 flex items-center justify-center p-4">
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

                <!-- Submit Button -->
                <div class="flex justify-end space-x-3">
                    <a href="{{ route('certificates.index') }}" class="px-3 py-1 bg-gradient-to-r from-red-600 to-red-500 hover:from-red-700 hover:to-red-600 text-white rounded shadow-sm text-xs font-medium transition-colors duration-200 ease-in-out flex items-center">
                        <span class="material-icons text-xs mr-1">cancel</span>
                        Cancel
                    </a>
                    <button type="submit" id="generateBtn" class="px-3 py-1 bg-gradient-to-r from-blue-600 to-blue-500 hover:from-blue-700 hover:to-blue-600 text-white rounded shadow-sm text-xs font-medium transition-colors duration-200 ease-in-out flex items-center" disabled>
                        <span class="material-icons text-xs mr-1">workspace_premium</span>
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
            
            // Load participants when event changes
            eventSelect.addEventListener('change', loadParticipants);
            
            // Reload participants when data source changes
            dataSourceRadios.forEach(radio => {
                radio.addEventListener('change', loadParticipants);
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
                const participantsSelected = document.querySelectorAll('input[name="participants[]"]:checked').length > 0;
                
                previewBtn.disabled = !(eventSelected && templateSelected && participantsSelected);
                generateBtn.disabled = !(eventSelected && templateSelected && participantsSelected);
            }
            
            // Load participants based on selected event and data source
            function loadParticipants() {
                const eventId = eventSelect.value;
                if (!eventId) {
                    participantsContainer.innerHTML = `
                        <div class="flex items-center justify-center h-20 text-gray-500">
                            <span class="material-icons text-gray-300 mr-2">info</span>
                            Please select an event first
                        </div>
                    `;
                    participants = [];
                    checkFormValidity();
                    return;
                }
                
                const dataSource = document.querySelector('input[name="data_source"]:checked').value;
                
                participantsContainer.innerHTML = `
                    <div class="flex items-center justify-center h-20">
                        <div class="inline-block animate-spin rounded-full h-5 w-5 border-t-2 border-b-2 border-primary-DEFAULT"></div>
                        <span class="ml-2 text-sm text-gray-600">Loading participants...</span>
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
                            <div class="flex items-center justify-center h-20 text-red-500">
                                <span class="material-icons text-red-400 mr-2">error</span>
                                Error loading participants
                            </div>
                        `;
                    });
            }
            
            // Render participants list
            function renderParticipants() {
                if (participants.length === 0) {
                    participantsContainer.innerHTML = `
                        <div class="flex items-center justify-center h-20 text-gray-500">
                            <span class="material-icons text-gray-300 mr-2">people</span>
                            No participants found
                        </div>
                    `;
                    checkFormValidity();
                    return;
                }
                
                const searchTerm = searchInput.value.toLowerCase();
                const filteredParticipants = searchTerm 
                    ? participants.filter(p => 
                        p.name.toLowerCase().includes(searchTerm) || 
                        p.organization.toLowerCase().includes(searchTerm)
                      )
                    : participants;
                
                if (filteredParticipants.length === 0) {
                    participantsContainer.innerHTML = `
                        <div class="flex items-center justify-center h-20 text-gray-500">
                            <span class="material-icons text-gray-300 mr-2">search</span>
                            No participants match your search
                        </div>
                    `;
                    checkFormValidity();
                    return;
                }
                
                participantsContainer.innerHTML = filteredParticipants.map(p => `
                    <div class="flex items-center py-2 border-b border-gray-100 last:border-0">
                        <input type="checkbox" name="participants[]" value="${p.id}" id="participant_${p.id}" class="participant-checkbox mr-2 h-4 w-4 text-primary-DEFAULT focus:ring-primary-light rounded" onchange="checkFormValidity()">
                        <label for="participant_${p.id}" class="flex-1 text-sm">
                            <div class="font-medium">${p.name}</div>
                            <div class="text-xs text-gray-500">${p.organization}</div>
                        </label>
                    </div>
                `).join('');
                
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
                    <div class="inline-block animate-spin rounded-full h-3 w-3 border-t-2 border-b-2 border-white mr-1"></div>
                    Generating Preview...
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
                        <span class="material-icons text-xs mr-1">visibility</span>
                        Preview Certificate
                    `;
                });
            }
            
            // Make checkFormValidity available globally for the onchange handler
            window.checkFormValidity = checkFormValidity;
        });
    </script>
</x-app-layout> 