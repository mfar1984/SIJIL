@props([
    'name',
    'label',
    'description',
    'icon' => 'tune',
    'checked' => false,
    'note' => null,
])

{{--
    One switch on the event form.

    Five bare checkboxes in a column read as a wall of jargon, so each one is a
    card that says what it does in plain words. The border lights up when it is
    on, which makes the enabled set readable at a glance.
--}}
{{-- h-full keeps every card in a row the same height even when the notes differ
     in length, so the grid does not look ragged. --}}
<label x-data="{ on: {{ $checked ? 'true' : 'false' }} }"
       class="flex h-full border rounded p-3 cursor-pointer transition-colors"
       :class="on ? 'border-primary-DEFAULT bg-primary-DEFAULT/5' : 'border-gray-200 hover:border-gray-300'">
    <div class="flex items-start gap-2 w-full">
        <input type="checkbox"
               name="{{ $name }}"
               id="{{ $name }}"
               value="1"
               x-model="on"
               class="mt-0.5 shrink-0 rounded border-gray-300 text-primary-DEFAULT focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50">

        <span class="material-icons-outlined text-base mt-0.5 shrink-0"
              :class="on ? 'text-primary-DEFAULT' : 'text-gray-400'">{{ $icon }}</span>

        <span class="min-w-0">
            <span class="block text-xs font-medium text-gray-800">{{ $label }}</span>
            <span class="block text-xs text-gray-500 mt-0.5">{{ $description }}</span>

            @if($note)
                <span class="block text-xs text-gray-400 mt-1">{{ $note }}</span>
            @endif

            {{-- Extra controls that only make sense while this switch is on.
                 Rendered as a div, so this span cannot stay inline-only. --}}
            @if(trim($slot) !== '')
                <span class="block">{{ $slot }}</span>
            @endif
        </span>
    </div>
</label>
