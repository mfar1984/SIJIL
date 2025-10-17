@props(['messages'])

@if ($messages)
    <ul {{ $attributes->merge(['class' => 'text-[12px] text-red-600 space-y-1 mt-1']) }}>
        @foreach ((array) $messages as $message)
            @php
                $msg = $message;
                $msg = str_replace('Your account is banned. Please contact support.', 'Your account is banned. Please contact support.', $msg);
                $msg = str_replace('Your account is inactive. Please contact support.', 'Your account is inactive. Please contact support.', $msg);
            @endphp
            <li>
                {!! str_replace('Please contact support.', 'Please contact <a href="mailto:admin@e-certificate.com.my" class="underline">support</a>.', e($msg)) !!}
            </li>
        @endforeach
    </ul>
@endif
