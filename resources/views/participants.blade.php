<x-app-layout>
    <x-slot name="breadcrumb">
        <span>Participants</span>
    </x-slot>

    <x-slot name="title">Participants</x-slot>

    <div class="bg-white rounded shadow-md border border-gray-300">
        <div class="p-6 border-b border-gray-200">
            <div class="flex justify-between items-start">
                <div>
                    <div class="flex items-center">
                        <span class="material-icons mr-2 text-primary-DEFAULT">groups</span>
                        <h1 class="text-xl font-bold text-gray-800">Participants</h1>
                    </div>
                    <p class="text-xs text-gray-500 mt-1 ml-8">Manage all participants and attendees</p>
                </div>
                <a href="{{ route('participants.create') }}" class="bg-gradient-to-r from-blue-600 to-blue-500 hover:from-blue-700 hover:to-blue-600 text-white px-3 py-1 rounded shadow-sm font-medium flex items-center text-xs transition-colors duration-200 ease-in-out">
                    <span class="material-icons text-xs mr-1">add_circle</span>
                    Add New Participant
                </a>
            </div>
        </div>
        
        <div class="p-4">
            <!-- Search & Filter Row -->
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-4 gap-2">
                <!-- Search & Filter Form -->
                <form method="GET" action="{{ route('participants') }}" class="flex flex-wrap gap-2 items-center justify-between w-full">
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
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Search name, email, phone, IC..." class="border border-gray-300 rounded px-2 py-1 text-xs focus:ring focus:ring-primary-light focus:border-primary-light" id="searchInput" />
                    <select name="event" onchange="this.form.submit()" class="appearance-none px-3 py-1.5 pr-8 text-xs border border-gray-300 rounded focus:ring focus:ring-primary-light focus:border-primary-light bg-white bg-no-repeat bg-right max-w-[200px] truncate" style="background-image: url('data:image/svg+xml;charset=US-ASCII,<svg xmlns=%22http://www.w3.org/2000/svg%22 width=%2216%22 height=%2216%22 viewBox=%220 0 24 24%22 fill=%22none%22 stroke=%22%23888%22 stroke-width=%222%22 stroke-linecap=%22round%22 stroke-linejoin=%22round%22><polyline points=%226 9 12 15 18 9%22></polyline></svg>'); background-position: right 0.75rem center; background-size: 1em;">
                        <option value="">All Events</option>
                        @foreach($events as $event)
                            <option value="{{ $event->id }}" @if(request('event') == $event->id) selected @endif class="truncate">{{ $event->name }}</option>
                        @endforeach
                    </select>
                    <select name="status" onchange="this.form.submit()" class="appearance-none px-3 py-1.5 pr-8 text-xs border border-gray-300 rounded focus:ring focus:ring-primary-light focus:border-primary-light bg-white bg-no-repeat bg-right w-[120px]" style="background-image: url('data:image/svg+xml;charset=US-ASCII,<svg xmlns=%22http://www.w3.org/2000/svg%22 width=%2216%22 height=%2216%22 viewBox=%220 0 24 24%22 fill=%22none%22 stroke=%22%23888%22 stroke-width=%222%22 stroke-linecap=%22round%22 stroke-linejoin=%22round%22><polyline points=%226 9 12 15 18 9%22></polyline></svg>'); background-position: right 0.75rem center; background-size: 1em;">
                        <option value="">All Status</option>
                        <option value="active" @if(request('status') == 'active') selected @endif>Active</option>
                        <option value="inactive" @if(request('status') == 'inactive') selected @endif>Inactive</option>
                    </select>
                    <button type="submit" class="bg-primary-light text-white px-3 py-1 h-[38px] rounded text-xs font-medium flex items-center justify-center" title="Search">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4-4m0 0A7 7 0 104 4a7 7 0 0013 13z" />
                        </svg>
                    </button>
                    @if(request('search') || request('event') || request('status'))
                        <a href="{{ route('participants') }}?per_page={{ request('per_page', 10) }}" class="text-xs text-gray-500 underline ml-2">Reset</a>
                    @endif
                    </div>
                </form>
            </div>
            <!-- Display success/error messages -->
            @if(session('success'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4 text-xs">
                    {{ session('success') }}
                </div>
            @endif
            @if(session('error'))
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4 text-xs">
                    {{ session('error') }}
                </div>
            @endif
            
            <!-- Search Results Summary -->
            @if(request('search') || request('event') || request('status'))
                <div class="bg-blue-50 border border-blue-200 text-blue-700 px-4 py-2 rounded mb-4 text-xs">
                    <span class="font-medium">Search Results:</span>
                    @if(request('search'))
                        <span class="ml-2">Searching for "{{ request('search') }}"</span>
                    @endif
                    @if(request('event'))
                        <span class="ml-2">Event: {{ $events->find(request('event'))->name ?? 'Unknown' }}</span>
                    @endif
                    @if(request('status'))
                        <span class="ml-2">Status: {{ ucfirst(request('status')) }}</span>
                    @endif
                    <span class="ml-2">({{ $participants->total() }} results)</span>
                </div>
            @endif
            
            <!-- Participants Table -->
            <div class="overflow-visible border border-gray-200 rounded">
                <table class="min-w-full border-collapse">
                    <thead>
                        <tr class="bg-primary-light text-white text-xs uppercase">
                            <th class="py-3 px-4 text-left rounded-tl">Name</th>
                            <th class="py-3 px-4 text-left">Email</th>
                            <th class="py-3 px-4 text-left">Phone</th>
                            <th class="py-3 px-4 text-left">IC</th>
                            <th class="py-3 px-4 text-left">Event</th>
                            <th class="py-3 px-4 text-left">Status</th>
                            <th class="py-3 px-4 text-center rounded-tr">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($participants as $participant)
                            <tr class="text-xs hover:bg-gray-50">
                                <td class="py-3 px-4 font-medium">{{ $participant->name }}</td>
                                <td class="py-3 px-4">{{ $participant->email }}</td>
                                <td class="py-3 px-4">{{ $participant->phone }}</td>
                                <td class="py-3 px-4">{{ $participant->organization }}</td>
                                <td class="py-3 px-4">{{ $participant->event->name }}</td>
                                <td class="py-3 px-4">
                                    @if($participant->status === 'active')
                                        <span class="bg-status-active-bg text-status-active-text px-2 py-1 rounded-full text-xs">Active</span>
                                    @elseif($participant->status === 'inactive')
                                        <span class="bg-status-pending-bg text-status-pending-text px-2 py-1 rounded-full text-xs">Inactive</span>
                                    @endif
                                </td>
                                <td class="py-3 px-4">
                                    <div class="flex justify-center space-x-2">
                                        <a href="{{ route('participants.show', $participant->id) }}" class="p-1 bg-blue-50 rounded hover:bg-blue-100 border border-blue-100" title="View">
                                            <span class="material-icons text-primary-DEFAULT text-xs">visibility</span>
                                        </a>
                                        <a href="{{ route('participants.edit', $participant->id) }}" class="p-1 bg-yellow-50 rounded hover:bg-yellow-100 border border-yellow-100" title="Edit">
                                            <span class="material-icons text-yellow-600 text-xs">edit</span>
                                        </a>
                                        <form method="POST" action="{{ route('participants.destroy', $participant->id) }}" onsubmit="return confirm('Are you sure you want to delete this participant?')" class="inline-block">
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
                    Showing {{ $participants->firstItem() ?? 0 }} to {{ $participants->lastItem() ?? 0 }} of {{ $participants->total() }} entries
                    @if($participants->total() > 0)
                        ({{ request('per_page', 10) }} per page)
                    @endif
                </div>
                <div class="flex justify-end">
                    {{ $participants->appends(request()->query())->links('components.pagination-modern') }}
                </div>
            </div>
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
    </script>
</x-app-layout> 