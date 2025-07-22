<x-app-layout>
    <x-slot name="breadcrumb">
        <span>Attendance</span>
    </x-slot>

    <x-slot name="title">Attendance Management</x-slot>

    <div class="bg-white rounded shadow-md border border-gray-300">
        <div class="p-6 border-b border-gray-200">
            <div class="flex justify-between items-start">
                <div>
                    <div class="flex items-center">
                        <span class="material-icons mr-2 text-primary-DEFAULT">how_to_reg</span>
                        <h1 class="text-xl font-bold text-gray-800">Attendance Management</h1>
                    </div>
                    <p class="text-xs text-gray-500 mt-1 ml-8">Manage all attendance sessions for your events</p>
                </div>
                <a href="{{ route('attendance.create') }}" class="bg-gradient-to-r from-blue-600 to-blue-500 hover:from-blue-700 hover:to-blue-600 text-white px-3 py-1 rounded shadow-sm font-medium flex items-center text-xs transition-colors duration-200 ease-in-out">
                    <span class="material-icons text-xs mr-1">add_circle</span>
                    Create Attendance
                </a>
            </div>
        </div>
        
        <div class="p-4">
            <!-- Attendance Table -->
            <div class="overflow-visible border border-gray-200 rounded">
                <table class="min-w-full border-collapse">
                    <thead>
                        <tr class="bg-primary-light text-white text-xs uppercase">
                            <th class="py-3 px-4 text-left rounded-tl">Event Name</th>
                            <th class="py-3 px-4 text-left">Date</th>
                            <th class="py-3 px-4 text-left">Time</th>
                            <th class="py-3 px-4 text-left">Status</th>
                            <th class="py-3 px-4 text-center rounded-tr">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($attendances as $attendance)
                        <tr class="text-xs hover:bg-gray-50">
                            <td class="py-3 px-4 font-medium">{{ $attendance->event->name ?? '-' }}</td>
                            <td class="py-3 px-4">{{ \Carbon\Carbon::parse($attendance->date)->format('d M Y') }}</td>
                            <td class="py-3 px-4">{{ substr($attendance->start_time,0,5) }} - {{ substr($attendance->end_time,0,5) }}</td>
                            <td class="py-3 px-4">
                                @if($attendance->status === 'active')
                                    <span class="bg-status-active-bg text-status-active-text px-2 py-1 rounded-full text-xs">Active</span>
                                @elseif($attendance->status === 'expired')
                                    <span class="bg-yellow-100 text-yellow-800 px-2 py-1 rounded-full text-xs">Expired</span>
                                @elseif($attendance->status === 'completed')
                                    <span class="bg-status-completed-bg text-status-completed-text px-2 py-1 rounded-full text-xs">Completed</span>
                                @else
                                    <span class="bg-gray-100 text-gray-800 px-2 py-1 rounded-full text-xs">{{ ucfirst($attendance->status) }}</span>
                                @endif
                            </td>
                            <td class="py-3 px-4">
                                <div class="flex justify-center space-x-2">
                                    <a href="{{ route('attendance.qrcode', $attendance->id) }}" class="p-1 bg-green-50 rounded hover:bg-green-100 border border-green-100" title="View QR Code" target="_blank">
                                        <span class="material-icons text-green-600 text-xs">qr_code</span>
                                    </a>
                                    <a href="{{ route('attendance.show', $attendance->id) }}" class="p-1 bg-blue-50 rounded hover:bg-blue-100 border border-blue-100" title="Show Details">
                                        <span class="material-icons text-primary-DEFAULT text-xs">visibility</span>
                                    </a>
                                    <a href="{{ route('attendance.edit', $attendance->id) }}" class="p-1 bg-yellow-50 rounded hover:bg-yellow-100 border border-yellow-100" title="Edit">
                                        <span class="material-icons text-yellow-600 text-xs">edit</span>
                                    </a>
                                    <form method="POST" action="{{ route('attendance.destroy', $attendance->id) }}" onsubmit="return confirm('Are you sure you want to delete this attendance session?')" class="inline-block">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="p-1 bg-red-50 rounded hover:bg-red-100 border border-red-100" title="Delete">
                                            <span class="material-icons text-red-600 text-xs">delete</span>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="py-8 text-center text-xs text-gray-400">No attendance sessions found. Click "Create Attendance" to add a new session.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
