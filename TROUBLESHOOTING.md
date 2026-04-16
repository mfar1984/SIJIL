# Troubleshooting Guide - Sijil System

## Common Issues & Solutions

### 1. Template Designer Canvas Not Showing (404 Error)

**Symptoms:**
- Design canvas shows "404 | NOT FOUND"
- Background PDF tidak load
- Error message: "Add text elements with placeholder tags..."

**Causes:**
1. PDF file tidak wujud dalam storage
2. Storage link broken
3. URL mismatch (hardcoded localhost:8000)

**Solutions:**

#### A. Quick Fix (Recommended)
```bash
# 1. Fix storage link
Remove-Item public\storage -Force
php artisan storage:link

# 2. Fix template URLs
php artisan templates:fix-urls

# 3. Verify all templates
php artisan templates:verify

# 4. Clear caches
php artisan view:clear
php artisan config:clear
```

#### B. Check Storage Link
```bash
# Windows
php artisan storage:link

# Verify link exists
dir public\storage

# If broken, delete and recreate
Remove-Item public\storage -Force
php artisan storage:link
```

#### C. Verify PDF File Exists
```bash
# Check if template PDF exists
dir storage\app\public\certificate-templates

# Verify specific template
php artisan templates:verify

# If file missing, template needs to be re-uploaded
```

#### D. Fix URL Mismatch in Database
```bash
# Automatic fix for all templates
php artisan templates:fix-urls

# Manual fix for specific template
php artisan tinker
>>> $template = App\Models\CertificateTemplate::find(YOUR_ID);
>>> $template->background_pdf = '/storage/certificate-templates/FILENAME.pdf';
>>> $template->save();
```

#### E. Clear Caches
```bash
php artisan view:clear
php artisan config:clear
php artisan cache:clear
```

### 2. Firebase Authentication Error

**Symptoms:**
- "Unable to parse credentials"
- "Permission denied" errors
- Push notifications not working

**Solutions:**

#### A. Check Credentials File
```bash
# Verify file exists
dir storage\credentials\firebase-service-account.json

# Check file permissions (should be readable)
```

#### B. Verify Environment Variable
```bash
# Check .env
GOOGLE_APPLICATION_CREDENTIALS="${APP_BASE_PATH}/storage/credentials/firebase-service-account.json"

# Test in tinker
php artisan tinker
>>> config('services.firebase.credentials')
```

#### C. Re-download Credentials
1. Go to Firebase Console
2. Project Settings → Service Accounts
3. Generate New Private Key
4. Save as `storage/credentials/firebase-service-account.json`

See `FIREBASE_SETUP.md` for detailed instructions.

### 3. Database Connection Error

**Symptoms:**
- "SQLSTATE[HY000] [2002] Connection refused"
- "Access denied for user"

**Solutions:**

#### A. Check MySQL Service
```bash
# Windows - Check if MySQL is running
sc query MySQL80

# Start MySQL if stopped
net start MySQL80
```

#### B. Verify Database Credentials
```env
# Check .env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=esijil
DB_USERNAME=root
DB_PASSWORD=root
```

#### C. Test Connection
```bash
php artisan tinker
>>> DB::connection()->getPdo();
```

### 4. Queue Not Processing

**Symptoms:**
- Emails not sending
- Certificates not generating
- Jobs stuck in queue

**Solutions:**

#### A. Check Queue Connection
```env
# In .env
QUEUE_CONNECTION=database
```

#### B. Run Queue Worker
```bash
# Development
php artisan queue:work

# Production (use Supervisor)
# See DEPLOYMENT.md
```

#### C. Check Failed Jobs
```bash
# List failed jobs
php artisan queue:failed

# Retry all failed jobs
php artisan queue:retry all

# Clear failed jobs
php artisan queue:flush
```

### 5. Assets Not Loading (CSS/JS)

**Symptoms:**
- Page looks broken
- No styling
- JavaScript not working

**Solutions:**

#### A. Build Assets
```bash
# Development
npm run dev

# Production
npm run build
```

#### B. Check Vite Server
```bash
# If using npm run dev, ensure Vite server is running
# Check for errors in terminal
```

#### C. Clear Browser Cache
- Hard refresh: Ctrl+Shift+R (Windows) or Cmd+Shift+R (Mac)
- Clear browser cache completely

### 6. Permission Denied Errors

**Symptoms:**
- "Permission denied" when accessing features
- 403 Forbidden errors
- Features not visible in menu

**Solutions:**

#### A. Check User Role
```bash
php artisan tinker
>>> $user = App\Models\User::find(YOUR_ID);
>>> $user->roles->pluck('name');
>>> $user->getAllPermissions()->pluck('name');
```

#### B. Reseed Permissions
```bash
php artisan db:seed --class=PermissionMatrixSeeder
```

#### C. Assign Role to User
```bash
php artisan tinker
>>> $user = App\Models\User::find(YOUR_ID);
>>> $user->assignRole('Administrator');
```

### 7. Storage Permission Errors

**Symptoms:**
- "Failed to open stream: Permission denied"
- Cannot write to storage
- Cannot upload files

**Solutions:**

#### A. Fix Permissions (Linux/Mac)
```bash
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
```

#### B. Windows
```bash
# Run as Administrator
icacls storage /grant Users:F /t
icacls bootstrap\cache /grant Users:F /t
```

### 8. Session/Login Issues

**Symptoms:**
- Logged out randomly
- Session expired errors
- CSRF token mismatch

**Solutions:**

#### A. Check Session Configuration
```env
# In .env
SESSION_DRIVER=file
SESSION_LIFETIME=120
SESSION_DOMAIN=localhost
```

#### B. Clear Sessions
```bash
php artisan session:flush
php artisan cache:clear
```

#### C. Check Storage Permissions
```bash
# Ensure storage/framework/sessions is writable
dir storage\framework\sessions
```

### 9. Email Not Sending

**Symptoms:**
- Emails not received
- Mail queue stuck
- SMTP errors

**Solutions:**

#### A. Check Mail Configuration
```env
# In .env
MAIL_MAILER=smtp
MAIL_HOST=your-smtp-host
MAIL_PORT=587
MAIL_USERNAME=your-email
MAIL_PASSWORD=your-password
MAIL_ENCRYPTION=tls
```

#### B. Test Email
```bash
php artisan tinker
>>> Mail::raw('Test email', function($msg) {
    $msg->to('test@example.com')->subject('Test');
});
```

#### C. Check Logs
```bash
# Check Laravel logs
type storage\logs\laravel.log

# Check for mail errors
```

### 10. PDF Generation Errors

**Symptoms:**
- Certificates not generating
- Blank PDFs
- TCPDF errors

**Solutions:**

#### A. Check Template
- Ensure template has valid background PDF
- Verify template_data is not null
- Check placeholders are correctly formatted

#### B. Check Memory Limit
```ini
# In php.ini
memory_limit = 256M
max_execution_time = 300
```

#### C. Test PDF Generation
```bash
php artisan tinker
>>> $template = App\Models\CertificateTemplate::first();
>>> $participant = App\Models\Participant::first();
# Try generating certificate manually
```

## Debug Mode

### Enable Debug Mode (Development Only)
```env
APP_DEBUG=true
APP_ENV=local
```

### Check Logs
```bash
# Laravel log
type storage\logs\laravel.log

# Web server log (Nginx)
type /var/log/nginx/error.log

# Web server log (Apache)
type /var/log/apache2/error.log
```

### Verbose Errors
```bash
# Run commands with verbose flag
php artisan migrate --verbose
php artisan queue:work --verbose
```

## Getting Help

If issues persist:

1. Check Laravel logs: `storage/logs/laravel.log`
2. Check browser console for JavaScript errors
3. Check network tab for failed requests
4. Enable debug mode temporarily
5. Check system requirements in `README.md`

## Useful Commands

```bash
# Clear all caches
php artisan optimize:clear

# Check application status
php artisan about

# Check routes
php artisan route:list

# Check database connection
php artisan db:show

# Check migrations status
php artisan migrate:status

# Verify certificate templates
php artisan templates:verify

# Fix template URLs
php artisan templates:fix-urls

# Run diagnostics
php artisan tinker
>>> app()->version()
>>> config('app.env')
>>> config('database.default')
```

## Prevention

### Regular Maintenance
- Keep backups of database and files
- Monitor logs regularly
- Update dependencies periodically
- Test after major changes
- Document custom configurations

### Best Practices
- Use version control (Git)
- Test in staging before production
- Keep .env secure and backed up
- Monitor disk space
- Set up proper logging and monitoring

## Related Documentation

- **Setup**: `QUICK_START.md`
- **Deployment**: `DEPLOYMENT.md`
- **Firebase**: `FIREBASE_SETUP.md`
- **Main README**: `README.md`
