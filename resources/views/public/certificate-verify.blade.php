<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    @php
        $valid = $certificate !== null;
        $holder = $valid ? $certificate->participant->name : null;
        $eventName = $valid ? ($certificate->event->name ?? null) : null;
    @endphp

    <title>
        {{ $valid ? 'Verified: ' . $eventName : 'Verify a certificate' }} &middot;
        {{ config('app.name', 'e-Certificate') }}
    </title>

    {{-- A verification result is meant to be sent to someone and opened, not indexed.
         Letting search engines keep copies would put participant names in results
         long after an event, which nobody asked for. --}}
    <meta name="robots" content="noindex, nofollow">

    {{-- So a shared link previews sensibly in a chat app. --}}
    <meta property="og:title" content="{{ $valid ? 'Certificate verified' : 'Verify a certificate' }}">
    <meta property="og:description"
          content="{{ $valid ? $holder . ' — ' . $eventName : 'Check a certificate number against the issuing system.' }}">
    <meta property="og:type" content="website">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons+Outlined&display=block" rel="stylesheet">

    @vite(['resources/css/app.css'])
</head>
<body class="font-sans text-gray-900 antialiased bg-gray-100">
    <div class="min-h-screen py-8 px-4">
        <div class="max-w-xl mx-auto">
            <div class="text-center mb-6">
                <h1 class="text-lg font-semibold text-gray-800">Certificate verification</h1>
                <p class="text-xs text-gray-500 mt-1">
                    Checked against the {{ config('app.name', 'e-Certificate') }} record at the moment this page loaded.
                </p>
            </div>

            @if($number === null)
                {{-- Someone arrived without a number. --}}
                <div class="bg-white rounded shadow-sm border border-gray-200 p-6">
                    <form method="POST" action="{{ route('certificate.verify.lookup') }}" class="space-y-3">
                        @csrf

                        <label for="certificate_number" class="block text-xs font-medium text-gray-700">
                            Certificate number
                        </label>
                        <input type="text" name="certificate_number" id="certificate_number" required
                               value="{{ old('certificate_number') }}"
                               placeholder="CERT-00000000000000-XXXXXX"
                               class="w-full h-10 text-sm border-gray-300 rounded px-3 font-mono focus:border-primary-light focus:ring focus:ring-primary-light focus:ring-opacity-50">

                        @error('certificate_number')
                            <p class="text-[11px] text-red-600">{{ $message }}</p>
                        @enderror

                        <p class="text-[11px] text-gray-500">
                            The number is printed on the certificate itself.
                        </p>

                        <button type="submit"
                                class="h-10 px-4 w-full rounded text-sm font-medium text-white bg-gradient-to-r from-blue-600 to-blue-500 hover:from-blue-700 hover:to-blue-600 inline-flex items-center justify-center">
                            <span class="material-icons-outlined text-base mr-1">search</span>
                            Check
                        </button>
                    </form>
                </div>
            @elseif($valid)
                <div class="bg-white rounded shadow-sm border-t-4 border-green-500 border-x border-b border-gray-200 overflow-hidden">
                    <div class="p-6 text-center border-b border-gray-100">
                        <div class="w-14 h-14 mx-auto rounded-full bg-green-100 flex items-center justify-center">
                            <span class="material-icons-outlined text-green-600 text-3xl">verified</span>
                        </div>
                        <p class="text-base font-semibold text-green-700 mt-3">This certificate is genuine</p>
                        <p class="text-xs text-gray-500 mt-1">It matches a record issued by this system.</p>
                    </div>

                    <dl class="divide-y divide-gray-100">
                        @php
                            $event = $certificate->event;

                            $dates = null;
                            if ($event?->start_date) {
                                $dates = $event->start_date->format('j F Y');

                                if ($event->end_date && ! $event->end_date->isSameDay($event->start_date)) {
                                    $dates .= ' – ' . $event->end_date->format('j F Y');
                                }
                            }

                            // Name, event, date and issuer. Nothing that could be used to
                            // contact or identify the holder beyond their name.
                            $rows = array_filter([
                                'Awarded to' => $holder,
                                'Event' => $eventName,
                                'Held on' => $dates,
                                'Location' => $event->location ?? null,
                                'Issued by' => $event->user->name ?? null,
                                'Issued on' => $certificate->generated_at?->format('j F Y'),
                            ]);
                        @endphp

                        @foreach($rows as $label => $value)
                            <div class="px-6 py-3 flex flex-wrap justify-between gap-2">
                                <dt class="text-xs text-gray-500">{{ $label }}</dt>
                                <dd class="text-xs font-medium text-gray-800 text-right">{{ $value }}</dd>
                            </div>
                        @endforeach

                        <div class="px-6 py-3 flex flex-wrap justify-between gap-2">
                            <dt class="text-xs text-gray-500">Certificate number</dt>
                            <dd class="text-xs font-mono text-gray-800 text-right break-all">
                                {{ $certificate->certificate_number }}
                            </dd>
                        </div>
                    </dl>

                    {{-- No download link. The PDF belongs to the holder; this page exists
                         to confirm the award, not to hand out the artwork. --}}
                    <div class="bg-gray-50 px-6 py-3 border-t border-gray-100">
                        <p class="text-[11px] text-gray-500">
                            This page confirms the record only. The certificate file itself is
                            available to the person it was issued to.
                        </p>
                    </div>
                </div>
            @else
                <div class="bg-white rounded shadow-sm border-t-4 border-red-500 border-x border-b border-gray-200">
                    <div class="p-6 text-center">
                        <div class="w-14 h-14 mx-auto rounded-full bg-red-100 flex items-center justify-center">
                            <span class="material-icons-outlined text-red-600 text-3xl">error_outline</span>
                        </div>
                        <p class="text-base font-semibold text-red-700 mt-3">We cannot verify this</p>
                        <p class="text-xs text-gray-500 mt-1">
                            No certificate matches this number.
                        </p>

                        <p class="text-xs font-mono text-gray-700 bg-gray-50 border border-gray-200 rounded px-3 py-2 mt-4 break-all">
                            {{ $number }}
                        </p>

                        <p class="text-[11px] text-gray-500 mt-3">
                            Check for a typo, or ask the holder to send the link again. A number that
                            was valid before will still be valid now, so a mismatch usually means the
                            number was transcribed rather than copied.
                        </p>
                    </div>

                    <div class="bg-gray-50 px-6 py-4 border-t border-gray-100">
                        <a href="{{ route('certificate.verify.form') }}"
                           class="text-xs text-primary-DEFAULT hover:underline inline-flex items-center">
                            <span class="material-icons-outlined text-sm mr-1">edit</span>
                            Enter a different number
                        </a>
                    </div>
                </div>
            @endif

            <p class="text-center text-[11px] text-gray-400 mt-6">
                Powered by {{ config('app.name', 'e-Certificate') }}
            </p>
        </div>
    </div>
</body>
</html>
