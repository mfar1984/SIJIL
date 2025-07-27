<x-app-layout>
    <x-slot name="breadcrumb">
        <span>Survey</span>
    </x-slot>

    <x-slot name="title">Survey Management</x-slot>

    <div class="bg-white rounded shadow-md border border-gray-300">
        <div class="p-6 border-b border-gray-200">
            <div class="flex justify-between items-start">
                <div>
                    <div class="flex items-center">
                        <span class="material-icons mr-2 text-primary-DEFAULT">quiz</span>
                        <h1 class="text-xl font-bold text-gray-800">Survey Management</h1>
                    </div>
                    <p class="text-xs text-gray-500 mt-1 ml-8">Create and manage surveys for events and feedback</p>
                </div>
                <a href="{{ route('survey.create') }}" class="bg-gradient-to-r from-blue-600 to-blue-500 hover:from-blue-700 hover:to-blue-600 text-white px-3 py-1 rounded shadow-sm font-medium flex items-center text-xs transition-colors duration-200 ease-in-out">
                    <span class="material-icons text-xs mr-1">add_circle</span>
                    Create New Survey
                </a>
            </div>
        </div>
        
        <div class="p-4">
            @if(session('success'))
                <div class="bg-green-50 text-green-800 p-4 mb-4 rounded-md flex items-start">
                    <span class="material-icons mr-2">check_circle</span>
                    <span>{{ session('success') }}</span>
                </div>
            @endif

            <!-- Survey Table -->
            @if($surveys->count() > 0)
                <div class="overflow-visible border border-gray-200 rounded">
                    <table class="min-w-full border-collapse">
                        <thead>
                            <tr class="bg-primary-light text-white text-xs uppercase">
                                <th class="py-3 px-4 text-left rounded-tl">Title</th>
                                <th class="py-3 px-4 text-left">Related Event</th>
                                <th class="py-3 px-4 text-left">Questions</th>
                                <th class="py-3 px-4 text-left">Status</th>
                                <th class="py-3 px-4 text-left">Access Type</th>
                                <th class="py-3 px-4 text-left">Created At</th>
                                <th class="py-3 px-4 text-center rounded-tr">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach($surveys as $survey)
                                <tr class="text-xs hover:bg-gray-50">
                                    <td class="py-3 px-4 font-medium">{{ $survey->title }}</td>
                                    <td class="py-3 px-4">
                                        @if($survey->event)
                                            {{ $survey->event->name }}
                                        @else
                                            <span class="text-gray-400">-</span>
                                        @endif
                                    </td>
                                    <td class="py-3 px-4">{{ $survey->questions->count() }}</td>
                                    <td class="py-3 px-4">
                                        @if($survey->status === 'published')
                                            <span class="bg-status-active-bg text-status-active-text px-2 py-1 rounded-full text-xs">Published</span>
                                        @elseif($survey->status === 'draft')
                                            <span class="bg-status-pending-bg text-status-pending-text px-2 py-1 rounded-full text-xs">Draft</span>
                                        @else
                                            <span class="bg-status-completed-bg text-status-completed-text px-2 py-1 rounded-full text-xs">Closed</span>
                                        @endif
                                    </td>
                                    <td class="py-3 px-4">
                                        @if($survey->access_type === 'public')
                                            <span class="bg-green-50 text-green-700 px-2 py-1 rounded-full text-xs">Public</span>
                                        @elseif($survey->access_type === 'private')
                                            <span class="bg-yellow-50 text-yellow-700 px-2 py-1 rounded-full text-xs">Private</span>
                                        @else
                                            <span class="bg-blue-50 text-blue-700 px-2 py-1 rounded-full text-xs">Registered</span>
                                        @endif
                                    </td>
                                    <td class="py-3 px-4">{{ $survey->created_at->format('d M Y') }}</td>
                                    <td class="py-3 px-4">
                                        <div class="flex justify-center space-x-2">
                                            <!-- Share Options Dropdown -->
                                            @if($survey->status === 'published')
                                                <div class="relative" x-data="{ shareDropdownOpen{{ $survey->id }}: false }">
                                                    <button @click="shareDropdownOpen{{ $survey->id }} = !shareDropdownOpen{{ $survey->id }}" class="p-1 bg-green-50 rounded hover:bg-green-100 border border-green-100" title="Share Options">
                                                        <span class="material-icons text-green-600 text-xs">share</span>
                                                    </button>
                                                    <div x-show="shareDropdownOpen{{ $survey->id }}" @click.outside="shareDropdownOpen{{ $survey->id }} = false" class="absolute right-0 mt-2 z-50 w-48 bg-white rounded-md shadow-lg">
                                                        <div class="py-1 border border-gray-200 rounded-md">
                                                            <button @click="copyLink('{{ $survey->public_url }}')" class="flex items-center w-full text-left px-4 py-2 text-xs text-gray-700 hover:bg-gray-100">
                                                                <span class="material-icons text-blue-600 text-xs mr-2">link</span>
                                                                Copy Survey Link
                                                            </button>
                                                            <a href="#" class="flex items-center w-full text-left px-4 py-2 text-xs text-gray-700 hover:bg-gray-100">
                                                                <span class="material-icons text-purple-600 text-xs mr-2">qr_code</span>
                                                                Generate QR Code
                                                            </a>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endif
                                            
                                            <a href="{{ route('survey.show', $survey) }}" class="p-1 bg-blue-50 rounded hover:bg-blue-100 border border-blue-100" title="View">
                                                <span class="material-icons text-primary-DEFAULT text-xs">visibility</span>
                                            </a>
                                            <a href="{{ route('survey.edit', $survey) }}" class="p-1 bg-yellow-50 rounded hover:bg-yellow-100 border border-yellow-100" title="Edit">
                                                <span class="material-icons text-yellow-600 text-xs">edit</span>
                                            </a>
                                            @if($survey->status === 'published')
                                                <a href="{{ route('survey.responses', $survey) }}" class="p-1 bg-purple-50 rounded hover:bg-purple-100 border border-purple-100" title="View Responses">
                                                    <span class="material-icons text-purple-600 text-xs">format_list_bulleted</span>
                                                </a>
                                            @endif
                                            <form method="POST" action="{{ route('survey.destroy', $survey) }}" onsubmit="return confirm('Are you sure you want to delete this survey?')" class="inline-block">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="p-1 bg-red-50 rounded hover:bg-red-100 border border-red-100" title="Delete">
                                                    <span class="material-icons text-red-600 text-xs">delete</span>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                
                <div class="mt-4">
                    {{ $surveys->links() }}
                </div>
            @else
                <div class="bg-gray-50 rounded-md p-6 text-center">
                    <div class="flex justify-center">
                        <span class="material-icons text-gray-400 text-5xl">quiz</span>
                    </div>
                    <h3 class="mt-2 text-gray-500 text-lg font-medium">No surveys found</h3>
                    <p class="mt-1 text-gray-400 text-sm">Create your first survey to get started</p>
                    <div class="mt-6">
                        <a href="{{ route('survey.create') }}" class="bg-primary-DEFAULT hover:bg-primary-dark text-white px-4 py-2 rounded font-medium text-sm">
                            Create New Survey
                        </a>
                    </div>
                </div>
            @endif
        </div>
    </div>

    <!-- Copy to Clipboard JavaScript -->
    <script>
    function copyLink(url) {
        navigator.clipboard.writeText(url)
            .then(() => {
                alert('Survey link copied to clipboard!');
            })
            .catch((error) => {
                console.error('Could not copy text: ', error);
                // Fallback
                const textarea = document.createElement('textarea');
                textarea.value = url;
                document.body.appendChild(textarea);
                textarea.select();
                document.execCommand('copy');
                document.body.removeChild(textarea);
                alert('Survey link copied to clipboard!');
            });
    }
    </script>
</x-app-layout>
