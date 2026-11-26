# =====================================================================

# Skrip ini ada di scripts/; akar repo satu tingkat di atasnya.
$repoRoot = Split-Path -Parent $PSScriptRoot
# connect-hp.ps1 - Otomatis Deteksi IP Tailscale HP & Hubungkan ADB
# =====================================================================
param(
    [string]$deviceIp = "",
    [int]$port = 5555,
    [switch]$run
)

$ErrorActionPreference = "Continue"

Write-Host "=====================================================" -ForegroundColor Cyan
Write-Host "  SISTEM PENGHUBUNG HP KE PC (AUTO-DETECT TAILSCALE)  " -ForegroundColor Cyan
Write-Host "=====================================================" -ForegroundColor Cyan

# 1. Deteksi IP Tailscale dari PC dan HP secara Otomatis
$pcIp = ""
$hpIp = ""
$hpName = ""
$dnsName = ""

try {
    $tsPath = (Get-Command tailscale -ErrorAction SilentlyContinue).Source
    if (-not $tsPath) {
        $defaultTsPath = "C:\Program Files\Tailscale\tailscale.exe"
        if (Test-Path $defaultTsPath) {
            $tsPath = $defaultTsPath
        }
    }

    if ($tsPath) {
        $tsRaw = & $tsPath status --json 2>$null | Out-String
        if ($tsRaw) {
            $tsJson = $tsRaw | ConvertFrom-Json
            
            # IP Tailscale PC (Server Laravel)
            if ($tsJson.Self -and $tsJson.Self.TailscaleIPs -and $tsJson.Self.TailscaleIPs.Count -gt 0) {
                $pcIp = $tsJson.Self.TailscaleIPs[0]
                $pcName = $tsJson.Self.HostName
                Write-Host "[TAILSCALE] PC Server    : $pcName ($pcIp)" -ForegroundColor Green
            }

            # IP Tailscale HP (Android / A04s)
            if ($tsJson.Peer) {
                $androidPeer = $tsJson.Peer.psobject.properties.Value | 
                    Where-Object { $_.OS -eq "android" -or $_.HostName -like "*a04s*" -or $_.HostName -like "*hp*" -or $_.HostName -like "*phone*" } | 
                    Select-Object -First 1

                if ($androidPeer -and $androidPeer.TailscaleIPs -and $androidPeer.TailscaleIPs.Count -gt 0) {
                    $hpIp = $androidPeer.TailscaleIPs[0]
                    $hpName = $androidPeer.HostName
                    $dnsName = $androidPeer.DNSName
                    $statusOnline = if ($androidPeer.Online) { "ONLINE" } else { "OFFLINE/IDLE" }
                    Write-Host "[TAILSCALE] HP Terdeteksi: $hpName ($hpIp) [$statusOnline]" -ForegroundColor Green
                }
            }
        }
    }
} catch {
    Write-Host "[INFO] Tidak dapat mengambil data dari Tailscale CLI: $($_.Exception.Message)" -ForegroundColor Yellow
}

# 2. Sinkronkan URL Tailscale Funnel Permanen ke file app_config.dart
$funnelDomain = "https://dul-1378.tail09bd7a.ts.net"
$appConfigFile = Join-Path $PSScriptRoot "mobile\lib\core\config\app_config.dart"
if (Test-Path $appConfigFile) {
    $configContent = Get-Content $appConfigFile -Raw
    if (-not ($configContent -match [regex]::Escape($funnelDomain))) {
        Write-Host "[SYNC] Memperbarui URL Server di app_config.dart ke: $funnelDomain" -ForegroundColor Cyan
        $updatedConfig = [regex]::Replace($configContent, "defaultBaseUrl\s*=\s*'[^']+'", "defaultBaseUrl = '$funnelDomain'")
        $updatedConfig = [regex]::Replace($updatedConfig, "activeOrigin\s*=\s*'[^']+'", "activeOrigin = '$funnelDomain'")
        Set-Content -Path $appConfigFile -Value $updatedConfig -Encoding UTF8
    }
}

# 3. Tentukan Target IP HP yang akan disambungkan
$targetDevice = ""

if ($deviceIp) {
    $targetDevice = if ($deviceIp -match ":") { $deviceIp } else { "${deviceIp}:${port}" }
    Write-Host "[TARGET] Menggunakan IP Manual: $targetDevice" -ForegroundColor Cyan
} elseif ($hpIp) {
    $targetDevice = "${hpIp}:${port}"
    Write-Host "[TARGET] Menggunakan IP Tailscale HP Otomatis: $targetDevice" -ForegroundColor Cyan
} else {
    $targetDevice = "100.82.242.85:${port}"
    Write-Host "[TARGET] Menggunakan IP Fallback: $targetDevice" -ForegroundColor Yellow
}

# 4. Memulai ADB dan Menyambungkan ke HP
Write-Host ""
Write-Host "Menyambungkan ADB ke $targetDevice..." -ForegroundColor Cyan
adb start-server | Out-Null

adb connect $targetDevice

Start-Sleep -Seconds 1
$devicesOutput = adb devices

$targetHost = ($targetDevice -split ":")[0]
$escapedTargetHost = [regex]::Escape($targetHost)

if ($devicesOutput -match "$escapedTargetHost.*device\b") {
    Write-Host "-----------------------------------------------------" -ForegroundColor Green
    Write-Host "[OK] BERHASIL TERHUBUNG KE HP ($targetDevice)!" -ForegroundColor Green
    
    # Pasang port reverse agar localhost:8000 di HP langsung mengarah ke Laravel PC
    adb -s $targetDevice reverse tcp:8000 tcp:8000 | Out-Null
    Write-Host "[OK] Port reverse 8000 aktif (localhost:8000 HP -> PC Laravel)" -ForegroundColor Green
    Write-Host "-----------------------------------------------------" -ForegroundColor Green
    Write-Host ""
    Write-Host "Daftar Perangkat ADB Terhubung:" -ForegroundColor Yellow
    adb devices -l
    Write-Host ""

    if ($run) {
        Write-Host "Menjalankan aplikasi Flutter pada perangkat: $targetDevice..." -ForegroundColor Cyan
        Set-Location (Join-Path $repoRoot "mobile")
        flutter run -d $targetDevice
    } else {
        Write-Host "Aplikasi Flutter kini siap dijalankan dengan: flutter run" -ForegroundColor Cyan
        Write-Host "Atau jalankan: .\adhp.bat -run  (untuk langsung connect dan jalankan aplikasi)" -ForegroundColor Cyan
    }
} else {
    Write-Host "-----------------------------------------------------" -ForegroundColor Red
    Write-Host "[PERINGATAN] Belum dapat terhubung ke HP $targetDevice." -ForegroundColor Red
    Write-Host "-----------------------------------------------------" -ForegroundColor Red
    Write-Host "Penyebab Umum & Solusi Cepat:" -ForegroundColor Yellow
    Write-Host "1. HP baru saja restart: Buka aplikasi Tailscale di HP dan pastikan statusnya CONNECTED."
    Write-Host "2. Buka 'Opsi Pengembang' (Developer Options) di HP -> Aktifkan 'Debugging Nirkabel' (Wireless Debugging)."
    Write-Host "3. Jika port berubah setelah restart, Anda bisa jalankan:"
    Write-Host "   .\adhp.bat -port [PORT_BARU]" -ForegroundColor Cyan
    Write-Host "4. Atau sambungkan HP sebentar pakai kabel USB lalu jalankan:"
    Write-Host "   adb tcpip 5555" -ForegroundColor Cyan
    Write-Host "   setelah itu cabut kabel dan jalankan kembali: .\adhp.bat"
    Write-Host ""
    Write-Host "Daftar Perangkat Saat Ini:" -ForegroundColor Yellow
    adb devices -l
}
