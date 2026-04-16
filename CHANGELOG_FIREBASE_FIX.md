# Firebase Credential Configuration Fix

## Date: April 16, 2026

## Problem
Firebase service account credentials were hardcoded with absolute Mac path in `.env`:
```
GOOGLE_APPLICATION_CREDENTIALS=/Users/faizan/Downloads/Faizan/Faizan/Programming/sijil/e-certificate-com-my-firebase-adminsdk-fbsvc-35809f6449.json
```

This caused issues:
- ❌ Not portable across different systems
- ❌ Security risk (exposed personal directory structure)
- ❌ Won't work on Windows or Linux servers
- ❌ Credential file not in project structure

## Solution Implemented

### 1. Created Secure Credentials Directory
```
storage/credentials/
├── .gitkeep
├── README.md
├── firebase-service-account.json.example
└── firebase-service-account.json (user must add this)
```

### 2. Updated .env Configuration
**Before:**
```env
GOOGLE_APPLICATION_CREDENTIALS=/Users/faizan/Downloads/Faizan/Faizan/Programming/sijil/e-certificate-com-my-firebase-adminsdk-fbsvc-35809f6449.json
```

**After:**
```env
GOOGLE_APPLICATION_CREDENTIALS="${APP_BASE_PATH}/storage/credentials/firebase-service-account.json"
```

### 3. Updated .gitignore
Added comprehensive credential protection:
```gitignore
# Secrets & Credentials
client_secret_*.json
*firebase*.json
/storage/credentials/
```

### 4. Updated .env.example
Added proper placeholders with documentation:
```env
# Firebase Configuration
FIREBASE_PROJECT_ID=your-project-id
GOOGLE_APPLICATION_CREDENTIALS="${APP_BASE_PATH}/storage/credentials/firebase-service-account.json"

# Firebase Web Config (for frontend)
VITE_FIREBASE_API_KEY=your-api-key
VITE_FIREBASE_AUTH_DOMAIN=your-project.firebaseapp.com
...
```

### 5. Created Documentation
- **FIREBASE_SETUP.md**: Complete Firebase setup guide
- **storage/credentials/README.md**: Quick setup instructions
- **DEPLOYMENT.md**: Full deployment guide including Firebase

## Files Changed

### Modified
- `.env` - Updated credential path
- `.gitignore` - Enhanced credential protection
- `.env.example` - Added Firebase configuration template

### Created
- `storage/credentials/.gitkeep`
- `storage/credentials/README.md`
- `storage/credentials/firebase-service-account.json.example`
- `FIREBASE_SETUP.md`
- `DEPLOYMENT.md`
- `CHANGELOG_FIREBASE_FIX.md` (this file)

## Migration Steps for Existing Installations

### For Development
```bash
# 1. Create credentials directory
mkdir -p storage/credentials

# 2. Copy your Firebase service account JSON
cp /path/to/your/firebase-credentials.json storage/credentials/firebase-service-account.json

# 3. Set proper permissions
chmod 600 storage/credentials/firebase-service-account.json

# 4. Update .env (already done in this commit)

# 5. Clear compiled views
php artisan view:clear

# 6. Test Firebase connection
php artisan tinker
>>> config('services.firebase.credentials')
```

### For Production
See `DEPLOYMENT.md` section 3 (Firebase Setup)

## Security Improvements

✅ Credentials now stored in project structure
✅ Path is relative and portable
✅ Credentials directory is git-ignored
✅ Example file provided for reference
✅ Comprehensive documentation added
✅ Proper file permissions documented

## Verification

After applying this fix, verify:

```bash
# Check file exists
ls -la storage/credentials/firebase-service-account.json

# Check environment variable
php artisan tinker
>>> config('services.firebase.credentials')
=> "/path/to/sijil/storage/credentials/firebase-service-account.json"

# Test FCM service (if enabled)
# Should not throw authentication errors
```

## Next Steps

1. ✅ Firebase credentials fixed
2. ⏳ Add comprehensive testing
3. ⏳ Complete API documentation
4. ⏳ Create user manual
5. ⏳ Setup CI/CD pipeline

## Notes

- The actual `firebase-service-account.json` file must be obtained from Firebase Console
- Each environment (dev/staging/production) should have its own Firebase project
- Never commit the actual credentials file to version control
- Rotate service account keys periodically for security

## References

- Firebase Admin SDK Setup: https://firebase.google.com/docs/admin/setup
- Laravel Environment Configuration: https://laravel.com/docs/configuration
- Security Best Practices: See `FIREBASE_SETUP.md`
