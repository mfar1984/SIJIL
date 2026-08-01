<x-app-layout>
    <x-slot name="breadcrumb">
        <span>Participants</span>
    </x-slot>

    <x-slot name="title">Participants</x-slot>

    <div class="bg-white rounded shadow-md border border-gray-300" x-data="{ activeTab: '{{ $activeTab ?? 'verified' }}' }">
        <div class="p-6 border-b border-gray-200">
            <div class="flex justify-between items-start">
                <div>
                    <div class="flex items-center">
                        <span class="material-icons-outlined mr-2 text-primary-DEFAULT">groups</span>
                        <h1 class="text-xl font-bold text-gray-800">Participants</h1>
                    </div>
                    <p class="text-xs text-gray-500 mt-1 ml-8">Manage all participants and attendees</p>
                </div>
                <div class="flex gap-2">
                    @can('participants.read')
                    <a href="{{ route('participants.export', request()->query()) }}" class="bg-gradient-to-r from-green-600 to-green-500 hover:from-green-700 hover:to-green-600 text-white px-3 h-[36px] rounded shadow-sm font-medium flex items-center text-xs transition-colors duration-200 ease-in-out">
                        <span class="material-icons-outlined text-xs mr-1">download</span>
                        Export Excel
                    </a>
                    @endcan
                    @can('participants.create')
                    <a href="{{ route('participants.create') }}" class="bg-gradient-to-r from-blue-600 to-blue-500 hover:from-blue-700 hover:to-blue-600 text-white px-3 h-[36px] rounded shadow-sm font-medium flex items-center text-xs transition-colors duration-200 ease-in-out">
                        <span class="material-icons-outlined text-xs mr-1">add_circle</span>
                        Add New Participant
                    </a>
                    @endcan
                </div>
            </div>
        </div>
        
        <!-- Tab Navigation -->
        {{-- Tab styling mirrors settings/global-config.blade.php so both pages match --}}
        <div class="border-b border-gray-200 px-4">
            <div class="flex flex-wrap -mb-px">
                <a href="{{ route('participants', array_merge(request()->except('tab'), ['tab' => 'verified'])) }}" 
                   :class="activeTab === 'verified' ? 'border-primary-DEFAULT text-primary-DEFAULT' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                   class="inline-flex items-center whitespace-nowrap py-3 px-4 text-xs font-medium leading-5 border-b-2 focus:outline-none transition duration-150 ease-in-out">
                    <span class="material-icons-outlined text-xs mr-2">verified_user</span>
                    Verified Participants
                </a>
                <a href="{{ route('participants', array_merge(request()->except('tab'), ['tab' => 'simplified'])) }}" 
                   :class="activeTab === 'simplified' ? 'border-primary-DEFAULT text-primary-DEFAULT' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                   class="inline-flex items-center whitespace-nowrap py-3 px-4 text-xs font-medium leading-5 border-b-2 focus:outline-none transition duration-150 ease-in-out">
                    <span class="material-icons-outlined text-xs mr-2">flash_on</span>
                    Quick Registration
                </a>
            </div>
        </div>
        
        <div class="p-4">
            {{-- Search takes the remaining space; the filters keep their own width. --}}
            <form method="GET" action="{{ route('participants') }}" class="flex flex-wrap items-center gap-2 mb-4">
                <input type="hidden" name="tab" value="{{ $activeTab ?? 'verified' }}">

                <input type="text" name="search" id="searchInput" value="{{ request('search') }}"
                       placeholder="Search name, email, phone, IC..."
                       class="flex-1 min-w-[12rem] h-9 text-xs border-gray-300 rounded px-3 focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50">

                <select name="event" onchange="this.form.submit()"
                        class="h-9 text-xs border-gray-300 rounded pl-3 pr-8 w-[13rem] shrink-0 focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50">
                    <option value="">All Events</option>
                    @foreach($events as $event)
                        <option value="{{ $event->id }}" @if(request('event') == $event->id) selected @endif>{{ $event->name }}</option>
                    @endforeach
                </select>

                <select name="status" onchange="this.form.submit()"
                        class="h-9 text-xs border-gray-300 rounded pl-3 pr-8 w-[9rem] shrink-0 focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50">
                    <option value="">All Status</option>
                    <option value="active" @if(request('status') == 'active') selected @endif>Active</option>
                    <option value="inactive" @if(request('status') == 'inactive') selected @endif>Inactive</option>
                </select>

                <button type="submit"
                        class="h-9 px-3 bg-primary-DEFAULT hover:bg-primary-dark text-white rounded text-xs flex items-center shrink-0 transition-colors duration-200 ease-in-out" title="Search">
                    <span class="material-icons-outlined text-xs">search</span>
                </button>

                @if(request('search') || request('event') || request('status'))
                    <a href="{{ route('participants', ['tab' => $activeTab ?? 'verified']) }}" class="text-xs text-gray-500 underline shrink-0">Reset</a>
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
                                {{-- The IC column used to render organization, so the header
                                     and the values underneath it described different things.
                                     A participant carries an IC or a passport, never both, so
                                     the passport stands in when there is no IC. --}}
                                <td class="py-3 px-4">
                                    {{ $participant->identity_card ?: $participant->passport_no }}
                                </td>
                                <td class="py-3 px-4">{{ $participant->event?->name }}</td>
                                <td class="py-3 px-4">
                                    @if($participant->status === 'active')
                                        <span class="bg-status-active-bg text-status-active-text px-2 py-1 rounded-full text-xs">Active</span>
                                    @elseif($participant->status === 'inactive')
                                        <span class="bg-status-pending-bg text-status-pending-text px-2 py-1 rounded-full text-xs">Inactive</span>
                                    @endif
                                </td>
                                <td class="py-3 px-4">
                                    <div class="flex justify-center space-x-2">
                                        @can('participants.read')
                                        <a href="{{ route('participants.show', $participant->id) }}" class="p-1 bg-blue-50 rounded hover:bg-blue-100 border border-blue-100" title="View">
                                            <span class="material-icons-outlined text-primary-DEFAULT text-xs">visibility</span>
                                        </a>
                                        @endcan
                                        @can('participants.update')
                                        <a href="{{ route('participants.edit', $participant->id) }}" class="p-1 bg-yellow-50 rounded hover:bg-yellow-100 border border-yellow-100" title="Edit">
                                            <span class="material-icons-outlined text-yellow-600 text-xs">edit</span>
                                        </a>
                                        @endcan
                                        @can('participants.delete')
                                        <form method="POST" action="{{ route('participants.destroy', $participant->id) }}" onsubmit="return confirm('Are you sure you want to delete this participant?')" class="inline-block">
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
                    Showing {{ $participants->firstItem() ?? 0 }} to {{ $participants->lastItem() ?? 0 }} of {{ $participants->total() }} entries
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