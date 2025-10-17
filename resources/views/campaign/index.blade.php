<x-app-layout>
    <x-slot name="breadcrumb">
        <span>Campaign</span>
    </x-slot>

    <x-slot name="title">Campaign</x-slot>

    <div class="bg-white rounded shadow-md border border-gray-300">
        <div class="p-6 border-b border-gray-200">
            <div class="flex justify-between items-start">
                <div>
                    <div class="flex items-center">
                        <span class="material-icons mr-2 text-primary-DEFAULT">campaign</span>
                        <h1 class="text-xl font-bold text-gray-800">Campaign</h1>
                    </div>
                    <p class="text-xs text-gray-500 mt-1 ml-8">Manage your marketing campaigns</p>
                </div>
                <div>
                    @can('campaigns.create')
                    <a href="{{ route('campaign.create') }}" class="bg-gradient-to-r from-blue-600 to-blue-500 hover:from-blue-700 hover:to-blue-600 text-white px-3 h-[36px] rounded shadow-sm font-medium flex items-center text-xs transition-colors duration-200 ease-in-out ml-2">
                        <span class="material-icons text-xs mr-1">add_circle</span>
                        Create New Campaign
                    </a>
                    @endcan
                </div>
            </div>
        </div>
        
        <div class="p-4">
            
            <!-- Campaign Summary -->
            <div class="mb-4 grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="bg-blue-50 rounded-md p-4 border border-blue-100">
                    <p class="text-xs text-blue-700 font-medium">Total Campaigns</p>
                    <p class="text-2xl font-bold text-blue-800">{{ $totalCampaigns }}</p>
                </div>
                
                <div class="bg-green-50 rounded-md p-4 border border-green-100">
                    <p class="text-xs text-green-700 font-medium">Active Campaigns</p>
                    <p class="text-2xl font-bold text-green-800">{{ $activeCampaigns }}</p>
                </div>
                
                <div class="bg-amber-50 rounded-md p-4 border border-amber-100">
                    <p class="text-xs text-amber-700 font-medium">Average Open Rate</p>
                    <p class="text-2xl font-bold text-amber-800">{{ $averageOpenRate }}%</p>
                </div>
            </div>
            
            <!-- Show Entries & Filter Row -->
            <div class="mb-4">
                <form method="GET" action="{{ route('campaign.index') }}" class="flex flex-wrap gap-2 items-center justify-between">
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
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Search campaign name, description..." class="border border-gray-300 rounded px-2 py-1 text-xs focus:ring focus:ring-primary-light focus:border-primary-light" id="searchInput" />
                        <select name="campaign_type" onchange="this.form.submit()" class="appearance-none px-3 py-1.5 pr-8 text-xs border border-gray-300 rounded focus:ring focus:ring-primary-light focus:border-primary-light bg-white bg-no-repeat bg-right w-[140px]" style="background-image: url('data:image/svg+xml;charset=US-ASCII,<svg xmlns=%22http://www.w3.org/2000/svg%22 width=%2216%22 height=%2216%22 viewBox=%220 0 24 24%22 fill=%22none%22 stroke=%22%23888%22 stroke-width=%222%22 stroke-linecap=%22round%22 stroke-linejoin=%22round%22><polyline points=%226 9 12 15 18 9%22></polyline></svg>'); background-position: right 0.75rem center; background-size: 1em;">
                            <option value="">All Types</option>
                            <option value="email" @if(request('campaign_type') == 'email') selected @endif>Email Campaign</option>
                            <option value="sms" @if(request('campaign_type') == 'sms') selected @endif>SMS Campaign</option>
                            <option value="whatsapp" @if(request('campaign_type') == 'whatsapp') selected @endif>WhatsApp Campaign</option>
                        </select>
                        <select name="status" onchange="this.form.submit()" class="appearance-none px-3 py-1.5 pr-8 text-xs border border-gray-300 rounded focus:ring focus:ring-primary-light focus:border-primary-light bg-white bg-no-repeat bg-right w-[120px]" style="background-image: url('data:image/svg+xml;charset=US-ASCII,<svg xmlns=%22http://www.w3.org/2000/svg%22 width=%2216%22 height=%2216%22 viewBox=%220 0 24 24%22 fill=%22none%22 stroke=%22%23888%22 stroke-width=%222%22 stroke-linecap=%22round%22 stroke-linejoin=%22round%22><polyline points=%226 9 12 15 18 9%22></polyline></svg>'); background-position: right 0.75rem center; background-size: 1em;">
                            <option value="">All Status</option>
                            <option value="draft" @if(request('status') == 'draft') selected @endif>Draft</option>
                            <option value="scheduled" @if(request('status') == 'scheduled') selected @endif>Scheduled</option>
                            <option value="running" @if(request('status') == 'running') selected @endif>Running</option>
                            <option value="completed" @if(request('status') == 'completed') selected @endif>Completed</option>
                        </select>
                        <button type="submit" class="bg-primary-light text-white px-3 py-1 h-[36px] rounded text-xs font-medium flex items-center justify-center" title="Search">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4-4m0 0A7 7 0 104 4a7 7 0 0013 13z" />
                            </svg>
                        </button>
                        @if(request('search') || request('campaign_type') || request('status'))
                            <a href="{{ route('campaign.index') }}?per_page={{ request('per_page', 10) }}" class="text-xs text-gray-500 underline ml-2">Reset</a>
                        @endif
                    </div>
                </form>
            </div>
            
            <!-- Search Results Summary -->
            @if(request('search') || request('campaign_type') || request('status'))
                <div class="bg-blue-50 border border-blue-200 text-blue-700 px-4 py-2 rounded mb-4 text-xs">
                    <span class="font-medium">Search Results:</span>
                    @if(request('search'))
                        <span class="ml-2">Searching for "{{ request('search') }}"</span>
                    @endif
                    @if(request('campaign_type'))
                        <span class="ml-2">Type: {{ ucfirst(request('campaign_type')) }}</span>
                    @endif
                    @if(request('status'))
                        <span class="ml-2">Status: {{ ucfirst(request('status')) }}</span>
                    @endif
                    <span class="ml-2">({{ $campaigns->total() }} results)</span>
                </div>
            @endif
            
            <!-- Campaign Table -->
            <div class="overflow-visible border border-gray-200 rounded">
                <table class="min-w-full border-collapse">
                    <thead>
                        <tr class="bg-primary-light text-white text-xs uppercase">
                            <th class="py-3 px-4 text-left rounded-tl">Campaign Name</th>
                            <th class="py-3 px-4 text-left">Type</th>
                            <th class="py-3 px-4 text-left">Created Date</th>
                            <th class="py-3 px-4 text-left">Status</th>
                            <th class="py-3 px-4 text-left">Recipients</th>
                            <th class="py-3 px-4 text-left">Open Rate</th>
                            <th class="py-3 px-4 text-center rounded-tr">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($campaigns as $campaign)
                        <tr class="text-xs hover:bg-gray-50">
                            <td class="py-3 px-4 font-medium">{{ $campaign->name }}</td>
                            <td class="py-3 px-4">{{ ucfirst($campaign->campaign_type) }}</td>
                            <td class="py-3 px-4">{{ $campaign->created_at->format('d M Y') }}</td>
                            <td class="py-3 px-4">
                                @if($campaign->status == 'completed')
                                <span class="px-2 py-1 bg-green-100 text-green-800 rounded-full text-xs">Completed</span>
                                @elseif($campaign->status == 'running')
                                <span class="px-2 py-1 bg-blue-100 text-blue-800 rounded-full text-xs">Running</span>
                                @elseif($campaign->status == 'scheduled')
                                <span class="px-2 py-1 bg-purple-100 text-purple-800 rounded-full text-xs">Scheduled</span>
                                @else
                                <span class="px-2 py-1 bg-yellow-100 text-yellow-800 rounded-full text-xs">Draft</span>
                                @endif
                            </td>
                            <td class="py-3 px-4">{{ $campaign->recipients_count > 0 ? $campaign->recipients_count : '--' }}</td>
                            <td class="py-3 px-4">
                                @if($campaign->delivered_count > 0)
                                <div class="flex items-center">
                                    <div class="relative max-w-[120px] w-full bg-gray-200 rounded-full h-4 mr-3">
                                        <div class="bg-blue-600 h-4 rounded-full" style="width: {{ min($campaign->open_rate, 100) }}%"></div>
                                        <span class="absolute left-0 right-0 top-0 bottom-0 flex items-center justify-center font-bold text-white text-xs" style="text-shadow: 0 1px 2px #000;">
                                            {{ $campaign->open_rate }}%
                                        </span>
                                    </div>
                                </div>
                                @else
                                --
                                @endif
                            </td>
                            <td class="py-3 px-4">
                                <div class="flex justify-center space-x-2">
                                    <a href="{{ route('campaign.show', ['campaign' => $campaign->id]) }}" class="p-1 bg-blue-50 rounded hover:bg-blue-100 border border-blue-100" title="View Details">
                                        <span class="material-icons text-primary-DEFAULT text-xs">visibility</span>
                                    </a>
                                    @can('campaigns.update')
                                    <a href="{{ route('campaign.edit', ['campaign' => $campaign->id]) }}" class="p-1 bg-yellow-50 rounded hover:bg-yellow-100 border border-yellow-100" title="Edit">
                                        <span class="material-icons text-yellow-600 text-xs">edit</span>
                                    </a>
                                    @endcan
                                    @can('campaigns.delete')
                                    <form action="{{ route('campaign.destroy', ['campaign' => $campaign->id]) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this campaign?')" class="inline">
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
                            <td colspan="7" class="py-4 px-4 text-center text-gray-500">No campaigns found</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination -->
            <div class="mt-6 flex flex-col sm:flex-row sm:items-center sm:justify-between">
                <div class="mb-2 sm:mb-0 text-xs text-gray-500">
                    @if($campaigns->total() > 0)
                        Showing <span class="font-medium">{{ $campaigns->firstItem() }}</span> to <span class="font-medium">{{ $campaigns->lastItem() }}</span> of <span class="font-medium">{{ $campaigns->total() }}</span> entries ({{ request('per_page', 10) }} per page)
                    @else
                        Showing <span class="font-medium">0</span> to <span class="font-medium">0</span> of <span class="font-medium">0</span> entries
                    @endif
                </div>
                <div class="flex justify-end">
                    {{ $campaigns->appends(request()->query())->links('components.pagination-modern') }}
                </div>
            </div>
        </div>
    </div>

    <!-- JavaScript for search debounce -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const searchInput = document.getElementById('searchInput');
            let searchTimeout;

            searchInput.addEventListener('input', function() {
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(() => {
                    this.form.submit();
                }, 500);
            });
        });
    </script>
</x-app-layout> 