<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Http\Resources\ApiResponse;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    protected function getAuthenticatedUserId(Request $request): ?int
    {
        return AuthController::resolveUser($request)?->id;
    }

    /**
     * Ambil daftar notifikasi milik user yang sedang login.
     * Diurutkan terbaru dulu, paginasi 30 per halaman.
     */
    public function index(Request $request): JsonResponse
    {
        $userId = $this->getAuthenticatedUserId($request);

        if (!$userId) {
            return ApiResponse::success([], 'Tidak ada notifikasi');
        }

        $notifications = Notification::where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->limit(50)
            ->get()
            ->map(fn($n) => [
                'id'         => $n->id,
                'type'       => $n->type,
                'title'      => $n->title,
                'body'       => $n->body,
                'data'       => $n->data_json,
                'is_read'    => $n->read_at !== null,
                'created_at' => $n->created_at->toIso8601String(),
            ]);

        $unreadCount = Notification::where('user_id', $userId)
            ->whereNull('read_at')
            ->count();

        return ApiResponse::success([
            'notifications' => $notifications,
            'unread_count'  => $unreadCount,
        ]);
    }

    /**
     * Tandai satu notifikasi sudah dibaca.
     */
    public function markRead(Request $request, int $id): JsonResponse
    {
        $userId = $this->getAuthenticatedUserId($request);
        $notification = Notification::where('id', $id)
            ->where('user_id', $userId)
            ->firstOrFail();

        $notification->markAsRead();

        return ApiResponse::success(null, 'Notifikasi ditandai sudah dibaca');
    }

    /**
     * Tandai semua notifikasi sudah dibaca.
     */
    public function markAllRead(Request $request): JsonResponse
    {
        $userId = $this->getAuthenticatedUserId($request);

        if ($userId) {
            Notification::where('user_id', $userId)
                ->whereNull('read_at')
                ->update(['read_at' => now()]);
        }

        return ApiResponse::success(null, 'Semua notifikasi sudah dibaca');
    }

    /**
     * Hapus satu notifikasi milik user.
     */
    public function destroy(Request $request, int $id): JsonResponse
    {
        $userId = $this->getAuthenticatedUserId($request);
        $notification = Notification::where('id', $id)
            ->where('user_id', $userId)
            ->first();

        if (!$notification) {
            return ApiResponse::error('Notifikasi tidak ditemukan', 404);
        }

        $notification->delete();

        return ApiResponse::success(null, 'Notifikasi berhasil dihapus');
    }

    /**
     * Hapus semua notifikasi milik user.
     */
    public function clearAll(Request $request): JsonResponse
    {
        $userId = $this->getAuthenticatedUserId($request);

        if ($userId) {
            Notification::where('user_id', $userId)->delete();
        }

        return ApiResponse::success(null, 'Semua notifikasi berhasil dihapus');
    }
}
