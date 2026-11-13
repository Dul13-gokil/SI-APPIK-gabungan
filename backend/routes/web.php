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
| Web Routes - Sistem Informasi Pengaduan Infrastruktur Kolaka Utara
|--------------------------------------------------------------------------
*/

// 1. Halaman Beranda Publik (GIS Persebaran & Tracking Aduan)
Route::get('/', function () {
    $total = Complaint::count();
    $submitted = Complaint::whereIn('status', ['SUBMITTED', 'VERIFICATION'])->count();
    $dispatched = Complaint::where('status', 'DISPATCHED')->count();
    $inProgress = Complaint::where('status', 'IN_PROGRESS')->count();
    $waitingValidation = Complaint::where('status', 'WAITING_VERIFICATION')->count();
    $completed = Complaint::whereIn('status', ['COMPLETED', 'CLOSED'])->count();
    $rejected = Complaint::where('status', 'REJECTED')->count();

    $stats = [
        'total'              => $total,
        'submitted'          => $submitted,
        'dispatched'         => $dispatched,
        'in_progress'        => $inProgress,
        'waiting_validation' => $waitingValidation,
        'completed'          => $completed,
        'rejected'           => $rejected,
    ];

    $districts = Region::pluck('district')->filter()->unique()->sort()->values();

    $allComplaints = Complaint::with('category:id,name')->select('id', 'category_id', 'title')->get();
    $haystack = fn($c) => strtolower(($c->category?->name ?? '') . ' ' . ($c->title ?? ''));
    $categoryStats = [
        'jalan' => $allComplaints->filter(fn($c) => str_contains($haystack($c), 'jalan') || str_contains($haystack($c), 'aspal') || str_contains($haystack($c), 'jembatan') || str_contains($haystack($c), 'paving'))->count(),
        'drainase' => $allComplaints->filter(fn($c) => str_contains($haystack($c), 'drainase') || str_contains($haystack($c), 'saluran') || str_contains($haystack($c), 'got') || str_contains($haystack($c), 'banjir'))->count(),
        'pju' => $allComplaints->filter(fn($c) => str_contains($haystack($c), 'penerangan') || str_contains($haystack($c), 'pju') || str_contains($haystack($c), 'lampu'))->count(),
        'fasilitas' => $allComplaints->filter(fn($c) => str_contains($haystack($c), 'fasilitas') || str_contains($haystack($c), 'umum') || str_contains($haystack($c), 'taman') || str_contains($haystack($c), 'trotoar'))->count(),
    ];

    return view('landing', compact('stats', 'districts', 'categoryStats'));
})->name('home');

// 2. Autentikasi Web
Route::get('/login', [WebAuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [WebAuthController::class, 'login'])->middleware('throttle:5,1');
Route::match(['get', 'post'], '/logout', [WebAuthController::class, 'logout'])->name('logout');
Route::post('/forgot-password', [WebAuthController::class, 'sendForgotPasswordOtp'])->middleware('throttle:5,1')->name('password.update');
Route::post('/auth/forgot-password/send-otp', [WebAuthController::class, 'sendForgotPasswordOtp'])->middleware('throttle:5,1')->name('auth.forgot-password.send-otp');
Route::post('/auth/forgot-password/reset', [WebAuthController::class, 'resetForgotPassword'])->middleware('throttle:5,1')->name('auth.forgot-password.reset');
Route::post('/auth/check-warga', [WebAuthController::class, 'checkWargaRole'])->name('auth.check-warga');
Route::post('/profile/first-login-setup', [WebAuthController::class, 'completeFirstLoginSetup'])->middleware('auth')->name('profile.first-login.setup');
Route::get('/download-apk', [WebAuthController::class, 'downloadApk'])->name('download.apk');

// 3. Panel Super Admin
Route::prefix('admin')->middleware(['auth', 'role:SUPERADMIN,ADMIN'])->group(function () {
    Route::get('/', [WebSuperAdminController::class, 'index'])->name('admin.dashboard');
    Route::match(['post', 'put'], '/bupati', [WebSuperAdminController::class, 'updateBupati'])->name('admin.bupati.update');
    Route::post('/users', [WebSuperAdminController::class, 'storeUser'])->name('admin.users.store');
    Route::match(['post', 'put'], '/users/{id}', [WebSuperAdminController::class, 'updateUser'])->name('admin.users.update');
    Route::post('/users/{id}/reset', [WebSuperAdminController::class, 'resetUserPassword'])->name('admin.users.reset');
    Route::match(['post', 'delete'], '/users/{id}/delete', [WebSuperAdminController::class, 'deleteUser'])->name('admin.users.delete');
    Route::match(['post', 'delete'], '/complaints/{id}/delete', [WebSuperAdminController::class, 'deleteComplaint'])->name('admin.complaints.delete');

    // Pengaturan Dispatch Mode & Manajemen Regu Tim Lapangan
    Route::post('/settings/dispatch-mode', [WebTeamController::class, 'updateDispatchMode'])->name('admin.settings.dispatch-mode');
    Route::post('/teams', [WebTeamController::class, 'store'])->name('admin.teams.store');
    Route::post('/teams/{team}', [WebTeamController::class, 'update'])->name('admin.teams.update');
    Route::post('/teams/{team}/delete', [WebTeamController::class, 'destroy'])->name('admin.teams.destroy');
    Route::post('/teams/{team}/members', [WebTeamController::class, 'addMember'])->name('admin.teams.members.add');
    Route::post('/teams/{team}/members/{user}', [WebTeamController::class, 'removeMember'])->name('admin.teams.members.remove');
    Route::post('/teams/{team}/leader/{user}', [WebTeamController::class, 'setLeader'])->name('admin.teams.leader.set');

    // Manajemen Wilayah GIS
    Route::post('/regions/{id}/coordinates', [WebSuperAdminController::class, 'updateCoordinates'])->name('admin.regions.coordinates');
    Route::post('/regions', [WebSuperAdminController::class, 'storeRegion'])->name('admin.regions.store');
    Route::post('/regions/{id}/delete', [WebSuperAdminController::class, 'deleteRegion'])->name('admin.regions.delete');
});

// 4. Pusat Kendali Operator Dinas
Route::prefix('operator')->middleware(['auth', 'role:OPERATOR,SUPERADMIN,ADMIN'])->group(function () {
    Route::get('/', [WebOperatorController::class, 'index'])->name('operator.dashboard');
    Route::post('/complaints/{id}/verify-dispatch', [WebOperatorController::class, 'verifyAndDispatch'])->name('operator.complaints.dispatch');
    Route::post('/complaints/{id}/reject', [WebOperatorController::class, 'reject'])->name('operator.complaints.reject');
    Route::post('/complaints/{id}/validate', [WebOperatorController::class, 'validateCompletion'])->name('operator.complaints.validate');
    Route::post('/complaints/{id}/assign-team', [WebOperatorController::class, 'assignOrChangeTeam'])->name('operator.complaints.assign-team');
});

// 5. Dashboard Eksekutif Kepala Daerah (Bupati)
Route::prefix('bupati')->middleware(['auth', 'role:KEPALA_DAERAH,BUPATI,SUPERADMIN,ADMIN'])->group(function () {
    Route::get('/', [WebBupatiController::class, 'index'])->name('bupati.dashboard');
});

// 6. Portal Petugas Lapangan
Route::prefix('petugas')->middleware(['auth', 'role:PETUGAS,SUPERADMIN,ADMIN'])->group(function () {
    Route::get('/', [WebPetugasController::class, 'index'])->name('petugas.dashboard');
    Route::post('/complaints/{id}/start', [WebPetugasController::class, 'startWork'])->name('petugas.complaints.start');
    Route::post('/complaints/{id}/finish', [WebPetugasController::class, 'finishWork'])->name('petugas.complaints.finish');
    Route::post('/profile', [WebPetugasController::class, 'updateProfile'])->name('petugas.profile.update');
});
