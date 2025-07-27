<x-app-layout>
    <x-slot name="breadcrumb">
        <span>Survey</span>
        <span class="mx-2 text-gray-500">/</span>
        <span>Create</span>
    </x-slot>

    <x-slot name="title">Create Survey</x-slot>

    <div class="bg-white rounded shadow-md border border-gray-300">
        <div class="p-6 border-b border-gray-200">
            <div class="flex items-center">
                <span class="material-icons mr-2 text-primary-DEFAULT">quiz</span>
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

            <form method="POST" action="{{ route('survey.store') }}" class="space-y-6">
                @csrf
                
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
                                value="{{ old('title') }}" 
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
                            >{{ old('description') }}</textarea>
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
                                class="w-full text-xs border-gray-300 rounded-[1px] pl-12 focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50" 
                                required
                            >
                                <option value="public" {{ old('access_type') == 'public' ? 'selected' : '' }}>Public - Anyone with the link can access</option>
                                <option value="private" {{ old('access_type') == 'private' ? 'selected' : '' }}>Private - Only authenticated users can access</option>
                                <option value="registered" {{ old('access_type') == 'registered' ? 'selected' : '' }}>Registered - Only registered participants can access</option>
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
                                {{ old('allow_anonymous') ? 'checked' : '' }}
                                class="rounded border-gray-300 text-primary-DEFAULT shadow-sm focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50"
                            >
                            <label for="allow_anonymous" class="ml-2 block text-xs text-gray-700">
                                Allow anonymous responses (respondents can submit without providing their information)
                            </label>
                        </div>
                    </div>
                </div>
                
                <!-- Related Event -->
                <div class="pb-5">
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
                                class="w-full text-xs border-gray-300 rounded-[1px] pl-12 focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50"
                            >
                                <option value="">-- No event --</option>
                                @foreach($events as $event)
                                    <option value="{{ $event->id }}" {{ old('event_id') == $event->id ? 'selected' : '' }}>
                                        {{ $event->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <p class="mt-1 text-[10px] text-gray-500">Optionally link this survey to an event</p>
                    </div>
                    
                    <div class="bg-blue-50 border border-blue-100 rounded-md p-4">
                        <div class="flex items-start">
                            <span class="material-icons text-blue-700 mr-2">info</span>
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
                
                <div class="border-t border-gray-200 pt-4 mt-6 flex justify-end space-x-3">
                    <a 
                        href="{{ route('survey.index') }}" 
                        class="px-3 py-1 bg-gradient-to-r from-red-600 to-red-500 hover:from-red-700 hover:to-red-600 text-white rounded shadow-sm text-xs font-medium transition-colors duration-200 ease-in-out flex items-center"
                    >
                        <span class="material-icons text-xs mr-1">cancel</span>
                        Cancel
                    </a>
                    <button 
                        type="submit" 
                        class="px-3 py-1 bg-gradient-to-r from-blue-600 to-blue-500 hover:from-blue-700 hover:to-blue-600 text-white rounded shadow-sm text-xs font-medium transition-colors duration-200 ease-in-out flex items-center"
                    >
                        <span class="material-icons text-xs mr-1">save</span>
                        Create Survey
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
