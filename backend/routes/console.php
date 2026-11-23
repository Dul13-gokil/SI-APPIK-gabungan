<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Jaringan jalan OSM berubah pelan; impor ulang tiap bulan agar peta tidak usang.
// Aktifkan cron `php artisan schedule:run` tiap menit di server (lihat docs/DOCKER-DEPLOY.md).
Schedule::command('roads:import')
    ->monthlyOn(1, '02:00')
    ->timezone('Asia/Makassar')
    ->withoutOverlapping();
