<x-app-layout>
    <x-slot name="breadcrumb">
        <span>Campaign</span>
        <span class="mx-2 text-gray-500">/</span>
        <span>View Campaign</span>
    </x-slot>

    <x-slot name="title">Campaign Details</x-slot>

    <div class="bg-white rounded shadow-md border border-gray-300">
        <div class="p-6 border-b border-gray-200 flex justify-between items-start">
            <div>
                <div class="flex items-center">
                    <span class="material-icons mr-2 text-primary-DEFAULT">campaign</span>
                    <h1 class="text-xl font-bold text-gray-800">Campaign Details</h1>
                </div>
                <p class="text-xs text-gray-500 mt-1 ml-8">View detailed information about this campaign</p>
            </div>
            <div class="flex space-x-3">
                <a href="{{ route('campaign.edit', ['campaign' => $campaign->id]) }}" class="p-1 bg-yellow-50 rounded hover:bg-yellow-100 border border-yellow-100" title="Edit">
                    <span class="material-icons text-yellow-600 text-xs">edit</span>
                </a>
                <form method="POST" action="{{ route('campaign.destroy', ['campaign' => $campaign->id]) }}" onsubmit="return confirm('Are you sure you want to delete this campaign?')" class="inline-block">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="p-1 bg-red-50 rounded hover:bg-red-100 border border-red-100" title="Delete">
                        <span class="material-icons text-red-600 text-xs">delete</span>
                    </button>
                </form>
                <a href="{{ route('campaign.index') }}" class="p-1 bg-blue-50 rounded hover:bg-blue-100 border border-blue-100" title="Back">
                    <span class="material-icons text-primary-DEFAULT text-xs">arrow_back</span>
                </a>
            </div>
        </div>
        <div class="p-6 space-y-8">
            <!-- Campaign Information -->
            <div>
                <h2 class="text-base font-semibold text-gray-700 mb-4">Campaign Information</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="flex items-center text-xs font-medium text-gray-700 mb-1"><span class="material-icons text-sm mr-1 text-primary-DEFAULT">campaign</span>Campaign Name</label>
                        <div class="w-full text-xs border border-gray-200 bg-gray-50 rounded py-2 px-3">{{ $campaign->name }}</div>
                    </div>
                    <div>
                        <label class="flex items-center text-xs font-medium text-gray-700 mb-1"><span class="material-icons text-sm mr-1 text-primary-DEFAULT">category</span>Campaign Type</label>
                        <div class="w-full text-xs border border-gray-200 bg-gray-50 rounded py-2 px-3">{{ ucfirst($campaign->campaign_type) }} Campaign</div>
                    </div>
                    <div>
                        <label class="flex items-center text-xs font-medium text-gray-700 mb-1"><span class="material-icons text-sm mr-1 text-primary-DEFAULT">description</span>Description</label>
                        <div class="w-full text-xs border border-gray-200 bg-gray-50 rounded py-2 px-3 min-h-[40px]">{{ $campaign->description ?: 'No description available.' }}</div>
                    </div>
                    <div>
                        <label class="flex items-center text-xs font-medium text-gray-700 mb-1"><span class="material-icons text-sm mr-1 text-primary-DEFAULT">toggle_on</span>Status</label>
                        <div class="w-full text-xs border border-gray-200 bg-gray-50 rounded py-2 px-3">
                            @if($campaign->status == 'completed')
                                <span class="bg-green-100 text-green-800 px-2 py-0.5 rounded-full text-xs">Completed</span>
                            @elseif($campaign->status == 'running')
                                <span class="bg-blue-100 text-blue-800 px-2 py-0.5 rounded-full text-xs">Running</span>
                            @elseif($campaign->status == 'scheduled')
                                <span class="bg-purple-100 text-purple-800 px-2 py-0.5 rounded-full text-xs">Scheduled</span>
                            @else
                                <span class="bg-yellow-100 text-yellow-800 px-2 py-0.5 rounded-full text-xs">Draft</span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            <!-- Schedule Information -->
            <div>
                <h2 class="text-base font-semibold text-gray-700 mb-4">Schedule Information</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="flex items-center text-xs font-medium text-gray-700 mb-1"><span class="material-icons text-sm mr-1 text-primary-DEFAULT">event_available</span>Start Date</label>
                        <div class="w-full text-xs border border-gray-200 bg-gray-50 rounded py-2 px-3">{{ $campaign->start_date ? $campaign->start_date->format('d M Y') : 'Not specified' }}</div>
                    </div>
                    <div>
                        <label class="flex items-center text-xs font-medium text-gray-700 mb-1"><span class="material-icons text-sm mr-1 text-primary-DEFAULT">event_busy</span>End Date</label>
                        <div class="w-full text-xs border border-gray-200 bg-gray-50 rounded py-2 px-3">{{ $campaign->end_date ? $campaign->end_date->format('d M Y') : 'Not specified' }}</div>
                    </div>
                    <div>
                        <label class="flex items-center text-xs font-medium text-gray-700 mb-1"><span class="material-icons text-sm mr-1 text-primary-DEFAULT">schedule</span>Schedule Type</label>
                        <div class="w-full text-xs border border-gray-200 bg-gray-50 rounded py-2 px-3">
                            @if($campaign->schedule_type == 'now')
                                Immediate
                            @elseif($campaign->schedule_type == 'scheduled')
                                Scheduled ({{ $campaign->scheduled_at ? $campaign->scheduled_at->format('d M Y H:i') : 'Time not set' }})
                            @else
                                Not specified
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            <!-- Audience Information -->
            <div>
                <h2 class="text-base font-semibold text-gray-700 mb-4">Audience Information</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="flex items-center text-xs font-medium text-gray-700 mb-1"><span class="material-icons text-sm mr-1 text-primary-DEFAULT">group</span>Audience Type</label>
                        <div class="w-full text-xs border border-gray-200 bg-gray-50 rounded py-2 px-3">
                            @if($campaign->audience_type == 'all_participants')
                                All Participants
                            @elseif($campaign->audience_type == 'specific_event')
                                Specific Event
                            @elseif($campaign->audience_type == 'custom_filter')
                                Custom Filter
                            @elseif($campaign->audience_type == 'custom_emails')
                                Custom Emails
                            @else
                                {{ ucfirst($campaign->audience_type) }}
                            @endif
                        </div>
                    </div>
                    <div>
                        <label class="flex items-center text-xs font-medium text-gray-700 mb-1"><span class="material-icons text-sm mr-1 text-primary-DEFAULT">people</span>Recipients Count</label>
                        <div class="w-full text-xs border border-gray-200 bg-gray-50 rounded py-2 px-3">
                            @if($campaign->recipients_count > 0)
                                {{ $campaign->recipients_count }} participants
                            @else
                                Not sent yet
                            @endif
                        </div>
                    </div>
                    @if($campaign->audience_type == 'specific_event' && $campaign->event)
                    <div>
                        <label class="flex items-center text-xs font-medium text-gray-700 mb-1"><span class="material-icons text-sm mr-1 text-primary-DEFAULT">event_note</span>Event</label>
                        <div class="w-full text-xs border border-gray-200 bg-gray-50 rounded py-2 px-3">{{ $campaign->event->name }}</div>
                    </div>
                    @endif
                    @if($campaign->audience_type == 'custom_filter' && is_array($campaign->filter_criteria))
                    <div>
                        <label class="flex items-center text-xs font-medium text-gray-700 mb-1"><span class="material-icons text-sm mr-1 text-primary-DEFAULT">filter_list</span>Filter Criteria</label>
                        <div class="w-full text-xs border border-gray-200 bg-gray-50 rounded py-2 px-3">
                            <ul class="list-disc pl-4">
                                @if(isset($campaign->filter_criteria['age']) && $campaign->filter_criteria['age'])
                                    <li>Age: {{ $campaign->filter_criteria['age'] }}</li>
                                @endif
                                @if(isset($campaign->filter_criteria['gender']) && $campaign->filter_criteria['gender'])
                                    <li>Gender: {{ ucfirst($campaign->filter_criteria['gender']) }}</li>
                                @endif
                                @if(isset($campaign->filter_criteria['attendance']) && $campaign->filter_criteria['attendance'])
                                    <li>Attendance Status: {{ ucfirst(str_replace('_', ' ', $campaign->filter_criteria['attendance'])) }}</li>
                                @endif
                            </ul>
                        </div>
                    </div>
                    @endif
                    @if($campaign->audience_type == 'custom_emails' && isset($campaign->filter_criteria['custom_emails']))
                    <div>
                        <label class="flex items-center text-xs font-medium text-gray-700 mb-1"><span class="material-icons text-sm mr-1 text-primary-DEFAULT">email</span>Custom Email Addresses</label>
                        <div class="w-full text-xs border border-gray-200 bg-gray-50 rounded py-2 px-3">
                            <ul class="list-disc pl-4">
                                @foreach($campaign->filter_criteria['custom_emails'] as $email)
                                    <li>{{ $email }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
            <!-- Content Information -->
            <div>
                <h2 class="text-base font-semibold text-gray-700 mb-4">Content Information</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="flex items-center text-xs font-medium text-gray-700 mb-1"><span class="material-icons text-sm mr-1 text-primary-DEFAULT">subject</span>Email Subject</label>
                        <div class="w-full text-xs border border-gray-200 bg-gray-50 rounded py-2 px-3">{{ $campaign->content['subject'] ?? 'No subject' }}</div>
                    </div>
                    <div class="md:col-span-2">
                        <label class="flex items-center text-xs font-medium text-gray-700 mb-1"><span class="material-icons text-sm mr-1 text-primary-DEFAULT">description</span>Email Content Preview</label>
                        <div class="w-full text-xs border border-gray-200 bg-gray-50 rounded py-2 px-3">
                            <div style="background: #fff; padding: 20px; border-radius: 8px; font-family: Arial, sans-serif; font-size: 14px; color: #333; line-height: 1.6; box-shadow: 0 2px 4px rgba(0,0,0,0.05); max-width: 800px; margin: 0 auto;">
                                <div style="border-bottom: 1px solid #eee; padding-bottom: 15px; margin-bottom: 15px;">
                                    <div style="font-weight: bold; font-size: 16px; color: #333; margin-bottom: 5px;">{{ $campaign->content['subject'] ?? 'No subject' }}</div>
                                    <div style="font-size: 12px; color: #666;">From: SIJIL System &lt;no-reply@sijil.com&gt;</div>
                                    <div style="font-size: 12px; color: #666;">To: {{ 'Contoh Nama' }} &lt;{{ 'contoh@email.com' }}&gt;</div>
                                </div>
                                <div class="email-body">
                                    {!! \App\Helpers\EmailHelper::personalizeContent($campaign->content['body'] ?? '', ['name' => 'Contoh Nama', 'email' => 'contoh@email.com']) !!}
                                </div>
                                @if(isset($campaign->content['include_unsubscribe']) && $campaign->content['include_unsubscribe'])
                                <div style="margin-top: 20px; padding-top: 15px; border-top: 1px solid #eee; font-size: 12px; color: #999; text-align: center;">
                                    <p>If you no longer wish to receive these emails, you can <a href="#" style="color: #999; text-decoration: underline;">unsubscribe</a>.</p>
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Analytics -->
            @if($campaign->recipients_count > 0)
            <div>
                <h2 class="text-base font-semibold text-gray-700 mb-4">Analytics</h2>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div class="bg-blue-50 rounded-md p-4 border border-blue-100">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-xs text-blue-700 font-medium">Sent</p>
                                <p class="text-2xl font-bold text-blue-800">{{ $campaign->recipients_count }}</p>
                            </div>
                            <span class="material-icons text-blue-500 text-2xl">send</span>
                        </div>
                    </div>
                    <div class="bg-green-50 rounded-md p-4 border border-green-100">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-xs text-green-700 font-medium">Delivered</p>
                                <p class="text-2xl font-bold text-green-800">{{ $campaign->delivered_count }}</p>
                            </div>
                            <span class="material-icons text-green-500 text-2xl">mark_email_read</span>
                        </div>
                    </div>
                    <div class="bg-amber-50 rounded-md p-4 border border-amber-100">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-xs text-amber-700 font-medium">
                                    {{ $campaign->campaign_type == 'email' ? 'Opened' : 'Read' }}
                                </p>
                                <p class="text-2xl font-bold text-amber-800">
                                    {{ $campaign->opened_count }}
                                    @if($campaign->delivered_count > 0)
                                        <span class="text-sm">({{ round(($campaign->opened_count / $campaign->delivered_count) * 100) }}%)</span>
                                    @endif
                                </p>
                            </div>
                            <span class="material-icons text-amber-500 text-2xl">visibility</span>
                        </div>
                    </div>
                </div>
                @if($campaign->campaign_type == 'email' && $campaign->clicked_count > 0)
                <div class="mt-4">
                    <h3 class="text-xs font-medium text-gray-700 mb-2">Link Click Analytics</h3>
                    <div class="border border-gray-200 rounded overflow-hidden">
                        <table class="min-w-full border-collapse">
                            <thead>
                                <tr class="bg-gray-50 text-xs uppercase">
                                    <th class="py-2 px-4 text-left">Total Clicks</th>
                                    <th class="py-2 px-4 text-left">Click Rate</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr class="text-xs">
                                    <td class="py-2 px-4">{{ $campaign->clicked_count }}</td>
                                    <td class="py-2 px-4">
                                        @if($campaign->opened_count > 0)
                                            {{ round(($campaign->clicked_count / $campaign->opened_count) * 100, 1) }}%
                                        @else
                                            0%
                                        @endif
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                @endif
            </div>
            @endif
        </div>
    </div>
</x-app-layout> 