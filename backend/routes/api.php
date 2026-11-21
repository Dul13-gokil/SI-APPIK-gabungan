<?php

use App\Http\Controllers\Api\v1\AIServiceController;
use App\Http\Controllers\Api\v1\AuthController;
use App\Http\Controllers\Api\v1\ChatbotController;
use App\Http\Controllers\Api\v1\ComplaintController;
use App\Http\Controllers\Api\v1\DashboardController;
use App\Http\Controllers\Api\v1\NotificationController;
use App\Http\Controllers\Api\v1\WorkflowController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| REST API v1 Routes (Zero Trust Architecture)
|--------------------------------------------------------------------------
*/

Route::prefix('v1')->group(function () {

    // ==========================================
    // 1. PUBLIC ROUTES (Rate Limited & Open GIS)
    // ==========================================
    Route::middleware('throttle:api-auth')->group(function () {
        Route::post('/auth/register/send-otp',        [AuthController::class, 'sendRegisterOtp']);
        Route::post('/auth/register',                 [AuthController::class, 'register']);
        Route::post('/auth/login',                    [AuthController::class, 'login']);
        Route::post('/auth/google',                   [AuthController::class, 'googleLogin']);
        Route::post('/auth/forgot-password/send-otp', [AuthController::class, 'sendForgotPasswordOtp']);
        Route::post('/auth/forgot-password',          [AuthController::class, 'forgotPassword']);
        Route::get('/auth/check-phone',               [AuthController::class, 'checkPhone']);
    });

    // Public feeds & GIS analytics (Data telepon disensor di ComplaintResource demi privasi UU PDP)
    Route::get('/complaints',                           [ComplaintController::class, 'index']);
    Route::get('/complaints/nearby-duplicates',         [ComplaintController::class, 'nearbyDuplicates']);
    Route::post('/complaints',                          [ComplaintController::class, 'store'])->middleware('throttle:complaint-create'); // Mendukung Warga Login & Anonim (Rate Limited)
    Route::get('/complaints/{id}',                      [ComplaintController::class, 'show']);
    Route::get('/complaints/{id}/timeline',             [ComplaintController::class, 'timeline']);
    Route::get('/complaints/{id}/duplicate-candidates', [ComplaintController::class, 'duplicateCandidates']);
    Route::get('/reverse-geocode',                      [ComplaintController::class, 'reverseGeocode'])->middleware('throttle:reverse-geocode');

    // Dashboard Executive & GIS Maps
    Route::get('/dashboard/summary',     [DashboardController::class, 'summary']);
    Route::get('/dashboard/map',         [DashboardController::class, 'map']);
    Route::get('/dashboard/performance', [DashboardController::class, 'performance']);

    // AI Chatbot Assistant (Public Access, Rate Limited 30 req/min)
    Route::middleware('throttle:api-chat')->group(function () {
        Route::post('/chat/sessions',               [ChatbotController::class, 'createSession']);
        Route::post('/chat/sessions/{id}/messages', [ChatbotController::class, 'sendMessage']);
    });

    // AI Computer Vision (Rate limited to 15 req/min to protect server GPU/CPU)
    Route::post('/ai/classify-image', [AIServiceController::class, 'classifyImage'])->middleware('throttle:api-ai');


    // ==========================================
    // 2. PROTECTED ROUTES (Requires auth:sanctum)
    // ==========================================
    Route::middleware(['auth:sanctum', 'active'])->group(function () {

        // A. Autentikasi, Profil & Keamanan Akun
        Route::post('/auth/logout',          [AuthController::class, 'logout']);
        Route::get('/me',                    [AuthController::class, 'me']);
        Route::put('/profile',               [AuthController::class, 'updateProfile']);
        Route::post('/profile',              [AuthController::class, 'updateProfile']);
        Route::post('/auth/change-password', [AuthController::class, 'changePassword']);

        // B. Laporan Khusus Warga Login (BOLA/IDOR Protected)
        Route::post('/complaints/{id}/cancel',       [ComplaintController::class, 'cancel']);
    Route::post('/complaints/{id}/rating',       [ComplaintController::class, 'storeRating']);
    Route::delete('/complaints/{id}',            [ComplaintController::class, 'destroy']);
        Route::post('/complaints/{id}/supports',   [ComplaintController::class, 'toggleSupport']);
        Route::delete('/complaints/{id}/supports', [ComplaintController::class, 'toggleSupport']);
        Route::get('/my-complaints',               [ComplaintController::class, 'myComplaints']);

        // C. Notifikasi Pengguna (Strictly scoped to auth user_id)
        Route::get('/notifications',            [NotificationController::class, 'index']);
        Route::post('/notifications/{id}/read', [NotificationController::class, 'markRead']);
        Route::post('/notifications/read-all',  [NotificationController::class, 'markAllRead']);
        Route::delete('/notifications/{id}',    [NotificationController::class, 'destroy']);
        Route::delete('/notifications',         [NotificationController::class, 'clearAll']);
        Route::post('/notifications/clear-all', [NotificationController::class, 'clearAll']);

        // D. Tugas & Laporan Kinerja Khusus Petugas Lapangan
        Route::get('/field-tasks', [ComplaintController::class, 'fieldTasks'])->middleware('role:PETUGAS,SUPERADMIN,ADMIN');
        Route::get('/field/performance', [\App\Http\Controllers\Api\v1\FieldPerformanceController::class, 'show'])->middleware('role:PETUGAS,SUPERADMIN,ADMIN');

        // E. Alur Kerja Lapangan (Khusus Petugas Teknis Lapangan)
        Route::middleware('role:PETUGAS,SUPERADMIN,ADMIN')->group(function () {
            Route::post('/complaints/{id}/start-work',  [WorkflowController::class, 'startWork']);
            Route::post('/complaints/{id}/finish-work', [WorkflowController::class, 'finishWork']);
            Route::post('/complaints/{id}/resolution',  [WorkflowController::class, 'submitResolution']);
        });

        // F. Alur Kerja Administratif (Khusus Operator Verifikasi & Super Admin)
        Route::middleware('role:OPERATOR,SUPERADMIN,ADMIN')->group(function () {
            Route::post('/complaints/{id}/verify',              [WorkflowController::class, 'verify']);
            Route::post('/complaints/{id}/dispositions',        [WorkflowController::class, 'disposition']);
            Route::post('/complaints/{id}/validate-resolution', [WorkflowController::class, 'validateResolution']);
            Route::post('/complaints/{id}/merge',               [WorkflowController::class, 'merge']);
        });

        // G. Mutasi Status State Machine Terkendali
        Route::post('/complaints/{id}/status', [WorkflowController::class, 'updateStatus'])
            ->middleware('role:OPERATOR,PETUGAS,SUPERADMIN,ADMIN');
    });
});
