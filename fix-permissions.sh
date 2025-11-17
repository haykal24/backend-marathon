#!/bin/bash
#
# Script untuk fix permissions Laravel di cPanel
# Jalankan script ini di cPanel Terminal setelah deployment
#
# Usage: bash fix-permissions.sh
#

echo "🔧 Fixing Laravel Permissions for cPanel..."
echo ""

# Get current directory
CURRENT_DIR=$(pwd)
echo "📂 Working directory: $CURRENT_DIR"
echo ""

# Create storage directories if not exists
echo "📁 Creating storage directories..."
mkdir -p storage/framework/{sessions,views,cache/data}
mkdir -p storage/logs
mkdir -p storage/app/public
mkdir -p bootstrap/cache

# Set permissions
echo "🔐 Setting permissions..."
chmod -R 775 storage/
chmod -R 775 bootstrap/cache/

# Clear compiled views if exists
echo "🧹 Clearing compiled views..."
rm -rf storage/framework/views/*.php 2>/dev/null || true

# Clear all caches
echo "🗑️  Clearing all caches..."
php artisan optimize:clear 2>/dev/null || echo "⚠️  optimize:clear failed (might be normal on first run)"

# Create symbolic link for storage
echo "🔗 Creating storage symbolic link..."
php artisan storage:link --force

# Optimize for production
echo "⚡ Optimizing for production..."
php artisan filament:optimize
php artisan optimize

echo ""
echo "✅ Permissions fixed successfully!"
echo ""
echo "📋 Next steps:"
echo "   1. Make sure .env is configured correctly"
echo "   2. Run: php artisan migrate --force"
echo "   3. Run: php artisan db:seed --class=ShieldSeeder (only once)"
echo "   4. Visit your admin panel: https://yourdomain.com/admin"
echo ""

