<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

/**
 * Mencadangkan database dan berkas bukti foto dalam satu kali jalan.
 *
 * Dump database saja tidak cukup: foto aduan dan bukti pengerjaan hidup di volume
 * terpisah (storage/app/public), sehingga restore tanpa arsip foto akan menghasilkan
 * laporan yang fotonya hilang.
 */
class BackupDatabase extends Command
{
    protected $signature = 'db:backup
                            {--keep=7 : Jumlah hari cadangan disimpan}
                            {--out= : Folder tujuan lain (mis. drive eksternal)}';

    protected $description = 'Cadangkan database PostgreSQL dan berkas foto ke satu folder';

    public function handle(): int
    {
        $keep = max(1, (int) $this->option('keep'));
        $dir = $this->option('out') ?: storage_path('app/backups');
        File::ensureDirectoryExists($dir);

        $stamp = now()->format('Ymd-His');
        $sqlFile = "{$dir}/db-{$stamp}.sql.gz";
        $photoFile = "{$dir}/foto-{$stamp}.tar.gz";

        if (!$this->dumpDatabase($sqlFile)) {
            return self::FAILURE;
        }

        if (!$this->archivePhotos($photoFile)) {
            return self::FAILURE;
        }

        $this->info(sprintf(
            'Cadangan selesai: %s (%s) dan %s (%s)',
            basename($sqlFile),
            $this->humanSize(filesize($sqlFile)),
            basename($photoFile),
            $this->humanSize(filesize($photoFile))
        ));

        $this->prune($dir, $keep);

        return self::SUCCESS;
    }

    private function dumpDatabase(string $target): bool
    {
        $command = sprintf(
            'PGPASSWORD=%s pg_dump --no-owner --no-acl -h %s -p %s -U %s %s | gzip > %s',
            escapeshellarg((string) config('database.connections.pgsql.password')),
            escapeshellarg((string) config('database.connections.pgsql.host')),
            escapeshellarg((string) config('database.connections.pgsql.port')),
            escapeshellarg((string) config('database.connections.pgsql.username')),
            escapeshellarg((string) config('database.connections.pgsql.database')),
            escapeshellarg($target)
        );

        exec($command . ' 2>&1', $output, $code);

        // Pipe mengembalikan status perintah terakhir; periksa isi berkas juga.
        if ($code !== 0 || !is_file($target) || filesize($target) < 1024) {
            $this->error('Gagal mencadangkan database: ' . implode(' ', $output));
            File::delete($target);

            return false;
        }

        return true;
    }

    private function archivePhotos(string $target): bool
    {
        $publicDir = storage_path('app/public');

        if (!is_dir($publicDir)) {
            // Tanpa foto tidak ada yang perlu diarsipkan; buat berkas kosong agar
            // penamaan cadangan tetap berpasangan.
            File::put($target, '');

            return true;
        }

        // Hanya folder unggahan; cache/ dan berkas lain tidak perlu ikut.
        $command = sprintf(
            'tar -czf %s -C %s complaints resolutions 2>&1',
            escapeshellarg($target),
            escapeshellarg($publicDir)
        );

        exec($command, $output, $code);

        // tar gagal bila salah satu folder belum ada (belum ada bukti pengerjaan).
        if ($code !== 0) {
            File::put($target, '');
            $this->warn('Arsip foto dilewati: ' . implode(' ', $output));
        }

        return true;
    }

    private function prune(string $dir, int $keepDays): void
    {
        $batas = now()->subDays($keepDays)->getTimestamp();
        $dihapus = 0;

        foreach (File::glob("{$dir}/{db,foto}-*.gz", GLOB_BRACE) as $file) {
            if (filemtime($file) < $batas) {
                File::delete($file);
                $dihapus++;
            }
        }

        $this->line("Cadangan lebih dari {$keepDays} hari dihapus: {$dihapus} berkas.");
    }

    private function humanSize(int $bytes): string
    {
        return $bytes >= 1048576
            ? round($bytes / 1048576, 1) . ' MB'
            : round($bytes / 1024, 1) . ' KB';
    }
}
