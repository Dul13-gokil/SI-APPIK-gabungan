@extends('layouts.app')

@section('title', 'SI-APPIK - Login Panel')

@section('content')
<div class="min-h-[calc(100vh-110px)] flex items-center justify-center py-3 px-3 sm:px-4 relative overflow-hidden bg-[#F8FAFC]">
    <div class="max-w-lg w-full bg-white rounded-xl border border-[#E2E8F0] p-5 sm:p-6 space-y-4 relative z-10">

        <!-- 1. Header & Logo Panel -->
        <div class="text-center relative z-10 flex flex-col items-center justify-center">
            <img src="{{ asset('images/logo-pin.png') }}" class="w-12 h-12 object-contain mb-1.5 mx-auto" alt="Logo SI-APPIK">
            <div class="flex items-center justify-center gap-1.5 mb-0.5">
                <span class="font-outfit font-bold text-2xl tracking-tight text-[#0F172A]">
                    SI-APPIK
                </span>
            </div>
            <p class="text-[10.5px] sm:text-xs font-semibold text-[#475569] tracking-tight font-outfit">
                Sistem Informasi Aduan &amp; Penanganan Prasarana Infrastruktur Kabupaten
            </p>
            <p class="text-[11px] text-[#475569] mt-0.5">
                Portal Masuk Khusus Petugas, Operator, dan Pimpinan Daerah
            </p>
        </div>

        @if(app()->environment('local', 'development'))
        <!-- 2. Kotak Pilih Cepat Akun Tugas (Khusus Lingkungan Pengembangan / Local Testing) -->
        <div id="quickRolesWrapper" class="bg-slate-50/90 rounded-2xl p-3.5 border border-slate-200/80 space-y-2.5 relative z-10 transition-all duration-200">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-[#1E3A8A] animate-pulse"></span>
                    <span class="text-[11px] font-bold tracking-tight text-slate-700">Pilih Cepat Akun Tugas</span>
                </div>
                <button type="button" onclick="toggleQuickRoles()" id="toggleQuickRolesBtn" 
                    class="inline-flex items-center gap-1.5 text-[11px] font-bold text-[#1E3A8A] hover:text-[#172e6e] bg-white hover:bg-slate-100 border border-slate-200 px-2.5 py-1 rounded-lg transition shadow-2xs cursor-pointer">
                    <i id="toggleQuickRolesIcon" data-lucide="eye-off" class="w-3.5 h-3.5 text-[#1E3A8A]"></i>
                    <span id="toggleQuickRolesText">Sembunyikan</span>
                </button>
            </div>

            <!-- Konten Kartu Akun Cepat (Bisa Disembunyikan / Dimunculkan) -->
            <div id="quickRolesContent" class="space-y-2 transition-all duration-200">
                <div class="text-[10px] text-slate-400 flex items-center justify-between pb-0.5">
                    <span>Klik akun di bawah untuk auto-fill form:</span>
                    <span class="text-[#1E3A8A] font-semibold">5 Akun Tersedia</span>
                </div>
                <div class="grid grid-cols-2 gap-2">
                    <button type="button" onclick="selectRole('admin@kabupaten.go.id', 'password', 'Super Admin')" 
                        class="role-btn p-2.5 rounded-xl border border-slate-200 bg-white hover:border-[#1E3A8A] hover:bg-[#1E3A8A]/5 text-left transition duration-150 flex flex-col gap-0.5 shadow-xs focus:ring-2 focus:ring-[#1E3A8A]/30 focus:border-[#1E3A8A] cursor-pointer">
                        <div class="flex items-center gap-1.5 text-slate-900 font-bold text-xs">
                            <i data-lucide="shield" class="w-3.5 h-3.5 text-[#1E3A8A]"></i>
                            <span>Super Admin</span>
                        </div>
                        <span class="text-[10px] text-slate-500">Kendali Penuh</span>
                    </button>

                    <button type="button" onclick="selectRole('operator@kabupaten.go.id', 'password', 'Operator')" 
                        class="role-btn p-2.5 rounded-xl border border-slate-200 bg-white hover:border-[#1E3A8A] hover:bg-[#1E3A8A]/5 text-left transition duration-150 flex flex-col gap-0.5 shadow-xs focus:ring-2 focus:ring-[#1E3A8A]/30 focus:border-[#1E3A8A] cursor-pointer">
                        <div class="flex items-center gap-1.5 text-slate-900 font-bold text-xs">
                            <i data-lucide="clipboard-check" class="w-3.5 h-3.5 text-[#1E3A8A]"></i>
                            <span>Operator</span>
                        </div>
                        <span class="text-[10px] text-slate-500">Verifikasi Laporan</span>
                    </button>

                    <button type="button" onclick="selectRole('bupati@kabupaten.go.id', 'password', 'Kepala Daerah')" 
                        class="role-btn p-2.5 rounded-xl border border-slate-200 bg-white hover:border-[#1E3A8A] hover:bg-[#1E3A8A]/5 text-left transition duration-150 flex flex-col gap-0.5 shadow-xs focus:ring-2 focus:ring-[#1E3A8A]/30 focus:border-[#1E3A8A] cursor-pointer">
                        <div class="flex items-center gap-1.5 text-slate-900 font-bold text-xs">
                            <i data-lucide="landmark" class="w-3.5 h-3.5 text-[#1E3A8A]"></i>
                            <span>Kepala Daerah</span>
                        </div>
                        <span class="text-[10px] text-slate-500">Monitoring Eksekutif</span>
                    </button>

                    <button type="button" onclick="selectRole('petugas@kabupaten.go.id', 'password', 'Petugas Lap.')" 
                        class="role-btn p-2.5 rounded-xl border border-slate-200 bg-white hover:border-[#1E3A8A] hover:bg-[#1E3A8A]/5 text-left transition duration-150 flex flex-col gap-0.5 shadow-xs focus:ring-2 focus:ring-[#1E3A8A]/30 focus:border-[#1E3A8A] cursor-pointer">
                        <div class="flex items-center gap-1.5 text-slate-900 font-bold text-xs">
                            <i data-lucide="wrench" class="w-3.5 h-3.5 text-[#1E3A8A]"></i>
                            <span>Petugas Lap.</span>
                        </div>
                        <span class="text-[10px] text-slate-500">Pengerjaan Fisik</span>
                    </button>

                    <!-- Tombol Uji Coba Cepat Role Warga -->
                    <button type="button" onclick="selectRole('warga@example.com', 'password', 'Warga (Uji Coba)')" 
                        class="role-btn col-span-2 p-2.5 rounded-xl border border-dashed border-slate-300 bg-white hover:border-[#1E3A8A] hover:bg-[#1E3A8A]/5 text-left transition duration-150 flex items-center justify-between shadow-xs focus:ring-2 focus:ring-[#1E3A8A]/30 focus:border-[#1E3A8A] cursor-pointer group">
                        <div class="flex items-center gap-2">
                            <div class="w-6 h-6 rounded-lg bg-[#1E3A8A]/10 group-hover:bg-[#1E3A8A]/20 text-[#1E3A8A] flex items-center justify-center shrink-0 transition">
                                <i data-lucide="user-x" class="w-3.5 h-3.5"></i>
                            </div>
                            <div>
                                <span class="block text-xs font-bold text-slate-800 group-hover:text-[#1E3A8A] leading-tight transition">Uji Coba Akun Warga</span>
                                <span class="block text-[10px] text-slate-400">Klik untuk menguji pengalihan ke aplikasi</span>
                            </div>
                        </div>
                        <span class="text-[10px] font-mono font-semibold text-[#1E3A8A] bg-[#1E3A8A]/10 px-2 py-0.5 rounded-md border border-[#1E3A8A]/20">warga@example.com</span>
                    </button>
                </div>
            </div>
        </div>
        @endif

        <!-- 3. Form Input Kredensial -->
        <form id="loginForm" method="POST" action="{{ route('login') }}" onsubmit="handleLoginSubmit(event)" class="space-y-3 relative z-10">
            @csrf

            @if(session('success'))
                <div class="p-3 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs flex items-center gap-2 shadow-xs">
                    <i data-lucide="check-circle" class="w-4 h-4 shrink-0 text-emerald-600"></i>
                    <span>{{ session('success') }}</span>
                </div>
            @endif

            @if($errors->any())
                <div class="p-3 rounded-xl bg-red-50 border border-red-200 text-red-700 text-xs flex items-center gap-2 shadow-xs">
                    <i data-lucide="alert-circle" class="w-4 h-4 shrink-0 text-red-500"></i>
                    <span>{{ $errors->first() }}</span>
                </div>
            @endif

            <div class="space-y-1">
                <label for="email" class="block text-xs font-bold text-slate-700">
                    Email atau Nomor Telepon
                </label>
                <div class="relative rounded-xl shadow-xs group">
                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400 group-focus-within:text-[#1E3A8A] transition-colors">
                        <i data-lucide="mail" class="w-4 h-4"></i>
                    </div>
                    <input id="email" name="email" type="text" autocomplete="username" required
                        value="{{ old('email', 'operator@kabupaten.go.id') }}"
                        placeholder="Email atau No. Telepon (contoh: 08xxxxxxxxxx)"
                        class="block w-full pl-10 pr-3.5 py-2.5 text-xs rounded-xl border border-slate-300 bg-slate-50/50 hover:border-slate-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#1E3A8A]/20 focus:border-[#1E3A8A] transition">
                </div>
                <!-- Banner Pemberitahuan jika Akun Warga Terdeteksi -->
                <div id="wargaInlineNotice" class="hidden mt-2 p-3 rounded-xl bg-amber-50 border border-amber-300 text-amber-900 text-[11.5px] leading-relaxed animate-in fade-in flex items-start gap-2.5 shadow-xs">
                    <i data-lucide="alert-triangle" class="w-4 h-4 text-amber-600 shrink-0 mt-0.5"></i>
                    <div class="flex-1">
                        <p class="font-bold text-amber-950">Portal Khusus Operator, Petugas Lapangan, & Kepala Daerah</p>
                        <p class="text-[11px] text-amber-800 mt-0.5">
                            Akun <strong id="wargaDetectedName" class="text-slate-900"></strong> terdaftar sebagai <strong>Warga Masyarakat</strong>. Website ini hanya diperuntukkan bagi aparatur internal kedinasan.
                        </p>
                        <button type="button" onclick="openWargaModal(wargaCurrentDetectedName)" class="mt-1.5 inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-amber-200/80 hover:bg-amber-300/80 text-amber-950 text-[11px] font-bold transition cursor-pointer">
                            <i data-lucide="smartphone" class="w-3.5 h-3.5 text-[#1E3A8A]"></i>
                            <span>Buka Panduan Unduh Aplikasi Mobile Warga</span>
                        </button>
                    </div>
                </div>
            </div>

            <div class="space-y-1">
                <label for="password" class="block text-xs font-bold text-slate-700">
                    Kata Sandi
                </label>
                <div class="relative rounded-xl group">
                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400 group-focus-within:text-[#1E3A8A] transition-colors">
                        <i data-lucide="lock" class="w-4 h-4"></i>
                    </div>
                    <input id="password" name="password" type="password" required
                        value="password"
                        placeholder="••••••••"
                        class="block w-full pl-10 pr-10 py-2.5 text-xs rounded-xl border border-slate-300 bg-slate-50/50 hover:border-slate-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#1E3A8A]/20 focus:border-[#1E3A8A] transition">
                    <button type="button" id="togglePasswordBtn" onclick="toggleLoginPasswordVisibility()" aria-label="Tampilkan atau sembunyikan kata sandi"
                        class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-[#1E3A8A] transition-colors cursor-pointer">
                        <i id="togglePasswordIcon" data-lucide="eye" class="w-4 h-4"></i>
                    </button>
                </div>
            </div>
            <script>
                function toggleLoginPasswordVisibility() {
                    const input = document.getElementById('password');
                    const icon = document.getElementById('togglePasswordIcon');
                    const btn = document.getElementById('togglePasswordBtn');
                    if (!input || !icon) return;
                    const isHidden = input.type === 'password';
                    input.type = isHidden ? 'text' : 'password';
                    icon.setAttribute('data-lucide', isHidden ? 'eye-off' : 'eye');
                    if (window.lucide && btn) lucide.createIcons({ root: btn });
                }
            </script>

            <div class="flex items-center justify-between text-xs pt-0.5">
                <label class="flex items-center text-slate-600 cursor-pointer select-none">
                    <input type="checkbox" id="rememberMe" name="remember" value="1" class="w-3.5 h-3.5 text-[#1E3A8A] rounded border-slate-300 focus:ring-[#1E3A8A]">
                    <span class="ml-2 text-[11px] font-medium text-slate-700">Ingat sesi saya</span>
                </label>
                <button type="button" onclick="openForgotPasswordModal()" class="text-[11px] font-bold text-[#1E3A8A] hover:text-[#172e6e] hover:underline cursor-pointer">
                    Lupa Kata Sandi?
                </button>
            </div>

            <button type="submit" id="submitLoginBtn"
                class="w-full min-h-[44px] px-4 rounded-lg bg-[#1E3A8A] hover:bg-[#172554] text-white font-semibold text-xs tracking-wide transition duration-150 flex items-center justify-center gap-2 cursor-pointer focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#1E3A8A]">
                <i data-lucide="log-in" class="w-4 h-4"></i>
                <span id="submitBtnText">Masuk ke Panel Kerja</span>
            </button>
        </form>

        <!-- 4. Tombol Kembali ke Halaman Publik -->
        <div class="text-center pt-2 border-t border-slate-100 relative z-10">
            <a href="{{ url('/') }}" class="inline-flex items-center gap-2 text-xs text-slate-500 hover:text-[#1E3A8A] font-semibold transition py-1 group">
                <i data-lucide="arrow-left" class="w-3.5 h-3.5 group-hover:-translate-x-0.5 transition-transform"></i>
                <span>Kembali ke Peta Persebaran Publik</span>
            </a>
        </div>

    </div>
</div>

<!-- Modal Pengalihan Akun Warga ke Mobile -->
<div id="wargaRedirectModal" class="fixed inset-0 z-50 bg-slate-900/70 hidden flex items-center justify-center p-3 sm:p-4 overflow-y-auto w-full h-full">
    <div class="w-full max-w-[calc(100vw-24px)] sm:max-w-md bg-white rounded-xl border border-[#E2E8F0] overflow-hidden relative my-auto">
        
        <!-- Header Modal -->
        <div class="bg-[#1E3A8A] text-white p-4 sm:p-5 relative border-b border-[#172554]">
            <button type="button" onclick="handleSayaMengerti()" aria-label="Tutup"
                class="absolute top-3.5 right-3.5 w-8 h-8 rounded-lg bg-[#172554] hover:bg-blue-950 text-white flex items-center justify-center transition cursor-pointer focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-white">
                <i data-lucide="x" class="w-4 h-4"></i>
            </button>

            <div class="flex items-center gap-3 pr-8">
                <div class="w-10 h-10 rounded-lg bg-[#172554] border border-blue-800 flex items-center justify-center text-white shrink-0">
                    <i id="modalHeaderIcon" data-lucide="smartphone" class="w-5 h-5 text-white"></i>
                </div>
                <div>
                    <h3 id="modalHeaderTitle" class="font-outfit font-bold text-base sm:text-lg text-white tracking-tight leading-snug">
                        Gunakan Aplikasi Mobile SI-APPIK
                    </h3>
                    <p id="modalHeaderSub" class="text-xs text-blue-100 mt-0.5 leading-snug">
                        Portal web ini khusus untuk petugas dinas
                    </p>
                </div>
            </div>
        </div>

        <div class="p-4 sm:p-5 space-y-4">
            
            <div class="p-3.5 rounded-lg bg-amber-50 border border-amber-200 text-[#0F172A] text-xs leading-relaxed">
                Halo <strong id="wargaCitizenName" class="text-[#1E3A8A]">Warga</strong>, akun Anda terdaftar sebagai <strong>Warga Masyarakat</strong>. Pelaporan kerusakan dan pantauan status dilakukan melalui aplikasi mobile SI-APPIK.
            </div>

            <!-- Tampilan Download di Smartphone -->
            <div id="viewMobileContainer" class="space-y-3">
                <p class="text-xs text-[#475569]">
                    Unduh aplikasi resmi SI-APPIK di ponsel Anda:
                </p>

                <div class="flex flex-col gap-2.5 w-full">
                    <a href="{{ route('download.apk') }}" 
                        class="w-full flex items-center justify-center gap-3 px-4 min-h-[48px] rounded-lg bg-[#0F172A] hover:bg-slate-800 text-white border border-slate-800 transition">
                        <svg class="w-5 h-5 shrink-0 fill-white" viewBox="0 0 24 24">
                            <path d="M4.059 2.046C3.714 2.39 3.5 2.915 3.5 3.612v16.776c0 .697.214 1.222.56 1.566l.08.077 9.4-9.399v-.223l-9.4-9.4-.08.037zM17.067 15.65l-3.427-3.428v-.444l3.427-3.428.077.045 4.062 2.308c1.16.658 1.16 1.737 0 2.396l-4.062 2.307-.077.244zM13.64 11.778L4.208 2.346c.386-.41.986-.445 1.71-.035l11.149 6.335-3.427 3.132zM13.64 12.222l3.427 3.132-11.149 6.335c-.724.41-1.324.375-1.71-.035L13.64 12.222z"/>
                        </svg>
                        <div class="text-left leading-tight">
                            <span class="text-[10px] font-semibold text-slate-300 uppercase tracking-wider block">Unduh APK</span>
                            <span class="text-sm font-bold text-white">Android (Google Play)</span>
                        </div>
                    </a>

                    <a href="https://apps.apple.com" target="_blank" rel="noopener noreferrer" 
                        class="w-full flex items-center justify-center gap-3 px-4 min-h-[48px] rounded-lg bg-[#0F172A] hover:bg-slate-800 text-white border border-slate-800 transition">
                        <svg class="w-5 h-5 shrink-0 fill-white" viewBox="0 0 24 24">
                            <path d="M18.71 19.5c-.83 1.24-1.71 2.45-3.05 2.47-1.34.03-1.77-.79-3.29-.79-1.53 0-2 .77-3.27.82-1.31.05-2.3-1.32-3.14-2.53C4.25 17 2.94 12.45 4.7 9.39c.87-1.52 2.43-2.48 4.12-2.51 1.28-.02 2.5.87 3.29.87.78 0 2.26-1.07 3.81-.91.65.03 2.47.26 3.64 1.98-.09.06-2.17 1.28-2.15 3.81.03 3.02 2.65 4.03 2.68 4.04-.03.07-.42 1.44-1.38 2.83M15.97 6.37c.62-.75 1.04-1.8 1.01-2.87-.96.04-2.12.64-2.8 1.44-.59.68-1.11 1.77-1 2.82 1.07.08 2.17-.55 2.79-1.39z"/>
                        </svg>
                        <div class="text-left leading-tight">
                            <span class="text-[10px] font-semibold text-slate-300 uppercase tracking-wider block">Tersedia di</span>
                            <span class="text-sm font-bold text-white">App Store</span>
                        </div>
                    </a>
                </div>
            </div>

            <!-- Tampilan Desktop: QR Code -->
            <div id="viewDesktopContainer" class="hidden space-y-3">
                <div class="flex flex-col sm:flex-row items-center gap-4 p-3.5 rounded-lg bg-[#F8FAFC] border border-[#E2E8F0]">
                    <div class="w-24 h-24 p-1.5 bg-white rounded-lg border border-[#E2E8F0] shrink-0 flex items-center justify-center">
                        <img src="https://api.qrserver.com/v1/create-qr-code/?size=150x150&data={{ urlencode(route('download.apk')) }}" 
                             alt="QR Code Unduh Aplikasi" 
                             class="w-full h-full object-contain">
                    </div>
                    <div class="text-center sm:text-left space-y-1">
                        <span class="text-xs font-bold text-[#0F172A] block">Pindai QR dengan Kamera Ponsel</span>
                        <p class="text-xs text-[#475569] leading-relaxed">
                            Arahkan kamera ponsel ke QR Code untuk mengunduh aplikasi SI-APPIK.
                        </p>
                    </div>
                </div>
            </div>

            <!-- Footer Tombol -->
            <div class="pt-2 border-t border-[#E2E8F0]">
                <button type="button" onclick="handleSayaMengerti()" 
                    class="w-full min-h-[44px] rounded-lg bg-[#1E3A8A] hover:bg-[#172554] text-white font-semibold text-sm transition cursor-pointer flex items-center justify-center gap-2 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#1E3A8A]">
                    <span>Saya Mengerti</span>
                    <i data-lucide="arrow-right" class="w-4 h-4"></i>
                </button>
            </div>
        </div>
    </div>
</div>

<!-- ========================================================================= -->
<!-- MODAL LUPA KATA SANDI - VERIFIKASI OTP RESMI (WEBSITE) -->
<!-- ========================================================================= -->
<div id="forgotPasswordModal" class="fixed inset-0 z-50 bg-slate-900/65 hidden flex items-center justify-center p-3 sm:p-4 overflow-y-auto w-full h-full">
    <div class="w-full max-w-[calc(100vw-24px)] sm:max-w-md bg-white rounded-xl shadow-md border border-[#E2E8F0] overflow-hidden relative my-auto">
        
        <!-- Header Modal (#1E3A8A) -->
        <div class="bg-[#1E3A8A] text-white p-4 sm:p-5 relative">
            <button type="button" onclick="closeForgotPasswordModal()" 
                class="absolute top-3.5 right-3.5 w-7 h-7 rounded-full bg-white/10 hover:bg-white/20 text-white flex items-center justify-center transition cursor-pointer">
                <i data-lucide="x" class="w-3.5 h-3.5"></i>
            </button>

            <div class="flex items-center gap-3 pr-6">
                <div class="w-10 h-10 rounded-xl bg-white/15 border border-white/20 flex items-center justify-center text-white shrink-0">
                    <i data-lucide="key-round" class="w-5 h-5 text-white"></i>
                </div>
                <div>
                    <h3 class="font-outfit font-extrabold text-base sm:text-lg text-white tracking-tight leading-snug">
                        Atur Ulang Kata Sandi
                    </h3>
                    <p class="text-[11px] text-blue-100 mt-0.5 leading-snug">
                        Verifikasi Kode OTP Resmi SI-APPIK Kolaka Utara
                    </p>
                </div>
            </div>
        </div>

        <div class="p-4 sm:p-6 space-y-4">
            
            <!-- Alert Notifikasi / Pesan Error atau Sukses -->
            <div id="forgotAlertBox" class="hidden p-3 rounded-xl text-xs flex items-start gap-2 shadow-xs">
                <i id="forgotAlertIcon" data-lucide="info" class="w-4 h-4 shrink-0 mt-0.5"></i>
                <span id="forgotAlertMsg" class="leading-relaxed"></span>
            </div>

            <!-- TAHAP 1: INPUT ALAMAT EMAIL -->
            <div id="forgotStep1" class="space-y-3.5">
                <p class="text-xs text-slate-600 leading-relaxed">
                    Masukkan <strong>Alamat Email</strong> kedinasan yang terdaftar pada akun Anda. Sistem akan mengirimkan 6-digit kode OTP keamanan ke alamat email resmi Anda:
                </p>

                <div class="space-y-1">
                    <label for="forgotEmailInput" class="block text-xs font-bold text-slate-700">Alamat Email</label>
                    <div class="relative rounded-xl shadow-xs">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                            <i data-lucide="mail" class="w-4 h-4"></i>
                        </div>
                        <input id="forgotEmailInput" type="email" placeholder="nama@kabupaten.go.id"
                            class="block w-full pl-10 pr-3.5 py-2.5 text-xs rounded-xl border border-slate-300 bg-slate-50/50 hover:border-slate-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#1E3A8A]/20 focus:border-[#1E3A8A] transition">
                    </div>
                </div>

                <!-- Banner Pemberitahuan jika Akun Warga Terdeteksi di Modal Lupa Sandi -->
                <div id="forgotWargaInlineNotice" class="hidden p-3 rounded-xl bg-amber-50 border border-amber-300 text-amber-900 text-[11.5px] leading-relaxed flex items-start gap-2.5 shadow-xs">
                    <i data-lucide="alert-triangle" class="w-4 h-4 text-amber-600 shrink-0 mt-0.5"></i>
                    <div class="flex-1">
                        <p class="font-bold text-amber-950">Portal Khusus Operator, Petugas Lapangan, & Kepala Daerah</p>
                        <p class="text-[11px] text-amber-800 mt-0.5">
                            Akun <strong id="forgotWargaDetectedName" class="text-slate-900"></strong> terdaftar sebagai <strong>Warga Masyarakat</strong>. Website ini khusus untuk Operator, Petugas Lapangan, dan Kepala Daerah. Silakan gunakan Aplikasi Mobile SI-APPIK untuk mengatur ulang sandi.
                        </p>
                        <button type="button" onclick="closeForgotPasswordModal(); openWargaModal(forgotWargaCurrentDetectedName);" class="mt-1.5 inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-amber-200/80 hover:bg-amber-300/80 text-amber-950 text-[11px] font-bold transition cursor-pointer">
                            <i data-lucide="smartphone" class="w-3.5 h-3.5 text-[#1E3A8A]"></i>
                            <span>Buka Panduan Unduh Aplikasi Mobile Warga</span>
                        </button>
                    </div>
                </div>

                <button type="button" id="btnSendForgotOtp" onclick="requestForgotOtp()"
                    class="w-full py-3 rounded-xl bg-[#1E3A8A] hover:bg-[#172e6e] text-white font-bold text-xs shadow-md shadow-[#1E3A8A]/25 transition flex items-center justify-center gap-2 cursor-pointer">
                    <i data-lucide="send" class="w-4 h-4"></i>
                    <span id="btnSendForgotOtpText">Kirim Kode OTP</span>
                </button>
            </div>

            <!-- TAHAP 2: INPUT KODE OTP & KATA SANDI BARU (MIN 8 KARAKTER, BESAR, KECIL, ANGKA) -->
            <div id="forgotStep2" class="hidden space-y-3.5">
                <div class="p-2.5 rounded-xl bg-blue-50 border border-blue-100 flex items-center justify-between text-xs">
                    <div class="flex items-center gap-2 overflow-hidden">
                        <i data-lucide="mail-check" class="w-4 h-4 text-[#1E3A8A] shrink-0"></i>
                        <span id="forgotTargetEmailDisplay" class="font-bold text-[#1E3A8A] truncate text-[11px]">email@kabupaten.go.id</span>
                    </div>
                    <button type="button" onclick="switchForgotStep(1)" class="text-[11px] text-slate-500 hover:text-slate-800 font-semibold underline shrink-0 cursor-pointer">
                        Ganti
                    </button>
                </div>

                <div class="space-y-1">
                    <label for="forgotOtpInput" class="block text-xs font-bold text-slate-700">Kode OTP 6 Digit *</label>
                    <div class="relative rounded-xl shadow-xs">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                            <i data-lucide="key" class="w-4 h-4"></i>
                        </div>
                        <input id="forgotOtpInput" type="text" maxlength="6" placeholder="Contoh: 123456"
                            class="block w-full pl-10 pr-3.5 py-2.5 text-xs font-mono tracking-widest font-bold rounded-xl border border-slate-300 bg-slate-50/50 hover:border-slate-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#1E3A8A]/20 focus:border-[#1E3A8A] transition text-slate-800">
                    </div>
                </div>

                <div class="space-y-1">
                    <label for="forgotNewPasswordInput" class="block text-xs font-bold text-slate-700">Kata Sandi Baru *</label>
                    <div class="relative rounded-xl shadow-xs">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                            <i data-lucide="lock" class="w-4 h-4"></i>
                        </div>
                        <input id="forgotNewPasswordInput" type="password" minlength="8" placeholder="Minimal 8 karakter..."
                            class="block w-full pl-10 pr-10 py-2.5 text-xs rounded-xl border border-slate-300 bg-slate-50/50 hover:border-slate-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#1E3A8A]/20 focus:border-[#1E3A8A] transition">
                        <button type="button" onclick="togglePassVisibility('forgotNewPasswordInput', 'iconForgotEye1')" class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-slate-600 cursor-pointer">
                            <i id="iconForgotEye1" data-lucide="eye" class="w-4 h-4"></i>
                        </button>
                    </div>
                </div>

                <div class="space-y-1">
                    <label for="forgotConfirmPasswordInput" class="block text-xs font-bold text-slate-700">Konfirmasi Kata Sandi Baru *</label>
                    <div class="relative rounded-xl shadow-xs">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                            <i data-lucide="check-check" class="w-4 h-4"></i>
                        </div>
                        <input id="forgotConfirmPasswordInput" type="password" minlength="8" placeholder="Ulangi kata sandi baru..."
                            class="block w-full pl-10 pr-10 py-2.5 text-xs rounded-xl border border-slate-300 bg-slate-50/50 hover:border-slate-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#1E3A8A]/20 focus:border-[#1E3A8A] transition">
                        <button type="button" onclick="togglePassVisibility('forgotConfirmPasswordInput', 'iconForgotEye2')" class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-slate-600 cursor-pointer">
                            <i id="iconForgotEye2" data-lucide="eye" class="w-4 h-4"></i>
                        </button>
                    </div>
                </div>

                <!-- Petunjuk Keamanan Sandi -->
                <div class="p-2.5 rounded-xl bg-slate-50 border border-slate-200 text-[10.5px] text-slate-600 flex items-start gap-2">
                    <i data-lucide="shield-check" class="w-3.5 h-3.5 text-[#1E3A8A] shrink-0 mt-0.5"></i>
                    <span>Wajib minimal <strong>8 karakter</strong> kombinasi <strong>huruf besar (A-Z)</strong>, <strong>huruf kecil (a-z)</strong>, dan <strong>angka (0-9)</strong>.</span>
                </div>

                <button type="button" id="btnResetForgotPass" onclick="submitResetPassword()"
                    class="w-full py-3 rounded-xl bg-[#1E3A8A] hover:bg-[#172e6e] text-white font-bold text-xs shadow-md shadow-[#1E3A8A]/25 transition flex items-center justify-center gap-2 cursor-pointer">
                    <i data-lucide="check-circle" class="w-4 h-4"></i>
                    <span id="btnResetForgotPassText">Simpan Kata Sandi Baru</span>
                </button>
            </div>

            <!-- Tombol Tutup -->
            <div class="pt-2 border-t border-slate-100 flex items-center justify-end">
                <button type="button" onclick="closeForgotPasswordModal()" 
                    class="text-xs text-slate-500 hover:text-slate-800 font-semibold py-1 transition cursor-pointer">
                    Batal
                </button>
            </div>

        </div>
    </div>
</div>

<script>
    // Toggle Hide/Unhide Bagian Pilih Cepat Akun Tugas (Dev Only)
    function toggleQuickRoles() {
        const content = document.getElementById('quickRolesContent');
        const btnText = document.getElementById('toggleQuickRolesText');
        const icon = document.getElementById('toggleQuickRolesIcon');
        if (!content) return;

        const isHidden = content.classList.contains('hidden');
        if (isHidden) {
            content.classList.remove('hidden');
            if (btnText) btnText.textContent = 'Sembunyikan';
            if (icon) icon.setAttribute('data-lucide', 'eye-off');
            localStorage.setItem('aduan_dev_roles_hidden', 'false');
        } else {
            content.classList.add('hidden');
            if (btnText) btnText.textContent = 'Tampilkan';
            if (icon) icon.setAttribute('data-lucide', 'eye');
            localStorage.setItem('aduan_dev_roles_hidden', 'true');
        }
        if (window.lucide) lucide.createIcons();
    }

    // Identifikasi otomatis apakah user mengakses via Smartphone / HP atau Laptop / Desktop
    function isUserOnMobile() {
        const ua = navigator.userAgent || navigator.vendor || window.opera;
        const mobileRegex = /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini|Mobile|mobile/i;
        const isTouchScreen = ('ontouchstart' in window) || (navigator.maxTouchPoints > 0);
        const isSmallScreen = window.innerWidth <= 768;
        return mobileRegex.test(ua) || (isTouchScreen && isSmallScreen);
    }

    function openWargaModal(citizenName) {
        document.getElementById('wargaCitizenName').textContent = citizenName || 'Warga';
        const isMobile = isUserOnMobile();
        
        const viewMobile = document.getElementById('viewMobileContainer');
        const viewDesktop = document.getElementById('viewDesktopContainer');
        const headerIcon = document.getElementById('modalHeaderIcon');
        const headerTitle = document.getElementById('modalHeaderTitle');
        const headerSub = document.getElementById('modalHeaderSub');

        if (isMobile) {
            viewMobile.classList.remove('hidden');
            viewDesktop.classList.add('hidden');

            headerTitle.textContent = "Portal Khusus Petugas";
            headerSub.textContent = "Layanan aduan warga dialihkan ke aplikasi mobile";
            headerIcon.setAttribute('data-lucide', 'smartphone');
        } else {
            viewMobile.classList.add('hidden');
            viewDesktop.classList.remove('hidden');

            headerTitle.textContent = "Akses Khusus Petugas Kantor";
            headerSub.textContent = "Pengaduan warga wajib diakses melalui smartphone";
            headerIcon.setAttribute('data-lucide', 'laptop');
        }

        if (window.lucide) lucide.createIcons();

        const modal = document.getElementById('wargaRedirectModal');
        modal.classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    }

    // Alihkan otomatis ke halaman landing / menu utama
    function handleSayaMengerti() {
        window.location.href = "{{ url('/') }}";
    }

    function closeWargaModal() {
        handleSayaMengerti();
    }

    // =========================================================================
    // MODAL LUPA KATA SANDI (DENGAN OTP EMAIL RESMI)
    // =========================================================================
    let verifiedForgotEmail = '';

    function openForgotPasswordModal() {
        const modal = document.getElementById('forgotPasswordModal');
        const emailInput = document.getElementById('email');
        const forgotEmailInput = document.getElementById('forgotEmailInput');

        if (emailInput && forgotEmailInput && emailInput.value && emailInput.value.indexOf('@') !== -1) {
            forgotEmailInput.value = emailInput.value.trim();
        }

        switchForgotStep(1);
        hideForgotAlert();
        modal.classList.remove('hidden');
        document.body.style.overflow = 'hidden';
        if (window.lucide) lucide.createIcons();
    }

    function closeForgotPasswordModal() {
        const modal = document.getElementById('forgotPasswordModal');
        modal.classList.add('hidden');
        document.body.style.overflow = '';
    }

    function togglePassVisibility(inputId, iconId) {
        const input = document.getElementById(inputId);
        const icon = document.getElementById(iconId);
        if (!input) return;
        if (input.type === 'password') {
            input.type = 'text';
            if (icon) icon.setAttribute('data-lucide', 'eye-off');
        } else {
            input.type = 'password';
            if (icon) icon.setAttribute('data-lucide', 'eye');
        }
        if (window.lucide) lucide.createIcons();
    }

    function switchForgotStep(step) {
        const step1 = document.getElementById('forgotStep1');
        const step2 = document.getElementById('forgotStep2');
        hideForgotAlert();

        if (step === 1) {
            step1.classList.remove('hidden');
            step2.classList.add('hidden');
        } else {
            step1.classList.add('hidden');
            step2.classList.remove('hidden');
            document.getElementById('forgotTargetEmailDisplay').textContent = verifiedForgotEmail;
        }
        if (window.lucide) lucide.createIcons();
    }

    function showForgotAlert(msg, isSuccess = false) {
        const box = document.getElementById('forgotAlertBox');
        const text = document.getElementById('forgotAlertMsg');
        const icon = document.getElementById('forgotAlertIcon');
        if (!box) return;

        box.className = isSuccess
            ? 'p-3 rounded-xl text-xs flex items-start gap-2 shadow-xs bg-emerald-50 border border-emerald-200 text-emerald-800'
            : 'p-3 rounded-xl text-xs flex items-start gap-2 shadow-xs bg-red-50 border border-red-200 text-red-700';

        icon.setAttribute('data-lucide', isSuccess ? 'check-circle' : 'alert-circle');
        text.textContent = msg;
        box.classList.remove('hidden');
        if (window.lucide) lucide.createIcons();
    }

    function hideForgotAlert() {
        const box = document.getElementById('forgotAlertBox');
        if (box) box.classList.add('hidden');
    }

    async function requestForgotOtp() {
        const input = document.getElementById('forgotEmailInput').value.trim();
        if (!input) {
            showForgotAlert('Email atau nomor telepon terdaftar wajib diisi.');
            return;
        }

        const btn = document.getElementById('btnSendForgotOtp');
        const btnText = document.getElementById('btnSendForgotOtpText');
        btn.disabled = true;
        btnText.textContent = 'Mengirim Kode OTP...';
        hideForgotAlert();

        try {
            const token = document.querySelector('input[name="_token"]')?.value;
            const res = await fetch("{{ route('auth.forgot-password.send-otp') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': token,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ email: input })
            });

            const data = await res.json();
            if (res.ok && data.status === 'success') {
                verifiedForgotEmail = data.email;
                switchForgotStep(2);
                showForgotAlert(data.message, true);
            } else if (data.is_warga) {
                showForgotAlert(data.message || 'Portal website ini khusus untuk Operator, Petugas Lapangan, dan Kepala Daerah. Akun Warga Masyarakat silakan gunakan Aplikasi Mobile SI-APPIK.');
                setTimeout(() => {
                    closeForgotPasswordModal();
                    openWargaModal(data.name || 'Warga');
                }, 1800);
            } else {
                showForgotAlert(data.message || 'Gagal mengirim kode OTP. Periksa kembali email Anda.');
            }
        } catch (err) {
            showForgotAlert('Terjadi gangguan jaringan saat menghubungi server.');
        } finally {
            btn.disabled = false;
            btnText.textContent = 'Kirim Kode OTP';
        }
    }

    let userGeoLocation = { latitude: null, longitude: null };
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(
            (pos) => {
                userGeoLocation.latitude = pos.coords.latitude;
                userGeoLocation.longitude = pos.coords.longitude;
            },
            () => {},
            { timeout: 6000, maximumAge: 300000 }
        );
    }

    async function submitResetPassword() {
        const otp = document.getElementById('forgotOtpInput').value.trim();
        const newPass = document.getElementById('forgotNewPasswordInput').value;
        const confirmPass = document.getElementById('forgotConfirmPasswordInput').value;

        if (!otp || otp.length !== 6) {
            showForgotAlert('Kode OTP harus 6 digit angka.');
            return;
        }

        if (!newPass || newPass.length < 8) {
            showForgotAlert('Kata sandi baru minimal 8 karakter.');
            return;
        }

        if (!/[A-Z]/.test(newPass)) {
            showForgotAlert('Kata sandi baru wajib mengandung minimal satu huruf besar (A-Z).');
            return;
        }

        if (!/[a-z]/.test(newPass)) {
            showForgotAlert('Kata sandi baru wajib mengandung minimal satu huruf kecil (a-z).');
            return;
        }

        if (!/[0-9]/.test(newPass)) {
            showForgotAlert('Kata sandi baru wajib mengandung minimal satu angka (0-9).');
            return;
        }

        if (newPass !== confirmPass) {
            showForgotAlert('Konfirmasi kata sandi baru tidak cocok.');
            return;
        }

        const btn = document.getElementById('btnResetForgotPass');
        const btnText = document.getElementById('btnResetForgotPassText');
        btn.disabled = true;
        btnText.textContent = 'Menyimpan Kata Sandi...';
        hideForgotAlert();

        try {
            const token = document.querySelector('input[name="_token"]')?.value;
            const res = await fetch("{{ route('auth.forgot-password.reset') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': token,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    email: verifiedForgotEmail,
                    otp: otp,
                    password: newPass,
                    password_confirmation: confirmPass,
                    latitude: userGeoLocation.latitude,
                    longitude: userGeoLocation.longitude
                })
            });

            const data = await res.json();
            if (res.ok && data.status === 'success') {
                showForgotAlert(data.message, true);
                setTimeout(() => {
                    closeForgotPasswordModal();
                    const mainEmailInput = document.getElementById('email');
                    const mainPassInput = document.getElementById('password');
                    if (mainEmailInput) mainEmailInput.value = verifiedForgotEmail;
                    if (mainPassInput) {
                        mainPassInput.value = '';
                        mainPassInput.focus();
                    }
                }, 1500);
            } else {
                showForgotAlert(data.message || 'Gagal mengatur ulang kata sandi.');
            }
        } catch (err) {
            showForgotAlert('Terjadi gangguan jaringan saat menghubungi server.');
        } finally {
            btn.disabled = false;
            btnText.textContent = 'Simpan Kata Sandi Baru';
        }
    }

    // Intersepsi Submit Login: Langsung cegah submit jika akun role WARGA & simpan sesi remembered
    async function handleLoginSubmit(event) {
        if (event) {
            event.preventDefault();
        }

        const emailInput = document.getElementById('email');
        const email = emailInput ? emailInput.value.trim() : '';

        // Simpan preferensi Ingat Sesi Saya ke localStorage
        const rememberCheckbox = document.getElementById('rememberMe');
        if (rememberCheckbox && rememberCheckbox.checked && email) {
            localStorage.setItem('si_appik_remembered_login', email);
        } else {
            localStorage.removeItem('si_appik_remembered_login');
        }

        if (!email) {
            HTMLFormElement.prototype.submit.call(document.getElementById('loginForm'));
            return;
        }

        const submitBtn = document.getElementById('submitLoginBtn');
        const btnText = document.getElementById('submitBtnText');
        const origText = btnText ? btnText.textContent : 'Masuk ke Panel Kerja';

        if (btnText) btnText.textContent = 'Memeriksa Hak Akses...';
        if (submitBtn) submitBtn.disabled = true;

        try {
            const token = document.querySelector('input[name="_token"]')?.value;
            const res = await fetch("{{ route('auth.check-warga') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': token,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ email: email })
            });

            if (res.ok) {
                const data = await res.json();
                if (data.is_warga) {
                    if (submitBtn) submitBtn.disabled = false;
                    if (btnText) btnText.textContent = origText;
                    openWargaModal(data.name);
                    return false;
                }
            }
        } catch (err) {
            console.log('Role check fallback to server submit', err);
        }

        // Jika bukan role warga (petugas internal), submit langsung form ke Laravel
        if (btnText) btnText.textContent = origText;
        HTMLFormElement.prototype.submit.call(document.getElementById('loginForm'));
    }

    function selectRole(email, password, roleName) {
        document.getElementById('email').value = email;
        document.getElementById('password').value = password;
        if (roleName) {
            document.getElementById('submitBtnText').textContent = 'Masuk sebagai ' + roleName;
        }
    }

    let wargaCurrentDetectedName = 'Warga';
    let checkWargaTimer = null;

    function checkWargaLive(inputVal) {
        clearTimeout(checkWargaTimer);
        const noticeEl = document.getElementById('wargaInlineNotice');
        const nameEl = document.getElementById('wargaDetectedName');
        const val = (inputVal || '').trim();

        if (val.length < 3) {
            if (noticeEl) noticeEl.classList.add('hidden');
            return;
        }

        checkWargaTimer = setTimeout(async () => {
            try {
                const token = document.querySelector('input[name="_token"]')?.value;
                const res = await fetch("{{ route('auth.check-warga') }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': token,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ email: val })
                });

                if (res.ok) {
                    const data = await res.json();
                    if (data.is_warga) {
                        wargaCurrentDetectedName = data.name || 'Warga';
                        if (nameEl) nameEl.textContent = `"${data.name}"`;
                        if (noticeEl) {
                            noticeEl.classList.remove('hidden');
                            if (window.lucide) lucide.createIcons();
                        }
                    } else {
                        if (noticeEl) noticeEl.classList.add('hidden');
                    }
                }
            } catch (e) {
                // ignore
            }
        }, 350);
    }

    // Otomatis cek preferensi sembunyikan role, load sesi tersimpan, & cek flash session
    document.addEventListener('DOMContentLoaded', function() {
        // Muat sesi pengguna yang tersimpan (Ingat sesi saya)
        const rememberedLogin = localStorage.getItem('si_appik_remembered_login');
        const emailInput = document.getElementById('email');
        const rememberCheckbox = document.getElementById('rememberMe');
        if (rememberedLogin && emailInput && rememberCheckbox) {
            emailInput.value = rememberedLogin;
            rememberCheckbox.checked = true;
        }

        if (emailInput) {
            emailInput.addEventListener('input', function(e) {
                checkWargaLive(e.target.value);
            });
            emailInput.addEventListener('blur', function(e) {
                checkWargaLive(e.target.value);
            });
            if (emailInput.value) {
                checkWargaLive(emailInput.value);
            }
        }

        if (localStorage.getItem('aduan_dev_roles_hidden') === 'true') {
            const content = document.getElementById('quickRolesContent');
            const btnText = document.getElementById('toggleQuickRolesText');
            const icon = document.getElementById('toggleQuickRolesIcon');
            if (content) {
                content.classList.add('hidden');
                if (btnText) btnText.textContent = 'Tampilkan';
                if (icon) icon.setAttribute('data-lucide', 'eye');
                if (window.lucide) lucide.createIcons();
            }
        }

        @if(session('warga_blocked'))
            openWargaModal("{{ session('citizen_name', 'Masyarakat') }}");
        @endif
    });
</script>
@endsection
