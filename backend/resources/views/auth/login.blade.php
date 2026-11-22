@extends('layouts.app')

@section('title', 'SI-APPIK - Login Portal')

@section('content')
<div class="min-h-screen lg:h-screen lg:overflow-hidden grid grid-cols-1 lg:grid-cols-2 bg-[#FFF9F2]">

    <!-- Sisi Kiri Desktop: Panel Visual Kabupaten (1:1 FigCraft Node 1645 · Deep Burgundy/Maroon Scrim, 0px Sharp) -->
    <aside class="hidden lg:flex flex-col justify-between bg-[#4A0012] px-14 py-10 text-white h-full overflow-hidden relative">
        <div id="loginHeroSlideshow" class="absolute inset-0 z-0 pointer-events-none overflow-hidden">
            <img src="{{ asset('images/hero/repair-jalan.webp') }}" alt="Perbaikan Jalan Kabupaten" fetchpriority="high" decoding="async" class="login-slide active-slide absolute inset-0 w-full h-full object-cover opacity-100 transition-opacity duration-1000">
            <img src="{{ asset('images/hero/repair-pju.webp') }}" alt="Pemeliharaan PJU Kabupaten" loading="lazy" decoding="async" class="login-slide absolute inset-0 w-full h-full object-cover opacity-0 transition-opacity duration-1000">
            <img src="{{ asset('images/hero/repair-fasum.webp') }}" alt="Perbaikan Fasilitas Umum Kabupaten" loading="lazy" decoding="async" class="login-slide absolute inset-0 w-full h-full object-cover opacity-0 transition-opacity duration-1000">
            <div class="absolute inset-0 bg-gradient-to-t from-[#4A0012]/95 via-[#800020]/74 to-[#4A0012]/82"></div>
        </div>

        <style>
            @keyframes loginKenBurnsInOut {
                0%   { transform: scale(1.10); }
                50%  { transform: scale(1.00); }
                100% { transform: scale(1.10); }
            }
            #loginHeroSlideshow .login-slide.active-slide {
                animation: loginKenBurnsInOut 12s ease-in-out infinite;
            }
        </style>
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                const slides = document.querySelectorAll('#loginHeroSlideshow .login-slide');
                if (slides.length < 2) return;
                let current = 0;
                setInterval(() => {
                    slides[current].classList.remove('opacity-100', 'active-slide');
                    slides[current].classList.add('opacity-0');
                    current = (current + 1) % slides.length;
                    slides[current].classList.remove('opacity-0');
                    slides[current].classList.add('opacity-100', 'active-slide');
                }, 5500);
            });
        </script>

        <!-- Identitas Atas (Pure White 48x48 Logo Box, 0px Sharp) -->
        <div class="relative z-10 flex items-center gap-3.5">
            <div class="w-12 h-12 rounded-none bg-white flex items-center justify-center shrink-0 p-2">
                <img src="{{ asset('images/logo-pin.png') }}" class="w-full h-full object-contain" alt="Logo SI-APPIK">
            </div>
            <div>
                <span class="font-outfit font-bold text-2xl text-white tracking-tight block leading-none">
                    SI-APPIK
                </span>
                <span class="text-xs font-medium text-[#F3E6D5] mt-1 block">
                    Pemerintah Kabupaten &middot; Dinas PUPR
                </span>
            </div>
        </div>

        <!-- Sambutan Utama -->
        <div class="relative z-10 space-y-5 max-w-xl my-auto">
            <div class="w-12 h-[3px] bg-[#D45060]"></div>

            <h1 class="font-outfit font-extrabold text-[30px] xl:text-[36px] leading-[1.18] tracking-tight text-white">
                Portal Terpadu Monitoring &amp; Penanganan Aduan Infrastruktur Kabupaten
            </h1>

            <p class="text-sm xl:text-[15px] leading-relaxed text-[#F3E6D5]">
                Kelola verifikasi aduan warga, disposisi regu lapangan PUPR, serta pelacakan perbaikan jalan, jembatan, dan PJU secara transparan dan real-time.
            </p>
        </div>

        <!-- Hak Cipta Tunggal (Desktop) -->
        <div class="relative z-10 text-xs font-medium text-[#F3E6D5]">
            &copy; 2026 Pemerintah Kabupaten &middot; Dinas Pekerjaan Umum dan Penataan Ruang
        </div>
    </aside>

    <!-- Sisi Kanan Desktop & Single-Column Mobile: Area Formulir Login (#FFF9F2 Warm Ivory Canvas) -->
    <main class="flex flex-col items-center bg-[#FFF9F2] px-5 py-6 sm:px-10 lg:px-12 lg:py-8 min-h-screen lg:min-h-0 lg:h-full overflow-y-auto">
        <div class="w-full max-w-[450px] flex flex-col gap-4 my-auto">

            <!-- Header Ringkas Khusus Mobile -->
            <div class="lg:hidden flex flex-col items-center text-center space-y-2.5 pb-1">
                <div class="w-12 h-12 rounded-none bg-[#800020] p-2 flex items-center justify-center shrink-0">
                    <div class="w-full h-full rounded-none bg-white p-1 flex items-center justify-center">
                        <img src="{{ asset('images/logo-pin.png') }}" class="w-full h-full object-contain" alt="Logo SI-APPIK">
                    </div>
                </div>
                <div>
                    <span class="font-outfit font-bold text-xl text-[#2B0F16] block leading-tight">SI-APPIK</span>
                    <span class="text-xs font-medium text-[#6D4E55] block leading-snug mt-0.5">Pemerintah Kabupaten &middot; Dinas PUPR</span>
                </div>
            </div>

            <!-- Tombol Kembali ke Beranda (Khusus Desktop di Atas Kartu · Warm Sand #F3E6D5, 0px Sharp) -->
            <div class="hidden lg:flex">
                <a href="{{ url('/') }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-none bg-[#F3E6D5] hover:bg-[#E6D5C1] active:scale-[0.98] text-xs font-bold text-[#2B0F16] transition-colors">
                    <i data-lucide="arrow-left" class="w-3.5 h-3.5 text-[#2B0F16]"></i>
                    <span>Kembali ke Beranda</span>
                </a>
            </div>

            <!-- Kartu Login Utama (Pure White 0px Sharp, Top 3px #800020 Accent Bar) -->
            <div class="bg-white rounded-none border-t-[3px] border-t-[#800020] shadow-[0_14px_38px_rgba(74,0,18,0.08)] p-6 sm:p-8 space-y-5">
                <div class="space-y-1.5">
                    <h2 class="font-outfit font-bold text-[24px] sm:text-[26px] text-[#2B0F16] tracking-tight leading-tight">
                        Masuk ke Akun
                    </h2>
                    <p class="text-[13px] text-[#593B42] leading-relaxed">
                        Silakan masuk menggunakan akun Admin, Operator, Kepala Daerah, atau Pekerja Lapangan Dinas PUPR terdaftar.
                    </p>
                </div>

                @if(app()->environment('local', 'development'))
                <!-- Kotak Pilih Cepat Akun Tugas (Hanya Aktif di Environment Lokal) -->
                <div id="quickRolesWrapper" class="bg-[#F3E6D5] rounded-none p-3.5 space-y-2.5">
                    <div class="flex items-center justify-between gap-2">
                        <div class="flex items-center gap-2">
                            <span class="w-2 h-2 rounded-none bg-[#800020] shrink-0"></span>
                            <span class="text-[11px] font-bold tracking-tight text-[#2B0F16]">Pilih Cepat Akun Tugas</span>
                        </div>
                        <button type="button" onclick="toggleQuickRoles()" id="toggleQuickRolesBtn"
                            class="inline-flex items-center gap-1.5 text-[11px] font-bold text-[#800020] hover:text-[#4A0012] bg-[#FFF9F2] px-2.5 py-1 rounded-none transition-colors cursor-pointer">
                            <i id="toggleQuickRolesIcon" data-lucide="eye-off" class="w-3.5 h-3.5"></i>
                            <span id="toggleQuickRolesText">Sembunyikan</span>
                        </button>
                    </div>

                    <div id="quickRolesContent" class="space-y-2">
                        <div class="grid grid-cols-2 gap-2">
                            <button type="button" onclick="selectRole('admin@kabupaten.go.id', 'password', 'Super Admin')"
                                class="role-btn p-2.5 rounded-none bg-[#FFF9F2] hover:bg-[#800020] hover:text-white active:scale-[0.98] text-left transition-colors flex flex-col gap-0.5 cursor-pointer group">
                                <div class="flex items-center gap-1.5 text-[#2B0F16] group-hover:text-white font-bold text-xs">
                                    <i data-lucide="shield" class="w-3.5 h-3.5 text-[#800020] group-hover:text-[#F3E6D5]"></i>
                                    <span>Super Admin</span>
                                </div>
                                <span class="text-[10px] text-[#6D4E55] group-hover:text-[#F3E6D5]">Kendali Penuh</span>
                            </button>

                            <button type="button" onclick="selectRole('operator@kabupaten.go.id', 'password', 'Operator')"
                                class="role-btn p-2.5 rounded-none bg-[#FFF9F2] hover:bg-[#800020] hover:text-white active:scale-[0.98] text-left transition-colors flex flex-col gap-0.5 cursor-pointer group">
                                <div class="flex items-center gap-1.5 text-[#2B0F16] group-hover:text-white font-bold text-xs">
                                    <i data-lucide="clipboard-check" class="w-3.5 h-3.5 text-[#800020] group-hover:text-[#F3E6D5]"></i>
                                    <span>Operator</span>
                                </div>
                                <span class="text-[10px] text-[#6D4E55] group-hover:text-[#F3E6D5]">Verifikasi Laporan</span>
                            </button>

                            <button type="button" onclick="selectRole('bupati@kabupaten.go.id', 'password', 'Kepala Daerah')"
                                class="role-btn p-2.5 rounded-none bg-[#FFF9F2] hover:bg-[#800020] hover:text-white active:scale-[0.98] text-left transition-colors flex flex-col gap-0.5 cursor-pointer group">
                                <div class="flex items-center gap-1.5 text-[#2B0F16] group-hover:text-white font-bold text-xs">
                                    <i data-lucide="landmark" class="w-3.5 h-3.5 text-[#800020] group-hover:text-[#F3E6D5]"></i>
                                    <span>Kepala Daerah</span>
                                </div>
                                <span class="text-[10px] text-[#6D4E55] group-hover:text-[#F3E6D5]">Monitoring Eksekutif</span>
                            </button>

                            <button type="button" onclick="selectRole('petugas@kabupaten.go.id', 'password', 'Pekerja Lap.')"
                                class="role-btn p-2.5 rounded-none bg-[#FFF9F2] hover:bg-[#800020] hover:text-white active:scale-[0.98] text-left transition-colors flex flex-col gap-0.5 cursor-pointer group">
                                <div class="flex items-center gap-1.5 text-[#2B0F16] group-hover:text-white font-bold text-xs">
                                    <i data-lucide="wrench" class="w-3.5 h-3.5 text-[#800020] group-hover:text-[#F3E6D5]"></i>
                                    <span>Pekerja Lap.</span>
                                </div>
                                <span class="text-[10px] text-[#6D4E55] group-hover:text-[#F3E6D5]">Pengerjaan Fisik</span>
                            </button>
                        </div>
                    </div>
                </div>
                @endif

                <!-- Form Input Kredensial -->
                <form id="loginForm" method="POST" action="{{ route('login') }}" onsubmit="handleLoginSubmit(event)" class="space-y-4">
                    @csrf

                    @if(session('success'))
                        <div class="p-3 rounded-none bg-[#ECFDF5] border-l-[3px] border-l-[#047857] text-[#047857] text-xs font-semibold flex items-center gap-2">
                            <i data-lucide="check-circle" class="w-4 h-4 shrink-0"></i>
                            <span>{{ session('success') }}</span>
                        </div>
                    @endif

                    @if($errors->any())
                        <div role="alert" class="p-3 rounded-none bg-[#FEF2F2] border-l-[3px] border-l-[#DC2626] text-[#DC2626] flex items-start gap-2.5">
                            <i data-lucide="alert-circle" class="w-4 h-4 shrink-0 mt-0.5"></i>
                            <p class="text-[13px] font-semibold leading-snug">{{ $errors->first() }}</p>
                        </div>
                    @endif

                    <div class="space-y-1.5">
                        <label for="email" class="block text-[13px] font-semibold text-[#2B0F16]">
                            Email atau Username
                        </label>
                        <div class="relative group">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-[#800020] transition-colors">
                                <i data-lucide="mail" class="w-4 h-4"></i>
                            </div>
                            <input id="email" name="email" type="text" autocomplete="username" required
                                value="{{ old('email', app()->environment('local', 'development') ? 'operator@kabupaten.go.id' : '') }}"
                                placeholder="contoh: operator@kabupaten.go.id"
                                class="block w-full h-[48px] pl-11 pr-4 text-sm text-[#2B0F16] placeholder:text-[#6D4E55] rounded-none border-b-2 border-transparent bg-[#F3E6D5] hover:bg-[#EBDAC5] focus:bg-[#EBDAC5] focus:outline-none focus:border-[#800020] transition-colors">
                        </div>

                        <!-- Banner Pemberitahuan jika Akun Warga Terdeteksi -->
                        <div id="wargaInlineNotice" class="hidden mt-2 px-3.5 py-2.5 rounded-none bg-[#FFF9F2] border-l-[3px] border-l-[#B45309] text-[#2B0F16] text-xs flex items-center justify-between gap-3">
                            <div class="flex items-center gap-2 min-w-0">
                                <i data-lucide="info" class="w-4 h-4 text-[#B45309] shrink-0"></i>
                                <span class="text-[#593B42] leading-snug">
                                    Akun <strong id="wargaDetectedName" class="text-[#2B0F16]"></strong> terdaftar sebagai warga. Gunakan aplikasi mobile SI-APPIK.
                                </span>
                            </div>
                            <button type="button" onclick="openWargaModal(wargaCurrentDetectedName)" class="shrink-0 text-[11px] font-bold text-[#800020] hover:underline whitespace-nowrap cursor-pointer">
                                Unduh App
                            </button>
                        </div>
                    </div>

                    <div class="space-y-1.5">
                        <label for="password" class="block text-[13px] font-semibold text-[#2B0F16]">
                            Kata Sandi
                        </label>
                        <div class="relative group">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-[#800020] transition-colors">
                                <i data-lucide="lock" class="w-4 h-4"></i>
                            </div>
                            <input id="password" name="password" type="password" required
                                value="{{ app()->environment('local', 'development') ? 'password' : '' }}"
                                placeholder="Masukkan kata sandi Anda"
                                class="block w-full h-[48px] pl-11 pr-12 text-sm text-[#2B0F16] placeholder:text-[#6D4E55] rounded-none border-b-2 border-transparent bg-[#F3E6D5] hover:bg-[#EBDAC5] focus:bg-[#EBDAC5] focus:outline-none focus:border-[#800020] transition-colors">
                            <button type="button" id="togglePasswordBtn" onclick="toggleLoginPasswordVisibility()" aria-label="Tampilkan atau sembunyikan kata sandi"
                                class="absolute inset-y-0 right-0 pr-4 flex items-center text-[#593B42] hover:text-[#800020] transition-colors cursor-pointer">
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

                    <div class="flex items-center justify-between text-[13px] pt-0.5">
                        <label class="flex items-center text-[#593B42] cursor-pointer select-none">
                            <input type="checkbox" id="rememberMe" name="remember" value="1" class="w-4 h-4 accent-[#800020] rounded-none cursor-pointer">
                            <span class="ml-2 font-medium text-[#593B42]">Ingat saya</span>
                        </label>
                        <button type="button" onclick="openForgotPasswordModal()" class="font-bold text-[#800020] hover:text-[#4A0012] hover:underline cursor-pointer">
                            Lupa kata sandi?
                        </button>
                    </div>

                    <button type="submit" id="submitLoginBtn"
                        class="w-full h-[48px] px-4 rounded-none bg-[#800020] hover:bg-[#630018] active:scale-[0.99] text-white font-semibold text-[14.5px] transition-colors flex items-center justify-center gap-2 cursor-pointer focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#800020]">
                        <span id="submitBtnText">Masuk</span>
                    </button>

                    <!-- Tombol Beranda Khusus Mobile -->
                    <div class="lg:hidden pt-1">
                        <a href="{{ url('/') }}"
                            class="w-full h-[46px] px-4 rounded-none bg-[#F3E6D5] hover:bg-[#E6D5C1] active:scale-[0.99] text-[#2B0F16] font-semibold text-xs transition-colors flex items-center justify-center gap-2">
                            <i data-lucide="arrow-left" class="w-3.5 h-3.5 text-[#800020]"></i>
                            <span>Kembali ke Beranda</span>
                        </a>
                    </div>
                </form>

                <!-- Callout Informasi Warga (Warm Ivory #FFF9F2, Left 3px #D45060 Bar, 0px Sharp) -->
                <div class="bg-[#FFF9F2] border-l-[3px] border-l-[#D45060] rounded-none px-4 py-3 text-[12px] text-[#593B42] leading-relaxed">
                    Akun warga? Pelaporan kerusakan dilakukan melalui aplikasi mobile SI-APPIK.
                </div>
            </div>

            <!-- Catatan Bawah -->
            <div class="text-center space-y-1">
                <p class="text-[11.5px] font-medium text-[#6D4E55]">
                    Belum memiliki akses akun petugas? Hubungi Admin Dinas PUPR Kabupaten.
                </p>
                <p class="lg:hidden text-[11px] text-[#6D4E55]">
                    &copy; 2026 Pemerintah Kabupaten &middot; Dinas PUPR
                </p>
            </div>

        </div>
    </main>
</div>

<!-- Modal Pengalihan Akun Warga ke Mobile (Maroon & Cream 0px Sharp) -->
<div id="wargaRedirectModal" class="fixed inset-0 z-50 bg-[#4A0012]/65 backdrop-blur-[3px] hidden flex items-center justify-center p-3 sm:p-5 overflow-y-auto overscroll-contain w-full h-full">
    <div class="w-full max-w-md bg-white rounded-none border-t-[4px] border-t-[#800020] shadow-2xl overflow-hidden relative my-auto">

        <!-- Header Modal -->
        <div class="px-6 py-4 border-b border-[#E6D5C1] bg-white flex items-center justify-between gap-3">
            <div class="flex items-center gap-3 min-w-0">
                <div class="w-10 h-10 rounded-none bg-[#F3E6D5] flex items-center justify-center text-[#800020] shrink-0">
                    <i id="modalHeaderIcon" data-lucide="smartphone" class="w-5 h-5 text-[#800020]"></i>
                </div>
                <div class="min-w-0">
                    <h3 id="modalHeaderTitle" class="font-outfit font-bold text-base text-[#2B0F16] tracking-tight leading-snug truncate">
                        Akses Khusus Petugas Kantor
                    </h3>
                    <p id="modalHeaderSub" class="text-[11px] text-[#6D4E55] mt-0.5 leading-snug truncate">
                        Pengaduan warga wajib diakses melalui smartphone
                    </p>
                </div>
            </div>
            <button type="button" onclick="handleSayaMengerti()" aria-label="Tutup"
                class="w-9 h-9 rounded-none bg-[#F3E6D5] hover:bg-[#E6D5C1] text-[#2B0F16] flex items-center justify-center transition-colors cursor-pointer shrink-0">
                <i data-lucide="x" class="w-4 h-4"></i>
            </button>
        </div>

        <div class="p-6 space-y-4">
            <div class="p-3.5 rounded-none bg-[#FFF9F2] border-l-[3px] border-l-[#B45309] text-[#593B42] text-xs leading-relaxed">
                Halo <strong id="wargaCitizenName" class="text-[#2B0F16]">Warga</strong>, akun Anda terdaftar sebagai <strong class="text-[#2B0F16]">Warga Masyarakat</strong>. Pelaporan kerusakan dan pantauan status dilakukan melalui aplikasi mobile SI-APPIK.
            </div>

            @php
                $apkFile = public_path('downloads/SI-APPIK.APK');
                $apkVer = file_exists($apkFile) ? filemtime($apkFile) : time();
                $apkDownloadUrl = route('download.apk', ['v' => $apkVer]);
            @endphp
            <div id="viewMobileContainer" class="space-y-3">
                <p class="text-xs text-[#6D4E55]">
                    Unduh aplikasi resmi SI-APPIK di ponsel Anda:
                </p>

                <div class="flex flex-col gap-2.5 w-full">
                    <a href="{{ $apkDownloadUrl }}" download="SI-APPIK.APK"
                        class="w-full flex items-center justify-center gap-3 px-4 min-h-[48px] rounded-none bg-[#800020] hover:bg-[#630018] text-white transition-colors">
                        <svg class="w-5 h-5 shrink-0 fill-white" viewBox="0 0 24 24">
                            <path d="M4.059 2.046C3.714 2.39 3.5 2.915 3.5 3.612v16.776c0 .697.214 1.222.56 1.566l.08.077 9.4-9.399v-.223l-9.4-9.4-.08.037zM17.067 15.65l-3.427-3.428v-.444l3.427-3.428.077.045 4.062 2.308c1.16.658 1.16 1.737 0 2.396l-4.062 2.307-.077.244zM13.64 11.778L4.208 2.346c.386-.41.986-.445 1.71-.035l11.149 6.335-3.427 3.132zM13.64 12.222l3.427 3.132-11.149 6.335c-.724.41-1.324.375-1.71-.035L13.64 12.222z"/>
                        </svg>
                        <div class="text-left leading-tight">
                            <span class="text-[10px] font-semibold text-[#F3E6D5] uppercase tracking-wider block">Tersedia di</span>
                            <span class="text-sm font-bold text-white">Google Play (APK)</span>
                        </div>
                    </a>

                    <a href="https://apps.apple.com" target="_blank" rel="noopener noreferrer"
                        class="w-full flex items-center justify-center gap-3 px-4 min-h-[48px] rounded-none bg-[#4A0012] hover:bg-[#630018] text-white transition-colors">
                        <svg class="w-5 h-5 shrink-0 fill-white" viewBox="0 0 24 24">
                            <path d="M18.71 19.5c-.83 1.24-1.71 2.45-3.05 2.47-1.34.03-1.77-.79-3.29-.79-1.53 0-2 .77-3.27.82-1.31.05-2.3-1.32-3.14-2.53C4.25 17 2.94 12.45 4.7 9.39c.87-1.52 2.43-2.48 4.12-2.51 1.28-.02 2.5.87 3.29.87.78 0 2.26-1.07 3.81-.91.65.03 2.47.26 3.64 1.98-.09.06-2.17 1.28-2.15 3.81.03 3.02 2.65 4.03 2.68 4.04-.03.07-.42 1.44-1.38 2.83M15.97 6.37c.62-.75 1.04-1.8 1.01-2.87-.96.04-2.12.64-2.8 1.44-.59.68-1.11 1.77-1 2.82 1.07.08 2.17-.55 2.79-1.39z"/>
                        </svg>
                        <div class="text-left leading-tight">
                            <span class="text-[10px] font-semibold text-[#F3E6D5] uppercase tracking-wider block">Tersedia di</span>
                            <span class="text-sm font-bold text-white">App Store</span>
                        </div>
                    </a>
                </div>
            </div>

            <!-- Tampilan Desktop: QR Code -->
            <div id="viewDesktopContainer" class="hidden space-y-3">
                <div class="flex flex-col sm:flex-row items-center gap-4 p-4 rounded-none bg-[#F3E6D5]">
                    <div class="w-24 h-24 p-1.5 bg-white rounded-none shrink-0 flex items-center justify-center">
                        <img src="https://api.qrserver.com/v1/create-qr-code/?size=150x150&color=4A0012&data={{ urlencode($apkDownloadUrl) }}"
                             alt="QR Code Unduh Aplikasi"
                             class="w-full h-full object-contain">
                    </div>
                    <div class="text-center sm:text-left space-y-1">
                        <span class="text-xs font-bold text-[#2B0F16] block">Pindai QR dengan Kamera Ponsel</span>
                        <p class="text-xs text-[#593B42] leading-relaxed">
                            Arahkan kamera ponsel ke QR Code untuk mengunduh aplikasi SI-APPIK.
                        </p>
                    </div>
                </div>
            </div>

            <div class="pt-4 border-t border-[#E6D5C1] flex items-center justify-end">
                <button type="button" onclick="handleSayaMengerti()"
                    class="w-full py-3 px-5 rounded-none bg-[#800020] hover:bg-[#630018] text-white font-bold text-xs uppercase tracking-wider transition-colors cursor-pointer flex items-center justify-center gap-2">
                    <span>Saya Mengerti</span>
                    <i data-lucide="arrow-right" class="w-4 h-4"></i>
                </button>
            </div>
        </div>
    </div>
</div>

<!-- MODAL LUPA KATA SANDI - VERIFIKASI OTP RESMI (Maroon & Cream 0px Sharp) -->
<div id="forgotPasswordModal" class="fixed inset-0 z-50 bg-[#4A0012]/65 backdrop-blur-[3px] hidden flex items-center justify-center p-3 sm:p-5 overflow-y-auto overscroll-contain w-full h-full">
    <div class="w-full max-w-md bg-white rounded-none border-t-[4px] border-t-[#800020] shadow-2xl overflow-hidden relative my-auto">

        <!-- Header Modal -->
        <div class="px-6 py-4 border-b border-[#E6D5C1] bg-white flex items-center justify-between gap-3">
            <div class="flex items-center gap-3 min-w-0">
                <div class="w-10 h-10 rounded-none bg-[#F3E6D5] flex items-center justify-center text-[#800020] shrink-0">
                    <i data-lucide="key-round" class="w-5 h-5"></i>
                </div>
                <div class="min-w-0">
                    <h3 class="font-outfit font-bold text-base text-[#2B0F16] tracking-tight leading-snug truncate">
                        Atur Ulang Kata Sandi
                    </h3>
                    <p class="text-[11px] text-[#6D4E55] mt-0.5 leading-snug truncate">
                        Verifikasi Kode OTP Resmi SI-APPIK Kabupaten
                    </p>
                </div>
            </div>
            <button type="button" onclick="closeForgotPasswordModal()" aria-label="Tutup"
                class="w-9 h-9 rounded-none bg-[#F3E6D5] hover:bg-[#E6D5C1] text-[#2B0F16] flex items-center justify-center transition-colors cursor-pointer shrink-0">
                <i data-lucide="x" class="w-4 h-4"></i>
            </button>
        </div>

        <div class="p-6 space-y-4">

            <!-- Alert Notifikasi -->
            <div id="forgotAlertBox" class="hidden p-3 rounded-none text-xs flex items-start gap-2">
                <i id="forgotAlertIcon" data-lucide="info" class="w-4 h-4 shrink-0 mt-0.5"></i>
                <span id="forgotAlertMsg" class="leading-relaxed"></span>
            </div>

            <!-- TAHAP 1: INPUT ALAMAT EMAIL -->
            <div id="forgotStep1" class="space-y-3.5">
                <p class="text-xs text-[#593B42] leading-relaxed">
                    Masukkan <strong class="text-[#2B0F16]">Alamat Email</strong> kedinasan yang terdaftar pada akun Anda. Sistem akan mengirimkan 6-digit kode OTP keamanan ke alamat email resmi Anda.
                </p>

                <div class="space-y-1.5">
                    <label for="forgotEmailInput" class="block text-xs font-bold uppercase tracking-wider text-[#2B0F16]">Alamat Email</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-[#800020]">
                            <i data-lucide="mail" class="w-4 h-4"></i>
                        </div>
                        <input id="forgotEmailInput" type="email" placeholder="nama@kabupaten.go.id"
                            class="block w-full pl-10 pr-3.5 py-3 text-xs rounded-none border-b-2 border-transparent bg-[#F3E6D5] text-[#2B0F16] placeholder:text-[#6D4E55] focus:outline-none focus:border-[#800020] transition-colors">
                    </div>
                </div>

                <!-- Banner Warga di Modal Lupa Sandi -->
                <div id="forgotWargaInlineNotice" class="hidden px-3.5 py-2.5 rounded-none bg-[#FFF9F2] border-l-[3px] border-l-[#B45309] text-[#2B0F16] text-xs flex items-center justify-between gap-3">
                    <div class="flex items-center gap-2 min-w-0">
                        <i data-lucide="info" class="w-4 h-4 text-[#B45309] shrink-0"></i>
                        <span class="text-[#593B42] leading-snug">
                            Akun <strong id="forgotWargaDetectedName" class="text-[#2B0F16]"></strong> adalah akun warga. Gunakan aplikasi mobile SI-APPIK.
                        </span>
                    </div>
                    <button type="button" onclick="closeForgotPasswordModal(); openWargaModal(forgotWargaCurrentDetectedName);" class="shrink-0 text-[11px] font-bold text-[#800020] hover:underline whitespace-nowrap cursor-pointer">
                        Unduh App
                    </button>
                </div>

                <div class="pt-3 border-t border-[#E6D5C1] flex items-center justify-end gap-2.5">
                    <button type="button" onclick="closeForgotPasswordModal()"
                        class="px-5 py-2.5 rounded-none bg-[#F3E6D5] hover:bg-[#E6D5C1] text-[#2B0F16] font-bold text-xs transition-colors cursor-pointer">
                        Batal
                    </button>
                    <button type="button" id="btnSendForgotOtp" onclick="requestForgotOtp()"
                        class="px-5 py-2.5 rounded-none bg-[#800020] hover:bg-[#630018] text-white font-bold text-xs transition-colors flex items-center justify-center gap-2 cursor-pointer">
                        <i data-lucide="send" class="w-3.5 h-3.5"></i>
                        <span id="btnSendForgotOtpText">Kirim Kode OTP</span>
                    </button>
                </div>
            </div>

            <!-- TAHAP 2: INPUT KODE OTP & KATA SANDI BARU -->
            <div id="forgotStep2" class="hidden space-y-3.5">
                <div class="p-3 rounded-none bg-[#F3E6D5] flex items-center justify-between text-xs">
                    <div class="flex items-center gap-2 overflow-hidden">
                        <i data-lucide="mail-check" class="w-4 h-4 text-[#800020] shrink-0"></i>
                        <span id="forgotTargetEmailDisplay" class="font-bold text-[#2B0F16] truncate text-[11px]">email@kabupaten.go.id</span>
                    </div>
                    <button type="button" onclick="switchForgotStep(1)" class="text-[11px] text-[#800020] hover:text-[#4A0012] font-bold underline shrink-0 cursor-pointer">
                        Ganti
                    </button>
                </div>

                <div class="space-y-1">
                    <label for="forgotOtpInput" class="block text-xs font-bold text-[#2B0F16]">Kode OTP 6 Digit *</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-[#800020]">
                            <i data-lucide="key" class="w-4 h-4"></i>
                        </div>
                        <input id="forgotOtpInput" type="text" maxlength="6" placeholder="Contoh: 123456"
                            class="block w-full pl-10 pr-3.5 py-2.5 text-xs font-mono tracking-widest font-bold rounded-none border-b-2 border-transparent bg-[#F3E6D5] focus:outline-none focus:border-[#800020] transition-colors text-[#2B0F16]">
                    </div>
                </div>

                <div class="space-y-1">
                    <label for="forgotNewPasswordInput" class="block text-xs font-bold text-[#2B0F16]">Kata Sandi Baru *</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-[#800020]">
                            <i data-lucide="lock" class="w-4 h-4"></i>
                        </div>
                        <input id="forgotNewPasswordInput" type="password" minlength="8" placeholder="Minimal 8 karakter..."
                            class="block w-full pl-10 pr-10 py-2.5 text-xs rounded-none border-b-2 border-transparent bg-[#F3E6D5] text-[#2B0F16] focus:outline-none focus:border-[#800020] transition-colors">
                        <button type="button" onclick="togglePassVisibility('forgotNewPasswordInput', 'iconForgotEye1')" class="absolute inset-y-0 right-0 pr-3 flex items-center text-[#593B42] hover:text-[#800020] cursor-pointer">
                            <i id="iconForgotEye1" data-lucide="eye" class="w-4 h-4"></i>
                        </button>
                    </div>
                </div>

                <div class="space-y-1">
                    <label for="forgotConfirmPasswordInput" class="block text-xs font-bold text-[#2B0F16]">Konfirmasi Kata Sandi Baru *</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-[#800020]">
                            <i data-lucide="check-check" class="w-4 h-4"></i>
                        </div>
                        <input id="forgotConfirmPasswordInput" type="password" minlength="8" placeholder="Ulangi kata sandi baru..."
                            class="block w-full pl-10 pr-10 py-2.5 text-xs rounded-none border-b-2 border-transparent bg-[#F3E6D5] text-[#2B0F16] focus:outline-none focus:border-[#800020] transition-colors">
                        <button type="button" onclick="togglePassVisibility('forgotConfirmPasswordInput', 'iconForgotEye2')" class="absolute inset-y-0 right-0 pr-3 flex items-center text-[#593B42] hover:text-[#800020] cursor-pointer">
                            <i id="iconForgotEye2" data-lucide="eye" class="w-4 h-4"></i>
                        </button>
                    </div>
                </div>

                <div class="p-3 rounded-none bg-[#FFF9F2] border-l-[3px] border-l-[#800020] text-[10.5px] text-[#593B42] flex items-start gap-2">
                    <i data-lucide="shield-check" class="w-3.5 h-3.5 text-[#800020] shrink-0 mt-0.5"></i>
                    <span>Wajib minimal <strong class="text-[#2B0F16]">8 karakter</strong> kombinasi <strong class="text-[#2B0F16]">huruf besar (A-Z)</strong>, <strong class="text-[#2B0F16]">huruf kecil (a-z)</strong>, dan <strong class="text-[#2B0F16]">angka (0-9)</strong>.</span>
                </div>

                <div class="pt-3 border-t border-[#E6D5C1] flex items-center justify-end gap-2.5">
                    <button type="button" onclick="closeForgotPasswordModal()"
                        class="px-5 py-2.5 rounded-none bg-[#F3E6D5] hover:bg-[#E6D5C1] text-[#2B0F16] font-bold text-xs transition-colors cursor-pointer">
                        Batal
                    </button>
                    <button type="button" id="btnResetForgotPass" onclick="submitResetPassword()"
                        class="px-5 py-2.5 rounded-none bg-[#800020] hover:bg-[#630018] text-white font-bold text-xs transition-colors flex items-center justify-center gap-2 cursor-pointer">
                        <i data-lucide="check-circle" class="w-4 h-4"></i>
                        <span id="btnResetForgotPassText">Simpan Kata Sandi Baru</span>
                    </button>
                </div>
            </div>

        </div>
    </div>
</div>

<script>
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
            headerTitle.textContent = 'Portal Khusus Petugas';
            headerSub.textContent = 'Layanan aduan warga dialihkan ke aplikasi mobile';
            headerIcon.setAttribute('data-lucide', 'smartphone');
        } else {
            viewMobile.classList.add('hidden');
            viewDesktop.classList.remove('hidden');
            headerTitle.textContent = 'Akses Khusus Petugas Kantor';
            headerSub.textContent = 'Pengaduan warga wajib diakses melalui smartphone';
            headerIcon.setAttribute('data-lucide', 'laptop');
        }

        if (window.lucide) lucide.createIcons();

        const modal = document.getElementById('wargaRedirectModal');
        modal.classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    }

    function handleSayaMengerti() {
        window.location.href = "{{ url('/') }}";
    }

    function closeWargaModal() {
        handleSayaMengerti();
    }

    let verifiedForgotEmail = '';
    let forgotWargaCurrentDetectedName = 'Warga';

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
        if (modal) modal.classList.add('hidden');
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
            ? 'p-3 rounded-none text-xs flex items-start gap-2 bg-[#ECFDF5] border-l-[3px] border-l-[#047857] text-[#047857] font-semibold'
            : 'p-3 rounded-none text-xs flex items-start gap-2 bg-[#FEF2F2] border-l-[3px] border-l-[#DC2626] text-[#DC2626] font-semibold';

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
                forgotWargaCurrentDetectedName = data.name || 'Warga';
                showForgotAlert(data.message || 'Portal website ini khusus untuk Operator, Pekerja Lapangan, dan Kepala Daerah. Akun Warga Masyarakat silakan gunakan Aplikasi Mobile SI-APPIK.');
                setTimeout(() => {
                    closeForgotPasswordModal();
                    openWargaModal(forgotWargaCurrentDetectedName);
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

    async function handleLoginSubmit(event) {
        if (event) event.preventDefault();

        const emailInput = document.getElementById('email');
        const email = emailInput ? emailInput.value.trim() : '';

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
        const origText = btnText ? btnText.textContent : 'Masuk';

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

        if (btnText) btnText.textContent = origText;
        HTMLFormElement.prototype.submit.call(document.getElementById('loginForm'));
    }

    function selectRole(email, password) {
        document.getElementById('email').value = email;
        document.getElementById('password').value = password;
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
            } catch (e) {}
        }, 350);
    }

    document.addEventListener('DOMContentLoaded', function() {
        const rememberedLogin = localStorage.getItem('si_appik_remembered_login');
        const emailInput = document.getElementById('email');
        const rememberCheckbox = document.getElementById('rememberMe');
        if (rememberedLogin && emailInput && rememberCheckbox) {
            emailInput.value = rememberedLogin;
            rememberCheckbox.checked = true;
        }

        if (emailInput) {
            emailInput.addEventListener('input', e => checkWargaLive(e.target.value));
            emailInput.addEventListener('blur', e => checkWargaLive(e.target.value));
            if (emailInput.value) checkWargaLive(emailInput.value);
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

        window.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') closeForgotPasswordModal();
        });

        const forgotModal = document.getElementById('forgotPasswordModal');
        if (forgotModal) {
            forgotModal.addEventListener('click', function(e) {
                if (e.target === forgotModal) closeForgotPasswordModal();
            });
        }

        @if(session('warga_blocked'))
            openWargaModal("{{ session('citizen_name', 'Masyarakat') }}");
        @endif
    });
</script>
@endsection
