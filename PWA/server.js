import express from 'express';
import path from 'path';
import compression from 'compression';
import { fileURLToPath } from 'url';

const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);

const app = express();

// Get port from environment or default to 3000
const PORT = process.env.PORT || 3000;

// Enable gzip compression
app.use(compression());

// Security headers
app.use((req, res, next) => {
  res.setHeader('X-Content-Type-Options', 'nosniff');
  res.setHeader('X-Frame-Options', 'DENY');
  res.setHeader('X-XSS-Protection', '1; mode=block');
  next();
});

// Custom middleware to set correct MIME types
app.use((req, res, next) => {
  const ext = path.extname(req.url).toLowerCase();
  
  const mimeTypes = {
    '.js': 'application/javascript; charset=UTF-8',
    '.mjs': 'application/javascript; charset=UTF-8',
    '.jsx': 'text/jsx; charset=UTF-8',
    '.css': 'text/css; charset=UTF-8',
    '.json': 'application/json; charset=UTF-8',
    '.html': 'text/html; charset=UTF-8',
    '.png': 'image/png',
    '.jpg': 'image/jpeg',
    '.jpeg': 'image/jpeg',
    '.gif': 'image/gif',
    '.svg': 'image/svg+xml',
    '.webp': 'image/webp',
    '.ico': 'image/x-icon',
    '.woff': 'font/woff',
    '.woff2': 'font/woff2',
    '.ttf': 'font/ttf',
    '.eot': 'application/vnd.ms-fontobject',
    '.wasm': 'application/wasm',
    '.webmanifest': 'application/manifest+json; charset=UTF-8',
    '.mp3': 'audio/mpeg'
  };

  if (mimeTypes[ext]) {
    res.setHeader('Content-Type', mimeTypes[ext]);
  }
  
  next();
});

// Serve static files from dist directory
app.use(express.static(path.join(__dirname, 'dist'), {
  maxAge: '1d',
  etag: true,
  index: false // Don't auto-serve index.html
}));

// Service worker - no cache
app.get('/sw.js', (req, res) => {
  res.setHeader('Content-Type', 'application/javascript; charset=UTF-8');
  res.setHeader('Cache-Control', 'no-cache, no-store, must-revalidate');
  res.setHeader('Pragma', 'no-cache');
  res.setHeader('Expires', '0');
  res.sendFile(path.join(__dirname, 'dist', 'sw.js'));
});

// Workbox files - no cache
app.get('/workbox-*.js', (req, res) => {
  res.setHeader('Content-Type', 'application/javascript; charset=UTF-8');
  res.setHeader('Cache-Control', 'no-cache, no-store, must-revalidate');
  const filename = path.basename(req.url);
  res.sendFile(path.join(__dirname, 'dist', filename));
});

// Manifest file
app.get('/manifest.webmanifest', (req, res) => {
  res.setHeader('Content-Type', 'application/manifest+json; charset=UTF-8');
  res.setHeader('Cache-Control', 'public, max-age=86400');
  res.sendFile(path.join(__dirname, 'dist', 'manifest.webmanifest'));
});

// Health check endpoint
app.get('/health', (req, res) => {
  res.json({ status: 'ok', timestamp: new Date().toISOString() });
});

// Handle client-side routing - send index.html for all routes
app.get('*', (req, res) => {
  res.setHeader('Content-Type', 'text/html; charset=UTF-8');
  res.sendFile(path.join(__dirname, 'dist', 'index.html'));
});

// Error handling
app.use((err, req, res, next) => {
  console.error('Server error:', err);
  res.status(500).json({ error: 'Internal server error' });
});

// Start server
const server = app.listen(PORT, '0.0.0.0', () => {
  console.log(`PWA server running on port ${PORT}`);
  console.log(`Environment: ${process.env.NODE_ENV || 'development'}`);
  console.log(`Serving from: ${path.join(__dirname, 'dist')}`);
});

// Graceful shutdown
const shutdown = () => {
  console.log('Shutting down gracefully...');
  server.close(() => {
    console.log('Server closed');
    process.exit(0);
  });
  
  // Force close after 10 seconds
  setTimeout(() => {
    console.error('Forcing shutdown...');
    process.exit(1);
  }, 10000);
};

process.on('SIGTERM', shutdown);
process.on('SIGINT', shutdown);
