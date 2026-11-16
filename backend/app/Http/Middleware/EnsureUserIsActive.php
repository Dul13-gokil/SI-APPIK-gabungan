<?php

namespace App\Http\Middleware;

use App\Http\Controllers\Api\v1\AuthController;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsActive
{
    /**
     * Pastikan akun pengguna masih berstatus aktif.
     * Jika sudah dinonaktifkan oleh Super Admin, cabut sesi/token dan tolak akses.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user() ?? AuthController::resolveUser($request);

        if ($user && !($user->is_active ?? true)) {
            $reasonText = $user->deactivation_reason
                ? " Alasan: {$user->deactivation_reason}"
                : '';
            $message = "Akun Anda telah dinonaktifkan oleh Super Admin.{$reasonText} Silakan hubungi administrator untuk pengaktifan kembali.";

            if ($request->expectsJson() || $request->is('api/*')) {
                try {
                    if (method_exists($user, 'currentAccessToken')) {
                        $user->currentAccessToken()?->delete();
                    }
                    $user->tokens()->delete();
                } catch (\Throwable $e) {
                    // Abaikan jika token sudah dicabut
                }

                return response()->json([
                    'status'  => 'error',
                    'message' => $message,
                    'data'    => [
                        'account_deactivated' => true,
                        'deactivation_reason' => $user->deactivation_reason,
                    ],
                ], 403);
            }

            Auth::logout();
            if ($request->hasSession()) {
                $request->session()->invalidate();
                $request->session()->regenerateToken();
            }

            return redirect()->route('login')->withErrors([
                'email' => $message,
            ]);
        }

        return $next($request);
    }
}
