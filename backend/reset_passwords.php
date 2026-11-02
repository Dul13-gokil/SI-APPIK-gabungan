<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$users = App\Models\User::all();
foreach ($users as $u) {
    // Set all users password to 'password'
    $u->password = Illuminate\Support\Facades\Hash::make('password');
    $u->save();
    echo "Password untuk {$u->email} ({$u->role}) telah direset ke: 'password'\n";
}
echo "\nSemua akun sekarang bisa login dengan password: password\n";
