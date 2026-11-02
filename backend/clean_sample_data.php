<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

echo "\n=== MENGOSONGKAN DATA LAPORAN SAMPLE DARI DATABASE POSTGRESQL ===\n";

DB::statement('TRUNCATE TABLE supports, notifications, dispositions, complaint_status_histories, complaint_photos, ai_assessments, audit_logs CASCADE');
DB::statement('TRUNCATE TABLE complaints RESTART IDENTITY CASCADE');

echo "Semua tabel transaksi pengaduan berhasil dikosongkan (0 data).\n";
echo "Total laporan saat ini: " . App\Models\Complaint::count() . "\n";
echo "Akun user tetap aman: " . App\Models\User::count() . " pengguna terdaftar.\n";
