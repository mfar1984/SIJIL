<x-app-layout>
    <x-slot name="breadcrumb">
        <span>Survey</span>
        <span class="mx-2 text-gray-500">/</span>
        <span>Create</span>
    </x-slot>

    <x-slot name="title">Create Survey</x-slot>

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
            <div class="flex items-center">
                <span class="material-icons-outlined mr-2 text-primary-DEFAULT">quiz</span>
                <h1 class="text-xl font-bold text-gray-800">Create New Survey</h1>
            </div>
            <p class="text-xs text-gray-500 mt-1 ml-8">Create a new survey for feedback and data collection</p>
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

            <form method="POST" action="{{ route('survey.store') }}" class="space-y-2">
                @csrf
                
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
                                <label for="title" class="text-xs font-medium text-gray-700 md:w-40 flex items-center gap-1">
                                    Survey Title
                                    <div class="tooltip-wrapper" x-data="{ show: false }">
                                        <span class="material-icons-outlined text-gray-400 text-sm cursor-help" 
                                              @mouseenter="show = true" 
                                              @mouseleave="show = false">
                                            help_outline
                                        </span>
                                        <div x-show="show" x-transition class="tooltip-content">
                                            Enter a descriptive title for the survey
                                        </div>
                                    </div>
                                </label>
                                <div class="flex-1">
                                    <div class="relative">
                                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                            <span class="material-icons-outlined text-[#004aad] text-base">title</span>
                                        </div>
                                        <input 
                                            type="text" 
                                            name="title" 
                                            id="title" 
                                            class="w-full text-xs border-gray-300 rounded-[1px] pl-12 focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50" 
                                            value="{{ old('title') }}" 
                                            placeholder="e.g., Customer Satisfaction Survey"
                                            required
                                        >
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Description -->
                            <div class="flex flex-col md:flex-row md:items-start gap-3">
                                <label for="description" class="text-xs font-medium text-gray-700 md:w-40 pt-2 flex items-center gap-1">
                                    Description
                                    <div class="tooltip-wrapper" x-data="{ show: false }">
                                        <span class="material-icons-outlined text-gray-400 text-sm cursor-help" 
                                              @mouseenter="show = true" 
                                              @mouseleave="show = false">
                                            help_outline
                                        </span>
                                        <div x-show="show" x-transition class="tooltip-content">
                                            Provide a detailed description of the survey
                                        </div>
                                    </div>
                                </label>
                                <div class="flex-1">
                                    <textarea 
                                        name="description" 
                                        id="description" 
                                        rows="3" 
                                        class="w-full text-xs border-gray-300 rounded focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50"
                                        placeholder="Enter survey description here"
                                    >{{ old('description') }}</textarea>
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
                                <label for="access_type" class="text-xs font-medium text-gray-700 md:w-40 flex items-center gap-1">
                                    Access Type
                                    <div class="tooltip-wrapper" x-data="{ show: false }">
                                        <span class="material-icons-outlined text-gray-400 text-sm cursor-help" 
                                              @mouseenter="show = true" 
                                              @mouseleave="show = false">
                                            help_outline
                                        </span>
                                        <div x-show="show" x-transition class="tooltip-content">
                                            Control who can access and respond to this survey
                                        </div>
                                    </div>
                                </label>
                                <div class="flex-1">
                                    <div class="relative">
                                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                            <span class="material-icons-outlined text-[#004aad] text-base">shield</span>
                                        </div>
                                        <select 
                                            name="access_type" 
                                            id="access_type" 
                                            class="w-full h-9 text-xs border-gray-300 rounded-[1px] pl-12 focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50 leading-[1rem]" 
                                            required
                                        >
                                            <option value="public" {{ old('access_type') == 'public' ? 'selected' : '' }}>Public - Anyone with the link can access</option>
                                            <option value="private" {{ old('access_type') == 'private' ? 'selected' : '' }}>Private - Only authenticated users can access</option>
                                            <option value="registered" {{ old('access_type') == 'registered' ? 'selected' : '' }}>Registered - Only registered participants can access</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Allow Anonymous Responses -->
                            <div class="flex flex-col md:flex-row md:items-center gap-3">
                                <div class="md:w-40"></div>
                                <div class="flex-1">
                                    <label class="flex items-center gap-1">
                                        <input 
                                            type="checkbox" 
                                            name="allow_anonymous" 
                                            id="allow_anonymous" 
                                            value="1" 
                                            {{ old('allow_anonymous') ? 'checked' : '' }}
                                            class="rounded border-gray-300 text-primary-DEFAULT focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50"
                                        >
                                        <span class="ml-2 text-xs text-gray-700">Allow anonymous responses</span>
                                        <div class="tooltip-wrapper" x-data="{ show: false }">
                                            <span class="material-icons-outlined text-gray-400 text-sm cursor-help" 
                                                  @mouseenter="show = true" 
                                                  @mouseleave="show = false">
                                                help_outline
                                            </span>
                                            <div x-show="show" x-transition class="tooltip-content">
                                                Respondents can submit without providing information
                                            </div>
                                        </div>
                                    </label>
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
                            <!-- Link to Event -->
                            <div class="flex flex-col md:flex-row md:items-center gap-3">
                                <label for="event_id" class="text-xs font-medium text-gray-700 md:w-40 flex items-center gap-1">
                                    Link to Event
                                    <div class="tooltip-wrapper" x-data="{ show: false }">
                                        <span class="material-icons-outlined text-gray-400 text-sm cursor-help" 
                                              @mouseenter="show = true" 
                                              @mouseleave="show = false">
                                            help_outline
                                        </span>
                                        <div x-show="show" x-transition class="tooltip-content">
                                            Optionally link this survey to an event
                                        </div>
                                    </div>
                                </label>
                                <div class="flex-1">
                                    <div class="relative">
                                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                            <span class="material-icons-outlined text-[#004aad] text-base">event_note</span>
                                        </div>
                                        <select 
                                            name="event_id" 
                                            id="event_id" 
                                            class="w-full h-9 text-xs border-gray-300 rounded-[1px] pl-12 focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50 leading-[1rem]"
                                        >
                                            <option value="">-- No event --</option>
                                            @foreach($events as $event)
                                                <option value="{{ $event->id }}" {{ old('event_id') == $event->id ? 'selected' : '' }}>
                                                    {{ $event->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Info Box -->
                            <div class="flex flex-col md:flex-row md:items-start gap-3">
                                <div class="md:w-40"></div>
                                <div class="flex-1">
                                    <div class="bg-blue-50 border border-blue-100 rounded-md p-4">
                                        <div class="flex items-start">
                                            <span class="material-icons-outlined text-blue-700 mr-2">info</span>
                                            <div>
                                                <p class="text-xs font-medium text-blue-800">Getting Started</p>
                                                <ol class="list-decimal list-inside text-xs mt-2 space-y-1 text-blue-700">
                                                    <li>Create your survey by filling in the details</li>
                                                    <li>After saving, add questions to your survey</li>
                                                    <li>Preview your survey before publishing</li>
                                                    <li>Publish when ready to collect responses</li>
                                                </ol>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Form Actions -->
                <div class="flex justify-end space-x-3 pt-4">
                    <a 
                        href="{{ route('survey.index') }}" 
                        class="px-3 h-[36px] bg-gradient-to-r from-red-600 to-red-500 hover:from-red-700 hover:to-red-600 text-white rounded shadow-sm text-xs font-medium transition-colors duration-200 ease-in-out flex items-center"
                    >
                        <span class="material-icons-outlined text-xs mr-1">cancel</span>
                        Cancel
                    </a>
                    <button 
                        type="submit" 
                        class="px-3 h-[36px] bg-gradient-to-r from-blue-600 to-blue-500 hover:from-blue-700 hover:to-blue-600 text-white rounded shadow-sm text-xs font-medium transition-colors duration-200 ease-in-out flex items-center"
                    >
                        <span class="material-icons-outlined text-xs mr-1">save</span>
                        Create Survey
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
