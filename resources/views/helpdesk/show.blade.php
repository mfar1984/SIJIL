<x-app-layout>
    <x-slot name="breadcrumb">
        <span>Helpdesk</span>
        <span class="mx-2 text-gray-500">/</span>
        <span>Ticket {{ $ticket->ticket_id }}</span>
    </x-slot>

    <x-slot name="title">Ticket Details</x-slot>

    <div class="bg-white rounded shadow-md border border-gray-300">
        <div class="p-6 border-b border-gray-200">
            <div class="flex justify-between items-start">
                <div>
                    <div class="flex items-center">
                        <span class="material-icons mr-2 text-primary-DEFAULT">help</span>
                        <h1 class="text-xl font-bold text-gray-800">Ticket #{{ $ticket->ticket_id }}</h1>
                    </div>
                    <p class="text-xs text-gray-500 mt-1 ml-8">{{ $ticket->subject }}</p>
                </div>
                <div class="flex space-x-2">
                    @if($isAdmin)
                        <div class="flex space-x-2">
                            <button @click="document.querySelector('[x-data=\"{ showModal: false }\"]').__x.$data.showModal = true" class="bg-gradient-to-r from-blue-600 to-blue-500 hover:from-blue-700 hover:to-blue-600 text-white px-3 py-1 rounded shadow-sm font-medium flex items-center text-xs transition-colors duration-200 ease-in-out">
                                <span class="material-icons text-xs mr-1">update</span>
                                Update Status
                            </button>
                            
                            <!-- Quick Action Buttons -->
                            @if($ticket->status == 'open')
                            <form action="{{ route('helpdesk.status', $ticket->id) }}" method="POST" class="inline">
                                @csrf
                                <input type="hidden" name="status" value="in_progress">
                                <input type="hidden" name="assigned_to" value="{{ Auth::id() }}">
                                <button type="submit" class="bg-gradient-to-r from-yellow-600 to-yellow-500 hover:from-yellow-700 hover:to-yellow-600 text-white px-3 py-1 rounded shadow-sm font-medium flex items-center text-xs transition-colors duration-200 ease-in-out">
                                    <span class="material-icons text-xs mr-1">engineering</span>
                                    Mark In Progress
                                </button>
                            </form>
                            @endif
                            
                            @if($ticket->status == 'in_progress')
                            <form action="{{ route('helpdesk.status', $ticket->id) }}" method="POST" class="inline">
                                @csrf
                                <input type="hidden" name="status" value="resolved">
                                <button type="submit" class="bg-gradient-to-r from-green-600 to-green-500 hover:from-green-700 hover:to-green-600 text-white px-3 py-1 rounded shadow-sm font-medium flex items-center text-xs transition-colors duration-200 ease-in-out">
                                    <span class="material-icons text-xs mr-1">check_circle</span>
                                    Mark Resolved
                                </button>
                            </form>
                            @endif
                            
                            @if($ticket->status == 'resolved')
                            <form action="{{ route('helpdesk.status', $ticket->id) }}" method="POST" class="inline">
                                @csrf
                                <input type="hidden" name="status" value="closed">
                                <button type="submit" class="bg-gradient-to-r from-gray-600 to-gray-500 hover:from-gray-700 hover:to-gray-600 text-white px-3 py-1 rounded shadow-sm font-medium flex items-center text-xs transition-colors duration-200 ease-in-out">
                                    <span class="material-icons text-xs mr-1">lock</span>
                                    Close Ticket
                                </button>
                            </form>
                            @endif
                        </div>
                    @else
                        <!-- For Organizer: Reply button that scrolls to reply form -->
                        <a href="#reply-form" class="bg-gradient-to-r from-blue-600 to-blue-500 hover:from-blue-700 hover:to-blue-600 text-white px-3 py-1 rounded shadow-sm font-medium flex items-center text-xs transition-colors duration-200 ease-in-out">
                            <span class="material-icons text-xs mr-1">reply</span>
                            Reply
                        </a>
                    @endif
                    <a href="{{ route('helpdesk.index') }}" class="bg-gradient-to-r from-gray-600 to-gray-500 hover:from-gray-700 hover:to-gray-600 text-white px-3 py-1 rounded shadow-sm font-medium flex items-center text-xs transition-colors duration-200 ease-in-out">
                        <span class="material-icons text-xs mr-1">arrow_back</span>
                        Back to List
                    </a>
                </div>
            </div>
        </div>
        
        <div class="p-6">
            <!-- Ticket Details -->
            <div class="mb-6">
                <h2 class="text-sm font-semibold text-gray-700 mb-4 flex items-center">
                    <span class="material-icons text-primary-DEFAULT text-base mr-2">info</span>
                    Ticket Information
                </h2>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div class="bg-gray-50 rounded-md p-4 border border-gray-200">
                        <p class="text-xs text-gray-500 font-medium flex items-center">
                            <span class="material-icons text-gray-400 text-xs mr-1">label</span>
                            Status
                        </p>
                        <div class="mt-1">
                            @if($ticket->status == 'open')
                                <span class="px-2 py-1 bg-blue-100 text-blue-800 rounded-full text-xs">Open</span>
                            @elseif($ticket->status == 'in_progress')
                                <span class="px-2 py-1 bg-amber-100 text-amber-800 rounded-full text-xs">In Progress</span>
                            @elseif($ticket->status == 'resolved')
                                <span class="px-2 py-1 bg-green-100 text-green-800 rounded-full text-xs">Resolved</span>
                            @else
                                <span class="px-2 py-1 bg-gray-100 text-gray-800 rounded-full text-xs">Closed</span>
                            @endif
                        </div>
                    </div>
                    
                    <div class="bg-gray-50 rounded-md p-4 border border-gray-200">
                        <p class="text-xs text-gray-500 font-medium flex items-center">
                            <span class="material-icons text-gray-400 text-xs mr-1">flag</span>
                            Priority
                        </p>
                        <div class="mt-1">
                            @if($ticket->priority == 'low')
                                <span class="px-2 py-1 bg-blue-100 text-blue-800 rounded-full text-xs">Low</span>
                            @elseif($ticket->priority == 'medium')
                                <span class="px-2 py-1 bg-amber-100 text-amber-800 rounded-full text-xs">Medium</span>
                            @elseif($ticket->priority == 'high')
                                <span class="px-2 py-1 bg-red-100 text-red-800 rounded-full text-xs">High</span>
                            @else
                                <span class="px-2 py-1 bg-purple-100 text-purple-800 rounded-full text-xs">Urgent</span>
                            @endif
                        </div>
                    </div>
                    
                    <div class="bg-gray-50 rounded-md p-4 border border-gray-200">
                        <p class="text-xs text-gray-500 font-medium flex items-center">
                            <span class="material-icons text-gray-400 text-xs mr-1">category</span>
                            Category
                        </p>
                        <div class="mt-1">
                            <span class="px-2 py-1 bg-gray-100 text-gray-800 rounded-full text-xs">{{ ucfirst($ticket->category) }}</span>
                        </div>
                    </div>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                    <div class="bg-gray-50 rounded-md p-4 border border-gray-200">
                        <p class="text-xs text-gray-500 font-medium flex items-center">
                            <span class="material-icons text-gray-400 text-xs mr-1">person</span>
                            Submitted By
                        </p>
                        <p class="text-sm mt-1">{{ $ticket->user->name }}</p>
                    </div>
                    
                    <div class="bg-gray-50 rounded-md p-4 border border-gray-200">
                        <p class="text-xs text-gray-500 font-medium flex items-center">
                            <span class="material-icons text-gray-400 text-xs mr-1">calendar_today</span>
                            Date Submitted
                        </p>
                        <p class="text-sm mt-1">{{ $ticket->created_at->format('d M Y H:i') }}</p>
                    </div>
                </div>
                
                @if($isAdmin)
                <div class="bg-gray-50 rounded-md p-4 border border-gray-200 mt-4">
                    <p class="text-xs text-gray-500 font-medium flex items-center">
                        <span class="material-icons text-gray-400 text-xs mr-1">assignment_ind</span>
                        Assigned To
                    </p>
                    <p class="text-sm mt-1">{{ $ticket->assignedUser ? $ticket->assignedUser->name : 'Not assigned' }}</p>
                </div>
                @endif
            </div>
            
            <!-- Conversation -->
            <div class="mb-6">
                <h2 class="text-sm font-semibold text-gray-700 mb-4 flex items-center">
                    <span class="material-icons text-primary-DEFAULT text-base mr-2">forum</span>
                    Conversation
                </h2>
                
                <div class="space-y-4">
                    <!-- Initial Message -->
                    <div class="bg-blue-50 rounded-md p-4 border border-blue-100">
                        <div class="flex justify-between items-start">
                            <div class="flex items-start">
                                <div class="bg-blue-200 rounded-full w-8 h-8 flex items-center justify-center text-blue-800 font-bold text-sm">
                                    {{ substr($ticket->user->name, 0, 1) }}
                                </div>
                                <div class="ml-3">
                                    <p class="text-xs font-medium">{{ $ticket->user->name }}</p>
                                    <p class="text-xs text-gray-500">{{ $ticket->created_at->format('d M Y H:i') }}</p>
                                </div>
                            </div>
                            <span class="px-2 py-0.5 bg-blue-100 text-blue-800 rounded-full text-xs">Initial Request</span>
                        </div>
                        <div class="mt-3 text-sm">
                            {!! nl2br(e($ticket->description)) !!}
                        </div>
                    </div>
                    
                    <!-- Messages -->
                    @foreach($messages as $message)
                        @if($message->is_internal && $isAdmin)
                            <!-- Internal Note (Admin Only) -->
                            <div class="bg-yellow-50 rounded-md p-4 border border-yellow-100">
                                <div class="flex justify-between items-start">
                                    <div class="flex items-start">
                                        <div class="bg-yellow-200 rounded-full w-8 h-8 flex items-center justify-center text-yellow-800 font-bold text-sm">
                                            {{ substr($message->user->name, 0, 1) }}
                                        </div>
                                        <div class="ml-3">
                                            <p class="text-xs font-medium">{{ $message->user->name }}</p>
                                            <p class="text-xs text-gray-500">{{ $message->created_at->format('d M Y H:i') }}</p>
                                        </div>
                                    </div>
                                    <span class="px-2 py-0.5 bg-yellow-100 text-yellow-800 rounded-full text-xs">Internal Note</span>
                                </div>
                                <div class="mt-3 text-sm">
                                    {!! nl2br(e($message->message)) !!}
                                </div>
                            </div>
                        @elseif(!$message->is_internal)
                            <!-- Regular Message -->
                            <div class="bg-gray-50 rounded-md p-4 border border-gray-200 {{ $message->user_id === $ticket->user_id ? '' : 'bg-blue-50 border-blue-100' }}">
                                <div class="flex justify-between items-start">
                                    <div class="flex items-start">
                                        <div class="bg-{{ $message->user_id === $ticket->user_id ? 'gray' : 'blue' }}-200 rounded-full w-8 h-8 flex items-center justify-center text-{{ $message->user_id === $ticket->user_id ? 'gray' : 'blue' }}-800 font-bold text-sm">
                                            {{ substr($message->user->name, 0, 1) }}
                                        </div>
                                        <div class="ml-3">
                                            <p class="text-xs font-medium">{{ $message->user->name }}</p>
                                            <p class="text-xs text-gray-500">{{ $message->created_at->format('d M Y H:i') }}</p>
                                        </div>
                                    </div>
                                    <span class="px-2 py-0.5 bg-{{ $message->user_id === $ticket->user_id ? 'gray' : 'blue' }}-100 text-{{ $message->user_id === $ticket->user_id ? 'gray' : 'blue' }}-800 rounded-full text-xs">
                                        {{ $message->user_id === $ticket->user_id ? 'Requester' : 'Support' }}
                                    </span>
                                </div>
                                <div class="mt-3 text-sm">
                                    {!! nl2br(e($message->message)) !!}
                                </div>
                                
                                @if($message->attachments && count($message->attachments) > 0)
                                <div class="mt-3 pt-3 border-t border-gray-200">
                                    <p class="text-xs font-medium mb-2">Attachments:</p>
                                    <div class="flex flex-wrap gap-2">
                                        @foreach($message->attachments as $index => $attachment)
                                            <a href="{{ route('helpdesk.attachment', ['messageId' => $message->id, 'attachmentIndex' => $index]) }}" class="flex items-center px-2 py-1 bg-gray-100 hover:bg-gray-200 rounded text-xs">
                                                <span class="material-icons text-xs mr-1">attachment</span>
                                                {{ $attachment['name'] }}
                                            </a>
                                        @endforeach
                                    </div>
                                </div>
                                @endif
                            </div>
                        @endif
                    @endforeach
                </div>
            </div>
            
            <!-- Reply Form -->
            <div id="reply-form">
                <h2 class="text-sm font-semibold text-gray-700 mb-4 flex items-center">
                    <span class="material-icons text-primary-DEFAULT text-base mr-2">reply</span>
                    Add Reply
                </h2>
                
                @if($ticket->status == 'closed')
                    <div class="bg-gray-100 border border-gray-300 rounded-md p-4 text-gray-700 text-sm">
                        <div class="flex items-center">
                            <span class="material-icons text-gray-500 mr-2">lock</span>
                            <p>This ticket has been closed and cannot be replied to anymore. If you need further assistance, please create a new ticket.</p>
                        </div>
                    </div>
                @elseif($ticket->status == 'resolved')
                    <div class="bg-yellow-50 border border-yellow-200 rounded-md p-4 text-yellow-800 text-sm mb-4">
                        <div class="flex items-center">
                            <span class="material-icons text-yellow-500 mr-2">info</span>
                            <p>This ticket has been marked as resolved. If you reply, it will be automatically reopened as "In Progress". If no reply is received within 7 days, the ticket will be automatically closed.</p>
                        </div>
                    </div>
                    <form action="{{ route('helpdesk.reply', $ticket->id) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="space-y-4">
                            <div>
                                <label for="message" class="block text-xs font-medium text-gray-700 mb-1">Message</label>
                                <textarea id="message" name="message" rows="5" class="w-full rounded-md border-gray-300 shadow-sm focus:border-primary-DEFAULT focus:ring focus:ring-primary-DEFAULT focus:ring-opacity-50 text-sm" placeholder="Type your reply here..."></textarea>
                            </div>
                            
                            <div>
                                <label for="attachments" class="block text-xs font-medium text-gray-700 mb-1">Attachments (Optional)</label>
                                <input type="file" id="attachments" name="attachments[]" multiple class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded file:border-0 file:text-xs file:font-semibold file:bg-blue-50 file:text-primary-DEFAULT hover:file:bg-blue-100">
                            </div>
                            
                            @if($isAdmin)
                            <div class="flex items-center">
                                <input type="checkbox" id="is_internal" name="is_internal" class="rounded border-gray-300 text-primary-DEFAULT shadow-sm focus:border-primary-DEFAULT focus:ring focus:ring-primary-DEFAULT focus:ring-opacity-50">
                                <label for="is_internal" class="ml-2 block text-xs text-gray-700">Internal note (only visible to administrators)</label>
                            </div>
                            @endif
                            
                            <div>
                                <button type="submit" class="bg-gradient-to-r from-blue-600 to-blue-500 hover:from-blue-700 hover:to-blue-600 text-white px-4 py-2 rounded shadow-sm font-medium flex items-center text-xs transition-colors duration-200 ease-in-out">
                                    <span class="material-icons text-xs mr-1">send</span>
                                    Send Reply
                                </button>
                            </div>
                        </div>
                    </form>
                @else
                    <form action="{{ route('helpdesk.reply', $ticket->id) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="space-y-4">
                            <div>
                                <label for="message" class="block text-xs font-medium text-gray-700 mb-1">Message</label>
                                <textarea id="message" name="message" rows="5" class="w-full rounded-md border-gray-300 shadow-sm focus:border-primary-DEFAULT focus:ring focus:ring-primary-DEFAULT focus:ring-opacity-50 text-sm" placeholder="Type your reply here..."></textarea>
                            </div>
                            
                            <div>
                                <label for="attachments" class="block text-xs font-medium text-gray-700 mb-1">Attachments (Optional)</label>
                                <input type="file" id="attachments" name="attachments[]" multiple class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded file:border-0 file:text-xs file:font-semibold file:bg-blue-50 file:text-primary-DEFAULT hover:file:bg-blue-100">
                            </div>
                            
                            @if($isAdmin)
                            <div class="flex items-center">
                                <input type="checkbox" id="is_internal" name="is_internal" class="rounded border-gray-300 text-primary-DEFAULT shadow-sm focus:border-primary-DEFAULT focus:ring focus:ring-primary-DEFAULT focus:ring-opacity-50">
                                <label for="is_internal" class="ml-2 block text-xs text-gray-700">Internal note (only visible to administrators)</label>
                            </div>
                            @endif
                            
                            <div>
                                <button type="submit" class="bg-gradient-to-r from-blue-600 to-blue-500 hover:from-blue-700 hover:to-blue-600 text-white px-4 py-2 rounded shadow-sm font-medium flex items-center text-xs transition-colors duration-200 ease-in-out">
                                    <span class="material-icons text-xs mr-1">send</span>
                                    Send Reply
                                </button>
                            </div>
                        </div>
                    </form>
                @endif
            </div>
        </div>
    </div>
    
    @if($isAdmin)
    <!-- Status Update Modal -->
    <div x-data="{ showModal: false }">
        <div
            x-show="showModal"
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50"
            style="display: none;"
        >
            <div class="bg-white rounded-lg shadow-xl w-full max-w-md mx-4 overflow-hidden" @click.away="showModal = false">
                <div class="px-6 py-4 bg-primary-light text-white flex items-center justify-between">
                    <h3 class="text-lg font-medium">Update Ticket Status</h3>
                    <button @click="showModal = false" class="text-white hover:text-gray-200">
                        <span class="material-icons">close</span>
                    </button>
                </div>
                <form action="{{ route('helpdesk.status', $ticket->id) }}" method="POST">
                    @csrf
                    <div class="p-6">
                        <div class="mb-4">
                            <label for="status" class="block text-xs font-medium text-gray-700 mb-1">Status</label>
                            <select id="status" name="status" class="w-full rounded-md border-gray-300 shadow-sm focus:border-primary-DEFAULT focus:ring focus:ring-primary-DEFAULT focus:ring-opacity-50 text-sm">
                                <option value="open" {{ $ticket->status === 'open' ? 'selected' : '' }}>Open</option>
                                <option value="in_progress" {{ $ticket->status === 'in_progress' ? 'selected' : '' }}>In Progress</option>
                                <option value="resolved" {{ $ticket->status === 'resolved' ? 'selected' : '' }}>Resolved</option>
                                <option value="closed" {{ $ticket->status === 'closed' ? 'selected' : '' }}>Closed</option>
                            </select>
                        </div>
                        
                        <div>
                            <label for="assigned_to" class="block text-xs font-medium text-gray-700 mb-1">Assigned To</label>
                            <select id="assigned_to" name="assigned_to" class="w-full rounded-md border-gray-300 shadow-sm focus:border-primary-DEFAULT focus:ring focus:ring-primary-DEFAULT focus:ring-opacity-50 text-sm">
                                <option value="">Not Assigned</option>
                                @foreach($assignableUsers as $user)
                                    <option value="{{ $user->id }}" {{ $ticket->assigned_to === $user->id ? 'selected' : '' }}>{{ $user->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="px-6 py-4 bg-gray-50 border-t border-gray-200 flex justify-end">
                        <button @click="showModal = false" type="button" class="bg-white border border-gray-300 text-gray-700 hover:bg-gray-50 px-4 py-2 rounded-md text-xs mr-2">
                            Cancel
                        </button>
                        <button type="submit" class="bg-primary-DEFAULT hover:bg-primary-dark text-white px-4 py-2 rounded-md text-xs">
                            Update Status
                        </button>
                    </div>
                </form>
            </div>
        </div>
        
        <!-- Script untuk menampilkan modal tidak diperlukan lagi karena sudah menggunakan @click langsung -->
    </div>
    @endif
    
    <!-- Realtime Updates with Reverb -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Connect to Reverb
            const userId = {{ Auth::id() }};
            const ticketId = {{ $ticket->id }};
            
            // Listen for new messages
            window.Echo.private(`helpdesk.ticket.${ticketId}`)
                .listen('NewMessageSent', (e) => {
                    if (e.message.user.id !== userId) {
                        // Reload the page to show the new message
                        location.reload();
                        
                        // Or use a more sophisticated approach to append the message without reloading
                        // appendNewMessage(e.message);
                    }
                })
                .listen('TicketStatusUpdated', (e) => {
                    if (e.ticket.id === ticketId) {
                        // Reload the page to show the updated status
                        location.reload();
                    }
                });
        });
    </script>
</x-app-layout> 