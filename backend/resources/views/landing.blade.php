@extends('layouts.app')

@section('content')
<!-- Hero Section Asimetris 2 Kolom (Dark Scenic Backdrop Kabupaten + Perbaikan Jalan, High-Contrast + Bento KPI Grid) -->
<section id="top" class="relative z-30 w-full bg-[#0F172A] {{ auth()->check() ? 'pt-8 sm:pt-10 pb-10 sm:pb-12' : '-mt-[76px] pt-[108px] sm:pt-[120px] pb-12 sm:pb-16' }}">
    <!-- Slideshow Latar Belakang Hero (Terklip di dalam overflow-hidden agar parallax tidak menimpa section di bawah) -->
    <div class="absolute inset-0 overflow-hidden pointer-events-none z-0">
        <div id="landingHeroSlideshow" class="absolute inset-0 will-change-transform">
            <img src="{{ asset('images/hero/repair-jalan.webp') }}" alt="Perbaikan Jalan Regu PUPR" fetchpriority="high" decoding="async" class="landing-hero-slide active-slide absolute inset-0 w-full h-full object-cover opacity-100 transition-opacity duration-1000">
            <img data-src="{{ asset('images/hero/repair-pju.webp') }}" alt="Pemeliharaan PJU Kabupaten" decoding="async" class="landing-hero-slide absolute inset-0 w-full h-full object-cover opacity-0 transition-opacity duration-1000">
            <img data-src="{{ asset('images/hero/repair-fasum.webp') }}" alt="Perbaikan Fasilitas Umum Kabupaten" decoding="async" class="landing-hero-slide absolute inset-0 w-full h-full object-cover opacity-0 transition-opacity duration-1000">
            <!-- Overlay Gelap Kontras Tinggi -->
            <div class="absolute inset-0 bg-gradient-to-b from-slate-950/85 via-slate-950/70 to-slate-950/90"></div>
        </div>
    </div>

    <style>
        @keyframes landingKenBurnsInOut {
            0%   { transform: scale(1.12); }
            50%  { transform: scale(1.00); }
            100% { transform: scale(1.12); }
        }
        #landingHeroSlideshow .landing-hero-slide.active-slide {
            animation: landingKenBurnsInOut 11s ease-in-out infinite;
        }
    </style>

    <div class="relative z-10 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-10 items-center">

            <!-- Kolom Kiri: Copy Utama -->
            <div class="lg:col-span-6 space-y-5 text-left">
                <div class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full bg-white/10 backdrop-blur-md border border-white/20 text-[#93C5FD] text-xs font-bold tracking-wide shadow-xs">
                    <span class="w-2 h-2 rounded-full bg-[#10B981] shrink-0"></span>
                    <span>Layanan Resmi Pemerintah Kabupaten</span>
                </div>

                <h1 class="font-outfit font-bold text-3xl sm:text-4xl lg:text-[2.65rem] text-white tracking-tight leading-[1.16] drop-shadow-sm">
                    Pemantauan Jalan, Jembatan, dan Prasarana Kabupaten
                </h1>

                <p class="text-sm sm:text-base text-slate-200 leading-relaxed max-w-xl drop-shadow-xs">
                    Laporkan kerusakan infrastruktur atau pantau progres perbaikan regu PUPR secara transparan dan real-time.
                </p>
            </div>

            <!-- Kolom Kanan: Bento Grid 1+2 KPI + Kolase Foto Infrastruktur (1:1 FigCraft) -->
            <div class="lg:col-span-6 space-y-3.5" data-reveal-group>
                <div class="grid grid-cols-2 gap-3.5">
                    <!-- 1. Semua Laporan (Full Width Top Bento Card) -->
                    <button type="button" onclick="filterMapByStatus('ALL')" data-reveal style="transition-delay: 0ms;"
                        class="col-span-2 relative overflow-hidden bg-white rounded-[24px] border border-[#E2E8F0] shadow-[0_8px_20px_rgba(15,23,42,0.05)] hover:shadow-md hover:-translate-y-0.5 p-5 sm:p-6 text-left cursor-pointer transition-all duration-200 group kpi-filter-box focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#2563EB]"
                        data-status="ALL" title="Ketuk untuk melihat seluruh titik sebaran aduan">
                        <div class="flex items-center justify-between gap-3">
                            <div>
                                <span class="text-xs font-bold uppercase tracking-wider text-[#475569] group-hover:text-[#2563EB] transition">Semua Laporan Masyarakat</span>
                                <div class="font-outfit font-bold text-3xl sm:text-4xl text-[#0F172A] mt-1 leading-none">
                                    {{ $stats['total'] ?? 0 }}
                                </div>
                            </div>
                            <div class="w-12 h-12 rounded-[14px] bg-[#EFF6FF] text-[#2563EB] border border-[#BFDBFE] flex items-center justify-center shrink-0">
                                <i data-lucide="layers" class="w-5 h-5"></i>
                            </div>
                        </div>
                    </button>

                    <!-- 2. Diproses -->
                    <button type="button" onclick="filterMapByStatus('PROCESSING')" data-reveal style="transition-delay: 80ms;"
                        class="relative overflow-hidden bg-white rounded-[24px] border border-[#FDE68A] shadow-[0_8px_20px_rgba(15,23,42,0.05)] hover:shadow-md hover:-translate-y-0.5 p-5 text-left cursor-pointer transition-all duration-200 group kpi-filter-box focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#F59E0B]"
                        data-status="PROCESSING" title="Ketuk untuk memfilter aduan yang sedang diproses">
                        <div class="flex items-center justify-between gap-2">
                            <span class="text-xs font-bold uppercase tracking-wider text-[#B45309]">Diproses</span>
                            <div class="w-8 h-8 rounded-lg bg-amber-50 text-[#F59E0B] border border-[#FDE68A] flex items-center justify-center shrink-0">
                                <i data-lucide="clock" class="w-4 h-4"></i>
                            </div>
                        </div>
                        <div class="font-outfit font-bold text-3xl text-[#0F172A] mt-2 leading-none">
                            {{ ($stats['submitted'] ?? 0) + ($stats['dispatched'] ?? 0) + ($stats['in_progress'] ?? 0) + ($stats['waiting_validation'] ?? 0) }}
                        </div>
                    </button>

                    <!-- 3. Selesai -->
                    <button type="button" onclick="filterMapByStatus('COMPLETED')" data-reveal style="transition-delay: 160ms;"
                        class="relative overflow-hidden bg-white rounded-[24px] border border-[#A7F3D0] shadow-[0_8px_20px_rgba(15,23,42,0.05)] hover:shadow-md hover:-translate-y-0.5 p-5 text-left cursor-pointer transition-all duration-200 group kpi-filter-box focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#10B981]"
                        data-status="COMPLETED" title="Ketuk untuk memfilter aduan yang telah selesai diperbaiki">
                        <div class="flex items-center justify-between gap-2">
                            <span class="text-xs font-bold uppercase tracking-wider text-[#047857]">Selesai</span>
                            <div class="w-8 h-8 rounded-lg bg-emerald-50 text-[#10B981] border border-[#A7F3D0] flex items-center justify-center shrink-0">
                                <i data-lucide="check-circle-2" class="w-4 h-4"></i>
                            </div>
                        </div>
                        <div class="font-outfit font-bold text-3xl text-[#10B981] mt-2 leading-none">
                            {{ $stats['completed'] ?? 0 }}
                        </div>
                    </button>
                </div>
            </div>

        </div>

        <!-- Kotak Pencarian Memanjang Penuh Kiri ke Kanan (rounded-[18px] + #2563EB CTA) -->
        <div id="searchContainer" class="mt-7 lg:mt-8 w-full relative z-30 text-left">
            <form id="trackForm" class="flex flex-col sm:flex-row gap-2 bg-white p-2 rounded-[18px] border border-white/25 shadow-[0_12px_32px_rgba(2,6,23,0.35)] relative z-20">
                <div class="relative flex-1">
                    <i data-lucide="search" class="w-4 h-4 absolute left-3.5 top-1/2 -translate-y-1/2 text-[#64748B] pointer-events-none"></i>
                    <input type="text" id="trackCodeInput" autocomplete="off"
                        placeholder="Cari lokasi, jenis kerusakan, atau nomor tiket..."
                        class="w-full bg-white border-0 rounded-xl pl-10 pr-4 min-h-[50px] text-sm text-[#0F172A] placeholder-[#64748B] focus:outline-none focus-visible:ring-2 focus-visible:ring-[#2563EB]/25">
                </div>
                <button type="submit" class="bg-[#2563EB] hover:bg-[#1D4ED8] active:scale-[0.99] text-white font-bold px-7 min-h-[50px] rounded-[12px] text-sm transition-all flex items-center justify-center gap-2 shrink-0 shadow-xs focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#2563EB] cursor-pointer">
                    <i data-lucide="search" class="w-4 h-4 text-white"></i>
                    <span>Cari Aduan</span>
                </button>
            </form>

            <!-- Floating Live Autocomplete Suggestions -->
            <div id="searchSuggestions" class="absolute left-0 right-0 top-full mt-2.5 bg-white rounded-[20px] border border-[#E2E8F0] shadow-[0_20px_48px_rgba(15,23,42,0.14)] overflow-hidden z-50 hidden text-left text-[#0F172A]">
                <div class="px-5 py-3 bg-[#F8FAFC] border-b border-[#E2E8F0] flex items-center justify-between text-[11px] font-bold text-[#475569] uppercase tracking-wider">
                    <span>Daftar Hasil Pencarian</span>
                    <span id="searchResultCount" class="text-[#2563EB] font-bold lowercase"></span>
                </div>
                <div id="searchSuggestionsList" class="divide-y divide-[#E2E8F0]/70 max-h-[360px] overflow-y-auto">
                    <!-- Item hasil pencarian disuntikkan via JavaScript -->
                </div>
                <div class="px-5 py-2.5 bg-[#F8FAFC] border-t border-[#E2E8F0] flex items-center justify-between text-[11px] text-[#475569]">
                    <span>Gunakan tombol <kbd class="px-1.5 py-0.5 bg-white border border-[#E2E8F0] rounded font-mono text-[10px]">↑</kbd> <kbd class="px-1.5 py-0.5 bg-white border border-[#E2E8F0] rounded font-mono text-[10px]">↓</kbd> dan <kbd class="px-1.5 py-0.5 bg-white border border-[#E2E8F0] rounded font-mono text-[10px]">Enter</kbd></span>
                    <span><kbd class="px-1.5 py-0.5 bg-white border border-[#E2E8F0] rounded font-mono text-[10px]">ESC</kbd> tutup</span>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Konten Utama (Peta, Kategori, Alur, Unduh Aplikasi) -->
<div class="relative z-20 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-10 pb-6 space-y-12">

    <!-- Peta Sebaran Interaktif Titik Kerusakan (Desain FigCraft Gambar 7) -->
    <div class="space-y-4 scroll-mt-20" id="mapSectionContainer">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div>
                <h2 class="font-outfit font-bold text-xl sm:text-2xl text-[#0F172A]">
                    Peta Sebaran Interaktif Titik Kerusakan
                </h2>
            </div>
        </div>

        <!-- Peta Leaflet + Floating Left Filter & Daftar Aduan Card -->
        <div class="bg-white rounded-[24px] border border-[#E2E8F0] shadow-[0_8px_24px_rgba(15,23,42,0.05)] overflow-hidden relative h-[540px] lg:h-[620px]">
            <!-- Full-Width GIS Map -->
            <div id="gisMap" class="w-full h-full relative z-10"></div>
            
            <div id="mapLoading" class="absolute inset-0 bg-slate-900/10 flex items-center justify-center z-30 pointer-events-none">
                <div class="bg-white px-5 py-2.5 rounded-xl border border-[#E2E8F0] flex items-center gap-2 text-xs font-semibold text-[#0F172A]">
                    <i data-lucide="loader-2" class="w-4 h-4 animate-spin text-[#2563EB]"></i>
                    <span>Memuat titik persebaran laporan...</span>
                </div>
            </div>

            <!-- Panel Kiri Mengambang: Filter & Daftar Aduan (Selalu tampil di Desktop & Mobile; di Mobile ringkas & bisa dibuka/tutup) -->
            <div id="reportDrawer" class="flex absolute top-3 left-3 right-3 md:right-auto md:top-4 md:left-4 z-20 md:w-[360px] lg:w-[385px] max-h-[calc(100%-1.5rem)] md:max-h-[calc(100%-2rem)] bg-white rounded-2xl border border-[#E2E8F0] shadow-lg flex-col overflow-hidden transition-all duration-300">
                <!-- Header Button (Klik untuk menutup ke atas / membuka ke bawah) -->
                <div class="px-4 py-3 bg-white flex items-center justify-between gap-2 shrink-0 border-b border-[#E2E8F0]">
                    <button type="button" id="filterCollapseBtn" onclick="toggleFilterPanelBody()" aria-expanded="true" aria-controls="filterDrawerBody"
                        class="flex-1 flex items-center justify-between gap-2 text-left rounded-xl py-1 px-1.5 -ml-1.5 hover:bg-[#EFF6FF]/70 transition cursor-pointer focus-visible:outline-2 focus-visible:outline-[#2563EB]">
                        <span class="flex items-center gap-2 min-w-0">
                            <span class="w-7 h-7 rounded-lg bg-[#EFF6FF] text-[#2563EB] flex items-center justify-center shrink-0">
                                <i data-lucide="list-filter" class="w-4 h-4"></i>
                            </span>
                            <span class="font-outfit font-bold text-sm text-[#0F172A] truncate">Filter &amp; Daftar Aduan</span>
                        </span>
                        <i id="filterCollapseChevron" data-lucide="chevron-up" class="w-4 h-4 text-[#2563EB] shrink-0 transition-transform duration-300"></i>
                    </button>
                    <div class="flex items-center gap-1.5 shrink-0">
                        <span id="drawerTotalText" class="text-[11px] font-bold px-2 py-0.5 bg-[#EFF6FF] text-[#2563EB] rounded-full border border-[#BFDBFE]">
                            0 Aduan
                        </span>
                    </div>
                </div>

                <!-- Konten Panel yang Bisa Collapse ke Atas & Expand ke Bawah -->
                <div id="filterDrawerBody" class="flex flex-col flex-1 min-h-0 overflow-hidden transition-[max-height,opacity] duration-300 ease-in-out" style="max-height: 540px; opacity: 1;">
                    <!-- Filter Kecamatan & Status -->
                    <div class="p-4 border-b border-[#E2E8F0] bg-white space-y-3 shrink-0">
                        <!-- Dropdown 15 Kecamatan -->
                        <div class="relative">
                            <select id="districtFilterSelect" onchange="onDistrictFilterChange(this.value)" aria-label="Filter 15 Kecamatan"
                                class="w-full bg-white border border-[#E2E8F0] text-xs font-semibold text-[#0F172A] min-h-[42px] pl-3 pr-8 rounded-xl focus:outline-none focus-visible:ring-2 focus-visible:ring-[#2563EB] focus:border-[#2563EB] cursor-pointer">
                                <option value="ALL">15 Kecamatan (Batu Putih - Wawo)</option>
                                @foreach($districts as $d)
                                    <option value="{{ $d }}">{{ str_starts_with($d, 'Kecamatan ') ? $d : 'Kecamatan ' . $d }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Filter Status Ringkas + Reset (Sejajar 1 Baris di Desktop) -->
                        <div class="flex flex-wrap md:flex-nowrap items-center gap-1 text-[11px] font-semibold text-[#475569]">
                            <button type="button" onclick="filterMapByStatus('SUBMITTED')" class="inline-flex items-center gap-1 px-1.5 py-1 rounded-full hover:bg-red-50 hover:text-[#EF4444] transition cursor-pointer whitespace-nowrap">
                                <span class="w-2 h-2 rounded-full bg-[#EF4444] shrink-0"></span>Verifikasi
                            </button>
                            <button type="button" onclick="filterMapByStatus('DISPATCHED')" class="inline-flex items-center gap-1 px-1.5 py-1 rounded-full hover:bg-[#EFF6FF] hover:text-[#2563EB] transition cursor-pointer whitespace-nowrap">
                                <span class="w-2 h-2 rounded-full bg-[#2563EB] shrink-0"></span>Ditugaskan
                            </button>
                            <button type="button" onclick="filterMapByStatus('PROCESSING')" class="inline-flex items-center gap-1 px-1.5 py-1 rounded-full hover:bg-amber-50 hover:text-[#B45309] transition cursor-pointer whitespace-nowrap">
                                <span class="w-2 h-2 rounded-full bg-[#F59E0B] shrink-0"></span>Diproses
                            </button>
                            <button type="button" onclick="filterMapByStatus('COMPLETED')" class="inline-flex items-center gap-1 px-1.5 py-1 rounded-full hover:bg-emerald-50 hover:text-[#047857] transition cursor-pointer whitespace-nowrap">
                                <span class="w-2 h-2 rounded-full bg-[#10B981] shrink-0"></span>Selesai
                            </button>
                            <button type="button" id="resetFilterBtn" onclick="resetAllFilters()"
                                class="hidden items-center gap-1 px-2 py-1 rounded-full bg-red-50 hover:bg-red-100 text-[#DC2626] font-bold transition cursor-pointer ml-auto whitespace-nowrap shrink-0" title="Reset filter">
                                Reset
                            </button>
                        </div>
                    </div>

                    <!-- List Content -->
                    <div id="drawerListContainer" class="p-3 space-y-2.5 overflow-y-auto flex-1 overscroll-contain bg-[#FAFAFA]" style="-webkit-overflow-scrolling: touch;">
                        <!-- Kartu-kartu aduan di-generate dinamis oleh JS -->
                    </div>

                    <!-- Mini Footer Hint -->
                    <div class="px-3.5 py-2 border-t border-[#E2E8F0] bg-white text-[11px] text-[#475569] flex items-center justify-between shrink-0">
                        <span class="flex items-center gap-1.5 truncate">
                            <i data-lucide="mouse-pointer-click" class="w-3.5 h-3.5 text-[#2563EB] shrink-0"></i>
                            <span class="truncate">Pilih aduan untuk menuju lokasi pada peta</span>
                        </span>
                    </div>
                </div>
            </div>

            <!-- Legend Status Peta (Pojok Kanan Bawah agar tidak bentrok dengan kartu kiri) -->
            <div class="absolute bottom-3 right-3 sm:bottom-4 sm:right-4 z-20 flex flex-wrap items-center gap-2.5 sm:gap-3.5 px-3 py-2 rounded-xl bg-white/95 backdrop-blur-sm border border-[#E2E8F0] shadow-sm text-[11px] font-semibold text-[#0F172A] select-none max-w-[92vw]">
                <span class="flex items-center gap-1.5">
                    <span class="w-3.5 h-3.5 rounded-full bg-[#EF4444] flex items-center justify-center text-white shrink-0">
                        <svg width="8" height="8" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                    </span>
                    Verifikasi
                </span>
                <span class="flex items-center gap-1.5">
                    <span class="w-3.5 h-3.5 rounded-full bg-[#2563EB] flex items-center justify-center text-white shrink-0">
                        <svg width="8" height="8" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg>
                    </span>
                    Ditugaskan
                </span>
                <span class="flex items-center gap-1.5">
                    <span class="w-3.5 h-3.5 rounded-full bg-[#F59E0B] flex items-center justify-center text-white shrink-0">
                        <svg width="8" height="8" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z"/></svg>
                    </span>
                    Diproses
                </span>
                <span class="flex items-center gap-1.5">
                    <span class="w-3.5 h-3.5 rounded-full bg-[#10B981] flex items-center justify-center text-white shrink-0">
                        <svg width="8" height="8" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3.5"><polyline points="20 6 9 17 4 12"/></svg>
                    </span>
                    Selesai
                </span>
            </div>
        </div>
    </div>

    <!-- Blok 1 (Di Bawah Peta): Kategori Infrastruktur yang Ditangani -->
    <section id="kategori-infrastruktur" class="scroll-mt-20 space-y-3.5">
        <div data-reveal>
            <h2 class="font-outfit font-bold text-xl sm:text-2xl text-[#0F172A]">
                Kategori Infrastruktur yang Ditangani
            </h2>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-6">
            <div data-reveal style="transition-delay: 0ms;" class="bg-white rounded-[24px] border border-[#E2E8F0] shadow-[0_6px_18px_rgba(15,23,42,0.04)] hover:shadow-md hover:border-[#2563EB]/40 transition-all duration-200 p-5 sm:p-6 flex flex-col justify-between gap-4">
                <div class="flex items-start justify-between gap-2">
                    <div class="w-11 h-11 rounded-[12px] bg-[#EFF6FF] text-[#2563EB] border border-[#BFDBFE] flex items-center justify-center shrink-0">
                        <i data-lucide="route" class="w-5 h-5"></i>
                    </div>
                    <span id="catCountJalan" class="px-3 py-1 rounded-full bg-[#EFF6FF] border border-[#BFDBFE] text-xs font-bold text-[#2563EB] whitespace-nowrap">
                        {{ $categoryStats['jalan'] ?? 0 }} Laporan
                    </span>
                </div>
                <div>
                    <h3 class="font-outfit font-bold text-base sm:text-lg text-[#0F172A] leading-snug">Jalan &amp; Jembatan</h3>
                    <p class="text-xs sm:text-sm text-[#475569] mt-1.5 leading-relaxed">Jalan berlubang, aspal retak, paving rusak, longsoran bahu jalan, dan kerusakan jembatan.</p>
                </div>
            </div>

            <div data-reveal style="transition-delay: 90ms;" class="bg-white rounded-[24px] border border-[#E2E8F0] shadow-[0_6px_18px_rgba(15,23,42,0.04)] hover:shadow-md hover:border-[#2563EB]/40 transition-all duration-200 p-5 sm:p-6 flex flex-col justify-between gap-4">
                <div class="flex items-start justify-between gap-2">
                    <div class="w-11 h-11 rounded-[12px] bg-[#ECFDF5] text-[#10B981] border border-[#A7F3D0] flex items-center justify-center shrink-0">
                        <i data-lucide="building-2" class="w-5 h-5"></i>
                    </div>
                    <span id="catCountFasilitas" class="px-3 py-1 rounded-full bg-[#EFF6FF] border border-[#BFDBFE] text-xs font-bold text-[#2563EB] whitespace-nowrap">
                        {{ $categoryStats['fasilitas'] ?? 0 }} Laporan
                    </span>
                </div>
                <div>
                    <h3 class="font-outfit font-bold text-base sm:text-lg text-[#0F172A] leading-snug">Fasilitas Umum Lainnya</h3>
                    <p class="text-xs sm:text-sm text-[#475569] mt-1.5 leading-relaxed">Lampu jalan padam, trotoar, taman kota, rambu, dan prasarana umum daerah lainnya.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Blok 2: Alur Penanganan Aduan (Stepper Card Horizontal 01-04 di Desktop, Vertical Timeline di Mobile) -->
    <section id="alur-penanganan" class="scroll-mt-20 space-y-5">
        <div data-reveal>
            <h2 class="font-outfit font-bold text-xl sm:text-2xl text-[#0F172A]">
                Alur Penanganan Aduan
            </h2>
        </div>

        <div class="relative">
            <!-- Garis Penghubung Putus-putus Horizontal (Desktop) -->
            <div class="hidden lg:block absolute top-1/2 left-[8%] right-[8%] border-t-2 border-dashed border-[#CBD5E1] z-0" aria-hidden="true"></div>

            <div class="grid grid-cols-1 lg:grid-cols-4 gap-4 lg:gap-5 relative z-10">
                <!-- Tahap 01 -->
                <div data-reveal style="transition-delay: 0ms;" class="bg-white rounded-[24px] border border-[#E2E8F0] shadow-[0_6px_18px_rgba(15,23,42,0.04)] p-5 sm:p-6 space-y-2.5">
                    <span class="font-outfit font-bold text-3xl text-[#2563EB] block leading-none">01</span>
                    <h3 class="font-outfit font-bold text-base text-[#0F172A]">Lapor Melalui Aplikasi</h3>
                    <p class="text-xs sm:text-sm text-[#475569] leading-relaxed">
                        Warga mengirim foto kerusakan &amp; titik GPS langsung dari lokasi melalui aplikasi mobile.
                    </p>
                </div>

                <!-- Tahap 02 -->
                <div data-reveal style="transition-delay: 100ms;" class="bg-white rounded-[24px] border border-[#E2E8F0] shadow-[0_6px_18px_rgba(15,23,42,0.04)] p-5 sm:p-6 space-y-2.5">
                    <span class="font-outfit font-bold text-3xl text-[#2563EB] block leading-none">02</span>
                    <h3 class="font-outfit font-bold text-base text-[#0F172A]">Verifikasi &amp; Disposisi Regu</h3>
                    <p class="text-xs sm:text-sm text-[#475569] leading-relaxed">
                        Operator menelaah keabsahan laporan dan menugaskan regu lapangan Dinas PUPR.
                    </p>
                </div>

                <!-- Tahap 03 -->
                <div data-reveal style="transition-delay: 200ms;" class="bg-white rounded-[24px] border border-[#E2E8F0] shadow-[0_6px_18px_rgba(15,23,42,0.04)] p-5 sm:p-6 space-y-2.5">
                    <span class="font-outfit font-bold text-3xl text-[#F59E0B] block leading-none">03</span>
                    <h3 class="font-outfit font-bold text-base text-[#0F172A]">Perbaikan Fisik di Lokasi</h3>
                    <p class="text-xs sm:text-sm text-[#475569] leading-relaxed">
                        Regu teknis mengerjakan perbaikan fisik sesuai batas SLA dan mengunggah foto bukti.
                    </p>
                </div>

                <!-- Tahap 04 -->
                <div data-reveal style="transition-delay: 300ms;" class="bg-white rounded-[24px] border border-[#A7F3D0] shadow-[0_6px_18px_rgba(15,23,42,0.04)] p-5 sm:p-6 space-y-2.5">
                    <span class="font-outfit font-bold text-3xl text-[#10B981] block leading-none">04</span>
                    <h3 class="font-outfit font-bold text-base text-[#0F172A]">Pemeriksaan Otomatis &amp; Penutupan</h3>
                    <p class="text-xs sm:text-sm text-[#475569] leading-relaxed">
                        Foto hasil pekerjaan diperiksa otomatis; bila sesuai, laporan dinyatakan tuntas dan peta publik ikut diperbarui.
                    </p>
                </div>
            </div>
        </div>
    </section>

    @php
        $featuredComplaint = \App\Models\Complaint::with(['category', 'photos', 'latestDisposition.team'])
            ->where('status', 'SUBMITTED')
            ->where('rejected_by_superadmin', false)
            ->where('created_at', '>=', now()->subDays(3))
            ->latest('created_at')
            ->first();
        $featuredBefore = $featuredComplaint?->photos->firstWhere('type', 'evidence_before');
        $featuredAfter = $featuredComplaint?->photos->firstWhere('type', 'resolution_after');
        $featuredBeforeUrl = $featuredBefore ? ($featuredBefore->url ?? asset('storage/' . $featuredBefore->storage_key)) : null;
        $featuredAfterUrl = $featuredAfter ? ($featuredAfter->url ?? asset('storage/' . $featuredAfter->storage_key)) : null;
    @endphp
    <!-- Blok 3: Kartu Pelacakan Status Aduan Terkini (Real Database PostgreSQL: Hanya Status Verifikasi dalam 3 Hari Terakhir) -->
    <section class="space-y-4">
        <h2 class="font-outfit font-bold text-xl sm:text-2xl text-[#0F172A]">
            Pelacakan Status Aduan Terkini
        </h2>
        @if($featuredComplaint)
        <div class="bg-white rounded-[24px] border border-[#E2E8F0] shadow-[0_8px_24px_rgba(15,23,42,0.05)] p-5 sm:p-8 space-y-5">
            <div class="flex flex-wrap items-center justify-between gap-2">
                <div class="flex flex-wrap items-center gap-2 text-xs sm:text-sm font-bold text-[#2563EB]">
                    <span class="font-mono">#{{ $featuredComplaint->public_code }}</span>
                    <span>&bull;</span>
                    <span>{{ $featuredComplaint->category?->name ?? 'Infrastruktur' }}</span>
                </div>
                <span class="px-3 py-1 rounded-full text-xs font-bold border bg-red-50 text-[#EF4444] border-red-200">
                    Verifikasi
                </span>
            </div>

            <h3 class="font-outfit font-bold text-lg sm:text-2xl text-[#0F172A] leading-snug">
                {{ $featuredComplaint->title }}
            </h3>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="h-48 sm:h-56 rounded-2xl bg-[#F1F5F9] border border-[#CBD5E1] overflow-hidden flex items-center justify-center relative">
                    @if($featuredBeforeUrl)
                        <img src="{{ $featuredBeforeUrl }}" alt="Foto Kondisi Kerusakan" loading="lazy" class="w-full h-full object-cover" onerror="this.classList.add('hidden'); this.nextElementSibling.classList.remove('hidden');">
                        <span class="hidden text-xs sm:text-sm font-semibold text-[#475569] px-4 text-center">Foto Kondisi Kerusakan (Sebelum)</span>
                    @else
                        <span class="text-xs sm:text-sm font-semibold text-[#475569] px-4 text-center">Foto Kondisi Kerusakan (Sebelum)</span>
                    @endif
                </div>
                <div class="h-48 sm:h-56 rounded-2xl bg-[#ECFDF5] border border-[#A7F3D0] overflow-hidden flex items-center justify-center relative">
                    @if($featuredAfterUrl)
                        <img src="{{ $featuredAfterUrl }}" alt="Foto Hasil Perbaikan Fisik" loading="lazy" class="w-full h-full object-cover" onerror="this.classList.add('hidden'); this.nextElementSibling.classList.remove('hidden');">
                        <span class="hidden text-xs sm:text-sm font-semibold text-[#047857] px-4 text-center">Foto Hasil Perbaikan Fisik (Sesudah)</span>
                    @else
                        <span class="text-xs sm:text-sm font-semibold text-[#047857] px-4 text-center">Foto Hasil Perbaikan Fisik (Sesudah)</span>
                    @endif
                </div>
            </div>

            <div class="p-4 rounded-2xl bg-[#F8FAFC] border border-[#E2E8F0] flex flex-col sm:flex-row sm:items-center justify-between gap-2 text-xs sm:text-sm">
                <span class="font-bold text-[#0F172A]">
                    {{ $featuredComplaint->latestDisposition?->team?->name ?? 'Menunggu Verifikasi Operator Dinas PUPR' }}
                </span>
                <span class="text-[#475569] truncate">{{ $featuredComplaint->address }}</span>
            </div>
        </div>
        @else
        <div class="bg-white rounded-[24px] border border-[#E2E8F0] shadow-[0_6px_18px_rgba(15,23,42,0.04)] p-8 sm:p-10 text-center space-y-2.5">
            <div class="w-12 h-12 rounded-2xl bg-[#EFF6FF] border border-[#BFDBFE] text-[#2563EB] flex items-center justify-center mx-auto">
                <i data-lucide="inbox" class="w-6 h-6"></i>
            </div>
            <h3 class="font-outfit font-bold text-base sm:text-lg text-[#0F172A]">
                Laporan terbaru belum masuk lagi
            </h3>
            <p class="text-xs sm:text-sm text-[#475569] max-w-md mx-auto leading-relaxed">
                Belum ada laporan baru berstatus menunggu verifikasi yang masuk dalam 3 hari terakhir. Seluruh laporan aktif dapat dipantau langsung melalui Peta Sebaran di atas.
            </p>
        </div>
        @endif
    </section>

    <!-- Banner Unduh Aplikasi Mobile + QR Code Desktop (Dark Accent Card #0F172A) -->
    <div id="download-app" class="scroll-mt-20 rounded-[24px] p-6 sm:p-10 bg-[#0F172A] text-white border border-slate-800 shadow-lg">
        <div class="flex flex-col lg:flex-row items-center justify-between gap-6 text-center lg:text-left">
            <div class="max-w-xl space-y-2.5">
                <span class="inline-flex items-center px-3 py-1 rounded-full bg-blue-500/15 border border-blue-400/30 text-[#60A5FA] text-xs font-semibold">
                    Aplikasi Resmi Warga Kabupaten
                </span>
                <h3 class="font-outfit font-bold text-xl sm:text-2xl lg:text-[26px] text-white tracking-tight">
                    Sampaikan Aduan Infrastruktur Anda Melalui Aplikasi
                </h3>
                <p class="text-sm text-slate-300 leading-relaxed">
                    Laporkan kerusakan jalan, jembatan, PJU, dan fasilitas umum langsung dari lokasi dengan foto dan titik GPS akurat.
                </p>
            </div>

            <!-- Tombol Unduh Aplikasi (Tanpa QR Code, Warna Seragam #2563EB, Terpusat Rapi di Mobile) -->
            @php
                $landingApkFile = public_path('downloads/SI-APPIK.APK');
                $landingApkVer = file_exists($landingApkFile) ? filemtime($landingApkFile) : time();
                $landingApkUrl = route('download.apk', ['v' => $landingApkVer]);
            @endphp
            <div class="flex flex-col sm:flex-row items-center justify-center lg:justify-end gap-3 shrink-0 w-full lg:w-auto">
                <a href="{{ $landingApkUrl }}" download="SI-APPIK.APK"
                    class="flex items-center justify-center gap-3 bg-[#2563EB] hover:bg-[#1D4ED8] text-white px-5 min-h-[52px] rounded-xl shadow-sm transition w-full sm:w-52 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-white">
                    <svg class="w-5 h-5 fill-current shrink-0" viewBox="0 0 24 24">
                        <path d="M3,20.5V3.5C3,2.91 3.34,2.39 3.84,2.15L13.69,12L3.84,21.85C3.34,21.6 3,21.09 3,20.5M16.81,15.12L6.05,21.34L14.54,12.85L16.81,15.12M20.16,10.81C20.5,11.08 20.75,11.5 20.75,12C20.75,12.5 20.5,12.92 20.16,13.19L17.89,14.5L15.39,12L17.89,9.5L20.16,10.81M6.05,2.66L16.81,8.88L14.54,11.15L6.05,2.66Z" />
                    </svg>
                    <div class="text-center sm:text-left leading-tight">
                        <span class="text-[10px] uppercase tracking-wider text-blue-100 block font-semibold">TERSEDIA DI</span>
                        <span class="font-outfit font-bold text-sm text-white block">Google Play Store</span>
                    </div>
                </a>

                <a href="https://apps.apple.com" target="_blank" rel="noopener noreferrer" 
                    class="flex items-center justify-center gap-3 bg-[#2563EB] hover:bg-[#1D4ED8] text-white px-5 min-h-[52px] rounded-xl shadow-sm transition w-full sm:w-48 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-white">
                    <svg class="w-5 h-5 fill-current shrink-0" viewBox="0 0 24 24">
                        <path d="M18.71 19.5c-.83 1.24-1.71 2.45-3.05 2.47-1.34.03-1.77-.79-3.29-.79-1.53 0-2 .77-3.27.82-1.31.05-2.3-1.32-3.14-2.53C4.25 17 2.94 12.45 4.7 9.39c.87-1.52 2.43-2.48 4.12-2.51 1.28-.02 2.5.87 3.29.87.78 0 2.26-1.07 3.81-.91.65.03 2.47.26 3.64 1.98-.09.06-2.17 1.28-2.15 3.81.03 3.02 2.65 4.03 2.68 4.04-.03.07-.42 1.44-1.38 2.83M15.97 6.37c.62-.75 1.04-1.8 1.01-2.87-.96.04-2.12.64-2.8 1.44-.59.68-1.11 1.77-1 2.82 1.07.08 2.17-.55 2.79-1.39z"/>
                    </svg>
                    <div class="text-center sm:text-left leading-tight">
                        <span class="text-[10px] uppercase tracking-wider text-blue-100 block font-semibold">TERSEDIA DI</span>
                        <span class="font-outfit font-bold text-sm text-white block">App Store</span>
                    </div>
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Modal Detail Pengaduan Publik -->
@include('partials.modals.landing_complaint_detail_modal')

<!-- Tombol Mengambang Unduh APK (Konsisten dengan tombol biru #2563EB) -->
<a id="floatingDownloadApkBtn" href="{{ $landingApkUrl }}" download="SI-APPIK.APK"
    aria-label="Unduh APK Android SI-APPIK"
    style="bottom: 24px;"
    class="fixed right-4 sm:right-6 z-40 inline-flex items-center gap-2.5 bg-[#2563EB] hover:bg-[#1D4ED8] text-white px-5 py-3 rounded-full border border-blue-400/30 shadow-lg transition-[opacity,transform] duration-300 opacity-0 translate-y-3 pointer-events-none focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#2563EB]">
    <svg class="w-5 h-5 fill-[#3DDC84] shrink-0" viewBox="0 0 24 24" aria-hidden="true">
        <path d="M17.6 9.48l1.84-3.18c.16-.31.04-.69-.26-.85-.29-.15-.65-.06-.83.22l-1.88 3.24c-1.4-.63-2.96-.98-4.47-.98s-3.07.35-4.47.98L5.65 5.67c-.19-.29-.55-.38-.84-.22-.3.16-.42.54-.26.85l1.84 3.18C4.07 10.94 2.5 13.38 2.13 16.25h19.74c-.37-2.87-1.94-5.31-4.27-6.77zM8.5 13.5c-.55 0-1-.45-1-1s.45-1 1-1 1 .45 1 1-.45 1-1 1zm7 0c-.55 0-1-.45-1-1s.45-1 1-1 1 .45 1 1-.45 1-1 1z"/>
    </svg>
    <span class="font-outfit font-bold text-xs sm:text-sm tracking-tight whitespace-nowrap">Unduh APK</span>
</a>



@push('scripts')
<style>
    .custom-map-tooltip {
        background-color: #0F172A !important;
        color: #FFFFFF !important;
        font-weight: 700 !important;
        font-size: 11px !important;
        border-radius: 8px !important;
        padding: 5px 10px !important;
        border: 1px solid rgba(255,255,255,0.2) !important;
        box-shadow: 0 4px 14px rgba(0,0,0,0.3) !important;
    }
    .custom-map-tooltip::before {
        border-top-color: #0F172A !important;
    }
    .no-scrollbar::-webkit-scrollbar { display: none; }
    .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
</style>

<script>
    let map;
    let markersLayer;
    let markersMap = {};
    let reportsData = @json($mapPoints ?? []);

    function hideMapLoading() {
        const el = document.getElementById('mapLoading');
        if (el) {
            el.style.display = 'none';
            el.style.opacity = '0';
            el.classList.add('hidden');
        }
    }

    document.addEventListener('DOMContentLoaded', () => {
        initMap();

        // Jika data titik peta sudah di-inject dari server, render instan tanpa menunggu request jaringan
        if (reportsData && reportsData.length > 0) {
            applyFilters(false);
            hideMapLoading();
        } else {
            loadMapData();
        }

        setupSearch();

        // Failsafe: Pastikan indikator loading dihilangkan maksimal dalam 500ms
        setTimeout(() => {
            hideMapLoading();
            if (map) map.invalidateSize();
        }, 500);

        // Keyboard ESC untuk menutup modal detail
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                if (typeof closeLandingDetailModal === 'function') closeLandingDetailModal();
                else if (typeof closeDetailModal === 'function') closeDetailModal();
            }
        });

        // Klik background luar untuk menutup modal
        const modal = document.getElementById('landingComplaintDetailModal');
        if (modal) {
            modal.addEventListener('click', (e) => {
                if (e.target === modal) {
                    if (typeof closeLandingDetailModal === 'function') closeLandingDetailModal();
                    else if (typeof closeDetailModal === 'function') closeDetailModal();
                }
            });
        }
    });

    function initMap() {
        const mapContainer = document.getElementById('gisMap');
        if (!mapContainer || mapContainer._leaflet_id) return;

        // Inisialisasi Peta GIS Kabupaten (Kabupaten)
        map = L.map('gisMap', {
            zoomControl: false,
            scrollWheelZoom: true,
        }).setView([-3.50601, 120.88780], 12);

        // Posisikan kontrol zoom di pojok kanan atas agar tidak tertutup panel Filter & Daftar Aduan di sisi kiri
        L.control.zoom({ position: 'topright' }).addTo(map);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '© OpenStreetMap kontributor | Sistem Pengaduan Daerah'
        }).addTo(map);

        markersLayer = L.layerGroup().addTo(map);

        // Batas Administratif Resmi Wilayah Kabupaten
        fetch('{{ asset("geojson/kolaka_utara.geojson") }}')
            .then(res => res.json())
            .then(geoJsonData => {
                L.geoJSON(geoJsonData, {
                    interactive: false,
                    style: {
                        color: "#2563EB",
                        weight: 2.5,
                        dashArray: '6, 6',
                        fillColor: "#3B82F6",
                        fillOpacity: 0.06
                    }
                }).addTo(map);
            })
            .catch(err => console.log('Polygon error', err));
            
        setTimeout(() => {
            if (map) map.invalidateSize();
        }, 250);
    }

    let currentStatusFilter = 'ALL';
    let currentDistrictFilter = 'ALL';
    let isDrawerOpen = false;

    async function loadMapData() {
        try {
            const resp = await fetch('{{ url("/api/v1/dashboard/map") }}');
            if (resp.ok) {
                const json = await resp.json();
                if (json && json.data) {
                    reportsData = json.data;
                    updateCategoryCounts(reportsData);
                    applyFilters(false);
                }
            }
        } catch (err) {
            console.error('Gagal memuat data titik peta:', err);
        } finally {
            hideMapLoading();
        }
    }

    function updateCategoryCounts(data) {
        if (!Array.isArray(data)) return;
        const counts = { jalan: 0, fasilitas: 0 };
        data.forEach(item => {
            const catId = Number(item.category_id || 0);
            // Hanya 2 kategori resmi: 1 = Jalan & Jembatan, sisanya Fasilitas Umum Lainnya
            if (catId === 1) {
                counts.jalan++;
            } else {
                counts.fasilitas++;
            }
        });
        const mapIds = {
            catCountJalan: counts.jalan,
            catCountFasilitas: counts.fasilitas
        };
        Object.entries(mapIds).forEach(([id, val]) => {
            const el = document.getElementById(id);
            if (el) el.textContent = `${val} Laporan`;
        });
    }

    let isFilterBodyOpen = window.innerWidth >= 768;

    function syncFilterPanelBodyState() {
        const body = document.getElementById('filterDrawerBody');
        const chevron = document.getElementById('filterCollapseChevron');
        const btn = document.getElementById('filterCollapseBtn');
        if (!body) return;

        if (isFilterBodyOpen) {
            body.style.maxHeight = '540px';
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

    document.addEventListener('DOMContentLoaded', syncFilterPanelBodyState);

    function toggleFilterPanelBody() {
        isFilterBodyOpen = !isFilterBodyOpen;
        syncFilterPanelBodyState();
    }

    function toggleReportDrawer() {
        isDrawerOpen = !isDrawerOpen;
        if (isDrawerOpen && !isFilterBodyOpen) {
            isFilterBodyOpen = true;
            syncFilterPanelBodyState();
        }

        setTimeout(() => {
            if (map) map.invalidateSize();
        }, 300);
    }

    function onDistrictFilterChange(district) {
        currentDistrictFilter = district;
        applyFilters(true);
    }

    function filterMapByStatus(status) {
        currentStatusFilter = status;
        applyFilters(true);

        // Pastikan daftar titik (drawer) otomatis terbuka saat kartu status diklik (termasuk 'Semua Laporan')
        if (!isDrawerOpen) {
            toggleReportDrawer();
        }

        // Gulir halus ke arah peta agar warga langsung melihat titik yang disorot
        const mapSection = document.getElementById('mapSectionContainer');
        if (mapSection) {
            mapSection.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        }
    }

    function resetAllFilters() {
        currentStatusFilter = 'ALL';
        currentDistrictFilter = 'ALL';
        const distSelect = document.getElementById('districtFilterSelect');
        if (distSelect) distSelect.value = 'ALL';
        applyFilters(true);
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
            toast.className = 'fixed top-20 sm:top-24 left-1/2 -translate-x-1/2 z-[90] max-w-[92vw] px-4 py-2.5 rounded-full bg-white text-[#0F172A] text-xs font-semibold shadow-[0_12px_32px_rgba(15,23,42,0.14)] flex items-center gap-2.5 border border-[#E2E8F0] transition-all duration-300 opacity-0 pointer-events-none transform -translate-y-2';
            document.body.appendChild(toast);
        }
        toast.innerHTML = `
            <span class="w-6 h-6 rounded-full bg-[#EFF6FF] border border-[#BFDBFE] text-[#2563EB] flex items-center justify-center shrink-0">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </span>
            <span class="leading-snug">${message}</span>
        `;
        toast.classList.remove('opacity-0', 'pointer-events-none', '-translate-y-2');
        toast.classList.add('opacity-100', 'translate-y-0');

        clearTimeout(window._mapToastTimer);
        window._mapToastTimer = setTimeout(() => {
            toast.classList.remove('opacity-100', 'translate-y-0');
            toast.classList.add('opacity-0', 'pointer-events-none', '-translate-y-2');
        }, 3200);
    }

    function applyFilters(shouldZoom = true) {
        // Update status aktif KPI box (border tegas tanpa bayangan atau transparansi)
        document.querySelectorAll('.kpi-filter-box').forEach(box => {
            if (box.getAttribute('data-status') === currentStatusFilter) {
                box.classList.remove('border-[#E2E8F0]');
                box.classList.add('border-[#2563EB]', 'bg-[#F8FAFC]');
            } else {
                box.classList.remove('border-[#2563EB]', 'bg-[#F8FAFC]');
                box.classList.add('border-[#E2E8F0]');
            }
        });

        // Tampilkan tombol Reset Filter persis di samping dropdown filter jika filter sedang aktif
        const resetBtn = document.getElementById('resetFilterBtn');
        if (resetBtn) {
            if (currentStatusFilter !== 'ALL' || currentDistrictFilter !== 'ALL') {
                resetBtn.classList.remove('hidden');
                resetBtn.classList.add('inline-flex');
            } else {
                resetBtn.classList.add('hidden');
                resetBtn.classList.remove('inline-flex');
            }
        }

        // Update active filter badge & labels
        const drawerFilterStatusText = document.getElementById('drawerFilterStatusText');

        let statusLabel = '';
        if (currentStatusFilter === 'PROCESSING' || currentStatusFilter === 'IN_HANDLING') statusLabel = 'Diproses';
        else if (currentStatusFilter === 'COMPLETED') statusLabel = 'Selesai';
        else if (currentStatusFilter === 'SUBMITTED') statusLabel = 'Verifikasi';
        else if (currentStatusFilter === 'DISPATCHED') statusLabel = 'Ditugaskan';

        let distLabel = currentDistrictFilter !== 'ALL' 
            ? currentDistrictFilter.replace(/^Kecamatan\s*/i, 'Kec. ') 
            : '';

        let summaryParts = [];
        if (statusLabel) summaryParts.push(statusLabel);
        if (distLabel) summaryParts.push(distLabel);

        if (summaryParts.length > 0) {
            if (drawerFilterStatusText) drawerFilterStatusText.innerText = `Filter: ${summaryParts.join(' • ')}`;
        } else {
            if (drawerFilterStatusText) drawerFilterStatusText.innerText = 'Menampilkan semua titik di Kabupaten';
        }

        // Filtering dataset
        let filtered = reportsData.filter(r => r.status !== 'REJECTED');

        if (currentStatusFilter === 'PROCESSING') {
            filtered = filtered.filter(r => ['SUBMITTED', 'VERIFICATION', 'DISPATCHED', 'IN_PROGRESS', 'WAITING_VERIFICATION', 'REVISION_REQUIRED'].includes(r.status));
        } else if (currentStatusFilter === 'SUBMITTED') {
            filtered = filtered.filter(r => ['SUBMITTED', 'VERIFICATION'].includes(r.status));
        } else if (currentStatusFilter === 'DISPATCHED') {
            filtered = filtered.filter(r => r.status === 'DISPATCHED');
        } else if (currentStatusFilter === 'IN_HANDLING') {
            filtered = filtered.filter(r => ['IN_PROGRESS', 'WAITING_VERIFICATION', 'REVISION_REQUIRED'].includes(r.status));
        } else if (currentStatusFilter === 'COMPLETED') {
            filtered = filtered.filter(r => ['COMPLETED', 'CLOSED'].includes(r.status));
        }

        if (currentDistrictFilter && currentDistrictFilter !== 'ALL') {
            const cleanDist = currentDistrictFilter.replace(/^(Kecamatan|Kec\.)\s*/i, '').trim().toLowerCase();
            filtered = filtered.filter(r => {
                if (!r.address) return false;
                return r.address.toLowerCase().includes(cleanDist);
            });
        }

        // Pastikan drawer terbuka jika user aktif memfilter
        if (!isDrawerOpen && (currentStatusFilter !== 'ALL' || currentDistrictFilter !== 'ALL')) {
            toggleReportDrawer();
        }

        // Update drawer badge counts
        const drawerCountBadge = document.getElementById('drawerCountBadge');
        if (drawerCountBadge) drawerCountBadge.innerText = filtered.length;
        const drawerTotalText = document.getElementById('drawerTotalText');
        if (drawerTotalText) drawerTotalText.innerText = `${filtered.length} Aduan`;

        // Render drawer items
        renderDrawerList(filtered);

        // Feedback ramah jika filter tidak menemukan aduan di kecamatan yang dipilih
        if (shouldZoom && filtered.length === 0) {
            if (currentDistrictFilter && currentDistrictFilter !== 'ALL') {
                const distName = currentDistrictFilter.replace(/^Kecamatan\s*/i, 'Kecamatan ');
                showMapToast(`Belum ada laporan masuk di ${distName} untuk status ini.`);
            } else {
                showMapToast('Belum ada laporan masuk untuk filter status ini.');
            }
        }

        // Render markers on map
        renderMarkers(filtered, shouldZoom);
    }

    function renderDrawerList(items) {
        const container = document.getElementById('drawerListContainer');
        if (!container) return;

        if (!items || items.length === 0) {
            container.innerHTML = `
                <div class="py-12 px-4 text-center">
                    <div class="w-12 h-12 rounded-2xl bg-blue-50 text-[#1E3A8A] flex items-center justify-center mx-auto mb-3">
                        <i data-lucide="map-pin-off" class="w-6 h-6"></i>
                    </div>
                    <h4 class="text-xs font-bold text-slate-800">Tidak Ada Titik Aduan</h4>
                    <p class="text-[11px] text-slate-500 mt-1 max-w-[230px] mx-auto leading-relaxed">
                        Tidak ada laporan yang sesuai dengan kombinasi status & kecamatan yang dipilih.
                    </p>
                    <button type="button" onclick="resetAllFilters()" class="mt-3.5 px-3 py-1.5 rounded-xl bg-white border border-slate-200 shadow-2xs text-xs font-bold text-[#1E3A8A] hover:bg-slate-50 transition">
                        Tampilkan Semua Titik
                    </button>
                </div>
            `;
            lucide.createIcons({ root: container });
            return;
        }

        let html = '';
        items.forEach(item => {
            const adm = parseAdministrativeAddress(item.address);
            const districtText = getDistrictDisplayName(item.address);
            const shortLocation = adm.village && adm.village !== 'Desa Wilayah' ? adm.village : (adm.road && adm.road !== 'Jalan / Area Kerusakan' ? adm.road : 'Kabupaten');

            let badgeBg = 'bg-red-50 text-red-700 border-red-200';
            let badgeText = 'VERIFIKASI';
            if (item.status === 'DISPATCHED') {
                badgeBg = 'bg-blue-50 text-[#1E3A8A] border-blue-200';
                badgeText = 'DITUGASKAN';
            } else if (item.status === 'IN_PROGRESS' || item.status === 'REVISION_REQUIRED') {
                badgeBg = 'bg-amber-50 text-amber-800 border-amber-200';
                badgeText = item.status === 'REVISION_REQUIRED' ? 'PERLU REVISI' : 'DIKERJAKAN';
            } else if (item.status === 'WAITING_VERIFICATION') {
                badgeBg = 'bg-amber-50 text-amber-700 border-amber-200';
                badgeText = 'VALIDASI';
            } else if (item.status === 'COMPLETED' || item.status === 'CLOSED') {
                badgeBg = 'bg-emerald-50 text-emerald-700 border-emerald-200';
                badgeText = 'SELESAI';
            }

            const imgHtml = item.photo_url
                ? `<img src="${item.photo_url}" class="w-full h-full object-cover group-hover:scale-110 transition duration-300" alt="Foto Bukti" loading="lazy">`
                : `<div class="w-full h-full flex items-center justify-center bg-slate-100 text-slate-400">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                   </div>`;

            html += `
                <div onclick="focusReportOnMap(${item.id})" 
                    class="p-2.5 sm:p-3 rounded-2xl bg-white border border-slate-200 hover:border-[#1E3A8A] hover:shadow-md cursor-pointer transition transform hover:-translate-y-0.5 flex items-center gap-3 group">
                    <div class="w-14 h-14 rounded-xl overflow-hidden bg-slate-100 border border-slate-200 shrink-0 relative">
                        ${imgHtml}
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center justify-between gap-1 mb-1">
                            <span class="text-[10px] font-extrabold px-1.5 py-0.5 rounded bg-slate-100 text-slate-700 tracking-tight">
                                ${item.public_code}
                            </span>
                            <span class="text-[9px] font-bold px-2 py-0.5 rounded-full border ${badgeBg}">
                                ${badgeText}
                            </span>
                        </div>
                        <h4 class="text-xs font-bold text-slate-900 truncate group-hover:text-[#1E3A8A] transition">
                            ${item.title || 'Laporan Kerusakan'}
                        </h4>
                        <div class="flex items-center gap-1 text-[11px] text-slate-500 mt-0.5 truncate">
                            <span class="font-bold text-slate-700">${districtText}</span>
                            <span>•</span>
                            <span class="truncate">${shortLocation}</span>
                        </div>
                    </div>
                    <div class="text-slate-300 group-hover:text-[#1E3A8A] transition shrink-0 pr-1">
                        <i data-lucide="chevron-right" class="w-4 h-4"></i>
                    </div>
                </div>
            `;
        });

        container.innerHTML = html;
        lucide.createIcons({ root: container });
    }

    function focusReportOnMap(id) {
        const item = reportsData.find(r => r.id === id);
        if (!item || !item.latitude || !item.longitude) return;

        // Meluncur halus (smooth pan / flyTo) langsung ke titik spesifik itu tanpa loncat membabi buta
        map.flyTo([item.latitude, item.longitude], 16, {
            animate: true,
            duration: 1.1
        });

        // Buka rincian lengkap & foto
        setTimeout(() => {
            if (typeof openLandingDetailModal === 'function') {
                openLandingDetailModal(item);
            } else if (typeof openDetailModal === 'function') {
                openDetailModal(item);
            }
        }, 900);
    }

    function renderMarkers(items, shouldZoom = true) {
        markersLayer.clearLayers();
        markersMap = {};
        if (!items || items.length === 0) return;

        const bounds = [];

        items.forEach(item => {
            if (!item.latitude || !item.longitude) return;

            // Jangan tampilkan laporan yang berstatus DITOLAK pada peta publik
            if (item.status === 'REJECTED') return;

            const latLng = [item.latitude, item.longitude];
            bounds.push(latLng);

            // Warna & Ikon Pin berdasarkan status (Sesuai Gambar 4 & 5)
            let pinColor = '#EF4444'; // Verifikasi (Merah)
            let statusText = 'VERIFIKASI';
            let iconSvg = `
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="#FFFFFF" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="10"/>
                    <line x1="12" y1="8" x2="12" y2="12"/>
                    <line x1="12" y1="16" x2="12.01" y2="16"/>
                </svg>
            `;

            if (item.status === 'DISPATCHED') {
                pinColor = '#1E3A8A'; // Ditugaskan (Biru Resmi)
                statusText = 'DITUGASKAN';
                iconSvg = `
                    <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="#FFFFFF" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="22" y1="2" x2="11" y2="13"/>
                        <polygon points="22 2 15 22 11 13 2 9 22 2"/>
                    </svg>
                `;
            } else if (item.status === 'IN_PROGRESS' || item.status === 'REVISION_REQUIRED') {
                pinColor = '#F59E0B'; // Dikerjakan (Kuning / Amber)
                statusText = item.status === 'REVISION_REQUIRED' ? 'PERLU REVISI' : 'DIKERJAKAN';
                iconSvg = `
                    <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="#FFFFFF" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z"/>
                    </svg>
                `;
            } else if (item.status === 'WAITING_VERIFICATION') {
                pinColor = '#F59E0B'; // Diproses (Kuning / Amber)
                statusText = 'VALIDASI';
                iconSvg = `
                    <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="#FFFFFF" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z"/>
                    </svg>
                `;
            } else if (item.status === 'COMPLETED' || item.status === 'CLOSED') {
                pinColor = '#10B981'; // Selesai (Hijau Emerald)
                statusText = 'SELESAI';
                iconSvg = `
                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="#FFFFFF" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="20 6 9 17 4 12"/>
                    </svg>
                `;
            } else if (item.status === 'REJECTED') {
                pinColor = '#DC2626'; // Ditolak (Merah Tua)
                statusText = 'DITOLAK';
                iconSvg = `
                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="#FFFFFF" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="18" y1="6" x2="6" y2="18"/>
                        <line x1="6" y1="6" x2="18" y2="18"/>
                    </svg>
                `;
            }

            // Circular Badge Pin Marker (Persis Gambar 5)
            const customIcon = L.divIcon({
                className: 'custom-pin',
                html: `
                    <div style="
                        background-color: ${pinColor};
                        width: 26px;
                        height: 26px;
                        border-radius: 50%;
                        border: 2.5px solid #FFFFFF;
                        box-shadow: 0 4px 10px rgba(0,0,0,0.35);
                        display: flex;
                        align-items: center;
                        justify-content: center;
                        cursor: pointer;
                        transition: transform 0.15s ease;
                    " onmouseover="this.style.transform='scale(1.25)'" onmouseout="this.style.transform='scale(1)'">
                        ${iconSvg}
                    </div>
                `,
                iconSize: [26, 26],
                iconAnchor: [13, 13]
            });

            const marker = L.marker(latLng, { icon: customIcon }).addTo(markersLayer);
            markersMap[item.id] = marker;

            // Visualisasi Jalur Ruas Jalan Rusak Panjang (>500 Meter / Major)
            if (item.damage_type === 'major' && item.end_latitude && item.end_longitude) {
                let lineCoords = [latLng, [item.end_latitude, item.end_longitude]];
                if (Array.isArray(item.route_coordinates) && item.route_coordinates.length > 1) {
                    lineCoords = item.route_coordinates;
                }
                const polyline = L.polyline(lineCoords, {
                    color: pinColor,
                    weight: 6,
                    opacity: 0.85,
                    dashArray: '6, 8',
                    lineCap: 'round'
                }).addTo(markersLayer);

                polyline.bindTooltip(`Ruas Jalan Rusak: ${item.road_length || 'Panjang'} • ${statusText}`, {
                    direction: 'top',
                    className: 'custom-map-tooltip'
                });

                polyline.on('click', () => {
                    if (typeof openLandingDetailModal === 'function') {
                        openLandingDetailModal(item);
                    } else if (typeof openDetailModal === 'function') {
                        openDetailModal(item);
                    }
                });

                bounds.push([item.end_latitude, item.end_longitude]);
            }

            // Tooltip ringkas
            const shortTitle = item.title && item.title.length > 25 ? item.title.substring(0, 25) + '...' : (item.title || '');
            const typeLabel = item.damage_type === 'major' ? `[Kerusakan Berat: ${item.road_length ? item.road_length + 'm' : 'Parah'}]` : '[Kerusakan Ringan]';
            marker.bindTooltip(`${item.public_code} • ${typeLabel} ${shortTitle} - ${statusText}`, {
                direction: 'top',
                offset: [0, -14],
                className: 'custom-map-tooltip'
            });

            // Klik marker membuka modal detail popup tanpa lag
            marker.on('click', () => {
                if (typeof openLandingDetailModal === 'function') {
                    openLandingDetailModal(item);
                } else if (typeof openDetailModal === 'function') {
                    openDetailModal(item);
                }
            });
        });

        // Smart fitBounds: proporsional dan tidak loncat membabi buta
        if (shouldZoom && bounds.length > 0) {
            if (bounds.length === 1) {
                map.flyTo(bounds[0], 15, { animate: true, duration: 0.9 });
            } else {
                map.fitBounds(bounds, { padding: [50, 50], maxZoom: 15 });
            }
        }
    }

    // Fungsi Penguraian Alamat Lengkap (Jalan, Desa, Kecamatan, Kabupaten)
    function parseAdministrativeAddress(rawAddress) {
        let road = 'Jalan / Area Kerusakan';
        let village = 'Desa Wilayah';
        let district = 'Kecamatan Terkait';
        let regency = 'Kabupaten';

        if (!rawAddress) {
            return { road, village, district, regency };
        }

        const parts = rawAddress.split(',').map(s => s.trim());

        parts.forEach(p => {
            if (p.toLowerCase().startsWith('desa') || p.toLowerCase().startsWith('kel.')) {
                village = p;
            } else if (p.toLowerCase().startsWith('kec.')) {
                district = p;
            } else if (p.toLowerCase().startsWith('kab.') || p.toLowerCase().startsWith('kota')) {
                regency = p;
            } else if (!p.toLowerCase().includes('titik koordinat')) {
                road = p;
            }
        });

        if (parts.length === 1 && !rawAddress.toLowerCase().includes('titik koordinat')) {
            road = rawAddress;
        }

        return { road, village, district, regency };
    }

    // Bridge pemanggilan modal detail landing page (Mengarahkan langsung ke modal resmi landing page)
    window.openDetailModal = function(item) {
        if (typeof openLandingDetailModal === 'function') {
            openLandingDetailModal(item);
        }
    };
    window.closeDetailModal = function() {
        if (typeof closeLandingDetailModal === 'function') {
            closeLandingDetailModal();
        }
    };

    function setupSearch() {
        const form = document.getElementById('trackForm');
        const input = document.getElementById('trackCodeInput');
        const suggestionsBox = document.getElementById('searchSuggestions');
        const suggestionsList = document.getElementById('searchSuggestionsList');
        const countSpan = document.getElementById('searchResultCount');
        const container = document.getElementById('searchContainer');

        let currentMatches = [];
        let highlightedIndex = -1;

        function normalizeText(text) {
            return (text || '').toLowerCase().trim();
        }

        function highlightMatch(text, query) {
            if (!query || !text) return text || '';
            const cleanQuery = query.trim().replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
            if (!cleanQuery) return text;
            const regex = new RegExp(`(${cleanQuery})`, 'gi');
            return text.replace(regex, '<mark class="bg-[#EFF6FF] text-[#2563EB] font-bold rounded px-0.5">$1</mark>');
        }

        function renderStatusPill(status) {
            switch (status) {
                case 'SUBMITTED':
                case 'PENDING':
                case 'VERIFIED':
                case 'VERIFICATION':
                    return '<span class="px-2.5 py-1 rounded-full text-[11px] font-bold bg-red-50 text-[#EF4444] border border-red-200 shrink-0">Verifikasi</span>';
                case 'DISPATCHED':
                    return '<span class="px-2.5 py-1 rounded-full text-[11px] font-bold bg-[#EFF6FF] text-[#2563EB] border border-[#BFDBFE] shrink-0">Ditugaskan</span>';
                case 'IN_PROGRESS':
                case 'REVISION_REQUIRED':
                case 'WAITING_VERIFICATION':
                    return '<span class="px-2.5 py-1 rounded-full text-[11px] font-bold bg-amber-50 text-[#B45309] border border-[#FDE68A] shrink-0">Diproses</span>';
                case 'COMPLETED':
                case 'RESOLVED':
                case 'CLOSED':
                    return '<span class="px-2.5 py-1 rounded-full text-[11px] font-bold bg-emerald-50 text-[#047857] border border-[#A7F3D0] shrink-0">Selesai</span>';
                default:
                    return '<span class="px-2.5 py-1 rounded-full text-[11px] font-bold bg-slate-100 text-[#475569] border border-[#E2E8F0] shrink-0">Verifikasi</span>';
            }
        }

        function doSearch(query) {
            const q = normalizeText(query);
            if (!q) {
                suggestionsBox.classList.add('hidden');
                currentMatches = [];
                highlightedIndex = -1;
                return;
            }

            // Normalisasi tanpa tanda baca / pemisah untuk memudahkan pencarian ID format apa pun
            const qClean = q.replace(/[^a-z0-9]/g, '');

            currentMatches = reportsData.filter(r => {
                const title = normalizeText(r.title);
                const code = normalizeText(r.public_code);
                const cleanCode = code.replace(/[^a-z0-9]/g, '');
                const reporter = normalizeText(r.reporter_name || r.citizen_name);
                const address = normalizeText(r.address);
                const category = normalizeText(r.category_name);
                const desc = normalizeText(r.description);

                return title.includes(q) ||
                       code.includes(q) ||
                       (qClean && cleanCode.includes(qClean)) ||
                       reporter.includes(q) ||
                       address.includes(q) ||
                       category.includes(q) ||
                       desc.includes(q);
            });

            // Urutkan prioritas: judul atau nomor tiket yang diawali kata kunci ditaruh di paling atas
            currentMatches.sort((a, b) => {
                const aTitle = normalizeText(a.title);
                const bTitle = normalizeText(b.title);
                const aCode = normalizeText(a.public_code);
                const bCode = normalizeText(b.public_code);

                const aStarts = aTitle.startsWith(q) || aCode.startsWith(q);
                const bStarts = bTitle.startsWith(q) || bCode.startsWith(q);

                if (aStarts && !bStarts) return -1;
                if (!aStarts && bStarts) return 1;
                return 0;
            });

            const displayMatches = currentMatches.slice(0, 6);
            highlightedIndex = -1;

            if (displayMatches.length === 0) {
                countSpan.innerText = '0 ditemukan';
                suggestionsList.innerHTML = `
                    <div class="p-6 text-center">
                        <div class="w-10 h-10 rounded-full bg-slate-100 text-[#475569] mx-auto flex items-center justify-center mb-2">
                            <i data-lucide="search-x" class="w-5 h-5"></i>
                        </div>
                        <p class="text-xs font-bold text-[#0F172A]">Tidak ada aduan ditemukan untuk "${query}"</p>
                        <p class="text-xs text-[#475569] mt-1 max-w-sm mx-auto">
                            Silakan ketik kata kunci kerusakan (contoh: <strong>aspal</strong>, <strong>jembatan</strong>), nama pelapor, atau nomor tiket.
                        </p>
                    </div>
                `;
            } else {
                countSpan.innerText = `${currentMatches.length} aduan ditemukan`;
                suggestionsList.innerHTML = displayMatches.map((r, idx) => {
                    const addressSnippet = r.address ? r.address.split(',')[0] : 'Kabupaten';
                    return `
                        <div class="suggestion-item px-5 py-3.5 hover:bg-[#F8FAFC] transition flex items-center justify-between gap-3 cursor-pointer group" data-index="${idx}">
                            <div class="flex items-center gap-3.5 min-w-0">
                                <div class="suggestion-icon-box w-9 h-9 rounded-xl bg-[#EFF6FF] border border-[#BFDBFE] text-[#2563EB] group-hover:bg-[#2563EB] group-hover:text-white group-hover:border-[#2563EB] flex items-center justify-center shrink-0 transition">
                                    <i data-lucide="map-pin" class="w-4 h-4"></i>
                                </div>
                                <div class="min-w-0 space-y-1">
                                    <div class="text-sm font-bold text-[#0F172A] group-hover:text-[#2563EB] transition truncate leading-snug">
                                        ${highlightMatch(r.title, query)}
                                    </div>
                                    <div class="text-xs text-[#475569] flex flex-wrap items-center gap-1.5">
                                        <span class="font-bold text-[#2563EB] bg-[#EFF6FF] px-2 py-0.5 rounded-md text-[11px] tracking-wide font-mono border border-[#BFDBFE]">
                                            ${highlightMatch(r.public_code, query)}
                                        </span>
                                        <span class="text-slate-300">&bull;</span>
                                        <span class="truncate">Pelapor: <strong class="text-[#0F172A] font-semibold">${highlightMatch(r.reporter_name || 'Warga', query)}</strong></span>
                                        <span class="text-slate-300">&bull;</span>
                                        <span class="text-[#475569] truncate">${addressSnippet}</span>
                                    </div>
                                </div>
                            </div>
                            <div class="shrink-0">
                                ${renderStatusPill(r.status)}
                            </div>
                        </div>
                    `;
                }).join('');
            }

            suggestionsBox.classList.remove('hidden');
            lucide.createIcons({ root: suggestionsBox });

            // Pasang event klik pada masing-masing item saran
            const items = suggestionsList.querySelectorAll('.suggestion-item');
            items.forEach(el => {
                el.addEventListener('click', () => {
                    const idx = parseInt(el.getAttribute('data-index'));
                    if (displayMatches[idx]) {
                        selectReport(displayMatches[idx]);
                    }
                });
            });
        }

        function updateHighlight() {
            const items = suggestionsList.querySelectorAll('.suggestion-item');
            items.forEach((el, idx) => {
                const iconBox = el.querySelector('.suggestion-icon-box');
                if (idx === highlightedIndex) {
                    el.classList.add('bg-blue-50/90', 'border-l-4', 'border-l-[#1E3A8A]');
                    if (iconBox) {
                        iconBox.classList.remove('bg-slate-100', 'text-slate-600');
                        iconBox.classList.add('bg-[#1E3A8A]', 'text-white');
                    }
                    el.scrollIntoView({ block: 'nearest' });
                } else {
                    el.classList.remove('bg-blue-50/90', 'border-l-4', 'border-l-[#1E3A8A]');
                    if (iconBox) {
                        iconBox.classList.add('bg-slate-100', 'text-slate-600');
                        iconBox.classList.remove('bg-[#1E3A8A]', 'text-white');
                    }
                }
            });
        }

        function selectReport(item) {
            input.value = item.title ? `${item.public_code} (${item.title})` : item.public_code;
            suggestionsBox.classList.add('hidden');
            highlightedIndex = -1;

            // 1. Gulir halus langsung ke posisi peta
            const mapSection = document.getElementById('mapSectionContainer');
            if (mapSection) {
                mapSection.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
            }

            // 2. Arahkan kamera peta Leaflet ke titik koordinat aduan
            if (map && item.latitude && item.longitude) {
                map.setView([item.latitude, item.longitude], 16, { animate: true });
                setTimeout(() => {
                    if (typeof openLandingDetailModal === 'function') {
                        openLandingDetailModal(item);
                    } else if (typeof openDetailModal === 'function') {
                        openDetailModal(item);
                    }
                }, 450);
            }
        }

        // Live input saat pengguna mengetik huruf demi huruf
        input.addEventListener('input', (e) => {
            doSearch(e.target.value);
        });

        // Tampilkan kembali saran saat input diklik/fokus jika sudah ada teks
        input.addEventListener('focus', () => {
            if (input.value.trim()) {
                doSearch(input.value);
            }
        });

        // Navigasi keyboard (Panah Atas / Bawah, Enter, Escape)
        input.addEventListener('keydown', (e) => {
            const displayMatches = currentMatches.slice(0, 6);
            if (suggestionsBox.classList.contains('hidden') || displayMatches.length === 0) {
                return;
            }

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
                if (highlightedIndex >= 0 && displayMatches[highlightedIndex]) {
                    selectReport(displayMatches[highlightedIndex]);
                } else if (displayMatches.length > 0) {
                    selectReport(displayMatches[0]);
                }
            } else if (e.key === 'Escape') {
                suggestionsBox.classList.add('hidden');
                highlightedIndex = -1;
            }
        });

        // Form submit (Klik tombol Cari Aduan / Tekan Enter)
        form.addEventListener('submit', (e) => {
            e.preventDefault();
            const displayMatches = currentMatches.slice(0, 6);
            if (highlightedIndex >= 0 && displayMatches[highlightedIndex]) {
                selectReport(displayMatches[highlightedIndex]);
            } else if (displayMatches.length > 0) {
                selectReport(displayMatches[0]);
            } else if (input.value.trim()) {
                doSearch(input.value);
            }
        });

        // Tutup dropdown jika pengguna mengeklik di luar kotak pencarian
        document.addEventListener('click', (e) => {
            if (container && !container.contains(e.target)) {
                suggestionsBox.classList.add('hidden');
                highlightedIndex = -1;
            }
        });
    }

    // Slideshow latar belakang Hero + Parallax halus saat di-scroll ke bawah
    (function initHeroSlideshow() {
        const bgWrapper = document.getElementById('landingHeroSlideshow');
        const slides = document.querySelectorAll('#landingHeroSlideshow .landing-hero-slide');
        const reduced = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;

        if (slides.length > 1 && !reduced) {
            window.addEventListener('load', () => {
                setTimeout(() => {
                    slides.forEach(s => {
                        if (s.dataset && s.dataset.src && !s.getAttribute('src')) {
                            s.src = s.dataset.src;
                        }
                    });
                }, 1200);
            }, { once: true });
            let current = 0;
            setInterval(() => {
                slides[current].classList.remove('opacity-100', 'active-slide');
                slides[current].classList.add('opacity-0');
                current = (current + 1) % slides.length;
                if (slides[current].dataset && slides[current].dataset.src && !slides[current].getAttribute('src')) {
                    slides[current].src = slides[current].dataset.src;
                }
                slides[current].classList.remove('opacity-0');
                slides[current].classList.add('opacity-100', 'active-slide');
            }, 5000);
        }

        if (bgWrapper && !reduced) {
            let ticking = false;
            window.addEventListener('scroll', () => {
                if (ticking) return;
                ticking = true;
                window.requestAnimationFrame(() => {
                    const y = Math.min(window.scrollY || 0, 520);
                    bgWrapper.style.transform = `translate3d(0, ${y * 0.16}px, 0)`;
                    ticking = false;
                });
            }, { passive: true });
        }
    })();

    // Transisi Scroll Reveal Modern saat user menggeser halaman ke bawah
    (function initScrollReveal() {
        const items = document.querySelectorAll('[data-reveal], #mapSectionContainer, #download-app');
        if (!items.length) return;
        const reduced = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
        if (reduced || !('IntersectionObserver' in window)) {
            items.forEach(el => {
                el.style.opacity = '1';
                el.style.transform = 'none';
            });
            return;
        }

        const vh = window.innerHeight || 900;
        const observer = new IntersectionObserver((entries, obs) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.style.opacity = '1';
                    entry.target.style.transform = 'translateY(0)';
                    obs.unobserve(entry.target);
                }
            });
        }, { threshold: 0.08, rootMargin: '0px 0px -16px 0px' });

        items.forEach(el => {
            const rect = el.getBoundingClientRect();
            if (rect.top < vh * 0.95) {
                el.style.opacity = '1';
                el.style.transform = 'none';
            } else {
                el.style.opacity = '0';
                el.style.transform = 'translateY(22px)';
                el.style.transitionProperty = 'opacity, transform';
                el.style.transitionDuration = '550ms';
                el.style.transitionTimingFunction = 'cubic-bezier(0.22, 1, 0.36, 1)';
                observer.observe(el);
            }
        });
    })();

    // Tombol Mengambang Unduh APK saat berada di Section #download-app (Selalu di atas Footer)
    (function initFloatingDownloadApk() {
        const section = document.getElementById('download-app');
        const btn = document.getElementById('floatingDownloadApkBtn');
        const footer = document.querySelector('footer');
        if (!section || !btn || !('IntersectionObserver' in window)) return;

        function updateFloatingApkPosition() {
            const baseBottom = 24;
            if (!footer) {
                btn.style.bottom = `${baseBottom}px`;
                return;
            }
            const footerRect = footer.getBoundingClientRect();
            const overlap = window.innerHeight - footerRect.top;
            if (overlap > 0) {
                btn.style.bottom = `${Math.max(baseBottom, overlap + 16)}px`;
            } else {
                btn.style.bottom = `${baseBottom}px`;
            }
        }

        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    updateFloatingApkPosition();
                    btn.classList.remove('opacity-0', 'translate-y-3', 'pointer-events-none');
                    btn.classList.add('opacity-100', 'translate-y-0', 'pointer-events-auto');
                } else {
                    btn.classList.add('opacity-0', 'translate-y-3', 'pointer-events-none');
                    btn.classList.remove('opacity-100', 'translate-y-0', 'pointer-events-auto');
                }
            });
        }, { threshold: 0.1 });

        observer.observe(section);
        window.addEventListener('scroll', updateFloatingApkPosition, { passive: true });
        window.addEventListener('resize', updateFloatingApkPosition, { passive: true });
        updateFloatingApkPosition();
    })();
</script>
@endpush
@endsection
