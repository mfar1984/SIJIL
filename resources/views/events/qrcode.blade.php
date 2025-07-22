<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Event Registration QR Code</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            line-height: 1.6;
            color: #333;
            margin: 0;
            padding: 20px;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            border: 1px solid #ddd;
            padding: 30px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
        }
        .header h1 {
            font-size: 24px;
            margin-bottom: 5px;
            color: #2563EB;
        }
        .event-details {
            margin-bottom: 30px;
            border-bottom: 1px solid #ddd;
            padding-bottom: 20px;
        }
        .event-details h2 {
            font-size: 18px;
            margin-bottom: 10px;
            color: #2563EB;
        }
        .event-details p {
            font-size: 14px;
            margin: 5px 0;
        }
        .qr-container {
            text-align: center;
        }
        .qr-container img {
            max-width: 300px;
            height: auto;
        }
        .registration-link {
            text-align: center;
            margin-top: 20px;
            font-size: 12px;
            color: #666;
            word-break: break-all;
        }
        .footer {
            text-align: center;
            margin-top: 30px;
            font-size: 12px;
            color: #666;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Event Registration QR Code</h1>
            <p>Scan this QR code to register for the event</p>
        </div>
        
        <div class="event-details">
            <h2>{{ $event['name'] }}</h2>
            <p><strong>Organizer:</strong> {{ $event['organizer'] }}</p>
            <p><strong>Date:</strong> {{ $event['start_date'] }} to {{ $event['end_date'] }}</p>
            <p><strong>Location:</strong> {{ $event['location'] }}</p>
        </div>
        
        <div class="qr-container">
            {!! $qrCode !!}
        </div>
        
        <div class="registration-link">
            <p>Registration Link: {{ $registrationLink }}</p>
        </div>
        
        <div class="footer">
            <p>Generated on: {{ now()->format('d M Y - H:i:s') }}</p>
            <p>This QR code will expire when the event begins.</p>
        </div>
    </div>
</body>
</html> 