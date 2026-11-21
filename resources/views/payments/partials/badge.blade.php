{{-- Small coloured label. $tone: green, amber, red, blue, gray, purple. --}}
@php
    $toneClasses = [
        'green' => 'bg-green-100 text-green-800',
        'amber' => 'bg-amber-100 text-amber-800',
        'red' => 'bg-red-100 text-red-800',
        'blue' => 'bg-blue-100 text-blue-800',
        'purple' => 'bg-purple-100 text-purple-800',
        'gray' => 'bg-gray-100 text-gray-700',
    ];
@endphp
<span class="inline-block px-2 py-0.5 rounded-full text-[11px] font-medium whitespace-nowrap {{ $toneClasses[$tone ?? 'gray'] ?? $toneClasses['gray'] }}">{{ $label }}</span>
