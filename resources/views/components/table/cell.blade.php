@props(['align' => 'left'])

<td {{ $attributes->merge(['class' => 'px-4 py-3 text-sm text-' . $align]) }}>
    {{ $slot }}
</td> 