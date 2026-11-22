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

    $featuredDistrict = 'Kabupaten';
    $featuredStatusDesc = 'Menunggu Verifikasi Operator Dinas PUPR';
    if ($featuredComplaint) {
        $addrParts = array_map('trim', explode(',', (string) $featuredComplaint->address));
        foreach ($addrParts as $p) {
            if (stripos($p, 'kec.') === 0 || stripos($p, 'kecamatan') === 0) {
                $featuredDistrict = preg_replace('/^kecamatan\s*/i', 'Kec. ', $p);
                break;
            }
        }
        $fSt = strtoupper((string) $featuredComplaint->status);
        if ($fSt === 'DISPATCHED') {
            $featuredStatusDesc = 'Ditugaskan ke Pekerja Lapangan Dinas PUPR';
        } elseif (in_array($fSt, ['IN_PROGRESS', 'REVISION_REQUIRED'], true)) {
            $featuredStatusDesc = 'Sedang Dikerjakan Pekerja Lapangan Dinas PUPR';
        } elseif ($fSt === 'WAITING_VERIFICATION') {
            $featuredStatusDesc = 'Menunggu Uji Mutu Operator Dinas PUPR';
        } elseif (in_array($fSt, ['COMPLETED', 'CLOSED'], true)) {
            $featuredStatusDesc = 'Selesai & Tervalidasi Operator Dinas PUPR';
        }
    }
@endphp

<!-- SECTION 2: SPLIT EDITORIAL MAGAZINE HERO (1:1 FigCraft Page 1128 Node 1148 · Warm Ivory #FFF9F2 & Maroon #800020) -->
<section id="top" class="relative z-30 w-full bg-[#FFF9F2] pt-6 pb-10 md:pt-[56px] md:pb-[64px]">
    <style>
        @keyframes maroonHeroKenBurns {
            0%   { transform: scale(1.08); }
            50%  { transform: scale(1.00); }
            100% { transform: scale(1.08); }
        }
        #landingHeroSlideshow .landing-hero-slide.active-slide {
            animation: maroonHeroKenBurns 13s ease-in-out infinite;
        }
    </style>

    <div class="max-w-[1280px] mx-auto px-5 sm:px-6 xl:px-0">
        <div class="flex flex-col lg:flex-row lg:items-start justify-between gap-10 lg:gap-12">

            <!-- Left Editorial Column (Exact 624px Width · Node 1149-1166) -->
            <div class="w-full lg:w-[624px] shrink-0">
                <!-- Crimson Rose Accent Bar (52x3px) -->
                <div class="w-[52px] h-[3px] bg-[#D45060]"></div>

                <!-- Display Heading (Outfit Bold 54px Desktop / 34px Mobile) -->
                <h1 class="mt-4 md:mt-5 font-outfit font-bold text-[34px] leading-[40px] sm:text-[44px] sm:leading-[52px] lg:text-[54px] lg:leading-[62px] tracking-[-1.1px] text-[#2B0F16]">
                    Bersama Menjaga<br>Infrastruktur Kabupaten
                </h1>

                <!-- Subtitle -->
                <p class="mt-4 md:mt-5 text-[14.5px] leading-[22px] sm:text-[16px] sm:leading-[26px] text-[#593B42] max-w-[596px]">
                    <span class="sm:hidden">Sampaikan laporan kerusakan di lingkungan Anda dan pantau tindak lanjut Pekerja Lapangan Dinas PUPR.</span>
                    <span class="hidden sm:inline">Laporkan jalan rusak, jembatan, lampu penerangan, atau fasilitas umum di sekitar Anda. Setiap laporan tercatat resmi dan ditindaklanjuti oleh Pekerja Lapangan Dinas PUPR.</span>
                </p>

                <!-- Search Bar (Warm Sand #F3E6D5 Input + Primary Maroon #800020 Submit, 0px Sharp, Zero White Gap) -->
                <div id="searchContainer" class="mt-6 md:mt-8 w-full max-w-[624px] relative z-40 text-left">
                    <form id="trackForm" class="flex flex-col sm:flex-row items-stretch relative z-20">
                        <div class="relative flex-1 flex items-center bg-[#F3E6D5]">
                            <i data-lucide="search" class="hidden sm:block w-[17px] h-[17px] absolute left-5 top-1/2 -translate-y-1/2 text-[#800020] pointer-events-none"></i>
                            <input type="text" id="trackCodeInput" autocomplete="off"
                                placeholder="Cari lokasi, jenis kerusakan, atau nomor tiket..."
                                aria-label="Cari lokasi, jenis kerusakan, atau nomor tiket"
                                class="w-full bg-[#F3E6D5] border-0 rounded-none pl-4 sm:pl-[50px] pr-4 h-[52px] sm:h-[56px] text-[14px] sm:text-[14.5px] text-[#2B0F16] placeholder-[#593B42] focus:outline-none focus:bg-[#EBDAC5] transition-colors">
                            <button type="button" id="clearSearchBtn" aria-label="Hapus pencarian"
                                class="hidden absolute right-3 top-1/2 -translate-y-1/2 px-2.5 py-1.5 rounded-none text-[12.5px] font-medium text-[#593B42] hover:text-[#2B0F16] hover:bg-[#FFF9F2] transition-colors cursor-pointer items-center gap-1">
                                <span>Hapus</span>
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <button type="submit"
                            class="mt-2.5 sm:mt-0 bg-[#800020] hover:bg-[#630018] active:scale-[0.99] text-white font-semibold sm:w-[152px] h-[50px] sm:h-[56px] rounded-none border-0 text-[14px] transition-colors duration-200 flex items-center justify-center shrink-0 cursor-pointer focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#800020]">
                            Cari Aduan
                        </button>
                    </form>

                    <!-- Floating Live Autocomplete Results (1:1 FigCraft Node 2406 · Maroon & Cream 0px) -->
                    <div id="searchSuggestions" role="region" aria-label="Hasil Pencarian Aduan"
                        class="absolute left-0 right-0 top-full mt-2 bg-white rounded-none border-t-[3px] border-t-[#800020] shadow-[0_24px_56px_rgba(74,0,18,0.22)] overflow-hidden z-50 hidden text-left text-[#2B0F16] transition-all duration-200 smooth-ease opacity-0 -translate-y-1.5">
                        <!-- Warm Sand Filter Chip Bar + Result Count (Clean 2-row layout inside 624px so zero chip clipping) -->
                        <div class="px-4 sm:px-5 py-3 bg-[#F3E6D5] flex flex-col gap-2">
                            <div class="flex items-center justify-between gap-2">
                                <span id="searchResultCount" class="text-[12px] font-semibold text-[#800020] truncate"></span>
                            </div>
                            <div id="searchStatusChips" class="flex items-center gap-1.5 overflow-x-auto no-scrollbar pb-0.5">
                                <button type="button" data-search-status="ALL"
                                    class="search-status-chip px-3 py-1.5 rounded-none text-[12px] font-semibold transition-colors cursor-pointer bg-[#800020] text-white whitespace-nowrap shrink-0">
                                    Semua
                                </button>
                                <button type="button" data-search-status="SUBMITTED"
                                    class="search-status-chip px-2.5 py-1.5 rounded-none text-[12px] font-medium transition-colors cursor-pointer bg-transparent text-[#593B42] hover:bg-[#FFF9F2] whitespace-nowrap shrink-0">
                                    Verifikasi
                                </button>
                                <button type="button" data-search-status="DISPATCHED"
                                    class="search-status-chip px-2.5 py-1.5 rounded-none text-[12px] font-medium transition-colors cursor-pointer bg-transparent text-[#593B42] hover:bg-[#FFF9F2] whitespace-nowrap shrink-0">
                                    Ditugaskan
                                </button>
                                <button type="button" data-search-status="PROCESSING"
                                    class="search-status-chip px-2.5 py-1.5 rounded-none text-[12px] font-medium transition-colors cursor-pointer bg-transparent text-[#593B42] hover:bg-[#FFF9F2] whitespace-nowrap shrink-0">
                                    Diproses
                                </button>
                                <button type="button" data-search-status="COMPLETED"
                                    class="search-status-chip px-2.5 py-1.5 rounded-none text-[12px] font-medium transition-colors cursor-pointer bg-transparent text-[#593B42] hover:bg-[#FFF9F2] whitespace-nowrap shrink-0">
                                    Selesai
                                </button>
                            </div>
                        </div>

                        <!-- Editorial Results List -->
                        <div id="searchSuggestionsList" class="divide-y divide-[#E6D5C1] max-h-[340px] overflow-y-auto overscroll-contain bg-white" style="-webkit-overflow-scrolling: touch;"></div>

                        <!-- Keyboard Shortcut Footer -->
                        <div class="px-4 sm:px-5 py-2.5 bg-[#F3E6D5] flex items-center justify-between gap-2 text-[11.5px] text-[#593B42]">
                            <span class="hidden sm:inline">&uarr; &darr; Navigasi hasil &nbsp;&middot;&nbsp; Enter Buka detail laporan</span>
                            <span class="sm:hidden">Ketuk laporan untuk membuka detail</span>
                            <span class="shrink-0">Esc Tutup pencarian</span>
                        </div>
                    </div>
                </div>

                <!-- Mobile Compact KPI Strip (Exact FigCraft Mobile 1364: Warm Sand #F3E6D5 Box) -->
                <div class="mt-6 sm:hidden bg-[#F3E6D5] rounded-none grid grid-cols-3 px-4 py-4 gap-2">
                    <button type="button" onclick="filterMapByStatus('ALL')" class="text-left cursor-pointer active:scale-[0.98] transition-transform">
                        <span class="font-outfit font-bold text-[28px] leading-none text-[#800020] block">{{ str_pad((string) $totalActiveCount, 2, '0', STR_PAD_LEFT) }}</span>
                        <span class="text-[12px] font-medium text-[#593B42] block mt-1.5">Laporan</span>
                    </button>
                    <button type="button" onclick="filterMapByStatus('PROCESSING')" class="text-left cursor-pointer active:scale-[0.98] transition-transform">
                        <span class="font-outfit font-bold text-[28px] leading-none text-[#2B0F16] block">{{ str_pad((string) $processingCount, 2, '0', STR_PAD_LEFT) }}</span>
                        <span class="text-[12px] font-medium text-[#593B42] block mt-1.5">Ditangani</span>
                    </button>
                    <button type="button" onclick="filterMapByStatus('COMPLETED')" class="text-left cursor-pointer active:scale-[0.98] transition-transform">
                        <span class="font-outfit font-bold text-[28px] leading-none text-[#2B0F16] block">{{ str_pad((string) $completedCount, 2, '0', STR_PAD_LEFT) }}</span>
                        <span class="text-[12px] font-medium text-[#593B42] block mt-1.5">Selesai</span>
                    </button>
                </div>

                <!-- Desktop 3-Column Editorial KPI Strip (Exact Node 1157-1166) -->
                <div class="hidden sm:block mt-11">
                    <div class="h-[1px] bg-[#E6D5C1] w-full"></div>
                    <div class="pt-7 grid grid-cols-3 gap-7">
                        <button type="button" onclick="filterMapByStatus('ALL')" data-status="ALL"
                            class="kpi-filter-box group text-left cursor-pointer transition-transform duration-300 smooth-ease hover:-translate-y-0.5 focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-[#800020]">
                            <span class="font-outfit font-bold text-[36px] leading-none text-[#800020] block">{{ str_pad((string) $totalActiveCount, 2, '0', STR_PAD_LEFT) }}</span>
                            <span class="text-[13px] font-bold text-[#2B0F16] group-hover:text-[#800020] transition-colors block mt-2">Laporan Masyarakat</span>
                            <span class="text-[11.5px] text-[#6D4E55] block mt-1">Aktif di 15 kecamatan</span>
                        </button>

                        <button type="button" onclick="filterMapByStatus('PROCESSING')" data-status="PROCESSING"
                            class="kpi-filter-box group text-left cursor-pointer transition-transform duration-300 smooth-ease hover:-translate-y-0.5 focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-[#800020]">
                            <span class="font-outfit font-bold text-[36px] leading-none text-[#2B0F16] block">{{ str_pad((string) $processingCount, 2, '0', STR_PAD_LEFT) }}</span>
                            <span class="text-[13px] font-bold text-[#2B0F16] group-hover:text-[#800020] transition-colors block mt-2">Sedang Ditangani</span>
                            <span class="text-[11.5px] text-[#6D4E55] block mt-1">Verifikasi &amp; perbaikan fisik</span>
                        </button>

                        <button type="button" onclick="filterMapByStatus('COMPLETED')" data-status="COMPLETED"
                            class="kpi-filter-box group text-left cursor-pointer transition-transform duration-300 smooth-ease hover:-translate-y-0.5 focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-[#800020]">
                            <span class="font-outfit font-bold text-[36px] leading-none text-[#2B0F16] block">{{ str_pad((string) $completedCount, 2, '0', STR_PAD_LEFT) }}</span>
                            <span class="text-[13px] font-bold text-[#2B0F16] group-hover:text-[#800020] transition-colors block mt-2">Selesai Diperbaiki</span>
                            <span class="text-[11.5px] text-[#6D4E55] block mt-1">Tervalidasi di peta publik</span>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Right Editorial Visual Frame (Desktop Only · Exact FigCraft Node 1167-1173 with Smooth Moving 4-Photo Slideshow!) -->
            <div class="hidden lg:block relative w-[600px] h-[488px] shrink-0 select-none">
                <!-- Offset Warm Sand Architectural Plinth (568x452px, offset +32px right & +36px down) -->
                <div class="absolute right-0 top-[36px] w-[568px] h-[452px] bg-[#F3E6D5]" aria-hidden="true"></div>

                <!-- Main Moving Photo Frame (568x444px at top-left, 4-image Ken Burns Crossfade Slideshow) -->
                <div id="landingHeroSlideshow" class="relative w-[568px] h-[444px] bg-[#4A0012] overflow-hidden z-10">
                    <img src="{{ asset('images/hero/hero-1.webp') }}" alt="Pemandangan Pesisir dan Infrastruktur Kabupaten" fetchpriority="high" decoding="async"
                        class="landing-hero-slide active-slide absolute inset-0 w-full h-full object-cover object-center opacity-100 transition-opacity duration-1000">
                    <img data-src="{{ asset('images/hero/repair-jalan.webp') }}" alt="Perbaikan Jalan oleh Pekerja Lapangan Dinas PUPR" decoding="async"
                        class="landing-hero-slide absolute inset-0 w-full h-full object-cover object-center opacity-0 transition-opacity duration-1000">
                    <img data-src="{{ asset('images/hero/repair-pju.webp') }}" alt="Pemeliharaan Penerangan Jalan Umum Kabupaten" decoding="async"
                        class="landing-hero-slide absolute inset-0 w-full h-full object-cover object-center opacity-0 transition-opacity duration-1000">
                    <img data-src="{{ asset('images/hero/repair-fasum.webp') }}" alt="Perbaikan Fasilitas Umum dan Drainase Kabupaten" decoding="async"
                        class="landing-hero-slide absolute inset-0 w-full h-full object-cover object-center opacity-0 transition-opacity duration-1000">

                    <!-- Synced Slide Index Controls (01 / 02 / 03 / 04 moves with the hero slideshow) -->
                    <div id="heroSlideRail" class="absolute top-4 right-4 z-20 flex items-center gap-1.5 bg-[#4A0012]/78 backdrop-blur-xs px-3 py-1.5" aria-label="Navigasi galeri foto utama">
                        <button type="button" data-slide-idx="0" onclick="goToHeroSlide(0)"
                            class="hero-rail-btn px-1.5 py-0.5 text-[11.5px] font-outfit font-bold text-white border-b-[2px] border-[#D45060] transition-all cursor-pointer">01</button>
                        <button type="button" data-slide-idx="1" onclick="goToHeroSlide(1)"
                            class="hero-rail-btn px-1.5 py-0.5 text-[11.5px] font-outfit font-semibold text-[#F3E6D5]/75 hover:text-white border-b-[2px] border-transparent transition-all cursor-pointer">02</button>
                        <button type="button" data-slide-idx="2" onclick="goToHeroSlide(2)"
                            class="hero-rail-btn px-1.5 py-0.5 text-[11.5px] font-outfit font-semibold text-[#F3E6D5]/75 hover:text-white border-b-[2px] border-transparent transition-all cursor-pointer">03</button>
                        <button type="button" data-slide-idx="3" onclick="goToHeroSlide(3)"
                            class="hero-rail-btn px-1.5 py-0.5 text-[11.5px] font-outfit font-semibold text-[#F3E6D5]/75 hover:text-white border-b-[2px] border-transparent transition-all cursor-pointer">04</button>
                    </div>
                </div>

                <!-- Overlapping Inset Latest Report Plate (Primary Maroon #800020, 416x102px, Top 3px #D45060 Stripe · Node 1169) -->
                @if($featuredComplaint)
                <button type="button" onclick="openFeaturedComplaint({{ $featuredComplaint->id }})"
                    class="group absolute left-0 bottom-[26px] z-20 w-[416px] min-h-[102px] bg-[#800020] hover:bg-[#630018] border-t-[3px] border-t-[#D45060] px-[22px] py-[16px] text-left transition-colors duration-200 cursor-pointer shadow-[0_14px_32px_rgba(74,0,18,0.28)] focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#800020]">
                    <span class="block text-[11.5px] font-semibold text-[#F3E6D5] leading-snug truncate">
                        Laporan Masuk Terbaru &middot; #{{ $featuredComplaint->public_code }}
                    </span>
                    <span class="block mt-1 font-outfit font-bold text-[17px] text-white leading-snug truncate group-hover:underline">
                        {{ $featuredComplaint->title }}
                    </span>
                    <span class="block mt-1 text-[12px] text-[#F3E6D5] leading-snug truncate">
                        {{ $featuredDistrict }} &middot; {{ $featuredStatusDesc }}
                    </span>
                </button>
                @endif
            </div>

        </div>
    </div>
</section>

<!-- SECTION 3: BIDANG PENANGANAN INFRASTRUKTUR (1:1 FigCraft Node 1174 · Full Primary Maroon Band #800020) -->
<section id="kategori-infrastruktur" class="relative z-20 w-full bg-[#800020] py-10 md:py-[68px] scroll-mt-[76px]">
    <div class="max-w-[1280px] mx-auto px-5 sm:px-6 xl:px-0">
        <div class="flex flex-col lg:flex-row lg:items-start justify-between gap-7 lg:gap-[36px]">
            <!-- Left Intro Column (320px Desktop · Node 1175-1179) -->
            <div class="w-full lg:w-[320px] shrink-0">
                <div class="w-[48px] h-[3px] bg-[#D45060]"></div>
                <h2 class="mt-4 font-outfit font-bold text-[26px] md:text-[34px] leading-[1.2] tracking-[-0.4px] text-white">
                    <span class="md:hidden">Bidang Penanganan</span>
                    <span class="hidden md:inline">Bidang<br>Penanganan<br>Infrastruktur</span>
                </h2>
                <p class="hidden md:block mt-4 text-[14.5px] leading-[23px] text-[#F3E6D5]">
                    Tiga sektor utama prasarana publik daerah yang dapat warga laporkan langsung dari lokasi kejadian.
                </p>
                <div class="hidden md:block mt-7 h-[1px] bg-[#D45060]/45 w-full"></div>
                <p class="hidden md:block mt-5 text-[12.5px] leading-[20px] text-[#F3E6D5]">
                    Ditindaklanjuti Pekerja Lapangan Dinas PUPR di 15 wilayah kecamatan.
                </p>
            </div>

            <!-- Right 3 Warm Ivory Cards (Vertical Photo-Top on Desktop Node 1180-1197 / Horizontal Photo-Left on Mobile Node 1364) -->
            <div class="flex-1 grid grid-cols-1 md:grid-cols-3 gap-4 md:gap-[28px]">
                <!-- Card 1: Jalan & Jembatan -->
                <button type="button" onclick="filterMapByCategory(1)"
                    class="group flex flex-row md:flex-col w-full bg-[#FFF9F2] rounded-none overflow-hidden text-left cursor-pointer transition-transform duration-300 smooth-ease md:hover:-translate-y-1 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-white">
                    <div class="w-[116px] sm:w-[140px] md:w-full h-auto min-h-[104px] md:h-[188px] shrink-0 overflow-hidden bg-[#4A0012] relative">
                        <img src="{{ asset('images/hero/repair-jalan.webp') }}" alt="Jalan dan Jembatan Kabupaten" loading="lazy" decoding="async"
                            class="absolute inset-0 w-full h-full object-cover object-center group-hover:scale-[1.05] transition-transform duration-700 smooth-ease">
                    </div>
                    <div class="p-4 md:p-5 flex-1 flex flex-col justify-center md:justify-start">
                        <div class="hidden md:block w-[36px] h-[3px] bg-[#D45060] mb-3"></div>
                        <span id="catCountJalan" class="text-[11.5px] font-bold text-[#800020] block">
                            {{ $categoryStats['jalan'] ?? 0 }} Laporan Aktif
                        </span>
                        <h3 class="mt-1 md:mt-1.5 font-outfit font-bold text-[16.5px] md:text-[19px] text-[#2B0F16] leading-snug group-hover:text-[#800020] transition-colors">
                            Jalan &amp; Jembatan
                        </h3>
                        <p class="mt-1 md:mt-2 text-[12px] md:text-[13px] leading-[18px] md:leading-[19.5px] text-[#593B42]">
                            <span class="md:hidden">Aspal berlubang, bahu longsor, jembatan.</span>
                            <span class="hidden md:inline">Jalan berlubang, aspal retak, bahu jalan longsor, dan perbaikan jembatan.</span>
                        </p>
                    </div>
                </button>

                <!-- Card 2: Penerangan Jalan -->
                <button type="button" onclick="filterMapByCategory(2)"
                    class="group flex flex-row md:flex-col w-full bg-[#FFF9F2] rounded-none overflow-hidden text-left cursor-pointer transition-transform duration-300 smooth-ease md:hover:-translate-y-1 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-white">
                    <div class="w-[116px] sm:w-[140px] md:w-full h-auto min-h-[104px] md:h-[188px] shrink-0 overflow-hidden bg-[#4A0012] relative">
                        <img src="{{ asset('images/hero/repair-pju.webp') }}" alt="Penerangan Jalan Kabupaten" loading="lazy" decoding="async"
                            class="absolute inset-0 w-full h-full object-cover object-center group-hover:scale-[1.05] transition-transform duration-700 smooth-ease">
                    </div>
                    <div class="p-4 md:p-5 flex-1 flex flex-col justify-center md:justify-start">
                        <div class="hidden md:block w-[36px] h-[3px] bg-[#D45060] mb-3"></div>
                        <span id="catCountPju" class="text-[11.5px] font-bold text-[#800020] block">
                            {{ $categoryStats['pju'] ?? 0 }} Laporan Aktif
                        </span>
                        <h3 class="mt-1 md:mt-1.5 font-outfit font-bold text-[16.5px] md:text-[19px] text-[#2B0F16] leading-snug group-hover:text-[#800020] transition-colors">
                            Penerangan Jalan
                        </h3>
                        <p class="mt-1 md:mt-2 text-[12px] md:text-[13px] leading-[18px] md:leading-[19.5px] text-[#593B42]">
                            <span class="md:hidden">Lampu PJU padam, tiang &amp; kabel putus.</span>
                            <span class="hidden md:inline">Lampu PJU padam, tiang penerangan miring, dan jaringan kabel putus.</span>
                        </p>
                    </div>
                </button>

                <!-- Card 3: Fasilitas Umum -->
                <button type="button" onclick="filterMapByCategory(3)"
                    class="group flex flex-row md:flex-col w-full bg-[#FFF9F2] rounded-none overflow-hidden text-left cursor-pointer transition-transform duration-300 smooth-ease md:hover:-translate-y-1 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-white">
                    <div class="w-[116px] sm:w-[140px] md:w-full h-auto min-h-[104px] md:h-[188px] shrink-0 overflow-hidden bg-[#4A0012] relative">
                        <img src="{{ asset('images/hero/repair-fasum.webp') }}" alt="Fasilitas Umum Kabupaten" loading="lazy" decoding="async"
                            class="absolute inset-0 w-full h-full object-cover object-center group-hover:scale-[1.05] transition-transform duration-700 smooth-ease">
                    </div>
                    <div class="p-4 md:p-5 flex-1 flex flex-col justify-center md:justify-start">
                        <div class="hidden md:block w-[36px] h-[3px] bg-[#D45060] mb-3"></div>
                        <span id="catCountFasilitas" class="text-[11.5px] font-bold text-[#800020] block">
                            {{ $categoryStats['fasilitas'] ?? 0 }} Laporan Aktif
                        </span>
                        <h3 class="mt-1 md:mt-1.5 font-outfit font-bold text-[16.5px] md:text-[19px] text-[#2B0F16] leading-snug group-hover:text-[#800020] transition-colors">
                            Fasilitas Umum
                        </h3>
                        <p class="mt-1 md:mt-2 text-[12px] md:text-[13px] leading-[18px] md:leading-[19.5px] text-[#593B42]">
                            <span class="md:hidden">Drainase tersumbat, trotoar, taman kota.</span>
                            <span class="hidden md:inline">Saluran drainase tersumbat, trotoar, taman kota, dan prasarana daerah.</span>
                        </p>
                    </div>
                </button>
            </div>
        </div>
    </div>
</section>

<!-- SECTION 4: PETA SEBARAN INTERAKTIF GIS (1:1 FigCraft Node 1198 · Warm Ivory #FFF9F2) -->
<section id="mapSectionContainer" class="relative z-20 w-full bg-[#FFF9F2] py-11 md:py-[56px] scroll-mt-[76px]">
    <div class="max-w-[1280px] mx-auto px-5 sm:px-6 xl:px-0 space-y-6">
        <!-- Header & District Dropdown -->
        <div class="flex flex-col md:flex-row md:items-end justify-between gap-4">
            <div class="space-y-1.5">
                <p class="hidden md:block text-[12.5px] font-semibold text-[#800020]">
                    Pantauan Wilayah 15 Kecamatan
                </p>
                <h2 class="font-outfit font-bold text-[24px] md:text-[30px] text-[#2B0F16] tracking-tight leading-tight">
                    <span class="md:hidden">Peta Sebaran Kerusakan</span>
                    <span class="hidden md:inline">Peta Sebaran Titik Kerusakan</span>
                </h2>
                <p id="drawerFilterStatusText" class="hidden md:block text-[14.5px] text-[#593B42]">
                    Pilih kecamatan atau status untuk menelusuri titik minor maupun ruas jalan panjang yang sedang ditangani.
                </p>
            </div>

            <!-- 0px Warm Sand District Select -->
            <div class="flex items-center gap-2.5 w-full md:w-auto">
                <div class="relative flex-1 md:flex-initial md:w-[332px]">
                    <select id="districtFilterSelect" onchange="onDistrictFilterChange(this.value)" aria-label="Filter 15 Kecamatan"
                        class="w-full appearance-none bg-[#F3E6D5] hover:bg-[#EBDAC5] text-[13.5px] font-semibold text-[#2B0F16] h-[48px] pl-[18px] pr-10 rounded-none border-b-2 border-transparent focus:border-[#800020] focus:outline-none cursor-pointer transition-colors">
                        <option value="ALL">15 Kecamatan (Batu Putih - Wawo)</option>
                        @foreach($districts as $d)
                            <option value="{{ $d }}">{{ str_starts_with($d, 'Kecamatan ') ? $d : 'Kecamatan ' . $d }}</option>
                        @endforeach
                    </select>
                    <i data-lucide="chevron-down" class="w-4 h-4 text-[#800020] absolute right-4 top-1/2 -translate-y-1/2 pointer-events-none"></i>
                </div>
                <button type="button" id="resetFilterBtn" onclick="resetAllFilters()"
                    class="hidden items-center gap-1.5 px-4 h-[48px] rounded-none bg-[#800020] hover:bg-[#630018] text-white text-xs font-bold transition-colors cursor-pointer shrink-0">
                    <i data-lucide="rotate-ccw" class="w-3.5 h-3.5"></i>
                    <span>Reset</span>
                </button>
            </div>
        </div>

        <!-- 0px Split Map Workspace (Desktop: Daftar Aduan Left 416px + Map Right 864px · Mobile: Map Top 360px + Daftar Aduan Below) -->
        <div class="bg-[#FFF9F2] rounded-none shadow-[0_12px_32px_rgba(74,0,18,0.08)] overflow-hidden flex flex-col md:flex-row md:h-[576px] border border-[#E6D5C1]">
            <!-- Panel Daftar Aduan Aktif (Left on Desktop, Below Map on Mobile) -->
            <div id="reportDrawer" class="order-2 md:order-1 w-full md:w-[416px] md:h-full bg-[#FFF9F2] flex flex-col shrink-0 z-20 border-t md:border-t-0 md:border-r border-[#E6D5C1]">
                <!-- Solid Primary Maroon Header -->
                <div class="px-5 h-[52px] md:h-[48px] bg-[#800020] text-white flex items-center justify-between gap-2 shrink-0">
                    <button type="button" id="filterCollapseBtn" onclick="toggleFilterPanelBody()" aria-expanded="true" aria-controls="filterDrawerBody"
                        class="flex-1 h-full flex items-center justify-between gap-2 text-left cursor-pointer focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-white md:cursor-default">
                        <span class="font-outfit font-bold text-[14px] text-white truncate">Daftar Aduan Aktif</span>
                        <i id="filterCollapseChevron" data-lucide="chevron-up" class="w-4 h-4 text-white shrink-0 transition-transform duration-300 smooth-ease md:hidden"></i>
                    </button>
                    <span id="drawerTotalText" class="text-[11px] font-semibold px-2.5 py-1 bg-[#4A0012] text-white rounded-none shrink-0">
                        {{ $totalActiveCount }} Aduan
                    </span>
                </div>

                <!-- Drawer Body -->
                <div id="filterDrawerBody" class="flex flex-col flex-1 min-h-0 overflow-hidden transition-[max-height,opacity] duration-350 smooth-ease">
                    <!-- Status Filter Bar (Warm Sand #F3E6D5) -->
                    <div id="mapStatusTabs" class="px-3 py-2.5 bg-[#F3E6D5] flex flex-nowrap items-center md:justify-between gap-1.5 md:gap-1 overflow-x-auto no-scrollbar shrink-0">
                        <button type="button" data-map-status="ALL" onclick="filterMapByStatus('ALL', false)"
                            class="map-status-tab px-2.5 md:px-2 py-1.5 md:py-1 rounded-none text-[11px] md:text-[10.5px] font-semibold transition-colors cursor-pointer bg-[#800020] text-white whitespace-nowrap shrink-0">
                            Semua
                        </button>
                        <button type="button" data-map-status="SUBMITTED" onclick="filterMapByStatus('SUBMITTED', false)"
                            class="map-status-tab inline-flex items-center gap-1.5 md:gap-1 px-2.5 md:px-1.5 py-1.5 md:py-1 rounded-none text-[11px] md:text-[10.5px] font-semibold transition-colors cursor-pointer bg-[#FFF9F2] text-[#593B42] hover:bg-[#E6D5C1] whitespace-nowrap shrink-0">
                            <span class="w-1.5 h-1.5 rounded-none bg-[#DC2626] shrink-0"></span>Verifikasi
                        </button>
                        <button type="button" data-map-status="DISPATCHED" onclick="filterMapByStatus('DISPATCHED', false)"
                            class="map-status-tab inline-flex items-center gap-1.5 md:gap-1 px-2.5 md:px-1.5 py-1.5 md:py-1 rounded-none text-[11px] md:text-[10.5px] font-semibold transition-colors cursor-pointer bg-[#FFF9F2] text-[#593B42] hover:bg-[#E6D5C1] whitespace-nowrap shrink-0">
                            <span class="w-1.5 h-1.5 rounded-none bg-[#1E40AF] shrink-0"></span>Ditugaskan
                        </button>
                        <button type="button" data-map-status="PROCESSING" onclick="filterMapByStatus('PROCESSING', false)"
                            class="map-status-tab inline-flex items-center gap-1.5 md:gap-1 px-2.5 md:px-1.5 py-1.5 md:py-1 rounded-none text-[11px] md:text-[10.5px] font-semibold transition-colors cursor-pointer bg-[#FFF9F2] text-[#593B42] hover:bg-[#E6D5C1] whitespace-nowrap shrink-0">
                            <span class="w-1.5 h-1.5 rounded-none bg-[#B45309] shrink-0"></span>Diproses
                        </button>
                        <button type="button" data-map-status="COMPLETED" onclick="filterMapByStatus('COMPLETED', false)"
                            class="map-status-tab inline-flex items-center gap-1.5 md:gap-1 px-2.5 md:px-1.5 py-1.5 md:py-1 rounded-none text-[11px] md:text-[10.5px] font-semibold transition-colors cursor-pointer bg-[#FFF9F2] text-[#593B42] hover:bg-[#E6D5C1] whitespace-nowrap shrink-0">
                            <span class="w-1.5 h-1.5 rounded-none bg-[#047857] shrink-0"></span>Selesai
                        </button>
                    </div>

                    <!-- Active Category Chip (if filtered via Bidang Penanganan card) -->
                    <div id="activeCategoryBanner" class="hidden px-4 py-2 bg-[#4A0012] text-white text-[11px] font-semibold items-center justify-between gap-2 shrink-0">
                        <span id="activeCategoryLabel" class="truncate">Bidang: Semua</span>
                        <button type="button" onclick="clearCategoryFilter()" class="text-[#F3E6D5] hover:text-white font-bold cursor-pointer shrink-0">
                            Hapus &times;
                        </button>
                    </div>

                    <!-- Clean Editorial Scrollable Report List -->
                    <div id="drawerListContainer" class="divide-y divide-[#E6D5C1] overflow-y-auto flex-1 overscroll-contain bg-[#FFF9F2] max-h-[300px] md:max-h-none" style="-webkit-overflow-scrolling: touch;"></div>

                    <!-- Mini Footer Hint -->
                    <div class="px-5 py-3 bg-[#F3E6D5] text-[11.5px] text-[#6D4E55] shrink-0">
                        Pilih aduan untuk menyorot lokasi koordinat pada peta
                    </div>
                </div>
            </div>

            <!-- Panel Peta: Live Leaflet GIS Map Canvas (Top 360px Mobile, Right 864px x 576px Desktop) -->
            <div class="order-1 md:order-2 relative w-full h-[360px] shrink-0 md:w-auto md:flex-1 md:shrink md:h-full bg-[#F3E6D5]">
                <div id="gisMap" class="w-full h-[360px] md:h-full relative z-10"></div>

                <div id="mapLoading" class="absolute inset-0 bg-[#4A0012]/12 flex items-center justify-center z-30 pointer-events-none transition-opacity duration-300">
                    <div class="bg-[#FFF9F2] px-5 py-3 rounded-none shadow-lg flex items-center gap-2.5 text-xs font-bold text-[#2B0F16]">
                        <i data-lucide="loader-2" class="w-4 h-4 animate-spin text-[#800020]"></i>
                        <span>Memuat titik persebaran laporan...</span>
                    </div>
                </div>

                <!-- Legend Status Peta (0px Sharp, Single-row compact) -->
                <div class="absolute bottom-7 right-2.5 sm:bottom-4 sm:right-4 z-20 flex items-center gap-2.5 sm:gap-3.5 px-2.5 py-1.5 sm:px-3.5 sm:py-2 rounded-none bg-[#FFF9F2]/95 backdrop-blur-xs shadow-[0_8px_24px_rgba(74,0,18,0.14)] text-[10px] sm:text-[11px] font-semibold text-[#2B0F16] select-none whitespace-nowrap">
                    <span class="flex items-center gap-1.5">
                        <span class="w-2 h-2 rounded-none bg-[#DC2626] shrink-0"></span>
                        Verifikasi
                    </span>
                    <span class="flex items-center gap-1.5">
                        <span class="w-2 h-2 rounded-none bg-[#1E40AF] shrink-0"></span>
                        Ditugaskan
                    </span>
                    <span class="flex items-center gap-1.5">
                        <span class="w-2 h-2 rounded-none bg-[#B45309] shrink-0"></span>
                        Diproses
                    </span>
                    <span class="flex items-center gap-1.5">
                        <span class="w-2 h-2 rounded-none bg-[#047857] shrink-0"></span>
                        Selesai
                    </span>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- SECTION 5: ALUR PENANGANAN ADUAN (1:1 FigCraft Node 1303 · Full Warm Sand Band #F3E6D5) -->
<section id="alur-penanganan" class="relative z-20 w-full bg-[#F3E6D5] py-12 md:py-[56px] scroll-mt-[76px]">
    <div class="max-w-[1280px] mx-auto px-5 sm:px-6 xl:px-0">
        <div class="space-y-2">
            <p class="text-[12.5px] font-semibold text-[#800020]">
                Transparansi Penanganan Publik
            </p>
            <h2 class="font-outfit font-bold text-[24px] md:text-[28px] text-[#2B0F16] tracking-tight leading-tight">
                Bagaimana Laporan Anda Ditangani
            </h2>
        </div>

        <div class="mt-8 md:mt-9 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-8">
            <!-- Tahap 01 -->
            <div class="pt-5 border-t-[2px] border-t-[#800020] space-y-2.5">
                <span class="font-outfit font-bold text-[28px] text-[#800020] block leading-none">01</span>
                <h3 class="font-outfit font-bold text-[17px] text-[#2B0F16] leading-snug pt-0.5">Laporkan dari Lokasi</h3>
                <p class="text-[13.5px] leading-[21px] text-[#593B42]">
                    Kirim foto kondisi kerusakan beserta titik GPS atau rute ruas jalan lewat aplikasi mobile.
                </p>
            </div>

            <!-- Tahap 02 -->
            <div class="pt-5 border-t-[2px] border-t-[#D45060] space-y-2.5">
                <span class="font-outfit font-bold text-[28px] text-[#800020] block leading-none">02</span>
                <h3 class="font-outfit font-bold text-[17px] text-[#2B0F16] leading-snug pt-0.5">Diverifikasi Operator</h3>
                <p class="text-[13.5px] leading-[21px] text-[#593B42]">
                    Operator Dinas PUPR memeriksa laporan dan menerbitkan disposisi kepada regu lapangan.
                </p>
            </div>

            <!-- Tahap 03 -->
            <div class="pt-5 border-t-[2px] border-t-[#D45060] space-y-2.5">
                <span class="font-outfit font-bold text-[28px] text-[#800020] block leading-none">03</span>
                <h3 class="font-outfit font-bold text-[17px] text-[#2B0F16] leading-snug pt-0.5">Ditangani Pekerja Lapangan</h3>
                <p class="text-[13.5px] leading-[21px] text-[#593B42]">
                    Pekerja Lapangan turun memperbaiki kerusakan fisik sesuai SLA dan mengunggah bukti foto.
                </p>
            </div>

            <!-- Tahap 04 -->
            <div class="pt-5 border-t-[2px] border-t-[#800020] space-y-2.5">
                <span class="font-outfit font-bold text-[28px] text-[#800020] block leading-none">04</span>
                <h3 class="font-outfit font-bold text-[17px] text-[#2B0F16] leading-snug pt-0.5">Uji Mutu &amp; Selesai</h3>
                <p class="text-[13.5px] leading-[21px] text-[#593B42]">
                    Operator memvalidasi hasil pekerjaan hingga tuntas dan memperbarui status peta publik.
                </p>
            </div>
        </div>
    </div>
</section>

<!-- SECTION 6: APLIKASI MOBILE & QR CODE (1:1 FigCraft Node 1322 · Warm Ivory Canvas + Maroon/Sand Split Card 1280x340px) -->
<section id="download-app" class="relative z-20 w-full bg-[#800020] md:bg-[#FFF9F2] md:py-[60px] scroll-mt-[76px]">
    <div class="max-w-[1280px] mx-auto px-0 md:px-6 xl:px-0">
        <div class="flex flex-col lg:flex-row items-stretch rounded-none overflow-hidden lg:min-h-[340px]">
            <!-- Kiri: Primary Maroon #800020 Panel (792px Desktop) -->
            <div class="w-full lg:w-[792px] bg-[#800020] px-5 py-10 sm:px-8 md:px-[56px] md:py-[48px] flex flex-col justify-center shrink-0">
                <div class="w-[48px] h-[3px] bg-[#D45060]"></div>
                <p class="mt-4 text-[12px] sm:text-[12.5px] font-semibold text-[#F3E6D5]">
                    <span class="sm:hidden">Aplikasi Resmi &middot; Android &amp; iOS</span>
                    <span class="hidden sm:inline">Aplikasi Resmi Warga Kabupaten &middot; Android &amp; iOS</span>
                </p>
                <h2 class="mt-2.5 font-outfit font-bold text-[28px] leading-[34px] sm:text-[38px] sm:leading-[45px] tracking-[-0.7px] text-white">
                    Laporkan Kerusakan<br>Langsung dari Lokasi
                </h2>
                <p class="mt-3.5 text-[14px] sm:text-[14.5px] leading-[22px] sm:leading-[23px] text-[#F3E6D5] max-w-[548px]">
                    <span class="sm:hidden">Kirim foto kerusakan &amp; koordinat GPS langsung ke Dinas PUPR Kabupaten.</span>
                    <span class="hidden sm:inline">Unggah foto kerusakan, tandai titik koordinat GPS atau rute ruas jalan, dan pantau penanganannya bersama Pekerja Lapangan Dinas PUPR.</span>
                </p>

                <!-- 2 Warm Ivory Solid Store Buttons (0px Sharp) -->
                <div class="mt-6 grid grid-cols-2 sm:flex sm:flex-row items-center gap-3 sm:gap-4">
                    <a href="{{ $landingApkUrl }}" download="SI-APPIK.APK"
                        class="flex items-center justify-center sm:justify-start gap-3.5 bg-[#FFF9F2] hover:bg-[#F3E6D5] active:scale-[0.98] text-[#2B0F16] px-4 sm:px-5 sm:w-[206px] h-[54px] rounded-none transition-colors focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-white">
                        <svg class="w-5 h-5 fill-[#800020] shrink-0" viewBox="0 0 24 24" aria-hidden="true">
                            <path d="M3,20.5V3.5C3,2.91 3.34,2.39 3.84,2.15L13.69,12L3.84,21.85C3.34,21.6 3,21.09 3,20.5M16.81,15.12L6.05,21.34L14.54,12.85L16.81,15.12M20.16,10.81C20.5,11.08 20.75,11.5 20.75,12C20.75,12.5 20.5,12.92 20.16,13.19L17.89,14.5L15.39,12L17.89,9.5L20.16,10.81M6.05,2.66L16.81,8.88L14.54,11.15L6.05,2.66Z" />
                        </svg>
                        <div class="text-left leading-tight">
                            <span class="text-[9.5px] sm:text-[10px] uppercase tracking-[0.4px] text-[#6D4E55] block font-semibold">TERSEDIA DI</span>
                            <span class="font-outfit font-bold text-[14.5px] sm:text-[15px] text-[#2B0F16] block">Google Play</span>
                        </div>
                    </a>

                    <a href="https://apps.apple.com" target="_blank" rel="noopener noreferrer"
                        class="flex items-center justify-center sm:justify-start gap-3.5 bg-[#FFF9F2] hover:bg-[#F3E6D5] active:scale-[0.98] text-[#2B0F16] px-4 sm:px-5 sm:w-[206px] h-[54px] rounded-none transition-colors focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-white">
                        <svg class="w-5 h-5 fill-[#800020] shrink-0" viewBox="0 0 24 24" aria-hidden="true">
                            <path d="M18.71 19.5c-.83 1.24-1.71 2.45-3.05 2.47-1.34.03-1.77-.79-3.29-.79-1.53 0-2 .77-3.27.82-1.31.05-2.3-1.32-3.14-2.53C4.25 17 2.94 12.45 4.7 9.39c.87-1.52 2.43-2.48 4.12-2.51 1.28-.02 2.5.87 3.29.87.78 0 2.26-1.07 3.81-.91.65.03 2.47.26 3.64 1.98-.09.06-2.17 1.28-2.15 3.81.03 3.02 2.65 4.03 2.68 4.04-.03.07-.42 1.44-1.38 2.83M15.97 6.37c.62-.75 1.04-1.8 1.01-2.87-.96.04-2.12.64-2.8 1.44-.59.68-1.11 1.77-1 2.82 1.07.08 2.17-.55 2.79-1.39z"/>
                        </svg>
                        <div class="text-left leading-tight">
                            <span class="text-[9.5px] sm:text-[10px] uppercase tracking-[0.4px] text-[#6D4E55] block font-semibold">TERSEDIA DI</span>
                            <span class="font-outfit font-bold text-[14.5px] sm:text-[15px] text-[#2B0F16] block">App Store</span>
                        </div>
                    </a>
                </div>
            </div>

            <!-- Kanan: Warm Sand #F3E6D5 QR Code Panel (488px Desktop) -->
            <div class="hidden lg:flex flex-1 bg-[#F3E6D5] p-10 flex-col items-center justify-center text-center">
                <div class="w-[152px] h-[152px] p-3 bg-white rounded-none flex items-center justify-center shrink-0">
                    <img src="https://api.qrserver.com/v1/create-qr-code/?size=160x160&color=4A0012&data={{ urlencode($landingApkUrl) }}"
                        alt="QR Code Unduh Aplikasi SI-APPIK Android"
                        class="w-full h-full object-contain">
                </div>
                <h3 class="mt-4 font-outfit font-bold text-[17px] text-[#2B0F16]">
                    Pindai QR Kamera Ponsel
                </h3>
                <p class="mt-2 text-[12.5px] leading-[19px] text-[#593B42] max-w-[300px]">
                    Arahkan kamera ponsel Anda untuk mengunduh aplikasi resmi SI-APPIK di Google Play atau App Store.
                </p>
                <span class="mt-4 text-[11.5px] font-semibold text-[#800020]">
                    Rilis Resmi &middot; Google Play &amp; App Store
                </span>
            </div>
        </div>
    </div>
</section>

<!-- Modal Detail Pengaduan Publik (1:1 FigCraft Maroon & Cream Node 2321) -->
@include('partials.modals.landing_complaint_detail_modal')

@push('scripts')
<style>
    .custom-map-tooltip {
        background-color: #4A0012 !important;
        color: #FFFFFF !important;
        font-weight: 700 !important;
        font-size: 11px !important;
        border-radius: 0px !important;
        padding: 6px 10px !important;
        border: none !important;
        box-shadow: 0 8px 20px rgba(74, 0, 18, 0.35) !important;
    }
    .custom-map-tooltip::before {
        border-top-color: #4A0012 !important;
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

        L.control.zoom({ position: 'topright' }).addTo(map);

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
                        color: '#800020',
                        weight: 2,
                        dashArray: '6, 6',
                        fillColor: '#D45060',
                        fillOpacity: 0.07
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
            toast.className = 'fixed top-24 left-1/2 -translate-x-1/2 z-[90] max-w-[92vw] px-4 py-3 rounded-none bg-[#4A0012] text-white text-xs font-semibold shadow-[0_14px_34px_rgba(74,0,18,0.32)] flex items-center gap-2.5 border-l-4 border-l-[#D45060] transition-all duration-300 smooth-ease opacity-0 pointer-events-none transform -translate-y-2';
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
                btn.classList.add('bg-[#800020]', 'text-white');
                btn.classList.remove('bg-[#FFF9F2]', 'text-[#593B42]', 'hover:bg-[#E6D5C1]');
            } else {
                btn.classList.remove('bg-[#800020]', 'text-white');
                btn.classList.add('bg-[#FFF9F2]', 'text-[#593B42]', 'hover:bg-[#E6D5C1]');
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
                : 'Pilih kecamatan atau status untuk menelusuri titik minor maupun ruas jalan panjang yang sedang ditangani.';
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
                <div class="py-12 px-5 text-center space-y-2.5 bg-[#FFF9F2]">
                    <h4 class="font-outfit font-bold text-sm text-[#2B0F16]">Tidak Ada Titik Aduan</h4>
                    <p class="text-xs text-[#6D4E55] max-w-[240px] mx-auto leading-relaxed">
                        Tidak ada laporan yang sesuai dengan filter wilayah, bidang, atau status saat ini.
                    </p>
                    <button type="button" onclick="resetAllFilters()"
                        class="mt-2 px-4 py-2 rounded-none bg-[#800020] hover:bg-[#630018] text-white text-xs font-semibold transition-colors cursor-pointer">
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
                ? 'bg-[#F3E6D5] border-l-[3px] border-l-[#800020]'
                : 'bg-[#FFF9F2] hover:bg-[#F3E6D5]/70 border-l-[3px] border-l-transparent hover:border-l-[#800020]';

            html += `
                <div onclick="focusReportOnMap(${Number(item.id)})" role="button" tabindex="0"
                    onkeydown="if(event.key==='Enter'||event.key===' '){event.preventDefault();focusReportOnMap(${Number(item.id)});}"
                    class="px-5 py-4 ${rowClass} cursor-pointer transition-colors space-y-1.5 group">
                    <div class="flex items-center justify-between gap-2">
                        <span class="text-[11px] font-medium text-[#6D4E55] truncate">
                            #${safeCode}${safeDate ? ' &middot; ' + safeDate : ''}
                        </span>
                        <span class="text-[10.5px] font-semibold px-2.5 py-0.5 rounded-none shrink-0 ${badge.bgClass}">
                            ${badge.label}
                        </span>
                    </div>
                    <h4 class="font-outfit font-bold text-[14px] text-[#2B0F16] truncate group-hover:text-[#800020] transition-colors">
                        ${safeTitle}
                    </h4>
                    <p class="text-[11.5px] text-[#593B42] truncate">
                        ${safeLoc} &middot; ${safeCat}
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
                        box-shadow: 0 4px 12px rgba(74, 0, 18, 0.36);
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
                    btn.classList.add('bg-[#800020]', 'text-white', 'font-semibold');
                    btn.classList.remove('bg-transparent', 'text-[#593B42]', 'hover:bg-[#FFF9F2]', 'font-medium');
                } else {
                    btn.classList.remove('bg-[#800020]', 'text-white', 'font-semibold');
                    btn.classList.add('bg-transparent', 'text-[#593B42]', 'hover:bg-[#FFF9F2]', 'font-medium');
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
                    <div class="p-6 text-center text-xs font-semibold text-[#6D4E55] flex items-center justify-center gap-2">
                        <i data-lucide="loader-2" class="w-4 h-4 animate-spin text-[#800020]"></i>
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
                        <p class="font-outfit font-bold text-sm text-[#2B0F16]">
                            ${safeQ ? `Tidak ada aduan cocok untuk "${safeQ}"` : 'Tidak ada aduan pada status ini'}
                        </p>
                        <p class="text-xs text-[#6D4E55] max-w-sm mx-auto leading-relaxed">
                            Coba kata kunci lain seperti <strong class="text-[#2B0F16]">Lasusua</strong>, <strong class="text-[#2B0F16]">PJU</strong>, <strong class="text-[#2B0F16]">aspal</strong>, atau ubah filter status di atas.
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
                        <div class="suggestion-item px-5 py-4 bg-white hover:bg-[#FFF9F2] border-l-[4px] border-l-transparent hover:border-l-[#800020] transition-colors flex items-center justify-between gap-4 cursor-pointer group" data-index="${idx}">
                            <div class="min-w-0 flex-1 space-y-1">
                                <div class="font-outfit font-bold text-[15px] text-[#2B0F16] truncate leading-snug">
                                    ${safeTitle}
                                </div>
                                <div class="text-[12.5px] text-[#593B42] flex flex-wrap items-center gap-x-2 gap-y-0.5">
                                    <span class="font-medium text-[#6D4E55]">#${safeCode}</span>
                                    <span class="text-[#E6D5C1]">&middot;</span>
                                    <span>${safeCat}</span>
                                    <span class="text-[#E6D5C1]">&middot;</span>
                                    <span class="truncate max-w-[260px]">${safeLoc}</span>
                                </div>
                            </div>
                            <div class="flex flex-col items-end gap-1.5 shrink-0">
                                <span class="px-3 py-1 rounded-none text-[11px] font-semibold ${badge.bgClass}">
                                    ${badge.label}
                                </span>
                                <span class="hidden sm:inline text-[11.5px] font-semibold text-[#800020] opacity-0 group-hover:opacity-100 transition-opacity duration-200">
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
                el.classList.toggle('bg-[#FFF9F2]', isSelected);
                el.classList.toggle('bg-white', !isSelected);
                el.classList.toggle('border-l-[#800020]', isSelected);
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

    // Hero Photo Slideshow synced 1:1 with Top-Right Index Rail (01 / 02 / 03 / 04)
    (function initHeroSlideshow() {
        const slides = Array.from(document.querySelectorAll('#landingHeroSlideshow .landing-hero-slide'));
        const railButtons = Array.from(document.querySelectorAll('#heroSlideRail .hero-rail-btn'));
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
                const active = i === idx;
                btn.classList.toggle('text-white', active);
                btn.classList.toggle('font-bold', active);
                btn.classList.toggle('border-[#D45060]', active);
                btn.classList.toggle('text-[#F3E6D5]/75', !active);
                btn.classList.toggle('font-semibold', !active);
                btn.classList.toggle('border-transparent', !active);
            });
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
    })();
</script>
@endpush
@endsection
