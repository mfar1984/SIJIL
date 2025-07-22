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
            <!-- Filter Section -->
            <div class="bg-gray-50 border border-gray-200 rounded p-4 mb-6 flex flex-col md:flex-row md:items-end md:space-x-4 space-y-2 md:space-y-0">
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1">Event</label>
                    <select x-model="selectedEventId" @change="fetchSessions()" class="form-select w-56 text-xs rounded border-gray-300">
                        <template x-for="event in events" :key="event.id">
                            <option :value="event.id" x-text="event.name"></option>
                        </template>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1">Session</label>
                    <select x-model="selectedSessionId" @change="goToPage(1)" class="form-select w-40 text-xs rounded border-gray-300">
                        <template x-for="session in sessions" :key="session.id">
                            <option :value="session.id" x-text="session.name"></option>
                        </template>
                    </select>
                </div>
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
                            <template x-for="p in participants" :key="p.id">
                                <tr class="text-xs hover:bg-gray-50">
                                    <td class="py-3 px-4 font-medium" x-text="p.name"></td>
                                    <td class="py-3 px-4" x-text="p.ic"></td>
                                    <td class="py-3 px-4" x-text="p.time"></td>
                                    <td class="py-3 px-4" x-text="p.checkout_time ?? '-' "></td>
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
                <nav class="inline-flex -space-x-px">
                    <button type="button" @click="goToPage(page-1)" :disabled="page === 1" class="px-3 py-1 rounded-l border border-gray-300 bg-gray-100 text-xs text-gray-500">&laquo;</button>
                    <template x-for="n in totalPages" :key="n">
                        <button type="button" @click="goToPage(n)" :class="page === n ? 'bg-blue-50 text-blue-600 font-semibold' : 'bg-white text-gray-600'" class="px-3 py-1 border border-gray-300 text-xs" x-text="n"></button>
                    </template>
                    <button type="button" @click="goToPage(page+1)" :disabled="page === totalPages" class="px-3 py-1 rounded-r border border-gray-300 bg-gray-100 text-xs text-gray-500">&raquo;</button>
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
            get showingFrom() {
                return this.meta.total ? ((this.page - 1) * this.perPage + 1) : 0;
            },
            get showingTo() {
                return this.meta.total ? Math.min(this.page * this.perPage, this.meta.total) : 0;
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
                fetch(`/api/attendance-participants?session_id=${this.selectedSessionId}&page=${this.page}&per_page=${this.perPage}`)
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
            }
        }
    }
    </script>
</x-app-layout> 