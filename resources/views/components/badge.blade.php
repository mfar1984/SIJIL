@props(['type' => 'default'])

@php
$types = [
    'default' => 'bg-gray-100 text-gray-800',
    'active' => 'bg-[#4f90ff] text-[#1563e6]',
    'pending' => 'bg-[#ffedd5] text-[#c2410c]',
    'completed' => 'bg-[#dcfce7] text-[#15803d]',
    'inactive' => 'bg-[#fef2f2] text-[#dc2626]',
];

$classes = $types[$type] ?? $types['default'];
@endphp

<span {{ $attributes->merge(['class' => 'inline-flex items-center px-3 py-0.5 rounded-full text-[10px] font-medium ' . $classes]) }}>
    {{ $slot }}
</span> 