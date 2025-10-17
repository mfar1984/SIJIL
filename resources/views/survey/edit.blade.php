<x-app-layout>
    <x-slot name="breadcrumb">
        <span>Survey</span>
        <span class="mx-2 text-gray-500">/</span>
        <span>Edit Survey</span>
    </x-slot>

    <x-slot name="title">Edit Survey</x-slot>

    <div class="bg-white rounded shadow-md border border-gray-300">
        <div class="p-6 border-b border-gray-200">
            <div class="flex items-center">
                <span class="material-icons mr-2 text-primary-DEFAULT">quiz</span>
                <h1 class="text-xl font-bold text-gray-800">Edit Survey: {{ $survey->title }}</h1>
            </div>
            <p class="text-xs text-gray-500 mt-1 ml-8">Modify survey information and questions</p>
        </div>
        
        <div class="p-6">
            @if ($errors->any())
                <div class="mb-4 p-4 bg-red-50 border border-red-200 text-red-700 rounded">
                    <ul class="list-disc pl-5">
                        @foreach ($errors->all() as $error)
                            <li class="text-xs">{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @if(session('success'))
                <div class="mb-4 p-4 bg-green-50 border border-green-200 text-green-700 rounded">
                    <p class="text-xs">{{ session('success') }}</p>
                </div>
            @endif
            
            @if(session('error'))
                <div class="mb-4 p-4 bg-red-50 border border-red-200 text-red-700 rounded">
                    <p class="text-xs">{{ session('error') }}</p>
                </div>
            @endif

            <form method="POST" action="{{ route('survey.update', $survey) }}" class="space-y-6">
                @csrf
                @method('PUT')
                
                <!-- Basic Information -->
                <div class="border-b border-gray-200 pb-5">
                    <h2 class="text-sm font-semibold text-gray-700 mb-4">Basic Information</h2>
                    
                    <!-- Survey Title -->
                    <div class="mb-4">
                        <label for="title" class="flex items-center text-xs font-medium text-gray-700 mb-1">
                            <span class="material-icons text-sm mr-1 text-primary-DEFAULT">title</span>
                            Survey Title
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <span class="material-icons text-[#004aad] text-base">title</span>
                            </div>
                            <input 
                                type="text" 
                                name="title" 
                                id="title" 
                                class="w-full text-xs border-gray-300 rounded-[1px] pl-12 focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50" 
                                value="{{ old('title', $survey->title) }}" 
                                placeholder="e.g., Customer Satisfaction Survey"
                                required
                            >
                        </div>
                        <p class="mt-1 text-[10px] text-gray-500">Enter a descriptive title for the survey</p>
                    </div>
                    
                    <!-- Description -->
                    <div class="mb-4">
                        <label for="description" class="flex items-center text-xs font-medium text-gray-700 mb-1">
                            <span class="material-icons text-sm mr-1 text-primary-DEFAULT">description</span>
                            Description
                        </label>
                        <div class="relative">
                            <div class="absolute top-3 left-0 pl-3 flex items-start pointer-events-none">
                                <span class="material-icons text-[#004aad] text-base">notes</span>
                            </div>
                            <textarea 
                                name="description" 
                                id="description" 
                                rows="3" 
                                class="w-full text-xs border-gray-300 rounded-[1px] pl-12 focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50"
                                placeholder="Enter survey description here"
                            >{{ old('description', $survey->description) }}</textarea>
                        </div>
                        <p class="mt-1 text-[10px] text-gray-500">Provide a detailed description of the survey</p>
                    </div>
                </div>
                
                <!-- Settings -->
                <div class="border-b border-gray-200 pb-5">
                    <h2 class="text-sm font-semibold text-gray-700 mb-4">Survey Settings</h2>
                    
                    <!-- Access Type -->
                    <div class="mb-4">
                        <label for="access_type" class="flex items-center text-xs font-medium text-gray-700 mb-1">
                            <span class="material-icons text-sm mr-1 text-primary-DEFAULT">security</span>
                            Access Type
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <span class="material-icons text-[#004aad] text-base">shield</span>
                            </div>
                            <select 
                                name="access_type" 
                                id="access_type" 
                                class="w-full h-9 text-xs border-gray-300 rounded-[1px] pl-12 focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50 leading-[1rem]" 
                                required
                            >
                                <option value="public" {{ old('access_type', $survey->access_type) == 'public' ? 'selected' : '' }}>Public - Anyone with the link can access</option>
                                <option value="private" {{ old('access_type', $survey->access_type) == 'private' ? 'selected' : '' }}>Private - Only authenticated users can access</option>
                                <option value="registered" {{ old('access_type', $survey->access_type) == 'registered' ? 'selected' : '' }}>Registered - Only registered participants can access</option>
                            </select>
                        </div>
                        <p class="mt-1 text-[10px] text-gray-500">Control who can access and respond to this survey</p>
                    </div>
                    
                    <!-- Allow Anonymous Responses -->
                    <div class="mb-4">
                        <label class="flex items-center text-xs font-medium text-gray-700 mb-2">
                            <span class="material-icons text-sm mr-1 text-primary-DEFAULT">people</span>
                            Anonymous Responses
                        </label>
                        <div class="flex items-center">
                            <input 
                                type="checkbox" 
                                name="allow_anonymous" 
                                id="allow_anonymous" 
                                value="1" 
                                {{ old('allow_anonymous', $survey->allow_anonymous) ? 'checked' : '' }}
                                class="rounded border-gray-300 text-primary-DEFAULT shadow-sm focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50"
                            >
                            <label for="allow_anonymous" class="ml-2 block text-xs text-gray-700">
                                Allow anonymous responses (respondents can submit without providing their information)
                            </label>
                        </div>
                    </div>
                </div>
                
                <!-- Related Event -->
                <div class="border-b border-gray-200 pb-5">
                    <h2 class="text-sm font-semibold text-gray-700 mb-4">Related Event</h2>
                    
                    <div class="mb-4">
                        <label for="event_id" class="flex items-center text-xs font-medium text-gray-700 mb-1">
                            <span class="material-icons text-sm mr-1 text-primary-DEFAULT">event</span>
                            Link to Event
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <span class="material-icons text-[#004aad] text-base">event_note</span>
                            </div>
                            <select 
                                name="event_id" 
                                id="event_id" 
                                class="w-full h-9 text-xs border-gray-300 rounded-[1px] pl-12 focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50 leading-[1rem]"
                            >
                                <option value="">-- No event --</option>
                                @foreach($events as $event)
                                    <option value="{{ $event->id }}" {{ old('event_id', $survey->event_id) == $event->id ? 'selected' : '' }}>
                                        {{ $event->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <p class="mt-1 text-[10px] text-gray-500">Optionally link this survey to an event</p>
                    </div>
                </div>
                
                <!-- Status -->
                <div class="border-b border-gray-200 pb-5">
                    <h2 class="text-sm font-semibold text-gray-700 mb-4">Status</h2>
                    
                    <div>
                        <div class="flex items-center text-xs font-medium text-gray-700 mb-2">
                            <span class="material-icons text-sm mr-1 text-primary-DEFAULT">info</span>
                            Current Status: 
                            @if($survey->status === 'published')
                                <span class="ml-2 px-2 py-0.5 bg-green-100 text-green-800 rounded-full text-xs">Published</span>
                            @elseif($survey->status === 'draft')
                                <span class="ml-2 px-2 py-0.5 bg-gray-100 text-gray-800 rounded-full text-xs">Draft</span>
                            @else
                                <span class="ml-2 px-2 py-0.5 bg-red-100 text-red-800 rounded-full text-xs">Closed</span>
                            @endif
                        </div>
                        <div class="text-xs text-gray-600 mt-2">
                            <div class="mb-1"><span class="font-medium">Created:</span> {{ $survey->created_at->format('d M Y') }}</div>
                            
                            @if($survey->published_at)
                                <div class="mb-1"><span class="font-medium">Published:</span> {{ $survey->published_at->format('d M Y') }}</div>
                            @endif
                            
                            @if($survey->expires_at)
                                <div class="mb-1"><span class="font-medium">Expires:</span> {{ $survey->expires_at->format('d M Y') }}</div>
                            @endif
                        </div>
                        <div class="mt-4">
                            <p class="text-xs font-medium text-gray-700">Public URL:</p>
                            <div class="flex items-center">
                                <input type="text" value="{{ $survey->public_url }}" readonly
                                    class="w-full text-xs border-gray-300 rounded-[1px] focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50 bg-gray-50">
                                <button type="button" onclick="copyToClipboard('{{ $survey->public_url }}')" class="ml-2 text-primary-DEFAULT hover:text-primary-dark">
                                    <span class="material-icons text-base">content_copy</span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Publish/Unpublish Button -->
                <div class="border-b border-gray-200 pb-5">
                    <h2 class="text-sm font-semibold text-gray-700 mb-4">Publication Settings</h2>
                    <div class="flex items-center space-x-3">
                        @if($survey->status === 'draft')
                            <form action="{{ route('survey.toggle-publish', $survey) }}" method="POST">
                                @csrf
                                <button type="submit" class="px-3 py-1 bg-gradient-to-r from-green-600 to-green-500 hover:from-green-700 hover:to-green-600 text-white rounded shadow-sm text-xs font-medium transition-colors duration-200 ease-in-out flex items-center">
                                    <span class="material-icons text-xs mr-1">publish</span>
                                    Publish Survey
                                </button>
                            </form>
                        @else
                            <form action="{{ route('survey.toggle-publish', $survey) }}" method="POST">
                                @csrf
                                <button type="submit" class="px-3 py-1 bg-gradient-to-r from-gray-600 to-gray-500 hover:from-gray-700 hover:to-gray-600 text-white rounded shadow-sm text-xs font-medium transition-colors duration-200 ease-in-out flex items-center">
                                    <span class="material-icons text-xs mr-1">visibility_off</span>
                                    Unpublish
                                </button>
                            </form>
                        @endif
                        
                        <a href="{{ $survey->public_url }}" target="_blank" class="px-3 py-1 bg-gradient-to-r from-blue-600 to-blue-500 hover:from-blue-700 hover:to-blue-600 text-white rounded shadow-sm text-xs font-medium transition-colors duration-200 ease-in-out flex items-center">
                            <span class="material-icons text-xs mr-1">visibility</span>
                            Preview Survey
                        </a>
                    </div>
                </div>
                
                <div class="border-t border-gray-200 pt-4 mt-6 flex justify-end space-x-3">
                    <a 
                        href="{{ route('survey.show', $survey) }}" 
                        class="px-3 h-[36px] bg-gradient-to-r from-red-600 to-red-500 hover:from-red-700 hover:to-red-600 text-white rounded shadow-sm text-xs font-medium transition-colors duration-200 ease-in-out flex items-center"
                    >
                        <span class="material-icons text-xs mr-1">cancel</span>
                        Cancel
                    </a>
                    <button 
                        type="submit" 
                        class="px-3 h-[36px] bg-gradient-to-r from-blue-600 to-blue-500 hover:from-blue-700 hover:to-blue-600 text-white rounded shadow-sm text-xs font-medium transition-colors duration-200 ease-in-out flex items-center"
                    >
                        <span class="material-icons text-xs mr-1">save</span>
                        Update Survey
                    </button>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Questions Section -->
    <div class="bg-white rounded shadow-md border border-gray-300 mt-6">
        <div class="p-6 border-b border-gray-200">
            <div class="flex items-center">
                <span class="material-icons mr-2 text-primary-DEFAULT">help</span>
                <h1 class="text-xl font-bold text-gray-800">Survey Questions</h1>
            </div>
            <p class="text-xs text-gray-500 mt-1 ml-8">Manage questions for this survey</p>
        </div>
        
        <div class="p-6">
            @if($survey->questions->isEmpty())
                <div class="bg-blue-50 border border-blue-100 text-blue-800 p-4 mb-6 rounded-md flex items-start">
                    <span class="material-icons mr-2">info</span>
<div>
                        <p class="font-medium text-sm">No questions yet</p>
                        <p class="text-xs mt-1">Add questions to your survey using the form below.</p>
                    </div>
                </div>
            @else
                <!-- Questions List -->
                <div class="mb-6 border-b border-gray-200 pb-5">
                    <h2 class="text-sm font-semibold text-gray-700 mb-4">Existing Questions ({{ $survey->questions->count() }})</h2>
                    
                    <div class="space-y-4" id="question-list">
                        @foreach($survey->questions as $question)
                            <div class="bg-white border border-gray-200 rounded-md p-4 shadow-sm" data-question-id="{{ $question->id }}">
                                <div class="flex justify-between">
                                    <div class="flex items-center">
                                        <span class="material-icons text-gray-400 cursor-move mr-2">drag_indicator</span>
                                        <span class="font-medium text-xs">Q{{ $loop->iteration }}:</span>
                                    </div>
                                    
                                    <div class="flex space-x-2">
                                        <button type="button" onclick="openEditModal({{ $question->id }})" class="text-blue-600 hover:text-blue-800">
                                            <span class="material-icons text-sm">edit</span>
                                        </button>
                                        
                                        <form action="{{ route('survey.questions.destroy', [$survey, $question]) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this question?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-600 hover:text-red-800">
                                                <span class="material-icons text-sm">delete</span>
                                            </button>
                                        </form>
                                    </div>
                                </div>
                                
                                <div class="mt-2">
                                    <p class="text-xs font-medium">{{ $question->question_text }}</p>
                                    @if($question->description)
                                        <p class="text-[10px] text-gray-500 mt-1">{{ $question->description }}</p>
                                    @endif
                                    
                                    <div class="mt-2 flex items-center">
                                        <span class="bg-gray-100 text-gray-700 text-[10px] px-2 py-0.5 rounded-full">{{ ucfirst(str_replace('_', ' ', $question->question_type)) }}</span>
                                        
                                        @if($question->required)
                                            <span class="ml-2 bg-red-100 text-red-800 text-[10px] px-2 py-0.5 rounded-full">Required</span>
                                        @endif
                                    </div>
                                    
                                    @if($question->options && in_array($question->question_type, ['multiple_choice', 'checkbox', 'dropdown']))
                                        <div class="mt-3">
                                            <p class="text-[10px] font-medium text-gray-700">Options:</p>
                                            <div class="flex flex-wrap gap-2 mt-1">
                                                @foreach($question->options as $option)
                                                    <span class="bg-gray-50 text-gray-600 text-[10px] px-2 py-1 rounded border border-gray-200">{{ $option }}</span>
                                                @endforeach
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
            
            <!-- Add New Question Form -->
            <div class="border-b border-gray-200 pb-5">
                <h2 class="text-sm font-semibold text-gray-700 mb-4">Add New Question</h2>
                
                <form action="{{ route('survey.questions.store', $survey) }}" method="POST" class="space-y-4">
                    @csrf
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="col-span-1">
                            <label class="flex items-center text-xs font-medium text-gray-700 mb-1">
                                <span class="material-icons text-sm mr-1 text-primary-DEFAULT">help</span>
                                Question Text
                            </label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <span class="material-icons text-[#004aad] text-base">help_outline</span>
                                </div>
                                <input 
                                    type="text" 
                                    name="question_text" 
                                    class="w-full text-xs border-gray-300 rounded-[1px] pl-12 focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50" 
                                    placeholder="Enter your question"
                                    required
                                >
                            </div>
                        </div>
                        
                        <div class="col-span-1">
                            <label class="flex items-center text-xs font-medium text-gray-700 mb-1">
                                <span class="material-icons text-sm mr-1 text-primary-DEFAULT">category</span>
                                Question Type
                            </label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <span class="material-icons text-[#004aad] text-base">view_list</span>
                                </div>
                                <select 
                                    name="question_type" 
                                    id="question_type"
                                    class="w-full text-xs border-gray-300 rounded-[1px] pl-12 focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50" 
                                    onchange="toggleOptionsField()"
                                    required
                                >
                                    <option value="text">Short Text</option>
                                    <option value="textarea">Paragraph Text</option>
                                    <option value="multiple_choice">Multiple Choice (Single Selection)</option>
                                    <option value="checkbox">Checkboxes (Multiple Selection)</option>
                                    <option value="dropdown">Dropdown</option>
                                    <option value="rating">Rating Scale (1-5)</option>
                                    <option value="date">Date</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="col-span-1 md:col-span-2">
                            <label class="flex items-center text-xs font-medium text-gray-700 mb-1">
                                <span class="material-icons text-sm mr-1 text-primary-DEFAULT">info</span>
                                Help Text (Optional)
                            </label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <span class="material-icons text-[#004aad] text-base">info</span>
                                </div>
                                <input 
                                    type="text" 
                                    name="description" 
                                    class="w-full text-xs border-gray-300 rounded-[1px] pl-12 focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50" 
                                    placeholder="Additional information about this question"
                                >
                            </div>
                        </div>
                        
                        <div id="options-container" class="col-span-1 md:col-span-2 hidden border-t border-gray-100 pt-4">
                            <label class="flex items-center text-xs font-medium text-gray-700 mb-2">
                                <span class="material-icons text-sm mr-1 text-primary-DEFAULT">list</span>
                                Options
                            </label>
                            
                            <div class="space-y-2" id="options-list">
                                <div class="flex items-center">
                                    <div class="relative flex-grow">
                                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                            <span class="material-icons text-[#004aad] text-xs">circle</span>
                                        </div>
                                        <input 
                                            type="text" 
                                            name="options[]" 
                                            class="w-full text-xs border-gray-300 rounded-[1px] pl-8 focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50" 
                                            placeholder="Option 1"
                                        >
                                    </div>
                                    <button type="button" class="ml-2 text-gray-400 hover:text-red-600 hidden remove-option">
                                        <span class="material-icons text-xs">close</span>
                                    </button>
                                </div>
                                <div class="flex items-center">
                                    <div class="relative flex-grow">
                                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                            <span class="material-icons text-[#004aad] text-xs">circle</span>
                                        </div>
                                        <input 
                                            type="text" 
                                            name="options[]" 
                                            class="w-full text-xs border-gray-300 rounded-[1px] pl-8 focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50" 
                                            placeholder="Option 2"
                                        >
                                    </div>
                                    <button type="button" class="ml-2 text-gray-400 hover:text-red-600 hidden remove-option">
                                        <span class="material-icons text-xs">close</span>
                                    </button>
                                </div>
                            </div>
                            
                            <button type="button" id="add-option" class="mt-2 text-primary-DEFAULT hover:text-primary-dark text-xs flex items-center">
                                <span class="material-icons text-xs mr-1">add_circle</span>
                                Add Option
                            </button>
                        </div>
                        
                        <div class="col-span-1 md:col-span-2">
                            <div class="flex items-center">
                                <input 
                                    type="checkbox" 
                                    name="required" 
                                    id="required" 
                                    value="1"
                                    class="rounded border-gray-300 text-primary-DEFAULT shadow-sm focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50"
                                >
                                <label for="required" class="ml-2 block text-xs text-gray-700">
                                    Required question
                                </label>
                            </div>
                        </div>
                    </div>
                    
                    <div class="pt-4">
                        <button type="submit" class="px-3 py-1 bg-gradient-to-r from-green-600 to-green-500 hover:from-green-700 hover:to-green-600 text-white rounded shadow-sm text-xs font-medium transition-colors duration-200 ease-in-out flex items-center">
                            <span class="material-icons text-xs mr-1">add</span>
                            Add Question
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Edit Question Modal -->
    <div id="edit-question-modal" class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center hidden">
        <div class="bg-white rounded-lg max-w-2xl w-full max-h-[90vh] overflow-y-auto">
            <div class="p-4 border-b border-gray-200 flex justify-between items-center">
                <h3 class="text-base font-medium text-gray-800">Edit Question</h3>
                <button onclick="closeEditModal()" class="text-gray-500 hover:text-gray-700">
                    <span class="material-icons">close</span>
                </button>
            </div>
            
            <form id="edit-question-form" method="POST" class="p-4 space-y-4">
                @csrf
                @method('PUT')
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="col-span-1">
                        <label class="flex items-center text-xs font-medium text-gray-700 mb-1">
                            <span class="material-icons text-sm mr-1 text-primary-DEFAULT">help</span>
                            Question Text
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <span class="material-icons text-[#004aad] text-base">help_outline</span>
                            </div>
                            <input 
                                type="text" 
                                name="question_text" 
                                id="edit_question_text"
                                class="w-full text-xs border-gray-300 rounded-[1px] pl-12 focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50" 
                                placeholder="Enter your question"
                                required
                            >
                        </div>
                    </div>
                    
                    <div class="col-span-1">
                        <label class="flex items-center text-xs font-medium text-gray-700 mb-1">
                            <span class="material-icons text-sm mr-1 text-primary-DEFAULT">category</span>
                            Question Type
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <span class="material-icons text-[#004aad] text-base">view_list</span>
                            </div>
                            <select 
                                name="question_type" 
                                id="edit_question_type"
                                class="w-full text-xs border-gray-300 rounded-[1px] pl-12 focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50" 
                                onchange="toggleEditOptionsField()"
                                required
                            >
                                <option value="text">Short Text</option>
                                <option value="textarea">Paragraph Text</option>
                                <option value="multiple_choice">Multiple Choice (Single Selection)</option>
                                <option value="checkbox">Checkboxes (Multiple Selection)</option>
                                <option value="dropdown">Dropdown</option>
                                <option value="rating">Rating Scale (1-5)</option>
                                <option value="date">Date</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="col-span-1 md:col-span-2">
                        <label class="flex items-center text-xs font-medium text-gray-700 mb-1">
                            <span class="material-icons text-sm mr-1 text-primary-DEFAULT">info</span>
                            Help Text (Optional)
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <span class="material-icons text-[#004aad] text-base">info</span>
                            </div>
                            <input 
                                type="text" 
                                name="description" 
                                id="edit_question_description"
                                class="w-full text-xs border-gray-300 rounded-[1px] pl-12 focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50" 
                                placeholder="Additional information about this question"
                            >
                        </div>
                    </div>
                    
                    <div id="edit-options-container" class="col-span-1 md:col-span-2 hidden border-t border-gray-100 pt-4">
                        <label class="flex items-center text-xs font-medium text-gray-700 mb-2">
                            <span class="material-icons text-sm mr-1 text-primary-DEFAULT">list</span>
                            Options
                        </label>
                        
                        <div class="space-y-2" id="edit-options-list">
                            <!-- Options will be populated dynamically -->
                        </div>
                        
                        <button type="button" id="edit-add-option" class="mt-2 text-primary-DEFAULT hover:text-primary-dark text-xs flex items-center">
                            <span class="material-icons text-xs mr-1">add_circle</span>
                            Add Option
                        </button>
                    </div>
                    
                    <div class="col-span-1 md:col-span-2">
                        <div class="flex items-center">
                            <input 
                                type="checkbox" 
                                name="required" 
                                id="edit_required" 
                                value="1"
                                class="rounded border-gray-300 text-primary-DEFAULT shadow-sm focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50"
                            >
                            <label for="edit_required" class="ml-2 block text-xs text-gray-700">
                                Required question
                            </label>
                        </div>
                    </div>
                </div>
                
                <div class="pt-4 flex justify-end space-x-3">
                    <button type="button" onclick="closeEditModal()" class="px-3 py-1 bg-gradient-to-r from-gray-600 to-gray-500 hover:from-gray-700 hover:to-gray-600 text-white rounded shadow-sm text-xs font-medium transition-colors duration-200 ease-in-out">
                        Cancel
                    </button>
                    <button type="submit" class="px-3 py-1 bg-gradient-to-r from-blue-600 to-blue-500 hover:from-blue-700 hover:to-blue-600 text-white rounded shadow-sm text-xs font-medium transition-colors duration-200 ease-in-out flex items-center">
                        <span class="material-icons text-xs mr-1">save</span>
                        Update Question
                    </button>
                </div>
            </form>
        </div>
</div>
    
    <script>
        function toggleOptionsField() {
            const questionType = document.getElementById('question_type').value;
            const optionsContainer = document.getElementById('options-container');
            
            if (['multiple_choice', 'checkbox', 'dropdown'].includes(questionType)) {
                optionsContainer.classList.remove('hidden');
            } else {
                optionsContainer.classList.add('hidden');
            }
        }
        
        function copyToClipboard(text) {
            navigator.clipboard.writeText(text).then(() => {
                alert('URL copied to clipboard');
            }).catch(err => {
                console.error('Failed to copy URL: ', err);
            });
        }
        
        document.addEventListener('DOMContentLoaded', function() {
            // Initial options field toggle
            toggleOptionsField();
            
            // Add option button
            const addOptionButton = document.getElementById('add-option');
            const optionsList = document.getElementById('options-list');
            
            if (addOptionButton) {
                addOptionButton.addEventListener('click', function() {
                    const optionCount = optionsList.children.length + 1;
                    
                    const optionDiv = document.createElement('div');
                    optionDiv.className = 'flex items-center';
                    optionDiv.innerHTML = `
                        <div class="relative flex-grow">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <span class="material-icons text-[#004aad] text-xs">circle</span>
                            </div>
                            <input 
                                type="text" 
                                name="options[]" 
                                class="w-full text-xs border-gray-300 rounded-[1px] pl-8 focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50" 
                                placeholder="Option ${optionCount}"
                            >
                        </div>
                        <button type="button" class="ml-2 text-gray-400 hover:text-red-600 remove-option">
                            <span class="material-icons text-xs">close</span>
                        </button>
                    `;
                    
                    optionsList.appendChild(optionDiv);
                    
                    // Show all remove buttons if we have more than 2 options
                    if (optionsList.children.length > 2) {
                        const removeButtons = document.querySelectorAll('.remove-option');
                        removeButtons.forEach(button => {
                            button.classList.remove('hidden');
                        });
                    }
                    
                    // Add event listener for the new remove button
                    const removeButton = optionDiv.querySelector('.remove-option');
                    removeButton.addEventListener('click', function() {
                        optionDiv.remove();
                        
                        // Hide remove buttons if we're back to 2 options
                        if (optionsList.children.length <= 2) {
                            const removeButtons = document.querySelectorAll('.remove-option');
                            removeButtons.forEach(button => {
                                button.classList.add('hidden');
                            });
                        }
                    });
                });
            }
            
            // Initialize event listeners for existing remove buttons
            const removeButtons = document.querySelectorAll('.remove-option');
            removeButtons.forEach(button => {
                button.addEventListener('click', function() {
                    button.parentElement.remove();
                    
                    // Hide remove buttons if we're back to 2 options
                    if (optionsList.children.length <= 2) {
                        const remainingButtons = document.querySelectorAll('.remove-option');
                        remainingButtons.forEach(btn => {
                            btn.classList.add('hidden');
                        });
                    }
                });
            });
        });
        
        function openEditModal(questionId) {
            document.getElementById('edit-question-modal').classList.remove('hidden');
            
            // Get question data from the DOM
            const questionElement = document.querySelector(`[data-question-id="${questionId}"]`);
            if (questionElement) {
                const questionText = questionElement.querySelector('p.text-xs.font-medium').textContent;
                const questionType = questionElement.querySelector('.bg-gray-100').textContent.toLowerCase().replace(/\s+/g, '_');
                const descNode = questionElement.querySelector('p.text-\\[10px\\].text-gray-500');
                const description = descNode ? descNode.textContent : '';
                const isRequired = questionElement.querySelector('.bg-red-100') !== null;
                
                // Populate form fields
                document.getElementById('edit_question_text').value = questionText;
                document.getElementById('edit_question_type').value = questionType;
                document.getElementById('edit_question_description').value = description;
                document.getElementById('edit_required').checked = isRequired;
                
                // Set form action to the correct route using hardcoded URL for debugging
                const form = document.getElementById('edit-question-form');
                form.action = `/survey/{{ $survey->id }}/questions/${questionId}`;
                // Form action set
                
                // Handle options if they exist
                const optionsContainer = questionElement.querySelector('.flex.flex-wrap.gap-2');
                if (optionsContainer && ['multiple_choice', 'checkbox', 'dropdown'].includes(questionType)) {
                    const options = Array.from(optionsContainer.querySelectorAll('span')).map(span => span.textContent);
                    populateEditOptions(options);
                    document.getElementById('edit-options-container').classList.remove('hidden');
                } else {
                    document.getElementById('edit-options-container').classList.add('hidden');
                }
            }
        }
        
        function closeEditModal() {
            document.getElementById('edit-question-modal').classList.add('hidden');
            // Reset form
            document.getElementById('edit-question-form').reset();
            document.getElementById('edit-options-list').innerHTML = '';
        }
        
        function toggleEditOptionsField() {
            const questionType = document.getElementById('edit_question_type').value;
            const optionsContainer = document.getElementById('edit-options-container');
            
            if (['multiple_choice', 'checkbox', 'dropdown'].includes(questionType)) {
                optionsContainer.classList.remove('hidden');
                // Add default options if none exist
                if (document.getElementById('edit-options-list').children.length === 0) {
                    populateEditOptions(['Option 1', 'Option 2']);
                }
            } else {
                optionsContainer.classList.add('hidden');
            }
        }
        
        function populateEditOptions(options) {
            const optionsList = document.getElementById('edit-options-list');
            optionsList.innerHTML = '';
            
            options.forEach((option, index) => {
                const optionDiv = document.createElement('div');
                optionDiv.className = 'flex items-center';
                optionDiv.innerHTML = `
                    <div class="relative flex-grow">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <span class="material-icons text-[#004aad] text-xs">circle</span>
                        </div>
                        <input 
                            type="text" 
                            name="options[]" 
                            class="w-full text-xs border-gray-300 rounded-[1px] pl-8 focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50" 
                            value="${option}"
                            placeholder="Option ${index + 1}"
                        >
                    </div>
                    <button type="button" class="ml-2 text-gray-400 hover:text-red-600 edit-remove-option">
                        <span class="material-icons text-xs">close</span>
                    </button>
                `;
                
                optionsList.appendChild(optionDiv);
            });
            
            // Add event listeners for remove buttons
            const removeButtons = optionsList.querySelectorAll('.edit-remove-option');
            removeButtons.forEach(button => {
                button.addEventListener('click', function() {
                    button.parentElement.remove();
                });
            });
        }
        
        // Add option button for edit modal
        document.addEventListener('DOMContentLoaded', function() {
            const editAddOptionButton = document.getElementById('edit-add-option');
            const editOptionsList = document.getElementById('edit-options-list');
            
            if (editAddOptionButton) {
                editAddOptionButton.addEventListener('click', function() {
                    const optionCount = editOptionsList.children.length + 1;
                    
                    const optionDiv = document.createElement('div');
                    optionDiv.className = 'flex items-center';
                    optionDiv.innerHTML = `
                        <div class="relative flex-grow">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <span class="material-icons text-[#004aad] text-xs">circle</span>
                            </div>
                            <input 
                                type="text" 
                                name="options[]" 
                                class="w-full text-xs border-gray-300 rounded-[1px] pl-8 focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50" 
                                placeholder="Option ${optionCount}"
                            >
                        </div>
                        <button type="button" class="ml-2 text-gray-400 hover:text-red-600 edit-remove-option">
                            <span class="material-icons text-xs">close</span>
                        </button>
                    `;
                    
                    editOptionsList.appendChild(optionDiv);
                    
                    // Add event listener for the new remove button
                    const removeButton = optionDiv.querySelector('.edit-remove-option');
                    removeButton.addEventListener('click', function() {
                        optionDiv.remove();
                    });
                });
            }
            
            // Add form submission debugging
            const editForm = document.getElementById('edit-question-form');
            if (editForm) {
                editForm.addEventListener('submit', function(e) {
                    // Form submitted
                });
            }
        });
    </script>
</x-app-layout>
