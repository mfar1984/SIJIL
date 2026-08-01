{{--
    One tab's worth of tickets.

    This markup existed four times over in index.blade.php, once per tab, differing
    only in which paginator it read. Extracting it means the "From" column below -
    which the list never had - appears on every tab rather than one.

    Expects:
      $rows    paginator of HelpdeskTicket
      $isAdmin whether the viewer is an Administrator
      $empty   message when there is nothing to show
--}}
<div class="overflow-x-auto border border-gray-200 rounded">
    <table class="min-w-full border-collapse">
        <thead>
            <tr class="bg-primary-light text-white text-xs uppercase">
                <th class="py-3 px-4 text-left rounded-tl">Ticket</th>
                <th class="py-3 px-4 text-left">Subject</th>
                @if($isAdmin)
                    {{-- Who asked. Without this an Administrator could not tell an
                         organizer's ticket from a participant's. --}}
                    <th class="py-3 px-4 text-left">From</th>
                @endif
                <th class="py-3 px-4 text-left">Category</th>
                <th class="py-3 px-4 text-left">Opened</th>
                <th class="py-3 px-4 text-left">Status</th>
                <th class="py-3 px-4 text-left">Priority</th>
                <th class="py-3 px-4 text-left">Last update</th>
                <th class="py-3 px-4 text-center rounded-tr">Actions</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
            @forelse($rows as $ticket)
                <tr class="text-xs hover:bg-gray-50">
                    <td class="py-3 px-4 font-medium whitespace-nowrap">
                        <a href="{{ route('helpdesk.show', $ticket->id) }}" class="text-blue-600 hover:underline">
                            {{ $ticket->ticket_id }}
                        </a>
                    </td>
                    <td class="py-3 px-4">
                        <a href="{{ route('helpdesk.show', $ticket->id) }}" class="text-gray-800 hover:underline">
                            {{ $ticket->subject }}
                        </a>
                    </td>
                    @if($isAdmin)
                        <td class="py-3 px-4">
                            <div class="text-gray-800">{{ $ticket->author_name }}</div>
                            @if($ticket->isFromApp())
                                <span class="inline-flex items-center px-1.5 py-0.5 bg-indigo-100 text-indigo-700 rounded text-[10px] mt-0.5">
                                    <span class="material-icons-outlined text-[10px] mr-0.5">smartphone</span>
                                    App
                                </span>
                            @else
                                <span class="inline-flex items-center px-1.5 py-0.5 bg-gray-100 text-gray-600 rounded text-[10px] mt-0.5">
                                    Organizer
                                </span>
                            @endif
                        </td>
                    @endif
                    <td class="py-3 px-4">{{ ucfirst($ticket->category) }}</td>
                    <td class="py-3 px-4 whitespace-nowrap">{{ $ticket->created_at->format('d M Y') }}</td>
                    <td class="py-3 px-4">
                        @php
                            $badge = match ($ticket->status) {
                                'open' => ['bg-blue-100 text-blue-800', 'Open'],
                                'in_progress' => ['bg-yellow-100 text-yellow-800', 'In Progress'],
                                'resolved' => ['bg-green-100 text-green-800', 'Resolved'],
                                default => ['bg-gray-100 text-gray-800', 'Closed'],
                            };
                        @endphp
                        <span class="px-2 py-1 rounded-full text-xs {{ $badge[0] }}">{{ $badge[1] }}</span>
                    </td>
                    <td class="py-3 px-4">{{ ucfirst($ticket->priority) }}</td>
                    <td class="py-3 px-4 whitespace-nowrap">{{ $ticket->updated_at->format('d M Y H:i') }}</td>
                    <td class="py-3 px-4">
                        <div class="flex justify-center space-x-2">
                            <a href="{{ route('helpdesk.show', $ticket->id) }}"
                               class="p-1 bg-blue-50 rounded hover:bg-blue-100 border border-blue-100" title="View ticket">
                                <span class="material-icons-outlined text-primary-DEFAULT text-xs">visibility</span>
                            </a>

                            @if($ticket->status !== 'closed')
                                @can('helpdesk.update')
                                    <a href="{{ route('helpdesk.show', $ticket->id) }}#reply-form"
                                       class="p-1 bg-green-50 rounded hover:bg-green-100 border border-green-100" title="Reply">
                                        <span class="material-icons-outlined text-green-600 text-xs">reply</span>
                                    </a>
                                @endcan
                            @endif

                            @if($isAdmin)
                                @can('helpdesk.delete')
                                    <form action="{{ route('helpdesk.delete', $ticket->id) }}" method="POST" class="inline"
                                          onsubmit="return confirm('Delete this ticket and its messages?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="p-1 bg-red-50 rounded hover:bg-red-100 border border-red-100" title="Delete ticket">
                                            <span class="material-icons-outlined text-red-600 text-xs">delete</span>
                                        </button>
                                    </form>
                                @endcan
                            @endif
                        </div>
                    </td>
                </tr>
            @empty
                <tr class="text-xs">
                    <td colspan="{{ $isAdmin ? 9 : 8 }}" class="py-8 text-center text-gray-500">
                        {{ $empty ?? 'No tickets here.' }}
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="mt-6 flex flex-col sm:flex-row sm:items-center sm:justify-between">
    <div class="mb-2 sm:mb-0 text-xs text-gray-500">
        @if($rows->total() > 0)
            Showing <span class="font-medium">{{ $rows->firstItem() }}</span>
            to <span class="font-medium">{{ $rows->lastItem() }}</span>
            of <span class="font-medium">{{ number_format($rows->total()) }}</span> tickets
        @else
            Showing <span class="font-medium">0</span> tickets
        @endif
    </div>
    <div class="flex justify-end">
        {{ $rows->links('components.pagination-modern') }}
    </div>
</div>
