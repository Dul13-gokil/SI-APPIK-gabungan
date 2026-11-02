@echo off
echo =========================================
echo  Menyambungkan ke HP (100.82.242.85:5555)...
echo =========================================
adb start-server >nul 2>&1
adb connect 100.82.242.85:5555
ping -n 2 127.0.0.1 >nul
adb -s 100.82.242.85:5555 reverse tcp:8000 tcp:8000 >nul 2>&1
echo [OK] Berhasil terhubung dan port 8000 reverse aktif!
adb devices -l
