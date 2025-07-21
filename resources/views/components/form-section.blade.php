@props(['submit'])

<div {{ $attributes->merge(['class' => 'mt-5 md:mt-0']) }}>
    <div class="bg-white shadow-xl rounded-lg overflow-hidden">
        <div class="px-4 py-3 bg-gradient-to-r from-slate-600 to-slate-700 text-white">
            <h3 class="text-base font-medium">{{ $title }}</h3>
        </div>

        <form wire:submit="{{ $submit }}">
            <div class="p-6">
                <div class="grid grid-cols-6 gap-6">
                    {{ $form }}
                </div>
            </div>

            @if (isset($actions))
                <div class="px-4 py-3 bg-gray-50 text-right sm:px-6 border-t border-gray-100">
                    {{ $actions }}
                </div>
            @endif
        </form>
    </div>
</div> 