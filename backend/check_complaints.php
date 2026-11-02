<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$complaints = App\Models\Complaint::all();
echo "\n=== CEK TABEL 'complaints' DI DATABASE POSTGRESQL 'sik' ===\n";
echo "Total baris di tabel complaints: " . $complaints->count() . "\n\n";

foreach ($complaints as $c) {
    echo "ID: {$c->id} | Code: {$c->public_code} | Type: {$c->damage_type}\n";
}

echo "\n--- TEST CHATBOT QUERY ---\n";
$service = $app->make(App\Domain\Chatbot\Services\ChatbotService::class);
$res = $service->handleMessage('START', 'cek status 0005/ringan/2026');
echo $res['reply'] . "\n";
