#!/bin/bash
set -e

echo "🚀 Memulai Deployment Update UKK..."

# Direktori project di server
TARGET_DIR="/var/www/ukkmuhyiyuddin"

if [ -d "$TARGET_DIR" ]; then
    cd "$TARGET_DIR"
fi

# Aktifkan mode maintenance sementara
php artisan down || true

# Tarik perubahan kode terbaru dari GitHub
git pull origin main

# Install dependensi PHP & kompilasi frontend
composer install --no-dev --optimize-autoloader --no-interaction
npm install
npm run build

# Eksekusi migrasi database jika ada tabel baru
php artisan migrate --force

# Segarkan cache Laravel
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Restart worker dan reload PHP-FPM
systemctl restart ukk-worker.service 2>/dev/null || true
systemctl reload php8.4-fpm 2>/dev/null || true

# Nonaktifkan mode maintenance
php artisan up

echo "✅ Deployment Sukses & Website Live!"

