<x-app-layout>
    <x-slot name="breadcrumb">
        <span>Attendance</span>
        <span class="mx-2 text-gray-500">/</span>
        <span>Attendance List</span>
    </x-slot>

    <x-slot name="title">Attendance List</x-slot>

    <div class="bg-white rounded shadow-md border border-gray-300 mt-6" x-data="attendanceList()" x-init="init()">
        <div class="p-6 border-b border-gray-200 flex items-center justify-between">
            <div class="flex items-center">
                <span class="material-icons mr-2 text-primary-DEFAULT">view_list</span>
                <h1 class="text-xl font-bold text-gray-800">Attendance List</h1>
            </div>
        </div>
        <div class="p-6">
            <!-- Search & Filter Row -->
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-4 gap-2">
                <!-- Show Entries Dropdown -->
                <div class="flex items-center gap-2">
                    <span class="text-xs text-gray-600 font-medium">Show</span>
                    <select x-model="perPage" @change="goToPage(1)" class="appearance-none px-2 py-1 text-xs border border-gray-300 rounded focus:ring focus:ring-primary-light focus:border-primary-light bg-white bg-no-repeat bg-right w-[60px] font-medium" style="background-image: url('data:image/svg+xml;charset=US-ASCII,<svg xmlns=%22http://www.w3.org/2000/svg%22 width=%2216%22 height=%2216%22 viewBox=%220 0 24 24%22 fill=%22none%22 stroke=%22%23888%22 stroke-width=%222%22 stroke-linecap=%22round%22 stroke-linejoin=%22round%22><polyline points=%226 9 12 15 18 9%22></polyline></svg>'); background-position: right 0.25rem center; background-size: 0.75em;">
                        <option value="10">10</option>
                        <option value="25">25</option>
                        <option value="50">50</option>
                        <option value="100">100</option>
                    </select>
                    <span class="text-xs text-gray-600">entries per page</span>
                </div>
                
                <!-- Search & Filter Controls -->
                <form @submit.prevent="goToPage(1)" class="flex flex-wrap gap-2 items-center">
                    <!-- Event Filter -->
                    <select x-model="selectedEventId" @change="fetchSessions()" class="appearance-none px-3 py-1.5 pr-8 text-xs border border-gray-300 rounded focus:ring focus:ring-primary-light focus:border-primary-light bg-white bg-no-repeat bg-right max-w-[200px] truncate" style="background-image: url('data:image/svg+xml;charset=US-ASCII,<svg xmlns=%22http://www.w3.org/2000/svg%22 width=%2216%22 height=%2216%22 viewBox=%220 0 24 24%22 fill=%22none%22 stroke=%22%23888%22 stroke-width=%222%22 stroke-linecap=%22round%22 stroke-linejoin=%22round%22><polyline points=%226 9 12 15 18 9%22></polyline></svg>'); background-position: right 0.75rem center; background-size: 1em;">
                        <option value="">Select Event</option>
                        <template x-for="event in events" :key="event.id">
                            <option :value="event.id" x-text="event.name"></option>
                        </template>
                    </select>
                    
                    <!-- Session Filter -->
                    <select x-model="selectedSessionId" @change="goToPage(1)" class="appearance-none px-3 py-1.5 pr-8 text-xs border border-gray-300 rounded focus:ring focus:ring-primary-light focus:border-primary-light bg-white bg-no-repeat bg-right max-w-[200px] truncate" style="background-image: url('data:image/svg+xml;charset=US-ASCII,<svg xmlns=%22http://www.w3.org/2000/svg%22 width=%2216%22 height=%2216%22 viewBox=%220 0 24 24%22 fill=%22none%22 stroke=%22%23888%22 stroke-width=%222%22 stroke-linecap=%22round%22 stroke-linejoin=%22round%22><polyline points=%226 9 12 15 18 9%22></polyline></svg>'); background-position: right 0.75rem center; background-size: 1em;">
                        <option value="">Select Session</option>
                        <template x-for="session in sessions" :key="session.id">
                            <option :value="session.id" x-text="session.name"></option>
                        </template>
                    </select>
                    
                    <input type="text" x-model="search" placeholder="Search name, IC..." class="border border-gray-300 rounded px-2 py-1 text-xs focus:ring focus:ring-primary-light focus:border-primary-light" />
                    <select x-model="status" class="appearance-none px-3 py-1.5 pr-8 text-xs border border-gray-300 rounded focus:ring focus:ring-primary-light focus:border-primary-light bg-white bg-no-repeat bg-right w-[120px]">
                        <option value="">All Status</option>
                        <option value="present">Present</option>
                        <option value="absent">Absent</option>
                    </select>
                    <button type="submit" class="bg-primary-light text-white px-3 py-1 h-[38px] rounded text-xs font-medium flex items-center justify-center" title="Search">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4-4m0 0A7 7 0 104 4a7 7 0 0013 13z" />
                        </svg>
                    </button>
                    <button type="button" @click="resetFilter" class="text-xs text-gray-500 underline ml-2">Reset</button>
                </form>
            </div>
            <!-- Table Section -->
            <div class="overflow-visible border border-gray-200 rounded">
                <table class="min-w-full border-collapse">
                    <thead>
                        <tr class="bg-primary-light text-white text-xs uppercase">
                            <th class="py-3 px-4 text-left rounded-tl">Name</th>
                            <th class="py-3 px-4 text-left">IC / Passport</th>
                            <th class="py-3 px-4 text-left">Check-in Time</th>
                            <th class="py-3 px-4 text-left">Check-out Time</th>
                            <th class="py-3 px-4 text-left rounded-tr">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <template x-if="participants.length">
                            <template x-for="p in participants" :key="p.record_id">
                                <tr class="text-xs hover:bg-gray-50">
                                    <td class="py-3 px-4 font-medium" x-text="p.name"></td>
                                    <td class="py-3 px-4" x-text="p.ic"></td>
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
            <!-- Showing & Pagination -->
            <div class="flex flex-col md:flex-row md:items-center md:justify-between mt-4">
                <div class="text-xs text-gray-500 mb-2 md:mb-0">
                    <template x-if="meta.total">
                        <span>
                            Showing <span x-text="showingFrom"></span> to <span x-text="showingTo"></span> of <span x-text="meta.total"></span> participants
                        </span>
                    </template>
                </div>
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
                this.search = '';
                this.status = '';
                this.goToPage(1);
            }
        }
    }
    </script>
</x-app-layout> 