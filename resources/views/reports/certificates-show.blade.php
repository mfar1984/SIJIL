<x-app-layout>
    <x-slot name="breadcrumb">
        <span>Reports</span>
        <span class="mx-2 text-gray-500">/</span>
        <span>Certificate Reports</span>
        <span class="mx-2 text-gray-500">/</span>
        <span>Certificate Details</span>
    </x-slot>

    <x-slot name="title">Certificate Details</x-slot>

    <div class="bg-white rounded shadow-md border border-gray-300">
        <div class="p-6 border-b border-gray-200">
            <div class="flex justify-between items-start">
                <div>
                    <div class="flex items-center">
                        <span class="material-icons mr-2 text-primary-DEFAULT">card_membership</span>
                        <h1 class="text-xl font-bold text-gray-800">Certificate Details</h1>
                    </div>
                    <p class="text-xs text-gray-500 mt-1 ml-8">
                        Certificate ID: {{ $certificate->certificate_number }}
                    </p>
                </div>
                <div class="flex space-x-2">
                    <a href="{{ route('reports.certificates') }}" class="bg-white border border-gray-300 text-gray-700 hover:bg-gray-50 px-3 h-[36px] rounded shadow-sm font-medium flex items-center text-xs transition-colors duration-200 ease-in-out">
                        <span class="material-icons text-xs mr-1">arrow_back</span>
                        Back to List
                    </a>
                    <button onclick="printCertificate()" class="bg-white border border-gray-300 text-gray-700 hover:bg-gray-50 px-3 h-[36px] rounded shadow-sm font-medium flex items-center text-xs transition-colors duration-200 ease-in-out">
                        <span class="material-icons text-xs mr-1">print</span>
                        Print
                    </button>
                    @can('certificate_reports.export')
                    <a href="{{ route('reports.certificates.download', ['id' => $certificate->certificate_number]) }}" class="bg-gradient-to-r from-green-600 to-green-500 hover:from-green-700 hover:to-green-600 text-white px-3 h-[36px] rounded shadow-sm font-medium flex items-center text-xs transition-colors duration-200 ease-in-out">
                        <span class="material-icons text-xs mr-1">file_download</span>
                        Download PDF
                    </a>
                    @endcan
                </div>
            </div>
        </div>
        
        <div class="p-4">
            <!-- Certificate Preview -->
            <div class="mb-6">
                <div class="border border-gray-200 rounded-md p-4 bg-gray-50">
                    <div class="flex justify-between items-center mb-3">
                        <h2 class="text-sm font-semibold text-gray-700 flex items-center">
                            <span class="material-icons text-primary-DEFAULT mr-2 text-base">preview</span>
                            Certificate Preview
                        </h2>
                        @if($certificate->pdf_file)
                        <a href="{{ asset('storage/' . $certificate->pdf_file) }}" target="_blank" class="text-xs text-primary-DEFAULT flex items-center">
                            <span class="material-icons text-xs mr-1">fullscreen</span>
                            View Full Size
                        </a>
                        @endif
                    </div>
                    
                    <div class="flex justify-center">
                        @if($certificate->pdf_file)
                            <div class="relative w-full max-w-3xl aspect-[1.414/1] border border-gray-300 shadow-md bg-white">
                                <iframe src="{{ asset('storage/' . $certificate->pdf_file) }}" class="w-full h-full"></iframe>
                            </div>
                        @else
                            <div class="relative w-full max-w-3xl aspect-[1.414/1] border border-gray-300 shadow-md bg-white flex items-center justify-center">
                                <p class="text-gray-500">Certificate preview not available</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
            
            <!-- Certificate Information -->
            <div class="mb-6 border border-gray-200 rounded-md p-4">
                <h2 class="text-sm font-semibold text-gray-700 mb-4 flex items-center">
                    <span class="material-icons text-primary-DEFAULT mr-2 text-base">info</span>
                    Certificate Information
                </h2>
                
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 text-xs">
                    <div>
                        <p class="text-gray-500 mb-1">Certificate ID</p>
                        <p class="font-medium">{{ $certificate->certificate_number }}</p>
                    </div>
                    
                    <div>
                        <p class="text-gray-500 mb-1">Issue Date</p>
                        <p class="font-medium">{{ $certificate->generated_at ? $certificate->generated_at->format('d M Y') : 'N/A' }}</p>
                    </div>
                    
                    <div>
                        <p class="text-gray-500 mb-1">Status</p>
                        <p class="font-medium">
                            <span class="bg-green-100 text-green-800 px-2 py-1 rounded-full text-xs">Issued</span>
                        </p>
                    </div>
                    
                    <div>
                        <p class="text-gray-500 mb-1">Generated By</p>
                        <p class="font-medium">{{ $certificate->generator->name ?? 'System' }}</p>
                    </div>
                    
                    <div>
                        <p class="text-gray-500 mb-1">Expiry Date</p>
                        <p class="font-medium">No Expiry</p>
                    </div>
                </div>
            </div>
            
            <!-- Recipient Information -->
            <div class="mb-6 border border-gray-200 rounded-md p-4">
                <h2 class="text-sm font-semibold text-gray-700 mb-4 flex items-center">
                    <span class="material-icons text-primary-DEFAULT mr-2 text-base">person</span>
                    Recipient Information
                </h2>
                
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 text-xs">
                    <div>
                        <p class="text-gray-500 mb-1">Name</p>
                        <p class="font-medium">{{ $certificate->participant->name ?? 'Unknown' }}</p>
                    </div>
                    
                    <div>
                        <p class="text-gray-500 mb-1">Email</p>
                        <p class="font-medium">{{ $certificate->participant->email ?? 'N/A' }}</p>
                    </div>
                    
                    <div>
                        <p class="text-gray-500 mb-1">Phone</p>
                        <p class="font-medium">{{ $certificate->participant->phone ?? 'N/A' }}</p>
                    </div>

                    <div>
                        <p class="text-gray-500 mb-1">Identity Card No.</p>
                        <p class="font-medium">{{ $certificate->participant->identity_card ?? 'N/A' }}</p>
                    </div>
                    
                    <div>
                        <p class="text-gray-500 mb-1">Passport No.</p>
                        <p class="font-medium">{{ $certificate->participant->passport_no ?? 'N/A' }}</p>
                    </div>

                    <div>
                        <p class="text-gray-500 mb-1">Gender</p>
                        <p class="font-medium">{{ $certificate->participant->gender ? ucfirst($certificate->participant->gender) : 'N/A' }}</p>
                    </div>

                    <div>
                        <p class="text-gray-500 mb-1">Date of Birth</p>
                        <p class="font-medium">{{ $certificate->participant->date_of_birth ? $certificate->participant->date_of_birth->format('d M Y') : 'N/A' }}</p>
                    </div>

                    <div>
                        <p class="text-gray-500 mb-1">Job Title</p>
                        <p class="font-medium">{{ $certificate->participant->job_title ?? 'N/A' }}</p>
                    </div>

                    @if($certificate->participant && $certificate->participant->organization)
                    <div>
                        <p class="text-gray-500 mb-1">Organization</p>
                        <p class="font-medium">{{ $certificate->participant->organization }}</p>
                    </div>
                    @endif

                    <div class="col-span-full">
                        <p class="text-gray-500 mb-1">Address</p>
                        <p class="font-medium">
                            @if($certificate->participant)
                                @if($certificate->participant->address1)
                                    {{ $certificate->participant->address1 }}<br>
                                    @if($certificate->participant->address2)
                                        {{ $certificate->participant->address2 }}<br>
                                    @endif
                                    @if($certificate->participant->city)
                                        {{ $certificate->participant->city }},
                                    @endif
                                    @if($certificate->participant->state)
                                        {{ $certificate->participant->state }}
                                    @endif
                                    @if($certificate->participant->postcode)
                                        {{ $certificate->participant->postcode }}
                                    @endif
                                    <br>
                                    {{ $certificate->participant->country ?? 'Malaysia' }}
                                @else
                                    N/A
                                @endif
                            @else
                                N/A
                            @endif
                        </p>
                    </div>

                    <div>
                        <p class="text-gray-500 mb-1">Participant ID</p>
                        <p class="font-medium">{{ $certificate->participant->id ?? 'N/A' }}</p>
                    </div>
                </div>
            </div>
            
            <!-- Event Information -->
            <div class="mb-6 border border-gray-200 rounded-md p-4">
                <h2 class="text-sm font-semibold text-gray-700 mb-4 flex items-center">
                    <span class="material-icons text-primary-DEFAULT mr-2 text-base">event</span>
                    Event Information
                </h2>
                
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 text-xs">
                    <div>
                        <p class="text-gray-500 mb-1">Event Name</p>
                        <p class="font-medium">{{ $certificate->event->name ?? 'Unknown' }}</p>
                    </div>
                    
                    <div>
                        <p class="text-gray-500 mb-1">Event Date</p>
                        <p class="font-medium">
                            @if($certificate->event)
                                {{ $certificate->event->start_date ? \Carbon\Carbon::parse($certificate->event->start_date)->format('d M Y') : 'N/A' }}
                                @if($certificate->event->end_date && $certificate->event->end_date != $certificate->event->start_date)
                                    - {{ \Carbon\Carbon::parse($certificate->event->end_date)->format('d M Y') }}
                                @endif
                            @else
                                N/A
                            @endif
                        </p>
                    </div>
                    
                    <div>
                        <p class="text-gray-500 mb-1">Event ID</p>
                        <p class="font-medium">{{ $certificate->event->id ?? 'N/A' }}</p>
                    </div>
                    
                    <div>
                        <p class="text-gray-500 mb-1">Location</p>
                        <p class="font-medium">{{ $certificate->event->location ?? 'N/A' }}</p>
                    </div>
                    
                    <div>
                        <p class="text-gray-500 mb-1">Organizer</p>
                        <p class="font-medium">{{ $certificate->event->user->name ?? 'SIJIL Events Team' }}</p>
                    </div>
                </div>
            </div>
            
            <!-- Delivery Information -->
            <div class="border border-gray-200 rounded-md p-4">
                <h2 class="text-sm font-semibold text-gray-700 mb-4 flex items-center">
                    <span class="material-icons text-primary-DEFAULT mr-2 text-base">send</span>
                    Delivery Information
                </h2>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-xs">
                    <div>
                        <p class="text-gray-500 mb-1">Email Delivery Status</p>
                        <p class="font-medium">
                            <span class="bg-green-100 text-green-800 px-2 py-1 rounded-full text-xs">Delivered</span>
                        </p>
                    </div>
                    
                    <div>
                        <p class="text-gray-500 mb-1">Delivery Date</p>
                        <p class="font-medium">{{ $certificate->generated_at ? $certificate->generated_at->format('d M Y H:i:s') : 'N/A' }}</p>
                    </div>
                    
                    <div>
                        <p class="text-gray-500 mb-1">Download Count</p>
                        <p class="font-medium">0 times</p>
                    </div>
                    
                    <div>
                        <p class="text-gray-500 mb-1">Last Accessed</p>
                        <p class="font-medium">Not accessed yet</p>
                    </div>
                </div>
                
                <!-- Actions -->
                <div class="mt-4 flex space-x-2">
                    @can('certificate_reports.delete')
                    <form method="POST" action="{{ route('reports.certificates.delete', ['id' => $certificate->certificate_number]) }}" onsubmit="return confirm('Are you sure you want to delete this certificate?')" class="inline-block">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-md text-xs flex items-center">
                            <span class="material-icons text-xs mr-1">delete</span>
                            Delete Certificate
                        </button>
                    </form>
                    @endcan
                </div>
            </div>
        </div>
    </div>

    <!-- JavaScript for the page -->
    <script>
        function printCertificate() {
            // Logic to print certificate
            window.print();
        }
    </script>
</x-app-layout> 