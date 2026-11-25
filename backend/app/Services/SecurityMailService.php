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

        $safeName = e($user->name);
        $htmlBody = view('emails.v2_notification', [
            'tone'     => 'primary',
            'badge'    => 'Keamanan Akun',
            'title'    => 'Kata Sandi Berhasil Diperbarui',
            'greeting' => "Halo <strong>{$safeName}</strong>,",
            'intro'    => 'Kata sandi akun SI-APPIK Anda baru saja diubah dengan rincian berikut:',
            'details'  => [
                'Waktu'     => e($timeString),
                'Lokasi'    => e($deviceLocation),
                'Perangkat' => e($deviceInfo),
            ],
            'noteTone' => 'danger',
            'note'     => '<strong>Bukan Anda?</strong> Segera atur ulang kata sandi melalui aplikasi dan hubungi Administrator IT Dinas PUPR Kabupaten.',
        ])->render();

        $plainText = "PEMBERITAHUAN PERUBAHAN KATA SANDI - SI-APPIK\n\n"
            . "Halo {$user->name},\n"
            . "Kata sandi untuk akun SI-APPIK Anda telah berhasil diubah.\n\n"
            . "Rincian Aktivitas:\n"
            . "- Waktu: {$timeString}\n"
            . "- Lokasi: {$deviceLocation}\n"
            . "- Perangkat: {$deviceInfo}\n\n"
            . "Jika BUKAN Anda yang melakukan perubahan ini, segera amankan akun Anda dengan melakukan reset kata sandi melalui aplikasi.\n\n"
            . "Pemerintah Kabupaten - Dinas PUPR";

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
                Dinas Pekerjaan Umum & Penataan Ruang (PUPR) - Kabupaten
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
            . "Pemerintah Kabupaten - Dinas PUPR";

        return $this->sendPriorityMail($normalizedEmail, $subject, $htmlBody, $plainText);
    }

    /**
     * Kirim email aktivasi / undangan akun baru untuk Pengawas Lapangan atau Operator.
     */
    public function sendStaffInvitationEmail(User $user, string $role, string $defaultPassword = 'password'): bool
    {
        $normalizedEmail = strtolower(trim($user->email));
        if (!filter_var($normalizedEmail, FILTER_VALIDATE_EMAIL)) {
            return false;
        }

        $loginUrl = url('/login');
        $isOperator = in_array(strtoupper($role), ['OPERATOR', 'DINAS']);
        $roleTitle = $isOperator ? 'Operator Verifikasi Sistem' : 'Pengawas Lapangan (Tim Teknis PUPR)';
        $portalDescription = $isOperator 
            ? 'Portal Web Resmi SI-APPIK Kabupaten' 
            : 'Aplikasi Mobile SI-APPIK & Web Portal Petugas';

        $subject = "[AKUN BARU] Selamat Datang di SI-APPIK Kabupaten - Akun {$roleTitle}";

        $safeName = e($user->name);
        $htmlBody = view('emails.v2_notification', [
            'tone'        => 'primary',
            'badge'       => 'Aktivasi Staf',
            'title'       => "Akun {$roleTitle} Aktif",
            'greeting'    => "Halo <strong>{$safeName}</strong>,",
            'intro'       => $isOperator
                ? "Akun Anda sebagai <strong>{$roleTitle}</strong> Dinas PUPR Kabupaten telah dibuat untuk verifikasi laporan, disposisi regu, dan validasi pekerjaan."
                : "Akun Anda sebagai <strong>{$roleTitle}</strong> Dinas PUPR Kabupaten telah dibuat untuk menerima penugasan perbaikan dan mengunggah dokumentasi lapangan.",
            'details'     => [
                'Email'              => e($user->email),
                'Kata Sandi Awal'    => '<span style="font-family: monospace;">' . e($defaultPassword) . '</span>',
                'Peran'              => e($roleTitle),
                'Akses'              => e($portalDescription),
            ],
            'actionLabel' => 'Masuk ke SI-APPIK',
            'actionUrl'   => $loginUrl,
            'noteTone'    => 'info',
            'note'        => '<strong>Login Pertama:</strong> Periksa nama lengkap, lengkapi nomor WhatsApp aktif, dan ganti kata sandi awal Anda.',
        ])->render();

        $plainText = "AKTIVASI AKUN {$roleTitle} - SI-APPIK KABUPATEN\n\n"
            . "Halo {$user->name},\n\n"
            . "Anda telah didaftarkan sebagai {$roleTitle} pada SI-APPIK Dinas PUPR Kabupaten.\n\n"
            . "Informasi Akun:\n"
            . "- Email: {$user->email}\n"
            . "- Kata Sandi Default: {$defaultPassword}\n"
            . "- Tautan Masuk: {$loginUrl}\n\n"
            . "PENTING: Saat pertama kali masuk, Anda diwajibkan memeriksa nama, melengkapi nomor handphone/WA, dan mengganti kata sandi default menjadi kata sandi pribadi Anda.\n\n"
            . "Pemerintah Kabupaten - Dinas PUPR";

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

        $details = [];
        $changeRowsPlain = '';
        foreach ($changes as $field => $desc) {
            $details[$field] = e($desc);
            $changeRowsPlain .= "- {$field}: {$desc}\n";
        }
        $details['Waktu'] = e($timeString);

        $safeName = e($user->name);
        $htmlBody = view('emails.v2_notification', [
            'tone'     => 'primary',
            'badge'    => 'Pembaruan Admin',
            'title'    => 'Data Akun Diperbarui',
            'greeting' => "Halo <strong>{$safeName}</strong>,",
            'intro'    => 'Data profil akun SI-APPIK Anda baru saja diperbarui oleh <strong>Super Administrator</strong>:',
            'details'  => $details,
            'noteTone' => 'warning',
            'note'     => '<strong>Catatan Keamanan:</strong> Jika Anda tidak meminta perubahan ini, segera hubungi Helpdesk IT Dinas PUPR Kabupaten.',
        ])->render();

        $plainText = "PEMBERITAHUAN PEMBARUAN PROFIL AKUN - SI-APPIK\n\n"
            . "Halo {$user->name},\n"
            . "Data akun SI-APPIK Anda telah diperbarui oleh Super Administrator:\n\n"
            . "Rincian Pembaruan:\n"
            . $changeRowsPlain
            . "- Waktu: {$timeString}\n\n"
            . "Jika Anda merasa tidak mengajukan permohonan ini, hubungi Administrator IT Dinas PUPR Kabupaten.\n\n"
            . "Pemerintah Kabupaten - Dinas PUPR";

        // 1. Kirim ke email baru (atau email saat ini)
        $sentNew = false;
        if (filter_var($newEmail, FILTER_VALIDATE_EMAIL)) {
            $sentNew = $this->sendPriorityMail($newEmail, $subject, $htmlBody, $plainText);
        }

        // 2. Jika email diubah, kirim juga notifikasi ke email lama demi mencegah pengambilalihan akun diam-diam (Anti-Account Takeover)
        if ($oldEmail && strtolower(trim($oldEmail)) !== $newEmail && filter_var($oldEmail, FILTER_VALIDATE_EMAIL)) {
            $oldEmailSubject = "[PERINGATAN KEAMANAN] Alamat Email Akun SI-APPIK Anda Telah Dialihkan";
            $oldPlain = "PERINGATAN KEAMANAN - SI-APPIK KABUPATEN\n\n"
                . "Halo,\n"
                . "Kami memberitahukan bahwa alamat email akun SI-APPIK yang terhubung dengan email ini ({$oldEmail}) telah dialihkan ke ({$newEmail}) oleh Administrator Sistem pada {$timeString}.\n\n"
                . "Jika perubahan ini tanpa persetujuan Anda, segera laporkan ke Tim IT Dinas PUPR Kabupaten.\n\n"
                . "Pemerintah Kabupaten - Dinas PUPR";
            $this->sendPriorityMail(trim($oldEmail), $oldEmailSubject, $htmlBody, $oldPlain);
        }

        return $sentNew;
    }

    /**
     * Kirim email pemberitahuan resmi saat akun dinonaktifkan atau diaktifkan kembali oleh Super Admin.
     */
    public function sendAccountStatusChangedAlert(User $user, bool $isActive, ?string $reason = null): bool
    {
        $targetEmail = strtolower(trim($user->email));
        if (empty($targetEmail)) {
            return false;
        }

        $timeString = now('Asia/Makassar')->translatedFormat('d F Y, H:i') . ' WITA';
        $safeName = e($user->name);
        $safeReason = e($reason ?: 'Kebijakan tata kelola dan keamanan sistem.');

        if ($isActive) {
            $subject = 'Pemberitahuan Pengaktifan Kembali Akun SI-APPIK';
            $statusTitle = 'Akun Diaktifkan Kembali';
            $leadText = 'Akun SI-APPIK Anda telah <strong>diaktifkan kembali</strong> oleh Super Administrator. Anda kini dapat masuk menggunakan kredensial resmi Anda.';
            $reasonLabel = 'Catatan';
            $tone = 'success';
            $badge = 'Akun Aktif';
        } else {
            $subject = 'Pemberitahuan Penonaktifan Akun SI-APPIK';
            $statusTitle = 'Akun Dinonaktifkan';
            $leadText = 'Akun SI-APPIK Anda telah <strong>dinonaktifkan sementara</strong> oleh Super Administrator sehingga akses masuk dibatasi.';
            $reasonLabel = 'Alasan';
            $tone = 'danger';
            $badge = 'Akun Nonaktif';
        }

        $htmlBody = view('emails.v2_notification', [
            'tone'     => $tone,
            'badge'    => $badge,
            'title'    => $statusTitle,
            'greeting' => "Halo <strong>{$safeName}</strong>,",
            'intro'    => $leadText,
            'details'  => [
                $reasonLabel => $safeReason,
                'Waktu'      => e($timeString),
            ],
            'noteTone' => $isActive ? 'success' : 'danger',
            'note'     => 'Apabila memerlukan bantuan lebih lanjut, silakan hubungi Administrator Dinas PUPR Kabupaten.',
        ])->render();

        $plainText = "Yth. {$user->name},\n\n"
            . ($isActive
                ? "Akun SI-APPIK Anda telah diaktifkan kembali oleh Super Administrator pada {$timeString}.\n"
                : "Akun SI-APPIK Anda telah dinonaktifkan oleh Super Administrator pada {$timeString}.\n")
            . "{$reasonLabel}: " . ($reason ?: 'Kebijakan tata kelola dan keamanan sistem.') . "\n\n"
            . "Pemerintah Kabupaten - Dinas PUPR";

        return $this->sendPriorityMail($targetEmail, $subject, $htmlBody, $plainText);
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

        $details = [];
        $changeRowsPlain = '';
        foreach ($changes as $field => $desc) {
            $details[$field] = e($desc);
            $changeRowsPlain .= "- {$field}: {$desc}\n";
        }
        $details['Lokasi'] = e($deviceLocation);
        $details['Perangkat'] = e($deviceInfo);
        $details['Waktu'] = e($timeString);

        $safeName = e($user->name);
        $htmlBody = view('emails.v2_notification', [
            'tone'     => 'primary',
            'badge'    => 'Pembaruan Akun',
            'title'    => 'Data Akun Diperbarui',
            'greeting' => "Halo <strong>{$safeName}</strong>,",
            'intro'    => 'Data profil akun SI-APPIK Anda baru saja diperbarui dengan rincian berikut:',
            'details'  => $details,
            'noteTone' => 'danger',
            'note'     => '<strong>Bukan Anda?</strong> Segera atur ulang kata sandi melalui aplikasi dan hubungi Administrator IT Dinas PUPR Kabupaten.',
        ])->render();

        $plainText = "PEMBERITAHUAN PEMBARUAN DATA AKUN - SI-APPIK\n\n"
            . "Halo {$user->name},\n"
            . "Data akun SI-APPIK Anda baru saja berhasil diperbarui:\n\n"
            . "Rincian Pembaruan:\n"
            . $changeRowsPlain
            . "- Lokasi: {$deviceLocation}\n"
            . "- Perangkat: {$deviceInfo}\n"
            . "- Waktu: {$timeString}\n\n"
            . "Jika BUKAN Anda yang melakukan perubahan ini, segera hubungi Helpdesk IT Dinas PUPR Kabupaten.\n\n"
            . "Pemerintah Kabupaten - Dinas PUPR";

        // 1. Kirim ke email aktif saat ini
        $sentNew = false;
        if (filter_var($newEmail, FILTER_VALIDATE_EMAIL)) {
            $sentNew = $this->sendPriorityMail($newEmail, $subject, $htmlBody, $plainText);
        }

        // 2. Jika email diubah, kirim juga notifikasi ke email lama (Anti-Account Takeover)
        if ($oldEmail && strtolower(trim($oldEmail)) !== $newEmail && filter_var($oldEmail, FILTER_VALIDATE_EMAIL)) {
            $oldEmailSubject = "[PERINGATAN KEAMANAN] Alamat Email Akun SI-APPIK Anda Telah Diubah";
            $oldPlain = "PERINGATAN KEAMANAN - SI-APPIK KABUPATEN\n\n"
                . "Halo,\n"
                . "Alamat email akun SI-APPIK yang sebelumnya terhubung dengan email ini ({$oldEmail}) telah dialihkan ke ({$newEmail}) pada {$timeString} dari {$deviceLocation}.\n\n"
                . "Jika perubahan ini bukan atas persetujuan Anda, segera hubungi Tim IT Dinas PUPR Kabupaten.\n\n"
                . "Pemerintah Kabupaten - Dinas PUPR";
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
                            'User-Agent' => 'SI-APPIK-KolakaUtara-Aplikasi/1.0 (admin@kabupaten.go.id)',
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
                return $user->region->name . ', Kabupaten, Sulawesi Tenggara';
            }
        } catch (\Throwable $e) {
            // ignore
        }

        return 'Kota Makassar, Sulawesi Selatan';
    }
}
