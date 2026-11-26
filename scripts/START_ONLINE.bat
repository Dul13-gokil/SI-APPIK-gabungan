@echo off
title SI-APPIK - TAILSCALE FUNNEL ONLINE
color 0B

echo =====================================================================
echo    SISTEM INFORMASI SI-APPIK - TAILSCALE FUNNEL (DOMAIN PERMANEN)
echo =====================================================================
echo.
echo Domain Publik Permanen:
echo https://dul-1378.tail09bd7a.ts.net/
echo.
echo =====================================================================

cd /d "%~dp0.."

echo [1/3] Memeriksa & Menjalankan Laravel Backend di port 8000...
netstat -ano | findstr :8000 | findstr LISTENING >nul
if %errorlevel% neq 0 (
    echo       Menyalakan Laravel di latar belakang...
    start /B "" "backend\artisan" serve --host=127.0.0.1 --port=8000 >nul 2>&1
    :: Alternatif menggunakan php artisan
    start /B php "backend\artisan" serve --host=127.0.0.1 --port=8000 >nul 2>&1
    timeout /t 2 /nobreak >nul
) else (
    echo       Laravel Backend sudah aktif di port 8000.
)

echo.
echo [2/3] Mengaktifkan Tailscale Funnel ke port 8000...
tailscale funnel --yes --bg 8000

echo.
echo [3/3] Membuka Website di Google Chrome...
start https://dul-1378.tail09bd7a.ts.net/

echo.
echo =====================================================================
echo    BERHASIL! WEBSITE DAN API ANDA SEKARANG AKTIF ONLINE!
echo    URL Permanen: https://dul-1378.tail09bd7a.ts.net/
echo    Aplikasi Flutter: Sudah otomatis terhubung ke URL ini.
echo =====================================================================
echo.
echo Tekan tombol apa saja untuk menutup jendela ini...
pause >nul
