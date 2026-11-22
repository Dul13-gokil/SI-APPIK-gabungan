<!DOCTYPE html>
<html lang="id" class="bg-[#F8FAFC] scroll-smooth overscroll-y-none">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Portal resmi pengaduan dan pemantauan infrastruktur jalan, jembatan, dan fasilitas umum Kabupaten (Dinas PUPR).">
    <meta name="format-detection" content="telephone=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'SI-APPIK')</title>
    @if(request()->routeIs('home'))
        <link rel="preload" as="image" href="{{ asset('images/hero/hero-1.webp') }}" fetchpriority="high">
    @endif
    <link rel="icon" type="image/png" href="{{ asset('images/logo-pin.png') }}?v=6">
    <link rel="shortcut icon" type="image/png" href="{{ asset('images/logo-pin.png') }}?v=6">
    <link rel="apple-touch-icon" href="{{ asset('images/logo-pin.png') }}?v=6">

    <!-- Google Fonts: Inter & Outfit + Preconnect CDN -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="preconnect" href="https://unpkg.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Outfit:wght@600;700;800&display=swap" rel="stylesheet">

    <!-- Tailwind CSS (Precompiled Vite CSS jika tersedia, fallback ke CDN) -->
    @if(file_exists(public_path('build/manifest.json')))
        @vite(['resources/css/app.css'])
    @else
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
                            primary: '#2563EB',
                            primaryLight: '#3B82F6',
                            primaryDark: '#1D4ED8',
                            secondary: '#0D9488',
                            accent: '#F59E0B',
                            textPrimary: '#0F172A',
                            textSecondary: '#64748B',
                            borderSlate: '#E2E8F0',
                            bgSlate: '#F8FAFC',
                        }
                    }
                }
            }
        </script>
    @endif

    <!-- Lucide Icons CDN (Non-blocking) -->
    <script defer src="https://unpkg.com/lucide@latest"></script>

    @if(!request()->routeIs('login'))
        <!-- Leaflet GIS Map CSS (Non-blocking) & JS (Defer) -->
        <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" media="print" onload="this.media='all'" />
        <noscript><link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" /></noscript>
        <script defer src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    @endif

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
        @if(request()->is('/') || request()->routeIs('home'))
        .leaflet-popup-content-wrapper {
            border-radius: 10px !important;
            border: none !important;
            box-shadow: 0 12px 30px rgba(24, 54, 84, 0.18) !important;
        }
        .leaflet-bar {
            border-radius: 8px !important;
            overflow: hidden !important;
            border: none !important;
            box-shadow: 0 4px 14px rgba(24, 54, 84, 0.16) !important;
        }
        .leaflet-bar a {
            border-radius: 0px !important;
            border-color: #E6E2D8 !important;
            color: #183654 !important;
            transition: background-color 0.2s ease, color 0.2s ease !important;
        }
        .leaflet-bar a:hover {
            background-color: #F2EFE7 !important;
            color: #3368A0 !important;
        }
        @endif
        .smooth-ease {
            transition-timing-function: cubic-bezier(0.22, 1, 0.36, 1);
        }
        a, button {
            -webkit-tap-highlight-color: transparent;
        }
        @media (prefers-reduced-motion: reduce) {
            *, *::before, *::after {
                animation-duration: 0.01ms !important;
                animation-iteration-count: 1 !important;
                transition-duration: 0.01ms !important;
                scroll-behavior: auto !important;
            }
        }
    </style>
</head>
<body class="min-h-screen bg-[#F8FAFC] antialiased overflow-x-clip overscroll-y-none">

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

        <!-- Left Sidebar Navigation (Fixed drawer di Mobile, Sticky flex-item di Desktop agar tidak pernah memotong konten) -->
        <aside id="mainSidebar" class="fixed lg:sticky top-0 inset-y-0 left-0 z-50 w-64 lg:h-screen bg-white border-r border-[#E2E8F0] flex flex-col justify-between transform -translate-x-full lg:translate-x-0 transition-transform duration-200 ease-in-out shrink-0">
            <div class="flex-1 min-h-0 flex flex-col">
                <!-- Brand Top -->
                <div class="h-16 flex items-center px-4 border-b border-[#E2E8F0] gap-3 shrink-0">
                    <div class="w-9 h-9 rounded-lg bg-[#F8FAFC] border border-[#E2E8F0] flex items-center justify-center shrink-0">
                        <img src="{{ asset('images/logo-pin.png') }}" class="w-6 h-6 object-contain" alt="Logo SI-APPIK">
                    </div>
                    <div class="leading-none min-w-0">
                        <a href="{{ url('/') }}" class="font-outfit font-bold text-base block hover:opacity-90 transition text-[#0F172A] tracking-tight">
                            SI-APPIK
                        </a>
                        <span class="text-[10px] text-[#475569] font-semibold uppercase tracking-wider block mt-1">Kabupaten</span>
                    </div>
                </div>

                <!-- Navigation List -->
                @php
                    $isAdminRoute = request()->routeIs('admin.*');
                    $isOperatorRoute = request()->routeIs('operator.*');
                    $isBupatiRoute = request()->routeIs('bupati.*');
                    $isPetugasRoute = request()->routeIs('petugas.*');
                    $currentQueryTab = request()->get('tab', $isAdminRoute ? 'overview' : ($isOperatorRoute ? 'verify' : ($isBupatiRoute ? 'executive' : ($isPetugasRoute ? 'spatial' : ''))));
                @endphp
                <nav class="p-3 space-y-1.5 flex-1 overflow-y-auto">
                    <div class="px-3 pt-1 pb-1 text-[10px] font-bold uppercase tracking-wider text-[#64748B]">
                        Menu Utama
                    </div>

                    <a href="{{ url('/') }}" class="flex items-center gap-2.5 px-3 py-2.5 rounded-xl text-xs font-semibold {{ request()->is('/') ? 'bg-[#2563EB] text-white shadow-xs' : 'text-[#475569] hover:bg-[#F8FAFC] hover:text-[#0F172A]' }} transition">
                        <i data-lucide="map" class="w-4 h-4 shrink-0 {{ request()->is('/') ? 'text-white' : 'text-[#64748B]' }}"></i>
                        <span class="truncate">Peta Sebaran Laporan</span>
                    </a>

                    <div class="px-3 pt-3 pb-1 text-[10px] font-bold uppercase tracking-wider text-[#64748B]">
                        Portal Kerja &amp; Role
                    </div>

                    @if(auth()->user()->isSuperAdmin())
                        <div class="role-accordion-group rounded-xl transition">
                            <button type="button" onclick="toggleRoleAccordion('admin')" aria-expanded="{{ $isAdminRoute ? 'true' : 'false' }}"
                                class="w-full flex items-center justify-between gap-2 px-3 py-2.5 rounded-xl text-xs font-bold border {{ $isAdminRoute ? 'bg-[#EFF6FF] text-[#2563EB] border-[#BFDBFE]' : 'bg-white text-[#334155] border-[#E2E8F0] hover:bg-[#F8FAFC] hover:text-[#0F172A]' }} transition">
                                <span class="flex items-center gap-2.5 min-w-0">
                                    <i data-lucide="shield" class="w-4 h-4 shrink-0 {{ $isAdminRoute ? 'text-[#2563EB]' : 'text-[#64748B]' }}"></i>
                                    <span class="truncate">Super Admin</span>
                                </span>
                                <i data-lucide="chevron-down" id="accordionChevron-admin" class="w-3.5 h-3.5 shrink-0 transition-transform duration-200 {{ $isAdminRoute ? 'rotate-180 text-[#2563EB]' : 'text-[#64748B]' }}"></i>
                            </button>
                            <div id="accordionBody-admin" class="{{ $isAdminRoute ? 'block' : 'hidden' }} py-1.5 px-1 space-y-1">
                                @php
                                    $adminMenuItems = [
                                        ['tab' => 'overview', 'label' => 'Dashboard Admin', 'icon' => 'layout-dashboard'],
                                        ['tab' => 'users-management', 'label' => 'Kelola Akun & Pengguna', 'icon' => 'users'],
                                        ['tab' => 'complaints-list', 'label' => 'Daftar Aduan', 'icon' => 'layers'],
                                        ['tab' => 'archive', 'label' => 'Arsip Aduan', 'icon' => 'archive'],
                                        ['tab' => 'admin-rejected', 'label' => 'Aduan Ditolak', 'icon' => 'archive-x'],
                                        ['tab' => 'teams-management', 'label' => 'Regu & Tugas', 'icon' => 'hard-hat'],
                                        ['tab' => 'regions-gis', 'label' => 'Peta Wilayah GIS', 'icon' => 'map-pin'],
                                        ['tab' => 'reporting', 'label' => 'Laporan Kinerja', 'icon' => 'bar-chart-3'],
                                    ];
                                @endphp
                                @foreach($adminMenuItems as $item)
                                    @php
                                        $isSubActive = $isAdminRoute && (
                                            $currentQueryTab === $item['tab'] ||
                                            ($item['tab'] === 'users-management' && $currentQueryTab === 'users-list')
                                        );
                                    @endphp
                                    <a href="{{ route('admin.dashboard', ['tab' => $item['tab']]) }}"
                                       onclick="return handleSidebarTabClick(event, 'admin', '{{ $item['tab'] }}')"
                                       data-sidebar-role="admin" data-sidebar-tab="{{ $item['tab'] }}"
                                       class="sidebar-subitem flex items-center gap-2 px-3 py-2 rounded-xl text-[11px] font-semibold transition {{ $isSubActive ? 'bg-[#2563EB] text-white font-bold shadow-xs' : 'text-[#475569] hover:bg-[#F8FAFC] hover:text-[#0F172A]' }}">
                                        <i data-lucide="{{ $item['icon'] }}" class="w-3.5 h-3.5 shrink-0 {{ $isSubActive ? 'text-white' : 'text-[#64748B]' }}"></i>
                                        <span class="truncate">{{ $item['label'] }}</span>
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    @if(auth()->user()->isOperator() || auth()->user()->isSuperAdmin())
                        <div class="role-accordion-group rounded-xl transition">
                            <button type="button" onclick="toggleRoleAccordion('operator')" aria-expanded="{{ $isOperatorRoute ? 'true' : 'false' }}"
                                class="w-full flex items-center justify-between gap-2 px-3 py-2.5 rounded-xl text-xs font-bold border {{ $isOperatorRoute ? 'bg-[#EFF6FF] text-[#2563EB] border-[#BFDBFE]' : 'bg-white text-[#334155] border-[#E2E8F0] hover:bg-[#F8FAFC] hover:text-[#0F172A]' }} transition">
                                <span class="flex items-center gap-2.5 min-w-0">
                                    <i data-lucide="clipboard-check" class="w-4 h-4 shrink-0 {{ $isOperatorRoute ? 'text-[#2563EB]' : 'text-[#64748B]' }}"></i>
                                    <span class="truncate">Operator PUPR</span>
                                </span>
                                <i data-lucide="chevron-down" id="accordionChevron-operator" class="w-3.5 h-3.5 shrink-0 transition-transform duration-200 {{ $isOperatorRoute ? 'rotate-180 text-[#2563EB]' : 'text-[#64748B]' }}"></i>
                            </button>
                            <div id="accordionBody-operator" class="{{ $isOperatorRoute ? 'block' : 'hidden' }} py-1.5 px-1 space-y-1">
                                @php
                                    $operatorMenuItems = [
                                        ['tab' => 'verify', 'label' => 'Verifikasi Aduan', 'icon' => 'filter'],
                                        ['tab' => 'dispatched', 'label' => 'Ditugaskan', 'icon' => 'send'],
                                        ['tab' => 'inprogress', 'label' => 'Dikerjakan', 'icon' => 'hammer'],
                                        ['tab' => 'validation', 'label' => 'Validasi Hasil', 'icon' => 'check-square'],
                                        ['tab' => 'archive', 'label' => 'Arsip Aduan', 'icon' => 'archive'],
                                        ['tab' => 'regions-gis', 'label' => 'Peta Wilayah GIS', 'icon' => 'map-pin'],
                                        ['tab' => 'monitoring', 'label' => 'Laporan Kinerja', 'icon' => 'bar-chart-3'],
                                    ];
                                @endphp
                                @foreach($operatorMenuItems as $item)
                                    @php $isSubActive = $isOperatorRoute && $currentQueryTab === $item['tab']; @endphp
                                    <a href="{{ route('operator.dashboard', ['tab' => $item['tab']]) }}"
                                       onclick="return handleSidebarTabClick(event, 'operator', '{{ $item['tab'] }}')"
                                       data-sidebar-role="operator" data-sidebar-tab="{{ $item['tab'] }}"
                                       class="sidebar-subitem flex items-center gap-2 px-3 py-2 rounded-xl text-[11px] font-semibold transition {{ $isSubActive ? 'bg-[#2563EB] text-white font-bold shadow-xs' : 'text-[#475569] hover:bg-[#F8FAFC] hover:text-[#0F172A]' }}">
                                        <i data-lucide="{{ $item['icon'] }}" class="w-3.5 h-3.5 shrink-0 {{ $isSubActive ? 'text-white' : 'text-[#64748B]' }}"></i>
                                        <span class="truncate">{{ $item['label'] }}</span>
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    @if(auth()->user()->isBupati() || auth()->user()->isSuperAdmin())
                        <div class="role-accordion-group rounded-xl transition">
                            <button type="button" onclick="toggleRoleAccordion('bupati')" aria-expanded="{{ $isBupatiRoute ? 'true' : 'false' }}"
                                class="w-full flex items-center justify-between gap-2 px-3 py-2.5 rounded-xl text-xs font-bold border {{ $isBupatiRoute ? 'bg-[#EFF6FF] text-[#2563EB] border-[#BFDBFE]' : 'bg-white text-[#334155] border-[#E2E8F0] hover:bg-[#F8FAFC] hover:text-[#0F172A]' }} transition">
                                <span class="flex items-center gap-2.5 min-w-0">
                                    <i data-lucide="landmark" class="w-4 h-4 shrink-0 {{ $isBupatiRoute ? 'text-[#2563EB]' : 'text-[#64748B]' }}"></i>
                                    <span class="truncate">Kepala Daerah</span>
                                </span>
                                <i data-lucide="chevron-down" id="accordionChevron-bupati" class="w-3.5 h-3.5 shrink-0 transition-transform duration-200 {{ $isBupatiRoute ? 'rotate-180 text-[#2563EB]' : 'text-[#64748B]' }}"></i>
                            </button>
                            <div id="accordionBody-bupati" class="{{ $isBupatiRoute ? 'block' : 'hidden' }} py-1.5 px-1 space-y-1">
                                @php
                                    $bupatiMenuItems = [
                                        ['tab' => 'complaints', 'label' => 'Daftar Aduan', 'icon' => 'layers'],
                                        ['tab' => 'archive', 'label' => 'Arsip Aduan', 'icon' => 'archive'],
                                        ['tab' => 'executive', 'label' => 'Peta Wilayah GIS', 'icon' => 'map-pin'],
                                        ['tab' => 'reporting', 'label' => 'Laporan Kinerja', 'icon' => 'bar-chart-3'],
                                    ];
                                @endphp
                                @foreach($bupatiMenuItems as $item)
                                    @php $isSubActive = $isBupatiRoute && $currentQueryTab === $item['tab']; @endphp
                                    <a href="{{ route('bupati.dashboard', ['tab' => $item['tab']]) }}"
                                       onclick="return handleSidebarTabClick(event, 'bupati', '{{ $item['tab'] }}')"
                                       data-sidebar-role="bupati" data-sidebar-tab="{{ $item['tab'] }}"
                                       class="sidebar-subitem flex items-center gap-2 px-3 py-2 rounded-xl text-[11px] font-semibold transition {{ $isSubActive ? 'bg-[#2563EB] text-white font-bold shadow-xs' : 'text-[#475569] hover:bg-[#F8FAFC] hover:text-[#0F172A]' }}">
                                        <i data-lucide="{{ $item['icon'] }}" class="w-3.5 h-3.5 shrink-0 {{ $isSubActive ? 'text-white' : 'text-[#64748B]' }}"></i>
                                        <span class="truncate">{{ $item['label'] }}</span>
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    @if(auth()->user()->isPetugas() || auth()->user()->isSuperAdmin())
                        <div class="role-accordion-group rounded-xl transition">
                            <button type="button" onclick="toggleRoleAccordion('petugas')" aria-expanded="{{ $isPetugasRoute ? 'true' : 'false' }}"
                                class="w-full flex items-center justify-between gap-2 px-3 py-2.5 rounded-xl text-xs font-bold border {{ $isPetugasRoute ? 'bg-[#EFF6FF] text-[#2563EB] border-[#BFDBFE]' : 'bg-white text-[#334155] border-[#E2E8F0] hover:bg-[#F8FAFC] hover:text-[#0F172A]' }} transition">
                                <span class="flex items-center gap-2.5 min-w-0">
                                    <i data-lucide="wrench" class="w-4 h-4 shrink-0 {{ $isPetugasRoute ? 'text-[#2563EB]' : 'text-[#64748B]' }}"></i>
                                    <span class="truncate">Pekerja Lapangan</span>
                                </span>
                                <i data-lucide="chevron-down" id="accordionChevron-petugas" class="w-3.5 h-3.5 shrink-0 transition-transform duration-200 {{ $isPetugasRoute ? 'rotate-180 text-[#2563EB]' : 'text-[#64748B]' }}"></i>
                            </button>
                            <div id="accordionBody-petugas" class="{{ $isPetugasRoute ? 'block' : 'hidden' }} py-1.5 px-1 space-y-1">
                                @php
                                    $petugasMenuItems = [
                                        ['tab' => 'assigned', 'label' => 'Penugasan', 'icon' => 'send'],
                                        ['tab' => 'tasks', 'label' => 'Dikerjakan', 'icon' => 'hard-hat'],
                                        ['tab' => 'validation', 'label' => 'Validasi', 'icon' => 'shield-check'],
                                        ['tab' => 'history', 'label' => 'Tugas Selesai', 'icon' => 'check-circle'],
                                        ['tab' => 'spatial', 'label' => 'Peta Wilayah GIS', 'icon' => 'map-pin'],
                                        ['tab' => 'performance', 'label' => 'Laporan Kinerja', 'icon' => 'bar-chart-3'],
                                    ];
                                @endphp
                                @foreach($petugasMenuItems as $item)
                                    @php $isSubActive = $isPetugasRoute && $currentQueryTab === $item['tab']; @endphp
                                    <a href="{{ route('petugas.dashboard', ['tab' => $item['tab']]) }}"
                                       onclick="return handleSidebarTabClick(event, 'petugas', '{{ $item['tab'] }}')"
                                       data-sidebar-role="petugas" data-sidebar-tab="{{ $item['tab'] }}"
                                       class="sidebar-subitem flex items-center gap-2 px-3 py-2 rounded-xl text-[11px] font-semibold transition {{ $isSubActive ? 'bg-[#2563EB] text-white font-bold shadow-xs' : 'text-[#475569] hover:bg-[#F8FAFC] hover:text-[#0F172A]' }}">
                                        <i data-lucide="{{ $item['icon'] }}" class="w-3.5 h-3.5 shrink-0 {{ $isSubActive ? 'text-white' : 'text-[#64748B]' }}"></i>
                                        <span class="truncate">{{ $item['label'] }}</span>
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </nav>
            </div>

            <!-- Profile Tile -->
            <div class="p-3 border-t border-[#E2E8F0] bg-[#F8FAFC]">
                <div class="p-2.5 rounded-lg bg-white border border-[#E2E8F0] flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-lg bg-[#2563EB] text-white flex items-center justify-center font-outfit font-bold text-xs shrink-0">
                        {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                    </div>
                    <div class="min-w-0">
                        <span class="block text-xs font-bold text-[#0F172A] truncate">{{ auth()->user()->name }}</span>
                        <span class="block text-[10px] font-semibold text-[#2563EB] uppercase tracking-wide">{{ str_replace('_', ' ', auth()->user()->role) }}</span>
                    </div>
                </div>
            </div>
        </aside>

        <!-- Content Area -->
        <div class="flex-1 flex flex-col min-w-0">
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

            <!-- Main Page Content -->
            <main class="flex-1 w-full {{ request()->is('/') ? '' : 'max-w-[1600px] mx-auto px-4 sm:px-6 lg:px-8 pb-12' }}">
                @yield('content')
            </main>

            <footer class="border-t border-[#E2E8F0] bg-white py-4 px-6 text-center text-xs text-slate-600">
                © 2026 Pemerintah Kabupaten • Dinas Pekerjaan Umum dan Penataan Ruang (PUPR)
            </footer>
        </div>
    </div>

@else
    <!-- Halaman Publik (Tanpa Sidebar - SI-APPIK E-Gov Blue & Cream 1:1) -->
    <div class="min-h-full flex flex-col justify-between {{ request()->routeIs('login') ? 'bg-[#F2EFE7]' : 'bg-white' }}">
        @if(!request()->routeIs('login'))
            <!-- Public Top Navbar: Transparent at top (shows hero photo directly), solid #183654 on scroll -->
            <div id="publicNavWrapper" class="fixed top-0 left-0 right-0 z-40 w-full transition-all duration-300 smooth-ease">
                <header id="publicHeader" class="bg-transparent text-white w-full transition-[background-color,backdrop-filter] duration-300 smooth-ease">
                    <div class="max-w-[1280px] mx-auto px-4 sm:px-6 xl:px-0 h-[72px] md:h-[80px] flex items-center justify-between gap-3 sm:gap-4 relative">
                        <!-- Brand & Instansi -->
                        <a href="{{ url('/') }}#top" class="flex items-center gap-3 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-white rounded-[6px] min-w-0 shrink-0 group">
                            <div class="w-9 h-9 rounded-[6px] bg-white flex items-center justify-center p-1.5 shrink-0 transition-transform duration-300 smooth-ease group-hover:scale-[1.04]">
                                <img src="{{ asset('images/logo-pin.png') }}" class="w-full h-full object-contain" alt="Logo SI-APPIK">
                            </div>
                            <div class="leading-none min-w-0">
                                <span class="font-outfit font-bold text-[18px] text-white tracking-[0.2px] block">
                                    SI-APPIK
                                </span>
                                <span class="block text-[11px] sm:text-[11.5px] text-[#F2EFE7] font-normal mt-1 truncate">
                                    <span class="sm:hidden">Kabupaten &bull; Dinas PUPR</span>
                                    <span class="hidden sm:inline">Pemerintah Kabupaten &bull; Dinas PUPR</span>
                                </span>
                            </div>
                        </a>

                        <!-- Desktop Center Nav Links (Bidang Layanan -> Peta Sebaran -> Alur Penanganan -> Aplikasi Mobile) -->
                        <nav id="publicDesktopNav" class="hidden md:flex items-center gap-7 lg:gap-9 h-full text-[13.5px] font-normal text-[#F2EFE7]" aria-label="Navigasi Utama">
                            <a href="{{ url('/') }}#top" data-nav-target="top" class="public-nav-link relative h-full inline-flex items-center font-semibold text-white border-b-2 border-[#66A3BF] transition-colors duration-250 smooth-ease">Beranda</a>
                            <a href="{{ url('/') }}#kategori-infrastruktur" data-nav-target="kategori-infrastruktur" class="public-nav-link relative h-full inline-flex items-center border-b-2 border-transparent hover:text-white transition-colors duration-250 smooth-ease whitespace-nowrap">Bidang Layanan</a>
                            <a href="{{ url('/') }}#mapSectionContainer" data-nav-target="mapSectionContainer" class="public-nav-link relative h-full inline-flex items-center border-b-2 border-transparent hover:text-white transition-colors duration-250 smooth-ease whitespace-nowrap">Peta Sebaran</a>
                            <a href="{{ url('/') }}#alur-penanganan" data-nav-target="alur-penanganan" class="public-nav-link relative h-full inline-flex items-center border-b-2 border-transparent hover:text-white transition-colors duration-250 smooth-ease whitespace-nowrap">Alur Penanganan</a>
                            <a href="{{ url('/') }}#download-app" data-nav-target="download-app" class="public-nav-link relative h-full inline-flex items-center border-b-2 border-transparent hover:text-white transition-colors duration-250 smooth-ease whitespace-nowrap">Aplikasi Mobile</a>
                        </nav>

                        <!-- Right CTA + Mobile Menu -->
                        <div class="flex items-center gap-2 shrink-0">
                            <!-- Mobile Menu Button (Subtle 8px Rounded, 44px Touch Target) -->
                            <button type="button" id="publicBurgerBtn" onclick="togglePublicMobileMenu()" aria-label="Buka Menu Navigasi" aria-expanded="false" aria-controls="publicMobileMenu"
                                class="md:hidden h-[42px] px-4 rounded-[8px] bg-[#224363]/95 hover:bg-[#3368A0] active:scale-[0.97] text-white text-[13px] font-semibold inline-flex items-center justify-center transition-all duration-250 smooth-ease cursor-pointer focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-white">
                                <span id="publicBurgerLabel">Menu</span>
                            </button>

                            @auth
                                <a href="{{ auth()->user()->isSuperAdmin() ? route('admin.dashboard') : (auth()->user()->isOperator() ? route('operator.dashboard') : (auth()->user()->isBupati() ? route('bupati.dashboard') : route('petugas.dashboard'))) }}"
                                    class="px-5 h-[44px] rounded-[8px] bg-white hover:bg-[#F2EFE7] active:scale-[0.98] text-[#183654] font-semibold text-[13.5px] transition-all duration-250 smooth-ease inline-flex items-center gap-2 whitespace-nowrap shrink-0 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#66A3BF]">
                                    <i data-lucide="layout-dashboard" class="w-4 h-4 shrink-0"></i>
                                    <span class="whitespace-nowrap">Dashboard Petugas</span>
                                </a>
                            @else
                                <a href="{{ route('login') }}" title="Masuk Portal" aria-label="Masuk Portal"
                                    class="px-4 md:px-6 h-[42px] md:h-[44px] rounded-[8px] bg-white hover:bg-[#F2EFE7] active:scale-[0.98] text-[#183654] font-semibold text-[13px] md:text-[13.5px] transition-all duration-250 smooth-ease inline-flex items-center justify-center whitespace-nowrap shrink-0 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#66A3BF]">
                                    <span class="md:hidden">Masuk</span>
                                    <span class="hidden md:inline whitespace-nowrap">Masuk Portal</span>
                                </a>
                            @endauth
                        </div>

                        <!-- Quiet Hairline at bottom of transparent top nav (1280px) -->
                        <div id="publicNavHairline" class="hidden md:block absolute left-0 right-0 bottom-0 h-[1px] bg-[#C8DFDB]/25 pointer-events-none transition-opacity duration-300"></div>
                    </div>

                    <!-- Mobile Dropdown Menu (Frosted Glass Navy, Subtle 8px Links, Smooth Slide) -->
                    <div id="publicMobileMenu" class="md:hidden overflow-hidden bg-[#132B44]/85 backdrop-blur-xl px-4 transition-all duration-300 smooth-ease" style="max-height: 0px; opacity: 0; padding-top: 0px; padding-bottom: 0px; transform: translateY(-6px);">
                        <div class="space-y-1">
                            <a href="{{ url('/') }}#top" data-nav-target="top" onclick="closePublicMobileMenu()" class="public-mobile-link block px-3.5 py-3 rounded-[8px] border-l-[3px] border-[#66A3BF] bg-[#183654] text-sm font-bold text-white transition-all duration-200">Beranda</a>
                            <a href="{{ url('/') }}#kategori-infrastruktur" data-nav-target="kategori-infrastruktur" onclick="closePublicMobileMenu()" class="public-mobile-link block px-3.5 py-3 rounded-[8px] border-l-[3px] border-transparent text-sm font-medium text-[#F2EFE7] hover:bg-[#183654] hover:text-white transition-all duration-200">Bidang Layanan</a>
                            <a href="{{ url('/') }}#mapSectionContainer" data-nav-target="mapSectionContainer" onclick="closePublicMobileMenu()" class="public-mobile-link block px-3.5 py-3 rounded-[8px] border-l-[3px] border-transparent text-sm font-medium text-[#F2EFE7] hover:bg-[#183654] hover:text-white transition-all duration-200">Peta Sebaran</a>
                            <a href="{{ url('/') }}#alur-penanganan" data-nav-target="alur-penanganan" onclick="closePublicMobileMenu()" class="public-mobile-link block px-3.5 py-3 rounded-[8px] border-l-[3px] border-transparent text-sm font-medium text-[#F2EFE7] hover:bg-[#183654] hover:text-white transition-all duration-200">Alur Penanganan</a>
                            <a href="{{ url('/') }}#download-app" data-nav-target="download-app" onclick="closePublicMobileMenu()" class="public-mobile-link block px-3.5 py-3 rounded-[8px] border-l-[3px] border-transparent text-sm font-medium text-[#F2EFE7] hover:bg-[#183654] hover:text-white transition-all duration-200">Aplikasi Mobile</a>
                        </div>
                    </div>
                </header>
            </div>
            <script>
                let isPublicMobileMenuOpen = false;
                function syncPublicHeaderBackground() {
                    const navWrapper = document.getElementById('publicNavWrapper');
                    const headerEl = document.getElementById('publicHeader');
                    const hairlineEl = document.getElementById('publicNavHairline');
                    if (!navWrapper || !headerEl) return;
                    const scrolled = (window.scrollY || 0) > 16;
                    const showBg = scrolled || isPublicMobileMenuOpen;

                    if (showBg) {
                        headerEl.style.backgroundColor = 'rgba(24, 54, 84, 0.72)';
                        headerEl.style.backdropFilter = 'blur(18px) saturate(160%)';
                        headerEl.style.webkitBackdropFilter = 'blur(18px) saturate(160%)';
                        headerEl.style.borderBottom = '1px solid rgba(200, 223, 219, 0.22)';
                        navWrapper.classList.add('shadow-[0_10px_30px_rgba(12,28,46,0.22)]');
                        if (hairlineEl) hairlineEl.classList.add('opacity-0');
                    } else {
                        headerEl.style.backgroundColor = 'transparent';
                        headerEl.style.backdropFilter = 'none';
                        headerEl.style.webkitBackdropFilter = 'none';
                        headerEl.style.borderBottom = '1px solid transparent';
                        navWrapper.classList.remove('shadow-[0_10px_30px_rgba(12,28,46,0.22)]');
                        if (hairlineEl) hairlineEl.classList.remove('opacity-0');
                    }
                }

                function applyPublicMobileMenuState(open) {
                    const menu = document.getElementById('publicMobileMenu');
                    const btn = document.getElementById('publicBurgerBtn');
                    const lbl = document.getElementById('publicBurgerLabel');
                    isPublicMobileMenuOpen = open;
                    if (menu) {
                        if (open) {
                            menu.style.maxHeight = '320px';
                            menu.style.opacity = '1';
                            menu.style.paddingTop = '12px';
                            menu.style.paddingBottom = '14px';
                            menu.style.transform = 'translateY(0)';
                        } else {
                            menu.style.maxHeight = '0px';
                            menu.style.opacity = '0';
                            menu.style.paddingTop = '0px';
                            menu.style.paddingBottom = '0px';
                            menu.style.transform = 'translateY(-4px)';
                        }
                    }
                    if (btn) btn.setAttribute('aria-expanded', open ? 'true' : 'false');
                    if (lbl) lbl.textContent = open ? 'Tutup' : 'Menu';
                    syncPublicHeaderBackground();
                }
                function togglePublicMobileMenu() {
                    applyPublicMobileMenuState(!isPublicMobileMenuOpen);
                }
                function closePublicMobileMenu() {
                    applyPublicMobileMenuState(false);
                }

                // Scroll-Spy Navbar + Transparent-to-Solid Header (Desktop & Mobile)
                document.addEventListener('DOMContentLoaded', () => {
                    const sectionIds = ['top', 'kategori-infrastruktur', 'mapSectionContainer', 'alur-penanganan', 'download-app'];
                    const sections = sectionIds.map(id => document.getElementById(id)).filter(Boolean);

                    const setActiveNav = (activeId) => {
                        document.querySelectorAll('.public-nav-link').forEach(link => {
                            const isMatch = link.getAttribute('data-nav-target') === activeId;
                            if (isMatch) {
                                link.classList.add('text-white', 'font-semibold', 'border-[#66A3BF]');
                                link.classList.remove('border-transparent', 'font-normal');
                            } else {
                                link.classList.remove('text-white', 'font-semibold', 'border-[#66A3BF]');
                                link.classList.add('border-transparent', 'font-normal');
                            }
                        });
                        document.querySelectorAll('.public-mobile-link').forEach(link => {
                            const isMatch = link.getAttribute('data-nav-target') === activeId;
                            if (isMatch) {
                                link.classList.add('text-white', 'bg-[#183654]', 'border-[#66A3BF]', 'font-bold');
                                link.classList.remove('text-[#F2EFE7]', 'border-transparent', 'font-medium');
                            } else {
                                link.classList.remove('text-white', 'bg-[#183654]', 'border-[#66A3BF]', 'font-bold');
                                link.classList.add('text-[#F2EFE7]', 'border-transparent', 'font-medium');
                            }
                        });
                    };

                    const updateOnScroll = () => {
                        syncPublicHeaderBackground();
                        if (!sections.length) return;
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

        <!-- Main Content (Full-width on landing page & login split-screen, container on other pages) -->
        <main class="flex-1 w-full {{ (request()->is('/') || request()->routeIs('login')) ? '' : 'max-w-[1280px] mx-auto px-6 sm:px-8 lg:px-10 pt-24 pb-12' }}">
            @yield('content')
        </main>

        @if(!request()->routeIs('login') && !(request()->is('/') || request()->routeIs('home')))
        <footer class="bg-transparent text-[#183654]">
            <div class="max-w-[1280px] mx-auto px-5 sm:px-6 xl:px-0">
                <div class="h-[1px] bg-[#C8DFDB] w-full"></div>
                <div class="py-7 md:h-[108px] flex flex-col md:flex-row items-start md:items-center justify-between gap-5">
                    <div class="flex items-center gap-3">
                        <div class="w-[34px] h-[34px] rounded-none bg-white flex items-center justify-center p-1.5 shrink-0">
                            <img src="{{ asset('images/logo-pin.png') }}" class="w-full h-full object-contain" alt="Logo SI-APPIK">
                        </div>
                        <div>
                            <span class="font-outfit font-bold text-[16px] text-[#183654] block leading-tight">SI-APPIK Kabupaten</span>
                            <span class="text-[12px] text-[#49637A] block mt-0.5">&copy; 2026 Pemerintah Kabupaten &bull; Dinas Pekerjaan Umum dan Penataan Ruang (PUPR)</span>
                        </div>
                    </div>
                    <div class="hidden md:flex items-center gap-7 text-[12.5px] font-normal text-[#49637A]">
                        <a href="{{ url('/') }}#top" class="hover:text-[#183654] transition">Beranda</a>
                        <a href="{{ url('/') }}#kategori-infrastruktur" class="hover:text-[#183654] transition">Bidang Layanan</a>
                        <a href="{{ url('/') }}#mapSectionContainer" class="hover:text-[#183654] transition">Peta Sebaran</a>
                        <a href="{{ url('/') }}#alur-penanganan" class="hover:text-[#183654] transition">Alur Penanganan</a>
                        <a href="{{ route('login') }}" class="text-[#3368A0] hover:text-[#183654] font-semibold transition">Masuk Portal</a>
                    </div>
                </div>
            </div>
        </footer>
        @endif
    </div>
@endif

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            if (window.lucide) lucide.createIcons();
        });

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

        function toggleRoleAccordion(roleKey) {
            const body = document.getElementById('accordionBody-' + roleKey);
            const chevron = document.getElementById('accordionChevron-' + roleKey);
            if (!body) return;
            const isHidden = body.classList.contains('hidden');
            body.classList.toggle('hidden', !isHidden);
            if (chevron) {
                chevron.classList.toggle('rotate-180', isHidden);
            }
            const btn = body.previousElementSibling;
            if (btn) btn.setAttribute('aria-expanded', isHidden ? 'true' : 'false');
        }

        function syncSidebarSubItemActive(roleKey, tabId) {
            const normalizedTab = (roleKey === 'admin' && tabId === 'users-list') ? 'users-management' : tabId;
            document.querySelectorAll(`.sidebar-subitem[data-sidebar-role="${roleKey}"]`).forEach(el => {
                const isMatch = el.getAttribute('data-sidebar-tab') === normalizedTab;
                el.classList.toggle('bg-[#2563EB]', isMatch);
                el.classList.toggle('text-white', isMatch);
                el.classList.toggle('font-bold', isMatch);
                el.classList.toggle('shadow-xs', isMatch);
                el.classList.toggle('text-[#475569]', !isMatch);
                el.classList.toggle('hover:bg-[#F8FAFC]', !isMatch);
                el.classList.toggle('hover:text-[#0F172A]', !isMatch);
                const icon = el.querySelector('svg, i');
                if (icon) {
                    icon.classList.toggle('text-white', isMatch);
                    icon.classList.toggle('text-[#64748B]', !isMatch);
                }
            });
        }

        function handleSidebarTabClick(event, roleKey, tabId) {
            if (roleKey === 'admin' && typeof window.switchAdminTab === 'function') {
                event.preventDefault();
                window.switchAdminTab(tabId);
            } else if (roleKey === 'operator' && typeof window.switchTab === 'function') {
                event.preventDefault();
                window.switchTab(tabId);
            } else if (roleKey === 'bupati' && typeof window.switchBupatiTab === 'function') {
                event.preventDefault();
                window.switchBupatiTab(tabId);
            } else if (roleKey === 'petugas' && typeof window.switchPetugasTab === 'function') {
                event.preventDefault();
                window.switchPetugasTab(tabId);
            } else {
                return true;
            }
            syncSidebarSubItemActive(roleKey, tabId);
            if (window.innerWidth < 1024) {
                const sidebar = document.getElementById('mainSidebar');
                if (sidebar && !sidebar.classList.contains('-translate-x-full')) {
                    toggleSidebar();
                }
            }
            return false;
        }

        // Jam Realtime Berjalan Akurat Waktu Indonesia Tengah (WITA / Kabupaten, UTC+8)
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
    <div id="firstLoginModal" class="fixed inset-0 z-[999999] flex items-center justify-center p-3 sm:p-5 bg-slate-900/65 select-none overflow-y-auto overscroll-contain">
        <div class="bg-white rounded-[24px] max-w-lg w-full shadow-2xl border border-[#E2E8F0] overflow-hidden my-auto">
            <!-- Modal Header -->
            <div class="px-6 py-4 border-b border-[#E2E8F0] bg-white flex items-center justify-between gap-3">
                <div class="flex items-center gap-3 min-w-0">
                    <div class="w-10 h-10 rounded-xl bg-[#EFF6FF] text-[#2563EB] border border-blue-100 flex items-center justify-center shrink-0">
                        <i data-lucide="shield-check" class="w-5 h-5"></i>
                    </div>
                    <div class="min-w-0">
                        <div class="flex items-center gap-2 flex-wrap">
                            <h3 class="font-outfit font-bold text-base text-[#0F172A] leading-tight">
                                Verifikasi Akun Baru
                            </h3>
                            <span class="px-2 py-0.5 rounded-md bg-[#EFF6FF] text-[#2563EB] border border-blue-100 text-[10px] font-bold uppercase tracking-wide">
                                {{ str_replace('_', ' ', auth()->user()->role) }}
                            </span>
                        </div>
                        <p class="text-[11px] text-[#64748B] mt-0.5 truncate">
                            Lengkapi nomor WhatsApp dan buat kata sandi baru Anda
                        </p>
                    </div>
                </div>
                <button type="button" onclick="document.getElementById('firstLoginLogoutForm').submit()" class="w-9 h-9 rounded-[10px] bg-white hover:bg-slate-100 text-slate-500 border border-[#E2E8F0] flex items-center justify-center transition cursor-pointer shrink-0" aria-label="Tutup">
                    <i data-lucide="x" class="w-4 h-4"></i>
                </button>
            </div>

            <!-- Modal Form Body -->
            <form method="POST" action="{{ route('profile.first-login.setup') }}" class="p-6 space-y-4">
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

                <div class="space-y-3.5">
                    <!-- 1. Nama Lengkap -->
                    <div>
                        <label class="block text-xs font-bold text-[#334155] mb-1.5">
                            Nama Lengkap <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="name" value="{{ old('name', auth()->user()->name) }}" required
                            placeholder="Nama sesuai SK / identitas"
                            class="w-full px-3.5 py-2.5 text-xs font-semibold rounded-xl border border-[#CBD5E1] bg-white text-[#0F172A] focus:border-[#2563EB] focus:ring-2 focus:ring-[#2563EB]/20 focus:outline-none transition">
                    </div>

                    <!-- 2. Nomor Handphone / WhatsApp (Standar Indonesia) -->
                    <div>
                        <label class="block text-xs font-bold text-[#334155] mb-1.5">
                            Nomor WhatsApp Aktif <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <input type="tel" name="phone" id="setupPhoneInput"
                                value="{{ old('phone', auth()->user()->phone) }}" required
                                maxlength="13"
                                oninput="this.value = this.value.replace(/[^0-9]/g, '')"
                                placeholder="Contoh: 081234567890"
                                class="w-full px-3.5 py-2.5 text-xs font-mono font-semibold rounded-xl border border-[#CBD5E1] bg-white text-[#0F172A] focus:border-[#2563EB] focus:ring-2 focus:ring-[#2563EB]/20 focus:outline-none transition">
                        </div>
                        <p class="text-[11px] text-[#64748B] mt-1">Format standar Indonesia diawali 08 (10–13 digit angka).</p>
                    </div>

                    <!-- 3. Kata Sandi Baru & Ulangi Sandi (Dengan Ikon Mata) -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 pt-0.5">
                        <div>
                            <label class="block text-xs font-bold text-[#334155] mb-1.5">
                                Sandi Baru <span class="text-red-500">*</span>
                            </label>
                            <div class="relative">
                                <input type="password" name="password" id="setupNewPassInput" required minlength="8"
                                    placeholder="Min. 8 karakter (A-Z, a-z, 0-9)"
                                    class="w-full pl-3.5 pr-9 py-2.5 text-xs rounded-xl border border-[#CBD5E1] bg-white text-[#0F172A] focus:border-[#2563EB] focus:ring-2 focus:ring-[#2563EB]/20 focus:outline-none transition">
                                <button type="button" onclick="toggleSetupPass('setupNewPassInput', 'eyeIconSetup1')" class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-slate-600 cursor-pointer">
                                    <i id="eyeIconSetup1" data-lucide="eye" class="w-4 h-4"></i>
                                </button>
                            </div>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-[#334155] mb-1.5">
                                Ulangi Sandi <span class="text-red-500">*</span>
                            </label>
                            <div class="relative">
                                <input type="password" name="password_confirmation" id="setupConfirmPassInput" required minlength="8"
                                    placeholder="Ulangi sandi baru"
                                    class="w-full pl-3.5 pr-9 py-2.5 text-xs rounded-xl border border-[#CBD5E1] bg-white text-[#0F172A] focus:border-[#2563EB] focus:ring-2 focus:ring-[#2563EB]/20 focus:outline-none transition">
                                <button type="button" onclick="toggleSetupPass('setupConfirmPassInput', 'eyeIconSetup2')" class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-slate-600 cursor-pointer">
                                    <i id="eyeIconSetup2" data-lucide="eye" class="w-4 h-4"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Footer Tombol -->
                <div class="pt-4 border-t border-[#E2E8F0] flex items-center justify-end gap-2.5">
                    <button type="button" onclick="document.getElementById('firstLoginLogoutForm').submit()"
                        class="px-5 py-2.5 rounded-xl bg-[#F1F5F9] hover:bg-slate-200 text-[#334155] font-bold text-xs transition cursor-pointer">
                        Nanti Saja
                    </button>
                    <button type="submit"
                        class="px-5 py-2.5 rounded-xl bg-[#2563EB] hover:bg-[#1D4ED8] text-white font-bold text-xs transition flex items-center justify-center gap-1.5 cursor-pointer">
                        <i data-lucide="check" class="w-4 h-4"></i>
                        <span>Simpan &amp; Aktifkan</span>
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

    @if(session('success') || session('info') || session('error'))
    <!-- Floating Bottom-Right Toast Notification (Auto-Dismiss 4s) -->
    <div id="globalToastStack" class="fixed bottom-5 right-5 z-[999999] flex flex-col gap-2.5 max-w-sm w-[calc(100vw-2.5rem)] pointer-events-none">
        @if(session('success'))
        <div class="global-toast pointer-events-auto flex items-start gap-3 bg-white border border-emerald-200 shadow-lg rounded-2xl px-4 py-3 transition-all duration-300">
            <div class="w-8 h-8 rounded-xl bg-emerald-50 text-emerald-600 border border-emerald-100 flex items-center justify-center shrink-0">
                <i data-lucide="check-circle-2" class="w-4 h-4"></i>
            </div>
            <div class="flex-1 min-w-0">
                <p class="text-xs font-bold text-[#0F172A]">Berhasil</p>
                <p class="text-[11px] text-[#475569] mt-0.5 leading-snug">{{ session('success') }}</p>
            </div>
            <button type="button" onclick="this.closest('.global-toast').remove()" class="w-6 h-6 rounded-lg text-slate-400 hover:text-slate-600 hover:bg-slate-100 flex items-center justify-center shrink-0 cursor-pointer" aria-label="Tutup">
                <i data-lucide="x" class="w-3.5 h-3.5"></i>
            </button>
        </div>
        @endif

        @if(session('info'))
        <div class="global-toast pointer-events-auto flex items-start gap-3 bg-white border border-blue-200 shadow-lg rounded-2xl px-4 py-3 transition-all duration-300">
            <div class="w-8 h-8 rounded-xl bg-[#EFF6FF] text-[#2563EB] border border-blue-100 flex items-center justify-center shrink-0">
                <i data-lucide="info" class="w-4 h-4"></i>
            </div>
            <div class="flex-1 min-w-0">
                <p class="text-xs font-bold text-[#0F172A]">Informasi</p>
                <p class="text-[11px] text-[#475569] mt-0.5 leading-snug">{{ session('info') }}</p>
            </div>
            <button type="button" onclick="this.closest('.global-toast').remove()" class="w-6 h-6 rounded-lg text-slate-400 hover:text-slate-600 hover:bg-slate-100 flex items-center justify-center shrink-0 cursor-pointer" aria-label="Tutup">
                <i data-lucide="x" class="w-3.5 h-3.5"></i>
            </button>
        </div>
        @endif

        @if(session('error'))
        <div class="global-toast pointer-events-auto flex items-start gap-3 bg-white border border-red-200 shadow-lg rounded-2xl px-4 py-3 transition-all duration-300">
            <div class="w-8 h-8 rounded-xl bg-red-50 text-red-600 border border-red-100 flex items-center justify-center shrink-0">
                <i data-lucide="alert-circle" class="w-4 h-4"></i>
            </div>
            <div class="flex-1 min-w-0">
                <p class="text-xs font-bold text-red-700">Perhatian</p>
                <p class="text-[11px] text-[#475569] mt-0.5 leading-snug">{{ session('error') }}</p>
            </div>
            <button type="button" onclick="this.closest('.global-toast').remove()" class="w-6 h-6 rounded-lg text-slate-400 hover:text-slate-600 hover:bg-slate-100 flex items-center justify-center shrink-0 cursor-pointer" aria-label="Tutup">
                <i data-lucide="x" class="w-3.5 h-3.5"></i>
            </button>
        </div>
        @endif
    </div>
    <script>
        setTimeout(() => {
            document.querySelectorAll('#globalToastStack .global-toast').forEach(el => {
                el.style.opacity = '0';
                el.style.transform = 'translateY(8px)';
                setTimeout(() => el.remove(), 300);
            });
        }, 4000);
    </script>
    @endif

    @stack('modals')
    @stack('scripts')
</body>
</html>
