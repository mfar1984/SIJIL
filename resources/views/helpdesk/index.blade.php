<x-app-layout>
    <x-slot name="breadcrumb">
        <span>Helpdesk</span>
    </x-slot>

    <x-slot name="title">Helpdesk</x-slot>
    
    <!-- Make sure Alpine.js is loaded -->
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <div class="bg-white rounded shadow-md border border-gray-300">
        <div class="p-6 border-b border-gray-200">
            <div class="flex justify-between items-start">
                <div>
                    <div class="flex items-center">
                        <span class="material-icons mr-2 text-primary-DEFAULT">help</span>
                        <h1 class="text-xl font-bold text-gray-800">Helpdesk</h1>
                    </div>
                    <p class="text-xs text-gray-500 mt-1 ml-8">Communication platform between Administrators and Organizers</p>
                </div>
                <div x-data="{ showModal: false }">
                    <!-- Trigger button -->
                    @can('helpdesk.create')
                    <button @click="showModal = true" class="bg-gradient-to-r from-blue-600 to-blue-500 hover:from-blue-700 hover:to-blue-600 text-white px-3 h-[36px] rounded shadow-sm font-medium flex items-center text-xs transition-colors duration-200 ease-in-out">
                        <span class="material-icons text-xs mr-1">add_circle</span>
                        Create New Ticket
                    </button>
                    @endcan
                    
                    <div
                        x-show="showModal"
                        x-transition:enter="transition ease-out duration-300"
                        x-transition:enter-start="opacity-0"
                        x-transition:enter-end="opacity-100"
                        x-transition:leave="transition ease-in duration-200"
                        x-transition:leave-start="opacity-100"
                        x-transition:leave-end="opacity-0"
                        class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50"
                        style="display: none;"
                    >
                        <div class="bg-white rounded-lg shadow-xl w-full max-w-2xl mx-4 overflow-hidden" @click.away="showModal = false">
                            <div class="px-6 py-4 bg-primary-light text-white flex items-center justify-between">
                                <h3 class="text-lg font-medium">Create New Ticket</h3>
                                <button @click="showModal = false" class="text-white hover:text-gray-200">
                                    <span class="material-icons">close</span>
                                </button>
                            </div>
                            @can('helpdesk.create')
                            <form action="{{ route('helpdesk.store') }}" method="POST" enctype="multipart/form-data">
                                @csrf
                                <div class="p-6">
                                    <div class="grid grid-cols-1 gap-4">
                                        <div>
                                            <label for="subject" class="block text-xs font-medium text-gray-700 mb-1">Subject</label>
                                            <input type="text" id="subject" name="subject" class="w-full rounded-md border-gray-300 shadow-sm focus:border-primary-DEFAULT focus:ring focus:ring-primary-DEFAULT focus:ring-opacity-50 text-sm" placeholder="Brief description of the issue" required>
                                        </div>
                                        
                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                            <div>
                                                <label for="category" class="block text-xs font-medium text-gray-700 mb-1">Category</label>
                                                <select id="category" name="category" class="w-full rounded-md border-gray-300 shadow-sm focus:border-primary-DEFAULT focus:ring focus:ring-primary-DEFAULT focus:ring-opacity-50 text-sm" required>
                                                    <option value="technical">Technical Issue</option>
                                                    <option value="billing">Billing</option>
                                                    <option value="event">Event Management</option>
                                                    <option value="account">Account Access</option>
                                                    <option value="other">Other</option>
                                                </select>
                                            </div>
                                            
                                            <div>
                                                <label for="priority" class="block text-xs font-medium text-gray-700 mb-1">Priority</label>
                                                <select id="priority" name="priority" class="w-full rounded-md border-gray-300 shadow-sm focus:border-primary-DEFAULT focus:ring focus:ring-primary-DEFAULT focus:ring-opacity-50 text-sm" required>
                                                    <option value="low">Low</option>
                                                    <option value="medium">Medium</option>
                                                    <option value="high">High</option>
                                                    <option value="urgent">Urgent</option>
                                                </select>
                                            </div>
                                        </div>
                                        
                                        <div>
                                            <label for="message" class="block text-xs font-medium text-gray-700 mb-1">Message</label>
                                            <textarea id="message" name="message" rows="5" class="w-full rounded-md border-gray-300 shadow-sm focus:border-primary-DEFAULT focus:ring focus:ring-primary-DEFAULT focus:ring-opacity-50 text-sm" placeholder="Describe your issue in detail" required></textarea>
                                        </div>
                                        
                                        <div>
                                            <label for="attachments" class="block text-xs font-medium text-gray-700 mb-1">Attachments (Optional)</label>
                                            <input type="file" id="attachments" name="attachments[]" multiple class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded file:border-0 file:text-xs file:font-semibold file:bg-blue-50 file:text-primary-DEFAULT hover:file:bg-blue-100">
                                        </div>
                                    </div>
                                </div>
                                <div class="px-6 py-4 bg-gray-50 border-t border-gray-200 flex justify-end">
                                    <button @click="showModal = false" type="button" class="bg-white border border-gray-300 text-gray-700 hover:bg-gray-50 px-4 py-2 rounded-md text-xs mr-2">
                                        Cancel
                                    </button>
                                    <button type="submit" class="bg-primary-DEFAULT hover:bg-primary-dark text-white px-4 py-2 rounded-md text-xs">
                                        Submit Ticket
                                    </button>
                                </div>
                            </form>
                            @endcan
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="p-4" x-data="{ activeTab: 'all' }">
            <!-- Tab Navigation -->
            <div class="border-b border-gray-200 mb-4">
                <div class="flex flex-wrap -mb-px">
                    <button 
                        @click="activeTab = 'all'" 
                        :class="{'border-primary-DEFAULT text-primary-DEFAULT': activeTab === 'all', 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300': activeTab !== 'all'}"
                        class="inline-flex items-center py-3 px-4 text-xs font-medium leading-5 border-b-2 focus:outline-none transition duration-150 ease-in-out"
                    >
                        <span class="material-icons text-xs mr-2">list</span>
                        All Tickets
                    </button>
                    <button 
                        @click="activeTab = 'open'" 
                        :class="{'border-primary-DEFAULT text-primary-DEFAULT': activeTab === 'open', 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300': activeTab !== 'open'}"
                        class="inline-flex items-center py-3 px-4 text-xs font-medium leading-5 border-b-2 focus:outline-none transition duration-150 ease-in-out"
                    >
                        <span class="material-icons text-xs mr-2">fiber_new</span>
                        Open
                        <span class="ml-1 bg-blue-100 text-blue-800 text-xs font-semibold px-2 py-0.5 rounded-full">{{ $openCount }}</span>
                    </button>
                    <button 
                        @click="activeTab = 'inProgress'" 
                        :class="{'border-primary-DEFAULT text-primary-DEFAULT': activeTab === 'inProgress', 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300': activeTab !== 'inProgress'}"
                        class="inline-flex items-center py-3 px-4 text-xs font-medium leading-5 border-b-2 focus:outline-none transition duration-150 ease-in-out"
                    >
                        <span class="material-icons text-xs mr-2">pending_actions</span>
                        In Progress
                        <span class="ml-1 bg-amber-100 text-amber-800 text-xs font-semibold px-2 py-0.5 rounded-full">{{ $inProgressCount }}</span>
                    </button>
                    <button 
                        @click="activeTab = 'resolved'" 
                        :class="{'border-primary-DEFAULT text-primary-DEFAULT': activeTab === 'resolved', 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300': activeTab !== 'resolved'}"
                        class="inline-flex items-center py-3 px-4 text-xs font-medium leading-5 border-b-2 focus:outline-none transition duration-150 ease-in-out"
                    >
                        <span class="material-icons text-xs mr-2">task_alt</span>
                        Resolved
                        <span class="ml-1 bg-green-100 text-green-800 text-xs font-semibold px-2 py-0.5 rounded-full">{{ $resolvedCount }}</span>
                    </button>
                </div>
            </div>
            
            <!-- Show Entries & Filter Row -->
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-4 gap-2">
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
                <form method="GET" action="{{ route('helpdesk.index') }}" class="flex flex-wrap gap-2 items-center">
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Search ticket ID, subject..." class="border border-gray-300 rounded px-2 py-1 text-xs focus:ring focus:ring-primary-light focus:border-primary-light" id="searchInput" />
                    <select name="priority" onchange="this.form.submit()" class="appearance-none px-3 py-1.5 pr-8 text-xs border border-gray-300 rounded focus:ring focus:ring-primary-light focus:border-primary-light bg-white bg-no-repeat bg-right w-[120px]" style="background-image: url('data:image/svg+xml;charset=US-ASCII,<svg xmlns=%22http://www.w3.org/2000/svg%22 width=%2216%22 height=%2216%22 viewBox=%220 0 24 24%22 fill=%22none%22 stroke=%22%23888%22 stroke-width=%222%22 stroke-linecap=%22round%22 stroke-linejoin=%22round%22><polyline points=%226 9 12 15 18 9%22></polyline></svg>'); background-position: right 0.75rem center; background-size: 1em;">
                        <option value="">All Priorities</option>
                        <option value="low" @if(request('priority') == 'low') selected @endif>Low</option>
                        <option value="medium" @if(request('priority') == 'medium') selected @endif>Medium</option>
                        <option value="high" @if(request('priority') == 'high') selected @endif>High</option>
                        <option value="urgent" @if(request('priority') == 'urgent') selected @endif>Urgent</option>
                    </select>
                    <select name="category" onchange="this.form.submit()" class="appearance-none px-3 py-1.5 pr-8 text-xs border border-gray-300 rounded focus:ring focus:ring-primary-light focus:border-primary-light bg-white bg-no-repeat bg-right w-[140px]" style="background-image: url('data:image/svg+xml;charset=US-ASCII,<svg xmlns=%22http://www.w3.org/2000/svg%22 width=%2216%22 height=%2216%22 viewBox=%220 0 24 24%22 fill=%22none%22 stroke=%22%23888%22 stroke-width=%222%22 stroke-linecap=%22round%22 stroke-linejoin=%22round%22><polyline points=%226 9 12 15 18 9%22></polyline></svg>'); background-position: right 0.75rem center; background-size: 1em;">
                        <option value="">All Categories</option>
                        <option value="technical" @if(request('category') == 'technical') selected @endif>Technical Issue</option>
                        <option value="billing" @if(request('category') == 'billing') selected @endif>Billing</option>
                        <option value="event" @if(request('category') == 'event') selected @endif>Event Management</option>
                        <option value="account" @if(request('category') == 'account') selected @endif>Account Access</option>
                        <option value="other" @if(request('category') == 'other') selected @endif>Other</option>
                    </select>
                    <select name="status" onchange="this.form.submit()" class="appearance-none px-3 py-1.5 pr-8 text-xs border border-gray-300 rounded focus:ring focus:ring-primary-light focus:border-primary-light bg-white bg-no-repeat bg-right w-[120px]" style="background-image: url('data:image/svg+xml;charset=US-ASCII,<svg xmlns=%22http://www.w3.org/2000/svg%22 width=%2216%22 height=%2216%22 viewBox=%220 0 24 24%22 fill=%22none%22 stroke=%22%23888%22 stroke-width=%222%22 stroke-linecap=%22round%22 stroke-linejoin=%22round%22><polyline points=%226 9 12 15 18 9%22></polyline></svg>'); background-position: right 0.75rem center; background-size: 1em;">
                        <option value="">All Status</option>
                        <option value="open" @if(request('status') == 'open') selected @endif>Open</option>
                        <option value="in_progress" @if(request('status') == 'in_progress') selected @endif>In Progress</option>
                        <option value="resolved" @if(request('status') == 'resolved') selected @endif>Resolved</option>
                        <option value="closed" @if(request('status') == 'closed') selected @endif>Closed</option>
                    </select>
                    <button type="submit" class="bg-primary-light text-white px-3 py-1 h-[36px] rounded text-xs font-medium flex items-center justify-center" title="Search">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4-4m0 0A7 7 0 104 4a7 7 0 0013 13z" />
                        </svg>
                    </button>
                    @if(request('search') || request('priority') || request('category') || request('status'))
                        <a href="{{ route('helpdesk.index') }}?per_page={{ request('per_page', 10) }}" class="text-xs text-gray-500 underline ml-2">Reset</a>
                    @endif
                </form>
            </div>
            
            <!-- Search Results Summary -->
            @if(request('search') || request('priority') || request('category') || request('status'))
                <div class="bg-blue-50 border border-blue-200 text-blue-700 px-4 py-2 rounded mb-4 text-xs">
                    <span class="font-medium">Search Results:</span>
                    @if(request('search'))
                        <span class="ml-2">Searching for "{{ request('search') }}"</span>
                    @endif
                    @if(request('priority'))
                        <span class="ml-2">Priority: {{ ucfirst(request('priority')) }}</span>
                    @endif
                    @if(request('category'))
                        <span class="ml-2">Category: {{ ucfirst(request('category')) }}</span>
                    @endif
                    @if(request('status'))
                        <span class="ml-2">Status: {{ ucfirst(str_replace('_', ' ', request('status'))) }}</span>
                    @endif
                    <span class="ml-2">({{ $tickets->total() }} results)</span>
                </div>
            @endif
            
            <!-- Tickets Table: All Tickets -->
            <div x-show="activeTab === 'all'">
                <div class="overflow-visible border border-gray-200 rounded">
                    <table class="min-w-full border-collapse">
                        <thead>
                            <tr class="bg-primary-light text-white text-xs uppercase">
                                <th class="py-3 px-4 text-left rounded-tl">TICKET ID</th>
                                <th class="py-3 px-4 text-left">SUBJECT</th>
                                <th class="py-3 px-4 text-left">CATEGORY</th>
                                <th class="py-3 px-4 text-left">DATE</th>
                                <th class="py-3 px-4 text-left">STATUS</th>
                                <th class="py-3 px-4 text-left">PRIORITY</th>
                                <th class="py-3 px-4 text-left">LAST UPDATED</th>
                                <th class="py-3 px-4 text-center rounded-tr">ACTIONS</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach($tickets as $ticket)
                            <tr class="text-xs hover:bg-gray-50">
                                <td class="py-3 px-4 font-medium">
                                    <a href="{{ route('helpdesk.show', $ticket->id) }}" class="text-blue-600 hover:underline">
                                        {{ $ticket->ticket_id }}
                                    </a>
                                </td>
                                <td class="py-3 px-4">
                                    <a href="{{ route('helpdesk.show', $ticket->id) }}" class="text-gray-800 hover:underline">
                                        {{ $ticket->subject }}
                                    </a>
                                </td>
                                <td class="py-3 px-4">{{ ucfirst($ticket->category) }}</td>
                                <td class="py-3 px-4">{{ $ticket->created_at->format('d M Y') }}</td>
                                <td class="py-3 px-4">
                                    @if($ticket->status == 'open')
                                    <span class="px-2 py-1 bg-blue-100 text-blue-800 rounded-full text-xs">Open</span>
                                    @elseif($ticket->status == 'in_progress')
                                    <span class="px-2 py-1 bg-yellow-100 text-yellow-800 rounded-full text-xs">In Progress</span>
                                    @elseif($ticket->status == 'resolved')
                                    <span class="px-2 py-1 bg-green-100 text-green-800 rounded-full text-xs">Resolved</span>
                                    @else
                                    <span class="px-2 py-1 bg-gray-100 text-gray-800 rounded-full text-xs">Closed</span>
                                    @endif
                                </td>
                                <td class="py-3 px-4">{{ ucfirst($ticket->priority) }}</td>
                                <td class="py-3 px-4">{{ $ticket->updated_at->format('d M Y H:i') }}</td>
                                <td class="py-3 px-4">
                                    <div class="flex justify-center space-x-2">
                                        <a href="{{ route('helpdesk.show', $ticket->id) }}" class="p-1 bg-blue-50 rounded hover:bg-blue-100 border border-blue-100" title="View Ticket">
                                            <span class="material-icons text-primary-DEFAULT text-xs">visibility</span>
                                        </a>
                                        @can('helpdesk.delete')
                                        @if($isAdmin)
                                        <form action="{{ route('helpdesk.delete', $ticket->id) }}" method="POST" class="inline" onsubmit="return confirm('Delete this ticket?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="p-1 bg-red-50 rounded hover:bg-red-100 border border-red-100" title="Delete Ticket">
                                                <span class="material-icons text-red-600 text-xs">delete</span>
                                            </button>
                                        </form>
                                        @endif
                                        @endcan
                                        @if($ticket->status != 'closed')
                                        @can('helpdesk.update')
                                        <a href="{{ route('helpdesk.show', $ticket->id) }}#reply-form" class="p-1 bg-green-50 rounded hover:bg-green-100 border border-green-100" title="Reply to Ticket">
                                            <span class="material-icons text-green-600 text-xs">reply</span>
                                        </a>
                                        @endcan
                                        @endif
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            <!-- Tickets Table: Open -->
            <div x-show="activeTab === 'open'">
                <div class="overflow-visible border border-gray-200 rounded">
                    <table class="min-w-full border-collapse">
                        <thead>
                            <tr class="bg-primary-light text-white text-xs uppercase">
                                <th class="py-3 px-4 text-left rounded-tl">TICKET ID</th>
                                <th class="py-3 px-4 text-left">SUBJECT</th>
                                <th class="py-3 px-4 text-left">CATEGORY</th>
                                <th class="py-3 px-4 text-left">DATE</th>
                                <th class="py-3 px-4 text-left">STATUS</th>
                                <th class="py-3 px-4 text-left">PRIORITY</th>
                                <th class="py-3 px-4 text-left">LAST UPDATED</th>
                                <th class="py-3 px-4 text-center rounded-tr">ACTIONS</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach($openTickets as $ticket)
                            <tr class="text-xs hover:bg-gray-50">
                                <td class="py-3 px-4 font-medium">
                                    <a href="{{ route('helpdesk.show', $ticket->id) }}" class="text-blue-600 hover:underline">
                                        {{ $ticket->ticket_id }}
                                    </a>
                                </td>
                                <td class="py-3 px-4">
                                    <a href="{{ route('helpdesk.show', $ticket->id) }}" class="text-gray-800 hover:underline">
                                        {{ $ticket->subject }}
                                    </a>
                                </td>
                                <td class="py-3 px-4">{{ ucfirst($ticket->category) }}</td>
                                <td class="py-3 px-4">{{ $ticket->created_at->format('d M Y') }}</td>
                                <td class="py-3 px-4">
                                    <span class="px-2 py-1 bg-blue-100 text-blue-800 rounded-full text-xs">Open</span>
                                </td>
                                <td class="py-3 px-4">{{ ucfirst($ticket->priority) }}</td>
                                <td class="py-3 px-4">{{ $ticket->updated_at->format('d M Y H:i') }}</td>
                                <td class="py-3 px-4">
                                    <div class="flex justify-center space-x-2">
                                        <a href="{{ route('helpdesk.show', $ticket->id) }}" class="p-1 bg-blue-50 rounded hover:bg-blue-100 border border-blue-100" title="View Ticket">
                                            <span class="material-icons text-primary-DEFAULT text-xs">visibility</span>
                                        </a>
                                        @can('helpdesk.delete')
                                        @if($isAdmin)
                                        <form action="{{ route('helpdesk.delete', $ticket->id) }}" method="POST" class="inline" onsubmit="return confirm('Delete this ticket?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="p-1 bg-red-50 rounded hover:bg-red-100 border border-red-100" title="Delete Ticket">
                                                <span class="material-icons text-red-600 text-xs">delete</span>
                                            </button>
                                        </form>
                                        @endif
                                        @endcan
                                        @if($ticket->status != 'closed')
                                        @can('helpdesk.update')
                                        <a href="{{ route('helpdesk.show', $ticket->id) }}#reply-form" class="p-1 bg-green-50 rounded hover:bg-green-100 border border-green-100" title="Reply to Ticket">
                                            <span class="material-icons text-green-600 text-xs">reply</span>
                                        </a>
                                        @endcan
                                        @endif
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                
                <!-- Pagination for Open Tickets -->
                <div class="mt-6 flex flex-col sm:flex-row sm:items-center sm:justify-between">
                    <div class="mb-2 sm:mb-0 text-xs text-gray-500">
                        @if($openTickets->total() > 0)
                            Showing <span class="font-medium">{{ $openTickets->firstItem() }}</span> to <span class="font-medium">{{ $openTickets->lastItem() }}</span> of <span class="font-medium">{{ $openTickets->total() }}</span> tickets
                        @else
                            Showing <span class="font-medium">0</span> to <span class="font-medium">0</span> of <span class="font-medium">0</span> tickets
                        @endif
                    </div>
                    <div class="flex justify-end">
                        {{ $openTickets->appends(request()->query())->links('components.pagination-modern') }}
                    </div>
                </div>
            </div>
            <!-- Tickets Table: In Progress -->
            <div x-show="activeTab === 'inProgress'">
                <div class="overflow-visible border border-gray-200 rounded">
                    <table class="min-w-full border-collapse">
                        <thead>
                            <tr class="bg-primary-light text-white text-xs uppercase">
                                <th class="py-3 px-4 text-left rounded-tl">TICKET ID</th>
                                <th class="py-3 px-4 text-left">SUBJECT</th>
                                <th class="py-3 px-4 text-left">CATEGORY</th>
                                <th class="py-3 px-4 text-left">DATE</th>
                                <th class="py-3 px-4 text-left">STATUS</th>
                                <th class="py-3 px-4 text-left">PRIORITY</th>
                                <th class="py-3 px-4 text-left">LAST UPDATED</th>
                                <th class="py-3 px-4 text-center rounded-tr">ACTIONS</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach($inProgressTickets as $ticket)
                            <tr class="text-xs hover:bg-gray-50">
                                <td class="py-3 px-4 font-medium">
                                    <a href="{{ route('helpdesk.show', $ticket->id) }}" class="text-blue-600 hover:underline">
                                        {{ $ticket->ticket_id }}
                                    </a>
                                </td>
                                <td class="py-3 px-4">
                                    <a href="{{ route('helpdesk.show', $ticket->id) }}" class="text-gray-800 hover:underline">
                                        {{ $ticket->subject }}
                                    </a>
                                </td>
                                <td class="py-3 px-4">{{ ucfirst($ticket->category) }}</td>
                                <td class="py-3 px-4">{{ $ticket->created_at->format('d M Y') }}</td>
                                <td class="py-3 px-4">
                                    <span class="px-2 py-1 bg-yellow-100 text-yellow-800 rounded-full text-xs">In Progress</span>
                                </td>
                                <td class="py-3 px-4">{{ ucfirst($ticket->priority) }}</td>
                                <td class="py-3 px-4">{{ $ticket->updated_at->format('d M Y H:i') }}</td>
                                <td class="py-3 px-4">
                                    <div class="flex justify-center space-x-2">
                                        <a href="{{ route('helpdesk.show', $ticket->id) }}" class="p-1 bg-blue-50 rounded hover:bg-blue-100 border border-blue-100" title="View Ticket">
                                            <span class="material-icons text-primary-DEFAULT text-xs">visibility</span>
                                        </a>
                                        @can('helpdesk.delete')
                                        @if($isAdmin)
                                        <form action="{{ route('helpdesk.delete', $ticket->id) }}" method="POST" class="inline" onsubmit="return confirm('Delete this ticket?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="p-1 bg-red-50 rounded hover:bg-red-100 border border-red-100" title="Delete Ticket">
                                                <span class="material-icons text-red-600 text-xs">delete</span>
                                            </button>
                                        </form>
                                        @endif
                                        @endcan
                                        @if($ticket->status != 'closed')
                                        @can('helpdesk.update')
                                        <a href="{{ route('helpdesk.show', $ticket->id) }}#reply-form" class="p-1 bg-green-50 rounded hover:bg-green-100 border border-green-100" title="Reply to Ticket">
                                            <span class="material-icons text-green-600 text-xs">reply</span>
                                        </a>
                                        @endcan
                                        @endif
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                
                <!-- Pagination for In Progress Tickets -->
                <div class="mt-6 flex flex-col sm:flex-row sm:items-center sm:justify-between">
                    <div class="mb-2 sm:mb-0 text-xs text-gray-500">
                        @if($inProgressTickets->total() > 0)
                            Showing <span class="font-medium">{{ $inProgressTickets->firstItem() }}</span> to <span class="font-medium">{{ $inProgressTickets->lastItem() }}</span> of <span class="font-medium">{{ $inProgressTickets->total() }}</span> tickets
                        @else
                            Showing <span class="font-medium">0</span> to <span class="font-medium">0</span> of <span class="font-medium">0</span> tickets
                        @endif
                    </div>
                    <div class="flex justify-end">
                        {{ $inProgressTickets->appends(request()->query())->links('components.pagination-modern') }}
                    </div>
                </div>
            </div>
            <!-- Tickets Table: Resolved -->
            <div x-show="activeTab === 'resolved'">
                <div class="overflow-visible border border-gray-200 rounded">
                    <table class="min-w-full border-collapse">
                        <thead>
                            <tr class="bg-primary-light text-white text-xs uppercase">
                                <th class="py-3 px-4 text-left rounded-tl">TICKET ID</th>
                                <th class="py-3 px-4 text-left">SUBJECT</th>
                                <th class="py-3 px-4 text-left">CATEGORY</th>
                                <th class="py-3 px-4 text-left">DATE</th>
                                <th class="py-3 px-4 text-left">STATUS</th>
                                <th class="py-3 px-4 text-left">PRIORITY</th>
                                <th class="py-3 px-4 text-left">LAST UPDATED</th>
                                <th class="py-3 px-4 text-center rounded-tr">ACTIONS</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach($resolvedTickets as $ticket)
                            <tr class="text-xs hover:bg-gray-50">
                                <td class="py-3 px-4 font-medium">
                                    <a href="{{ route('helpdesk.show', $ticket->id) }}" class="text-blue-600 hover:underline">
                                        {{ $ticket->ticket_id }}
                                    </a>
                                </td>
                                <td class="py-3 px-4">
                                    <a href="{{ route('helpdesk.show', $ticket->id) }}" class="text-gray-800 hover:underline">
                                        {{ $ticket->subject }}
                                    </a>
                                </td>
                                <td class="py-3 px-4">{{ ucfirst($ticket->category) }}</td>
                                <td class="py-3 px-4">{{ $ticket->created_at->format('d M Y') }}</td>
                                <td class="py-3 px-4">
                                    <span class="px-2 py-1 bg-green-100 text-green-800 rounded-full text-xs">Resolved</span>
                                </td>
                                <td class="py-3 px-4">{{ ucfirst($ticket->priority) }}</td>
                                <td class="py-3 px-4">{{ $ticket->updated_at->format('d M Y H:i') }}</td>
                                <td class="py-3 px-4">
                                    <div class="flex justify-center space-x-2">
                                        <a href="{{ route('helpdesk.show', $ticket->id) }}" class="p-1 bg-blue-50 rounded hover:bg-blue-100 border border-blue-100" title="View Ticket">
                                            <span class="material-icons text-primary-DEFAULT text-xs">visibility</span>
                                        </a>
                                        @can('helpdesk.delete')
                                        @if($isAdmin)
                                        <form action="{{ route('helpdesk.delete', $ticket->id) }}" method="POST" class="inline" onsubmit="return confirm('Delete this ticket?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="p-1 bg-red-50 rounded hover:bg-red-100 border border-red-100" title="Delete Ticket">
                                                <span class="material-icons text-red-600 text-xs">delete</span>
                                            </button>
                                        </form>
                                        @endif
                                        @endcan
                                        @if($ticket->status != 'closed')
                                        @can('helpdesk.update')
                                        <a href="{{ route('helpdesk.show', $ticket->id) }}#reply-form" class="p-1 bg-green-50 rounded hover:bg-green-100 border border-green-100" title="Reply to Ticket">
                                            <span class="material-icons text-green-600 text-xs">reply</span>
                                        </a>
                                        @endcan
                                        @endif
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                
                <!-- Pagination for Resolved Tickets -->
                <div class="mt-6 flex flex-col sm:flex-row sm:items-center sm:justify-between">
                    <div class="mb-2 sm:mb-0 text-xs text-gray-500">
                        @if($resolvedTickets->total() > 0)
                            Showing <span class="font-medium">{{ $resolvedTickets->firstItem() }}</span> to <span class="font-medium">{{ $resolvedTickets->lastItem() }}</span> of <span class="font-medium">{{ $resolvedTickets->total() }}</span> tickets
                        @else
                            Showing <span class="font-medium">0</span> to <span class="font-medium">0</span> of <span class="font-medium">0</span> tickets
                        @endif
                    </div>
                    <div class="flex justify-end">
                        {{ $resolvedTickets->appends(request()->query())->links('components.pagination-modern') }}
                    </div>
                </div>
            </div>
            
            <!-- Pagination for All Tickets -->
            <div class="mt-6 flex flex-col sm:flex-row sm:items-center sm:justify-between">
                <div class="mb-2 sm:mb-0 text-xs text-gray-500">
                    @if($tickets->total() > 0)
                        Showing <span class="font-medium">{{ $tickets->firstItem() }}</span> to <span class="font-medium">{{ $tickets->lastItem() }}</span> of <span class="font-medium">{{ $tickets->total() }}</span> tickets
                    @else
                        Showing <span class="font-medium">0</span> to <span class="font-medium">0</span> of <span class="font-medium">0</span> tickets
                    @endif
                </div>
                <div class="flex justify-end">
                    {{-- Using Laravel's built-in pagination links --}}
                    {{ $tickets->appends(request()->query())->links('components.pagination-modern') }}
                </div>
            </div>
        </div>
    </div>
    
    <!-- Realtime Updates handled by FCM in resources/js/fcm.js -->
</x-app-layout> 