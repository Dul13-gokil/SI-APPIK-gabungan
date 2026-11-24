# Menyiapkan Flutter & Android SDK di Mesin Ini

Catatan hasil pemasangan dan pengujian aplikasi mobile SI-APPIK.

## Yang dipasang

| Komponen | Lokasi | Catatan |
| :--- | :--- | :--- |
| Flutter SDK 3.50.1 (stable) | `D:\flutter` | Diunduh dari arsip resmi `storage.googleapis.com` |
| JDK 17 (Temurin 17.0.21.1) | `D:\jdk-17\jdk` | Dibutuhkan Gradle/AGP |
| Android SDK | `C:\Users\lenov\AppData\Local\Android\Sdk` | Platform 34/35/36, build-tools 35 & 36, emulator, platform-tools |
| command-line tools | `...\Sdk\cmdline-tools\latest` | Untuk `sdkmanager`, `avdmanager` |
| Emulator AVD | `si_appik` | system-image `android-35;google_apis_playstore;x86_64`, akselerasi WHPX |

## Variabel yang perlu diekspor per sesi

```bash
export JAVA_HOME="D:/jdk-17/jdk"
export ANDROID_SDK_ROOT="C:/Users/lenov/AppData/Local/Android/Sdk"
export ANDROID_HOME="$ANDROID_SDK_ROOT"
export PATH="/d/jdk-17/jdk/bin:/d/flutter/bin:$PATH"
```

Konfigurasi Flutter cukup sekali:

```bash
flutter config --android-sdk "C:/Users/lenov/AppData/Local/Android/Sdk" --jdk-dir "D:/jdk-17/jdk"
flutter doctor --android-licenses     # jawab y
```

## Kebutuhan Windows

Plugin Flutter memakai symlink. `flutter pub get` akan gagal dengan pesan
"Building with plugins requires symlink support" bila Developer Mode belum aktif.
Aktifkan sekali (butuh hak admin):

```powershell
reg add "HKLM\SOFTWARE\Microsoft\Windows\CurrentVersion\AppModelUnlock" /t REG_DWORD /f /v AllowDevelopmentWithoutDevLicense /d 1
```

## Perintah harian

```bash
cd mobile
flutter pub get
flutter analyze                      # saat ini 23 catatan level info (deprecation), 0 error
flutter build apk --debug            # hasil: build/app/outputs/flutter-apk/app-debug.apk
```

Versi AGP di `android/settings.gradle.kts` dinaikkan ke 9.1.1 karena Flutter 3.50
menolak AGP 9.1.0.

## Menjalankan emulator

```bash
export ANDROID_SDK_ROOT="C:/Users/lenov/AppData/Local/Android/Sdk"
"$ANDROID_SDK_ROOT/emulator/emulator.exe" -avd si_appik -no-window -no-audio -no-snapshot -gpu swiftshader_indirect -memory 3072
"$ANDROID_SDK_ROOT/platform-tools/adb.exe" wait-for-device
"$ANDROID_SDK_ROOT/platform-tools/adb.exe" install -r mobile/build/app/outputs/flutter-apk/app-debug.apk
```

## Mengarahkan aplikasi ke backend lokal

Server bawaan aplikasi (`https://si-appik-production.up.railway.app`) sudah mati (404).
Untuk pengujian, alamat server disimpan di SharedPreferences. Emulator mencapai host
lewat `10.0.2.2`:

```bash
adb shell am force-stop com.kabupaten.aduan.citizen_complaint_app
adb shell "run-as com.kabupaten.aduan.citizen_complaint_app sh -c 'mkdir -p shared_prefs && echo \"<?xml version=\\\"1.0\\\" encoding=\\\"utf-8\\\" standalone=\\\"yes\\\" ?>\n<map>\n    <string name=\\\"flutter.custom_base_url\\\">http://10.0.2.2:8080/api/v1</string>\n</map>\" > shared_prefs/FlutterSharedPreferences.xml'"
```

Cara normal di aplikasi: login, lalu Profil -> Alamat Server API.

Akun demo aktif (sandi `password`): `dularafat@gmail.com` (warga),
`budi.petugas@kabupaten.go.id` (petugas). `budi@gmail.com` dinonaktifkan.

## Keterbatasan emulator di mesin ini

- `adb emu geo fix` tidak mengubah posisi (tetap 37.42, -122.08), sehingga validasi
  "di luar wilayah" pada peta tidak bisa dilewati tanpa mengubah kode atau memakai
  perangkat fisik.
- Karena itu pengujian pembuatan laporan dengan GPS di dalam Kolaka Utara belum bisa
  dilakukan di emulator ini.
