<x-app-layout>
    <x-slot name="breadcrumb">
        <span>Certificate</span>
        <span class="mx-2 text-gray-500">/</span>
        <span><a href="{{ route('template.designer') }}" class="text-primary-DEFAULT hover:underline">Template Designer</a></span>
        <span class="mx-2 text-gray-500">/</span>
        <span>Design Editor</span>
    </x-slot>

    <x-slot name="title">Certificate Designer</x-slot>

    <!-- Add CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <div x-data="certificateEditor({
            templateId: {{ $template->id }},
            pdfUrl: '{{ asset('storage/' . $template->pdf_file) }}',
            placeholders: {{ $template->placeholders ? json_encode($template->placeholders) : '[]' }},
            orientation: '{{ $template->orientation }}'
        })" 
        x-init="init()"
        class="bg-white rounded shadow-md border border-gray-300">
        
        <div class="p-6 border-b border-gray-200">
            <div class="flex items-center">
                <span class="material-icons mr-2 text-primary-DEFAULT">design_services</span>
                <h1 class="text-xl font-bold text-gray-800">Design Certificate: {{ $template->name }}</h1>
            </div>
            <p class="text-xs text-gray-500 mt-1 ml-8">Add and position text elements on your certificate template</p>
        </div>

        <!-- Success/Error Messages -->
        <div class="px-6 pt-4">
            <div x-show="message.text" x-transition x-cloak :class="{'bg-green-100 border-green-400 text-green-700': message.type === 'success', 'bg-red-100 border-red-400 text-red-700': message.type === 'error', 'bg-blue-100 border-blue-400 text-blue-700': message.type === 'info'}" class="border px-4 py-3 rounded mb-4">
                <div class="flex items-center">
                    <span class="material-icons mr-2" x-show="message.type === 'success'">check_circle</span>
                    <span class="material-icons mr-2" x-show="message.type === 'error'">error</span>
                    <span class="material-icons mr-2" x-show="message.type === 'info'">info</span>
                <span x-text="message.text"></span>
                </div>
            </div>
        </div>

        <div class="p-6 grid grid-cols-1 lg:grid-cols-4 gap-6">
            <!-- Left Column: Certificate Preview & Tools -->
            <div class="lg:col-span-3 border rounded-lg p-4 bg-gray-50">
                <div class="mb-4 flex justify-between items-center">
                    <div class="flex space-x-2">
                        @verbatim
                        <button @click="addPlaceholder('name')" class="bg-blue-100 hover:bg-blue-200 text-blue-700 px-2 py-1 rounded text-xs flex items-center">
                            <span class="material-icons text-xs mr-1">add</span> {{ name }}
                        </button>
                        <button @click="addPlaceholder('organization')" class="bg-blue-100 hover:bg-blue-200 text-blue-700 px-2 py-1 rounded text-xs flex items-center">
                            <span class="material-icons text-xs mr-1">add</span> {{ organization }}
                        </button>
                        <button @click="addPlaceholder('event')" class="bg-blue-100 hover:bg-blue-200 text-blue-700 px-2 py-1 rounded text-xs flex items-center">
                            <span class="material-icons text-xs mr-1">add</span> {{ event }}
                        </button>
                        <button @click="addPlaceholder('date')" class="bg-blue-100 hover:bg-blue-200 text-blue-700 px-2 py-1 rounded text-xs flex items-center">
                            <span class="material-icons text-xs mr-1">add</span> {{ date }}
                        </button>
                        <button @click="addPlaceholder('identity_card')" class="bg-blue-100 hover:bg-blue-200 text-blue-700 px-2 py-1 rounded text-xs flex items-center">
                            <span class="material-icons text-xs mr-1">add</span> {{ identity_card }}
                        </button>
                        @endverbatim
                    </div>
                    <div class="flex items-center space-x-2">
                        <span class="bg-yellow-100 text-yellow-800 px-2 py-1 rounded text-xs">
                            <span class="material-icons text-xs mr-1">info</span>
                            Position: <span x-text="mousePosition.x"></span>mm, <span x-text="mousePosition.y"></span>mm
                        </span>
                        <button @click="toggleGrid()" class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-2 py-1 rounded text-xs flex items-center">
                            <span class="material-icons text-xs mr-1" x-text="showGrid ? 'grid_off' : 'grid_on'"></span>
                            <span x-text="showGrid ? 'Hide Grid' : 'Show Grid'"></span>
                        </button>
                    </div>
                </div>
                
                <!-- Certificate Canvas Container -->
                <div class="certificate-wrapper">
                    <div 
                        id="certificate-container" 
                        :class="orientation === 'portrait' ? 'portrait-container' : 'landscape-container'"
                        class="relative bg-white border rounded-lg"
                        x-ref="certificateContainer"
                        @mousemove="trackMousePosition"
                        @click="handleContainerClick"
                    >
                        <!-- Loading Indicator -->
                    <div x-show="isLoading" class="absolute inset-0 flex items-center justify-center bg-white bg-opacity-80 z-30">
                        <div class="text-center">
                            <div class="inline-block animate-spin rounded-full h-8 w-8 border-t-2 border-b-2 border-primary-DEFAULT"></div>
                                <p class="mt-2 text-sm text-gray-600">Loading certificate...</p>
                        </div>
                    </div>
                    
                        <!-- Debug PDF Path Info -->
                        <div x-show="!backgroundImageUrl || pdfLoadError" class="absolute top-0 left-0 right-0 bg-red-100 p-2 text-xs text-red-700 z-40">
                            <p class="font-bold">PDF loading issue:</p>
                            <p>Expected path: <span x-text="pdfUrl"></span></p>
                            <p x-show="pdfLoadError">Error: <span x-text="pdfLoadError"></span></p>
                            <button @click="tryAlternativePath()" class="mt-1 bg-red-200 px-2 py-1 rounded text-xs">Try Alternative Path</button>
                    </div>
                    
                        <!-- Background Image -->
                        <img 
                            x-ref="backgroundImage" 
                            :src="backgroundImageUrl" 
                            class="absolute top-0 left-0 w-full h-full object-contain"
                            @load="backgroundLoaded"
                            @error="imageLoadError"
                            style="z-index: 1;"
                        />
                    
                        <!-- Grid Overlay -->
                        <div 
                            x-show="showGrid" 
                            class="absolute inset-0 z-10 pointer-events-none" 
                            x-ref="gridOverlay"
                            style="z-index: 2;"
                        ></div>
                        
                        <!-- Placeholders Layer -->
                        <div class="absolute inset-0" style="z-index: 20;">
                            <template x-for="(placeholder, index) in placeholders" :key="placeholder.id || index">
                                <div 
                                    class="absolute bg-white bg-opacity-80 border cursor-move"
                                    :class="{'border-2 border-blue-500': selectedPlaceholder && selectedPlaceholder.id === placeholder.id}"
                                        :style="`
                                        left: ${placeholder.x}mm; 
                                        top: ${placeholder.y}mm; 
                                        font-family: ${placeholder.fontFamily || 'Arial, sans-serif'}; 
                                        font-size: ${placeholder.fontSize || 6}mm; 
                                        color: ${placeholder.color || '#000000'}; 
                                            font-weight: ${placeholder.bold ? 'bold' : 'normal'}; 
                                            font-style: ${placeholder.italic ? 'italic' : 'normal'}; 
                                            text-decoration: ${placeholder.underline ? 'underline' : 'none'}; 
                                            text-align: ${placeholder.textAlign || 'left'};
                                            background-color: ${placeholder.backgroundColor || 'transparent'};
                                        padding: 2px 5px;
                                        min-width: 30mm;
                                        transform: scale(2); /* Scale for preview */
                                        transform-origin: top left;
                                    `"
                                    @mousedown="startDrag($event, placeholder)"
                                    @click.stop="selectPlaceholder(placeholder)"
                                    x-text="placeholder.type"
                                ></div>
                                </template>
                    </div>
                </div>
                
                <!-- Save Button -->
                <div class="mt-4 flex justify-end">
                    <button 
                        @click="savePlaceholders()" 
                        class="bg-primary-DEFAULT hover:bg-primary-dark text-white px-4 py-2 rounded-md flex items-center"
                    >
                        <span class="material-icons mr-1">save</span>
                        Save Design
                    </button>
                </div>
            </div>
            
            <!-- Right Column: Properties Panel -->
            <div class="lg:col-span-1 border rounded-lg p-4 bg-white">
                <h2 class="text-lg font-semibold mb-4">Properties</h2>
                
                    <div x-show="!selectedPlaceholder">
                    <p class="text-gray-500 text-sm italic">Select a placeholder to edit its properties</p>
                </div>
                
                    <div x-show="selectedPlaceholder">
                        <h3 class="text-md font-medium text-gray-700 mb-2" x-text="selectedPlaceholder ? selectedPlaceholder.type : ''"></h3>
                    
                    <!-- Position Inputs -->
                    <div class="grid grid-cols-2 gap-4 mb-4">
                        <div>
                                <label for="x-position" class="block text-sm font-medium text-gray-700 mb-1">X Position (mm)</label>
                            <input
                                type="number"
                                id="x-position"
                                class="w-full rounded-md border-gray-300 shadow-sm focus:border-primary-DEFAULT focus:ring focus:ring-primary-DEFAULT focus:ring-opacity-50"
                                    x-model="selectedPlaceholder.x"
                                    @input="updatePlaceholder('x', $event.target.value)"
                                step="0.1"
                            />
                        </div>
                        <div>
                                <label for="y-position" class="block text-sm font-medium text-gray-700 mb-1">Y Position (mm)</label>
                            <input
                                type="number"
                                id="y-position"
                                class="w-full rounded-md border-gray-300 shadow-sm focus:border-primary-DEFAULT focus:ring focus:ring-primary-DEFAULT focus:ring-opacity-50"
                                    x-model="selectedPlaceholder.y"
                                    @input="updatePlaceholder('y', $event.target.value)"
                                step="0.1"
                            />
                        </div>
                    </div>
                    
                    <!-- Text Formatting Options -->
                    <div>
                        <!-- Font Family -->
                        <div class="mb-4">
                            <label for="font-family" class="block text-sm font-medium text-gray-700 mb-1">Font</label>
                            <select
                                id="font-family"
                                class="w-full rounded-md border-gray-300 shadow-sm focus:border-primary-DEFAULT focus:ring focus:ring-primary-DEFAULT focus:ring-opacity-50"
                                    x-model="selectedPlaceholder.fontFamily"
                                    @change="updatePlaceholder('fontFamily', $event.target.value)"
                            >
                                <option value="Arial, sans-serif">Arial</option>
                                <option value="'Times New Roman', serif">Times New Roman</option>
                                <option value="'Courier New', monospace">Courier New</option>
                                <option value="Georgia, serif">Georgia</option>
                                <option value="Verdana, sans-serif">Verdana</option>
                                <option value="Tahoma, sans-serif">Tahoma</option>
                                <option value="'Trebuchet MS', sans-serif">Trebuchet MS</option>
                                <option value="Impact, sans-serif">Impact</option>
                                <option value="'Arial Black', sans-serif">Arial Black</option>
                                <option value="'Palatino Linotype', serif">Palatino Linotype</option>
                            </select>
                        </div>
                        
                        <!-- Font Size -->
                        <div class="mb-4">
                            <label for="font-size" class="block text-sm font-medium text-gray-700 mb-1">Font Size (mm)</label>
                            <div class="flex items-center">
                                <input
                                    type="range"
                                    id="font-size"
                                    min="1"
                                    max="20"
                                    step="0.5"
                                    class="w-full mr-2"
                                        x-model="selectedPlaceholder.fontSize"
                                        @input="updatePlaceholder('fontSize', $event.target.value)"
                                />
                                    <span class="text-sm text-gray-700 w-10" x-text="selectedPlaceholder.fontSize"></span>
                            </div>
                        </div>
                        
                        <!-- Text Color -->
                        <div class="mb-4">
                            <label for="text-color" class="block text-sm font-medium text-gray-700 mb-1">Text Color</label>
                            <div class="flex items-center">
                                <input
                                    type="color"
                                    id="text-color"
                                    class="w-10 h-10 rounded-md border-gray-300 shadow-sm"
                                        x-model="selectedPlaceholder.color"
                                        @input="updatePlaceholder('color', $event.target.value)"
                                />
                                    <span class="ml-2 text-sm text-gray-700" x-text="selectedPlaceholder.color"></span>
                            </div>
                        </div>
                        
                        <!-- Text Style -->
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Text Style</label>
                            <div class="flex space-x-2">
                                <button
                                    @click="toggleBold()"
                                        :class="{'bg-blue-500 text-white': selectedPlaceholder && selectedPlaceholder.bold, 'bg-gray-200 text-gray-700': !selectedPlaceholder || !selectedPlaceholder.bold}"
                                    class="px-3 py-1 rounded-md"
                                >
                                    Bold
                                </button>
                                <button
                                    @click="toggleItalic()"
                                        :class="{'bg-blue-500 text-white': selectedPlaceholder && selectedPlaceholder.italic, 'bg-gray-200 text-gray-700': !selectedPlaceholder || !selectedPlaceholder.italic}"
                                    class="px-3 py-1 rounded-md"
                                >
                                    Italic
                                </button>
                                <button
                                    @click="toggleUnderline()"
                                        :class="{'bg-blue-500 text-white': selectedPlaceholder && selectedPlaceholder.underline, 'bg-gray-200 text-gray-700': !selectedPlaceholder || !selectedPlaceholder.underline}"
                                    class="px-3 py-1 rounded-md"
                                >
                                    Underline
                                </button>
                            </div>
                        </div>
                        
                        <!-- Text Alignment -->
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Text Alignment</label>
                            <div class="flex space-x-2">
                                <button
                                        @click="updatePlaceholder('textAlign', 'left')"
                                        :class="{'bg-blue-500 text-white': selectedPlaceholder && selectedPlaceholder.textAlign === 'left', 'bg-gray-200 text-gray-700': !selectedPlaceholder || selectedPlaceholder.textAlign !== 'left'}"
                                    class="px-3 py-1 rounded-md"
                                >
                                    Left
                                </button>
                                <button
                                        @click="updatePlaceholder('textAlign', 'center')"
                                        :class="{'bg-blue-500 text-white': selectedPlaceholder && selectedPlaceholder.textAlign === 'center', 'bg-gray-200 text-gray-700': !selectedPlaceholder || selectedPlaceholder.textAlign !== 'center'}"
                                    class="px-3 py-1 rounded-md"
                                >
                                    Center
                                </button>
                                <button
                                        @click="updatePlaceholder('textAlign', 'right')"
                                        :class="{'bg-blue-500 text-white': selectedPlaceholder && selectedPlaceholder.textAlign === 'right', 'bg-gray-200 text-gray-700': !selectedPlaceholder || selectedPlaceholder.textAlign !== 'right'}"
                                    class="px-3 py-1 rounded-md"
                                >
                                    Right
                                </button>
                            </div>
                        </div>
                        
                        <!-- Background Color -->
                        <div class="mb-4">
                            <label for="bg-color" class="block text-sm font-medium text-gray-700 mb-1">Background Color</label>
                            <div class="flex items-center">
                                <input
                                    type="color"
                                    id="bg-color"
                                    class="w-10 h-10 rounded-md border-gray-300 shadow-sm"
                                        :value="selectedPlaceholder.backgroundColor || '#ffffff'"
                                        @input="updatePlaceholder('backgroundColor', $event.target.value)"
                                />
                                    <span class="ml-2 text-sm text-gray-700" x-text="selectedPlaceholder.backgroundColor || 'Transparent'"></span>
                                <button
                                    @click="toggleTransparentBackground()"
                                        :class="{'bg-blue-500 text-white': selectedPlaceholder && !selectedPlaceholder.backgroundColor, 'bg-gray-200 text-gray-700': !selectedPlaceholder || selectedPlaceholder.backgroundColor}"
                                    class="ml-2 px-3 py-1 rounded-md text-xs"
                                >
                                    Transparent
                                </button>
                            </div>
                        </div>
                        
                        <!-- Delete Button -->
                        <div class="mt-6">
                            <button
                                @click="deletePlaceholder()"
                                class="bg-red-500 text-white px-4 py-2 rounded-md hover:bg-red-600 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-opacity-50"
                            >
                                Delete Placeholder
                            </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function certificateEditor(config) {
            return {
                templateId: config.templateId,
                pdfUrl: config.pdfUrl,
                backgroundImageUrl: '',
                placeholders: config.placeholders || [],
                orientation: config.orientation || 'portrait',
                selectedPlaceholder: null,
                isLoading: true,
                mousePosition: { x: 0, y: 0 },
                showGrid: false,
                dragging: false,
                currentDragElement: null,
                initialX: 0,
                initialY: 0,
                offsetX: 0,
                offsetY: 0,
                pdfLoadError: null,
                
                // A4 dimensions in mm
                dimensions: {
                    portrait: { width: 210, height: 297 },
                    landscape: { width: 297, height: 210 }
                },
                
                message: {
                    text: '',
                    type: 'success'
                },
                
                init() {
                    // Generate unique IDs for placeholders if they don't have them
                    this.placeholders = this.placeholders.map(placeholder => {
                        if (!placeholder.id) {
                            placeholder.id = this.generateUniqueId();
                        }
                        return placeholder;
                    });
                    
                    // Convert PDF to image for background
                    this.loadPdfAsImage();
                    
                    // Close selected placeholder when clicking outside
                    document.addEventListener('click', (e) => {
                        if (!e.target.closest('.cursor-move') && !e.target.closest('#certificate-container')) {
                            this.selectedPlaceholder = null;
                        }
                    });
                },
                
                generateUniqueId() {
                    return 'ph_' + Date.now() + '_' + Math.floor(Math.random() * 1000);
                },
                
                loadPdfAsImage() {
                    this.isLoading = true;
                    this.pdfLoadError = null;
                    
                    // Use PDF as image directly but log the URL for debugging
                    // Loading PDF from URL
                    this.backgroundImageUrl = this.pdfUrl;
                    
                    // Log additional information for debugging
                    fetch(this.pdfUrl, {method: 'HEAD'})
                        .then(response => {
                            // PDF URL check
                            if (!response.ok) {
                                console.error('PDF URL may be invalid or inaccessible');
                                this.pdfLoadError = `HTTP ${response.status}: Resource not available`;
                            }
                        })
                        .catch(error => {
                            console.error('Error checking PDF URL:', error);
                            this.pdfLoadError = error.message;
                        });
                },
                
                backgroundLoaded() {
                            this.isLoading = false;
                    this.pdfLoadError = null;
                    // Background image loaded
                                    this.drawGrid();
                },
                        
                imageLoadError(e) {
                    console.error('Failed to load image:', e);
                            this.isLoading = false;
                    this.pdfLoadError = "Failed to load PDF as image";
                },
                
                tryAlternativePath() {
                    if (this.pdfUrl.includes('/storage/')) {
                        // Try without leading slash
                        const alternativePath = this.pdfUrl.replace('/storage/', 'storage/');
                        // Trying alternative path
                        this.backgroundImageUrl = alternativePath;
                    } else if (this.pdfUrl.includes('storage/')) {
                        // Try with leading slash
                        const alternativePath = '/' + this.pdfUrl;
                        // Trying alternative path with leading slash
                        this.backgroundImageUrl = alternativePath;
                    } else {
                        // Try direct public path
                        const filename = this.pdfUrl.split('/').pop();
                        const alternativePath = `/storage/certificate-templates/${filename}`;
                        // Trying direct public path
                        this.backgroundImageUrl = alternativePath;
                    }
                },
                
                drawGrid() {
                    if (!this.showGrid) return;
                    
                    const gridOverlay = this.$refs.gridOverlay;
                    if (!gridOverlay) return;
                    
                    // Clear previous grid
                    gridOverlay.innerHTML = '';
                    
                    // Get dimensions based on orientation
                    const width = this.dimensions[this.orientation].width;
                    const height = this.dimensions[this.orientation].height;
                    
                    // Create SVG for grid
                    const svg = document.createElementNS('http://www.w3.org/2000/svg', 'svg');
                    svg.setAttribute('width', width + 'mm');
                    svg.setAttribute('height', height + 'mm');
                    svg.style.position = 'absolute';
                    svg.style.top = '0';
                    svg.style.left = '0';
                    svg.style.width = '100%';
                    svg.style.height = '100%';
                    
                    // Draw grid lines (every 10mm)
                    const gridSize = 10; // mm
                        
                    // Horizontal lines
                    for (let i = 0; i <= height; i += gridSize) {
                        const line = document.createElementNS('http://www.w3.org/2000/svg', 'line');
                        line.setAttribute('x1', '0');
                        line.setAttribute('y1', i + 'mm');
                        line.setAttribute('x2', width + 'mm');
                        line.setAttribute('y2', i + 'mm');
                        line.setAttribute('stroke', '#ddd');
                        line.setAttribute('stroke-width', '0.5');
                        svg.appendChild(line);
                        
                        // Add label every 50mm
                        if (i % 50 === 0) {
                            const text = document.createElementNS('http://www.w3.org/2000/svg', 'text');
                            text.setAttribute('x', '2mm');
                            text.setAttribute('y', (i - 2) + 'mm');
                            text.setAttribute('fill', '#999');
                            text.setAttribute('font-size', '8');
                            text.textContent = i + 'mm';
                            svg.appendChild(text);
                        }
                    }
                    
                    // Vertical lines
                    for (let i = 0; i <= width; i += gridSize) {
                        const line = document.createElementNS('http://www.w3.org/2000/svg', 'line');
                        line.setAttribute('x1', i + 'mm');
                        line.setAttribute('y1', '0');
                        line.setAttribute('x2', i + 'mm');
                        line.setAttribute('y2', height + 'mm');
                        line.setAttribute('stroke', '#ddd');
                        line.setAttribute('stroke-width', '0.5');
                        svg.appendChild(line);
                        
                        // Add label every 50mm
                        if (i % 50 === 0) {
                            const text = document.createElementNS('http://www.w3.org/2000/svg', 'text');
                            text.setAttribute('x', i + 'mm');
                            text.setAttribute('y', '10mm');
                            text.setAttribute('fill', '#999');
                            text.setAttribute('font-size', '8');
                            text.textContent = i + 'mm';
                            svg.appendChild(text);
                        }
                    }
                    
                    gridOverlay.appendChild(svg);
                },
                
                toggleGrid() {
                    this.showGrid = !this.showGrid;
                    if (this.showGrid) {
                        this.drawGrid();
                    }
                },
                
                trackMousePosition(e) {
                    const rect = this.$refs.certificateContainer.getBoundingClientRect();
                    const scaleX = this.dimensions[this.orientation].width / rect.width;
                    const scaleY = this.dimensions[this.orientation].height / rect.height;
                    
                    const x = (e.clientX - rect.left) * scaleX;
                    const y = (e.clientY - rect.top) * scaleY;
                    
                    this.mousePosition = {
                        x: Math.round(x * 10) / 10,
                        y: Math.round(y * 10) / 10
                    };
                },
                
                handleContainerClick(e) {
                    // Only handle click if we're not dragging
                    if (this.dragging) return;
                    
                    // Clear selected placeholder
                    this.selectedPlaceholder = null;
                },
                
                addPlaceholder(type) {
                    const newPlaceholder = {
                        id: this.generateUniqueId(),
                        type: '@{{' + type + '}}',
                        x: this.dimensions[this.orientation].width / 2 - 15, // Center X, minus half the width
                        y: this.dimensions[this.orientation].height / 2, // Center Y
                        fontFamily: 'Arial, sans-serif',
                        fontSize: 6,
                        color: '#000000',
                        bold: false,
                        italic: false,
                        underline: false,
                        textAlign: 'left',
                        backgroundColor: null
                    };
                    
                    this.placeholders.push(newPlaceholder);
                    this.selectPlaceholder(newPlaceholder);
                },
                
                selectPlaceholder(placeholder) {
                    this.selectedPlaceholder = placeholder;
                },
                
                updatePlaceholder(key, value) {
                    if (!this.selectedPlaceholder) return;
                    
                    const index = this.placeholders.findIndex(p => p.id === this.selectedPlaceholder.id);
                    if (index !== -1) {
                        // Convert to appropriate type
                        if (['x', 'y', 'fontSize'].includes(key)) {
                            value = parseFloat(value);
                        }
                        
                        // Update the placeholder in the array
                        this.placeholders[index][key] = value;
                        
                        // Update the selected placeholder reference
                        this.selectedPlaceholder[key] = value;
                    }
                },
                
                startDrag(e, placeholder) {
                    e.preventDefault();
                    
                    this.selectPlaceholder(placeholder);
                    this.dragging = true;
                    this.currentDragElement = placeholder;
                    
                    // Get initial positions
                    this.initialX = e.clientX;
                    this.initialY = e.clientY;
                    
                    // Calculate element offsets
                    const rect = this.$refs.certificateContainer.getBoundingClientRect();
                    const scaleX = this.dimensions[this.orientation].width / rect.width;
                    
                    // Set up mouse move and mouse up event listeners
                    document.addEventListener('mousemove', this.handleDrag.bind(this));
                    document.addEventListener('mouseup', this.stopDrag.bind(this));
                },
                
                handleDrag(e) {
                    if (!this.dragging || !this.currentDragElement) return;
                    
                    e.preventDefault();
                    
                    // Get the element offset
                    const rect = this.$refs.certificateContainer.getBoundingClientRect();
                    const scaleX = this.dimensions[this.orientation].width / rect.width;
                    const scaleY = this.dimensions[this.orientation].height / rect.height;
                    
                    // Calculate the movement in mm
                    const deltaX = (e.clientX - this.initialX) * scaleX;
                    const deltaY = (e.clientY - this.initialY) * scaleY;
                    
                    // Update the placeholder position
                    const index = this.placeholders.findIndex(p => p.id === this.currentDragElement.id);
                    if (index !== -1) {
                        // Calculate new position
                        const newX = this.currentDragElement.x + deltaX;
                        const newY = this.currentDragElement.y + deltaY;
                        
                        // Constrain within the certificate
                        const maxX = this.dimensions[this.orientation].width - 10; // 10mm margin
                        const maxY = this.dimensions[this.orientation].height - 10; // 10mm margin
                        
                        const constrainedX = Math.max(0, Math.min(maxX, newX));
                        const constrainedY = Math.max(0, Math.min(maxY, newY));
                        
                        // Update the placeholder
                        this.placeholders[index].x = Math.round(constrainedX * 10) / 10;
                        this.placeholders[index].y = Math.round(constrainedY * 10) / 10;
                        
                        // Update selected placeholder
                        this.selectedPlaceholder.x = Math.round(constrainedX * 10) / 10;
                        this.selectedPlaceholder.y = Math.round(constrainedY * 10) / 10;
                        
                        // Update initial position for next movement
                        this.initialX = e.clientX;
                        this.initialY = e.clientY;
                    }
                },
                
                stopDrag() {
                    this.dragging = false;
                    this.currentDragElement = null;
                    
                    // Remove event listeners
                    document.removeEventListener('mousemove', this.handleDrag);
                    document.removeEventListener('mouseup', this.stopDrag);
                },
                
                toggleBold() {
                    if (this.selectedPlaceholder) {
                        this.updatePlaceholder('bold', !this.selectedPlaceholder.bold);
                    }
                },
                
                toggleItalic() {
                    if (this.selectedPlaceholder) {
                        this.updatePlaceholder('italic', !this.selectedPlaceholder.italic);
                    }
                },
                
                toggleUnderline() {
                    if (this.selectedPlaceholder) {
                        this.updatePlaceholder('underline', !this.selectedPlaceholder.underline);
                    }
                },
                
                toggleTransparentBackground() {
                    if (this.selectedPlaceholder) {
                        if (this.selectedPlaceholder.backgroundColor) {
                            this.updatePlaceholder('backgroundColor', null);
                        } else {
                            this.updatePlaceholder('backgroundColor', '#ffffff');
                        }
                    }
                },
                
                deletePlaceholder() {
                    if (!this.selectedPlaceholder) return;
                    
                    const index = this.placeholders.findIndex(p => p.id === this.selectedPlaceholder.id);
                    if (index !== -1) {
                        this.placeholders.splice(index, 1);
                        this.selectedPlaceholder = null;
                    }
                },
                
                savePlaceholders() {
                    // Show saving message
                    this.showMessage('Menyimpan perubahan...', 'info');
                    
                    // Prepare data
                    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                    
                    // Log for debugging
                    // Saving placeholders
                    
                    // Send data to server
                    fetch(`/template-designer/${this.templateId}/editor`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken
                        },
                        body: JSON.stringify({ placeholders: this.placeholders })
                    })
                    .then(response => {
                        // Response status
                        if (!response.ok) {
                            return response.text().then(text => {
                                console.error('Error response:', text);
                                throw new Error(`Failed to save placeholders: ${response.status}`);
                            });
                        }
                        return response.json();
                    })
                    .then(data => {
                        // Response data
                        if (data.success) {
                            this.showMessage('Template saved successfully!', 'success');
                        } else {
                            this.showMessage(data.message || 'Failed to save template', 'error');
                        }
                    })
                    .catch(error => {
                        console.error('Error saving placeholders:', error);
                        this.showMessage('Error saving: ' + error.message, 'error');
                    });
                },
                
                showMessage(text, type = 'info') {
                    this.message = { text, type };
                    
                    // Clear message after a delay (longer for errors)
                    const timeout = type === 'error' ? 10000 : 3000;
                    setTimeout(() => {
                        if (this.message.text === text) { // Only clear if it's the same message
                        this.message = { text: '', type: 'info' };
                        }
                    }, timeout);
                }
            };
        }
    </script>
    
    <style>
        [x-cloak] { display: none !important; }
        
        /* Certificate editor styles */
        #certificate-container {
            margin: 0 auto;
            position: relative;
            background: white;
            box-shadow: 0 2px 8px rgba(0,0,0,0.15);
            transform-origin: top left;
            overflow: hidden;
        }

        /* Container for visual representation */
        .certificate-wrapper {
            position: relative;
            width: 100%;
            overflow: auto;
            padding: 20px;
            background: #f0f0f0;
            height: 80vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        /* Fix for the scale transformation - handle both portrait and landscape */
        .portrait-container {
            width: 210mm;
            height: 297mm;
            aspect-ratio: 210 / 297;
        }
        
        .landscape-container {
            width: 297mm;
            height: 210mm;
            aspect-ratio: 297 / 210;
        }
        
        /* Responsive styling to ensure visibility */
        @media (max-width: 768px) {
            #certificate-container {
                transform: scale(0.3);
            }
        }
        
        @media (min-width: 769px) and (max-width: 1280px) {
            #certificate-container {
                transform: scale(0.4);
            }
        }
        
        @media (min-width: 1281px) {
            #certificate-container {
                transform: scale(0.5);
            }
        }
    </style>
</x-app-layout> 