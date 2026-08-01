@props([
    'name',
    'label',
    'value' => null,
    'type' => 'text',
    'placeholder' => null,
    'help' => null,
    'required' => false,
])

{{--
    One field on the delivery form.

    The same ten lines of markup were repeated for every credential on the page,
    each with a decorative icon sitting inside the input. The icons ate the left
    third of an already narrow field and told the reader nothing the label had not
    already said, so the input is plain and the label carries the meaning.
--}}
<div>
    <label for="{{ $name }}" class="block text-xs font-medium text-gray-700 mb-1">
        {{ $label }}
        @if($required)
            <span class="text-red-500">*</span>
        @endif
    </label>

    <input type="{{ $type }}"
           name="{{ $name }}"
           id="{{ $name }}"
           value="{{ old($name, $value) }}"
           @if($placeholder) placeholder="{{ $placeholder }}" @endif
           {{-- Stops the browser offering the account's own login password here. --}}
           @if($type === 'password') autocomplete="new-password" @endif
           {{ $attributes->merge([
               'class' => 'w-full h-9 text-xs border-gray-300 rounded-[1px] px-3 focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50',
           ]) }}>

    @error($name)
        <p class="text-[11px] text-red-600 mt-1">{{ $message }}</p>
    @enderror

    @if($help)
        <p class="text-[11px] text-gray-500 mt-1">{{ $help }}</p>
    @endif
</div>
