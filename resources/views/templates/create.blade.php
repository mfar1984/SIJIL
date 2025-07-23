<x-app-layout>
    <x-slot name="breadcrumb">
        <span>Certificate</span>
        <span class="mx-2 text-gray-500">/</span>
        <span><a href="{{ route('template.designer') }}" class="text-primary-DEFAULT hover:underline">Template Designer</a></span>
        <span class="mx-2 text-gray-500">/</span>
        <span>Create Template</span>
    </x-slot>

    <x-slot name="title">Create Certificate Template</x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm rounded-lg p-6">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-xl font-semibold text-gray-800">Create Certificate Template</h3>
                </div>

                <form action="{{ route('template.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf

                    <div class="grid grid-cols-1 gap-6">
                        <!-- Template Name -->
                        <div>
                            <x-input-label for="name" :value="__('Template Name')" />
                            <x-text-input id="name" class="block mt-1 w-full" type="text" name="name" :value="old('name')" required autofocus />
                            <x-input-error :messages="$errors->get('name')" class="mt-2" />
                        </div>

                        <!-- Description -->
                        <div>
                            <x-input-label for="description" :value="__('Description')" />
                            <textarea id="description" name="description" rows="3" class="block w-full mt-1 rounded-md border-gray-300 shadow-sm focus:border-primary-DEFAULT focus:ring focus:ring-primary-DEFAULT focus:ring-opacity-50">{{ old('description') }}</textarea>
                            <x-input-error :messages="$errors->get('description')" class="mt-2" />
                        </div>

                        <!-- Orientation -->
                        <div>
                            <x-input-label for="orientation" :value="__('Orientation')" />
                            <div class="mt-2 flex gap-4">
                                <div class="flex items-center">
                                    <input id="portrait" name="orientation" type="radio" value="portrait" class="h-4 w-4 text-primary-DEFAULT focus:ring-primary-DEFAULT" checked>
                                    <label for="portrait" class="ml-2 block text-sm font-medium text-gray-700">Portrait (210×297mm)</label>
                                </div>
                                <div class="flex items-center">
                                    <input id="landscape" name="orientation" type="radio" value="landscape" class="h-4 w-4 text-primary-DEFAULT focus:ring-primary-DEFAULT">
                                    <label for="landscape" class="ml-2 block text-sm font-medium text-gray-700">Landscape (297×210mm)</label>
                                </div>
                            </div>
                            <x-input-error :messages="$errors->get('orientation')" class="mt-2" />
                        </div>

                        <!-- PDF Background -->
                        <div>
                            <x-input-label for="pdf_file" :value="__('PDF Background')" />
                            <div class="mt-2 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-md">
                                <div class="space-y-1 text-center">
                                    <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48" aria-hidden="true">
                                        <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                    </svg>
                                    <div class="flex text-sm text-gray-600">
                                        <label for="pdf_file" class="relative cursor-pointer bg-white rounded-md font-medium text-primary-DEFAULT hover:text-primary-dark focus-within:outline-none">
                                            <span>Upload a PDF file</span>
                                            <input id="pdf_file" name="pdf_file" type="file" accept=".pdf" class="sr-only" required>
                                        </label>
                                        <p class="pl-1">or drag and drop</p>
                                    </div>
                                    <p class="text-xs text-gray-500">
                                        PDF up to 10MB
                                    </p>
                                </div>
                            </div>
                            <x-input-error :messages="$errors->get('pdf_file')" class="mt-2" />
                        </div>
                    </div>

                    <div class="flex items-center justify-end mt-8">
                        <x-secondary-button type="button" onclick="window.history.back()">
                            {{ __('Cancel') }}
                        </x-secondary-button>

                        <x-primary-button class="ml-4">
                            <span class="material-icons mr-1 text-sm">save</span>
                            {{ __('Save Template') }}
                        </x-primary-button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout> 