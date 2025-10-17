<x-app-layout>
    <x-slot name="breadcrumb">
        <span>Reports</span>
        <span class="mx-2 text-gray-500">/</span>
        <span>Attendance Reports</span>
        <span class="mx-2 text-gray-500">/</span>
        <span>Attendance Details</span>
    </x-slot>

    <x-slot name="title">Attendance Details</x-slot>

    <!-- Alpine.js initialization for interactive charts -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <div class="bg-white rounded shadow-md border border-gray-300">
        <div class="p-6 border-b border-gray-200">
            <div class="flex justify-between items-start">
                <div>
                    <div class="flex items-center">
                        <span class="material-icons mr-2 text-primary-DEFAULT">event_available</span>
                        <h1 class="text-xl font-bold text-gray-800">Attendance Details</h1>
                    </div>
                    <p class="text-xs text-gray-500 mt-1 ml-8">
                        Event: {{ $session->attendance->event->name ?? 'Unknown Event' }}
                    </p>
                </div>
                <div class="flex space-x-2">
                    <a href="{{ route('reports.attendance.index') }}" class="bg-white border border-gray-300 text-gray-700 hover:bg-gray-50 px-3 h-[36px] rounded shadow-sm font-medium flex items-center text-xs transition-colors duration-200 ease-in-out">
                        <span class="material-icons text-xs mr-1">arrow_back</span>
                        Back to List
                    </a>
                    <button onclick="exportAttendanceDetails()" class="bg-gradient-to-r from-green-600 to-green-500 hover:from-green-700 hover:to-green-600 text-white px-3 h-[36px] rounded shadow-sm font-medium flex items-center text-xs transition-colors duration-200 ease-in-out">
                        <span class="material-icons text-xs mr-1">file_download</span>
                        Export Details
                    </button>
                </div>
            </div>
        </div>
        
        <div class="p-4">
            <!-- Event Information -->
            <div class="mb-6 border border-gray-200 rounded-md p-4">
                <h2 class="text-sm font-semibold text-gray-700 mb-3 flex items-center">
                    <span class="material-icons text-primary-DEFAULT mr-2 text-base">event</span>
                    Event Information
                </h2>
                
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 text-xs">
                    <div>
                        <p class="text-gray-500 mb-1">Event Name</p>
                        <p class="font-medium">{{ $session->attendance->event->name ?? 'Unknown Event' }}</p>
                    </div>
                    
                    <div>
                        <p class="text-gray-500 mb-1">Date</p>
                        <p class="font-medium">{{ \Carbon\Carbon::parse($session->date)->format('d M Y') }}</p>
                    </div>
                    
                    <div>
                        <p class="text-gray-500 mb-1">Start Time</p>
                        <p class="font-medium">{{ substr($session->checkin_start_time, 0, 5) }}</p>
                    </div>
                    
                    <div>
                        <p class="text-gray-500 mb-1">End Time</p>
                        <p class="font-medium">{{ substr($session->checkin_end_time, 0, 5) }}</p>
                    </div>
                    
                    <div>
                        <p class="text-gray-500 mb-1">Location</p>
                        <p class="font-medium">{{ $session->attendance->event->location ?? 'Not specified' }}</p>
                    </div>
                    
                    <div>
                        <p class="text-gray-500 mb-1">Organizer</p>
                        <p class="font-medium">{{ $session->attendance->event->organizer ?? 'Not specified' }}</p>
                    </div>
                    
                    <div>
                        <p class="text-gray-500 mb-1">Status</p>
                        <p class="font-medium">
                            <span class="px-2 py-1 bg-green-100 text-green-800 rounded-full text-xs">{{ ucfirst($session->attendance->status ?? 'Active') }}</span>
                        </p>
                    </div>
                </div>
            </div>
            
            <!-- Attendance Stats -->
            <div class="mb-6 grid grid-cols-1 md:grid-cols-3 gap-4">
                @php
                    $registered = \App\Models\Participant::where('event_id', $session->attendance->event_id ?? 0)->count();
                    $attended = $records->where('status', 'present')->count();
                    $rate = $registered > 0 ? round(($attended / $registered) * 100) : 0;
                @endphp
                <div class="bg-blue-50 rounded-md p-4 border border-blue-100">
                    <p class="text-xs text-blue-700 font-medium">Registered Participants</p>
                    <p class="text-2xl font-bold text-blue-800">{{ $registered }}</p>
                </div>
                
                <div class="bg-green-50 rounded-md p-4 border border-green-100">
                    <p class="text-xs text-green-700 font-medium">Actual Attendees</p>
                    <p class="text-2xl font-bold text-green-800">{{ $attended }}</p>
                </div>
                
                <div class="bg-amber-50 rounded-md p-4 border border-amber-100">
                    <p class="text-xs text-amber-700 font-medium">Attendance Rate</p>
                    <p class="text-2xl font-bold text-amber-800">{{ $rate }}%</p>
                </div>
            </div>
            
            <!-- Attendance Details Table -->
            <div class="mb-6">
                <h2 class="text-sm font-semibold text-gray-700 mb-3 flex items-center">
                    <span class="material-icons text-primary-DEFAULT mr-2 text-base">people</span>
                    Attendee List
                </h2>
                
                <div class="overflow-visible border border-gray-200 rounded">
                    <table class="min-w-full border-collapse">
                        <thead>
                            <tr class="bg-primary-light text-white text-xs uppercase">
                                <th class="py-3 px-4 text-left rounded-tl">Participant</th>
                                <th class="py-3 px-4 text-left">Email</th>
                                <th class="py-3 px-4 text-left">Check-in Time</th>
                                <th class="py-3 px-4 text-left">Check-out Time</th>
                                <th class="py-3 px-4 text-left">Duration</th>
                                <th class="py-3 px-4 text-left">Status</th>
                                <th class="py-3 px-4 text-center rounded-tr">Certificate</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse($records as $record)
                                <tr class="text-xs hover:bg-gray-50">
                                    <td class="py-3 px-4 font-medium">{{ $record->participant->name ?? 'Unknown' }}</td>
                                    <td class="py-3 px-4">{{ $record->participant->email ?? '-' }}</td>
                                    <td class="py-3 px-4">{{ $record->checkin_time ? \Carbon\Carbon::parse($record->checkin_time)->format('H:i') : '-' }}</td>
                                    <td class="py-3 px-4">{{ $record->checkout_time ? \Carbon\Carbon::parse($record->checkout_time)->format('H:i') : '-' }}</td>
                                    <td class="py-3 px-4">
                                        @if($record->checkin_time && $record->checkout_time)
                                            @php
                                                $duration = \Carbon\Carbon::parse($record->checkin_time)->diffInMinutes(\Carbon\Carbon::parse($record->checkout_time));
                                                $hours = floor($duration / 60);
                                                $minutes = $duration % 60;
                                            @endphp
                                            {{ $hours }}h {{ $minutes }}m
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td class="py-3 px-4">
                                        <span class="px-2 py-1 bg-{{ $record->status == 'present' ? 'green' : 'red' }}-100 text-{{ $record->status == 'present' ? 'green' : 'red' }}-800 rounded-full text-xs">
                                            {{ ucfirst($record->status) }}
                                        </span>
                                    </td>
                                    <td class="py-3 px-4 text-center">
                                        @php
                                            $certificate = \App\Models\Certificate::where('participant_id', $record->participant_id)
                                                ->where('event_id', $session->attendance->event_id ?? 0)
                                                ->first();
                                        @endphp
                                        @if($certificate)
                                            <a href="{{ asset('storage/' . $certificate->pdf_file) }}" target="_blank" class="p-1 bg-blue-50 rounded hover:bg-blue-100 border border-blue-100" title="View Certificate">
                                                <span class="material-icons text-red-600 align-middle">picture_as_pdf</span>
                                            </a>
                                        @else
                                            -
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="py-8 text-center text-xs text-gray-400">No attendance records found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            
            <!-- Charts -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <!-- Attendance Timeline Chart -->
                <div class="border border-gray-200 rounded-md p-4" x-data="{ timelineView: 'hourly' }">
                    <div class="flex justify-between items-center mb-3">
                        <h3 class="text-xs font-medium text-gray-700 flex items-center">
                            <span class="material-icons text-primary-DEFAULT mr-1 text-sm">timeline</span>
                            Attendance Timeline
                        </h3>
                        <div class="flex space-x-2">
                            <button @click="timelineView = 'hourly'" :class="timelineView === 'hourly' ? 'text-primary-DEFAULT bg-blue-50 border border-blue-100' : 'text-gray-500 hover:text-primary-DEFAULT'" class="text-xs px-2 py-1 rounded transition-colors">Hourly</button>
                            <button @click="timelineView = 'daily'" :class="timelineView === 'daily' ? 'text-primary-DEFAULT bg-blue-50 border border-blue-100' : 'text-gray-500 hover:text-primary-DEFAULT'" class="text-xs px-2 py-1 rounded transition-colors">Daily</button>
                        </div>
                    </div>
                    
                    <!-- Hourly View -->
                    <div x-show="timelineView === 'hourly'" class="bg-white rounded-md h-72 relative">
                        <!-- Timeline Chart Visualization -->
                        <div class="absolute inset-0 p-2 pt-5">
                            @if(empty($timelineData['hourly']['checkins']) && empty($timelineData['hourly']['checkouts']))
                                <div class="text-xs text-center text-gray-500 h-full flex items-center justify-center">
                                    No timeline data available
                                </div>
                            @else
                                <canvas id="hourlyChart" class="w-full h-full"></canvas>
                            @endif
                        </div>
                    </div>
                    
                    <!-- Daily View -->
                    <div x-show="timelineView === 'daily'" class="bg-white rounded-md h-72 relative">
                        <!-- Timeline Chart Visualization -->
                        <div class="absolute inset-0 p-2 pt-5">
                            @if(empty($timelineData['daily']['checkins']) && empty($timelineData['daily']['checkouts']))
                                <div class="text-xs text-center text-gray-500 h-full flex items-center justify-center">
                                    No daily data available
                                </div>
                            @else
                                <canvas id="dailyChart" class="w-full h-full"></canvas>
                            @endif
                        </div>
                    </div>
                    
                    <!-- Stats Footer - consistent styling for both views -->
                    <div class="mt-3 p-2 bg-gray-50 rounded-md border border-gray-100">
                        <!-- Hourly Stats -->
                        <div x-show="timelineView === 'hourly'" class="flex flex-wrap justify-between text-[10px] text-gray-500">
                            <div class="flex items-center space-x-1 mb-1 md:mb-0">
                                <span class="material-icons text-blue-500 text-[10px]">schedule</span>
                                <span>Peak check-in time: <span class="font-medium text-gray-700">{{ $timelineData['hourly']['peak_checkin_time'] }}</span></span>
                            </div>
                            <div class="flex items-center space-x-1">
                                <span class="material-icons text-green-500 text-[10px]">schedule</span>
                                <span>Peak check-out time: <span class="font-medium text-gray-700">{{ $timelineData['hourly']['peak_checkout_time'] }}</span></span>
                            </div>
                        </div>
                        
                        <!-- Daily Stats -->
                        <div x-show="timelineView === 'daily'" class="flex flex-wrap justify-between text-[10px] text-gray-500">
                            <div class="flex items-center space-x-1 mb-1 md:mb-0">
                                <span class="material-icons text-blue-500 text-[10px]">event</span>
                                <span>Peak attendance day: <span class="font-medium text-gray-700">{{ $timelineData['daily']['peak_day'] }}</span></span>
                            </div>
                            <div class="flex items-center space-x-1">
                                <span class="material-icons text-blue-500 text-[10px]">people</span>
                                <span>Total attendance: <span class="font-medium text-gray-700">{{ $timelineData['daily']['total_weekly'] }} attendees</span></span>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Attendance Demographics Chart -->
                <div class="border border-gray-200 rounded-md p-4" x-data="{ demographicView: 'gender' }">
                    <div class="flex justify-between items-center mb-3">
                        <h3 class="text-xs font-medium text-gray-700 flex items-center">
                            <span class="material-icons text-primary-DEFAULT mr-1 text-sm">pie_chart</span>
                            Attendance Demographics
                        </h3>
                        <div class="flex space-x-2">
                            <button @click="demographicView = 'gender'" :class="demographicView === 'gender' ? 'text-primary-DEFAULT bg-blue-50 border border-blue-100' : 'text-gray-500 hover:text-primary-DEFAULT'" class="text-xs px-2 py-1 rounded transition-colors">Gender</button>
                            <button @click="demographicView = 'age'" :class="demographicView === 'age' ? 'text-primary-DEFAULT bg-blue-50 border border-blue-100' : 'text-gray-500 hover:text-primary-DEFAULT'" class="text-xs px-2 py-1 rounded transition-colors">Age</button>
                        </div>
                    </div>
                    
                    <!-- Note about data accuracy -->
                    <div class="bg-yellow-50 border border-yellow-100 rounded-md p-2 mb-3">
                        <p class="text-xs text-yellow-700 flex items-center">
                            <span class="material-icons text-yellow-500 mr-1 text-xs">info</span>
                            <span>Demographics data may not be fully accurate as {{ $demographics['gender']['unknown'] }} participants have no gender data and {{ $demographics['age_groups']['unknown'] }} have no date of birth recorded.</span>
                        </p>
                    </div>
                    
                    <!-- Gender View -->
                    <div x-show="demographicView === 'gender'" class="bg-white rounded-md h-72 relative">
                        <!-- Pie Chart Visualization -->
                        <div class="grid grid-cols-5 h-full">
                            <div class="col-span-2 flex items-center justify-center">
                                <div class="relative w-36 h-36">
                                    @if($demographics['total_attendees'] > 0)
                                    <!-- Pie Chart -->
                                    <svg viewBox="0 0 36 36" class="w-full h-full">
                                        <!-- Male (blue) -->
                                        <path d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" fill="none" stroke="#3b82f6" stroke-width="2" stroke-dasharray="{{ $demographics['gender']['male_percent'] }}, 100" stroke-dashoffset="25" />
                                        
                                        <!-- Female (purple) -->
                                        <path d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" fill="none" stroke="#8b5cf6" stroke-width="2" stroke-dasharray="{{ $demographics['gender']['female_percent'] }}, 100" stroke-dashoffset="{{ 25 + $demographics['gender']['male_percent'] }}" />
                                        
                                        <!-- Other/Unknown (gray) -->
                                        @php
                                            $otherUnknown = $demographics['gender']['other_percent'] + $demographics['gender']['unknown_percent'];
                                            $offset = 25 + $demographics['gender']['male_percent'] + $demographics['gender']['female_percent'];
                                        @endphp
                                        @if($otherUnknown > 0)
                                        <path d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" fill="none" stroke="#9ca3af" stroke-width="2" stroke-dasharray="{{ $otherUnknown }}, 100" stroke-dashoffset="{{ $offset }}" />
                                        @endif
                                    </svg>
                                    @endif
                                    
                                    <!-- Center text -->
                                    <div class="absolute inset-0 flex flex-col items-center justify-center text-xs">
                                        <span class="font-semibold">{{ $demographics['total_attendees'] }}</span>
                                        <span class="text-[10px] text-gray-500">Attendees</span>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Legend and Stats -->
                            <div class="col-span-3 flex flex-col justify-center">
                                <div class="space-y-4">
                                    <!-- Male stats -->
                                    <div class="flex items-center">
                                        <div class="w-3 h-3 bg-blue-500 rounded-full mr-2"></div>
                                        <div class="flex-1">
                                            <div class="flex justify-between items-center">
                                                <span class="text-xs font-medium">Male</span>
                                                <span class="text-xs font-medium">{{ $demographics['gender']['male_percent'] }}% ({{ $demographics['gender']['male'] }})</span>
                                            </div>
                                            <div class="w-full bg-gray-200 rounded-full h-1.5 mt-1">
                                                <div class="bg-blue-500 h-1.5 rounded-full" style="width: {{ $demographics['gender']['male_percent'] }}%"></div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <!-- Female stats -->
                                    <div class="flex items-center">
                                        <div class="w-3 h-3 bg-purple-500 rounded-full mr-2"></div>
                                        <div class="flex-1">
                                            <div class="flex justify-between items-center">
                                                <span class="text-xs font-medium">Female</span>
                                                <span class="text-xs font-medium">{{ $demographics['gender']['female_percent'] }}% ({{ $demographics['gender']['female'] }})</span>
                                            </div>
                                            <div class="w-full bg-gray-200 rounded-full h-1.5 mt-1">
                                                <div class="bg-purple-500 h-1.5 rounded-full" style="width: {{ $demographics['gender']['female_percent'] }}%"></div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <!-- Other/Unknown stats (if any) -->
                                    @if($demographics['gender']['other'] > 0 || $demographics['gender']['unknown'] > 0)
                                    <div class="flex items-center">
                                        <div class="w-3 h-3 bg-gray-400 rounded-full mr-2"></div>
                                        <div class="flex-1">
                                            <div class="flex justify-between items-center">
                                                <span class="text-xs font-medium">Other/Unknown</span>
                                                <span class="text-xs font-medium">{{ $demographics['gender']['other_percent'] + $demographics['gender']['unknown_percent'] }}% ({{ $demographics['gender']['other'] + $demographics['gender']['unknown'] }})</span>
                                            </div>
                                            <div class="w-full bg-gray-200 rounded-full h-1.5 mt-1">
                                                <div class="bg-gray-400 h-1.5 rounded-full" style="width: {{ $demographics['gender']['other_percent'] + $demographics['gender']['unknown_percent'] }}%"></div>
                                            </div>
                                        </div>
                                    </div>
                                    @endif
                                </div>
                                
                                <!-- Additional stats -->
                                <div class="grid grid-cols-2 gap-2 mt-4 text-[10px]">
                                    <div class="bg-gray-50 p-2 rounded">
                                        <p class="text-gray-500">Avg. Age</p>
                                        <p class="font-medium text-gray-700">{{ $demographics['avg_age'] }} years</p>
                                    </div>
                                    <div class="bg-gray-50 p-2 rounded">
                                        <p class="text-gray-500">First-time</p>
                                        <p class="font-medium text-gray-700">{{ $demographics['first_time'] }} ({{ $demographics['first_time_percent'] }}%)</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Age View -->
                    <div x-show="demographicView === 'age'" class="bg-white rounded-md h-72 relative">
                        <!-- Bar Chart Visualization -->
                        <div class="grid grid-cols-5 h-full">
                            <div class="col-span-2 flex items-center justify-center">
                                <div class="h-36 w-36 flex items-end justify-around">
                                    <div class="w-4 bg-blue-400 rounded-t flex-grow-0 flex-shrink-0" style="height: {{ $demographics['age_groups']['under_18_percent'] }}%;" title="Under 18: {{ $demographics['age_groups']['under_18_percent'] }}%"></div>
                                    <div class="w-4 bg-blue-500 rounded-t flex-grow-0 flex-shrink-0" style="height: {{ $demographics['age_groups']['18_24_percent'] }}%;" title="18-24: {{ $demographics['age_groups']['18_24_percent'] }}%"></div>
                                    <div class="w-4 bg-blue-600 rounded-t flex-grow-0 flex-shrink-0" style="height: {{ $demographics['age_groups']['25_34_percent'] }}%;" title="25-34: {{ $demographics['age_groups']['25_34_percent'] }}%"></div>
                                    <div class="w-4 bg-blue-700 rounded-t flex-grow-0 flex-shrink-0" style="height: {{ $demographics['age_groups']['35_44_percent'] }}%;" title="35-44: {{ $demographics['age_groups']['35_44_percent'] }}%"></div>
                                    <div class="w-4 bg-blue-800 rounded-t flex-grow-0 flex-shrink-0" style="height: {{ $demographics['age_groups']['45_54_percent'] }}%;" title="45-54: {{ $demographics['age_groups']['45_54_percent'] }}%"></div>
                                    <div class="w-4 bg-blue-900 rounded-t flex-grow-0 flex-shrink-0" style="height: {{ $demographics['age_groups']['55_plus_percent'] }}%;" title="55+: {{ $demographics['age_groups']['55_plus_percent'] }}%"></div>
                                </div>
                            </div>
                            
                            <!-- Legend and Stats -->
                            <div class="col-span-3 flex flex-col justify-center">
                                <div class="space-y-3">
                                    <!-- Age group stats -->
                                    <div class="flex items-center">
                                        <div class="w-3 h-3 bg-blue-400 rounded-full mr-2"></div>
                                        <div class="flex-1">
                                            <div class="flex justify-between items-center">
                                                <span class="text-xs font-medium">Under 18</span>
                                                <span class="text-xs font-medium">{{ $demographics['age_groups']['under_18_percent'] }}% ({{ $demographics['age_groups']['under_18'] }})</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="flex items-center">
                                        <div class="w-3 h-3 bg-blue-500 rounded-full mr-2"></div>
                                        <div class="flex-1">
                                            <div class="flex justify-between items-center">
                                                <span class="text-xs font-medium">18-24</span>
                                                <span class="text-xs font-medium">{{ $demographics['age_groups']['18_24_percent'] }}% ({{ $demographics['age_groups']['18_24'] }})</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="flex items-center">
                                        <div class="w-3 h-3 bg-blue-600 rounded-full mr-2"></div>
                                        <div class="flex-1">
                                            <div class="flex justify-between items-center">
                                                <span class="text-xs font-medium">25-34</span>
                                                <span class="text-xs font-medium">{{ $demographics['age_groups']['25_34_percent'] }}% ({{ $demographics['age_groups']['25_34'] }})</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="flex items-center">
                                        <div class="w-3 h-3 bg-blue-700 rounded-full mr-2"></div>
                                        <div class="flex-1">
                                            <div class="flex justify-between items-center">
                                                <span class="text-xs font-medium">35-44</span>
                                                <span class="text-xs font-medium">{{ $demographics['age_groups']['35_44_percent'] }}% ({{ $demographics['age_groups']['35_44'] }})</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="flex items-center">
                                        <div class="w-3 h-3 bg-blue-800 rounded-full mr-2"></div>
                                        <div class="flex-1">
                                            <div class="flex justify-between items-center">
                                                <span class="text-xs font-medium">45-54</span>
                                                <span class="text-xs font-medium">{{ $demographics['age_groups']['45_54_percent'] }}% ({{ $demographics['age_groups']['45_54'] }})</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="flex items-center">
                                        <div class="w-3 h-3 bg-blue-900 rounded-full mr-2"></div>
                                        <div class="flex-1">
                                            <div class="flex justify-between items-center">
                                                <span class="text-xs font-medium">55+</span>
                                                <span class="text-xs font-medium">{{ $demographics['age_groups']['55_plus_percent'] }}% ({{ $demographics['age_groups']['55_plus'] }})</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Demographics Stats Footer - consistent with timeline footer -->
                    <div class="mt-3 p-2 bg-gray-50 rounded-md border border-gray-100">
                        <!-- Gender Stats -->
                        <div x-show="demographicView === 'gender'" class="flex flex-wrap justify-between text-[10px] text-gray-500">
                            <div class="flex items-center space-x-1">
                                <span class="material-icons text-blue-500 text-[10px]">pie_chart</span>
                                <span>Gender ratio: <span class="font-medium text-gray-700">{{ $demographics['gender']['male_percent'] }}% male / {{ $demographics['gender']['female_percent'] }}% female</span></span>
                            </div>
                            <div class="flex items-center space-x-1">
                                <span class="material-icons text-purple-500 text-[10px]">groups</span>
                                <span>Total attendees: <span class="font-medium text-gray-700">{{ $demographics['total_attendees'] }}</span></span>
                            </div>
                        </div>
                        
                        <!-- Age Stats -->
                        <div x-show="demographicView === 'age'" class="flex flex-wrap justify-between text-[10px] text-gray-500">
                            <div class="flex items-center space-x-1 mb-1 md:mb-0">
                                <span class="material-icons text-blue-500 text-[10px]">calculate</span>
                                <span>Median age: <span class="font-medium text-gray-700">{{ $demographics['avg_age'] }} years</span></span>
                            </div>
                            <div class="flex items-center space-x-1">
                                <span class="material-icons text-blue-600 text-[10px]">trending_up</span>
                                <span>Unknown age: <span class="font-medium text-gray-700">{{ $demographics['age_groups']['unknown'] }} attendees</span></span>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Additional Analytics Section -->
                <div class="border border-gray-200 rounded-md p-4 md:col-span-2">
                    <div class="flex justify-between items-center mb-3">
                        <h3 class="text-xs font-medium text-gray-700 flex items-center">
                            <span class="material-icons text-primary-DEFAULT mr-1 text-sm">analytics</span>
                            Attendance Analytics
                        </h3>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <div class="bg-gray-50 p-3 rounded-md">
                            <div class="flex items-center justify-between">
                                <span class="text-xs text-gray-500">Avg. Duration</span>
                                <span class="material-icons text-primary-DEFAULT text-sm">timer</span>
                            </div>
                            <p class="text-lg font-semibold text-gray-800 mt-1">{{ $analytics['avgDuration'] }}</p>
                            <p class="text-[10px] text-gray-500 flex items-center mt-1">
                                Based on check-in/out times
                            </p>
                        </div>
                        
                        <div class="bg-gray-50 p-3 rounded-md">
                            <div class="flex items-center justify-between">
                                <span class="text-xs text-gray-500">Attendance Rate</span>
                                <span class="material-icons text-primary-DEFAULT text-sm">percent</span>
                            </div>
                            <p class="text-lg font-semibold text-gray-800 mt-1">{{ $analytics['attendanceRate'] }}%</p>
                            <p class="text-[10px] text-gray-500 flex items-center mt-1">
                                Of registered participants
                            </p>
                        </div>
                        
                        <div class="bg-gray-50 p-3 rounded-md">
                            <div class="flex items-center justify-between">
                                <span class="text-xs text-gray-500">Early Check-ins</span>
                                <span class="material-icons text-primary-DEFAULT text-sm">schedule</span>
                            </div>
                            <p class="text-lg font-semibold text-gray-800 mt-1">{{ $analytics['earlyCheckins'] }}%</p>
                            <p class="text-[10px] text-gray-500 flex items-center mt-1">
                                Before scheduled start time
                            </p>
                        </div>
                        
                        <div class="bg-gray-50 p-3 rounded-md">
                            <div class="flex items-center justify-between">
                                <span class="text-xs text-gray-500">Certificate Claims</span>
                                <span class="material-icons text-primary-DEFAULT text-sm">workspace_premium</span>
                            </div>
                            <p class="text-lg font-semibold text-gray-800 mt-1">{{ $analytics['certificateClaims'] }}%</p>
                            <p class="text-[10px] text-gray-500 flex items-center mt-1">
                                Of attendees with certificates
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- JavaScript for the page -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        function exportAttendanceDetails() {
            // Logic to export attendance details
            alert('Exporting attendance details...');
        }

        // Initialize charts when document is ready
        document.addEventListener('DOMContentLoaded', function() {
            // Hourly Chart
            @if(!empty($timelineData['hourly']['checkins']) || !empty($timelineData['hourly']['checkouts']))
            const hourlyCtx = document.getElementById('hourlyChart').getContext('2d');
            
            // Prepare data for hourly chart
            const hourLabels = Array.from({length: 24}, (_, i) => `${i}:00`);
            
            // Initialize data arrays with zeros
            const checkinData = Array(24).fill(0);
            const checkoutData = Array(24).fill(0);
            
            // Fill in actual data where available
            @foreach($timelineData['hourly']['checkins'] as $hour => $count)
                checkinData[parseInt("{{ $hour }}")] = {{ $count }};
            @endforeach
            
            @foreach($timelineData['hourly']['checkouts'] as $hour => $count)
                checkoutData[parseInt("{{ $hour }}")] = {{ $count }};
            @endforeach
            
            new Chart(hourlyCtx, {
                type: 'line',
                data: {
                    labels: hourLabels,
                    datasets: [
                        {
                            label: 'Check-ins',
                            data: checkinData,
                            borderColor: '#3b82f6',
                            backgroundColor: 'rgba(59, 130, 246, 0.1)',
                            pointBackgroundColor: '#3b82f6',
                            pointRadius: 4,
                            tension: 0.3,
                            fill: false
                        },
                        {
                            label: 'Check-outs',
                            data: checkoutData,
                            borderColor: '#10b981',
                            backgroundColor: 'rgba(16, 185, 129, 0.1)',
                            pointBackgroundColor: '#10b981',
                            pointRadius: 4,
                            tension: 0.3,
                            fill: false
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: true,
                            position: 'top',
                            align: 'center',
                            labels: {
                                boxWidth: 12,
                                padding: 10,
                                font: {
                                    size: 10
                                }
                            }
                        },
                        tooltip: {
                            mode: 'index',
                            intersect: false
                        }
                    },
                    scales: {
                        x: {
                            grid: {
                                display: false
                            },
                            ticks: {
                                autoSkip: true,
                                maxTicksLimit: 12,
                                font: {
                                    size: 9
                                }
                            }
                        },
                        y: {
                            beginAtZero: true,
                            grid: {
                                color: 'rgba(0, 0, 0, 0.05)'
                            },
                            ticks: {
                                precision: 0,
                                font: {
                                    size: 9
                                }
                            }
                        }
                    }
                }
            });
            @endif
            
            // Daily Chart
            @if(!empty($timelineData['daily']['checkins']) || !empty($timelineData['daily']['checkouts']))
            const dailyCtx = document.getElementById('dailyChart').getContext('2d');
            
            // Prepare data for daily chart
            const dayLabels = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];
            const daysMap = {
                'Mon': 0, 'Tue': 1, 'Wed': 2, 'Thu': 3, 'Fri': 4, 'Sat': 5, 'Sun': 6
            };
            
            // Initialize data arrays with zeros
            const dailyCheckinData = Array(7).fill(0);
            const dailyCheckoutData = Array(7).fill(0);
            
            // Fill in actual data where available
            @foreach($timelineData['daily']['checkins'] as $day => $count)
                if (daysMap.hasOwnProperty("{{ $day }}")) {
                    dailyCheckinData[daysMap["{{ $day }}"]] = {{ $count }};
                }
            @endforeach
            
            @foreach($timelineData['daily']['checkouts'] as $day => $count)
                if (daysMap.hasOwnProperty("{{ $day }}")) {
                    dailyCheckoutData[daysMap["{{ $day }}"]] = {{ $count }};
                }
            @endforeach
            
            new Chart(dailyCtx, {
                type: 'bar',
                data: {
                    labels: dayLabels,
                    datasets: [
                        {
                            label: 'Check-ins',
                            data: dailyCheckinData,
                            backgroundColor: 'rgba(59, 130, 246, 0.7)',
                            borderColor: '#3b82f6',
                            borderWidth: 1
                        },
                        {
                            label: 'Check-outs',
                            data: dailyCheckoutData,
                            backgroundColor: 'rgba(16, 185, 129, 0.7)',
                            borderColor: '#10b981',
                            borderWidth: 1
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: true,
                            position: 'top',
                            align: 'center',
                            labels: {
                                boxWidth: 12,
                                padding: 10,
                                font: {
                                    size: 10
                                }
                            }
                        },
                        tooltip: {
                            mode: 'index',
                            intersect: false
                        }
                    },
                    scales: {
                        x: {
                            grid: {
                                display: false
                            },
                            ticks: {
                                font: {
                                    size: 9
                                }
                            }
                        },
                        y: {
                            beginAtZero: true,
                            grid: {
                                color: 'rgba(0, 0, 0, 0.05)'
                            },
                            ticks: {
                                precision: 0,
                                font: {
                                    size: 9
                                }
                            }
                        }
                    }
                }
            });
            @endif
        });
    </script>
</x-app-layout> 