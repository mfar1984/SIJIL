<x-app-layout>
    <x-slot name="breadcrumb">
        <span>PWA Management</span>
        <span class="mx-2">/</span>
        <span>Participants</span>
    </x-slot>

    <x-slot name="title">PWA Participants</x-slot>

    <div class="bg-white rounded shadow-md border border-gray-300">
        <div class="p-6 border-b border-gray-200">
            <div class="flex justify-between items-start">
                <div>
                    <div class="flex items-center">
                        <span class="material-icons-outlined mr-2 text-primary-DEFAULT">groups</span>
                        <h1 class="text-xl font-bold text-gray-800">PWA Participants</h1>
                    </div>
                    <p class="text-xs text-gray-500 mt-1 ml-8">Manage all PWA participants and attendees</p>
                </div>
                @can('pwa_participants.create')
                <a href="{{ route('pwa.participants.create') }}" class="bg-gradient-to-r from-blue-600 to-blue-500 hover:from-blue-700 hover:to-blue-600 text-white px-3 h-[36px] rounded shadow-sm font-medium flex items-center text-xs transition-colors duration-200 ease-in-out">
                    <span class="material-icons-outlined text-xs mr-1">add_circle</span>
                    Add New PWA Participant
                </a>
                @endcan
            </div>
        </div>
        <div class="p-4">
            <div class="mb-4 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="bg-blue-50 rounded-md p-4 border border-blue-100">
                    <p class="text-xs text-blue-700 font-medium">PWA Accounts</p>
                    <p class="text-2xl font-bold text-blue-800">{{ $stats['total'] ?? 0 }}</p>
                </div>
                <div class="bg-green-50 rounded-md p-4 border border-green-100">
                    <p class="text-xs text-green-700 font-medium">Active</p>
                    <p class="text-2xl font-bold text-green-800">{{ $stats['active'] ?? 0 }}</p>
                </div>
                {{-- Surfaces accounts that were created but whose owner was never
                     given the password, so they cannot sign in. --}}
                <div class="bg-amber-50 rounded-md p-4 border border-amber-100">
                    <p class="text-xs text-amber-700 font-medium">Never signed in</p>
                    <p class="text-2xl font-bold text-amber-800">{{ $stats['never_signed_in'] ?? 0 }}</p>
                </div>
                <div class="bg-red-50 rounded-md p-4 border border-red-100">
                    <p class="text-xs text-red-700 font-medium">Banned</p>
                    <p class="text-2xl font-bold text-red-800">{{ $stats['banned'] ?? 0 }}</p>
                    <p class="text-xs text-red-600 mt-1">Cannot sign in or register again</p>
                </div>
            </div>

            {{-- Search takes the remaining space; the filters keep their own width. --}}
            <form method="GET" action="{{ route('pwa.participants') }}" class="flex flex-wrap items-center gap-2 mb-4">
                <input type="text" name="search" id="searchInput" value="{{ request('search') }}"
                       placeholder="Search name, email, phone, IC..."
                       class="flex-1 min-w-[12rem] h-9 text-xs border-gray-300 rounded px-3 focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50">

                <select name="status" onchange="this.form.submit()"
                        class="h-9 text-xs border-gray-300 rounded pl-3 pr-8 w-[9rem] shrink-0 focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50">
                    <option value="">All Status</option>
                    <option value="active" @if(request('status') == 'active') selected @endif>Active</option>
                    <option value="inactive" @if(request('status') == 'inactive') selected @endif>Inactive</option>
                    <option value="banned" @if(request('status') == 'banned') selected @endif>Banned</option>
                </select>

                <button type="submit"
                        class="h-9 px-3 bg-primary-DEFAULT hover:bg-primary-dark text-white rounded text-xs flex items-center shrink-0 transition-colors duration-200 ease-in-out" title="Search">
                    <span class="material-icons-outlined text-xs">search</span>
                </button>

                @if(request('search') || request('status'))
                    <a href="{{ route('pwa.participants') }}" class="text-xs text-gray-500 underline shrink-0">Reset</a>
                @endif
            </form>
            <!-- Display success/error messages -->
            @if(session('success'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4 text-xs">
                    {{ session('success') }}
                </div>
            @endif
            @if(session('error'))
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4 text-xs">
                    {{ session('error') }}
                </div>
            @endif
            <!-- Search Results Summary -->
            @if(request('search') || request('status'))
                <div class="bg-blue-50 border border-blue-200 text-blue-700 px-4 py-2 rounded mb-4 text-xs">
                    <span class="font-medium">Search Results:</span>
                    @if(request('search'))
                        <span class="ml-2">Searching for "{{ request('search') }}"</span>
                    @endif
                    @if(request('status'))
                        <span class="ml-2">Status: {{ ucfirst(request('status')) }}</span>
                    @endif
                    <span class="ml-2">({{ $participants->total() }} results)</span>
                </div>
            @endif
            <!-- PWA Participants Table -->
            <div class="overflow-visible border border-gray-200 rounded">
                <table class="min-w-full border-collapse">
                    <thead>
                        <tr class="bg-primary-light text-white text-xs uppercase">
                            <th class="py-3 px-4 text-left rounded-tl">Name</th>
                            <th class="py-3 px-4 text-left">Email</th>
                            <th class="py-3 px-4 text-left">Phone</th>
                            <th class="py-3 px-4 text-left">Organization</th>
                            <th class="py-3 px-4 text-left">Status</th>
                            <th class="py-3 px-4 text-left">Sign-in</th>
                            <th class="py-3 px-4 text-center rounded-tr">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($participants as $participant)
                            <tr class="text-xs hover:bg-gray-50">
                                <td class="py-3 px-4 font-medium">{{ $participant->name }}</td>
                                <td class="py-3 px-4">{{ $participant->email }}</td>
                                <td class="py-3 px-4">{{ $participant->phone ?: '—' }}</td>
                                <td class="py-3 px-4">{{ $participant->organization ?: '—' }}</td>
                                <td class="py-3 px-4">
                                    @if($participant->isBanned())
                                        {{-- Shown ahead of active/inactive: a ban is the thing
                                             that matters about this row. --}}
                                        <span class="bg-red-100 text-red-800 px-2 py-1 rounded-full text-xs"
                                              title="Banned {{ $participant->banned_at->format('d M Y') }}{{ $participant->ban_reason ? ' — ' . $participant->ban_reason : '' }}">
                                            Banned
                                        </span>
                                    @elseif($participant->is_active)
                                        <span class="bg-status-active-bg text-status-active-text px-2 py-1 rounded-full text-xs">Active</span>
                                    @else
                                        <span class="bg-status-pending-bg text-status-pending-text px-2 py-1 rounded-full text-xs">Inactive</span>
                                    @endif
                                </td>
                                <td class="py-3 px-4">
                                    @if($participant->last_login_at)
                                        <span class="text-gray-600" title="Last signed in to the mobile app">
                                            {{ $participant->last_login_at->diffForHumans() }}
                                        </span>
                                    @elseif($participant->password_changed_at)
                                        <span class="text-gray-500" title="Set their own password on {{ $participant->password_changed_at->format('d M Y') }}, before sign-ins were recorded">
                                            Password set
                                        </span>
                                    @else
                                        <span class="px-2 py-1 bg-amber-100 text-amber-800 rounded-full text-xs" title="Still holds the generated password. Reset it to email them working credentials.">
                                            Never
                                        </span>
                                    @endif
                                </td>
                                <td class="py-3 px-4">
                                    <div class="flex justify-center space-x-2">
                                        <a href="{{ route('pwa.participants.show', $participant) }}" class="p-1 bg-blue-50 rounded hover:bg-blue-100 border border-blue-100" title="View">
                                            <span class="material-icons-outlined text-blue-600 text-xs">visibility</span>
                                        </a>
                                        @can('pwa_participants.update')
                                        <a href="{{ route('pwa.participants.edit', $participant) }}" class="p-1 bg-yellow-50 rounded hover:bg-yellow-100 border border-yellow-100" title="Edit">
                                            <span class="material-icons-outlined text-yellow-600 text-xs">edit</span>
                                        </a>
                                        @endcan
                                        {{-- Reset has its own permission now, rather than borrowing update. --}}
                                        @can('pwa_participants.reset_password')
                                        @if($participant->isBanned())
                                            <span class="p-1 bg-gray-50 rounded border border-gray-100 cursor-not-allowed" title="Banned accounts cannot sign in, so there is no point resetting the password">
                                                <span class="material-icons-outlined text-gray-300 text-xs">lock_reset</span>
                                            </span>
                                        @else
                                        <form method="POST" action="{{ route('pwa.participants.reset-password', $participant) }}" onsubmit="return confirm('Reset the password for {{ $participant->name }} and email them the new one?')" class="inline-block">
                                            @csrf
                                            <button type="submit" class="p-1 bg-purple-50 rounded hover:bg-purple-100 border border-purple-100" title="Reset password and email it">
                                                <span class="material-icons-outlined text-purple-600 text-xs">lock_reset</span>
                                            </button>
                                        </form>
                                        @endif
                                        @endcan

                                        @can('pwa_participants.ban')
                                        @if($participant->isBanned())
                                        <form method="POST" action="{{ route('pwa.participants.unban', $participant) }}" onsubmit="return confirm('Lift the ban on {{ $participant->name }}? They will be able to sign in and register again.')" class="inline-block">
                                            @csrf
                                            <button type="submit" class="p-1 bg-green-50 rounded hover:bg-green-100 border border-green-100" title="Lift the ban">
                                                <span class="material-icons-outlined text-green-600 text-xs">lock_open</span>
                                            </button>
                                        </form>
                                        @else
                                        <form method="POST" action="{{ route('pwa.participants.ban', $participant) }}"
                                              onsubmit="return banParticipant(this, '{{ addslashes($participant->name) }}')" class="inline-block">
                                            @csrf
                                            <input type="hidden" name="ban_reason" value="">
                                            <button type="submit" class="p-1 bg-orange-50 rounded hover:bg-orange-100 border border-orange-100" title="Ban this participant from signing in and from registering again">
                                                <span class="material-icons-outlined text-orange-600 text-xs">block</span>
                                            </button>
                                        </form>
                                        @endif
                                        @endcan

                                        @can('pwa_participants.delete')
                                        <form method="POST" action="{{ route('pwa.participants.destroy', $participant) }}" onsubmit="return confirm('Are you sure you want to delete this participant?')" class="inline-block">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="p-1 bg-red-50 rounded hover:bg-red-100 border border-red-100" title="Delete">
                                                <span class="material-icons-outlined text-red-600 text-xs">delete</span>
                                            </button>
                                        </form>
                                        @endcan
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr class="text-xs">
                                <td colspan="7" class="py-8 px-4 text-center text-gray-500">
                                    @if(request('search') || request('status'))
                                        No PWA participants match your filters.
                                    @else
                                        No PWA participants yet. Add one, or assign existing event participants to the mobile app.
                                    @endif
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <!-- Pagination Row -->
            <div class="mt-6 flex flex-col sm:flex-row sm:items-center sm:justify-between">
                <div class="mb-2 sm:mb-0 text-xs text-gray-500">
                    Showing {{ $participants->firstItem() ?? 0 }} to {{ $participants->lastItem() ?? 0 }} of {{ $participants->total() }} entries
                </div>
                <div class="flex justify-end">
                    {{ $participants->appends(request()->query())->links('components.pagination-modern') }}
                </div>
            </div>
        </div>
    </div>
    <script>
        // Ban asks for a reason so the record explains itself later. Cancelling the
        // prompt aborts the whole thing; leaving it empty is allowed.
        function banParticipant(form, name) {
            const reason = prompt(
                'Ban ' + name + '?\n\n'
                + 'They will not be able to sign in, and will not be able to register again '
                + 'with the same email or IC.\n\n'
                + 'Reason (optional, kept internal):'
            );

            if (reason === null) {
                return false;
            }

            form.querySelector('input[name="ban_reason"]').value = reason;
            return true;
        }

        // Debounce search input
        let searchTimeout;
        const searchInput = document.getElementById('searchInput');
        if (searchInput) {
            searchInput.addEventListener('input', function() {
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(() => {
                    this.form.submit();
                }, 500); // Wait 500ms after user stops typing
            });
        }
    </script>
</x-app-layout> 