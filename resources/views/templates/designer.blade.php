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

    <div x-data="templateDesigner()" x-init="initialize()">
        <div class="bg-white rounded shadow-md border border-gray-300">
            <div class="p-6 border-b border-gray-200">
                <div class="flex justify-between items-start">
                    <div>
                        <div class="flex items-center">
                            <span class="material-icons mr-2 text-primary-DEFAULT">design_services</span>
                            <h1 class="text-xl font-bold text-gray-800">{{ $template ? 'Edit Template Design' : 'Create Template Design' }}</h1>
                        </div>
                        <p class="text-xs text-gray-500 mt-1 ml-8">{{ $template ? 'Customize the layout and elements of ' . $template->name : 'Create and position elements for your certificate' }}</p>
                    </div>
                    <div class="flex gap-2">
                        <a href="{{ route('template.designer') }}" class="bg-white border border-gray-300 hover:bg-gray-50 text-gray-700 px-3 py-1 rounded shadow-sm font-medium flex items-center text-xs transition-colors duration-200 ease-in-out">
                            <span class="material-icons text-xs mr-1">arrow_back</span>
                            Back to Templates
                        </a>
                    </div>
                </div>
            </div>
            
            <div class="p-6">
                <!-- Step 1: Background Selection -->
                <div x-show="step === 1" class="border rounded-lg bg-white p-6 shadow-sm">
                    <h2 class="text-sm font-semibold text-gray-700 mb-4 flex items-center">
                        <span class="material-icons text-primary-DEFAULT text-base mr-1">palette</span>
                        Step 1: Choose Certificate Background
                    </h2>
                    
                    <div class="mb-6 ml-6">
                        <label class="block text-xs font-medium text-gray-700 mb-1 flex items-center">
                            <span class="material-icons text-primary-DEFAULT text-base mr-1">crop_landscape</span>
                            Orientation
                        </label>
                        <div class="flex gap-4 mt-2">
                            <label class="flex items-center">
                                <input id="landscape" name="orientation" type="radio" value="landscape" class="h-4 w-4 text-primary-DEFAULT focus:ring-primary-DEFAULT" x-model="orientation">
                                <span class="ml-2 text-sm">Landscape (297×210mm)</span>
                            </label>
                            <label class="flex items-center">
                                <input id="portrait" name="orientation" type="radio" value="portrait" class="h-4 w-4 text-primary-DEFAULT focus:ring-primary-DEFAULT" x-model="orientation">
                                <span class="ml-2 text-sm">Portrait (210×297mm)</span>
                            </label>
                        </div>
                    </div>
                    
                    <div class="mb-6 ml-6">
                        <label class="block text-xs font-medium text-gray-700 mb-1 flex items-center">
                            <span class="material-icons text-primary-DEFAULT text-base mr-1">picture_as_pdf</span>
                            PDF Background
                        </label>
                        <input type="file" accept=".pdf" class="hidden" id="pdf-upload" @change="handleBackgroundUpload($event)">
                        <label for="pdf-upload" class="inline-flex px-3 py-1 bg-gradient-to-r from-blue-600 to-blue-500 hover:from-blue-700 hover:to-blue-600 text-white rounded shadow-sm font-medium items-center text-xs transition-colors duration-200 ease-in-out cursor-pointer">
                            <span class="material-icons text-xs mr-1">upload_file</span>
                            Upload PDF Background
                        </label>
                        <p class="text-xs text-gray-500 mt-1">Select a PDF file to use as the certificate background</p>
                    </div>
                    
                    <template x-if="selectedBackground">
                        <div class="mb-6 border border-gray-300 rounded-lg p-4 bg-gray-50 ml-6">
                            <div class="text-center">
                                <h3 class="text-sm font-medium" x-text="`Selected: ${selectedBackground.name}`"></h3>
                                <div class="mt-4 relative mx-auto border border-gray-200 shadow-sm" style="width: 300px; height: 212px;">
                                    <iframe x-bind:src="selectedBackground.preview_image + '#toolbar=0&navpanes=0&scrollbar=0'" class="w-full h-full"></iframe>
                                </div>
                                <button type="button" class="mt-4 px-3 py-1 bg-gradient-to-r from-blue-600 to-blue-500 hover:from-blue-700 hover:to-blue-600 text-white rounded shadow-sm font-medium flex items-center text-xs transition-colors duration-200 ease-in-out mx-auto" @click="proceedToDesign">
                                    <span class="material-icons text-xs mr-1">navigate_next</span>
                                    Proceed to Design Canvas
                                </button>
                            </div>
                        </div>
                    </template>
                </div>
                
                <!-- Step 2: Design Canvas -->
                <div x-show="step === 2">
                    <form id="template-form" action="{{ $template ? route('template.update', $template->id) : route('template.store') }}" method="POST" enctype="multipart/form-data" @submit.prevent="handleSubmit">
                        @csrf
                        @if($template)
                            @method('PUT')
                        @endif
                        
                        <!-- Toolbar -->
                        <div class="border rounded-lg bg-white p-4 shadow-sm mb-4">
                            <div class="flex flex-wrap items-center justify-between gap-4">
                                <div class="flex items-center flex-wrap gap-3">
                                    <div>
                                        <label for="name" class="block text-xs font-medium text-gray-700 mb-1 flex items-center">
                                            <span class="material-icons text-primary-DEFAULT text-base mr-1">title</span>
                                            Template Name
                                        </label>
                                        <input type="text" id="name" name="name" class="w-full border border-gray-300 rounded px-3 py-1 text-sm focus:ring focus:ring-primary-light focus:border-primary-light" x-model="name" required>
                                    </div>
                                    
                                    <div>
                                        <button type="button" class="px-3 py-1 bg-white border border-gray-300 hover:bg-gray-50 text-gray-700 rounded shadow-sm font-medium flex items-center text-xs transition-colors duration-200 ease-in-out mt-5" @click="addTextElement">
                                            <span class="material-icons text-xs mr-1">text_fields</span>
                                            Add Text
                                        </button>
                                    </div>
                                    
                                    <div>
                                        <button type="button" class="px-3 py-1 bg-white border border-gray-300 hover:bg-gray-50 text-gray-700 rounded shadow-sm font-medium flex items-center text-xs transition-colors duration-200 ease-in-out mt-5" @click="addImageElement">
                                            <span class="material-icons text-xs mr-1">image</span>
                                            Add Image
                                        </button>
                                        <input type="file" id="image-upload" accept="image/*" class="hidden" @change="handleImageUpload">
                                    </div>
                                    
                                    <div>
                                        <button type="button" class="px-3 py-1 bg-white border border-gray-300 hover:bg-gray-50 text-gray-700 rounded shadow-sm font-medium flex items-center text-xs transition-colors duration-200 ease-in-out mt-5" @click="step = 1">
                                            <span class="material-icons text-xs mr-1">palette</span>
                                            Background
                                        </button>
                                    </div>
                                </div>
                                
                                <div>
                                    <button type="submit" class="px-3 py-1 bg-gradient-to-r from-blue-600 to-blue-500 hover:from-blue-700 hover:to-blue-600 text-white rounded shadow-sm font-medium flex items-center text-xs transition-colors duration-200 ease-in-out">
                                        <span class="material-icons text-xs mr-1">save</span>
                                        {{ $template ? 'Update Template' : 'Save Template' }}
                                    </button>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Design Canvas -->
                        <div class="border rounded-lg bg-white p-6 shadow-sm relative">
                            <h3 class="text-xs font-medium text-gray-700 mb-3 flex items-center">
                                <span class="material-icons text-primary-DEFAULT text-base mr-1">design_services</span>
                                Design Canvas
                            </h3>
                            
                            <p class="text-xs text-gray-500 mb-4 ml-6">Click to place elements on the canvas. Drag elements to reposition them.</p>
                            
                            <div class="flex flex-col lg:flex-row gap-4">
                                <!-- Certificate Preview Canvas -->
                                <div class="lg:w-2/3">
                                    <div id="design-canvas" class="mx-auto relative border border-gray-300 overflow-hidden bg-white shadow-md"
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
                                                        border: selectedElement && selectedElement.id === element.id ? '2px solid #2563eb' : '1px dashed transparent',
                                                        padding: '4px',
                                                        backgroundColor: selectedElement && selectedElement.id === element.id ? 'rgba(37, 99, 235, 0.1)' : 'transparent',
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
                                                            border: selectedElement && selectedElement.id === element.id ? '2px solid #2563eb' : 'none'
                                                        }"
                                                        @click="handleElementClick(element, $event)"
                                                        @mousedown="handleElementDrag(element, $event)">
                                                </div>
                                            </template>
                                        </template>
                                    </div>
                                    
                                    <div class="mt-4 text-center">
                                        <p class="text-xs text-gray-500">Add text elements with placeholder tags like <code>@{{participant_name}}</code> to be replaced with actual data.</p>
                                    </div>
                                </div>
                                
                                <!-- Floating Element Properties Panel -->
                                <div class="lg:w-1/3">
                                    <!-- Text Element Properties -->
                                    <div x-show="selectedElement !== null" class="border rounded-lg bg-white p-4 shadow-md">
                                        <h3 class="text-xs font-medium text-gray-700 mb-3 bg-primary-light text-white p-2 rounded flex items-center">
                                            <span class="material-icons text-base mr-1">tune</span>
                                            Element Properties
                                        </h3>
                                        
                                        <!-- Text Element Properties -->
                                        <div x-show="selectedElement && selectedElement.type === 'text'">
                                            <!-- Content -->
                                            <div class="mb-3">
                                                <label class="block text-xs font-medium text-gray-700 mb-1">Text Content</label>
                                                <input type="text" class="w-full border border-gray-300 rounded px-3 py-2 text-sm" x-model="selectedElement.content" @input="updateElement('content', $event.target.value)">
                                            </div>
                                            
                                            <!-- Font Options -->
                                            <div class="grid grid-cols-2 gap-3 mb-3">
                                                <div>
                                                    <label class="block text-xs font-medium text-gray-700 mb-1">Font Family</label>
                                                    <select class="w-full border border-gray-300 rounded px-3 py-2 text-sm bg-white" x-model="selectedElement.fontFamily" @change="updateElement('fontFamily', $event.target.value)">
                                                        <option value="Arial">Arial</option>
                                                        <option value="Times New Roman">Times New Roman</option>
                                                        <option value="Courier New">Courier New</option>
                                                        <option value="Georgia">Georgia</option>
                                                        <option value="Tahoma">Tahoma</option>
                                                        <option value="Verdana">Verdana</option>
                                                    </select>
                                                </div>
                                                <div>
                                                    <label class="block text-xs font-medium text-gray-700 mb-1">Font Size</label>
                                                    <div class="flex items-center">
                                                        <input type="number" class="w-full border border-gray-300 rounded px-3 py-2 text-sm" x-model="selectedElement.fontSize" @input="updateElement('fontSize', parseInt($event.target.value) || 12)">
                                                        <span class="ml-1 text-xs text-gray-500">px</span>
                                                    </div>
                                                </div>
                                            </div>
                                            
                                            <!-- Color -->
                                            <div class="mb-3">
                                                <label class="block text-xs font-medium text-gray-700 mb-1">Text Color</label>
                                                <div class="flex items-center">
                                                    <input type="color" class="h-8 w-16 border border-gray-300 rounded" x-model="selectedElement.color" @input="updateElement('color', $event.target.value)">
                                                    <span class="ml-2 text-xs" x-text="selectedElement.color"></span>
                                                </div>
                                            </div>
                                            
                                            <!-- Text Formatting -->
                                            <div class="mb-3 border-t border-gray-200 pt-3">
                                                <label class="block text-xs font-medium text-gray-700 mb-1">Text Formatting</label>
                                                <div class="flex flex-wrap gap-2">
                                                    <button type="button" 
                                                        class="p-1.5 rounded"
                                                        :class="selectedElement.fontWeight === 'bold' ? 'bg-primary-DEFAULT text-white' : 'bg-white border border-gray-300'"
                                                        @click="updateElement('fontWeight', selectedElement.fontWeight === 'bold' ? 'normal' : 'bold')">
                                                        <span class="material-icons text-xs">format_bold</span>
                                                    </button>
                                                    
                                                    <button type="button" 
                                                        class="p-1.5 rounded"
                                                        :class="selectedElement.fontStyle === 'italic' ? 'bg-primary-DEFAULT text-white' : 'bg-white border border-gray-300'"
                                                        @click="updateElement('fontStyle', selectedElement.fontStyle === 'italic' ? 'normal' : 'italic')">
                                                        <span class="material-icons text-xs">format_italic</span>
                                                    </button>
                                                    
                                                    <button type="button" 
                                                        class="p-1.5 rounded"
                                                        :class="selectedElement.textDecoration === 'underline' ? 'bg-primary-DEFAULT text-white' : 'bg-white border border-gray-300'"
                                                        @click="updateElement('textDecoration', selectedElement.textDecoration === 'underline' ? 'none' : 'underline')">
                                                        <span class="material-icons text-xs">format_underlined</span>
                                                    </button>
                                                    
                                                    <div class="mx-1 h-6 border-l border-gray-300"></div>
                                                    
                                                    <button type="button" 
                                                        class="p-1.5 rounded"
                                                        :class="selectedElement.textAlign === 'left' ? 'bg-primary-DEFAULT text-white' : 'bg-white border border-gray-300'"
                                                        @click="updateElement('textAlign', 'left')">
                                                        <span class="material-icons text-xs">format_align_left</span>
                                                    </button>
                                                    
                                                    <button type="button" 
                                                        class="p-1.5 rounded"
                                                        :class="selectedElement.textAlign === 'center' ? 'bg-primary-DEFAULT text-white' : 'bg-white border border-gray-300'"
                                                        @click="updateElement('textAlign', 'center')">
                                                        <span class="material-icons text-xs">format_align_center</span>
                                                    </button>
                                                    
                                                    <button type="button" 
                                                        class="p-1.5 rounded"
                                                        :class="selectedElement.textAlign === 'right' ? 'bg-primary-DEFAULT text-white' : 'bg-white border border-gray-300'"
                                                        @click="updateElement('textAlign', 'right')">
                                                        <span class="material-icons text-xs">format_align_right</span>
                                                    </button>
                                                </div>
                                            </div>
                                            
                                            <!-- Position -->
                                            <div class="mb-3 border-t border-gray-200 pt-3">
                                                <label class="block text-xs font-medium text-gray-700 mb-1">Element Position</label>
                                                <div class="grid grid-cols-2 gap-3">
                                                    <div>
                                                        <label class="block text-xs text-gray-500 mb-1">X Position</label>
                                                        <input type="number" class="w-full border border-gray-300 rounded px-3 py-1 text-sm" x-model="Math.round(selectedElement.x || 0)" @input="updateElement('x', parseInt($event.target.value) || 0)">
                                                    </div>
                                                    <div>
                                                        <label class="block text-xs text-gray-500 mb-1">Y Position</label>
                                                        <input type="number" class="w-full border border-gray-300 rounded px-3 py-1 text-sm" x-model="Math.round(selectedElement.y || 0)" @input="updateElement('y', parseInt($event.target.value) || 0)">
                                                    </div>
                                                </div>
                                            </div>
                                            
                                            <!-- Delete Button -->
                                            <div class="mt-4">
                                                <button type="button" class="w-full px-3 py-2 bg-red-600 hover:bg-red-700 text-white rounded text-xs font-medium flex items-center justify-center" @click="deleteElement">
                                                    <span class="material-icons text-xs mr-1">delete</span>
                                                    Delete Element
                                                </button>
                                            </div>
                                        </div>
                                        
                                        <!-- Image Element Properties -->
                                        <div x-show="selectedElement && selectedElement.type === 'image'">
                                            <!-- Dimensions -->
                                            <div class="mb-3">
                                                <label class="block text-xs font-medium text-gray-700 mb-1">Image Dimensions</label>
                                                <div class="grid grid-cols-2 gap-3">
                                                    <div>
                                                        <label class="block text-xs text-gray-500 mb-1">Width</label>
                                                        <div class="flex items-center">
                                                            <input type="number" class="w-full border border-gray-300 rounded px-3 py-1 text-sm" x-model="selectedElement.width" @input="updateElement('width', parseInt($event.target.value) || 20)">
                                                            <span class="ml-1 text-xs text-gray-500">px</span>
                                                        </div>
                                                    </div>
                                                    <div>
                                                        <label class="block text-xs text-gray-500 mb-1">Height</label>
                                                        <div class="flex items-center">
                                                            <input type="number" class="w-full border border-gray-300 rounded px-3 py-1 text-sm" x-model="selectedElement.height" @input="updateElement('height', parseInt($event.target.value) || 20)">
                                                            <span class="ml-1 text-xs text-gray-500">px</span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            
                                            <!-- Position -->
                                            <div class="mb-3 border-t border-gray-200 pt-3">
                                                <label class="block text-xs font-medium text-gray-700 mb-1">Image Position</label>
                                                <div class="grid grid-cols-2 gap-3">
                                                    <div>
                                                        <label class="block text-xs text-gray-500 mb-1">X Position</label>
                                                        <input type="number" class="w-full border border-gray-300 rounded px-3 py-1 text-sm" x-model="Math.round(selectedElement.x || 0)" @input="updateElement('x', parseInt($event.target.value) || 0)">
                                                    </div>
                                                    <div>
                                                        <label class="block text-xs text-gray-500 mb-1">Y Position</label>
                                                        <input type="number" class="w-full border border-gray-300 rounded px-3 py-1 text-sm" x-model="Math.round(selectedElement.y || 0)" @input="updateElement('y', parseInt($event.target.value) || 0)">
                                                    </div>
                                                </div>
                                            </div>
                                            
                                            <!-- Delete Button -->
                                            <div class="mt-4">
                                                <button type="button" class="w-full px-3 py-2 bg-red-600 hover:bg-red-700 text-white rounded text-xs font-medium flex items-center justify-center" @click="deleteElement">
                                                    <span class="material-icons text-xs mr-1">delete</span>
                                                    Delete Image
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <!-- Add Elements Panel when no element is selected -->
                                    <div x-show="!selectedElement" class="border rounded-lg bg-white p-4 shadow-md">
                                        <div class="text-center p-4">
                                            <span class="material-icons text-primary-DEFAULT text-2xl">add_circle</span>
                                            <p class="text-sm font-medium text-gray-700 mt-2">Add Elements</p>
                                            <p class="text-xs text-gray-500 mb-4">Add text or images to your certificate</p>
                                            
                                            <div class="flex justify-center gap-2">
                                                <button type="button" class="px-3 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded text-xs font-medium flex items-center" @click="addTextElement">
                                                    <span class="material-icons text-xs mr-1">text_fields</span>
                                                    Add Text
                                                </button>
                                                <button type="button" class="px-3 py-2 bg-purple-600 hover:bg-purple-700 text-white rounded text-xs font-medium flex items-center" @click="addImageElement">
                                                    <span class="material-icons text-xs mr-1">image</span>
                                                    Add Image
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
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