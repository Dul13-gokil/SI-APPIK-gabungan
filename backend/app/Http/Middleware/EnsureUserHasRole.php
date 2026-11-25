<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserHasRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  ...$roles
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user() ?? \App\Http\Controllers\Api\v1\AuthController::resolveUser($request);

        if (! $user) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'Sesi autentikasi tidak valid atau telah kedaluwarsa. Silakan masuk kembali.',
                    'data'    => null,
                ], 401);
            }

            return redirect()->guest(route('login'));
        }

        $userRole = strtoupper((string) $user->role);
        
        $allowedRoles = [];
        foreach ($roles as $roleParam) {
            foreach (explode(',', $roleParam) as $singleRole) {
                $trimmed = strtoupper(trim($singleRole));
                if ($trimmed !== '') {
                    $allowedRoles[] = $trimmed;
                }
            }
        }

        // Super Admin adalah peran tertinggi: boleh masuk semua portal.
        // Peran lain (termasuk Kepala Daerah & Kepala Dinas) harus disebut eksplisit
        // pada rute, supaya pembatasan seperti log aktivitas & pengaturan sistem berlaku.
        if ($userRole === 'SUPERADMIN') {
            return $next($request);
        }

        if (! in_array($userRole, $allowedRoles, true)) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'Akses ditolak. Anda tidak memiliki hak akses untuk tindakan ini.',
                    'data'    => null,
                ], 403);
            }

            abort(403, 'Anda tidak memiliki hak akses ke halaman ini.');
        }

        return $next($request);
    }
}
