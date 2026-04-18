@props([
    'type' => 'text',
    'name' => '',
    'id' => '',
    'label' => '',
    'icon' => '', // Material icon name
    'required' => false,
    'autocomplete' => '',
    'value' => '',
    'placeholder' => '',
])
@php
    $inputId = $id ?: $name;
@endphp
<div class="mb-3">
    @if($label)
        <label for="{{ $inputId }}" class="block mb-1 text-xs font-semibold text-blue-700">
            {{ $label }}@if($required)*@endif
        </label>
    @endif
    <div class="flex items-center border border-gray-400 rounded-[3px] px-3 py-2 transition">
        @if($icon)
            <span class="material-icons-outlined text-xl flex items-center justify-center" style="color: #2c61b6;">{{ $icon }}</span>
        @endif
        <input
            type="{{ $type }}"
            name="{{ $name }}"
            id="{{ $inputId }}"
            @if($required) required @endif
            @if($autocomplete) autocomplete="{{ $autocomplete }}" @endif
            value="{{ old($name, $value) }}"
            class="flex-1 bg-transparent border-none outline-none text-xs text-gray-800 ml-3 py-0 focus:outline-none focus:ring-0 focus:border-gray-400"
        />
    </div>
</div> 