@props(['disabled' => false])
 
<select {{ $disabled ? 'disabled' : '' }} {!! $attributes->merge(['class' => 'border-gray-300 focus:border-slate-500 focus:ring-2 focus:ring-slate-500 rounded-lg shadow-sm px-3 py-2 text-sm appearance-none bg-white bg-no-repeat bg-right']) !!} style="background-image: url('data:image/svg+xml;charset=UTF-8,%3csvg xmlns=\'http://www.w3.org/2000/svg\' viewBox=\'0 0 24 24\' fill=\'none\' stroke=\'currentColor\' stroke-width=\'2\' stroke-linecap=\'round\' stroke-linejoin=\'round\'%3e%3cpolyline points=\'6 9 12 15 18 9\'%3e%3c/polyline%3e%3c/svg%3e'); background-size: 1em;">
    {{ $slot }}
</select> 