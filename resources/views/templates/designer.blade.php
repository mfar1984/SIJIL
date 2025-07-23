@php
// Pre-process and escape all potentially problematic data
$initialStep = $template ? 2 : 1;
$placeholderFormat = '{{participant_name}}';

// Background data
if ($template && $template->background_pdf) {
    $initialBackground = [
        'name' => $template->name,
        'preview_image' => $template->background_pdf,
        'pdf_file' => $template->background_pdf
    ];
} else {
    $initialBackground = null;
}

// Template data
if ($template && $template->template_data) {
    if (is_string($template->template_data)) {
        $templateData = json_decode($template->template_data, true);
    } else {
        $templateData = $template->template_data;
    }
    
    if (empty($templateData) || !is_array($templateData)) {
        $templateData = ['width' => 297, 'height' => 210, 'elements' => []];
    }
} else {
    $templateData = ['width' => 297, 'height' => 210, 'elements' => []];
}

$initialOrientation = $template ? $template->orientation : 'landscape';
$initialName = $template ? $template->name : '';
$initialDescription = $template ? $template->description : '';
$initialIsActive = $template && $template->is_active ? true : false;
@endphp

<x-app-layout>
    <x-slot name="breadcrumb">
        <span>Certificate</span>
        <span class="mx-2 text-gray-500">/</span>
        <span><a href="{{ route('template.designer') }}" class="text-primary-DEFAULT hover:underline">Template Designer</a></span>
        <span class="mx-2 text-gray-500">/</span>
        <span>Visual Designer</span>
    </x-slot>

    <x-slot name="title">{{ $template ? 'Edit Template' : 'Create Template' }}</x-slot>

    <!-- Debug section - will be hidden in production -->
    <div class="bg-red-100 p-4 mb-4 rounded-md">
        <h3 class="font-bold">Debug Info</h3>
        <p>Template ID: {{ $template ? $template->id : 'New' }}</p>
        <p>Elements Count: {{ isset($templateData['elements']) ? count($templateData['elements']) : 0 }}</p>
        <p>Using placeholder format: <code>@{{ "{{participant_name}}" }}</code> (double curly braces)</p>
    </div>

    <div x-data="templateDesigner()" x-init="initialize()">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Step 1: Background Selection -->
            <div x-show="step === 1" class="bg-white overflow-hidden shadow-sm rounded-lg">
                <div class="p-6">
                    <h2 class="text-xl font-semibold text-gray-800 mb-4">Step 1: Choose Certificate Background</h2>
                    
                    <div class="mb-6">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Orientation</label>
                        <div class="flex gap-4">
                            <div class="flex items-center">
                                <input id="landscape" name="orientation" type="radio" value="landscape" class="h-4 w-4 text-primary-DEFAULT focus:ring-primary-DEFAULT" x-model="orientation">
                                <label for="landscape" class="ml-2 block text-sm font-medium text-gray-700">Landscape (297×210mm)</label>
                            </div>
                            <div class="flex items-center">
                                <input id="portrait" name="orientation" type="radio" value="portrait" class="h-4 w-4 text-primary-DEFAULT focus:ring-primary-DEFAULT" x-model="orientation">
                                <label for="portrait" class="ml-2 block text-sm font-medium text-gray-700">Portrait (210×297mm)</label>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-6">
                        <input type="file" accept=".pdf" class="hidden" id="pdf-upload" @change="handleBackgroundUpload($event)">
                        <label for="pdf-upload" class="inline-block px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-800 font-medium rounded-md cursor-pointer">
                            <span class="material-icons align-middle mr-1">upload</span>
                            Upload PDF Background
                        </label>
                    </div>
                    
                    <template x-if="selectedBackground">
                        <div class="mb-6 border border-gray-300 rounded-lg p-4">
                            <div class="text-center">
                                <h3 class="font-medium" x-text="`Selected: ${selectedBackground.name}`"></h3>
                                <div class="mt-4 relative mx-auto" style="width: 300px; height: 212px;">
                                    <iframe x-bind:src="selectedBackground.preview_image + '#toolbar=0&navpanes=0&scrollbar=0'" class="w-full h-full border"></iframe>
                                </div>
                                <button type="button" class="mt-4 px-4 py-2 bg-primary-DEFAULT hover:bg-primary-dark text-white font-medium rounded-md" @click="proceedToDesign">
                                    Proceed to Design Canvas
                                </button>
                            </div>
                        </div>
                    </template>
                </div>
            </div>
            
            <!-- Step 2: Design Canvas -->
            <div x-show="step === 2">
                <form id="template-form" action="{{ $template ? route('template.update', $template->id) : route('template.store') }}" method="POST" enctype="multipart/form-data" @submit.prevent="handleSubmit">
                    @csrf
                    @if($template)
                        @method('PUT')
                    @endif
                    
                    <!-- Toolbar -->
                    <div class="bg-white overflow-hidden shadow-sm rounded-lg mb-4 p-4">
                        <div class="flex flex-wrap items-center justify-between gap-4">
                            <div class="flex items-center space-x-4">
                                <div>
                                    <label for="name" class="block text-sm font-medium text-gray-700">Template Name</label>
                                    <input type="text" id="name" name="name" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-DEFAULT focus:ring focus:ring-primary-DEFAULT focus:ring-opacity-50" x-model="name" required>
                                </div>
                                
                                <div>
                                    <button type="button" class="bg-white hover:bg-gray-100 border border-gray-300 px-3 py-2 rounded-md text-sm font-medium flex items-center" @click="addTextElement">
                                        <span class="material-icons mr-1">text_fields</span>
                                        Add Text
                                    </button>
                                </div>
                                
                                <div>
                                    <button type="button" class="bg-white hover:bg-gray-100 border border-gray-300 px-3 py-2 rounded-md text-sm font-medium flex items-center" @click="addImageElement">
                                        <span class="material-icons mr-1">image</span>
                                        Add Image
                                    </button>
                                    <input type="file" id="image-upload" accept="image/*" class="hidden" @change="handleImageUpload">
                                </div>
                                
                                <div>
                                    <button type="button" class="bg-white hover:bg-gray-100 border border-gray-300 px-3 py-2 rounded-md text-sm font-medium flex items-center" @click="step = 1">
                                        <span class="material-icons mr-1">palette</span>
                                        Background
                                    </button>
                                </div>
                            </div>
                            
                            <div>
                                <button type="submit" class="bg-primary-DEFAULT hover:bg-primary-dark text-white px-4 py-2 rounded-md flex items-center">
                                    <span class="material-icons mr-1">save</span>
                                    {{ $template ? 'Update Template' : 'Save Template' }}
                                </button>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Element Properties -->
                    <div x-show="selectedElement !== null" class="bg-white overflow-hidden shadow-sm rounded-lg mb-4 p-4">
                        <div x-show="selectedElement && selectedElement.type === 'text'" class="space-y-4">
                            <div class="flex flex-wrap gap-4">
                                <div class="flex-1 min-w-[200px]">
                                    <label for="content" class="block text-sm font-medium text-gray-700">Text Content</label>
                                    <input type="text" id="content" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-DEFAULT focus:ring focus:ring-primary-DEFAULT focus:ring-opacity-50" x-model="selectedElement.content" @input="updateElement('content', $event.target.value)">
                                </div>
                                <div>
                                    <label for="fontFamily" class="block text-sm font-medium text-gray-700">Font Family</label>
                                    <select id="fontFamily" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-DEFAULT focus:ring focus:ring-primary-DEFAULT focus:ring-opacity-50" x-model="selectedElement.fontFamily" @change="updateElement('fontFamily', $event.target.value)">
                                        <option value="Arial">Arial</option>
                                        <option value="Times New Roman">Times New Roman</option>
                                        <option value="Courier New">Courier New</option>
                                        <option value="Georgia">Georgia</option>
                                        <option value="Tahoma">Tahoma</option>
                                        <option value="Verdana">Verdana</option>
                                    </select>
                                </div>
                                <div>
                                    <label for="fontSize" class="block text-sm font-medium text-gray-700">Font Size</label>
                                    <input type="number" id="fontSize" class="mt-1 block w-20 rounded-md border-gray-300 shadow-sm focus:border-primary-DEFAULT focus:ring focus:ring-primary-DEFAULT focus:ring-opacity-50" x-model="selectedElement.fontSize" @input="updateElement('fontSize', parseInt($event.target.value) || 12)">
                                </div>
                                <div>
                                    <label for="color" class="block text-sm font-medium text-gray-700">Text Color</label>
                                    <input type="color" id="color" class="mt-1 block rounded-md h-9 w-16 p-0" x-model="selectedElement.color" @input="updateElement('color', $event.target.value)">
                                </div>
                            </div>
                            
                            <div class="flex flex-wrap gap-2 items-center">
                                <button type="button" 
                                    class="px-2 py-1 rounded-md" 
                                    :class="selectedElement.fontWeight === 'bold' ? 'bg-primary-DEFAULT text-white' : 'bg-white border border-gray-300'"
                                    @click="updateElement('fontWeight', selectedElement.fontWeight === 'bold' ? 'normal' : 'bold')"
                                    title="Bold">
                                    <span class="material-icons">format_bold</span>
                                </button>
                                <button type="button" 
                                    class="px-2 py-1 rounded-md" 
                                    :class="selectedElement.fontStyle === 'italic' ? 'bg-primary-DEFAULT text-white' : 'bg-white border border-gray-300'"
                                    @click="updateElement('fontStyle', selectedElement.fontStyle === 'italic' ? 'normal' : 'italic')"
                                    title="Italic">
                                    <span class="material-icons">format_italic</span>
                                </button>
                                <button type="button" 
                                    class="px-2 py-1 rounded-md" 
                                    :class="selectedElement.textDecoration === 'underline' ? 'bg-primary-DEFAULT text-white' : 'bg-white border border-gray-300'"
                                    @click="updateElement('textDecoration', selectedElement.textDecoration === 'underline' ? 'none' : 'underline')"
                                    title="Underline">
                                    <span class="material-icons">format_underlined</span>
                                </button>
                                
                                <div class="mx-2 h-6 border-l border-gray-300"></div>
                                
                                <button type="button" 
                                    class="px-2 py-1 rounded-md" 
                                    :class="selectedElement.textAlign === 'left' ? 'bg-primary-DEFAULT text-white' : 'bg-white border border-gray-300'"
                                    @click="updateElement('textAlign', 'left')"
                                    title="Align Left">
                                    <span class="material-icons">format_align_left</span>
                                </button>
                                <button type="button" 
                                    class="px-2 py-1 rounded-md" 
                                    :class="selectedElement.textAlign === 'center' ? 'bg-primary-DEFAULT text-white' : 'bg-white border border-gray-300'"
                                    @click="updateElement('textAlign', 'center')"
                                    title="Align Center">
                                    <span class="material-icons">format_align_center</span>
                                </button>
                                <button type="button" 
                                    class="px-2 py-1 rounded-md" 
                                    :class="selectedElement.textAlign === 'right' ? 'bg-primary-DEFAULT text-white' : 'bg-white border border-gray-300'"
                                    @click="updateElement('textAlign', 'right')"
                                    title="Align Right">
                                    <span class="material-icons">format_align_right</span>
                                </button>
                                
                                <div class="mx-2 h-6 border-l border-gray-300"></div>
                                
                                <button type="button" class="px-2 py-1 bg-red-600 text-white rounded-md" @click="deleteElement" title="Delete">
                                    <span class="material-icons">delete</span>
                                </button>
                            </div>
                        </div>
                        
                        <div x-show="selectedElement && selectedElement.type === 'image'" class="space-y-4">
                            <div class="flex flex-wrap gap-4">
                                <div>
                                    <label for="width" class="block text-sm font-medium text-gray-700">Width</label>
                                    <input type="number" id="width" class="mt-1 block w-20 rounded-md border-gray-300 shadow-sm focus:border-primary-DEFAULT focus:ring focus:ring-primary-DEFAULT focus:ring-opacity-50" x-model="selectedElement.width" @input="updateElement('width', parseInt($event.target.value) || 20)">
                                </div>
                                <div>
                                    <label for="height" class="block text-sm font-medium text-gray-700">Height</label>
                                    <input type="number" id="height" class="mt-1 block w-20 rounded-md border-gray-300 shadow-sm focus:border-primary-DEFAULT focus:ring focus:ring-primary-DEFAULT focus:ring-opacity-50" x-model="selectedElement.height" @input="updateElement('height', parseInt($event.target.value) || 20)">
                                </div>
                                <div>
                                    <label for="x" class="block text-sm font-medium text-gray-700">X Position</label>
                                    <input type="number" id="x" class="mt-1 block w-20 rounded-md border-gray-300 shadow-sm focus:border-primary-DEFAULT focus:ring focus:ring-primary-DEFAULT focus:ring-opacity-50" x-model="Math.round(selectedElement.x || 0)" @input="updateElement('x', parseInt($event.target.value) || 0)">
                                </div>
                                <div>
                                    <label for="y" class="block text-sm font-medium text-gray-700">Y Position</label>
                                    <input type="number" id="y" class="mt-1 block w-20 rounded-md border-gray-300 shadow-sm focus:border-primary-DEFAULT focus:ring focus:ring-primary-DEFAULT focus:ring-opacity-50" x-model="Math.round(selectedElement.y || 0)" @input="updateElement('y', parseInt($event.target.value) || 0)">
                                </div>
                            </div>
                            
                            <div class="flex">
                                <button type="button" class="px-2 py-1 bg-red-600 text-white rounded-md" @click="deleteElement" title="Delete">
                                    <span class="material-icons">delete</span>
                                </button>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Design Canvas -->
                    <div class="bg-white overflow-hidden shadow-sm rounded-lg p-6">
                        <h2 class="text-xl font-semibold text-gray-800 mb-4">Design Canvas - Click to place elements</h2>
                        
                        <div id="design-canvas" class="mx-auto relative border-2 border-dashed border-blue-500 overflow-hidden bg-white"
                            :style="{
                                width: orientation === 'portrait' ? '420px' : '594px',
                                height: orientation === 'portrait' ? '594px' : '420px',
                                cursor: draggedElement ? 'crosshair' : 'default'
                            }"
                            @click="handleCanvasClick">
                            
                            <!-- Background PDF -->
                            <template x-if="selectedBackground">
                                <iframe x-bind:src="selectedBackground.preview_image + '#toolbar=0&navpanes=0&scrollbar=0&view=FitH'" class="absolute top-0 left-0 w-full h-full border-0 pointer-events-none"></iframe>
                            </template>
                            
                            <!-- Text Elements -->
                            <template x-for="element in templateData.elements" :key="element.id">
                                <template x-if="element.type === 'text'">
                                    <div class="absolute cursor-move"
                                        :style="{
                                            left: `${(element.x / templateData.width) * 100}%`,
                                            top: `${(element.y / templateData.height) * 100}%`,
                                            fontSize: `${element.fontSize}px`,
                                            fontFamily: element.fontFamily || 'Arial',
                                            fontWeight: element.fontWeight || 'normal',
                                            fontStyle: element.fontStyle || 'normal',
                                            textDecoration: element.textDecoration || 'none',
                                            color: element.color || '#000000',
                                            textAlign: element.textAlign || 'left',
                                            transform: element.textAlign === 'center' ? 'translateX(-50%)' : 'none',
                                            border: selectedElement && selectedElement.id === element.id ? '2px solid #ff9800' : '1px dashed transparent',
                                            padding: '4px',
                                            backgroundColor: selectedElement && selectedElement.id === element.id ? 'rgba(255,152,0,0.1)' : 'transparent',
                                            minWidth: '50px'
                                        }"
                                        @click="handleElementClick(element, $event)"
                                        @mousedown="handleElementDrag(element, $event)">
                                        <span x-text="element.content"></span>
                                    </div>
                                </template>
                                <template x-if="element.type === 'image'">
                                    <div class="absolute"
                                        :style="{
                                            left: `${(element.x / templateData.width) * 100}%`,
                                            top: `${(element.y / templateData.height) * 100}%`,
                                            width: `${(element.width / templateData.width) * 100}%`,
                                            height: `${(element.height / templateData.height) * 100}%`
                                        }">
                                        <img :src="element.src" class="w-full h-full cursor-move"
                                            :style="{
                                                border: selectedElement && selectedElement.id === element.id ? '2px solid #ff9800' : 'none'
                                            }"
                                            @click="handleElementClick(element, $event)"
                                            @mousedown="handleElementDrag(element, $event)">
                                    </div>
                                </template>
                            </template>
                        </div>
                    </div>
                    
                    <!-- Hidden Fields -->
                    <input type="hidden" name="orientation" x-model="orientation">
                    <input type="hidden" name="template_data" x-bind:value="JSON.stringify(templateData)">
                    <template x-if="selectedBackground && selectedBackground.pdf_file instanceof File">
                        <input type="file" name="pdf_file" id="pdf_file" class="hidden">
                    </template>
                    <template x-if="selectedBackground && !(selectedBackground.pdf_file instanceof File)">
                        <input type="hidden" name="background_pdf" x-model="selectedBackground.pdf_file">
                    </template>
                </form>
            </div>
        </div>
    </div>
    
    <script>
        function templateDesigner() {
            return {
                // Initialize with server-side data
                step: {{ $initialStep }},
                selectedBackground: @json($initialBackground),
                orientation: '{{ $initialOrientation }}',
                selectedElement: null,
                draggedElement: null,
                templateData: @json($templateData),
                name: '{{ $initialName }}',
                description: '{{ $initialDescription }}',
                isActive: {{ $initialIsActive ? 'true' : 'false' }},
                
                initialize() {
                    console.log('Template designer initialized');
                    if (this.step === 2) {
                        this.updateCanvasDimensions();
                    }
                },
                
                updateCanvasDimensions() {
                    if (this.orientation === 'portrait') {
                        this.templateData.width = 210;
                        this.templateData.height = 297;
                    } else {
                        this.templateData.width = 297;
                        this.templateData.height = 210;
                    }
                },
                
                proceedToDesign() {
                    if (!this.selectedBackground) {
                        alert('Please select a background first.');
                        return;
                    }
                    
                    this.updateCanvasDimensions();
                    this.step = 2;
                },
                
                handleCanvasClick(e) {
                    if (!this.draggedElement) return;
                    
                    const canvas = document.getElementById('design-canvas');
                    const rect = canvas.getBoundingClientRect();
                    const x = ((e.clientX - rect.left) / rect.width) * this.templateData.width;
                    const y = ((e.clientY - rect.top) / rect.height) * this.templateData.height;
                    
                    const newElement = {
                        ...this.draggedElement,
                        x: Math.round(x),
                        y: Math.round(y),
                        id: Date.now()
                    };
                    
                    this.templateData.elements.push(newElement);
                    this.draggedElement = null;
                },
                
                handleElementClick(element, e) {
                    e.stopPropagation();
                    this.selectedElement = element;
                },
                
                handleElementDrag(element, e) {
                    e.preventDefault();
                    e.stopPropagation();
                    
                    const canvas = document.getElementById('design-canvas');
                    const rect = canvas.getBoundingClientRect();
                    const startX = e.clientX;
                    const startY = e.clientY;
                    const startElementX = element.x;
                    const startElementY = element.y;
                    
                    const handleMouseMove = (moveEvent) => {
                        moveEvent.preventDefault();
                        const deltaX = moveEvent.clientX - startX;
                        const deltaY = moveEvent.clientY - startY;
                        const newX = startElementX + (deltaX / rect.width) * this.templateData.width;
                        const newY = startElementY + (deltaY / rect.height) * this.templateData.height;
                        
                        // Update element in template data
                        this.templateData.elements = this.templateData.elements.map(el => 
                            el.id === element.id ? { 
                                ...el, 
                                x: Math.max(0, Math.min(this.templateData.width - (el.width || 50), newX)),
                                y: Math.max(0, Math.min(this.templateData.height - (el.height || 20), newY))
                            } : el
                        );
                        
                        // Update selected element
                        if (this.selectedElement && this.selectedElement.id === element.id) {
                            this.selectedElement = { 
                                ...this.selectedElement, 
                                x: Math.max(0, Math.min(this.templateData.width - (element.width || 50), newX)),
                                y: Math.max(0, Math.min(this.templateData.height - (element.height || 20), newY))
                            };
                        }
                    };
                    
                    const handleMouseUp = () => {
                        document.removeEventListener('mousemove', handleMouseMove);
                        document.removeEventListener('mouseup', handleMouseUp);
                    };
                    
                    document.addEventListener('mousemove', handleMouseMove);
                    document.addEventListener('mouseup', handleMouseUp);
                },
                
                updateElement(field, value) {
                    if (!this.selectedElement) return;
                    
                    // Update element in template data
                    this.templateData.elements = this.templateData.elements.map(el => 
                        el.id === this.selectedElement.id ? { ...el, [field]: value } : el
                    );
                    
                    // Update selected element
                    this.selectedElement = { ...this.selectedElement, [field]: value };
                },
                
                addTextElement() {
                    // Using PHP variable to avoid parsing issues
                    this.draggedElement = {
                        type: 'text',
                        content: '{{ $placeholderFormat }}',
                        fontSize: 24,
                        fontFamily: 'Times New Roman',
                        fontWeight: 'bold',
                        fontStyle: 'normal',
                        textDecoration: 'none',
                        color: '#000000',
                        textAlign: 'center'
                    };
                },
                
                addImageElement() {
                    document.getElementById('image-upload').click();
                },
                
                handleImageUpload(e) {
                    const file = e.target.files[0];
                    if (file && file.type.startsWith('image/')) {
                        const imageUrl = URL.createObjectURL(file);
                        const img = new Image();
                        img.onload = () => {
                            // Scale down if too large for canvas
                            const maxSize = 300;
                            let width = img.naturalWidth;
                            let height = img.naturalHeight;
                            
                            if (width > maxSize || height > maxSize) {
                                const ratio = Math.min(maxSize / width, maxSize / height);
                                width = width * ratio;
                                height = height * ratio;
                            }
                            
                            this.draggedElement = {
                                type: 'image',
                                src: imageUrl,
                                width: width,
                                height: height,
                                file: file,
                                id: Date.now()
                            };
                        };
                        img.src = imageUrl;
                    }
                },
                
                deleteElement() {
                    if (!this.selectedElement) return;
                    
                    this.templateData.elements = this.templateData.elements.filter(el => 
                        el.id !== this.selectedElement.id
                    );
                    
                    this.selectedElement = null;
                },
                
                handleBackgroundUpload(e) {
                    const file = e.target.files[0];
                    if (file && file.type === 'application/pdf') {
                        const previewUrl = URL.createObjectURL(file);
                        this.selectedBackground = {
                            name: file.name,
                            preview_image: previewUrl,
                            pdf_file: file
                        };
                    } else {
                        alert('Please select a valid PDF file.');
                    }
                },
                
                handleSubmit() {
                    if (!this.name) {
                        alert('Please enter template name');
                        return;
                    }
                    
                    // Update template_data input field
                    document.querySelector('input[name="template_data"]').value = JSON.stringify(this.templateData);
                    
                    // Submit form directly
                    document.getElementById('template-form').submit();
                }
            };
        }
        
        // File input handler for PDF upload
        document.addEventListener('DOMContentLoaded', function() {
            const pdfInput = document.getElementById('pdf_file');
            const pdfUploadInput = document.getElementById('pdf-upload');
            
            if (pdfInput && pdfUploadInput) {
                pdfUploadInput.addEventListener('change', function() {
                    if (this.files.length > 0) {
                        pdfInput.files = this.files;
                    }
                });
            }
        });
    </script>
</x-app-layout> 