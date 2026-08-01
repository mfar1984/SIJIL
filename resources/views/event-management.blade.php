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
                        <span class="material-icons-outlined mr-2 text-primary-DEFAULT">event</span>
                        <h1 class="text-xl font-bold text-gray-800">Event Management</h1>
                    </div>
                    <p class="text-xs text-gray-500 mt-1 ml-8">Manage all events and activities</p>
                </div>
                @can('events.create')
                <a href="{{ route('event.create') }}" class="bg-gradient-to-r from-blue-600 to-blue-500 hover:from-blue-700 hover:to-blue-600 text-white px-3 h-[36px] rounded shadow-sm font-medium flex items-center text-xs transition-colors duration-200 ease-in-out">
                    <span class="material-icons-outlined text-xs mr-1">add_circle</span>
                    Create New Event
                </a>
                @endcan
            </div>
        </div>
        
        <div class="p-4">
            {{-- Search takes the remaining space; the filters keep their own width. --}}
            <form method="GET" action="{{ route('event.management') }}" class="flex flex-wrap items-center gap-2 mb-4">
                <input type="text" name="search" id="searchInput" value="{{ request('search') }}"
                       placeholder="Search event name, organizer, location..."
                       class="flex-1 min-w-[12rem] h-9 text-xs border-gray-300 rounded px-3 focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50">

                <select name="status" onchange="this.form.submit()"
                        class="h-9 text-xs border-gray-300 rounded pl-3 pr-8 w-[9rem] shrink-0 focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50">
                    <option value="">All Status</option>
                    <option value="active" @if(request('status') == 'active') selected @endif>Active</option>
                    <option value="pending" @if(request('status') == 'pending') selected @endif>Pending</option>
                    <option value="completed" @if(request('status') == 'completed') selected @endif>Completed</option>
                </select>

                <select name="date_filter" onchange="this.form.submit()"
                        class="h-9 text-xs border-gray-300 rounded pl-3 pr-8 w-[9rem] shrink-0 focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50">
                    <option value="">All Dates</option>
                    <option value="today" @if(request('date_filter') == 'today') selected @endif>Today</option>
                    <option value="week" @if(request('date_filter') == 'week') selected @endif>This Week</option>
                    <option value="month" @if(request('date_filter') == 'month') selected @endif>This Month</option>
                    <option value="past" @if(request('date_filter') == 'past') selected @endif>Past Events</option>
                </select>

                <button type="submit"
                        class="h-9 px-3 bg-primary-DEFAULT hover:bg-primary-dark text-white rounded text-xs flex items-center shrink-0 transition-colors duration-200 ease-in-out" title="Search">
                    <span class="material-icons-outlined text-xs">search</span>
                </button>

                @if(request('search') || request('status') || request('date_filter'))
                    <a href="{{ route('event.management') }}" class="text-xs text-gray-500 underline shrink-0">Reset</a>
                @endif
            </form>
            
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
                                <td class="py-3 px-4">{{ $event->participants_count }}</td>
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
                                                <span class="material-icons-outlined text-purple-600 text-xs">format_list_bulleted</span>
                                            </button>
                                            <div x-show="registrationDropdownOpen{{ $event->id }}" @click.outside="registrationDropdownOpen{{ $event->id }} = false" class="absolute right-0 mt-2 z-50 w-48 bg-white rounded-md shadow-lg">
                                                <div class="py-1 border border-gray-200 rounded-md">
                                                    <button @click="copyRegistrationLink('{{ route('event.register', ['token' => $event->registration_link]) }}')" class="flex items-center w-full text-left px-4 py-2 text-xs text-gray-700 hover:bg-gray-100">
                                                        <span class="material-icons-outlined text-blue-600 text-xs mr-2">link</span>
                                                        Copy Registration Link
                                                    </button>
                                                    <a href="{{ route('event.qrcode-image', $event->id) }}" class="flex items-center w-full text-left px-4 py-2 text-xs text-gray-700 hover:bg-gray-100">
                                                        <span class="material-icons-outlined text-indigo-600 text-xs mr-2">qr_code</span>
                                                        Download QR Code
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <a href="{{ route('event.show', $event->id) }}" class="p-1 bg-blue-50 rounded hover:bg-blue-100 border border-blue-100" title="View">
                                            <span class="material-icons-outlined text-blue-600 text-xs">visibility</span>
                                        </a>
                                        @can('events.update')
                                        <a href="{{ route('event.edit', $event->id) }}" class="p-1 bg-yellow-50 rounded hover:bg-yellow-100 border border-yellow-100" title="Edit">
                                            <span class="material-icons-outlined text-yellow-700 text-xs">edit</span>
                                        </a>
                                        @endcan
                                        @can('events.delete')
                                        <form method="POST" action="{{ route('event.destroy', $event->id) }}" onsubmit="return confirm('Are you sure you want to delete this event?')" class="inline-block">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="p-1 bg-red-50 rounded hover:bg-red-100 border border-red-100" title="Delete">
                                                <span class="material-icons-outlined text-red-600 text-xs">delete</span>
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

<script>
    // window.copyWithFeedback lives in resources/js/app.js. It checks that the
    // clipboard API is actually available before reaching for it, which the code
    // here did not: over plain HTTP navigator.clipboard is undefined, so this
    // button threw before any promise existed and the .catch() fallback was
    // unreachable. Nothing happened and nothing was reported.
    function copyRegistrationLink(url) {
        window.copyWithFeedback(url, 'Registration link copied to clipboard.');
    }
</script>
</x-app-layout> 