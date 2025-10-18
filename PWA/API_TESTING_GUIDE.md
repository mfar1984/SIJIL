# API Testing Guide - PWA SIJIL

## 🌐 API Auto-Detection

API akan automatically detect berdasarkan hostname PWA:

### Scenario 1: Access dari localhost
**URL PWA:** `http://localhost:5173`  
**API akan guna:** `http://localhost:8000`

### Scenario 2: Access dari IP network (192.168.10.110)
**URL PWA:** `http://192.168.10.110:5173`  
**API akan guna:** `http://192.168.10.110:8000`

### Scenario 3: Access dari IP lain (contoh: 192.168.10.120)
**URL PWA:** `http://192.168.10.120:5173`  
**API akan guna:** `http://192.168.10.120:8000`

---

## ✅ Check API URL di Browser Console

1. Buka browser
2. Access PWA (contoh: http://192.168.10.110:5173)
3. Buka Developer Tools (F12)
4. Check Console tab
5. Cari message:
   ```
   🌐 API Base URL (network): http://192.168.10.110:8000
   ✅ API configured: http://192.168.10.110:8000
   ```

---

## �� Cara Test dengan Backend Laravel

### Step 1: Start Laravel Backend
```bash
cd /Users/faizan/Downloads/Faizan/Faizan/Programming/sijil
php artisan serve --host=0.0.0.0 --port=8000
```

Output:
```
Server running on [http://0.0.0.0:8000]
```

### Step 2: Check Laravel accessible dari network
Test di browser:
```
http://192.168.10.110:8000
```

Should show Laravel welcome atau login page.

### Step 3: Start PWA Dev Server
```bash
cd PWA
npm run dev -- --host
```

Output:
```
VITE v5.x.x  ready in xxx ms

➜  Local:   http://localhost:5173/
➜  Network: http://192.168.10.110:5173/
```

### Step 4: Test Login
1. Access PWA: `http://192.168.10.110:5173`
2. Go to Login page
3. Check browser console - should see:
   ```
   🌐 API Base URL (network): http://192.168.10.110:8000
   ✅ API configured: http://192.168.10.110:8000
   ```
4. Try login dengan PWA participant credentials
5. Check Network tab untuk request ke `http://192.168.10.110:8000/api/participant/login`

---

## 🐛 Troubleshooting

### Issue: CORS Error
**Error:** `Access to XMLHttpRequest has been blocked by CORS policy`

**Solution:** Check Laravel `config/cors.php`:
```php
'paths' => ['api/*', 'sanctum/csrf-cookie'],
'allowed_origins' => ['*'], // or specific: ['http://192.168.10.110:5173']
'allowed_methods' => ['*'],
'allowed_headers' => ['*'],
```

Run:
```bash
php artisan config:clear
php artisan cache:clear
```

### Issue: API not responding
**Check:**
1. Laravel backend running? `php artisan serve --host=0.0.0.0`
2. Port 8000 accessible? Try: `http://192.168.10.110:8000`
3. Firewall blocking? Check system firewall settings

### Issue: 401 Unauthorized after login
**Check:**
1. Token saved di localStorage? Check Application > Local Storage
2. Authorization header attached? Check Network > Headers
3. Sanctum configured correctly?

---

## 📱 Test API Endpoints

### Auth Endpoints
```javascript
// Login
POST http://192.168.10.110:8000/api/participant/login
Body: { username: "xxx", password: "xxx" }

// Get Profile (need token)
GET http://192.168.10.110:8000/api/participant/profile
Headers: { Authorization: "Bearer token_here" }
```

### Data Endpoints
```javascript
// Get Events
GET http://192.168.10.110:8000/api/participant/events

// Get Certificates
GET http://192.168.10.110:8000/api/participant/certificates

// Get Attendance History
GET http://192.168.10.110:8000/api/participant/attendance-history
```

---

## ✨ Quick Commands

### Start everything:
```bash
# Terminal 1 - Laravel Backend
cd /Users/faizan/Downloads/Faizan/Faizan/Programming/sijil
php artisan serve --host=0.0.0.0 --port=8000

# Terminal 2 - PWA Frontend
cd /Users/faizan/Downloads/Faizan/Faizan/Programming/sijil/PWA
npm run dev -- --host
```

### Access:
- **PWA:** http://192.168.10.110:5173
- **Laravel:** http://192.168.10.110:8000
- **PWA (localhost):** http://localhost:5173
- **Laravel (localhost):** http://localhost:8000

---

**API akan auto-detect dan guna IP yang sama dengan PWA!** ✅
