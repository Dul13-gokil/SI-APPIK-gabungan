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
                $htmlBody = view('emails.v2_notification', [
                    'tone'        => 'primary',
                    'badge'       => 'Verifikasi OTP',
                    'title'       => $purposeLabel,
                    'greeting'    => 'Halo,',
                    'intro'       => 'Masukkan 6-digit kode verifikasi berikut pada aplikasi SI-APPIK untuk melanjutkan proses Anda.',
                    'otpCode'     => $code,
                    'otpValidity' => "{$validMinutes} menit",
                    'noteTone'    => 'warning',
                    'note'        => '<strong>Keamanan:</strong> Jangan bagikan kode ini kepada siapa pun, termasuk pihak yang mengaku petugas atau admin.',
                ])->render();

                $subject = "[KODE OTP: {$code}] Verifikasi Akun SI-APPIK Pemerintah Kabupaten";
                $plainBody = "KODE OTP ANDA: {$code}\n\n"
                    . "Kode verifikasi ini digunakan untuk {$purposeLabel}.\n"
                    . "Masa berlaku: {$validMinutes} Menit.\n\n"
                    . "PERHATIAN KEAMANAN:\n"
                    . "JANGAN PERNAH memberikan kode OTP ini kepada siapa pun, termasuk pihak yang mengaku sebagai admin, pekerja lapangan, atau operator dinas.\n\n"
                    . "Dinas Pekerjaan Umum & Penataan Ruang (PUPR)\n"
                    . "Pemerintah Kabupaten";

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

        if (app()->environment('local')) {
            Log::info("OTP Generated [{$purpose}] for {$normalizedEmail}: {$code} (Expires in {$validMinutes}m)");
        } else {
            Log::info("OTP Generated [{$purpose}] for {$normalizedEmail} (mail_sent=" . ($mailSent ? 'true' : 'false') . ", expires_in={$validMinutes}m)");
        }

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
