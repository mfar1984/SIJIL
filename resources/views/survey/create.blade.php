<x-app-layout>
    <x-slot name="breadcrumb">
        <a href="{{ route('survey.index') }}" class="text-primary-light">Survey</a>
        <span class="mx-2 text-gray-500">/</span>
        <span>New</span>
    </x-slot>

    <x-slot name="title">New Survey</x-slot>

    <div class="bg-white rounded shadow-md border border-gray-300">
        <div class="p-6 border-b border-gray-200">
            <div class="flex items-center">
                <span class="material-icons-outlined mr-2 text-primary-DEFAULT">poll</span>
                <h1 class="text-xl font-bold text-gray-800">New Survey</h1>
            </div>
            <p class="text-xs text-gray-500 mt-1 ml-8">
                Give it a name to get started. You add questions and configure the rest in the next step.
            </p>
        </div>

        <div class="p-4">
            @if($errors->any())
                <div class="bg-red-50 border border-red-200 text-red-700 px-3 py-2 rounded mb-3">
                    <ul class="list-disc pl-4 space-y-0.5">
                        @foreach($errors->all() as $error)
                            <li class="text-xs">{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('survey.store') }}" class="space-y-3">
                @csrf

                {{--
                    One grid for every label/control pair, so all controls share the
                    exact same left edge regardless of row height.
                --}}
                <div class="border border-gray-200 rounded">
                    <div class="p-4 grid grid-cols-1 md:grid-cols-[10rem_1fr] gap-x-4 gap-y-4 md:items-center">
                        <label for="title" class="text-xs font-medium text-gray-700">
                            Survey title <span class="text-red-500">*</span>
                        </label>
                        <div>
                            <input type="text" name="title" id="title" required autofocus
                                   value="{{ old('title') }}"
                                   placeholder="e.g. Post-event feedback"
                                   class="w-full h-9 text-xs border-gray-300 rounded-[1px] px-3 focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50">
                        </div>

                        <label for="event_id" class="text-xs font-medium text-gray-700 md:self-start md:pt-2">Linked event</label>
                        <div>
                            <select name="event_id" id="event_id"
                                    class="w-full h-9 text-xs border-gray-300 rounded-[1px] px-3 focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50 leading-[1rem]">
                                <option value="">-- Not linked to an event --</option>
                                @foreach($events as $event)
                                    <option value="{{ $event->id }}" {{ (int) old('event_id') === $event->id ? 'selected' : '' }}>
                                        {{ $event->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('event_id')
                                <p class="text-[11px] text-red-600 mt-1.5">{{ $message }}</p>
                            @enderror

                            <p class="text-[11px] text-gray-500 mt-1.5">
                                Link an event if you want to limit the survey to its registered participants.
                                An event can only have one survey.
                            </p>

                            @if(($linkedElsewhere ?? 0) > 0)
                                <p class="text-[11px] text-gray-500 mt-1">
                                    {{ $linkedElsewhere === 1
                                        ? '1 event is not listed because a survey is already attached.'
                                        : $linkedElsewhere . ' events are not listed because a survey is already attached.' }}
                                </p>
                            @endif
                        </div>

                        <label for="description" class="text-xs font-medium text-gray-700 md:self-start md:pt-2">Description</label>
                        <div>
                            <textarea name="description" id="description" rows="3"
                                      placeholder="Optional. Shown to respondents above the first question."
                                      class="w-full text-xs border-gray-300 rounded-[1px] px-3 py-2 focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50">{{ old('description') }}</textarea>
                        </div>
                    </div>
                </div>

                <div class="flex justify-end gap-2">
                    <a href="{{ route('survey.index') }}"
                       class="bg-gradient-to-r from-gray-500 to-gray-400 hover:from-gray-600 hover:to-gray-500 text-white px-3 h-[36px] rounded shadow-sm font-medium flex items-center text-xs transition-colors duration-200 ease-in-out">
                        Cancel
                    </a>
                    <button type="submit"
                            class="bg-gradient-to-r from-blue-600 to-blue-500 hover:from-blue-700 hover:to-blue-600 text-white px-3 h-[36px] rounded shadow-sm font-medium flex items-center text-xs transition-colors duration-200 ease-in-out">
                        <span class="material-icons-outlined text-xs mr-1">arrow_forward</span>
                        Create and add questions
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
