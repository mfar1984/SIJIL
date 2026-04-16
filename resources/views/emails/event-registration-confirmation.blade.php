<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Event Registration Confirmation</title>
</head>
<body style="font-family: 'Poppins', Arial, sans-serif; line-height: 1.6; color: #333; max-width: 650px; margin: 0 auto; padding: 0; background: #e5e7eb;">
    <!-- Email Container -->
    <table cellpadding="0" cellspacing="0" border="0" width="100%" style="background: #e5e7eb; padding: 30px 0;">
        <tr>
            <td align="center">
                <!-- Main Content Table -->
                <table cellpadding="0" cellspacing="0" border="0" width="650" style="background: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
                    <!-- Header -->
                    <tr>
                        <td style="background: #111827; padding: 35px 40px; text-align: center;">
                            <div style="background: #10b981; width: 70px; height: 70px; border-radius: 50%; margin: 0 auto 15px; display: inline-flex; align-items: center; justify-content: center; border: 3px solid #ffffff;">
                                <svg width="40" height="40" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M20 6L9 17L4 12" stroke="#ffffff" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                            </div>
                            <h1 style="color: #ffffff; margin: 0; font-size: 26px; font-weight: 600; letter-spacing: -0.5px;">Registration Confirmed</h1>
                            <div style="background: #10b981; color: #ffffff; display: inline-block; padding: 6px 16px; border-radius: 4px; font-size: 12px; font-weight: 600; margin-top: 12px; text-transform: uppercase; letter-spacing: 0.5px;">Confirmed</div>
                        </td>
                    </tr>
                    
                    <!-- Content -->
                    <tr>
                        <td style="padding: 40px;">
                            <p style="font-size: 16px; color: #111827; margin: 0 0 8px 0;">Dear <strong>{{ $participant->name }}</strong>,</p>
                            <p style="font-size: 14px; color: #6b7280; margin: 0 0 30px 0;">Your registration has been successfully processed.</p>
                            
                            <!-- Event Details Table -->
                            <table cellpadding="0" cellspacing="0" border="0" width="100%" style="background: #f9fafb; border: 1px solid #e5e7eb; border-radius: 6px; margin-bottom: 25px;">
                                <tr>
                                    <td style="padding: 25px;">
                                        <h2 style="color: #111827; font-size: 18px; margin: 0 0 20px 0; font-weight: 600; border-bottom: 2px solid #004aad; padding-bottom: 10px;">Event Information</h2>
                                        
                                        <table cellpadding="0" cellspacing="0" border="0" width="100%">
                                            <tr>
                                                <td style="padding: 10px 0; color: #6b7280; font-size: 13px; font-weight: 500; width: 140px; vertical-align: top;">EVENT NAME</td>
                                                <td style="padding: 10px 0; color: #111827; font-size: 14px; font-weight: 600;">{{ $event->name }}</td>
                                            </tr>
                                            <tr>
                                                <td style="padding: 10px 0; color: #6b7280; font-size: 13px; font-weight: 500; vertical-align: top;">ORGANIZER</td>
                                                <td style="padding: 10px 0; color: #111827; font-size: 14px;">{{ $event->organizer }}</td>
                                            </tr>
                                            <tr>
                                                <td style="padding: 10px 0; color: #6b7280; font-size: 13px; font-weight: 500; vertical-align: top;">DATE</td>
                                                <td style="padding: 10px 0; color: #111827; font-size: 14px;">
                                                    {{ $event->start_date->format('l, d F Y') }}
                                                    @if($event->start_date != $event->end_date)
                                                        - {{ $event->end_date->format('l, d F Y') }}
                                                    @endif
                                                </td>
                                            </tr>
                                            <tr>
                                                <td style="padding: 10px 0; color: #6b7280; font-size: 13px; font-weight: 500; vertical-align: top;">TIME</td>
                                                <td style="padding: 10px 0; color: #111827; font-size: 14px;">{{ substr($event->start_time, 0, 5) }} - {{ substr($event->end_time, 0, 5) }}</td>
                                            </tr>
                                            <tr>
                                                <td style="padding: 10px 0; color: #6b7280; font-size: 13px; font-weight: 500; vertical-align: top;">LOCATION</td>
                                                <td style="padding: 10px 0; color: #111827; font-size: 14px;">{{ $event->location }}</td>
                                            </tr>
                                            @if($event->address)
                                            <tr>
                                                <td style="padding: 10px 0; color: #6b7280; font-size: 13px; font-weight: 500; vertical-align: top;">ADDRESS</td>
                                                <td style="padding: 10px 0; color: #111827; font-size: 14px;">{{ $event->address }}</td>
                                            </tr>
                                            @endif
                                        </table>
                                    </td>
                                </tr>
                            </table>
                            
                            @if($event->description)
                            <!-- Event Description -->
                            <table cellpadding="0" cellspacing="0" border="0" width="100%" style="margin-bottom: 25px;">
                                <tr>
                                    <td style="padding: 0;">
                                        <h3 style="color: #111827; font-size: 16px; margin: 0 0 10px 0; font-weight: 600;">About This Event</h3>
                                        <p style="font-size: 14px; color: #4b5563; line-height: 1.6; margin: 0;">{{ $event->description }}</p>
                                    </td>
                                </tr>
                            </table>
                            @endif
                            
                            <!-- Certificate Portal -->
                            <table cellpadding="0" cellspacing="0" border="0" width="100%" style="background: #004aad; border-radius: 6px; margin-bottom: 25px;">
                                <tr>
                                    <td style="padding: 30px; text-align: center;">
                                        <div style="margin-bottom: 15px;">
                                            <svg width="50" height="50" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" style="display: inline-block;">
                                                <rect x="3" y="3" width="18" height="18" rx="2" stroke="#ffffff" stroke-width="2" fill="none"/>
                                                <path d="M8 12L11 15L16 9" stroke="#ffffff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                                <circle cx="18" cy="6" r="3" fill="#10b981"/>
                                            </svg>
                                        </div>
                                        <h3 style="color: #ffffff; font-size: 19px; margin: 0 0 8px 0; font-weight: 600;">Certificate Portal</h3>
                                        <p style="color: rgba(255,255,255,0.9); font-size: 14px; margin: 0 0 20px 0; line-height: 1.5;">Access your official event certificate after completion</p>
                                        <a href="https://user.e-certificate.com.my" style="display: inline-block; background: #ffffff; color: #004aad; padding: 12px 32px; text-decoration: none; border-radius: 5px; font-weight: 600; font-size: 14px;">Access Portal</a>
                                        <p style="color: rgba(255,255,255,0.8); font-size: 12px; margin: 15px 0 0 0; font-family: monospace;">https://user.e-certificate.com.my</p>
                                    </td>
                                </tr>
                            </table>
                            
                            @if($event->contact_person || $event->contact_email || $event->contact_phone)
                            <!-- Contact -->
                            <table cellpadding="0" cellspacing="0" border="0" width="100%" style="border-top: 1px solid #e5e7eb; border-bottom: 1px solid #e5e7eb; margin: 25px 0;">
                                <tr>
                                    <td style="padding: 20px 0;">
                                        <h3 style="color: #111827; font-size: 15px; margin: 0 0 12px 0; font-weight: 600;">Contact Information</h3>
                                        @if($event->contact_person)
                                        <p style="margin: 5px 0; font-size: 13px; color: #4b5563;"><strong>Contact Person:</strong> {{ $event->contact_person }}</p>
                                        @endif
                                        @if($event->contact_email)
                                        <p style="margin: 5px 0; font-size: 13px; color: #4b5563;"><strong>Email:</strong> <a href="mailto:{{ $event->contact_email }}" style="color: #004aad; text-decoration: none;">{{ $event->contact_email }}</a></p>
                                        @endif
                                        @if($event->contact_phone)
                                        <p style="margin: 5px 0; font-size: 13px; color: #4b5563;"><strong>Phone:</strong> {{ $event->contact_phone }}</p>
                                        @endif
                                    </td>
                                </tr>
                            </table>
                            @endif
                            
                            <p style="text-align: center; color: #6b7280; font-size: 14px; margin: 25px 0 0 0;">We look forward to your participation.</p>
                        </td>
                    </tr>
                    
                    <!-- Footer -->
                    <tr>
                        <td style="background: #f9fafb; padding: 25px 40px; text-align: center; border-top: 1px solid #e5e7eb;">
                            <p style="margin: 5px 0; color: #9ca3af; font-size: 12px;">SIJIL Event Management System</p>
                            <p style="margin: 5px 0; color: #9ca3af; font-size: 11px;">This is an automated notification • Please do not reply</p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
