# =========================================================================
# STAGE 1: Build Frontend Assets (Blade + Tailwind CSS v4 + Vite)
# =========================================================================
FROM node:22-alpine AS frontend-builder
WORKDIR /app

# Copy package config & install dependencies
COPY package.json ./
RUN npm install --prefer-offline --no-audit

# Copy template resources and build config
COPY resources ./resources
COPY vite.config.js ./
COPY public ./public

# Compile production assets to public/build
RUN npm run build

# =========================================================================
# STAGE 2: Build PHP Backend Dependencies (Composer)
# =========================================================================
FROM composer:latest AS backend-builder
WORKDIR /app

COPY composer.json composer.lock ./
RUN composer install \
    --no-dev \
    --no-interaction \
    --prefer-dist \
    --optimize-autoloader \
    --no-scripts \
    --ignore-platform-reqs

# =========================================================================
# STAGE 3: Final Production Runtime (PHP 8.4-FPM Alpine + Nginx + Python 3)
# =========================================================================
FROM php:8.4-fpm-alpine
WORKDIR /var/www/html

# 1. Install dependensi sistem, web server Nginx, Supervisor, dan Python3
RUN apk add --no-cache \
    nginx \
    supervisor \
    curl \
    ca-certificates \
    tzdata \
    libpng-dev \
    libjpeg-turbo-dev \
    freetype-dev \
    libzip-dev \
    icu-dev \
    libxml2-dev \
    oniguruma-dev \
    python3 \
    py3-pip \
    && update-ca-certificates

# 2. Compile ekstensi PHP yang dibutuhkan Laravel 13 & PhpSpreadsheet
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
        pdo_mysql \
        mbstring \
        exif \
        pcntl \
        bcmath \
        gd \
        zip \
        intl \
        xml \
        opcache

# 3. Install ekstensi Redis (phpredis) via PECL
RUN apk add --no-cache --virtual .build-deps $PHPIZE_DEPS \
    && pecl install redis \
    && docker-php-ext-enable redis \
    && apk del .build-deps

# 4. Install library Python untuk modul ekstraksi jadwal PDF (extract_pdf_schedules.py)
RUN pip install --no-cache-dir --break-system-packages pdfplumber

# 5. Salin konfigurasi kustom Nginx, Supervisor, PHP, dan Opcache
COPY docker/php/opcache.ini /usr/local/etc/php/conf.d/opcache.ini
COPY docker/php/custom.ini /usr/local/etc/php/conf.d/custom.ini
COPY docker/nginx/nginx.conf /etc/nginx/nginx.conf
COPY docker/supervisor/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

# 6. Salin source code aplikasi dan hasil build
COPY . /var/www/html
COPY --from=backend-builder /app/vendor /var/www/html/vendor
COPY --from=frontend-builder /app/public/build /var/www/html/public/build

# 7. Bersihkan cache lokal laptop, siapkan direktori storage, dan perbaiki permission
RUN rm -f /var/www/html/bootstrap/cache/*.php \
    && mkdir -p /var/www/html/storage/app/public \
    && mkdir -p /var/www/html/storage/framework/cache \
    && mkdir -p /var/www/html/storage/framework/sessions \
    && mkdir -p /var/www/html/storage/framework/views \
    && mkdir -p /var/www/html/storage/logs \
    && chown -R www-data:www-data /var/www/html \
    && chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

EXPOSE 80
CMD ["/usr/bin/supervisord", "-c", "/etc/supervisor/conf.d/supervisord.conf"]

