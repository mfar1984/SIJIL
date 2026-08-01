@props(['event' => null, 'existing' => null])

{{--
    Scan times, set on the event form itself.

    The standalone /attendance/create form reads its dates from an event that is
    already saved. This one reads them from the event form's own inputs as they
    are typed, so attendance can be set up in the same pass as the event instead
    of making the operator come back for a second round.

    Field names are prefixed with attendance_ so they cannot collide with the
    event's own start_date / start_time inputs.
--}}
<div x-data="attendancePicker()"
     x-show="visible"
     x-cloak
     x-transition
     class="mt-3 border border-gray-200 rounded">

    <div class="bg-gray-50 border-b border-gray-200 px-4 py-2.5">
        <h3 class="text-xs font-semibold text-gray-700 flex items-center">
            <span class="material-icons-outlined text-primary-DEFAULT text-base mr-2">qr_code_2</span>
            Scan times
        </h3>
    </div>

    @if($existing)
        {{-- Already set up. Editing the windows belongs on the attendance form,
             which owns session add/remove and the QR codes. --}}
        <div class="p-4">
            <p class="text-xs text-gray-700">
                <span class="material-icons-outlined text-sm text-green-600 align-middle">check_circle</span>
                Set up as
                <span class="font-medium">
                    @switch($existing->attendance_type)
                        @case('daily') Scan every day @break
                        @case('custom') Custom sessions @break
                        @default Scan once
                    @endswitch
                </span>
                &mdash; {{ $existing->sessions->count() }} QR code(s)
            </p>
            <a href="{{ route('attendance.edit', $existing->id) }}"
               class="inline-flex items-center mt-2 text-xs text-primary-DEFAULT hover:underline">
                <span class="material-icons-outlined text-sm mr-1">edit</span>
                Change the scan times
            </a>
        </div>
    @else
        <div class="p-4 space-y-4">
            {{-- Mode --}}
            <div class="flex flex-col md:flex-row md:items-start gap-3">
                <label class="text-xs font-medium text-gray-700 md:w-40 shrink-0 md:pt-1">
                    How often do they scan?
                </label>
                <div class="flex-1 grid grid-cols-1 md:grid-cols-3 gap-2">
                    <label class="cursor-pointer border rounded p-3 flex gap-2 h-full transition-colors"
                           :class="type === 'single' ? 'border-primary-DEFAULT bg-primary-DEFAULT/5' : 'border-gray-200 hover:border-gray-300'">
                        <input type="radio" name="attendance_type" value="single" x-model="type" class="mt-0.5 shrink-0">
                        <span>
                            <span class="block text-xs font-medium text-gray-800">Scan once</span>
                            <span class="block text-xs text-gray-500 mt-0.5">
                                One QR code for the whole event, taken from the dates and times above.
                            </span>
                            <span class="block text-xs text-gray-400 mt-1">1 QR code</span>
                        </span>
                    </label>

                    <label class="cursor-pointer border rounded p-3 flex gap-2 h-full transition-colors"
                           :class="type === 'daily' ? 'border-primary-DEFAULT bg-primary-DEFAULT/5' : 'border-gray-200 hover:border-gray-300'">
                        <input type="radio" name="attendance_type" value="daily" x-model="type" class="mt-0.5 shrink-0">
                        <span>
                            <span class="block text-xs font-medium text-gray-800">Scan every day</span>
                            <span class="block text-xs text-gray-500 mt-0.5">
                                A separate QR code for each day, so a code only works on its own day.
                            </span>
                            <span class="block text-xs text-gray-400 mt-1"
                                  x-text="days.length + ' QR code' + (days.length === 1 ? '' : 's') + ', one per day'"></span>
                        </span>
                    </label>

                    <label class="cursor-pointer border rounded p-3 flex gap-2 h-full transition-colors"
                           :class="type === 'custom' ? 'border-primary-DEFAULT bg-primary-DEFAULT/5' : 'border-gray-200 hover:border-gray-300'">
                        <input type="radio" name="attendance_type" value="custom" x-model="type" class="mt-0.5 shrink-0">
                        <span>
                            <span class="block text-xs font-medium text-gray-800">Let me choose</span>
                            <span class="block text-xs text-gray-500 mt-0.5">
                                Set each window yourself. The only mode that can also record check-out.
                            </span>
                            <span class="block text-xs text-gray-400 mt-1"
                                  x-text="(sessions.length * (checkout ? 2 : 1)) + ' QR code' + ((sessions.length * (checkout ? 2 : 1)) === 1 ? '' : 's')"></span>
                        </span>
                    </label>
                </div>
            </div>

            {{-- Waiting on the event dates --}}
            <template x-if="!hasDates">
                <div class="flex flex-col md:flex-row md:items-start gap-3">
                    <span class="text-xs font-medium text-gray-700 md:w-40 shrink-0">Windows</span>
                    <p class="flex-1 text-xs text-amber-700 bg-amber-50 border border-amber-200 rounded px-3 py-2">
                        Fill in the event start and end dates above and the scan windows will appear here.
                    </p>
                </div>
            </template>

            {{-- Automatic modes: show what will be created, submit it hidden. --}}
            <template x-if="hasDates && type !== 'custom'">
                <div class="flex flex-col md:flex-row md:items-start gap-3">
                    <span class="text-xs font-medium text-gray-700 md:w-40 shrink-0 md:pt-2">
                        <span x-text="type === 'daily' ? 'QR codes' : 'QR code'"></span>
                    </span>
                    <div class="flex-1">
                        <div class="border border-gray-200 rounded divide-y divide-gray-100">
                            <template x-for="(day, idx) in autoRows" :key="day.date">
                                <div class="px-3 py-2 flex flex-wrap items-center gap-x-6 gap-y-1">
                                    <template x-if="type === 'daily'">
                                        <span class="text-xs font-medium text-gray-800 w-16 shrink-0"
                                              x-text="'Day ' + (idx + 1)"></span>
                                    </template>
                                    <span class="text-xs text-gray-700" x-text="dayLabel(day.date)"></span>
                                    <span class="text-xs text-gray-500">
                                        Valid <span x-text="day.checkin_start_time"></span> &ndash; <span x-text="day.checkin_end_time"></span>
                                    </span>
                                    <span class="text-xs text-gray-400">Check-in only</span>

                                    <input type="hidden" :name="`attendance_sessions[${idx}][date]`" :value="day.date">
                                    <input type="hidden" :name="`attendance_sessions[${idx}][checkin_start_time]`" :value="day.checkin_start_time">
                                    <input type="hidden" :name="`attendance_sessions[${idx}][checkin_end_time]`" :value="day.checkin_end_time">
                                </div>
                            </template>
                        </div>
                        <p class="text-xs text-gray-500 mt-2" x-text="autoHint"></p>
                    </div>
                </div>
            </template>

            {{-- Manual mode --}}
            <template x-if="type === 'custom'">
                <div class="space-y-3">
                    <div class="flex flex-col md:flex-row md:items-start gap-3">
                        <label class="text-xs font-medium text-gray-700 md:w-40 shrink-0 md:pt-1">Check-out</label>
                        <div class="flex-1">
                            <label class="flex items-start gap-2 cursor-pointer">
                                <input type="checkbox" name="attendance_enable_checkout" value="1"
                                       x-model="checkout" class="mt-0.5 shrink-0">
                                <span>
                                    <span class="block text-xs text-gray-800">Participants also scan when leaving</span>
                                    <span class="block text-xs text-gray-500 mt-0.5">
                                        Adds a second QR code per session and records how long people stayed.
                                    </span>
                                </span>
                            </label>
                        </div>
                    </div>

                    <template x-for="(s, idx) in sessions" :key="idx">
                        <div class="flex flex-col md:flex-row md:items-start gap-3">
                            <label class="text-xs font-medium text-gray-700 md:w-40 shrink-0 md:pt-2"
                                   x-text="'Session ' + (idx + 1)"></label>
                            <div class="flex-1 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-2">
                                <div>
                                    <span class="block text-xs text-gray-500 mb-1">Date</span>
                                    <input type="date" :name="`attendance_sessions[${idx}][date]`" x-model="s.date"
                                           :min="startDate" :max="endDate"
                                           class="w-full h-9 text-xs border-gray-300 rounded px-3">
                                </div>
                                <div>
                                    <span class="block text-xs text-gray-500 mb-1">Check-in opens</span>
                                    <input type="time" :name="`attendance_sessions[${idx}][checkin_start_time]`"
                                           x-model="s.checkin_start_time"
                                           class="w-full h-9 text-xs border-gray-300 rounded px-3">
                                </div>
                                <div>
                                    <span class="block text-xs text-gray-500 mb-1">Check-in closes</span>
                                    <input type="time" :name="`attendance_sessions[${idx}][checkin_end_time]`"
                                           x-model="s.checkin_end_time"
                                           class="w-full h-9 text-xs border-gray-300 rounded px-3">
                                </div>
                                <template x-if="checkout">
                                    <div>
                                        <span class="block text-xs text-gray-500 mb-1">Check-out opens</span>
                                        <input type="time" :name="`attendance_sessions[${idx}][checkout_start_time]`"
                                               x-model="s.checkout_start_time"
                                               class="w-full h-9 text-xs border-gray-300 rounded px-3">
                                    </div>
                                </template>
                                <template x-if="checkout">
                                    <div>
                                        <span class="block text-xs text-gray-500 mb-1">Check-out closes</span>
                                        <input type="time" :name="`attendance_sessions[${idx}][checkout_end_time]`"
                                               x-model="s.checkout_end_time"
                                               class="w-full h-9 text-xs border-gray-300 rounded px-3">
                                    </div>
                                </template>
                                <div class="flex items-end">
                                    <button type="button" @click="removeSession(idx)"
                                            x-show="sessions.length > 1"
                                            class="h-9 px-3 text-xs border border-gray-300 rounded text-gray-600 hover:bg-gray-50 inline-flex items-center">
                                        <span class="material-icons-outlined text-sm mr-1">delete_outline</span>
                                        Remove
                                    </button>
                                </div>
                            </div>
                        </div>
                    </template>

                    <div class="flex flex-col md:flex-row gap-3">
                        <span class="md:w-40 shrink-0"></span>
                        <div class="flex-1">
                            <button type="button" @click="addSession()"
                                    class="h-9 px-3 text-xs border border-gray-300 rounded text-gray-700 hover:bg-gray-50 inline-flex items-center">
                                <span class="material-icons-outlined text-sm mr-1">add</span>
                                Add another session
                            </button>
                        </div>
                    </div>
                </div>
            </template>

            {{-- Summary --}}
            <div class="flex flex-col md:flex-row md:items-start gap-3 pt-1 border-t border-gray-100">
                <span class="text-xs font-medium text-gray-700 md:w-40 shrink-0 md:pt-2">Summary</span>
                <p class="flex-1 text-xs text-gray-600 bg-gray-50 border border-gray-200 rounded px-3 py-2"
                   x-text="summary"></p>
            </div>
        </div>
    @endif
</div>

{{-- Defined inline rather than pushed to a stack: this layout has no
     @stack('scripts'), and @once keeps it to a single definition even if the
     component is ever rendered twice on one page. --}}
@once
<script>
function attendancePicker() {
    return {
        type: '{{ old('attendance_type', 'single') }}',
        checkout: {{ old('attendance_enable_checkout') ? 'true' : 'false' }},
        visible: false,
        startDate: '',
        endDate: '',
        startTime: '',
        endTime: '',
        sessions: [{date: '', checkin_start_time: '', checkin_end_time: '', checkout_start_time: '', checkout_end_time: ''}],

        init() {
            // The event's own inputs are the source of truth for dates and times,
            // so watch them rather than duplicating the values.
            const watch = ['start_date', 'end_date', 'start_time', 'end_time'];
            watch.forEach(name => {
                const el = document.querySelector(`[name="${name}"]`);
                if (el) {
                    el.addEventListener('change', () => this.readEvent());
                    el.addEventListener('input', () => this.readEvent());
                }
            });

            const toggle = document.querySelector('[name="attendance_required"]');
            if (toggle) {
                this.visible = toggle.checked;
                toggle.addEventListener('change', () => { this.visible = toggle.checked; });
            }

            this.readEvent();

            if (!this.sessions[0].date && this.startDate) {
                this.sessions[0] = {
                    date: this.startDate,
                    checkin_start_time: this.startTime,
                    checkin_end_time: this.endTime,
                    checkout_start_time: '',
                    checkout_end_time: '',
                };
            }
        },

        readEvent() {
            const val = (name) => document.querySelector(`[name="${name}"]`)?.value || '';
            this.startDate = val('start_date');
            this.endDate = val('end_date') || this.startDate;
            this.startTime = (val('start_time') || '08:00').slice(0, 5);
            this.endTime = (val('end_time') || '17:00').slice(0, 5);
        },

        get hasDates() {
            return this.startDate !== '';
        },

        // Every date from the event start to the event end, inclusive.
        get days() {
            if (!this.startDate) return [];

            const out = [];
            const start = new Date(this.startDate + 'T00:00:00');
            const end = new Date((this.endDate || this.startDate) + 'T00:00:00');

            if (isNaN(start) || isNaN(end) || end < start) return [];

            // A guard so a mistyped year cannot spin here forever.
            for (let d = new Date(start), i = 0; d <= end && i < 366; d.setDate(d.getDate() + 1), i++) {
                out.push(d.toISOString().slice(0, 10));
            }

            return out;
        },

        get autoRows() {
            const rows = this.type === 'daily' ? this.days : this.days.slice(0, 1);

            return rows.map(date => ({
                date,
                checkin_start_time: this.startTime,
                checkin_end_time: this.endTime,
            }));
        },

        get autoHint() {
            if (this.type === 'daily') {
                return 'One QR code per day, taken from the event schedule. Each day\'s code only works on that day.';
            }

            return 'Taken from the event schedule. Any scan inside this window counts as attending, so nobody is marked late.';
        },

        get summary() {
            if (!this.hasDates && this.type !== 'custom') {
                return 'Fill in the event dates above to see what will be created.';
            }

            if (this.type === 'single') {
                return `1 QR code, valid on ${this.dayLabel(this.startDate)} from ${this.startTime} to ${this.endTime}. Check-in only.`;
            }

            if (this.type === 'daily') {
                const n = this.days.length;
                return `${n} QR code${n === 1 ? '' : 's'}, one for each day from ${this.startTime} to ${this.endTime}. Check-in only.`;
            }

            const total = this.sessions.length * (this.checkout ? 2 : 1);
            return `${this.sessions.length} session${this.sessions.length === 1 ? '' : 's'}, ${total} QR code${total === 1 ? '' : 's'}`
                + (this.checkout ? ', with check-out.' : ', check-in only.');
        },

        dayLabel(date) {
            if (!date) return '';
            const d = new Date(date + 'T00:00:00');
            return isNaN(d) ? date : d.toLocaleDateString('en-MY', {weekday: 'short', day: 'numeric', month: 'short'});
        },

        addSession() {
            const last = this.sessions[this.sessions.length - 1];
            this.sessions.push({
                date: last?.date || this.startDate,
                checkin_start_time: last?.checkin_start_time || this.startTime,
                checkin_end_time: last?.checkin_end_time || this.endTime,
                checkout_start_time: '',
                checkout_end_time: '',
            });
        },

        removeSession(idx) {
            if (this.sessions.length > 1) {
                this.sessions.splice(idx, 1);
            }
        },
    };
}
</script>
@endonce
