<!DOCTYPE html>
<html lang="id" class="h-full bg-[#F8FAFC] scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="format-detection" content="telephone=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'SI-APPIK')</title>
    <link rel="icon" type="image/png" href="{{ asset('images/logo-pin.png') }}?v=3">
    <link rel="shortcut icon" type="image/png" href="{{ asset('images/logo-pin.png') }}?v=3">
    <link rel="apple-touch-icon" href="{{ asset('images/logo-pin.png') }}?v=3">

    <!-- Google Fonts: Inter & Outfit -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Outfit:wght@500;600;700;800&display=swap" rel="stylesheet">

    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                        outfit: ['Outfit', 'sans-serif'],
                    },
                    colors: {
                        primary: '#1E3A8A',    /* Mobile AppColors.primary (Deep Slate Blue) */
                        primaryLight: '#3B82F6',
                        primaryDark: '#172554',
                        secondary: '#0D9488',  /* Teal */
                        accent: '#F59E0B',     /* Amber */
                        textPrimary: '#0F172A',
                        textSecondary: '#64748B',
                        borderSlate: '#E2E8F0',
                        bgSlate: '#F8FAFC',
                    }
                }
            }
        }
    </script>

    <!-- Lucide Icons CDN -->
    <script src="https://unpkg.com/lucide@latest"></script>

    <!-- Leaflet GIS Map CSS & JS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

    <style>
        body {
            background-color: #F8FAFC;
            color: #0F172A;
            font-family: 'Inter', sans-serif;
        }
        .card-white {
            background: #FFFFFF;
            border: 1px solid #E2E8F0;
            box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.04);
            transition: all 0.15s ease-in-out;
        }
        .no-scrollbar::-webkit-scrollbar {
            display: none;
        }
        .no-scrollbar {
            -ms-overflow-style: none;
            scrollbar-width: none;
        }
        /* Custom map popup matching official government civic clarity */
        .leaflet-popup-content-wrapper {
            border-radius: 12px !important;
            padding: 0 !important;
            background: #FFFFFF !important;
            box-shadow: 0 4px 12px rgba(15, 23, 42, 0.12) !important;
            border: 1px solid #E2E8F0 !important;
            overflow: hidden !important;
        }
        .leaflet-popup-content {
            margin: 0 !important;
            line-height: 1.4 !important;
            background: #FFFFFF !important;
            padding: 0 !important;
            width: 280px !important;
            box-sizing: border-box !important;
        }
        .leaflet-popup-tip-container {
            margin-top: -1px !important;
        }
        .leaflet-popup-tip {
            background: #FFFFFF !important;
            box-shadow: 0 2px 6px rgba(15, 23, 42, 0.1) !important;
        }
        .leaflet-container a.leaflet-popup-close-button {
            top: 8px !important;
            right: 8px !important;
            width: 28px !important;
            height: 28px !important;
            background: #FFFFFF !important;
            border-radius: 9999px !important;
            box-shadow: 0 1px 3px rgba(15, 23, 42, 0.15) !important;
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
            color: #0F172A !important;
            font: bold 14px/28px Arial, sans-serif !important;
            z-index: 30 !important;
            border: 1px solid #E2E8F0 !important;
            padding: 0 !important;
        }
        .leaflet-container a.leaflet-popup-close-button:hover {
            background: #FFFFFF !important;
            color: #EF4444 !important;
        }
    </style>
</head>
<body class="h-full bg-[#F8FAFC] antialiased overflow-x-hidden">

@php
    // Cek apakah halaman ini adalah halaman publik (Landing / Login)
    $isPublicPage = request()->is('/') || request()->routeIs('home') || request()->routeIs('login');
    $isLoggedIn = auth()->check();
    // Sidebar hanya muncul jika pengguna SUDAH LOGIN dan BUKAN di halaman login
    $showSidebar = $isLoggedIn && !request()->routeIs('login');
@endphp

@if($showSidebar)
    <!-- Panel Internal (Sidebar) -->
    <div class="min-h-full flex flex-col lg:flex-row">
        <!-- Backdrop for Mobile Sidebar -->
        <div id="sidebarBackdrop" onclick="toggleSidebar()" class="fixed inset-0 bg-slate-900/50 z-40 hidden lg:hidden transition-opacity"></div>

        <!-- Left Sidebar Navigation -->
        <aside id="mainSidebar" class="fixed inset-y-0 left-0 z-50 w-[264px] bg-white border-r border-[#E2E8F0] flex flex-col justify-between transform -translate-x-full lg:translate-x-0 transition-transform duration-200 ease-in-out shrink-0">
            <div>
                <!-- Brand Top -->
                <div class="h-16 flex items-center px-4 border-b border-[#E2E8F0] gap-3">
                    <div class="w-9 h-9 rounded-lg bg-[#F8FAFC] border border-[#E2E8F0] flex items-center justify-center shrink-0">
                        <img src="{{ asset('images/logo-pin.png') }}" class="w-6 h-6 object-contain" alt="Logo SI-APPIK">
                    </div>
                    <div class="leading-none min-w-0">
                        <a href="{{ url('/') }}" class="font-outfit font-bold text-base block hover:opacity-90 transition text-[#0F172A] tracking-tight">
                            SI-APPIK
                        </a>
                        <span class="text-[10px] text-[#475569] font-semibold uppercase tracking-wider block mt-1">Kab. Kolaka Utara</span>
                    </div>
                </div>

                <!-- Navigation List -->
                <nav class="p-3 space-y-1">
                    <div class="px-3 pt-2 pb-1 text-[10px] font-bold uppercase tracking-wider text-[#64748B]">
                        Menu Utama
                    </div>

                    <a href="{{ url('/') }}" class="flex items-center gap-2.5 px-3 py-2.5 rounded-lg text-xs font-semibold {{ request()->is('/') ? 'bg-[#1E3A8A] text-white' : 'text-[#475569] hover:bg-[#F8FAFC] hover:text-[#0F172A]' }} transition">
                        <i data-lucide="map" class="w-4 h-4 shrink-0 {{ request()->is('/') ? 'text-white' : 'text-[#64748B]' }}"></i>
                        <span class="truncate">Peta Sebaran Laporan</span>
                    </a>

                    <div class="px-3 pt-3.5 pb-1 text-[10px] font-bold uppercase tracking-wider text-[#64748B]">
                        Portal Kerja
                    </div>

                    @if(auth()->user()->isSuperAdmin())
                        <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-2.5 px-3 py-2.5 rounded-lg text-xs font-semibold {{ request()->routeIs('admin.*') ? 'bg-[#1E3A8A] text-white' : 'text-[#475569] hover:bg-[#F8FAFC] hover:text-[#0F172A]' }} transition">
                            <i data-lucide="shield" class="w-4 h-4 shrink-0 {{ request()->routeIs('admin.*') ? 'text-white' : 'text-[#64748B]' }}"></i>
                            <span class="truncate">Pusat Kendali Super Admin</span>
                        </a>
                    @endif

                    @if(auth()->user()->isOperator() || auth()->user()->isSuperAdmin())
                        <a href="{{ route('operator.dashboard') }}" class="flex items-center gap-2.5 px-3 py-2.5 rounded-lg text-xs font-semibold {{ request()->routeIs('operator.*') ? 'bg-[#1E3A8A] text-white' : 'text-[#475569] hover:bg-[#F8FAFC] hover:text-[#0F172A]' }} transition">
                            <i data-lucide="clipboard-check" class="w-4 h-4 shrink-0 {{ request()->routeIs('operator.*') ? 'text-white' : 'text-[#64748B]' }}"></i>
                            <span class="truncate">Pusat Kendali Operator</span>
                        </a>
                    @endif

                    @if(auth()->user()->isBupati() || auth()->user()->isSuperAdmin())
                        <a href="{{ route('bupati.dashboard') }}" class="flex items-center gap-2.5 px-3 py-2.5 rounded-lg text-xs font-semibold {{ request()->routeIs('bupati.*') ? 'bg-[#1E3A8A] text-white' : 'text-[#475569] hover:bg-[#F8FAFC] hover:text-[#0F172A]' }} transition">
                            <i data-lucide="landmark" class="w-4 h-4 shrink-0 {{ request()->routeIs('bupati.*') ? 'text-white' : 'text-[#64748B]' }}"></i>
                            <span class="truncate">Pusat Kendali Kepala Daerah</span>
                        </a>
                    @endif

                    @if(auth()->user()->isPetugas() || auth()->user()->isSuperAdmin())
                        <a href="{{ route('petugas.dashboard') }}" class="flex items-center gap-2.5 px-3 py-2.5 rounded-lg text-xs font-semibold {{ request()->routeIs('petugas.*') ? 'bg-[#1E3A8A] text-white' : 'text-[#475569] hover:bg-[#F8FAFC] hover:text-[#0F172A]' }} transition">
                            <i data-lucide="wrench" class="w-4 h-4 shrink-0 {{ request()->routeIs('petugas.*') ? 'text-white' : 'text-[#64748B]' }}"></i>
                            <span class="truncate">Pusat Kendali Pekerja Lapangan</span>
                        </a>
                    @endif
                </nav>
            </div>

            <!-- Profile Tile -->
            <div class="p-3 border-t border-[#E2E8F0] bg-[#F8FAFC]">
                <div class="p-2.5 rounded-lg bg-white border border-[#E2E8F0] flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-lg bg-[#1E3A8A] text-white flex items-center justify-center font-outfit font-bold text-xs shrink-0">
                        {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                    </div>
                    <div class="min-w-0">
                        <span class="block text-xs font-bold text-[#0F172A] truncate">{{ auth()->user()->name }}</span>
                        <span class="block text-[10px] font-semibold text-[#1E3A8A] uppercase tracking-wide">{{ auth()->user()->role }}</span>
                    </div>
                </div>
            </div>
        </aside>

        <!-- Content Area -->
        <div class="flex-1 flex flex-col min-w-0 lg:pl-[264px]">
            <!-- Top Header Bar -->
            <header class="sticky top-0 z-30 bg-white border-b border-[#E2E8F0] h-16 flex items-center justify-between px-4 sm:px-6">
                <div class="flex items-center gap-3">
                    <button onclick="toggleSidebar()" class="lg:hidden p-2 rounded-lg text-[#475569] hover:text-[#0F172A] hover:bg-[#F8FAFC] border border-[#E2E8F0] transition">
                        <i data-lucide="menu" class="w-5 h-5"></i>
                    </button>
                    <h1 class="font-outfit font-bold text-sm sm:text-base text-[#0F172A]">
                        @yield('header_title', 'Sistem Pengaduan')
                    </h1>
                </div>

                <div class="flex items-center gap-3">
                    <a href="{{ route('logout') }}" class="px-3 py-1.5 rounded-lg bg-[#F8FAFC] hover:bg-red-50 text-[#475569] hover:text-red-600 text-xs font-semibold transition flex items-center gap-1.5 border border-[#E2E8F0]">
                        <i data-lucide="log-out" class="w-3.5 h-3.5"></i>
                        <span>Keluar</span>
                    </a>
                </div>
            </header>

            <!-- Alerts -->
            <div class="max-w-[1600px] w-full mx-auto px-4 sm:px-6 lg:px-8 pt-4">
                @if(session('success'))
                    <div class="p-3.5 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs flex items-center justify-between shadow-xs mb-3">
                        <div class="flex items-center gap-2">
                            <i data-lucide="check-circle" class="w-4 h-4 text-emerald-600 shrink-0"></i>
                            <span>{{ session('success') }}</span>
                        </div>
                        <button onclick="this.parentElement.remove()" class="text-emerald-500 hover:text-emerald-700">✕</button>
                    </div>
                @endif

                @if(session('info'))
                    <div class="p-3.5 rounded-xl bg-blue-50 border border-blue-200 text-blue-800 text-xs flex items-center justify-between shadow-xs mb-3">
                        <div class="flex items-center gap-2">
                            <i data-lucide="info" class="w-4 h-4 text-blue-600 shrink-0"></i>
                            <span>{{ session('info') }}</span>
                        </div>
                        <button onclick="this.parentElement.remove()" class="text-blue-500 hover:text-blue-700">✕</button>
                    </div>
                @endif

                @if(session('error'))
                    <div class="p-3.5 rounded-xl bg-red-50 border border-red-200 text-red-800 text-xs flex items-center justify-between shadow-xs mb-3">
                        <div class="flex items-center gap-2">
                            <i data-lucide="alert-circle" class="w-4 h-4 text-red-600 shrink-0"></i>
                            <span>{{ session('error') }}</span>
                        </div>
                        <button onclick="this.parentElement.remove()" class="text-red-500 hover:text-red-700">✕</button>
                    </div>
                @endif
            </div>

            <!-- Main Page Content -->
            <main class="flex-1 w-full max-w-[1600px] mx-auto px-4 sm:px-6 lg:px-8 pb-12">
                @yield('content')
            </main>

            <footer class="border-t border-[#E2E8F0] bg-white py-4 px-6 text-center text-xs text-slate-600">
                © 2026 Pemerintah Kabupaten Kolaka Utara • Dinas Pekerjaan Umum dan Penataan Ruang (PUPR)
            </footer>
        </div>
    </div>

@else
    <!-- Halaman Publik (Tanpa Sidebar) -->
    <div class="min-h-full flex flex-col justify-between">
        @if(!request()->routeIs('login'))
            <!-- Public Top Navbar (Putih Bersih, Proporsional & Responsif dengan Burger Menu) -->
            <header class="sticky top-0 z-40 bg-white text-[#0F172A] border-b border-[#E2E8F0] w-full">
                <div class="max-w-[1440px] mx-auto px-5 sm:px-8 lg:px-10 h-16 flex items-center justify-between gap-4">
                    <!-- Brand & Kepanjangan -->
                    <a href="{{ url('/') }}#top" class="flex items-center gap-3 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#1E3A8A] rounded-lg min-w-0">
                        <div class="w-9 h-9 rounded-lg bg-white border border-[#E2E8F0] flex items-center justify-center p-1 shrink-0">
                            <img src="{{ asset('images/logo-pin.png') }}" class="w-full h-full object-contain" alt="Logo SI-APPIK">
                        </div>
                        <div class="leading-none min-w-0">
                            <span class="font-outfit font-bold text-lg sm:text-xl text-[#0F172A] tracking-tight block">
                                SI-APPIK
                            </span>
                            <span class="hidden lg:block text-[11px] text-[#475569] font-medium mt-0.5 truncate">
                                Sistem Informasi Aduan &amp; Penanganan Prasarana Infrastruktur Kabupaten Kolaka Utara
                            </span>
                        </div>
                    </a>

                    <!-- Nav Links + CTA + Mobile Burger -->
                    <div class="flex items-center gap-3 lg:gap-6 shrink-0">
                        <nav id="publicDesktopNav" class="hidden md:flex items-center gap-5 lg:gap-6 text-[13px] font-semibold text-[#475569]" aria-label="Navigasi Utama">
                            <a href="{{ url('/') }}#top" data-nav-target="top" class="public-nav-link relative py-1.5 text-[#1E3A8A] border-b-2 border-[#1E3A8A] transition-colors duration-200">Beranda</a>
                            <a href="{{ url('/') }}#mapSectionContainer" data-nav-target="mapSectionContainer" class="public-nav-link relative py-1.5 border-b-2 border-transparent hover:text-[#1E3A8A] hover:border-[#93C5FD] transition-colors duration-200">Peta Sebaran</a>
                            <a href="{{ url('/') }}#kategori-infrastruktur" data-nav-target="kategori-infrastruktur" class="public-nav-link relative py-1.5 border-b-2 border-transparent hover:text-[#1E3A8A] hover:border-[#93C5FD] transition-colors duration-200 whitespace-nowrap">Kategori &amp; Layanan</a>
                            <a href="{{ url('/') }}#download-app" data-nav-target="download-app" class="public-nav-link relative py-1.5 border-b-2 border-transparent hover:text-[#1E3A8A] hover:border-[#93C5FD] transition-colors duration-200">Unduh Aplikasi</a>
                        </nav>

                        @auth
                            <a href="{{ auth()->user()->isSuperAdmin() ? route('admin.dashboard') : (auth()->user()->isOperator() ? route('operator.dashboard') : (auth()->user()->isBupati() ? route('bupati.dashboard') : route('petugas.dashboard'))) }}" 
                                class="px-4 h-10 rounded-lg bg-[#1E3A8A] hover:bg-[#172554] text-white font-semibold text-xs sm:text-[13px] transition inline-flex items-center gap-2 whitespace-nowrap shrink-0 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#1E3A8A]">
                                <i data-lucide="layout-dashboard" class="w-4 h-4 shrink-0"></i>
                                <span class="whitespace-nowrap">Dashboard Petugas</span>
                            </a>
                        @else
                            <a href="{{ route('login') }}" title="Masuk" aria-label="Masuk"
                                class="w-10 md:w-auto md:px-4 h-10 rounded-lg bg-[#1E3A8A] hover:bg-[#172554] text-white font-semibold text-xs sm:text-[13px] transition inline-flex items-center justify-center gap-1.5 whitespace-nowrap shrink-0 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#1E3A8A]">
                                <i data-lucide="external-link" class="w-4 h-4 shrink-0"></i>
                                <span class="hidden md:inline whitespace-nowrap">Masuk</span>
                            </a>
                        @endauth

                        <!-- Burger Menu Button (Mobile / Tablet) -->
                        <button type="button" id="publicBurgerBtn" onclick="togglePublicMobileMenu()" aria-label="Buka Menu Navigasi" aria-expanded="false" aria-controls="publicMobileMenu"
                            class="md:hidden w-10 h-10 rounded-lg border border-[#E2E8F0] bg-white hover:bg-[#F8FAFC] text-[#0F172A] flex items-center justify-center transition cursor-pointer focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#1E3A8A]">
                            <i id="publicBurgerIcon" data-lucide="menu" class="w-5 h-5"></i>
                        </button>
                    </div>
                </div>

                <!-- Mobile Dropdown Menu -->
                <div id="publicMobileMenu" class="hidden md:hidden bg-white border-t border-[#E2E8F0] px-5 py-3 space-y-1">
                    <a href="{{ url('/') }}#top" data-nav-target="top" onclick="closePublicMobileMenu()" class="public-mobile-link block px-3 py-2.5 rounded-lg text-sm font-semibold text-[#1E3A8A] bg-[#F8FAFC] transition-colors">Beranda</a>
                    <a href="{{ url('/') }}#mapSectionContainer" data-nav-target="mapSectionContainer" onclick="closePublicMobileMenu()" class="public-mobile-link block px-3 py-2.5 rounded-lg text-sm font-medium text-[#0F172A] hover:bg-[#F8FAFC] hover:text-[#1E3A8A] transition-colors">Peta Sebaran</a>
                    <a href="{{ url('/') }}#kategori-infrastruktur" data-nav-target="kategori-infrastruktur" onclick="closePublicMobileMenu()" class="public-mobile-link block px-3 py-2.5 rounded-lg text-sm font-medium text-[#0F172A] hover:bg-[#F8FAFC] hover:text-[#1E3A8A] transition-colors">Kategori &amp; Layanan</a>
                    <a href="{{ url('/') }}#download-app" data-nav-target="download-app" onclick="closePublicMobileMenu()" class="public-mobile-link block px-3 py-2.5 rounded-lg text-sm font-medium text-[#0F172A] hover:bg-[#F8FAFC] hover:text-[#1E3A8A] transition-colors">Unduh Aplikasi</a>
                </div>
            </header>
            <script>
                function togglePublicMobileMenu() {
                    const menu = document.getElementById('publicMobileMenu');
                    const btn = document.getElementById('publicBurgerBtn');
                    const icon = document.getElementById('publicBurgerIcon');
                    if (!menu || !btn) return;
                    const isOpen = !menu.classList.contains('hidden');
                    menu.classList.toggle('hidden', isOpen);
                    btn.setAttribute('aria-expanded', isOpen ? 'false' : 'true');
                    if (icon && window.lucide) {
                        icon.setAttribute('data-lucide', isOpen ? 'menu' : 'x');
                        lucide.createIcons({ root: btn });
                    }
                }
                function closePublicMobileMenu() {
                    const menu = document.getElementById('publicMobileMenu');
                    const btn = document.getElementById('publicBurgerBtn');
                    const icon = document.getElementById('publicBurgerIcon');
                    if (menu) menu.classList.add('hidden');
                    if (btn) btn.setAttribute('aria-expanded', 'false');
                    if (icon && window.lucide) {
                        icon.setAttribute('data-lucide', 'menu');
                        lucide.createIcons({ root: btn });
                    }
                }

                // Scroll-Spy Navbar (Desktop & Mobile)
                document.addEventListener('DOMContentLoaded', () => {
                    const sectionIds = ['top', 'mapSectionContainer', 'kategori-infrastruktur', 'download-app'];
                    const sections = sectionIds.map(id => document.getElementById(id)).filter(Boolean);
                    if (!sections.length) return;

                    const setActiveNav = (activeId) => {
                        document.querySelectorAll('.public-nav-link').forEach(link => {
                            const isMatch = link.getAttribute('data-nav-target') === activeId;
                            if (isMatch) {
                                link.classList.add('text-[#1E3A8A]', 'border-[#1E3A8A]');
                                link.classList.remove('border-transparent');
                            } else {
                                link.classList.remove('text-[#1E3A8A]', 'border-[#1E3A8A]');
                                link.classList.add('border-transparent');
                            }
                        });
                        document.querySelectorAll('.public-mobile-link').forEach(link => {
                            const isMatch = link.getAttribute('data-nav-target') === activeId;
                            if (isMatch) {
                                link.classList.add('text-[#1E3A8A]', 'bg-[#F8FAFC]', 'font-semibold');
                                link.classList.remove('text-[#0F172A]', 'font-medium');
                            } else {
                                link.classList.remove('text-[#1E3A8A]', 'bg-[#F8FAFC]', 'font-semibold');
                                link.classList.add('text-[#0F172A]', 'font-medium');
                            }
                        });
                    };

                    const updateOnScroll = () => {
                        let currentId = 'top';
                        for (const sec of sections) {
                            const rect = sec.getBoundingClientRect();
                            if (rect.top <= 140) {
                                currentId = sec.id;
                            }
                        }
                        const docHeight = document.documentElement.scrollHeight;
                        if (window.scrollY > 100 && docHeight > window.innerHeight + 100 && (window.innerHeight + window.scrollY) >= docHeight - 30) {
                            currentId = sections[sections.length - 1].id;
                        }
                        setActiveNav(currentId);
                    };

                    document.querySelectorAll('.public-nav-link, .public-mobile-link').forEach(link => {
                        link.addEventListener('click', (e) => {
                            const targetId = link.getAttribute('data-nav-target');
                            const targetEl = targetId ? document.getElementById(targetId) : null;
                            if (targetEl && (window.location.pathname === '/' || window.location.pathname === '')) {
                                e.preventDefault();
                                setActiveNav(targetId);
                                targetEl.scrollIntoView({ behavior: 'smooth', block: 'start' });
                                if (history.replaceState) {
                                    history.replaceState(null, '', '#' + targetId);
                                }
                            } else if (targetId) {
                                setActiveNav(targetId);
                            }
                        });
                    });

                    window.addEventListener('scroll', updateOnScroll, { passive: true });
                    document.addEventListener('scroll', updateOnScroll, { passive: true, capture: true });
                    updateOnScroll();
                });
            </script>
        @endif

        <!-- Alerts -->
        @if(session('success') || session('info') || session('error'))
            <div class="max-w-7xl mx-auto px-6 sm:px-8 lg:px-10 pt-4 w-full">
                @if(session('success'))
                    <div class="p-3.5 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs flex items-center justify-between shadow-xs mb-3">
                        <div class="flex items-center gap-2">
                            <i data-lucide="check-circle" class="w-4 h-4 text-emerald-600 shrink-0"></i>
                            <span>{{ session('success') }}</span>
                        </div>
                        <button onclick="this.parentElement.remove()" class="text-emerald-500 hover:text-emerald-700">✕</button>
                    </div>
                @endif

                @if(session('info'))
                    <div class="p-3.5 rounded-xl bg-blue-50 border border-blue-200 text-blue-800 text-xs flex items-center justify-between shadow-xs mb-3">
                        <div class="flex items-center gap-2">
                            <i data-lucide="info" class="w-4 h-4 text-blue-600 shrink-0"></i>
                            <span>{{ session('info') }}</span>
                        </div>
                        <button onclick="this.parentElement.remove()" class="text-blue-500 hover:text-blue-700">✕</button>
                    </div>
                @endif

                @if(session('error'))
                    <div class="p-3.5 rounded-xl bg-red-50 border border-red-200 text-red-800 text-xs flex items-center justify-between shadow-xs mb-3">
                        <div class="flex items-center gap-2">
                            <i data-lucide="alert-circle" class="w-4 h-4 text-red-600 shrink-0"></i>
                            <span>{{ session('error') }}</span>
                        </div>
                        <button onclick="this.parentElement.remove()" class="text-red-500 hover:text-red-700">✕</button>
                    </div>
                @endif
            </div>
        @endif

        <!-- Main Content (Full-width on landing page for hero, container on other pages) -->
        <main class="flex-1 w-full {{ request()->is('/') ? '' : 'max-w-7xl mx-auto px-6 sm:px-8 lg:px-10' }} pb-12">
            @yield('content')
        </main>

        <footer class="border-t border-[#E2E8F0] bg-white py-6 px-6 text-center text-xs text-[#475569]">
            <div class="max-w-7xl mx-auto flex flex-col sm:flex-row items-center justify-between gap-3">
                <span>© 2026 Pemerintah Kabupaten Kolaka Utara • Dinas Pekerjaan Umum dan Penataan Ruang (PUPR)</span>
                <div class="flex items-center gap-4 text-xs font-semibold text-[#475569]">
                    <a href="#download-app" class="text-[#1E3A8A] hover:text-[#172554] flex items-center gap-1.5 transition font-bold">
                        <i data-lucide="smartphone" class="w-3.5 h-3.5 text-[#1E3A8A]"></i>
                        <span>Aplikasi Warga</span>
                    </a>
                    <a href="https://play.google.com" target="_blank" rel="noopener noreferrer" class="hover:text-[#1E3A8A] transition">Google Play</a>
                    <a href="https://www.apple.com/app-store/" target="_blank" rel="noopener noreferrer" class="hover:text-[#1E3A8A] transition">App Store</a>
                </div>
            </div>
        </footer>
    </div>
@endif

    <script>
        lucide.createIcons();

        function toggleSidebar() {
            const sidebar = document.getElementById('mainSidebar');
            const backdrop = document.getElementById('sidebarBackdrop');
            if (sidebar && backdrop) {
                if (sidebar.classList.contains('-translate-x-full')) {
                    sidebar.classList.remove('-translate-x-full');
                    backdrop.classList.remove('hidden');
                } else {
                    sidebar.classList.add('-translate-x-full');
                    backdrop.classList.add('hidden');
                }
            }
        }

        // Jam Realtime Berjalan Akurat Waktu Indonesia Tengah (WITA / Kolaka Utara, UTC+8)
        function updateLiveWitaClock() {
            const clockElements = document.querySelectorAll('.live-wita-clock');
            if (!clockElements.length) return;

            const now = new Date();
            const optionsDate = {
                timeZone: 'Asia/Makassar',
                weekday: 'long',
                day: 'numeric',
                month: 'long',
                year: 'numeric'
            };
            const optionsTime = {
                timeZone: 'Asia/Makassar',
                hour: '2-digit',
                minute: '2-digit',
                second: '2-digit',
                hour12: false
            };

            try {
                const dateStr = new Intl.DateTimeFormat('id-ID', optionsDate).format(now);
                const timeStr = new Intl.DateTimeFormat('id-ID', optionsTime).format(now).replace(/\./g, ':');
                const formatted = `${dateStr} • ${timeStr} WITA`;

                clockElements.forEach(el => {
                    el.textContent = formatted;
                });
            } catch (e) {
                console.error('Error updating live WITA clock:', e);
            }
        }

        document.addEventListener('DOMContentLoaded', function() {
            updateLiveWitaClock();
            setInterval(updateLiveWitaClock, 1000);
        });
    </script>

    @if(auth()->check() && auth()->user()->needsFirstLoginSetup())
    <!-- Modal Aktivasi Login Pertama Kali -->
    <div id="firstLoginModal" class="fixed inset-0 z-[999999] flex items-center justify-center p-4 bg-slate-900/70 select-none overflow-y-auto">
        <div class="bg-white rounded-2xl max-w-md w-full shadow-xl border border-slate-200 overflow-hidden my-auto">
            <!-- Modal Header: Bersih & Rapi Tanpa Kotak Ikon -->
            <div class="p-5 border-b border-slate-100 bg-slate-50/60">
                <div class="flex items-center gap-2">
                    <h3 class="font-outfit font-extrabold text-base text-slate-900">
                        Verifikasi Akun Baru
                    </h3>
                    <span class="px-2 py-0.5 rounded-md bg-blue-100 text-[#1E3A8A] text-[10px] font-bold">
                        {{ auth()->user()->role }}
                    </span>
                </div>
                <p class="text-xs text-slate-500 mt-1 leading-relaxed">
                    Demi keamanan akun dinas, silakan lengkapi no. WhatsApp dan buat kata sandi baru Anda.
                </p>
            </div>

            <!-- Modal Form Body -->
            <form method="POST" action="{{ route('profile.first-login.setup') }}" class="p-5 space-y-4">
                @csrf

                @if($errors->any())
                <div class="p-3 rounded-xl bg-red-50 border border-red-200 text-red-700 text-xs space-y-1">
                    <div class="font-bold flex items-center gap-1.5">
                        <i data-lucide="alert-circle" class="w-4 h-4 text-red-600"></i>
                        <span>Mohon periksa data:</span>
                    </div>
                    <ul class="list-disc list-inside pl-1 text-[11px] space-y-0.5">
                        @foreach($errors->all() as $err)
                            <li>{{ $err }}</li>
                        @endforeach
                    </ul>
                </div>
                @endif

                <input type="hidden" name="latitude" id="firstLoginLat" value="">
                <input type="hidden" name="longitude" id="firstLoginLon" value="">

                <div class="space-y-3">
                    <!-- 1. Nama Lengkap -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">
                            Nama Lengkap <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="name" value="{{ old('name', auth()->user()->name) }}" required
                            placeholder="Nama sesuai SK / identitas"
                            class="w-full px-3 py-2.5 text-xs font-semibold rounded-xl border border-slate-200 bg-white focus:border-[#1E3A8A] focus:ring-1 focus:ring-[#1E3A8A] transition">
                    </div>

                    <!-- 2. Nomor Handphone / WhatsApp (Standar Indonesia) -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">
                            Nomor WhatsApp Aktif <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <input type="tel" name="phone" id="setupPhoneInput"
                                value="{{ old('phone', auth()->user()->phone) }}" required
                                maxlength="13"
                                oninput="this.value = this.value.replace(/[^0-9]/g, '')"
                                placeholder="Contoh: 081234567890"
                                class="w-full px-3 py-2.5 text-xs font-mono font-semibold rounded-xl border border-slate-200 bg-white focus:border-[#1E3A8A] focus:ring-1 focus:ring-[#1E3A8A] transition">
                        </div>
                        <p class="text-[10px] text-slate-400 mt-1">Format standar Indonesia diawali 08 (10-13 digit angka).</p>
                    </div>

                    <!-- 3. Kata Sandi Baru & Ulangi Sandi (Dengan Ikon Mata) -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5 pt-0.5">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">
                                Sandi Baru <span class="text-red-500">*</span>
                            </label>
                            <div class="relative">
                                <input type="password" name="password" id="setupNewPassInput" required minlength="8"
                                    placeholder="Min. 8 karakter (A-Z, a-z, 0-9)"
                                    class="w-full pl-3 pr-9 py-2.5 text-xs rounded-xl border border-slate-200 bg-white focus:border-[#1E3A8A] focus:ring-1 focus:ring-[#1E3A8A] transition">
                                <button type="button" onclick="toggleSetupPass('setupNewPassInput', 'eyeIconSetup1')" class="absolute inset-y-0 right-0 pr-2.5 flex items-center text-slate-400 hover:text-slate-600 cursor-pointer">
                                    <i id="eyeIconSetup1" data-lucide="eye" class="w-4 h-4"></i>
                                </button>
                            </div>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">
                                Ulangi Sandi <span class="text-red-500">*</span>
                            </label>
                            <div class="relative">
                                <input type="password" name="password_confirmation" id="setupConfirmPassInput" required minlength="8"
                                    placeholder="Ulangi sandi baru"
                                    class="w-full pl-3 pr-9 py-2.5 text-xs rounded-xl border border-slate-200 bg-white focus:border-[#1E3A8A] focus:ring-1 focus:ring-[#1E3A8A] transition">
                                <button type="button" onclick="toggleSetupPass('setupConfirmPassInput', 'eyeIconSetup2')" class="absolute inset-y-0 right-0 pr-2.5 flex items-center text-slate-400 hover:text-slate-600 cursor-pointer">
                                    <i id="eyeIconSetup2" data-lucide="eye" class="w-4 h-4"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tombol Aksi: Simetris, Rapi & Seimbang -->
                <div class="pt-3 border-t border-slate-100 grid grid-cols-2 gap-3">
                    <button type="button" onclick="document.getElementById('firstLoginLogoutForm').submit()"
                        class="w-full py-2.5 rounded-xl border border-slate-200 bg-slate-50 hover:bg-slate-100 text-slate-600 font-bold text-xs text-center transition cursor-pointer">
                        Nanti Saja
                    </button>
                    <button type="submit"
                        class="w-full py-2.5 rounded-xl bg-[#1E3A8A] hover:bg-blue-900 text-white font-bold text-xs text-center shadow-xs transition flex items-center justify-center gap-1.5 cursor-pointer">
                        <i data-lucide="check" class="w-4 h-4"></i>
                        <span>Simpan & Aktifkan</span>
                    </button>
                </div>
            </form>

            <!-- Form Logout Tersembunyi untuk Tombol Nanti Saja -->
            <form id="firstLoginLogoutForm" action="{{ route('logout') }}" method="POST" class="hidden">
                @csrf
            </form>
        </div>
    </div>
    <script>
        function toggleSetupPass(inputId, iconId) {
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

        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(function(pos) {
                const latEl = document.getElementById('firstLoginLat');
                const lonEl = document.getElementById('firstLoginLon');
                if (latEl) latEl.value = pos.coords.latitude;
                if (lonEl) lonEl.value = pos.coords.longitude;
            }, function() {}, { timeout: 6000, maximumAge: 300000 });
        }
    </script>
    @endif

    @stack('modals')
    @stack('scripts')
</body>
</html>
