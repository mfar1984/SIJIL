<x-app-layout>
    <x-slot name="breadcrumb">
        <span>Attendance</span>
        <span class="mx-2 text-gray-500">/</span>
        <span>Archive</span>
    </x-slot>

    <x-slot name="title">Attendance Archive</x-slot>

    <div class="bg-white rounded shadow-md border border-gray-300">
        <div class="p-6 border-b border-gray-200">
            <div class="flex items-center">
                <span class="material-icons mr-2 text-orange-500">inventory</span>
                <h1 class="text-xl font-bold text-gray-800">Attendance Archive</h1>
            </div>
            <p class="text-xs text-gray-500 mt-1 ml-8">View archived/completed attendance sessions</p>
        </div>
        <div class="p-4">
            <div class="overflow-visible border border-gray-200 rounded">
                <table class="min-w-full border-collapse">
                    <thead>
                        <tr class="bg-gray-200 text-gray-900 text-xs uppercase">
                            <th class="py-3 px-4 text-left rounded-tl">Event Name</th>
                            <th class="py-3 px-4 text-left">Date</th>
                            <th class="py-3 px-4 text-left">Time</th>
                            <th class="py-3 px-4 text-left">Status</th>
                            <th class="py-3 px-4 text-center rounded-tr">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($attendances as $attendance)
                        <tr>
                            <td class="py-2 px-4">{{ $attendance->event->name ?? '-' }}</td>
                            <td class="py-2 px-4">{{ $attendance->date ? date('d M Y', strtotime($attendance->date)) : '-' }}</td>
                            <td class="py-2 px-4">{{ $attendance->start_time ? date('H:i', strtotime($attendance->start_time)) : '-' }} - {{ $attendance->end_time ? date('H:i', strtotime($attendance->end_time)) : '-' }}</td>
                            <td class="py-2 px-4">
                                <span class="inline-block px-2 py-1 rounded text-xs {{ $attendance->status == 'archived' ? 'bg-gray-300 text-gray-700' : 'bg-green-200 text-green-800' }}">
                                    {{ ucfirst($attendance->status) }}
                                </span>
                            </td>
                            <td class="py-2 px-4 text-center">
                                <a href="{{ route('attendance.show', $attendance->id) }}" class="text-blue-500 hover:underline">View</a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="py-8 text-center text-xs text-gray-400">No archive attendance sessions yet.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout> 