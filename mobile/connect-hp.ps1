# connect-hp.ps1
param(
    [string]$deviceIp = "100.82.242.85:5555"
)

Write-Host "=========================================" -ForegroundColor Cyan
Write-Host " Menyambungkan ke HP ($deviceIp)..." -ForegroundColor Cyan
Write-Host "=========================================" -ForegroundColor Cyan

adb start-server | Out-Null
adb connect $deviceIp

Start-Sleep -Seconds 1
$devices = adb devices

if ($devices -match "100\.82\.242\.85") {
    Write-Host "[OK] Berhasil terhubung ke HP ($deviceIp)!" -ForegroundColor Green
    adb -s $deviceIp reverse tcp:8000 tcp:8000
    Write-Host "[OK] Port reverse 8000 aktif (localhost:8000 -> PC Laravel)" -ForegroundColor Green
    Write-Host ""
    Write-Host "Daftar Perangkat Terhubung:" -ForegroundColor Yellow
    adb devices -l
} else {
    Write-Host "[GAGAL] Gagal terhubung ke HP $deviceIp." -ForegroundColor Red
    Write-Host "Pastikan HP dan Tailscale/Wi-Fi aktif, serta Wireless Debugging di HP menyala." -ForegroundColor Yellow
}
