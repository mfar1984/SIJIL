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
                        <span class="material-icons-outlined mr-2 text-primary-DEFAULT">description</span>
                        <h1 class="text-xl font-bold text-gray-800">Create Certificate Template</h1>
                    </div>
                    <p class="text-xs text-gray-500 mt-1 ml-8">Upload a background, then place the text in the designer</p>
                </div>
            </div>
        </div>
        <div class="p-6" x-data="{ orientation: '{{ old('orientation', 'landscape') }}' }">
            <form action="{{ route('template.store') }}" method="POST" enctype="multipart/form-data" class="space-y-2">
                @csrf
                
                <!-- Template Information -->
                <div class="bg-white border border-gray-200 rounded-md shadow-sm">
                    <div class="bg-gray-50 border-b border-gray-200 px-4 py-3">
                        <h2 class="text-sm font-semibold text-gray-700 flex items-center">
                            <span class="material-icons-outlined text-primary-DEFAULT mr-2">description</span>
                            Template Information
                        </h2>
                    </div>
                    
                    <div class="p-4 space-y-4">
                        <!-- Template Name -->
                        <div class="flex flex-col md:flex-row md:items-start gap-3">
                            <label for="name" class="text-xs font-medium text-gray-700 md:w-48 shrink-0 md:pt-2">
                                Template name <span class="text-red-500">*</span>
                            </label>
                            <div class="flex-1">
                                <input id="name" name="name" type="text" value="{{ old('name') }}"
                                       class="w-full h-9 text-xs border-gray-300 rounded px-3 focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50"
                                       required autofocus />
                                <p class="text-xs text-gray-500 mt-1">
                                    This is the name you will pick from when generating certificates.
                                </p>
                                <x-input-error :messages="$errors->get('name')" class="mt-1" />
                            </div>
                        </div>

                        <!-- Description -->
                        <div class="flex flex-col md:flex-row md:items-start gap-3">
                            <label for="description" class="text-xs font-medium text-gray-700 md:w-48 shrink-0 md:pt-2">
                                Description
                            </label>
                            <div class="flex-1">
                                <textarea id="description" name="description" rows="2"
                                          class="w-full text-xs border-gray-300 rounded px-3 py-2 focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50">{{ old('description') }}</textarea>
                                <p class="text-xs text-gray-500 mt-1">Optional. Helps you tell similar templates apart.</p>
                                <x-input-error :messages="$errors->get('description')" class="mt-1" />
                            </div>
                        </div>
                        
                        <!-- Orientation -->
                        <div class="flex flex-col md:flex-row md:items-start gap-3">
                            <label class="text-xs font-medium text-gray-700 md:w-48 shrink-0 md:pt-1">
                                Page orientation <span class="text-red-500">*</span>
                            </label>
                            <div class="flex-1">
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                    <label class="cursor-pointer border rounded p-3 flex gap-3 transition-colors duration-150"
                                           :class="orientation === 'landscape' ? 'border-primary-DEFAULT bg-blue-50' : 'border-gray-200 hover:border-gray-300'">
                                        <input id="landscape" name="orientation" type="radio" value="landscape"
                                               x-model="orientation" class="mt-0.5 shrink-0 text-primary-DEFAULT">
                                        <span>
                                            <span class="block text-xs font-medium text-gray-800">Landscape</span>
                                            <span class="block text-xs text-gray-500 mt-1">297 &times; 210 mm (A4 sideways)</span>
                                        </span>
                                    </label>
                                    <label class="cursor-pointer border rounded p-3 flex gap-3 transition-colors duration-150"
                                           :class="orientation === 'portrait' ? 'border-primary-DEFAULT bg-blue-50' : 'border-gray-200 hover:border-gray-300'">
                                        <input id="portrait" name="orientation" type="radio" value="portrait"
                                               x-model="orientation" class="mt-0.5 shrink-0 text-primary-DEFAULT">
                                        <span>
                                            <span class="block text-xs font-medium text-gray-800">Portrait</span>
                                            <span class="block text-xs text-gray-500 mt-1">210 &times; 297 mm (A4 upright)</span>
                                        </span>
                                    </label>
                                </div>
                                <p class="text-xs text-gray-500 mt-1">
                                    Match this to your PDF background, otherwise the text will not line up.
                                </p>
                                <x-input-error :messages="$errors->get('orientation')" class="mt-1" />
                            </div>
                        </div>
                        
                        <!-- PDF Background -->
                        <div class="flex flex-col md:flex-row md:items-start gap-3">
                            <label for="pdf_file" class="text-xs font-medium text-gray-700 md:w-48 shrink-0 md:pt-2">
                                PDF background <span class="text-red-500">*</span>
                            </label>
                            <div class="flex-1">
                                <div class="flex flex-wrap items-center gap-2">
                                    <input type="text" id="pdf-filename" readonly placeholder="No file chosen"
                                           class="flex-1 min-w-[12rem] h-9 text-xs border-gray-300 rounded px-3 bg-gray-50 cursor-default">
                                    <label for="pdf_file"
                                           class="h-9 px-3 flex items-center border border-gray-300 rounded text-xs text-gray-700 hover:bg-gray-50 cursor-pointer shrink-0">
                                        <span class="material-icons-outlined text-xs mr-1">upload_file</span>
                                        Browse
                                    </label>
                                    <input id="pdf_file" name="pdf_file" type="file" accept=".pdf" class="hidden" required
                                           onchange="document.getElementById('pdf-filename').value = this.files[0] ? this.files[0].name : ''">
                                </div>
                                <p class="text-xs text-gray-500 mt-1">PDF only, up to 10 MB.</p>
                                <x-input-error :messages="$errors->get('pdf_file')" class="mt-1" />
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Starting canvas for the designer. The server recalculates the
                     page size from the chosen orientation, so these values can
                     never drift out of sync with it. --}}
                <input type="hidden" name="template_data" :value="JSON.stringify({
                    width: orientation === 'portrait' ? 210 : 297,
                    height: orientation === 'portrait' ? 297 : 210,
                    elements: []
                })">
                
                <div class="flex justify-end space-x-3 pt-4">
                    <a href="{{ route('template.designer') }}" class="px-3 h-[36px] bg-gradient-to-r from-red-600 to-red-500 hover:from-red-700 hover:to-red-600 text-white rounded shadow-sm text-xs font-medium transition-colors duration-200 ease-in-out flex items-center">
                        <span class="material-icons-outlined text-xs mr-1">cancel</span>
                        Cancel
                    </a>
                    <button type="submit" class="px-3 h-[36px] bg-gradient-to-r from-green-600 to-green-500 hover:from-green-700 hover:to-green-600 text-white rounded shadow-sm text-xs font-medium transition-colors duration-200 ease-in-out flex items-center">
                        <span class="material-icons-outlined text-xs mr-1">save</span>
                        Save and open designer
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
