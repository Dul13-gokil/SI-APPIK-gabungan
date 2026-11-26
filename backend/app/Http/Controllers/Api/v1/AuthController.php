<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Http\Resources\ApiResponse;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public static function resolveUser(Request $request): ?User
    {
        if ($user = $request->user()) {
            return $user;
        }

        if ($bearer = $request->bearerToken()) {
            if (class_exists(\Laravel\Sanctum\PersonalAccessToken::class)) {
                $pat = \Laravel\Sanctum\PersonalAccessToken::findToken($bearer);
                if ($pat && $pat->tokenable instanceof User) {
                    return $pat->tokenable;
                }
            }
        }

        return Auth::user();
    }

    public function __construct(
        protected \App\Services\OtpService $otpService,
        protected \App\Services\SecurityMailService $securityMailService
    ) {}

    public function sendRegisterOtp(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|email|max:255|unique:users,email',
        ], [
            'email.required' => 'Alamat email wajib diisi.',
            'email.email'    => 'Format email tidak valid.',
            'email.unique'   => 'Alamat email ini sudah terdaftar. Silakan masuk atau gunakan email lain.',
        ]);

        $result = $this->otpService->generateOtp($request->email, 'REGISTER');

        return ApiResponse::success([
            'email'      => $result['email'],
            'expires_in' => $result['expires_in'],
            'dev_otp'    => $result['dev_otp'],
        ], 'Kode OTP verifikasi pendaftaran telah dikirim ke email Anda.');
    }

    public function sendForgotPasswordOtp(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|email',
        ], [
            'email.required' => 'Alamat email wajib diisi.',
            'email.email'    => 'Format email tidak valid.',
        ]);

        $user = User::where('email', $request->email)->first();
        if (!$user) {
            return ApiResponse::error(
                'Alamat email belum terdaftar dalam sistem. Pastikan email Anda sudah terdaftar.',
                [],
                404
            );
        }

        $role = strtoupper($user->role);

        // Hanya WARGA dan PETUGAS yang diizinkan menerima OTP reset sandi via mobile
        if (!in_array($role, ['WARGA', 'PETUGAS'], true)) {
            return ApiResponse::error(
                'Pengiriman OTP hanya diizinkan untuk akun Warga dan Pengawas Lapangan. Akun pimpinan atau dinas tidak diizinkan melalui aplikasi mobile.',
                [],
                403
            );
        }

        $result = $this->otpService->generateOtp($request->email, 'FORGOT_PASSWORD');

        return ApiResponse::success([
            'email'      => $result['email'],
            'role'       => $role,
            'expires_in' => $result['expires_in'],
            'dev_otp'    => $result['dev_otp'],
        ], 'Kode OTP untuk mengatur ulang kata sandi telah dikirimkan ke email Anda.');
    }

    public function checkPhone(Request $request): JsonResponse
    {
        $raw = trim($request->input('phone', ''));
        if (empty($raw)) {
            return ApiResponse::error('Nomor telepon wajib diisi.', [], 422);
        }

        // Normalisasi format Indonesia (+62 atau 62 menjadi 0)
        $normalized = $raw;
        if (str_starts_with($normalized, '+62')) {
            $normalized = '0' . substr($normalized, 3);
        } elseif (str_starts_with($normalized, '62')) {
            $normalized = '0' . substr($normalized, 2);
        }

        // Validasi 10-13 digit diawali 08
        $isValidFormat = (bool) preg_match('/^08[1-9][0-9]{7,10}$/', $normalized);
        if (!$isValidFormat) {
            return ApiResponse::success([
                'valid_format' => false,
                'registered'   => false,
                'normalized'   => $normalized,
                'digit_count'  => strlen($normalized),
                'message'      => 'Format nomor tidak valid. Nomor Indonesia harus 10 hingga 13 digit dan diawali dengan 08.',
            ]);
        }

        // Cek apakah sudah terdaftar di database
        $userId = $request->input('user_id') ?? self::resolveUser($request)?->id;

        $exists = User::where(function ($q) use ($raw, $normalized) {
            $q->where('phone', $normalized)
              ->orWhere('phone', $raw)
              ->orWhere('phone', '+62' . substr($normalized, 1))
              ->orWhere('phone', '62' . substr($normalized, 1));
        })
        ->when($userId, fn($q) => $q->where('id', '!=', $userId))
        ->exists();

        if ($exists) {
            return ApiResponse::success([
                'valid_format' => true,
                'registered'   => true,
                'normalized'   => $normalized,
                'digit_count'  => strlen($normalized),
                'message'      => 'Nomor telepon ini sudah terdaftar oleh pengguna lain.',
            ]);
        }

        return ApiResponse::success([
            'valid_format' => true,
            'registered'   => false,
            'normalized'   => $normalized,
            'digit_count'  => strlen($normalized),
            'message'      => 'Nomor valid dan dapat digunakan (' . strlen($normalized) . ' digit).',
        ]);
    }

    public function register(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name'         => 'required|string|max:255',
            'email'        => 'required|string|email|max:255|unique:users',
            'phone'        => ['required', 'string', 'regex:/^(?:\+62|62|0)8[1-9][0-9]{7,10}$/', 'unique:users,phone'],
            'password'     => ['required', 'string', 'min:8', 'regex:/[a-z]/', 'regex:/[A-Z]/', 'regex:/[0-9]/', 'confirmed'],
            'otp'          => 'required|string|size:6',
            'region_id'    => 'nullable|exists:regions,id',
            'is_anonymous' => 'boolean',
        ], [
            'name.required'      => 'Nama lengkap wajib diisi.',
            'email.required'     => 'Email wajib diisi.',
            'phone.required'     => 'Nomor HP / WhatsApp wajib diisi (10 - 13 digit).',
            'otp.required'       => 'Kode OTP verifikasi email wajib diisi.',
            'otp.size'           => 'Kode OTP harus 6 digit angka.',
            'password.required'   => 'Kata sandi wajib diisi.',
            'password.confirmed' => 'Konfirmasi kata sandi tidak cocok.',
            'password.min'       => 'Kata sandi minimal 8 karakter.',
            'password.regex'     => 'Kata sandi harus mengandung kombinasi huruf besar, huruf kecil, dan angka.',
            'phone.regex'        => 'Format nomor telepon tidak valid. Nomor Indonesia harus 10 hingga 13 digit dan diawali dengan 08 atau +628.',
            'phone.unique'       => 'Nomor telepon ini sudah terdaftar dalam sistem oleh pengguna lain.',
        ]);

        // Verifikasi kode OTP dengan hasil detail dan pembatasan 3 kali percobaan salah
        $otpCheck = $this->otpService->verifyOtpWithResult($validated['email'], $validated['otp'], 'REGISTER');
        if (!$otpCheck['valid']) {
            return ApiResponse::error($otpCheck['message'], [], 422);
        }

        $currentIp   = $request->ip();
        $currentUserAgent = $request->userAgent();

        $user = User::create([
            'name'                  => $validated['name'],
            'email'                 => $validated['email'],
            'phone'                 => $validated['phone'],
            'password'              => Hash::make($validated['password']),
            'role'                  => 'WARGA',
            'region_id'             => $validated['region_id'] ?? null,
            'is_anonymous'          => $validated['is_anonymous'] ?? false,
            'last_login_ip'         => $currentIp,
            'last_login_user_agent' => $currentUserAgent,
            'last_login_at'         => now(),
        ]);

        $token = $user->createToken('auth-token')->plainTextToken;

        return ApiResponse::success([
            'user'  => $user,
            'token' => $token,
        ], 'Registrasi berhasil', 201);
    }

    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'email'    => 'required|string',
            'password' => 'required|string',
        ]);

        $loginInput = trim($request->input('email'));

        // Email/HP dicocokkan tanpa membedakan huruf besar-kecil (PostgreSQL peka huruf).
        $user = User::findByLogin($loginInput);

        if (!$user || !Hash::check($request->password, $user->password)) {
            return ApiResponse::error('Kredensial tidak cocok dengan data kami.', [], 401);
        }

        if (!($user->is_active ?? true)) {
            $reasonText = $user->deactivation_reason
                ? " Alasan: {$user->deactivation_reason}"
                : '';
            return ApiResponse::error(
                "Akun Anda telah dinonaktifkan oleh Super Admin.{$reasonText} Silakan hubungi administrator untuk pengaktifan kembali.",
                ['account_deactivated' => true, 'deactivation_reason' => $user->deactivation_reason],
                403
            );
        }

        $currentIp        = $request->ip();
        $currentUserAgent = $request->userAgent();

        $user->update([
            'last_login_ip'         => $currentIp,
            'last_login_user_agent' => $currentUserAgent,
            'last_login_at'         => now(),
        ]);

        $token = $user->createToken('auth-token')->plainTextToken;

        return ApiResponse::success([
            'user'  => $user,
            'token' => $token,
        ], 'Login berhasil');
    }

    public function googleLogin(Request $request): JsonResponse
    {
        $request->validate([
            'id_token' => 'required|string',
        ]);

        $idToken = $request->input('id_token');

        try {
            // Verifikasi integritas token langsung ke Google OAuth2 API
            $response = \Illuminate\Support\Facades\Http::timeout(10)->get('https://oauth2.googleapis.com/tokeninfo', [
                'id_token' => $idToken,
            ]);

            if ($response->failed() || empty($response->json('email'))) {
                return ApiResponse::error('Token verifikasi Google tidak valid atau telah kedaluwarsa.', [], 401);
            }

            $googleData = $response->json();

            // Pastikan email Google telah terverifikasi secara resmi
            $emailVerified = filter_var($googleData['email_verified'] ?? false, FILTER_VALIDATE_BOOLEAN);
            if (!$emailVerified) {
                return ApiResponse::error('Akun Google Anda belum memverifikasi alamat email.', [], 403);
            }

            // Validasi audience (Client ID) jika dikonfigurasi di environment
            $expectedClientId = config('services.google.client_id') ?: env('GOOGLE_CLIENT_ID');
            if (!empty($expectedClientId) && (($googleData['aud'] ?? '') !== $expectedClientId)) {
                return ApiResponse::error('Token Google tidak diterbitkan untuk aplikasi SI-APPIK.', [], 401);
            }

            $email = strtolower(trim($googleData['email']));
            $name = $googleData['name'] ?? explode('@', $email)[0];

            // Cari user berdasarkan email
            $user = User::where('email', $email)->first();
            $isNewUser = false;

            if (!$user) {
                $isNewUser = true;
                // Registrasi otomatis akun baru warga melalui Google OAuth (avatar default aplikasi)
                $user = User::create([
                    'name'                  => $name,
                    'email'                 => $email,
                    'password'              => Hash::make(\Illuminate\Support\Str::random(32)),
                    'role'                  => 'WARGA',
                    'email_verified_at'     => now(),
                    'avatar'                => null,
                    'last_login_ip'         => $request->ip(),
                    'last_login_user_agent' => $request->userAgent(),
                    'last_login_at'         => now(),
                ]);
            }

            $currentIp        = $request->ip();
            $currentUserAgent = $request->userAgent();

            if (!$isNewUser) {
                if (!($user->is_active ?? true)) {
                    $reasonText = $user->deactivation_reason
                        ? " Alasan: {$user->deactivation_reason}"
                        : '';
                    return ApiResponse::error(
                        "Akun Anda telah dinonaktifkan oleh Super Admin.{$reasonText} Silakan hubungi administrator untuk pengaktifan kembali.",
                        ['account_deactivated' => true, 'deactivation_reason' => $user->deactivation_reason],
                        403
                    );
                }

                $user->update([
                    'last_login_ip'         => $currentIp,
                    'last_login_user_agent' => $currentUserAgent,
                    'last_login_at'         => now(),
                ]);
            }

            // Terbitkan Token Sanctum 30 hari
            $token = $user->createToken('auth-token')->plainTextToken;

            return ApiResponse::success([
                'user'        => $user,
                'token'       => $token,
                'is_new_user' => $isNewUser,
            ], 'Autentikasi Google berhasil!');
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Google Login Error: ' . $e->getMessage());
            return ApiResponse::error('Terjadi gangguan saat memverifikasi akun Google ke server.', [], 500);
        }
    }

    public function forgotPassword(Request $request): JsonResponse
    {
        $request->validate([
            'email'    => 'required|email',
            'otp'      => 'required|string|size:6',
            'password' => ['required', 'string', 'min:8', 'regex:/[a-z]/', 'regex:/[A-Z]/', 'regex:/[0-9]/', 'confirmed'],
        ], [
            'otp.required'       => 'Kode OTP verifikasi wajib diisi.',
            'otp.size'           => 'Kode OTP harus 6 digit angka.',
            'password.confirmed' => 'Konfirmasi kata sandi baru tidak cocok.',
            'password.min'       => 'Kata sandi baru minimal 8 karakter.',
            'password.regex'     => 'Kata sandi baru harus kombinasi huruf besar, huruf kecil, dan angka.',
        ]);

        $user = User::where('email', $request->email)->first();
        if (!$user) {
            return ApiResponse::error('Proses reset kata sandi tidak dapat dilanjutkan.', [], 422);
        }

        if (!($user->is_active ?? true)) {
            $reasonText = $user->deactivation_reason
                ? " Alasan: {$user->deactivation_reason}"
                : '';
            return ApiResponse::error(
                "Akun Anda telah dinonaktifkan oleh Super Admin.{$reasonText} Silakan hubungi administrator untuk pengaktifan kembali.",
                ['account_deactivated' => true, 'deactivation_reason' => $user->deactivation_reason],
                403
            );
        }

        $role = strtoupper($user->role);

        // Hanya WARGA dan PETUGAS yang diizinkan reset sandi secara mandiri via OTP di aplikasi mobile
        if (!in_array($role, ['WARGA', 'PETUGAS'], true)) {
            return ApiResponse::error(
                'Demi keamanan, akun kedinasan/pimpinan tidak dapat diatur ulang melalui form publik. Silakan hubungi Administrator IT Dinas Kominfo / PUPR.',
                [],
                403
            );
        }

        // Verifikasi kode OTP dengan hasil detail dan pembatasan 3 kali percobaan salah
        $otpCheck = $this->otpService->verifyOtpWithResult($request->email, $request->otp, 'FORGOT_PASSWORD');
        if (!$otpCheck['valid']) {
            return ApiResponse::error($otpCheck['message'], [], 422);
        }

        $user->password = Hash::make($request->password);
        $user->save();

        // Kirim email peringatan kata sandi telah diubah dengan deteksi lokasi dinamis
        $this->securityMailService->sendPasswordChangedAlert(
            $user, 
            $request->ip(), 
            $request->userAgent(),
            $request->input('latitude') ? (float)$request->input('latitude') : null,
            $request->input('longitude') ? (float)$request->input('longitude') : null,
            $request->input('client_location')
        );

        // Session Revocation: putuskan semua sesi aktif di seluruh perangkat
        $user->tokens()->delete();

        return ApiResponse::success([
            'email' => $user->email,
            'role'  => $role,
        ], 'Kata sandi berhasil diatur ulang dan semua sesi login lama telah dicabut. Silakan masuk menggunakan kata sandi baru Anda.');
    }

    public function changePassword(Request $request): JsonResponse
    {
        $user = self::resolveUser($request);
        if (!$user) {
            return ApiResponse::error('Sesi autentikasi tidak valid atau belum masuk.', [], 401);
        }

        $validated = $request->validate([
            'current_password' => 'required|string',
            'password'         => ['required', 'string', 'min:8', 'regex:/[a-z]/', 'regex:/[A-Z]/', 'regex:/[0-9]/', 'confirmed'],
        ], [
            'current_password.required' => 'Kata sandi saat ini wajib diisi.',
            'password.required'         => 'Kata sandi baru wajib diisi.',
            'password.min'              => 'Kata sandi baru minimal 8 karakter.',
            'password.regex'            => 'Kata sandi baru harus mengandung kombinasi huruf besar, huruf kecil, dan angka.',
            'password.confirmed'        => 'Konfirmasi kata sandi baru tidak cocok.',
        ]);

        if (!Hash::check($validated['current_password'], $user->password)) {
            return ApiResponse::error('Kata sandi saat ini yang Anda masukkan tidak sesuai.', [], 422);
        }

        $user->password = Hash::make($validated['password']);
        $user->save();

        // Kirim email pemberitahuan kata sandi telah diubah dengan deteksi lokasi dinamis
        $this->securityMailService->sendPasswordChangedAlert(
            $user, 
            $request->ip(), 
            $request->userAgent(),
            $request->input('latitude') ? (float)$request->input('latitude') : null,
            $request->input('longitude') ? (float)$request->input('longitude') : null,
            $request->input('client_location')
        );

        // Session Revocation: cabut semua token aktif di perangkat lain
        if ($currentToken = $user->currentAccessToken()) {
            $user->tokens()->where('id', '!=', $currentToken->id)->delete();
        }

        return ApiResponse::success(null, 'Kata sandi berhasil diperbarui dan sesi perangkat lain telah dicabut demi keamanan.');
    }

    public function me(Request $request): JsonResponse
    {
        $user = self::resolveUser($request);
        if (!$user) {
            return ApiResponse::error('Sesi autentikasi kedaluwarsa atau belum masuk.', [], 401);
        }

        if (!($user->is_active ?? true)) {
            try {
                $user->tokens()->delete();
            } catch (\Throwable $e) {
            }
            $reasonText = $user->deactivation_reason
                ? " Alasan: {$user->deactivation_reason}"
                : '';
            return ApiResponse::error(
                "Akun Anda telah dinonaktifkan oleh Super Admin.{$reasonText} Silakan hubungi administrator untuk pengaktifan kembali.",
                ['account_deactivated' => true, 'deactivation_reason' => $user->deactivation_reason],
                403
            );
        }

        return ApiResponse::success($user);
    }

    public function updateProfile(Request $request): JsonResponse
    {
        $user = self::resolveUser($request);
        if (!$user) {
            return ApiResponse::error('Sesi autentikasi tidak valid atau belum login.', [], 401);
        }

        $validated = $request->validate([
            'name'             => 'required|string|max:255',
            'email'            => 'required|email|max:255|unique:users,email,' . $user->id,
            'current_password' => 'nullable|string',
            'password'         => ['nullable', 'string', 'min:8', 'regex:/[a-z]/', 'regex:/[A-Z]/', 'regex:/[0-9]/'],
            'phone'            => ['nullable', 'string', 'regex:/^(?:\+62|62|0)8[1-9][0-9]{7,10}$/', 'unique:users,phone,' . $user->id],
            'avatar'           => 'nullable',
        ], [
            'password.min'   => 'Kata sandi baru minimal 8 karakter.',
            'password.regex' => 'Kata sandi baru harus kombinasi huruf besar, huruf kecil, dan angka.',
            'phone.regex'    => 'Format nomor telepon tidak valid. Nomor Indonesia harus 10 hingga 13 digit dan diawali dengan 08 atau +628.',
            'phone.unique'   => 'Nomor telepon ini sudah terdaftar dalam sistem oleh pengguna lain.',
        ]);

        $passwordChanged = false;
        $currentProvided = $validated['current_password'] ?? '';

        // Verifikasi ketat kata sandi lama jika pengguna ingin mengubah kata sandi
        if (!empty($validated['password'])) {
            if (empty($currentProvided)) {
                return ApiResponse::error('Kata sandi saat ini wajib diisi untuk verifikasi keamanan perubahan sandi.', [], 422);
            }

            if (!Hash::check($currentProvided, $user->password)) {
                return ApiResponse::error('Kata sandi saat ini yang Anda masukkan salah. Silakan periksa kembali kata sandi akun Anda.', [], 422);
            }

            if (strtolower(trim($validated['password'])) === 'password') {
                return ApiResponse::error('Kata sandi baru tidak boleh menggunakan kata sandi default "password".', [], 422);
            }

            if (Hash::check($validated['password'], $user->password)) {
                return ApiResponse::error('Kata sandi baru tidak boleh sama dengan kata sandi yang sedang digunakan saat ini.', [], 422);
            }

            $user->password = Hash::make($validated['password']);
            $passwordChanged = true;

            // Session Revocation: cabut sesi di seluruh perangkat lain
            if ($currentToken = $user->currentAccessToken()) {
                $user->tokens()->where('id', '!=', $currentToken->id)->delete();
            }
        }

        // Catat jejak perubahan data akun untuk notifikasi email keamanan
        $oldName  = $user->name;
        $oldEmail = $user->email;
        $oldPhone = $user->phone;

        $changes = [];
        if ($oldName !== $validated['name']) {
            $changes['Nama Lengkap'] = "Diubah dari '{$oldName}' menjadi '{$validated['name']}'";
        }

        if (strtolower(trim($oldEmail)) !== strtolower(trim($validated['email']))) {
            $changes['Alamat Email'] = "Diubah dari '{$oldEmail}' menjadi '{$validated['email']}'";
        }

        if (array_key_exists('phone', $validated) && $oldPhone !== $validated['phone']) {
            $oldPhoneStr = $oldPhone ?: '(Belum diatur)';
            $newPhoneStr = $validated['phone'] ?: '(Dihapus)';
            $changes['No. Handphone / WhatsApp'] = "Diubah dari {$oldPhoneStr} menjadi {$newPhoneStr}";
        }

        if ($passwordChanged) {
            $changes['Kata Sandi'] = 'Kata sandi akun telah berhasil diperbarui ke kata sandi baru';
        }

        $user->name = $validated['name'];
        $user->email = $validated['email'];

        if (array_key_exists('phone', $validated)) {
            $user->phone = $validated['phone'];
        }

        if ($request->hasFile('avatar')) {
            $path = $request->file('avatar')->store('avatars', 'public');
            $user->avatar = '/storage/' . $path;
        } elseif ($request->has('avatar')) {
            $avatarVal = $request->input('avatar');
            if ($avatarVal === '' || $avatarVal === 'DELETE' || $avatarVal === null || $avatarVal === 'null') {
                $user->avatar = null;
            } else {
                $user->avatar = $avatarVal;
            }
        }

        $user->save();

        // Kirim email pemberitahuan keamanan jika ada data penting atau sandi yang diubah
        if (!empty($changes)) {
            $this->securityMailService->sendProfileUpdatedAlert(
                $user, 
                $changes,
                $oldEmail,
                $request->ip(), 
                $request->userAgent(),
                $request->input('latitude') ? (float)$request->input('latitude') : null,
                $request->input('longitude') ? (float)$request->input('longitude') : null,
                $request->input('client_location')
            );
        }

        return ApiResponse::success($user, 'Profil berhasil diperbarui');
    }

    public function logout(Request $request): JsonResponse
    {
        if ($user = self::resolveUser($request)) {
            if (method_exists($user, 'currentAccessToken')) {
                $user->currentAccessToken()?->delete();
            }
        }

        return ApiResponse::success(null, 'Logout berhasil');
    }

    protected function getAuthenticatedUser(Request $request): ?User
    {
        return self::resolveUser($request);
    }
}
