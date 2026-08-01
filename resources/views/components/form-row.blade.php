@props([
    'name' => null,
    'label',
    'required' => false,
    'help' => null,
    'top' => false,
])

{{--
    One label-and-control row inside a two-column form grid.

    The parent supplies the grid (md:grid-cols-[11rem_1fr]); this emits the label
    and a wrapper for the control, so a form is a flat list of rows instead of a
    nest of flex containers. Errors and help text sit under the control where the
    reader is already looking, which is why the old hover tooltips are gone: help
    that only appears on hover is invisible on touch and unreadable next to a
    field you are typing in.
--}}
<label @if($name) for="{{ $name }}" @endif
       class="text-xs font-medium text-gray-700 {{ $top ? 'md:self-start md:pt-2' : '' }}">
    {{ $label }}
    @if($required)
        <span class="text-red-500">*</span>
    @endif
</label>

<div>
    {{ $slot }}

    @if($name)
        @error($name)
            <p class="text-[11px] text-red-600 mt-1.5">{{ $message }}</p>
        @enderror
    @endif

    @if($help)
        <p class="text-[11px] text-gray-500 mt-1.5">{{ $help }}</p>
    @endif
</div>
