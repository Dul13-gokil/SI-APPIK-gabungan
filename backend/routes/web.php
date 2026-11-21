<?php

use App\Http\Controllers\Web\WebAuthController;
use App\Http\Controllers\Web\WebBupatiController;
use App\Http\Controllers\Web\WebOperatorController;
use App\Http\Controllers\Web\WebPetugasController;
use App\Http\Controllers\Web\WebSuperAdminController;
use App\Http\Controllers\Web\WebTeamController;
use App\Models\Complaint;
use App\Models\Region;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes - Sistem Informasi Pengaduan Infrastruktur Kabupaten
|--------------------------------------------------------------------------
*/

// 1. Halaman Beranda Publik (GIS Persebaran & Tracking Aduan) — Langsung dari data aktual PostgreSQL (sinkron 100% dengan Peta & API)
Route::get('/', function () {
    $allStatusCounts = Complaint::selectRaw('status, count(*) as total')
        ->groupBy('status')
        ->pluck('total', 'status');

    // Publik: Hanya hitung laporan valid (non-REJECTED & non-DRAFT) agar 100% sama dengan Peta GIS & API Summary
    $publicTotal = (int) $allStatusCounts
        ->reject(fn ($cnt, $st) => in_array($st, ['REJECTED', 'DRAFT'], true))
        ->sum();

    $stats = [
        'total'              => $publicTotal,
        'submitted'          => (int) (($allStatusCounts['SUBMITTED'] ?? 0) + ($allStatusCounts['VERIFICATION'] ?? 0) + ($allStatusCounts['VERIFIED'] ?? 0)),
        'dispatched'         => (int) ($allStatusCounts['DISPATCHED'] ?? 0),
        'in_progress'        => (int) (($allStatusCounts['IN_PROGRESS'] ?? 0) + ($allStatusCounts['REVISION_REQUIRED'] ?? 0)),
        'waiting_validation' => (int) ($allStatusCounts['WAITING_VERIFICATION'] ?? 0),
        'completed'          => (int) (($allStatusCounts['COMPLETED'] ?? 0) + ($allStatusCounts['CLOSED'] ?? 0)),
        'rejected'           => (int) ($allStatusCounts['REJECTED'] ?? 0),
    ];

    $districts = Region::pluck('district')
        ->filter()
        ->map(fn ($d) => (string) $d)
        ->unique()
        ->sort()
        ->values()
        ->all();

    $catCounts = Complaint::whereNotIn('status', ['REJECTED', 'DRAFT'])
        ->selectRaw('category_id, count(*) as total')
        ->groupBy('category_id')
        ->pluck('total', 'category_id');

    $categoryStats = [
        'jalan'     => (int) ($catCounts[1] ?? 0),
        'pju'       => (int) ($catCounts[2] ?? 0),
        'fasilitas' => (int) ($catCounts[3] ?? 0),
    ];

    return view('landing', compact('stats', 'districts', 'categoryStats'));
})->name('home');

// 2. Autentikasi Web
Route::get('/login', [WebAuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [WebAuthController::class, 'login'])->middleware('throttle:login');
Route::match(['get', 'post'], '/logout', [WebAuthController::class, 'logout'])->name('logout');
Route::post('/forgot-password', [WebAuthController::class, 'sendForgotPasswordOtp'])->middleware('throttle:password-reset')->name('password.update');
Route::post('/auth/forgot-password/send-otp', [WebAuthController::class, 'sendForgotPasswordOtp'])->middleware('throttle:password-reset')->name('auth.forgot-password.send-otp');
Route::post('/auth/forgot-password/reset', [WebAuthController::class, 'resetForgotPassword'])->middleware('throttle:password-reset')->name('auth.forgot-password.reset');
Route::post('/auth/check-warga', [WebAuthController::class, 'checkWargaRole'])->middleware('throttle:check-warga')->name('auth.check-warga');
Route::post('/profile/first-login-setup', [WebAuthController::class, 'completeFirstLoginSetup'])->middleware(['auth', 'active'])->name('profile.first-login.setup');
Route::get('/download-apk', [WebAuthController::class, 'downloadApk'])->name('download.apk');

// 3. Panel Super Admin
Route::prefix('admin')->middleware(['auth', 'active', 'role:SUPERADMIN,ADMIN'])->group(function () {
    Route::get('/', [WebSuperAdminController::class, 'index'])->name('admin.dashboard');
    Route::match(['post', 'put'], '/bupati', [WebSuperAdminController::class, 'updateBupati'])->name('admin.bupati.update');
    Route::post('/users', [WebSuperAdminController::class, 'storeUser'])->name('admin.users.store');
    Route::match(['post', 'put'], '/users/{id}', [WebSuperAdminController::class, 'updateUser'])->name('admin.users.update');
    Route::post('/users/{id}/reset', [WebSuperAdminController::class, 'resetUserPassword'])->name('admin.users.reset');
    Route::post('/users/{id}/deactivate', [WebSuperAdminController::class, 'deactivateUser'])->name('admin.users.deactivate');
    Route::post('/users/{id}/reactivate', [WebSuperAdminController::class, 'reactivateUser'])->name('admin.users.reactivate');
    Route::post('/complaints/{id}/reject', [WebSuperAdminController::class, 'rejectComplaint'])->name('admin.complaints.reject');

    // Pengaturan Dispatch Mode & Manajemen Regu Tim Lapangan
    Route::post('/settings/dispatch-mode', [WebTeamController::class, 'updateDispatchMode'])->name('admin.settings.dispatch-mode');
    Route::post('/teams', [WebTeamController::class, 'store'])->name('admin.teams.store');
    Route::match(['post', 'put'], '/teams/{team}', [WebTeamController::class, 'update'])->name('admin.teams.update');
    Route::match(['post', 'delete'], '/teams/{team}/delete', [WebTeamController::class, 'destroy'])->name('admin.teams.destroy');
    Route::post('/teams/{team}/members', [WebTeamController::class, 'addMember'])->name('admin.teams.members.add');
    Route::match(['post', 'delete'], '/teams/{team}/members/{user}', [WebTeamController::class, 'removeMember'])->name('admin.teams.members.remove');
    Route::post('/teams/{team}/leader/{user}', [WebTeamController::class, 'setLeader'])->name('admin.teams.leader.set');

    // Manajemen Wilayah GIS
    Route::post('/regions/{id}/coordinates', [WebSuperAdminController::class, 'updateCoordinates'])->name('admin.regions.coordinates');
    Route::post('/regions', [WebSuperAdminController::class, 'storeRegion'])->name('admin.regions.store');
    Route::match(['post', 'delete'], '/regions/{id}/delete', [WebSuperAdminController::class, 'deleteRegion'])->name('admin.regions.delete');
});

// 4. Pusat Kendali Operator Dinas
Route::prefix('operator')->middleware(['auth', 'active', 'role:OPERATOR,SUPERADMIN,ADMIN'])->group(function () {
    Route::get('/', [WebOperatorController::class, 'index'])->name('operator.dashboard');
    Route::post('/complaints/{id}/verify-dispatch', [WebOperatorController::class, 'verifyAndDispatch'])->name('operator.complaints.dispatch');
    Route::post('/complaints/{id}/reject', [WebOperatorController::class, 'reject'])->name('operator.complaints.reject');
    Route::post('/complaints/{id}/validate', [WebOperatorController::class, 'validateCompletion'])->name('operator.complaints.validate');
    Route::post('/complaints/{id}/assign-team', [WebOperatorController::class, 'assignOrChangeTeam'])->name('operator.complaints.assign-team');
});

// 5. Dashboard Eksekutif Kepala Daerah (Bupati)
Route::prefix('bupati')->middleware(['auth', 'active', 'role:KEPALA_DAERAH,BUPATI,SUPERADMIN,ADMIN'])->group(function () {
    Route::get('/', [WebBupatiController::class, 'index'])->name('bupati.dashboard');
});

// 6. Portal Petugas Lapangan
Route::prefix('petugas')->middleware(['auth', 'active', 'role:PETUGAS,SUPERADMIN,ADMIN'])->group(function () {
    Route::get('/', [WebPetugasController::class, 'index'])->name('petugas.dashboard');
    Route::post('/complaints/{id}/start', [WebPetugasController::class, 'startWork'])->name('petugas.complaints.start');
    Route::post('/complaints/{id}/finish', [WebPetugasController::class, 'finishWork'])->name('petugas.complaints.finish');
    Route::post('/profile', [WebPetugasController::class, 'updateProfile'])->name('petugas.profile.update');
});

// 7. Fallback Permanen Media Foto Pengaduan dari PostgreSQL (Dilindungi Anti Path Traversal)
Route::get('/storage/{path}', function (string $path) {
    $cleanPath = ltrim(str_replace('\\', '/', $path), '/');
    if ($cleanPath === '' || str_contains($cleanPath, '..') || str_contains($cleanPath, "\0")) {
        abort(403, 'Akses path tidak diizinkan.');
    }

    $baseDir = realpath(storage_path('app/public'));
    $diskPath = $baseDir ? realpath($baseDir . DIRECTORY_SEPARATOR . $cleanPath) : false;

    if ($diskPath !== false && $baseDir !== false && str_starts_with($diskPath, $baseDir . DIRECTORY_SEPARATOR) && is_file($diskPath)) {
        return response()->file($diskPath, [
            'Access-Control-Allow-Origin' => '*',
            'Cache-Control' => 'public, max-age=86400',
        ]);
    }

    $photo = \App\Models\ComplaintPhoto::where('storage_key', $cleanPath)->first();
    if ($photo && !empty($photo->file_data)) {
        $binary = base64_decode($photo->file_data, true);
        if ($binary !== false) {
            return response($binary, 200)
                ->header('Content-Type', $photo->mime ?: 'image/jpeg')
                ->header('Cache-Control', 'public, max-age=86400');
        }
    }

    abort(404);
})->where('path', '.*');

// Ekspor Rekapitulasi CSV (Excel) & Cetak Berita Acara Penyelesaian (BAP)
Route::middleware(['auth', 'role:SUPERADMIN,ADMIN,OPERATOR,DINAS,BUPATI,KEPALA_DAERAH'])->group(function () {
    Route::get('/reports/export-csv', [\App\Http\Controllers\Web\WebReportController::class, 'exportCsv'])->name('reports.export-csv');
});
Route::middleware(['auth', 'role:SUPERADMIN,ADMIN,OPERATOR,DINAS,BUPATI,KEPALA_DAERAH,PETUGAS'])->group(function () {
    Route::get('/complaints/{id}/bap', [\App\Http\Controllers\Web\WebReportController::class, 'printBap'])->name('complaints.bap');
    Route::get('/petugas/performance/print', [\App\Http\Controllers\Web\WebReportController::class, 'printPetugasPerformance'])->name('petugas.performance.print');
});

// Dokumen Cetak Resmi via Signed URL (30 menit, khusus akses aman dari Aplikasi Mobile)
Route::middleware('signed')->group(function () {
    Route::get('/mobile-print/petugas-performance/{user}', [\App\Http\Controllers\Web\WebReportController::class, 'printPetugasPerformanceSigned'])
        ->name('mobile.petugas.performance.print');
    Route::get('/mobile-print/complaints/{id}/bap', [\App\Http\Controllers\Web\WebReportController::class, 'printBapSigned'])
        ->name('mobile.complaints.bap');
});
