<x-app-layout>
    <x-slot name="breadcrumb">
        <span>Attendance</span>
        <span class="mx-2 text-gray-500">/</span>
        <span>Attendance Management</span>
        <span class="mx-2 text-gray-500">/</span>
        <span>View Attendance</span>
    </x-slot>

    <x-slot name="title">Attendance Details</x-slot>

    <div class="bg-white rounded shadow-md border border-gray-300">
        <div class="p-6 border-b border-gray-200">
            <div class="flex items-center justify-between">
                <div class="flex items-center">
                    <span class="material-icons mr-2 text-primary-DEFAULT">how_to_reg</span>
                    <h1 class="text-xl font-bold text-gray-800">Attendance Details</h1>
                </div>
                <div class="flex space-x-3">
                    @can('attendance.update')
                    <a href="{{ route('attendance.edit', $attendance->id) }}" class="bg-gradient-to-r from-yellow-500 to-yellow-400 hover:from-yellow-600 hover:to-yellow-500 text-white px-3 h-[36px] rounded shadow-sm font-medium flex items-center text-xs transition-colors duration-200 ease-in-out">
                        <span class="material-icons text-xs mr-1">edit</span>
                        Edit Attendance
                    </a>
                    @endcan
                    @can('attendance.delete')
                    <form method="POST" action="{{ route('attendance.destroy', $attendance->id) }}" onsubmit="return confirm('Are you sure you want to delete this attendance session?');" class="inline-block">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="bg-gradient-to-r from-red-500 to-red-400 hover:from-red-600 hover:to-red-500 text-white px-3 h-[36px] rounded shadow-sm font-medium flex items-center text-xs transition-colors duration-200 ease-in-out">
                            <span class="material-icons text-xs mr-1">delete</span>
                            Delete Attendance
                        </button>
                    </form>
                    @endcan
                    <a href="{{ route('attendance.index') }}" class="bg-gradient-to-r from-gray-500 to-gray-400 hover:from-gray-600 hover:to-gray-500 text-white px-3 h-[36px] rounded shadow-sm font-medium flex items-center text-xs transition-colors duration-200 ease-in-out">
                        <span class="material-icons text-xs mr-1">arrow_back</span>
                        Back to List
                    </a>
                </div>
            </div>
            <p class="text-xs text-gray-500 mt-1 ml-8">View detailed information about this attendance session</p>
        </div>
        <div class="p-6 space-y-6">
            <!-- Basic Information -->
            <div class="border-b border-gray-200 pb-5">
                <h2 class="text-sm font-semibold text-gray-700 mb-4">Session Information</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-4">
                    <!-- Event Name -->
                    <div>
                        <label class="flex items-center text-xs font-medium text-gray-700 mb-1">
                            <span class="material-icons text-sm mr-1 text-primary-DEFAULT">event</span>
                            Event Name
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <span class="material-icons text-[#004aad] text-base">event</span>
                            </div>
                            <div class="w-full text-xs border-gray-200 bg-gray-50 rounded-[1px] pl-12 py-2 border">
                                {{ $attendance->event->name ?? '-' }}
                            </div>
                        </div>
                    </div>
                    <!-- Date -->
                    <div>
                        <label class="flex items-center text-xs font-medium text-gray-700 mb-1">
                            <span class="material-icons text-sm mr-1 text-primary-DEFAULT">calendar_today</span>
                            Date
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <span class="material-icons text-[#004aad] text-base">event</span>
                            </div>
                            <div class="w-full text-xs border-gray-200 bg-gray-50 rounded-[1px] pl-12 py-2 border">
                                {{ \Carbon\Carbon::parse($attendance->date)->format('l, d F Y') }}
                            </div>
                        </div>
                    </div>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-4">
                    <!-- Time -->
                    <div>
                        <label class="flex items-center text-xs font-medium text-gray-700 mb-1">
                            <span class="material-icons text-sm mr-1 text-primary-DEFAULT">schedule</span>
                            Time
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <span class="material-icons text-[#004aad] text-base">access_time</span>
                            </div>
                            <div class="w-full text-xs border-gray-200 bg-gray-50 rounded-[1px] pl-12 py-2 border">
                                {{ substr($attendance->start_time,0,5) }} - {{ substr($attendance->end_time,0,5) }}
                            </div>
                        </div>
                    </div>
                    <!-- Status -->
                    <div>
                        <label class="flex items-center text-xs font-medium text-gray-700 mb-1">
                            <span class="material-icons text-sm mr-1 text-primary-DEFAULT">toggle_on</span>
                            Status
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <span class="material-icons text-[#004aad] text-base">shield</span>
                            </div>
                            <div class="w-full text-xs border-gray-200 bg-gray-50 rounded-[1px] pl-12 py-2 border flex items-center">
                                @if($attendance->status === 'active')
                                    <span class="bg-status-active-bg text-status-active-text px-2 py-0.5 rounded-full text-xs">Active</span>
                                @elseif($attendance->status === 'pending')
                                    <span class="bg-status-pending-bg text-status-pending-text px-2 py-0.5 rounded-full text-xs">Pending</span>
                                @elseif($attendance->status === 'completed')
                                    <span class="bg-status-completed-bg text-status-completed-text px-2 py-0.5 rounded-full text-xs">Completed</span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- QR Code -->
            <div class="border-b border-gray-200 pb-5">
                <h2 class="text-sm font-semibold text-gray-700 mb-4">QR Code</h2>
                <div class="flex flex-col md:flex-row md:items-center gap-2 md:gap-4">
                    <a href="{{ route('attendance.qrcode', $attendance->id) }}" target="_blank" class="inline-flex items-center px-3 py-1 bg-gradient-to-r from-green-600 to-green-500 hover:from-green-700 hover:to-green-600 text-white rounded shadow-sm text-xs font-medium transition-colors duration-200 ease-in-out">
                        <span class="material-icons text-xs mr-1">qr_code</span>
                        View QR Code
                    </a>
                    <span class="text-xs text-gray-700">Attendance Code: <span class="font-mono">{{ $attendance->unique_code }}</span></span>
                </div>
            </div>
            <!-- Created Info -->
            <div class="pb-5">
                <h2 class="text-sm font-semibold text-gray-700 mb-4">Administrative Information</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Created Date -->
                    <div>
                        <label class="flex items-center text-xs font-medium text-gray-700 mb-1">
                            <span class="material-icons text-xs mr-1 text-primary-DEFAULT">calendar_today</span>
                            Created Date
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <span class="material-icons text-[#004aad] text-base">event</span>
                            </div>
                            <div class="w-full text-xs border-gray-200 bg-gray-50 rounded-[1px] pl-12 py-2 border">
                                {{ $attendance->created_at instanceof \DateTime ? $attendance->created_at->format('d M Y - H:i:s') : ($attendance->created_at ?? 'N/A') }}
                            </div>
                        </div>
                    </div>
                    <!-- Created By -->
                    <div>
                        <label class="flex items-center text-xs font-medium text-gray-700 mb-1">
                            <span class="material-icons text-xs mr-1 text-primary-DEFAULT">person</span>
                            Created By
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <span class="material-icons text-[#004aad] text-base">badge</span>
                            </div>
                            <div class="w-full text-xs border-gray-200 bg-gray-50 rounded-[1px] pl-12 py-2 border">
                                {{ $attendance->creator->name ?? '-' }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
