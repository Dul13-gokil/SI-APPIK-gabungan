<?php

namespace App\Services;

use App\Models\OtpVerification;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class OtpService
{
    /**
     * Buat kode OTP 6 digit baru dan kirim ke email pengguna.
     */
    public function generateOtp(string $email, string $purpose = 'REGISTER', int $validMinutes = 10): array
    {
        $normalizedEmail = strtolower(trim($email));

        // Batalkan OTP aktif sebelumnya untuk email & tujuan yang sama
        OtpVerification::where('email', $normalizedEmail)
            ->where('purpose', $purpose)
            ->whereNull('used_at')
            ->update(['used_at' => now()]);

        // Buat 6-digit kode OTP
        $code = str_pad((string) random_int(100000, 999999), 6, '0', STR_PAD_LEFT);

        $otp = OtpVerification::create([
            'email'      => $normalizedEmail,
            'code'       => $code,
            'purpose'    => $purpose,
            'expires_at' => now()->addMinutes($validMinutes),
        ]);

        $purposeLabel = match ($purpose) {
            'REGISTER'        => 'Pendaftaran Akun Warga',
            'FORGOT_PASSWORD' => 'Atur Ulang Kata Sandi',
            default           => 'Verifikasi Keamanan Akun',
        };

        // Kirim email HTML resmi (Jika target berupa email)
        $mailSent = false;
        if (filter_var($normalizedEmail, FILTER_VALIDATE_EMAIL)) {
            try {
                $htmlBody = <<<HTML
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Kode Verifikasi OTP</title>
</head>
<body style="margin: 0; padding: 0; background-color: #f8fafc; font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; color: #1e293b;">
  <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background-color: #f8fafc; padding: 30px 15px;">
    <tr>
      <td align="center">
        <table role="presentation" width="100%" style="max-width: 520px; background-color: #ffffff; border-radius: 16px; overflow: hidden; box-shadow: 0 4px 20px rgba(0,0,0,0.06); border: 1px solid #e2e8f0;">
          
          <!-- Header -->
          <tr>
            <td style="background: linear-gradient(135deg, #1e3a8a 0%, #1e40af 100%); padding: 28px 24px; text-align: center;">
              <h1 style="margin: 0; color: #ffffff; font-size: 18px; font-weight: 700; letter-spacing: 0.5px;">
                PEMERINTAH KABUPATEN
              </h1>
              <p style="margin: 6px 0 0 0; color: #93c5fd; font-size: 13px; font-weight: 500;">
                SI-APPIK - Sistem Informasi Pengaduan & Penanganan Infrastruktur
              </p>
            </td>
          </tr>

          <!-- Body Content -->
          <tr>
            <td style="padding: 32px 28px;">
              <h2 style="margin: 0 0 10px 0; color: #0f172a; font-size: 17px; font-weight: 700;">
                {$purposeLabel}
              </h2>
              <p style="margin: 0 0 20px 0; color: #475569; font-size: 14px; line-height: 1.6;">
                Halo,<br>
                Berikut adalah 6-digit kode verifikasi (OTP) Anda untuk melanjutkan proses autentikasi pada aplikasi:
              </p>

              <!-- OTP Box Card -->
              <div style="background-color: #f1f5f9; border: 2px dashed #3b82f6; border-radius: 14px; padding: 24px 16px; text-align: center; margin: 20px 0;">
                <div style="font-size: 11px; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: 1.5px; margin-bottom: 8px;">
                  SALIN KODE OTP ANDA
                </div>
                <div style="font-size: 40px; font-weight: 800; letter-spacing: 10px; color: #1e3a8a; font-family: 'Courier New', Courier, monospace; margin: 4px 0;">
                  {$code}
                </div>
                <div style="margin-top: 12px;">
                  <span style="display: inline-block; background-color: #dbeafe; color: #1e40af; font-size: 12px; font-weight: 600; padding: 5px 14px; border-radius: 20px;">
                    ⏱ Berlaku selama {$validMinutes} Menit
                  </span>
                </div>
              </div>

              <!-- Peringatan Keamanan -->
              <div style="background-color: #fffbeb; border-left: 4px solid #f59e0b; padding: 12px 14px; border-radius: 6px; margin: 20px 0 22px 0;">
                <p style="margin: 0; font-size: 12.5px; color: #92400e; line-height: 1.5;">
                  <strong>Penting:</strong> Salin kode 6-digit di atas dan masukkan secara manual pada kolom verifikasi aplikasi. <strong>JANGAN</strong> berikan kode ini kepada siapa pun termasuk petugas atau admin.
                </p>
              </div>

              <p style="margin: 0; color: #94a3b8; font-size: 12px; line-height: 1.5;">
                Jika Anda tidak merasa meminta kode verifikasi ini, abaikan pesan ini. Akun Anda tetap aman.
              </p>
            </td>
          </tr>

          <!-- Footer -->
          <tr>
            <td style="background-color: #f8fafc; border-top: 1px solid #e2e8f0; padding: 20px 24px; text-align: center;">
              <p style="margin: 0; color: #64748b; font-size: 11.5px; line-height: 1.4;">
                Dinas Pekerjaan Umum & Penataan Ruang (PUPR)<br>
                Pemerintah Kabupaten
              </p>
              <p style="margin: 6px 0 0 0; color: #94a3b8; font-size: 11px;">
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

                $subject = "[KODE OTP: {$code}] Verifikasi Akun SI-APPIK Pemkab Kolaka Utara";
                $plainBody = "KODE OTP ANDA: {$code}\n\n"
                    . "Kode verifikasi ini digunakan untuk {$purposeLabel}.\n"
                    . "Masa berlaku: {$validMinutes} Menit.\n\n"
                    . "PERHATIAN KEAMANAN:\n"
                    . "JANGAN PERNAH memberikan kode OTP ini kepada siapa pun, termasuk pihak yang mengaku sebagai admin, petugas teknis, atau operator dinas.\n\n"
                    . "Dinas Pekerjaan Umum & Penataan Ruang (PUPR)\n"
                    . "Pemerintah Kabupaten Kolaka Utara";

                Mail::send([], [], function ($message) use ($normalizedEmail, $subject, $htmlBody, $plainBody) {
                    $message->to($normalizedEmail)
                        ->subject($subject)
                        ->html($htmlBody)
                        ->text($plainBody)
                        ->priority(1);

                    $headers = $message->getHeaders();
                    $headers->addTextHeader('X-Priority', '1');
                    $headers->addTextHeader('Importance', 'High');
                    $headers->addTextHeader('X-MSMail-Priority', 'High');
                });
                $mailSent = true;
            } catch (\Throwable $e) {
                Log::warning("Gagal mengirim email OTP ke {$normalizedEmail}: " . $e->getMessage());
            }
        }

        Log::info("OTP Generated [{$purpose}] for {$normalizedEmail}: {$code} (Expires in {$validMinutes}m)");

        return [
            'success'   => true,
            'email'     => $normalizedEmail,
            'expires_in'=> $validMinutes * 60,
            'dev_otp'   => null,
            'mail_sent' => $mailSent,
        ];
    }

    /**
     * Verifikasi kode OTP dengan hasil detail dan pembatasan maksimal 3 kali salah.
     *
     * @return array{valid: bool, message: string}
     */
    public function verifyOtpWithResult(string $email, string $code, string $purpose = 'REGISTER', bool $consume = true): array
    {
        $normalizedEmail = strtolower(trim($email));
        $cleanCode       = trim($code);

        // Cari OTP aktif terbaru untuk email & purpose ini (belum used_at dan belum expired)
        $otp = OtpVerification::where('email', $normalizedEmail)
            ->where('purpose', $purpose)
            ->whereNull('used_at')
            ->where('expires_at', '>=', now())
            ->latest('id')
            ->first();

        if (!$otp) {
            return [
                'valid'   => false,
                'message' => 'Kode OTP tidak ditemukan atau telah kedaluwarsa. Silakan minta kode OTP baru.',
            ];
        }

        // Cek jika sudah melebihi batas percobaan sebelumnya
        if ($otp->hasExceededAttempts(3)) {
            $otp->markAsUsed();
            return [
                'valid'   => false,
                'message' => 'Kode OTP telah dibatalkan karena salah sebanyak 3 kali. Silakan minta kode OTP baru demi keamanan akun Anda.',
            ];
        }

        // Jika kode tidak cocok
        if ($otp->code !== $cleanCode) {
            $otp->increment('attempts');
            $remaining = 3 - (int) $otp->attempts;

            if ($remaining <= 0) {
                $otp->markAsUsed();
                return [
                    'valid'   => false,
                    'message' => 'Kode OTP salah sebanyak 3 kali dan telah dibatalkan demi keamanan. Silakan minta kode OTP baru.',
                ];
            }

            return [
                'valid'   => false,
                'message' => "Kode OTP yang Anda masukkan salah. Sisa kesempatan mencoba: {$remaining} kali sebelum kode dibatalkan.",
            ];
        }

        // Jika kode valid, tandai sudah digunakan jika $consume bernilai true
        if ($consume) {
            $otp->markAsUsed();
        }

        return [
            'valid'   => true,
            'message' => 'Kode OTP berhasil diverifikasi.',
        ];
    }

    /**
     * Verifikasi kode OTP (kompatibilitas backward).
     */
    public function verifyOtp(string $email, string $code, string $purpose = 'REGISTER', bool $consume = true): bool
    {
        return $this->verifyOtpWithResult($email, $code, $purpose, $consume)['valid'];
    }
}
