<x-app-layout>
    <x-slot name="title">Attendance QR Code</x-slot>
    <x-slot name="breadcrumb">
        <a href="{{ route('dashboard') }}" class="text-blue-600 hover:underline">Home</a>
        <span class="mx-1">/</span>
        <a href="{{ route('attendance.index') }}" class="text-blue-600 hover:underline">Attendance</a>
        <span class="mx-1">/</span>
        <span class="text-gray-500">QR Code</span>
    </x-slot>
    <div class="min-h-screen flex flex-col justify-center items-center bg-gray-50 py-8" x-data="{
        idType: 'ic',
        manualIc: '',
        manualPassport: '',
        searchLoading: false,
        participant: null,
        participantHistory: [],
        showModal: false,
        message: '',
        messageType: '',
        async searchParticipant() {
            if (this.idType === 'ic' && !this.manualIc) {
                this.message = 'Please enter IC.';
                this.messageType = 'error';
                return;
            }
            if (this.idType === 'passport' && !this.manualPassport) {
                this.message = 'Please enter Passport.';
                this.messageType = 'error';
                return;
            }
            this.searchLoading = true;
            this.message = '';
            this.participant = null;
            try {
                const params = new URLSearchParams();
                params.append('id_type', this.idType);
                if (this.idType === 'ic') {
                    const normalized = this.manualIc.replace(/\D/g, '');
                    params.append('ic', normalized);
                } else {
                    params.append('passport', this.manualPassport.trim());
                }
                const res = await fetch(`/attendance/{{ $attendance->id }}/search-participant?${params.toString()}`);
                const data = await res.json();
                if (!data.success) {
                    this.message = data.message || 'Participant not found.';
                    this.messageType = 'error';
                    return;
                }
                this.participant = data.data.participant;
                this.participantHistory = data.data.history || [];
                this.showModal = true;
                this.messageType = 'success';
            } catch (e) {
                this.message = 'Network error during search.';
                this.messageType = 'error';
            } finally {
                this.searchLoading = false;
            }
        },
        async confirmCheckIn() {
            if (!this.participant) return;
            this.searchLoading = true;
            this.message = '';
            try {
                const res = await fetch(`/attendance/{{ $attendance->id }}/checkin-manual`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({ participant_id: this.participant.id })
                });
                const data = await res.json();
                if (!data.success) {
                    this.message = data.message || 'Check-in failed.';
                    this.messageType = 'error';
                    return;
                }
                this.message = data.message || 'Check-in successful for ' + this.participant.name + '!';
                this.messageType = 'success';
                this.showModal = false;
                this.participant = null;
                this.participantHistory = [];
                this.manualIc = '';
                this.manualPassport = '';
            } catch (e) {
                this.message = 'Network error during check-in.';
                this.messageType = 'error';
            } finally {
                this.searchLoading = false;
            }
        },
        formatIC(e) {
            let val = e.target.value.replace(/\D/g, '');
            let formatted = '';
            if (val.length > 6) {
                formatted = val.substring(0, 6) + '-';
                if (val.length > 8) {
                    formatted += val.substring(6, 8) + '-';
                    formatted += val.substring(8, 12);
                } else {
                    formatted += val.substring(6);
                }
            } else {
                formatted = val;
            }
            e.target.value = formatted;
            this.manualIc = formatted;
        }
    }">
        <div class="relative flex flex-col items-center">
            <!-- Minimalist Fullscreen Icon -->
            <button type="button" onclick="toggleFullScreen()" title="Full Screen" class="absolute -top-8 right-0 z-20">
                <span class="material-icons text-gray-500 text-xl hover:text-gray-700">fullscreen</span>
            </button>
            <div id="qrCard" class="bg-white rounded shadow-md border border-gray-300 p-8 flex flex-col items-center transition-all duration-200">
                <div class="mb-4 text-center">
                    <h1 class="text-lg font-bold text-gray-800 flex items-center justify-center">
                        <span class="material-icons text-primary-DEFAULT mr-2">qr_code</span>
                        Attendance QR Codes
                    </h1>
                    <div class="text-xs text-gray-500 mt-1">{{ $attendance->event->name ?? '-' }}</div>
                </div>

                @if($sessionsWithQR->isEmpty())
                    <div class="text-center py-8 text-gray-500 text-xs">
                        <span class="material-icons text-4xl mb-2">event_busy</span>
                        <div>No active sessions available at this time</div>
                    </div>
                @else
                    @php $sess = $sessionsWithQR->first(); @endphp
                    <div class="w-full max-w-md">
                        <div class="text-center mb-3">
                            <div class="font-semibold text-base text-gray-700">{{ \Carbon\Carbon::parse($sess['date'])->format('l, d F Y') }}</div>
                            <div class="text-xs text-gray-600 mt-2 flex items-center justify-center gap-2">
                                @if($sess['session_type'] === 'checkin')
                                    <span class="inline-flex items-center gap-1 bg-blue-100 text-blue-800 px-3 py-1 rounded font-semibold">
                                        <span class="material-icons text-sm">login</span>
                                        Check-In: {{ substr($sess['checkin_start_time'], 0, 5) }} - {{ substr($sess['checkin_end_time'], 0, 5) }}
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 bg-green-100 text-green-800 px-3 py-1 rounded font-semibold">
                                        <span class="material-icons text-sm">logout</span>
                                        Check-Out: {{ substr($sess['checkout_start_time'], 0, 5) }} - {{ substr($sess['checkout_end_time'], 0, 5) }}
                                    </span>
                                @endif
                                @if($sess['is_active_now'])
                                    <span class="inline-flex items-center px-3 py-1 bg-green-500 text-white rounded text-xs font-bold animate-pulse">ACTIVE NOW</span>
                                @endif
                            </div>
                        </div>
                        <div class="flex justify-center items-center">
                            {!! $sess['qr_svg'] !!}
                        </div>
                        <div class="mt-4 text-xs text-gray-400 text-center">
                            Session Code: <span class="font-mono">{{ $sess['unique_code'] }}</span>
                        </div>
                    </div>
                @endif

                <!-- Manual IC Check-in Section -->
                <div class="mt-8 w-full max-w-lg border-t border-gray-200 pt-6">
                    <div class="text-center mb-3">
                        <h3 class="text-sm font-semibold text-gray-700 flex items-center justify-center gap-2">
                            <span class="material-icons text-sm text-primary-DEFAULT">edit</span>
                            Manual Check-in (IC/Passport)
                        </h3>
                        <p class="text-xs text-gray-500 mt-1">Masukkan IC atau Passport untuk check-in tanpa scan</p>
                    </div>

                    <div class="flex gap-2 mb-3">
                        <select x-model="idType" class="px-3 py-2 border border-gray-300 rounded text-xs focus:border-primary-DEFAULT focus:ring focus:ring-primary-light focus:ring-opacity-50 leading-[1rem]">
                            <option value="ic">IC</option>
                            <option value="passport">Passport</option>
                        </select>
                        <input 
                            type="text" 
                            x-model="manualIc" 
                            x-show="idType === 'ic'"
                            @input="formatIC($event)"
                            placeholder="000000-00-0000" 
                            maxlength="14"
                            class="flex-1 px-3 py-2 border border-gray-300 rounded text-xs focus:border-primary-DEFAULT focus:ring focus:ring-primary-light focus:ring-opacity-50"
                            @keyup.enter="searchParticipant()"
                        >
                        <input 
                            type="text" 
                            x-model="manualPassport" 
                            x-show="idType === 'passport'"
                            placeholder="A12345678" 
                            class="flex-1 px-3 py-2 border border-gray-300 rounded text-xs focus:border-primary-DEFAULT focus:ring focus:ring-primary-light focus:ring-opacity-50"
                            @keyup.enter="searchParticipant()"
                        >
                        <button 
                            type="button" 
                            @click="searchParticipant()" 
                            :disabled="searchLoading"
                            class="px-4 py-2 bg-blue-600 text-white rounded text-xs font-semibold hover:bg-blue-700 flex items-center gap-1 transition-colors"
                        >
                            <span class="material-icons text-sm" x-show="!searchLoading">search</span>
                            <span x-show="searchLoading" class="inline-block w-3 h-3 border-2 border-white border-t-transparent rounded-full animate-spin"></span>
                            <span x-text="searchLoading ? 'Searching...' : 'Search'"></span>
                        </button>
                    </div>

                    <!-- Message -->
                    <div x-show="message" :class="{
                        'bg-red-50 border-red-200 text-red-700': messageType === 'error',
                        'bg-green-50 border-green-200 text-green-700': messageType === 'success'
                    }" class="px-3 py-2 border rounded text-xs mb-3" x-transition>
                        <span x-text="message"></span>
                    </div>
                </div>

                <!-- Modal for Participant Details & History (MOVED INSIDE #qrCard) -->
                <div x-show="showModal" style="display:none" x-transition>
                    <div class="absolute inset-0 bg-black bg-opacity-50" style="z-index: 1000;" @click="showModal=false"></div>
                    <div class="absolute inset-0 flex items-center justify-center p-4" style="z-index: 1001; pointer-events: none;">
                        <div class="relative bg-white rounded-lg shadow-xl max-w-2xl w-full max-h-[80vh] overflow-y-auto pointer-events-auto">
                <div class="sticky top-0 bg-white border-b px-4 py-3 flex items-center justify-between">
                    <h3 class="font-semibold text-sm">Participant Info & Attendance History</h3>
                    <button @click="showModal=false" class="text-gray-500 hover:text-gray-700">
                        <span class="material-icons text-sm">close</span>
                    </button>
                </div>

                <div class="p-4 text-xs">
                    <!-- Participant Info -->
                    <div class="bg-blue-50 border border-blue-200 rounded p-3 mb-4">
                        <div class="font-semibold text-sm mb-2 text-blue-900">Basic Information</div>
                        <div class="space-y-1">
                            <div class="flex items-start">
                                <span class="font-semibold inline-block w-32">Name</span>
                                <span class="mx-1">:</span>
                                <span x-text="participant?.name || '-'"></span>
                            </div>
                            <div class="flex items-start" x-show="participant?.identity_card">
                                <span class="font-semibold inline-block w-32">IC</span>
                                <span class="mx-1">:</span>
                                <span x-text="participant?.identity_card || '-'"></span>
                            </div>
                            <div class="flex items-start" x-show="participant?.passport_no">
                                <span class="font-semibold inline-block w-32">Passport</span>
                                <span class="mx-1">:</span>
                                <span x-text="participant?.passport_no || '-'"></span>
                            </div>
                            <div class="flex items-start">
                                <span class="font-semibold inline-block w-32">Email</span>
                                <span class="mx-1">:</span>
                                <span x-text="participant?.email || '-'"></span>
                            </div>
                            <div class="flex items-start">
                                <span class="font-semibold inline-block w-32">Phone</span>
                                <span class="mx-1">:</span>
                                <span x-text="participant?.phone || '-'"></span>
                            </div>
                            <div class="flex items-start">
                                <span class="font-semibold inline-block w-32">Organization</span>
                                <span class="mx-1">:</span>
                                <span x-text="participant?.organization || '-'"></span>
                            </div>
                        </div>
                    </div>

                    <!-- Attendance History -->
                    <div class="mb-4">
                        <div class="font-semibold text-sm mb-2 text-gray-900">Attendance History</div>
                        <template x-if="participantHistory.length === 0">
                            <div class="text-center py-4 text-gray-500">No attendance records</div>
                        </template>
                        <template x-if="participantHistory.length > 0">
                            <div class="space-y-3">
                                <template x-for="item in participantHistory" :key="item.date">
                                    <div class="border border-gray-200 rounded p-3 bg-white">
                                        <div class="font-semibold mb-3 pb-2 border-b text-sm text-gray-800" x-text="'Date: ' + item.date"></div>
                                        <table class="w-full text-xs border-collapse">
                                            <thead>
                                                <tr class="bg-gray-50">
                                                    <th class="text-left py-2 px-2 font-semibold border-b w-1/3">Attendance</th>
                                                    <th class="text-left py-2 px-2 font-semibold border-b w-1/3">Time</th>
                                                    <th class="text-left py-2 px-2 font-semibold border-b w-1/3">Status</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr class="border-b">
                                                    <td class="py-2 px-2">Check-In</td>
                                                    <td class="py-2 px-2" x-text="item.checkin_time || '-'"></td>
                                                    <td class="py-2 px-2">
                                                        <span class="font-semibold" :class="{
                                                            'text-green-700': item.checkin_status === 'On Time',
                                                            'text-orange-600': item.checkin_status === 'Late',
                                                            'text-red-600': item.checkin_status === 'Absent',
                                                            'text-gray-500': item.checkin_status === 'Pending'
                                                        }" x-text="item.checkin_status || '-'"></span>
                                                    </td>
                                                </tr>
                                                <tr x-show="item.has_checkout">
                                                    <td class="py-2 px-2">Check-Out</td>
                                                    <td class="py-2 px-2" x-text="item.checkout_time || '-'"></td>
                                                    <td class="py-2 px-2">
                                                        <span class="font-semibold" :class="{
                                                            'text-green-700': item.checkout_status === 'On Time',
                                                            'text-orange-600': item.checkout_status === 'Early',
                                                            'text-red-600': item.checkout_status === 'Absent',
                                                            'text-gray-500': item.checkout_status === 'Pending'
                                                        }" x-text="item.checkout_status || '-'"></span>
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </template>
                            </div>
                        </template>
                    </div>

                    <!-- Actions -->
                    <div class="flex gap-2">
                        <button 
                            type="button" 
                            @click="confirmCheckIn()"
                            :disabled="searchLoading"
                            class="flex-1 px-4 py-2 bg-green-600 text-white rounded text-xs font-semibold hover:bg-green-700 flex items-center justify-center gap-1 transition-colors"
                        >
                            <span class="material-icons text-sm">check_circle</span>
                            <span>Confirm Check-in</span>
                        </button>
                        <button 
                            type="button" 
                            @click="showModal=false; participant=null; participantHistory=[]; manualIc=''; manualPassport=''; message=''"
                            class="px-4 py-2 bg-gray-200 text-gray-700 rounded text-xs font-semibold hover:bg-gray-300 transition-colors"
                        >
                            Cancel
                        </button>
                    </div>
                </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <style>
    .fullscreen-center {
        min-height: 100vh !important;
        display: flex !important;
        flex-direction: column !important;
        justify-content: center !important;
        align-items: center !important;
        padding: 0 !important;
    }
    #qrCard {
        position: relative;
    }
    </style>
    <script>
    function toggleFullScreen() {
        const card = document.getElementById('qrCard');
        if (!document.fullscreenElement) {
            card.requestFullscreen();
        } else {
            document.exitFullscreen();
        }
    }
    document.addEventListener('fullscreenchange', function() {
        const card = document.getElementById('qrCard');
        if (document.fullscreenElement === card) {
            card.classList.add('fullscreen-center');
        } else {
            card.classList.remove('fullscreen-center');
        }
    });
    </script>
</x-app-layout> 