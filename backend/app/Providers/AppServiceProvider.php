<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        \Carbon\Carbon::setLocale('id');
        app()->setLocale('id');

        $this->configureRateLimiters();
    }

    /**
     * Bucket terpisah per jenis aksi.
     *
     * Bawaan Laravel memakai satu penghitung per domain+IP untuk SEMUA rute ber-throttle,
     * sehingga permintaan OTP atau API ikut memakan jatah login 5/menit. Dengan limiter
     * bernama, tiap aksi punya penghitung sendiri dan 429 hanya muncul untuk aksi yang
     * benar-benar melewati batasnya.
     */
    private function configureRateLimiters(): void
    {
        // 5 percobaan/menit terlalu ketat untuk kantor yang berbagi satu IP.
        RateLimiter::for('login', fn (Request $request) => Limit::perMinute(10)->by($request->ip()));
        RateLimiter::for('password-reset', fn (Request $request) => Limit::perMinute(5)->by($request->ip()));
        RateLimiter::for('api-auth', fn (Request $request) => Limit::perMinute(5)->by($request->ip()));
        RateLimiter::for('check-warga', fn (Request $request) => Limit::perMinute(30)->by($request->ip()));
        RateLimiter::for('complaint-create', fn (Request $request) => Limit::perMinute(10)->by($request->ip()));
        RateLimiter::for('reverse-geocode', fn (Request $request) => Limit::perMinute(30)->by($request->ip()));
        RateLimiter::for('api-chat', fn (Request $request) => Limit::perMinute(30)->by($request->ip()));
        RateLimiter::for('api-ai', fn (Request $request) => Limit::perMinute(15)->by($request->ip()));
    }
}
