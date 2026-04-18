<x-app-layout>
    <x-slot name="breadcrumb">
        <span>Survey</span>
        <span class="mx-2 text-gray-500">/</span>
        <span>View Survey</span>
    </x-slot>

    <x-slot name="title">Survey Details</x-slot>

    <style>
        /* Tooltip styles */
        .tooltip-wrapper {
            position: relative;
            display: inline-flex;
        }
        
        .tooltip-content {
            position: absolute;
            bottom: 100%;
            left: 50%;
            transform: translateX(-50%) translateY(-4px);
            background-color: #1f2937;
            color: white;
            padding: 6px 10px;
            border-radius: 6px;
            font-size: 11px;
            white-space: nowrap;
            z-index: 1000;
            pointer-events: none;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        }
        
        .tooltip-content::after {
            content: '';
            position: absolute;
            top: 100%;
            left: 50%;
            transform: translateX(-50%);
            border: 4px solid transparent;
            border-top-color: #1f2937;
        }
    </style>

    <div class="bg-white rounded shadow-md border border-gray-300">
        <div class="p-6 border-b border-gray-200">
            <div class="flex items-center justify-between">
                <div class="flex items-center">
                    <span class="material-icons-outlined mr-2 text-primary-DEFAULT">quiz</span>
                    <h1 class="text-xl font-bold text-gray-800">Survey Details</h1>
                </div>
                <div class="flex space-x-3">
                    @can('surveys.update')
                    <a href="{{ route('survey.edit', $survey) }}" class="bg-gradient-to-r from-yellow-500 to-yellow-400 hover:from-yellow-600 hover:to-yellow-500 text-white px-3 h-[36px] rounded shadow-sm font-medium flex items-center text-xs transition-colors duration-200 ease-in-out">
                        <span class="material-icons-outlined text-xs mr-1">edit</span>
                        Edit Survey
                    </a>
                    @endcan
                    <a href="{{ route('survey.index') }}" class="bg-gradient-to-r from-gray-500 to-gray-400 hover:from-gray-600 hover:to-gray-500 text-white px-3 h-[36px] rounded shadow-sm font-medium flex items-center text-xs transition-colors duration-200 ease-in-out">
                        <span class="material-icons-outlined text-xs mr-1">arrow_back</span>
                        Back to List
                    </a>
                </div>
            </div>
            <p class="text-xs text-gray-500 mt-1 ml-8">View detailed information about this survey</p>
        </div>
        
        <div class="p-6 space-y-2">
            <!-- Survey Status Banner -->
            <div class="rounded p-3 mb-4 flex items-center 
                @if($survey->status === 'published')
                    bg-status-active-bg border border-status-active-text/20
                @elseif($survey->status === 'draft')
                    bg-status-pending-bg border border-status-pending-text/20
                @elseif($survey->status === 'closed')
                    bg-status-completed-bg border border-status-completed-text/20
                @endif
            ">
                <span class="material-icons-outlined mr-2 
                    @if($survey->status === 'published')
                        text-status-active-text
                    @elseif($survey->status === 'draft')
                        text-status-pending-text
                    @elseif($survey->status === 'closed')
                        text-status-completed-text
                    @endif
                ">
                    @if($survey->status === 'published')
                        check_circle
                    @elseif($survey->status === 'draft')
                        edit
                    @elseif($survey->status === 'closed')
                        cancel
                    @endif
                </span>
                <div>
                    <p class="font-medium
                        @if($survey->status === 'published')
                            text-status-active-text
                        @elseif($survey->status === 'draft')
                            text-status-pending-text
                        @elseif($survey->status === 'closed')
                            text-status-completed-text
                        @endif
                    ">
                        This survey is currently <span class="font-bold">{{ ucfirst($survey->status) }}</span>
                    </p>
                    <p class="text-xs text-gray-600">
                        @if($survey->status === 'published')
                            Survey is active and collecting responses.
                        @elseif($survey->status === 'draft')
                            Survey is in draft mode and not yet available to respondents.
                        @elseif($survey->status === 'closed')
                            Survey is closed and no longer accepting responses.
                        @endif
                    </p>
                </div>
            </div>
            
            <!-- Basic Information Section -->
            <div class="bg-white border border-gray-200 rounded-md shadow-sm">
                <div class="bg-gray-50 border-b border-gray-200 px-4 py-3">
                    <h2 class="text-sm font-semibold text-gray-700 flex items-center">
                        <span class="material-icons-outlined text-primary-DEFAULT mr-2">info</span>
                        Basic Information
                    </h2>
                </div>
                
                <div class="p-4">
                    <div class="space-y-3">
                        <!-- Survey Title -->
                        <div class="flex flex-col md:flex-row md:items-center gap-3">
                            <label class="text-xs font-medium text-gray-700 md:w-40">Survey Title</label>
                            <div class="flex-1">
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <span class="material-icons-outlined text-[#004aad] text-base">title</span>
                                    </div>
                                    <div class="w-full text-xs border-gray-200 bg-gray-50 rounded-[1px] pl-12 py-2 border">
                                        {{ $survey->title }}
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Survey Description -->
                        <div class="flex flex-col md:flex-row md:items-start gap-3">
                            <label class="text-xs font-medium text-gray-700 md:w-40 pt-2">Description</label>
                            <div class="flex-1">
                                <div class="w-full text-xs border-gray-200 bg-gray-50 rounded-[1px] py-2 px-3 border min-h-[80px] whitespace-pre-wrap">
                                    {{ $survey->description ?? 'No description provided.' }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Survey Settings Section -->
            <div class="bg-white border border-gray-200 rounded-md shadow-sm">
                <div class="bg-gray-50 border-b border-gray-200 px-4 py-3">
                    <h2 class="text-sm font-semibold text-gray-700 flex items-center">
                        <span class="material-icons-outlined text-primary-DEFAULT mr-2">settings</span>
                        Survey Settings
                    </h2>
                </div>
                
                <div class="p-4">
                    <div class="space-y-3">
                        <!-- Access Type -->
                        <div class="flex flex-col md:flex-row md:items-center gap-3">
                            <label class="text-xs font-medium text-gray-700 md:w-40">Access Type</label>
                            <div class="flex-1">
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <span class="material-icons-outlined text-[#004aad] text-base">shield</span>
                                    </div>
                                    <div class="w-full text-xs border-gray-200 bg-gray-50 rounded-[1px] pl-12 py-2 border">
                                        @if($survey->access_type === 'public')
                                            Public - Anyone with the link can access
                                        @elseif($survey->access_type === 'private')
                                            Private - Only authenticated users can access
                                        @else
                                            Registered - Only registered participants can access
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Anonymous Responses -->
                        <div class="flex flex-col md:flex-row md:items-center gap-3">
                            <label class="text-xs font-medium text-gray-700 md:w-40">Anonymous Responses</label>
                            <div class="flex-1">
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <span class="material-icons-outlined text-[#004aad] text-base">people</span>
                                    </div>
                                    <div class="w-full text-xs border-gray-200 bg-gray-50 rounded-[1px] pl-12 py-2 border">
                                        {{ $survey->allow_anonymous ? 'Allowed' : 'Not Allowed' }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Related Event Section -->
            <div class="bg-white border border-gray-200 rounded-md shadow-sm">
                <div class="bg-gray-50 border-b border-gray-200 px-4 py-3">
                    <h2 class="text-sm font-semibold text-gray-700 flex items-center">
                        <span class="material-icons-outlined text-primary-DEFAULT mr-2">event</span>
                        Related Event
                    </h2>
                </div>
                
                <div class="p-4">
                    <div class="space-y-3">
                        <!-- Linked Event -->
                        <div class="flex flex-col md:flex-row md:items-center gap-3">
                            <label class="text-xs font-medium text-gray-700 md:w-40">Linked Event</label>
                            <div class="flex-1">
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <span class="material-icons-outlined text-[#004aad] text-base">event_note</span>
                                    </div>
                                    <div class="w-full text-xs border-gray-200 bg-gray-50 rounded-[1px] pl-12 py-2 border">
                                        @if($survey->event)
                                            {{ $survey->event->name }}
                                        @else
                                            No event linked
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Statistics Section -->
            <div class="bg-white border border-gray-200 rounded-md shadow-sm">
                <div class="bg-gray-50 border-b border-gray-200 px-4 py-3">
                    <h2 class="text-sm font-semibold text-gray-700 flex items-center">
                        <span class="material-icons-outlined text-primary-DEFAULT mr-2">analytics</span>
                        Statistics
                    </h2>
                </div>
                
                <div class="p-4">
                    <div class="space-y-3">
                        <!-- Total Questions -->
                        <div class="flex flex-col md:flex-row md:items-center gap-3">
                            <label class="text-xs font-medium text-gray-700 md:w-40">Total Questions</label>
                            <div class="flex-1">
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <span class="material-icons-outlined text-[#004aad] text-base">help_outline</span>
                                    </div>
                                    <div class="w-full text-xs border-gray-200 bg-gray-50 rounded-[1px] pl-12 py-2 border">
                                        {{ $survey->questions->count() }}
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Total Responses -->
                        <div class="flex flex-col md:flex-row md:items-center gap-3">
                            <label class="text-xs font-medium text-gray-700 md:w-40">Total Responses</label>
                            <div class="flex-1">
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <span class="material-icons-outlined text-[#004aad] text-base">assignment_turned_in</span>
                                    </div>
                                    <div class="w-full text-xs border-gray-200 bg-gray-50 rounded-[1px] pl-12 py-2 border">
                                        {{ $survey->responses->count() }}
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Created Date -->
                        <div class="flex flex-col md:flex-row md:items-center gap-3">
                            <label class="text-xs font-medium text-gray-700 md:w-40">Created Date</label>
                            <div class="flex-1">
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <span class="material-icons-outlined text-[#004aad] text-base">date_range</span>
                                    </div>
                                    <div class="w-full text-xs border-gray-200 bg-gray-50 rounded-[1px] pl-12 py-2 border">
                                        {{ $survey->created_at->format('d M Y - H:i') }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
