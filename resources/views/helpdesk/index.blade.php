<x-app-layout>
    <x-slot name="breadcrumb">
        <span>Helpdesk</span>
    </x-slot>

    <x-slot name="title">Helpdesk</x-slot>
    
    {{-- Alpine already comes from the bundle via the layout. Loading a second copy
         here made this page run two instances against the same DOM. --}}

    <div class="bg-white rounded shadow-md border border-gray-300">
        <div class="p-6 border-b border-gray-200">
            <div class="flex justify-between items-start">
                <div>
                    <div class="flex items-center">
                        <span class="material-icons-outlined mr-2 text-primary-DEFAULT">help</span>
                        <h1 class="text-xl font-bold text-gray-800">Helpdesk</h1>
                    </div>
                    <p class="text-xs text-gray-500 mt-1 ml-8">Communication platform between Administrators and Organizers</p>
                </div>
                <div x-data="{ showModal: false }">
                    <!-- Trigger button -->
                    @can('helpdesk.create')
                    <button @click="showModal = true" class="bg-gradient-to-r from-blue-600 to-blue-500 hover:from-blue-700 hover:to-blue-600 text-white px-3 h-[36px] rounded shadow-sm font-medium flex items-center text-xs transition-colors duration-200 ease-in-out">
                        <span class="material-icons-outlined text-xs mr-1">add_circle</span>
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
                        class="fixed inset-0 z-50 flex items-center justify-center modal-backdrop-glass"
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
                        <span class="material-icons-outlined text-xs mr-2">list</span>
                        All Tickets
                    </button>
                    <button 
                        @click="activeTab = 'open'" 
                        :class="{'border-primary-DEFAULT text-primary-DEFAULT': activeTab === 'open', 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300': activeTab !== 'open'}"
                        class="inline-flex items-center py-3 px-4 text-xs font-medium leading-5 border-b-2 focus:outline-none transition duration-150 ease-in-out"
                    >
                        <span class="material-icons-outlined text-xs mr-2">fiber_new</span>
                        Open
                        <span class="ml-1 bg-blue-100 text-blue-800 text-xs font-semibold px-2 py-0.5 rounded-full">{{ $openCount }}</span>
                    </button>
                    <button 
                        @click="activeTab = 'inProgress'" 
                        :class="{'border-primary-DEFAULT text-primary-DEFAULT': activeTab === 'inProgress', 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300': activeTab !== 'inProgress'}"
                        class="inline-flex items-center py-3 px-4 text-xs font-medium leading-5 border-b-2 focus:outline-none transition duration-150 ease-in-out"
                    >
                        <span class="material-icons-outlined text-xs mr-2">pending_actions</span>
                        In Progress
                        <span class="ml-1 bg-amber-100 text-amber-800 text-xs font-semibold px-2 py-0.5 rounded-full">{{ $inProgressCount }}</span>
                    </button>
                    <button 
                        @click="activeTab = 'resolved'" 
                        :class="{'border-primary-DEFAULT text-primary-DEFAULT': activeTab === 'resolved', 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300': activeTab !== 'resolved'}"
                        class="inline-flex items-center py-3 px-4 text-xs font-medium leading-5 border-b-2 focus:outline-none transition duration-150 ease-in-out"
                    >
                        <span class="material-icons-outlined text-xs mr-2">task_alt</span>
                        Resolved
                        <span class="ml-1 bg-green-100 text-green-800 text-xs font-semibold px-2 py-0.5 rounded-full">{{ $resolvedCount }}</span>
                    </button>
                </div>
            </div>
            
            {{-- Search takes the remaining space; the filters keep their own width. --}}
            <form method="GET" action="{{ route('helpdesk.index') }}" class="flex flex-wrap items-center gap-2 mb-4">
                <input type="text" name="search" id="searchInput" value="{{ request('search') }}"
                       placeholder="Search ticket ID, subject..."
                       class="flex-1 min-w-[12rem] h-9 text-xs border-gray-300 rounded px-3 focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50">

                <select name="priority" onchange="this.form.submit()"
                        class="h-9 text-xs border-gray-300 rounded pl-3 pr-8 w-[9rem] shrink-0 focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50">
                    <option value="">All Priorities</option>
                    <option value="low" @if(request('priority') == 'low') selected @endif>Low</option>
                    <option value="medium" @if(request('priority') == 'medium') selected @endif>Medium</option>
                    <option value="high" @if(request('priority') == 'high') selected @endif>High</option>
                    <option value="urgent" @if(request('priority') == 'urgent') selected @endif>Urgent</option>
                </select>

                <select name="category" onchange="this.form.submit()"
                        class="h-9 text-xs border-gray-300 rounded pl-3 pr-8 w-[11rem] shrink-0 focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50">
                    <option value="">All Categories</option>
                    <option value="technical" @if(request('category') == 'technical') selected @endif>Technical Issue</option>
                    <option value="billing" @if(request('category') == 'billing') selected @endif>Billing</option>
                    <option value="event" @if(request('category') == 'event') selected @endif>Event Management</option>
                    <option value="account" @if(request('category') == 'account') selected @endif>Account Access</option>
                    <option value="other" @if(request('category') == 'other') selected @endif>Other</option>
                </select>

                <select name="status" onchange="this.form.submit()"
                        class="h-9 text-xs border-gray-300 rounded pl-3 pr-8 w-[9rem] shrink-0 focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50">
                    <option value="">All Status</option>
                    <option value="open" @if(request('status') == 'open') selected @endif>Open</option>
                    <option value="in_progress" @if(request('status') == 'in_progress') selected @endif>In Progress</option>
                    <option value="resolved" @if(request('status') == 'resolved') selected @endif>Resolved</option>
                    <option value="closed" @if(request('status') == 'closed') selected @endif>Closed</option>
                </select>

                <button type="submit"
                        class="h-9 px-3 bg-primary-DEFAULT hover:bg-primary-dark text-white rounded text-xs flex items-center shrink-0 transition-colors duration-200 ease-in-out" title="Search">
                    <span class="material-icons-outlined text-xs">search</span>
                </button>

                @if(request('search') || request('priority') || request('category') || request('status'))
                    <a href="{{ route('helpdesk.index') }}" class="text-xs text-gray-500 underline shrink-0">Reset</a>
                @endif
            </form>
            
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
            
            {{--
                Four tabs, one table. The markup below used to be repeated once per
                tab - roughly 330 lines of it - which is why the list was missing a
                "From" column: adding one meant editing the same thing four times.
            --}}
            <div x-show="activeTab === 'all'">
                @include('helpdesk.partials.ticket-table', [
                    'rows' => $tickets,
                    'empty' => 'No tickets yet.',
                ])
            </div>

            <div x-show="activeTab === 'open'" x-cloak>
                @include('helpdesk.partials.ticket-table', [
                    'rows' => $openTickets,
                    'empty' => 'Nothing open.',
                ])
            </div>

            <div x-show="activeTab === 'inProgress'" x-cloak>
                @include('helpdesk.partials.ticket-table', [
                    'rows' => $inProgressTickets,
                    'empty' => 'Nothing in progress.',
                ])
            </div>

            <div x-show="activeTab === 'resolved'" x-cloak>
                @include('helpdesk.partials.ticket-table', [
                    'rows' => $resolvedTickets,
                    'empty' => 'Nothing resolved yet.',
                ])
            </div>
        </div>
    </div>
    
    <!-- Realtime Updates handled by FCM in resources/js/fcm.js -->
</x-app-layout> 