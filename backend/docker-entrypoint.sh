#!/bin/sh
set -e

# Sesuaikan port Apache dengan variabel PORT yang diberikan oleh Railway
PORT="${PORT:-80}"
sed -i "s/80/$PORT/g" /etc/apache2/ports.conf /etc/apache2/sites-available/000-default.conf

# Generate app key jika belum ada
if [ -z "$APP_KEY" ]; then
    echo "Peringatan: APP_KEY belum diatur di environment variable Railway!"
fi

# Buat symlink storage jika belum ada
php artisan storage:link --force || true

# Bersihkan & cache konfigurasi untuk kecepatan maksimal
php artisan optimize:clear || true

# Jalankan migrasi database otomatis saat deploy
echo "Menjalankan migrasi database..."
php artisan migrate --force || true

echo "Aplikasi Laravel siap melayani request di port $PORT..."
exec apache2-foreground
