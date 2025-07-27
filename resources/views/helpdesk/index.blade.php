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
                    <button @click="showModal = true" class="bg-gradient-to-r from-blue-600 to-blue-500 hover:from-blue-700 hover:to-blue-600 text-white px-3 py-1 rounded shadow-sm font-medium flex items-center text-xs transition-colors duration-200 ease-in-out">
                        <span class="material-icons text-xs mr-1">add_circle</span>
                        Create New Ticket
                    </button>
                    
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
            
            <!-- Filters -->
            <div class="mb-4 grid grid-cols-1 md:grid-cols-4 gap-4">
                <div>
                    <label for="search" class="block text-xs font-medium text-gray-700 mb-1">Search</label>
                    <input type="text" id="search" name="search" class="w-full rounded-md border-gray-300 shadow-sm focus:border-primary-DEFAULT focus:ring focus:ring-primary-DEFAULT focus:ring-opacity-50 text-sm" placeholder="Search by ticket ID or subject">
                </div>
                
                <div>
                    <label for="priority" class="block text-xs font-medium text-gray-700 mb-1">Priority</label>
                    <select id="priority" name="priority" class="w-full rounded-md border-gray-300 shadow-sm focus:border-primary-DEFAULT focus:ring focus:ring-primary-DEFAULT focus:ring-opacity-50 text-sm">
                        <option value="">All Priorities</option>
                        <option value="low">Low</option>
                        <option value="medium">Medium</option>
                        <option value="high">High</option>
                        <option value="urgent">Urgent</option>
                    </select>
                </div>
                
                <div>
                    <label for="category" class="block text-xs font-medium text-gray-700 mb-1">Category</label>
                    <select id="category" name="category" class="w-full rounded-md border-gray-300 shadow-sm focus:border-primary-DEFAULT focus:ring focus:ring-primary-DEFAULT focus:ring-opacity-50 text-sm">
                        <option value="">All Categories</option>
                        <option value="technical">Technical Issue</option>
                        <option value="billing">Billing</option>
                        <option value="event">Event Management</option>
                        <option value="account">Account Access</option>
                    </select>
                </div>
                
                <div class="flex items-end">
                    <button type="button" class="bg-primary-DEFAULT hover:bg-primary-dark text-white px-4 py-2 rounded-md text-xs flex items-center">
                        <span class="material-icons text-xs mr-1">filter_list</span>
                        Apply Filter
                    </button>
                    <button type="button" class="ml-2 bg-white border border-gray-300 text-gray-700 hover:bg-gray-50 px-4 py-2 rounded-md text-xs flex items-center">
                        <span class="material-icons text-xs mr-1">refresh</span>
                        Reset
                    </button>
                </div>
            </div>
            
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
                                        @if($ticket->status != 'closed')
                                        <a href="{{ route('helpdesk.show', $ticket->id) }}#reply-form" class="p-1 bg-green-50 rounded hover:bg-green-100 border border-green-100" title="Reply to Ticket">
                                            <span class="material-icons text-green-600 text-xs">reply</span>
                                        </a>
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
                                        @if($ticket->status != 'closed')
                                        <a href="{{ route('helpdesk.show', $ticket->id) }}#reply-form" class="p-1 bg-green-50 rounded hover:bg-green-100 border border-green-100" title="Reply to Ticket">
                                            <span class="material-icons text-green-600 text-xs">reply</span>
                                        </a>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
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
                                        @if($ticket->status != 'closed')
                                        <a href="{{ route('helpdesk.show', $ticket->id) }}#reply-form" class="p-1 bg-green-50 rounded hover:bg-green-100 border border-green-100" title="Reply to Ticket">
                                            <span class="material-icons text-green-600 text-xs">reply</span>
                                        </a>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
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
                                        @if($ticket->status != 'closed')
                                        <a href="{{ route('helpdesk.show', $ticket->id) }}#reply-form" class="p-1 bg-green-50 rounded hover:bg-green-100 border border-green-100" title="Reply to Ticket">
                                            <span class="material-icons text-green-600 text-xs">reply</span>
                                        </a>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            
            <!-- Pagination -->
            <div class="mt-6 flex flex-col sm:flex-row sm:items-center sm:justify-between">
                <div class="mb-2 sm:mb-0 text-xs text-gray-500">
                    Showing <span class="font-medium">1</span> to <span class="font-medium">4</span> of <span class="font-medium">16</span> tickets
                </div>
                <div class="flex justify-end">
                    <div class="flex items-center space-x-1">
                        <a href="#" class="px-2 py-1 text-gray-500 hover:text-primary-DEFAULT rounded-none text-xs opacity-50 cursor-not-allowed">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M15.707 15.707a1 1 0 01-1.414 0l-5-5a1 1 0 010-1.414l5-5a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 010 1.414zm-6 0a1 1 0 01-1.414 0l-5-5a1 1 0 010-1.414l5-5a1 1 0 011.414 1.414L5.414 10l4.293 4.293a1 1 0 010 1.414z" clip-rule="evenodd" /></svg>
                        </a>
                        <a href="#" class="px-2 py-1 text-gray-500 hover:text-primary-DEFAULT rounded-none text-xs mr-2 opacity-50 cursor-not-allowed">
                            <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd" /></svg>
                        </a>
                        <span class="w-6 h-6 flex items-center justify-center bg-primary-light text-white rounded-full shadow-sm text-xs font-medium">1</span>
                        <a href="#" class="px-2 py-1 text-gray-600 hover:text-primary-DEFAULT rounded-none text-xs font-medium">2</a>
                        <a href="#" class="px-2 py-1 text-gray-600 hover:text-primary-DEFAULT rounded-none text-xs font-medium">3</a>
                        <a href="#" class="px-2 py-1 text-gray-600 hover:text-primary-DEFAULT rounded-none text-xs font-medium">4</a>
                        <a href="#" class="px-2 py-1 text-gray-500 hover:text-primary-DEFAULT rounded-none text-xs ml-2">
                            <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" /></svg>
                        </a>
                        <a href="#" class="px-2 py-1 text-gray-500 hover:text-primary-DEFAULT rounded-none text-xs">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0l5 5a1 1 0 010 1.414l-5 5a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414zM10 4.293a1 1 0 011.414 0l5 5a1 1 0 010 1.414l-5 5a1 1 0 01-1.414-1.414L14.586 10l-4.293-4.293a1 1 0 010-1.414z" clip-rule="evenodd" /></svg>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Realtime Updates -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Check if Echo is loaded
            if (window.Echo) {
                try {
                    // Connect to Pusher
                    const userId = {{ Auth::id() }};
                    
                    // Listen for new tickets (admin only)
                    @if($isAdmin)
                    window.Echo.private('helpdesk.admin')
                        .listen('NewTicketCreated', (e) => {
                            console.log('New ticket created:', e);
                            
                            // Show notification
                            const notification = document.createElement('div');
                            notification.className = 'fixed top-4 right-4 bg-green-100 border-l-4 border-green-500 text-green-700 p-4 rounded shadow-md z-50';
                            notification.innerHTML = `
                                <div class="flex items-center">
                                    <span class="material-icons text-green-500 mr-2">notifications</span>
                                    <div>
                                        <p class="font-bold">New Ticket Created</p>
                                        <p class="text-sm">${e.ticket.ticket_id}: ${e.ticket.subject}</p>
                                    </div>
                                </div>
                            `;
                            document.body.appendChild(notification);
                            
                            // Remove notification after 5 seconds
                            setTimeout(() => {
                                notification.remove();
                            }, 5000);
                            
                            // Reload the page after a short delay
                            setTimeout(() => {
                                location.reload();
                            }, 2000);
                        });
                    @endif
                    
                    // Listen for status updates on user's tickets
                    window.Echo.private(`helpdesk.user.${userId}`)
                        .listen('TicketStatusUpdated', (e) => {
                            console.log('Ticket status updated:', e);
                            
                            // Show notification
                            const notification = document.createElement('div');
                            notification.className = 'fixed top-4 right-4 bg-blue-100 border-l-4 border-blue-500 text-blue-700 p-4 rounded shadow-md z-50';
                            notification.innerHTML = `
                                <div class="flex items-center">
                                    <span class="material-icons text-blue-500 mr-2">update</span>
                                    <div>
                                        <p class="font-bold">Ticket Status Updated</p>
                                        <p class="text-sm">${e.ticket.ticket_id}: Status changed to ${e.ticket.status}</p>
                                    </div>
                                </div>
                            `;
                            document.body.appendChild(notification);
                            
                            // Remove notification after 5 seconds
                            setTimeout(() => {
                                notification.remove();
                            }, 5000);
                            
                            // Reload the page after a short delay
                            setTimeout(() => {
                                location.reload();
                            }, 2000);
                        });
                } catch (error) {
                    console.error('Error setting up Echo listeners:', error);
                }
            }
        });
    </script>
</x-app-layout> 