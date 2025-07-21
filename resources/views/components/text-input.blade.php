@props(['disabled' => false])

<input {{ $disabled ? 'disabled' : '' }} {!! $attributes->merge(['class' => 'border-gray-300 focus:border-slate-500 focus:ring-2 focus:ring-slate-500 rounded-lg shadow-sm px-3 py-2 text-sm']) !!}>
