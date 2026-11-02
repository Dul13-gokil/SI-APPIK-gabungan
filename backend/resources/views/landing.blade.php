@extends('layouts.app')

@section('content')
<!-- Hero Section -->
<section class="w-full bg-[#1E3A8A] text-white py-14 sm:py-20 border-b border-[#172e6e]">
    <div class="max-w-4xl mx-auto px-6 sm:px-10 lg:px-12 text-center flex flex-col items-center justify-center">
        <!-- Badge: Layanan pengaduan terbuka 24 jam -->
        <div class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full bg-white/15 text-blue-100 border border-white/20 text-xs font-semibold mb-6 backdrop-blur-xs">
            <span>Layanan Resmi Pemerintah Kabupaten • Terbuka 24 Jam</span>
        </div>

        <!-- Headline (Centered) -->
        <h1 class="font-outfit font-extrabold text-2xl sm:text-3xl lg:text-5xl text-white tracking-tight leading-tight max-w-3xl mx-auto">
            Pantau kondisi jalan, jembatan, dan fasilitas umum di wilayah Anda
        </h1>

        <!-- Subtitle (Centered) -->
        <p class="text-xs sm:text-sm lg:text-base text-blue-100/90 leading-relaxed mt-4 max-w-2xl mx-auto font-normal">
            Setiap laporan warga tercatat dan ditampilkan secara terbuka di peta. Ketuk sebuah titik untuk melihat foto bukti, status penanganan, dan riwayat perbaikannya.
        </p>

        <!-- Kotak Pencarian / Lacak Laporan Cerdas (Live Autocomplete Dropdown - Standar Google/YouTube) -->
        <div id="searchContainer" class="mt-8 w-full max-w-2xl lg:max-w-3xl mx-auto relative text-left">
            <form id="trackForm" class="flex flex-col sm:flex-row gap-2 bg-white/15 p-1.5 rounded-2xl border border-white/20 backdrop-blur-xs shadow-lg relative z-20">
                <div class="relative flex-1">
                    <i data-lucide="search" class="w-4 h-4 absolute left-3.5 top-3.5 text-blue-200"></i>
                    <input type="text" id="trackCodeInput" autocomplete="off" 
                        placeholder="Cari aduan... Ketik nama kerusakan (misal: aspal), pelapor, atau nomor tiket" 
                        class="w-full bg-transparent border-0 rounded-xl pl-10 pr-4 py-2.5 text-xs sm:text-sm text-white placeholder-blue-200/80 focus:outline-none focus:ring-0">
                </div>
                <button type="submit" class="bg-white hover:bg-blue-50 text-[#1E3A8A] font-bold px-6 py-2.5 rounded-xl text-xs sm:text-sm transition flex items-center justify-center gap-2 shadow-xs shrink-0">
                    <i data-lucide="search" class="w-4 h-4 text-[#1E3A8A]"></i>
                    <span>Cari Aduan</span>
                </button>
            </form>

            <!-- Floating Live Autocomplete Suggestions (Google / YouTube Style) -->
            <div id="searchSuggestions" class="absolute left-0 right-0 top-full mt-2 bg-white rounded-2xl border border-slate-200/90 shadow-2xl overflow-hidden z-50 hidden text-left text-slate-800 transition-all duration-200">
                <div class="px-4 py-2 bg-slate-50 border-b border-slate-100 flex items-center justify-between text-[11px] font-bold text-slate-500 uppercase tracking-wider">
                    <span>Saran Hasil Aduan</span>
                    <span id="searchResultCount" class="text-[#1E3A8A] font-bold lowercase"></span>
                </div>
                <div id="searchSuggestionsList" class="divide-y divide-slate-100 max-h-[360px] overflow-y-auto">
                    <!-- Item hasil pencarian akan disuntikkan secara dinamis via JavaScript -->
                </div>
                <div class="px-4 py-2 bg-slate-50 border-t border-slate-100 flex items-center justify-between text-[10.5px] text-slate-400">
                    <span>Tekan <kbd class="px-1.5 py-0.5 bg-white border border-slate-200 rounded font-mono text-[9.5px]">↑</kbd> <kbd class="px-1.5 py-0.5 bg-white border border-slate-200 rounded font-mono text-[9.5px]">↓</kbd> dan <kbd class="px-1.5 py-0.5 bg-white border border-slate-200 rounded font-mono text-[9.5px]">Enter</kbd> untuk memilih</span>
                    <span><kbd class="px-1.5 py-0.5 bg-white border border-slate-200 rounded font-mono text-[9.5px]">ESC</kbd> untuk menutup</span>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Statistik Ringkasan -->
<div class="max-w-7xl mx-auto px-6 sm:px-10 lg:px-12 -mt-7 sm:-mt-9 space-y-10">
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm grid grid-cols-1 sm:grid-cols-3 divide-y sm:divide-y-0 sm:divide-x divide-slate-100 overflow-hidden">
        <!-- 1. Semua Laporan (Aksen Biru Tua) -->
        <div onclick="filterMapByStatus('ALL')" class="p-5 relative cursor-pointer hover:bg-slate-50 transition group kpi-filter-box" data-status="ALL" title="Ketuk untuk melihat seluruh titik sebaran aduan">
            <div class="absolute top-0 left-0 right-0 h-1.5 bg-[#1E3A8A]"></div>
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold text-slate-600 block group-hover:text-[#1E3A8A] transition">Semua Laporan</span>
                <i data-lucide="layers" class="w-4 h-4 text-slate-400 group-hover:text-[#1E3A8A] transition"></i>
            </div>
            <div class="font-outfit font-extrabold text-2xl sm:text-3xl text-slate-900 mt-2">
                {{ $stats['total'] ?? 0 }}
            </div>
            <span class="text-[11px] text-slate-400 mt-1 block">Akumulasi laporan masuk</span>
        </div>

        <!-- 2. Diproses (Aksen Kuning/Amber - Verifikasi, Disposisi, Fisik, Validasi) -->
        <div onclick="filterMapByStatus('PROCESSING')" class="p-5 relative cursor-pointer hover:bg-slate-50 transition group kpi-filter-box" data-status="PROCESSING" title="Ketuk untuk memfilter aduan yang sedang dalam verifikasi atau penanganan dinas">
            <div class="absolute top-0 left-0 right-0 h-1.5 bg-[#F59E0B]"></div>
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold text-slate-600 block group-hover:text-[#F59E0B] transition">Diproses</span>
                <i data-lucide="clock" class="w-4 h-4 text-slate-400 group-hover:text-[#F59E0B] transition"></i>
            </div>
            <div class="font-outfit font-extrabold text-2xl sm:text-3xl text-slate-900 mt-2">
                {{ ($stats['submitted'] ?? 0) + ($stats['dispatched'] ?? 0) + ($stats['in_progress'] ?? 0) + ($stats['waiting_validation'] ?? 0) }}
            </div>
            <span class="text-[11px] text-slate-400 mt-1 block">Verifikasi, ditugaskan, dikerjakan & validasi</span>
        </div>

        <!-- 3. Selesai (Aksen Hijau Emerald) -->
        <div onclick="filterMapByStatus('COMPLETED')" class="p-5 relative cursor-pointer hover:bg-slate-50 transition group kpi-filter-box" data-status="COMPLETED" title="Ketuk untuk memfilter aduan yang telah selesai diperbaiki">
            <div class="absolute top-0 left-0 right-0 h-1.5 bg-[#10B981]"></div>
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold text-slate-600 block group-hover:text-[#10B981] transition">Selesai</span>
                <i data-lucide="check-circle-2" class="w-4 h-4 text-slate-400 group-hover:text-[#10B981] transition"></i>
            </div>
            <div class="font-outfit font-extrabold text-2xl sm:text-3xl text-slate-900 mt-2">
                {{ $stats['completed'] ?? 0 }}
            </div>
            <span class="text-[11px] text-slate-400 mt-1 block">Tuntas dan tervalidasi 100%</span>
        </div>
    </div>

    <!-- Peta Sebaran Titik Kerusakan -->
    <div class="space-y-4" id="mapSectionContainer">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div>
                <h2 class="font-outfit font-extrabold text-xl sm:text-2xl text-slate-900">
                    Peta sebaran titik kerusakan
                </h2>
                <p class="text-xs sm:text-sm text-slate-500 mt-0.5">
                    Pilih status di atas atau gunakan filter wilayah untuk melihat sebaran titik di Kolaka Utara.
                </p>
            </div>

            <!-- Toolbar Kanan: HANYA 1 BARIS RAPI (Dropdown Kecamatan + Reset + Toggle Titik) -->
            <div class="flex items-center gap-2 shrink-0">
                <!-- Dropdown Filter Kecamatan (Ramping) -->
                <div class="relative">
                    <select id="districtFilterSelect" onchange="onDistrictFilterChange(this.value)" 
                        class="bg-white border border-slate-200 text-xs font-bold text-slate-700 py-2 pl-3 pr-8 rounded-xl shadow-2xs focus:ring-2 focus:ring-[#1E3A8A] focus:border-[#1E3A8A] cursor-pointer">
                        <option value="ALL">Filter Kecamatan (Semua)</option>
                        @foreach($districts as $d)
                            <option value="{{ $d }}">{{ str_starts_with($d, 'Kecamatan ') ? $d : 'Kecamatan ' . $d }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Tombol Reset Filter Persis di Samping Filter -->
                <button type="button" id="resetFilterBtn" onclick="resetAllFilters()" 
                    class="hidden items-center gap-1.5 px-3 py-2 rounded-xl text-xs font-bold bg-rose-50 hover:bg-rose-100 text-rose-700 border border-rose-200 transition shadow-2xs shrink-0" title="Reset filter status & kecamatan">
                    <i data-lucide="rotate-ccw" class="w-3.5 h-3.5"></i>
                    <span>Reset</span>
                </button>

                <!-- Tombol Toggle Floating List (Kontras Tinggi & Selaras Tema) -->
                <!-- Tombol Toggle Floating List (Default Tertutup saat Awal Buka / Refresh) -->
                <button type="button" id="toggleDrawerBtn" onclick="toggleReportDrawer()" 
                    class="flex items-center gap-1.5 px-3.5 py-2 rounded-xl text-xs font-bold bg-white border border-slate-200 text-slate-700 hover:bg-slate-50 transition shadow-2xs shrink-0">
                    <i data-lucide="list" class="w-3.5 h-3.5"></i>
                    <span id="drawerToggleText">Daftar Titik</span>
                    <span id="drawerCountBadge" class="ml-0.5 px-1.5 py-0.5 rounded-full text-[10px] bg-blue-50 text-[#1E3A8A] font-extrabold border border-blue-100">0</span>
                </button>
            </div>
        </div>

        <!-- Full-Width Leaflet Map with Sleek Floating Overlay Card Panel -->
        <div class="bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden relative h-[580px] lg:h-[640px]">
            <!-- Full-Width GIS Map -->
            <div id="gisMap" class="w-full h-full relative z-10"></div>
            
            <div id="mapLoading" class="absolute inset-0 bg-slate-900/10 flex items-center justify-center z-30 pointer-events-none">
                <div class="bg-white px-5 py-2.5 rounded-2xl shadow-md border border-slate-200 flex items-center gap-2 text-xs font-bold text-slate-700">
                    <i data-lucide="loader-2" class="w-4 h-4 animate-spin text-[#1E3A8A]"></i>
                    <span>Memuat titik persebaran laporan...</span>
                </div>
            </div>

            <!-- Floating Legend Pasif di Pojok Kiri Bawah Peta (Standar Google Maps / GIS Modern & Responsif) -->
            <div class="absolute bottom-3 left-3 sm:bottom-4 sm:left-4 z-20 flex flex-wrap items-center gap-2 sm:gap-3 px-2.5 py-1.5 sm:px-3.5 sm:py-2 rounded-2xl bg-white/95 backdrop-blur-md border border-slate-200/90 shadow-lg text-[10px] sm:text-[11px] font-bold text-slate-700 select-none max-w-[90vw]">
                <span class="flex items-center gap-1.5">
                    <span class="w-3.5 h-3.5 rounded-full bg-[#EF4444] shadow-2xs flex items-center justify-center text-white shrink-0">
                        <svg width="8" height="8" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                    </span>
                    Verifikasi
                </span>
                <span class="flex items-center gap-1.5">
                    <span class="w-3.5 h-3.5 rounded-full bg-[#1E3A8A] shadow-2xs flex items-center justify-center text-white shrink-0">
                        <svg width="8" height="8" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg>
                    </span>
                    Ditugaskan
                </span>
                <span class="flex items-center gap-1.5">
                    <span class="w-3.5 h-3.5 rounded-full bg-[#F59E0B] shadow-2xs flex items-center justify-center text-white shrink-0">
                        <svg width="8" height="8" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z"/></svg>
                    </span>
                    Diproses
                </span>
                <span class="flex items-center gap-1.5">
                    <span class="w-3.5 h-3.5 rounded-full bg-[#10B981] shadow-2xs flex items-center justify-center text-white shrink-0">
                        <svg width="8" height="8" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3.5"><polyline points="20 6 9 17 4 12"/></svg>
                    </span>
                    Selesai
                </span>
            </div>

            <!-- Modern Floating Card Panel (Melayang di Atas Peta, Tertutup Default saat Pertama Buka) -->
            <div id="reportDrawer" class="hidden absolute top-4 right-4 z-20 w-[92%] sm:w-[350px] md:w-[370px] max-h-[460px] bg-white/95 backdrop-blur-md rounded-2xl border border-slate-200/90 shadow-2xl flex flex-col transition-all duration-300 overflow-hidden">
                <!-- Header Floating Card -->
                <div class="px-4 py-3 border-b border-slate-100 bg-white/90 flex items-center justify-between shrink-0">
                    <div>
                        <div class="flex items-center gap-1.5">
                            <i data-lucide="list-filter" class="w-4 h-4 text-[#1E3A8A]"></i>
                            <h3 class="font-outfit font-extrabold text-sm text-slate-900">Daftar Titik Aduan</h3>
                        </div>
                        <p id="drawerFilterStatusText" class="text-[10.5px] text-slate-500 mt-0.5 font-medium">
                            Menampilkan semua titik
                        </p>
                    </div>
                    <div class="flex items-center gap-1.5">
                        <span id="drawerTotalText" class="text-[11px] font-bold px-2 py-0.5 bg-blue-50 text-[#1E3A8A] rounded-lg border border-blue-100">
                            0 Aduan
                        </span>
                        <button type="button" onclick="toggleReportDrawer()" class="p-1 rounded-lg hover:bg-slate-100 text-slate-400 hover:text-slate-600 transition" title="Tutup Panel">
                            <i data-lucide="x" class="w-4 h-4"></i>
                        </button>
                    </div>
                </div>

                <!-- List Content (Auto-Height, max-h-[380px], Scrollable jika banyak) -->
                <div id="drawerListContainer" class="p-2.5 space-y-2 overflow-y-auto max-h-[380px] overscroll-contain" style="-webkit-overflow-scrolling: touch;">
                    <!-- Kartu-kartu aduan di-generate dinamis oleh JS -->
                </div>

                <!-- Mini Footer Hint -->
                <div class="px-3.5 py-2 border-t border-slate-100 bg-slate-50/90 text-[10px] text-slate-500 flex items-center justify-between shrink-0">
                    <span class="flex items-center gap-1 truncate">
                        <i data-lucide="mouse-pointer-click" class="w-3 h-3 text-[#1E3A8A] shrink-0"></i>
                        <span class="truncate">Klik aduan untuk meluncur halus ke titik</span>
                    </span>
                </div>
            </div>
        </div>
    </div>

    <!-- Banner Unduh Aplikasi Mobile -->
    <div id="download-app" class="rounded-3xl p-8 sm:p-12 bg-gradient-to-br from-[#172554] via-[#1E3A8A] to-[#1E40AF] text-white border border-blue-400/20 shadow-lg relative overflow-hidden">
        <div class="relative z-10 flex flex-col xl:flex-row items-center justify-between gap-8 text-center xl:text-left">
            <div class="max-w-xl space-y-3">
                <div class="inline-flex items-center gap-2 px-3.5 py-1 rounded-full bg-white/15 text-blue-100 border border-white/20 text-xs font-semibold">
                    <i data-lucide="smartphone" class="w-3.5 h-3.5"></i>
                    <span>Aplikasi Mobile Resmi Warga & Petugas</span>
                </div>
                <h3 class="font-outfit font-extrabold text-2xl sm:text-3xl lg:text-4xl text-white tracking-tight">
                    Laporkan Kerusakan Fasilitas Langsung dari Ponsel
                </h3>
                <p class="text-xs sm:text-sm text-blue-100/90 leading-relaxed max-w-xl">
                    Ajukan pengaduan jalan rusak, jembatan, dan fasilitas umum di Kabupaten Kolaka Utara dengan bukti foto AI, koordinat GPS presisi, serta transparansi penanganan oleh Dinas PUPR.
                </p>
            </div>

            <!-- Tombol Unduh Aplikasi -->
            <div class="flex flex-col sm:flex-row items-center justify-center xl:justify-end gap-3 shrink-0">
                <!-- Google Play Button -->
                <a href="https://play.google.com" target="_blank" rel="noopener noreferrer" 
                    class="flex items-center gap-3 bg-black/80 hover:bg-black text-white px-5 py-3 rounded-2xl border border-white/20 transition shadow-sm w-full sm:w-48 justify-center">
                    <svg class="w-6 h-6 fill-current shrink-0" viewBox="0 0 24 24">
                        <path d="M3,20.5V3.5C3,2.91 3.34,2.39 3.84,2.15L13.69,12L3.84,21.85C3.34,21.6 3,21.09 3,20.5M16.81,15.12L6.05,21.34L14.54,12.85L16.81,15.12M20.16,10.81C20.5,11.08 20.75,11.5 20.75,12C20.75,12.5 20.5,12.92 20.16,13.19L17.89,14.5L15.39,12L17.89,9.5L20.16,10.81M6.05,2.66L16.81,8.88L14.54,11.15L6.05,2.66Z" />
                    </svg>
                    <div class="text-left leading-tight">
                        <span class="text-[9px] uppercase tracking-wider text-slate-300 block font-medium">TERSEDIA DI</span>
                        <span class="font-outfit font-bold text-sm text-white block">Google Play</span>
                    </div>
                </a>

                <!-- App Store Button (Khusus iOS) -->
                <a href="https://apps.apple.com" target="_blank" rel="noopener noreferrer" 
                    class="flex items-center gap-3 bg-black/80 hover:bg-black text-white px-5 py-3 rounded-2xl border border-white/20 transition shadow-sm w-full sm:w-48 justify-center">
                    <svg class="w-6 h-6 fill-current shrink-0" viewBox="0 0 24 24">
                        <path d="M18.71 19.5c-.83 1.24-1.71 2.45-3.05 2.47-1.34.03-1.77-.79-3.29-.79-1.53 0-2 .77-3.27.82-1.31.05-2.3-1.32-3.14-2.53C4.25 17 2.94 12.45 4.7 9.39c.87-1.52 2.43-2.48 4.12-2.51 1.28-.02 2.5.87 3.29.87.78 0 2.26-1.07 3.81-.91.65.03 2.47.26 3.64 1.98-.09.06-2.17 1.28-2.15 3.81.03 3.02 2.65 4.03 2.68 4.04-.03.07-.42 1.44-1.38 2.83M15.97 6.37c.62-.75 1.04-1.8 1.01-2.87-.96.04-2.12.64-2.8 1.44-.59.68-1.11 1.77-1 2.82 1.07.08 2.17-.55 2.79-1.39z"/>
                    </svg>
                    <div class="text-left leading-tight">
                        <span class="text-[9px] uppercase tracking-wider text-slate-300 block font-medium">TERSEDIA DI</span>
                        <span class="font-outfit font-bold text-sm text-white block">App Store</span>
                    </div>
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Modal Detail Pengaduan Publik -->
@include('partials.modals.landing_complaint_detail_modal')



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

        // Inisialisasi Peta GIS Kabupaten (Kabupaten Kolaka Utara)
        map = L.map('gisMap', {
            zoomControl: false,
            scrollWheelZoom: true,
        }).setView([-3.50601, 120.88780], 12);

        // Posisikan kontrol zoom di pojok kiri atas agar tidak tertutup floating card di kanan atas
        L.control.zoom({ position: 'topleft' }).addTo(map);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '© OpenStreetMap kontributor | Sistem Pengaduan Daerah'
        }).addTo(map);

        markersLayer = L.layerGroup().addTo(map);

        // Batas Administratif Resmi Wilayah Kabupaten Kolaka Utara
        fetch('{{ asset("geojson/kolaka_utara.geojson") }}')
            .then(res => res.json())
            .then(geoJsonData => {
                L.geoJSON(geoJsonData, {
                    interactive: false,
                    style: {
                        color: "#1E3A8A",
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
                    applyFilters(false);
                }
            }
        } catch (err) {
            console.error('Gagal memuat data titik peta:', err);
        } finally {
            hideMapLoading();
        }
    }

    function toggleReportDrawer() {
        isDrawerOpen = !isDrawerOpen;
        const drawer = document.getElementById('reportDrawer');
        const toggleBtn = document.getElementById('toggleDrawerBtn');
        const badge = document.getElementById('drawerCountBadge');

        if (isDrawerOpen) {
            drawer.classList.remove('hidden');
            if (toggleBtn) {
                toggleBtn.className = "flex items-center gap-1.5 px-3.5 py-2 rounded-xl text-xs font-bold bg-[#1E3A8A] text-white hover:bg-[#172e6e] transition shadow-2xs shrink-0";
            }
            if (badge) {
                badge.className = "ml-0.5 px-1.5 py-0.5 rounded-full text-[10px] bg-white/20 text-white font-extrabold";
            }
        } else {
            drawer.classList.add('hidden');
            if (toggleBtn) {
                toggleBtn.className = "flex items-center gap-1.5 px-3.5 py-2 rounded-xl text-xs font-bold bg-white border border-slate-200 text-slate-700 hover:bg-slate-50 transition shadow-2xs shrink-0";
            }
            if (badge) {
                badge.className = "ml-0.5 px-1.5 py-0.5 rounded-full text-[10px] bg-blue-50 text-[#1E3A8A] font-extrabold border border-blue-100";
            }
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
        if (!rawAddress) return 'Kolaka Utara';
        const parts = rawAddress.split(',').map(s => s.trim());
        for (const p of parts) {
            if (p.toLowerCase().startsWith('kec.') || p.toLowerCase().startsWith('kecamatan')) {
                return p.replace(/^kecamatan\s*/i, 'Kec. ');
            }
        }
        return 'Kolaka Utara';
    }

    function showMapToast(message) {
        let toast = document.getElementById('mapToast');
        if (!toast) {
            toast = document.createElement('div');
            toast.id = 'mapToast';
            toast.className = 'fixed top-6 left-1/2 -translate-x-1/2 z-[9999] px-4 py-2.5 rounded-2xl bg-slate-900/95 text-white text-xs font-bold shadow-2xl backdrop-blur-md flex items-center gap-2.5 border border-slate-700/80 transition-all duration-300 opacity-0 pointer-events-none transform -translate-y-2';
            document.body.appendChild(toast);
        }
        toast.innerHTML = `
            <svg class="w-4 h-4 text-amber-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <span>${message}</span>
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
        // Update status aktif KPI box
        document.querySelectorAll('.kpi-filter-box').forEach(box => {
            if (box.getAttribute('data-status') === currentStatusFilter) {
                box.classList.add('bg-slate-50/90');
            } else {
                box.classList.remove('bg-slate-50/90');
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
            if (drawerFilterStatusText) drawerFilterStatusText.innerText = 'Menampilkan semua titik di Kolaka Utara';
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
            const shortLocation = adm.village && adm.village !== 'Desa Wilayah' ? adm.village : (adm.road && adm.road !== 'Jalan / Area Kerusakan' ? adm.road : 'Kab. Kolaka Utara');

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
                pinColor = '#F59E0B'; // Validasi (Kuning / Amber)
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
            return text.replace(regex, '<mark class="bg-amber-100 text-amber-900 font-extrabold px-0.5 rounded">$1</mark>');
        }

        function renderStatusPill(status) {
            switch (status) {
                case 'SUBMITTED':
                case 'PENDING':
                case 'VERIFIED':
                case 'VERIFICATION':
                    return '<span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-50 text-rose-600 border border-rose-200 shrink-0">Verifikasi</span>';
                case 'DISPATCHED':
                    return '<span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-blue-50 text-[#1E3A8A] border border-blue-200 shrink-0">Ditugaskan</span>';
                case 'IN_PROGRESS':
                case 'REVISION_REQUIRED':
                case 'WAITING_VERIFICATION':
                    return '<span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-50 text-amber-700 border border-amber-200 shrink-0">Validasi</span>';
                case 'COMPLETED':
                case 'RESOLVED':
                case 'CLOSED':
                    return '<span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200 shrink-0">Selesai</span>';
                default:
                    return '<span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 text-slate-600 shrink-0">Verifikasi</span>';
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
                        <div class="w-10 h-10 rounded-full bg-slate-100 text-slate-400 mx-auto flex items-center justify-center mb-2">
                            <i data-lucide="search-x" class="w-5 h-5"></i>
                        </div>
                        <p class="text-xs font-bold text-slate-800">Tidak ada aduan ditemukan untuk "${query}"</p>
                        <p class="text-[11px] text-slate-400 mt-1 max-w-sm mx-auto">
                            Coba ketik kata kunci nama kerusakan (contoh: <strong>aspal</strong>, <strong>jembatan</strong>), nama pelapor, atau nomor tiket.
                        </p>
                    </div>
                `;
            } else {
                countSpan.innerText = `${currentMatches.length} aduan ditemukan`;
                suggestionsList.innerHTML = displayMatches.map((r, idx) => {
                    const addressSnippet = r.address ? r.address.split(',')[0] : 'Kab. Kolaka Utara';
                    return `
                        <div class="suggestion-item p-3.5 hover:bg-blue-50/80 transition flex items-center justify-between gap-3 cursor-pointer group" data-index="${idx}">
                            <div class="flex items-start gap-3 min-w-0">
                                <div class="suggestion-icon-box w-8 h-8 rounded-xl bg-slate-100 group-hover:bg-[#1E3A8A] group-hover:text-white text-slate-600 flex items-center justify-center shrink-0 mt-0.5 transition shadow-2xs">
                                    <i data-lucide="map-pin" class="w-4 h-4"></i>
                                </div>
                                <div class="min-w-0">
                                    <div class="text-[13px] font-bold text-slate-900 group-hover:text-[#1E3A8A] transition truncate">
                                        ${highlightMatch(r.title, query)}
                                    </div>
                                    <div class="text-[11px] text-slate-500 flex flex-wrap items-center gap-1.5 mt-0.5">
                                        <span class="font-bold text-slate-700 bg-slate-100 px-1.5 py-0.5 rounded text-[10px] tracking-wide font-mono">
                                            ${highlightMatch(r.public_code, query)}
                                        </span>
                                        <span>•</span>
                                        <span class="truncate">Pelapor: <strong class="text-slate-700 font-medium">${highlightMatch(r.reporter_name || 'Warga', query)}</strong></span>
                                        <span>•</span>
                                        <span class="text-slate-400 truncate">${addressSnippet}</span>
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
</script>
@endpush
@endsection
