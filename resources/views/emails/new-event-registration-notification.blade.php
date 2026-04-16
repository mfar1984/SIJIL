<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>New Event Registration</title>
</head>
<body style="font-family: 'Poppins', Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px;">
    <div style="background: linear-gradient(135deg, #059669 0%, #10b981 100%); padding: 30px; text-align: center; border-radius: 10px 10px 0 0;">
        <h1 style="color: #ffffff; margin: 0; font-size: 24px; font-weight: 600;">New Event Registration</h1>
    </div>
    
    <div style="background: #ffffff; padding: 30px; border: 1px solid #e5e7eb; border-top: none; border-radius: 0 0 10px 10px;">
        <p style="font-size: 16px; margin-bottom: 20px;">Hello,</p>
        
        <p style="font-size: 14px; margin-bottom: 20px;">A new participant has registered for your event <strong>{{ $event->name }}</strong>.</p>
        
        <div style="background: #f0fdf4; padding: 20px; border-radius: 8px; margin: 25px 0; border-left: 4px solid #059669;">
            <h2 style="color: #059669; font-size: 18px; margin-top: 0; margin-bottom: 15px; font-weight: 600;">Participant Information</h2>
            
            <table style="width: 100%; border-collapse: collapse;">
                <tr>
                    <td style="padding: 8px 0; font-weight: 500; color: #6b7280; width: 140px;">Name:</td>
                    <td style="padding: 8px 0; color: #111827;">{{ $participant->name }}</td>
                </tr>
                <tr>
                    <td style="padding: 8px 0; font-weight: 500; color: #6b7280;">Email:</td>
                    <td style="padding: 8px 0; color: #111827;"><a href="mailto:{{ $participant->email }}" style="color: #059669;">{{ $participant->email }}</a></td>
                </tr>
                @if($participant->phone)
                <tr>
                    <td style="padding: 8px 0; font-weight: 500; color: #6b7280;">Phone:</td>
                    <td style="padding: 8px 0; color: #111827;">{{ $participant->phone }}</td>
                </tr>
                @endif
                @if($participant->organization)
                <tr>
                    <td style="padding: 8px 0; font-weight: 500; color: #6b7280;">Organization:</td>
                    <td style="padding: 8px 0; color: #111827;">{{ $participant->organization }}</td>
                </tr>
                @endif
                @if($participant->job_title)
                <tr>
                    <td style="padding: 8px 0; font-weight: 500; color: #6b7280;">Job Title:</td>
                    <td style="padding: 8px 0; color: #111827;">{{ $participant->job_title }}</td>
                </tr>
                @endif
                <tr>
                    <td style="padding: 8px 0; font-weight: 500; color: #6b7280;">Registration Date:</td>
                    <td style="padding: 8px 0; color: #111827;">{{ $participant->registration_date->format('d/m/Y H:i') }}</td>
                </tr>
            </table>
        </div>
        
        <div style="background: #f3f4f6; padding: 20px; border-radius: 8px; margin: 25px 0;">
            <h3 style="color: #111827; font-size: 16px; margin-top: 0; margin-bottom: 15px; font-weight: 600;">Event Details</h3>
            
            <table style="width: 100%; border-collapse: collapse;">
                <tr>
                    <td style="padding: 8px 0; font-weight: 500; color: #6b7280; width: 140px;">Event Name:</td>
                    <td style="padding: 8px 0; color: #111827;">{{ $event->name }}</td>
                </tr>
                <tr>
                    <td style="padding: 8px 0; font-weight: 500; color: #6b7280;">Date:</td>
                    <td style="padding: 8px 0; color: #111827;">
                        {{ $event->start_date->format('d/m/Y') }}
                        @if($event->start_date != $event->end_date)
                            - {{ $event->end_date->format('d/m/Y') }}
                        @endif
                    </td>
                </tr>
                <tr>
                    <td style="padding: 8px 0; font-weight: 500; color: #6b7280;">Location:</td>
                    <td style="padding: 8px 0; color: #111827;">{{ $event->location }}</td>
                </tr>
                <tr>
                    <td style="padding: 8px 0; font-weight: 500; color: #6b7280;">Total Participants:</td>
                    <td style="padding: 8px 0; color: #111827;">{{ $event->participants()->count() }} / {{ $event->max_participants }}</td>
                </tr>
            </table>
        </div>
        
        <div style="text-align: center; margin-top: 30px;">
            <a href="{{ route('participants') }}?event_id={{ $event->id }}" style="display: inline-block; background: #059669; color: #ffffff; padding: 12px 30px; text-decoration: none; border-radius: 6px; font-weight: 500; font-size: 14px;">View All Participants</a>
        </div>
    </div>
    
    <div style="text-align: center; padding: 20px; color: #9ca3af; font-size: 12px;">
        <p style="margin: 5px 0;">This is an automated notification from SIJIL Event Management System</p>
        <p style="margin: 5px 0;">Please do not reply to this email</p>
    </div>
</body>
</html>
