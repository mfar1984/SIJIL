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
                    <span class="material-icons-outlined mr-2 text-primary-DEFAULT">how_to_reg</span>
                    <h1 class="text-xl font-bold text-gray-800">Attendance Details</h1>
                </div>
                <div class="flex space-x-3">
                    @can('attendance.update')
                    <a href="{{ route('attendance.edit', $attendance->id) }}" class="bg-gradient-to-r from-yellow-500 to-yellow-400 hover:from-yellow-600 hover:to-yellow-500 text-white px-3 h-[36px] rounded shadow-sm font-medium flex items-center text-xs transition-colors duration-200 ease-in-out">
                        <span class="material-icons-outlined text-xs mr-1">edit</span>
                        Edit Attendance
                    </a>
                    @endcan
                    @can('attendance.delete')
                    <form method="POST" action="{{ route('attendance.destroy', $attendance->id) }}" onsubmit="return confirm('Are you sure you want to delete this attendance session?');" class="inline-block">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="bg-gradient-to-r from-red-500 to-red-400 hover:from-red-600 hover:to-red-500 text-white px-3 h-[36px] rounded shadow-sm font-medium flex items-center text-xs transition-colors duration-200 ease-in-out">
                            <span class="material-icons-outlined text-xs mr-1">delete</span>
                            Delete Attendance
                        </button>
                    </form>
                    @endcan
                    <a href="{{ route('attendance.index') }}" class="bg-gradient-to-r from-gray-500 to-gray-400 hover:from-gray-600 hover:to-gray-500 text-white px-3 h-[36px] rounded shadow-sm font-medium flex items-center text-xs transition-colors duration-200 ease-in-out">
                        <span class="material-icons-outlined text-xs mr-1">arrow_back</span>
                        Back to List
                    </a>
                </div>
            </div>
            <p class="text-xs text-gray-500 mt-1 ml-8">View detailed information about this attendance session</p>
        </div>
        <div class="p-6 space-y-2">
            <!-- Session Information -->
            <div class="bg-white border border-gray-200 rounded-md shadow-sm">
                <div class="bg-gray-50 border-b border-gray-200 px-4 py-3">
                    <h2 class="text-sm font-semibold text-gray-700 flex items-center">
                        <span class="material-icons-outlined text-primary-DEFAULT mr-2">info</span>
                        Session Information
                    </h2>
                </div>
                
                <div class="p-4">
                    <div class="space-y-3">
                        <!-- Event Name -->
                        <div class="flex flex-col md:flex-row md:items-center gap-3">
                            <label class="text-xs font-medium text-gray-700 md:w-40">
                                Event Name
                            </label>
                            <div class="flex-1">
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <span class="material-icons-outlined text-[#004aad] text-base">event</span>
                                    </div>
                                    <div class="w-full text-xs border-gray-200 bg-gray-50 rounded-[1px] pl-12 py-2 border">
                                        {{ $attendance->event->name ?? '-' }}
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Date -->
                        <div class="flex flex-col md:flex-row md:items-center gap-3">
                            <label class="text-xs font-medium text-gray-700 md:w-40">
                                Date
                            </label>
                            <div class="flex-1">
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <span class="material-icons-outlined text-[#004aad] text-base">event</span>
                                    </div>
                                    <div class="w-full text-xs border-gray-200 bg-gray-50 rounded-[1px] pl-12 py-2 border">
                                        {{ \Carbon\Carbon::parse($attendance->date)->format('l, d F Y') }}
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Time -->
                        <div class="flex flex-col md:flex-row md:items-center gap-3">
                            <label class="text-xs font-medium text-gray-700 md:w-40">
                                Time
                            </label>
                            <div class="flex-1">
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <span class="material-icons-outlined text-[#004aad] text-base">access_time</span>
                                    </div>
                                    <div class="w-full text-xs border-gray-200 bg-gray-50 rounded-[1px] pl-12 py-2 border">
                                        {{ substr($attendance->start_time,0,5) }} - {{ substr($attendance->end_time,0,5) }}
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Status -->
                        <div class="flex flex-col md:flex-row md:items-center gap-3">
                            <label class="text-xs font-medium text-gray-700 md:w-40">
                                Status
                            </label>
                            <div class="flex-1">
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <span class="material-icons-outlined text-[#004aad] text-base">shield</span>
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
                </div>
            </div>
            
            <!-- QR Code -->
            <div class="bg-white border border-gray-200 rounded-md shadow-sm">
                <div class="bg-gray-50 border-b border-gray-200 px-4 py-3">
                    <h2 class="text-sm font-semibold text-gray-700 flex items-center">
                        <span class="material-icons-outlined text-primary-DEFAULT mr-2">qr_code</span>
                        QR Code
                    </h2>
                </div>
                
                <div class="p-4">
                    <div class="space-y-3">
                        <div class="flex flex-col md:flex-row md:items-center gap-3">
                            <label class="text-xs font-medium text-gray-700 md:w-40">
                                Attendance Code
                            </label>
                            <div class="flex-1">
                                <div class="flex items-center gap-3">
                                    <span class="text-xs font-mono bg-gray-100 px-3 py-2 rounded border border-gray-200">{{ $attendance->unique_code }}</span>
                                    <a href="{{ route('attendance.qrcode', $attendance->id) }}" target="_blank" class="inline-flex items-center px-3 h-[36px] bg-gradient-to-r from-green-600 to-green-500 hover:from-green-700 hover:to-green-600 text-white rounded shadow-sm text-xs font-medium transition-colors duration-200 ease-in-out">
                                        <span class="material-icons-outlined text-xs mr-1">qr_code</span>
                                        View QR Code
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Administrative Information -->
            <div class="bg-white border border-gray-200 rounded-md shadow-sm">
                <div class="bg-gray-50 border-b border-gray-200 px-4 py-3">
                    <h2 class="text-sm font-semibold text-gray-700 flex items-center">
                        <span class="material-icons-outlined text-primary-DEFAULT mr-2">admin_panel_settings</span>
                        Administrative Information
                    </h2>
                </div>
                
                <div class="p-4">
                    <div class="space-y-3">
                        <!-- Created Date -->
                        <div class="flex flex-col md:flex-row md:items-center gap-3">
                            <label class="text-xs font-medium text-gray-700 md:w-40">
                                Created Date
                            </label>
                            <div class="flex-1">
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <span class="material-icons-outlined text-[#004aad] text-base">event</span>
                                    </div>
                                    <div class="w-full text-xs border-gray-200 bg-gray-50 rounded-[1px] pl-12 py-2 border">
                                        {{ $attendance->created_at instanceof \DateTime ? $attendance->created_at->format('d M Y - H:i:s') : ($attendance->created_at ?? 'N/A') }}
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Created By -->
                        <div class="flex flex-col md:flex-row md:items-center gap-3">
                            <label class="text-xs font-medium text-gray-700 md:w-40">
                                Created By
                            </label>
                            <div class="flex-1">
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <span class="material-icons-outlined text-[#004aad] text-base">badge</span>
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
        </div>
    </div>
</x-app-layout>
