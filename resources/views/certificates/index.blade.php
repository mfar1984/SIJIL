<x-app-layout>
    <x-slot name="breadcrumb">
        <span>Certificate</span>
        <span class="mx-2 text-gray-500">/</span>
        <span>Manage Certificates</span>
    </x-slot>

    <x-slot name="title">Manage Certificates</x-slot>

    <div class="bg-white rounded shadow-md border border-gray-300">
        <div class="p-6 border-b border-gray-200">
            <div class="flex justify-between items-start">
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
        </div>
        <div class="p-4">
            <!-- Search & Filter Row -->
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-end mb-4 gap-2">
                <form method="GET" action="{{ route('certificates.index') }}" class="flex flex-wrap gap-2 items-center justify-end w-full sm:w-auto">
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Search certificate #, participant..." class="border border-gray-300 rounded px-2 py-1 text-xs focus:ring focus:ring-primary-light focus:border-primary-light" />
                    <select name="event_id" class="appearance-none px-3 py-1.5 pr-8 text-xs border border-gray-300 rounded focus:ring focus:ring-primary-light focus:border-primary-light bg-white bg-no-repeat bg-right max-w-[200px] truncate" style="background-image: url('data:image/svg+xml;charset=US-ASCII,<svg xmlns=%22http://www.w3.org/2000/svg%22 width=%2216%22 height=%2216%22 viewBox=%220 0 24 24%22 fill=%22none%22 stroke=%22%23888%22 stroke-width=%222%22 stroke-linecap=%22round%22 stroke-linejoin=%22round%22><polyline points=%226 9 12 15 18 9%22></polyline></svg>'); background-position: right 0.75rem center; background-size: 1em;">
                        <option value="">All Events</option>
                        @foreach($events as $event)
                            <option value="{{ $event->id }}" {{ request('event_id') == $event->id ? 'selected' : '' }} class="truncate">{{ $event->name }}</option>
                        @endforeach
                    </select>
                    <select name="template_id" class="appearance-none px-3 py-1.5 pr-8 text-xs border border-gray-300 rounded focus:ring focus:ring-primary-light focus:border-primary-light bg-white bg-no-repeat bg-right max-w-[200px] truncate" style="background-image: url('data:image/svg+xml;charset=US-ASCII,<svg xmlns=%22http://www.w3.org/2000/svg%22 width=%2216%22 height=%2216%22 viewBox=%220 0 24 24%22 fill=%22none%22 stroke=%22%23888%22 stroke-width=%222%22 stroke-linecap=%22round%22 stroke-linejoin=%22round%22><polyline points=%226 9 12 15 18 9%22></polyline></svg>'); background-position: right 0.75rem center; background-size: 1em;">
                        <option value="">All Templates</option>
                        @foreach($templates as $template)
                            <option value="{{ $template->id }}" {{ request('template_id') == $template->id ? 'selected' : '' }} class="truncate">{{ $template->name }}</option>
                        @endforeach
                    </select>
                    <button type="submit" class="bg-primary-light text-white px-3 py-1 h-[38px] rounded text-xs font-medium flex items-center justify-center" title="Search">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4-4m0 0A7 7 0 104 4a7 7 0 0013 13z" />
                        </svg>
                    </button>
                    @if(request('search') || request('event_id') || request('template_id'))
                        <a href="{{ route('certificates.index') }}" class="text-xs text-gray-500 underline ml-2">Reset</a>
                    @endif
                </form>
            </div>

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

            <!-- Certificates Table -->
            <div class="overflow-visible border border-gray-200 rounded">
                <table class="min-w-full border-collapse">
                    <thead>
                        <tr class="bg-primary-light text-white text-xs uppercase">
                            <th class="py-3 px-4 text-left rounded-tl">Certificate #</th>
                            <th class="py-3 px-4 text-left">Event</th>
                            <th class="py-3 px-4 text-left">Participant</th>
                            <th class="py-3 px-4 text-left">Generated</th>
                            <th class="py-3 px-4 text-center rounded-tr">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($certificates ?? [] as $certificate)
                            <tr class="text-xs hover:bg-gray-50">
                                <td class="py-3 px-4 font-medium">{{ $certificate->certificate_number }}</td>
                                <td class="py-3 px-4" style="max-width: 200px; overflow-wrap: break-word; word-wrap: break-word; hyphens: auto;">{{ $certificate->event->name }}</td>
                                <td class="py-3 px-4">{{ $certificate->participant->name }}</td>
                                <td class="py-3 px-4">{{ $certificate->generated_at->format('d M Y, H:i') }}</td>
                                <td class="py-3 px-4">
                                    <div class="flex justify-center space-x-2">
                                        <a href="{{ asset('storage/' . $certificate->pdf_file) }}" target="_blank" class="p-1 bg-blue-50 rounded hover:bg-blue-100 border border-blue-100" title="View">
                                            <span class="material-icons text-primary-DEFAULT text-xs">visibility</span>
                                        </a>
                                        <a href="{{ asset('storage/' . $certificate->pdf_file) }}" download class="p-1 bg-green-50 rounded hover:bg-green-100 border border-green-100" title="Download">
                                            <span class="material-icons text-green-600 text-xs">download</span>
                                        </a>
                                        <form method="POST" action="{{ route('certificates.destroy', $certificate->id) }}" onsubmit="return confirm('Are you sure you want to delete this certificate?')" class="inline-block">
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
                                <td colspan="5" class="py-8 text-center text-gray-500">
                                    No certificates found. <a href="{{ route('certificates.create') }}" class="text-primary-DEFAULT hover:underline">Generate certificates</a>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination Row -->
            @if(isset($certificates) && $certificates->hasPages())
                <div class="mt-6 flex flex-col sm:flex-row sm:items-center sm:justify-between">
                    <div class="mb-2 sm:mb-0 text-xs text-gray-500">
                        Showing {{ $certificates->firstItem() }} to {{ $certificates->lastItem() }} of {{ $certificates->total() }} results
                    </div>
                    <div class="flex justify-end">
                        {{ $certificates->links('components.pagination-modern') }}
                    </div>
                </div>
            @endif
        </div>
    </div>
</x-app-layout> 