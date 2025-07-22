<x-app-layout>
    <x-slot name="breadcrumb">
        <span>Attendance</span>
        <span class="mx-2 text-gray-500">/</span>
        <span>Create Attendance</span>
    </x-slot>

    <x-slot name="title">Create New Attendance</x-slot>

    <div class="bg-white rounded shadow-md border border-gray-300">
        <div class="p-6 border-b border-gray-200">
            <div class="flex items-center">
                <span class="material-icons mr-2 text-primary-DEFAULT">how_to_reg</span>
                <h1 class="text-xl font-bold text-gray-800">Create New Attendance</h1>
            </div>
            <p class="text-xs text-gray-500 mt-1 ml-8">Add a new attendance session for an event</p>
        </div>
        <div class="p-6" x-data="attendanceForm()">
            <form method="POST" action="{{ route('attendance.store') }}" class="space-y-8">
                @csrf
                <!-- Step 1: Select Event -->
                <div class="border-b border-gray-200 pb-5">
                    <h2 class="text-sm font-semibold text-gray-700 mb-4">1. Select Event</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="event_id" class="flex items-center text-xs font-medium text-gray-700 mb-1">
                                <span class="material-icons text-sm mr-1 text-primary-DEFAULT">event</span>
                                Event
                            </label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <span class="material-icons text-[#004aad] text-base">event</span>
                                </div>
                                <select name="event_id" id="event_id" class="w-full text-xs border-gray-300 rounded-[1px] pl-12 focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50" x-model="selectedEventId" @change="updateEventInfo()" required>
                                    <option value="">-- Select Event --</option>
                                    <template x-for="event in events" :key="event.id">
                                        <option :value="event.id" x-text="event.name"></option>
                                    </template>
                                </select>
                            </div>
                            <p class="mt-1 text-[10px] text-gray-500">Choose an active event for this attendance session</p>
                        </div>
                        <template x-if="selectedEvent">
                            <div class="bg-gray-50 border border-gray-200 rounded p-3 text-xs text-gray-700">
                                <div class="mb-1 font-semibold" x-text="selectedEvent.name"></div>
                                <div>
                                    <span class="font-medium">Date:</span>
                                    <span x-text="formatDate(selectedEvent.start_date)"></span>
                                    <template x-if="selectedEvent.end_date && selectedEvent.end_date !== selectedEvent.start_date">
                                        <span> - <span x-text="formatDate(selectedEvent.end_date)"></span></span>
                                    </template>
                                </div>
                                <div><span class="font-medium">Time:</span> <span x-text="selectedEvent.start_time"></span> - <span x-text="selectedEvent.end_time"></span></div>
                                <div><span class="font-medium">Location:</span> <span x-text="selectedEvent.location"></span></div>
                            </div>
                        </template>
                    </div>
                </div>

                <!-- Step 2: Attendance Type -->
                <div class="border-b border-gray-200 pb-5">
                    <h2 class="text-sm font-semibold text-gray-700 mb-4">2. Attendance Type</h2>
                    <div class="flex flex-col md:flex-row gap-4">
                        <label class="flex items-center cursor-pointer">
                            <input type="radio" name="attendance_type" value="single" x-model="attendanceType" class="mr-2">
                            <span class="text-xs">Single session (once for the whole event)</span>
                        </label>
                        <label class="flex items-center cursor-pointer">
                            <input type="radio" name="attendance_type" value="daily" x-model="attendanceType" class="mr-2">
                            <span class="text-xs">Every day (auto session for each day)</span>
                        </label>
                        <label class="flex items-center cursor-pointer">
                            <input type="radio" name="attendance_type" value="custom" x-model="attendanceType" class="mr-2">
                            <span class="text-xs">Custom sessions (add multiple sessions manually)</span>
                        </label>
                    </div>
                    <div class="mt-4">
                        <label class="flex items-center cursor-pointer">
                            <input type="checkbox" x-model="enableCheckout" class="mr-2">
                            <span class="text-xs">Enable Checkout (if unchecked, participants only need to check in)</span>
                        </label>
                    </div>
                </div>

                <!-- Step 3: Session Input -->
                <div class="border-b border-gray-200 pb-5">
                    <h2 class="text-sm font-semibold text-gray-700 mb-4">3. Attendance Sessions</h2>
                    <!-- Single session -->
                    <template x-if="attendanceType === 'single' && selectedEvent">
                        <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
                            <div>
                                <label class="text-xs font-medium text-gray-700 mb-1 flex items-center"><span class="material-icons text-sm mr-1 text-primary-DEFAULT">calendar_today</span>Date</label>
                                <input type="date" name="sessions[0][date]" class="w-full text-xs border-gray-300 rounded-[1px]" :value="selectedEvent.start_date" required>
                            </div>
                            <div>
                                <label class="text-xs font-semibold text-gray-700 mb-1 flex items-center"><span class="material-icons text-sm mr-1 text-primary-DEFAULT">login</span>Check-in Start</label>
                                <input type="time" name="sessions[0][checkin_start_time]" class="w-full text-xs border-gray-300 rounded-[1px]" :value="selectedEvent.start_time" required>
                            </div>
                            <div>
                                <label class="text-xs font-semibold text-gray-700 mb-1 flex items-center"><span class="material-icons text-sm mr-1 text-primary-DEFAULT">login</span>Check-in End</label>
                                <input type="time" name="sessions[0][checkin_end_time]" class="w-full text-xs border-gray-300 rounded-[1px]" :value="selectedEvent.end_time" required>
                            </div>
                            <template x-if="enableCheckout">
                                <div>
                                    <label class="text-xs font-semibold text-gray-700 mb-1 flex items-center"><span class="material-icons text-sm mr-1 text-primary-DEFAULT">logout</span>Check-out Start</label>
                                    <input type="time" name="sessions[0][checkout_start_time]" class="w-full text-xs border-gray-300 rounded-[1px]" :value="selectedEvent.start_time">
                                </div>
                            </template>
                            <template x-if="enableCheckout">
                                <div>
                                    <label class="text-xs font-semibold text-gray-700 mb-1 flex items-center"><span class="material-icons text-sm mr-1 text-primary-DEFAULT">logout</span>Check-out End</label>
                                    <input type="time" name="sessions[0][checkout_end_time]" class="w-full text-xs border-gray-300 rounded-[1px]" :value="selectedEvent.end_time">
                                </div>
                            </template>
                        </div>
                    </template>
                    <!-- Daily session -->
                    <template x-if="attendanceType === 'daily' && selectedEvent">
                        <div class="space-y-4">
                            <template x-for="(day, idx) in eventDays" :key="day.date">
                                <div class="grid grid-cols-1 md:grid-cols-5 gap-4 items-center">
                                    <div>
                                        <label class="text-xs font-medium text-gray-700 mb-1 flex items-center"><span class="material-icons text-sm mr-1 text-primary-DEFAULT">calendar_today</span>Date</label>
                                        <input type="date" :name="`sessions[${idx}][date]`" class="w-full text-xs border-gray-300 rounded-[1px]" :value="day.date" readonly>
                                    </div>
                                    <div>
                                        <label class="text-xs font-semibold text-gray-700 mb-1 flex items-center"><span class="material-icons text-sm mr-1 text-primary-DEFAULT">login</span>Check-in Start</label>
                                        <input type="time" :name="`sessions[${idx}][checkin_start_time]`" class="w-full text-xs border-gray-300 rounded-[1px]" required>
                                    </div>
                                    <div>
                                        <label class="text-xs font-semibold text-gray-700 mb-1 flex items-center"><span class="material-icons text-sm mr-1 text-primary-DEFAULT">login</span>Check-in End</label>
                                        <input type="time" :name="`sessions[${idx}][checkin_end_time]`" class="w-full text-xs border-gray-300 rounded-[1px]" required>
                                    </div>
                                    <template x-if="enableCheckout">
                                        <div>
                                            <label class="text-xs font-semibold text-gray-700 mb-1 flex items-center"><span class="material-icons text-sm mr-1 text-primary-DEFAULT">logout</span>Check-out Start</label>
                                            <input type="time" :name="`sessions[${idx}][checkout_start_time]`" class="w-full text-xs border-gray-300 rounded-[1px]">
                                        </div>
                                    </template>
                                    <template x-if="enableCheckout">
                                        <div>
                                            <label class="text-xs font-semibold text-gray-700 mb-1 flex items-center"><span class="material-icons text-sm mr-1 text-primary-DEFAULT">logout</span>Check-out End</label>
                                            <input type="time" :name="`sessions[${idx}][checkout_end_time]`" class="w-full text-xs border-gray-300 rounded-[1px]">
                                        </div>
                                    </template>
                                </div>
                            </template>
                        </div>
                    </template>
                    <!-- Custom sessions -->
                    <template x-if="attendanceType === 'custom'">
                        <div class="space-y-4">
                            <template x-for="(session, idx) in customSessions" :key="idx">
                                <div class="grid grid-cols-1 gap-4" :class="enableCheckout ? 'md:grid-cols-5' : 'md:grid-cols-3'">
                                    <div>
                                        <label class="text-xs font-medium text-gray-700 mb-1 flex items-center"><span class="material-icons text-sm mr-1 text-primary-DEFAULT">calendar_today</span>Date</label>
                                        <input type="date" :name="`sessions[${idx}][date]`" class="w-full text-xs border-gray-300 rounded-[1px]" x-model="session.date" required>
                                    </div>
                                    <div>
                                        <label class="text-xs font-semibold text-gray-700 mb-1 flex items-center"><span class="material-icons text-sm mr-1 text-primary-DEFAULT">login</span>Check-in Start</label>
                                        <input type="time" :name="`sessions[${idx}][checkin_start_time]`" class="w-full text-xs border-gray-300 rounded-[1px]" x-model="session.checkin_start_time" required>
                                    </div>
                                    <div>
                                        <label class="text-xs font-semibold text-gray-700 mb-1 flex items-center"><span class="material-icons text-sm mr-1 text-primary-DEFAULT">login</span>Check-in End</label>
                                        <input type="time" :name="`sessions[${idx}][checkin_end_time]`" class="w-full text-xs border-gray-300 rounded-[1px]" x-model="session.checkin_end_time" required>
                                    </div>
                                    <template x-if="enableCheckout">
                                        <div>
                                            <label class="text-xs font-semibold text-gray-700 mb-1 flex items-center"><span class="material-icons text-sm mr-1 text-primary-DEFAULT">logout</span>Check-out Start</label>
                                            <input type="time" :name="`sessions[${idx}][checkout_start_time]`" class="w-full text-xs border-gray-300 rounded-[1px]" x-model="session.checkout_start_time">
                                        </div>
                                    </template>
                                    <template x-if="enableCheckout">
                                        <div class="flex items-center w-full gap-2">
                                            <div class="flex-1">
                                                <label class="text-xs font-semibold text-gray-700 mb-1 flex items-center"><span class="material-icons text-sm mr-1 text-primary-DEFAULT">logout</span>Check-out End</label>
                                                <input type="time" :name="`sessions[${idx}][checkout_end_time]`" class="w-full text-xs border-gray-300 rounded-[1px]" x-model="session.checkout_end_time">
                                            </div>
                                            <button type="button" @click="removeCustomSession(idx)" class="ml-2 mt-6">
                                                <span class="material-icons text-red-600 text-xl">delete</span>
                                            </button>
                                        </div>
                                    </template>
                                    <template x-if="!enableCheckout">
                                        <div class="flex items-center h-full pt-6">
                                            <button type="button" @click="removeCustomSession(idx)" class="ml-2 mt-6">
                                                <span class="material-icons text-red-600 text-xl">delete</span>
                                            </button>
                                        </div>
                                    </template>
                                </div>
                            </template>
                            <button type="button" class="mt-2 px-3 py-1 bg-gradient-to-r from-blue-600 to-blue-500 hover:from-blue-700 hover:to-blue-600 text-white rounded shadow-sm text-xs font-medium flex items-center" @click="addCustomSession()">
                                <span class="material-icons text-xs mr-1">add_circle</span>
                                Add Session
                            </button>
                        </div>
                    </template>
                </div>

                <!-- Step 4: Status -->
                <div class="border-b border-gray-200 pb-5">
                    <h2 class="text-sm font-semibold text-gray-700 mb-4">4. Status</h2>
                    <div class="w-full md:w-1/3">
                        <label for="status" class="flex items-center text-xs font-medium text-gray-700 mb-1">
                            <span class="material-icons text-sm mr-1 text-primary-DEFAULT">toggle_on</span>
                            Status
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <span class="material-icons text-[#004aad] text-base">shield</span>
                            </div>
                            <select name="status" id="status" class="w-full text-xs border-gray-300 rounded-[1px] pl-12 focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50" required>
                                <option value="active">Active</option>
                                <option value="expired">Expired</option>
                                <option value="completed">Completed</option>
                            </select>
                        </div>
                        <p class="mt-1 text-[10px] text-gray-500">Current status of this attendance session</p>
                    </div>
                </div>
                <!-- Step 5: Buttons -->
                <div class="pt-6 flex justify-end space-x-3">
                    <a href="{{ route('attendance.index') }}" class="px-3 py-1 bg-gradient-to-r from-red-600 to-red-500 hover:from-red-700 hover:to-red-600 text-white rounded shadow-sm text-xs font-medium transition-colors duration-200 ease-in-out flex items-center">
                        <span class="material-icons text-xs mr-1">cancel</span>
                        Cancel
                    </a>
                    <button type="submit" class="px-3 py-1 bg-gradient-to-r from-blue-600 to-blue-500 hover:from-blue-700 hover:to-blue-600 text-white rounded shadow-sm text-xs font-medium transition-colors duration-200 ease-in-out flex items-center">
                        <span class="material-icons text-xs mr-1">save</span>
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
            selectedEventId: '',
            selectedEvent: null,
            attendanceType: 'single',
            enableCheckout: true,
            eventDays: [],
            customSessions: [
                {date: '', checkin_start_time: '', checkin_end_time: '', checkout_start_time: '', checkout_end_time: ''}
            ],
            updateEventInfo() {
                this.selectedEvent = this.events.find(e => e.id == this.selectedEventId);
                if (this.selectedEvent) {
                    // Generate eventDays for daily attendance
                    const start = new Date(this.selectedEvent.start_date);
                    const end = new Date(this.selectedEvent.end_date);
                    this.eventDays = [];
                    for (let d = new Date(start); d <= end; d.setDate(d.getDate() + 1)) {
                        this.eventDays.push({date: d.toISOString().slice(0,10)});
                    }
                } else {
                    this.eventDays = [];
                }
            },
            addCustomSession() {
                this.customSessions.push({date: '', checkin_start_time: '', checkin_end_time: '', checkout_start_time: '', checkout_end_time: ''});
            },
            removeCustomSession(idx) {
                this.customSessions.splice(idx, 1);
            },
            formatDate(dateStr) {
                if (!dateStr) return '';
                const d = new Date(dateStr);
                return d.toLocaleDateString('en-GB', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' });
            }
        }
    }
    </script>
</x-app-layout>
