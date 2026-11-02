<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$users = App\Models\User::all(['id', 'name', 'email', 'phone', 'role', 'is_anonymous', 'created_at']);
echo "\n=== DAFTAR AKUN PENGGUNA DI DATABASE 'sik' (PostgreSQL) ===\n\n";
printf("%-4s | %-22s | %-28s | %-14s | %-8s | %-9s\n", "ID", "NAMA", "EMAIL", "NO HP", "ROLE", "ANONIM");
echo str_repeat("-", 95) . "\n";
foreach ($users as $u) {
    printf(
        "%-4d | %-22s | %-28s | %-14s | %-8s | %-9s\n",
        $u->id,
        mb_strimwidth($u->name, 0, 22, '..'),
        mb_strimwidth($u->email, 0, 28, '..'),
        $u->phone ?? '-',
        $u->role,
        $u->is_anonymous ? 'YA' : 'TIDAK'
    );
}
echo "\nTotal akun terdaftar: " . count($users) . "\n";
