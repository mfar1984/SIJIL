<x-app-layout>
    <x-slot name="breadcrumb">
        <span>Reports</span>
        <span class="mx-2 text-gray-500">/</span>
        <span>Certificate Reports</span>
    </x-slot>

    <x-slot name="title">Certificate Reports</x-slot>

    <div class="bg-white rounded shadow-md border border-gray-300">
        <div class="p-6 border-b border-gray-200">
            <div class="flex justify-between items-start">
                <div>
                    <div class="flex items-center">
                        <span class="material-icons mr-2 text-primary-DEFAULT">card_membership</span>
                        <h1 class="text-xl font-bold text-gray-800">Certificate Reports</h1>
                    </div>
                    <p class="text-xs text-gray-500 mt-1 ml-8">Monitor and export certificate issuance data</p>
                </div>
                <div class="flex gap-2">
                    <a href="#" onclick="printReport()" class="bg-white border border-gray-300 hover:bg-gray-50 text-gray-700 px-3 h-[36px] rounded shadow-sm font-medium flex items-center text-xs transition-colors duration-200 ease-in-out">
                        <span class="material-icons text-xs mr-1">print</span>
                        Print
                    </a>
                    @can('certificate_reports.export')
                    <a href="#" onclick="exportCertificateReport()" class="bg-gradient-to-r from-green-600 to-green-500 hover:from-green-700 hover:to-green-600 text-white px-3 h-[36px] rounded shadow-sm font-medium flex items-center text-xs transition-colors duration-200 ease-in-out">
                        <span class="material-icons text-xs mr-1">file_download</span>
                        Export Report
                    </a>
                    @endcan
                </div>
            </div>
        </div>
        
        <div class="p-4">
            
            <!-- Certificate Summary -->
            <div class="mb-4 grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="bg-amber-50 rounded-md p-4 border border-amber-100">
                    <p class="text-xs text-amber-700 font-medium">Total Certificates Issued</p>
                    <p class="text-2xl font-bold text-amber-800">{{ number_format($totalCertificates) }}</p>
                    <div class="mt-1 text-xs text-amber-600 flex items-center">
                        <span class="material-icons text-xs mr-1">card_membership</span>
                        <span>Certificates in the system</span>
                    </div>
                </div>
                
                <div class="bg-blue-50 rounded-md p-4 border border-blue-100">
                    <p class="text-xs text-blue-700 font-medium">Certificate Templates</p>
                    <p class="text-2xl font-bold text-blue-800">{{ $totalTemplates }}</p>
                    <div class="mt-1 text-xs text-blue-600 flex items-center">
                        <span class="material-icons text-xs mr-1">add_circle</span>
                        <span>{{ $newTemplatesThisMonth }} new templates this month</span>
                    </div>
                </div>
                
                <div class="bg-green-50 rounded-md p-4 border border-green-100">
                    <p class="text-xs text-green-700 font-medium">Email Delivery Rate</p>
                    <p class="text-2xl font-bold text-green-800">{{ $emailDeliveryRate }}%</p>
                    <div class="mt-1 text-xs text-green-600 flex items-center">
                        <span class="material-icons text-xs mr-1">check_circle</span>
                        <span>{{ number_format($emailsDelivered) }} emails delivered successfully</span>
                    </div>
                </div>
            </div>
            
            <!-- Show Entries & Filter Row -->
            <div class="mb-4">
                <form method="GET" action="{{ route('reports.certificates') }}" class="flex flex-wrap gap-2 items-center justify-between">
                    <!-- Show Entries Dropdown -->
                    <div class="flex items-center gap-2">
                        <span class="text-xs text-gray-600 font-medium">Show</span>
                        <select name="per_page" onchange="this.form.submit()" class="appearance-none px-2 py-1 text-xs border border-gray-300 rounded focus:ring focus:ring-primary-light focus:border-primary-light bg-white bg-no-repeat bg-right w-[60px] font-medium" style="background-image: url('data:image/svg+xml;charset=US-ASCII,<svg xmlns=%22http://www.w3.org/2000/svg%22 width=%2216%22 height=%2216%22 viewBox=%220 0 24 24%22 fill=%22none%22 stroke=%22%23888%22 stroke-width=%222%22 stroke-linecap=%22round%22 stroke-linejoin=%22round%22><polyline points=%226 9 12 15 18 9%22></polyline></svg>'); background-position: right 0.25rem center; background-size: 0.75em;">
                            <option value="10" @if(request('per_page', 10) == 10) selected @endif>10</option>
                            <option value="25" @if(request('per_page') == 25) selected @endif>25</option>
                            <option value="50" @if(request('per_page') == 50) selected @endif>50</option>
                            <option value="100" @if(request('per_page') == 100) selected @endif>100</option>
                        </select>
                        <span class="text-xs text-gray-600">entries per page</span>
                    </div>
                    
                    <!-- Search & Filter Controls -->
                    <div class="flex flex-wrap gap-2 items-center">
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Search participant name, email, event, certificate #..." class="border border-gray-300 rounded px-2 py-1 text-xs focus:ring focus:ring-primary-light focus:border-primary-light" id="searchInput" />
                        <select name="event_filter" onchange="this.form.submit()" class="appearance-none px-3 py-1.5 pr-8 text-xs border border-gray-300 rounded focus:ring focus:ring-primary-light focus:border-primary-light bg-white bg-no-repeat bg-right max-w-[200px] truncate" style="background-image: url('data:image/svg+xml;charset=US-ASCII,<svg xmlns=%22http://www.w3.org/2000/svg%22 width=%2216%22 height=%2216%22 viewBox=%220 0 24 24%22 fill=%22none%22 stroke=%22%23888%22 stroke-width=%222%22 stroke-linecap=%22round%22 stroke-linejoin=%22round%22><polyline points=%226 9 12 15 18 9%22></polyline></svg>'); background-position: right 0.75rem center; background-size: 1em;">
                            <option value="">All Events</option>
                            @foreach($events as $event)
                                <option value="{{ $event->id }}" @if(request('event_filter') == $event->id) selected @endif class="truncate">{{ $event->name }}</option>
                            @endforeach
                        </select>
                        <select name="template_filter" onchange="this.form.submit()" class="appearance-none px-3 py-1.5 pr-8 text-xs border border-gray-300 rounded focus:ring focus:ring-primary-light focus:border-primary-light bg-white bg-no-repeat bg-right max-w-[200px] truncate" style="background-image: url('data:image/svg+xml;charset=US-ASCII,<svg xmlns=%22http://www.w3.org/2000/svg%22 width=%2216%22 height=%2216%22 viewBox=%220 0 24 24%22 fill=%22none%22 stroke=%22%23888%22 stroke-width=%222%22 stroke-linecap=%22round%22 stroke-linejoin=%22round%22><polyline points=%226 9 12 15 18 9%22></polyline></svg>'); background-position: right 0.75rem center; background-size: 1em;">
                            <option value="">All Templates</option>
                            @foreach($templates as $template)
                                <option value="{{ $template->id }}" @if(request('template_filter') == $template->id) selected @endif class="truncate">{{ $template->name }}</option>
                            @endforeach
                        </select>
                        <select name="date_filter" onchange="this.form.submit()" class="appearance-none px-3 py-1.5 pr-8 text-xs border border-gray-300 rounded focus:ring focus:ring-primary-light focus:border-primary-light bg-white bg-no-repeat bg-right w-[120px]" style="background-image: url('data:image/svg+xml;charset=US-ASCII,<svg xmlns=%22http://www.w3.org/2000/svg%22 width=%2216%22 height=%2216%22 viewBox=%220 0 24 24%22 fill=%22none%22 stroke=%22%23888%22 stroke-width=%222%22 stroke-linecap=%22round%22 stroke-linejoin=%22round%22><polyline points=%226 9 12 15 18 9%22></polyline></svg>'); background-position: right 0.75rem center; background-size: 1em;">
                            <option value="">All Dates</option>
                            <option value="today" @if(request('date_filter') == 'today') selected @endif>Today</option>
                            <option value="week" @if(request('date_filter') == 'week') selected @endif>This Week</option>
                            <option value="month" @if(request('date_filter') == 'month') selected @endif>This Month</option>
                            <option value="past" @if(request('date_filter') == 'past') selected @endif>Past</option>
                        </select>
                        <button type="submit" class="bg-primary-light text-white px-3 py-1 h-[36px] rounded text-xs font-medium flex items-center justify-center" title="Search">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4-4m0 0A7 7 0 104 4a7 7 0 0013 13z" />
                            </svg>
                        </button>
                        @if(request('search') || request('event_filter') || request('template_filter') || request('date_filter'))
                            <a href="{{ route('reports.certificates') }}?per_page={{ request('per_page', 10) }}" class="text-xs text-gray-500 underline ml-2">Reset</a>
                        @endif
                    </div>
                </form>
            </div>
            
            <!-- Search Results Summary -->
            @if(request('search') || request('event_filter') || request('template_filter') || request('date_filter'))
                <div class="bg-blue-50 border border-blue-200 text-blue-700 px-4 py-2 rounded mb-4 text-xs">
                    <span class="font-medium">Search Results:</span>
                    @if(request('search'))
                        <span class="ml-2">Searching for "{{ request('search') }}"</span>
                    @endif
                    @if(request('event_filter'))
                        <span class="ml-2">Event: {{ $events->find(request('event_filter'))->name ?? 'Unknown' }}</span>
                    @endif
                    @if(request('template_filter'))
                        <span class="ml-2">Template: {{ $templates->find(request('template_filter'))->name ?? 'Unknown' }}</span>
                    @endif
                    @if(request('date_filter'))
                        <span class="ml-2">Date: {{ ucfirst(str_replace('_', ' ', request('date_filter'))) }}</span>
                    @endif
                    <span class="ml-2">({{ $certificates->total() }} results)</span>
                </div>
            @endif
            
            <!-- Certificate Table -->
            <div class="overflow-visible border border-gray-200 rounded">
                <table class="min-w-full border-collapse">
                    <thead>
                        <tr class="bg-primary-light text-white text-xs uppercase">
                            <th class="py-3 px-4 text-left rounded-tl">Certificate ID</th>
                            <th class="py-3 px-4 text-left">Participant</th>
                            <th class="py-3 px-4 text-left">Event</th>
                            <th class="py-3 px-4 text-left">Issue Date</th>
                            <th class="py-3 px-4 text-left">Status</th>
                            <th class="py-3 px-4 text-center rounded-tr">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($certificates as $certificate)
                            <tr class="text-xs hover:bg-gray-50">
                                <td class="py-3 px-4">{{ $certificate->certificate_number }}</td>
                                <td class="py-3 px-4 font-medium">{{ $certificate->participant->name ?? 'Unknown' }}</td>
                                <td class="py-3 px-4 break-words max-w-[200px]">{{ $certificate->event->name ?? 'Unknown' }}</td>
                                <td class="py-3 px-4">{{ $certificate->generated_at ? $certificate->generated_at->format('d M Y') : 'N/A' }}</td>
                                <td class="py-3 px-4">
                                    <span class="bg-status-active-bg text-status-active-text px-2 py-1 rounded-full text-xs">Issued</span>
                                </td>
                                <td class="py-3 px-4">
                                    <div class="flex justify-center space-x-2">
                                        <a href="{{ route('reports.certificates.show', ['id' => $certificate->certificate_number]) }}" class="p-1 bg-blue-50 rounded hover:bg-blue-100 border border-blue-100" title="View">
                                            <span class="material-icons text-primary-DEFAULT text-xs">visibility</span>
                                        </a>
                                        @can('certificate_reports.export')
                                        <a href="{{ route('reports.certificates.download', ['id' => $certificate->certificate_number]) }}" class="p-1 bg-green-50 rounded hover:bg-green-100 border border-green-100" title="Download">
                                            <span class="material-icons text-green-600 text-xs">download</span>
                                        </a>
                                        <a href="#" onclick="sendEmail('{{ $certificate->certificate_number }}', '{{ $certificate->participant->email ?? '' }}')" class="p-1 bg-purple-50 rounded hover:bg-purple-100 border border-purple-100" title="Email">
                                            <span class="material-icons text-purple-600 text-xs">email</span>
                                        </a>
                                        @endcan
                                        @can('certificate_reports.delete')
                                        <form method="POST" action="{{ route('reports.certificates.delete', ['id' => $certificate->certificate_number]) }}" onsubmit="return confirm('Are you sure you want to delete this certificate?')" class="inline-block">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="p-1 bg-red-50 rounded hover:bg-red-100 border border-red-100" title="Delete">
                                                <span class="material-icons text-red-600 text-xs">delete</span>
                                            </button>
                                        </form>
                                        @endcan
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr class="text-xs">
                                <td colspan="6" class="py-8 text-center text-gray-500">No certificates found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination -->
            <div class="mt-6 flex flex-col sm:flex-row sm:items-center sm:justify-between">
                <div class="mb-2 sm:mb-0 text-xs text-gray-500">
                    @if($certificates->total() > 0)
                        Showing <span class="font-medium">{{ $certificates->firstItem() }}</span> to <span class="font-medium">{{ $certificates->lastItem() }}</span> of <span class="font-medium">{{ $certificates->total() }}</span> entries
                    @else
                        Showing <span class="font-medium">0</span> to <span class="font-medium">0</span> of <span class="font-medium">0</span> entries
                    @endif
                </div>
                <div class="flex justify-end">
                    {{ $certificates->appends(request()->query())->links('components.pagination-modern') }}
                </div>
            </div>
        </div>
    </div>

    <!-- Modal for sending email -->
    <div id="sendEmailModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-40 hidden">
        <div class="bg-white rounded-lg shadow-xl w-full max-w-md mx-4 overflow-hidden">
            <div class="px-6 py-4 border-b flex items-center justify-between">
                <h3 class="text-lg font-medium">Send Certificate Email</h3>
                <button onclick="closeSendEmailModal()" class="text-gray-500 hover:text-gray-700">
                    <span class="material-icons">close</span>
                </button>
            </div>
            <div class="px-6 py-4">
                <p id="sendEmailText" class="mb-4 text-sm text-gray-700"></p>
                <div class="flex justify-end">
                    <button onclick="closeSendEmailModal()" class="bg-white border border-gray-300 text-gray-700 hover:bg-gray-50 px-4 py-2 rounded-md text-xs mr-2">Cancel</button>
                    <button id="sendEmailConfirmBtn" class="bg-gradient-to-r from-blue-600 to-blue-500 hover:from-blue-700 hover:to-blue-600 text-white px-4 py-2 rounded-md text-xs font-semibold shadow-sm flex items-center">Send</button>
                </div>
            </div>
        </div>
    </div>

    <!-- JavaScript for the page -->
    <script>
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
        
        function exportCertificateReport() {
            // Logic to export certificate report
            alert('This feature will be implemented in a future update.');
        }
        
        function printReport() {
            // Logic to print report
            window.print();
        }

        let currentCertificateId = null;
        let currentEmail = null;
        function sendEmail(certificateId, email) {
            if (!email) {
                alert('No email address available for this participant.');
                return;
            }
            currentCertificateId = certificateId;
            currentEmail = email;
            document.getElementById('sendEmailText').innerText = `Send certificate to: ${email}`;
            document.getElementById('sendEmailModal').classList.remove('hidden');
        }
        function closeSendEmailModal() {
            document.getElementById('sendEmailModal').classList.add('hidden');
            currentCertificateId = null;
            currentEmail = null;
        }
        document.getElementById('sendEmailConfirmBtn').onclick = function() {
            if (!currentCertificateId || !currentEmail) return;
            fetch(`/reports/certificates/${currentCertificateId}/send-email`, {
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
                closeSendEmailModal();
                if (data.success) {
                    alert('Email sent successfully!\n' + data.message);
                } else {
                    alert('Failed to send email.\n' + (data.message || 'Unknown error.'));
                }
            })
            .catch(() => {
                closeSendEmailModal();
                alert('Failed to send email.');
            });
        };
    </script>
</x-app-layout> 