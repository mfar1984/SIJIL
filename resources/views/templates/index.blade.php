<x-app-layout>
    <x-slot name="breadcrumb">
        <span>Certificate</span>
        <span class="mx-2 text-gray-500">/</span>
        <span>Template Designer</span>
    </x-slot>

    <x-slot name="title">Certificate Template Designer</x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Header with Title and Create Button -->
            <div class="flex justify-between items-center mb-6">
                <h1 class="text-2xl font-semibold text-gray-900">Template Library</h1>
                <a href="{{ route('template.create') }}" class="bg-primary-DEFAULT hover:bg-primary-dark text-white px-4 py-2 rounded-md flex items-center">
                    <span class="material-icons mr-1">add</span>
                    Create Template
                </a>
            </div>

            <!-- Filter by Category -->
            <div class="mb-6">
                <div class="bg-white rounded-md shadow-sm p-4">
                    <h2 class="text-lg font-medium text-gray-700 mb-3">Filter by Category</h2>
                    <div class="flex flex-wrap gap-2">
                        <a href="{{ route('template.designer') }}" class="px-3 py-1 rounded-full {{ request()->query('category') ? 'bg-gray-100 text-gray-700' : 'bg-primary-DEFAULT text-white' }}">
                            All
                        </a>
                        <a href="{{ route('template.designer', ['category' => 'portrait']) }}" class="px-3 py-1 rounded-full {{ request()->query('category') == 'portrait' ? 'bg-primary-DEFAULT text-white' : 'bg-gray-100 text-gray-700' }}">
                            Portrait
                        </a>
                        <a href="{{ route('template.designer', ['category' => 'landscape']) }}" class="px-3 py-1 rounded-full {{ request()->query('category') == 'landscape' ? 'bg-primary-DEFAULT text-white' : 'bg-gray-100 text-gray-700' }}">
                            Landscape
                        </a>
                    </div>
                </div>
            </div>

            <!-- Templates Grid -->
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
                @forelse($templates as $template)
                    <div class="bg-white rounded-lg shadow-sm overflow-hidden border border-gray-200 hover:shadow-md transition-shadow">
                        <div class="relative h-48 bg-gray-100">
                            @if($template->pdf_file)
                                <iframe src="{{ asset('storage/' . $template->pdf_file) }}#toolbar=0&navpanes=0&scrollbar=0" class="w-full h-full"></iframe>
                                <div class="absolute top-2 right-2 bg-white rounded-full px-2 py-1 text-xs font-medium shadow-sm">
                                    {{ ucfirst($template->orientation) }}
                                </div>
                            @else
                                <div class="flex items-center justify-center h-full">
                                    <span class="text-gray-400 text-5xl material-icons">image</span>
                                </div>
                            @endif
                        </div>
                        <div class="w-full flex-1 flex flex-col items-center p-4">
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
                                <form action="{{ route('template.destroy', $template->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this template?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-500 text-xs font-medium underline flex items-center">
                                        <span class="material-icons text-xs mr-1">delete</span>
                                        Delete
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-span-full bg-white rounded-lg shadow-sm p-8 text-center">
                        <div class="flex flex-col items-center">
                            <span class="material-icons text-gray-400 text-5xl mb-4">description</span>
                            <h3 class="text-lg font-medium text-gray-700 mb-2">No Templates Found</h3>
                            <p class="text-gray-500 mb-6">Get started by creating your first certificate template</p>
                            <a href="{{ route('template.create') }}" class="bg-primary-DEFAULT hover:bg-primary-dark text-white px-4 py-2 rounded-md flex items-center">
                                <span class="material-icons mr-1">add</span>
                                Create Template
                            </a>
                        </div>
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</x-app-layout> 