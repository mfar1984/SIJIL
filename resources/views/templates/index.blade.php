<x-app-layout>
    <x-slot name="breadcrumb">
        <span>Certificate</span>
        <span class="mx-2 text-gray-500">/</span>
        <span>Template Designer</span>
    </x-slot>

    <x-slot name="title">Certificate Templates</x-slot>

    <div class="bg-white rounded shadow-md border border-gray-300">
        <div class="p-6 border-b border-gray-200">
            <div class="flex justify-between items-start">
                <div>
                    <div class="flex items-center">
                        <span class="material-icons mr-2 text-primary-DEFAULT">design_services</span>
                        <h1 class="text-xl font-bold text-gray-800">Template Management</h1>
                    </div>
                    <p class="text-xs text-gray-500 mt-1 ml-8">Design and manage certificate templates</p>
                </div>
                <div class="flex gap-2">
                    <a href="{{ route('template.create') }}" class="bg-gradient-to-r from-blue-600 to-blue-500 hover:from-blue-700 hover:to-blue-600 text-white px-3 py-1 rounded shadow-sm font-medium flex items-center text-xs transition-colors duration-200 ease-in-out">
                        <span class="material-icons text-xs mr-1">add_circle</span>
                        Create Template
                    </a>
                </div>
            </div>
        </div>
        
        <div class="p-4">
            @if(session('success'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                    {{ session('success') }}
                </div>
            @endif

            @if(session('error'))
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                    {{ session('error') }}
                </div>
            @endif
            
            <div class="overflow-visible border border-gray-200 rounded">
                <table class="min-w-full border-collapse">
                    <thead>
                        <tr class="bg-primary-light text-white text-xs uppercase">
                            <th class="py-3 px-4 text-left rounded-tl">Template Name</th>
                            <th class="py-3 px-4 text-left">Description</th>
                            <th class="py-3 px-4 text-left">Status</th>
                            <th class="py-3 px-4 text-left">Created Date</th>
                            <th class="py-3 px-4 text-center rounded-tr">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($templates as $template)
                            <tr class="text-xs hover:bg-gray-50">
                                <td class="py-3 px-4 font-medium">{{ $template->name }}</td>
                                <td class="py-3 px-4">{{ $template->description ?? 'No description' }}</td>
                                <td class="py-3 px-4">
                                    @if($template->is_active)
                                        <span class="bg-status-active-bg text-status-active-text px-2 py-1 rounded-full text-xs">Active</span>
                                    @else
                                        <span class="bg-status-pending-bg text-status-pending-text px-2 py-1 rounded-full text-xs">Inactive</span>
                                    @endif
                                </td>
                                <td class="py-3 px-4">{{ $template->created_at->format('d M Y') }}</td>
                                <td class="py-3 px-4">
                                    <div class="flex justify-center space-x-2">
                                        <a href="{{ route('template.show', $template->id) }}" class="p-1 bg-blue-50 rounded hover:bg-blue-100 border border-blue-100" title="View">
                                            <span class="material-icons text-primary-DEFAULT text-xs">visibility</span>
                                        </a>
                                        <a href="{{ route('template.edit', $template->id) }}" class="p-1 bg-yellow-50 rounded hover:bg-yellow-100 border border-yellow-100" title="Edit">
                                            <span class="material-icons text-yellow-600 text-xs">edit</span>
                                        </a>
                                        <a href="{{ route('template.designer.create', ['id' => $template->id]) }}" class="p-1 bg-purple-50 rounded hover:bg-purple-100 border border-purple-100" title="Design">
                                            <span class="material-icons text-purple-600 text-xs">design_services</span>
                                        </a>
                                        <form action="{{ route('template.duplicate', $template->id) }}" method="POST" class="inline">
                                            @csrf
                                            <button type="submit" class="p-1 bg-indigo-50 rounded hover:bg-indigo-100 border border-indigo-100" title="Duplicate">
                                                <span class="material-icons text-indigo-600 text-xs">content_copy</span>
                                            </button>
                                        </form>
                                        <form action="{{ route('template.destroy', $template->id) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete this template?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="p-1 bg-red-50 rounded hover:bg-red-100 border border-red-100" title="Delete">
                                                <span class="material-icons text-red-600 text-xs">delete</span>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr class="text-xs">
                                <td colspan="5" class="py-8 text-center text-gray-500">
                                    No templates found. <a href="{{ route('template.create') }}" class="text-primary-DEFAULT hover:underline">Create your first template</a>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination Row -->
            @if($templates->hasPages())
                <div class="mt-6 flex flex-col sm:flex-row sm:items-center sm:justify-between">
                    <div class="mb-2 sm:mb-0 text-xs text-gray-500">
                        Showing {{ $templates->firstItem() }} to {{ $templates->lastItem() }} of {{ $templates->total() }} results
                    </div>
                    <div class="flex justify-end">
                        {{ $templates->links('components.pagination-modern') }}
                    </div>
                </div>
            @endif
        </div>
    </div>
</x-app-layout> 