<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Memulihkan cadangan hasil `db:backup` ke database yang sedang aktif.
 *
 * Sengaja tidak memulihkan arsip foto: berkas foto tidak pernah ditimpa oleh aplikasi,
 * jadi memulihkannya hanya perlu saat benar-benar hilang. Jalankan manual bila perlu:
 *   tar -xzf foto-<stamp>.tar.gz -C storage/app/public
 */
class RestoreDatabase extends Command
{
    protected $signature = 'db:restore
                            {file : Berkas .sql.gz hasil db:backup}
                            {--force : Lewati konfirmasi}';

    protected $description = 'Pulihkan database dari berkas cadangan .sql.gz';

    public function handle(): int
    {
        $file = $this->argument('file');

        if (!is_file($file)) {
            $this->error("Berkas cadangan tidak ditemukan: {$file}");

            return self::FAILURE;
        }

        if (!$this->option('force') && !$this->confirm('Seluruh isi database saat ini akan ditimpa. Lanjutkan?')) {
            $this->line('Dibatalkan.');

            return self::FAILURE;
        }

        $conn = config('database.connections.pgsql');
        $command = sprintf(
            'gunzip -c %s | PGPASSWORD=%s psql -h %s -p %s -U %s -d %s -v ON_ERROR_STOP=1',
            escapeshellarg($file),
            escapeshellarg((string) $conn['password']),
            escapeshellarg((string) $conn['host']),
            escapeshellarg((string) $conn['port']),
            escapeshellarg((string) $conn['username']),
            escapeshellarg((string) $conn['database'])
        );

        exec($command . ' 2>&1', $output, $code);

        if ($code !== 0) {
            $this->error('Restore gagal: ' . implode("\n", array_slice($output, -10)));

            return self::FAILURE;
        }

        $this->info('Restore selesai. Jumlah baris tabel utama:');
        $this->table(['Tabel', 'Baris'], collect(['users', 'complaints', 'complaint_photos', 'dispositions'])
            ->map(fn ($t) => [$t, DB::table($t)->count()])
            ->all());

        return self::SUCCESS;
    }
}
