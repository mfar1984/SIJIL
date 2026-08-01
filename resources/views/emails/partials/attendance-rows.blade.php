{{--
    Attendance rows for the participant's registration confirmation email.

    Rendered inside the "Event Information" table, so it emits <tr> only. The
    check-in and check-out windows are spelled out because the whole point of the
    setting is that a participant should not turn up outside the scan window.

    The QR code is deliberately not included: it goes to the organizer, who
    displays it at the venue.

    Expects $attendance (App\Support\AttendanceSummary).
--}}
@if($attendance->required)
    <tr>
        <td style="padding: 10px 0; color: #6b7280; font-size: 13px; font-weight: 500; vertical-align: top;">ATTENDANCE</td>
        <td style="padding: 10px 0; color: #111827; font-size: 14px;">
            {{ $attendance->participantNotice() }}
        </td>
    </tr>

    @if($attendance->configured && count($attendance->days) > 0)
        <tr>
            <td colspan="2" style="padding: 6px 0 0 0;">
                <table cellpadding="0" cellspacing="0" border="0" width="100%"
                       style="border: 1px solid #e5e7eb; border-radius: 4px; background: #ffffff;">
                    <tr style="background: #f3f4f6;">
                        <th align="left" style="padding: 8px 10px; font-size: 11px; color: #6b7280; font-weight: 600; text-transform: uppercase;">Date</th>
                        <th align="left" style="padding: 8px 10px; font-size: 11px; color: #6b7280; font-weight: 600; text-transform: uppercase;">Check-in</th>
                        @if($attendance->hasCheckout())
                            <th align="left" style="padding: 8px 10px; font-size: 11px; color: #6b7280; font-weight: 600; text-transform: uppercase;">Check-out</th>
                        @endif
                    </tr>
                    @foreach($attendance->days as $day)
                        <tr>
                            <td style="padding: 8px 10px; font-size: 13px; color: #111827; border-top: 1px solid #f3f4f6;">
                                {{ $day['date']->format('d M Y') }}
                            </td>
                            <td style="padding: 8px 10px; font-size: 13px; color: #111827; border-top: 1px solid #f3f4f6;">
                                @if($day['checkin_opens'])
                                    {{ $day['checkin_opens'] }} &ndash; {{ $day['checkin_closes'] ?? '—' }}
                                @else
                                    &mdash;
                                @endif
                            </td>
                            @if($attendance->hasCheckout())
                                <td style="padding: 8px 10px; font-size: 13px; color: #111827; border-top: 1px solid #f3f4f6;">
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
                <p style="margin: 8px 0 0 0; font-size: 12px; color: #6b7280;">
                    Please be ready to scan the QR code shown by the organizer within the times above.
                </p>
            </td>
        </tr>
    @endif
@endif
