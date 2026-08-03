@php
    $primary = \App\Support\Branding::primary();
    $logo = \App\Support\Branding::image('org_logo') ?? \App\Support\Branding::image('login_logo');
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Temporarily unavailable &mdash; {{ $orgName }}</title>
    @include('partials.branding-head')
    <style>
        body {
            margin: 0;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #f3f4f6;
            font-family: var(--brand-font, system-ui, sans-serif);
            color: #111827;
            padding: 24px;
        }
        .card {
            background: #fff;
            border: 1px solid #e5e7eb;
            border-radius: 10px;
            max-width: 460px;
            width: 100%;
            overflow: hidden;
            box-shadow: 0 10px 30px -12px rgba(0, 0, 0, 0.15);
        }
        .head { background: {{ $primary }}; padding: 22px; text-align: center; }
        .head img { max-height: 46px; max-width: 190px; }
        .head span { color: #fff; font-size: 17px; font-weight: 600; }
        .body { padding: 26px 24px; text-align: center; }
        h1 { font-size: 17px; margin: 0 0 10px; }
        p { font-size: 13px; line-height: 1.65; color: #4b5563; margin: 0 0 14px; }
        .foot { font-size: 11px; color: #9ca3af; padding: 14px; border-top: 1px solid #e5e7eb; text-align: center; }
    </style>
</head>
<body>
    <div class="card">
        <div class="head">
            @if($logo)
                <img src="{{ $logo }}" alt="{{ $orgName }}">
            @else
                <span>{{ $orgName }}</span>
            @endif
        </div>

        <div class="body">
            <h1>Temporarily unavailable</h1>
            <p>
                We are carrying out maintenance and will be back shortly. Nothing you have submitted has been
                lost.
            </p>
            <p>
                If you were part-way through registering for an event, your place is safe once we are back.
            </p>
        </div>

        <div class="foot">{{ $orgName }}</div>
    </div>
</body>
</html>
