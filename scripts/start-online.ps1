# =====================================================================
# start-online.ps1 - Otomatis Jalankan Laravel & Tailscale Funnel Permanen
# =====================================================================

$ErrorActionPreference = "Continue"

# Skrip ini ada di scripts/; akar repo satu tingkat di atasnya.
$repoRoot = Split-Path -Parent $PSScriptRoot

Write-Host "==========================================================" -ForegroundColor Cyan
Write-Host "   SI-APPIK - TAILSCALE FUNNEL (DOMAIN PERMANEN RESMI)    " -ForegroundColor Cyan
Write-Host "==========================================================" -ForegroundColor Cyan
Write-Host ""

$funnelUrl = "https://dul-1378.tail09bd7a.ts.net/"

# 1. Pastikan Laravel Backend berjalan di port 8000
$port8000 = Get-NetTCPConnection -LocalPort 8000 -State Listen -ErrorAction SilentlyContinue
if (-not $port8000) {
    Write-Host "[1/3] Menjalankan Laravel Backend & Database PostgreSQL..." -ForegroundColor Yellow
    Start-Process -FilePath "php" -ArgumentList "artisan serve --host=127.0.0.1 --port=8000" -WorkingDirectory (Join-Path $repoRoot "backend") -WindowStyle Hidden
    Start-Sleep -Seconds 2
} else {
    Write-Host "[1/3] Laravel Backend sudah aktif di port 8000." -ForegroundColor Green
}

# 2. Arahkan Tailscale Funnel ke port 8000
Write-Host "[2/3] Mengaktifkan Tailscale Funnel ke port 8000..." -ForegroundColor Yellow
tailscale funnel --yes --bg 8000

# 3. Sinkronkan ke app_config.dart untuk mobile Flutter
$appConfigFile = Join-Path $PSScriptRoot "mobile\lib\core\config\app_config.dart"
if (Test-Path $appConfigFile) {
    $content = Get-Content $appConfigFile -Raw
    $content = [regex]::Replace($content, "defaultBaseUrl\s*=\s*'[^']+'", "defaultBaseUrl = '$funnelUrl'")
    $content = [regex]::Replace($content, "activeOrigin\s*=\s*'[^']+'", "activeOrigin = '$funnelUrl'")
    Set-Content -Path $appConfigFile -Value $content -Encoding UTF8
    Write-Host "[SYNC] File mobile app_config.dart otomatis disesuaikan ke Tailscale Funnel!" -ForegroundColor Green
}

# 4. Buka otomatis di Browser Default (Chrome)
Write-Host "[3/3] Membuka website di Google Chrome..." -ForegroundColor Yellow
Start-Process $funnelUrl

Write-Host ""
Write-Host "==========================================================" -ForegroundColor Green
Write-Host "  WEBSITE BERHASIL ONLINE DI INTERNET!" -ForegroundColor Green
Write-Host "  URL: $funnelUrl" -ForegroundColor Cyan
Write-Host "==========================================================" -ForegroundColor Green
Write-Host ""
