{{--
    Renders the answer control for a single survey question.

    Expects: $question (SurveyQuestion), $field (input name)
--}}
@php
    $inputClass = 'w-full text-xs border-gray-300 rounded px-3 py-2 focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50';
@endphp

@switch($question->question_type)
    @case('textarea')
        <textarea name="{{ $field }}" rows="4" class="{{ $inputClass }}"
                  {{ $question->required ? 'required' : '' }}>{{ old($field) }}</textarea>
        @break

    @case('multiple_choice')
        <div class="space-y-2">
            @foreach($question->options ?? [] as $index => $option)
                <label class="flex items-start gap-2.5 cursor-pointer">
                    <input type="radio" name="{{ $field }}" value="{{ $option }}"
                           class="h-4 w-4 shrink-0 mt-[1px] border-gray-300 text-primary-DEFAULT focus:ring-primary-light focus:ring-offset-0"
                           {{ old($field) === $option ? 'checked' : '' }}
                           {{ $question->required ? 'required' : '' }}>
                    <span class="text-xs text-gray-700 leading-4">{{ $option }}</span>
                </label>
            @endforeach
        </div>
        @break

    @case('checkbox')
        @php $checked = (array) old($field, []); @endphp
        <div class="space-y-2">
            @foreach($question->options ?? [] as $option)
                <label class="flex items-start gap-2.5 cursor-pointer">
                    <input type="checkbox" name="{{ $field }}[]" value="{{ $option }}"
                           class="h-4 w-4 shrink-0 mt-[1px] rounded border-gray-300 text-primary-DEFAULT focus:ring-primary-light focus:ring-offset-0"
                           {{ in_array($option, $checked, true) ? 'checked' : '' }}>
                    <span class="text-xs text-gray-700 leading-4">{{ $option }}</span>
                </label>
            @endforeach
        </div>
        @break

    @case('dropdown')
        <select name="{{ $field }}" class="{{ $inputClass }}" {{ $question->required ? 'required' : '' }}>
            <option value="">-- Select an option --</option>
            @foreach($question->options ?? [] as $option)
                <option value="{{ $option }}" {{ old($field) === $option ? 'selected' : '' }}>{{ $option }}</option>
            @endforeach
        </select>
        @break

    @case('rating')
        @php $values = $question->scaleValues(); @endphp
        <div class="flex items-center gap-3">
            @if($question->scale_min_label)
                <span class="text-[11px] text-gray-500 max-w-[90px] text-right">{{ $question->scale_min_label }}</span>
            @endif

            <div class="flex items-center gap-4">
                @foreach($values as $value)
                    <label class="flex flex-col items-center gap-1 cursor-pointer">
                        <input type="radio" name="{{ $field }}" value="{{ $value }}"
                               class="h-4 w-4 border-gray-300 text-primary-DEFAULT focus:ring-primary-light"
                               {{ (string) old($field) === (string) $value ? 'checked' : '' }}
                               {{ $question->required ? 'required' : '' }}>
                        <span class="text-xs text-gray-600">{{ $value }}</span>
                    </label>
                @endforeach
            </div>

            @if($question->scale_max_label)
                <span class="text-[11px] text-gray-500 max-w-[90px]">{{ $question->scale_max_label }}</span>
            @endif
        </div>
        @break

    @case('date')
        <input type="date" name="{{ $field }}" value="{{ old($field) }}" class="{{ $inputClass }}"
               {{ $question->required ? 'required' : '' }}>
        @break

    @case('email')
        <input type="email" name="{{ $field }}" value="{{ old($field) }}" class="{{ $inputClass }}"
               placeholder="name@example.com" {{ $question->required ? 'required' : '' }}>
        @break

    @case('number')
        <input type="number" step="any" name="{{ $field }}" value="{{ old($field) }}" class="{{ $inputClass }}"
               {{ $question->required ? 'required' : '' }}>
        @break

    @default
        <input type="text" name="{{ $field }}" value="{{ old($field) }}" class="{{ $inputClass }}"
               {{ $question->required ? 'required' : '' }}>
@endswitch
