#!/bin/bash

# PWA Build Script for cPanel Deployment
# This script builds the PWA and prepares it for cPanel upload

echo "🚀 Building PWA for cPanel deployment..."

# Build PWA
npm run build

# Copy .htaccess to dist
cp .htaccess dist/.htaccess

# Ensure favicon files exist (fallback to logo.png if missing)
if [ ! -f dist/favicon-32x32.png ]; then
  cp dist/logo.png dist/favicon-32x32.png 2>/dev/null || true
fi
if [ ! -f dist/favicon-16x16.png ]; then
  cp dist/logo.png dist/favicon-16x16.png 2>/dev/null || true
fi

echo "✅ Build complete!"
echo ""
echo "📦 Files ready for upload in: dist/"
echo ""
echo "📋 Upload these files to cPanel:"
echo "   - All files from dist/ folder"
echo "   - Including .htaccess"
echo ""
echo "🌐 Upload to: /home/kflegacy/user.e-certificate.com.my/"
echo ""
echo "⚠️  IMPORTANT:"
echo "   1. Upload .htaccess first"
echo "   2. Ensure SSL is enabled"
echo "   3. Clear browser cache after upload"
echo ""

