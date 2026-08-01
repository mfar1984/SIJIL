{{--
    Question editor used for both adding and editing.

    Expects: $action (form action), $method ('POST' or 'PUT'), $submitLabel,
             $question (SurveyQuestion|null), $questionTypes
--}}
@php
    $q = $question ?? null;
    $initialType = old('question_type', $q->question_type ?? 'multiple_choice');
    $initialOptions = old('options', $q && $q->needsOptions() ? ($q->options ?: ['']) : ['', '']);
    $optionTypes = \App\Models\SurveyQuestion::OPTION_TYPES;
@endphp

<form method="POST" action="{{ $action }}"
      x-data="{
          type: @js($initialType),
          options: @js(array_values((array) $initialOptions)),
          get needsOptions() { return @js($optionTypes).includes(this.type) },
          get isScale() { return this.type === 'rating' },
          addOption() { this.options.push('') },
          removeOption(i) { if (this.options.length > 1) this.options.splice(i, 1) }
      }"
      class="space-y-4">
    @csrf
    @if($method === 'PUT')
        @method('PUT')
    @endif

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="md:col-span-2">
            <label class="block text-xs font-medium text-gray-700 mb-1.5">
                Question <span class="text-red-500">*</span>
            </label>
            <input type="text" name="question_text" required
                   value="{{ old('question_text', $q->question_text ?? '') }}"
                   placeholder="e.g. How would you rate the event overall?"
                   class="w-full h-9 text-xs border-gray-300 rounded-[1px] px-3 focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50">
        </div>

        <div>
            <label class="block text-xs font-medium text-gray-700 mb-1.5">Type</label>
            <select name="question_type" x-model="type"
                    class="w-full h-9 text-xs border-gray-300 rounded-[1px] px-3 focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50">
                @foreach($questionTypes as $value => $label)
                    <option value="{{ $value }}">{{ $label }}</option>
                @endforeach
            </select>
        </div>
    </div>

    <div>
        <label class="block text-xs font-medium text-gray-700 mb-1.5">Helper text</label>
        <input type="text" name="description"
               value="{{ old('description', $q->description ?? '') }}"
               placeholder="Optional guidance shown under the question"
               class="w-full h-9 text-xs border-gray-300 rounded-[1px] px-3 focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50">
    </div>

    {{-- Options list, shown only for choice based types --}}
    <div x-show="needsOptions" x-cloak>
        <label class="block text-xs font-medium text-gray-700 mb-2">Options</label>

        <template x-for="(option, index) in options" :key="index">
            <div class="flex items-center gap-2.5 mb-2.5">
                <span class="material-icons-outlined text-gray-300 text-base"
                      x-text="type === 'checkbox' ? 'check_box_outline_blank' : 'radio_button_unchecked'"></span>
                <input type="text" name="options[]" x-model="options[index]"
                       :placeholder="'Option ' + (index + 1)"
                       class="flex-1 h-9 text-xs border-gray-300 rounded-[1px] px-3 focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50">
                <button type="button" @click="removeOption(index)"
                        class="p-1 rounded hover:bg-red-50 border border-transparent hover:border-red-100"
                        title="Remove option">
                    <span class="material-icons-outlined text-red-500 text-xs">close</span>
                </button>
            </div>
        </template>

        <button type="button" @click="addOption()"
                class="inline-flex items-center text-xs text-primary-DEFAULT hover:underline">
            <span class="material-icons-outlined text-xs mr-1">add</span>
            Add option
        </button>
    </div>

    {{-- Linear scale settings --}}
    <div x-show="isScale" x-cloak class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div>
            <label class="block text-xs font-medium text-gray-700 mb-1.5">From</label>
            <select name="scale_min"
                    class="w-full h-9 text-xs border-gray-300 rounded-[1px] px-3 focus:border-primary-light focus:ring focus:ring-primary-light">
                @foreach([0, 1] as $v)
                    <option value="{{ $v }}" {{ (int) old('scale_min', $q->scale_min ?? 1) === $v ? 'selected' : '' }}>{{ $v }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="block text-xs font-medium text-gray-700 mb-1.5">To</label>
            <select name="scale_max"
                    class="w-full h-9 text-xs border-gray-300 rounded-[1px] px-3 focus:border-primary-light focus:ring focus:ring-primary-light">
                @for($v = 2; $v <= 10; $v++)
                    <option value="{{ $v }}" {{ (int) old('scale_max', $q->scale_max ?? 5) === $v ? 'selected' : '' }}>{{ $v }}</option>
                @endfor
            </select>
        </div>

        <div>
            <label class="block text-xs font-medium text-gray-700 mb-1.5">Label for lowest</label>
            <input type="text" name="scale_min_label" value="{{ old('scale_min_label', $q->scale_min_label ?? '') }}"
                   placeholder="e.g. Very poor"
                   class="w-full h-9 text-xs border-gray-300 rounded-[1px] px-3 focus:border-primary-light focus:ring focus:ring-primary-light">
        </div>

        <div>
            <label class="block text-xs font-medium text-gray-700 mb-1.5">Label for highest</label>
            <input type="text" name="scale_max_label" value="{{ old('scale_max_label', $q->scale_max_label ?? '') }}"
                   placeholder="e.g. Excellent"
                   class="w-full h-9 text-xs border-gray-300 rounded-[1px] px-3 focus:border-primary-light focus:ring focus:ring-primary-light">
        </div>
    </div>

    <div class="flex items-center justify-between pt-3 mt-1 border-t border-gray-200">
        <label class="flex items-center gap-2.5 cursor-pointer">
            <input type="checkbox" name="required" value="1"
                   {{ old('required', $q->required ?? false) ? 'checked' : '' }}
                   class="h-4 w-4 shrink-0 rounded border-gray-300 text-primary-DEFAULT focus:ring-primary-light focus:ring-offset-0">
            <span class="text-xs text-gray-700 leading-4">Answer required</span>
        </label>

        <div class="flex items-center gap-2">
            {{ $slot ?? '' }}
            <button type="submit"
                    class="bg-gradient-to-r from-blue-600 to-blue-500 hover:from-blue-700 hover:to-blue-600 text-white px-3 h-[36px] rounded shadow-sm font-medium flex items-center text-xs transition-colors duration-200 ease-in-out">
                <span class="material-icons-outlined text-xs mr-1">save</span>
                {{ $submitLabel }}
            </button>
        </div>
    </div>
</form>
