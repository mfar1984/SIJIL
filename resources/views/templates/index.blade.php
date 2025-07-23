<x-app-layout>
    <x-slot name="breadcrumb">
        <span>Certificate</span>
        <span class="mx-2 text-gray-500">/</span>
        <span>Template Designer</span>
    </x-slot>

    <x-slot name="title">Certificate Templates</x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="flex justify-between mb-6">
                <h2 class="text-xl font-semibold text-gray-800">Template Management</h2>
                <div class="flex gap-2">
                    <a href="{{ route('template.designer.create') }}" class="bg-primary-DEFAULT hover:bg-primary-dark text-white px-4 py-2 rounded-md flex items-center">
                        <span class="material-icons mr-1 text-sm">add</span>
                        Visual Designer
                    </a>
                    <a href="{{ route('template.create') }}" class="border border-gray-300 hover:border-primary-DEFAULT text-gray-700 px-4 py-2 rounded-md flex items-center">
                        <span class="material-icons mr-1 text-sm">add</span>
                        Simple Create
                    </a>
                </div>
            </div>
            
            <div class="bg-white overflow-hidden shadow-sm rounded-lg">
                <div class="p-6">
                    <div class="overflow-x-auto">
                        <table class="w-full whitespace-nowrap">
                            <thead>
                                <tr class="bg-gray-100">
                                    <th class="px-4 py-3 text-left">Template Name</th>
                                    <th class="px-4 py-3 text-left">Description</th>
                                    <th class="px-4 py-3 text-left">Status</th>
                                    <th class="px-4 py-3 text-left">Created Date</th>
                                    <th class="px-4 py-3 text-center">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($templates as $template)
                                    <tr class="border-b">
                                        <td class="px-4 py-3 font-medium">{{ $template->name }}</td>
                                        <td class="px-4 py-3 text-gray-600">{{ $template->description ?? 'No description' }}</td>
                                        <td class="px-4 py-3">
                                            @if($template->is_active)
                                                <span class="px-2 py-1 text-xs rounded-full bg-green-100 text-green-800">Active</span>
                                            @else
                                                <span class="px-2 py-1 text-xs rounded-full bg-gray-100 text-gray-800">Inactive</span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-3">{{ $template->created_at->format('d/m/Y') }}</td>
                                        <td class="px-4 py-3 text-center">
                                            <div class="flex justify-center space-x-1">
                                                <a href="{{ route('template.show', $template->id) }}" class="p-1 text-blue-600 hover:text-blue-800" title="View">
                                                    <span class="material-icons text-sm">visibility</span>
                                                </a>
                                                <a href="{{ route('template.edit', $template->id) }}" class="p-1 text-primary-DEFAULT hover:text-primary-dark" title="Edit">
                                                    <span class="material-icons text-sm">edit</span>
                                                </a>
                                                <a href="{{ route('template.designer.create', ['id' => $template->id]) }}" class="p-1 text-amber-600 hover:text-amber-800" title="Design">
                                                    <span class="material-icons text-sm">design_services</span>
                                                </a>
                                                <form action="{{ route('template.duplicate', $template->id) }}" method="POST" class="inline">
                                                    @csrf
                                                    <button type="submit" class="p-1 text-purple-600 hover:text-purple-800" title="Duplicate">
                                                        <span class="material-icons text-sm">content_copy</span>
                                                    </button>
                                                </form>
                                                <form action="{{ route('template.destroy', $template->id) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete this template?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="p-1 text-red-600 hover:text-red-800" title="Delete">
                                                        <span class="material-icons text-sm">delete</span>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="px-4 py-6 text-center text-gray-500">No templates found.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-4">
                        {{ $templates->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout> 