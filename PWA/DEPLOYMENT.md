# PWA Deployment Guide - Plesk

## 📋 Plesk Configuration

### Node.js Settings:

- **Application root:** `/home/kflegacy/user.e-certificate.com.my/`
- **Application URL:** `user.e-certificate.com.my` (or `apps.e-certificate.com.my`)
- **Application startup file:** `server.js`
- **Application mode:** `Production`
- **Node.js version:** `20.19.4 (recommended)`

---

## 🚀 Deployment Steps

### 1. Build PWA Locally

```bash
cd PWA
npm run build
```

This creates the `dist/` folder with compiled files.

### 2. Upload Files to Server

Upload these files to `/home/kflegacy/user.e-certificate.com.my/`:

```
user.e-certificate.com.my/
├── dist/                    # Build output (REQUIRED)
│   ├── index.html
│   ├── assets/
│   ├── manifest.webmanifest
│   └── sw.js
├── server.js               # Express server (REQUIRED)
├── package.json            # Dependencies (REQUIRED)
└── node_modules/          # Will be installed by Plesk
```

**Important:** Upload `dist/`, `server.js`, and `package.json`

### 3. Install Dependencies on Server

Via Plesk:
- Go to Node.js settings
- Click "NPM Install" button

OR via SSH:
```bash
cd /home/kflegacy/user.e-certificate.com.my/
npm install --production
```

### 4. Start Application

Via Plesk:
- Click "Restart App" button

OR via SSH:
```bash
npm start
```

---

## 🔧 Server.js Features

✅ Serves static files from `dist/` folder
✅ Handles client-side routing (React Router)
✅ Gzip compression enabled
✅ Security headers configured
✅ Service Worker support
✅ PWA Manifest support
✅ Graceful shutdown handling

---

## 🌐 Environment Variables (Optional)

Add in Plesk Node.js → Environment Variables:

| Variable | Value | Description |
|----------|-------|-------------|
| `NODE_ENV` | `production` | Production mode |
| `PORT` | `3000` | Server port (optional) |

---

## 📝 Available Scripts

```bash
# Development (local only)
npm run dev

# Build for production
npm run build

# Start server only
npm start

# Build + Start
npm run serve
```

---

## ✅ Verification

After deployment, test:

1. **PWA Access:** `https://user.e-certificate.com.my/`
2. **API Connection:** Check if calls go to `https://login.e-certificate.com.my`
3. **Service Worker:** Check browser DevTools → Application → Service Workers
4. **Install Prompt:** PWA should show install prompt after 2 seconds

---

## 🐛 Troubleshooting

### App won't start:
- Check Node.js version (20.19.4 recommended)
- Verify `dist/` folder exists
- Check Plesk logs

### 404 errors:
- Ensure `server.js` handles all routes (`app.get('*', ...)`)
- Check file permissions

### API not connecting:
- Verify domain in `PWA/src/services/api.js`
- Check CORS settings on backend
- Ensure SSL certificates are valid

---

## 📦 Files to Upload

**Minimum Required:**
1. `dist/` folder (complete)
2. `server.js`
3. `package.json`

**Optional:**
- `package-lock.json` (for exact versions)

**DO NOT upload:**
- `node_modules/` (will be installed by Plesk)
- `src/` (source files not needed)
- `.env` files

---

## 🔄 Update Process

To deploy updates:

1. Build locally: `npm run build`
2. Upload new `dist/` folder
3. Restart app in Plesk

No need to reinstall dependencies if `package.json` unchanged.

---

## 📞 Support

For issues, check:
- Plesk Node.js logs
- Browser console (F12)
- Network tab for API calls

