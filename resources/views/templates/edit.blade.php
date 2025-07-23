<x-app-layout>
    <x-slot name="breadcrumb">
        <span>Certificate</span>
        <span class="mx-2 text-gray-500">/</span>
        <span><a href="{{ route('template.designer') }}" class="text-primary-DEFAULT hover:underline">Template Designer</a></span>
        <span class="mx-2 text-gray-500">/</span>
        <span>Edit Template</span>
    </x-slot>

    <x-slot name="title">Edit Template</x-slot>

    <div class="bg-white rounded shadow-md border border-gray-300">
        <div class="p-6 border-b border-gray-200">
            <div class="flex items-center">
                <span class="material-icons mr-2 text-primary-DEFAULT">edit</span>
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
            <form action="{{ route('template.update', $template->id) }}" method="POST" enctype="multipart/form-data" class="space-y-6">
                @csrf
                @method('PUT')
                <!-- Basic Information -->
                <div class="border-b border-gray-200 pb-5">
                    <h2 class="text-sm font-semibold text-gray-700 mb-4 flex items-center">
                        <span class="material-icons text-sm mr-1 text-primary-DEFAULT">description</span>
                        Basic Information
                    </h2>
                    <div class="mb-4">
                        <label for="name" class="flex items-center text-xs font-medium text-gray-700 mb-1">
                            <span class="material-icons text-sm mr-1 text-primary-DEFAULT">title</span>
                            Template Name
                        </label>
                        <input type="text" name="name" id="name" value="{{ old('name', $template->name) }}" class="w-full text-xs border-gray-300 rounded-[1px] focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50" required>
                        <p class="mt-1 text-[10px] text-gray-500">Enter a descriptive name for the template</p>
                        @error('name')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div class="mb-4">
                        <label for="description" class="flex items-center text-xs font-medium text-gray-700 mb-1">
                            <span class="material-icons text-sm mr-1 text-primary-DEFAULT">notes</span>
                            Description
                        </label>
                        <textarea name="description" id="description" rows="3" class="w-full text-xs border-gray-300 rounded-[1px] focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50">{{ old('description', $template->description) }}</textarea>
                        <p class="mt-1 text-[10px] text-gray-500">Describe the template for your reference</p>
                        @error('description')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
                <!-- Template Files -->
                <div class="border-b border-gray-200 pb-5">
                    <h2 class="text-sm font-semibold text-gray-700 mb-4 flex items-center">
                        <span class="material-icons text-sm mr-1 text-primary-DEFAULT">cloud_upload</span>
                        Template Files
                    </h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <div class="mb-4">
                                <label for="preview_image" class="flex items-center text-xs font-medium text-gray-700 mb-1">
                                    <span class="material-icons text-sm mr-1 text-primary-DEFAULT">image</span>
                                    Preview Image
                                </label>
                                <div class="border-2 border-dashed border-gray-300 rounded-lg p-6 text-center mb-2">
                                    <div class="mb-3">
                                        @if($template->preview_image)
                                            <img src="{{ asset('storage/' . $template->preview_image) }}" alt="{{ $template->name }}" class="h-32 mx-auto mb-2">
                                        @else
                                            <span class="material-icons text-gray-400 text-3xl">cloud_upload</span>
                                        @endif
                                    </div>
                                    <p class="text-sm text-gray-500 mb-2">Drag and drop your image here, or click to browse</p>
                                    <p class="text-xs text-gray-400">PNG, JPG, GIF up to 2MB</p>
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
                                    <span class="material-icons text-sm mr-1 text-primary-DEFAULT">picture_as_pdf</span>
                                    PDF File
                                </label>
                                <div class="border-2 border-dashed border-gray-300 rounded-lg p-6 text-center mb-2">
                                    <div class="mb-3">
                                        @if($template->pdf_file)
                                            <span class="material-icons text-primary-DEFAULT text-3xl">picture_as_pdf</span>
                                            <p class="text-xs text-primary-DEFAULT mt-1">{{ basename($template->pdf_file) }}</p>
                                        @else
                                            <span class="material-icons text-gray-400 text-3xl">picture_as_pdf</span>
                                        @endif
                                    </div>
                                    <p class="text-sm text-gray-500 mb-2">Upload your certificate PDF file here</p>
                                    <p class="text-xs text-gray-400">PDF up to 5MB</p>
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
                <!-- Orientation -->
                <div class="pb-5">
                    <h2 class="text-sm font-semibold text-gray-700 mb-4 flex items-center">
                        <span class="material-icons text-sm mr-1 text-primary-DEFAULT">crop_portrait</span>
                        Orientation
                    </h2>
                    <div class="flex space-x-4 mb-4">
                        <label class="flex items-center">
                            <input type="radio" name="orientation" value="portrait" class="mr-2" {{ old('orientation', $template->orientation) === 'portrait' ? 'checked' : '' }} required>
                            <span class="text-sm">Portrait</span>
                        </label>
                        <label class="flex items-center">
                            <input type="radio" name="orientation" value="landscape" class="mr-2" {{ old('orientation', $template->orientation) === 'landscape' ? 'checked' : '' }}>
                            <span class="text-sm">Landscape</span>
                        </label>
                    </div>
                    @error('orientation')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
                <div class="border-t border-gray-200 pt-4 mt-6 flex justify-end space-x-3">
                    <a href="{{ route('template.designer') }}" class="px-3 py-1 bg-gradient-to-r from-red-600 to-red-500 hover:from-red-700 hover:to-red-600 text-white rounded shadow-sm text-xs font-medium transition-colors duration-200 ease-in-out flex items-center">
                        <span class="material-icons text-xs mr-1">cancel</span>
                        Cancel
                    </a>
                    <button type="submit" class="px-3 py-1 bg-gradient-to-r from-blue-600 to-blue-500 hover:from-blue-700 hover:to-blue-600 text-white rounded shadow-sm text-xs font-medium transition-colors duration-200 ease-in-out flex items-center">
                        <span class="material-icons text-xs mr-1">save</span>
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