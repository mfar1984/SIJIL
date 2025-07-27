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
                    <a href="#" onclick="printReport()" class="bg-white border border-gray-300 hover:bg-gray-50 text-gray-700 px-3 py-1 rounded shadow-sm font-medium flex items-center text-xs transition-colors duration-200 ease-in-out">
                        <span class="material-icons text-xs mr-1">print</span>
                        Print
                    </a>
                    <a href="#" onclick="exportCertificateReport()" class="bg-gradient-to-r from-green-600 to-green-500 hover:from-green-700 hover:to-green-600 text-white px-3 py-1 rounded shadow-sm font-medium flex items-center text-xs transition-colors duration-200 ease-in-out">
                        <span class="material-icons text-xs mr-1">file_download</span>
                        Export Report
                    </a>
                </div>
            </div>
        </div>
        
        <div class="p-4">
            <!-- Filters -->
            <div class="mb-4 grid grid-cols-1 md:grid-cols-4 gap-4">
                <div>
                    <label for="event_filter" class="block text-xs font-medium text-gray-700 mb-1">Event</label>
                    <select id="event_filter" name="event_filter" class="w-full rounded-md border-gray-300 shadow-sm focus:border-primary-DEFAULT focus:ring focus:ring-primary-DEFAULT focus:ring-opacity-50 text-sm">
                        <option value="">All Events</option>
                        @foreach($events as $event)
                            <option value="{{ $event->id }}" {{ request('event_filter') == $event->id ? 'selected' : '' }}>{{ $event->name }}</option>
                        @endforeach
                    </select>
                </div>
                
                <div>
                    <label for="template_filter" class="block text-xs font-medium text-gray-700 mb-1">Template</label>
                    <select id="template_filter" name="template_filter" class="w-full rounded-md border-gray-300 shadow-sm focus:border-primary-DEFAULT focus:ring focus:ring-primary-DEFAULT focus:ring-opacity-50 text-sm">
                        <option value="">All Templates</option>
                        @foreach($templates as $template)
                            <option value="{{ $template->id }}" {{ request('template_filter') == $template->id ? 'selected' : '' }}>{{ $template->name }}</option>
                        @endforeach
                    </select>
                </div>
                
                <div>
                    <label for="date_range" class="block text-xs font-medium text-gray-700 mb-1">Issue Date</label>
                    <input type="text" id="date_range" name="date_range" value="{{ request('date_range') }}" class="w-full rounded-md border-gray-300 shadow-sm focus:border-primary-DEFAULT focus:ring focus:ring-primary-DEFAULT focus:ring-opacity-50 text-sm" placeholder="Select date range">
                </div>
                
                <div class="flex items-end">
                    <button type="button" onclick="filterCertificates()" class="bg-primary-DEFAULT hover:bg-primary-dark text-white px-4 py-2 rounded-md text-xs flex items-center">
                        <span class="material-icons text-xs mr-1">filter_list</span>
                        Apply Filter
                    </button>
                    <button type="button" onclick="resetCertificateFilters()" class="ml-2 bg-white border border-gray-300 text-gray-700 hover:bg-gray-50 px-4 py-2 rounded-md text-xs flex items-center">
                        <span class="material-icons text-xs mr-1">refresh</span>
                        Reset
                    </button>
                </div>
            </div>
            
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
                                        <a href="{{ route('reports.certificates.download', ['id' => $certificate->certificate_number]) }}" class="p-1 bg-green-50 rounded hover:bg-green-100 border border-green-100" title="Download">
                                            <span class="material-icons text-green-600 text-xs">download</span>
                                        </a>
                                        <a href="#" onclick="sendEmail('{{ $certificate->certificate_number }}', '{{ $certificate->participant->email ?? '' }}')" class="p-1 bg-purple-50 rounded hover:bg-purple-100 border border-purple-100" title="Email">
                                            <span class="material-icons text-purple-600 text-xs">email</span>
                                        </a>
                                        <form method="POST" action="{{ route('reports.certificates.delete', ['id' => $certificate->certificate_number]) }}" onsubmit="return confirm('Are you sure you want to delete this certificate?')" class="inline-block">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="p-1 bg-red-50 rounded hover:bg-red-100 border border-red-100" title="Delete">
                                                <span class="material-icons text-red-600 text-xs">delete</span>
                                            </button>
                                        </form>
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
        function filterCertificates() {
            // Get filter values
            const eventFilter = document.getElementById('event_filter').value;
            const templateFilter = document.getElementById('template_filter').value;
            const dateRange = document.getElementById('date_range').value;
            
            // Build query string
            let queryParams = [];
            if (eventFilter) {
                queryParams.push(`event_filter=${eventFilter}`);
            }
            if (templateFilter) {
                queryParams.push(`template_filter=${templateFilter}`);
            }
            if (dateRange) {
                queryParams.push(`date_range=${encodeURIComponent(dateRange)}`);
            }
            
            // Redirect with query parameters
            const queryString = queryParams.length > 0 ? `?${queryParams.join('&')}` : '';
            window.location.href = `{{ route('reports.certificates') }}${queryString}`;
        }
        
        function resetCertificateFilters() {
            // Reset form fields
            document.getElementById('event_filter').value = '';
            document.getElementById('template_filter').value = '';
            document.getElementById('date_range').value = '';
            
            // Redirect to base URL without query parameters
            window.location.href = "{{ route('reports.certificates') }}";
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
        
        // Initialize date range picker if available
        document.addEventListener('DOMContentLoaded', function() {
            // Check if date range picker library is available
            if (typeof flatpickr !== 'undefined') {
                flatpickr('#date_range', {
                    mode: 'range',
                    dateFormat: 'Y-m-d',
                    // Set initial value if exists in URL params
                    defaultDate: "{{ request('date_range') }}"
                });
            }
        });
    </script>
</x-app-layout> 