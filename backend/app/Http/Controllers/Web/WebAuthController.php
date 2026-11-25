<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class WebAuthController extends Controller
{
    public function showLoginForm(): View|RedirectResponse
    {
        if (Auth::check()) {
            return $this->redirectBasedOnRole(Auth::user());
        }

        return view('auth.login');
    }

    public function login(Request $request): RedirectResponse
    {
        $request->validate([
            'email'    => 'required|string',
            'password' => 'required',
        ]);

        $loginInput = trim($request->input('email'));

        // Mendukung login aman berbasis Email atau Nomor Handphone (nama dikecualikan agar tidak terjadi tabrakan nama)
        $user = User::where(function ($q) use ($loginInput) {
            $q->where('email', $loginInput)
              ->orWhere('phone', $loginInput);
        })->first();

        if ($user && Hash::check($request->password, $user->password)) {
            if (!($user->is_active ?? true)) {
                $reasonText = $user->deactivation_reason
                    ? " Alasan: {$user->deactivation_reason}"
                    : '';
                return back()->withErrors([
                    'email' => "Akun Anda telah dinonaktifkan oleh Super Admin.{$reasonText} Silakan hubungi administrator untuk pengaktifan kembali.",
                ])->withInput($request->only('email'));
            }

            // Pengecekan Khusus Role WARGA:
            // Role warga di website sama sekali tidak bisa digunakan. 
            // Tampilkan pop-up pemberitahuan resmi untuk unduh aplikasi mobile (PlayStore & AppStore).
            if ($user->isWarga()) {
                $citizenName = $user->name;
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return redirect()->route('login')
                    ->with('warga_blocked', true)
                    ->with('citizen_name', $citizenName);
            }

            Auth::login($user, $request->boolean('remember'));
            $request->session()->regenerate();

            $currentIp        = $request->ip();
            $currentUserAgent = $request->userAgent();

            $user->update([
                'last_login_ip'         => $currentIp,
                'last_login_user_agent' => $currentUserAgent,
                'last_login_at'         => now(),
            ]);

            return $this->redirectBasedOnRole($user);
        }

        return back()->withErrors([
            'email' => 'Kredensial yang diberikan tidak cocok dengan data kami.',
        ])->withInput($request->only('email'));
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login');
    }

    /**
     * Kirim OTP Reset Kata Sandi ke email terdaftar (Website)
     */
    public function sendForgotPasswordOtp(Request $request): \Illuminate\Http\JsonResponse
    {
        $request->validate([
            'email' => 'required|email',
        ], [
            'email.required' => 'Alamat email terdaftar wajib diisi.',
            'email.email'    => 'Format alamat email tidak valid.',
        ]);

        $email = strtolower(trim($request->input('email')));

        $user = User::where('email', $email)->first();

        if (!$user) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Akun dengan alamat email tersebut belum terdaftar dalam sistem.',
            ], 404);
        }

        // Khusus Akun WARGA: Portal website khusus aparatur internal (Operator, Pengawas Lapangan, Kepala Daerah).
        // Warga masyarakat wajib diarahkan menggunakan Aplikasi Mobile SI-APPIK.
        if ($user->isWarga()) {
            return response()->json([
                'status'   => 'warga_blocked',
                'is_warga' => true,
                'name'     => $user->name,
                'message'  => 'Akun Anda terdaftar sebagai Warga Masyarakat. Portal website ini khusus diperuntukkan bagi Operator, Pengawas Lapangan, dan Kepala Daerah. Silakan gunakan Aplikasi Mobile SI-APPIK untuk mengatur ulang kata sandi Anda.',
            ], 403);
        }

        if (empty($user->email)) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Akun ini tidak memiliki alamat email aktif untuk pengiriman kode OTP. Hubungi Administrator.',
            ], 422);
        }

        $otpService = app(\App\Services\OtpService::class);
        $result = $otpService->generateOtp($user->email, 'FORGOT_PASSWORD');

        return response()->json([
            'status'     => 'success',
            'email'      => $user->email,
            'message'    => 'Kode OTP berhasil dikirimkan ke email resmi Anda (' . substr($user->email, 0, 3) . '***@' . explode('@', $user->email)[1] . '). Silakan cek kotak masuk atau folder spam.',
            'expires_in' => $result['expires_in'] ?? 10,
        ]);
    }

    /**
     * Verifikasi OTP dan atur ulang kata sandi (Website)
     */
    public function resetForgotPassword(Request $request): \Illuminate\Http\JsonResponse
    {
        $validated = $request->validate([
            'email'                 => 'required|email',
            'otp'                   => 'required|string|size:6',
            'password'              => ['required', 'string', 'min:8', 'regex:/[A-Z]/', 'regex:/[a-z]/', 'regex:/[0-9]/', 'confirmed'],
            'password_confirmation' => 'required|string',
        ], [
            'email.required'        => 'Alamat email wajib disertakan.',
            'email.email'           => 'Format email tidak valid.',
            'otp.required'          => 'Kode OTP 6 digit wajib diisi.',
            'otp.size'              => 'Kode OTP harus tepat 6 digit angka.',
            'password.required'     => 'Kata sandi baru wajib dibuat.',
            'password.min'          => 'Kata sandi baru minimal 8 karakter.',
            'password.regex'        => 'Kata sandi baru wajib kombinasi huruf besar, huruf kecil, dan angka.',
            'password.confirmed'    => 'Konfirmasi kata sandi baru tidak sesuai.',
        ]);

        $user = User::where('email', $validated['email'])->first();
        if (!$user) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Pengguna dengan email ini tidak ditemukan.',
            ], 404);
        }

        if ($user->isWarga()) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Akun Warga Masyarakat hanya dapat mengatur ulang kata sandi melalui Aplikasi Mobile SI-APPIK.',
            ], 403);
        }

        $otpService = app(\App\Services\OtpService::class);
        $otpCheck = $otpService->verifyOtpWithResult($validated['email'], $validated['otp'], 'FORGOT_PASSWORD');
        if (!$otpCheck['valid']) {
            return response()->json([
                'status'  => 'error',
                'message' => $otpCheck['message'],
            ], 422);
        }

        $user->password = Hash::make($validated['password']);
        $user->save();

        try {
            app(\App\Services\SecurityMailService::class)->sendPasswordChangedAlert(
                $user, 
                $request->ip(), 
                $request->userAgent(),
                $request->input('latitude') ? (float)$request->input('latitude') : null,
                $request->input('longitude') ? (float)$request->input('longitude') : null
            );
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning("Gagal mengirim email ganti sandi: " . $e->getMessage());
        }

        return response()->json([
            'status'  => 'success',
            'message' => 'Kata sandi berhasil diatur ulang! Silakan masuk menggunakan kata sandi baru Anda.',
        ]);
    }

    /**
     * Alur wajib login pertama kali: Verifikasi Nama, No. WhatsApp, dan Sandi Baru (Operator & Pengawas Lapangan)
     */
    public function completeFirstLoginSetup(Request $request): RedirectResponse
    {
        $user = Auth::user();
        if (!$user) {
            return redirect()->route('login');
        }

        $validated = $request->validate([
            'name'     => 'required|string|max:255',
            'phone'    => ['required', 'string', 'regex:/^(?:\+62|62|0)8[1-9][0-9]{7,10}$/', 'unique:users,phone,' . $user->id],
            'password' => ['required', 'string', 'min:8', 'regex:/[A-Z]/', 'regex:/[a-z]/', 'regex:/[0-9]/', 'confirmed'],
        ], [
            'name.required'      => 'Nama lengkap wajib diperiksa dan diisi.',
            'phone.required'     => 'Nomor handphone / WhatsApp wajib diisi.',
            'phone.regex'        => 'Format nomor WhatsApp tidak valid. Format Indonesia harus 10-13 digit (contoh: 081234567890).',
            'phone.unique'       => 'Nomor telepon tersebut telah terdaftar pada akun lain.',
            'password.required'  => 'Kata sandi baru wajib dibuat.',
            'password.min'       => 'Kata sandi baru minimal 8 karakter.',
            'password.regex'     => 'Kata sandi baru wajib mengandung kombinasi huruf besar, huruf kecil, dan angka.',
            'password.confirmed' => 'Konfirmasi kata sandi baru tidak cocok.',
        ]);

        if (strtolower(trim($validated['password'])) === 'password') {
            return back()->withErrors(['password' => 'Kata sandi baru tidak boleh menggunakan kata sandi default "password".'])->withInput();
        }

        $user->name = $validated['name'];
        $user->phone = $validated['phone'];
        $user->password = Hash::make($validated['password']);
        $user->save();

        // Kirim email konfirmasi perubahan sandi dengan deteksi lokasi dinamis
        try {
            app(\App\Services\SecurityMailService::class)->sendPasswordChangedAlert(
                $user, 
                $request->ip(), 
                $request->userAgent(),
                $request->input('latitude') ? (float)$request->input('latitude') : null,
                $request->input('longitude') ? (float)$request->input('longitude') : null
            );
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning("Gagal mengirim email ganti sandi first-login: " . $e->getMessage());
        }

        return back()->with('success', 'Aktivasi akun berhasil! Identitas dan kata sandi baru Anda telah aktif. Selamat bertugas di SI-APPIK!');
    }

    protected function redirectBasedOnRole($user): RedirectResponse
    {
        if ($user->isWarga()) {
            Auth::logout();
            return redirect()->route('login')->with('warga_blocked', true)->with('citizen_name', $user->name);
        }

        if ($user->isSuperAdmin()) {
            return redirect()->intended('/admin');
        }

        if ($user->isOperator()) {
            return redirect()->intended('/operator');
        }

        if ($user->isBupati()) {
            return redirect()->intended('/bupati');
        }

        if ($user->isPetugas()) {
            return redirect()->intended('/petugas');
        }

        if ($user->isPengawas()) {
            return redirect()->intended('/petugas');
        }

        if ($user->isMonitoring()) {
            return redirect()->intended('/monitoring');
        }

        if ($user->isKepalaDinas()) {
            return redirect()->intended('/operator');
        }

        return redirect()->route('home');
    }

    /**
     * Pengecekan cepat AJAX apakah email/phone yang dimasukkan adalah akun role WARGA
     */
    public function checkWargaRole(Request $request): \Illuminate\Http\JsonResponse
    {
        $input = trim($request->input('email', ''));
        $user = \App\Models\User::where(function ($q) use ($input) {
            $q->where('email', $input)
              ->orWhere('phone', $input);
        })->first();

        if ($user && $user->isWarga()) {
            return response()->json([
                'is_warga' => true,
                'name'     => $user->name,
                'email'    => $user->email,
                'message'  => 'Akun ini terdaftar sebagai Warga Masyarakat. Portal website ini khusus diperuntukkan bagi Operator, Pengawas Lapangan, dan Kepala Daerah. Silakan gunakan Aplikasi Mobile SI-APPIK pada smartphone Anda.',
            ]);
        }

        return response()->json([
            'is_warga' => false,
        ]);
    }

    /**
     * Endpoint download berkas APK Android resmi
     */
    public function downloadApk(): \Symfony\Component\HttpFoundation\BinaryFileResponse|RedirectResponse
    {
        $apkPath = public_path('downloads/SI-APPIK.APK');
        $candidates = [
            base_path('../mobile/build/app/outputs/flutter-apk/app-release.apk'),
            base_path('../mobile/build/app/outputs/flutter-apk/app-arm64-v8a-release.apk'),
            base_path('../mobile/build/app/outputs/flutter-apk/app-debug.apk'),
            base_path('../mobile/build/app/outputs/apk/debug/app-debug.apk'),
        ];

        $newestSource = null;
        $newestMtime = 0;
        foreach ($candidates as $candidate) {
            if (file_exists($candidate)) {
                $mtime = filemtime($candidate) ?: 0;
                if ($mtime > $newestMtime) {
                    $newestMtime = $mtime;
                    $newestSource = $candidate;
                }
            }
        }

        if ($newestSource && (!file_exists($apkPath) || $newestMtime > filemtime($apkPath))) {
            if (!is_dir(public_path('downloads'))) {
                mkdir(public_path('downloads'), 0777, true);
            }
            copy($newestSource, $apkPath);
        }

        if (file_exists($apkPath)) {
            return response()->download($apkPath, 'SI-APPIK.APK', [
                'Content-Type' => 'application/vnd.android.package-archive',
                'Cache-Control' => 'no-cache, no-store, must-revalidate',
            ]);
        }

        return redirect()->route('login')->withErrors(['apk' => 'Berkas APK sedang disiapkan.']);
    }
}
