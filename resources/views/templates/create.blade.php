<x-app-layout>
    <x-slot name="breadcrumb">
        <span>Certificate</span>
        <span class="mx-2 text-gray-500">/</span>
        <span><a href="{{ route('template.designer') }}" class="text-primary-DEFAULT hover:underline">Template Designer</a></span>
        <span class="mx-2 text-gray-500">/</span>
        <span>Create Template</span>
    </x-slot>

    <x-slot name="title">Create Certificate Template</x-slot>

    <div class="bg-white rounded shadow-md border border-gray-300">
        <div class="p-6 border-b border-gray-200">
            <div class="flex justify-between items-start">
                <div>
                    <div class="flex items-center">
                        <span class="material-icons mr-2 text-primary-DEFAULT">description</span>
                        <h1 class="text-xl font-bold text-gray-800">Create Certificate Template</h1>
                    </div>
                    <p class="text-xs text-gray-500 mt-1 ml-8">Design and upload a new certificate template</p>
                </div>
            </div>
        </div>
        <div class="p-6">
            <form action="{{ route('template.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Template Name -->
                    <div>
                        <label for="name" class="block text-xs font-medium text-gray-700 mb-1 flex items-center">
                            <span class="material-icons text-primary-DEFAULT text-base mr-1">title</span>
                            Template Name
                        </label>
                        <input id="name" name="name" type="text" class="w-full border border-gray-300 rounded px-3 py-2 text-sm focus:ring focus:ring-primary-light focus:border-primary-light" required autofocus />
                        <p class="text-xs text-gray-500 mt-1 ml-6">Enter a descriptive name for this template.</p>
                        <x-input-error :messages="$errors->get('name')" class="mt-2" />
                    </div>
                    <!-- Orientation -->
                    <div>
                        <label for="orientation" class="block text-xs font-medium text-gray-700 mb-1 flex items-center">
                            <span class="material-icons text-primary-DEFAULT text-base mr-1">crop_landscape</span>
                            Orientation
                        </label>
                        <div class="flex gap-4 mt-2">
                            <label class="flex items-center">
                                <input id="landscape" name="orientation" type="radio" value="landscape" class="h-4 w-4 text-primary-DEFAULT focus:ring-primary-DEFAULT" checked>
                                <span class="ml-2 text-sm">Landscape (297×210mm)</span>
                            </label>
                            <label class="flex items-center">
                                <input id="portrait" name="orientation" type="radio" value="portrait" class="h-4 w-4 text-primary-DEFAULT focus:ring-primary-DEFAULT">
                                <span class="ml-2 text-sm">Portrait (210×297mm)</span>
                            </label>
                        </div>
                        <p class="text-xs text-gray-500 mt-1 ml-6">Choose the orientation for your certificate.</p>
                        <x-input-error :messages="$errors->get('orientation')" class="mt-2" />
                    </div>
                    <!-- Description -->
                    <div class="md:col-span-2">
                        <label for="description" class="block text-xs font-medium text-gray-700 mb-1 flex items-center">
                            <span class="material-icons text-primary-DEFAULT text-base mr-1">notes</span>
                            Description
                        </label>
                        <textarea id="description" name="description" rows="2" class="w-full border border-gray-300 rounded px-3 py-2 text-sm focus:ring focus:ring-primary-light focus:border-primary-light"></textarea>
                        <p class="text-xs text-gray-500 mt-1 ml-6">Optional: Add a short description for this template.</p>
                        <x-input-error :messages="$errors->get('description')" class="mt-2" />
                    </div>
                    <!-- PDF Background -->
                    <div class="md:col-span-2">
                        <label for="pdf_file" class="block text-xs font-medium text-gray-700 mb-1 flex items-center">
                            <span class="material-icons text-primary-DEFAULT text-base mr-1">picture_as_pdf</span>
                            PDF Background
                        </label>
                        <input id="pdf_file" name="pdf_file" type="file" accept=".pdf" class="border border-gray-300 rounded px-3 py-2 text-sm focus:ring focus:ring-primary-light focus:border-primary-light w-full" required>
                        <p class="text-xs text-gray-500 mt-1 ml-6">Upload a PDF file to use as the certificate background. Max size: 10MB.</p>
                        <x-input-error :messages="$errors->get('pdf_file')" class="mt-2" />
                    </div>
                    <input type="hidden" name="template_data" value="{{ json_encode([
                        'width' => 297,
                        'height' => 210,
                        'elements' => [
                            [
                                'type' => 'text',
                                'content' => 'Certificate of Completion',
                                'x' => 148,
                                'y' => 40,
                                'fontSize' => 16,
                                'fontWeight' => 'bold',
                                'textAlign' => 'center'
                            ]
                        ]
                    ]) }}">
                </div>
                <div class="border-t border-gray-200 pt-4 mt-6 flex justify-end space-x-3">
                    <a href="{{ route('template.designer') }}" class="px-3 py-1 bg-gradient-to-r from-red-600 to-red-500 hover:from-red-700 hover:to-red-600 text-white rounded shadow-sm text-xs font-medium transition-colors duration-200 ease-in-out flex items-center">
                        <span class="material-icons text-xs mr-1">cancel</span>
                        Cancel
                    </a>
                    <button type="submit" class="px-3 py-1 bg-gradient-to-r from-blue-600 to-blue-500 hover:from-blue-700 hover:to-blue-600 text-white rounded shadow-sm text-xs font-medium transition-colors duration-200 ease-in-out flex items-center">
                        <span class="material-icons text-xs mr-1">save</span>
                        Save Template
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout> 