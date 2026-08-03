<x-app-layout>
    <x-slot name="breadcrumb">
        <span>Notifications</span>
    </x-slot>

    <x-slot name="title">Notifications</x-slot>

    <div class="space-y-3">
        <div class="bg-white rounded shadow-md border border-gray-300">
            <div class="p-6 border-b border-gray-200">
                <div class="flex flex-wrap justify-between items-start gap-3">
                    <div>
                        <div class="flex items-center">
                            <span class="material-icons-outlined mr-2 text-primary-DEFAULT">notifications</span>
                            <h1 class="text-xl font-bold text-gray-800">Notifications</h1>
                        </div>
                        <p class="text-xs text-gray-500 mt-1 ml-8">
                            Everything the system has told you, newest first.
                        </p>
                    </div>

                    @if($counts['unread'] > 0)
                        <form method="POST" action="{{ route('notifications.mark-all-read') }}" id="markAllForm">
                            @csrf
                            <button type="submit"
                                    class="h-9 px-3 border border-gray-300 rounded text-xs font-medium text-gray-700 hover:bg-gray-50 inline-flex items-center shrink-0">
                                <span class="material-icons-outlined text-sm mr-1">done_all</span>
                                Mark all as read
                            </button>
                        </form>
                    @endif
                </div>
            </div>

            <div class="p-4">
                <div class="border-b border-gray-200 mb-4">
                    <nav class="flex flex-wrap -mb-px">
                        @foreach(['all' => 'All', 'unread' => 'Unread', 'read' => 'Read'] as $key => $label)
                            @php $active = $filter === $key; @endphp
                            <a href="{{ route('notifications.index', $key === 'all' ? [] : ['filter' => $key]) }}"
                               class="inline-flex items-center whitespace-nowrap py-3 px-4 text-xs font-medium leading-5 border-b-2 transition duration-150 ease-in-out
                                      {{ $active ? 'text-primary-DEFAULT border-primary-DEFAULT' : 'text-gray-500 hover:text-primary-DEFAULT border-transparent' }}">
                                {{ $label }}
                                <span class="ml-1.5 px-1.5 py-0.5 rounded text-[10px] {{ $active ? 'bg-primary-DEFAULT text-white' : 'bg-gray-100 text-gray-600' }}">
                                    {{ number_format($counts[$key]) }}
                                </span>
                            </a>
                        @endforeach
                    </nav>
                </div>

                @forelse($notifications as $notification)
                    <div class="flex items-start gap-3 py-3 border-b border-gray-100 last:border-0 {{ $notification->isUnread() ? 'bg-blue-50/50 -mx-2 px-2 rounded' : '' }}">
                        <span class="material-icons-outlined text-base shrink-0 mt-0.5 {{ $notification->isUnread() ? 'text-primary-DEFAULT' : 'text-gray-300' }}">
                            {{ $notification->icon ?: 'notifications' }}
                        </span>

                        <div class="min-w-0 flex-1">
                            <p class="text-xs font-medium text-gray-800">{{ $notification->title }}</p>
                            <p class="text-xs text-gray-500 mt-0.5">{{ $notification->message }}</p>
                            <p class="text-[11px] text-gray-400 mt-1"
                               title="{{ $notification->created_at->format('j M Y, H:i') }}">
                                {{ $notification->created_at->diffForHumans() }}
                            </p>
                        </div>

                        <div class="flex items-center gap-1 shrink-0">
                            @if($notification->safe_url !== '#')
                                {{-- The stored column holds an absolute URL from whichever host wrote
                                     the row, so it is reduced to a path before being offered. --}}
                                <a href="{{ $notification->safe_url }}"
                                   class="p-1 bg-blue-50 rounded hover:bg-blue-100 border border-blue-100"
                                   title="Open">
                                    <span class="material-icons-outlined text-primary-DEFAULT text-xs">open_in_new</span>
                                </a>
                            @endif

                            @if($notification->isUnread())
                                <form method="POST" action="{{ route('notifications.mark-read', $notification->id) }}">
                                    @csrf
                                    <button type="submit"
                                            class="p-1 bg-gray-50 rounded hover:bg-gray-100 border border-gray-200"
                                            title="Mark as read">
                                        <span class="material-icons-outlined text-gray-500 text-xs">done</span>
                                    </button>
                                </form>
                            @endif

                            <form method="POST" action="{{ route('notifications.destroy', $notification->id) }}">
                                @csrf
                                @method('DELETE')
                                <button type="submit"
                                        class="p-1 bg-red-50 rounded hover:bg-red-100 border border-red-100"
                                        title="Delete">
                                    <span class="material-icons-outlined text-red-500 text-xs">delete</span>
                                </button>
                            </form>
                        </div>
                    </div>
                @empty
                    <div class="flex flex-col items-center justify-center py-16 text-gray-400">
                        <span class="material-icons-outlined text-4xl mb-2">notifications_none</span>
                        <p class="text-sm text-gray-600">
                            @if($filter === 'unread')
                                Nothing unread.
                            @elseif($filter === 'read')
                                Nothing read yet.
                            @else
                                No notifications yet.
                            @endif
                        </p>
                        <p class="text-xs text-gray-500 mt-1">
                            Entries appear here as people register and events change.
                        </p>
                    </div>
                @endforelse

                @if($notifications->hasPages())
                    <div class="mt-6 flex flex-col sm:flex-row sm:items-center sm:justify-between">
                        <div class="mb-2 sm:mb-0 text-xs text-gray-500">
                            Showing <span class="font-medium">{{ $notifications->firstItem() }}</span>
                            to <span class="font-medium">{{ $notifications->lastItem() }}</span>
                            of <span class="font-medium">{{ number_format($notifications->total()) }}</span> entries
                        </div>
                        <div class="flex justify-end">
                            {{ $notifications->links('components.pagination-modern') }}
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
