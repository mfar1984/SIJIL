<x-app-layout>
    <x-slot name="breadcrumb">
        <span>Survey</span>
    </x-slot>

    <x-slot name="title">Survey</x-slot>

    <div class="bg-white rounded shadow-md border border-gray-300">
        <div class="p-6 border-b border-gray-200">
            <div class="flex justify-between items-start">
                <div>
                    <div class="flex items-center">
                        <span class="material-icons-outlined mr-2 text-primary-DEFAULT">poll</span>
                        <h1 class="text-xl font-bold text-gray-800">Survey</h1>
                    </div>
                    <p class="text-xs text-gray-500 mt-1 ml-8">Build feedback forms for your events and collect responses</p>
                </div>

                @can('surveys.create')
                <a href="{{ route('survey.create') }}"
                   class="bg-gradient-to-r from-blue-600 to-blue-500 hover:from-blue-700 hover:to-blue-600 text-white px-3 h-[36px] rounded shadow-sm font-medium flex items-center text-xs transition-colors duration-200 ease-in-out">
                    <span class="material-icons-outlined text-xs mr-1">add_circle</span>
                    New Survey
                </a>
                @endcan
            </div>
        </div>

        <div class="p-4">
            @if(session('success'))
                <div class="bg-green-50 border border-green-200 text-green-700 px-3 py-2 rounded mb-3 text-xs">
                    {{ session('success') }}
                </div>
            @endif

            @if(session('error'))
                <div class="bg-red-50 border border-red-200 text-red-700 px-3 py-2 rounded mb-3 text-xs">
                    {{ session('error') }}
                </div>
            @endif

            {{-- Search takes the remaining space; the filters keep their own width. --}}
            <form method="GET" action="{{ route('survey.index') }}" class="flex flex-wrap items-center gap-2 mb-4">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search title or description"
                       class="flex-1 min-w-[12rem] h-9 text-xs border-gray-300 rounded px-3 focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50">

                <select name="status" onchange="this.form.submit()"
                        class="h-9 text-xs border-gray-300 rounded pl-3 pr-8 w-[9rem] shrink-0 focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50">
                    <option value="">All status</option>
                    @foreach(['draft' => 'Draft', 'published' => 'Published', 'closed' => 'Closed'] as $value => $label)
                        <option value="{{ $value }}" {{ request('status') === $value ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>

                <select name="audience" onchange="this.form.submit()"
                        class="h-9 text-xs border-gray-300 rounded pl-3 pr-8 w-[11rem] shrink-0 focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50">
                    <option value="">All audiences</option>
                    <option value="anyone" {{ request('audience') === 'anyone' ? 'selected' : '' }}>Anyone with link</option>
                    <option value="participants" {{ request('audience') === 'participants' ? 'selected' : '' }}>Participants only</option>
                </select>

                <select name="event_id" onchange="this.form.submit()"
                        class="h-9 text-xs border-gray-300 rounded pl-3 pr-8 w-[13rem] shrink-0 focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50">
                    <option value="">All events</option>
                    @foreach($events as $event)
                        <option value="{{ $event->id }}" {{ (int) request('event_id') === $event->id ? 'selected' : '' }}>{{ $event->name }}</option>
                    @endforeach
                </select>

                <button type="submit"
                        class="h-9 px-3 bg-primary-DEFAULT hover:bg-primary-dark text-white rounded text-xs flex items-center shrink-0 transition-colors duration-200 ease-in-out">
                    <span class="material-icons-outlined text-xs">search</span>
                </button>

                @if(request()->hasAny(['search', 'status', 'audience', 'event_id']))
                    <a href="{{ route('survey.index') }}" class="text-xs text-gray-500 underline shrink-0">Reset</a>
                @endif
            </form>

            @if($surveys->isEmpty())
                <div class="text-center py-12 border border-dashed border-gray-300 rounded">
                    <span class="material-icons-outlined text-gray-300" style="font-size: 40px !important; width: 40px; height: 40px;">poll</span>
                    <p class="text-sm text-gray-500 mt-2">No surveys yet</p>
                    <p class="text-xs text-gray-400 mt-1">Create one to start collecting feedback</p>

                    @can('surveys.create')
                    <a href="{{ route('survey.create') }}" class="inline-flex items-center mt-4 text-xs text-primary-DEFAULT hover:underline">
                        <span class="material-icons-outlined text-xs mr-1">add_circle</span>
                        Create your first survey
                    </a>
                    @endcan
                </div>
            @else
                {{-- Table styling mirrors event-management.blade.php.
                     overflow-visible so the action dropdown is not clipped. --}}
                <div class="overflow-visible border border-gray-200 rounded">
                    <table class="min-w-full border-collapse">
                        <thead>
                            <tr class="bg-primary-light text-white text-xs uppercase">
                                <th class="py-3 px-4 text-left rounded-tl">Title</th>
                                <th class="py-3 px-4 text-left">Event</th>
                                <th class="py-3 px-4 text-left">Questions</th>
                                <th class="py-3 px-4 text-left">Responses</th>
                                <th class="py-3 px-4 text-left">Audience</th>
                                <th class="py-3 px-4 text-left">Status</th>
                                <th class="py-3 px-4 text-center rounded-tr">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach($surveys as $survey)
                                <tr class="text-xs hover:bg-gray-50">
                                    <td class="py-3 px-4">
                                        <a href="{{ route('survey.show', $survey) }}" class="font-medium text-gray-800 hover:text-primary-DEFAULT">
                                            {{ $survey->title }}
                                        </a>
                                    </td>
                                    <td class="py-3 px-4">{{ $survey->event->name ?? '—' }}</td>
                                    <td class="py-3 px-4">
                                        @if($survey->questions_count === 0)
                                            <span class="text-red-600 font-medium">0</span>
                                        @else
                                            {{ $survey->questions_count }}
                                        @endif
                                    </td>
                                    <td class="py-3 px-4">{{ $survey->completed_responses_count }}</td>
                                    <td class="py-3 px-4">{{ $survey->isParticipantsOnly() ? 'Participants' : 'Anyone' }}</td>
                                    <td class="py-3 px-4">
                                        @php
                                            $badge = match ($survey->status_label) {
                                                'Accepting responses' => 'bg-status-active-bg text-status-active-text',
                                                'Draft' => 'bg-status-pending-bg text-status-pending-text',
                                                'Scheduled' => 'bg-blue-50 text-blue-700',
                                                default => 'bg-gray-100 text-gray-600',
                                            };
                                        @endphp
                                        <span class="{{ $badge }} px-2 py-1 rounded-full text-xs whitespace-nowrap">{{ $survey->status_label }}</span>
                                    </td>
                                    <td class="py-3 px-4">
                                        <div class="flex justify-center space-x-2">
                                            {{-- Share options dropdown, same pattern as event-management --}}
                                            <div class="relative" x-data="{ open: false }">
                                                <button type="button" @click="open = !open"
                                                        class="p-1 bg-purple-50 rounded hover:bg-purple-100 border border-purple-100" title="Survey Options">
                                                    <span class="material-icons-outlined text-purple-600 text-xs">format_list_bulleted</span>
                                                </button>

                                                <div x-show="open" x-cloak @click.outside="open = false"
                                                     class="absolute right-0 mt-2 z-50 w-52 bg-white rounded-md shadow-lg">
                                                    <div class="py-1 border border-gray-200 rounded-md">
                                                        <button type="button"
                                                                @click="copySurveyLink('{{ $survey->public_url }}'); open = false"
                                                                class="flex items-center w-full text-left px-4 py-2 text-xs text-gray-700 hover:bg-gray-100">
                                                            <span class="material-icons-outlined text-blue-600 text-xs mr-2">link</span>
                                                            Copy Survey Link
                                                        </button>

                                                        <a href="{{ route('survey.qrcode-image', $survey) }}"
                                                           class="flex items-center w-full text-left px-4 py-2 text-xs text-gray-700 hover:bg-gray-100">
                                                            <span class="material-icons-outlined text-indigo-600 text-xs mr-2">qr_code</span>
                                                            Download QR Code
                                                        </a>

                                                        @can('survey_responses.read')
                                                        <a href="{{ route('survey.responses', $survey) }}"
                                                           class="flex items-center w-full text-left px-4 py-2 text-xs text-gray-700 hover:bg-gray-100">
                                                            <span class="material-icons-outlined text-purple-600 text-xs mr-2">format_list_bulleted</span>
                                                            View Responses
                                                        </a>

                                                        <a href="{{ route('survey.analytics', $survey) }}"
                                                           class="flex items-center w-full text-left px-4 py-2 text-xs text-gray-700 hover:bg-gray-100">
                                                            <span class="material-icons-outlined text-emerald-600 text-xs mr-2">insights</span>
                                                            View Analytics
                                                        </a>
                                                        @endcan
                                                    </div>
                                                </div>
                                            </div>

                                            <a href="{{ route('survey.show', $survey) }}"
                                               class="p-1 bg-blue-50 rounded hover:bg-blue-100 border border-blue-100" title="Open builder">
                                                <span class="material-icons-outlined text-blue-600 text-xs">edit_note</span>
                                            </a>

                                            @can('surveys.delete')
                                            <form method="POST" action="{{ route('survey.destroy', $survey) }}"
                                                  onsubmit="return confirm('Delete this survey?')" class="inline-block">
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
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="mt-4 text-xs text-gray-600">
                    Showing {{ $surveys->firstItem() }} to {{ $surveys->lastItem() }} of {{ $surveys->total() }}
                    {{ Str::plural('entry', $surveys->total()) }}
                </div>

                @if($surveys->hasPages())
                    <div class="mt-3">
                        {{ $surveys->links() }}
                    </div>
                @endif
            @endif
        </div>
    </div>

    <script>
        // See window.copyToClipboard in resources/js/app.js. The fallback here was
        // unreachable over plain HTTP, where navigator.clipboard does not exist and
        // the call threw before returning a promise to catch.
        function copySurveyLink(url) {
            window.copyWithFeedback(url, 'Survey link copied to clipboard.');
        }
    </script>
</x-app-layout>
