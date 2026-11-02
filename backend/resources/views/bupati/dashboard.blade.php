@extends('layouts.app')

@section('title', 'Pusat Kendali Eksekutif Kepala Daerah - Sistem Pengaduan')
@section('header_title', 'Pusat Kendali Eksekutif Kepala Daerah')

@section('content')
@php
    $todayStr = \Carbon\Carbon::now('Asia/Makassar')->locale('id')->isoFormat('dddd, D MMMM Y • HH:mm:ss') . ' WITA';
@endphp

<div class="py-4 sm:py-6 space-y-6">

    <!-- Header Eksekutif -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pt-1">
        <div>
            <h2 class="font-outfit font-extrabold text-lg sm:text-xl text-slate-900 leading-tight">
                Tata Kelola Pengaduan & Infrastruktur Daerah
            </h2>
            <p class="text-xs text-slate-500 mt-0.5">
                Pemantauan berkala penanganan pengaduan fisik jalan, jembatan, dan fasilitas umum kabupaten.
            </p>
        </div>
        <div class="flex items-center gap-2 shrink-0">
            <span class="px-3.5 py-1.5 rounded-xl bg-white text-slate-700 text-xs font-semibold border border-slate-200 flex items-center gap-2 shadow-2xs">
                <i data-lucide="clock" class="w-4 h-4 text-[#1E3A8A]"></i>
                <span class="live-wita-clock">{{ $todayStr }}</span>
            </span>
        </div>
    </div>

    <!-- Ringkasan KPI Eksekutif -->
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-7 gap-3">
        <!-- 1. Total Aduan -->
        <div class="card-white p-4 rounded-2xl bg-white border border-[#E2E8F0] shadow-2xs hover:shadow-md transition flex flex-col justify-between">
            <div class="flex items-center justify-between">
                <span class="text-[11px] text-slate-500 font-bold uppercase tracking-wider">TOTAL ADUAN</span>
                <div class="w-8 h-8 rounded-xl bg-blue-50/80 text-[#1E3A8A] border border-blue-100/70 flex items-center justify-center">
                    <i data-lucide="inbox" class="w-4 h-4"></i>
                </div>
            </div>
            <div class="mt-2">
                <div class="font-outfit text-2xl sm:text-3xl font-extrabold text-slate-900 leading-tight">
                    {{ $summary['total'] }}
                </div>
                <span class="text-[11px] text-slate-500 font-medium mt-0.5 block">Laporan masuk</span>
            </div>
        </div>

        <!-- 2. Menunggu -->
        <div class="card-white p-4 rounded-2xl bg-white border border-[#E2E8F0] shadow-2xs hover:shadow-md transition flex flex-col justify-between">
            <div class="flex items-center justify-between">
                <span class="text-[11px] text-slate-500 font-bold uppercase tracking-wider">MENUNGGU</span>
                <div class="w-8 h-8 rounded-xl bg-blue-50/80 text-[#1E3A8A] border border-blue-100/70 flex items-center justify-center">
                    <i data-lucide="clock" class="w-4 h-4"></i>
                </div>
            </div>
            <div class="mt-2">
                <div class="font-outfit text-2xl sm:text-3xl font-extrabold text-slate-900 leading-tight">
                    {{ $summary['pending'] }}
                </div>
                <span class="text-[11px] text-slate-500 font-medium mt-0.5 block">Menunggu verifikasi</span>
            </div>
        </div>

        <!-- 3. Ditugaskan -->
        <div class="card-white p-4 rounded-2xl bg-white border border-[#E2E8F0] shadow-2xs hover:shadow-md transition flex flex-col justify-between">
            <div class="flex items-center justify-between">
                <span class="text-[11px] text-slate-500 font-bold uppercase tracking-wider">DITUGASKAN</span>
                <div class="w-8 h-8 rounded-xl bg-blue-50/80 text-[#1E3A8A] border border-blue-100/70 flex items-center justify-center">
                    <i data-lucide="send" class="w-4 h-4"></i>
                </div>
            </div>
            <div class="mt-2">
                <div class="font-outfit text-2xl sm:text-3xl font-extrabold text-slate-900 leading-tight">
                    {{ $summary['dispatched'] }}
                </div>
                <span class="text-[11px] text-slate-500 font-medium mt-0.5 block">Ditugaskan ke petugas</span>
            </div>
        </div>

        <!-- 4. Dikerjakan -->
        <div class="card-white p-4 rounded-2xl bg-white border border-[#E2E8F0] shadow-2xs hover:shadow-md transition flex flex-col justify-between">
            <div class="flex items-center justify-between">
                <span class="text-[11px] text-slate-500 font-bold uppercase tracking-wider">DIKERJAKAN</span>
                <div class="w-8 h-8 rounded-xl bg-blue-50/80 text-[#1E3A8A] border border-blue-100/70 flex items-center justify-center">
                    <i data-lucide="hard-hat" class="w-4 h-4"></i>
                </div>
            </div>
            <div class="mt-2">
                <div class="font-outfit text-2xl sm:text-3xl font-extrabold text-slate-900 leading-tight">
                    {{ $summary['in_progress'] }}
                </div>
                <span class="text-[11px] text-slate-500 font-medium mt-0.5 block">Fisik di lapangan</span>
            </div>
        </div>

        <!-- 5. Menunggu Validasi -->
        <div class="card-white p-4 rounded-2xl bg-white border border-[#E2E8F0] shadow-2xs hover:shadow-md transition flex flex-col justify-between">
            <div class="flex items-center justify-between">
                <span class="text-[11px] text-slate-500 font-bold uppercase tracking-wider">VALIDASI</span>
                <div class="w-8 h-8 rounded-xl bg-blue-50/80 text-[#1E3A8A] border border-blue-100/70 flex items-center justify-center">
                    <i data-lucide="clock-3" class="w-4 h-4"></i>
                </div>
            </div>
            <div class="mt-2">
                <div class="font-outfit text-2xl sm:text-3xl font-extrabold text-slate-900 leading-tight">
                    {{ $summary['waiting_verification'] ?? 0 }}
                </div>
                <span class="text-[11px] text-slate-500 font-medium mt-0.5 block">Menunggu validasi</span>
            </div>
        </div>

        <!-- 6. Selesai -->
        <div class="card-white p-4 rounded-2xl bg-white border border-[#E2E8F0] shadow-2xs hover:shadow-md transition flex flex-col justify-between">
            <div class="flex items-center justify-between">
                <span class="text-[11px] text-slate-500 font-bold uppercase tracking-wider">SELESAI</span>
                <div class="w-8 h-8 rounded-xl bg-blue-50/80 text-[#1E3A8A] border border-blue-100/70 flex items-center justify-center">
                    <i data-lucide="check-circle-2" class="w-4 h-4"></i>
                </div>
            </div>
            <div class="mt-2">
                <div class="font-outfit text-2xl sm:text-3xl font-extrabold text-slate-900 leading-tight">
                    {{ $summary['completed'] }}
                </div>
                <span class="text-[11px] text-slate-500 font-medium mt-0.5 block">Tuntas diperbaiki</span>
            </div>
        </div>

        <!-- 7. Ditolak -->
        <div class="card-white p-4 rounded-2xl bg-white border border-[#E2E8F0] shadow-2xs hover:shadow-md transition flex flex-col justify-between">
            <div class="flex items-center justify-between">
                <span class="text-[11px] text-slate-500 font-bold uppercase tracking-wider">DITOLAK</span>
                <div class="w-8 h-8 rounded-xl bg-blue-50/80 text-[#1E3A8A] border border-blue-100/70 flex items-center justify-center">
                    <i data-lucide="x-circle" class="w-4 h-4"></i>
                </div>
            </div>
            <div class="mt-2">
                <div class="font-outfit text-2xl sm:text-3xl font-extrabold text-slate-900 leading-tight">
                    {{ $summary['rejected'] }}
                </div>
                <span class="text-[11px] text-slate-500 font-medium mt-0.5 block">Tidak memenuhi syarat</span>
            </div>
        </div>
    </div>

    <!-- Navigasi Tab Eksekutif -->
    <div class="w-full bg-slate-100/90 p-1.5 rounded-2xl border border-slate-200/80 shadow-2xs">
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-1.5 w-full">
            <!-- 1. Tab Peta Spasial & Analitik Eksekutif -->
            <button type="button" onclick="switchBupatiTab('executive')" id="tabBtn-executive"
                class="bupati-tab-btn w-full py-2.5 px-3 rounded-xl text-xs font-bold transition-all flex items-center justify-center gap-2 bg-white text-[#1E3A8A] shadow-xs">
                <i data-lucide="map" class="w-4 h-4 shrink-0"></i>
                <span class="truncate">Peta Spasial & Analitik Eksekutif</span>
            </button>

            <!-- 2. Tab Daftar Seluruh Pengaduan -->
            <button type="button" onclick="switchBupatiTab('complaints')" id="tabBtn-complaints"
                class="bupati-tab-btn w-full py-2.5 px-3 rounded-xl text-xs font-bold transition-all flex items-center justify-center gap-2 text-slate-600 hover:text-slate-900 hover:bg-white/60">
                <i data-lucide="layers" class="w-4 h-4 shrink-0"></i>
                <span class="truncate">Daftar Seluruh Pengaduan</span>
                <span class="px-2 py-0.5 rounded-full text-[10px] font-extrabold bg-slate-200/70 text-slate-700">{{ $allComplaints->count() }}</span>
            </button>

            <!-- 3. Tab Arsip Laporan (Selesai & Ditolak Sesuai Gambar 2) -->
            <button type="button" onclick="switchBupatiTab('archive')" id="tabBtn-archive"
                class="bupati-tab-btn w-full py-2.5 px-3 rounded-xl text-xs font-bold transition-all flex items-center justify-center gap-2 text-slate-600 hover:text-slate-900 hover:bg-white/60">
                <i data-lucide="archive" class="w-4 h-4 shrink-0"></i>
                <span class="truncate">Arsip Laporan</span>
                <span class="px-2 py-0.5 rounded-full text-[10px] font-extrabold bg-slate-200/70 text-slate-700">{{ $archivedComplaints->count() }}</span>
            </button>
        </div>
    </div>

    <!-- Tab 1: Peta Spasial & Analitik Eksekutif -->
    <div id="tabContent-executive" class="space-y-6">
        <!-- PETA SEBARAN INFRASTRUKTUR -->
        <div class="card-white rounded-2xl p-5 bg-white border border-[#E2E8F0] space-y-3">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 border-b border-slate-100 pb-3">
                <div>
                    <h3 class="font-outfit font-bold text-base text-slate-900 flex items-center gap-2">
                        <i data-lucide="map-pin" class="w-4 h-4 text-[#1E3A8A]"></i>
                        <span>Peta Sebaran Infrastruktur Rusak Seluruh Kabupaten</span>
                    </h3>
                    <p class="text-xs text-slate-500">Visualisasi titik kerusakan jalan, jembatan, drainase, dan fasilitas umum.</p>
                </div>
                <div class="flex flex-wrap items-center gap-3 text-xs font-semibold">
                    <span class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full bg-[#EF4444]"></span> Verifikasi</span>
                    <span class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full bg-[#1E40AF]"></span> Ditugaskan</span>
                    <span class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full bg-[#F59E0B]"></span> Sedang Dikerjakan</span>
                    <span class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full bg-[#F59E0B]"></span> Validasi</span>
                    <span class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full bg-[#10B981]"></span> Selesai</span>
                    <span class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full bg-[#DC2626]"></span> Ditolak</span>
                </div>
            </div>

            <!-- Leaflet Map Container -->
            <div id="bupatiMap" class="w-full h-[420px] rounded-xl border border-[#E2E8F0] z-10"></div>
        </div>

        <!-- MODUL TERPADU: MONITORING EKSEKUTIF, GRAFIK KINERJA & PELAPORAN RESMI (PDF) -->
        @include('partials.executive_monitoring_reporting')



        <!-- Sebaran Pengaduan per Wilayah Kecamatan & Desa -->
        <div class="card-white rounded-2xl p-5 sm:p-6 bg-white border border-[#E2E8F0] shadow-2xs space-y-5">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-3 border-b border-slate-100 pb-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-2xl bg-blue-50 text-[#1E3A8A] flex items-center justify-center border border-blue-200 shrink-0">
                        <i data-lucide="map" class="w-5 h-5"></i>
                    </div>
                    <div>
                        <h3 class="font-outfit font-extrabold text-sm sm:text-base text-slate-900 leading-tight">
                            Sebaran Pengaduan Infrastruktur per Wilayah Kecamatan & Desa
                        </h3>
                        <p class="text-xs text-slate-500 mt-0.5">
                            Distribusi laporan masyarakat, efektivitas penanganan fisik Dinas PUPR, dan status wilayah di 15 Kecamatan Kabupaten Kolaka Utara.
                        </p>
                    </div>
                </div>
                <div class="flex items-center gap-2 self-start md:self-auto shrink-0">
                    <span class="px-3 py-1 rounded-full bg-blue-50 text-[#1E3A8A] text-xs font-bold border border-blue-200 flex items-center gap-1.5">
                        <i data-lucide="shield-check" class="w-3.5 h-3.5 text-blue-600"></i>
                        <span>15 Kecamatan Terintegrasi</span>
                    </span>
                </div>
            </div>

            <!-- Ringkasan Statistik Kewilayahan Kolaka Utara -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                <div class="p-3.5 rounded-xl bg-slate-50 border border-slate-200/80 flex items-center justify-between">
                    <div>
                        <span class="text-[11px] text-slate-500 font-semibold block">Total Kecamatan Terlayani</span>
                        <span class="text-lg font-outfit font-extrabold text-slate-900">15 Kecamatan</span>
                    </div>
                    <div class="w-8 h-8 rounded-xl bg-blue-50/80 text-[#1E3A8A] border border-blue-100/70 flex items-center justify-center shrink-0">
                        <i data-lucide="building-2" class="w-4 h-4"></i>
                    </div>
                </div>

                <div class="p-3.5 rounded-xl bg-slate-50 border border-slate-200/80 flex items-center justify-between">
                    <div>
                        <span class="text-[11px] text-slate-500 font-semibold block">Pusat Pemerintahan (Ibukota)</span>
                        <span class="text-lg font-outfit font-extrabold text-[#1E3A8A]">Lasusua</span>
                    </div>
                    <div class="w-8 h-8 rounded-xl bg-blue-50/80 text-[#1E3A8A] border border-blue-100/70 flex items-center justify-center shrink-0">
                        <i data-lucide="navigation" class="w-4 h-4"></i>
                    </div>
                </div>

                <div class="p-3.5 rounded-xl bg-slate-50 border border-slate-200/80 flex items-center justify-between">
                    <div>
                        <span class="text-[11px] text-slate-500 font-semibold block">Perlindungan Geofencing</span>
                        <span class="text-xs font-extrabold text-[#1E3A8A] flex items-center gap-1.5 mt-1">
                            <span class="w-2 h-2 rounded-full bg-[#1E3A8A]"></span>
                            100% Terproteksi Kolaka Utara
                        </span>
                    </div>
                    <div class="w-8 h-8 rounded-xl bg-blue-50/80 text-[#1E3A8A] border border-blue-100/70 flex items-center justify-center shrink-0">
                        <i data-lucide="radar" class="w-4 h-4"></i>
                    </div>
                </div>
            </div>

            <!-- Grid 15 Kecamatan Kolaka Utara -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3 text-xs">
                @foreach($districtStats as $ds)
                    <div class="p-3.5 rounded-xl bg-slate-50/90 border border-slate-200/80 hover:bg-white hover:border-blue-300 hover:shadow-sm transition flex flex-col justify-between space-y-3">
                        <div class="flex items-start justify-between gap-2">
                            <div class="min-w-0">
                                <span class="font-extrabold text-slate-900 text-xs sm:text-sm block truncate">{{ $ds['district'] }}</span>
                                <span class="text-[11px] text-slate-500 block truncate flex items-center gap-1.5 mt-0.5">
                                    <span class="w-1.5 h-1.5 rounded-full {{ $ds['total'] > 0 ? 'bg-blue-600' : 'bg-slate-400' }}"></span>
                                    <span>Pusat: {{ $ds['village'] }}</span>
                                </span>
                            </div>
                            @if($ds['total'] > 0)
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-extrabold bg-blue-100 text-[#1E3A8A] shrink-0 whitespace-nowrap">
                                    {{ $ds['total'] }} Laporan
                                </span>
                            @else
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-semibold bg-slate-200/70 text-slate-600 shrink-0 whitespace-nowrap">
                                    Nihil (Aman)
                                </span>
                            @endif
                        </div>

                        <div class="pt-2 border-t border-slate-200/70 space-y-1.5 text-[11px]">
                            <div class="flex items-center justify-between text-slate-500">
                                <span>Status Penanganan:</span>
                                @if($ds['total'] === 0)
                                    <span class="text-slate-400 font-medium">Kondusif (Nihil)</span>
                                @elseif($ds['completed'] === $ds['total'])
                                    <span class="font-bold text-emerald-600 flex items-center gap-1">
                                        <i data-lucide="check-check" class="w-3.5 h-3.5"></i>
                                        100% Selesai
                                    </span>
                                @endif
                            </div>
                            @if($ds['total'] > 0 && $ds['completed'] !== $ds['total'])
                                <div class="flex items-center gap-1.5 flex-wrap">
                                    @if($ds['pending'] > 0)
                                        <span class="px-2 py-0.5 rounded-md bg-red-50 text-red-700 border border-red-200 text-[10px] font-bold flex items-center gap-1">
                                            <span class="w-1.5 h-1.5 rounded-full bg-red-500"></span>
                                            {{ $ds['pending'] }} Verifikasi
                                        </span>
                                    @endif
                                    @if($ds['dispatched'] > 0)
                                        <span class="px-2 py-0.5 rounded-md bg-indigo-50 text-indigo-700 border border-indigo-200 text-[10px] font-bold flex items-center gap-1">
                                            <span class="w-1.5 h-1.5 rounded-full bg-indigo-500"></span>
                                            {{ $ds['dispatched'] }} Ditugaskan
                                        </span>
                                    @endif
                                    @if(($ds['in_progress'] ?? 0) > 0)
                                        <span class="px-2 py-0.5 rounded-md bg-amber-50 text-amber-700 border border-amber-200 text-[10px] font-bold flex items-center gap-1">
                                            <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                                            {{ $ds['in_progress'] }} Dikerjakan
                                        </span>
                                    @endif
                                    @if(($ds['waiting_verification'] ?? 0) > 0)
                                        <span class="px-2 py-0.5 rounded-md bg-amber-50 text-amber-700 border border-amber-200 text-[10px] font-bold flex items-center gap-1">
                                            <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                                            {{ $ds['waiting_verification'] }} Validasi
                                        </span>
                                    @endif
                                    @if($ds['completed'] > 0)
                                        <span class="px-2 py-0.5 rounded-md bg-emerald-50 text-emerald-700 border border-emerald-200 text-[10px] font-bold flex items-center gap-1">
                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                            {{ $ds['completed'] }} Selesai
                                        </span>
                                    @endif
                                    @if($ds['rejected'] > 0)
                                        <span class="px-2 py-0.5 rounded-md bg-red-50 text-red-700 border border-red-200 text-[10px] font-bold flex items-center gap-1">
                                            <span class="w-1.5 h-1.5 rounded-full bg-red-500"></span>
                                            {{ $ds['rejected'] }} Ditolak
                                        </span>
                                    @endif
                                </div>
                            @endif
                        </div>

                        <div class="pt-1 flex items-center justify-between">
                            <span class="font-mono text-[10px] text-slate-400">
                                {{ number_format($ds['latitude'], 3) }}, {{ number_format($ds['longitude'], 3) }}
                            </span>
                            <button type="button" onclick="focusDistrictOnMap({{ $ds['latitude'] }}, {{ $ds['longitude'] }}, '{{ $ds['district'] }}')"
                                class="px-2.5 py-1 rounded-lg bg-white border border-slate-200 hover:border-[#1E3A8A] text-[#1E3A8A] font-bold text-[10px] transition flex items-center gap-1 cursor-pointer">
                                <i data-lucide="crosshair" class="w-3 h-3"></i>
                                <span>Fokus Peta</span>
                            </button>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <!-- Tab 2: Daftar Seluruh Pengaduan -->
    <div id="tabContent-complaints" class="hidden space-y-4">
        <div class="card-white rounded-2xl p-5 bg-white border border-[#E2E8F0] space-y-4">
            <!-- Toolbar Filter & Search (Sejajar Satu Baris Rapi Persis Gambar 2) -->
            <div class="flex flex-col sm:flex-row sm:items-center gap-2 border-b border-slate-100 pb-4">
                <!-- Search Input (Flex-1 Memanjang Mengisi Ruang Tanpa Celah Kosong) -->
                <div class="relative w-full sm:flex-1 min-w-[160px]">
                    <i data-lucide="search" class="w-3.5 h-3.5 text-slate-400 absolute left-2.5 top-1/2 -translate-y-1/2 pointer-events-none"></i>
                    <input type="text" id="bupatiSearchInput" oninput="filterBupatiTable()"
                        placeholder="Cari aduan, judul kerusakan, pelapor, atau alamat..."
                        class="w-full pl-8 pr-3 py-1.5 rounded-xl bg-slate-50 border border-slate-200 text-xs text-slate-800 placeholder-slate-400 focus:outline-hidden focus:ring-1 focus:ring-[#1E3A8A] focus:border-[#1E3A8A] focus:bg-white transition">
                </div>

                <!-- Filter Controls (Sejajar Satu Baris Tanpa Turun ke Bawah) -->
                <div class="flex items-center gap-1.5 w-full sm:w-auto shrink-0 flex-nowrap overflow-x-auto pb-1 sm:pb-0">
                    <!-- Dropdown Kategori -->
                    <div class="flex items-center gap-1 text-xs text-slate-500 shrink-0 font-medium whitespace-nowrap">
                        <i data-lucide="layers" class="w-3.5 h-3.5 text-[#1E3A8A]"></i>
                        <span>Filter Kategori:</span>
                    </div>
                    <select id="bupatiCategorySelect" onchange="filterBupatiTable()" 
                        class="text-xs font-semibold rounded-xl border border-slate-200 py-1.5 px-2 bg-slate-50 text-slate-700 focus:outline-none focus:border-[#1E3A8A] focus:bg-white transition shrink-0">
                        <option value="ALL">Semua Kategori</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                        @endforeach
                    </select>

                    <!-- Dropdown Tipe Laporan (Kerusakan Ringan / Berat) -->
                    <div class="flex items-center gap-1 text-xs text-slate-500 shrink-0 font-medium whitespace-nowrap">
                        <i data-lucide="git-commit" class="w-3.5 h-3.5 text-[#1E3A8A]"></i>
                        <span>Tipe Laporan:</span>
                    </div>
                    <select id="bupatiDamageTypeSelect" onchange="filterBupatiTable()" 
                        class="text-xs font-semibold rounded-xl border border-slate-200 py-1.5 px-2 bg-slate-50 text-slate-700 focus:outline-none focus:border-[#1E3A8A] focus:bg-white transition shrink-0">
                        <option value="ALL">Semua Tipe</option>
                        <option value="minor">Kerusakan Ringan</option>
                        <option value="major">Kerusakan Berat</option>
                    </select>

                    <!-- Dropdown Status -->
                    <div class="flex items-center gap-1 text-xs text-slate-500 shrink-0 font-medium whitespace-nowrap">
                        <i data-lucide="check-square" class="w-3.5 h-3.5 text-[#1E3A8A]"></i>
                        <span>Status:</span>
                    </div>
                    <select id="bupatiStatusSelect" onchange="filterBupatiTable()" 
                        class="text-xs font-semibold rounded-xl border border-slate-200 py-1.5 px-2 bg-slate-50 text-slate-700 focus:outline-none focus:border-[#1E3A8A] focus:bg-white transition shrink-0">
                        <option value="ALL">Semua Status</option>
                        <option value="SUBMITTED">Verifikasi</option>
                        <option value="DISPATCHED">Ditugaskan</option>
                        <option value="IN_PROGRESS">Dikerjakan</option>
                        <option value="WAITING_VERIFICATION">Validasi</option>
                        <option value="COMPLETED">Selesai</option>
                        <option value="REJECTED">Ditolak</option>
                    </select>

                    <!-- Tombol Reset Filter -->
                    <button type="button" onclick="resetBupatiFilters()"
                        class="px-2.5 py-1.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs transition flex items-center gap-1 shrink-0 cursor-pointer">
                        <i data-lucide="rotate-ccw" class="w-3.5 h-3.5"></i>
                        <span>Reset</span>
                    </button>
                </div>
            </div>

            <!-- Table Pengaduan Terkini (Simetris, Rapih, Tanpa Scroll Horizontal) -->
            <div class="rounded-2xl border border-slate-200 shadow-2xs overflow-hidden bg-white">
                <table class="w-full text-left text-xs text-slate-600">
                    <thead class="bg-slate-50 text-slate-700 font-bold border-b border-slate-200 uppercase tracking-wider text-[11px]">
                        <tr>
                            <th class="py-3 px-3 w-28 whitespace-nowrap">Kode & Waktu</th>
                            <th class="py-3 px-3">Judul Aduan & Pelapor</th>
                            <th class="py-3 px-3 w-28 whitespace-nowrap">Kategori</th>
                            <th class="py-3 px-3 w-24 whitespace-nowrap">Tipe Laporan</th>
                            <th class="py-3 px-3 w-24 text-center whitespace-nowrap">Status</th>
                            <th class="py-3 px-3 w-40 whitespace-nowrap">OPD & Petugas</th>
                            <th class="py-3 px-3 w-16 text-center whitespace-nowrap">Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="bupatiTableBody" class="divide-y divide-slate-100 bg-white">
                        @forelse($allComplaints as $c)
                            @php
                                $firstPhoto = $c->photos->first();
                                $firstPhotoUrl = $firstPhoto ? ($firstPhoto->url ?? asset('storage/' . $firstPhoto->storage_key)) : null;
                                $statusBadgeClass = match($c->status) {
                                    'COMPLETED', 'CLOSED' => 'bg-emerald-50 text-emerald-800 border-emerald-200',
                                    'WAITING_VERIFICATION' => 'bg-amber-50 text-amber-700 border-amber-300',
                                    'IN_PROGRESS' => 'bg-amber-50 text-amber-700 border-amber-300',
                                    'DISPATCHED' => 'bg-indigo-50 text-indigo-700 border-indigo-200',
                                    'SUBMITTED', 'VERIFICATION' => 'bg-red-50 text-red-700 border-red-200',
                                    'REJECTED' => 'bg-red-50 text-red-700 border-red-200',
                                    'REVISION_REQUIRED' => 'bg-amber-50 text-amber-800 border-amber-200',
                                    default => 'bg-red-50 text-[#EF4444] border-red-200'
                                };
                                $statusLabel = match($c->status) {
                                    'COMPLETED', 'CLOSED' => 'SELESAI',
                                    'WAITING_VERIFICATION' => 'VALIDASI',
                                    'IN_PROGRESS' => 'DIKERJAKAN',
                                    'DISPATCHED' => 'DITUGASKAN',
                                    'SUBMITTED', 'VERIFICATION' => 'VERIFIKASI',
                                    'REJECTED' => 'DITOLAK',
                                    'REVISION_REQUIRED' => 'PERLU REVISI',
                                    default => 'VERIFIKASI'
                                };
                            @endphp
                            <tr class="bupati-complaint-row hover:bg-slate-50/70 transition"
                                data-code="{{ strtolower($c->public_code) }}"
                                data-title="{{ strtolower($c->title) }}"
                                data-reporter="{{ strtolower($c->user?->name ?? $c->citizen?->name ?? 'warga') }}"
                                data-address="{{ strtolower($c->address ?? '') }}"
                                data-category="{{ $c->category_id }}"
                                data-damage-type="{{ $c->damage_type ?? 'minor' }}"
                                data-status="{{ $c->status }}">
                                <td class="py-2.5 px-3 align-middle whitespace-nowrap">
                                    <button type="button" onclick="openBupatiDetailModal({{ $c->id }})" class="font-mono font-bold text-[#1E3A8A] text-xs hover:underline text-left cursor-pointer">
                                        #{{ $c->public_code }}
                                    </button>
                                    <div class="text-[10px] text-slate-400 font-sans mt-0.5">{{ $c->created_at?->diffForHumans() }}</div>
                                </td>
                                <td class="py-2.5 px-3 align-middle">
                                    <div class="flex items-center gap-2.5">
                                        <div onclick="openBupatiDetailModal({{ $c->id }})" class="relative w-11 h-11 rounded-xl overflow-hidden border border-slate-200 bg-slate-100 shrink-0 cursor-pointer hover:ring-2 hover:ring-[#1E3A8A] transition shadow-2xs" title="Lihat Foto & Detail">
                                            @if($firstPhotoUrl)
                                                <img src="{{ $firstPhotoUrl }}" alt="Foto Aduan" class="w-full h-full object-cover">
                                            @else
                                                <div class="w-full h-full flex items-center justify-center text-slate-300">
                                                    <i data-lucide="image" class="w-4 h-4"></i>
                                                </div>
                                            @endif
                                        </div>
                                        <div class="min-w-0 flex-1">
                                            <div class="flex items-center gap-1.5 flex-wrap mb-0.5">
                                                <span class="font-bold text-slate-900 text-xs leading-snug truncate max-w-[200px]" title="{{ $c->title }}">{{ $c->title }}</span>
                                                @if($c->severity_text)
                                                    <span class="px-1.5 py-0.2 rounded text-[9.5px] font-bold bg-blue-50 text-[#1E3A8A] border border-blue-200 shrink-0 whitespace-nowrap">{{ $c->severity_text }}</span>
                                                @endif
                                            </div>
                                            <div class="text-[10.5px] text-slate-500 flex items-center gap-1 truncate max-w-[240px]">
                                                <i data-lucide="user" class="w-3 h-3 text-slate-400 shrink-0"></i>
                                                <span class="font-semibold text-slate-700 truncate">{{ $c->user?->name ?? $c->citizen?->name ?? 'Warga Terdaftar' }}</span>
                                                <span class="text-slate-300 shrink-0">•</span>
                                                <span class="truncate text-slate-400" title="{{ $c->address }}">{{ $c->address ?? 'Kolaka Utara' }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="py-2.5 px-3 align-middle whitespace-nowrap">
                                    <span class="px-2 py-0.5 rounded-md bg-slate-100 text-slate-700 font-semibold text-[10.5px] whitespace-nowrap inline-block border border-slate-200/60 truncate max-w-[120px]" title="{{ $c->category?->name ?? 'Umum' }}">
                                        {{ $c->category?->name ?? 'Umum' }}
                                    </span>
                                </td>
                                <td class="py-2.5 px-3 align-middle whitespace-nowrap">
                                    @if($c->damage_type === 'major')
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-red-50 text-red-700 border border-red-200 inline-flex items-center gap-1 whitespace-nowrap">
                                            <span class="w-1.5 h-1.5 rounded-full bg-red-500 shrink-0"></span>
                                            <span>Berat</span>
                                        </span>
                                    @else
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-red-50 text-red-700 border border-red-200 inline-flex items-center gap-1 whitespace-nowrap">
                                            <span class="w-1.5 h-1.5 rounded-full bg-red-500 shrink-0"></span>
                                            <span>Ringan</span>
                                        </span>
                                    @endif
                                </td>
                                <td class="py-2.5 px-3 text-center align-middle whitespace-nowrap">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-extrabold border {{ $statusBadgeClass }} whitespace-nowrap">
                                        {{ $statusLabel }}
                                    </span>
                                </td>
                                <td class="py-2.5 px-3 align-middle">
                                    @if(in_array($c->status, ['IN_PROGRESS', 'WAITING_VERIFICATION', 'COMPLETED', 'CLOSED']) && ($c->latestDisposition?->department || $c->latestDisposition?->assignee))
                                        <div class="font-semibold text-slate-800 text-xs leading-snug truncate max-w-[160px]">{{ $c->latestDisposition?->department?->name ?? 'Dinas Teknis' }}</div>
                                        <div class="text-[10.5px] text-slate-500 mt-0.5 flex items-center gap-1">
                                            <i data-lucide="hard-hat" class="w-3 h-3 text-slate-400 shrink-0"></i>
                                            <span class="truncate max-w-[140px]">{{ $c->latestDisposition?->assignee?->name ?? 'Petugas Lapangan' }}</span>
                                        </div>
                                    @else
                                        <span class="text-[11px] text-slate-400 font-medium italic">Belum diambil</span>
                                    @endif
                                </td>
                                <td class="py-2.5 px-3 text-center align-middle whitespace-nowrap">
                                    <button type="button" onclick="openBupatiDetailModal({{ $c->id }})"
                                        title="Lihat Detail & Timeline"
                                        class="w-7 h-7 sm:w-8 sm:h-8 rounded-xl bg-blue-50 hover:bg-[#1E3A8A] text-[#1E3A8A] hover:text-white border border-blue-200 transition inline-flex items-center justify-center shadow-2xs cursor-pointer mx-auto">
                                        <i data-lucide="eye" class="w-3.5 h-3.5"></i>
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="py-8 text-center text-slate-400 text-xs">Belum ada data pengaduan masuk.</td>
                            </tr>
                        @endforelse
                        <!-- Empty Filter Row Notice -->
                        <tr id="bupatiEmptyFilterRow" class="hidden">
                            <td colspan="7" class="py-8 text-center text-slate-400 text-xs">
                                <div class="flex flex-col items-center justify-center gap-1">
                                    <i data-lucide="filter-x" class="w-5 h-5 text-slate-300"></i>
                                    <span>Tidak ada laporan yang sesuai dengan filter pencarian.</span>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Tab 3: Arsip Laporan -->
    <div id="tabContent-archive" class="hidden space-y-4">
        <div class="card-white rounded-2xl p-5 bg-white border border-[#E2E8F0] space-y-4">
            <!-- Header Section -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 border-b border-slate-100 pb-3">
                <div>
                    <h3 class="font-outfit font-bold text-base text-slate-900 flex items-center gap-2">
                        <i data-lucide="archive" class="w-4 h-4 text-[#1E3A8A]"></i>
                        <span>Arsip Laporan (Selesai & Ditolak)</span>
                    </h3>
                    <p class="text-xs text-slate-500">Dokumentasi permanen laporan yang telah tuntas ditangani demi akuntabilitas dan transparansi publik.</p>
                </div>
            </div>

            <!-- Toolbar Filter & Search (Sejajar Satu Baris Rapi Persis Gambar 2) -->
            <div class="flex flex-col sm:flex-row sm:items-center gap-2 border-b border-slate-100 pb-4">
                <!-- Search Input (Flex-1 Memanjang Mengisi Ruang Tanpa Celah Kosong) -->
                <div class="relative w-full sm:flex-1 min-w-[160px]">
                    <i data-lucide="search" class="w-3.5 h-3.5 text-slate-400 absolute left-2.5 top-1/2 -translate-y-1/2 pointer-events-none"></i>
                    <input type="text" id="bupatiArchiveSearchInput" oninput="filterBupatiArchive()"
                        placeholder="Cari arsip aduan, kode, atau pelapor..."
                        class="w-full pl-8 pr-3 py-1.5 rounded-xl bg-slate-50 border border-slate-200 text-xs text-slate-800 placeholder-slate-400 focus:outline-hidden focus:ring-1 focus:ring-[#1E3A8A] focus:border-[#1E3A8A] focus:bg-white transition">
                </div>

                <!-- Filter Controls (Sejajar Satu Baris Tanpa Turun ke Bawah Persis Gambar 2) -->
                <div class="flex items-center gap-1.5 w-full sm:w-auto shrink-0 flex-nowrap overflow-x-auto pb-1 sm:pb-0">
                    <!-- Dropdown Kategori / Tipe Pelaporan -->
                    <div class="flex items-center gap-1 text-xs text-slate-500 shrink-0 font-medium whitespace-nowrap">
                        <i data-lucide="layers" class="w-3.5 h-3.5 text-[#1E3A8A]"></i>
                        <span>Filter Kategori:</span>
                    </div>
                    <select id="bupatiArchiveCategorySelect" onchange="filterBupatiArchive()" 
                        class="text-xs font-semibold rounded-xl border border-slate-200 py-1.5 px-2 bg-slate-50 text-slate-700 focus:outline-none focus:border-[#1E3A8A] focus:bg-white transition shrink-0">
                        <option value="ALL">Semua Kategori</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                        @endforeach
                    </select>

                    <!-- Dropdown Tipe Laporan (Kerusakan Ringan / Berat) -->
                    <div class="flex items-center gap-1 text-xs text-slate-500 shrink-0 font-medium whitespace-nowrap">
                        <i data-lucide="git-commit" class="w-3.5 h-3.5 text-[#1E3A8A]"></i>
                        <span>Tipe Laporan:</span>
                    </div>
                    <select id="bupatiArchiveDamageTypeSelect" onchange="filterBupatiArchive()" 
                        class="text-xs font-semibold rounded-xl border border-slate-200 py-1.5 px-2 bg-slate-50 text-slate-700 focus:outline-none focus:border-[#1E3A8A] focus:bg-white transition shrink-0">
                        <option value="ALL">Semua Tipe</option>
                        <option value="minor">Kerusakan Ringan</option>
                        <option value="major">Kerusakan Berat</option>
                    </select>

                    <!-- Dropdown Status Arsip (Selesai vs Ditolak Sesuai Gambar 2) -->
                    <div class="flex items-center gap-1 text-xs text-slate-500 shrink-0 font-medium whitespace-nowrap">
                        <i data-lucide="check-square" class="w-3.5 h-3.5 text-[#1E3A8A]"></i>
                        <span>Status:</span>
                    </div>
                    <select id="bupatiArchiveStatusSelect" onchange="filterBupatiArchive()" 
                        class="text-xs font-semibold rounded-xl border border-slate-200 py-1.5 px-2 bg-slate-50 text-slate-700 focus:outline-none focus:border-[#1E3A8A] focus:bg-white transition shrink-0">
                        <option value="ALL">Semua Status</option>
                        <option value="COMPLETED">Selesai / Tuntas</option>
                        <option value="REJECTED">Ditolak (Hoax / Palsu)</option>
                    </select>

                    <!-- Tombol Reset Filter -->
                    <button type="button" onclick="resetBupatiArchiveFilters()"
                        class="px-2.5 py-1.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs transition flex items-center gap-1 shrink-0 cursor-pointer">
                        <i data-lucide="rotate-ccw" class="w-3.5 h-3.5"></i>
                        <span>Reset</span>
                    </button>
                </div>
            </div>

            @if($archivedComplaints->isEmpty())
                <div class="py-12 text-center text-slate-400 text-xs">
                    Belum ada arsip laporan yang tuntas atau ditolak saat ini.
                </div>
            @else
                <!-- Grid Kartu Arsip (Sesuai Gaya Kartu Halaman Operator / Gambar 2) -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    @foreach($archivedComplaints as $ac)
                        @php
                            $isRejected = ($ac->status === 'REJECTED');
                            $beforePhoto = $ac->photos->where('type', 'evidence_before')->first() ?? $ac->photos->first();
                            $afterPhoto = $ac->photos->where('type', 'resolution_after')->first() ?? $ac->photos->last();
                            $beforeUrl = $beforePhoto ? ($beforePhoto->url ?? asset('storage/' . $beforePhoto->storage_key)) : null;
                            $afterUrl = $afterPhoto && $afterPhoto->id !== ($beforePhoto?->id) ? ($afterPhoto->url ?? asset('storage/' . $afterPhoto->storage_key)) : null;
                            $displayPhoto = $afterUrl ?: $beforeUrl;
                        @endphp
                        <div class="bupati-archive-card card-white rounded-2xl p-4 sm:p-5 bg-white border border-slate-200/90 hover:border-blue-300 hover:shadow-md transition-all duration-200 flex flex-col justify-between space-y-3.5"
                            data-code="{{ strtolower($ac->public_code) }}"
                            data-title="{{ strtolower($ac->title) }}"
                            data-address="{{ strtolower($ac->address ?? '') }}"
                            data-citizen="{{ strtolower($ac->citizen?->name ?? ($ac->user?->name ?? '')) }}"
                            data-category="{{ $ac->category_id }}"
                            data-damage-type="{{ $ac->damage_type ?? 'minor' }}"
                            data-status="{{ $ac->status }}">
                            
                            <div class="space-y-3">
                                <!-- 1. Header Card: 1 Baris Sejajar - Kode + Tipe + Status + Kategori (Kiri) & Waktu (Kanan) -->
                                <div class="flex items-center justify-between gap-2 pb-2.5 border-b border-slate-100 min-w-0">
                                    <div class="flex items-center gap-1.5 min-w-0 flex-nowrap overflow-hidden">
                                        <span class="px-2 py-0.5 rounded-lg bg-[#1E3A8A]/10 text-[#1E3A8A] font-bold text-xs font-mono tracking-tight border border-blue-100/60 whitespace-nowrap shrink-0">
                                            #{{ $ac->public_code }}
                                        </span>
                                        <span class="px-2 py-0.5 rounded-full text-[10.5px] font-bold bg-red-50 text-red-700 border border-red-200 inline-flex items-center gap-1 whitespace-nowrap shrink-0">
                                            <span class="w-1.5 h-1.5 rounded-full bg-red-500"></span>
                                            <span>{{ $ac->damage_type === 'major' ? 'Berat' : 'Ringan' }}</span>
                                        </span>
                                        @if($isRejected)
                                            <span class="px-2 py-0.5 rounded-full bg-red-50 text-red-700 border border-red-200 text-[10px] font-bold whitespace-nowrap shrink-0">
                                                DITOLAK
                                            </span>
                                        @endif
                                        <span class="inline-flex px-2 py-0.5 rounded-full text-[10.5px] font-bold bg-slate-100 text-slate-600 border border-slate-200/80 whitespace-nowrap truncate shrink-0 max-w-[170px]" title="{{ $ac->category?->name ?? 'Infrastruktur' }}">
                                            {{ $ac->category?->name ?? 'Infrastruktur' }}
                                        </span>
                                    </div>
                                    <span class="text-[11px] text-slate-400 font-medium whitespace-nowrap flex items-center gap-1 shrink-0">
                                        <i data-lucide="clock" class="w-3 h-3 text-slate-300"></i>
                                        <span>{{ $ac->created_at->diffForHumans() }}</span>
                                    </span>
                                </div>

                                <!-- 2. Konten Utama: 1 Foto Bukti + Rincian (Sejajar Sempurna Sesuai Gambar 2) -->
                                <div class="flex gap-4 items-stretch">
                                    <div class="relative w-24 h-24 sm:w-28 sm:h-28 rounded-2xl overflow-hidden border border-slate-200/90 bg-slate-100 shrink-0 shadow-2xs">
                                        @if($displayPhoto)
                                            <img src="{{ $displayPhoto }}" alt="Foto Aduan" class="w-full h-full object-cover">
                                            @if($afterUrl)
                                                <span class="absolute bottom-1.5 left-1.5 bg-emerald-800/90 text-white text-[9px] font-bold px-1.5 py-0.5 rounded shadow-xs">
                                                    Hasil Selesai
                                                </span>
                                            @endif
                                        @else
                                            <div class="w-full h-full flex flex-col items-center justify-center text-slate-400 text-[10px] gap-1 p-2 text-center bg-slate-50">
                                                <i data-lucide="image-off" class="w-5 h-5 text-slate-300"></i>
                                                <span>Tanpa Foto</span>
                                            </div>
                                        @endif
                                    </div>

                                    <!-- Rincian Informasi Aduan: Sejajar Sempurna dengan Foto -->
                                    <div class="flex-1 min-w-0 flex flex-col justify-between py-0.5">
                                        <!-- Baris 1: Judul & Tingkat Kerusakan (1 Baris Rapi, Sejajar Kiri-Kanan) -->
                                        <div class="flex items-center justify-between gap-2 min-w-0">
                                            <h4 class="font-outfit font-extrabold text-sm sm:text-base text-slate-900 tracking-tight leading-snug truncate" title="{{ $ac->title }}">
                                                {{ $ac->title }}
                                            </h4>
                                            @if($ac->severity_text)
                                                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-blue-50 text-[#1E3A8A] border border-blue-200/80 shrink-0 whitespace-nowrap">
                                                    {{ $ac->severity_text }}
                                                </span>
                                            @endif
                                        </div>

                                        <!-- Baris 2: Lokasi Fisik (1 Baris Rapi) -->
                                        <div class="text-xs text-slate-600 flex items-center gap-1.5 min-w-0">
                                            <i data-lucide="map-pin" class="w-3.5 h-3.5 text-[#1E3A8A] shrink-0"></i>
                                            <span class="truncate font-medium" title="{{ $ac->address }}">{{ $ac->address ?? 'Kolaka Utara' }}</span>
                                        </div>

                                        <!-- Baris 3: Info Pelapor & No Telepon (1 Baris Rapi) -->
                                        <div class="flex items-center gap-2 text-xs min-w-0">
                                            <div class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-lg bg-blue-50/90 border border-blue-100/90 text-slate-700 min-w-0 max-w-full">
                                                <i data-lucide="user" class="w-3 h-3 text-[#1E3A8A] shrink-0"></i>
                                                <span class="font-semibold text-slate-800 text-[11px] truncate">{{ $ac->citizen?->name ?? ($ac->user?->name ?? 'Warga') }}</span>
                                                @if($ac->citizen?->phone ?? $ac->user?->phone)
                                                    <span class="text-blue-300 shrink-0">•</span>
                                                    <span class="font-mono text-slate-600 font-medium text-[10.5px] shrink-0">{{ $ac->citizen?->phone ?? $ac->user?->phone }}</span>
                                                @endif
                                            </div>
                                        </div>

                                        <!-- Baris 4: Tombol Detail Lengkap & Foto (1 Baris Rapi) -->
                                        <div class="relative z-10">
                                            <button type="button" onclick="openBupatiDetailModal({{ $ac->id }})" class="text-xs font-bold text-[#1E3A8A] hover:underline inline-flex items-center gap-1 cursor-pointer">
                                                <span>Lihat Rincian Lengkap & Foto</span>
                                                <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- 3. Footer: Status Arsip / Target SLA -->
                            @if($isRejected)
                                <div class="pt-2.5 border-t border-slate-100 flex items-center justify-between gap-2 text-xs">
                                    <span class="text-red-600 text-xs font-medium flex items-center gap-1">
                                        <i data-lucide="x-circle" class="w-3.5 h-3.5"></i>
                                        <span>Laporan Ditolak</span>
                                    </span>
                                    <button type="button" onclick="openBupatiDetailModal({{ $ac->id }})" 
                                        class="py-1 px-3 rounded-lg bg-[#1E3A8A] hover:bg-[#172554] text-white font-bold text-xs transition flex items-center gap-1 cursor-pointer" 
                                        title="Lihat Detail Lengkap">
                                        <span>Rincian</span>
                                        <i data-lucide="chevron-right" class="w-3 h-3"></i>
                                    </button>
                                </div>
                            @else
                                @php
                                    $archiveDeadline = $ac->latestDisposition?->deadline 
                                        ? \Carbon\Carbon::parse($ac->latestDisposition->deadline) 
                                        : \Carbon\Carbon::parse($ac->created_at)->addDays($ac->damage_type === 'major' ? 7 : 2);
                                @endphp
                                <div class="pt-2 border-t border-slate-100">
                                    <div class="p-2.5 rounded-xl bg-blue-50/50 border border-blue-100/90 flex items-center justify-between text-[10px]">
                                        <span class="flex items-center gap-1.5 text-slate-600 font-medium">
                                            <i data-lucide="clock" class="w-3 h-3 text-[#1E3A8A] shrink-0"></i>
                                            <span>Target: <strong class="text-slate-900 font-semibold">{{ $archiveDeadline->locale('id')->isoFormat('D MMM Y') }}</strong></span>
                                        </span>
                                        <span class="px-1.5 py-0.5 rounded text-[9px] font-bold shrink-0 bg-blue-100/80 text-[#1E3A8A]">
                                            {{ $archiveDeadline->diffForHumans() }}
                                        </span>
                                    </div>
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>

                <!-- Notifikasi Filter Kosong Tab Arsip -->
                <div id="bupatiArchiveEmptyNotice" class="hidden card-white rounded-2xl p-8 text-center text-slate-400 text-xs bg-white border border-slate-200">
                    Tidak ditemukan laporan arsip yang cocok dengan filter yang dipilih.
                </div>
            @endif
        </div>
    </div>

</div>

<!-- Modal Detail Pengaduan Eksekutif -->
@include('partials.complaint_detail_modal', ['role' => 'bupati'])


@push('scripts')
<!-- Chart.js CDN -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
    const bupatiPoints = @json($mapPoints ?? []);
    let bupatiMapInstance = null;
    let bupatiActiveFilter = 'ALL';

    document.addEventListener('DOMContentLoaded', function() {
        initBupatiMap();
        initExecutiveMonitoringCharts();
    });

    // Switch Sub Menu Tab Navigation (Gambar 2)
    function switchBupatiTab(tabName) {
        document.querySelectorAll('.bupati-tab-btn').forEach(btn => {
            btn.classList.remove('bg-white', 'text-[#1E3A8A]', 'shadow-xs');
            btn.classList.add('text-slate-600', 'hover:text-slate-900', 'hover:bg-white/60');
        });

        const activeBtn = document.getElementById('tabBtn-' + tabName);
        if (activeBtn) {
            activeBtn.classList.remove('text-slate-600', 'hover:text-slate-900', 'hover:bg-white/60');
            activeBtn.classList.add('bg-white', 'text-[#1E3A8A]', 'shadow-xs');
        }

        document.getElementById('tabContent-executive').classList.add('hidden');
        document.getElementById('tabContent-complaints').classList.add('hidden');
        document.getElementById('tabContent-archive').classList.add('hidden');

        document.getElementById('tabContent-' + tabName).classList.remove('hidden');

        if (tabName === 'executive' && bupatiMapInstance) {
            setTimeout(() => {
                bupatiMapInstance.invalidateSize();
            }, 100);
        }
    }

    // Fungsi Fokus Wilayah Kecamatan di Peta Eksekutif
    function focusDistrictOnMap(lat, lon, name) {
        if (bupatiMapInstance && !isNaN(lat) && !isNaN(lon)) {
            bupatiMapInstance.flyTo([lat, lon], 13, { duration: 1.2 });
            const mapCard = document.getElementById('bupatiMap');
            if (mapCard) {
                mapCard.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
        }
    }

    // Inisialisasi Peta Leaflet Eksekutif (Kabupaten Kolaka Utara)
    function initBupatiMap() {
        const mapEl = document.getElementById('bupatiMap');
        if (!mapEl) return;

        bupatiMapInstance = L.map('bupatiMap', { scrollWheelZoom: true }).setView([-3.50601, 120.88780], 12);
        L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '© OpenStreetMap | GIS Kolaka Utara'
        }).addTo(bupatiMapInstance);

        // Batas Administratif Resmi Wilayah Kabupaten Kolaka Utara (OpenStreetMap GeoJSON Polygon)
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
                        fillOpacity: 0.08
                    }
                }).addTo(bupatiMapInstance);
            })
            .catch(err => console.log('Gagal memuat polygon batas Kolaka Utara:', err));

        if (bupatiPoints && bupatiPoints.length > 0) {
            const bounds = [];
            bupatiPoints.forEach(p => {
                const lat = parseFloat(p.latitude);
                const lng = parseFloat(p.longitude);
                if (!isNaN(lat) && !isNaN(lng)) {
                    let color = '#EF4444';
                    let statusText = 'VERIFIKASI';
                    let statusBg = '#FEF2F2';
                    let statusColor = '#991B1B';
                    let statusBorder = '#FECACA';
                    let iconSvg = `
                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="#FFFFFF" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="12" cy="12" r="10"/>
                            <line x1="12" y1="8" x2="12" y2="12"/>
                            <line x1="12" y1="16" x2="12.01" y2="16"/>
                        </svg>
                    `;

                    if (p.status === 'COMPLETED' || p.status === 'CLOSED') {
                        color = '#10B981';
                        statusText = 'SELESAI';
                        statusBg = '#ECFDF5';
                        statusColor = '#065F46';
                        statusBorder = '#A7F3D0';
                        iconSvg = `
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="#FFFFFF" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                <polyline points="20 6 9 17 4 12"/>
                            </svg>
                        `;
                    } else if (p.status === 'REJECTED') {
                        color = '#DC2626';
                        statusText = 'DITOLAK';
                        statusBg = '#FEF2F2';
                        statusColor = '#991B1B';
                        statusBorder = '#FECACA';
                        iconSvg = `
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="#FFFFFF" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                <line x1="18" y1="6" x2="6" y2="18"/>
                                <line x1="6" y1="6" x2="18" y2="18"/>
                            </svg>
                        `;
                    } else if (p.status === 'IN_PROGRESS' || p.status === 'REVISION_REQUIRED') {
                        color = '#F59E0B';
                        statusText = 'DIKERJAKAN';
                        statusBg = '#FEF3C7';
                        statusColor = '#92400E';
                        statusBorder = '#FDE68A';
                        iconSvg = `
                            <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="#FFFFFF" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z"/>
                            </svg>
                        `;
                    } else if (p.status === 'DISPATCHED') {
                        color = '#1E40AF';
                        statusText = 'DITUGASKAN';
                        statusBg = '#EEF2FF';
                        statusColor = '#1E3A8A';
                        statusBorder = '#C7D2FE';
                        iconSvg = `
                            <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="#FFFFFF" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                <line x1="22" y1="2" x2="11" y2="13"/>
                                <polygon points="22 2 15 22 11 13 2 9 22 2"/>
                            </svg>
                        `;
                    } else if (p.status === 'WAITING_VERIFICATION') {
                        color = '#F59E0B';
                        statusText = 'VALIDASI';
                        statusBg = '#FEF3C7';
                        statusColor = '#92400E';
                        statusBorder = '#FDE68A';
                        iconSvg = `
                            <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="#FFFFFF" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z"/>
                            </svg>
                        `;
                    }

                    // Circular Badge Pin Marker Persis Gambar 5
                    const customPinIcon = L.divIcon({
                        className: 'custom-pin',
                        html: `
                            <div style="
                                background-color: ${color};
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

                    const marker = L.marker([lat, lng], { icon: customPinIcon }).addTo(bupatiMapInstance);

                    // Render Polyline untuk Kerusakan Ruas Panjang / Parah (Major)
                    if (p.damage_type === 'major' && p.end_latitude && p.end_longitude) {
                        let lineCoords = [[lat, lng], [p.end_latitude, p.end_longitude]];
                        if (Array.isArray(p.route_coordinates) && p.route_coordinates.length > 1) {
                            lineCoords = p.route_coordinates;
                        }
                        const polyline = L.polyline(lineCoords, {
                            color: color,
                            weight: 6,
                            opacity: 0.85,
                            dashArray: '6, 8',
                            lineCap: 'round'
                        }).addTo(bupatiMapInstance);

                        polyline.bindTooltip(`Ruas Jalan: ${p.road_length || 'Panjang'} • ${statusText}`, {
                            direction: 'top',
                            className: 'custom-map-tooltip'
                        });

                        bounds.push([p.end_latitude, p.end_longitude]);
                    }

                    // POPUP CARD PERSIS SAMA DENGAN OPERATOR
                    const photoHtml = p.photo_url 
                        ? `<div style="position:relative;width:100%;height:125px;background:#0F172A;overflow:hidden;">
                               <img src="${p.photo_url}" alt="Foto Bukti" style="width:100%;height:100%;object-fit:cover;display:block;"
                                    onload="if(marker && marker.getPopup()) marker.getPopup().update();">
                               <div style="position:absolute;bottom:8px;left:8px;z-index:2;">
                                   <span style="background:rgba(15,23,42,0.85);backdrop-filter:blur(4px);color:#FFFFFF;font-size:10px;font-weight:700;padding:2px 8px;border-radius:6px;border:1px solid rgba(255,255,255,0.25);">
                                       ${p.category_name}
                                   </span>
                               </div>
                           </div>`
                        : `<div style="position:relative;width:100%;height:44px;background:#F8FAFC;border-bottom:1px solid #E2E8F0;display:flex;align-items:center;justify-content:space-between;padding:0 12px;">
                               <span style="font-size:10px;font-weight:700;color:#64748B;background:#E2E8F0;padding:2px 8px;border-radius:6px;">
                                   ${p.category_name}
                               </span>
                               <span style="font-size:10px;color:#94A3B8;">Foto tidak dilampirkan</span>
                           </div>`;

                    const popupHtml = `
                        <div style="width:280px;background:#FFFFFF;border-radius:16px;overflow:hidden;font-family:'Outfit',system-ui,sans-serif;box-sizing:border-box;">
                            ${photoHtml}
                            <div style="padding:12px 14px 14px 14px;background:#FFFFFF;">
                                <div style="display:flex;align-items:center;justify-content:space-between;gap:8px;margin-bottom:6px;">
                                    <span style="font-size:11px;font-weight:800;color:#1E3A8A;background:#EFF6FF;border:1px solid #DBEAFE;padding:2px 8px;border-radius:6px;font-family:monospace;">
                                        #${p.public_code}
                                    </span>
                                    <span style="font-size:10px;font-weight:700;background:${statusBg};color:${statusColor};border:1px solid ${statusBorder};padding:2px 8px;border-radius:999px;white-space:nowrap;">
                                        ${statusText}
                                    </span>
                                </div>
                                <div style="margin-bottom:6px;">
                                    <span style="font-size:10px;font-weight:700;color:#B91C1C;background:#FEF2F2;border:1px solid #FECACA;padding:2px 8px;border-radius:999px;display:inline-flex;align-items:center;gap:4px;">
                                        <span style="width:6px;height:6px;border-radius:999px;background:#EF4444;display:inline-block;"></span>
                                        ${p.damage_type === 'major' ? ('Kerusakan Berat (' + (p.road_length ? (String(p.road_length).toLowerCase().includes('m') ? p.road_length : p.road_length + 'm') : '≥ 500m') + ')') : 'Kerusakan Ringan'}
                                    </span>
                                </div>
                                <h4 style="font-size:13px;font-weight:700;color:#0F172A;line-height:1.35;margin:0 0 4px 0;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden;">
                                    ${p.title}
                                </h4>
                                <div style="font-size:11px;color:#64748B;display:flex;align-items:flex-start;gap:4px;margin-bottom:8px;line-height:1.3;">
                                    <span style="flex-shrink:0;color:#EF4444;font-size:12px;">📍</span>
                                    <span style="display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden;">${p.address}</span>
                                </div>
                                <div style="font-size:10.5px;color:#64748B;border-top:1px solid #F1F5F9;padding-top:6px;margin-bottom:10px;display:flex;justify-content:space-between;align-items:center;">
                                    <span>Pelapor: <strong style="color:#1E293B;">${p.reporter_name}</strong></span>
                                    <span style="color:#94A3B8;">${p.created_at_human}</span>
                                </div>
                                <button type="button" onclick="openBupatiDetailModal(${p.id})" 
                                    style="width:100%;background:#1E3A8A;color:#FFFFFF;border:none;padding:8px 12px;border-radius:10px;font-size:11.5px;font-weight:700;cursor:pointer;display:flex;align-items:center;justify-content:center;gap:6px;box-shadow:0 2px 5px rgba(30,58,138,0.25);transition:background 0.15s;"
                                    onmouseover="this.style.background='#172554'" onmouseout="this.style.background='#1E3A8A'">
                                    <span>Buka Detail Lengkap</span>
                                    <span>&rarr;</span>
                                </button>
                            </div>
                        </div>
                    `;

                    marker.bindPopup(popupHtml, { minWidth: 280, maxWidth: 280, autoPan: true, autoPanPadding: [20, 20] });
                    bounds.push([lat, lng]);
                }
            });

            if (bounds.length > 0) {
                bupatiMapInstance.fitBounds(bounds, { padding: [35, 35] });
            }
        }
    }



    // Filter & Pencarian Tabel Bupati (Sesuai Gambar 5)
    function filterBupatiTable() {
        const query = (document.getElementById('bupatiSearchInput')?.value || '').toLowerCase().trim();
        const selectedCategory = document.getElementById('bupatiCategorySelect')?.value || 'ALL';
        const selectedDamageType = document.getElementById('bupatiDamageTypeSelect')?.value || 'ALL';
        const selectedStatus = document.getElementById('bupatiStatusSelect')?.value || 'ALL';
        const rows = document.querySelectorAll('.bupati-complaint-row');

        let visibleCount = 0;
        rows.forEach(row => {
            const code = row.getAttribute('data-code') || '';
            const title = row.getAttribute('data-title') || '';
            const reporter = row.getAttribute('data-reporter') || '';
            const address = row.getAttribute('data-address') || '';
            const status = row.getAttribute('data-status') || '';
            const categoryId = row.getAttribute('data-category') || '';
            const damageType = row.getAttribute('data-damage-type') || 'minor';

            const matchQuery = !query || code.includes(query) || title.includes(query) || reporter.includes(query) || address.includes(query);
            const matchCategory = (selectedCategory === 'ALL' || categoryId === selectedCategory);
            const matchDamageType = (selectedDamageType === 'ALL' || damageType === selectedDamageType);
            
            let matchStatus = true;
            if (selectedStatus !== 'ALL') {
                if (selectedStatus === 'IN_PROGRESS') {
                    matchStatus = (status === 'IN_PROGRESS');
                } else if (selectedStatus === 'DISPATCHED') {
                    matchStatus = (status === 'DISPATCHED');
                } else if (selectedStatus === 'COMPLETED') {
                    matchStatus = (status === 'COMPLETED' || status === 'CLOSED');
                } else if (selectedStatus === 'SUBMITTED') {
                    matchStatus = (status === 'SUBMITTED' || status === 'VERIFICATION' || status === 'VERIFIED');
                } else {
                    matchStatus = (status === selectedStatus);
                }
            }

            const isVisible = (matchQuery && matchCategory && matchDamageType && matchStatus);
            row.style.display = isVisible ? '' : 'none';
            if (isVisible) visibleCount++;
        });

        const countBadge = document.getElementById('bupatiCountBadge');
        if (countBadge) {
            countBadge.innerText = visibleCount + ' Laporan';
        }

        const emptyRow = document.getElementById('bupatiEmptyFilterRow');
        if (emptyRow) {
            emptyRow.classList.toggle('hidden', visibleCount > 0);
        }
    }

    // Filter & Pencarian Arsip Bupati (Sesuai Gambar 2)
    function filterBupatiArchive() {
        const query = (document.getElementById('bupatiArchiveSearchInput')?.value || '').toLowerCase().trim();
        const selectedCategory = document.getElementById('bupatiArchiveCategorySelect')?.value || 'ALL';
        const selectedDamageType = document.getElementById('bupatiArchiveDamageTypeSelect')?.value || 'ALL';
        const selectedStatus = document.getElementById('bupatiArchiveStatusSelect')?.value || 'ALL';
        const cards = document.querySelectorAll('.bupati-archive-card');

        let visibleCount = 0;
        cards.forEach(card => {
            const code = card.getAttribute('data-code') || '';
            const title = card.getAttribute('data-title') || '';
            const address = card.getAttribute('data-address') || '';
            const citizen = card.getAttribute('data-citizen') || '';
            const category = card.getAttribute('data-category') || '';
            const damageType = card.getAttribute('data-damage-type') || 'minor';
            const status = card.getAttribute('data-status') || '';

            const matchQuery = !query || code.includes(query) || title.includes(query) || address.includes(query) || citizen.includes(query);
            const matchCategory = (selectedCategory === 'ALL' || category === selectedCategory);
            const matchDamageType = (selectedDamageType === 'ALL' || damageType === selectedDamageType);
            
            let matchStatus = true;
            if (selectedStatus !== 'ALL') {
                matchStatus = (status === selectedStatus);
            }

            const isVisible = (matchQuery && matchCategory && matchDamageType && matchStatus);
            card.style.display = isVisible ? '' : 'none';
            if (isVisible) visibleCount++;
        });

        const countBadge = document.getElementById('bupatiArchiveCountBadge');
        if (countBadge) {
            countBadge.innerText = visibleCount + ' Laporan';
        }
        const emptyNotice = document.getElementById('bupatiArchiveEmptyNotice');
        if (emptyNotice) {
            emptyNotice.classList.toggle('hidden', visibleCount > 0);
        }
    }

    function resetBupatiFilters() {
        const q = document.getElementById('bupatiSearchInput');
        const cat = document.getElementById('bupatiCategorySelect');
        const dt = document.getElementById('bupatiDamageTypeSelect');
        const st = document.getElementById('bupatiStatusSelect');
        if (q) q.value = '';
        if (cat) cat.value = 'ALL';
        if (dt) dt.value = 'ALL';
        if (st) st.value = 'ALL';
        filterBupatiTable();
    }

    function resetBupatiArchiveFilters() {
        const q = document.getElementById('bupatiArchiveSearchInput');
        const cat = document.getElementById('bupatiArchiveCategorySelect');
        const dt = document.getElementById('bupatiArchiveDamageTypeSelect');
        const st = document.getElementById('bupatiArchiveStatusSelect');
        if (q) q.value = '';
        if (cat) cat.value = 'ALL';
        if (dt) dt.value = 'ALL';
        if (st) st.value = 'ALL';
        filterBupatiArchive();
    }

    // Inisialisasi Data Pengaduan Bupati untuk Modal Terpusat
    window.bupatiComplaintsMap = @json($complaintsDetailMap ?? []);

    function openBupatiDetailModal(complaintId) {
        if (window.openSharedDetailModal) {
            window.openSharedDetailModal(complaintId, 'bupati');
        }
    }

    function closeBupatiDetailModal() {
        if (window.closeSharedDetailModal) {
            window.closeSharedDetailModal('bupati');
        }
    }

    function _legacy_openBupatiDetailModal(complaintId) {
        const item = bupatiComplaintsMap[complaintId];
        if (!item) return;

        // 1. Kode & Kategori
        document.getElementById('bmCode').innerText = '#' + item.public_code;
        document.getElementById('bmCategory').innerText = item.category_name || 'Infrastruktur';
        
        // 2. Status Badge Harmonized
        let statusBadge = item.status;
        let statusBg = 'bg-slate-100 text-slate-700 border-slate-200';
        if (item.status === 'COMPLETED' || item.status === 'CLOSED') {
            statusBadge = 'SELESAI';
            statusBg = 'bg-emerald-50 text-[#10B981] border-emerald-200';
        } else if (item.status === 'WAITING_VERIFICATION') {
            statusBadge = 'VALIDASI';
            statusBg = 'bg-amber-50 text-amber-700 border-amber-300';
        } else if (item.status === 'IN_PROGRESS') {
            statusBadge = 'DIKERJAKAN';
            statusBg = 'bg-amber-50 text-amber-700 border-amber-300';
        } else if (item.status === 'DISPATCHED') {
            statusBadge = 'DITUGASKAN';
            statusBg = 'bg-indigo-50 text-indigo-700 border-indigo-200';
        } else if (item.status === 'REJECTED') {
            statusBadge = 'DITOLAK';
            statusBg = 'bg-red-50 text-[#DC2626] border-red-200';
        } else {
            statusBadge = 'VERIFIKASI';
            statusBg = 'bg-red-50 text-[#EF4444] border-red-200';
        }

        const stEl = document.getElementById('bmStatus');
        stEl.innerText = statusBadge;
        stEl.className = `shrink-0 px-2 sm:px-2.5 py-0.5 sm:py-1 rounded-full text-[10.5px] sm:text-xs font-bold border whitespace-nowrap ${statusBg}`;

        // 3. Tipe Kerusakan Badge (Merah Konsisten)
        const typeBadge = document.getElementById('bmType');
        if (typeBadge) {
            typeBadge.className = 'shrink-0 px-2 sm:px-2.5 py-0.5 sm:py-1 rounded-full text-[10.5px] sm:text-xs font-bold bg-red-50 text-red-700 border border-red-200 inline-flex items-center gap-1 sm:gap-1.5 whitespace-nowrap';
            typeBadge.innerHTML = `<span class="w-1.5 h-1.5 rounded-full bg-red-500 shrink-0"></span><span>${item.damage_type === 'major' ? 'Kerusakan Berat' : 'Kerusakan Ringan'}</span>`;
        }

        // 4. Judul, Tanggal, & Koordinat
        document.getElementById('bmTitle').innerText = item.title || 'Laporan Pengaduan';
        document.getElementById('bmCreated').innerText = item.created_at_full || item.created_at_formatted || '-';
        document.getElementById('bmCoords').innerText = (item.latitude && item.longitude) ? `${item.latitude}, ${item.longitude}` : 'Tidak tercatat';
        document.getElementById('bmDescription').innerText = item.description || 'Tidak ada keterangan tambahan.';

        // 5. Uraian Wilayah Administrasi 4-Kotak
        const adm = parseAdministrativeAddress(item.address);
        const bmRoadEl = document.getElementById('bmRoad');
        const bmVillageEl = document.getElementById('bmVillage');
        const bmDistrictEl = document.getElementById('bmDistrict');
        const bmRegencyEl = document.getElementById('bmRegency');
        if (bmRoadEl) bmRoadEl.innerText = adm.road;
        if (bmVillageEl) bmVillageEl.innerText = adm.village;
        if (bmDistrictEl) bmDistrictEl.innerText = adm.district;
        if (bmRegencyEl) bmRegencyEl.innerText = adm.regency;

        const addressEl = document.getElementById('bmAddress');
        if (addressEl) addressEl.innerText = item.address || 'Alamat lokasi tidak tercatat';

        // 6. Identitas Pelapor Aduan
        const citizenNameEl = document.getElementById('bmReporter');
        const citizenPhoneEl = document.getElementById('bmPhone');
        const citizenName = item.citizen_name || item.reporter_name || 'Warga Terdaftar';
        const citizenPhone = item.citizen_phone || item.reporter_phone || '-';
        if (citizenNameEl) citizenNameEl.innerText = citizenName;
        if (citizenPhoneEl) {
            citizenPhoneEl.innerText = citizenPhone;
            if (citizenPhone && citizenPhone !== '-') {
                citizenPhoneEl.href = 'tel:' + citizenPhone;
            } else {
                citizenPhoneEl.removeAttribute('href');
            }
        }

        // 7. Regu Penanganan Lapangan (Sesuai Permintaan: Menampilkan Regu, Ketua Regu, & Nomor HP)
        const officerCard = document.getElementById('bmOfficerCard');
        const teamNameEl = document.getElementById('bmTeamName');
        const officerDeptEl = document.getElementById('bmOfficerDept');
        const teamLeaderEl = document.getElementById('bmTeamLeader');
        const teamPhoneLink = document.getElementById('bmTeamPhone');
        const teamPhoneText = document.getElementById('bmTeamPhoneText');

        if (officerCard) {
            const isHandled = ['IN_PROGRESS', 'DISPATCHED', 'WAITING_VERIFICATION', 'COMPLETED', 'CLOSED'].includes(item.status);
            const hasDisposition = item.team_name || item.officer_name || item.assignee_name || item.department_name || item.instruction;
            if (isHandled && hasDisposition) {
                officerCard.classList.remove('hidden');

                // Tampilkan Nama Regu & Dinas
                const teamDisplayName = item.team_name 
                    ? (item.team_code ? `[${item.team_code}] ${item.team_name}` : item.team_name)
                    : (item.officer_name ? `Regu Petugas (${item.officer_name})` : 'Klaim Mandiri (Terbuka)');

                if (teamNameEl) teamNameEl.innerText = teamDisplayName;
                if (officerDeptEl) officerDeptEl.innerText = item.department_name || 'Dinas Pekerjaan Umum dan Penataan Ruang (PUPR)';

                // Ketua Regu & Nomor Telepon
                const showOfficerIdentity = ['IN_PROGRESS', 'WAITING_VERIFICATION', 'COMPLETED', 'CLOSED'].includes(item.status);
                const leaderName = item.team_leader || item.officer_name || item.assignee_name || 'Petugas Lapangan';
                const leaderPhone = item.team_leader_phone || item.officer_phone || item.assignee_phone || '-';

                if (teamLeaderEl) {
                    teamLeaderEl.innerText = showOfficerIdentity ? leaderName : 'Menunggu Konfirmasi Regu';
                }

                if (teamPhoneLink && teamPhoneText) {
                    if (showOfficerIdentity && leaderPhone && leaderPhone !== '-') {
                        teamPhoneText.innerText = leaderPhone;
                        teamPhoneLink.href = 'tel:' + leaderPhone;
                        teamPhoneLink.classList.remove('hidden');
                    } else {
                        teamPhoneText.innerText = '-';
                        teamPhoneLink.removeAttribute('href');
                        if (!showOfficerIdentity) {
                            teamPhoneLink.classList.add('hidden');
                        } else {
                            teamPhoneLink.classList.remove('hidden');
                        }
                    }
                }

                // Legacy fallback elements
                const assigneeEl = document.getElementById('bmAssignee');
                if (assigneeEl) assigneeEl.innerText = leaderName;
                const deptEl = document.getElementById('bmDepartment');
                if (deptEl) deptEl.innerText = item.department_name || 'Dinas Terkait';
                const officerPhoneEl = document.getElementById('bmOfficerPhone');
                if (officerPhoneEl) officerPhoneEl.innerText = leaderPhone;

                const deadlineBox = document.getElementById('bmDeadlineBox');
                if (deadlineBox) {
                    if (item.deadline) {
                        deadlineBox.classList.remove('hidden');
                        document.getElementById('bmDeadline').innerText = item.deadline;
                    } else {
                        deadlineBox.classList.add('hidden');
                    }
                }
                const instructionRow = document.getElementById('bmInstructionRow');
                if (instructionRow) {
                    if (item.instruction && item.instruction !== '-') {
                        instructionRow.classList.remove('hidden');
                        document.getElementById('bmInstruction').innerText = item.instruction;
                    } else {
                        instructionRow.classList.add('hidden');
                    }
                }
            } else {
                officerCard.classList.add('hidden');
            }
        }

        // 8. Foto Bukti Kerusakan Awal (Warga) & Galeri
        const photoBefore = document.getElementById('bmPhotoBefore');
        const photoBeforeContainer = document.getElementById('bmPhotoBeforeContainer');
        const gallery = document.getElementById('bmPhotoGallery');
        const photoLabel = document.getElementById('bmPhotoBeforeLabel');

        const citizenPhotos = (item.citizen_photos && item.citizen_photos.length > 0)
            ? item.citizen_photos
            : (item.photo_url ? [item.photo_url] : []);

        if (citizenPhotos.length > 0) {
            photoBefore.src = citizenPhotos[0];
            photoBeforeContainer.classList.remove('hidden');
            if (photoLabel) photoLabel.innerText = 'Foto Bukti Kerusakan Jalan';
        } else {
            photoBeforeContainer.classList.add('hidden');
        }

        if (gallery) {
            gallery.innerHTML = '';
            if (citizenPhotos.length > 1) {
                gallery.classList.remove('hidden');
                citizenPhotos.forEach((url, idx) => {
                    const thumb = document.createElement('img');
                    thumb.src = url;
                    thumb.className = `w-14 h-14 rounded-xl object-cover cursor-pointer border-2 transition ${idx === 0 ? 'border-[#1E3A8A] ring-2 ring-blue-200' : 'border-slate-200 opacity-70 hover:opacity-100'}`;
                    thumb.onclick = () => {
                        photoBefore.src = url;
                        if (photoLabel) photoLabel.innerText = `Foto Bukti Kerusakan ${idx + 1} dari ${citizenPhotos.length}`;
                        gallery.querySelectorAll('img').forEach(img => {
                            img.className = 'w-14 h-14 rounded-xl object-cover cursor-pointer border-2 border-slate-200 opacity-70 hover:opacity-100 transition';
                        });
                        thumb.className = 'w-14 h-14 rounded-xl object-cover cursor-pointer border-2 border-[#1E3A8A] ring-2 ring-blue-200 transition';
                    };
                    gallery.appendChild(thumb);
                });
            } else {
                gallery.classList.add('hidden');
            }
        }

        // 8b. Foto Bukti Penanganan Petugas Lapangan & Galeri
        const photoAfter = document.getElementById('bmPhotoAfter');
        const photoAfterContainer = document.getElementById('bmPhotoAfterContainer');
        const officerGallery = document.getElementById('bmPhotoOfficerGallery');
        const photoAfterLabel = document.getElementById('bmPhotoAfterLabel');

        const officerPhotos = (item.officer_photos && item.officer_photos.length > 0)
            ? item.officer_photos
            : (item.completion_photo_url ? [item.completion_photo_url] : (item.photo_after_url ? [item.photo_after_url] : []));

        if (officerPhotos.length > 0) {
            photoAfter.src = officerPhotos[0];
            photoAfterContainer.classList.remove('hidden');
            if (photoAfterLabel) photoAfterLabel.innerText = 'Foto Bukti Penyelesaian Fisik';
        } else {
            photoAfterContainer.classList.add('hidden');
        }

        if (officerGallery) {
            officerGallery.innerHTML = '';
            if (officerPhotos.length > 1) {
                officerGallery.classList.remove('hidden');
                officerPhotos.forEach((url, idx) => {
                    const thumb = document.createElement('img');
                    thumb.src = url;
                    thumb.className = `w-14 h-14 rounded-xl object-cover cursor-pointer border-2 transition ${idx === 0 ? 'border-emerald-600 ring-2 ring-emerald-200' : 'border-slate-200 opacity-70 hover:opacity-100'}`;
                    thumb.onclick = () => {
                        photoAfter.src = url;
                        if (photoAfterLabel) photoAfterLabel.innerText = `Foto Hasil Petugas ${idx + 1} dari ${officerPhotos.length}`;
                        officerGallery.querySelectorAll('img').forEach(img => {
                            img.className = 'w-14 h-14 rounded-xl object-cover cursor-pointer border-2 border-slate-200 opacity-70 hover:opacity-100 transition';
                        });
                        thumb.className = 'w-14 h-14 rounded-xl object-cover cursor-pointer border-2 border-emerald-600 ring-2 ring-emerald-200 transition';
                    };
                    officerGallery.appendChild(thumb);
                });
            } else {
                officerGallery.classList.add('hidden');
            }
        }

        // 9. Ruas Panjang Box
        const majorBox = document.getElementById('bmMajorRoadBox');
        if (majorBox) {
            if (item.damage_type === 'major' || item.road_length) {
                majorBox.classList.remove('hidden');
                document.getElementById('bmRoadLength').innerText = item.road_length || '≥ 500 Meter';
                document.getElementById('bmRoadCondition').innerText = item.road_condition || 'Jalan Tanah / Rusak';
                document.getElementById('bmStartCoords').innerText = `${item.latitude}, ${item.longitude}`;
                document.getElementById('bmEndCoords').innerText = (item.end_latitude && item.end_longitude) ? `${item.end_latitude}, ${item.end_longitude}` : 'Tidak ditentukan';
            } else {
                majorBox.classList.add('hidden');
            }
        }

        // Tampilkan Modal (Zero Lag)
        const modalEl = document.getElementById('bupatiDetailModal');
        const scrollBox = modalEl.querySelector('.overflow-y-auto');
        if (scrollBox) scrollBox.scrollTop = 0;
        modalEl.classList.remove('hidden');
        document.body.style.overflow = 'hidden';
        if (window.lucide) {
            lucide.createIcons({ root: modalEl });
        }
    }

    function closeBupatiDetailModal() {
        const modalEl = document.getElementById('bupatiDetailModal');
        if (modalEl) modalEl.classList.add('hidden');
        document.body.style.overflow = '';
    }

    // Close on click outside or Escape
    window.addEventListener('click', function(e) {
        const modalEl = document.getElementById('bupatiDetailModal');
        if (e.target === modalEl) {
            closeBupatiDetailModal();
        }
    });

    window.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeBupatiDetailModal();
        }
    });
</script>

{{-- Script JS Modal Detail Pengaduan Terpusat --}}
@include('partials.complaint_detail_modal_js')

{{-- Script JS Monitoring Eksekutif & Pelaporan Resmi --}}
@include('partials.executive_monitoring_reporting_js')
@endpush
@endsection
