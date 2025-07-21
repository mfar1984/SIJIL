@props(['header' => null, 'footer' => null])

<div {{ $attributes->merge(['class' => 'bg-white shadow-xl rounded-lg border border-gray-100 overflow-hidden']) }}>
    @if ($header)
        <div class="px-4 py-3 bg-gradient-to-r from-slate-600 to-slate-700 text-white">
            {{ $header }}
        </div>
    @endif

    <div class="p-4">
        {{ $slot }}
    </div>

    @if ($footer)
        <div class="px-4 py-3 bg-gray-50 border-t border-gray-100">
            {{ $footer }}
        </div>
    @endif
</div> 