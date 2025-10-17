<x-app-layout>
    <x-slot name="breadcrumb">
        <span>Survey</span>
        <span class="mx-2 text-gray-500">/</span>
        <span>View Survey</span>
    </x-slot>

    <x-slot name="title">Survey Details</x-slot>

    <div class="bg-white rounded shadow-md border border-gray-300">
        <div class="p-6 border-b border-gray-200">
            <div class="flex items-center justify-between">
                <div class="flex items-center">
                    <span class="material-icons mr-2 text-primary-DEFAULT">quiz</span>
                    <h1 class="text-xl font-bold text-gray-800">Survey Details</h1>
                </div>
                <div class="flex space-x-3">
                    @can('surveys.update')
                    <a href="{{ route('survey.edit', $survey) }}" class="bg-gradient-to-r from-yellow-500 to-yellow-400 hover:from-yellow-600 hover:to-yellow-500 text-white px-3 h-[36px] rounded shadow-sm font-medium flex items-center text-xs transition-colors duration-200 ease-in-out">
                        <span class="material-icons text-xs mr-1">edit</span>
                        Edit Survey
                    </a>
                    @endcan
                    <a href="{{ route('survey.index') }}" class="bg-gradient-to-r from-gray-500 to-gray-400 hover:from-gray-600 hover:to-gray-500 text-white px-3 h-[36px] rounded shadow-sm font-medium flex items-center text-xs transition-colors duration-200 ease-in-out">
                        <span class="material-icons text-xs mr-1">arrow_back</span>
                        Back to List
                    </a>
                </div>
            </div>
            <p class="text-xs text-gray-500 mt-1 ml-8">View detailed information about this survey</p>
        </div>
        
        <div class="p-6 space-y-6">
            <!-- Survey Status Banner -->
            <div class="rounded p-3 mb-6 flex items-center 
                @if($survey->status === 'published')
                    bg-status-active-bg border border-status-active-text/20
                @elseif($survey->status === 'draft')
                    bg-status-pending-bg border border-status-pending-text/20
                @elseif($survey->status === 'closed')
                    bg-status-completed-bg border border-status-completed-text/20
                @endif
            ">
                <span class="material-icons mr-2 
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
            
            <!-- Basic Information -->
            <div class="border-b border-gray-200 pb-5">
                <h2 class="text-sm font-semibold text-gray-700 mb-4">Basic Information</h2>
                <div class="grid grid-cols-1 gap-6">
                    <!-- Survey Title -->
                    <div>
                        <label class="flex items-center text-xs font-medium text-gray-700 mb-1">
                            <span class="material-icons text-sm mr-1 text-primary-DEFAULT">title</span>
                            Survey Title
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <span class="material-icons text-[#004aad] text-base">title</span>
                            </div>
                            <div class="w-full text-xs border-gray-200 bg-gray-50 rounded-[1px] pl-12 py-2 border">
                                {{ $survey->title }}
                            </div>
                        </div>
                    </div>
                    
                    <!-- Survey Description -->
                    <div class="mt-4">
                        <label class="flex items-center text-xs font-medium text-gray-700 mb-1">
                            <span class="material-icons text-sm mr-1 text-primary-DEFAULT">description</span>
                            Survey Description
                        </label>
                        <div class="relative">
                            <div class="w-full text-xs border-gray-200 bg-gray-50 rounded-[1px] py-2 px-3 border min-h-[80px] whitespace-pre-wrap">
                                {{ $survey->description ?? 'No description provided.' }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Settings -->
            <div class="border-b border-gray-200 pb-5">
                <h2 class="text-sm font-semibold text-gray-700 mb-4">Survey Settings</h2>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                    <!-- Access Type -->
                    <div>
                        <label class="flex items-center text-xs font-medium text-gray-700 mb-1">
                            <span class="material-icons text-sm mr-1 text-primary-DEFAULT">security</span>
                            Access Type
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <span class="material-icons text-[#004aad] text-base">shield</span>
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
                    
                    <!-- Anonymous Responses -->
                    <div>
                        <label class="flex items-center text-xs font-medium text-gray-700 mb-1">
                            <span class="material-icons text-sm mr-1 text-primary-DEFAULT">people</span>
                            Anonymous Responses
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <span class="material-icons text-[#004aad] text-base">person</span>
                            </div>
                            <div class="w-full text-xs border-gray-200 bg-gray-50 rounded-[1px] pl-12 py-2 border">
                                {{ $survey->allow_anonymous ? 'Allowed' : 'Not Allowed' }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Related Event -->
            @if($survey->event)
            <div class="border-b border-gray-200 pb-5">
                <h2 class="text-sm font-semibold text-gray-700 mb-4">Related Event</h2>
                <div>
                    <label class="flex items-center text-xs font-medium text-gray-700 mb-1">
                        <span class="material-icons text-sm mr-1 text-primary-DEFAULT">event</span>
                        Event Name
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <span class="material-icons text-[#004aad] text-base">event_note</span>
                        </div>
                        <div class="w-full text-xs border-gray-200 bg-gray-50 rounded-[1px] pl-12 py-2 border">
                            <a href="{{ route('event.show', $survey->event) }}" class="text-blue-600 hover:underline">
                                {{ $survey->event->name }}
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            @endif
            
            <!-- Questions Section -->
            <div class="border-b border-gray-200 pb-5">
                <h2 class="text-sm font-semibold text-gray-700 mb-4 flex items-center">
                    Survey Questions
                    <span class="ml-2 bg-gray-100 text-gray-700 text-xs px-2 py-0.5 rounded-full">{{ $survey->questions->count() }}</span>
                </h2>
                
                @if($survey->questions->isEmpty())
                    <div class="bg-yellow-50 border border-yellow-200 text-yellow-800 px-4 py-3 rounded relative" role="alert">
                        <div class="flex">
                            <div class="py-1"><span class="material-icons text-xl mr-2">warning</span></div>
                            <div>
                                <p class="font-bold">No questions</p>
                                <p class="text-xs">This survey doesn't have any questions yet. <a href="{{ route('survey.edit', $survey) }}" class="text-primary-DEFAULT hover:underline">Add questions</a> to make it available to respondents.</p>
                            </div>
                        </div>
                    </div>
                @else
                    <div class="space-y-4">
                        @foreach($survey->questions as $question)
                            <div class="bg-gray-50 border border-gray-200 rounded-[1px] p-4">
                                <div class="flex justify-between items-start">
                                    <div>
                                        <p class="font-medium text-xs">{{ $loop->iteration }}. {{ $question->question_text }}</p>
                                        @if($question->description)
                                            <p class="text-[10px] text-gray-500 mt-1">{{ $question->description }}</p>
                                        @endif
                                    </div>
                                    <div class="flex items-center">
                                        <span class="bg-gray-200 text-gray-800 text-[10px] px-2 py-0.5 rounded-full">{{ ucfirst(str_replace('_', ' ', $question->question_type)) }}</span>
                                        @if($question->required)
                                            <span class="ml-2 bg-red-100 text-red-800 text-[10px] px-2 py-0.5 rounded-full">Required</span>
                                        @endif
                                    </div>
                                </div>
                                
                                <!-- Mock answer field based on question type -->
                                <div class="mt-3">
                                    @switch($question->question_type)
                                        @case('text')
                                            <input type="text" disabled placeholder="Short text answer" class="block w-full rounded-[1px] border-gray-300 bg-gray-100 shadow-sm text-xs">
                                            @break
                                        @case('textarea')
                                            <textarea disabled placeholder="Paragraph text answer" rows="2" class="block w-full rounded-[1px] border-gray-300 bg-gray-100 shadow-sm text-xs"></textarea>
                                            @break
                                        @case('multiple_choice')
                                            @if($question->options)
                                                <div class="space-y-2">
                                                    @foreach($question->options as $option)
                                                        <div class="flex items-center">
                                                            <input type="radio" disabled class="rounded-full border-gray-300 text-primary-DEFAULT shadow-sm">
                                                            <label class="ml-2 block text-xs text-gray-700">{{ $option }}</label>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            @endif
                                            @break
                                        @case('checkbox')
                                            @if($question->options)
                                                <div class="space-y-2">
                                                    @foreach($question->options as $option)
                                                        <div class="flex items-center">
                                                            <input type="checkbox" disabled class="rounded border-gray-300 text-primary-DEFAULT shadow-sm">
                                                            <label class="ml-2 block text-xs text-gray-700">{{ $option }}</label>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            @endif
                                            @break
                                        @case('dropdown')
                                            @if($question->options)
                                                <select disabled class="block w-full rounded-[1px] border-gray-300 bg-gray-100 shadow-sm text-xs">
                                                    <option value="">Select an option</option>
                                                    @foreach($question->options as $option)
                                                        <option>{{ $option }}</option>
                                                    @endforeach
                                                </select>
                                            @endif
                                            @break
                                        @case('rating')
                                            <div class="flex space-x-4">
                                                @for($i = 1; $i <= 5; $i++)
                                                    <div class="flex flex-col items-center">
                                                        <input type="radio" disabled class="rounded-full border-gray-300 text-primary-DEFAULT shadow-sm">
                                                        <label class="mt-1 text-[10px] text-gray-500">{{ $i }}</label>
                                                    </div>
                                                @endfor
                                            </div>
                                            @break
                                        @case('date')
                                            <input type="date" disabled class="block w-auto rounded-[1px] border-gray-300 bg-gray-100 shadow-sm text-xs">
                                            @break
                                    @endswitch
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
            
            <!-- Response Information -->
            <div class="border-b border-gray-200 pb-5">
                <h2 class="text-sm font-semibold text-gray-700 mb-4">Response Information</h2>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <!-- Total Responses -->
                    <div>
                        <label class="flex items-center text-xs font-medium text-gray-700 mb-1">
                            <span class="material-icons text-sm mr-1 text-primary-DEFAULT">poll</span>
                            Total Responses
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <span class="material-icons text-[#004aad] text-base">analytics</span>
                            </div>
                            <div class="w-full text-xs border-gray-200 bg-gray-50 rounded-[1px] pl-12 py-2 border">
                                {{ $responsesCount }}
                            </div>
                        </div>
                    </div>
                    
                    <!-- Public URL -->
                    <div class="md:col-span-2">
                        <label class="flex items-center text-xs font-medium text-gray-700 mb-1">
                            <span class="material-icons text-sm mr-1 text-primary-DEFAULT">link</span>
                            Public URL
                        </label>
                        <div class="flex">
                            <div class="relative flex-grow">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <span class="material-icons text-[#004aad] text-base">link</span>
                                </div>
                                <input type="text" readonly value="{{ $survey->public_url }}" class="w-full text-xs border-gray-200 bg-gray-50 rounded-l-[1px] pl-12 py-2 border">
                            </div>
                            <button onclick="copyToClipboard('{{ $survey->public_url }}')" class="bg-gradient-to-r from-blue-600 to-blue-500 hover:from-blue-700 hover:to-blue-600 text-white px-3 py-2 rounded-r-[1px] shadow-sm text-xs font-medium transition-colors duration-200 ease-in-out flex items-center">
                                <span class="material-icons text-xs">content_copy</span>
                            </button>
                        </div>
                    </div>
                </div>
                
                <!-- Response Actions -->
                <div class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-4">
                    @can('survey_responses.read')
                    <a href="{{ route('survey.responses', $survey) }}" class="bg-gradient-to-r from-blue-600 to-blue-500 hover:from-blue-700 hover:to-blue-600 text-white px-3 py-2 rounded shadow-sm text-xs font-medium transition-colors duration-200 ease-in-out flex items-center justify-center">
                        <span class="material-icons text-xs mr-1">list_alt</span>
                        View All Responses
                    </a>
                    @endcan
                    
                    @can('survey_responses.read')
                    <a href="{{ route('survey.analytics', $survey) }}" class="bg-gradient-to-r from-green-600 to-green-500 hover:from-green-700 hover:to-green-600 text-white px-3 py-2 rounded shadow-sm text-xs font-medium transition-colors duration-200 ease-in-out flex items-center justify-center">
                        <span class="material-icons text-xs mr-1">insights</span>
                        View Analytics
                    </a>
                    @endcan
                </div>
            </div>
            
            <!-- Administrative Information -->
            <div>
                <h2 class="text-sm font-semibold text-gray-700 mb-4">Administrative Information</h2>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <!-- Created Date -->
                    <div>
                        <label class="flex items-center text-xs font-medium text-gray-700 mb-1">
                            <span class="material-icons text-sm mr-1 text-primary-DEFAULT">today</span>
                            Created Date
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <span class="material-icons text-[#004aad] text-base">date_range</span>
                            </div>
                            <div class="w-full text-xs border-gray-200 bg-gray-50 rounded-[1px] pl-12 py-2 border">
                                {{ $survey->created_at->format('d M Y - H:i') }}
                            </div>
                        </div>
                    </div>
                    
                    <!-- Last Updated -->
                    <div>
                        <label class="flex items-center text-xs font-medium text-gray-700 mb-1">
                            <span class="material-icons text-sm mr-1 text-primary-DEFAULT">update</span>
                            Last Updated
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <span class="material-icons text-[#004aad] text-base">edit_calendar</span>
                            </div>
                            <div class="w-full text-xs border-gray-200 bg-gray-50 rounded-[1px] pl-12 py-2 border">
                                {{ $survey->updated_at->format('d M Y - H:i') }}
                            </div>
                        </div>
                    </div>
                    
                    <!-- Created By -->
                    <div>
                        <label class="flex items-center text-xs font-medium text-gray-700 mb-1">
                            <span class="material-icons text-sm mr-1 text-primary-DEFAULT">person</span>
                            Created By
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <span class="material-icons text-[#004aad] text-base">account_circle</span>
                            </div>
                            <div class="w-full text-xs border-gray-200 bg-gray-50 rounded-[1px] pl-12 py-2 border">
                                {{ $survey->user ? $survey->user->name : 'Unknown' }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Action Buttons (Additional) -->
    <div class="mt-6 flex justify-end space-x-3">
        @can('surveys.publish')
            @if($survey->status === 'draft')
                <form action="{{ route('survey.toggle-publish', $survey) }}" method="POST">
                    @csrf
                    <button type="submit" class="bg-gradient-to-r from-green-600 to-green-500 hover:from-green-700 hover:to-green-600 text-white px-3 py-2 rounded shadow-sm text-xs font-medium transition-colors duration-200 ease-in-out flex items-center">
                        <span class="material-icons text-xs mr-1">publish</span>
                        Publish Survey
                    </button>
                </form>
            @elseif($survey->status === 'published')
                <form action="{{ route('survey.toggle-publish', $survey) }}" method="POST">
                    @csrf
                    <button type="submit" class="bg-gradient-to-r from-gray-600 to-gray-500 hover:from-gray-700 hover:to-gray-600 text-white px-3 py-2 rounded shadow-sm text-xs font-medium transition-colors duration-200 ease-in-out flex items-center">
                        <span class="material-icons text-xs mr-1">visibility_off</span>
                        Unpublish Survey
                    </button>
                </form>
            @endif
        @endcan
        
        @can('surveys.delete')
        <form action="{{ route('survey.destroy', $survey) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this survey? This action cannot be undone.')">
            @csrf
            @method('DELETE')
            <button type="submit" class="bg-gradient-to-r from-red-600 to-red-500 hover:from-red-700 hover:to-red-600 text-white px-3 py-2 rounded shadow-sm text-xs font-medium transition-colors duration-200 ease-in-out flex items-center">
                <span class="material-icons text-xs mr-1">delete</span>
                Delete Survey
            </button>
        </form>
        @endcan
    </div>
    
    <script>
        function copyToClipboard(text) {
            navigator.clipboard.writeText(text).then(() => {
                alert('Link copied to clipboard!');
            }).catch(err => {
                console.error('Failed to copy URL: ', err);
            });
        }
    </script>
</x-app-layout>
