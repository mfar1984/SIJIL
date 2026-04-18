# Certificate Verification Website

Standalone PHP website for verifying certificate authenticity via QR code scanning or manual certificate number entry.

## Features

- QR Code scanning using device camera
- Manual certificate number entry
- Real-time verification against main application API
- Responsive design for mobile and desktop
- Clean, modern UI with Poppins font

## Installation

1. Copy the `verification-website` folder to your web server
2. Configuration is automatic - detects localhost for development and production domain automatically
3. Ensure PHP cURL extension is enabled
4. Access via browser: `https://www.e-certificate.com` (production) or `http://localhost` (development)

## Configuration

### config.php

```php
<?php
// Auto-detect API endpoint based on environment
function getApiEndpoint() {
    $hostname = $_SERVER['HTTP_HOST'] ?? 'localhost';
    
    // Production
    if (strpos($hostname, 'e-certificate.com.my') !== false) {
        return 'https://apps.e-certificate.com.my/api/certificate/verify';
    }
    
    // Development
    return 'http://localhost:8000/api/certificate/verify';
}

define('API_ENDPOINT', getApiEndpoint());
```

The API endpoint automatically detects the environment. No manual configuration needed.

## File Structure

```
verification-website/
├── index.php           # Main HTML page
├── verify.php          # Backend verification script
├── config.php          # Configuration file
├── assets/
│   ├── css/
│   │   └── style.css   # Stylesheet
│   └── js/
│       └── app.js      # JavaScript functionality
└── README.md           # This file
```

## Usage

### QR Code Scanning

1. Click "Scan QR Code" tab
2. Click "Start Scanning" button
3. Allow camera access when prompted
4. Point camera at certificate QR code
5. Verification result will display automatically

### Manual Entry

1. Click "Enter Certificate Number" tab
2. Enter certificate number (format: CERT-YYYYMMDDHHMMSS-XXXXXX)
3. Click "Verify Certificate" button
4. Verification result will display

## API Response Format

### Valid Certificate

```json
{
    "status": "VALID",
    "data": {
        "certificate_number": "CERT-20260416223748-3B1D0A",
        "participant_name": "John Doe",
        "event_name": "Workshop 2026",
        "event_date": "16 April 2026",
        "event_time": "14:00"
    }
}
```

### Invalid Certificate

```json
{
    "status": "INVALID",
    "message": "Certificate not found"
}
```

## Requirements

- PHP 7.4 or higher
- cURL extension enabled
- Modern web browser with camera support (for QR scanning)
- HTTPS recommended for camera access

## Security Notes

- All certificate data is encrypted in QR codes
- Verification is done server-side via API
- No sensitive data stored in verification website
- HTTPS required for camera access in production

## Troubleshooting

### Camera not working

- Ensure HTTPS is enabled (required for camera access)
- Check browser permissions for camera access
- Try different browser if issues persist

### Verification fails

- Check API_ENDPOINT in config.php is correct
- Ensure main application API is accessible
- Check PHP cURL extension is enabled
- Verify certificate number format is correct

## Browser Support

- Chrome 60+
- Firefox 55+
- Safari 11+
- Edge 79+

## License

Part of eSijil Certificate Management System
