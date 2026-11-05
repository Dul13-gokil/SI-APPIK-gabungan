#!/bin/sh
set -e

# Sesuaikan port Apache dengan variabel PORT yang diberikan oleh Railway
PORT="${PORT:-80}"
sed -i "s/80/$PORT/g" /etc/apache2/ports.conf /etc/apache2/sites-available/000-default.conf

# Generate app key jika belum ada
if [ -z "$APP_KEY" ]; then
    echo "Peringatan: APP_KEY belum diatur di environment variable Railway! Mengenerate APP_KEY..."
    php artisan key:generate --force || true
fi

# Cek & Import Database Otomatis jika database kosong
DB_TARGET="${DATABASE_URL:-}"
if [ -z "$DB_TARGET" ] && [ -n "$DB_HOST" ]; then
    DB_TARGET="postgresql://${DB_USERNAME}:${DB_PASSWORD}@${DB_HOST}:${DB_PORT:-5432}/${DB_DATABASE}"
fi

if [ -n "$DB_TARGET" ] && [ -f "/var/www/html/database/si-appik.sql" ]; then
    echo "Mengecek status koneksi ke Railway PostgreSQL..."
    for i in 1 2 3 4 5; do
        if psql "$DB_TARGET" -c "SELECT 1;" >/dev/null 2>&1; then
            echo "PostgreSQL terhubung!"
            break
        fi
        echo "Menunggu PostgreSQL siap (percobaan $i/5)..."
        sleep 2
    done

    # Cek apakah tabel users sudah ada
    if ! psql "$DB_TARGET" -c "SELECT 1 FROM users LIMIT 1;" >/dev/null 2>&1; then
        echo "Database masih baru/kosong. Mengimpor database/si-appik.sql secara otomatis..."
        psql "$DB_TARGET" < /var/www/html/database/si-appik.sql || true
        echo "Import database awal selesai!"
    else
        echo "Database sudah memiliki tabel data, melewati import awal."
    fi
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
