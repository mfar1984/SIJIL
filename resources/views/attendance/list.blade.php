<x-app-layout>
    <x-slot name="breadcrumb">
        <span>Attendance</span>
        <span class="mx-2 text-gray-500">/</span>
        <span>Attendance List</span>
    </x-slot>

    <x-slot name="title">Attendance List</x-slot>

    <div class="bg-white rounded shadow-md border border-gray-300" x-data="attendanceList()" x-init="init()">
        <div class="p-6 border-b border-gray-200">
            <div class="flex justify-between items-start">
                <div>
                    <div class="flex items-center">
                        <span class="material-icons-outlined mr-2 text-primary-DEFAULT">view_list</span>
                        <h1 class="text-xl font-bold text-gray-800">Attendance List</h1>
                    </div>
                    <p class="text-xs text-gray-500 mt-1 ml-8">View and manage attendance records for events</p>
                </div>
            </div>
        </div>
        <div class="p-4">
            {{-- Search takes the remaining space; the filters keep their own width.
                 This page filters through Alpine, so perPage stays in the component
                 state and is no longer exposed as a dropdown. --}}
            <form @submit.prevent="goToPage(1)" class="flex flex-wrap items-center gap-2 mb-4">
                <input type="text" x-model="search" placeholder="Search name, IC/passport..."
                       class="flex-1 min-w-[12rem] h-9 text-xs border-gray-300 rounded px-3 focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50">

                <select x-model="selectedEventId" @change="fetchSessions()"
                        class="h-9 text-xs border-gray-300 rounded pl-3 pr-8 w-[13rem] shrink-0 focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50">
                    <option value="">Select Event</option>
                    <template x-for="event in events" :key="event.id">
                        <option :value="event.id" x-text="event.name"></option>
                    </template>
                </select>

                <select x-model="selectedSessionId" @change="goToPage(1)"
                        class="h-9 text-xs border-gray-300 rounded pl-3 pr-8 w-[13rem] shrink-0 focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50">
                    <option value="">Select Session</option>
                    <template x-for="session in sessions" :key="session.id">
                        <option :value="session.id" x-text="session.name"></option>
                    </template>
                </select>

                <select x-model="status"
                        class="h-9 text-xs border-gray-300 rounded pl-3 pr-8 w-[9rem] shrink-0 focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50">
                    <option value="">All Status</option>
                    <option value="present">Present</option>
                    <option value="absent">Absent</option>
                </select>

                <button type="submit"
                        class="h-9 px-3 bg-primary-DEFAULT hover:bg-primary-dark text-white rounded text-xs flex items-center shrink-0 transition-colors duration-200 ease-in-out" title="Search">
                    <span class="material-icons-outlined text-xs">search</span>
                </button>

                <button type="button" @click="resetFilter"
                        class="h-9 px-3 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded text-xs flex items-center shrink-0 transition-colors duration-200 ease-in-out" title="Reset">
                    <span class="material-icons-outlined text-xs">close</span>
                </button>
            </form>
            
            <!-- Search Results Summary -->
            <div x-show="search || selectedEventId || selectedSessionId || status" class="bg-blue-50 border border-blue-200 text-blue-700 px-4 py-2 rounded mb-4 text-xs">
                <span class="font-medium">Search Results:</span>
                <span x-show="search" class="ml-2">Searching for "<span x-text="search"></span>"</span>
                <span x-show="selectedEventId" class="ml-2">Event: <span x-text="(events.find(e => e.id == selectedEventId) ? events.find(e => e.id == selectedEventId).name : 'Unknown')"></span></span>
                <span x-show="selectedSessionId" class="ml-2">Session: <span x-text="(sessions.find(s => s.id == selectedSessionId) ? sessions.find(s => s.id == selectedSessionId).name : 'Unknown')"></span></span>
                <span x-show="status" class="ml-2">Status: <span x-text="status"></span></span>
                <span class="ml-2">(<span x-text="meta.total"></span> results)</span>
            </div>
            
            <!-- Table Section -->
            <div class="overflow-visible border border-gray-200 rounded">
                <table class="min-w-full border-collapse">
                    <thead>
                        <tr class="bg-primary-light text-white text-xs uppercase">
                            <th class="py-3 px-4 text-left rounded-tl">Name</th>
                            <th class="py-3 px-4 text-left">&nbsp;</th>
                            <th class="py-3 px-4 text-left">Check-in Time</th>
                            <th class="py-3 px-4 text-left">Check-out Time</th>
                            <th class="py-3 px-4 text-left rounded-tr">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <template x-if="participants.length">
                            <template x-for="p in participants" :key="p.record_id">
                                <tr class="text-xs hover:bg-gray-50">
                                    <td class="py-3 px-4 font-medium">
                                        <a :href="`/participants/${p.participant_id}`" class="text-primary-DEFAULT hover:underline" x-text="p.name"></a>
                                    </td>
                                    <td class="py-3 px-4"></td>
                                    <td class="py-3 px-4" x-text="formatDateTime(p.time)"></td>
                                    <td class="py-3 px-4" x-text="p.checkout_time ? formatDateTime(p.checkout_time) : '-'"></td>
                                    <td class="py-3 px-4">
                                        <span class="bg-green-100 text-green-700 px-2 py-1 rounded-full text-xs" x-text="p.status"></span>
                                    </td>
                                </tr>
                            </template>
                        </template>
                        <template x-if="!participants.length">
                            <tr>
                                <td colspan="5" class="py-8 text-center text-xs text-gray-400">No attendance records found. Please select an event and session.</td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>
            <!-- Pagination Row -->
            <div class="mt-6 flex flex-col sm:flex-row sm:items-center sm:justify-between">
                <div class="mb-2 sm:mb-0 text-xs text-gray-500">
                    <template x-if="meta.total">
                        <span>
                            Showing <span x-text="showingFrom"></span> to <span x-text="showingTo"></span> of <span x-text="meta.total"></span> entries
                            <span x-show="meta.total > 0">(<span x-text="perPage"></span> per page)</span>
                        </span>
                    </template>
                </div>
                <div class="flex justify-end">
                    <nav class="flex items-center justify-center">
                        <div class="flex items-center space-x-1">
                            <!-- First Page Link -->
                            <button @click="goToPage(1)" :disabled="page === 1" :class="page === 1 ? 'opacity-50 cursor-not-allowed' : ''" class="px-2 py-1 text-gray-500 hover:text-primary-DEFAULT rounded-none text-xs" aria-label="Go to first page">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M15.707 15.707a1 1 0 01-1.414 0l-5-5a1 1 0 010-1.414l5-5a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 010 1.414zm-6 0a1 1 0 01-1.414 0l-5-5a1 1 0 010-1.414l5-5a1 1 0 011.414 1.414L5.414 10l4.293 4.293a1 1 0 010 1.414z" clip-rule="evenodd" /></svg>
                            </button>
                            
                            <!-- Previous Page Link -->
                            <button @click="goToPage(page-1)" :disabled="page === 1" :class="page === 1 ? 'opacity-50 cursor-not-allowed' : ''" class="px-2 py-1 text-gray-500 hover:text-primary-DEFAULT rounded-none text-xs mr-2" aria-label="Previous page">
                                <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd" /></svg>
                            </button>

                            <!-- Page Numbers -->
                            <template x-for="n in totalPages" :key="n">
                                <template x-if="n === page">
                                    <span class="w-6 h-6 flex items-center justify-center bg-primary-light text-white rounded-full shadow-sm text-xs font-medium" x-text="n"></span>
                                </template>
                                <template x-if="n !== page">
                                    <button @click="goToPage(n)" class="px-2 py-1 text-gray-600 hover:text-primary-DEFAULT rounded-none text-xs font-medium" x-text="n"></button>
                                </template>
                            </template>
                            
                            <!-- Next Page Link -->
                            <button @click="goToPage(page+1)" :disabled="page === totalPages" :class="page === totalPages ? 'opacity-50 cursor-not-allowed' : ''" class="px-2 py-1 text-gray-500 hover:text-primary-DEFAULT rounded-none text-xs ml-2" aria-label="Next page">
                                <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" /></svg>
                            </button>

                            <!-- Last Page Link -->
                            <button @click="goToPage(totalPages)" :disabled="page === totalPages" :class="page === totalPages ? 'opacity-50 cursor-not-allowed' : ''" class="px-2 py-1 text-gray-500 hover:text-primary-DEFAULT rounded-none text-xs" aria-label="Go to last page">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0l5 5a1 1 0 010 1.414l-5 5a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414zM10 4.293a1 1 0 011.414 0l5 5a1 1 0 010 1.414l-5 5a1 1 0 01-1.414-1.414L14.586 10l-4.293-4.293a1 1 0 010-1.414z" clip-rule="evenodd" /></svg>
                            </button>
                        </div>
                    </nav>
                </div>
            </div>
        </div>
    </div>
    <script>
    function attendanceList() {
        return {
            events: @json($events),
            sessions: @json($sessions),
            participants: [],
            meta: { total: 0, page: 1, per_page: 10 },
            selectedEventId: '{{ $selectedEventId }}',
            selectedSessionId: '{{ $selectedSessionId }}',
            page: 1,
            perPage: 10,
            totalPages: 1,
            search: '',
            status: '',
            get showingFrom() {
                return this.meta.total ? ((this.page - 1) * this.perPage + 1) : 0;
            },
            get showingTo() {
                return this.meta.total ? Math.min(this.page * this.perPage, this.meta.total) : 0;
            },
            formatDateTime(dateTimeStr) {
                if (!dateTimeStr) return '-';
                const dt = new Date(dateTimeStr);
                const day = dt.getDate();
                const monthNames = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
                const month = monthNames[dt.getMonth()];
                const year = dt.getFullYear();
                
                let hours = dt.getHours();
                const ampm = hours >= 12 ? 'PM' : 'AM';
                hours = hours % 12;
                hours = hours ? hours : 12; // the hour '0' should be '12'
                const minutes = dt.getMinutes().toString().padStart(2, '0');
                const seconds = dt.getSeconds().toString().padStart(2, '0');
                
                return `${day} ${month} ${year} - ${hours}:${minutes}:${seconds} ${ampm}`;
            },
            init() {
                if (!this.selectedEventId && this.events.length) {
                    this.selectedEventId = this.events[0].id;
                }
                this.fetchSessions();
            },
            fetchSessions() {
                fetch(`/api/attendance-sessions?event_id=${this.selectedEventId}`)
                    .then(res => res.json())
                    .then(data => {
                        this.sessions = data;
                        this.selectedSessionId = this.sessions.length ? this.sessions[0].id : null;
                        this.goToPage(1);
                    });
            },
            fetchParticipants() {
                if (!this.selectedSessionId) {
                    this.participants = [];
                    this.meta = { total: 0, page: 1, per_page: this.perPage };
                    this.totalPages = 1;
                    return;
                }
                const params = new URLSearchParams({
                    session_id: this.selectedSessionId,
                    page: this.page,
                    per_page: this.perPage,
                    search: this.search,
                    status: this.status
                });
                fetch(`/api/attendance-participants?${params.toString()}`)
                    .then(res => res.json())
                    .then(data => {
                        this.participants = data.data;
                        this.meta = data.meta;
                        this.page = data.meta.page;
                        this.perPage = data.meta.per_page;
                        this.totalPages = Math.max(1, Math.ceil(data.meta.total / data.meta.per_page));
                    });
            },
            goToPage(n) {
                if (n < 1 || n > this.totalPages) return;
                this.page = n;
                this.fetchParticipants();
            },
            resetFilter() {
                // Reset all filters to initial state (first event/session if available)
                this.search = '';
                this.status = '';
                this.page = 1;
                this.perPage = 10;
                this.selectedEventId = this.events.length ? this.events[0].id : '';
                this.fetchSessions();
            }
        }
    }
    </script>
</x-app-layout> 