{{-- Survey settings. Expects: $survey, $events --}}

<form method="POST" action="{{ route('survey.update', $survey) }}" class="space-y-3">
    @csrf
    @method('PUT')

    <div class="border border-gray-200 rounded">
        <div class="bg-gray-50 border-b border-gray-200 px-4 py-3">
            <h2 class="text-sm font-semibold text-gray-700 flex items-center">
                <span class="material-icons-outlined text-primary-DEFAULT mr-2">info</span>
                Basic information
            </h2>
        </div>

        <div class="p-4 grid grid-cols-1 md:grid-cols-[11rem_1fr] gap-x-4 gap-y-4 md:items-center">
            <label for="title" class="text-xs font-medium text-gray-700">
                Survey title <span class="text-red-500">*</span>
            </label>
            <div>
                <input type="text" name="title" id="title" required
                       value="{{ old('title', $survey->title) }}"
                       class="w-full h-9 text-xs border-gray-300 rounded-[1px] px-3 focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50">
            </div>

            <label for="description" class="text-xs font-medium text-gray-700 md:self-start md:pt-2">Description</label>
            <div>
                <textarea name="description" id="description" rows="3"
                          placeholder="Shown to respondents above the first question"
                          class="w-full text-xs border-gray-300 rounded-[1px] px-3 py-2 focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50">{{ old('description', $survey->description) }}</textarea>
            </div>

            <label for="event_id" class="text-xs font-medium text-gray-700">Linked event</label>
            <div>
                <select name="event_id" id="event_id"
                        class="w-full h-9 text-xs border-gray-300 rounded-[1px] px-3 focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50 leading-[1rem]">
                    <option value="">-- Not linked to an event --</option>
                    @foreach($events as $event)
                        <option value="{{ $event->id }}" {{ (int) old('event_id', $survey->event_id) === $event->id ? 'selected' : '' }}>
                            {{ $event->name }}
                        </option>
                    @endforeach
                </select>

                @error('event_id')
                    <p class="text-[11px] text-red-600 mt-1.5">{{ $message }}</p>
                @enderror

                <p class="text-[11px] text-gray-500 mt-1.5">
                    An event can only have one survey.
                    @if(($linkedElsewhere ?? 0) > 0)
                        {{ $linkedElsewhere === 1
                            ? '1 event is not listed because a survey is already attached.'
                            : $linkedElsewhere . ' events are not listed because a survey is already attached.' }}
                    @endif
                </p>
            </div>
        </div>
    </div>

    <div class="border border-gray-200 rounded">
        <div class="bg-gray-50 border-b border-gray-200 px-4 py-3">
            <h2 class="text-sm font-semibold text-gray-700 flex items-center">
                <span class="material-icons-outlined text-primary-DEFAULT mr-2">group</span>
                Who can answer
            </h2>
        </div>

        {{--
            Radios and checkboxes share one control class so their boxes line up on
            the same left edge. Hidden companion inputs sit outside the labels:
            inside a flex container they would become flex items.
        --}}
        @php
            $controlClass = 'h-4 w-4 shrink-0 mt-[1px] border-gray-300 text-primary-DEFAULT focus:ring-primary-light focus:ring-offset-0';
            $audience = old('audience', $survey->audience);
        @endphp

        <div class="p-4 grid grid-cols-1 md:grid-cols-[11rem_1fr] gap-x-4 gap-y-5 md:items-start">
            <span class="text-xs font-medium text-gray-700 md:pt-[1px]">Audience</span>

            <div class="space-y-3">
                    <label class="flex items-start gap-2.5 cursor-pointer">
                        <input type="radio" name="audience" value="{{ \App\Models\Survey::AUDIENCE_ANYONE }}"
                               {{ $audience === \App\Models\Survey::AUDIENCE_ANYONE ? 'checked' : '' }}
                               class="{{ $controlClass }}">
                        <span class="min-w-0">
                            <span class="block text-xs text-gray-700 leading-4">Anyone with the link</span>
                            <span class="block text-[11px] text-gray-500 leading-4 mt-0.5">Good for QR codes and open feedback. Responses are anonymous unless you ask for details below.</span>
                        </span>
                    </label>

                    <label class="flex items-start gap-2.5 cursor-pointer">
                        <input type="radio" name="audience" value="{{ \App\Models\Survey::AUDIENCE_PARTICIPANTS }}"
                               {{ $audience === \App\Models\Survey::AUDIENCE_PARTICIPANTS ? 'checked' : '' }}
                               class="{{ $controlClass }}">
                        <span class="min-w-0">
                            <span class="block text-xs text-gray-700 leading-4">Registered participants of the linked event only</span>
                            <span class="block text-[11px] text-gray-500 leading-4 mt-0.5">Respondents confirm their email or IC/passport first, so every answer is tied to a participant. Requires a linked event.</span>
                        </span>
                    </label>
            </div>

            <input type="hidden" name="require_respondent_details" value="0">
            <input type="hidden" name="allow_multiple_responses" value="0">

            <span class="text-xs font-medium text-gray-700 md:pt-[1px]">Options</span>

            <div class="space-y-3">
                    <label class="flex items-start gap-2.5 cursor-pointer">
                        <input type="checkbox" name="require_respondent_details" value="1"
                               {{ old('require_respondent_details', $survey->require_respondent_details) ? 'checked' : '' }}
                               class="{{ $controlClass }} rounded">
                        <span class="min-w-0">
                            <span class="block text-xs text-gray-700 leading-4">Ask for name and email before the questions</span>
                            <span class="block text-[11px] text-gray-500 leading-4 mt-0.5">Ignored for participants, whose details already come from the event registration.</span>
                        </span>
                    </label>

                    <label class="flex items-start gap-2.5 cursor-pointer">
                        <input type="checkbox" name="allow_multiple_responses" value="1"
                               {{ old('allow_multiple_responses', $survey->allow_multiple_responses) ? 'checked' : '' }}
                               class="{{ $controlClass }} rounded">
                        <span class="min-w-0">
                            <span class="block text-xs text-gray-700 leading-4">Allow the same person to respond more than once</span>
                            <span class="block text-[11px] text-gray-500 leading-4 mt-0.5">Off means one response per participant, or one per browser for anonymous respondents.</span>
                        </span>
                    </label>
            </div>
        </div>
    </div>

    <div class="border border-gray-200 rounded">
        <div class="bg-gray-50 border-b border-gray-200 px-4 py-3">
            <h2 class="text-sm font-semibold text-gray-700 flex items-center">
                <span class="material-icons-outlined text-primary-DEFAULT mr-2">schedule</span>
                Response window
            </h2>
        </div>

        <div class="p-4 grid grid-cols-1 md:grid-cols-[11rem_1fr] gap-x-4 gap-y-4 md:items-start">
            <label for="opens_at" class="text-xs font-medium text-gray-700 md:pt-2">Opens at</label>
            <div>
                <input type="datetime-local" name="opens_at" id="opens_at"
                       value="{{ old('opens_at', $survey->opens_at?->format('Y-m-d\TH:i')) }}"
                       class="w-full h-9 text-xs border-gray-300 rounded-[1px] px-3 focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50">
                <p class="text-[11px] text-gray-500 mt-1.5">Leave empty to accept responses as soon as the survey is published.</p>
            </div>

            <label for="expires_at" class="text-xs font-medium text-gray-700 md:pt-2">Closes at</label>
            <div>
                <input type="datetime-local" name="expires_at" id="expires_at"
                       value="{{ old('expires_at', $survey->expires_at?->format('Y-m-d\TH:i')) }}"
                       class="w-full h-9 text-xs border-gray-300 rounded-[1px] px-3 focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50">
                <p class="text-[11px] text-gray-500 mt-1.5">Leave empty to keep it open until you unpublish it.</p>
            </div>
        </div>
    </div>

    <div class="flex justify-between items-center pt-1">
        @can('surveys.delete')
        <button type="button"
                onclick="if (confirm('Delete this survey? Questions and responses are kept and can be restored.')) { document.getElementById('delete-survey-form').submit(); }"
                class="bg-gradient-to-r from-red-600 to-red-500 hover:from-red-700 hover:to-red-600 text-white px-3 h-[36px] rounded shadow-sm font-medium flex items-center text-xs transition-colors duration-200 ease-in-out">
            <span class="material-icons-outlined text-xs mr-1">delete</span>
            Delete survey
        </button>
        @else
        <span></span>
        @endcan

        <button type="submit"
                class="bg-gradient-to-r from-green-600 to-green-500 hover:from-green-700 hover:to-green-600 text-white px-3 h-[36px] rounded shadow-sm font-medium flex items-center text-xs transition-colors duration-200 ease-in-out">
            <span class="material-icons-outlined text-xs mr-1">save</span>
            Save settings
        </button>
    </div>
</form>

@can('surveys.delete')
<form id="delete-survey-form" method="POST" action="{{ route('survey.destroy', $survey) }}" class="hidden">
    @csrf
    @method('DELETE')
</form>
@endcan
