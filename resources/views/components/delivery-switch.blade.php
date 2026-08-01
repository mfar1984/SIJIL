@props([
    'label',
    'description',
    'checked' => false,
])

{{--
    The on/off switch for a delivery channel.

    This replaced a page-wide "Edit Settings" button that put every field in a
    disabled state and then re-enabled them with JavaScript on submit. That told
    the reader nothing about whether the channel actually sends - which is the one
    thing they came here to control - so the switch answers that directly and the
    fields are simply always editable.

    Expects the surrounding form to declare x-data with an `enabled` property.
--}}
<div class="border rounded p-4 transition-colors"
     :class="enabled ? 'border-primary-DEFAULT bg-primary-DEFAULT/5' : 'border-gray-200 bg-gray-50'">
    <div class="flex items-start justify-between gap-4">
        <div class="min-w-0">
            <p class="text-xs font-medium text-gray-800">{{ $label }}</p>
            <p class="text-xs text-gray-500 mt-0.5">{{ $description }}</p>

            @if(trim($slot) !== '')
                <div class="text-[11px] text-gray-500 mt-1.5">{{ $slot }}</div>
            @endif
        </div>

        <label class="relative inline-flex items-center cursor-pointer shrink-0">
            {{-- Unchecked boxes are not submitted, so the hidden field carries the
                 "off" answer. The checkbox is declared after it and wins when ticked. --}}
            <input type="hidden" name="is_enabled" value="0">

            <input type="checkbox"
                   name="is_enabled"
                   value="1"
                   x-model="enabled"
                   {{ $checked ? 'checked' : '' }}
                   class="sr-only peer">

            <div class="w-9 h-5 bg-gray-200 peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-primary-light rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-primary-DEFAULT"></div>

            <span class="ml-2 text-xs font-medium w-6"
                  :class="enabled ? 'text-primary-DEFAULT' : 'text-gray-500'"
                  x-text="enabled ? 'On' : 'Off'"></span>
        </label>
    </div>
</div>
