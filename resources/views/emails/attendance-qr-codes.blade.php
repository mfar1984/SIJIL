<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Attendance QR codes</title>
</head>
<body style="margin: 0; padding: 0; background: #f3f4f6; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Arial, sans-serif;">
    <table cellpadding="0" cellspacing="0" border="0" width="100%" style="background: #f3f4f6; padding: 24px 0;">
        <tr>
            <td align="center">
                <table cellpadding="0" cellspacing="0" border="0" width="600" style="background: #ffffff; border-radius: 8px; overflow: hidden; max-width: 600px;">
                    <!-- Header -->
                    <tr>
                        <td style="background: #111827; padding: 32px 40px; text-align: center;">
                            <h1 style="color: #ffffff; margin: 0; font-size: 22px; font-weight: 600;">Attendance QR Codes</h1>
                            <p style="color: #9ca3af; margin: 8px 0 0 0; font-size: 13px;">{{ $event->name }}</p>
                        </td>
                    </tr>

                    <tr>
                        <td style="padding: 32px 40px;">
                            <p style="font-size: 14px; color: #111827; margin: 0 0 20px 0;">
                                Attendance is enabled for this event. The QR code{{ $attendance->qrCount() === 1 ? '' : 's' }}
                                {{ $attendance->qrCount() === 1 ? 'is' : 'are' }} attached to this email.
                            </p>

                            <table cellpadding="0" cellspacing="0" border="0" width="100%"
                                   style="background: #f9fafb; border: 1px solid #e5e7eb; border-radius: 6px; margin-bottom: 24px;">
                                <tr>
                                    <td style="padding: 20px;">
                                        <table cellpadding="0" cellspacing="0" border="0" width="100%">
                                            <tr>
                                                <td style="padding: 8px 0; color: #6b7280; font-size: 13px; width: 150px; vertical-align: top;">MODE</td>
                                                <td style="padding: 8px 0; color: #111827; font-size: 14px; font-weight: 600;">{{ $attendance->typeLabel() }}</td>
                                            </tr>
                                            <tr>
                                                <td style="padding: 8px 0; color: #6b7280; font-size: 13px; vertical-align: top;">QR CODES</td>
                                                <td style="padding: 8px 0; color: #111827; font-size: 14px;">{{ $attendance->qrCount() }} attached</td>
                                            </tr>
                                            <tr>
                                                <td style="padding: 8px 0; color: #6b7280; font-size: 13px; vertical-align: top;">LOCATION</td>
                                                <td style="padding: 8px 0; color: #111827; font-size: 14px;">{{ $event->location }}</td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                            </table>

                            @if(count($attendance->days) > 0)
                                <h2 style="font-size: 15px; color: #111827; margin: 0 0 12px 0; font-weight: 600;">Scan windows</h2>

                                <table cellpadding="0" cellspacing="0" border="0" width="100%"
                                       style="border: 1px solid #e5e7eb; border-radius: 6px; margin-bottom: 24px;">
                                    <tr style="background: #f3f4f6;">
                                        <th align="left" style="padding: 10px; font-size: 11px; color: #6b7280; font-weight: 600; text-transform: uppercase;">Date</th>
                                        <th align="left" style="padding: 10px; font-size: 11px; color: #6b7280; font-weight: 600; text-transform: uppercase;">Check-in</th>
                                        @if($attendance->hasCheckout())
                                            <th align="left" style="padding: 10px; font-size: 11px; color: #6b7280; font-weight: 600; text-transform: uppercase;">Check-out</th>
                                        @endif
                                    </tr>
                                    @foreach($attendance->days as $day)
                                        <tr>
                                            <td style="padding: 10px; font-size: 13px; color: #111827; border-top: 1px solid #f3f4f6;">
                                                {{ $day['date']->format('D, d M Y') }}
                                            </td>
                                            <td style="padding: 10px; font-size: 13px; color: #111827; border-top: 1px solid #f3f4f6;">
                                                @if($day['checkin_opens'])
                                                    {{ $day['checkin_opens'] }} &ndash; {{ $day['checkin_closes'] ?? '—' }}
                                                @else
                                                    &mdash;
                                                @endif
                                            </td>
                                            @if($attendance->hasCheckout())
                                                <td style="padding: 10px; font-size: 13px; color: #111827; border-top: 1px solid #f3f4f6;">
                                                    @if($day['checkout_opens'])
                                                        {{ $day['checkout_opens'] }} &ndash; {{ $day['checkout_closes'] ?? '—' }}
                                                    @else
                                                        &mdash;
                                                    @endif
                                                </td>
                                            @endif
                                        </tr>
                                    @endforeach
                                </table>
                            @endif

                            <table cellpadding="0" cellspacing="0" border="0" width="100%"
                                   style="background: #fef3c7; border-left: 4px solid #f59e0b; border-radius: 4px;">
                                <tr>
                                    <td style="padding: 16px;">
                                        <p style="margin: 0; font-size: 13px; color: #92400e; font-weight: 600;">Keep these codes with you</p>
                                        <p style="margin: 6px 0 0 0; font-size: 12px; color: #92400e; line-height: 1.6;">
                                            Display the QR code at the venue for participants to scan. Do not forward this
                                            email to participants: anyone holding the image could mark themselves present
                                            without attending.
                                        </p>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <tr>
                        <td style="padding: 20px 40px; background: #f9fafb; border-top: 1px solid #e5e7eb; text-align: center;">
                            <p style="margin: 0; font-size: 11px; color: #9ca3af;">SIJIL Event Management System</p>
                            <p style="margin: 4px 0 0 0; font-size: 11px; color: #9ca3af;">This is an automated notification &bull; Please do not reply</p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
