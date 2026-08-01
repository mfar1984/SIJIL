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
                        <span class="material-icons-outlined mr-2 text-primary-DEFAULT">campaign</span>
                        <h1 class="text-xl font-bold text-gray-800">Campaign</h1>
                    </div>
                    <p class="text-xs text-gray-500 mt-1 ml-8">Manage your marketing campaigns</p>
                </div>
                <div>
                    @can('campaigns.create')
                    <a href="{{ route('campaign.create') }}" class="bg-gradient-to-r from-blue-600 to-blue-500 hover:from-blue-700 hover:to-blue-600 text-white px-3 h-[36px] rounded shadow-sm font-medium flex items-center text-xs transition-colors duration-200 ease-in-out ml-2">
                        <span class="material-icons-outlined text-xs mr-1">add_circle</span>
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
            
            {{-- Search takes the remaining space; the filters keep their own width. --}}
            <form method="GET" action="{{ route('campaign.index') }}" class="flex flex-wrap items-center gap-2 mb-4">
                <input type="text" name="search" id="searchInput" value="{{ request('search') }}"
                       placeholder="Search campaign name, description..."
                       class="flex-1 min-w-[12rem] h-9 text-xs border-gray-300 rounded px-3 focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50">

                <select name="campaign_type" onchange="this.form.submit()"
                        class="h-9 text-xs border-gray-300 rounded pl-3 pr-8 w-[12rem] shrink-0 focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50">
                    <option value="">All Types</option>
                    <option value="email" @if(request('campaign_type') == 'email') selected @endif>Email Campaign</option>
                    <option value="sms" @if(request('campaign_type') == 'sms') selected @endif>SMS Campaign</option>
                    {{-- WhatsApp was offered here and nowhere else. No form could create
                         one and the sender had no branch for it, so the filter could only
                         ever return nothing. --}}
                </select>

                <select name="status" onchange="this.form.submit()"
                        class="h-9 text-xs border-gray-300 rounded pl-3 pr-8 w-[9rem] shrink-0 focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50">
                    <option value="">All Status</option>
                    <option value="draft" @if(request('status') == 'draft') selected @endif>Draft</option>
                    <option value="scheduled" @if(request('status') == 'scheduled') selected @endif>Scheduled</option>
                    <option value="running" @if(request('status') == 'running') selected @endif>Running</option>
                    <option value="completed" @if(request('status') == 'completed') selected @endif>Completed</option>
                </select>

                <button type="submit"
                        class="h-9 px-3 bg-primary-DEFAULT hover:bg-primary-dark text-white rounded text-xs flex items-center shrink-0 transition-colors duration-200 ease-in-out" title="Search">
                    <span class="material-icons-outlined text-xs">search</span>
                </button>

                @if(request('search') || request('campaign_type') || request('status'))
                    <a href="{{ route('campaign.index') }}" class="text-xs text-gray-500 underline shrink-0">Reset</a>
                @endif
            </form>
            
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
                                        <span class="material-icons-outlined text-primary-DEFAULT text-xs">visibility</span>
                                    </a>
                                    @can('campaigns.update')
                                    <a href="{{ route('campaign.edit', ['campaign' => $campaign->id]) }}" class="p-1 bg-yellow-50 rounded hover:bg-yellow-100 border border-yellow-100" title="Edit">
                                        <span class="material-icons-outlined text-yellow-600 text-xs">edit</span>
                                    </a>
                                    @endcan
                                    @can('campaigns.delete')
                                    <form action="{{ route('campaign.destroy', ['campaign' => $campaign->id]) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this campaign?')" class="inline">
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
                        Showing <span class="font-medium">{{ $campaigns->firstItem() }}</span> to <span class="font-medium">{{ $campaigns->lastItem() }}</span> of <span class="font-medium">{{ $campaigns->total() }}</span> entries
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