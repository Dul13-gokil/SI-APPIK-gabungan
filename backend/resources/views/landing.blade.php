@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 sm:py-8 space-y-6">

    <!-- Ringkasan Layanan Publik (Tanpa Dekorasi Berlebihan) -->
    <section class="space-y-1.5">
        <h1 class="font-outfit font-extrabold text-2xl sm:text-3xl text-[#0F172A] tracking-tight leading-snug">
            Pantau perbaikan jalan, jembatan, dan fasilitas umum di Kolaka Utara
        </h1>
        <p class="text-sm sm:text-base text-[#475569] max-w-3xl leading-relaxed">
            Pilih kecamatan atau ketuk salah satu titik di peta untuk melihat foto kondisi dari lokasi dan perkembangan penanganannya oleh Dinas PUPR.
        </p>
    </section>

    <!-- 1. Stat Bar: 3 Kotak Statistik Utama Satu Baris -->
    <section aria-label="Ringkasan jumlah laporan" class="grid grid-cols-1 sm:grid-cols-3 gap-3 sm:gap-4">
        <button type="button" onclick="filterMapByStatus('ALL')" data-status="ALL" aria-pressed="true"
            class="kpi-filter-box text-left bg-white rounded-xl border border-[#E2E8F0] border-t-4 border-t-[#1E3A8A] p-4 sm:p-5 hover:bg-slate-50 transition cursor-pointer min-h-[44px]">
            <div class="flex items-center justify-between gap-2">
                <span class="text-xs sm:text-sm font-semibold text-[#475569]">Total Laporan</span>
                <i data-lucide="layers" class="w-4 h-4 text-[#1E3A8A] shrink-0"></i>
            </div>
            <div class="font-outfit font-extrabold text-2xl sm:text-3xl text-[#0F172A] mt-1.5">
                {{ $stats['total'] ?? 0 }}
            </div>
            <span class="text-xs text-[#475569] mt-1 block">Semua laporan warga yang tercatat</span>
        </button>

        <button type="button" onclick="filterMapByStatus('PROCESSING')" data-status="PROCESSING" aria-pressed="false"
            class="kpi-filter-box text-left bg-white rounded-xl border border-[#E2E8F0] border-t-4 border-t-[#D97706] p-4 sm:p-5 hover:bg-slate-50 transition cursor-pointer min-h-[44px]">
            <div class="flex items-center justify-between gap-2">
                <span class="text-xs sm:text-sm font-semibold text-[#475569]">Sedang Dikerjakan</span>
                <i data-lucide="wrench" class="w-4 h-4 text-[#D97706] shrink-0"></i>
            </div>
            <div class="font-outfit font-extrabold text-2xl sm:text-3xl text-[#0F172A] mt-1.5">
                {{ ($stats['submitted'] ?? 0) + ($stats['dispatched'] ?? 0) + ($stats['in_progress'] ?? 0) + ($stats['waiting_validation'] ?? 0) }}
            </div>
            <span class="text-xs text-[#475569] mt-1 block">Diperiksa dan ditangani di lapangan</span>
        </button>

        <button type="button" onclick="filterMapByStatus('COMPLETED')" data-status="COMPLETED" aria-pressed="false"
            class="kpi-filter-box text-left bg-white rounded-xl border border-[#E2E8F0] border-t-4 border-t-[#15803D] p-4 sm:p-5 hover:bg-slate-50 transition cursor-pointer min-h-[44px]">
            <div class="flex items-center justify-between gap-2">
                <span class="text-xs sm:text-sm font-semibold text-[#475569]">Selesai</span>
                <i data-lucide="check-circle-2" class="w-4 h-4 text-[#15803D] shrink-0"></i>
            </div>
            <div class="font-outfit font-extrabold text-2xl sm:text-3xl text-[#0F172A] mt-1.5">
                {{ $stats['completed'] ?? 0 }}
            </div>
            <span class="text-xs text-[#475569] mt-1 block">Sudah tuntas diperbaiki</span>
        </button>
    </section>

    <!-- 2. Split View: Filter & Daftar Aduan (Kiri di Desktop, Pencarian di Atas Peta di Mobile) + Peta (Kanan) -->
    <section id="mapSectionContainer" class="flex flex-col lg:grid lg:grid-cols-12 gap-4 lg:gap-5 items-stretch">

        <!-- Blok A: Pencarian & Filter Kecamatan (Order 1 di Mobile, Kiri Atas di Desktop) -->
        <div class="order-1 lg:col-span-5 xl:col-span-4 lg:col-start-1 lg:row-start-1 bg-white rounded-xl border border-[#E2E8F0] p-4 space-y-3">
            <div id="searchContainer" class="relative">
                <label for="trackCodeInput" class="block text-xs font-bold text-[#0F172A] mb-1.5">
                    Cari Lokasi atau Nomor Laporan
                </label>
                <form id="trackForm" class="flex gap-2">
                    <div class="relative flex-1 min-w-0">
                        <i data-lucide="search" class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-[#475569] pointer-events-none"></i>
                        <input type="text" id="trackCodeInput" autocomplete="off"
                            placeholder="Ketik nama jalan, desa, atau kode..."
                            class="w-full min-h-[44px] bg-[#F8FAFC] border border-[#CBD5E1] rounded-lg pl-9 pr-3 py-2 text-sm text-[#0F172A] placeholder-[#64748B] focus:bg-white focus:border-[#1E3A8A] focus:outline-none">
                    </div>
                    <button type="submit" aria-label="Cari aduan"
                        class="min-h-[44px] px-4 py-2 rounded-lg bg-[#1E3A8A] hover:bg-[#172554] text-white text-sm font-semibold transition shrink-0 cursor-pointer">
                        Cari
                    </button>
                </form>

                <!-- Autocomplete Hasil Pencarian -->
                <div id="searchSuggestions" class="absolute left-0 right-0 top-full mt-1.5 bg-white rounded-xl border border-[#CBD5E1] shadow-lg overflow-hidden z-50 hidden">
                    <div class="px-3.5 py-2 bg-[#F8FAFC] border-b border-[#E2E8F0] flex items-center justify-between text-xs font-semibold text-[#475569]">
                        <span>Hasil Pencarian</span>
                        <span id="searchResultCount" class="text-[#1E3A8A]"></span>
                    </div>
                    <div id="searchSuggestionsList" class="divide-y divide-[#E2E8F0] max-h-[300px] overflow-y-auto"></div>
                </div>
            </div>

            <!-- Filter Wilayah Kecamatan -->
            <div class="flex items-end gap-2">
                <div class="flex-1 min-w-0">
                    <label for="districtFilterSelect" class="block text-xs font-bold text-[#0F172A] mb-1.5">
                        Pilih Kecamatan
                    </label>
                    <select id="districtFilterSelect" onchange="onDistrictFilterChange(this.value)"
                        class="w-full min-h-[44px] bg-[#F8FAFC] border border-[#CBD5E1] text-sm font-medium text-[#0F172A] py-2 px-3 rounded-lg focus:bg-white focus:border-[#1E3A8A] cursor-pointer">
                        <option value="ALL">Semua Kecamatan di Kolaka Utara</option>
                        @foreach($districts as $d)
                            <option value="{{ $d }}">{{ str_starts_with($d, 'Kecamatan ') ? $d : 'Kecamatan ' . $d }}</option>
                        @endforeach
                    </select>
                </div>

                <button type="button" id="resetFilterBtn" onclick="resetAllFilters()"
                    class="hidden min-h-[44px] px-3.5 py-2 rounded-lg text-xs font-bold bg-[#FEE2E2] hover:bg-red-200 text-[#DC2626] border border-[#FECACA] transition items-center gap-1.5 shrink-0 cursor-pointer">
                    <i data-lucide="rotate-ccw" class="w-3.5 h-3.5"></i>
                    <span>Reset</span>
                </button>
            </div>
        </div>

        <!-- Blok B: Peta Interaktif Leaflet (Order 2 di Mobile, Sisi Kanan Penuh di Desktop) -->
        <div class="order-2 lg:col-span-7 xl:col-span-8 lg:col-start-6 xl:col-start-5 lg:row-start-1 lg:row-span-2 flex flex-col bg-white rounded-xl border border-[#E2E8F0] overflow-hidden">
            <div class="px-4 py-3 border-b border-[#E2E8F0] bg-[#F8FAFC] flex flex-wrap items-center justify-between gap-2">
                <h2 class="font-outfit font-bold text-base text-[#0F172A]">
                    Peta Persebaran Kondisi Infrastruktur
                </h2>
                <!-- Legenda Status Fungsional -->
                <div class="flex flex-wrap items-center gap-3 sm:gap-4 text-xs font-semibold text-[#0F172A]">
                    <span class="inline-flex items-center gap-1.5">
                        <span class="w-3 h-3 rounded-full bg-[#DC2626] inline-block shrink-0" aria-hidden="true"></span>
                        Masuk / Verifikasi
                    </span>
                    <span class="inline-flex items-center gap-1.5">
                        <span class="w-3 h-3 rounded-full bg-[#D97706] inline-block shrink-0" aria-hidden="true"></span>
                        Diproses / Ditugaskan
                    </span>
                    <span class="inline-flex items-center gap-1.5">
                        <span class="w-3 h-3 rounded-full bg-[#15803D] inline-block shrink-0" aria-hidden="true"></span>
                        Selesai
                    </span>
                </div>
            </div>

            <div class="relative flex-1 min-h-[380px] sm:min-h-[460px] lg:min-h-[580px]">
                <div id="gisMap" class="w-full h-full relative z-10"></div>

                <div id="mapLoading" class="absolute inset-0 bg-slate-900/10 flex items-center justify-center z-30 pointer-events-none">
                    <div class="bg-white px-4 py-2.5 rounded-lg border border-[#E2E8F0] flex items-center gap-2 text-xs font-bold text-[#0F172A]">
                        <i data-lucide="loader-2" class="w-4 h-4 animate-spin text-[#1E3A8A]"></i>
                        <span>Memuat titik laporan di peta...</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Blok C: Daftar Titik Aduan (Order 3 di Mobile, Kiri Bawah di Desktop) -->
        <div class="order-3 lg:col-span-5 xl:col-span-4 lg:col-start-1 lg:row-start-2 bg-white rounded-xl border border-[#E2E8F0] flex flex-col overflow-hidden">
            <div class="px-4 py-3 border-b border-[#E2E8F0] bg-[#F8FAFC] flex items-center justify-between gap-2">
                <div class="min-w-0">
                    <h3 class="font-outfit font-bold text-sm sm:text-base text-[#0F172A]">Daftar Titik Aduan</h3>
                    <p id="drawerFilterStatusText" class="text-xs text-[#475569] truncate">
                        Menampilkan semua wilayah Kolaka Utara
                    </p>
                </div>
                <span id="drawerTotalText" class="text-xs font-bold px-2.5 py-1 bg-white text-[#1E3A8A] rounded-md border border-[#E2E8F0] shrink-0">
                    0 Aduan
                </span>
            </div>

            <div id="drawerListContainer" class="p-3 space-y-2.5 overflow-y-auto max-h-[420px] lg:max-h-[430px] overscroll-contain flex-1">
                <!-- Daftar aduan diisi dinamis -->
            </div>

            <div class="px-4 py-2.5 border-t border-[#E2E8F0] bg-[#F8FAFC] text-xs text-[#475569]">
                Pilih salah satu laporan di atas untuk melihat lokasinya di peta.
            </div>
        </div>

    </section>

    <!-- 3. Bagian Unduh Aplikasi Lapor Warga (Flat & Bordered, Tanpa Gradasi AI Slop) -->
    <section id="download-app" class="bg-white rounded-xl border border-[#E2E8F0] border-l-4 border-l-[#D97706] p-6 sm:p-8">
        <div class="flex flex-col lg:flex-row items-start lg:items-center justify-between gap-6">
            <div class="max-w-2xl space-y-2">
                <span class="inline-block text-xs font-bold uppercase tracking-wider text-[#B45309]">
                    Aplikasi Warga &amp; Petugas Lapangan
                </span>
                <h2 class="font-outfit font-extrabold text-xl sm:text-2xl text-[#0F172A]">
                    Laporkan kerusakan jalan langsung dari HP Anda
                </h2>
                <p class="text-sm text-[#475569] leading-relaxed">
                    Ambil foto kondisi jalan langsung dari lokasi, tandai titik lokasi otomatis dari HP Anda, dan pantau penanganannya oleh regu Dinas PUPR Kabupaten Kolaka Utara hingga tuntas.
                </p>
            </div>

            <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3 w-full lg:w-auto shrink-0">
                <a href="https://play.google.com" target="_blank" rel="noopener noreferrer"
                    class="min-h-[48px] flex items-center justify-center gap-3 bg-[#0F172A] hover:bg-[#1E3A8A] text-white px-5 py-2.5 rounded-lg transition w-full sm:w-44">
                    <svg class="w-5 h-5 fill-current shrink-0" viewBox="0 0 24 24" aria-hidden="true">
                        <path d="M3,20.5V3.5C3,2.91 3.34,2.39 3.84,2.15L13.69,12L3.84,21.85C3.34,21.6 3,21.09 3,20.5M16.81,15.12L6.05,21.34L14.54,12.85L16.81,15.12M20.16,10.81C20.5,11.08 20.75,11.5 20.75,12C20.75,12.5 20.5,12.92 20.16,13.19L17.89,14.5L15.39,12L17.89,9.5L20.16,10.81M6.05,2.66L16.81,8.88L14.54,11.15L6.05,2.66Z" />
                    </svg>
                    <div class="text-left leading-tight">
                        <span class="text-[10px] uppercase tracking-wider text-slate-300 block">Unduh di</span>
                        <span class="font-outfit font-bold text-sm text-white block">Google Play</span>
                    </div>
                </a>

                <a href="https://apps.apple.com" target="_blank" rel="noopener noreferrer"
                    class="min-h-[48px] flex items-center justify-center gap-3 bg-white hover:bg-[#F8FAFC] text-[#0F172A] border border-[#CBD5E1] px-5 py-2.5 rounded-lg transition w-full sm:w-44">
                    <svg class="w-5 h-5 fill-current shrink-0" viewBox="0 0 24 24" aria-hidden="true">
                        <path d="M18.71 19.5c-.83 1.24-1.71 2.45-3.05 2.47-1.34.03-1.77-.79-3.29-.79-1.53 0-2 .77-3.27.82-1.31.05-2.3-1.32-3.14-2.53C4.25 17 2.94 12.45 4.7 9.39c.87-1.52 2.43-2.48 4.12-2.51 1.28-.02 2.5.87 3.29.87.78 0 2.26-1.07 3.81-.91.65.03 2.47.26 3.64 1.98-.09.06-2.17 1.28-2.15 3.81.03 3.02 2.65 4.03 2.68 4.04-.03.07-.42 1.44-1.38 2.83M15.97 6.37c.62-.75 1.04-1.8 1.01-2.87-.96.04-2.12.64-2.8 1.44-.59.68-1.11 1.77-1 2.82 1.07.08 2.17-.55 2.79-1.39z"/>
                    </svg>
                    <div class="text-left leading-tight">
                        <span class="text-[10px] uppercase tracking-wider text-[#475569] block">Unduh di</span>
                        <span class="font-outfit font-bold text-sm text-[#0F172A] block">App Store</span>
                    </div>
                </a>
            </div>
        </div>
    </section>

</div>

<!-- Modal Rincian Pengaduan Publik -->
@include('partials.modals.landing_complaint_detail_modal')

@push('scripts')
<style>
    .custom-map-tooltip {
        background-color: #0F172A !important;
        color: #FFFFFF !important;
        font-weight: 600 !important;
        font-size: 12px !important;
        border-radius: 6px !important;
        padding: 5px 10px !important;
        border: 1px solid #334155 !important;
    }
    .custom-map-tooltip::before {
        border-top-color: #0F172A !important;
    }
</style>

<script>
    const STATUS_META = {
        SUBMITTED:            { label: 'Masuk / Verifikasi', color: '#DC2626', badge: 'bg-[#FEE2E2] text-[#DC2626] border-[#FECACA]', icon: '<circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/>' },
        VERIFICATION:         { label: 'Masuk / Verifikasi', color: '#DC2626', badge: 'bg-[#FEE2E2] text-[#DC2626] border-[#FECACA]', icon: '<circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/>' },
        DISPATCHED:           { label: 'Ditugaskan',         color: '#D97706', badge: 'bg-[#FEF3C7] text-[#92400E] border-[#FDE68A]', icon: '<circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/>' },
        IN_PROGRESS:          { label: 'Sedang Dikerjakan',  color: '#D97706', badge: 'bg-[#FEF3C7] text-[#92400E] border-[#FDE68A]', icon: '<path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z"/>' },
        WAITING_VERIFICATION: { label: 'Pemeriksaan Mutu',   color: '#D97706', badge: 'bg-[#FEF3C7] text-[#92400E] border-[#FDE68A]', icon: '<circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/>' },
        REVISION_REQUIRED:    { label: 'Perbaikan Lanjutan', color: '#D97706', badge: 'bg-[#FEF3C7] text-[#92400E] border-[#FDE68A]', icon: '<path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z"/>' },
        COMPLETED:            { label: 'Selesai',            color: '#15803D', badge: 'bg-[#DCFCE7] text-[#15803D] border-[#BBF7D0]', icon: '<polyline points="20 6 9 17 4 12"/>' },
        CLOSED:               { label: 'Selesai',            color: '#15803D', badge: 'bg-[#DCFCE7] text-[#15803D] border-[#BBF7D0]', icon: '<polyline points="20 6 9 17 4 12"/>' }
    };

    const PROCESSING_STATUSES = ['SUBMITTED', 'VERIFICATION', 'DISPATCHED', 'IN_PROGRESS', 'WAITING_VERIFICATION', 'REVISION_REQUIRED'];

    let map;
    let markersLayer;
    let reportsData = @json($mapPoints ?? []);
    let currentStatusFilter = 'ALL';
    let currentDistrictFilter = 'ALL';

    function getStatusMeta(status) {
        return STATUS_META[status] || STATUS_META.SUBMITTED;
    }

    function escapeHtml(str) {
        return String(str || '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    }

    function hideMapLoading() {
        const el = document.getElementById('mapLoading');
        if (el) el.classList.add('hidden');
    }

    document.addEventListener('DOMContentLoaded', () => {
        initMap();
        if (reportsData && reportsData.length > 0) {
            applyFilters(false);
            hideMapLoading();
        } else {
            loadMapData();
        }
        setupSearch();
        setTimeout(() => {
            hideMapLoading();
            if (map) map.invalidateSize();
        }, 500);
    });

    function initMap() {
        const mapContainer = document.getElementById('gisMap');
        if (!mapContainer || mapContainer._leaflet_id) return;

        map = L.map('gisMap', { zoomControl: true, scrollWheelZoom: true }).setView([-3.50601, 120.88780], 11);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '© OpenStreetMap • SI-APPIK Kolaka Utara'
        }).addTo(map);

        markersLayer = L.layerGroup().addTo(map);

        fetch('{{ asset("geojson/kolaka_utara.geojson") }}')
            .then(res => res.json())
            .then(geoJsonData => {
                L.geoJSON(geoJsonData, {
                    interactive: false,
                    style: { color: '#1E3A8A', weight: 2, dashArray: '6, 6', fillColor: '#1E3A8A', fillOpacity: 0.04 }
                }).addTo(map);
            })
            .catch(err => console.warn('GeoJSON wilayah tidak dimuat:', err));

        setTimeout(() => { if (map) map.invalidateSize(); }, 250);
    }

    async function loadMapData() {
        try {
            const resp = await fetch('{{ url("/api/v1/dashboard/map") }}');
            if (resp.ok) {
                const json = await resp.json();
                if (json && Array.isArray(json.data)) {
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

    function onDistrictFilterChange(district) {
        currentDistrictFilter = district;
        applyFilters(true);
    }

    function filterMapByStatus(status) {
        currentStatusFilter = status;
        applyFilters(true);
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
        const match = rawAddress.split(',').map(s => s.trim()).find(p => /^(kec\.|kecamatan)/i.test(p));
        return match ? match.replace(/^kecamatan\s*/i, 'Kec. ') : 'Kolaka Utara';
    }

    function parseAdministrativeAddress(rawAddress) {
        let road = 'Lokasi Jalan';
        let village = '';
        if (!rawAddress) return { road, village };

        rawAddress.split(',').map(s => s.trim()).forEach(p => {
            if (/^(desa|kel\.)/i.test(p)) village = p;
            else if (!/^(kec\.|kecamatan|kab\.|kota|titik koordinat)/i.test(p)) road = p;
        });
        return { road, village };
    }

    function showMapToast(message) {
        let toast = document.getElementById('mapToast');
        if (!toast) {
            toast = document.createElement('div');
            toast.id = 'mapToast';
            toast.className = 'fixed top-5 left-1/2 -translate-x-1/2 z-[9999] px-4 py-2.5 rounded-lg bg-[#0F172A] text-white text-xs font-semibold border border-slate-700 transition-opacity duration-200 opacity-0 pointer-events-none';
            document.body.appendChild(toast);
        }
        toast.textContent = message;
        toast.classList.remove('opacity-0');
        toast.classList.add('opacity-100');

        clearTimeout(window._mapToastTimer);
        window._mapToastTimer = setTimeout(() => {
            toast.classList.remove('opacity-100');
            toast.classList.add('opacity-0');
        }, 3000);
    }

    function applyFilters(shouldZoom = true) {
        document.querySelectorAll('.kpi-filter-box').forEach(box => {
            const isActive = box.getAttribute('data-status') === currentStatusFilter;
            box.setAttribute('aria-pressed', isActive ? 'true' : 'false');
            box.classList.toggle('ring-2', isActive);
            box.classList.toggle('ring-[#1E3A8A]', isActive);
            box.classList.toggle('bg-slate-50', isActive);
        });

        const resetBtn = document.getElementById('resetFilterBtn');
        if (resetBtn) {
            const hasFilter = currentStatusFilter !== 'ALL' || currentDistrictFilter !== 'ALL';
            resetBtn.classList.toggle('hidden', !hasFilter);
            resetBtn.classList.toggle('inline-flex', hasFilter);
        }

        const statusLabels = { PROCESSING: 'Sedang Dikerjakan', COMPLETED: 'Selesai' };
        const summaryParts = [];
        if (statusLabels[currentStatusFilter]) summaryParts.push(statusLabels[currentStatusFilter]);
        if (currentDistrictFilter !== 'ALL') summaryParts.push(currentDistrictFilter.replace(/^Kecamatan\s*/i, 'Kec. '));

        const filterTextEl = document.getElementById('drawerFilterStatusText');
        if (filterTextEl) {
            filterTextEl.innerText = summaryParts.length > 0
                ? `Filter: ${summaryParts.join(' • ')}`
                : 'Menampilkan semua wilayah Kolaka Utara';
        }

        let filtered = reportsData.filter(r => r.status !== 'REJECTED');

        if (currentStatusFilter === 'PROCESSING') {
            filtered = filtered.filter(r => PROCESSING_STATUSES.includes(r.status));
        } else if (currentStatusFilter === 'COMPLETED') {
            filtered = filtered.filter(r => ['COMPLETED', 'CLOSED'].includes(r.status));
        }

        if (currentDistrictFilter !== 'ALL') {
            const cleanDist = currentDistrictFilter.replace(/^(Kecamatan|Kec\.)\s*/i, '').trim().toLowerCase();
            filtered = filtered.filter(r => r.address && r.address.toLowerCase().includes(cleanDist));
        }

        const totalEl = document.getElementById('drawerTotalText');
        if (totalEl) totalEl.innerText = `${filtered.length} Aduan`;

        renderReportList(filtered);
        renderMarkers(filtered, shouldZoom);

        if (shouldZoom && filtered.length === 0) {
            showMapToast('Belum ada laporan untuk kombinasi filter yang dipilih.');
        }
    }

    function renderReportList(items) {
        const container = document.getElementById('drawerListContainer');
        if (!container) return;

        if (!items || items.length === 0) {
            container.innerHTML = `
                <div class="py-10 px-4 text-center space-y-2">
                    <i data-lucide="map-pin-off" class="w-7 h-7 text-[#475569] mx-auto"></i>
                    <p class="text-sm font-bold text-[#0F172A]">Tidak ada laporan ditemukan</p>
                    <p class="text-xs text-[#475569]">Coba ubah pilihan kecamatan atau tampilkan semua status.</p>
                    <button type="button" onclick="resetAllFilters()" class="mt-2 min-h-[44px] px-4 py-2 rounded-lg bg-white border border-[#CBD5E1] text-xs font-bold text-[#1E3A8A] hover:bg-[#F8FAFC] cursor-pointer">
                        Tampilkan Semua
                    </button>
                </div>
            `;
            lucide.createIcons({ root: container });
            return;
        }

        container.innerHTML = items.map(item => {
            const meta = getStatusMeta(item.status);
            const districtText = getDistrictDisplayName(item.address);
            const { village, road } = parseAdministrativeAddress(item.address);
            const subLoc = village || road;

            const thumb = item.photo_url
                ? `<img src="${escapeHtml(item.photo_url)}" class="w-full h-full object-cover" alt="Foto kondisi lokasi" loading="lazy">`
                : `<div class="w-full h-full flex items-center justify-center bg-[#F8FAFC] text-[#475569]"><i data-lucide="image" class="w-5 h-5"></i></div>`;

            return `
                <button type="button" onclick="focusReportOnMap(${item.id})"
                    class="w-full text-left p-3 rounded-lg bg-white border border-[#E2E8F0] hover:border-[#1E3A8A] hover:bg-[#F8FAFC] transition flex items-center gap-3 cursor-pointer min-h-[44px]">
                    <div class="w-14 h-14 rounded-md overflow-hidden border border-[#E2E8F0] bg-[#F8FAFC] shrink-0">
                        ${thumb}
                    </div>
                    <div class="flex-1 min-w-0 space-y-1">
                        <div class="flex items-center justify-between gap-2">
                            <span class="text-[11px] font-bold px-2 py-0.5 rounded border ${meta.badge}">
                                ${meta.label}
                            </span>
                            <span class="text-[11px] font-mono text-[#475569] truncate">
                                ${escapeHtml(item.public_code)}
                            </span>
                        </div>
                        <h4 class="text-sm font-bold text-[#0F172A] truncate">
                            ${escapeHtml(item.title || 'Laporan Kerusakan')}
                        </h4>
                        <p class="text-xs text-[#475569] truncate">
                            <strong class="text-[#0F172A]">${escapeHtml(districtText)}</strong> • ${escapeHtml(subLoc)}
                        </p>
                    </div>
                    <i data-lucide="chevron-right" class="w-4 h-4 text-[#475569] shrink-0"></i>
                </button>
            `;
        }).join('');

        lucide.createIcons({ root: container });
    }

    function focusReportOnMap(id) {
        const item = reportsData.find(r => r.id === id);
        if (!item || !item.latitude || !item.longitude) return;

        map.flyTo([item.latitude, item.longitude], 16, { animate: true, duration: 0.9 });
        setTimeout(() => openLandingDetailModal(item), 650);
    }

    function renderMarkers(items, shouldZoom = true) {
        markersLayer.clearLayers();
        if (!items || items.length === 0) return;

        const bounds = [];

        items.forEach(item => {
            if (!item.latitude || !item.longitude || item.status === 'REJECTED') return;

            const latLng = [item.latitude, item.longitude];
            bounds.push(latLng);
            const meta = getStatusMeta(item.status);

            const customIcon = L.divIcon({
                className: 'custom-pin',
                html: `
                    <div style="background-color:${meta.color};width:26px;height:26px;border-radius:50%;border:2.5px solid #FFFFFF;display:flex;align-items:center;justify-content:center;cursor:pointer;">
                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="#FFFFFF" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">${meta.icon}</svg>
                    </div>
                `,
                iconSize: [26, 26],
                iconAnchor: [13, 13]
            });

            const marker = L.marker(latLng, { icon: customIcon }).addTo(markersLayer);
            marker.bindTooltip(`${escapeHtml(item.title || 'Laporan')} • ${meta.label}`, {
                direction: 'top',
                offset: [0, -14],
                className: 'custom-map-tooltip'
            });
            marker.on('click', () => openLandingDetailModal(item));

            if (item.damage_type === 'major' && item.end_latitude && item.end_longitude) {
                const lineCoords = (Array.isArray(item.route_coordinates) && item.route_coordinates.length > 1)
                    ? item.route_coordinates
                    : [latLng, [item.end_latitude, item.end_longitude]];

                const polyline = L.polyline(lineCoords, {
                    color: meta.color,
                    weight: 6,
                    opacity: 0.85,
                    dashArray: '6, 8',
                    lineCap: 'round'
                }).addTo(markersLayer);

                polyline.bindTooltip(`Ruas Jalan Rusak (${escapeHtml(item.road_length || 'Panjang')}) • ${meta.label}`, {
                    direction: 'top',
                    className: 'custom-map-tooltip'
                });
                polyline.on('click', () => openLandingDetailModal(item));
                bounds.push([item.end_latitude, item.end_longitude]);
            }
        });

        if (shouldZoom && bounds.length > 0) {
            if (bounds.length === 1) {
                map.flyTo(bounds[0], 15, { animate: true, duration: 0.8 });
            } else {
                map.fitBounds(bounds, { padding: [40, 40], maxZoom: 15 });
            }
        }
    }

    function setupSearch() {
        const form = document.getElementById('trackForm');
        const input = document.getElementById('trackCodeInput');
        const suggestionsBox = document.getElementById('searchSuggestions');
        const suggestionsList = document.getElementById('searchSuggestionsList');
        const countSpan = document.getElementById('searchResultCount');
        const container = document.getElementById('searchContainer');

        let currentMatches = [];
        let highlightedIndex = -1;

        function doSearch(query) {
            const q = (query || '').toLowerCase().trim();
            if (!q) {
                suggestionsBox.classList.add('hidden');
                currentMatches = [];
                highlightedIndex = -1;
                return;
            }

            const qClean = q.replace(/[^a-z0-9]/g, '');
            currentMatches = reportsData.filter(r => {
                if (r.status === 'REJECTED') return false;
                const haystack = [r.title, r.public_code, r.reporter_name, r.address, r.category_name]
                    .map(v => (v || '').toLowerCase()).join(' ');
                const cleanCode = (r.public_code || '').toLowerCase().replace(/[^a-z0-9]/g, '');
                return haystack.includes(q) || (qClean && cleanCode.includes(qClean));
            });

            const displayMatches = currentMatches.slice(0, 6);
            highlightedIndex = -1;

            if (displayMatches.length === 0) {
                countSpan.innerText = '0 hasil';
                suggestionsList.innerHTML = `
                    <div class="p-4 text-center text-xs text-[#475569]">
                        Tidak ditemukan laporan untuk "<strong>${escapeHtml(query)}</strong>".
                    </div>
                `;
            } else {
                countSpan.innerText = `${currentMatches.length} laporan`;
                suggestionsList.innerHTML = displayMatches.map((r, idx) => {
                    const meta = getStatusMeta(r.status);
                    const addr = r.address ? r.address.split(',')[0] : 'Kolaka Utara';
                    return `
                        <button type="button" class="suggestion-item w-full text-left p-3 hover:bg-[#F8FAFC] transition flex items-center justify-between gap-2 cursor-pointer" data-index="${idx}">
                            <div class="min-w-0">
                                <div class="text-xs font-bold text-[#0F172A] truncate">${escapeHtml(r.title)}</div>
                                <div class="text-[11px] text-[#475569] truncate mt-0.5">
                                    <span class="font-mono font-semibold">${escapeHtml(r.public_code)}</span> • ${escapeHtml(addr)}
                                </div>
                            </div>
                            <span class="px-2 py-0.5 rounded border text-[10px] font-bold shrink-0 ${meta.badge}">${meta.label}</span>
                        </button>
                    `;
                }).join('');
            }

            suggestionsBox.classList.remove('hidden');
            suggestionsList.querySelectorAll('.suggestion-item').forEach(el => {
                el.addEventListener('click', () => {
                    const idx = parseInt(el.getAttribute('data-index'), 10);
                    if (displayMatches[idx]) selectReport(displayMatches[idx]);
                });
            });
        }

        function selectReport(item) {
            input.value = item.title ? `${item.public_code} (${item.title})` : item.public_code;
            suggestionsBox.classList.add('hidden');
            highlightedIndex = -1;
            focusReportOnMap(item.id);
        }

        input.addEventListener('input', e => doSearch(e.target.value));
        input.addEventListener('focus', () => { if (input.value.trim()) doSearch(input.value); });

        input.addEventListener('keydown', e => {
            const displayMatches = currentMatches.slice(0, 6);
            if (suggestionsBox.classList.contains('hidden') || displayMatches.length === 0) return;

            if (e.key === 'ArrowDown') {
                e.preventDefault();
                highlightedIndex = (highlightedIndex + 1) % displayMatches.length;
            } else if (e.key === 'ArrowUp') {
                e.preventDefault();
                highlightedIndex = (highlightedIndex - 1 + displayMatches.length) % displayMatches.length;
            } else if (e.key === 'Enter') {
                e.preventDefault();
                selectReport(displayMatches[highlightedIndex >= 0 ? highlightedIndex : 0]);
                return;
            } else if (e.key === 'Escape') {
                suggestionsBox.classList.add('hidden');
                highlightedIndex = -1;
                return;
            }

            suggestionsList.querySelectorAll('.suggestion-item').forEach((el, idx) => {
                el.classList.toggle('bg-slate-100', idx === highlightedIndex);
            });
        });

        form.addEventListener('submit', e => {
            e.preventDefault();
            const displayMatches = currentMatches.slice(0, 6);
            if (displayMatches.length > 0) selectReport(displayMatches[0]);
            else if (input.value.trim()) doSearch(input.value);
        });

        document.addEventListener('click', e => {
            if (container && !container.contains(e.target)) {
                suggestionsBox.classList.add('hidden');
            }
        });
    }
</script>
@endpush
@endsection
