# PWA Deployment Guide - cPanel

## 📋 cPanel Static Deployment (No Node.js Required)

PWA dah di-build jadi static files, so **NO NEED Node.js** di cPanel.

---

## 🚀 Deployment Steps

### 1. Build PWA Locally

```bash
cd PWA
npm run build
```

This creates `dist/` folder with all static files.

### 2. Upload Files to cPanel

**Via File Manager or FTP:**

Upload ALL files dari `dist/` folder ke:
```
/home/kflegacy/public_html/user.e-certificate.com.my/
```

OR if subdomain/addon domain:
```
/home/kflegacy/public_html/
```

**Files structure after upload:**
```
public_html/
├── .htaccess          # Apache config (IMPORTANT!)
├── index.html         # Main HTML
├── assets/            # JS, CSS files
│   ├── index-xxx.js
│   └── index-xxx.css
├── logo.png          # PWA icon
├── manifest.webmanifest  # PWA manifest
├── sw.js             # Service Worker
└── workbox-xxx.js    # Workbox runtime
```

### 3. Upload .htaccess

**IMPORTANT:** Upload `.htaccess` file ke root directory.

This file:
- ✅ Sets correct MIME types
- ✅ Handles React Router
- ✅ Enables HTTPS redirect
- ✅ Sets cache headers
- ✅ Enables gzip compression

---

## 📁 Files to Upload

**From `dist/` folder, upload ALL:**
- ✅ `index.html`
- ✅ `assets/` folder (complete)
- ✅ `logo.png`
- ✅ `manifest.webmanifest`
- ✅ `sw.js`
- ✅ `workbox-*.js`
- ✅ `registerSW.js`

**Also upload:**
- ✅ `.htaccess` (from PWA folder, NOT dist)

**DO NOT upload:**
- ❌ `node_modules/`
- ❌ `src/`
- ❌ `server.js` (not needed for cPanel)
- ❌ `package.json`

---

## 🔧 cPanel Configuration

### Domain Setup:

1. **Main Domain:**
   - Document Root: `/home/kflegacy/public_html/`
   - Upload files directly here

2. **Subdomain (e.g., apps.example.com):**
   - cPanel → Domains → Create Subdomain
   - Subdomain: `apps`
   - Document Root: `/home/kflegacy/public_html/apps`
   - Upload files to this folder

3. **Addon Domain (e.g., user.e-certificate.com.my):**
   - cPanel → Domains → Create Addon Domain
   - New Domain: `user.e-certificate.com.my`
   - Document Root: Auto-created
   - Upload files to the document root

### SSL Certificate:

1. cPanel → SSL/TLS Status
2. Select your domain
3. Click "Run AutoSSL" (Let's Encrypt)
4. Wait for SSL to be installed

---

## ⚙️ .htaccess Configuration

The `.htaccess` file handles:

1. **MIME Types:** Fixes "application/octet-stream" error
2. **React Router:** All routes redirect to `index.html`
3. **HTTPS:** Force SSL redirect
4. **Caching:** Service Worker no-cache, assets cached 1 year
5. **Security Headers:** X-Frame-Options, X-XSS-Protection
6. **Compression:** Gzip for text files

---

## 🐛 Troubleshooting

### Issue 1: "application/octet-stream" MIME type error

**Solution:**
- Ensure `.htaccess` is uploaded
- Check file permissions: `644` for `.htaccess`
- Verify `AddType` directives in `.htaccess`

```bash
# Check via SSH
ls -la /home/kflegacy/public_html/.htaccess
```

### Issue 2: 503 Service Unavailable

**Solution:**
- PWA is static, shouldn't get 503
- Check if files are uploaded correctly
- Verify domain points to correct directory
- Check Apache error logs in cPanel

### Issue 3: Icon not loading (pwa-192x192.png)

**Solution:**
- Upload `logo.png` to root directory
- Clear browser cache
- Check `manifest.webmanifest` paths

### Issue 4: Service Worker not registering

**Solution:**
- **MUST use HTTPS** (HTTP won't work except localhost)
- Ensure `sw.js` has correct MIME type
- Clear cache: Chrome DevTools → Application → Clear Storage

### Issue 5: Routes returning 404

**Solution:**
- `.htaccess` must have RewriteRule for React Router
- Check if `mod_rewrite` is enabled (usually yes in cPanel)

---

## ✅ Verification Checklist

After deployment, test:

- [ ] Visit: `https://user.e-certificate.com.my/`
- [ ] Check SSL (green padlock)
- [ ] PWA install prompt appears
- [ ] Service Worker registers (F12 → Application → Service Workers)
- [ ] Navigate to `/scan`, `/events` - no 404 errors
- [ ] Check API calls go to `https://login.e-certificate.com.my`
- [ ] Test offline mode (after visiting once)

---

## 🔄 Update Process

To deploy updates:

1. **Build locally:**
   ```bash
   npm run build
   ```

2. **Backup old files** (optional):
   ```bash
   # Via cPanel File Manager
   # Compress current files as backup
   ```

3. **Upload new `dist/` files:**
   - Replace all files in document root
   - Keep `.htaccess` (don't overwrite)

4. **Clear cache:**
   - Browser cache
   - Service Worker cache (auto-updates)
   - cPanel might cache, wait 5-10 minutes

---

## 📊 File Permissions

Set correct permissions via cPanel File Manager:

```
Directories: 755
Files: 644
.htaccess: 644
```

---

## 🌐 Multiple Environments

### Development:
- Local: `http://localhost:3000`
- API: `http://localhost:8000`

### Production:
- PWA: `https://user.e-certificate.com.my`
- API: `https://login.e-certificate.com.my`

PWA automatically detects environment (see `PWA/src/services/api.js`)

---

## 💡 Tips

1. **Always use HTTPS** - PWA features require SSL
2. **Upload .htaccess first** - Prevents MIME type errors
3. **Test on mobile** - PWA install prompt works best on mobile
4. **Check logs** - cPanel → Metrics → Errors for Apache errors
5. **Clear cache often** - Browser aggressively caches PWA

---

## 📞 Support

### Common cPanel Locations:

- **Error Logs:** cPanel → Metrics → Errors
- **File Manager:** cPanel → Files → File Manager
- **SSL:** cPanel → Security → SSL/TLS Status
- **Domains:** cPanel → Domains

### Testing Tools:

- Chrome DevTools → Application tab
- Lighthouse (PWA audit)
- Network tab (check MIME types)

---

## ✨ Production Checklist

Before going live:

- [x] Build PWA: `npm run build`
- [x] Upload all `dist/` files
- [x] Upload `.htaccess`
- [x] Install SSL certificate
- [x] Test PWA install
- [x] Test Service Worker
- [x] Test all routes
- [x] Test API connection
- [x] Clear all caches
- [x] Test on mobile device

---

**cPanel deployment is SIMPLER than Plesk - just upload static files!** 🎉

