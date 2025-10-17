<x-app-layout>
    <x-slot name="breadcrumb">
        <span>Certificate</span>
        <span class="mx-2 text-gray-500">/</span>
        <span>Template Designer</span>
    </x-slot>

    <x-slot name="title">Template Designer</x-slot>

    <div class="bg-white rounded shadow-md border border-gray-300">
        <div class="p-6 border-b border-gray-200 flex justify-between items-start">
            <div>
                <div class="flex items-center">
                    <span class="material-icons mr-2 text-primary-DEFAULT">design_services</span>
                    <h1 class="text-xl font-bold text-gray-800">Template Designer</h1>
                </div>
                <p class="text-xs text-gray-500 mt-1 ml-8">Manage all certificate templates</p>
            </div>
            <a href="{{ route('template.create') }}" class="bg-gradient-to-r from-blue-600 to-blue-500 hover:from-blue-700 hover:to-blue-600 text-white px-3 h-[36px] rounded shadow-sm font-medium flex items-center text-xs transition-colors duration-200 ease-in-out">
                <span class="material-icons text-xs mr-1">add_circle</span>
                Create Template
            </a>
        </div>
        <div class="p-4">
            <!-- Success Message -->
            @if(session('success'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                    {{ session('success') }}
                </div>
            @endif
            <!-- Error Message -->
            @if(session('error'))
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                    {{ session('error') }}
                </div>
            @endif
            <!-- Card Grid -->
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
                @forelse($templates as $template)
                <div class="bg-white border border-gray-200 rounded-lg shadow hover:shadow-lg transition-shadow duration-200 flex flex-col items-center p-4">
                    <div class="w-full h-64 bg-gray-100 rounded flex items-center justify-center mb-4">
                        @if($template->preview_image)
                            <img src="{{ asset('storage/' . $template->preview_image) }}" alt="{{ $template->name }}" class="h-full object-contain">
                        @else
                            <span class="text-gray-400 text-5xl material-icons">image</span>
                        @endif
                    </div>
                    <div class="w-full flex-1 flex flex-col items-center">
                        <h2 class="font-semibold text-base text-gray-700 mb-1">{{ $template->name }}</h2>
                        <p class="text-xs text-gray-500 mb-2">{{ $template->orientation }} · {{ $template->created_at ? $template->created_at->format('d M Y') : 'No date' }}</p>
                        <div class="flex space-x-3">
                            <a href="{{ route('template.edit', $template->id) }}" class="text-primary-DEFAULT text-xs font-medium underline flex items-center">
                                <span class="material-icons text-xs mr-1">edit</span>
                                Edit
                            </a>
                            @if($template->pdf_file)
                                <a href="{{ route('template.editor', $template->id) }}" class="text-green-600 text-xs font-medium underline flex items-center">
                                    <span class="material-icons text-xs mr-1">design_services</span>
                                    Design
                                </a>
                            @endif
                        </div>
                        @if($template->pdf_file)
                            <a href="{{ asset('storage/' . $template->pdf_file) }}" target="_blank" class="mt-2 inline-flex items-center text-xs text-blue-600 hover:underline">
                                <span class="material-icons text-base mr-1">picture_as_pdf</span> View PDF
                            </a>
                        @endif
                    </div>
                </div>
                @empty
                <div class="col-span-full text-center py-12">
                    <span class="material-icons text-gray-400 text-5xl mb-2">design_services</span>
                    <h3 class="text-gray-500 font-medium">No templates yet</h3>
                    <p class="text-gray-400 text-xs mt-1 mb-4">Get started by creating your first template</p>
                    <a href="{{ route('template.create') }}" class="bg-primary-DEFAULT hover:bg-primary-dark text-white px-6 py-3 rounded-md flex items-center mx-auto w-max">
                        <span class="material-icons mr-2">add_circle</span>
                        Create Template
                    </a>
                </div>
                @endforelse
            </div>
        </div>
    </div>
</x-app-layout> 