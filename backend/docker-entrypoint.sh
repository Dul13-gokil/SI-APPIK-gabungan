#!/bin/sh
set -e

# Pastikan hanya satu MPM (mpm_prefork) yang aktif di Apache
rm -f /etc/apache2/mods-enabled/mpm_event.* /etc/apache2/mods-enabled/mpm_worker.* || true
a2enmod mpm_prefork rewrite deflate expires headers >/dev/null 2>&1 || true

# Sesuaikan port Apache dengan variabel PORT yang diberikan oleh Railway
PORT="${PORT:-80}"
sed -i "s/Listen 80/Listen $PORT/g" /etc/apache2/ports.conf
sed -i "s/<VirtualHost \*:80>/<VirtualHost *:$PORT>/g" /etc/apache2/sites-available/000-default.conf

# Generate app key jika belum ada
if [ -z "$APP_KEY" ]; then
    echo "Peringatan: APP_KEY belum diatur di environment variable Railway! Mengenerate APP_KEY..."
    php artisan key:generate --force || true
fi

# Cek & Import Database Otomatis jika tabel complaints belum ada
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

    # Penanda versi dump database agar Railway mengimpor ulang si-appik.sql terbaru
    DB_DUMP_VERSION="20261119_kabupaten_v1"
    CURRENT_SYNC_VERSION=$(psql "$DB_TARGET" -t -A -c "SELECT version FROM public.app_db_sync_marker LIMIT 1;" 2>/dev/null || echo "")

    if [ "$CURRENT_SYNC_VERSION" != "$DB_DUMP_VERSION" ] || ! psql "$DB_TARGET" -c "SELECT 1 FROM public.complaints LIMIT 1;" >/dev/null 2>&1; then
        echo "Mendeteksi versi dump database baru ($DB_DUMP_VERSION). Mereset skema dan mengimpor database/si-appik.sql secara utuh..."
        psql "$DB_TARGET" -c "DROP SCHEMA IF EXISTS public CASCADE; CREATE SCHEMA public; GRANT ALL ON SCHEMA public TO public;" || true
        psql "$DB_TARGET" < /var/www/html/database/si-appik.sql || true
        psql "$DB_TARGET" -c "CREATE TABLE IF NOT EXISTS public.app_db_sync_marker (version VARCHAR(64) PRIMARY KEY, synced_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP); TRUNCATE TABLE public.app_db_sync_marker; INSERT INTO public.app_db_sync_marker (version) VALUES ('$DB_DUMP_VERSION');" || true
        echo "Import database versi $DB_DUMP_VERSION selesai dengan sukses!"
    else
        echo "Database sudah sinkron dengan versi $DB_DUMP_VERSION, melewati import ulang."
    fi
fi

# Buat ulang symlink storage dengan bersih dan pastikan permission dapat dibaca Apache
mkdir -p /var/www/html/storage/app/public
rm -rf /var/www/html/public/storage || true
php artisan storage:link --force || true
chown -R www-data:www-data /var/www/html/storage || true
chmod -R 775 /var/www/html/storage || true

# Bersihkan lalu bangun ulang cache produksi Laravel (config, routes, views)
php artisan optimize:clear || true
php artisan optimize || true

# Jalankan migrasi database otomatis saat deploy
echo "Menjalankan migrasi database..."
php artisan migrate --force || true

echo "Aplikasi Laravel siap melayani request di port $PORT..."
exec apache2-foreground
