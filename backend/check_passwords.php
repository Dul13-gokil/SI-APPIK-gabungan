<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$users = App\Models\User::all();
echo "\n=== CEK PASSWORD PENGGUNA ===\n\n";
foreach ($users as $u) {
    $isPassword = Illuminate\Support\Facades\Hash::check('password', $u->password) ? 'Cocok ("password")' : 'Bukan "password"';
    $is12345678 = Illuminate\Support\Facades\Hash::check('12345678', $u->password) ? 'Cocok ("12345678")' : 'Bukan "12345678"';
    $isRoot = Illuminate\Support\Facades\Hash::check('root', $u->password) ? 'Cocok ("root")' : 'Bukan "root"';
    $isDul = Illuminate\Support\Facades\Hash::check('dularafat', $u->password) ? 'Cocok ("dularafat")' : 'Bukan "dularafat"';
    echo "ID: {$u->id} | {$u->email} | Role: {$u->role}\n";
    echo "  -> 'password': {$isPassword}\n";
    echo "  -> '12345678': {$is12345678}\n";
    echo "  -> 'root': {$isRoot}\n";
    echo "  -> 'dularafat': {$isDul}\n\n";
}
