# Sijil - E-Certificate Management System

Sistem pengurusan sijil elektronik yang komprehensif untuk acara, latihan, dan program. Dibangunkan menggunakan Laravel 12 dengan sokongan penuh untuk pengurusan peserta, kehadiran, sijil digital, dan notifikasi push.

## ✨ Ciri-ciri Utama

### 🎯 Pengurusan Acara
- Cipta dan urus acara dengan mudah
- QR code untuk pendaftaran peserta
- Public registration forms
- Event poster upload
- Conditional attendance tracking

### 👥 Pengurusan Peserta
- Database peserta terpusat
- Import bulk participants
- PWA participant system dengan mobile app support
- Identity verification (IC/Passport)
- Participant profiles dengan address & demographics

### ✅ Sistem Kehadiran
- QR code check-in/check-out
- Multiple attendance sessions per event
- GPS coordinates tracking
- Manual check-in option
- Real-time attendance monitoring
- Archive system untuk historical data

### 🎓 Sijil Digital
- Template designer dengan drag-and-drop
- PDF certificate generation
- Bulk certificate creation
- Email delivery automation
- Certificate verification system
- Custom placeholders support

### 📊 Laporan & Analitik
- Attendance reports dengan export
- Certificate distribution reports
- Event statistics dashboard
- Activity logs & audit trails
- Security audit logs

### 📧 Campaign Management
- Email campaigns dengan tracking
- SMS campaigns (Infobip integration)
- Open & click tracking
- Template management
- Scheduled delivery

### 🎫 Helpdesk System
- Ticket management
- Real-time notifications
- File attachments support
- Status tracking (Open, In Progress, Resolved, Closed)
- Admin & user notifications

### 📝 Survey System
- Create surveys dengan multiple question types
- Public/Private/Registered access control
- Anonymous responses support
- Response analytics & visualization
- Export responses to CSV

### 🔐 Role & Permissions
- Granular permission system
- Administrator & Organizer roles
- Custom role creation
- Permission matrix management
- Activity logging

### 🔔 Push Notifications
- Firebase Cloud Messaging (FCM)
- Real-time browser notifications
- Service worker support
- Token management

## 🛠️ Teknologi

### Backend
- **Framework**: Laravel 12
- **PHP**: 8.1+
- **Database**: MySQL 5.7+
- **Queue**: Database driver (Redis recommended for production)
- **Cache**: Database/Redis

### Frontend
- **Build Tool**: Vite
- **CSS Framework**: Tailwind CSS 3
- **JavaScript**: Alpine.js
- **UI Components**: Flowbite
- **Icons**: Heroicons

### Integrations
- **Firebase**: Cloud Messaging & Admin SDK
- **Infobip**: SMS gateway
- **TCPDF**: PDF generation
- **QR Code**: Bacon QR Code
- **Permissions**: Spatie Laravel Permission

## 📋 Keperluan Sistem

- PHP 8.1 atau lebih tinggi
- MySQL 5.7+ atau MariaDB 10.3+
- Composer 2.x
- Node.js 18+ & NPM
- Redis (optional, recommended untuk production)

### PHP Extensions
- BCMath, Ctype, Fileinfo, JSON, Mbstring
- OpenSSL, PDO, Tokenizer, XML
- GD atau Imagick
- Redis (optional)

## 🚀 Quick Start

```bash
# 1. Clone repository
git clone <repository-url> sijil
cd sijil

# 2. Install dependencies
composer install
npm install

# 3. Setup environment
cp .env.example .env
php artisan key:generate

# 4. Configure database dalam .env
# DB_DATABASE=esijil
# DB_USERNAME=root
# DB_PASSWORD=root

# 5. Run migrations & seeders
php artisan migrate
php artisan db:seed

# 6. Build assets
npm run build

# 7. Create storage link
php artisan storage:link

# 8. Start development server
php artisan serve
```

**Default Login:**
- Email: `admin@e-certificate.com.my`
- Password: `password`

⚠️ **Tukar password selepas first login!**

## 📚 Dokumentasi

- **[Quick Start Guide](QUICK_START.md)** - Setup dalam 5 minit
- **[Firebase Setup](FIREBASE_SETUP.md)** - Configure push notifications
- **[Deployment Guide](DEPLOYMENT.md)** - Production deployment
- **[Changelog](CHANGELOG_FIREBASE_FIX.md)** - Recent updates

## 🔧 Development

### Run Development Server
```bash
# Option 1: Laravel server only
php artisan serve

# Option 2: Full stack (server + queue + logs + vite)
composer dev
```

### Queue Worker
```bash
php artisan queue:work
```

### Watch Assets
```bash
npm run dev
```

### Clear Caches
```bash
php artisan optimize:clear
```

### Maintenance Commands
```bash
# Verify certificate templates
php artisan templates:verify

# Fix template URLs
php artisan templates:fix-urls

# Check application status
php artisan about
```

## 🏗️ Struktur Modul

```
sijil/
├── app/
│   ├── Console/Commands/      # Scheduled tasks
│   ├── Events/                # Event classes
│   ├── Http/Controllers/      # Controllers
│   ├── Models/                # Eloquent models
│   ├── Services/              # Business logic
│   └── Helpers/               # Helper functions
├── database/
│   ├── migrations/            # Database migrations
│   └── seeders/               # Database seeders
├── resources/
│   ├── views/                 # Blade templates
│   ├── js/                    # JavaScript files
│   └── css/                   # Stylesheets
├── routes/
│   ├── web.php               # Web routes
│   ├── api.php               # API routes
│   └── channels.php          # Broadcasting channels
└── storage/
    ├── credentials/          # Firebase & other credentials
    └── app/public/           # Public storage
```

## 🔐 Security

- Environment variables untuk sensitive data
- Firebase credentials dalam `storage/credentials/` (git-ignored)
- CSRF protection enabled
- XSS protection
- SQL injection protection (Eloquent ORM)
- Password hashing (bcrypt)
- Activity logging & audit trails

## 🧪 Testing

```bash
# Run tests
php artisan test

# Run specific test
php artisan test --filter=TestName
```

## 📦 Production Deployment

Lihat [DEPLOYMENT.md](DEPLOYMENT.md) untuk panduan lengkap deployment ke production.

### Quick Checklist
- [ ] Set `APP_ENV=production`
- [ ] Set `APP_DEBUG=false`
- [ ] Configure proper database
- [ ] Setup Firebase credentials
- [ ] Configure mail server
- [ ] Setup queue workers (Supervisor)
- [ ] Setup cron jobs
- [ ] Configure web server (Nginx/Apache)
- [ ] Enable SSL certificate
- [ ] Setup backups
- [ ] Configure monitoring

## 🤝 Contributing

1. Fork repository
2. Create feature branch (`git checkout -b feature/AmazingFeature`)
3. Commit changes (`git commit -m 'Add some AmazingFeature'`)
4. Push to branch (`git push origin feature/AmazingFeature`)
5. Open Pull Request

## 📝 License

This project is proprietary software. All rights reserved.

## 👥 Team

- **Developer**: [Your Name]
- **Organization**: [Your Organization]

## 📞 Support

Untuk sokongan dan pertanyaan:
- Email: support@e-certificate.com.my
- Documentation: Lihat folder docs/
- Issues: Gunakan GitHub Issues

## 🙏 Acknowledgments

- Laravel Framework
- Tailwind CSS
- Alpine.js
- Firebase
- Spatie Laravel Permission
- Dan semua open source contributors

---

**Version**: 1.0.0  
**Last Updated**: April 16, 2026  
**Status**: Production Ready (with proper configuration)
