# Stage 1: Build precompiled Tailwind CSS / Vite assets
FROM node:20-alpine AS assets
WORKDIR /app
COPY package.json package-lock.json ./
RUN npm ci --no-audit --no-fund
COPY vite.config.js ./
COPY resources ./resources
COPY public ./public
RUN npm run build

# Stage 2: Production PHP 8.3 + Apache for Laravel 11/12 on Railway.app
FROM php:8.3-apache

# Install dependencies sistem, ekstensi PHP & Python pendukung AI
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    libzip-dev \
    libpq-dev \
    postgresql-client \
    zip \
    unzip \
    python3 \
    python3-pip \
    && docker-php-ext-install pdo pdo_mysql pdo_pgsql mbstring exif pcntl bcmath gd zip opcache \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Konfigurasi PHP OPcache untuk performa produksi maksimal
RUN { \
    echo 'opcache.enable=1'; \
    echo 'opcache.memory_consumption=128'; \
    echo 'opcache.interned_strings_buffer=16'; \
    echo 'opcache.max_accelerated_files=10000'; \
    echo 'opcache.validate_timestamps=0'; \
} > /usr/local/etc/php/conf.d/opcache-recommended.ini

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Pastikan hanya mpm_prefork yang aktif dan aktifkan mod_rewrite, deflate (gzip), expires, headers
RUN a2dismod mpm_event mpm_worker || true \
    && rm -f /etc/apache2/mods-enabled/mpm_event.* /etc/apache2/mods-enabled/mpm_worker.* \
    && a2enmod mpm_prefork rewrite deflate expires headers

# Konfigurasi Apache DocumentRoot ke folder /public dan aktifkan FollowSymLinks + AllowOverride All
ENV APACHE_DOCUMENT_ROOT=/var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf
RUN sed -ri -e 's!AllowOverride None!AllowOverride All!g' /etc/apache2/apache2.conf

WORKDIR /var/www/html

# Salin source code backend
COPY . /var/www/html

# Salin hasil build Vite CSS/JS dari stage assets
COPY --from=assets /app/public/build /var/www/html/public/build

# Salinan awal GeoJSON di luar public/, dipakai entrypoint untuk mengisi volume bersama
# (volume akan menutupi isi public/geojson milik image).
RUN mkdir -p /var/www/html/geo-seed && cp -a /var/www/html/public/geojson/. /var/www/html/geo-seed/ 2>/dev/null || true

# Install dependensi PHP tanpa dev packages
RUN composer install --no-dev --optimize-autoloader --no-interaction

# Dependensi Computer Vision untuk ai_analyzer.py (opencv-headless + numpy)
COPY requirements.txt /var/www/html/requirements.txt
RUN pip3 install --no-cache-dir --break-system-packages -r /var/www/html/requirements.txt

# Batas upload PHP harus di atas validasi aplikasi (foto maks 10 MB, ruas panjang 5-10 foto).
RUN { \
    echo 'upload_max_filesize=12M'; \
    echo 'post_max_size=64M'; \
    echo 'max_file_uploads=15'; \
    echo 'memory_limit=256M'; \
    echo 'max_execution_time=120'; \
} > /usr/local/etc/php/conf.d/uploads.ini

# Beri permission ke storage dan cache
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache \
    && chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# Salin script entrypoint
COPY docker-entrypoint.sh /usr/local/bin/docker-entrypoint.sh
RUN sed -i 's/\r$//' /usr/local/bin/docker-entrypoint.sh \
    && chmod +x /usr/local/bin/docker-entrypoint.sh

# Port default Railway
EXPOSE 80

ENTRYPOINT ["docker-entrypoint.sh"]
CMD []
