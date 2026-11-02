<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SecurityMailService
{
    /**
     * Kirim email pemberitahuan kata sandi telah diubah.
     */
    public function sendPasswordChangedAlert(
        User $user, 
        ?string $ip = null, 
        ?string $userAgent = null,
        ?float $latitude = null,
        ?float $longitude = null,
        ?string $clientLocation = null
    ): bool
    {
        $normalizedEmail = strtolower(trim($user->email));
        if (!filter_var($normalizedEmail, FILTER_VALIDATE_EMAIL)) {
            return false;
        }

        $timeString     = now('Asia/Makassar')->translatedFormat('d F Y, H:i') . ' WITA';
        $deviceLocation = $this->resolveLocation($user, $ip, $latitude, $longitude, $clientLocation);
        $deviceInfo     = $this->parseUserAgent($userAgent);
        $subject        = "Kata Sandi Akun SI-APPIK Anda Berhasil Diperbarui";

        $htmlBody = <<<HTML
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Pemberitahuan Kata Sandi Telah Diubah</title>
</head>
<body style="margin: 0; padding: 0; background-color: #f8fafc; font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; color: #1e293b;">
  <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background-color: #f8fafc; padding: 30px 15px;">
    <tr>
      <td align="center">
        <table role="presentation" width="100%" style="max-width: 520px; background-color: #ffffff; border-radius: 16px; overflow: hidden; box-shadow: 0 4px 20px rgba(0,0,0,0.06); border: 1px solid #e2e8f0;">
          
          <!-- Header -->
          <tr>
            <td style="background: linear-gradient(135deg, #1e3a8a 0%, #1e40af 100%); padding: 26px 24px; text-align: center;">
              <h1 style="margin: 0; color: #ffffff; font-size: 18px; font-weight: 700; letter-spacing: 0.5px;">
                PEMERINTAH KABUPATEN
              </h1>
              <p style="margin: 6px 0 0 0; color: #93c5fd; font-size: 13px; font-weight: 500;">
                SI-APPIK - Sistem Informasi Pengaduan & Penanganan Infrastruktur
              </p>
            </td>
          </tr>

          <!-- Body -->
          <tr>
            <td style="padding: 30px 26px;">
              <h2 style="margin: 0 0 10px 0; color: #0f172a; font-size: 17px; font-weight: 700;">
                Kata Sandi Anda Berhasil Diperbarui
              </h2>
              <p style="margin: 0 0 18px 0; color: #475569; font-size: 13.5px; line-height: 1.6;">
                Halo <strong>{$user->name}</strong>,<br>
                Kami memberitahukan bahwa kata sandi untuk akun SI-APPIK Anda baru saja berhasil diubah dengan rincian sebagai berikut:
              </p>

              <!-- Activity Card -->
              <table role="presentation" width="100%" style="background-color: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px; padding: 14px 16px; margin: 16px 0;">
                <tr>
                  <td style="padding: 6px 0; font-size: 12.5px; color: #64748b; width: 35%;">Waktu Perubahan</td>
                  <td style="padding: 6px 0; font-size: 13px; color: #0f172a; font-weight: 600;">: {$timeString}</td>
                </tr>
                <tr>
                  <td style="padding: 6px 0; font-size: 12.5px; color: #64748b;">Lokasi Perangkat</td>
                  <td style="padding: 6px 0; font-size: 13px; color: #0f172a; font-weight: 600;">: {$deviceLocation}</td>
                </tr>
                <tr>
                  <td style="padding: 6px 0; font-size: 12.5px; color: #64748b;">Perangkat / Klien</td>
                  <td style="padding: 6px 0; font-size: 13px; color: #0f172a; font-weight: 600;">: {$deviceInfo}</td>
                </tr>
              </table>

              <!-- Warning Box -->
              <div style="background-color: #fff1f2; border-left: 4px solid #ef4444; padding: 14px 16px; border-radius: 8px; margin: 20px 0;">
                <p style="margin: 0; font-size: 12.5px; color: #991b1b; line-height: 1.5;">
                  <strong>PENTING:</strong> Jika Anda merasa <strong>BUKAN</strong> Anda yang melakukan perubahan ini, akun Anda mungkin dalam bahaya. Segera lakukan reset kata sandi melalui fitur 'Lupa Kata Sandi' di aplikasi dan hubungi Administrator IT Dinas PUPR Kolaka Utara.
                </p>
              </div>

              <p style="margin: 0; color: #94a3b8; font-size: 12px; line-height: 1.5;">
                Jika Anda memang yang melakukan perubahan ini, Anda dapat mengabaikan email ini dengan aman.
              </p>
            </td>
          </tr>

          <!-- Footer -->
          <tr>
            <td style="background-color: #f8fafc; border-top: 1px solid #e2e8f0; padding: 18px 24px; text-align: center;">
              <p style="margin: 0; color: #64748b; font-size: 11.5px;">
                Dinas Pekerjaan Umum & Penataan Ruang (PUPR) - Kab. Kolaka Utara
              </p>
              <p style="margin: 4px 0 0 0; color: #94a3b8; font-size: 11px;">
                Email otomatis dari sistem &bull; Mohon tidak membalas email ini
              </p>
            </td>
          </tr>
        </table>
      </td>
    </tr>
  </table>
</body>
</html>
HTML;

        $plainText = "PEMBERITAHUAN PERUBAHAN KATA SANDI - SI-APPIK\n\n"
            . "Halo {$user->name},\n"
            . "Kata sandi untuk akun SI-APPIK Anda telah berhasil diubah.\n\n"
            . "Rincian Aktivitas:\n"
            . "- Waktu: {$timeString}\n"
            . "- Lokasi: {$deviceLocation}\n"
            . "- Perangkat: {$deviceInfo}\n\n"
            . "Jika BUKAN Anda yang melakukan perubahan ini, segera amankan akun Anda dengan melakukan reset kata sandi melalui aplikasi.\n\n"
            . "Pemerintah Kabupaten Kolaka Utara - Dinas PUPR";

        return $this->sendPriorityMail($normalizedEmail, $subject, $htmlBody, $plainText);
    }

    /**
     * Kirim email peringatan login dari perangkat atau IP baru.
     */
    public function sendNewDeviceLoginAlert(User $user, string $ip, ?string $userAgent = null): bool
    {
        // Fitur dinonaktifkan sesuai permintaan pengguna
        return false;
        if (!filter_var($normalizedEmail, FILTER_VALIDATE_EMAIL)) {
            return false;
        }

        $timeString   = now('Asia/Makassar')->translatedFormat('d F Y, H:i') . ' WITA';
        $deviceInfo   = $this->parseUserAgent($userAgent);
        $subject      = "[PERINGATAN] Terdeteksi Login Perangkat Baru pada Akun SI-APPIK Anda";

        $htmlBody = <<<HTML
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Peringatan Login Perangkat Baru</title>
</head>
<body style="margin: 0; padding: 0; background-color: #f8fafc; font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; color: #1e293b;">
  <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background-color: #f8fafc; padding: 30px 15px;">
    <tr>
      <td align="center">
        <table role="presentation" width="100%" style="max-width: 520px; background-color: #ffffff; border-radius: 16px; overflow: hidden; box-shadow: 0 4px 20px rgba(0,0,0,0.06); border: 1px solid #e2e8f0;">
          
          <!-- Header -->
          <tr>
            <td style="background: linear-gradient(135deg, #b91c1c 0%, #dc2626 100%); padding: 26px 24px; text-align: center;">
              <h1 style="margin: 0; color: #ffffff; font-size: 18px; font-weight: 700; letter-spacing: 0.5px;">
                PEMERINTAH KABUPATEN
              </h1>
              <p style="margin: 6px 0 0 0; color: #fecaca; font-size: 13px; font-weight: 500;">
                SI-APPIK - Pusat Pengaduan & Penanganan Infrastruktur
              </p>
            </td>
          </tr>

          <!-- Body -->
          <tr>
            <td style="padding: 30px 26px;">
              <div style="display: inline-block; background-color: #fef2f2; color: #dc2626; font-size: 12px; font-weight: 700; padding: 4px 12px; border-radius: 20px; margin-bottom: 12px;">
                ⚠️ DETEKSI PERANGKAT BARU
              </div>
              <h2 style="margin: 0 0 10px 0; color: #0f172a; font-size: 17px; font-weight: 700;">
                Login Baru Terdeteksi
              </h2>
              <p style="margin: 0 0 18px 0; color: #475569; font-size: 13.5px; line-height: 1.6;">
                Halo <strong>{$user->name}</strong>,<br>
                Sistem kami mendeteksi sesi masuk (login) baru pada akun Anda dari perangkat atau alamat IP yang berbeda:
              </p>

              <!-- Activity Card -->
              <table role="presentation" width="100%" style="background-color: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px; padding: 14px 16px; margin: 16px 0;">
                <tr>
                  <td style="padding: 6px 0; font-size: 12.5px; color: #64748b; width: 35%;">Waktu Masuk</td>
                  <td style="padding: 6px 0; font-size: 13px; color: #0f172a; font-weight: 600;">: {$timeString}</td>
                </tr>
                <tr>
                  <td style="padding: 6px 0; font-size: 12.5px; color: #64748b;">Alamat IP</td>
                  <td style="padding: 6px 0; font-size: 13px; color: #0f172a; font-weight: 600;">: {$ip}</td>
                </tr>
                <tr>
                  <td style="padding: 6px 0; font-size: 12.5px; color: #64748b;">Perangkat / Klien</td>
                  <td style="padding: 6px 0; font-size: 13px; color: #0f172a; font-weight: 600;">: {$deviceInfo}</td>
                </tr>
              </table>

              <!-- Advisory Box -->
              <div style="background-color: #fffbeb; border-left: 4px solid #f59e0b; padding: 14px 16px; border-radius: 8px; margin: 20px 0;">
                <p style="margin: 0; font-size: 12.5px; color: #92400e; line-height: 1.5;">
                  <strong>Apakah ini Anda?</strong><br>
                  Jika Anda baru saja masuk dari ponsel baru, jaringan WiFi lain, atau peramban berbeda, abaikan email ini. Jika Anda <strong>tidak merasa masuk</strong>, segera ganti kata sandi Anda dan cabut sesi aktif di menu profil aplikasi.
                </p>
              </div>

              <p style="margin: 0; color: #94a3b8; font-size: 12px; line-height: 1.5;">
                Pemberitahuan ini dikirim otomatis demi melindungi keamanan privasi dan akun warga Anda.
              </p>
            </td>
          </tr>

          <!-- Footer -->
          <tr>
            <td style="background-color: #f8fafc; border-top: 1px solid #e2e8f0; padding: 18px 24px; text-align: center;">
              <p style="margin: 0; color: #64748b; font-size: 11.5px;">
                Dinas Pekerjaan Umum & Penataan Ruang (PUPR) - Kab. Kolaka Utara
              </p>
              <p style="margin: 4px 0 0 0; color: #94a3b8; font-size: 11px;">
                Email otomatis dari sistem keamanan &bull; Mohon tidak membalas email ini
              </p>
            </td>
          </tr>
        </table>
      </td>
    </tr>
  </table>
</body>
</html>
HTML;

        $plainText = "PERINGATAN LOGIN PERANGKAT BARU - SI-APPIK\n\n"
            . "Halo {$user->name},\n"
            . "Terdeteksi login baru pada akun Anda dari perangkat atau IP berbeda:\n\n"
            . "- Waktu: {$timeString}\n"
            . "- IP Address: {$ip}\n"
            . "- Perangkat: {$deviceInfo}\n\n"
            . "Jika bukan Anda, segera ubah kata sandi akun Anda melalui aplikasi.\n\n"
            . "Pemerintah Kabupaten Kolaka Utara - Dinas PUPR";

        return $this->sendPriorityMail($normalizedEmail, $subject, $htmlBody, $plainText);
    }

    /**
     * Kirim email aktivasi / undangan akun baru untuk Petugas Lapangan atau Operator.
     */
    public function sendStaffInvitationEmail(User $user, string $role, string $defaultPassword = 'password'): bool
    {
        $normalizedEmail = strtolower(trim($user->email));
        if (!filter_var($normalizedEmail, FILTER_VALIDATE_EMAIL)) {
            return false;
        }

        $loginUrl = url('/login');
        $isOperator = in_array(strtoupper($role), ['OPERATOR', 'DINAS']);
        $roleTitle = $isOperator ? 'Operator Verifikasi Sistem' : 'Petugas Lapangan (Tim Teknis PUPR)';
        $portalDescription = $isOperator 
            ? 'Portal Web Resmi SI-APPIK Kolaka Utara' 
            : 'Aplikasi Mobile SI-APPIK & Web Portal Petugas';

        $subject = "[AKUN BARU] Selamat Datang di SI-APPIK Kolaka Utara - Akun {$roleTitle}";

        $accessNote = $isOperator
            ? "<p style=\"margin: 0 0 16px 0; color: #475569; font-size: 13.5px; line-height: 1.6;\">
                Anda telah resmi didaftarkan sebagai <strong>{$roleTitle}</strong> Dinas Pekerjaan Umum dan Penataan Ruang (PUPR) Kabupaten Kolaka Utara pada sistem SI-APPIK. Akun ini digunakan untuk memverifikasi laporan masyarakat, mendisposisi penugasan ke regu lapangan, dan memvalidasi pekerjaan fisik.
               </p>"
            : "<p style=\"margin: 0 0 16px 0; color: #475569; font-size: 13.5px; line-height: 1.6;\">
                Anda telah resmi didaftarkan sebagai <strong>{$roleTitle}</strong> Dinas Pekerjaan Umum dan Penataan Ruang (PUPR) Kabupaten Kolaka Utara pada sistem SI-APPIK. Akun ini dapat Anda gunakan pada <strong>Aplikasi Mobile SI-APPIK</strong> maupun <strong>Web Portal Petugas</strong> untuk menerima surat tugas perbaikan jalan/jembatan dan mengunggah dokumentasi progres pengerjaan lapangan.
               </p>";

        $htmlBody = <<<HTML
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Aktivasi Akun {$roleTitle}</title>
</head>
<body style="margin: 0; padding: 0; background-color: #f8fafc; font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; color: #1e293b;">
  <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background-color: #f8fafc; padding: 30px 15px;">
    <tr>
      <td align="center">
        <table role="presentation" width="100%" style="max-width: 520px; background-color: #ffffff; border-radius: 16px; overflow: hidden; box-shadow: 0 4px 20px rgba(0,0,0,0.06); border: 1px solid #e2e8f0;">
          
          <!-- Header -->
          <tr>
            <td style="background: linear-gradient(135deg, #1e3a8a 0%, #1e40af 100%); padding: 26px 24px; text-align: center;">
              <h1 style="margin: 0; color: #ffffff; font-size: 18px; font-weight: 700; letter-spacing: 0.5px;">
                PEMERINTAH KABUPATEN
              </h1>
              <p style="margin: 6px 0 0 0; color: #93c5fd; font-size: 13px; font-weight: 500;">
                SI-APPIK - Sistem Informasi Pengaduan & Penanganan Infrastruktur
              </p>
            </td>
          </tr>

          <!-- Body -->
          <tr>
            <td style="padding: 30px 26px;">
              <h2 style="margin: 0 0 10px 0; color: #0f172a; font-size: 17px; font-weight: 700;">
                Akun {$roleTitle} Anda Telah Aktif
              </h2>
              <p style="margin: 0 0 14px 0; color: #475569; font-size: 13.5px; line-height: 1.6;">
                Halo <strong>{$user->name}</strong>,
              </p>
              {$accessNote}

              <!-- Credentials Card -->
              <table role="presentation" width="100%" style="background-color: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px; padding: 16px 18px; margin: 18px 0;">
                <tr>
                  <td colspan="2" style="padding-bottom: 10px; font-size: 13px; font-weight: 700; color: #1e3a8a; border-bottom: 1px solid #e2e8f0;">
                    Informasi Masuk Sistem:
                  </td>
                </tr>
                <tr>
                  <td style="padding: 8px 0 4px 0; font-size: 12.5px; color: #64748b; width: 38%;">Alamat Email</td>
                  <td style="padding: 8px 0 4px 0; font-size: 13px; color: #0f172a; font-weight: 600;">: {$user->email}</td>
                </tr>
                <tr>
                  <td style="padding: 4px 0; font-size: 12.5px; color: #64748b;">Kata Sandi Default</td>
                  <td style="padding: 4px 0; font-size: 13px; color: #0f172a; font-weight: 700; font-family: monospace;">: {$defaultPassword}</td>
                </tr>
                <tr>
                  <td style="padding: 4px 0; font-size: 12.5px; color: #64748b;">Peran Sistem</td>
                  <td style="padding: 4px 0; font-size: 13px; color: #1e40af; font-weight: 600;">: {$roleTitle}</td>
                </tr>
                <tr>
                  <td style="padding: 4px 0 6px 0; font-size: 12.5px; color: #64748b;">Akses Masuk</td>
                  <td style="padding: 4px 0 6px 0; font-size: 12.5px; color: #0f172a; font-weight: 500;">: {$portalDescription}</td>
                </tr>
              </table>

              <!-- Notice Box -->
              <div style="background-color: #eff6ff; border-left: 4px solid #3b82f6; padding: 14px 16px; border-radius: 8px; margin: 20px 0;">
                <p style="margin: 0; font-size: 12.5px; color: #1e40af; line-height: 1.6;">
                  <strong>WAJIB DILAKUKAN SAAT LOGIN PERTAMA KALI:</strong><br>
                  Demi keamanan dan akurasi data dinas, saat pertama kali masuk Anda akan diminta untuk <strong>memeriksa nama lengkap</strong>, <strong>memasukkan nomor handphone/WhatsApp aktif</strong>, dan <strong>mengubah kata sandi default</strong> menjadi kata sandi pribadi Anda.
                </p>
              </div>

              <!-- Action Button -->
              <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="margin: 24px 0 10px 0;">
                <tr>
                  <td align="center">
                    <a href="{$loginUrl}" style="display: inline-block; background-color: #1e3a8a; color: #ffffff; font-size: 13px; font-weight: 700; text-decoration: none; padding: 12px 28px; border-radius: 10px; box-shadow: 0 2px 8px rgba(30,58,138,0.25);">
                      Masuk ke Sistem SI-APPIK
                    </a>
                  </td>
                </tr>
              </table>
              <p style="margin: 10px 0 0 0; text-align: center; color: #94a3b8; font-size: 11px;">
                Atau salin tautan berikut ke browser: <a href="{$loginUrl}" style="color: #3b82f6; text-decoration: none;">{$loginUrl}</a>
              </p>

            </td>
          </tr>

          <!-- Footer -->
          <tr>
            <td style="background-color: #f8fafc; border-top: 1px solid #e2e8f0; padding: 18px 24px; text-align: center;">
              <p style="margin: 0; color: #64748b; font-size: 11.5px;">
                Dinas Pekerjaan Umum & Penataan Ruang (PUPR) - Kab. Kolaka Utara
              </p>
              <p style="margin: 4px 0 0 0; color: #94a3b8; font-size: 11px;">
                Email otomatis dari sistem kepegawaian &bull; Mohon tidak membalas email ini
              </p>
            </td>
          </tr>
        </table>
      </td>
    </tr>
  </table>
</body>
</html>
HTML;

        $plainText = "AKTIVASI AKUN {$roleTitle} - SI-APPIK KOLAKA UTARA\n\n"
            . "Halo {$user->name},\n\n"
            . "Anda telah didaftarkan sebagai {$roleTitle} pada SI-APPIK Dinas PUPR Kabupaten Kolaka Utara.\n\n"
            . "Informasi Akun:\n"
            . "- Email: {$user->email}\n"
            . "- Kata Sandi Default: {$defaultPassword}\n"
            . "- Tautan Masuk: {$loginUrl}\n\n"
            . "PENTING: Saat pertama kali masuk, Anda diwajibkan memeriksa nama, melengkapi nomor handphone/WA, dan mengganti kata sandi default menjadi kata sandi pribadi Anda.\n\n"
            . "Pemerintah Kabupaten Kolaka Utara - Dinas PUPR";

        return $this->sendPriorityMail($normalizedEmail, $subject, $htmlBody, $plainText);
    }


    /**
     * Kirim email pemberitahuan resmi ketika data profil (Nama, Email, Nomor Telepon, atau Sandi)
     * diubah oleh Super Administrator.
     */
    public function sendProfileUpdatedByAdminAlert(User $user, array $changes, ?string $oldEmail = null): bool
    {
        $newEmail = strtolower(trim($user->email));
        $timeString = now('Asia/Makassar')->translatedFormat('d F Y, H:i') . ' WITA';
        $subject = "[KEAMANAN] Data Akun SI-APPIK Anda Telah Diperbarui oleh Administrator";

        $changeRowsHtml = '';
        $changeRowsPlain = '';
        foreach ($changes as $field => $desc) {
            $changeRowsHtml .= "<tr>
                <td style=\"padding: 8px 12px; font-size: 12.5px; color: #64748b; font-weight: 600; border-bottom: 1px solid #f1f5f9; width: 38%;\">{$field}</td>
                <td style=\"padding: 8px 12px; font-size: 13px; color: #0f172a; font-weight: 500; border-bottom: 1px solid #f1f5f9;\">{$desc}</td>
            </tr>";
            $changeRowsPlain .= "- {$field}: {$desc}\n";
        }

        $htmlBody = <<<HTML
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Pemberitahuan Pembaruan Akun oleh Administrator</title>
</head>
<body style="margin: 0; padding: 0; background-color: #f8fafc; font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; color: #1e293b;">
  <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background-color: #f8fafc; padding: 30px 15px;">
    <tr>
      <td align="center">
        <table role="presentation" width="100%" style="max-width: 520px; background-color: #ffffff; border-radius: 16px; overflow: hidden; box-shadow: 0 4px 20px rgba(0,0,0,0.06); border: 1px solid #e2e8f0;">
          
          <!-- Header Resmi -->
          <tr>
            <td style="background: linear-gradient(135deg, #1e3a8a 0%, #1e40af 100%); padding: 26px 24px; text-align: center;">
              <h1 style="margin: 0; color: #ffffff; font-size: 18px; font-weight: 700; letter-spacing: 0.5px;">
                PEMERINTAH KABUPATEN
              </h1>
              <p style="margin: 6px 0 0 0; color: #93c5fd; font-size: 13px; font-weight: 500;">
                SI-APPIK - Sistem Informasi Pengaduan & Penanganan Infrastruktur
              </p>
            </td>
          </tr>

          <!-- Body -->
          <tr>
            <td style="padding: 30px 26px;">
              <h2 style="margin: 0 0 10px 0; color: #0f172a; font-size: 17px; font-weight: 700;">
                Data Akun Anda Telah Diperbarui
              </h2>
              <p style="margin: 0 0 16px 0; color: #475569; font-size: 13.5px; line-height: 1.6;">
                Halo <strong>{$user->name}</strong>,<br>
                Kami menginformasikan bahwa data profil akun Anda pada sistem SI-APPIK Kabupaten Kolaka Utara baru saja diperbarui oleh <strong>Super Administrator</strong> dengan rincian sebagai berikut:
              </p>

              <!-- Table Rincian Perubahan -->
              <table role="presentation" width="100%" style="background-color: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px; border-collapse: collapse; margin: 16px 0; overflow: hidden;">
                {$changeRowsHtml}
                <tr>
                  <td style="padding: 8px 12px; font-size: 12.5px; color: #64748b; font-weight: 600;">Waktu Pembaruan</td>
                  <td style="padding: 8px 12px; font-size: 13px; color: #0f172a; font-weight: 500;">{$timeString}</td>
                </tr>
              </table>

              <!-- Notice Box -->
              <div style="background-color: #fffbeb; border-left: 4px solid #f59e0b; padding: 14px 16px; border-radius: 8px; margin: 20px 0;">
                <p style="margin: 0; font-size: 12.5px; color: #92400e; line-height: 1.5;">
                  <strong>Catatan Keamanan:</strong><br>
                  Jika Anda tidak pernah meminta perubahan data ini atau merasa terjadi kekeliruan identitas, segera hubungi Helpdesk IT Dinas PUPR Kabupaten Kolaka Utara untuk verifikasi akun Anda.
                </p>
              </div>

              <p style="margin: 0; color: #94a3b8; font-size: 12px; line-height: 1.5;">
                Pemberitahuan otomatis ini dikirimkan untuk menjamin transparansi data dan mencegah penyalahgunaan akun kedinasan/masyarakat.
              </p>
            </td>
          </tr>

          <!-- Footer -->
          <tr>
            <td style="background-color: #f8fafc; border-top: 1px solid #e2e8f0; padding: 18px 24px; text-align: center;">
              <p style="margin: 0; color: #64748b; font-size: 11.5px;">
                Dinas Pekerjaan Umum & Penataan Ruang (PUPR) - Kab. Kolaka Utara
              </p>
              <p style="margin: 4px 0 0 0; color: #94a3b8; font-size: 11px;">
                Email otomatis dari sistem keamanan akun &bull; Mohon tidak membalas email ini
              </p>
            </td>
          </tr>
        </table>
      </td>
    </tr>
  </table>
</body>
</html>
HTML;

        $plainText = "PEMBERITAHUAN PEMBARUAN PROFIL AKUN - SI-APPIK\n\n"
            . "Halo {$user->name},\n"
            . "Data akun SI-APPIK Anda telah diperbarui oleh Super Administrator:\n\n"
            . "Rincian Pembaruan:\n"
            . $changeRowsPlain
            . "- Waktu: {$timeString}\n\n"
            . "Jika Anda merasa tidak mengajukan permohonan ini, hubungi Administrator IT Dinas PUPR Kolaka Utara.\n\n"
            . "Pemerintah Kabupaten Kolaka Utara - Dinas PUPR";

        // 1. Kirim ke email baru (atau email saat ini)
        $sentNew = false;
        if (filter_var($newEmail, FILTER_VALIDATE_EMAIL)) {
            $sentNew = $this->sendPriorityMail($newEmail, $subject, $htmlBody, $plainText);
        }

        // 2. Jika email diubah, kirim juga notifikasi ke email lama demi mencegah pengambilalihan akun diam-diam (Anti-Account Takeover)
        if ($oldEmail && strtolower(trim($oldEmail)) !== $newEmail && filter_var($oldEmail, FILTER_VALIDATE_EMAIL)) {
            $oldEmailSubject = "[PERINGATAN KEAMANAN] Alamat Email Akun SI-APPIK Anda Telah Dialihkan";
            $oldPlain = "PERINGATAN KEAMANAN - SI-APPIK KOLAKA UTARA\n\n"
                . "Halo,\n"
                . "Kami memberitahukan bahwa alamat email akun SI-APPIK yang terhubung dengan email ini ({$oldEmail}) telah dialihkan ke ({$newEmail}) oleh Administrator Sistem pada {$timeString}.\n\n"
                . "Jika perubahan ini tanpa persetujuan Anda, segera laporkan ke Tim IT Dinas PUPR Kolaka Utara.\n\n"
                . "Pemerintah Kabupaten Kolaka Utara - Dinas PUPR";
            $this->sendPriorityMail(trim($oldEmail), $oldEmailSubject, $htmlBody, $oldPlain);
        }

        return $sentNew;
    }

    /**
     * Kirim email pemberitahuan ketika pengguna memperbarui profil akun mereka
     * (Nama, Email, Nomor Telepon, atau Kata Sandi) melalui aplikasi mobile atau web.
     */
    public function sendProfileUpdatedAlert(
        User $user, 
        array $changes, 
        ?string $oldEmail = null, 
        ?string $ip = null, 
        ?string $userAgent = null, 
        ?float $latitude = null, 
        ?float $longitude = null, 
        ?string $clientLocation = null
    ): bool {
        $newEmail = strtolower(trim($user->email));
        $timeString = now('Asia/Makassar')->translatedFormat('d F Y, H:i') . ' WITA';
        $deviceLocation = $this->resolveLocation($user, $ip, $latitude, $longitude, $clientLocation);
        $deviceInfo = $this->parseUserAgent($userAgent);
        $subject = "Pemberitahuan Pembaruan Data Akun SI-APPIK";

        $changeRowsHtml = '';
        $changeRowsPlain = '';
        foreach ($changes as $field => $desc) {
            $changeRowsHtml .= "<tr>
                <td style=\"padding: 8px 12px; font-size: 12.5px; color: #64748b; font-weight: 600; border-bottom: 1px solid #f1f5f9; width: 38%;\">{$field}</td>
                <td style=\"padding: 8px 12px; font-size: 13px; color: #0f172a; font-weight: 500; border-bottom: 1px solid #f1f5f9;\">{$desc}</td>
            </tr>";
            $changeRowsPlain .= "- {$field}: {$desc}\n";
        }

        $htmlBody = <<<HTML
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Pemberitahuan Pembaruan Akun</title>
</head>
<body style="margin: 0; padding: 0; background-color: #f8fafc; font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; color: #1e293b;">
  <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background-color: #f8fafc; padding: 30px 15px;">
    <tr>
      <td align="center">
        <table role="presentation" width="100%" style="max-width: 520px; background-color: #ffffff; border-radius: 16px; overflow: hidden; box-shadow: 0 4px 20px rgba(0,0,0,0.06); border: 1px solid #e2e8f0;">
          
          <!-- Header Resmi -->
          <tr>
            <td style="background: linear-gradient(135deg, #1e3a8a 0%, #1e40af 100%); padding: 26px 24px; text-align: center;">
              <h1 style="margin: 0; color: #ffffff; font-size: 18px; font-weight: 700; letter-spacing: 0.5px;">
                PEMERINTAH KABUPATEN
              </h1>
              <p style="margin: 6px 0 0 0; color: #93c5fd; font-size: 13px; font-weight: 500;">
                SI-APPIK - Sistem Informasi Pengaduan & Penanganan Infrastruktur
              </p>
            </td>
          </tr>

          <!-- Body -->
          <tr>
            <td style="padding: 30px 26px;">
              <h2 style="margin: 0 0 10px 0; color: #0f172a; font-size: 17px; font-weight: 700;">
                Data Akun Anda Berhasil Diperbarui
              </h2>
              <p style="margin: 0 0 16px 0; color: #475569; font-size: 13.5px; line-height: 1.6;">
                Halo <strong>{$user->name}</strong>,<br>
                Kami menginformasikan bahwa data profil akun Anda pada sistem SI-APPIK Kabupaten Kolaka Utara baru saja diperbarui melalui aplikasi dengan rincian sebagai berikut:
              </p>

              <!-- Table Rincian Perubahan -->
              <table role="presentation" width="100%" style="background-color: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px; border-collapse: collapse; margin: 16px 0; overflow: hidden;">
                {$changeRowsHtml}
                <tr>
                  <td style="padding: 8px 12px; font-size: 12.5px; color: #64748b; font-weight: 600; border-bottom: 1px solid #f1f5f9;">Lokasi Perangkat</td>
                  <td style="padding: 8px 12px; font-size: 13px; color: #0f172a; font-weight: 500; border-bottom: 1px solid #f1f5f9;">{$deviceLocation}</td>
                </tr>
                <tr>
                  <td style="padding: 8px 12px; font-size: 12.5px; color: #64748b; font-weight: 600; border-bottom: 1px solid #f1f5f9;">Perangkat / Klien</td>
                  <td style="padding: 8px 12px; font-size: 13px; color: #0f172a; font-weight: 500; border-bottom: 1px solid #f1f5f9;">{$deviceInfo}</td>
                </tr>
                <tr>
                  <td style="padding: 8px 12px; font-size: 12.5px; color: #64748b; font-weight: 600;">Waktu Pembaruan</td>
                  <td style="padding: 8px 12px; font-size: 13px; color: #0f172a; font-weight: 500;">{$timeString}</td>
                </tr>
              </table>

              <!-- Notice Box -->
              <div style="background-color: #fff1f2; border-left: 4px solid #ef4444; padding: 14px 16px; border-radius: 8px; margin: 20px 0;">
                <p style="margin: 0; font-size: 12.5px; color: #991b1b; line-height: 1.5;">
                  <strong>PENTING:</strong> Jika Anda merasa <strong>BUKAN</strong> Anda yang melakukan perubahan data ini, segera lakukan pengamanan akun melalui fitur 'Lupa Kata Sandi' di aplikasi mobile atau hubungi Administrator Helpdesk IT Dinas PUPR Kolaka Utara.
                </p>
              </div>

              <p style="margin: 0; color: #94a3b8; font-size: 12px; line-height: 1.5;">
                Pemberitahuan otomatis ini dikirimkan untuk menjamin keamanan akun dan transparansi identitas pengguna.
              </p>
            </td>
          </tr>

          <!-- Footer -->
          <tr>
            <td style="background-color: #f8fafc; border-top: 1px solid #e2e8f0; padding: 18px 24px; text-align: center;">
              <p style="margin: 0; color: #64748b; font-size: 11.5px;">
                Dinas Pekerjaan Umum & Penataan Ruang (PUPR) - Kab. Kolaka Utara
              </p>
              <p style="margin: 4px 0 0 0; color: #94a3b8; font-size: 11px;">
                Email otomatis dari sistem keamanan akun &bull; Mohon tidak membalas email ini
              </p>
            </td>
          </tr>
        </table>
      </td>
    </tr>
  </table>
</body>
</html>
HTML;

        $plainText = "PEMBERITAHUAN PEMBARUAN DATA AKUN - SI-APPIK\n\n"
            . "Halo {$user->name},\n"
            . "Data akun SI-APPIK Anda baru saja berhasil diperbarui:\n\n"
            . "Rincian Pembaruan:\n"
            . $changeRowsPlain
            . "- Lokasi: {$deviceLocation}\n"
            . "- Perangkat: {$deviceInfo}\n"
            . "- Waktu: {$timeString}\n\n"
            . "Jika BUKAN Anda yang melakukan perubahan ini, segera hubungi Helpdesk IT Dinas PUPR Kolaka Utara.\n\n"
            . "Pemerintah Kabupaten Kolaka Utara - Dinas PUPR";

        // 1. Kirim ke email aktif saat ini
        $sentNew = false;
        if (filter_var($newEmail, FILTER_VALIDATE_EMAIL)) {
            $sentNew = $this->sendPriorityMail($newEmail, $subject, $htmlBody, $plainText);
        }

        // 2. Jika email diubah, kirim juga notifikasi ke email lama (Anti-Account Takeover)
        if ($oldEmail && strtolower(trim($oldEmail)) !== $newEmail && filter_var($oldEmail, FILTER_VALIDATE_EMAIL)) {
            $oldEmailSubject = "[PERINGATAN KEAMANAN] Alamat Email Akun SI-APPIK Anda Telah Diubah";
            $oldPlain = "PERINGATAN KEAMANAN - SI-APPIK KOLAKA UTARA\n\n"
                . "Halo,\n"
                . "Alamat email akun SI-APPIK yang sebelumnya terhubung dengan email ini ({$oldEmail}) telah dialihkan ke ({$newEmail}) pada {$timeString} dari {$deviceLocation}.\n\n"
                . "Jika perubahan ini bukan atas persetujuan Anda, segera hubungi Tim IT Dinas PUPR Kolaka Utara.\n\n"
                . "Pemerintah Kabupaten Kolaka Utara - Dinas PUPR";
            $this->sendPriorityMail(trim($oldEmail), $oldEmailSubject, $htmlBody, $oldPlain);
        }

        return $sentNew;
    }

    /**
     * Kirim email dengan prioritas tinggi dan dukungan multipart (HTML + Text).
     */
    protected function sendPriorityMail(string $toEmail, string $subject, string $htmlBody, string $plainText): bool
    {
        try {
            Mail::send([], [], function ($message) use ($toEmail, $subject, $htmlBody, $plainText) {
                $message->to($toEmail)
                    ->subject($subject)
                    ->html($htmlBody)
                    ->text($plainText)
                    ->priority(1);

                $headers = $message->getHeaders();
                $headers->addTextHeader('X-Priority', '1');
                $headers->addTextHeader('Importance', 'High');
                $headers->addTextHeader('X-MSMail-Priority', 'High');
            });

            Log::info("Security Email sent to {$toEmail}: {$subject}");
            return true;
        } catch (\Throwable $e) {
            Log::warning("Gagal mengirim security email ke {$toEmail}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Sederhanakan User-Agent menjadi string nama perangkat yang mudah dipahami.
     */
    protected function parseUserAgent(?string $userAgent): string
    {
        if (empty($userAgent)) {
            return 'Aplikasi Mobile SI-APPIK (Android/iOS)';
        }

        if (str_contains($userAgent, 'Dart') || str_contains($userAgent, 'Flutter')) {
            return 'Aplikasi Mobile SI-APPIK';
        }

        $platform = 'Perangkat Tidak Dikenal';
        if (str_contains($userAgent, 'Android')) {
            $platform = 'Ponsel Android';
        } elseif (str_contains($userAgent, 'iPhone') || str_contains($userAgent, 'iPad')) {
            $platform = 'Ponsel Apple iOS';
        } elseif (str_contains($userAgent, 'Windows')) {
            $platform = 'Komputer Windows';
        } elseif (str_contains($userAgent, 'Macintosh')) {
            $platform = 'Komputer Mac OS';
        } elseif (str_contains($userAgent, 'Linux')) {
            $platform = 'Linux';
        }

        $browser = '';
        if (str_contains($userAgent, 'Chrome') && !str_contains($userAgent, 'Edg')) {
            $browser = 'Chrome';
        } elseif (str_contains($userAgent, 'Safari') && !str_contains($userAgent, 'Chrome')) {
            $browser = 'Safari';
        } elseif (str_contains($userAgent, 'Firefox')) {
            $browser = 'Firefox';
        } elseif (str_contains($userAgent, 'Edg')) {
            $browser = 'Edge';
        }

        return trim("{$platform} {$browser}");
    }

    /**
     * Dapatkan representasi lokasi perangkat pengguna yang dinamis & akurat
     * berdasarkan koordinat GPS perangkat (Laptop/HP) atau IP Geolocation.
     */
    public function resolveLocation(
        User $user, 
        ?string $ip = null, 
        ?float $latitude = null, 
        ?float $longitude = null, 
        ?string $clientLocation = null
    ): string {
        // 1. Jika nama lokasi sudah dikirimkan langsung oleh klien (reverse geocoder browser/hp)
        if (!empty($clientLocation) && strlen(trim($clientLocation)) > 2) {
            return trim($clientLocation);
        }

        // 2. Jika koordinat GPS (latitude & longitude) dikirimkan dari HP atau Laptop
        if (!empty($latitude) && !empty($longitude) && is_numeric($latitude) && is_numeric($longitude)) {
            $lat = round((float)$latitude, 4);
            $lon = round((float)$longitude, 4);

            $geoResult = \Illuminate\Support\Facades\Cache::remember("rev_geo_{$lat}_{$lon}", 86400, function () use ($lat, $lon) {
                try {
                    $response = \Illuminate\Support\Facades\Http::timeout(4)
                        ->withHeaders([
                            'User-Agent' => 'SI-APPIK-KolakaUtara-Aplikasi/1.0 (admin@kolakautarakab.go.id)',
                            'Accept'     => 'application/json',
                        ])
                        ->get("https://nominatim.openstreetmap.org/reverse", [
                            'format'         => 'json',
                            'lat'            => $lat,
                            'lon'            => $lon,
                            'zoom'           => 14,
                            'addressdetails' => 1,
                        ]);

                    if ($response->successful()) {
                        $data = $response->json();
                        $address = $data['address'] ?? [];

                        $city = $address['city'] 
                            ?? $address['town'] 
                            ?? $address['municipality'] 
                            ?? $address['county'] 
                            ?? $address['city_district']
                            ?? $address['suburb']
                            ?? null;

                        $state = $address['state'] ?? $address['region'] ?? null;

                        if ($city && $state) {
                            if (!str_starts_with(strtolower($city), 'kota') && !str_starts_with(strtolower($city), 'kabupaten')) {
                                $prefix = (str_contains(strtolower($city), 'makassar') || str_contains(strtolower($city), 'kendari')) ? 'Kota ' : '';
                                $city = $prefix . $city;
                            }
                            return "{$city}, {$state}";
                        } elseif ($city) {
                            return $city;
                        } elseif ($state) {
                            return $state;
                        }
                    }
                } catch (\Throwable $e) {
                    \Illuminate\Support\Facades\Log::warning("Reverse geocoding OSM failed: " . $e->getMessage());
                }
                return null;
            });

            if (!empty($geoResult)) {
                return $geoResult;
            }
        }

        // 3. Fallback: IP Geolocation (Jika GPS tidak dikirimkan atau izin lokasi dinonaktifkan)
        try {
            $targetIp = $ip;
            // Jika IP localhost / private (127.0.0.1 dsb di lingkungan dev), ambil IP publik koneksi saat ini
            if (empty($targetIp) || in_array($targetIp, ['127.0.0.1', '::1']) || str_starts_with($targetIp, '192.168.') || str_starts_with($targetIp, '10.')) {
                $publicIpRes = \Illuminate\Support\Facades\Cache::remember('my_dev_public_ip', 3600, function () {
                    try {
                        $res = \Illuminate\Support\Facades\Http::timeout(2)->get('https://api.ipify.org?format=json');
                        return $res->json('ip');
                    } catch (\Throwable $e) {
                        return null;
                    }
                });
                if ($publicIpRes) {
                    $targetIp = $publicIpRes;
                }
            }

            if (!empty($targetIp) && !in_array($targetIp, ['127.0.0.1', '::1'])) {
                $ipLocation = \Illuminate\Support\Facades\Cache::remember("ip_geo_{$targetIp}", 86400, function () use ($targetIp) {
                    try {
                        $response = \Illuminate\Support\Facades\Http::timeout(3)
                            ->get("http://ip-api.com/json/{$targetIp}?fields=status,message,country,regionName,city");
                        if ($response->successful() && $response->json('status') === 'success') {
                            $city   = $response->json('city');
                            $region = $response->json('regionName');
                            if ($city && $region) {
                                return "{$city}, {$region}";
                            } elseif ($city) {
                                return $city;
                            }
                        }
                    } catch (\Throwable $e) {
                        // ignore
                    }
                    return null;
                });

                if (!empty($ipLocation)) {
                    return $ipLocation;
                }
            }
        } catch (\Throwable $e) {
            // ignore
        }

        // 4. Fallback Terakhir
        try {
            if ($user->region) {
                return $user->region->name . ', Kolaka Utara, Sulawesi Tenggara';
            }
        } catch (\Throwable $e) {
            // ignore
        }

        return 'Kota Makassar, Sulawesi Selatan';
    }
}
