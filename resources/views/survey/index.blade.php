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
            <!-- Search & Filter Row -->
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-4 gap-2">
                <!-- Search & Filter Form -->
                <form method="GET" action="{{ route('survey.index') }}" class="flex flex-wrap gap-2 items-center justify-between w-full">
                    <!-- Show Entries Dropdown -->
                    <div class="flex items-center gap-2">
                        <span class="text-xs text-gray-600 font-medium">Show</span>
                        <select name="per_page" onchange="this.form.submit()" class="appearance-none px-2 py-1 text-xs border border-gray-300 rounded focus:ring focus:ring-primary-light focus:border-primary-light bg-white bg-no-repeat bg-right w-[60px] font-medium" style="background-image: url('data:image/svg+xml;charset=US-ASCII,<svg xmlns=%22http://www.w3.org/2000/svg%22 width=%2216%22 height=%2216%22 viewBox=%220 0 24 24%22 fill=%22none%22 stroke=%22%23888%22 stroke-width=%222%22 stroke-linecap=%22round%22 stroke-linejoin=%22round%22><polyline points=%226 9 12 15 18 9%22></polyline></svg>'); background-position: right 0.25rem center; background-size: 0.75em;">
                            <option value="10" @if(request('per_page', 10) == 10) selected @endif>10</option>
                            <option value="25" @if(request('per_page') == 25) selected @endif>25</option>
                            <option value="50" @if(request('per_page') == 50) selected @endif>50</option>
                            <option value="100" @if(request('per_page') == 100) selected @endif>100</option>
                        </select>
                        <span class="text-xs text-gray-600">entries per page</span>
                    </div>
                    
                    <!-- Search & Filter Controls -->
                    <div class="flex flex-wrap gap-2 items-center">
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Search survey title, description..." class="border border-gray-300 rounded px-2 py-1 text-xs focus:ring focus:ring-primary-light focus:border-primary-light" id="searchInput" />
                        <select name="status" onchange="this.form.submit()" class="appearance-none px-3 py-1.5 pr-8 text-xs border border-gray-300 rounded focus:ring focus:ring-primary-light focus:border-primary-light bg-white bg-no-repeat bg-right w-[120px]" style="background-image: url('data:image/svg+xml;charset=US-ASCII,<svg xmlns=%22http://www.w3.org/2000/svg%22 width=%2216%22 height=%2216%22 viewBox=%220 0 24 24%22 fill=%22none%22 stroke=%22%23888%22 stroke-width=%222%22 stroke-linecap=%22round%22 stroke-linejoin=%22round%22><polyline points=%226 9 12 15 18 9%22></polyline></svg>'); background-position: right 0.75rem center; background-size: 1em;">
                            <option value="">All Status</option>
                            <option value="draft" @if(request('status') == 'draft') selected @endif>Draft</option>
                            <option value="published" @if(request('status') == 'published') selected @endif>Published</option>
                            <option value="closed" @if(request('status') == 'closed') selected @endif>Closed</option>
                        </select>
                        <select name="access_type" onchange="this.form.submit()" class="appearance-none px-3 py-1.5 pr-8 text-xs border border-gray-300 rounded focus:ring focus:ring-primary-light focus:border-primary-light bg-white bg-no-repeat bg-right w-[120px]" style="background-image: url('data:image/svg+xml;charset=US-ASCII,<svg xmlns=%22http://www.w3.org/2000/svg%22 width=%2216%22 height=%2216%22 viewBox=%220 0 24 24%22 fill=%22none%22 stroke=%22%23888%22 stroke-width=%222%22 stroke-linecap=%22round%22 stroke-linejoin=%22round%22><polyline points=%226 9 12 15 18 9%22></polyline></svg>'); background-position: right 0.75rem center; background-size: 1em;">
                            <option value="">All Access</option>
                            <option value="public" @if(request('access_type') == 'public') selected @endif>Public</option>
                            <option value="private" @if(request('access_type') == 'private') selected @endif>Private</option>
                            <option value="registered" @if(request('access_type') == 'registered') selected @endif>Registered</option>
                        </select>
                        <select name="event_id" onchange="this.form.submit()" class="appearance-none px-3 py-1.5 pr-8 text-xs border border-gray-300 rounded focus:ring focus:ring-primary-light focus:border-primary-light bg-white bg-no-repeat bg-right max-w-[200px] truncate" style="background-image: url('data:image/svg+xml;charset=US-ASCII,<svg xmlns=%22http://www.w3.org/2000/svg%22 width=%2216%22 height=%2216%22 viewBox=%220 0 24 24%22 fill=%22none%22 stroke=%22%23888%22 stroke-width=%222%22 stroke-linecap=%22round%22 stroke-linejoin=%22round%22><polyline points=%226 9 12 15 18 9%22></polyline></svg>'); background-position: right 0.75rem center; background-size: 1em;">
                            <option value="">All Events</option>
                            @foreach($events as $event)
                                <option value="{{ $event->id }}" @if(request('event_id') == $event->id) selected @endif class="truncate">{{ $event->name }}</option>
                            @endforeach
                        </select>
                        <button type="submit" class="bg-primary-light text-white px-3 py-1 h-[38px] rounded text-xs font-medium flex items-center justify-center" title="Search">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4-4m0 0A7 7 0 104 4a7 7 0 0013 13z" />
                            </svg>
                        </button>
                        @if(request('search') || request('status') || request('access_type') || request('event_id'))
                            <a href="{{ route('survey.index') }}?per_page={{ request('per_page', 10) }}" class="text-xs text-gray-500 underline ml-2">Reset</a>
                        @endif
                    </div>
                </form>
            </div>
            
            @if(session('success'))
                <div class="bg-green-50 text-green-800 p-4 mb-4 rounded-md flex items-start">
                    <span class="material-icons mr-2">check_circle</span>
                    <span>{{ session('success') }}</span>
                </div>
            @endif
            
            <!-- Search Results Summary -->
            @if(request('search') || request('status') || request('access_type') || request('event_id'))
                <div class="bg-blue-50 border border-blue-200 text-blue-700 px-4 py-2 rounded mb-4 text-xs">
                    <span class="font-medium">Search Results:</span>
                    @if(request('search'))
                        <span class="ml-2">Searching for "{{ request('search') }}"</span>
                    @endif
                    @if(request('status'))
                        <span class="ml-2">Status: {{ ucfirst(request('status')) }}</span>
                    @endif
                    @if(request('access_type'))
                        <span class="ml-2">Access: {{ ucfirst(request('access_type')) }}</span>
                    @endif
                    @if(request('event_id'))
                        <span class="ml-2">Event: {{ $events->find(request('event_id'))->name ?? 'Unknown' }}</span>
                    @endif
                    <span class="ml-2">({{ $surveys->total() }} results)</span>
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
                
                            <!-- Pagination Row -->
            <div class="mt-6 flex flex-col sm:flex-row sm:items-center sm:justify-between">
                <div class="mb-2 sm:mb-0 text-xs text-gray-500">
                    Showing {{ $surveys->firstItem() ?? 0 }} to {{ $surveys->lastItem() ?? 0 }} of {{ $surveys->total() }} entries
                    @if($surveys->total() > 0)
                        ({{ request('per_page', 10) }} per page)
                    @endif
                </div>
                <div class="flex justify-end">
                    {{ $surveys->appends(request()->query())->links('components.pagination-modern') }}
                </div>
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

    <script>
        // Debounce search input
        let searchTimeout;
        const searchInput = document.getElementById('searchInput');
        
        if (searchInput) {
            searchInput.addEventListener('input', function() {
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(() => {
                    this.form.submit();
                }, 500); // Wait 500ms after user stops typing
            });
        }
        
        // Copy to Clipboard JavaScript
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
