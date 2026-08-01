<x-app-layout>
    <x-slot name="breadcrumb">
        <span>Reports</span>
        <span class="mx-2 text-gray-500">/</span>
        <span>Certificate Reports</span>
    </x-slot>

    <x-slot name="title">Certificate Reports</x-slot>

    <div class="bg-white rounded shadow-md border border-gray-300">
        <div class="p-6 border-b border-gray-200">
            <div class="flex flex-wrap justify-between items-start gap-3">
                <div>
                    <div class="flex items-center">
                        <span class="material-icons-outlined mr-2 text-primary-DEFAULT">card_membership</span>
                        <h1 class="text-xl font-bold text-gray-800">Certificate Reports</h1>
                    </div>
                    <p class="text-xs text-gray-500 mt-1 ml-8">Who has a certificate, who is still waiting, and which files are missing.</p>
                </div>
                <div class="flex gap-2">
                    <button type="button" onclick="window.print()"
                            class="h-9 px-3 border border-gray-300 rounded text-xs font-medium text-gray-700 hover:bg-gray-50 inline-flex items-center">
                        <span class="material-icons-outlined text-sm mr-1">print</span>
                        Print
                    </button>

                    @can('certificate_reports.export')
                        {{-- A real download of the filtered rows. This button used to call
                             alert('… future update') while the export permission existed
                             and was granted, so it looked available and did nothing. --}}
                        <form method="POST" action="{{ route('reports.certificates.export') }}" class="inline">
                            @csrf
                            @foreach(['search', 'event_filter', 'template_filter', 'date_filter'] as $carry)
                                <input type="hidden" name="{{ $carry }}" value="{{ request($carry) }}">
                            @endforeach
                            <button type="submit"
                                    class="h-9 px-3 rounded text-xs font-medium text-white bg-gradient-to-r from-green-600 to-green-500 hover:from-green-700 hover:to-green-600 inline-flex items-center shadow-sm">
                                <span class="material-icons-outlined text-sm mr-1">file_download</span>
                                Export CSV
                            </button>
                        </form>
                    @endcan
                </div>
            </div>
        </div>

        <div class="p-4">
            {{-- Every number here is counted. The page previously showed a hardcoded
                 98.5% "email delivery rate" and a recipient count derived from it;
                 nothing in the system records certificate email delivery. --}}
            <div class="mb-4 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
                <div class="border border-gray-200 rounded p-4">
                    <p class="text-xs text-gray-500">Certificates issued</p>
                    <p class="text-2xl font-bold text-gray-800 mt-0.5">{{ number_format($totalCertificates) }}</p>
                    <p class="text-[11px] text-gray-500 mt-1">
                        {{ number_format($issuedThisMonth) }} this month, {{ number_format($issuedLast7Days) }} in the last 7 days
                    </p>
                </div>

                <div class="border border-gray-200 rounded p-4">
                    <p class="text-xs text-gray-500">Participants covered</p>
                    <p class="text-2xl font-bold text-gray-800 mt-0.5">{{ $coverageRate }}%</p>
                    <div class="w-full bg-gray-100 rounded-full h-1.5 mt-2">
                        <div class="bg-primary-DEFAULT h-1.5 rounded-full" style="width: {{ min(100, $coverageRate) }}%"></div>
                    </div>
                    <p class="text-[11px] text-gray-500 mt-1">
                        {{ number_format($participantsWithCertificate) }} of {{ number_format($participantsInScope) }} participants
                    </p>
                </div>

                <div class="border border-gray-200 rounded p-4">
                    <p class="text-xs text-gray-500">Still waiting</p>
                    <p class="text-2xl font-bold {{ $participantsWaiting > 0 ? 'text-amber-700' : 'text-gray-800' }} mt-0.5">
                        {{ number_format($participantsWaiting) }}
                    </p>
                    <p class="text-[11px] text-gray-500 mt-1">
                        Registered participants with no certificate yet
                    </p>
                </div>

                <div class="border border-gray-200 rounded p-4">
                    <p class="text-xs text-gray-500">Missing PDF file</p>
                    <p class="text-2xl font-bold {{ $missingFile > 0 ? 'text-red-700' : 'text-gray-800' }} mt-0.5">
                        {{ number_format($missingFile) }}
                    </p>
                    <p class="text-[11px] text-gray-500 mt-1">
                        {{ $eventsCovered }} of {{ $totalEvents }} {{ $totalEvents === 1 ? 'event' : 'events' }} covered,
                        {{ $templatesInUse }} {{ $templatesInUse === 1 ? 'template' : 'templates' }} in use
                    </p>
                </div>
            </div>

            {{-- Search takes the remaining space; the filters keep their own width. --}}
            <form method="GET" action="{{ route('reports.certificates') }}" class="flex flex-wrap items-center gap-2 mb-4">
                <input type="text" name="search" id="searchInput" value="{{ request('search') }}"
                       placeholder="Search participant name, email, event, certificate #..."
                       class="flex-1 min-w-[12rem] h-9 text-xs border-gray-300 rounded px-3 focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50">

                <select name="event_filter" onchange="this.form.submit()"
                        class="h-9 text-xs border-gray-300 rounded pl-3 pr-8 w-[13rem] shrink-0 focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50">
                    <option value="">All Events</option>
                    @foreach($events as $event)
                        <option value="{{ $event->id }}" @if(request('event_filter') == $event->id) selected @endif>{{ $event->name }}</option>
                    @endforeach
                </select>

                <select name="template_filter" onchange="this.form.submit()"
                        class="h-9 text-xs border-gray-300 rounded pl-3 pr-8 w-[13rem] shrink-0 focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50">
                    <option value="">All Templates</option>
                    @foreach($templates as $template)
                        <option value="{{ $template->id }}" @if(request('template_filter') == $template->id) selected @endif>{{ $template->name }}</option>
                    @endforeach
                </select>

                {{-- These ranges look backwards. They used to count forwards from
                     today, so "This Week" meant the next seven days and matched
                     nothing at all. --}}
                <select name="date_filter" onchange="this.form.submit()"
                        class="h-9 text-xs border-gray-300 rounded pl-3 pr-8 w-[10rem] shrink-0 focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50">
                    <option value="">Any date</option>
                    <option value="today" @if(request('date_filter') == 'today') selected @endif>Today</option>
                    <option value="week" @if(request('date_filter') == 'week') selected @endif>Last 7 days</option>
                    <option value="month" @if(request('date_filter') == 'month') selected @endif>This month</option>
                    <option value="year" @if(request('date_filter') == 'year') selected @endif>This year</option>
                </select>

                <button type="submit"
                        class="h-9 px-3 bg-primary-DEFAULT hover:bg-primary-dark text-white rounded text-xs flex items-center shrink-0 transition-colors duration-200 ease-in-out" title="Search">
                    <span class="material-icons-outlined text-xs">search</span>
                </button>

                @if(request('search') || request('event_filter') || request('template_filter') || request('date_filter'))
                    <a href="{{ route('reports.certificates') }}" class="text-xs text-gray-500 underline shrink-0">Reset</a>
                @endif
            </form>

            @if(request('search') || request('event_filter') || request('template_filter') || request('date_filter'))
                <div class="bg-blue-50 border border-blue-200 text-blue-700 px-3 py-2 rounded mb-4 text-xs">
                    <span class="font-medium">Filtered:</span>
                    @if(request('search'))
                        <span class="ml-2">"{{ request('search') }}"</span>
                    @endif
                    @if(request('event_filter'))
                        <span class="ml-2">Event: {{ $events->firstWhere('id', (int) request('event_filter'))->name ?? 'Unknown' }}</span>
                    @endif
                    @if(request('template_filter'))
                        <span class="ml-2">Template: {{ $templates->firstWhere('id', (int) request('template_filter'))->name ?? 'Unknown' }}</span>
                    @endif
                    @if(request('date_filter'))
                        <span class="ml-2">Date: {{ ucfirst(str_replace('_', ' ', request('date_filter'))) }}</span>
                    @endif
                    <span class="ml-2">({{ number_format($certificates->total()) }} results)</span>
                </div>
            @endif

            <div class="overflow-x-auto border border-gray-200 rounded">
                <table class="min-w-full border-collapse">
                    <thead>
                        <tr class="bg-primary-light text-white text-xs uppercase">
                            <th class="py-3 px-4 text-left rounded-tl">Certificate</th>
                            <th class="py-3 px-4 text-left">Participant</th>
                            <th class="py-3 px-4 text-left">Event</th>
                            <th class="py-3 px-4 text-left">Template</th>
                            <th class="py-3 px-4 text-left">Issued</th>
                            <th class="py-3 px-4 text-left">File</th>
                            <th class="py-3 px-4 text-center rounded-tr">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($certificates as $certificate)
                            <tr class="text-xs hover:bg-gray-50">
                                <td class="py-3 px-4 font-mono">{{ $certificate->certificate_number }}</td>
                                <td class="py-3 px-4">
                                    <div class="font-medium text-gray-800">{{ $certificate->participant->name ?? 'Unknown' }}</div>
                                    <div class="text-gray-500">{{ $certificate->participant->email ?? '—' }}</div>
                                </td>
                                <td class="py-3 px-4 break-words max-w-[200px]">{{ $certificate->event->name ?? 'Unknown' }}</td>
                                <td class="py-3 px-4">{{ $certificate->template->name ?? '—' }}</td>
                                <td class="py-3 px-4 whitespace-nowrap">
                                    {{ $certificate->generated_at ? $certificate->generated_at->format('d M Y, H:i') : '—' }}
                                </td>
                                <td class="py-3 px-4">
                                    {{-- This column read "Issued" for every row regardless of
                                         state. Whether the PDF exists is the thing that
                                         actually varies and the thing worth knowing. --}}
                                    @if($certificate->pdf_file)
                                        <span class="bg-green-100 text-green-700 px-2 py-1 rounded-full text-xs">Ready</span>
                                    @else
                                        <span class="bg-amber-100 text-amber-800 px-2 py-1 rounded-full text-xs">No file</span>
                                    @endif
                                </td>
                                <td class="py-3 px-4">
                                    <div class="flex justify-center space-x-2">
                                        <a href="{{ route('reports.certificates.show', ['id' => $certificate->certificate_number]) }}"
                                           class="p-1 bg-blue-50 rounded hover:bg-blue-100 border border-blue-100" title="View">
                                            <span class="material-icons-outlined text-primary-DEFAULT text-xs">visibility</span>
                                        </a>

                                        {{-- Downloading is reading a file, not exporting a
                                             report; it used to be gated on the export
                                             permission. --}}
                                        @if($certificate->pdf_file)
                                            <a href="{{ route('reports.certificates.download', ['id' => $certificate->certificate_number]) }}"
                                               class="p-1 bg-green-50 rounded hover:bg-green-100 border border-green-100" title="Download">
                                                <span class="material-icons-outlined text-green-600 text-xs">download</span>
                                            </a>
                                        @endif

                                        @can('certificate_reports.send')
                                            @if($certificate->participant->email ?? null)
                                                <button type="button"
                                                        onclick="sendEmail('{{ $certificate->certificate_number }}', '{{ $certificate->participant->email }}')"
                                                        class="p-1 bg-purple-50 rounded hover:bg-purple-100 border border-purple-100" title="Email to participant">
                                                    <span class="material-icons-outlined text-purple-600 text-xs">email</span>
                                                </button>
                                            @endif
                                        @endcan

                                        @can('certificate_reports.delete')
                                            <form method="POST" action="{{ route('reports.certificates.delete', ['id' => $certificate->certificate_number]) }}"
                                                  onsubmit="return confirm('Delete this certificate? The PDF file is removed too.')" class="inline-block">
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
                                <td colspan="7" class="py-8 text-center text-gray-500">No certificates found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-6 flex flex-col sm:flex-row sm:items-center sm:justify-between">
                <div class="mb-2 sm:mb-0 text-xs text-gray-500">
                    @if($certificates->total() > 0)
                        Showing <span class="font-medium">{{ $certificates->firstItem() }}</span>
                        to <span class="font-medium">{{ $certificates->lastItem() }}</span>
                        of <span class="font-medium">{{ number_format($certificates->total()) }}</span> entries
                    @else
                        Showing <span class="font-medium">0</span> entries
                    @endif
                </div>
                <div class="flex justify-end">
                    {{ $certificates->links('components.pagination-modern') }}
                </div>
            </div>
        </div>
    </div>

    <div id="sendEmailModal" class="fixed inset-0 z-50 items-center justify-center bg-gray-600 bg-opacity-50 hidden">
        <div class="bg-white rounded shadow-xl w-full max-w-md mx-4 overflow-hidden">
            <div class="px-5 py-3 border-b border-gray-200 flex items-center justify-between">
                <h3 class="text-sm font-semibold text-gray-800">Send certificate</h3>
                <button type="button" onclick="closeSendEmailModal()" class="text-gray-400 hover:text-gray-600">
                    <span class="material-icons-outlined text-base">close</span>
                </button>
            </div>
            <div class="px-5 py-4">
                <p id="sendEmailText" class="mb-4 text-xs text-gray-700"></p>
                <div class="flex justify-end gap-2">
                    <button type="button" onclick="closeSendEmailModal()"
                            class="h-9 px-3 border border-gray-300 rounded text-xs font-medium text-gray-700 hover:bg-gray-50">
                        Cancel
                    </button>
                    <button type="button" id="sendEmailConfirmBtn"
                            class="h-9 px-3 rounded text-xs font-medium text-white bg-gradient-to-r from-blue-600 to-blue-500 hover:from-blue-700 hover:to-blue-600 inline-flex items-center">
                        <span class="material-icons-outlined text-sm mr-1">send</span>
                        Send
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            let searchTimeout;
            const searchInput = document.getElementById('searchInput');

            if (searchInput) {
                searchInput.addEventListener('input', function () {
                    clearTimeout(searchTimeout);
                    searchTimeout = setTimeout(() => this.form.submit(), 500);
                });
            }
        });

        let currentCertificateId = null;

        function sendEmail(certificateId, email) {
            if (!email) {
                alert('No email address available for this participant.');
                return;
            }

            currentCertificateId = certificateId;
            document.getElementById('sendEmailText').innerText = 'Send this certificate to ' + email + '?';

            const modal = document.getElementById('sendEmailModal');
            modal.classList.remove('hidden');
            modal.classList.add('flex');
        }

        function closeSendEmailModal() {
            const modal = document.getElementById('sendEmailModal');
            modal.classList.add('hidden');
            modal.classList.remove('flex');
            currentCertificateId = null;
        }

        document.getElementById('sendEmailConfirmBtn').addEventListener('click', function () {
            if (!currentCertificateId) {
                return;
            }

            const button = this;
            const original = button.innerHTML;
            button.disabled = true;
            button.innerHTML = '<span class="material-icons-outlined text-sm mr-1 animate-spin">refresh</span>Sending';

            fetch('/reports/certificates/' + encodeURIComponent(currentCertificateId) + '/send-email', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({})
            })
            .then(response => response.json())
            .then(data => {
                button.disabled = false;
                button.innerHTML = original;
                closeSendEmailModal();
                alert(data.message || (data.success ? 'Email sent.' : 'Failed to send email.'));
            })
            .catch(() => {
                button.disabled = false;
                button.innerHTML = original;
                closeSendEmailModal();
                alert('Failed to send email.');
            });
        });
    </script>
</x-app-layout>
