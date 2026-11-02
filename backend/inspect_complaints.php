<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$c1 = App\Models\Complaint::with(['user', 'citizen', 'latestDisposition.assignee', 'latestDisposition.department'])->where('public_code', 'ADU-0001')->first();
$c3 = App\Models\Complaint::with(['user', 'citizen', 'latestDisposition.assignee', 'latestDisposition.department'])->where('public_code', 'ADU-0003')->first();

echo "=== ADU-0001 ===\n";
echo "User: " . json_encode($c1?->user?->only(['id', 'name', 'phone', 'email', 'nik'])) . "\n";
echo "Citizen: " . json_encode($c1?->citizen?->only(['id', 'name', 'phone', 'email', 'nik'])) . "\n";
echo "Latest Disp: " . json_encode($c1?->latestDisposition?->only(['id', 'department_id', 'assigned_to', 'instruction', 'deadline'])) . "\n";
if ($c1?->latestDisposition) {
    echo "Assignee: " . json_encode($c1->latestDisposition->assignee?->only(['id', 'name', 'phone', 'email', 'role'])) . "\n";
    echo "Department: " . json_encode($c1->latestDisposition->department?->only(['id', 'name', 'phone'])) . "\n";
}

echo "\n=== ADU-0003 ===\n";
echo "User: " . json_encode($c3?->user?->only(['id', 'name', 'phone', 'email', 'nik'])) . "\n";
echo "Citizen: " . json_encode($c3?->citizen?->only(['id', 'name', 'phone', 'email', 'nik'])) . "\n";
echo "Latest Disp: " . json_encode($c3?->latestDisposition?->only(['id', 'department_id', 'assigned_to', 'instruction', 'deadline'])) . "\n";
