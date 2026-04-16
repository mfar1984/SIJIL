# Deployment Guide - Sijil E-Certificate System

## Pre-Deployment Checklist

### 1. Environment Configuration

#### Required Files
- [ ] `.env` file configured for production
- [ ] `storage/credentials/firebase-service-account.json` uploaded
- [ ] SSL certificate installed
- [ ] Domain configured

#### Environment Variables to Update

```env
# Application
APP_NAME=Sijil
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-domain.com

# Database
DB_CONNECTION=mysql
DB_HOST=your-db-host
DB_PORT=3306
DB_DATABASE=your-database
DB_USERNAME=your-username
DB_PASSWORD=your-secure-password

# Mail Configuration
MAIL_MAILER=smtp
MAIL_HOST=your-smtp-host
MAIL_PORT=587
MAIL_USERNAME=your-email
MAIL_PASSWORD=your-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@your-domain.com
MAIL_FROM_NAME="${APP_NAME}"

# Firebase (See FIREBASE_SETUP.md)
FIREBASE_PROJECT_ID=your-production-project
GOOGLE_APPLICATION_CREDENTIALS="${APP_BASE_PATH}/storage/credentials/firebase-service-account.json"
FCM_ENABLED=true

# Queue & Cache
QUEUE_CONNECTION=database
CACHE_STORE=redis
SESSION_DRIVER=redis
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379
```

### 2. Server Requirements

#### Minimum Requirements
- PHP 8.1 or higher
- MySQL 5.7+ or MariaDB 10.3+
- Composer 2.x
- Node.js 18+ & NPM
- Redis (recommended for cache/sessions)

#### PHP Extensions Required
```
- BCMath
- Ctype
- Fileinfo
- JSON
- Mbstring
- OpenSSL
- PDO
- Tokenizer
- XML
- GD or Imagick
- Redis (optional but recommended)
```

### 3. Installation Steps

#### Step 1: Clone & Setup
```bash
# Clone repository
git clone <repository-url> sijil
cd sijil

# Install PHP dependencies
composer install --optimize-autoloader --no-dev

# Install Node dependencies
npm install

# Build frontend assets
npm run build
```

#### Step 2: Environment Configuration
```bash
# Copy environment file
cp .env.example .env

# Edit .env with production values
nano .env

# Generate application key
php artisan key:generate
```

#### Step 3: Firebase Setup
```bash
# Create credentials directory
mkdir -p storage/credentials

# Upload Firebase service account JSON
# Place file as: storage/credentials/firebase-service-account.json

# Set proper permissions
chmod 600 storage/credentials/firebase-service-account.json
```

#### Step 4: Database Setup
```bash
# Run migrations
php artisan migrate --force

# Seed initial data (roles, permissions, admin user)
php artisan db:seed --force
```

#### Step 5: Storage & Permissions
```bash
# Create storage link
php artisan storage:link

# Set proper permissions
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
```

#### Step 6: Optimize Application
```bash
# Cache configuration
php artisan config:cache

# Cache routes
php artisan route:cache

# Cache views
php artisan view:cache

# Optimize autoloader
composer dump-autoload --optimize
```

### 4. Queue Worker Setup

#### Using Supervisor (Recommended)

Create `/etc/supervisor/conf.d/sijil-worker.conf`:

```ini
[program:sijil-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /path/to/sijil/artisan queue:work database --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=/path/to/sijil/storage/logs/worker.log
stopwaitsecs=3600
```

Start supervisor:
```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start sijil-worker:*
```

### 5. Cron Job Setup

Add to crontab:
```bash
* * * * * cd /path/to/sijil && php artisan schedule:run >> /dev/null 2>&1
```

### 6. Web Server Configuration

#### Nginx Configuration

```nginx
server {
    listen 80;
    listen [::]:80;
    server_name your-domain.com;
    return 301 https://$server_name$request_uri;
}

server {
    listen 443 ssl http2;
    listen [::]:443 ssl http2;
    server_name your-domain.com;
    root /path/to/sijil/public;

    # SSL Configuration
    ssl_certificate /path/to/ssl/cert.pem;
    ssl_certificate_key /path/to/ssl/key.pem;
    ssl_protocols TLSv1.2 TLSv1.3;
    ssl_ciphers HIGH:!aNULL:!MD5;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    index index.php;

    charset utf-8;

    # Increase upload size for certificates
    client_max_body_size 20M;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.1-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
        fastcgi_hide_header X-Powered-By;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

#### Apache Configuration

```apache
<VirtualHost *:80>
    ServerName your-domain.com
    Redirect permanent / https://your-domain.com/
</VirtualHost>

<VirtualHost *:443>
    ServerName your-domain.com
    DocumentRoot /path/to/sijil/public

    SSLEngine on
    SSLCertificateFile /path/to/ssl/cert.pem
    SSLCertificateKeyFile /path/to/ssl/key.pem

    <Directory /path/to/sijil/public>
        AllowOverride All
        Require all granted
    </Directory>

    # Increase upload size
    php_value upload_max_filesize 20M
    php_value post_max_size 20M

    ErrorLog ${APACHE_LOG_DIR}/sijil-error.log
    CustomLog ${APACHE_LOG_DIR}/sijil-access.log combined
</VirtualHost>
```

### 7. Security Hardening

#### File Permissions
```bash
# Application files
find /path/to/sijil -type f -exec chmod 644 {} \;
find /path/to/sijil -type d -exec chmod 755 {} \;

# Writable directories
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache

# Sensitive files
chmod 600 .env
chmod 600 storage/credentials/firebase-service-account.json
```

#### Environment Security
```bash
# Ensure these are set in .env
APP_DEBUG=false
APP_ENV=production

# Remove development files
rm -rf tests/
rm phpunit.xml
rm .editorconfig
```

### 8. Post-Deployment Verification

#### Health Checks
```bash
# Check application status
php artisan about

# Test database connection
php artisan tinker
>>> DB::connection()->getPdo();

# Verify queue is working
php artisan queue:work --once

# Check scheduled tasks
php artisan schedule:list
```

#### Browser Tests
- [ ] Login page loads
- [ ] Admin can login
- [ ] Dashboard displays correctly
- [ ] Create test event
- [ ] Generate test certificate
- [ ] Send test notification (if FCM enabled)
- [ ] Check all permissions work

### 9. Monitoring Setup

#### Log Monitoring
```bash
# Monitor Laravel logs
tail -f storage/logs/laravel.log

# Monitor queue worker
tail -f storage/logs/worker.log

# Monitor web server
tail -f /var/log/nginx/error.log
```

#### Recommended Tools
- **Uptime Monitoring**: UptimeRobot, Pingdom
- **Error Tracking**: Sentry, Bugsnag
- **Performance**: New Relic, Blackfire
- **Log Management**: Papertrail, Loggly

### 10. Backup Strategy

#### Database Backup
```bash
# Daily backup script
#!/bin/bash
DATE=$(date +%Y%m%d_%H%M%S)
mysqldump -u username -p database_name > /backups/sijil_$DATE.sql
gzip /backups/sijil_$DATE.sql

# Keep only last 30 days
find /backups -name "sijil_*.sql.gz" -mtime +30 -delete
```

#### File Backup
```bash
# Backup storage directory
tar -czf /backups/sijil_storage_$DATE.tar.gz /path/to/sijil/storage

# Backup .env and credentials
tar -czf /backups/sijil_config_$DATE.tar.gz \
    /path/to/sijil/.env \
    /path/to/sijil/storage/credentials/
```

### 11. Maintenance Mode

#### Enable Maintenance
```bash
php artisan down --secret="your-secret-token"
# Access via: https://your-domain.com/your-secret-token
```

#### Disable Maintenance
```bash
php artisan up
```

### 12. Troubleshooting

#### Common Issues

**500 Internal Server Error**
```bash
# Check logs
tail -f storage/logs/laravel.log

# Clear all caches
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
```

**Queue not processing**
```bash
# Restart queue worker
sudo supervisorctl restart sijil-worker:*

# Check failed jobs
php artisan queue:failed
```

**Firebase authentication error**
```bash
# Verify credentials file exists
ls -la storage/credentials/firebase-service-account.json

# Check file permissions
chmod 600 storage/credentials/firebase-service-account.json

# Verify environment variable
php artisan tinker
>>> config('services.firebase.credentials')
```

**Storage link broken**
```bash
# Remove old link
rm public/storage

# Recreate link
php artisan storage:link
```

### 13. Update Procedure

```bash
# Enable maintenance mode
php artisan down

# Pull latest code
git pull origin main

# Update dependencies
composer install --optimize-autoloader --no-dev
npm install
npm run build

# Run migrations
php artisan migrate --force

# Clear and rebuild caches
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Restart queue workers
sudo supervisorctl restart sijil-worker:*

# Disable maintenance mode
php artisan up
```

## Support & Documentation

- **Firebase Setup**: See `FIREBASE_SETUP.md`
- **API Documentation**: See `API_DOCUMENTATION.md` (if available)
- **User Manual**: See `USER_MANUAL.md` (if available)

## Emergency Contacts

- System Administrator: [contact]
- Database Administrator: [contact]
- Firebase Administrator: [contact]
