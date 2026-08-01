<x-app-layout>
    <x-slot name="breadcrumb">
        <span>Certificate</span>
        <span class="mx-2 text-gray-500">/</span>
        <span><a href="{{ route('template.designer') }}" class="text-primary-DEFAULT hover:underline">Template Designer</a></span>
        <span class="mx-2 text-gray-500">/</span>
        <span>Edit Template</span>
    </x-slot>

    <x-slot name="title">Edit Template</x-slot>

    <style>
        .tooltip-wrapper { position: relative; display: inline-flex; }
        .tooltip-content {
            position: absolute; bottom: 100%; left: 50%;
            transform: translateX(-50%) translateY(-4px);
            background-color: #1f2937; color: white;
            padding: 6px 10px; border-radius: 6px;
            font-size: 11px; white-space: nowrap;
            z-index: 1000; pointer-events: none;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }
        .tooltip-content::after {
            content: ''; position: absolute;
            top: 100%; left: 50%; transform: translateX(-50%);
            border: 4px solid transparent; border-top-color: #1f2937;
        }
    </style>

    <div class="bg-white rounded shadow-md border border-gray-300">
        <div class="p-6 border-b border-gray-200">
            <div class="flex items-center">
                <span class="material-icons-outlined mr-2 text-primary-DEFAULT">edit</span>
                <h1 class="text-xl font-bold text-gray-800">Edit Template</h1>
            </div>
            <p class="text-xs text-gray-500 mt-1 ml-8">Update template information</p>
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
            <form action="{{ route('template.update', $template->id) }}" method="POST" enctype="multipart/form-data" class="space-y-2">
                @csrf
                @method('PUT')
                
                <!-- Basic Information -->
                <div class="bg-white border border-gray-200 rounded-md shadow-sm">
                    <div class="bg-gray-50 border-b border-gray-200 px-4 py-3">
                        <h2 class="text-sm font-semibold text-gray-700 flex items-center">
                            <span class="material-icons-outlined text-primary-DEFAULT mr-2">description</span>
                            Basic Information
                        </h2>
                    </div>
                    
                    <div class="p-4 space-y-4">
                        <!-- Template Name -->
                        <div class="flex flex-col md:flex-row md:items-start gap-3">
                            <label for="name" class="text-xs font-medium text-gray-700 md:w-48 shrink-0 md:pt-2">
                                Template name <span class="text-red-500">*</span>
                            </label>
                            <div class="flex-1">
                                <input type="text" name="name" id="name" value="{{ old('name', $template->name) }}"
                                       class="w-full h-9 text-xs border-gray-300 rounded px-3 focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50" required>
                                <p class="text-xs text-gray-500 mt-1">
                                    This is the name you will pick from when generating certificates.
                                </p>
                                @error('name')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                        
                        <!-- Description -->
                        <div class="flex flex-col md:flex-row md:items-start gap-3">
                            <label for="description" class="text-xs font-medium text-gray-700 md:w-48 shrink-0 md:pt-2">
                                Description
                            </label>
                            <div class="flex-1">
                                <textarea name="description" id="description" rows="3"
                                          class="w-full text-xs border-gray-300 rounded px-3 py-2 focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50">{{ old('description', $template->description) }}</textarea>
                                <p class="text-xs text-gray-500 mt-1">Optional. Helps you tell similar templates apart.</p>
                                @error('description')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        {{-- The placed text lives in template_data, which this screen does
                             not touch. Editing here only changes the details above. --}}
                        <div class="flex flex-col md:flex-row md:items-start gap-3">
                            <span class="text-xs font-medium text-gray-700 md:w-48 shrink-0 md:pt-1">Certificate layout</span>
                            <div class="flex-1">
                                <a href="{{ route('template.designer.create', $template->id) }}"
                                   class="h-9 px-3 border border-gray-300 rounded text-xs text-gray-700 hover:bg-gray-50 inline-flex items-center">
                                    <span class="material-icons-outlined text-xs mr-1">design_services</span>
                                    Open in designer
                                </a>
                                <p class="text-xs text-gray-500 mt-1">
                                    Text positions are edited in the designer. Saving this form leaves them untouched.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Template Files -->
                <div class="bg-white border border-gray-200 rounded-md shadow-sm">
                    <div class="bg-gray-50 border-b border-gray-200 px-4 py-3">
                        <h2 class="text-sm font-semibold text-gray-700 flex items-center">
                            <span class="material-icons-outlined text-primary-DEFAULT mr-2">cloud_upload</span>
                            Template Files
                        </h2>
                    </div>
                    
                    <div class="p-4">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <div class="mb-4">
                                    <label for="preview_image" class="flex items-center text-xs font-medium text-gray-700 mb-1">
                                        <span class="material-icons-outlined text-sm mr-1 text-primary-DEFAULT">image</span>
                                        Preview Image
                                    </label>
                                    <div class="border-2 border-dashed border-gray-300 rounded-lg p-6 text-center mb-2">
                                        <div class="mb-3">
                                            @if($template->preview_image)
                                                <img src="{{ asset('storage/' . $template->preview_image) }}" alt="{{ $template->name }}" class="h-32 mx-auto mb-2">
                                            @else
                                                <span class="material-icons-outlined text-gray-400 text-3xl">cloud_upload</span>
                                            @endif
                                        </div>
                                        <p class="text-xs text-gray-500 mb-2">Drag and drop your image here, or click to browse</p>
                                        <p class="text-xs text-gray-400">PNG, JPG or GIF, up to 2 MB</p>
                                        <input type="file" name="preview_image" id="preview_image" class="hidden" accept="image/*">
                                        <button type="button" onclick="document.getElementById('preview_image').click()" class="mt-3 bg-gray-100 hover:bg-gray-200 text-gray-700 px-4 py-2 rounded text-xs">Select File</button>
                                        <div id="preview-container" class="hidden mt-4">
                                            <img id="image-preview" src="#" alt="Preview" class="max-h-32 mx-auto">
                                        </div>
                                    </div>
                                    @error('preview_image')
                                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                            <div>
                                <div class="mb-4">
                                    <label for="pdf_file" class="flex items-center text-xs font-medium text-gray-700 mb-1">
                                        <span class="material-icons-outlined text-sm mr-1 text-primary-DEFAULT">picture_as_pdf</span>
                                        PDF File
                                    </label>
                                    <div class="border-2 border-dashed border-gray-300 rounded-lg p-6 text-center mb-2">
                                        <div class="mb-3">
                                            @if($template->pdf_file)
                                                <span class="material-icons-outlined text-primary-DEFAULT text-3xl">picture_as_pdf</span>
                                                <p class="text-xs text-primary-DEFAULT mt-1">{{ basename($template->pdf_file) }}</p>
                                            @else
                                                <span class="material-icons-outlined text-gray-400 text-3xl">picture_as_pdf</span>
                                            @endif
                                        </div>
                                        <p class="text-xs text-gray-500 mb-2">Upload your certificate PDF background here</p>
                                        <p class="text-xs text-gray-400">PDF only, up to 10 MB. Leave empty to keep the current file.</p>
                                        <input type="file" name="pdf_file" id="pdf_file" class="hidden" accept="application/pdf">
                                        <button type="button" onclick="document.getElementById('pdf_file').click()" class="mt-3 bg-gray-100 hover:bg-gray-200 text-gray-700 px-4 py-2 rounded text-xs">Select PDF</button>
                                        <div id="pdf-preview-container" class="hidden mt-4">
                                            <span id="pdf-file-name" class="text-xs text-gray-700"></span>
                                        </div>
                                    </div>
                                    @error('pdf_file')
                                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Orientation -->
                <div class="bg-white border border-gray-200 rounded-md shadow-sm">
                    <div class="bg-gray-50 border-b border-gray-200 px-4 py-3">
                        <h2 class="text-sm font-semibold text-gray-700 flex items-center">
                            <span class="material-icons-outlined text-primary-DEFAULT mr-2">crop_portrait</span>
                            Orientation
                        </h2>
                    </div>
                    
                    <div class="p-4">
                        <div class="flex flex-col md:flex-row md:items-start gap-3"
                             x-data="{ orientation: '{{ old('orientation', $template->orientation) }}' }">
                            <label class="text-xs font-medium text-gray-700 md:w-48 shrink-0 md:pt-1">
                                Page orientation <span class="text-red-500">*</span>
                            </label>
                            <div class="flex-1">
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                    <label class="cursor-pointer border rounded p-3 flex gap-3 transition-colors duration-150"
                                           :class="orientation === 'landscape' ? 'border-primary-DEFAULT bg-blue-50' : 'border-gray-200 hover:border-gray-300'">
                                        <input type="radio" name="orientation" value="landscape" x-model="orientation"
                                               class="mt-0.5 shrink-0 text-primary-DEFAULT" required>
                                        <span>
                                            <span class="block text-xs font-medium text-gray-800">Landscape</span>
                                            <span class="block text-xs text-gray-500 mt-1">297 &times; 210 mm (A4 sideways)</span>
                                        </span>
                                    </label>
                                    <label class="cursor-pointer border rounded p-3 flex gap-3 transition-colors duration-150"
                                           :class="orientation === 'portrait' ? 'border-primary-DEFAULT bg-blue-50' : 'border-gray-200 hover:border-gray-300'">
                                        <input type="radio" name="orientation" value="portrait" x-model="orientation"
                                               class="mt-0.5 shrink-0 text-primary-DEFAULT">
                                        <span>
                                            <span class="block text-xs font-medium text-gray-800">Portrait</span>
                                            <span class="block text-xs text-gray-500 mt-1">210 &times; 297 mm (A4 upright)</span>
                                        </span>
                                    </label>
                                </div>
                                <p class="text-xs text-gray-500 mt-1">
                                    Changing this resizes the design canvas, so check the layout in the designer afterwards.
                                </p>
                                @error('orientation')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="flex justify-end space-x-3 pt-4">
                    <a href="{{ route('template.designer') }}" class="px-3 h-[36px] bg-gradient-to-r from-red-600 to-red-500 hover:from-red-700 hover:to-red-600 text-white rounded shadow-sm text-xs font-medium transition-colors duration-200 ease-in-out flex items-center">
                        <span class="material-icons-outlined text-xs mr-1">cancel</span>
                        Cancel
                    </a>
                    <button type="submit" class="px-3 h-[36px] bg-gradient-to-r from-green-600 to-green-500 hover:from-green-700 hover:to-green-600 text-white rounded shadow-sm text-xs font-medium transition-colors duration-200 ease-in-out flex items-center">
                        <span class="material-icons-outlined text-xs mr-1">save</span>
                        Update Template
                    </button>
                </div>
            </form>
        </div>
    </div>
    <script>
        // Image preview
        document.getElementById('preview_image').addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    document.getElementById('image-preview').src = e.target.result;
                    document.getElementById('preview-container').classList.remove('hidden');
                }
                reader.readAsDataURL(file);
            }
        });
        // PDF preview
        document.getElementById('pdf_file').addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                document.getElementById('pdf-file-name').textContent = file.name;
                document.getElementById('pdf-preview-container').classList.remove('hidden');
            }
        });
    </script>
</x-app-layout> 