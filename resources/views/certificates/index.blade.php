<x-app-layout>
    <x-slot name="breadcrumb">
        <span>Certificate</span>
        <span class="mx-2 text-gray-500">/</span>
        <span>Manage Certificates</span>
    </x-slot>

    <x-slot name="title">Manage Certificates</x-slot>

    <div class="bg-white rounded shadow-md border border-gray-300" x-data="{ activeTab: '{{ $activeTab ?? 'verified' }}' }">
        <div class="p-6 border-b border-gray-200">
            <div class="flex justify-between items-start">
                <div>
                    <div class="flex items-center">
                        <span class="material-icons-outlined mr-2 text-primary-DEFAULT">workspace_premium</span>
                        <h1 class="text-xl font-bold text-gray-800">Manage Certificates</h1>
                    </div>
                    <p class="text-xs text-gray-500 mt-1 ml-8">View and generate certificates for participants</p>
                </div>
                @can('certificates.create')
                <a href="{{ route('certificates.create') }}" class="bg-gradient-to-r from-blue-600 to-blue-500 hover:from-blue-700 hover:to-blue-600 text-white px-3 h-[36px] rounded shadow-sm font-medium flex items-center text-xs transition-colors duration-200 ease-in-out">
                    <span class="material-icons-outlined text-xs mr-1">add_circle</span>
                    Generate Certificates
                </a>
                @endcan
            </div>
        </div>
        
        {{-- Tab styling mirrors participants.blade.php --}}
        <div class="border-b border-gray-200 px-4">
            <div class="flex flex-wrap -mb-px">
                <a href="{{ route('certificates.index', array_merge(request()->except('tab'), ['tab' => 'verified'])) }}" 
                   :class="activeTab === 'verified' ? 'border-primary-DEFAULT text-primary-DEFAULT' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                   class="inline-flex items-center whitespace-nowrap py-3 px-4 text-xs font-medium leading-5 border-b-2 focus:outline-none transition duration-150 ease-in-out">
                    <span class="material-icons-outlined text-xs mr-2">verified_user</span>
                    Verified Certificates
                </a>
                <a href="{{ route('certificates.index', array_merge(request()->except('tab'), ['tab' => 'simplified'])) }}" 
                   :class="activeTab === 'simplified' ? 'border-primary-DEFAULT text-primary-DEFAULT' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                   class="inline-flex items-center whitespace-nowrap py-3 px-4 text-xs font-medium leading-5 border-b-2 focus:outline-none transition duration-150 ease-in-out">
                    <span class="material-icons-outlined text-xs mr-2">flash_on</span>
                    Quick Registration Certificates
                </a>
            </div>
        </div>
        
        <div class="p-4">
            {{-- Search takes the remaining space; the filters keep their own width. --}}
            <form method="GET" action="{{ route('certificates.index') }}" class="flex flex-wrap items-center gap-2 mb-4">
                <input type="hidden" name="tab" value="{{ $activeTab ?? 'verified' }}">

                <input type="text" name="search" id="searchInput" value="{{ request('search') }}"
                       placeholder="Search participant name, email, event..."
                       class="flex-1 min-w-[12rem] h-9 text-xs border-gray-300 rounded px-3 focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50">

                <select name="event_id" onchange="this.form.submit()"
                        class="h-9 text-xs border-gray-300 rounded pl-3 pr-8 w-[13rem] shrink-0 focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50">
                    <option value="">All Events</option>
                    @foreach($events as $event)
                        <option value="{{ $event->id }}" @if(request('event_id') == $event->id) selected @endif>{{ $event->name }}</option>
                    @endforeach
                </select>

                <select name="template_id" onchange="this.form.submit()"
                        class="h-9 text-xs border-gray-300 rounded pl-3 pr-8 w-[13rem] shrink-0 focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50">
                    <option value="">All Templates</option>
                    @foreach($templates as $template)
                        <option value="{{ $template->id }}" @if(request('template_id') == $template->id) selected @endif>{{ $template->name }}</option>
                    @endforeach
                </select>

                <select name="date_filter" onchange="this.form.submit()"
                        class="h-9 text-xs border-gray-300 rounded pl-3 pr-8 w-[9rem] shrink-0 focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50">
                    <option value="">All Dates</option>
                    <option value="today" @if(request('date_filter') == 'today') selected @endif>Today</option>
                    <option value="week" @if(request('date_filter') == 'week') selected @endif>This Week</option>
                    <option value="month" @if(request('date_filter') == 'month') selected @endif>This Month</option>
                    <option value="past" @if(request('date_filter') == 'past') selected @endif>Past</option>
                </select>

                <button type="submit"
                        class="h-9 px-3 bg-primary-DEFAULT hover:bg-primary-dark text-white rounded text-xs flex items-center shrink-0 transition-colors duration-200 ease-in-out" title="Search">
                    <span class="material-icons-outlined text-xs">search</span>
                </button>

                @if(request('search') || request('event_id') || request('template_id') || request('date_filter'))
                    <a href="{{ route('certificates.index', ['tab' => $activeTab ?? 'verified']) }}" class="text-xs text-gray-500 underline shrink-0">Reset</a>
                @endif
            </form>

            <!-- Success Message -->
            @if(session('success'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4 text-xs">
                    {{ session('success') }}
                </div>
            @endif

            <!-- Error Message -->
            @if(session('error'))
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4 text-xs">
                    {{ session('error') }}
                </div>
            @endif
            
            <!-- Search Results Summary -->
            @if(request('search') || request('event_id') || request('template_id') || request('date_filter'))
                <div class="bg-blue-50 border border-blue-200 text-blue-700 px-4 py-2 rounded mb-4 text-xs">
                    <span class="font-medium">Search Results:</span>
                    @if(request('search'))
                        <span class="ml-2">Searching for "{{ request('search') }}"</span>
                    @endif
                    @if(request('event_id'))
                        <span class="ml-2">Event: {{ $events->find(request('event_id'))->name ?? 'Unknown' }}</span>
                    @endif
                    @if(request('template_id'))
                        <span class="ml-2">Template: {{ $templates->find(request('template_id'))->name ?? 'Unknown' }}</span>
                    @endif
                    @if(request('date_filter'))
                        <span class="ml-2">Date: {{ ucfirst(str_replace('_', ' ', request('date_filter'))) }}</span>
                    @endif
                    <span class="ml-2">({{ $certificates->total() }} results)</span>
                </div>
            @endif
            
            <!-- Certificates Table -->
            <div class="overflow-visible border border-gray-200 rounded">
                <table class="min-w-full border-collapse">
                    <thead>
                        <tr class="bg-primary-light text-white text-xs uppercase">
                            <th class="py-3 px-4 text-left rounded-tl">Certificate #</th>
                            <th class="py-3 px-4 text-left">Event</th>
                            <th class="py-3 px-4 text-left">Participant</th>
                            <th class="py-3 px-4 text-left">Generated</th>
                            <th class="py-3 px-4 text-center rounded-tr">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($certificates ?? [] as $certificate)
                            <tr class="text-xs hover:bg-gray-50">
                                <td class="py-3 px-4 font-medium">{{ $certificate->certificate_number }}</td>
                                {{-- The event or participant may be sitting in the Recycle Bin,
                                     so never assume the relation is present. --}}
                                <td class="py-3 px-4" style="max-width: 200px; overflow-wrap: break-word; word-wrap: break-word; hyphens: auto;">
                                    {{ $certificate->event->name ?? '—' }}
                                    @if($certificate->event && $certificate->event->trashed())
                                        <span class="ml-1 px-1.5 py-0.5 text-[10px] bg-gray-100 text-gray-600 rounded">In Recycle Bin</span>
                                    @endif
                                </td>
                                <td class="py-3 px-4">
                                    {{ $certificate->participant->name ?? '—' }}
                                    @if($certificate->participant && $certificate->participant->trashed())
                                        <span class="ml-1 px-1.5 py-0.5 text-[10px] bg-gray-100 text-gray-600 rounded">In Recycle Bin</span>
                                    @endif
                                </td>
                                <td class="py-3 px-4">{{ $certificate->generated_at?->format('d M Y, H:i') ?? '—' }}</td>
                                <td class="py-3 px-4">
                                    <div class="flex justify-center space-x-2">
                                        <a href="{{ asset('storage/' . $certificate->pdf_file) }}" target="_blank" class="p-1 bg-blue-50 rounded hover:bg-blue-100 border border-blue-100" title="View">
                                            <span class="material-icons-outlined text-primary-DEFAULT text-xs">visibility</span>
                                        </a>
                                        <a href="{{ asset('storage/' . $certificate->pdf_file) }}" download class="p-1 bg-green-50 rounded hover:bg-green-100 border border-green-100" title="Download">
                                            <span class="material-icons-outlined text-green-600 text-xs">download</span>
                                        </a>
                                        @can('certificates.delete')
                                        <form method="POST" action="{{ route('certificates.destroy', $certificate->id) }}" onsubmit="return confirm('Are you sure you want to delete this certificate?')" class="inline-block">
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
                        @empty
                            <tr class="text-xs">
                                <td colspan="5" class="py-8 text-center text-gray-500">
                                    No certificates found.
                                    @can('certificates.create')
                                        <a href="{{ route('certificates.create') }}" class="text-primary-DEFAULT hover:underline">Generate certificates</a>
                                    @endcan
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination Row -->
            <div class="mt-6 flex flex-col sm:flex-row sm:justify-between">
                <div class="mb-2 sm:mb-0 text-xs text-gray-500">
                    Showing {{ $certificates->firstItem() ?? 0 }} to {{ $certificates->lastItem() ?? 0 }} of {{ $certificates->total() }} entries
                </div>
                <div class="flex justify-end">
                    {{ $certificates->appends(request()->query())->links('components.pagination-modern') }}
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