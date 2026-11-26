<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * Jalur pemulihan terakhir saat pemilik akun tidak bisa masuk dan email OTP
 * tidak bisa dikirim (mis. MAIL_MAILER=log di server tanpa SMTP).
 *
 *   php artisan user:password admin@kabupaten.go.id
 *   php artisan user:password admin@kabupaten.go.id --password=SandiBaru2026
 */
class ResetUserPassword extends Command
{
    protected $signature = 'user:password
                            {email : Email akun yang akan direset}
                            {--password= : Sandi baru; bila kosong dibuat acak}';

    protected $description = 'Reset kata sandi akun langsung dari server (tanpa email)';

    public function handle(): int
    {
        $email = mb_strtolower(trim((string) $this->argument('email')));

        $user = User::whereRaw('LOWER(email) = ?', [$email])->first();

        if (! $user) {
            $this->error("Akun dengan email {$email} tidak ditemukan.");
            $this->line('Daftar email yang ada:');
            foreach (User::orderBy('id')->pluck('email') as $ada) {
                $this->line('  - ' . $ada);
            }

            return self::FAILURE;
        }

        $ditetapkan = (string) ($this->option('password') ?? '');
        $sandi = $ditetapkan !== '' ? $ditetapkan : Str::password(12, symbols: false);

        $user->password = Hash::make($sandi);
        // Sandi yang ditetapkan operator dianggap final; sandi acak wajib diganti.
        $user->must_change_password = $ditetapkan === '';
        $user->is_active = true;
        $user->deactivated_at = null;
        $user->deactivation_reason = null;
        $user->save();

        // Sesi lama dicabut supaya sandi lama tidak bisa dipakai lagi.
        $user->tokens()->delete();

        $this->info("Sandi akun {$user->email} ({$user->role}) berhasil direset.");
        $this->line('Sandi baru: ' . $sandi);
        $this->line($ditetapkan === ''
            ? 'Akun diaktifkan kembali dan wajib mengganti sandi saat login berikutnya.'
            : 'Akun diaktifkan kembali. Sandi ini langsung dapat dipakai login.');

        return self::SUCCESS;
    }
}