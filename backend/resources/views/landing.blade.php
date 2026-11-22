@extends('layouts.app')

@section('content')
@php
    $totalActiveCount = (int) ($stats['total'] ?? 0);
    $processingCount = (int) (($stats['submitted'] ?? 0) + ($stats['dispatched'] ?? 0) + ($stats['in_progress'] ?? 0) + ($stats['waiting_validation'] ?? 0));
    $completedCount = (int) ($stats['completed'] ?? 0);

    $landingApkFile = public_path('downloads/SI-APPIK.APK');
    $landingApkVer = file_exists($landingApkFile) ? filemtime($landingApkFile) : time();
    $landingApkUrl = route('download.apk', ['v' => $landingApkVer]);

    $featuredComplaint = \App\Models\Complaint::with(['category', 'photos', 'latestDisposition.team'])
        ->whereNotIn('status', ['REJECTED', 'DRAFT', 'CANCELLED'])
        ->where('rejected_by_superadmin', false)
        ->whereRaw('LENGTH(TRIM(title)) >= 10')
        ->whereHas('photos')
        ->latest('created_at')
        ->first()
        ?? \App\Models\Complaint::with(['category', 'photos', 'latestDisposition.team'])
            ->whereNotIn('status', ['REJECTED', 'DRAFT', 'CANCELLED'])
            ->where('rejected_by_superadmin', false)
            ->latest('created_at')
            ->first();
    $featuredBefore = $featuredComplaint?->photos->firstWhere('type', 'evidence_before') ?? $featuredComplaint?->photos->first();
    $featuredBeforeUrl = $featuredBefore
        ? ($featuredBefore->storage_key ? asset('storage/' . ltrim($featuredBefore->storage_key, '/')) : ($featuredBefore->url ?? null))
        : null;
@endphp

<!-- SECTION 1: TOP BAR & EDITORIAL HERO (1:1 FigCraft Page 360 Node 753 · Height 752px Desktop) -->
<section id="top" class="relative z-30 w-full bg-[#183654] {{ auth()->check() ? 'pt-10 lg:pt-14' : 'pt-[104px] md:pt-[134px]' }} pb-10 md:pb-[40px] md:min-h-[752px] flex flex-col justify-between">
    <!-- Hero Scenic Background Slideshow (Top 592px Atmospheric Coastal Fade into #183654) -->
    <div class="absolute inset-x-0 top-0 h-[520px] md:h-[592px] overflow-hidden pointer-events-none z-0">
        <div id="landingHeroSlideshow" class="absolute inset-0 will-change-transform">
            <img src="{{ asset('images/hero/hero-1.webp') }}" alt="Pemandangan Pesisir dan Infrastruktur Kabupaten" fetchpriority="high" decoding="async" class="landing-hero-slide active-slide absolute inset-0 w-full h-full object-cover object-center opacity-100 transition-opacity duration-1000">
            <img data-src="{{ asset('images/hero/repair-jalan.webp') }}" alt="Pekerja Lapangan Dinas PUPR Memperbaiki Jalan" decoding="async" class="landing-hero-slide absolute inset-0 w-full h-full object-cover object-center opacity-0 transition-opacity duration-1000">
            <img data-src="{{ asset('images/hero/repair-pju.webp') }}" alt="Pemeliharaan Penerangan Jalan Umum Kabupaten" decoding="async" class="landing-hero-slide absolute inset-0 w-full h-full object-cover object-center opacity-0 transition-opacity duration-1000">
            <img data-src="{{ asset('images/hero/repair-fasum.webp') }}" alt="Perbaikan Fasilitas Umum dan Drainase Kabupaten" decoding="async" class="landing-hero-slide absolute inset-0 w-full h-full object-cover object-center opacity-0 transition-opacity duration-1000">
            <!-- Exact Vertical Smooth Navy Atmospheric Scrim (Node 755) -->
            <div class="absolute inset-0" style="background: linear-gradient(180deg, rgba(24,54,84,0.78) 0%, rgba(51,104,160,0.33) 32%, rgba(24,54,84,0.85) 74%, #183654 100%);"></div>
            <!-- Exact Left Readability Scrim (Node 756) -->
            <div class="absolute inset-0" style="background: linear-gradient(90deg, rgba(24,54,84,0.85) 0%, rgba(24,54,84,0.53) 42%, rgba(24,54,84,0) 85%);"></div>
        </div>
    </div>

    <style>
        @keyframes landingKenBurnsInOut {
            0%   { transform: scale(1.07); }
            50%  { transform: scale(1.00); }
            100% { transform: scale(1.07); }
        }
        #landingHeroSlideshow .landing-hero-slide.active-slide {
            animation: landingKenBurnsInOut 13s ease-in-out infinite;
        }
    </style>

    <!-- Right-Edge Synced Slide Index Rail (01 / 02 / 03 / 04 moves with background slideshow!) -->
    <div id="heroSlideRail" class="hidden xl:flex flex-col items-end gap-3 absolute right-0 top-[186px] z-20 select-none" aria-label="Navigasi latar belakang beranda">
        <button type="button" data-slide-idx="0" onclick="goToHeroSlide(0)"
            class="hero-rail-btn flex items-center justify-end cursor-pointer transition-all duration-300 focus-visible:outline-2 focus-visible:outline-white">
            <span class="hero-rail-num w-[38px] text-center font-outfit font-bold text-[28px] leading-none text-white transition-all duration-300">01</span>
            <span class="hero-rail-line block h-[1.5px] bg-[#66A3BF] ml-4 w-[66px] opacity-100 transition-all duration-300"></span>
        </button>
        <button type="button" data-slide-idx="1" onclick="goToHeroSlide(1)"
            class="hero-rail-btn flex items-center justify-end cursor-pointer transition-all duration-300 focus-visible:outline-2 focus-visible:outline-white">
            <span class="hero-rail-num w-[38px] text-center font-outfit font-semibold text-[14px] leading-none text-[#C8DFDB] hover:text-white transition-all duration-300">02</span>
            <span class="hero-rail-line block h-[1.5px] bg-[#66A3BF] ml-4 w-[66px] opacity-0 transition-all duration-300"></span>
        </button>
        <button type="button" data-slide-idx="2" onclick="goToHeroSlide(2)"
            class="hero-rail-btn flex items-center justify-end cursor-pointer transition-all duration-300 focus-visible:outline-2 focus-visible:outline-white">
            <span class="hero-rail-num w-[38px] text-center font-outfit font-semibold text-[14px] leading-none text-[#C8DFDB] hover:text-white transition-all duration-300">03</span>
            <span class="hero-rail-line block h-[1.5px] bg-[#66A3BF] ml-4 w-[66px] opacity-0 transition-all duration-300"></span>
        </button>
        <button type="button" data-slide-idx="3" onclick="goToHeroSlide(3)"
            class="hero-rail-btn flex items-center justify-end cursor-pointer transition-all duration-300 focus-visible:outline-2 focus-visible:outline-white">
            <span class="hero-rail-num w-[38px] text-center font-outfit font-semibold text-[14px] leading-none text-[#C8DFDB] hover:text-white transition-all duration-300">04</span>
            <span class="hero-rail-line block h-[1.5px] bg-[#66A3BF] ml-4 w-[66px] opacity-0 transition-all duration-300"></span>
        </button>
    </div>

    <div class="relative z-10 max-w-[1280px] w-full mx-auto px-5 sm:px-6 xl:px-0">
        <!-- Hero Copy Block (Exact FigCraft Typography & Widths) -->
        <div class="max-w-[760px]">
            <p class="text-[12px] sm:text-[13px] font-semibold text-[#C8DFDB] leading-normal">
                <span class="sm:hidden">Pemerintah Kabupaten &bull; Dinas PUPR</span>
            </p>

            <h1 class="mt-3 sm:mt-4 font-outfit font-bold text-[34px] leading-[40px] sm:text-[46px] sm:leading-[54px] lg:text-[54px] lg:leading-[62px] tracking-[-1px] text-white">
                Layanan Pengaduan<br>Infrastruktur Kabupaten
            </h1>

            <p class="mt-4 sm:mt-5 text-[14px] leading-[22px] sm:text-[16px] sm:leading-[26px] text-[#F2EFE7] max-w-[630px]">
                <span class="sm:hidden">Sampaikan kerusakan di sekitar Anda dan pantau penanganannya bersama Dinas PUPR.</span>
                <span class="hidden sm:inline">Sampaikan laporan jalan rusak, jembatan, lampu penerangan, atau fasilitas umum di sekitar Anda, lalu pantau penanganannya bersama Dinas PUPR.</span>
            </p>

            <!-- Hero Search Bar (Subtle 8px Rounded, Zero White Gap on Button, Mobile Touch-Friendly) -->
            <div id="searchContainer" class="mt-7 sm:mt-8 w-full max-w-[696px] relative z-40 text-left">
                <form id="trackForm" class="flex flex-col sm:flex-row items-stretch rounded-[8px] sm:overflow-hidden shadow-[0_18px_40px_rgba(12,28,46,0.30)] relative z-20 transition-shadow duration-300 smooth-ease focus-within:shadow-[0_22px_48px_rgba(12,28,46,0.42)]">
                    <div class="relative flex-1 flex items-center bg-white rounded-[8px] sm:rounded-none">
                        <i data-lucide="search" class="w-[17px] h-[17px] absolute left-4 sm:left-5 top-1/2 -translate-y-1/2 text-[#49637A] pointer-events-none"></i>
                        <input type="text" id="trackCodeInput" autocomplete="off"
                            placeholder="Cari lokasi, jenis kerusakan, atau nomor tiket..."
                            aria-label="Cari lokasi, jenis kerusakan, atau nomor tiket"
                            class="w-full bg-white border-0 rounded-[8px] sm:rounded-none pl-[48px] sm:pl-[52px] pr-4 h-[52px] sm:h-[56px] text-[14px] sm:text-[14.5px] text-[#183654] placeholder-[#49637A] focus:outline-none">
                        <button type="button" id="clearSearchBtn" aria-label="Hapus pencarian"
                            class="hidden absolute right-3 top-1/2 -translate-y-1/2 px-2.5 py-1.5 rounded-[6px] text-[12.5px] font-medium text-[#49637A] hover:text-[#183654] hover:bg-[#F2EFE7] transition-all duration-200 cursor-pointer items-center gap-1">
                            <span>Hapus</span>
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <button type="submit"
                        class="mt-2.5 sm:mt-0 bg-[#3368A0] hover:bg-[#275485] active:scale-[0.99] text-white font-semibold sm:w-[154px] h-[50px] sm:h-[56px] rounded-[8px] sm:rounded-none border-0 text-[14px] transition-all duration-250 smooth-ease flex items-center justify-center shrink-0 cursor-pointer focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-white">
                        Cari Aduan
                    </button>
                </form>

                <!-- Floating Live Autocomplete Results (Subtle 10px Rounded, Smooth Entrance) -->
                <div id="searchSuggestions" role="region" aria-label="Hasil Pencarian Aduan"
                    class="absolute left-0 right-0 top-full mt-2 bg-white rounded-[10px] border-t-[3px] border-t-[#3368A0] shadow-[0_24px_56px_rgba(12,28,46,0.32)] overflow-hidden z-50 hidden text-left text-[#183654] transition-all duration-200 smooth-ease opacity-0 -translate-y-1.5">
                    <!-- Warm Cream Filter Chip Bar + Result Count (Horizontal Scroll on Mobile) -->
                    <div class="px-4 sm:px-5 py-3 bg-[#F2EFE7] flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                        <div id="searchStatusChips" class="flex items-center gap-1.5 overflow-x-auto no-scrollbar pb-0.5 sm:pb-0">
                            <button type="button" data-search-status="ALL"
                                class="search-status-chip px-3 py-1.5 rounded-[6px] text-[12px] font-semibold transition-all duration-200 cursor-pointer bg-[#3368A0] text-white whitespace-nowrap shrink-0">
                                Semua
                            </button>
                            <button type="button" data-search-status="SUBMITTED"
                                class="search-status-chip px-2.5 py-1.5 rounded-[6px] text-[12px] font-medium transition-all duration-200 cursor-pointer bg-transparent text-[#2E4A66] hover:bg-white whitespace-nowrap shrink-0">
                                Verifikasi
                            </button>
                            <button type="button" data-search-status="DISPATCHED"
                                class="search-status-chip px-2.5 py-1.5 rounded-[6px] text-[12px] font-medium transition-all duration-200 cursor-pointer bg-transparent text-[#2E4A66] hover:bg-white whitespace-nowrap shrink-0">
                                Ditugaskan
                            </button>
                            <button type="button" data-search-status="PROCESSING"
                                class="search-status-chip px-2.5 py-1.5 rounded-[6px] text-[12px] font-medium transition-all duration-200 cursor-pointer bg-transparent text-[#2E4A66] hover:bg-white whitespace-nowrap shrink-0">
                                Diproses
                            </button>
                            <button type="button" data-search-status="COMPLETED"
                                class="search-status-chip px-2.5 py-1.5 rounded-[6px] text-[12px] font-medium transition-all duration-200 cursor-pointer bg-transparent text-[#2E4A66] hover:bg-white whitespace-nowrap shrink-0">
                                Selesai
                            </button>
                        </div>
                        <span id="searchResultCount" class="text-[12px] font-semibold text-[#3368A0] whitespace-nowrap"></span>
                    </div>

                    <!-- Clean Two-Line Editorial Results List -->
                    <div id="searchSuggestionsList" class="divide-y divide-[#E6E2D8] max-h-[340px] overflow-y-auto overscroll-contain bg-white" style="-webkit-overflow-scrolling: touch;"></div>

                    <!-- Keyboard Shortcut Footer -->
                    <div class="px-4 sm:px-5 py-2.5 bg-[#F2EFE7] flex items-center justify-between gap-2 text-[11.5px] text-[#49637A]">
                        <span class="hidden sm:inline">&uarr; &darr; Navigasi hasil &nbsp;&bull;&nbsp; Enter Buka detail laporan</span>
                        <span class="sm:hidden">Ketuk laporan untuk membuka detail</span>
                        <span class="shrink-0">Esc Tutup pencarian</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Mobile Compact KPI Strip (Subtle 10px Rounded, Smooth Tap Feedback) -->
        <div class="mt-7 sm:hidden bg-[#224363]/95 rounded-[10px] grid grid-cols-3 px-4 py-4 gap-2">
            <button type="button" onclick="filterMapByStatus('ALL')" class="text-left cursor-pointer active:scale-[0.97] transition-transform duration-200">
                <span class="font-outfit font-bold text-[26px] leading-none text-white block">{{ str_pad((string) $totalActiveCount, 2, '0', STR_PAD_LEFT) }}</span>
                <span class="text-[12px] text-[#C8DFDB] block mt-1.5">Laporan</span>
            </button>
            <button type="button" onclick="filterMapByStatus('PROCESSING')" class="text-left cursor-pointer active:scale-[0.97] transition-transform duration-200">
                <span class="font-outfit font-bold text-[26px] leading-none text-white block">{{ str_pad((string) $processingCount, 2, '0', STR_PAD_LEFT) }}</span>
                <span class="text-[12px] text-[#C8DFDB] block mt-1.5">Ditangani</span>
            </button>
            <button type="button" onclick="filterMapByStatus('COMPLETED')" class="text-left cursor-pointer active:scale-[0.97] transition-transform duration-200">
                <span class="font-outfit font-bold text-[26px] leading-none text-white block">{{ str_pad((string) $completedCount, 2, '0', STR_PAD_LEFT) }}</span>
                <span class="text-[12px] text-[#C8DFDB] block mt-1.5">Selesai</span>
            </button>
        </div>

        <!-- Desktop 3-Column Editorial KPI Strip -->
        <div class="hidden sm:block mt-[58px]">
            <div class="h-[1px] bg-[#C8DFDB]/25 w-full"></div>
            <div class="pt-6 grid grid-cols-3 gap-12">
                <button type="button" onclick="filterMapByStatus('ALL')" data-status="ALL"
                    class="kpi-filter-box group text-left cursor-pointer transition-transform duration-300 smooth-ease hover:-translate-y-0.5 focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-white rounded-[6px]">
                    <div class="flex items-baseline gap-4">
                        <span class="font-outfit font-bold text-[34px] leading-none text-white">{{ str_pad((string) $totalActiveCount, 2, '0', STR_PAD_LEFT) }}</span>
                        <span class="text-[13.5px] font-semibold text-[#C8DFDB] group-hover:text-white transition-colors duration-250">Laporan Masyarakat</span>
                    </div>
                    <p class="mt-2.5 text-[13px] leading-[20px] text-[#F2EFE7] max-w-[368px]">
                        Laporan aktif dari 15 kecamatan yang tercatat langsung pada basis data resmi Dinas PUPR.
                    </p>
                </button>

                <button type="button" onclick="filterMapByStatus('PROCESSING')" data-status="PROCESSING"
                    class="kpi-filter-box group text-left cursor-pointer transition-transform duration-300 smooth-ease hover:-translate-y-0.5 focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-white rounded-[6px]">
                    <div class="flex items-baseline gap-4">
                        <span class="font-outfit font-bold text-[34px] leading-none text-white">{{ str_pad((string) $processingCount, 2, '0', STR_PAD_LEFT) }}</span>
                        <span class="text-[13.5px] font-semibold text-[#C8DFDB] group-hover:text-white transition-colors duration-250">Sedang Ditangani</span>
                    </div>
                    <p class="mt-2.5 text-[13px] leading-[20px] text-[#F2EFE7] max-w-[368px]">
                        Sedang melalui tahap verifikasi operator, penugasan, hingga pengerjaan oleh Pekerja Lapangan.
                    </p>
                </button>

                <button type="button" onclick="filterMapByStatus('COMPLETED')" data-status="COMPLETED"
                    class="kpi-filter-box group text-left cursor-pointer transition-transform duration-300 smooth-ease hover:-translate-y-0.5 focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-white rounded-[6px]">
                    <div class="flex items-baseline gap-4">
                        <span class="font-outfit font-bold text-[34px] leading-none text-white">{{ str_pad((string) $completedCount, 2, '0', STR_PAD_LEFT) }}</span>
                        <span class="text-[13.5px] font-semibold text-[#C8DFDB] group-hover:text-white transition-colors duration-250">Selesai Diperbaiki</span>
                    </div>
                    <p class="mt-2.5 text-[13px] leading-[20px] text-[#F2EFE7] max-w-[380px]">
                        Perbaikan fisik telah diverifikasi mutunya dan statusnya diperbarui secara terbuka di peta.
                    </p>
                </button>
            </div>
        </div>

        <!-- Bottom Scroll Cue + Slide Progress Track (Exact 1:1 FigCraft Node 794-796) -->
        <div class="mt-8 sm:mt-12 space-y-3">
            <a href="#kategori-infrastruktur" class="hidden sm:inline-block text-[12px] font-semibold text-[#C8DFDB] hover:text-white transition-colors">
                Jelajahi bidang layanan &amp; peta sebaran
            </a>
            <div class="w-full h-[1.5px] bg-[#C8DFDB]/25 relative overflow-hidden">
                <div id="heroBottomProgressBar" class="h-full bg-[#66A3BF] transition-all duration-500" style="width: 25%;"></div>
            </div>
        </div>
    </div>
</section>

<!-- SECTION 2: BIDANG LAYANAN (1:1 FigCraft Node 797 · 3 Borderless 408x404 Photo Tiles Desktop / 3 Stacked Mobile) -->
<section id="kategori-infrastruktur" class="relative z-20 w-full bg-[#183654] pt-6 sm:pt-[38px] pb-12 sm:pb-[64px] scroll-mt-[80px]">
    <div class="max-w-[1280px] mx-auto px-5 sm:px-6 xl:px-0">
        <!-- Header: Left on Mobile, Center on Desktop (Exact FigCraft 798-799 & M03) -->
        <div class="text-left md:text-center space-y-2.5">
            <p class="hidden md:block text-[13px] font-semibold text-[#C8DFDB]">
                Bidang Penanganan
            </p>
            <h2 class="font-outfit font-bold text-[24px] md:text-[30px] tracking-[-0.4px] text-white leading-tight">
                <span class="md:hidden">Bidang Penanganan</span>
                <span class="hidden md:inline">Infrastruktur yang Dapat Anda Laporkan</span>
            </h2>
        </div>

        <!-- 3 Subtle Rounded Editorial Photo Tiles (10px Radius, Smooth Lift & Zoom) -->
        <div class="mt-6 md:mt-8 grid grid-cols-1 md:grid-cols-3 gap-4 md:gap-[28px]">
            <!-- Tile 1: Jalan & Jembatan -->
            <button type="button" onclick="filterMapByCategory(1)"
                class="group relative h-[124px] md:h-[404px] w-full overflow-hidden rounded-[10px] bg-[#3368A0] text-left cursor-pointer transition-all duration-400 smooth-ease hover:-translate-y-1 hover:shadow-[0_18px_36px_rgba(12,28,46,0.34)] active:scale-[0.99] focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-white">
                <img src="{{ asset('images/hero/repair-jalan.webp') }}" alt="Jalan dan Jembatan Kabupaten" loading="lazy" decoding="async"
                    class="absolute inset-0 w-full h-full object-cover object-center group-hover:scale-[1.05] transition-transform duration-700 smooth-ease">
                <div class="absolute inset-0" style="background: linear-gradient(180deg, rgba(24,54,84,0.12) 0%, rgba(24,54,84,0.30) 44%, rgba(24,54,84,0.92) 82%, #183654 100%);"></div>
                <div class="absolute inset-x-0 bottom-0 p-5 md:p-6 flex flex-col justify-end">
                    <span id="catCountJalan" class="order-2 md:order-1 mt-1 md:mt-0 text-[12px] font-semibold text-[#C8DFDB]">
                        {{ $categoryStats['jalan'] ?? 0 }} Laporan Aktif
                    </span>
                    <h3 class="order-1 md:order-2 md:mt-1.5 font-outfit font-bold text-[19px] md:text-[22px] text-white leading-snug">
                        Jalan &amp; Jembatan
                    </h3>
                    <p class="hidden md:block order-3 mt-1.5 text-[12.5px] leading-[19px] text-[#F2EFE7]">
                        Aspal berlubang, jalan retak, bahu longsor, dan jembatan daerah.
                    </p>
                </div>
            </button>

            <!-- Tile 2: Penerangan Jalan -->
            <button type="button" onclick="filterMapByCategory(2)"
                class="group relative h-[124px] md:h-[404px] w-full overflow-hidden rounded-[10px] bg-[#3368A0] text-left cursor-pointer transition-all duration-400 smooth-ease hover:-translate-y-1 hover:shadow-[0_18px_36px_rgba(12,28,46,0.34)] active:scale-[0.99] focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-white">
                <img src="{{ asset('images/hero/repair-pju.webp') }}" alt="Penerangan Jalan Kabupaten" loading="lazy" decoding="async"
                    class="absolute inset-0 w-full h-full object-cover object-center group-hover:scale-[1.05] transition-transform duration-700 smooth-ease">
                <div class="absolute inset-0" style="background: linear-gradient(180deg, rgba(24,54,84,0.12) 0%, rgba(24,54,84,0.30) 44%, rgba(24,54,84,0.92) 82%, #183654 100%);"></div>
                <div class="absolute inset-x-0 bottom-0 p-5 md:p-6 flex flex-col justify-end">
                    <span id="catCountPju" class="order-2 md:order-1 mt-1 md:mt-0 text-[12px] font-semibold text-[#C8DFDB]">
                        {{ $categoryStats['pju'] ?? 0 }} Laporan Aktif
                    </span>
                    <h3 class="order-1 md:order-2 md:mt-1.5 font-outfit font-bold text-[19px] md:text-[22px] text-white leading-snug">
                        Penerangan Jalan
                    </h3>
                    <p class="hidden md:block order-3 mt-1.5 text-[12.5px] leading-[19px] text-[#F2EFE7]">
                        Lampu PJU padam, tiang miring, dan perbaikan jaringan kabel.
                    </p>
                </div>
            </button>

            <!-- Tile 3: Fasilitas Umum -->
            <button type="button" onclick="filterMapByCategory(3)"
                class="group relative h-[124px] md:h-[404px] w-full overflow-hidden rounded-[10px] bg-[#3368A0] text-left cursor-pointer transition-all duration-400 smooth-ease hover:-translate-y-1 hover:shadow-[0_18px_36px_rgba(12,28,46,0.34)] active:scale-[0.99] focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-white">
                <img src="{{ asset('images/hero/repair-fasum.webp') }}" alt="Fasilitas Umum Kabupaten" loading="lazy" decoding="async"
                    class="absolute inset-0 w-full h-full object-cover object-center group-hover:scale-[1.05] transition-transform duration-700 smooth-ease">
                <div class="absolute inset-0" style="background: linear-gradient(180deg, rgba(24,54,84,0.12) 0%, rgba(24,54,84,0.30) 44%, rgba(24,54,84,0.92) 82%, #183654 100%);"></div>
                <div class="absolute inset-x-0 bottom-0 p-5 md:p-6 flex flex-col justify-end">
                    <span id="catCountFasilitas" class="order-2 md:order-1 mt-1 md:mt-0 text-[12px] font-semibold text-[#C8DFDB]">
                        {{ $categoryStats['fasilitas'] ?? 0 }} Laporan Aktif
                    </span>
                    <h3 class="order-1 md:order-2 md:mt-1.5 font-outfit font-bold text-[19px] md:text-[22px] text-white leading-snug">
                        Fasilitas Umum
                    </h3>
                    <p class="hidden md:block order-3 mt-1.5 text-[12.5px] leading-[19px] text-[#F2EFE7]">
                        Saluran drainase tersumbat, trotoar, taman kota, dan prasarana publik.
                    </p>
                </div>
            </button>
        </div>

        <!-- Subtle bottom split rule matching FigCraft 797 -->
        <div class="hidden md:grid grid-cols-2 gap-6 mt-10">
            <div class="h-[1px] bg-[#C8DFDB]/15"></div>
            <div class="h-[1px] bg-[#C8DFDB]/15"></div>
        </div>
    </div>
</section>

<!-- SECTION 3: PETA SEBARAN INTERAKTIF GIS (1:1 FigCraft Node 826 · Height 772px Desktop, White Canvas) -->
<section id="mapSectionContainer" class="relative z-20 w-full bg-white py-11 md:py-[44px] scroll-mt-[80px]">
    <div class="max-w-[1280px] mx-auto px-5 sm:px-6 xl:px-0 space-y-6">
        <!-- Header & District Dropdown (Exact Node 827-832) -->
        <div class="flex flex-col md:flex-row md:items-end justify-between gap-4">
            <div class="space-y-1.5">
                <p class="hidden md:block text-[12.5px] font-semibold text-[#3368A0]">
                    Pantauan Wilayah Kabupaten
                </p>
                <h2 class="font-outfit font-bold text-[24px] md:text-[30px] text-[#183654] tracking-tight leading-tight">
                    <span class="md:hidden">Peta Sebaran Kerusakan</span>
                    <span class="hidden md:inline">Peta Sebaran Titik Kerusakan</span>
                </h2>
                <p id="drawerFilterStatusText" class="hidden md:block text-[14.5px] text-[#49637A]">
                    Pilih kecamatan atau status untuk melihat titik kerusakan maupun ruas jalan yang sedang ditangani.
                </p>
            </div>

            <!-- Subtle 8px District Select -->
            <div class="flex items-center gap-2.5 w-full md:w-auto">
                <div class="relative flex-1 md:flex-initial md:w-[332px]">
                    <select id="districtFilterSelect" onchange="onDistrictFilterChange(this.value)" aria-label="Filter 15 Kecamatan"
                        class="w-full appearance-none bg-[#F2EFE7] hover:bg-[#EAE5D9] text-[13.5px] font-semibold text-[#183654] h-[48px] pl-[18px] pr-10 rounded-[8px] border-b-2 border-transparent focus:border-[#3368A0] focus:outline-none cursor-pointer transition-colors duration-200">
                        <option value="ALL">15 Kecamatan (Batu Putih - Wawo)</option>
                        @foreach($districts as $d)
                            <option value="{{ $d }}">{{ str_starts_with($d, 'Kecamatan ') ? $d : 'Kecamatan ' . $d }}</option>
                        @endforeach
                    </select>
                    <i data-lucide="chevron-down" class="w-4 h-4 text-[#3368A0] absolute right-4 top-1/2 -translate-y-1/2 pointer-events-none"></i>
                </div>
                <button type="button" id="resetFilterBtn" onclick="resetAllFilters()"
                    class="hidden items-center gap-1.5 px-4 h-[48px] rounded-[8px] bg-[#DC2626] hover:bg-[#B91C1C] active:scale-[0.98] text-white text-xs font-bold transition-all duration-200 cursor-pointer shrink-0">
                    <i data-lucide="rotate-ccw" class="w-3.5 h-3.5"></i>
                    <span>Reset</span>
                </button>
            </div>
        </div>

        <!-- Subtle 12px Split Map Workspace (Map on Left 864px + Daftar Aduan Aktif on Right 416px) -->
        <div class="bg-[#FAF8F4] rounded-[12px] shadow-[0_12px_32px_rgba(24,54,84,0.08)] overflow-hidden flex flex-col md:flex-row md:h-[576px]">
            <!-- Panel Kiri: Live Leaflet GIS Map Canvas (360px Height Mobile, 864px x 576px Desktop) -->
            <div class="relative w-full h-[360px] shrink-0 md:w-auto md:flex-1 md:shrink md:h-full bg-[#F2EFE7]">
                <div id="gisMap" class="w-full h-[360px] md:h-full relative z-10"></div>

                <div id="mapLoading" class="absolute inset-0 bg-[#183654]/12 flex items-center justify-center z-30 pointer-events-none transition-opacity duration-300">
                    <div class="bg-white px-5 py-3 rounded-[8px] shadow-lg flex items-center gap-2.5 text-xs font-bold text-[#183654]">
                        <i data-lucide="loader-2" class="w-4 h-4 animate-spin text-[#3368A0]"></i>
                        <span>Memuat titik persebaran laporan...</span>
                    </div>
                </div>

                <!-- Legend Status Peta (Subtle 8px Rounded, Single-row compact on mobile) -->
                <div class="absolute bottom-7 left-2.5 sm:bottom-4 sm:left-4 z-20 flex items-center gap-2.5 sm:gap-3.5 px-2.5 py-1.5 sm:px-3.5 sm:py-2 rounded-[8px] bg-white/95 backdrop-blur-xs shadow-[0_8px_24px_rgba(24,54,84,0.14)] text-[10px] sm:text-[11px] font-semibold text-[#183654] select-none whitespace-nowrap">
                    <span class="flex items-center gap-1.5">
                        <span class="w-2 h-2 rounded-[2px] bg-[#DC2626] shrink-0"></span>
                        Verifikasi
                    </span>
                    <span class="flex items-center gap-1.5">
                        <span class="w-2 h-2 rounded-[2px] bg-[#1E40AF] shrink-0"></span>
                        Ditugaskan
                    </span>
                    <span class="flex items-center gap-1.5">
                        <span class="w-2 h-2 rounded-[2px] bg-[#B45309] shrink-0"></span>
                        Diproses
                    </span>
                    <span class="flex items-center gap-1.5">
                        <span class="w-2 h-2 rounded-[2px] bg-[#047857] shrink-0"></span>
                        Selesai
                    </span>
                </div>
            </div>

            <!-- Panel Kanan: Daftar Aduan Aktif (Exact 416px Width, Smooth Mobile Collapse) -->
            <div id="reportDrawer" class="w-full md:w-[416px] md:h-full bg-[#FAF8F4] flex flex-col shrink-0 z-20">
                <!-- Solid Primary Blue Header -->
                <div class="px-5 h-[52px] md:h-[48px] bg-[#3368A0] text-white flex items-center justify-between gap-2 shrink-0">
                    <button type="button" id="filterCollapseBtn" onclick="toggleFilterPanelBody()" aria-expanded="true" aria-controls="filterDrawerBody"
                        class="flex-1 h-full flex items-center justify-between gap-2 text-left cursor-pointer focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-white md:cursor-default">
                        <span class="font-outfit font-bold text-[14px] text-white truncate">Daftar Aduan Aktif</span>
                        <i id="filterCollapseChevron" data-lucide="chevron-up" class="w-4 h-4 text-white shrink-0 transition-transform duration-300 smooth-ease md:hidden"></i>
                    </button>
                    <span id="drawerTotalText" class="text-[11px] font-semibold px-2.5 py-1 bg-[#183654] text-white rounded-[6px] shrink-0">
                        {{ $totalActiveCount }} Aduan
                    </span>
                </div>

                <!-- Drawer Body -->
                <div id="filterDrawerBody" class="flex flex-col flex-1 min-h-0 overflow-hidden transition-[max-height,opacity] duration-350 smooth-ease">
                    <!-- Status Filter Bar (Single Scrollable Row on Mobile, Single Row on Desktop, Subtle 6px Rounded) -->
                    <div id="mapStatusTabs" class="px-3 py-2.5 bg-[#F2EFE7] flex flex-nowrap items-center md:justify-between gap-1.5 md:gap-1 overflow-x-auto no-scrollbar shrink-0">
                        <button type="button" data-map-status="ALL" onclick="filterMapByStatus('ALL', false)"
                            class="map-status-tab px-2.5 md:px-2 py-1.5 md:py-1 rounded-[6px] text-[11px] md:text-[10.5px] font-semibold transition-all duration-200 cursor-pointer bg-[#3368A0] text-white whitespace-nowrap shrink-0">
                            Semua
                        </button>
                        <button type="button" data-map-status="SUBMITTED" onclick="filterMapByStatus('SUBMITTED', false)"
                            class="map-status-tab inline-flex items-center gap-1.5 md:gap-1 px-2.5 md:px-1.5 py-1.5 md:py-1 rounded-[6px] text-[11px] md:text-[10.5px] font-semibold transition-all duration-200 cursor-pointer bg-white text-[#2E4A66] hover:bg-[#E6E2D8] whitespace-nowrap shrink-0">
                            <span class="w-1.5 h-1.5 rounded-[2px] bg-[#DC2626] shrink-0"></span>Verifikasi
                        </button>
                        <button type="button" data-map-status="DISPATCHED" onclick="filterMapByStatus('DISPATCHED', false)"
                            class="map-status-tab inline-flex items-center gap-1.5 md:gap-1 px-2.5 md:px-1.5 py-1.5 md:py-1 rounded-[6px] text-[11px] md:text-[10.5px] font-semibold transition-all duration-200 cursor-pointer bg-white text-[#2E4A66] hover:bg-[#E6E2D8] whitespace-nowrap shrink-0">
                            <span class="w-1.5 h-1.5 rounded-[2px] bg-[#1E40AF] shrink-0"></span>Ditugaskan
                        </button>
                        <button type="button" data-map-status="PROCESSING" onclick="filterMapByStatus('PROCESSING', false)"
                            class="map-status-tab inline-flex items-center gap-1.5 md:gap-1 px-2.5 md:px-1.5 py-1.5 md:py-1 rounded-[6px] text-[11px] md:text-[10.5px] font-semibold transition-all duration-200 cursor-pointer bg-white text-[#2E4A66] hover:bg-[#E6E2D8] whitespace-nowrap shrink-0">
                            <span class="w-1.5 h-1.5 rounded-[2px] bg-[#B45309] shrink-0"></span>Diproses
                        </button>
                        <button type="button" data-map-status="COMPLETED" onclick="filterMapByStatus('COMPLETED', false)"
                            class="map-status-tab inline-flex items-center gap-1.5 md:gap-1 px-2.5 md:px-1.5 py-1.5 md:py-1 rounded-[6px] text-[11px] md:text-[10.5px] font-semibold transition-all duration-200 cursor-pointer bg-white text-[#2E4A66] hover:bg-[#E6E2D8] whitespace-nowrap shrink-0">
                            <span class="w-1.5 h-1.5 rounded-[2px] bg-[#047857] shrink-0"></span>Selesai
                        </button>
                    </div>

                    <!-- Active Category Chip (if filtered via Bidang Layanan tile) -->
                    <div id="activeCategoryBanner" class="hidden px-4 py-2 bg-[#183654] text-white text-[11px] font-semibold items-center justify-between gap-2 shrink-0">
                        <span id="activeCategoryLabel" class="truncate">Bidang: Semua</span>
                        <button type="button" onclick="clearCategoryFilter()" class="text-[#C8DFDB] hover:text-white font-bold cursor-pointer shrink-0">
                            Hapus &times;
                        </button>
                    </div>

                    <!-- Clean Editorial Scrollable Report List -->
                    <div id="drawerListContainer" class="divide-y divide-[#E6E2D8] overflow-y-auto flex-1 overscroll-contain bg-[#FAF8F4] max-h-[300px] md:max-h-none" style="-webkit-overflow-scrolling: touch;"></div>

                    <!-- Mini Footer Hint -->
                    <div class="px-5 py-3 bg-[#F2EFE7] text-[11.5px] text-[#49637A] shrink-0">
                        Pilih aduan untuk menyorot lokasi koordinat pada peta
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- SECTION 4: ALUR PENANGANAN ADUAN (1:1 FigCraft Node 931 · Height 368px Desktop, Warm Cream #F2EFE7) -->
<section id="alur-penanganan" class="relative z-20 w-full bg-[#F2EFE7] py-12 md:py-[52px] scroll-mt-[80px]">
    <div class="max-w-[1280px] mx-auto px-5 sm:px-6 xl:px-0">
        <div class="space-y-2">
            <p class="text-[12.5px] font-semibold text-[#3368A0]">
                Transparansi Layanan Publik
            </p>
            <h2 class="font-outfit font-bold text-[24px] md:text-[28px] text-[#183654] tracking-tight leading-tight">
                Bagaimana Laporan Anda Ditangani
            </h2>
        </div>

        <div class="mt-8 md:mt-9 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-8">
            <!-- Tahap 01 -->
            <div class="pt-5 border-t-[2px] border-t-[#3368A0] space-y-2.5">
                <span class="font-outfit font-bold text-[28px] text-[#3368A0] block leading-none">01</span>
                <h3 class="font-outfit font-bold text-[17px] text-[#183654] leading-snug pt-0.5">Laporkan dari Lokasi</h3>
                <p class="text-[13.5px] leading-[21px] text-[#2E4A66]">
                    Kirim foto kondisi kerusakan beserta titik GPS atau rute ruas jalan lewat aplikasi mobile.
                </p>
            </div>

            <!-- Tahap 02 -->
            <div class="pt-5 border-t-[2px] border-t-[#66A3BF] space-y-2.5">
                <span class="font-outfit font-bold text-[28px] text-[#3368A0] block leading-none">02</span>
                <h3 class="font-outfit font-bold text-[17px] text-[#183654] leading-snug pt-0.5">Diverifikasi Operator</h3>
                <p class="text-[13.5px] leading-[21px] text-[#2E4A66]">
                    Operator Dinas PUPR memeriksa laporan dan menugaskan regu yang bertanggung jawab.
                </p>
            </div>

            <!-- Tahap 03 -->
            <div class="pt-5 border-t-[2px] border-t-[#66A3BF] space-y-2.5">
                <span class="font-outfit font-bold text-[28px] text-[#3368A0] block leading-none">03</span>
                <h3 class="font-outfit font-bold text-[17px] text-[#183654] leading-snug pt-0.5">Ditangani Pekerja Lapangan</h3>
                <p class="text-[13.5px] leading-[21px] text-[#2E4A66]">
                    Pekerja Lapangan turun memperbaiki kerusakan fisik dan mengunggah dokumentasi bukti.
                </p>
            </div>

            <!-- Tahap 04 -->
            <div class="pt-5 border-t-[2px] border-t-[#3368A0] space-y-2.5">
                <span class="font-outfit font-bold text-[28px] text-[#3368A0] block leading-none">04</span>
                <h3 class="font-outfit font-bold text-[17px] text-[#183654] leading-snug pt-0.5">Uji Mutu &amp; Selesai</h3>
                <p class="text-[13.5px] leading-[21px] text-[#2E4A66]">
                    Operator memvalidasi hasil pekerjaan hingga tuntas dan memperbarui status peta terbuka.
                </p>
            </div>
        </div>
    </div>
</section>

<!-- SECTION 5 & FOOTER: APLIKASI MOBILE + DOKUMENTASI + FOOTER TRANSPARAN MENYATU -->
<section id="download-app" class="relative z-20 w-full bg-[#183654] pt-12 md:pt-[74px] pb-0 scroll-mt-[80px] overflow-hidden">
    <div class="absolute inset-0 pointer-events-none">
        <img src="{{ asset('images/hero/hero-1.webp') }}" alt="" decoding="async" class="w-full h-full object-cover object-center">
        <div class="absolute inset-0" style="background: linear-gradient(90deg, rgba(24,54,84,0.97) 0%, rgba(24,54,84,0.90) 48%, rgba(51,104,160,0.77) 100%);"></div>
    </div>

    <div class="relative z-10 max-w-[1280px] mx-auto px-5 sm:px-6 xl:px-0 pb-12 md:pb-[74px]">
        <div class="flex flex-col lg:flex-row items-stretch justify-between gap-8 lg:gap-[20px]">
            <!-- Kiri: Copy & 2 Pure White Store Buttons (Exact Width ~490px) -->
            <div class="w-full lg:w-[500px] flex flex-col justify-between shrink-0 space-y-5">
                <div class="space-y-4">
                    <p class="text-[12.5px] sm:text-[13px] font-semibold text-[#C8DFDB]">
                        <span class="sm:hidden">Aplikasi Resmi &bull; Android &amp; iOS</span>
                    </p>
                    <h2 class="font-outfit font-bold text-[30px] leading-[36px] sm:text-[42px] sm:leading-[50px] tracking-[-0.8px] text-white">
                        Laporkan Kerusakan<br>di Sekitar Anda
                    </h2>
                    <p class="text-[14px] sm:text-[15px] leading-[24px] text-[#F2EFE7] max-w-[484px]">
                        <span class="sm:hidden">Kirim foto kerusakan &amp; koordinat GPS langsung ke Dinas PUPR Kabupaten.</span>
                        <span class="hidden sm:inline">Setiap laporan Anda membantu Dinas PUPR mempercepat perbaikan jalan, lampu penerangan, dan fasilitas umum di seluruh 15 kecamatan.</span>
                    </p>
                </div>

                <!-- 2 Pure White Solid Store Buttons (Subtle 8px Rounded, Smooth Lift) -->
                <div class="space-y-4 pt-1">
                    <div class="grid grid-cols-2 sm:flex sm:flex-row items-center gap-3 sm:gap-4">
                        <a href="{{ $landingApkUrl }}" download="SI-APPIK.APK"
                            class="flex items-center justify-center sm:justify-start gap-3.5 bg-white hover:bg-[#F2EFE7] hover:-translate-y-0.5 active:scale-[0.98] text-[#183654] px-4 sm:px-5 sm:w-[206px] h-[54px] rounded-[8px] transition-all duration-250 smooth-ease focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-white">
                            <svg class="w-5 h-5 fill-[#3368A0] shrink-0" viewBox="0 0 24 24" aria-hidden="true">
                                <path d="M3,20.5V3.5C3,2.91 3.34,2.39 3.84,2.15L13.69,12L3.84,21.85C3.34,21.6 3,21.09 3,20.5M16.81,15.12L6.05,21.34L14.54,12.85L16.81,15.12M20.16,10.81C20.5,11.08 20.75,11.5 20.75,12C20.75,12.5 20.5,12.92 20.16,13.19L17.89,14.5L15.39,12L17.89,9.5L20.16,10.81M6.05,2.66L16.81,8.88L14.54,11.15L6.05,2.66Z" />
                            </svg>
                            <div class="text-left leading-tight">
                                <span class="text-[10px] uppercase tracking-[0.4px] text-[#49637A] block font-semibold">TERSEDIA DI</span>
                                <span class="font-outfit font-bold text-[15px] text-[#183654] block">Google Play</span>
                            </div>
                        </a>

                        <a href="https://apps.apple.com" target="_blank" rel="noopener noreferrer"
                            class="flex items-center justify-center sm:justify-start gap-3.5 bg-white hover:bg-[#F2EFE7] hover:-translate-y-0.5 active:scale-[0.98] text-[#183654] px-4 sm:px-5 sm:w-[206px] h-[54px] rounded-[8px] transition-all duration-250 smooth-ease focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-white">
                            <svg class="w-5 h-5 fill-[#3368A0] shrink-0" viewBox="0 0 24 24" aria-hidden="true">
                                <path d="M18.71 19.5c-.83 1.24-1.71 2.45-3.05 2.47-1.34.03-1.77-.79-3.29-.79-1.53 0-2 .77-3.27.82-1.31.05-2.3-1.32-3.14-2.53C4.25 17 2.94 12.45 4.7 9.39c.87-1.52 2.43-2.48 4.12-2.51 1.28-.02 2.5.87 3.29.87.78 0 2.26-1.07 3.81-.91.65.03 2.47.26 3.64 1.98-.09.06-2.17 1.28-2.15 3.81.03 3.02 2.65 4.03 2.68 4.04-.03.07-.42 1.44-1.38 2.83M15.97 6.37c.62-.75 1.04-1.8 1.01-2.87-.96.04-2.12.64-2.8 1.44-.59.68-1.11 1.77-1 2.82 1.07.08 2.17-.55 2.79-1.39z"/>
                            </svg>
                            <div class="text-left leading-tight">
                                <span class="text-[10px] uppercase tracking-[0.4px] text-[#49637A] block font-semibold">TERSEDIA DI</span>
                                <span class="font-outfit font-bold text-[15px] text-[#183654] block">App Store</span>
                            </div>
                        </a>
                    </div>
                </div>
            </div>

            <!-- Tengah: Full-Bleed Photo Card Laporan Terbaru (Subtle 10px Rounded) -->
            <div class="hidden lg:block w-[348px] h-[336px] shrink-0">
                @if($featuredComplaint)
                    @php
                        $fStatus = strtoupper((string) $featuredComplaint->status);
                        $fStatusLabel = 'Menunggu Verifikasi Operator';
                        if ($fStatus === 'DISPATCHED') $fStatusLabel = 'Ditugaskan ke Pekerja Lapangan';
                        elseif ($fStatus === 'IN_PROGRESS') $fStatusLabel = 'Sedang Dikerjakan Regu PUPR';
                        elseif ($fStatus === 'WAITING_VERIFICATION') $fStatusLabel = 'Menunggu Uji Mutu Operator';
                        elseif ($fStatus === 'REVISION_REQUIRED') $fStatusLabel = 'Dalam Revisi Lapangan';
                        elseif (in_array($fStatus, ['COMPLETED', 'CLOSED'], true)) $fStatusLabel = 'Selesai & Tervalidasi Operator';
                    @endphp
                    <div onclick="openFeaturedComplaint({{ $featuredComplaint->id }})" role="button" tabindex="0"
                        onkeydown="if(event.key==='Enter'||event.key===' '){event.preventDefault();openFeaturedComplaint({{ $featuredComplaint->id }});}"
                        class="group relative w-full h-full rounded-[10px] overflow-hidden bg-[#0A4174] cursor-pointer transition-all duration-400 smooth-ease hover:-translate-y-1 hover:shadow-[0_18px_36px_rgba(12,28,46,0.38)] focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-white">
                        <img src="{{ $featuredBeforeUrl ?: asset('images/hero/repair-jalan.webp') }}" alt="{{ $featuredComplaint->title }}"
                            class="absolute inset-0 w-full h-full object-cover object-center group-hover:scale-[1.05] transition-transform duration-700 smooth-ease"
                            onerror="this.src='{{ asset('images/hero/repair-jalan.webp') }}'">
                        <div class="absolute inset-0" style="background: linear-gradient(180deg, rgba(24,54,84,0) 0%, rgba(24,54,84,0.25) 44%, rgba(24,54,84,0.93) 82%, #183654 100%);"></div>
                        <div class="absolute inset-x-0 bottom-0 p-6 space-y-1.5">
                            <span class="block text-[11.5px] font-semibold text-[#C8DFDB]">
                                Terbaru &bull; #{{ $featuredComplaint->public_code }}
                            </span>
                            <h3 class="font-outfit font-bold text-[18.5px] leading-[23px] text-white line-clamp-2">
                                {{ $featuredComplaint->title }}
                            </h3>
                            <p class="text-[12px] text-[#F2EFE7] truncate">
                                {{ $featuredComplaint->address }}
                            </p>
                            <p class="pt-1 text-[11.5px] font-semibold text-white">
                                Status: {{ $fStatusLabel }}
                            </p>
                        </div>
                    </div>
                @endif
            </div>

            <!-- Kanan: Pure White QR Download Card (Subtle 10px Rounded) -->
            <div class="hidden lg:flex w-[372px] h-[336px] bg-white rounded-[10px] p-7 flex-col items-center justify-center text-center shrink-0 shadow-[0_14px_34px_rgba(12,28,46,0.18)]">
                <div class="w-[152px] h-[152px] p-3 bg-[#F2EFE7] rounded-[8px] flex items-center justify-center shrink-0">
                    <img src="https://api.qrserver.com/v1/create-qr-code/?size=160x160&data={{ urlencode($landingApkUrl) }}"
                        alt="QR Code Unduh Aplikasi SI-APPIK Android"
                        class="w-full h-full object-contain">
                </div>
                <h3 class="mt-4 font-outfit font-bold text-[17px] text-[#183654]">
                    Pindai QR Kamera Ponsel
                </h3>
                <p class="mt-2 text-[12.5px] leading-[19px] text-[#49637A] max-w-[290px]">
                    Arahkan kamera ponsel Anda untuk mengunduh aplikasi resmi SI-APPIK.
                </p>
                <span class="mt-4 text-[11.5px] font-semibold text-[#3368A0]">
                    Rilis Resmi &bull; Google Play &amp; App Store
                </span>
            </div>
        </div>
    </div>

    @if(!auth()->check())
    <!-- Footer Publik Transparan Menyatu (Tanpa Background Kotak, Hanya Garis Pemisah Atas 1px) -->
    <footer class="relative z-10 bg-transparent text-[#F2EFE7]">
        <div class="max-w-[1280px] mx-auto px-5 sm:px-6 xl:px-0">
            <div class="h-[1px] bg-[#C8DFDB]/30 w-full"></div>
            <div class="py-7 md:h-[108px] flex flex-col md:flex-row items-start md:items-center justify-between gap-5">
                <div class="flex items-center gap-3">
                    <div class="w-[34px] h-[34px] rounded-[6px] bg-white flex items-center justify-center p-1.5 shrink-0">
                        <img src="{{ asset('images/logo-pin.png') }}" class="w-full h-full object-contain" alt="Logo SI-APPIK">
                    </div>
                    <div>
                        <span class="font-outfit font-bold text-[16px] text-white block leading-tight">SI-APPIK Kabupaten</span>
                        <span class="text-[12px] text-[#F2EFE7] block mt-0.5">&copy; 2026 Pemerintah Kabupaten &bull; Dinas Pekerjaan Umum dan Penataan Ruang (PUPR)</span>
                    </div>
                </div>
                <div class="hidden md:flex items-center gap-7 text-[12.5px] font-normal text-[#F2EFE7]">
                    <a href="{{ url('/') }}#top" class="hover:text-white transition">Beranda</a>
                    <a href="{{ url('/') }}#kategori-infrastruktur" class="hover:text-white transition">Bidang Layanan</a>
                    <a href="{{ url('/') }}#mapSectionContainer" class="hover:text-white transition">Peta Sebaran</a>
                    <a href="{{ url('/') }}#alur-penanganan" class="hover:text-white transition">Alur Penanganan</a>
                    <a href="{{ route('login') }}" class="text-[#C8DFDB] hover:text-white font-semibold transition">Masuk Portal</a>
                </div>
            </div>
        </div>
    </footer>
    @endif
</section>

<!-- Modal Detail Pengaduan Publik (1:1 FigCraft Blue & Cream Node 2161) -->
@include('partials.modals.landing_complaint_detail_modal')

@push('scripts')
<style>
    .custom-map-tooltip {
        background-color: #183654 !important;
        color: #FFFFFF !important;
        font-weight: 700 !important;
        font-size: 11px !important;
        border-radius: 6px !important;
        padding: 6px 10px !important;
        border: none !important;
        box-shadow: 0 8px 20px rgba(24, 54, 84, 0.35) !important;
    }
    .custom-map-tooltip::before {
        border-top-color: #183654 !important;
    }
</style>

<script>
    let map;
    let markersLayer;
    let markersMap = {};
    let reportsData = @json($mapPoints ?? []);
    let isReportsLoaded = Array.isArray(reportsData) && reportsData.length > 0;
    let currentStatusFilter = 'ALL';
    let currentDistrictFilter = 'ALL';
    let currentCategoryFilter = null;
    let selectedReportId = null;
    let isFilterBodyOpen = window.innerWidth >= 768;

    function escapeHtml(str) {
        return String(str ?? '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#39;');
    }

    function getSolidStatusMeta(status) {
        const s = String(status || '').toUpperCase();
        if (s === 'DISPATCHED') {
            return { label: 'Ditugaskan', bgClass: 'bg-[#1E40AF] text-white', pinColor: '#1E40AF', group: 'DISPATCHED' };
        }
        if (s === 'IN_PROGRESS' || s === 'WAITING_VERIFICATION' || s === 'REVISION_REQUIRED') {
            return { label: 'Diproses', bgClass: 'bg-[#B45309] text-white', pinColor: '#B45309', group: 'PROCESSING' };
        }
        if (s === 'COMPLETED' || s === 'CLOSED' || s === 'RESOLVED') {
            return { label: 'Selesai', bgClass: 'bg-[#047857] text-white', pinColor: '#047857', group: 'COMPLETED' };
        }
        if (s === 'REJECTED') {
            return { label: 'Ditolak', bgClass: 'bg-[#991B1B] text-white', pinColor: '#991B1B', group: 'REJECTED' };
        }
        return { label: 'Verifikasi', bgClass: 'bg-[#DC2626] text-white', pinColor: '#DC2626', group: 'SUBMITTED' };
    }

    function hideMapLoading() {
        const el = document.getElementById('mapLoading');
        if (el) {
            el.style.display = 'none';
            el.classList.add('hidden');
        }
    }

    document.addEventListener('DOMContentLoaded', () => {
        initMap();
        syncFilterPanelBodyState();
        setupSearch();

        if (isReportsLoaded) {
            applyFilters(false);
            hideMapLoading();
        } else {
            loadMapData();
        }

        setTimeout(() => {
            hideMapLoading();
            if (map) map.invalidateSize();
        }, 600);

        window.addEventListener('resize', () => {
            if (window.innerWidth >= 768 && !isFilterBodyOpen) {
                isFilterBodyOpen = true;
                syncFilterPanelBodyState();
            }
            if (map) map.invalidateSize();
        });
    });

    function initMap() {
        const mapContainer = document.getElementById('gisMap');
        if (!mapContainer || mapContainer._leaflet_id || typeof L === 'undefined') return;

        map = L.map('gisMap', {
            zoomControl: false,
            scrollWheelZoom: true,
        }).setView([-3.50601, 120.88780], 11);

        L.control.zoom({ position: 'topleft' }).addTo(map);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '&copy; OpenStreetMap kontributor | SI-APPIK Kabupaten'
        }).addTo(map);

        markersLayer = L.layerGroup().addTo(map);

        fetch('{{ asset("geojson/kolaka_utara.geojson") }}')
            .then(res => res.json())
            .then(geoJsonData => {
                L.geoJSON(geoJsonData, {
                    interactive: false,
                    style: {
                        color: '#3368A0',
                        weight: 2,
                        dashArray: '6, 6',
                        fillColor: '#66A3BF',
                        fillOpacity: 0.08
                    }
                }).addTo(map);
            })
            .catch(() => {});

        setTimeout(() => {
            if (map) map.invalidateSize();
        }, 250);

        if (typeof ResizeObserver !== 'undefined') {
            const ro = new ResizeObserver(() => {
                if (map) map.invalidateSize();
            });
            ro.observe(mapContainer);
        }
    }

    async function loadMapData() {
        try {
            const resp = await fetch('{{ url("/api/v1/dashboard/map") }}');
            if (resp.ok) {
                const json = await resp.json();
                if (json && Array.isArray(json.data)) {
                    reportsData = json.data;
                    isReportsLoaded = true;
                    window.landingComplaintsMap = {};
                    reportsData.forEach(r => { window.landingComplaintsMap[r.id] = r; });
                    updateCategoryCounts(reportsData);
                    applyFilters(false);
                    if (typeof window._refreshActiveSearch === 'function') {
                        window._refreshActiveSearch();
                    }
                }
            }
        } catch (err) {
            console.error('Gagal memuat data titik peta:', err);
        } finally {
            isReportsLoaded = true;
            hideMapLoading();
        }
    }

    function updateCategoryCounts(data) {
        if (!Array.isArray(data)) return;
        const counts = { jalan: 0, pju: 0, fasilitas: 0 };
        data.forEach(item => {
            if (['REJECTED', 'DRAFT', 'CANCELLED'].includes(String(item.status || '').toUpperCase())) return;
            const catId = Number(item.category_id || 0);
            if (catId === 1) counts.jalan++;
            else if (catId === 2) counts.pju++;
            else if (catId === 3) counts.fasilitas++;
            else {
                const catName = (item.category_name || '').toLowerCase();
                if (catName.includes('penerangan') || catName.includes('pju')) counts.pju++;
                else if (catName.includes('fasilitas') || catName.includes('trotoar')) counts.fasilitas++;
                else if (catName.includes('jalan') || catName.includes('jembatan')) counts.jalan++;
            }
        });
        const mapIds = {
            catCountJalan: counts.jalan,
            catCountPju: counts.pju,
            catCountFasilitas: counts.fasilitas
        };
        Object.entries(mapIds).forEach(([id, val]) => {
            const el = document.getElementById(id);
            if (el) el.textContent = `${val} Laporan Aktif`;
        });
    }

    function syncFilterPanelBodyState() {
        const body = document.getElementById('filterDrawerBody');
        const chevron = document.getElementById('filterCollapseChevron');
        const btn = document.getElementById('filterCollapseBtn');
        if (!body) return;

        if (window.innerWidth >= 768 || isFilterBodyOpen) {
            body.style.maxHeight = window.innerWidth >= 768 ? 'none' : '560px';
            body.style.opacity = '1';
            if (chevron) chevron.style.transform = 'rotate(0deg)';
            if (btn) btn.setAttribute('aria-expanded', 'true');
        } else {
            body.style.maxHeight = '0px';
            body.style.opacity = '0';
            if (chevron) chevron.style.transform = 'rotate(180deg)';
            if (btn) btn.setAttribute('aria-expanded', 'false');
        }
    }

    function toggleFilterPanelBody() {
        if (window.innerWidth >= 768) return;
        isFilterBodyOpen = !isFilterBodyOpen;
        syncFilterPanelBodyState();
        setTimeout(() => { if (map) map.invalidateSize(); }, 320);
    }

    function onDistrictFilterChange(district) {
        currentDistrictFilter = district || 'ALL';
        applyFilters(true);
    }

    function filterMapByStatus(status, shouldScroll = true) {
        currentStatusFilter = status || 'ALL';
        if (!isFilterBodyOpen) {
            isFilterBodyOpen = true;
            syncFilterPanelBodyState();
        }
        applyFilters(true);

        if (shouldScroll) {
            const mapSection = document.getElementById('mapSectionContainer');
            if (mapSection) mapSection.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
    }

    function filterMapByCategory(categoryId) {
        currentCategoryFilter = Number(categoryId) || null;
        if (!isFilterBodyOpen) {
            isFilterBodyOpen = true;
            syncFilterPanelBodyState();
        }
        applyFilters(true);
        const mapSection = document.getElementById('mapSectionContainer');
        if (mapSection) mapSection.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }

    function clearCategoryFilter() {
        currentCategoryFilter = null;
        applyFilters(true);
    }

    function resetAllFilters() {
        currentStatusFilter = 'ALL';
        currentDistrictFilter = 'ALL';
        currentCategoryFilter = null;
        const distSelect = document.getElementById('districtFilterSelect');
        if (distSelect) distSelect.value = 'ALL';
        applyFilters(true);
    }

    function openFeaturedComplaint(id) {
        const found = reportsData.find(r => Number(r.id) === Number(id));
        if (found) {
            if (typeof openLandingDetailModal === 'function') openLandingDetailModal(found);
        } else if (typeof openLandingDetailModal === 'function') {
            openLandingDetailModal(id);
        }
    }

    function getDistrictDisplayName(rawAddress) {
        if (!rawAddress) return 'Kabupaten';
        const parts = rawAddress.split(',').map(s => s.trim());
        for (const p of parts) {
            if (p.toLowerCase().startsWith('kec.') || p.toLowerCase().startsWith('kecamatan')) {
                return p.replace(/^kecamatan\s*/i, 'Kec. ');
            }
        }
        return 'Kabupaten';
    }

    function showMapToast(message) {
        let toast = document.getElementById('mapToast');
        if (!toast) {
            toast = document.createElement('div');
            toast.id = 'mapToast';
            toast.className = 'fixed top-24 left-1/2 -translate-x-1/2 z-[90] max-w-[92vw] px-4 py-3 rounded-[8px] bg-[#183654] text-white text-xs font-semibold shadow-[0_14px_34px_rgba(12,28,46,0.3)] flex items-center gap-2.5 border-l-4 border-l-[#66A3BF] transition-all duration-300 smooth-ease opacity-0 pointer-events-none transform -translate-y-2';
            document.body.appendChild(toast);
        }
        toast.innerHTML = `<span class="leading-snug">${escapeHtml(message)}</span>`;
        toast.classList.remove('opacity-0', 'pointer-events-none', '-translate-y-2');
        toast.classList.add('opacity-100', 'translate-y-0');

        clearTimeout(window._mapToastTimer);
        window._mapToastTimer = setTimeout(() => {
            toast.classList.remove('opacity-100', 'translate-y-0');
            toast.classList.add('opacity-0', 'pointer-events-none', '-translate-y-2');
        }, 3200);
    }

    function applyFilters(shouldZoom = true) {
        document.querySelectorAll('.map-status-tab').forEach(btn => {
            const isMatch = btn.getAttribute('data-map-status') === currentStatusFilter;
            if (isMatch) {
                btn.classList.add('bg-[#3368A0]', 'text-white');
                btn.classList.remove('bg-white', 'text-[#2E4A66]', 'hover:bg-[#E6E2D8]');
            } else {
                btn.classList.remove('bg-[#3368A0]', 'text-white');
                btn.classList.add('bg-white', 'text-[#2E4A66]', 'hover:bg-[#E6E2D8]');
            }
        });

        const catBanner = document.getElementById('activeCategoryBanner');
        const catLabel = document.getElementById('activeCategoryLabel');
        const catNames = { 1: 'Jalan & Jembatan', 2: 'Penerangan Jalan', 3: 'Fasilitas Umum' };
        if (catBanner && catLabel) {
            if (currentCategoryFilter) {
                catLabel.textContent = `Bidang: ${catNames[currentCategoryFilter] || 'Infrastruktur'}`;
                catBanner.classList.remove('hidden');
                catBanner.classList.add('flex');
            } else {
                catBanner.classList.add('hidden');
                catBanner.classList.remove('flex');
            }
        }

        const resetBtn = document.getElementById('resetFilterBtn');
        const hasAnyFilter = currentStatusFilter !== 'ALL' || currentDistrictFilter !== 'ALL' || currentCategoryFilter !== null;
        if (resetBtn) {
            resetBtn.classList.toggle('hidden', !hasAnyFilter);
            resetBtn.classList.toggle('inline-flex', hasAnyFilter);
        }

        const summaryEl = document.getElementById('drawerFilterStatusText');
        const statusLabels = {
            SUBMITTED: 'Verifikasi',
            DISPATCHED: 'Ditugaskan',
            PROCESSING: 'Diproses',
            COMPLETED: 'Selesai'
        };
        const summaryParts = [];
        if (currentStatusFilter !== 'ALL' && statusLabels[currentStatusFilter]) summaryParts.push(statusLabels[currentStatusFilter]);
        if (currentCategoryFilter && catNames[currentCategoryFilter]) summaryParts.push(catNames[currentCategoryFilter]);
        if (currentDistrictFilter !== 'ALL') summaryParts.push(currentDistrictFilter.replace(/^Kecamatan\s*/i, 'Kec. '));
        if (summaryEl) {
            summaryEl.textContent = summaryParts.length > 0
                ? `Filter aktif: ${summaryParts.join(' · ')}`
                : 'Pilih kecamatan atau status untuk melihat titik kerusakan maupun ruas jalan yang sedang ditangani.';
        }

        let filtered = (reportsData || []).filter(r => !['REJECTED', 'DRAFT', 'CANCELLED'].includes(String(r.status || '').toUpperCase()));

        if (currentStatusFilter === 'PROCESSING') {
            filtered = filtered.filter(r => ['SUBMITTED', 'VERIFICATION', 'VERIFIED', 'DISPATCHED', 'IN_PROGRESS', 'WAITING_VERIFICATION', 'REVISION_REQUIRED'].includes(r.status));
        } else if (currentStatusFilter === 'SUBMITTED') {
            filtered = filtered.filter(r => ['SUBMITTED', 'VERIFICATION', 'VERIFIED'].includes(r.status));
        } else if (currentStatusFilter === 'DISPATCHED') {
            filtered = filtered.filter(r => r.status === 'DISPATCHED');
        } else if (currentStatusFilter === 'COMPLETED') {
            filtered = filtered.filter(r => ['COMPLETED', 'CLOSED'].includes(r.status));
        }

        if (currentCategoryFilter) {
            filtered = filtered.filter(r => Number(r.category_id || 0) === currentCategoryFilter);
        }

        if (currentDistrictFilter && currentDistrictFilter !== 'ALL') {
            const cleanDist = currentDistrictFilter.replace(/^(Kecamatan|Kec\.)\s*/i, '').trim().toLowerCase();
            filtered = filtered.filter(r => r.address && r.address.toLowerCase().includes(cleanDist));
        }

        const drawerTotalText = document.getElementById('drawerTotalText');
        if (drawerTotalText) drawerTotalText.textContent = `${filtered.length} Aduan`;

        renderDrawerList(filtered);

        if (shouldZoom && filtered.length === 0) {
            if (currentDistrictFilter && currentDistrictFilter !== 'ALL') {
                showMapToast(`Belum ada laporan di ${currentDistrictFilter.replace(/^Kecamatan\s*/i, 'Kecamatan ')} untuk filter ini.`);
            } else {
                showMapToast('Belum ada laporan untuk kombinasi filter yang dipilih.');
            }
        }

        renderMarkers(filtered, shouldZoom);
    }

    function renderDrawerList(items) {
        const container = document.getElementById('drawerListContainer');
        if (!container) return;

        if (!items || items.length === 0) {
            container.innerHTML = `
                <div class="py-12 px-5 text-center space-y-2.5 bg-white">
                    <h4 class="font-outfit font-bold text-sm text-[#183654]">Tidak Ada Titik Aduan</h4>
                    <p class="text-xs text-[#49637A] max-w-[240px] mx-auto leading-relaxed">
                        Tidak ada laporan yang sesuai dengan filter wilayah, bidang, atau status saat ini.
                    </p>
                    <button type="button" onclick="resetAllFilters()"
                        class="mt-2 px-4 py-2 rounded-[8px] bg-[#3368A0] hover:bg-[#275485] text-white text-xs font-semibold transition-all duration-200 cursor-pointer">
                        Tampilkan Semua Titik
                    </button>
                </div>
            `;
            return;
        }

        let html = '';
        items.forEach((item, index) => {
            const adm = parseAdministrativeAddress(item.address);
            const districtText = getDistrictDisplayName(item.address);
            const villageText = adm.village && adm.village !== 'Desa Wilayah'
                ? adm.village.replace(/^Desa\/Kel\.\s*/i, 'Kel. ')
                : (adm.road && adm.road !== 'Jalan / Area Kerusakan' ? adm.road : '');
            const locStr = villageText && villageText !== districtText ? `${villageText}, ${districtText}` : districtText;
            const badge = getSolidStatusMeta(item.status);
            const dateShort = item.created_at_formatted ? item.created_at_formatted.split(',')[0] : '';

            const safeTitle = escapeHtml(item.title || 'Laporan Kerusakan');
            const safeCode = escapeHtml(item.public_code || 'ADU-0000');
            const safeDate = escapeHtml(dateShort);
            const safeLoc = escapeHtml(locStr);
            const safeCat = escapeHtml(item.category_name || 'Infrastruktur');

            const isSelected = selectedReportId ? Number(selectedReportId) === Number(item.id) : index === 0;
            const rowClass = isSelected
                ? 'bg-[#F2EFE7] border-l-[3px] border-l-[#3368A0]'
                : 'bg-white hover:bg-[#F2EFE7]/70 border-l-[3px] border-l-transparent hover:border-l-[#3368A0]';

            html += `
                <div onclick="focusReportOnMap(${Number(item.id)})" role="button" tabindex="0"
                    onkeydown="if(event.key==='Enter'||event.key===' '){event.preventDefault();focusReportOnMap(${Number(item.id)});}"
                    class="px-5 py-4 ${rowClass} cursor-pointer transition-colors space-y-1.5 group">
                    <div class="flex items-center justify-between gap-2">
                        <span class="text-[11px] font-medium text-[#49637A] truncate">
                            #${safeCode}${safeDate ? ' &bull; ' + safeDate : ''}
                        </span>
                        <span class="text-[10.5px] font-semibold px-2.5 py-0.5 rounded-[6px] shrink-0 ${badge.bgClass}">
                            ${badge.label}
                        </span>
                    </div>
                    <h4 class="font-outfit font-bold text-[14px] text-[#183654] truncate group-hover:text-[#3368A0] transition-colors">
                        ${safeTitle}
                    </h4>
                    <p class="text-[11.5px] text-[#49637A] truncate">
                        ${safeLoc} &bull; ${safeCat}
                    </p>
                </div>
            `;
        });

        container.innerHTML = html;
    }

    function focusReportOnMap(id) {
        selectedReportId = Number(id);
        const item = reportsData.find(r => Number(r.id) === Number(id));
        if (!item) return;

        applyFilters(false);

        if (map && item.latitude && item.longitude) {
            map.flyTo([item.latitude, item.longitude], 16, {
                animate: true,
                duration: 0.85
            });
        }

        setTimeout(() => {
            if (typeof openLandingDetailModal === 'function') {
                openLandingDetailModal(item);
            } else if (typeof openDetailModal === 'function') {
                openDetailModal(item);
            }
        }, 450);
    }

    function renderMarkers(items, shouldZoom = true) {
        if (!markersLayer || typeof L === 'undefined') return;
        markersLayer.clearLayers();
        markersMap = {};
        if (!items || items.length === 0) return;

        const bounds = [];

        items.forEach(item => {
            if (!item.latitude || !item.longitude) return;
            if (['REJECTED', 'DRAFT', 'CANCELLED'].includes(String(item.status || '').toUpperCase())) return;

            const latLng = [item.latitude, item.longitude];
            bounds.push(latLng);

            const meta = getSolidStatusMeta(item.status);
            const pinColor = meta.pinColor;
            const statusText = meta.label;

            const customIcon = L.divIcon({
                className: 'custom-pin',
                html: `
                    <div style="
                        background-color: ${pinColor};
                        width: 20px;
                        height: 20px;
                        border-radius: 50%;
                        border: 3px solid #FFFFFF;
                        box-shadow: 0 4px 12px rgba(24, 54, 84, 0.36);
                        cursor: pointer;
                        transition: transform 0.15s ease;
                    " onmouseover="this.style.transform='scale(1.25)'" onmouseout="this.style.transform='scale(1)'"></div>
                `,
                iconSize: [20, 20],
                iconAnchor: [10, 10]
            });

            const marker = L.marker(latLng, { icon: customIcon }).addTo(markersLayer);
            markersMap[item.id] = marker;

            if (item.damage_type === 'major' && item.end_latitude && item.end_longitude) {
                let lineCoords = [latLng, [item.end_latitude, item.end_longitude]];
                if (Array.isArray(item.route_coordinates) && item.route_coordinates.length > 1) {
                    lineCoords = item.route_coordinates;
                }
                const polyline = L.polyline(lineCoords, {
                    color: pinColor,
                    weight: 5,
                    opacity: 0.9,
                    dashArray: '6, 8',
                    lineCap: 'butt'
                }).addTo(markersLayer);

                polyline.bindTooltip(`Ruas Jalan Rusak: ${escapeHtml(item.road_length || 'Panjang')} · ${statusText}`, {
                    direction: 'top',
                    className: 'custom-map-tooltip'
                });

                polyline.on('click', () => {
                    if (typeof openLandingDetailModal === 'function') openLandingDetailModal(item);
                });

                bounds.push([item.end_latitude, item.end_longitude]);
            }

            const shortTitle = item.title && item.title.length > 28 ? item.title.substring(0, 28) + '...' : (item.title || '');
            marker.bindTooltip(`${escapeHtml(item.public_code)} · ${escapeHtml(shortTitle)} (${statusText})`, {
                direction: 'top',
                offset: [0, -12],
                className: 'custom-map-tooltip'
            });

            marker.on('click', () => {
                if (typeof openLandingDetailModal === 'function') openLandingDetailModal(item);
            });
        });

        if (shouldZoom && bounds.length > 0 && map) {
            if (bounds.length === 1) {
                map.flyTo(bounds[0], 15, { animate: true, duration: 0.85 });
            } else {
                map.fitBounds(bounds, { padding: [50, 50], maxZoom: 15 });
            }
        }
    }

    function parseAdministrativeAddress(rawAddress) {
        let road = 'Jalan / Area Kerusakan';
        let village = 'Desa Wilayah';
        let district = 'Kecamatan Terkait';
        let regency = 'Kabupaten';

        if (!rawAddress) return { road, village, district, regency };

        const parts = rawAddress.split(',').map(s => s.trim());
        parts.forEach(p => {
            const lower = p.toLowerCase();
            if (lower.startsWith('desa') || lower.startsWith('kel.')) village = p;
            else if (lower.startsWith('kec.')) district = p;
            else if (lower.startsWith('kab.') || lower.startsWith('kota') || lower === 'kabupaten') regency = p;
            else if (!lower.includes('titik koordinat') && road === 'Jalan / Area Kerusakan') road = p;
        });

        if (parts.length === 1 && !rawAddress.toLowerCase().includes('titik koordinat')) {
            road = rawAddress;
        }
        return { road, village, district, regency };
    }

    window.openDetailModal = function(item) {
        if (typeof openLandingDetailModal === 'function') openLandingDetailModal(item);
    };
    window.closeDetailModal = function() {
        if (typeof closeLandingDetailModal === 'function') closeLandingDetailModal();
    };

    function setupSearch() {
        const form = document.getElementById('trackForm');
        const input = document.getElementById('trackCodeInput');
        const clearBtn = document.getElementById('clearSearchBtn');
        const suggestionsBox = document.getElementById('searchSuggestions');
        const suggestionsList = document.getElementById('searchSuggestionsList');
        const countSpan = document.getElementById('searchResultCount');
        const container = document.getElementById('searchContainer');
        const chipsContainer = document.getElementById('searchStatusChips');

        if (!form || !input || !suggestionsBox || !suggestionsList) return;

        let currentMatches = [];
        let displayMatches = [];
        let highlightedIndex = -1;
        let searchStatusFilter = 'ALL';

        function normalizeText(text) {
            return (text || '').toLowerCase().trim();
        }

        function updateClearButton() {
            if (!clearBtn) return;
            const hasVal = input.value.trim().length > 0;
            clearBtn.classList.toggle('hidden', !hasVal);
            clearBtn.classList.toggle('inline-flex', hasVal);
            input.classList.toggle('pr-24', hasVal);
            input.classList.toggle('pr-4', !hasVal);
        }

        function filterByStatusChip(list, chipStatus) {
            if (chipStatus === 'ALL') return list;
            if (chipStatus === 'SUBMITTED') return list.filter(r => ['SUBMITTED', 'VERIFICATION', 'VERIFIED'].includes(r.status));
            if (chipStatus === 'DISPATCHED') return list.filter(r => r.status === 'DISPATCHED');
            if (chipStatus === 'PROCESSING') return list.filter(r => ['IN_PROGRESS', 'WAITING_VERIFICATION', 'REVISION_REQUIRED'].includes(r.status));
            if (chipStatus === 'COMPLETED') return list.filter(r => ['COMPLETED', 'CLOSED'].includes(r.status));
            return list;
        }

        function syncSearchChipCountsAndStyles(queryMatched) {
            if (!chipsContainer) return;
            const counts = {
                ALL: queryMatched.length,
                SUBMITTED: filterByStatusChip(queryMatched, 'SUBMITTED').length,
                DISPATCHED: filterByStatusChip(queryMatched, 'DISPATCHED').length,
                PROCESSING: filterByStatusChip(queryMatched, 'PROCESSING').length,
                COMPLETED: filterByStatusChip(queryMatched, 'COMPLETED').length
            };
            const labels = {
                ALL: 'Semua',
                SUBMITTED: 'Verifikasi',
                DISPATCHED: 'Ditugaskan',
                PROCESSING: 'Diproses',
                COMPLETED: 'Selesai'
            };

            chipsContainer.querySelectorAll('.search-status-chip').forEach(btn => {
                const key = btn.getAttribute('data-search-status') || 'ALL';
                const isMatch = key === searchStatusFilter;
                btn.textContent = `${labels[key]} (${counts[key]})`;
                if (isMatch) {
                    btn.classList.add('bg-[#3368A0]', 'text-white', 'font-semibold');
                    btn.classList.remove('bg-transparent', 'text-[#2E4A66]', 'hover:bg-white', 'font-medium');
                } else {
                    btn.classList.remove('bg-[#3368A0]', 'text-white', 'font-semibold');
                    btn.classList.add('bg-transparent', 'text-[#2E4A66]', 'hover:bg-white', 'font-medium');
                }
            });
        }

        function showSuggestionsPanel() {
            if (suggestionsBox.classList.contains('hidden')) {
                suggestionsBox.classList.remove('hidden');
                requestAnimationFrame(() => {
                    suggestionsBox.classList.remove('opacity-0', '-translate-y-1.5');
                    suggestionsBox.classList.add('opacity-100', 'translate-y-0');
                });
            } else {
                suggestionsBox.classList.remove('opacity-0', '-translate-y-1.5');
                suggestionsBox.classList.add('opacity-100', 'translate-y-0');
            }
        }

        function hideSuggestionsPanel() {
            suggestionsBox.classList.add('hidden', 'opacity-0', '-translate-y-1.5');
            suggestionsBox.classList.remove('opacity-100', 'translate-y-0');
            highlightedIndex = -1;
        }

        function doSearch(query, allowEmptyBrowse = false) {
            updateClearButton();
            const q = normalizeText(query);

            if (!q && !allowEmptyBrowse) {
                hideSuggestionsPanel();
                currentMatches = [];
                displayMatches = [];
                return;
            }

            if (!isReportsLoaded && reportsData.length === 0) {
                if (countSpan) countSpan.textContent = 'Memuat data...';
                suggestionsList.innerHTML = `
                    <div class="p-6 text-center text-xs font-semibold text-[#49637A] flex items-center justify-center gap-2">
                        <i data-lucide="loader-2" class="w-4 h-4 animate-spin text-[#3368A0]"></i>
                        <span>Memuat basis data aduan...</span>
                    </div>
                `;
                showSuggestionsPanel();
                if (window.lucide) lucide.createIcons({ root: suggestionsBox });
                return;
            }

            const qClean = q.replace(/[^a-z0-9]/g, '');
            const activeReports = (reportsData || []).filter(r => !['REJECTED', 'DRAFT', 'CANCELLED'].includes(String(r.status || '').toUpperCase()));

            const queryMatched = q
                ? activeReports.filter(r => {
                    const title = normalizeText(r.title);
                    const code = normalizeText(r.public_code);
                    const cleanCode = code.replace(/[^a-z0-9]/g, '');
                    const reporter = normalizeText(r.reporter_name || r.citizen_name);
                    const address = normalizeText(r.address);
                    const category = normalizeText(r.category_name);
                    const team = normalizeText(r.team_name);
                    const desc = normalizeText(r.description);

                    return title.includes(q) ||
                           code.includes(q) ||
                           (qClean && cleanCode.includes(qClean)) ||
                           reporter.includes(q) ||
                           address.includes(q) ||
                           category.includes(q) ||
                           team.includes(q) ||
                           desc.includes(q);
                })
                : activeReports.slice();

            if (q) {
                queryMatched.sort((a, b) => {
                    const aStarts = normalizeText(a.title).startsWith(q) || normalizeText(a.public_code).startsWith(q);
                    const bStarts = normalizeText(b.title).startsWith(q) || normalizeText(b.public_code).startsWith(q);
                    if (aStarts && !bStarts) return -1;
                    if (!aStarts && bStarts) return 1;
                    return 0;
                });
            }

            syncSearchChipCountsAndStyles(queryMatched);
            currentMatches = filterByStatusChip(queryMatched, searchStatusFilter);
            displayMatches = currentMatches.slice(0, 8);
            highlightedIndex = -1;

            if (countSpan) {
                const rawTrim = query.trim();
                countSpan.textContent = rawTrim
                    ? `${currentMatches.length} aduan ditemukan untuk "${rawTrim}"`
                    : `${currentMatches.length} aduan ditemukan`;
            }

            if (displayMatches.length === 0) {
                const safeQ = escapeHtml(query.trim());
                suggestionsList.innerHTML = `
                    <div class="p-7 text-center space-y-2">
                        <p class="font-outfit font-bold text-sm text-[#183654]">
                            ${safeQ ? `Tidak ada aduan cocok untuk "${safeQ}"` : 'Tidak ada aduan pada status ini'}
                        </p>
                        <p class="text-xs text-[#49637A] max-w-sm mx-auto leading-relaxed">
                            Coba kata kunci lain seperti <strong class="text-[#183654]">Lasusua</strong>, <strong class="text-[#183654]">PJU</strong>, <strong class="text-[#183654]">aspal</strong>, atau ubah filter status di atas.
                        </p>
                    </div>
                `;
            } else {
                suggestionsList.innerHTML = displayMatches.map((r, idx) => {
                    const badge = getSolidStatusMeta(r.status);
                    const adm = parseAdministrativeAddress(r.address);
                    const districtText = getDistrictDisplayName(r.address);
                    const roadSnippet = adm.road && adm.road !== 'Jalan / Area Kerusakan' ? adm.road : districtText;

                    const safeTitle = escapeHtml(r.title || 'Laporan Kerusakan');
                    const safeCode = escapeHtml(r.public_code || 'ADU-0000');
                    const safeCat = escapeHtml(r.category_name || 'Infrastruktur');
                    const safeLoc = escapeHtml(roadSnippet !== districtText ? `${roadSnippet}, ${districtText}` : districtText);

                    return `
                        <div class="suggestion-item px-5 py-4 bg-white hover:bg-[#F2EFE7] border-l-[4px] border-l-transparent hover:border-l-[#3368A0] transition-colors flex items-center justify-between gap-4 cursor-pointer group" data-index="${idx}">
                            <div class="min-w-0 flex-1 space-y-1">
                                <div class="font-outfit font-bold text-[15px] text-[#183654] truncate leading-snug">
                                    ${safeTitle}
                                </div>
                                <div class="text-[12.5px] text-[#49637A] flex flex-wrap items-center gap-x-2 gap-y-0.5">
                                    <span class="font-medium text-[#49637A]">#${safeCode}</span>
                                    <span class="text-[#C8DFDB]">&bull;</span>
                                    <span>${safeCat}</span>
                                    <span class="text-[#C8DFDB]">&bull;</span>
                                    <span class="truncate max-w-[260px]">${safeLoc}</span>
                                </div>
                            </div>
                            <div class="flex flex-col items-end gap-1.5 shrink-0">
                                <span class="px-3 py-1 rounded-[6px] text-[11px] font-semibold ${badge.bgClass}">
                                    ${badge.label}
                                </span>
                                <span class="hidden sm:inline text-[11.5px] font-semibold text-[#3368A0] opacity-0 group-hover:opacity-100 transition-opacity duration-200">
                                    Lihat Detail &rarr;
                                </span>
                            </div>
                        </div>
                    `;
                }).join('');
            }

            showSuggestionsPanel();

            suggestionsList.querySelectorAll('.suggestion-item').forEach(el => {
                el.addEventListener('click', () => {
                    const idx = parseInt(el.getAttribute('data-index'), 10);
                    if (displayMatches[idx]) selectReport(displayMatches[idx]);
                });
            });
        }

        window._refreshActiveSearch = function() {
            if (!suggestionsBox.classList.contains('hidden')) {
                doSearch(input.value, true);
            }
        };

        function updateHighlight() {
            const items = suggestionsList.querySelectorAll('.suggestion-item');
            items.forEach((el, idx) => {
                const isSelected = idx === highlightedIndex;
                el.classList.toggle('bg-[#F2EFE7]', isSelected);
                el.classList.toggle('bg-white', !isSelected);
                el.classList.toggle('border-l-[#3368A0]', isSelected);
                el.classList.toggle('border-l-transparent', !isSelected);
                if (isSelected) el.scrollIntoView({ block: 'nearest' });
            });
        }

        function selectReport(item) {
            input.value = item.title ? `${item.public_code} - ${item.title}` : (item.public_code || '');
            updateClearButton();
            hideSuggestionsPanel();

            const mapSection = document.getElementById('mapSectionContainer');
            if (mapSection) mapSection.scrollIntoView({ behavior: 'smooth', block: 'start' });

            if (map && item.latitude && item.longitude) {
                map.flyTo([item.latitude, item.longitude], 16, { animate: true, duration: 0.85 });
            }

            setTimeout(() => {
                if (typeof openLandingDetailModal === 'function') openLandingDetailModal(item);
                else if (typeof openDetailModal === 'function') openDetailModal(item);
            }, 420);
        }

        if (chipsContainer) {
            chipsContainer.querySelectorAll('.search-status-chip').forEach(btn => {
                btn.addEventListener('click', (e) => {
                    e.preventDefault();
                    e.stopPropagation();
                    searchStatusFilter = btn.getAttribute('data-search-status') || 'ALL';
                    doSearch(input.value, true);
                    input.focus();
                });
            });
        }

        if (clearBtn) {
            clearBtn.addEventListener('click', (e) => {
                e.preventDefault();
                e.stopPropagation();
                input.value = '';
                searchStatusFilter = 'ALL';
                updateClearButton();
                hideSuggestionsPanel();
                currentMatches = [];
                displayMatches = [];
                input.focus();
            });
        }

        input.addEventListener('input', (e) => doSearch(e.target.value, false));
        input.addEventListener('focus', () => {
            if (input.value.trim()) doSearch(input.value, false);
        });

        input.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                hideSuggestionsPanel();
                return;
            }
            if (suggestionsBox.classList.contains('hidden') || displayMatches.length === 0) return;

            if (e.key === 'ArrowDown') {
                e.preventDefault();
                highlightedIndex = (highlightedIndex + 1) % displayMatches.length;
                updateHighlight();
            } else if (e.key === 'ArrowUp') {
                e.preventDefault();
                highlightedIndex = (highlightedIndex - 1 + displayMatches.length) % displayMatches.length;
                updateHighlight();
            } else if (e.key === 'Enter') {
                e.preventDefault();
                if (highlightedIndex >= 0 && displayMatches[highlightedIndex]) selectReport(displayMatches[highlightedIndex]);
                else if (displayMatches.length > 0) selectReport(displayMatches[0]);
            }
        });

        form.addEventListener('submit', (e) => {
            e.preventDefault();
            if (!input.value.trim()) {
                doSearch('', true);
                input.focus();
                return;
            }
            if (suggestionsBox.classList.contains('hidden')) {
                doSearch(input.value, false);
                return;
            }
            if (highlightedIndex >= 0 && displayMatches[highlightedIndex]) selectReport(displayMatches[highlightedIndex]);
            else if (displayMatches.length > 0) selectReport(displayMatches[0]);
            else doSearch(input.value, false);
        });

        document.addEventListener('click', (e) => {
            if (container && !container.contains(e.target)) {
                hideSuggestionsPanel();
            }
        });
    }

    // Hero Background Slideshow synced 1:1 with Right Index Rail (01 / 02 / 03 / 04) & Bottom Progress Bar
    (function initHeroSlideshow() {
        const bgWrapper = document.getElementById('landingHeroSlideshow');
        const slides = Array.from(document.querySelectorAll('#landingHeroSlideshow .landing-hero-slide'));
        const railButtons = Array.from(document.querySelectorAll('#heroSlideRail .hero-rail-btn'));
        const progressBar = document.getElementById('heroBottomProgressBar');
        const reduced = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
        let currentSlide = 0;
        let slideTimer = null;

        function ensureSlideLoaded(slide) {
            if (slide && slide.dataset && slide.dataset.src && !slide.getAttribute('src')) {
                slide.src = slide.dataset.src;
            }
        }

        function syncSlideUI(idx) {
            slides.forEach((s, i) => {
                if (i === idx) {
                    ensureSlideLoaded(s);
                    s.classList.remove('opacity-0');
                    s.classList.add('opacity-100', 'active-slide');
                } else {
                    s.classList.remove('opacity-100', 'active-slide');
                    s.classList.add('opacity-0');
                }
            });

            railButtons.forEach((btn, i) => {
                const num = btn.querySelector('.hero-rail-num');
                const line = btn.querySelector('.hero-rail-line');
                const active = i === idx;
                if (num) {
                    num.classList.toggle('text-[28px]', active);
                    num.classList.toggle('font-bold', active);
                    num.classList.toggle('text-white', active);
                    num.classList.toggle('text-[14px]', !active);
                    num.classList.toggle('font-semibold', !active);
                    num.classList.toggle('text-[#C8DFDB]', !active);
                }
                if (line) {
                    line.classList.toggle('opacity-100', active);
                    line.classList.toggle('opacity-0', !active);
                }
            });

            if (progressBar && slides.length > 0) {
                progressBar.style.width = `${((idx + 1) / slides.length) * 100}%`;
            }
        }

        function startTimer() {
            if (slideTimer) clearInterval(slideTimer);
            if (slides.length > 1 && !reduced) {
                slideTimer = setInterval(() => {
                    currentSlide = (currentSlide + 1) % slides.length;
                    syncSlideUI(currentSlide);
                }, 5500);
            }
        }

        window.goToHeroSlide = function(idx) {
            if (idx < 0 || idx >= slides.length) return;
            currentSlide = idx;
            syncSlideUI(currentSlide);
            startTimer();
        };

        if (slides.length > 1) {
            window.addEventListener('load', () => {
                setTimeout(() => slides.forEach(ensureSlideLoaded), 1200);
            }, { once: true });
            syncSlideUI(0);
            startTimer();
        }

        if (bgWrapper && !reduced) {
            let ticking = false;
            window.addEventListener('scroll', () => {
                if (ticking) return;
                ticking = true;
                window.requestAnimationFrame(() => {
                    const y = Math.min(window.scrollY || 0, 520);
                    bgWrapper.style.transform = `translate3d(0, ${y * 0.14}px, 0)`;
                    ticking = false;
                });
            }, { passive: true });
        }
    })();
</script>
@endpush
@endsection
