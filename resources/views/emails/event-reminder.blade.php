@php
    $config = \App\Models\GlobalConfig::getConfig();
    $orgName = $config->org_name ?: config('app.name');
    $primary = \App\Support\Branding::primary();
    // Absolute, because an email is read outside the application.
    $logo = \App\Support\Branding::image('org_logo');
    $logo = $logo ? rtrim(config('app.url'), '/') . $logo : null;
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Reminder: {{ $event->name }}</title>
</head>
<body style="margin:0;padding:0;background-color:#f3f4f6;font-family:Arial,Helvetica,sans-serif;color:#111827;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color:#f3f4f6;padding:24px 12px;">
        <tr>
            <td align="center">
                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width:560px;background-color:#ffffff;border-radius:8px;overflow:hidden;border:1px solid #e5e7eb;">
                    <tr>
                        <td style="background-color:{{ $primary }};padding:20px 24px;text-align:center;">
                            @if($logo)
                                <img src="{{ $logo }}" alt="{{ $orgName }}" style="max-height:48px;max-width:200px;">
                            @else
                                <div style="color:#ffffff;font-size:18px;font-weight:bold;">{{ $orgName }}</div>
                            @endif
                        </td>
                    </tr>

                    <tr>
                        <td style="padding:24px;">
                            <p style="margin:0 0 16px;font-size:14px;">Hello {{ $participant->name }},</p>

                            <p style="margin:0 0 16px;font-size:14px;line-height:1.6;">
                                This is a reminder that
                                <strong>{{ $event->name }}</strong>
                                @if($hoursAhead >= 48)
                                    starts in {{ (int) round($hoursAhead / 24) }} days.
                                @elseif($hoursAhead >= 24)
                                    starts tomorrow.
                                @else
                                    starts in about {{ $hoursAhead }} hours.
                                @endif
                            </p>

                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color:#f9fafb;border:1px solid #e5e7eb;border-radius:6px;margin-bottom:16px;">
                                <tr>
                                    <td style="padding:14px 16px;font-size:13px;line-height:1.8;">
                                        <strong>When</strong><br>
                                        {{ $event->start_date ? $event->start_date->format('l, d F Y') : 'To be confirmed' }}
                                        @if($event->start_time)
                                            at {{ \Illuminate\Support\Carbon::parse($event->start_time)->format('g:i A') }}
                                        @endif
                                        <br><br>
                                        <strong>Where</strong><br>
                                        {{ $event->location ?: 'To be confirmed' }}
                                    </td>
                                </tr>
                            </table>

                            <p style="margin:0;font-size:12px;color:#6b7280;line-height:1.6;">
                                You are receiving this because you registered for this event. No reply is needed.
                            </p>
                        </td>
                    </tr>

                    <tr>
                        <td style="background-color:#f9fafb;padding:14px 24px;text-align:center;border-top:1px solid #e5e7eb;">
                            <p style="margin:0;font-size:11px;color:#9ca3af;">{{ $orgName }}</p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
