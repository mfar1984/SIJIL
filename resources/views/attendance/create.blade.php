<x-app-layout>
    <x-slot name="breadcrumb">
        <span>Attendance</span>
        <span class="mx-2 text-gray-500">/</span>
        <span>Create Attendance</span>
    </x-slot>

    <x-slot name="title">Create New Attendance</x-slot>

    <style>
        /* Tooltip styles */
        .tooltip-wrapper {
            position: relative;
            display: inline-flex;
        }
        
        .tooltip-content {
            position: absolute;
            bottom: 100%;
            left: 50%;
            transform: translateX(-50%) translateY(-4px);
            background-color: #1f2937;
            color: white;
            padding: 6px 10px;
            border-radius: 6px;
            font-size: 11px;
            white-space: nowrap;
            z-index: 1000;
            pointer-events: none;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        }
        
        .tooltip-content::after {
            content: '';
            position: absolute;
            top: 100%;
            left: 50%;
            transform: translateX(-50%);
            border: 4px solid transparent;
            border-top-color: #1f2937;
        }
    </style>

    <div class="bg-white rounded shadow-md border border-gray-300">
        <div class="p-6 border-b border-gray-200">
            <div class="flex items-center">
                <span class="material-icons-outlined mr-2 text-primary-DEFAULT">how_to_reg</span>
                <h1 class="text-xl font-bold text-gray-800">Create New Attendance</h1>
            </div>
            <p class="text-xs text-gray-500 mt-1 ml-8">Add a new attendance session for an event</p>
        </div>
        <div class="p-6" x-data="attendanceForm()">
            <form method="POST" action="{{ route('attendance.store') }}" class="space-y-2">
                @csrf
                <!-- Step 1: Select Event -->
                <div class="bg-white border border-gray-200 rounded-md shadow-sm">
                    <div class="bg-gray-50 border-b border-gray-200 px-4 py-3">
                        <h2 class="text-sm font-semibold text-gray-700 flex items-center">
                            <span class="material-icons-outlined text-primary-DEFAULT mr-2">event</span>
                            1. Select Event
                        </h2>
                    </div>
                    
                    <div class="p-4 space-y-4">
                        <div class="flex flex-col md:flex-row md:items-center gap-3">
                            <label for="event_id" class="text-xs font-medium text-gray-700 md:w-48 shrink-0">
                                Event <span class="text-red-500">*</span>
                            </label>
                            <div class="flex-1">
                                <select name="event_id" id="event_id" class="w-full h-9 text-xs border-gray-300 rounded px-3 focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50" x-model="selectedEventId" @change="updateEventInfo()" required>
                                    <option value="">-- Select Event --</option>
                                    <template x-for="event in events" :key="event.id">
                                        <option :value="event.id" x-text="event.name"></option>
                                    </template>
                                </select>
                                @error('event_id')
                                    <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                                @enderror

                                @if($alreadyHas)
                                    <p class="text-xs text-amber-700 bg-amber-50 border border-amber-200 rounded px-3 py-2 mt-2">
                                        <span class="font-medium">{{ $alreadyHas->name }}</span> already has an attendance
                                        session, so it is not in this list. Edit the existing one from
                                        <a href="{{ route('attendance.index') }}" class="underline">Attendance Management</a>.
                                    </p>
                                @endif

                                @if($events->isEmpty())
                                    <p class="text-xs text-amber-700 bg-amber-50 border border-amber-200 rounded px-3 py-2 mt-2">
                                        Every active event of yours already has an attendance session. Create a new event,
                                        or edit an existing session from
                                        <a href="{{ route('attendance.index') }}" class="underline">Attendance Management</a>.
                                    </p>
                                @else
                                    <p class="text-xs text-gray-500 mt-1">
                                        Only <span class="font-medium">Active</span> events without an attendance session
                                        appear here. Each event has one session setup.
                                    </p>
                                @endif
                            </div>
                        </div>

                        <template x-if="selectedEvent">
                            <div class="flex flex-col md:flex-row md:items-start gap-3">
                                <span class="text-xs font-medium text-gray-700 md:w-48 shrink-0">Event details</span>
                                <div class="flex-1 bg-gray-50 border border-gray-200 rounded p-3 text-xs text-gray-700">
                                    <div class="mb-1 font-semibold" x-text="selectedEvent.name"></div>
                                    <div>
                                        <span class="font-medium">Date:</span>
                                        <span x-text="formatDate(selectedEvent.start_date)"></span>
                                        <template x-if="selectedEvent.end_date && selectedEvent.end_date !== selectedEvent.start_date">
                                            <span> &ndash; <span x-text="formatDate(selectedEvent.end_date)"></span></span>
                                        </template>
                                        <span class="ml-1 text-gray-500" x-text="'(' + eventDayCount + ' day' + (eventDayCount > 1 ? 's' : '') + ')'"></span>
                                    </div>
                                    <div><span class="font-medium">Time:</span> <span x-text="selectedEvent.start_time"></span> &ndash; <span x-text="selectedEvent.end_time"></span></div>
                                    <div><span class="font-medium">Location:</span> <span x-text="selectedEvent.location"></span></div>
                                </div>
                            </div>
                        </template>

                    </div>
                </div>

                <!-- Step 2: Attendance Type -->
                <div class="bg-white border border-gray-200 rounded-md shadow-sm">
                    <div class="bg-gray-50 border-b border-gray-200 px-4 py-3">
                        <h2 class="text-sm font-semibold text-gray-700 flex items-center">
                            <span class="material-icons-outlined text-primary-DEFAULT mr-2">category</span>
                            2. How often do participants scan?
                        </h2>
                    </div>
                    
                    <div class="p-4 space-y-4">
                        <div class="flex flex-col md:flex-row md:items-start gap-3">
                            <label class="text-xs font-medium text-gray-700 md:w-48 shrink-0 md:pt-1">
                                Attendance type <span class="text-red-500">*</span>
                            </label>
                            <div class="flex-1 grid grid-cols-1 md:grid-cols-3 gap-3">
                                {{-- Each option spells out what it produces, so no one has to guess. --}}
                                <label class="cursor-pointer border rounded p-3 flex gap-3 transition-colors duration-150"
                                       :class="attendanceType === 'single' ? 'border-primary-DEFAULT bg-blue-50' : 'border-gray-200 hover:border-gray-300'">
                                    <input type="radio" name="attendance_type" value="single" x-model="attendanceType" @change="onTypeChange()" class="mt-0.5 shrink-0">
                                    <span>
                                        <span class="block text-xs font-medium text-gray-800">Scan once</span>
                                        <span class="block text-xs text-gray-500 mt-1">
                                            One QR code for the whole event. Nothing to set up &mdash; the event's own
                                            date and time are used.
                                        </span>
                                        <span class="block text-xs text-primary-DEFAULT mt-1">1 QR code</span>
                                    </span>
                                </label>

                                <label class="cursor-pointer border rounded p-3 flex gap-3 transition-colors duration-150"
                                       :class="attendanceType === 'daily' ? 'border-primary-DEFAULT bg-blue-50' : 'border-gray-200 hover:border-gray-300'">
                                    <input type="radio" name="attendance_type" value="daily" x-model="attendanceType" @change="onTypeChange()" class="mt-0.5 shrink-0">
                                    <span>
                                        <span class="block text-xs font-medium text-gray-800">Scan every day</span>
                                        <span class="block text-xs text-gray-500 mt-1">
                                            A different QR code for each event day. Nothing to set up &mdash; the event's
                                            own dates and times are used.
                                        </span>
                                        <span class="block text-xs text-primary-DEFAULT mt-1"
                                              x-text="selectedEvent ? eventDayCount + ' QR code' + (eventDayCount > 1 ? 's' : '') + ', one per day' : 'Pick an event first'"></span>
                                    </span>
                                </label>

                                <label class="cursor-pointer border rounded p-3 flex gap-3 transition-colors duration-150"
                                       :class="attendanceType === 'custom' ? 'border-primary-DEFAULT bg-blue-50' : 'border-gray-200 hover:border-gray-300'">
                                    <input type="radio" name="attendance_type" value="custom" x-model="attendanceType" @change="onTypeChange()" class="mt-0.5 shrink-0">
                                    <span>
                                        <span class="block text-xs font-medium text-gray-800">Let me choose</span>
                                        <span class="block text-xs text-gray-500 mt-1">
                                            You set the exact date and times for every scan, so latecomers and early
                                            leavers are recorded. Also allows check-out.
                                        </span>
                                        <span class="block text-xs text-primary-DEFAULT mt-1"
                                              x-text="(customSessions.length * (enableCheckout ? 2 : 1)) + ' QR code' + ((customSessions.length * (enableCheckout ? 2 : 1)) > 1 ? 's' : '')"></span>
                                    </span>
                                </label>
                            </div>
                        </div>

                        {{-- Check-out only makes sense when the operator is setting the times
                             themselves; the two automatic modes are check-in only. --}}
                        <template x-if="attendanceType === 'custom'">
                            <div class="flex flex-col md:flex-row md:items-start gap-3">
                                <label class="text-xs font-medium text-gray-700 md:w-48 shrink-0 md:pt-1">
                                    Check-out
                                </label>
                                <div class="flex-1">
                                    <label class="flex items-start gap-2 cursor-pointer">
                                        <input type="checkbox" x-model="enableCheckout" class="mt-0.5 shrink-0">
                                        <span>
                                            <span class="block text-xs text-gray-800">Participants must also scan when leaving</span>
                                            <span class="block text-xs text-gray-500 mt-1">
                                                Adds a second QR code to every session and lets you measure how long
                                                people stayed.
                                            </span>
                                        </span>
                                    </label>
                                </div>
                            </div>
                        </template>

                        <div class="flex flex-col md:flex-row md:items-start gap-3">
                            <span class="text-xs font-medium text-gray-700 md:w-48 shrink-0">Summary</span>
                            <p class="flex-1 text-xs text-gray-600 bg-gray-50 border border-gray-200 rounded p-3">
                                <span x-text="summaryText"></span>
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Step 3: Session Input -->
                <div class="bg-white border border-gray-200 rounded-md shadow-sm">
                    <div class="bg-gray-50 border-b border-gray-200 px-4 py-3">
                        <h2 class="text-sm font-semibold text-gray-700 flex items-center">
                            <span class="material-icons-outlined text-primary-DEFAULT mr-2">schedule</span>
                            3. When can they scan?
                        </h2>
                    </div>
                    
                    <div class="p-4 space-y-4">
                    <template x-if="!selectedEvent && attendanceType !== 'custom'">
                        <div class="flex flex-col md:flex-row md:items-start gap-3">
                            <span class="text-xs font-medium text-gray-700 md:w-48 shrink-0">Sessions</span>
                            <p class="flex-1 text-xs text-gray-500">
                                Select an event above and the session times will be filled in from the event schedule.
                            </p>
                        </div>
                    </template>

                    {{-- Column headers belong to the manual mode only. --}}
                    <template x-if="attendanceType === 'custom'">
                        <div class="hidden md:flex gap-3 text-xs font-medium text-gray-500">
                            <span class="w-48 shrink-0"></span>
                            <span class="flex-1">Date</span>
                            <span class="flex-1">Check-in opens</span>
                            <span class="flex-1">Check-in closes</span>
                            <template x-if="enableCheckout">
                                <span class="flex-1">Check-out opens</span>
                            </template>
                            <template x-if="enableCheckout">
                                <span class="flex-1">Check-out closes</span>
                            </template>
                            <span class="w-8 shrink-0"></span>
                        </div>
                    </template>

                    <!-- Scan once: one QR code, taken straight from the event -->
                    <template x-if="attendanceType === 'single' && selectedEvent">
                        <div class="flex flex-col md:flex-row md:items-start gap-3">
                            <span class="text-xs font-medium text-gray-700 md:w-48 shrink-0 md:pt-2">QR code</span>
                            <div class="flex-1">
                                <div class="border border-gray-200 rounded divide-y divide-gray-100">
                                    <div class="px-3 py-2 flex flex-wrap items-center gap-x-6 gap-y-1">
                                        <span class="text-xs font-medium text-gray-800" x-text="formatDayLabel(selectedEvent.start_date)"></span>
                                        <span class="text-xs text-gray-500">
                                            Valid <span x-text="trimTime(defaultCheckinStart)"></span> &ndash; <span x-text="trimTime(defaultCheckinEnd)"></span>
                                        </span>
                                        <span class="text-xs text-gray-400">Check-in only</span>
                                    </div>
                                </div>
                                <p class="text-xs text-gray-500 mt-2">
                                    Taken from the event schedule. Any scan inside this window counts as attending, so
                                    nobody is marked late. Choose <span class="font-medium">Let me choose</span> if you
                                    need a stricter window or a check-out scan.
                                </p>

                                {{-- The times still have to reach the server, just not as questions. --}}
                                <input type="hidden" name="sessions[0][date]" :value="selectedEvent.start_date">
                                <input type="hidden" name="sessions[0][checkin_start_time]" :value="defaultCheckinStart">
                                <input type="hidden" name="sessions[0][checkin_end_time]" :value="defaultCheckinEnd">
                            </div>
                        </div>
                    </template>

                    <!-- Scan every day: one QR code per event day -->
                    <template x-if="attendanceType === 'daily' && selectedEvent">
                        <div class="flex flex-col md:flex-row md:items-start gap-3">
                            <span class="text-xs font-medium text-gray-700 md:w-48 shrink-0 md:pt-2">QR codes</span>
                            <div class="flex-1">
                                <div class="border border-gray-200 rounded divide-y divide-gray-100">
                                    <template x-for="(day, idx) in eventDays" :key="day.date">
                                        <div class="px-3 py-2 flex flex-wrap items-center gap-x-6 gap-y-1">
                                            <span class="text-xs font-medium text-gray-800 w-28 shrink-0"
                                                  x-text="'Day ' + (idx + 1)"></span>
                                            <span class="text-xs text-gray-700" x-text="formatDayLabel(day.date)"></span>
                                            <span class="text-xs text-gray-500">
                                                Valid <span x-text="trimTime(day.checkin_start_time)"></span> &ndash; <span x-text="trimTime(day.checkin_end_time)"></span>
                                            </span>
                                            <span class="text-xs text-gray-400">Check-in only</span>

                                            <input type="hidden" :name="`sessions[${idx}][date]`" :value="day.date">
                                            <input type="hidden" :name="`sessions[${idx}][checkin_start_time]`" :value="day.checkin_start_time">
                                            <input type="hidden" :name="`sessions[${idx}][checkin_end_time]`" :value="day.checkin_end_time">
                                        </div>
                                    </template>
                                </div>
                                <p class="text-xs text-gray-500 mt-2">
                                    One QR code per day, taken from the event schedule. Each day's code only works on
                                    that day. Choose <span class="font-medium">Let me choose</span> if the days or times
                                    are not all the same.
                                </p>
                            </div>
                        </div>
                    </template>
                    <!-- Custom sessions -->
                    <template x-if="attendanceType === 'custom'">
                        <div class="space-y-3">
                            <template x-for="(session, idx) in customSessions" :key="idx">
                                <div class="flex flex-col md:flex-row md:items-center gap-3">
                                    <label class="text-xs font-medium text-gray-700 md:w-48 shrink-0"
                                           x-text="'Session ' + (idx + 1)"></label>
                                    <div class="flex-1">
                                        <span class="md:hidden block text-xs text-gray-500 mb-1">Date</span>
                                        <input type="date" :name="`sessions[${idx}][date]`" class="w-full h-9 text-xs border-gray-300 rounded px-3" x-model="session.date" required>
                                    </div>
                                    <div class="flex-1">
                                        <span class="md:hidden block text-xs text-gray-500 mb-1">Check-in opens</span>
                                        <input type="time" :name="`sessions[${idx}][checkin_start_time]`" class="w-full h-9 text-xs border-gray-300 rounded px-3" x-model="session.checkin_start_time" required>
                                    </div>
                                    <div class="flex-1">
                                        <span class="md:hidden block text-xs text-gray-500 mb-1">Check-in closes</span>
                                        <input type="time" :name="`sessions[${idx}][checkin_end_time]`" class="w-full h-9 text-xs border-gray-300 rounded px-3" x-model="session.checkin_end_time" required>
                                    </div>
                                    <template x-if="enableCheckout">
                                        <div class="flex-1">
                                            <span class="md:hidden block text-xs text-gray-500 mb-1">Check-out opens</span>
                                            <input type="time" :name="`sessions[${idx}][checkout_start_time]`" class="w-full h-9 text-xs border-gray-300 rounded px-3" x-model="session.checkout_start_time">
                                        </div>
                                    </template>
                                    <template x-if="enableCheckout">
                                        <div class="flex-1">
                                            <span class="md:hidden block text-xs text-gray-500 mb-1">Check-out closes</span>
                                            <input type="time" :name="`sessions[${idx}][checkout_end_time]`" class="w-full h-9 text-xs border-gray-300 rounded px-3" x-model="session.checkout_end_time">
                                        </div>
                                    </template>
                                    <div class="w-8 shrink-0 flex md:justify-center">
                                        <button type="button" @click="removeCustomSession(idx)"
                                                :disabled="customSessions.length === 1"
                                                :class="customSessions.length === 1 ? 'opacity-30 cursor-not-allowed' : 'hover:bg-red-50'"
                                                class="p-1 rounded border border-red-100 bg-red-50"
                                                title="Remove this session">
                                            <span class="material-icons-outlined text-red-600 text-xs">delete</span>
                                        </button>
                                    </div>
                                </div>
                            </template>

                            <div class="flex flex-col md:flex-row gap-3">
                                <span class="md:w-48 shrink-0"></span>
                                <button type="button" class="h-9 px-3 bg-primary-DEFAULT hover:bg-primary-dark text-white rounded shadow-sm text-xs font-medium flex items-center shrink-0 transition-colors duration-200 ease-in-out" @click="addCustomSession()">
                                    <span class="material-icons-outlined text-xs mr-1">add_circle</span>
                                    Add another session
                                </button>
                            </div>
                        </div>
                    </template>
                    </div>
                </div>

                <!-- Step 4: Status -->
                <div class="bg-white border border-gray-200 rounded-md shadow-sm">
                    <div class="bg-gray-50 border-b border-gray-200 px-4 py-3">
                        <h2 class="text-sm font-semibold text-gray-700 flex items-center">
                            <span class="material-icons-outlined text-primary-DEFAULT mr-2">toggle_on</span>
                            4. Status
                        </h2>
                    </div>
                    
                    <div class="p-4">
                        <div class="flex flex-col md:flex-row md:items-start gap-3">
                            <label for="status" class="text-xs font-medium text-gray-700 md:w-48 shrink-0 md:pt-2">
                                Status <span class="text-red-500">*</span>
                            </label>
                            <div class="flex-1">
                                <select name="status" id="status" class="w-full md:max-w-xs h-9 text-xs border-gray-300 rounded px-3 focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50" required>
                                    <option value="active" @selected(old('status', 'active') === 'active')>Active — QR codes work during the session times</option>
                                    <option value="expired" @selected(old('status') === 'expired')>Expired — QR codes stop working</option>
                                    <option value="completed" @selected(old('status') === 'completed')>Completed — session is over, kept for records</option>
                                </select>
                                <p class="text-xs text-gray-500 mt-1">
                                    Keep it on Active unless you are recording a session that already happened.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Step 5: Buttons -->
                <div class="flex justify-end space-x-3 pt-4">
                    <a href="{{ route('attendance.index') }}" class="px-3 h-[36px] bg-gradient-to-r from-red-600 to-red-500 hover:from-red-700 hover:to-red-600 text-white rounded shadow-sm text-xs font-medium transition-colors duration-200 ease-in-out flex items-center">
                        <span class="material-icons-outlined text-xs mr-1">cancel</span>
                        Cancel
                    </a>
                    <button type="submit" class="px-3 h-[36px] bg-gradient-to-r from-blue-600 to-blue-500 hover:from-blue-700 hover:to-blue-600 text-white rounded shadow-sm text-xs font-medium transition-colors duration-200 ease-in-out flex items-center">
                        <span class="material-icons-outlined text-xs mr-1">save</span>
                        Create Attendance
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
    function attendanceForm() {
        return {
            events: @json($eventsArray),
            // Preselected when arriving from an event form, so the operator does not
            // have to find the event again.
            selectedEventId: '{{ old('event_id', $preselect) }}',
            selectedEvent: null,
            attendanceType: 'single',
            // "Scan once" is the default, and that mode is check-in only.
            enableCheckout: false,
            eventDays: [],
            customSessions: [
                {date: '', checkin_start_time: '', checkin_end_time: '', checkout_start_time: '', checkout_end_time: ''}
            ],
            get eventDayCount() {
                return this.eventDays.length;
            },
            // Fallbacks keep the hidden inputs valid even if the event has no times set.
            get defaultCheckinStart() {
                return (this.selectedEvent && this.selectedEvent.start_time) || '00:00';
            },
            get defaultCheckinEnd() {
                return (this.selectedEvent && this.selectedEvent.end_time) || '23:59';
            },
            get sessionCount() {
                if (this.attendanceType === 'single') return 1;
                if (this.attendanceType === 'daily') return this.eventDays.length;
                return this.customSessions.length;
            },
            get summaryText() {
                if (!this.selectedEvent && this.attendanceType !== 'custom') {
                    return 'Select an event to see what will be created.';
                }

                const count = this.sessionCount;
                const qr = count * (this.enableCheckout ? 2 : 1);
                const plural = n => n === 1 ? '' : 's';

                if (this.attendanceType === 'single') {
                    return `1 QR code, valid on ${this.formatDayLabel(this.selectedEvent.start_date)} `
                        + `from ${this.trimTime(this.defaultCheckinStart)} to ${this.trimTime(this.defaultCheckinEnd)}. `
                        + 'Participants scan once. Check-in only.';
                }

                if (this.attendanceType === 'daily') {
                    return `${count} QR code${plural(count)}, one for each event day. `
                        + 'Each code works only on its own day. Participants scan once a day. Check-in only.';
                }

                const scanWord = this.enableCheckout ? 'check in and check out' : 'check in';
                return `Participants ${scanWord} across ${count} session${plural(count)}, `
                    + `which produces ${qr} QR code${plural(qr)} in total. `
                    + 'You control the exact windows, so late and early scans are recorded.';
            },
            // The two automatic modes are check-in only, so the check-out
            // option is cleared whenever the operator leaves the manual mode.
            onTypeChange() {
                if (this.attendanceType !== 'custom') {
                    this.enableCheckout = false;
                }
            },
            init() {
                // Fill in the event details when the page loads with an event
                // already chosen, either from a preselect or after a validation error.
                if (this.selectedEventId) {
                    this.updateEventInfo();
                }
            },
            updateEventInfo() {
                this.selectedEvent = this.events.find(e => e.id == this.selectedEventId);
                if (this.selectedEvent) {
                    // Pre-fill one row per event day, using the event's own times
                    // so the operator usually only has to confirm.
                    const start = new Date(this.selectedEvent.start_date);
                    const end = new Date(this.selectedEvent.end_date || this.selectedEvent.start_date);
                    this.eventDays = [];
                    for (let d = new Date(start); d <= end; d.setDate(d.getDate() + 1)) {
                        this.eventDays.push({
                            date: d.toISOString().slice(0, 10),
                            checkin_start_time: this.selectedEvent.start_time || '',
                            checkin_end_time: this.selectedEvent.end_time || '',
                            checkout_start_time: this.selectedEvent.start_time || '',
                            checkout_end_time: this.selectedEvent.end_time || '',
                        });
                    }
                    // Give the custom list a sensible starting point too.
                    if (this.customSessions.length === 1 && !this.customSessions[0].date) {
                        this.customSessions[0] = {
                            date: this.selectedEvent.start_date,
                            checkin_start_time: this.selectedEvent.start_time || '',
                            checkin_end_time: this.selectedEvent.end_time || '',
                            checkout_start_time: this.selectedEvent.start_time || '',
                            checkout_end_time: this.selectedEvent.end_time || '',
                        };
                    }
                } else {
                    this.eventDays = [];
                }
            },
            addCustomSession() {
                const last = this.customSessions[this.customSessions.length - 1] || {};
                this.customSessions.push({
                    date: '',
                    checkin_start_time: last.checkin_start_time || '',
                    checkin_end_time: last.checkin_end_time || '',
                    checkout_start_time: last.checkout_start_time || '',
                    checkout_end_time: last.checkout_end_time || '',
                });
            },
            removeCustomSession(idx) {
                if (this.customSessions.length === 1) return;
                this.customSessions.splice(idx, 1);
            },
            formatDate(dateStr) {
                if (!dateStr) return '';
                const d = new Date(dateStr);
                return d.toLocaleDateString('en-GB', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' });
            },
            formatDayLabel(dateStr) {
                if (!dateStr) return '';
                const d = new Date(dateStr);
                return d.toLocaleDateString('en-GB', { weekday: 'short', day: 'numeric', month: 'short' });
            },
            // "08:00:00" reads better as "08:00"
            trimTime(timeStr) {
                return timeStr ? String(timeStr).slice(0, 5) : '';
            }
        }
    }
    </script>
</x-app-layout>
