<x-app-layout>
    <x-slot name="breadcrumb">
        <span>Event Management</span>
    </x-slot>

    <x-slot name="title">Event Management</x-slot>

    <div class="bg-white rounded shadow-md border border-gray-300">
        <div class="p-6 border-b border-gray-200">
            <div class="flex justify-between items-start">
                <div>
                    <div class="flex items-center">
                        <span class="material-icons mr-2 text-primary-DEFAULT">event</span>
                        <h1 class="text-xl font-bold text-gray-800">Event Management</h1>
                    </div>
                    <p class="text-xs text-gray-500 mt-1 ml-8">Manage all events and activities</p>
                </div>
                <a href="{{ route('event.create') }}" class="bg-gradient-to-r from-blue-600 to-blue-500 hover:from-blue-700 hover:to-blue-600 text-white px-3 py-1 rounded shadow-sm font-medium flex items-center text-xs transition-colors duration-200 ease-in-out">
                    <span class="material-icons text-xs mr-1">add_circle</span>
                    Create New Event
                </a>
            </div>
        </div>
        
        <div class="p-4">
            <!-- Events Table -->
            <div class="overflow-visible border border-gray-200 rounded">
                <table class="min-w-full border-collapse">
                    <thead>
                        <tr class="bg-primary-light text-white text-xs uppercase">
                            <th class="py-3 px-4 text-left rounded-tl">Event Name</th>
                            <th class="py-3 px-4 text-left">Organizer</th>
                            <th class="py-3 px-4 text-left">Start Date</th>
                            <th class="py-3 px-4 text-left">End Date</th>
                            <th class="py-3 px-4 text-left">Location</th>
                            <th class="py-3 px-4 text-left">Participants</th>
                            <th class="py-3 px-4 text-left">Status</th>
                            <th class="py-3 px-4 text-center rounded-tr">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($events as $event)
                            <tr class="text-xs hover:bg-gray-50">
                                <td class="py-3 px-4 font-medium">{{ $event->name }}</td>
                                <td class="py-3 px-4">{{ $event->organizer }}</td>
                                <td class="py-3 px-4">{{ \Carbon\Carbon::parse($event->start_date)->format('d M Y') }} {{ $event->start_time ? '- ' . substr($event->start_time, 0, 5) : '' }}</td>
                                <td class="py-3 px-4">{{ \Carbon\Carbon::parse($event->end_date)->format('d M Y') }} {{ $event->end_time ? '- ' . substr($event->end_time, 0, 5) : '' }}</td>
                                <td class="py-3 px-4">{{ $event->location }}</td>
                                <td class="py-3 px-4">{{ $event->participants->count() ?? 0 }}</td>
                                <td class="py-3 px-4">
                                    @if($event->status === 'active')
                                        <span class="bg-status-active-bg text-status-active-text px-2 py-1 rounded-full text-xs">Active</span>
                                    @elseif($event->status === 'pending')
                                        <span class="bg-status-pending-bg text-status-pending-text px-2 py-1 rounded-full text-xs">Pending</span>
                                    @elseif($event->status === 'completed')
                                        <span class="bg-status-completed-bg text-status-completed-text px-2 py-1 rounded-full text-xs">Completed</span>
                                    @endif
                                </td>
                                <td class="py-3 px-4">
                                    <div class="flex justify-center space-x-2">
                                        <!-- Registration Options Dropdown -->
                                        <div class="relative" x-data="{ registrationDropdownOpen{{ $event->id }}: false }">
                                            <button @click="registrationDropdownOpen{{ $event->id }} = !registrationDropdownOpen{{ $event->id }}" class="p-1 bg-green-50 rounded hover:bg-green-100 border border-green-100" title="Registration Options">
                                                <span class="material-icons text-green-600 text-xs">group_add</span>
                                            </button>
                                            <div x-show="registrationDropdownOpen{{ $event->id }}" @click.outside="registrationDropdownOpen{{ $event->id }} = false" class="absolute right-0 mt-2 z-50 w-48 bg-white rounded-md shadow-lg">
                                                <div class="py-1 border border-gray-200 rounded-md">
                                                    <button @click="copyRegistrationLink('{{ route('event.register', ['token' => $event->registration_link]) }}')" class="flex items-center w-full text-left px-4 py-2 text-xs text-gray-700 hover:bg-gray-100">
                                                        <span class="material-icons text-blue-600 text-xs mr-2">link</span>
                                                        Copy Registration Link
                                                    </button>
                                                    <a href="{{ route('event.qrcode', $event->id) }}" class="flex items-center w-full text-left px-4 py-2 text-xs text-gray-700 hover:bg-gray-100">
                                                        <span class="material-icons text-purple-600 text-xs mr-2">qr_code</span>
                                                        Download QR Code
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <a href="{{ route('event.show', $event->id) }}" class="p-1 bg-blue-50 rounded hover:bg-blue-100 border border-blue-100" title="View">
                                            <span class="material-icons text-primary-DEFAULT text-xs">visibility</span>
                                        </a>
                                        <a href="{{ route('event.edit', $event->id) }}" class="p-1 bg-yellow-50 rounded hover:bg-yellow-100 border border-yellow-100" title="Edit">
                                            <span class="material-icons text-yellow-600 text-xs">edit</span>
                                        </a>
                                        <form method="POST" action="{{ route('event.destroy', $event->id) }}" onsubmit="return confirm('Are you sure you want to delete this event?')" class="inline-block">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="p-1 bg-red-50 rounded hover:bg-red-100 border border-red-100" title="Delete">
                                            <span class="material-icons text-red-600 text-xs">delete</span>
                                        </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

<!-- Copy to Clipboard JavaScript -->
<script>
function copyRegistrationLink(url) {
    navigator.clipboard.writeText(url)
        .then(() => {
            // Show alert atau notification
            alert('Registration link copied to clipboard!');
        })
        .catch((error) => {
            console.error('Could not copy text: ', error);
            // Fallback
            const textarea = document.createElement('textarea');
            textarea.value = url;
            document.body.appendChild(textarea);
            textarea.select();
            document.execCommand('copy');
            document.body.removeChild(textarea);
            alert('Registration link copied to clipboard!');
        });
}
</script>
</x-app-layout> 