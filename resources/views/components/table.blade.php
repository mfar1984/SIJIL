@props(['striped' => true])

<div class="bg-white rounded-lg shadow overflow-hidden">
    <div class="overflow-x-auto">
        <table {{ $attributes->merge(['class' => 'min-w-full divide-y divide-gray-200']) }}>
            @if (isset($thead))
                <thead class="bg-[#5170ff] text-white">
                    {{ $thead }}
                </thead>
            @endif

            <tbody class="bg-white divide-y divide-gray-200 text-sm">
                @if($striped)
                    {{ $slot }}
                @else
                    {{ $slot }}
                @endif
            </tbody>

            @if (isset($tfoot))
                <tfoot class="bg-gray-50 text-gray-600 text-sm">
                    {{ $tfoot }}
                </tfoot>
            @endif
        </table>
    </div>
</div> 