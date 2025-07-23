<x-app-layout>
    <x-slot name="breadcrumb">
        <span>Certificate</span>
        <span class="mx-2 text-gray-500">/</span>
        <span>Manage Certificates</span>
    </x-slot>

    <x-slot name="title">Manage Certificates</x-slot>

    <div class="bg-white rounded shadow-md border border-gray-300">
        <div class="p-6 border-b border-gray-200 flex justify-between items-start">
            <div>
                <div class="flex items-center">
                    <span class="material-icons mr-2 text-primary-DEFAULT">workspace_premium</span>
                    <h1 class="text-xl font-bold text-gray-800">Manage Certificates</h1>
                </div>
                <p class="text-xs text-gray-500 mt-1 ml-8">View and generate certificates for participants</p>
            </div>
            <a href="{{ route('certificates.create') }}" class="bg-gradient-to-r from-blue-600 to-blue-500 hover:from-blue-700 hover:to-blue-600 text-white px-3 py-1 rounded shadow-sm font-medium flex items-center text-xs transition-colors duration-200 ease-in-out">
                <span class="material-icons text-xs mr-1">add_circle</span>
                Generate Certificates
            </a>
        </div>
        <div class="p-4">
            <!-- Success Message -->
            @if(session('success'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                    {{ session('success') }}
                </div>
            @endif

            <!-- Error Message -->
            @if(session('error'))
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                    {{ session('error') }}
                </div>
            @endif

            <!-- Filter Form -->
            <form action="{{ route('certificates.index') }}" method="GET" class="mb-4 bg-gray-50 p-4 rounded border border-gray-200">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label for="event_id" class="block text-xs font-medium text-gray-700 mb-1">Event</label>
                        <select id="event_id" name="event_id" class="w-full text-xs border-gray-300 rounded focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50">
                            <option value="">All Events</option>
                            @foreach($events as $event)
                                <option value="{{ $event->id }}" {{ request('event_id') == $event->id ? 'selected' : '' }}>{{ $event->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="template_id" class="block text-xs font-medium text-gray-700 mb-1">Template</label>
                        <select id="template_id" name="template_id" class="w-full text-xs border-gray-300 rounded focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50">
                            <option value="">All Templates</option>
                            @foreach($templates as $template)
                                <option value="{{ $template->id }}" {{ request('template_id') == $template->id ? 'selected' : '' }}>{{ $template->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="flex items-end">
                        <button type="submit" class="bg-primary-light text-white px-3 py-1 h-[38px] rounded text-xs font-medium flex items-center justify-center" title="Filter">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                            </svg>
                            Filter
                        </button>
                        <a href="{{ route('certificates.index') }}" class="ml-2 text-xs text-gray-500 underline">Reset</a>
                    </div>
                </div>
            </form>

            <!-- Certificates Table -->
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Certificate #</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Event</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Participant</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Template</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Generated</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($certificates ?? [] as $certificate)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $certificate->certificate_number }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $certificate->event->name }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $certificate->participant->name }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $certificate->template->name }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $certificate->generated_at->format('d M Y, H:i') }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <div class="flex space-x-2">
                                        <a href="{{ asset('storage/' . $certificate->pdf_file) }}" target="_blank" class="text-blue-600 hover:text-blue-900">
                                            <span class="material-icons text-base">visibility</span>
                                        </a>
                                        <a href="{{ asset('storage/' . $certificate->pdf_file) }}" download class="text-green-600 hover:text-green-900">
                                            <span class="material-icons text-base">download</span>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">
                                    No certificates found. <a href="{{ route('certificates.create') }}" class="text-primary-DEFAULT hover:underline">Generate certificates</a>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            @if(isset($certificates) && $certificates->hasPages())
                <div class="mt-4">
                    {{ $certificates->links() }}
                </div>
            @endif
        </div>
    </div>
</x-app-layout> 