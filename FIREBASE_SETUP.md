# Firebase Configuration Guide

## Overview

This application uses Firebase for:
- **Firebase Cloud Messaging (FCM)**: Push notifications to users
- **Firebase Admin SDK**: Server-side operations

## Setup Instructions

### 1. Firebase Service Account (Backend)

The backend requires a Firebase service account JSON file for authentication.

**Steps:**
1. Go to [Firebase Console](https://console.firebase.google.com/)
2. Select project: `e-certificate-com-my`
3. Navigate to: ⚙️ Project Settings → Service Accounts
4. Click "Generate New Private Key"
5. Save the downloaded file as `firebase-service-account.json`
6. Place it in: `storage/credentials/firebase-service-account.json`

**Environment Variable:**
```env
GOOGLE_APPLICATION_CREDENTIALS="${APP_BASE_PATH}/storage/credentials/firebase-service-account.json"
FIREBASE_PROJECT_ID=e-certificate-com-my
```

### 2. Firebase Web Configuration (Frontend)

The frontend needs Firebase web app credentials for client-side operations.

**Steps:**
1. In Firebase Console, go to: ⚙️ Project Settings → General
2. Scroll to "Your apps" section
3. Select your web app or create a new one
4. Copy the Firebase configuration object

**Environment Variables (.env):**
```env
VITE_FIREBASE_API_KEY=AIzaSyB9BWCjjeTm5PB0CzesKWvD_0p5ZXvntfY
VITE_FIREBASE_AUTH_DOMAIN=e-certificate-com-my.firebaseapp.com
VITE_FIREBASE_PROJECT_ID=e-certificate-com-my
VITE_FIREBASE_STORAGE_BUCKET=e-certificate-com-my.firebasestorage.app
VITE_FIREBASE_MESSAGING_SENDER_ID=379975704477
VITE_FIREBASE_APP_ID=1:379975704477:web:f2ae002e605b3ef93d1974
VITE_FIREBASE_MEASUREMENT_ID=G-KGXLXCLWNN
```

### 3. Firebase Cloud Messaging (FCM)

For push notifications, you need a VAPID key.

**Steps:**
1. In Firebase Console, go to: ⚙️ Project Settings → Cloud Messaging
2. Scroll to "Web configuration"
3. Under "Web Push certificates", generate or copy your VAPID key

**Environment Variable:**
```env
VITE_FIREBASE_VAPID_KEY=BA-vO9o2DGFNyRMlbNebHkCQGNuECftAC5cfa9IQ1aErM9u33pybTBxL4-6rSzWNv3zVSv_z0Zjrd7U5dErXl40
FCM_ENABLED=true
```

## Security Best Practices

### ✅ DO:
- Keep `firebase-service-account.json` in `storage/credentials/` (git-ignored)
- Use environment variables for all credentials
- Rotate service account keys periodically
- Restrict service account permissions to minimum required
- Use different Firebase projects for dev/staging/production

### ❌ DON'T:
- Commit service account JSON to git
- Hardcode credentials in source code
- Share service account keys publicly
- Use production credentials in development
- Expose private keys in client-side code

## File Structure

```
sijil/
├── .env                                    # Environment configuration
├── .env.example                            # Template with placeholders
├── storage/
│   └── credentials/
│       ├── README.md                       # Setup instructions
│       ├── .gitkeep                        # Keep directory in git
│       └── firebase-service-account.json   # Your Firebase credentials (git-ignored)
└── public/
    ├── sijil-fcm-sw.js                     # FCM service worker
    └── fcm-sw-config.js                    # FCM configuration
```

## Verification

After setup, verify your configuration:

```bash
# Check if file exists
php artisan tinker
>>> file_exists(storage_path('credentials/firebase-service-account.json'))
=> true

# Test FCM service (if enabled)
# Visit your app and check browser console for FCM registration
```

## Troubleshooting

### Error: "Unable to parse credentials"
- Verify JSON file is valid
- Check file path in `.env`
- Ensure file has correct permissions

### Error: "Permission denied"
- Check service account has required roles in Firebase Console
- Verify project ID matches in all configurations

### Push notifications not working
- Verify VAPID key is correct
- Check FCM_ENABLED=true in `.env`
- Ensure service worker is registered
- Check browser console for errors

## Production Deployment

For production:

1. **Use separate Firebase project** for production
2. **Generate new service account** for production environment
3. **Update all environment variables** with production values
4. **Secure the credentials file** with proper file permissions:
   ```bash
   chmod 600 storage/credentials/firebase-service-account.json
   ```
5. **Never expose credentials** in logs or error messages

## Additional Resources

- [Firebase Admin SDK Setup](https://firebase.google.com/docs/admin/setup)
- [Firebase Cloud Messaging](https://firebase.google.com/docs/cloud-messaging)
- [Web Push Notifications](https://firebase.google.com/docs/cloud-messaging/js/client)
