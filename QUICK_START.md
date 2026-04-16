# Quick Start Guide - Sijil E-Certificate System

## 🚀 Setup dalam 5 Minit

### 1. Install Dependencies
```bash
composer install
npm install
```

### 2. Environment Setup
```bash
# Copy .env
cp .env.example .env

# Generate key
php artisan key:generate

# Edit .env - update database credentials
nano .env
```

### 3. Database Setup
```bash
# Run migrations
php artisan migrate

# Seed data (creates admin user & permissions)
php artisan db:seed
```

### 4. Firebase Setup (Optional - untuk push notifications)
```bash
# Download Firebase service account JSON dari Firebase Console
# Simpan sebagai: storage/credentials/firebase-service-account.json

# Lihat panduan lengkap di FIREBASE_SETUP.md
```

### 5. Build Assets & Run
```bash
# Build frontend
npm run build

# Start development server
php artisan serve

# Atau gunakan composer script (dengan queue & logs)
composer dev
```

### 6. Login
```
URL: http://localhost:8000
Email: admin@e-certificate.com.my
Password: password
```

**⚠️ PENTING: Tukar password selepas first login!**

## 🔧 Common Commands

```bash
# Clear all caches
php artisan optimize:clear

# Run queue worker
php artisan queue:work

# Run scheduled tasks (for testing)
php artisan schedule:run

# Check application status
php artisan about
```

## 📚 Documentation

- **Firebase Setup**: `FIREBASE_SETUP.md`
- **Deployment**: `DEPLOYMENT.md`
- **Changelog**: `CHANGELOG_FIREBASE_FIX.md`

## 🆘 Troubleshooting

### Database connection error
```bash
# Check .env database settings
# Ensure MySQL is running
# Verify database exists
```

### Storage permission error
```bash
chmod -R 775 storage bootstrap/cache
```

### Assets not loading
```bash
npm run build
php artisan storage:link
```

### Queue not processing
```bash
# Check QUEUE_CONNECTION in .env
# Run queue worker manually
php artisan queue:work
```

## 🎯 Next Steps

1. ✅ Setup complete
2. 📖 Read `DEPLOYMENT.md` for production setup
3. 🔐 Configure Firebase (if using notifications)
4. 👥 Create users and assign roles
5. 📝 Create your first event
6. 🎓 Generate certificates

## 💡 Tips

- Use `composer dev` untuk run server + queue + logs sekaligus
- Enable Redis untuk better performance (production)
- Setup supervisor untuk queue workers (production)
- Configure proper backup strategy
- Monitor logs: `storage/logs/laravel.log`

## 🔗 Useful Links

- Laravel Docs: https://laravel.com/docs
- Firebase Console: https://console.firebase.google.com
- Tailwind CSS: https://tailwindcss.com
- Alpine.js: https://alpinejs.dev
