<x-app-layout>
    <x-slot name="breadcrumb">
        <span>Event Management</span>
    </x-slot>

    <x-slot name="title">Event Management</x-slot>

    <div class="bg-white rounded shadow-md border border-gray-300">
        <div class="p-6 border-b border-gray-200">
            <div class="flex justify-between items-start">
                <div>
                    <div class="flex items-center">
                        <span class="material-icons mr-2 text-primary-DEFAULT">event</span>
                        <h1 class="text-xl font-bold text-gray-800">Event Management</h1>
                    </div>
                    <p class="text-xs text-gray-500 mt-1 ml-8">Manage all events and activities</p>
                </div>
                @can('events.create')
                <a href="{{ route('event.create') }}" class="bg-gradient-to-r from-blue-600 to-blue-500 hover:from-blue-700 hover:to-blue-600 text-white px-3 h-[36px] rounded shadow-sm font-medium flex items-center text-xs transition-colors duration-200 ease-in-out">
                    <span class="material-icons text-xs mr-1">add_circle</span>
                    Create New Event
                </a>
                @endcan
            </div>
        </div>
        
        <div class="p-4">
            <!-- Search & Filter Row -->
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-4 gap-2">
                <!-- Search & Filter Form -->
                <form method="GET" action="{{ route('event.management') }}" class="flex flex-wrap gap-2 items-center justify-between w-full">
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
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Search event name, organizer, location..." class="border border-gray-300 rounded px-2 py-1 text-xs focus:ring focus:ring-primary-light focus:border-primary-light" id="searchInput" />
                        <select name="status" onchange="this.form.submit()" class="appearance-none px-3 py-1.5 pr-8 text-xs border border-gray-300 rounded focus:ring focus:ring-primary-light focus:border-primary-light bg-white bg-no-repeat bg-right w-[120px]" style="background-image: url('data:image/svg+xml;charset=US-ASCII,<svg xmlns=%22http://www.w3.org/2000/svg%22 width=%2216%22 height=%2216%22 viewBox=%220 0 24 24%22 fill=%22none%22 stroke=%22%23888%22 stroke-width=%222%22 stroke-linecap=%22round%22 stroke-linejoin=%22round%22><polyline points=%226 9 12 15 18 9%22></polyline></svg>'); background-position: right 0.75rem center; background-size: 1em;">
                            <option value="">All Status</option>
                            <option value="active" @if(request('status') == 'active') selected @endif>Active</option>
                            <option value="pending" @if(request('status') == 'pending') selected @endif>Pending</option>
                            <option value="completed" @if(request('status') == 'completed') selected @endif>Completed</option>
                        </select>
                        <select name="date_filter" onchange="this.form.submit()" class="appearance-none px-3 py-1.5 pr-8 text-xs border border-gray-300 rounded focus:ring focus:ring-primary-light focus:border-primary-light bg-white bg-no-repeat bg-right w-[120px]" style="background-image: url('data:image/svg+xml;charset=US-ASCII,<svg xmlns=%22http://www.w3.org/2000/svg%22 width=%2216%22 height=%2216%22 viewBox=%220 0 24 24%22 fill=%22none%22 stroke=%22%23888%22 stroke-width=%222%22 stroke-linecap=%22round%22 stroke-linejoin=%22round%22><polyline points=%226 9 12 15 18 9%22></polyline></svg>'); background-position: right 0.75rem center; background-size: 1em;">
                            <option value="">All Dates</option>
                            <option value="today" @if(request('date_filter') == 'today') selected @endif>Today</option>
                            <option value="week" @if(request('date_filter') == 'week') selected @endif>This Week</option>
                            <option value="month" @if(request('date_filter') == 'month') selected @endif>This Month</option>
                            <option value="past" @if(request('date_filter') == 'past') selected @endif>Past Events</option>
                        </select>
                        <button type="submit" class="bg-primary-light text-white px-3 py-1 h-[36px] rounded text-xs font-medium flex items-center justify-center" title="Search">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4-4m0 0A7 7 0 104 4a7 7 0 0013 13z" />
                            </svg>
                        </button>
                        @if(request('search') || request('status') || request('date_filter'))
                            <a href="{{ route('event.management') }}?per_page={{ request('per_page', 10) }}" class="text-xs text-gray-500 underline ml-2">Reset</a>
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
            @if(request('search') || request('status') || request('date_filter'))
                <div class="bg-blue-50 border border-blue-200 text-blue-700 px-4 py-2 rounded mb-4 text-xs">
                    <span class="font-medium">Search Results:</span>
                    @if(request('search'))
                        <span class="ml-2">Searching for "{{ request('search') }}"</span>
                    @endif
                    @if(request('status'))
                        <span class="ml-2">Status: {{ ucfirst(request('status')) }}</span>
                    @endif
                    @if(request('date_filter'))
                        <span class="ml-2">Date: {{ ucfirst(str_replace('_', ' ', request('date_filter'))) }}</span>
                    @endif
                    <span class="ml-2">({{ $events->total() }} results)</span>
                </div>
            @endif
            
            <!-- Events Table -->
            <div class="overflow-visible border border-gray-200 rounded">
                <table class="min-w-full border-collapse">
                    <thead>
                        <tr class="bg-primary-light text-white text-xs uppercase">
                            <th class="py-3 px-4 text-left rounded-tl">Event Name</th>
                            <th class="py-3 px-4 text-left">Organizer</th>
                            <th class="py-3 px-4 text-left">Start Date</th>
                            <th class="py-3 px-4 text-left">End Date</th>
                            <th class="py-3 px-4 text-left">Location</th>
                            <th class="py-3 px-4 text-left">Participants</th>
                            <th class="py-3 px-4 text-left">Status</th>
                            <th class="py-3 px-4 text-center rounded-tr">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($events as $event)
                            <tr class="text-xs hover:bg-gray-50">
                                <td class="py-3 px-4 font-medium">{{ $event->name }}</td>
                                <td class="py-3 px-4">{{ $event->organizer }}</td>
                                <td class="py-3 px-4">{{ \Carbon\Carbon::parse($event->start_date)->format('d M Y') }} {{ $event->start_time ? '- ' . substr($event->start_time, 0, 5) : '' }}</td>
                                <td class="py-3 px-4">{{ \Carbon\Carbon::parse($event->end_date)->format('d M Y') }} {{ $event->end_time ? '- ' . substr($event->end_time, 0, 5) : '' }}</td>
                                <td class="py-3 px-4">{{ $event->location }}</td>
                                <td class="py-3 px-4">{{ $event->participants->count() ?? 0 }}</td>
                                <td class="py-3 px-4">
                                    @if($event->status === 'active')
                                        <span class="bg-status-active-bg text-status-active-text px-2 py-1 rounded-full text-xs">Active</span>
                                    @elseif($event->status === 'pending')
                                        <span class="bg-status-pending-bg text-status-pending-text px-2 py-1 rounded-full text-xs">Pending</span>
                                    @elseif($event->status === 'completed')
                                        <span class="bg-status-completed-bg text-status-completed-text px-2 py-1 rounded-full text-xs">Completed</span>
                                    @endif
                                </td>
                                <td class="py-3 px-4">
                                    <div class="flex justify-center space-x-2">
                                        <!-- Registration Options Dropdown -->
                                        <div class="relative" x-data="{ registrationDropdownOpen{{ $event->id }}: false }">
                                            <button @click="registrationDropdownOpen{{ $event->id }} = !registrationDropdownOpen{{ $event->id }}" class="p-1 bg-purple-50 rounded hover:bg-purple-100 border border-purple-100" title="Registration Options">
                                                <span class="material-icons text-purple-600 text-xs">format_list_bulleted</span>
                                            </button>
                                            <div x-show="registrationDropdownOpen{{ $event->id }}" @click.outside="registrationDropdownOpen{{ $event->id }} = false" class="absolute right-0 mt-2 z-50 w-48 bg-white rounded-md shadow-lg">
                                                <div class="py-1 border border-gray-200 rounded-md">
                                                    <button @click="copyRegistrationLink('{{ route('event.register', ['token' => $event->registration_link]) }}')" class="flex items-center w-full text-left px-4 py-2 text-xs text-gray-700 hover:bg-gray-100">
                                                        <span class="material-icons text-blue-600 text-xs mr-2">link</span>
                                                        Copy Registration Link
                                                    </button>
                                                    <a href="{{ route('event.qrcode-image', $event->id) }}" class="flex items-center w-full text-left px-4 py-2 text-xs text-gray-700 hover:bg-gray-100">
                                                        <span class="material-icons text-indigo-600 text-xs mr-2">qr_code</span>
                                                        Download QR Code
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <a href="{{ route('event.show', $event->id) }}" class="p-1 bg-blue-50 rounded hover:bg-blue-100 border border-blue-100" title="View">
                                            <span class="material-icons text-blue-600 text-xs">visibility</span>
                                        </a>
                                        @can('events.update')
                                        <a href="{{ route('event.edit', $event->id) }}" class="p-1 bg-yellow-50 rounded hover:bg-yellow-100 border border-yellow-100" title="Edit">
                                            <span class="material-icons text-yellow-700 text-xs">edit</span>
                                        </a>
                                        @endcan
                                        @can('events.delete')
                                        <form method="POST" action="{{ route('event.destroy', $event->id) }}" onsubmit="return confirm('Are you sure you want to delete this event?')" class="inline-block">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="p-1 bg-red-50 rounded hover:bg-red-100 border border-red-100" title="Delete">
                                                <span class="material-icons text-red-600 text-xs">delete</span>
                                            </button>
                                        </form>
                                        @endcan
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
                    Showing {{ $events->firstItem() ?? 0 }} to {{ $events->lastItem() ?? 0 }} of {{ $events->total() }} entries
                    @if($events->total() > 0)
                        ({{ request('per_page', 10) }} per page)
                    @endif
                </div>
                <div class="flex justify-end">
                    {{ $events->appends(request()->query())->links('components.pagination-modern') }}
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

<!-- Copy to Clipboard JavaScript -->
<script>
function copyRegistrationLink(url) {
    navigator.clipboard.writeText(url)
        .then(() => {
            // Show alert atau notification
            alert('Registration link copied to clipboard!');
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
            alert('Registration link copied to clipboard!');
        });
}
</script>
</x-app-layout> 