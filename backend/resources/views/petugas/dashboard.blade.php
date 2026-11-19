@extends('layouts.app')

@section('title', 'Pusat Kendali Pekerja Lapangan - Sistem Pengaduan')
@section('header_title', 'Pusat Kendali Pekerja Lapangan')

@section('content')
@php
    $todayStr = \Carbon\Carbon::now('Asia/Makassar')->locale('id')->isoFormat('dddd, D MMMM Y • HH:mm:ss') . ' WITA';
@endphp

<div class="py-4 sm:py-6 space-y-5">

    <!-- Header Pekerja Lapangan (Seragam Desain Admin) -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pt-1">
        <div>
            <h2 class="font-outfit font-bold text-lg sm:text-xl text-[#0F172A] leading-tight">
                Pusat Penugasan &amp; Pengerjaan Lapangan
            </h2>
        </div>
        <div class="flex items-center gap-2 shrink-0">
            <span class="px-3.5 py-2 rounded-lg bg-white text-[#0F172A] text-xs font-semibold border border-[#E2E8F0] flex items-center gap-2">
                <i data-lucide="clock" class="w-4 h-4 text-[#2563EB]"></i>
                <span class="live-wita-clock">{{ $todayStr }}</span>
            </span>
        </div>
    </div>

    @if(isset($myTeam) && $myTeam)
        <!-- BANNER IDENTITAS REGU PETUGAS (Flat Solid Navy, Tanpa Gradient/Glassmorphism) -->
        <div class="p-4 sm:p-5 rounded-xl bg-[#2563EB] border border-[#1D4ED8] text-white flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div class="flex items-center gap-3.5">
                <div class="w-11 h-11 rounded-lg bg-[#1D4ED8] border border-blue-800 flex items-center justify-center shrink-0">
                    <i data-lucide="shield" class="w-5 h-5 text-amber-300"></i>
                </div>
                <div>
                    <div class="flex items-center gap-2 flex-wrap">
                        <span class="px-2 py-0.5 rounded bg-amber-400 text-[#0F172A] font-mono text-[10px] font-bold">
                            {{ $myTeam->code }}
                        </span>
                        <h3 class="font-outfit font-bold text-base sm:text-lg leading-tight">
                            {{ $myTeam->name }}
                        </h3>
                    </div>
                    <p class="text-xs text-blue-100 mt-1 flex items-center gap-2 flex-wrap">
                        <span>Wilayah: <strong>{{ $myTeam->coverage_area ?? 'Kecamatan Terkait' }}</strong></span>
                        <span>•</span>
                        <span>{{ $myTeam->members->count() }} Personil</span>
                        <span>•</span>
                        <span>Ketua Regu: <strong>{{ $myTeam->leader?->name ?? '-' }}</strong></span>
                    </p>
                </div>
            </div>
            <div class="flex items-center gap-2 shrink-0">
                <span class="px-3 py-1.5 rounded-lg bg-[#1D4ED8] border border-blue-800 text-xs font-semibold text-amber-200">
                    {{ $myTeam->leader_id === auth()->id() ? 'Ketua Regu (Koordinator)' : 'Anggota Regu' }}
                </span>
            </div>
        </div>
    @endif

    <!-- Ringkasan KPI Pekerja Lapangan (Flat Bordered Seragam Admin) -->
    <div class="grid grid-cols-3 sm:grid-cols-4 lg:grid-cols-7 gap-2 sm:gap-3">
        <!-- 1. Total Aduan -->
        <div class="p-2.5 sm:p-3.5 rounded-xl bg-white border border-[#E2E8F0] flex flex-col justify-between min-w-0">
            <div class="flex items-center justify-between gap-1.5">
                <span class="text-[8.5px] sm:text-[11px] text-[#475569] font-bold uppercase tracking-normal sm:tracking-wider leading-tight">Total Aduan</span>
                <div class="w-6 h-6 sm:w-7 sm:h-7 rounded-lg bg-blue-50 text-[#2563EB] border border-blue-200 flex items-center justify-center shrink-0">
                    <i data-lucide="inbox" class="w-3 h-3 sm:w-3.5 sm:h-3.5"></i>
                </div>
            </div>
            <div class="mt-1.5 sm:mt-2">
                <div class="font-outfit text-xl sm:text-2xl font-bold text-[#0F172A] leading-tight">
                    {{ $summary['total'] }}
                </div>
                <span class="hidden sm:block text-[11px] text-[#475569] font-medium mt-0.5">Laporan masuk</span>
            </div>
        </div>

        <!-- 2. Menunggu -->
        <div class="p-2.5 sm:p-3.5 rounded-xl bg-white border border-[#E2E8F0] flex flex-col justify-between min-w-0">
            <div class="flex items-center justify-between gap-1.5">
                <span class="text-[8.5px] sm:text-[11px] text-[#475569] font-bold uppercase tracking-normal sm:tracking-wider leading-tight">Menunggu</span>
                <div class="w-6 h-6 sm:w-7 sm:h-7 rounded-lg bg-red-50 text-[#EF4444] border border-red-200 flex items-center justify-center shrink-0">
                    <i data-lucide="clock" class="w-3 h-3 sm:w-3.5 sm:h-3.5"></i>
                </div>
            </div>
            <div class="mt-1.5 sm:mt-2">
                <div class="font-outfit text-xl sm:text-2xl font-bold text-[#0F172A] leading-tight">
                    {{ $summary['pending'] }}
                </div>
                <span class="hidden sm:block text-[11px] text-[#475569] font-medium mt-0.5">Perlu verifikasi</span>
            </div>
        </div>

        <!-- 3. Ditugaskan -->
        <div class="p-2.5 sm:p-3.5 rounded-xl bg-white border border-[#E2E8F0] flex flex-col justify-between min-w-0">
            <div class="flex items-center justify-between gap-1.5">
                <span class="text-[8.5px] sm:text-[11px] text-[#475569] font-bold uppercase tracking-normal sm:tracking-wider leading-tight">Ditugaskan</span>
                <div class="w-6 h-6 sm:w-7 sm:h-7 rounded-lg bg-blue-50 text-[#2563EB] border border-blue-200 flex items-center justify-center shrink-0">
                    <i data-lucide="send" class="w-3 h-3 sm:w-3.5 sm:h-3.5"></i>
                </div>
            </div>
            <div class="mt-1.5 sm:mt-2">
                <div class="font-outfit text-xl sm:text-2xl font-bold text-[#0F172A] leading-tight">
                    {{ $summary['dispatched'] }}
                </div>
                <span class="hidden sm:block text-[11px] text-[#475569] font-medium mt-0.5">Siap dikerjakan</span>
            </div>
        </div>

        <!-- 4. Dikerjakan -->
        <div class="p-2.5 sm:p-3.5 rounded-xl bg-white border border-[#E2E8F0] flex flex-col justify-between min-w-0">
            <div class="flex items-center justify-between gap-1.5">
                <span class="text-[8.5px] sm:text-[11px] text-[#475569] font-bold uppercase tracking-normal sm:tracking-wider leading-tight">Dikerjakan</span>
                <div class="w-6 h-6 sm:w-7 sm:h-7 rounded-lg bg-amber-50 text-[#D97706] border border-amber-200 flex items-center justify-center shrink-0">
                    <i data-lucide="hard-hat" class="w-3 h-3 sm:w-3.5 sm:h-3.5"></i>
                </div>
            </div>
            <div class="mt-1.5 sm:mt-2">
                <div class="font-outfit text-xl sm:text-2xl font-bold text-[#0F172A] leading-tight">
                    {{ $summary['in_progress'] }}
                </div>
                <span class="hidden sm:block text-[11px] text-[#475569] font-medium mt-0.5">Sedang ditangani</span>
            </div>
        </div>

        <!-- 5. Menunggu Validasi -->
        <div class="p-2.5 sm:p-3.5 rounded-xl bg-white border border-[#E2E8F0] flex flex-col justify-between min-w-0">
            <div class="flex items-center justify-between gap-1.5">
                <span class="text-[8.5px] sm:text-[11px] text-[#475569] font-bold uppercase tracking-normal sm:tracking-wider leading-tight">Validasi</span>
                <div class="w-6 h-6 sm:w-7 sm:h-7 rounded-lg bg-amber-50 text-[#D97706] border border-amber-200 flex items-center justify-center shrink-0">
                    <i data-lucide="clock-3" class="w-3 h-3 sm:w-3.5 sm:h-3.5"></i>
                </div>
            </div>
            <div class="mt-1.5 sm:mt-2">
                <div class="font-outfit text-xl sm:text-2xl font-bold text-[#0F172A] leading-tight">
                    {{ $summary['waiting_verification'] ?? 0 }}
                </div>
                <span class="hidden sm:block text-[11px] text-[#475569] font-medium mt-0.5">Menunggu uji mutu</span>
            </div>
        </div>

        <!-- 6. Selesai -->
        <div class="p-2.5 sm:p-3.5 rounded-xl bg-white border border-[#E2E8F0] flex flex-col justify-between min-w-0">
            <div class="flex items-center justify-between gap-1.5">
                <span class="text-[8.5px] sm:text-[11px] text-[#475569] font-bold uppercase tracking-normal sm:tracking-wider leading-tight">Selesai</span>
                <div class="w-6 h-6 sm:w-7 sm:h-7 rounded-lg bg-emerald-50 text-[#10B981] border border-emerald-200 flex items-center justify-center shrink-0">
                    <i data-lucide="check-circle-2" class="w-3 h-3 sm:w-3.5 sm:h-3.5"></i>
                </div>
            </div>
            <div class="mt-1.5 sm:mt-2">
                <div class="font-outfit text-xl sm:text-2xl font-bold text-[#0F172A] leading-tight">
                    {{ $summary['completed'] }}
                </div>
                <span class="hidden sm:block text-[11px] text-[#475569] font-medium mt-0.5">Tuntas diperbaiki</span>
            </div>
        </div>

        <!-- 7. Rasio Sukses -->
        <div class="col-span-3 sm:col-span-1 p-2.5 sm:p-3.5 rounded-xl bg-white border border-[#E2E8F0] flex flex-col justify-between min-w-0">
            <div class="flex items-center justify-between gap-1.5">
                <span class="text-[8.5px] sm:text-[11px] text-[#475569] font-bold uppercase tracking-normal sm:tracking-wider leading-tight">Rasio Sukses</span>
                <div class="w-6 h-6 sm:w-7 sm:h-7 rounded-lg bg-blue-50 text-[#2563EB] border border-blue-200 flex items-center justify-center shrink-0">
                    <i data-lucide="trending-up" class="w-3 h-3 sm:w-3.5 sm:h-3.5"></i>
                </div>
            </div>
            <div class="mt-1.5 sm:mt-2">
                <div class="font-outfit text-xl sm:text-2xl font-bold text-[#0F172A] leading-tight">
                    {{ $summary['completion_rate'] }}%
                </div>
                <span class="hidden sm:block text-[11px] text-[#475569] font-medium mt-0.5">Tingkat tuntas</span>
            </div>
        </div>
    </div>

    <!-- Navigasi Tab Petugas (Dipindahkan ke Sidebar Accordion) -->
    <div class="hidden grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-2 sm:gap-2.5 w-full">
        <!-- 1. Tab Peta Tugas -->
        <button type="button" onclick="switchPetugasTab('spatial')" id="petugasTabBtn-spatial"
            class="petugas-tab-btn text-left p-2.5 sm:p-3 rounded-xl border transition flex items-center gap-2 sm:gap-2.5 cursor-pointer bg-[#2563EB] text-white border-[#2563EB]">
            <i data-lucide="map" class="w-4 h-4 shrink-0 text-current"></i>
            <div class="min-w-0 flex-1">
                <span class="text-[11px] sm:text-xs font-bold block leading-tight">Peta Tugas</span>
            </div>
        </button>

        <!-- 2. Tab Penugasan -->
        <button type="button" onclick="switchPetugasTab('assigned')" id="petugasTabBtn-assigned"
            class="petugas-tab-btn text-left p-2.5 sm:p-3 rounded-xl border transition flex items-center gap-2 sm:gap-2.5 cursor-pointer bg-white text-[#0F172A] border-[#E2E8F0] hover:bg-[#F8FAFC]">
            <i data-lucide="send" class="w-4 h-4 shrink-0 text-current"></i>
            <div class="min-w-0 flex-1">
                <span class="text-[11px] sm:text-xs font-bold block leading-tight">Penugasan</span>
            </div>
            <span class="tab-badge px-1.5 py-0.5 rounded-md text-[10px] font-bold shrink-0 bg-blue-50 text-[#2563EB] border border-blue-200">{{ $dispatchedTasks->count() }}</span>
        </button>

        <!-- 3. Tab Dikerjakan -->
        <button type="button" onclick="switchPetugasTab('tasks')" id="petugasTabBtn-tasks"
            class="petugas-tab-btn text-left p-2.5 sm:p-3 rounded-xl border transition flex items-center gap-2 sm:gap-2.5 cursor-pointer bg-white text-[#0F172A] border-[#E2E8F0] hover:bg-[#F8FAFC]">
            <i data-lucide="hard-hat" class="w-4 h-4 shrink-0 text-current"></i>
            <div class="min-w-0 flex-1">
                <span class="text-[11px] sm:text-xs font-bold block leading-tight">Dikerjakan</span>
            </div>
            <span class="tab-badge px-1.5 py-0.5 rounded-md text-[10px] font-bold shrink-0 bg-blue-50 text-[#2563EB] border border-blue-200">{{ $inProgressTasks->count() }}</span>
        </button>

        <!-- 4. Tab Validasi -->
        <button type="button" onclick="switchPetugasTab('validation')" id="petugasTabBtn-validation"
            class="petugas-tab-btn text-left p-2.5 sm:p-3 rounded-xl border transition flex items-center gap-2 sm:gap-2.5 cursor-pointer bg-white text-[#0F172A] border-[#E2E8F0] hover:bg-[#F8FAFC]">
            <i data-lucide="shield-check" class="w-4 h-4 shrink-0 text-current"></i>
            <div class="min-w-0 flex-1">
                <span class="text-[11px] sm:text-xs font-bold block leading-tight">Validasi</span>
            </div>
            <span class="tab-badge px-1.5 py-0.5 rounded-md text-[10px] font-bold shrink-0 bg-blue-50 text-[#2563EB] border border-blue-200">{{ $waitingVerificationTasks->count() }}</span>
        </button>

        <!-- 5. Tab Tugas Selesai -->
        <button type="button" onclick="switchPetugasTab('history')" id="petugasTabBtn-history"
            class="petugas-tab-btn col-span-2 sm:col-span-1 text-left p-2.5 sm:p-3 rounded-xl border transition flex items-center gap-2 sm:gap-2.5 cursor-pointer bg-white text-[#0F172A] border-[#E2E8F0] hover:bg-[#F8FAFC]">
            <i data-lucide="check-circle-2" class="w-4 h-4 shrink-0 text-current"></i>
            <div class="min-w-0 flex-1">
                <span class="text-[11px] sm:text-xs font-bold block leading-tight">Tugas Selesai</span>
            </div>
            <span class="tab-badge px-1.5 py-0.5 rounded-md text-[10px] font-bold shrink-0 bg-blue-50 text-[#2563EB] border border-blue-200">{{ $completedTasks->count() }}</span>
        </button>
    </div>

    <!-- Tab 1: Peta Tugas (Di luar #petugasUnifiedCard agar tidak ikut tersembunyi) -->
    <div id="petugasTabContent-spatial" class="space-y-6">
        @include('partials.role_split_gis', [
            'mapId' => 'petugasMap',
            'prefix' => 'petugas',
            'focusFn' => 'focusDistrictOnPetugasMap',
            'mapTitle' => 'Peta Spasial 15 Kecamatan',
            'districtStats' => $districtStats
        ])
    </div>

    <!-- Tab Laporan Kinerja Pekerja Lapangan (Individu, Regu, & Responsivitas Operator) -->
    <div id="petugasTabContent-performance" class="hidden space-y-5">
        @include('partials.petugas_performance_report')
    </div>

    <!-- Wadah Kartu Terpadu Daftar Tugas Lapangan (Seragam Admin & Kepala Daerah) -->
    <div id="petugasUnifiedCard" class="hidden card-white rounded-2xl p-5 sm:p-6 bg-white border border-[#E2E8F0] shadow-xs space-y-4">
    <!-- Filter Pencarian dan Kategori (Seragam Daftar Pengguna & Daftar Aduan) -->
    <div id="petugasFilterContainer" class="hidden">
        <div class="flex flex-col xl:flex-row xl:items-center justify-between gap-3">
            <div class="flex items-center gap-2.5 shrink-0">
                <div class="w-9 h-9 rounded-xl bg-[#EFF6FF] text-[#2563EB] border border-[#BFDBFE] flex items-center justify-center shrink-0">
                    <i data-lucide="clipboard-list" class="w-4 h-4"></i>
                </div>
                <h3 class="font-outfit font-bold text-sm sm:text-base text-[#0F172A]">Daftar Tugas Lapangan</h3>
            </div>

            <div class="flex flex-wrap items-center gap-2 flex-1 xl:justify-end">
                <div class="relative flex-1 min-w-[160px] sm:max-w-xs">
                    <i data-lucide="search" class="w-4 h-4 text-[#64748B] absolute left-3 top-1/2 -translate-y-1/2 pointer-events-none"></i>
                    <input type="text" id="petugasSearchInput" oninput="applyPetugasFilters()"
                        placeholder="Cari kode tugas, judul, atau lokasi..."
                        class="w-full pl-9 pr-3 py-2 text-xs rounded-xl border border-[#E2E8F0] bg-[#F8FAFC] focus:bg-white focus:border-[#2563EB] focus:ring-2 focus:ring-[#2563EB]/15 transition">
                </div>

                <select id="petugasCategorySelect" onchange="applyPetugasFilters()"
                    class="text-xs font-semibold rounded-xl border border-[#E2E8F0] py-2 px-3 bg-[#F8FAFC] text-[#334155] focus:outline-none focus:border-[#2563EB] focus:bg-white transition shrink-0">
                    <option value="ALL">Kategori: Semua</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                    @endforeach
                </select>

                <select id="petugasDamageTypeSelect" onchange="applyPetugasFilters()"
                    class="text-xs font-semibold rounded-xl border border-[#E2E8F0] py-2 px-3 bg-[#F8FAFC] text-[#334155] focus:outline-none focus:border-[#2563EB] focus:bg-white transition shrink-0">
                    <option value="ALL">Tipe: Semua</option>
                    <option value="minor">Ringan</option>
                    <option value="major">Berat</option>
                </select>

                <button type="button" id="petugasResetBtn" onclick="resetPetugasFilters()"
                    class="hidden px-3 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs transition flex items-center gap-1 shrink-0 cursor-pointer">
                    <i data-lucide="rotate-ccw" class="w-3.5 h-3.5"></i>
                    <span>Reset</span>
                </button>

                <!-- Toggle Tata Letak Daftar / Petak (Rata Kanan, Rapat dengan Filter) -->
                <div class="inline-flex items-center p-0.5 rounded-xl bg-[#F8FAFC] border border-[#E2E8F0] shrink-0" role="group" aria-label="Tata letak tugas">
                    <button type="button" id="petugasLayoutBtn-list" onclick="setPetugasLayout('list')"
                        class="px-2.5 py-1.5 rounded-lg text-xs font-semibold flex items-center gap-1.5 transition cursor-pointer bg-[#2563EB] text-white">
                        <i data-lucide="list" class="w-3.5 h-3.5"></i>
                        <span>Daftar</span>
                    </button>
                    <button type="button" id="petugasLayoutBtn-grid" onclick="setPetugasLayout('grid')"
                        class="px-2.5 py-1.5 rounded-lg text-xs font-semibold flex items-center gap-1.5 transition cursor-pointer text-[#475569] hover:text-[#0F172A]">
                        <i data-lucide="layout-grid" class="w-3.5 h-3.5"></i>
                        <span>Petak</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Tab 1: Peta Spasial Penanganan Fisik -->
    <!-- Tab 2: Penugasan Baru Masuk -->
    <div id="petugasTabContent-assigned" class="hidden space-y-4">
        @if($dispatchedTasks->isEmpty())
            <div class="card-white rounded-2xl p-12 text-center space-y-2 bg-white border border-[#E2E8F0]">
                <div class="w-10 h-10 rounded-full bg-blue-50 text-[#2563EB] flex items-center justify-center mx-auto">
                    <i data-lucide="check" class="w-5 h-5"></i>
                </div>
                <h4 class="font-bold text-sm text-slate-800">Belum Ada Penugasan Baru</h4>
                <p class="text-xs text-slate-400">Seluruh tugas yang ditugaskan telah dimulai ke tahap pengerjaan.</p>
            </div>
        @else
            @include('partials.role_complaints_table', ['wrapId' => 'petugasTableWrap-assigned', 'rowClass' => 'petugas-assigned-row', 'items' => $dispatchedTasks, 'mode' => 'petugas-assigned', 'hidden' => false])
            <div class="petugas-task-grid hidden grid grid-cols-1 md:grid-cols-2 gap-4">
                @foreach($dispatchedTasks as $t)
                    @php
                        $disp = $t->latestDisposition;
                        $deadline = $disp?->deadline;
                        $isOverdue = $deadline && now()->greaterThan($deadline);
                    @endphp
                    <div onclick="if (window.innerWidth < 768) { openPetugasDetailModal({{ $t->id }}); }" class="petugas-task-card card-white rounded-2xl p-3.5 sm:p-4 flex flex-col justify-between space-y-3 bg-white border {{ $isOverdue ? 'border-red-300 shadow-md' : 'border-[#E2E8F0] shadow-xs' }} hover:shadow-md transition cursor-pointer md:cursor-default"
                        data-search="{{ strtolower($t->public_code . ' ' . $t->title . ' ' . $t->address . ' ' . $t->description) }}"
                        data-category="{{ $t->category_id }}"
                        data-damage-type="{{ $t->damage_type ?? 'minor' }}">
                        <div class="space-y-3">
                            <!-- 1. Header Card: 1 Baris Sejajar - Kode + Tipe + Kategori (Kiri) & Status Ditugaskan (Kanan) -->
                            <div class="flex items-center justify-between gap-2 pb-2.5 border-b border-slate-100 min-w-0">
                                <div class="flex items-center gap-1.5 min-w-0 flex-nowrap overflow-hidden">
                                    <span class="px-2 py-0.5 rounded-md bg-slate-100 text-[#0F172A] font-semibold text-[11px] font-mono border border-[#E2E8F0] whitespace-nowrap shrink-0">
                                        #{{ $t->public_code }}
                                    </span>
                                    <span class="px-2 py-0.5 rounded-full text-[10.5px] font-bold bg-red-50 text-red-700 border border-red-200/80 inline-flex items-center gap-1 whitespace-nowrap shrink-0">
                                        <span class="w-1.5 h-1.5 rounded-full bg-red-500"></span>
                                        <span>{{ $t->damage_type === 'major' ? 'Berat' : 'Ringan' }}</span>
                                    </span>
                                    <span class="inline-flex px-2 py-0.5 rounded-full text-[10.5px] font-bold bg-slate-100 text-slate-600 border border-slate-200/80 whitespace-nowrap truncate shrink-0 max-w-[170px]" title="{{ $t->category?->name ?? 'Infrastruktur' }}">
                                        {{ $t->category?->name ?? 'Infrastruktur' }}
                                    </span>
                                </div>
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-blue-50 text-[#2563EB] border border-blue-200 shrink-0 whitespace-nowrap">
                                    ● DITUGASKAN
                                </span>
                            </div>

                            <!-- 2. Konten Utama: Foto di Kiri & Rincian di Kanan (Sejajar Sempurna Gaya Operator) -->
                            @php
                                $firstPhotoUrl = $t->photos->isNotEmpty() 
                                    ? ($t->photos->first()->url ?? asset('storage/' . $t->photos->first()->storage_key)) 
                                    : null;
                            @endphp
                            <div class="flex gap-4 items-stretch">
                                <div onclick="openPetugasDetailModal({{ $t->id }})" class="relative w-24 h-24 sm:w-28 sm:h-28 rounded-2xl overflow-hidden border border-slate-200/90 bg-slate-100 shrink-0 shadow-2xs cursor-pointer group hover:ring-2 hover:ring-[#2563EB] transition" title="Klik untuk lihat rincian foto">
                                    @if($firstPhotoUrl)
                                        <img src="{{ $firstPhotoUrl }}" alt="Foto Kerusakan" class="w-full h-full object-cover group-hover:scale-105 transition duration-200">
                                        @if($t->photos->count() > 1)
                                            <span class="absolute bottom-1.5 right-1.5 bg-slate-900/80 text-white text-[10px] font-bold px-1.5 py-0.5 rounded-lg flex items-center gap-0.5 shadow-xs">
                                                <i data-lucide="layers" class="w-2.5 h-2.5"></i>
                                                +{{ $t->photos->count() }}
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
                                    <!-- Baris 1: Judul Laporan -->
                                    <div class="flex items-center justify-between gap-2 min-w-0">
                                        <h4 class="font-outfit font-extrabold text-sm sm:text-base text-slate-900 tracking-tight leading-snug truncate" title="{{ $t->title }}">
                                            {{ $t->title }}
                                        </h4>
                                    </div>

                                    <!-- Baris 2: Lokasi Fisik -->
                                    <div class="text-xs text-slate-600 flex items-center gap-1.5 min-w-0">
                                        <i data-lucide="map-pin" class="w-3.5 h-3.5 text-[#2563EB] shrink-0"></i>
                                        <span class="truncate font-medium" title="{{ $t->address }}">{{ $t->address }}</span>
                                    </div>

                                    <!-- Baris 3: Info Pelapor & No Telepon -->
                                    <div class="flex items-center gap-2 text-xs min-w-0">
                                        <div class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-lg bg-blue-50/90 border border-blue-100/90 text-slate-700 min-w-0 max-w-full">
                                            <i data-lucide="user" class="w-3 h-3 text-[#2563EB] shrink-0"></i>
                                            <span class="font-semibold text-slate-800 text-[11px] truncate">{{ $t->citizen?->name ?? 'Warga' }}</span>
                                            @if($t->citizen?->phone)
                                                <span class="text-blue-300 shrink-0">•</span>
                                                <span class="font-mono text-slate-600 font-medium text-[10.5px] shrink-0">{{ $t->citizen->phone }}</span>
                                            @endif
                                        </div>
                                    </div>

                                    <!-- Baris 4: Tombol Detail Lengkap & Foto -->
                                    <div class="relative z-10 hidden md:block">
                                        <button type="button" onclick="openPetugasDetailModal({{ $t->id }})" class="text-xs font-bold text-[#2563EB] hover:underline inline-flex items-center gap-1 cursor-pointer">
                                            <span>Lihat Rincian Lengkap & Foto</span>
                                            <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <!-- 3. Target SLA Penugasan & Instruksi -->
                            <div class="pt-2 border-t border-slate-100">
                                <div class="p-2.5 rounded-xl {{ $isOverdue ? 'bg-red-50 border border-red-200 text-red-800' : 'bg-blue-50/50 border border-blue-100/90' }} flex items-center justify-between text-[10px]">
                                    <span class="flex items-center gap-1.5 {{ $isOverdue ? 'text-red-700 font-bold' : 'text-slate-600 font-medium' }}">
                                        <i data-lucide="{{ $isOverdue ? 'alert-triangle' : 'clock' }}" class="w-3 h-3 {{ $isOverdue ? 'text-red-600' : 'text-[#2563EB]' }} shrink-0"></i>
                                        <span>{{ $isOverdue ? 'PERINGATAN: MELEWATI BATAS WAKTU!' : 'Batas Waktu Penyelesaian:' }}</span>
                                    </span>
                                    <span class="px-1.5 py-0.5 rounded text-[9px] font-bold shrink-0 {{ $isOverdue ? 'bg-red-100 text-red-800' : 'bg-blue-100/80 text-[#2563EB]' }}">
                                        {{ $deadline ? $deadline->diffForHumans() : '2 hari dari sekarang' }}
                                    </span>
                                </div>
                                @if($disp?->instruction)
                                    <div class="mt-1 text-[10.5px] text-slate-500 italic px-1 truncate">
                                        Instruksi: "{{ $disp->instruction }}"
                                    </div>
                                @endif
                            </div>
                        </div>

                        <!-- Action Button Mulai Kerja Lapangan -->
                        <div onclick="event.stopPropagation();" class="pt-2 border-t border-slate-100 cursor-default">
                            <form method="POST" action="{{ route('petugas.complaints.start', $t->id) }}">
                                @csrf
                                <button type="submit" class="w-full py-2.5 rounded-xl bg-[#2563EB] hover:bg-[#1D4ED8] text-white font-bold text-xs shadow-xs transition flex items-center justify-center gap-2 cursor-pointer">
                                    <i data-lucide="play" class="w-3.5 h-3.5"></i>
                                    <span>Mulai Kerja Lapangan</span>
                                </button>
                            </form>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    <!-- Tab 3: Pengerjaan Fisik Sedang Berjalan -->
    <div id="petugasTabContent-tasks" class="hidden space-y-4">
        @if($inProgressTasks->isEmpty())
            <div class="card-white rounded-2xl p-12 text-center space-y-2 bg-white border border-[#E2E8F0]">
                <div class="w-10 h-10 rounded-full bg-slate-100 text-slate-400 flex items-center justify-center mx-auto">
                    <i data-lucide="hard-hat" class="w-5 h-5 text-[#2563EB]"></i>
                </div>
                <h4 class="font-bold text-sm text-slate-800">Tidak Ada Pekerjaan Aktif</h4>
                <p class="text-xs text-slate-400">Silakan buka tab "Penugasan" lalu tekan "Mulai Kerja Lapangan" untuk memulai penanganan tugas.</p>
            </div>
        @else
            @include('partials.role_complaints_table', ['wrapId' => 'petugasTableWrap-tasks', 'rowClass' => 'petugas-tasks-row', 'items' => $inProgressTasks, 'mode' => 'petugas-tasks', 'hidden' => false])
            <div class="petugas-task-grid hidden grid grid-cols-1 md:grid-cols-2 gap-4">
                @foreach($inProgressTasks as $t)
                    @php
                        $disp = $t->latestDisposition;
                        $deadline = $disp?->deadline;
                        $isOverdue = $deadline && now()->greaterThan($deadline);
                        $isWorking = $t->status === 'IN_PROGRESS';
                        $isWaiting = $t->status === 'WAITING_VERIFICATION';
                    @endphp
                    <div onclick="if (window.innerWidth < 768) { openPetugasDetailModal({{ $t->id }}); }" class="petugas-task-card card-white rounded-2xl p-3.5 sm:p-4 flex flex-col justify-between space-y-3 bg-white border {{ $isOverdue ? 'border-red-300 shadow-md' : 'border-[#E2E8F0] shadow-xs' }} hover:shadow-md transition cursor-pointer md:cursor-default"
                        data-search="{{ strtolower($t->public_code . ' ' . $t->title . ' ' . $t->address . ' ' . $t->description) }}"
                        data-category="{{ $t->category_id }}"
                        data-damage-type="{{ $t->damage_type ?? 'minor' }}">
                        <div class="space-y-3">
                            <!-- 1. Header Badges -->
                            <div class="flex items-center justify-between gap-2 pb-2.5 border-b border-slate-100 min-w-0">
                                <div class="flex items-center gap-1.5 min-w-0 flex-nowrap overflow-hidden">
                                    <span class="px-2 py-0.5 rounded-md bg-slate-100 text-[#0F172A] font-semibold text-[11px] font-mono border border-[#E2E8F0] whitespace-nowrap shrink-0">
                                        #{{ $t->public_code }}
                                    </span>
                                    <span class="px-2 py-0.5 rounded-full text-[10.5px] font-bold bg-red-50 text-red-700 border border-red-200/80 inline-flex items-center gap-1 whitespace-nowrap shrink-0">
                                        <span class="w-1.5 h-1.5 rounded-full bg-red-500"></span>
                                        <span>{{ $t->damage_type === 'major' ? 'Berat' : 'Ringan' }}</span>
                                    </span>
                                    <span class="inline-flex px-2 py-0.5 rounded-full text-[10.5px] font-bold bg-slate-100 text-slate-600 border border-slate-200/80 whitespace-nowrap truncate shrink-0 max-w-[170px]" title="{{ $t->category?->name ?? 'Infrastruktur' }}">
                                        {{ $t->category?->name ?? 'Infrastruktur' }}
                                    </span>
                                </div>
                                @php
                                    $pStatusBadgeClass = 'bg-amber-50 text-amber-700 border border-amber-300';
                                    $pStatusLabel = 'DIKERJAKAN';
                                    if ($t->status === 'WAITING_VERIFICATION') {
                                        $pStatusBadgeClass = 'bg-blue-50 text-[#2563EB] border border-blue-200';
                                        $pStatusLabel = 'VALIDASI';
                                    } elseif ($t->status === 'REVISION_REQUIRED') {
                                        $pStatusBadgeClass = 'bg-amber-50 text-amber-800 border border-amber-200';
                                        $pStatusLabel = 'PERLU REVISI';
                                    }
                                @endphp
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold {{ $pStatusBadgeClass }} shrink-0 whitespace-nowrap">
                                    ● {{ $pStatusLabel }}
                                </span>
                            </div>

                            <!-- 2. Konten Utama: Foto di Kiri & Rincian di Kanan -->
                            @php
                                $firstPhotoUrl = $t->photos->isNotEmpty() 
                                    ? ($t->photos->first()->url ?? asset('storage/' . $t->photos->first()->storage_key)) 
                                    : null;
                            @endphp
                            <div class="flex gap-4 items-stretch">
                                <div onclick="openPetugasDetailModal({{ $t->id }})" class="relative w-24 h-24 sm:w-28 sm:h-28 rounded-2xl overflow-hidden border border-slate-200/90 bg-slate-100 shrink-0 shadow-2xs cursor-pointer group hover:ring-2 hover:ring-[#2563EB] transition" title="Klik untuk lihat rincian foto">
                                    @if($firstPhotoUrl)
                                        <img src="{{ $firstPhotoUrl }}" alt="Foto Kerusakan" class="w-full h-full object-cover group-hover:scale-105 transition duration-200">
                                        @if($t->photos->count() > 1)
                                            <span class="absolute bottom-1.5 right-1.5 bg-slate-900/80 text-white text-[10px] font-bold px-1.5 py-0.5 rounded-lg flex items-center gap-0.5 shadow-xs">
                                                <i data-lucide="layers" class="w-2.5 h-2.5"></i>
                                                +{{ $t->photos->count() }}
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
                                    <!-- Baris 1: Judul Laporan -->
                                    <div class="flex items-center justify-between gap-2 min-w-0">
                                        <h4 class="font-outfit font-extrabold text-sm sm:text-base text-slate-900 tracking-tight leading-snug truncate" title="{{ $t->title }}">
                                            {{ $t->title }}
                                        </h4>
                                    </div>

                                    <!-- Baris 2: Lokasi Fisik -->
                                    <div class="text-xs text-slate-600 flex items-center gap-1.5 min-w-0">
                                        <i data-lucide="map-pin" class="w-3.5 h-3.5 text-[#2563EB] shrink-0"></i>
                                        <span class="truncate font-medium" title="{{ $t->address }}">{{ $t->address }}</span>
                                    </div>

                                    <!-- Baris 3: Info Pelapor & No Telepon -->
                                    <div class="flex items-center gap-2 text-xs min-w-0">
                                        <div class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-lg bg-blue-50/90 border border-blue-100/90 text-slate-700 min-w-0 max-w-full">
                                            <i data-lucide="user" class="w-3 h-3 text-[#2563EB] shrink-0"></i>
                                            <span class="font-semibold text-slate-800 text-[11px] truncate">{{ $t->citizen?->name ?? 'Warga' }}</span>
                                            @if($t->citizen?->phone)
                                                <span class="text-blue-300 shrink-0">•</span>
                                                <span class="font-mono text-slate-600 font-medium text-[10.5px] shrink-0">{{ $t->citizen->phone }}</span>
                                            @endif
                                        </div>
                                    </div>

                                    <!-- Baris 4: Tombol Detail Lengkap & Foto -->
                                    <div class="relative z-10 hidden md:block">
                                        <button type="button" onclick="openPetugasDetailModal({{ $t->id }})" class="text-xs font-bold text-[#2563EB] hover:underline inline-flex items-center gap-1 cursor-pointer">
                                            <span>Lihat Rincian Lengkap & Foto</span>
                                            <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <!-- 3. Batas Waktu Disposisi (Satu Baris Kompak Sesuai Gambar 2) -->
                            <div class="pt-2 border-t border-slate-100">
                                <div class="p-2 rounded-xl bg-slate-50 border border-slate-200/80 flex items-center justify-between text-[10px]">
                                    <span class="text-slate-500 font-medium flex items-center gap-1.5">
                                        <i data-lucide="clock" class="w-3 h-3 text-slate-400 shrink-0"></i>
                                        <span>Batas Waktu Disposisi:</span>
                                    </span>
                                    <span class="font-bold text-slate-700 font-mono">
                                        {{ $deadline ? $deadline->translatedFormat('d M Y, H:i') : '-' }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        <!-- Action Button: Buka Modal Unggah Bukti Penyelesaian -->
                        <div onclick="event.stopPropagation();" class="pt-2 border-t border-slate-100 cursor-default">
                            <button type="button" onclick="openPetugasFinishModal({{ $t->id }}, '{{ $t->public_code }}', '{{ addslashes($t->title) }}')"
                                class="w-full py-2.5 rounded-xl bg-[#2563EB] hover:bg-[#1D4ED8] text-white font-bold text-xs shadow-xs transition flex items-center justify-center gap-2 cursor-pointer">
                                <i data-lucide="upload-cloud" class="w-3.5 h-3.5"></i>
                                <span>Kirim Bukti Selesai Lapangan</span>
                            </button>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    <!-- Tab 4: Validasi Bukti Pekerjaan Selesai -->
    <div id="petugasTabContent-validation" class="hidden space-y-4">
        @if($waitingVerificationTasks->isEmpty())
            <div class="card-white rounded-2xl p-12 text-center space-y-2 bg-white border border-[#E2E8F0]">
                <div class="w-10 h-10 rounded-full bg-amber-50 text-amber-600 flex items-center justify-center mx-auto border border-amber-200">
                    <i data-lucide="shield-check" class="w-5 h-5 text-amber-600"></i>
                </div>
                <h4 class="font-bold text-sm text-slate-800">Tidak Ada Tugas Menunggu Validasi</h4>
                <p class="text-xs text-slate-400">Tugas yang telah selesai diperbaiki di lapangan dan telah dikirim bukti fotonya akan muncul di sini menunggu peninjauan dan persetujuan dari Operator Dinas PUPR.</p>
            </div>
        @else
            @include('partials.role_complaints_table', ['wrapId' => 'petugasTableWrap-validation', 'rowClass' => 'petugas-validation-row', 'items' => $waitingVerificationTasks, 'mode' => 'petugas-validation', 'hidden' => false])
            <div class="petugas-task-grid hidden grid grid-cols-1 md:grid-cols-2 gap-4">
                @foreach($waitingVerificationTasks as $t)
                    @php
                        $disp = $t->latestDisposition;
                        $deadline = $disp?->deadline;
                        $afterPhoto = $t->photos->where('type', 'resolution_after')->first() ?? $t->photos->last();
                        $afterUrl = $afterPhoto ? ($afterPhoto->url ?? asset('storage/' . $afterPhoto->storage_key)) : null;
                    @endphp
                    <div onclick="if (window.innerWidth < 768) { openPetugasDetailModal({{ $t->id }}); }" class="petugas-task-card card-white rounded-2xl p-3.5 sm:p-4 flex flex-col justify-between space-y-3 bg-white border border-[#E2E8F0] shadow-xs hover:shadow-md transition cursor-pointer md:cursor-default"
                        data-search="{{ strtolower($t->public_code . ' ' . $t->title . ' ' . $t->address . ' ' . $t->description) }}"
                        data-category="{{ $t->category_id }}"
                        data-damage-type="{{ $t->damage_type ?? 'minor' }}">
                        <div class="space-y-3">
                            <!-- 1. Header Badges -->
                            <div class="flex items-center justify-between gap-2 pb-2.5 border-b border-slate-100 min-w-0">
                                <div class="flex items-center gap-1.5 min-w-0 flex-nowrap overflow-hidden">
                                    <span class="px-2 py-0.5 rounded-md bg-slate-100 text-[#0F172A] font-semibold text-[11px] font-mono border border-[#E2E8F0] whitespace-nowrap shrink-0">
                                        #{{ $t->public_code }}
                                    </span>
                                    <span class="px-2 py-0.5 rounded-full text-[10.5px] font-bold bg-red-50 text-red-700 border border-red-200/80 inline-flex items-center gap-1 whitespace-nowrap shrink-0">
                                        <span class="w-1.5 h-1.5 rounded-full bg-red-500"></span>
                                        <span>{{ $t->damage_type === 'major' ? 'Berat' : 'Ringan' }}</span>
                                    </span>
                                    <span class="inline-flex px-2 py-0.5 rounded-full text-[10.5px] font-bold bg-slate-100 text-slate-600 border border-slate-200/80 whitespace-nowrap truncate shrink-0 max-w-[170px]" title="{{ $t->category?->name ?? 'Infrastruktur' }}">
                                        {{ $t->category?->name ?? 'Infrastruktur' }}
                                    </span>
                                </div>
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-amber-50 text-amber-700 border border-amber-300 shrink-0 whitespace-nowrap flex items-center gap-1">
                                    <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                                    VALIDASI
                                </span>
                            </div>

                            <!-- 2. Konten Utama: Foto di Kiri & Rincian di Kanan -->
                            <div class="flex gap-4 items-stretch">
                                <div onclick="openPetugasDetailModal({{ $t->id }})" class="relative w-24 h-24 sm:w-28 sm:h-28 rounded-2xl overflow-hidden border border-slate-200/90 bg-slate-100 shrink-0 shadow-2xs cursor-pointer group hover:ring-2 hover:ring-amber-500 transition" title="Klik untuk lihat rincian foto">
                                    @if($afterUrl)
                                        <img src="{{ $afterUrl }}" alt="Foto Hasil Perbaikan" class="w-full h-full object-cover group-hover:scale-105 transition duration-200">
                                        <span class="absolute top-1.5 left-1.5 bg-emerald-600/90 text-white text-[9px] font-bold px-1.5 py-0.5 rounded shadow-xs">
                                            Bukti Hasil
                                        </span>
                                    @else
                                        <div class="w-full h-full flex flex-col items-center justify-center text-slate-400 text-[10px] gap-1 p-2 text-center bg-slate-50">
                                            <i data-lucide="image-off" class="w-5 h-5 text-slate-300"></i>
                                            <span>Tanpa Foto</span>
                                        </div>
                                    @endif
                                </div>

                                <!-- Rincian Informasi Aduan -->
                                <div class="flex-1 min-w-0 flex flex-col justify-between py-0.5">
                                    <div class="flex items-center justify-between gap-2 min-w-0">
                                        <h4 class="font-outfit font-extrabold text-sm sm:text-base text-slate-900 tracking-tight leading-snug truncate" title="{{ $t->title }}">
                                            {{ $t->title }}
                                        </h4>
                                    </div>

                                    <div class="text-xs text-slate-600 flex items-center gap-1.5 min-w-0">
                                        <i data-lucide="map-pin" class="w-3.5 h-3.5 text-[#2563EB] shrink-0"></i>
                                        <span class="truncate font-medium" title="{{ $t->address }}">{{ $t->address }}</span>
                                    </div>

                                    <div class="flex items-center gap-2 text-xs min-w-0">
                                        <div class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-lg bg-blue-50/90 border border-blue-100/90 text-slate-700 min-w-0 max-w-full">
                                            <i data-lucide="user" class="w-3 h-3 text-[#2563EB] shrink-0"></i>
                                            <span class="font-semibold text-slate-800 text-[11px] truncate">{{ $t->citizen?->name ?? 'Warga' }}</span>
                                            @if($t->citizen?->phone)
                                                <span class="text-blue-300 shrink-0">•</span>
                                                <span class="font-mono text-slate-600 font-medium text-[10.5px] shrink-0">{{ $t->citizen->phone }}</span>
                                            @endif
                                        </div>
                                    </div>

                                    <div class="relative z-10 hidden md:block">
                                        <button type="button" onclick="openPetugasDetailModal({{ $t->id }})" class="text-xs font-bold text-[#2563EB] hover:underline inline-flex items-center gap-1 cursor-pointer">
                                            <span>Lihat Rincian Lengkap & Foto</span>
                                            <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <!-- SLA info -->
                            <div class="pt-2 border-t border-slate-100">
                                <div class="p-2 rounded-xl bg-slate-50 border border-slate-200/80 flex items-center justify-between text-[10px]">
                                    <span class="text-slate-500 font-medium flex items-center gap-1.5">
                                        <i data-lucide="clock" class="w-3 h-3 text-slate-400 shrink-0"></i>
                                        <span>Batas Waktu Disposisi:</span>
                                    </span>
                                    <span class="font-bold text-slate-700 font-mono">
                                        {{ $deadline ? $deadline->translatedFormat('d M Y, H:i') : '-' }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        <!-- Action / Status Banner -->
                        <div class="pt-2 border-t border-slate-100">
                            <div class="p-2.5 rounded-xl bg-blue-50 border border-blue-200 text-[#2563EB] text-xs text-center font-bold flex items-center justify-center gap-2">
                                <i data-lucide="shield-check" class="w-4 h-4 text-[#2563EB] shrink-0"></i>
                                <span>Bukti fisik telah dikirim. Menunggu verifikasi & validasi Operator Dinas PUPR.</span>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    <!-- Tab 5: Riwayat Tugas Selesai -->
    <div id="petugasTabContent-history" class="hidden space-y-4">
        @if($completedTasks->isEmpty())
            <div class="card-white rounded-2xl p-12 text-center text-slate-400 text-xs bg-white border border-[#E2E8F0]">
                Belum ada riwayat tugas yang selesai divalidasi.
            </div>
        @else
            @include('partials.role_complaints_table', ['wrapId' => 'petugasTableWrap-history', 'rowClass' => 'petugas-history-row', 'items' => $completedTasks, 'mode' => 'petugas-history', 'hidden' => false])
            <div class="petugas-task-grid hidden grid grid-cols-1 lg:grid-cols-2 gap-5">
                @foreach($completedTasks as $c)
                    @php
                        $beforePhoto = $c->photos->where('type', 'evidence_before')->first() ?? $c->photos->first();
                        $afterPhoto = $c->photos->where('type', 'resolution_after')->first() ?? $c->photos->last();
                        $beforeUrl = $beforePhoto ? ($beforePhoto->url ?? asset('storage/' . $beforePhoto->storage_key)) : null;
                        $afterUrl = $afterPhoto && $afterPhoto->id !== ($beforePhoto?->id) ? ($afterPhoto->url ?? asset('storage/' . $afterPhoto->storage_key)) : null;
                        $displayPhoto = $afterUrl ?: $beforeUrl;
                        $archiveDeadline = $c->latestDisposition?->deadline 
                            ? \Carbon\Carbon::parse($c->latestDisposition->deadline) 
                            : \Carbon\Carbon::parse($c->created_at)->addDays($c->damage_type === 'major' ? 7 : 2);
                    @endphp
                    <div onclick="if (window.innerWidth < 768) { openPetugasDetailModal({{ $c->id }}); }" class="petugas-task-card card-white rounded-2xl p-4 sm:p-5 bg-white border border-slate-200/90 hover:border-blue-300 hover:shadow-md transition-all duration-200 flex flex-col justify-between space-y-3.5 cursor-pointer md:cursor-default"
                        data-search="{{ strtolower($c->public_code . ' ' . ($c->user?->name ?? $c->citizen?->name ?? '') . ' ' . $c->title . ' ' . $c->address) }}"
                        data-category="{{ $c->category_id }}"
                        data-damage-type="{{ $c->damage_type ?? 'minor' }}">
                        
                        <div class="space-y-3">
                            <!-- 1. Header Card: 1 Baris Sejajar - Kode + Tipe + Kategori (Kiri) & Waktu (Kanan) -->
                            <div class="flex items-center justify-between gap-2 pb-2.5 border-b border-slate-100 min-w-0">
                                <div class="flex items-center gap-1.5 min-w-0 flex-nowrap overflow-hidden">
                                    <span class="px-2 py-0.5 rounded-md bg-slate-100 text-[#0F172A] font-semibold text-[11px] font-mono border border-[#E2E8F0] whitespace-nowrap shrink-0">
                                        #{{ $c->public_code }}
                                    </span>
                                    <span class="px-2 py-0.5 rounded-full text-[10.5px] font-bold bg-red-50 text-red-700 border border-red-200/80 inline-flex items-center gap-1 whitespace-nowrap shrink-0">
                                        <span class="w-1.5 h-1.5 rounded-full bg-red-500"></span>
                                        <span>{{ $c->damage_type === 'major' ? 'Berat' : 'Ringan' }}</span>
                                    </span>
                                    <span class="inline-flex px-2 py-0.5 rounded-full text-[10.5px] font-bold bg-slate-100 text-slate-600 border border-slate-200/80 whitespace-nowrap truncate shrink-0 max-w-[170px]" title="{{ $c->category?->name ?? 'Infrastruktur' }}">
                                        {{ $c->category?->name ?? 'Infrastruktur' }}
                                    </span>
                                </div>
                                <span class="text-[11px] text-slate-400 font-medium whitespace-nowrap flex items-center gap-1 shrink-0">
                                    <i data-lucide="clock" class="w-3 h-3 text-slate-300"></i>
                                    <span>{{ $c->created_at->diffForHumans() }}</span>
                                </span>
                            </div>

                            <!-- 2. Konten Utama: 1 Foto Bukti + Rincian (Sejajar Sempurna Gaya Tab Arsip) -->
                            <div class="flex gap-4 items-stretch">
                                <div onclick="openPetugasDetailModal({{ $c->id }})" class="relative w-24 h-24 sm:w-28 sm:h-28 rounded-2xl overflow-hidden border border-slate-200/90 bg-slate-100 shrink-0 shadow-2xs cursor-pointer group hover:ring-2 hover:ring-[#2563EB] transition" title="Klik untuk lihat rincian foto">
                                    @if($displayPhoto)
                                        <img src="{{ $displayPhoto }}" alt="Foto Aduan" class="w-full h-full object-cover group-hover:scale-105 transition duration-200">
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
                                    <!-- Baris 1: Judul Laporan -->
                                    <div class="flex items-center justify-between gap-2 min-w-0">
                                        <h4 class="font-outfit font-extrabold text-sm sm:text-base text-slate-900 tracking-tight leading-snug truncate" title="{{ $c->title }}">
                                            {{ $c->title }}
                                        </h4>
                                    </div>

                                    <!-- Baris 2: Lokasi Fisik -->
                                    <div class="text-xs text-slate-600 flex items-center gap-1.5 min-w-0">
                                        <i data-lucide="map-pin" class="w-3.5 h-3.5 text-[#2563EB] shrink-0"></i>
                                        <span class="truncate font-medium" title="{{ $c->address }}">{{ $c->address }}</span>
                                    </div>

                                    <!-- Baris 3: Info Pelapor & No Telepon -->
                                    <div class="flex items-center gap-2 text-xs min-w-0">
                                        <div class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-lg bg-blue-50/90 border border-blue-100/90 text-slate-700 min-w-0 max-w-full">
                                            <i data-lucide="user" class="w-3 h-3 text-[#2563EB] shrink-0"></i>
                                            <span class="font-semibold text-slate-800 text-[11px] truncate">{{ $c->citizen?->name ?? 'Warga' }}</span>
                                            @if($c->citizen?->phone)
                                                <span class="text-blue-300 shrink-0">•</span>
                                                <span class="font-mono text-slate-600 font-medium text-[10.5px] shrink-0">{{ $c->citizen->phone }}</span>
                                            @endif
                                        </div>
                                    </div>

                                    <!-- Baris 4: Tombol Detail Lengkap & Foto -->
                                    <div class="relative z-10 hidden md:block">
                                        <button type="button" onclick="openPetugasDetailModal({{ $c->id }})" class="text-xs font-bold text-[#2563EB] hover:underline inline-flex items-center gap-1 cursor-pointer">
                                            <span>Lihat Rincian Lengkap & Foto</span>
                                            <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- 3. Footer: Target SLA (Sama Persis Tab Arsip Operator) -->
                        <div class="pt-2 border-t border-slate-100">
                            <div class="p-2.5 rounded-xl bg-blue-50/50 border border-blue-100/90 flex items-center justify-between text-[10px]">
                                <span class="flex items-center gap-1.5 text-slate-600 font-medium">
                                    <i data-lucide="clock" class="w-3 h-3 text-[#2563EB] shrink-0"></i>
                                    <span>Target: <strong class="text-slate-900 font-semibold">{{ $archiveDeadline->locale('id')->isoFormat('D MMM Y') }}</strong></span>
                                </span>
                                <span class="px-1.5 py-0.5 rounded text-[9px] font-bold shrink-0 bg-blue-100/80 text-[#2563EB]">
                                    {{ $archiveDeadline->diffForHumans() }}
                                </span>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    <!-- Pagination Bar Pekerja Lapangan (5 / 10 / 50 per halaman, default 10) -->
    <div id="petugasPaginationBar" class="hidden pt-3 border-t border-[#E2E8F0] flex flex-col sm:flex-row sm:items-center justify-between gap-3 text-xs">
        <div class="flex flex-wrap items-center gap-3 text-[#475569]">
            <div class="flex items-center gap-2">
                <span class="font-medium">Tampilkan:</span>
                <select id="petugasPageSizeSelect" onchange="changePetugasPageSize(this.value)" aria-label="Jumlah tugas per halaman"
                    class="text-xs font-semibold rounded-lg border border-[#E2E8F0] bg-[#F8FAFC] text-[#0F172A] px-2.5 py-1.5 focus:outline-none focus:border-[#2563EB] cursor-pointer">
                    <option value="5">5 tugas</option>
                    <option value="10" selected>10 tugas</option>
                    <option value="50">50 tugas</option>
                </select>
            </div>
            <span id="petugasPageInfo" class="font-medium">Menampilkan 0 dari 0 tugas</span>
        </div>
        <div id="petugasPageControls" class="flex items-center gap-1 flex-wrap"></div>
    </div>
    </div>

</div>

<script>
    let activePetugasTab = 'spatial';
    let petugasMapInstance = null;

    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(function(pos) {
            const pLat = document.getElementById('petugasProfileLat');
            const pLon = document.getElementById('petugasProfileLon');
            if (pLat) pLat.value = pos.coords.latitude;
            if (pLon) pLon.value = pos.coords.longitude;
        }, function() {}, { timeout: 6000, maximumAge: 300000 });
    }

    document.addEventListener('DOMContentLoaded', function() {
        const urlTab = new URLSearchParams(window.location.search).get('tab');
        if (urlTab && ['spatial', 'assigned', 'tasks', 'validation', 'history', 'performance'].includes(urlTab)) {
            switchPetugasTab(urlTab);
        }
    });

    function switchPetugasTab(tabName) {
        activePetugasTab = tabName;
        if (typeof window.syncSidebarSubItemActive === 'function') {
            window.syncSidebarSubItemActive('petugas', tabName);
        }

        // 1. Update button tabs style seragam desain Admin
        document.querySelectorAll('.petugas-tab-btn').forEach(btn => {
            btn.classList.remove('bg-[#2563EB]', 'text-white', 'border-[#2563EB]');
            btn.classList.add('bg-white', 'text-[#0F172A]', 'border-[#E2E8F0]', 'hover:bg-[#F8FAFC]');
            const badge = btn.querySelector('.tab-badge');
            if (badge) badge.className = 'tab-badge px-1.5 py-0.5 rounded-md text-[10px] font-bold shrink-0 bg-blue-50 text-[#2563EB] border border-blue-200';
            const sub = btn.querySelector('.tab-sub');
            if (sub) {
                sub.classList.remove('text-blue-100');
                sub.classList.add('text-[#475569]');
            }
        });

        const activeBtn = document.getElementById('petugasTabBtn-' + tabName);
        if (activeBtn) {
            activeBtn.classList.remove('bg-white', 'text-[#0F172A]', 'border-[#E2E8F0]', 'hover:bg-[#F8FAFC]');
            activeBtn.classList.add('bg-[#2563EB]', 'text-white', 'border-[#2563EB]');
            const badge = activeBtn.querySelector('.tab-badge');
            if (badge) badge.className = 'tab-badge px-1.5 py-0.5 rounded-md text-[10px] font-bold shrink-0 bg-white text-[#2563EB]';
            const sub = activeBtn.querySelector('.tab-sub');
            if (sub) {
                sub.classList.remove('text-[#475569]');
                sub.classList.add('text-blue-100');
            }
        }

        // 2. Hide all contents
        const panels = ['spatial', 'assigned', 'tasks', 'validation', 'history', 'performance'];
        panels.forEach(p => {
            const el = document.getElementById('petugasTabContent-' + p);
            if (el) el.classList.add('hidden');
        });

        // 3. Show active content
        const targetContent = document.getElementById('petugasTabContent-' + tabName);
        if (targetContent) {
            targetContent.classList.remove('hidden');
        }

        // Toggle visibility Unified Card, Filter Bar & Pagination Bar
        const unifiedCard = document.getElementById('petugasUnifiedCard');
        const filterContainer = document.getElementById('petugasFilterContainer');
        const paginationBar = document.getElementById('petugasPaginationBar');
        const hideListContainer = (tabName === 'spatial' || tabName === 'performance');
        if (unifiedCard) unifiedCard.classList.toggle('hidden', hideListContainer);
        if (filterContainer) filterContainer.classList.toggle('hidden', hideListContainer);
        if (paginationBar) paginationBar.classList.toggle('hidden', hideListContainer);
        if (typeof petugasPaginationState !== 'undefined') petugasPaginationState.page = 1;
        applyPetugasFilters(false);

        // 4. Invalidate map size if spatial tab
        if (tabName === 'spatial') {
            setTimeout(() => {
                if (!petugasMapInstance) {
                    initPetugasMap();
                } else {
                    petugasMapInstance.invalidateSize();
                }
            }, 80);
        }

        if (window.lucide) {
            lucide.createIcons();
        }
    }

    // Filter Real-time Kartu Tugas Petugas (Search, Kategori, Tipe Laporan)
    function setPetugasLayout(mode) {
        const tabs = ['assigned', 'tasks', 'validation', 'history'];
        tabs.forEach(tab => {
            const tableWrap = document.getElementById(`petugasTableWrap-${tab}`);
            if (tableWrap) tableWrap.classList.toggle('hidden', mode !== 'list');
        });
        document.querySelectorAll('.petugas-task-grid').forEach(el => {
            el.classList.toggle('hidden', mode !== 'grid');
        });
        const listBtn = document.getElementById('petugasLayoutBtn-list');
        const gridBtn = document.getElementById('petugasLayoutBtn-grid');
        if (listBtn && gridBtn) {
            listBtn.className = `px-2.5 py-1.5 rounded-md text-xs font-semibold flex items-center gap-1.5 transition cursor-pointer ${mode === 'list' ? 'bg-[#2563EB] text-white' : 'text-[#475569] hover:text-[#0F172A]'}`;
            gridBtn.className = `px-2.5 py-1.5 rounded-md text-xs font-semibold flex items-center gap-1.5 transition cursor-pointer ${mode === 'grid' ? 'bg-[#2563EB] text-white' : 'text-[#475569] hover:text-[#0F172A]'}`;
        }
    }

    const petugasPaginationState = { page: 1, size: 10 };

    function changePetugasPageSize(size) {
        petugasPaginationState.size = parseInt(size, 10) || 10;
        petugasPaginationState.page = 1;
        applyPetugasFilters(false);
    }

    function goPetugasPage(page) {
        petugasPaginationState.page = page;
        applyPetugasFilters(false);
    }

    function applyPetugasFilters(resetPage = true) {
        if (resetPage) petugasPaginationState.page = 1;
        const query = (document.getElementById('petugasSearchInput')?.value || '').toLowerCase().trim();
        const categoryVal = document.getElementById('petugasCategorySelect')?.value || 'ALL';
        const damageTypeVal = document.getElementById('petugasDamageTypeSelect')?.value || 'ALL';

        const resetBtn = document.getElementById('petugasResetBtn');
        if (resetBtn) {
            const hasActiveFilter = (query !== '' || categoryVal !== 'ALL' || damageTypeVal !== 'ALL');
            resetBtn.classList.toggle('hidden', !hasActiveFilter);
        }

        const activePanel = document.getElementById('petugasTabContent-' + activePetugasTab);
        const cards = activePanel ? activePanel.querySelectorAll('.petugas-task-card') : document.querySelectorAll('.petugas-task-card');
        const rows = activePanel ? activePanel.querySelectorAll(`.petugas-${activePetugasTab}-row`) : document.querySelectorAll(`.petugas-${activePetugasTab}-row`);
        const matchedCards = [];
        const matchedRows = [];

        const matchesItem = (el) => {
            const searchData = el.getAttribute('data-search') || '';
            const category = el.getAttribute('data-category') || '';
            const damageType = el.getAttribute('data-damage-type') || 'minor';

            const matchQuery = !query || searchData.includes(query);
            const matchCategory = (categoryVal === 'ALL') || (category === categoryVal);
            const matchDamageType = (damageTypeVal === 'ALL') || (damageType === damageTypeVal);
            return matchQuery && matchCategory && matchDamageType;
        };

        cards.forEach(card => {
            if (matchesItem(card)) {
                matchedCards.push(card);
            } else {
                card.style.display = 'none';
            }
        });

        rows.forEach(row => {
            if (matchesItem(row)) {
                matchedRows.push(row);
            } else {
                row.style.display = 'none';
            }
        });

        const total = Math.max(matchedCards.length, matchedRows.length);
        const size = petugasPaginationState.size;
        const totalPages = Math.max(1, Math.ceil(total / size));
        if (petugasPaginationState.page > totalPages) petugasPaginationState.page = totalPages;
        if (petugasPaginationState.page < 1) petugasPaginationState.page = 1;

        const startIdx = (petugasPaginationState.page - 1) * size;
        const endIdx = Math.min(startIdx + size, total);

        matchedCards.forEach((card, idx) => {
            card.style.display = (idx >= startIdx && idx < endIdx) ? '' : 'none';
        });
        matchedRows.forEach((row, idx) => {
            row.style.display = (idx >= startIdx && idx < endIdx) ? '' : 'none';
        });

        const infoEl = document.getElementById('petugasPageInfo');
        if (infoEl) {
            infoEl.innerHTML = total === 0
                ? '<span class="hidden sm:inline">Menampilkan </span>0 dari 0 tugas'
                : `<span class="hidden sm:inline">Menampilkan </span>${startIdx + 1}–${endIdx} dari ${total} tugas`;
        }

        const controlsEl = document.getElementById('petugasPageControls');
        if (controlsEl) {
            if (total === 0) {
                controlsEl.innerHTML = '';
            } else {
                const btnBase = 'px-2.5 py-1.5 rounded-lg border text-xs font-semibold transition cursor-pointer';
                const btnIdle = `${btnBase} bg-white border-[#E2E8F0] text-[#0F172A] hover:bg-slate-100`;
                const btnActive = `${btnBase} bg-[#2563EB] border-[#2563EB] text-white`;
                const btnDisabled = 'px-2.5 py-1.5 rounded-lg border border-[#E2E8F0] bg-slate-100 text-slate-400 text-xs font-semibold cursor-not-allowed';

                let html = petugasPaginationState.page > 1
                    ? `<button type="button" class="${btnIdle}" onclick="goPetugasPage(${petugasPaginationState.page - 1})">Sebelumnya</button>`
                    : `<button type="button" class="${btnDisabled}" disabled>Sebelumnya</button>`;

                for (let p = 1; p <= totalPages; p++) {
                    if (totalPages > 7 && p > 2 && p < totalPages - 1 && Math.abs(p - petugasPaginationState.page) > 1) {
                        if (p === 3 || p === totalPages - 2) html += `<span class="px-1.5 text-[#475569]">…</span>`;
                        continue;
                    }
                    html += `<button type="button" class="${p === petugasPaginationState.page ? btnActive : btnIdle}" onclick="goPetugasPage(${p})">${p}</button>`;
                }

                html += petugasPaginationState.page < totalPages
                    ? `<button type="button" class="${btnIdle}" onclick="goPetugasPage(${petugasPaginationState.page + 1})">Berikutnya</button>`
                    : `<button type="button" class="${btnDisabled}" disabled>Berikutnya</button>`;

                controlsEl.innerHTML = html;
            }
        }
    }

    function resetPetugasFilters() {
        const searchInput = document.getElementById('petugasSearchInput');
        const catSelect = document.getElementById('petugasCategorySelect');
        const damageTypeSelect = document.getElementById('petugasDamageTypeSelect');

        if (searchInput) searchInput.value = '';
        if (catSelect) catSelect.value = 'ALL';
        if (damageTypeSelect) damageTypeSelect.value = 'ALL';

        applyPetugasFilters();
    }

    function focusDistrictOnPetugasMap(lat, lng, districtName) {
        if (!petugasMapInstance) initPetugasMap();
        if (petugasMapInstance) {
            petugasMapInstance.flyTo([lat, lng], 13, { duration: 0.7 });
            const mapEl = document.getElementById('petugasMap');
            if (mapEl) mapEl.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
    }

    // Inisialisasi Peta Spasial Kabupaten Kolaka Utara (Leaflet & GeoJSON Resmi)
    function initPetugasMap() {
        const mapEl = document.getElementById('petugasMap');
        if (!mapEl || petugasMapInstance) return;

        try {
            petugasMapInstance = L.map('petugasMap', { scrollWheelZoom: true }).setView([-3.5020, 120.8850], 13);
            L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 19,
                attribution: '© OpenStreetMap | GIS Kolaka Utara'
            }).addTo(petugasMapInstance);

            // Batas Administratif Resmi Wilayah Kabupaten Kolaka Utara (OpenStreetMap GeoJSON Polygon)
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
                            fillOpacity: 0.08
                        }
                    }).addTo(petugasMapInstance);
                })
                .catch(err => console.log('Gagal memuat polygon batas Kolaka Utara:', err));

            const points = @json($mapPoints ?? []);
            if (points && points.length > 0) {
                const bounds = [];
                points.forEach(p => {
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
                            color = '#2563EB';
                            statusText = 'DITUGASKAN';
                            statusBg = '#EEF2FF';
                            statusColor = '#2563EB';
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

                        // Circular Badge Pin Marker Persis Sama dengan Bupati (Gambar 4)
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

                        const marker = L.marker([lat, lng], { icon: customPinIcon }).addTo(petugasMapInstance);

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
                            }).addTo(petugasMapInstance);

                            polyline.bindTooltip(`Ruas Jalan: ${p.road_length || 'Panjang'} • ${statusText}`, {
                                direction: 'top',
                                className: 'custom-map-tooltip'
                            });

                            bounds.push([p.end_latitude, p.end_longitude]);
                        }

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
                                        <span style="font-size:11px;font-weight:800;color:#2563EB;background:#EFF6FF;border:1px solid #DBEAFE;padding:2px 8px;border-radius:6px;font-family:monospace;">
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
                                    <button type="button" onclick="openPetugasDetailModal(${p.id})" 
                                        style="width:100%;background:#2563EB;color:#FFFFFF;border:none;padding:8px 12px;border-radius:10px;font-size:11.5px;font-weight:700;cursor:pointer;display:flex;align-items:center;justify-content:center;gap:6px;box-shadow:0 2px 5px rgba(30,58,138,0.25);transition:background 0.15s;"
                                        onmouseover="this.style.background='#1D4ED8'" onmouseout="this.style.background='#2563EB'">
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
                    petugasMapInstance.fitBounds(bounds, { padding: [35, 35] });
                }
            }
        } catch (e) {
            console.error('Error initializing Leaflet map for Petugas:', e);
        }
    }

    // Data Detail Seluruh Tugas untuk Modal Pop-up Cepat Petugas (Zero Lag)
    const petugasComplaintsMap = @json($complaintsDetailMap ?? []);

    function openPetugasDetailModal(complaintId) {
        const item = petugasComplaintsMap[complaintId];
        if (!item) return;

        // 1. Code, Category, Status, Damage Type
        document.getElementById('pmCode').innerText = '#' + item.public_code;
        document.getElementById('pmCategory').innerText = item.category_name;

        const typeBadge = document.getElementById('pmType');
        if (typeBadge) {
            typeBadge.className = 'shrink-0 px-2.5 py-1 rounded-md text-xs font-semibold bg-red-50 text-red-700 border border-red-200 inline-flex items-center gap-1.5 whitespace-nowrap';
            typeBadge.innerHTML = `<span class="w-1.5 h-1.5 rounded-full bg-red-600 shrink-0"></span><span>${item.damage_type === 'major' ? 'Kerusakan Berat' : 'Kerusakan Ringan'}</span>`;
        }

        let statusLabel = item.status;
        let statusClass = 'bg-slate-100 text-slate-700 border-slate-200';
        if (item.status === 'COMPLETED' || item.status === 'CLOSED') {
            statusLabel = 'SELESAI';
            statusClass = 'bg-emerald-50 text-emerald-800 border-emerald-200';
        } else if (item.status === 'IN_PROGRESS') {
            statusLabel = 'DIKERJAKAN';
            statusClass = 'bg-amber-50 text-amber-800 border-amber-300';
        } else if (item.status === 'DISPATCHED') {
            statusLabel = 'DITUGASKAN';
            statusClass = 'bg-blue-50 text-[#2563EB] border-blue-200';
        } else if (item.status === 'WAITING_VERIFICATION') {
            statusLabel = 'VALIDASI';
            statusClass = 'bg-amber-50 text-amber-700 border-amber-300';
        } else if (item.status === 'REVISION_REQUIRED') {
            statusLabel = 'PERLU REVISI';
            statusClass = 'bg-amber-50 text-amber-800 border-amber-300';
        } else if (item.status === 'REJECTED') {
            statusLabel = 'DITOLAK';
            statusClass = 'bg-red-50 text-red-700 border-red-200';
        } else if (item.status === 'SUBMITTED' || item.status === 'VERIFICATION') {
            statusLabel = 'VERIFIKASI';
            statusClass = 'bg-red-50 text-red-700 border-red-200';
        }

        const stEl = document.getElementById('pmStatus');
        stEl.innerText = statusLabel;
        stEl.className = `shrink-0 text-xs font-bold px-2.5 py-1 rounded-md border ${statusClass} whitespace-nowrap`;

        // 2. Foto Bukti Kerusakan Warga (Bagian Atas)
        const photoBefore = document.getElementById('pmPhotoBefore');
        const photoBeforeContainer = document.getElementById('pmPhotoBeforeContainer');
        const gallery = document.getElementById('pmPhotoGallery');
        const photoLabel = document.getElementById('pmPhotoBeforeLabel');

        const citizenPhotos = (item.citizen_photos && item.citizen_photos.length > 0)
            ? item.citizen_photos
            : (item.photo_url ? [item.photo_url] : []);

        if (citizenPhotos.length > 0) {
            photoBefore.src = citizenPhotos[0];
            photoBeforeContainer.classList.remove('hidden');
            if (photoLabel) photoLabel.innerText = 'Foto Bukti Kerusakan (Warga)';
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
                    thumb.className = `w-14 h-14 rounded-xl object-cover cursor-pointer border-2 transition ${idx === 0 ? 'border-[#2563EB] ring-2 ring-blue-200' : 'border-slate-200 opacity-70 hover:opacity-100'}`;
                    thumb.onclick = () => {
                        photoBefore.src = url;
                        if (photoLabel) photoLabel.innerText = `Foto Bukti ${idx + 1} dari ${citizenPhotos.length}`;
                        gallery.querySelectorAll('img').forEach(img => {
                            img.className = 'w-14 h-14 rounded-xl object-cover cursor-pointer border-2 border-slate-200 opacity-70 hover:opacity-100 transition';
                        });
                        thumb.className = 'w-14 h-14 rounded-xl object-cover cursor-pointer border-2 border-[#2563EB] ring-2 ring-blue-200 transition';
                    };
                    gallery.appendChild(thumb);
                });
            } else {
                gallery.classList.add('hidden');
            }
        }

        // 2b. Foto Bukti Penanganan Pekerja Lapangan (Selalu tampil berdampingan)
        const photoAfter = document.getElementById('pmPhotoAfter');
        const photoAfterContainer = document.getElementById('pmPhotoAfterContainer');
        const photoAfterPlaceholder = document.getElementById('pmPhotoAfterPlaceholder');
        const officerGallery = document.getElementById('pmPhotoOfficerGallery');
        const photoAfterLabel = document.getElementById('pmPhotoAfterLabel');

        const officerPhotos = (item.officer_photos && item.officer_photos.length > 0)
            ? item.officer_photos
            : (item.completion_photo_url ? [item.completion_photo_url] : (item.photo_after_url ? [item.photo_after_url] : []));

        if (photoAfterContainer) photoAfterContainer.classList.remove('hidden');
        if (officerPhotos.length > 0) {
            photoAfter.src = officerPhotos[0];
            photoAfter.classList.remove('hidden');
            if (photoAfterPlaceholder) photoAfterPlaceholder.classList.add('hidden');
            if (photoAfterLabel) photoAfterLabel.innerText = 'Foto Hasil Pengerjaan';
        } else {
            photoAfter.classList.add('hidden');
            if (photoAfterPlaceholder) photoAfterPlaceholder.classList.remove('hidden');
            if (photoAfterLabel) photoAfterLabel.innerText = 'Foto Hasil Pengerjaan';
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

        // 3. Reporter info
        document.getElementById('pmReporter').innerText = item.reporter_name;
        const phoneEl = document.getElementById('pmPhone');
        const phoneLink = document.getElementById('pmPhoneLink');
        if (phoneEl) phoneEl.innerText = item.reporter_phone;
        if (phoneLink) {
            const cleanPhone = (item.reporter_phone || '').replace(/[^0-9]/g, '');
            if (cleanPhone && cleanPhone.length > 6) {
                const waNumber = cleanPhone.startsWith('0') ? '62' + cleanPhone.slice(1) : cleanPhone;
                phoneLink.href = 'https://wa.me/' + waNumber;
                phoneLink.classList.remove('hidden');
            } else {
                phoneLink.classList.add('hidden');
            }
        }
        document.getElementById('pmCreated').innerText = item.created_at_formatted || item.created_at_human;

        // 4. Content
        document.getElementById('pmTitle').innerText = item.title;
        document.getElementById('pmAddress').innerText = item.address;
        document.getElementById('pmCoords').innerText = item.latitude + ', ' + item.longitude;
        document.getElementById('pmDescription').innerText = item.description;

        // Major road box
        const majorBox = document.getElementById('pmMajorRoadBox');
        if (majorBox) {
            if (item.damage_type === 'major' || item.road_length) {
                majorBox.classList.remove('hidden');
                const rlClean = (item.road_length || '').toString().replace(/\s*(meter|m)\s*$/i, '').trim();
                document.getElementById('pmRoadLength').innerText = rlClean ? rlClean + ' Meter' : '≥ 500 Meter';
                document.getElementById('pmRoadCondition').innerText = item.road_condition || 'Jalan Tanah / Rusak';
                document.getElementById('pmStartCoords').innerText = `${item.latitude}, ${item.longitude}`;
                document.getElementById('pmEndCoords').innerText = (item.end_latitude && item.end_longitude) ? `${item.end_latitude}, ${item.end_longitude}` : 'Tidak ditentukan';
            } else {
                majorBox.classList.add('hidden');
            }
        }

        // Regu Lapangan Pelaksana & Koordinator Lapangan
        const pmTeamNameEl = document.getElementById('pmTeamName');
        if (pmTeamNameEl) {
            pmTeamNameEl.innerText = item.team_name ? (item.team_code ? `[${item.team_code}] ${item.team_name}` : item.team_name) : (item.assignee_name || 'Regu Lapangan PUPR');
        }
        const pmOfficerDeptEl = document.getElementById('pmOfficerDept');
        if (pmOfficerDeptEl) {
            pmOfficerDeptEl.innerText = item.department_name || 'Dinas Pekerjaan Umum dan Penataan Ruang (PUPR)';
        }
        const pmTeamLeaderEl = document.getElementById('pmTeamLeader');
        if (pmTeamLeaderEl) {
            pmTeamLeaderEl.innerText = item.team_leader || item.assignee_name || 'Pekerja Lapangan';
        }
        const pmTeamPhoneEl = document.getElementById('pmTeamPhone');
        const pmTeamPhoneTextEl = document.getElementById('pmTeamPhoneText');
        const teamPhone = item.team_leader_phone || item.assignee_phone || '081100000004';
        if (pmTeamPhoneTextEl) pmTeamPhoneTextEl.innerText = teamPhone;
        if (pmTeamPhoneEl) {
            const cleanTeamPhone = (teamPhone || '').replace(/[^0-9]/g, '');
            pmTeamPhoneEl.href = cleanTeamPhone ? `tel:${cleanTeamPhone}` : '#';
        }

        // Deadline & Instructions
        document.getElementById('pmDeadline').innerText = item.deadline_human;
        document.getElementById('pmInstruction').innerText = item.instruction || 'Lakukan perbaikan dan penanganan fisik sesuai SOP dinas teknis';
        if (document.getElementById('pmDepartment')) document.getElementById('pmDepartment').innerText = item.department_name || '';
        if (document.getElementById('pmAssignee')) document.getElementById('pmAssignee').innerText = item.assignee_name || '';

        // AI estimate
        const aiEstimateBox = document.getElementById('pmAiEstimateBox');
        if (aiEstimateBox) {
            if (item.ai_repair_estimate) {
                aiEstimateBox.classList.remove('hidden');
                document.getElementById('pmAiEstimate').innerText = item.ai_repair_estimate;
            } else {
                aiEstimateBox.classList.add('hidden');
            }
        }

        // Action Buttons in Modal Footer (Left Side)
        const actionContainer = document.getElementById('pmActionContainer');
        if (actionContainer) {
            if (item.status === 'DISPATCHED') {
                actionContainer.innerHTML = `
                    <form method="POST" action="/petugas/complaints/${item.id}/start" class="w-full sm:w-auto">
                        <input type="hidden" name="_token" value="${document.querySelector('meta[name="csrf-token"]')?.content || '{{ csrf_token() }}'}">
                        <button type="submit" class="w-full sm:w-auto px-5 py-2.5 rounded-xl bg-[#2563EB] hover:bg-[#1D4ED8] text-white font-bold text-xs shadow-xs transition flex items-center justify-center gap-2 cursor-pointer">
                            <i data-lucide="play" class="w-4 h-4"></i>
                            <span>Mulai Kerja</span>
                        </button>
                    </form>
                `;
            } else if (item.status === 'IN_PROGRESS' || item.status === 'REVISION_REQUIRED') {
                actionContainer.innerHTML = `
                    <button type="button" onclick="const cid = ${item.id}; closePetugasDetailModal(); openPetugasFinishModal(cid);" class="w-full sm:w-auto px-5 py-2.5 rounded-xl bg-[#2563EB] hover:bg-[#1D4ED8] text-white font-bold text-xs shadow-xs transition flex items-center justify-center gap-2 cursor-pointer">
                        <i data-lucide="upload" class="w-4 h-4"></i>
                        <span>Unggah Bukti</span>
                    </button>
                `;
            } else {
                actionContainer.innerHTML = '';
            }
        }

        // Modal open (Zero Lag)
        const modalEl = document.getElementById('petugasDetailModal');
        const scrollBox = modalEl.querySelector('.overflow-y-auto');
        if (scrollBox) scrollBox.scrollTop = 0;
        modalEl.classList.remove('hidden');
        document.body.style.overflow = 'hidden';
        if (window.lucide) {
            lucide.createIcons({ root: modalEl });
        }
    }

    function closePetugasDetailModal() {
        const modalEl = document.getElementById('petugasDetailModal');
        if (modalEl) modalEl.classList.add('hidden');
        document.body.style.overflow = '';
    }

    function openPetugasProfileModal() {
        const modal = document.getElementById('petugasProfileModal');
        const content = document.getElementById('petugasProfileModalContent');
        modal.classList.remove('hidden');
        modal.classList.add('flex');
        setTimeout(() => {
            content.classList.remove('scale-95', 'opacity-0');
            content.classList.add('scale-100', 'opacity-100');
        }, 10);
        if (window.lucide) lucide.createIcons({ root: modal });
    }

    function closePetugasProfileModal() {
        const modal = document.getElementById('petugasProfileModal');
        const content = document.getElementById('petugasProfileModalContent');
        if (modal && content) {
            content.classList.remove('scale-100', 'opacity-100');
            content.classList.add('scale-95', 'opacity-0');
            setTimeout(() => {
                modal.classList.add('hidden');
                modal.classList.remove('flex');
            }, 180);
        }
    }

    function openPetugasFinishModal(id, code, title) {
        const item = (typeof petugasComplaintsMap !== 'undefined' && petugasComplaintsMap[id]) ? petugasComplaintsMap[id] : {};
        const form = document.getElementById('petugasFinishForm');
        if (form) {
            form.action = `/petugas/complaints/${id}/finish`;
            form.reset();
        }

        const finalCode = code || item.public_code || '';
        const finalTitle = title || item.title || '-';
        const reporterName = item.reporter_name || '-';
        const locationAddr = item.address || '-';
        const photoUrl = (item.citizen_photos && item.citizen_photos.length > 0)
            ? item.citizen_photos[0]
            : (item.photo_url || '');

        document.getElementById('finishModalCode').innerText = finalCode ? ('#' + finalCode) : '#-';
        document.getElementById('finishModalTitle').innerText = finalTitle;
        const reporterEl = document.getElementById('finishModalReporter');
        if (reporterEl) reporterEl.innerText = reporterName;
        const addrEl = document.getElementById('finishModalAddress');
        if (addrEl) addrEl.innerText = locationAddr;

        const imgEl = document.getElementById('finishModalPhoto');
        const fallbackEl = document.getElementById('finishModalPhotoFallback');
        if (imgEl && fallbackEl) {
            if (photoUrl) {
                imgEl.src = photoUrl;
                imgEl.classList.remove('hidden');
                fallbackEl.classList.add('hidden');
            } else {
                imgEl.src = '';
                imgEl.classList.add('hidden');
                fallbackEl.classList.remove('hidden');
            }
        }

        const modal = document.getElementById('petugasFinishModal');
        if (modal) {
            modal.classList.remove('hidden');
            document.body.style.overflow = 'hidden';
            if (window.lucide) lucide.createIcons({ root: modal });
        }
    }

    function closePetugasFinishModal() {
        const modal = document.getElementById('petugasFinishModal');
        if (modal) {
            modal.classList.add('hidden');
            document.body.style.overflow = '';
        }
    }

    // Modal click outside & escape listeners
    window.addEventListener('click', function(e) {
        const detailModal = document.getElementById('petugasDetailModal');
        if (e.target === detailModal) {
            closePetugasDetailModal();
        }
        const finishModal = document.getElementById('petugasFinishModal');
        if (e.target === finishModal) {
            closePetugasFinishModal();
        }
    });

    window.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closePetugasDetailModal();
            closePetugasProfileModal();
            closePetugasFinishModal();
        }
    });

    document.addEventListener('DOMContentLoaded', function() {
        initPetugasMap();
        if (window.innerWidth < 768) {
            setPetugasLayout('grid');
        }
    });
</script>

@push('modals')
    <!-- MODAL DETAIL LENGKAP PENGADUAN PEKERJA LAPANGAN (Desain 1:1 Landing & Admin SI-APPIK V2 — 760px Desktop & Mobile Bottom Sheet) -->
    <div id="petugasDetailModal" class="hidden fixed inset-0 z-50 bg-slate-900/65 flex items-end sm:items-center justify-center p-3.5 sm:p-5 overscroll-contain">
        <div class="bg-white rounded-[24px] border border-[#E2E8F0] shadow-[0_20px_50px_rgba(15,23,42,0.22)] sm:max-w-[760px] w-full max-h-[86vh] sm:max-h-[90vh] overflow-hidden flex flex-col relative">

            <!-- Drag Handle Khusus Mobile Bottom Sheet -->
            <div class="sm:hidden flex justify-center pt-3 pb-1 bg-white shrink-0">
                <span class="w-11 h-1 rounded-full bg-[#CBD5E1]"></span>
            </div>

            <!-- Sticky Modal Top Bar (3 Badge 1 Baris + Tanggal & Kode Aduan + Tombol X di Tengah Kanan) -->
            <div class="px-4 sm:px-7 pt-2.5 pb-3.5 sm:py-5 border-b border-[#E2E8F0] bg-white flex items-center justify-between gap-2.5 shrink-0">
                <div class="min-w-0 flex-1 space-y-2">
                    <div class="flex items-center gap-1 sm:gap-2 flex-nowrap overflow-x-auto no-scrollbar">
                        <span id="pmType" class="shrink-0 px-2 sm:px-3 py-0.5 sm:py-1 rounded-full text-[10.5px] sm:text-xs font-bold bg-[#FEF2F2] text-[#DC2626] border border-[#FECACA] inline-flex items-center gap-1 whitespace-nowrap">
                            <span class="w-1.5 h-1.5 rounded-full bg-[#DC2626] shrink-0"></span>
                            <span>Kerusakan Ringan</span>
                        </span>
                        <span id="pmCategory" class="shrink min-w-0 truncate px-2 sm:px-3 py-0.5 sm:py-1 rounded-full text-[10.5px] sm:text-xs font-semibold bg-[#F1F5F9] text-[#475569] border border-[#E2E8F0]">
                            Infrastruktur
                        </span>
                        <span id="pmStatus" class="shrink-0 text-[10.5px] sm:text-xs font-bold px-2 sm:px-3 py-0.5 sm:py-1 rounded-full bg-[#EFF6FF] text-[#2563EB] border border-[#BFDBFE] whitespace-nowrap">
                            DIKERJAKAN
                        </span>
                    </div>
                    <div class="flex items-center flex-wrap gap-2 text-[11.5px] sm:text-[13px] text-[#475569]">
                        <div class="flex items-center gap-1.5 font-medium">
                            <i data-lucide="calendar" class="w-3.5 h-3.5 text-[#64748B] shrink-0"></i>
                            <span id="pmCreated">02 Sep 2026, 16:28 WITA</span>
                        </div>
                        <span class="text-slate-300">&bull;</span>
                        <span id="pmCode" class="font-mono font-bold text-[#2563EB] bg-[#EFF6FF] border border-[#BFDBFE] px-2 py-0.5 rounded-full text-[10.5px] sm:text-[11px]">#ADU-0001</span>
                    </div>
                </div>
                <button type="button" onclick="closePetugasDetailModal()" class="w-8 h-8 sm:w-9 sm:h-9 rounded-[10px] bg-[#F8FAFC] hover:bg-slate-100 text-[#475569] hover:text-[#0F172A] border border-[#E2E8F0] flex items-center justify-center transition cursor-pointer shrink-0" title="Tutup Detail" aria-label="Tutup Rincian">
                    <i data-lucide="x" class="w-4 h-4"></i>
                </button>
            </div>

            <!-- Modal Body Scrollable -->
            <div class="px-5 py-4 sm:px-7 sm:py-5 overflow-y-auto space-y-4 flex-1 overscroll-contain bg-white" style="-webkit-overflow-scrolling: touch;">

                <!-- Judul Aduan -->
                <h3 id="pmTitle" class="font-outfit font-bold text-lg sm:text-xl text-[#0F172A] leading-snug">
                    Judul Laporan
                </h3>

                <!-- 1. Galeri Foto Berdampingan 2 Kolom (Rasio 16:10 Side-by-Side) -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5 sm:gap-3.5">
                    <!-- Kiri: Foto Kondisi Lokasi -->
                    <div id="pmPhotoBeforeContainer" class="space-y-1.5">
                        <div class="aspect-[16/10] rounded-xl sm:rounded-2xl overflow-hidden border border-[#E2E8F0] bg-[#1E293B] relative flex items-center justify-center">
                            <img id="pmPhotoBefore" src="data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7" alt="Foto Kondisi Lokasi" class="w-full h-full object-cover">
                            <div class="absolute bottom-2 left-2 sm:bottom-3 sm:left-3 bg-[#0F172A]/85 text-white text-[10px] sm:text-xs font-semibold px-2.5 py-1 rounded-full flex items-center gap-1.5 pointer-events-none">
                                <i data-lucide="camera" class="w-3 h-3 shrink-0 hidden sm:inline"></i>
                                <span id="pmPhotoBeforeLabel">Foto Kondisi Lokasi</span>
                            </div>
                        </div>
                        <div id="pmPhotoGallery" class="flex items-center gap-1.5 overflow-x-auto pb-0.5 hidden"></div>
                    </div>

                    <!-- Kanan: Foto Hasil Pengerjaan -->
                    <div id="pmPhotoAfterContainer" class="space-y-1.5">
                        <div class="aspect-[16/10] rounded-xl sm:rounded-2xl overflow-hidden border border-[#A7F3D0] bg-[#ECFDF5] relative flex items-center justify-center">
                            <img id="pmPhotoAfter" src="data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7" alt="Foto Hasil Pengerjaan" class="w-full h-full object-cover">
                            <div id="pmPhotoAfterPlaceholder" class="hidden inset-0 absolute flex flex-col items-center justify-center text-center p-3 text-[#047857] bg-[#ECFDF5]">
                                <i data-lucide="clock" class="w-5 h-5 mb-1 text-[#10B981]"></i>
                                <span class="text-[11px] sm:text-xs font-semibold leading-tight">Menunggu Foto Hasil Pengerjaan</span>
                            </div>
                            <div class="absolute bottom-2 left-2 sm:bottom-3 sm:left-3 bg-[#047857]/90 text-white text-[10px] sm:text-xs font-semibold px-2.5 py-1 rounded-full flex items-center gap-1.5 pointer-events-none">
                                <i data-lucide="badge-check" class="w-3 h-3 shrink-0 hidden sm:inline"></i>
                                <span id="pmPhotoAfterLabel">Foto Hasil Pengerjaan</span>
                            </div>
                        </div>
                        <div id="pmPhotoOfficerGallery" class="flex items-center gap-1.5 overflow-x-auto pb-0.5 hidden"></div>
                    </div>
                </div>

                <!-- 2. Grid Informasi Pelapor & Lokasi (2 Kolom Seimbang) -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 sm:gap-3.5">
                    <!-- Kartu Kiri: Pelapor Aduan -->
                    <div class="p-4 rounded-2xl bg-[#F8FAFC] border border-[#E2E8F0] space-y-1.5">
                        <div class="flex items-center gap-1.5 text-[11px] font-bold text-[#475569] uppercase tracking-wider">
                            <i data-lucide="user" class="w-3.5 h-3.5 text-[#2563EB]"></i>
                            <span>Pelapor Aduan</span>
                        </div>
                        <span id="pmReporter" class="font-outfit font-bold text-sm sm:text-base text-[#0F172A] block truncate">Nama Pelapor</span>
                        <div class="flex items-center gap-2 text-xs font-bold text-[#047857]">
                            <i data-lucide="shield-check" class="w-3.5 h-3.5 text-[#10B981] shrink-0"></i>
                            <span id="pmPhone" class="font-mono">081344697178</span>
                            <a id="pmPhoneLink" href="#" target="_blank" class="ml-auto px-2 py-0.5 rounded-lg bg-[#ECFDF5] hover:bg-[#D1FAE5] text-[#047857] border border-[#A7F3D0] font-bold text-[11px] inline-flex items-center gap-1 transition">
                                <i data-lucide="phone" class="w-3 h-3"></i>
                                <span>Hubungi</span>
                            </a>
                        </div>
                    </div>

                    <!-- Kartu Kanan: Lokasi Kerusakan -->
                    <div class="p-4 rounded-2xl bg-[#F8FAFC] border border-[#E2E8F0] space-y-2 flex flex-col justify-between">
                        <div class="space-y-1">
                            <div class="flex items-center gap-1.5 text-[11px] font-bold text-[#475569] uppercase tracking-wider">
                                <i data-lucide="map-pin" class="w-3.5 h-3.5 text-[#2563EB]"></i>
                                <span>Lokasi Kerusakan</span>
                            </div>
                            <p id="pmAddress" class="text-xs sm:text-[13px] font-semibold text-[#0F172A] leading-relaxed">
                                Alamat lokasi kerusakan...
                            </p>
                        </div>
                        <div class="flex items-center justify-between gap-2 pt-0.5">
                            <span id="pmCoords" class="hidden">-</span>
                            <button type="button" onclick="const c = (document.getElementById('pmCoords')?.textContent || '').trim(); if(c && c !== '-') window.open('https://www.google.com/maps?q=' + encodeURIComponent(c.replace(/\s+/g,'')), '_blank', 'noopener');"
                                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-[#EFF6FF] hover:bg-[#DBEAFE] text-[#2563EB] border border-[#BFDBFE] text-xs font-bold transition cursor-pointer">
                                <i data-lucide="map" class="w-3.5 h-3.5"></i>
                                <span>Buka di Peta</span>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- 3. Regu Penanganan Lapangan -->
                <div id="pmOfficerCard" class="p-3.5 sm:p-4 rounded-2xl bg-[#EFF6FF]/60 border border-[#BFDBFE] space-y-2.5">
                    <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-2.5">
                        <div class="flex items-start gap-2.5 min-w-0">
                            <div class="w-9 h-9 rounded-xl bg-[#2563EB] text-white flex items-center justify-center font-bold text-xs shrink-0">
                                <i data-lucide="hard-hat" class="w-4 h-4"></i>
                            </div>
                            <div class="min-w-0">
                                <span class="text-[10px] text-[#2563EB] font-extrabold uppercase tracking-wider block">Regu Lapangan Pelaksana</span>
                                <h4 id="pmTeamName" class="font-outfit font-bold text-xs sm:text-sm text-[#0F172A] break-words leading-snug">
                                    Regu Lapangan PUPR
                                </h4>
                                <span id="pmOfficerDept" class="text-[11px] font-medium text-[#475569] block break-words mt-0.5">
                                    Dinas Pekerjaan Umum dan Penataan Ruang (PUPR)
                                </span>
                            </div>
                        </div>
                        <div id="pmDeadlineBox" class="flex sm:block items-center justify-between sm:text-right gap-2 pt-2 sm:pt-0 border-t sm:border-t-0 border-[#BFDBFE]/50 shrink-0">
                            <span class="text-[10px] text-[#64748B] font-medium sm:block">Batas Waktu:</span>
                            <span id="pmDeadline" class="text-xs font-bold text-[#2563EB] bg-white px-2.5 py-0.5 rounded-lg border border-[#BFDBFE] inline-block sm:mt-0.5">Segera</span>
                        </div>
                    </div>

                    <div class="p-2.5 rounded-xl bg-white border border-[#E2E8F0] flex flex-wrap items-center justify-between gap-2 text-xs">
                        <div class="flex items-center gap-2 min-w-0">
                            <div class="w-6 h-6 rounded-lg bg-[#EFF6FF] text-[#2563EB] flex items-center justify-center shrink-0">
                                <i data-lucide="crown" class="w-3.5 h-3.5"></i>
                            </div>
                            <div class="min-w-0">
                                <span class="text-[10px] text-[#64748B] font-bold block leading-none">Ketua Regu:</span>
                                <span id="pmTeamLeader" class="font-bold text-[#0F172A] break-words block mt-0.5">Nama Ketua Regu</span>
                            </div>
                        </div>
                        <a id="pmTeamPhone" href="#" class="px-2.5 py-1 rounded-lg bg-[#EFF6FF] hover:bg-[#DBEAFE] text-[#2563EB] font-bold font-mono text-xs flex items-center gap-1.5 transition shrink-0">
                            <i data-lucide="phone-call" class="w-3 h-3"></i>
                            <span id="pmTeamPhoneText">081234567816</span>
                        </a>
                    </div>

                    <span id="pmDepartment" class="hidden"></span>
                    <span id="pmAssignee" class="hidden"></span>

                    <div id="pmInstructionRow" class="text-xs text-[#475569] flex items-start gap-1.5 pt-1.5 border-t border-[#BFDBFE]/60">
                        <i data-lucide="clipboard-list" class="w-3.5 h-3.5 text-[#2563EB] shrink-0 mt-0.5"></i>
                        <span>Instruksi: <span id="pmInstruction" class="text-[#0F172A] font-medium italic">Lakukan perbaikan</span></span>
                    </div>
                </div>

                <!-- 4. Detail Kerusakan Ruas Panjang -->
                <div id="pmMajorRoadBox" class="hidden p-4 rounded-2xl bg-[#EFF6FF]/60 border border-[#BFDBFE] space-y-2">
                    <div class="flex items-center justify-between text-xs font-bold text-[#2563EB]">
                        <span class="flex items-center gap-1.5">
                            <i data-lucide="git-commit" class="w-4 h-4 text-[#2563EB]"></i>
                            <span>Detail Kerusakan Ruas Panjang</span>
                        </span>
                        <span class="text-[10px] text-white bg-[#2563EB] px-2.5 py-0.5 rounded-full font-bold">
                            ≥ 500 Meter
                        </span>
                    </div>
                    <div class="grid grid-cols-2 gap-2 text-xs">
                        <div class="p-2.5 rounded-xl bg-white border border-[#E2E8F0]">
                            <span class="text-[10px] text-[#64748B] block font-semibold">Estimasi Panjang Ruas:</span>
                            <span id="pmRoadLength" class="font-bold text-[#0F172A]">-</span>
                        </div>
                        <div class="p-2.5 rounded-xl bg-white border border-[#E2E8F0]">
                            <span class="text-[10px] text-[#64748B] block font-semibold">Kondisi Permukaan:</span>
                            <span id="pmRoadCondition" class="font-bold text-[#0F172A]">-</span>
                        </div>
                        <div class="p-2.5 rounded-xl bg-white border border-[#E2E8F0] col-span-2">
                            <span class="text-[10px] text-[#64748B] block font-semibold">Koordinat Titik Awal &amp; Akhir:</span>
                            <div class="font-mono text-[11px] text-[#334155] flex flex-col gap-0.5 mt-1">
                                <div><span class="font-bold text-[#2563EB]">Titik Awal:</span> <span id="pmStartCoords">-</span></div>
                                <div><span class="font-bold text-[#0F172A]">Titik Akhir:</span> <span id="pmEndCoords">-</span></div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 5. Deskripsi Lengkap -->
                <div class="space-y-1.5">
                    <span class="text-[11px] font-bold text-[#475569] uppercase tracking-wider block">Deskripsi Kondisi Kerusakan</span>
                    <div id="pmDescription" class="p-4 rounded-2xl bg-[#F8FAFC] border border-[#E2E8F0] text-xs sm:text-[13px] text-[#334155] leading-relaxed whitespace-pre-line font-sans">
                        Deskripsi lengkap...
                    </div>
                </div>

                <!-- 6. Estimasi Material & Penanganan AI -->
                <div id="pmAiEstimateBox" class="hidden p-4 rounded-2xl bg-[#ECFDF5] border border-[#A7F3D0] space-y-1 text-xs">
                    <span class="text-[11px] font-bold text-[#047857] flex items-center gap-1.5">
                        <i data-lucide="wrench" class="w-3.5 h-3.5 text-[#10B981]"></i>
                        <span>Rekomendasi Material / Tindakan Teknis:</span>
                    </span>
                    <p id="pmAiEstimate" class="text-[#334155] font-medium pl-5 leading-relaxed">-</p>
                </div>

            </div>

            <!-- Modal Footer -->
            <div class="px-5 py-3.5 sm:px-7 border-t border-[#E2E8F0] bg-white flex flex-col-reverse sm:flex-row items-stretch sm:items-center justify-between gap-2.5 shrink-0">
                <div id="pmActionContainer" class="shrink-0"></div>
                <button type="button" onclick="closePetugasDetailModal()" 
                    class="w-full sm:w-auto px-6 py-2.5 rounded-xl bg-[#F1F5F9] hover:bg-slate-200 text-[#334155] font-bold text-xs transition cursor-pointer sm:ml-auto">
                    Tutup Rincian
                </button>
            </div>

        </div>
    </div>

    <!-- MODAL EDIT PROFIL PEKERJA LAPANGAN -->
    <div id="petugasProfileModal" class="fixed inset-0 z-[99999] hidden items-center justify-center p-4 bg-slate-900/65 transition-opacity duration-200">
        <div class="bg-white rounded-3xl max-w-md w-full shadow-2xl border border-slate-100 overflow-hidden transform transition-all scale-95 opacity-0 duration-200" id="petugasProfileModalContent">
            <div class="p-6 space-y-4">
                <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                    <div class="flex items-center gap-2.5">
                        <div class="w-10 h-10 rounded-xl bg-blue-50 text-[#2563EB] flex items-center justify-center font-bold">
                            <i data-lucide="user" class="w-5 h-5"></i>
                        </div>
                        <div>
                            <h3 class="font-outfit font-extrabold text-base text-slate-900">
                                Profil Pekerja Lapangan
                            </h3>
                            <p class="text-xs text-slate-400">
                                Perbarui identitas diri, nomor kontak, & kata sandi
                            </p>
                        </div>
                    </div>
                    <button type="button" onclick="closePetugasProfileModal()" class="p-2 rounded-xl text-slate-400 hover:text-slate-600 hover:bg-slate-100 transition cursor-pointer">
                        <i data-lucide="x" class="w-4 h-4"></i>
                    </button>
                </div>

                <form method="POST" action="{{ route('petugas.profile.update') }}" class="space-y-4">
                    @csrf
                    <input type="hidden" name="latitude" id="petugasProfileLat" value="">
                    <input type="hidden" name="longitude" id="petugasProfileLon" value="">
                    <div class="space-y-3">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Nama Lengkap</label>
                            <input type="text" name="name" value="{{ auth()->user()->name }}" required
                                class="w-full text-xs font-medium px-3 py-2.5 rounded-xl border border-slate-200 bg-slate-50 focus:bg-white focus:border-[#2563EB] focus:ring-1 focus:ring-[#2563EB] transition">
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Alamat Email</label>
                            <input type="email" name="email" value="{{ auth()->user()->email }}" required
                                class="w-full text-xs font-medium px-3 py-2.5 rounded-xl border border-slate-200 bg-slate-50 focus:bg-white focus:border-[#2563EB] focus:ring-1 focus:ring-[#2563EB] transition">
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">No. Handphone / WhatsApp</label>
                            <input type="text" name="phone" value="{{ auth()->user()->phone }}" placeholder="08xxxxxxxxxx"
                                class="w-full text-xs font-medium px-3 py-2.5 rounded-xl border border-slate-200 bg-slate-50 focus:bg-white focus:border-[#2563EB] focus:ring-1 focus:ring-[#2563EB] transition">
                        </div>

                        <div class="pt-2 border-t border-slate-100">
                            <label class="block text-xs font-bold text-slate-700 mb-1">
                                Kata Sandi Baru <span class="text-slate-400 font-normal">(Kosongkan jika tidak diubah)</span>
                            </label>
                            <input type="password" name="password" minlength="8" placeholder="Minimal 8 karakter (huruf besar, kecil, angka)..."
                                class="w-full text-xs font-medium px-3 py-2.5 rounded-xl border border-slate-200 bg-slate-50 focus:bg-white focus:border-[#2563EB] focus:ring-1 focus:ring-[#2563EB] transition">
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Konfirmasi Kata Sandi Baru</label>
                            <input type="password" name="password_confirmation" minlength="8" placeholder="Ketik ulang kata sandi baru..."
                                class="w-full text-xs font-medium px-3 py-2.5 rounded-xl border border-slate-200 bg-slate-50 focus:bg-white focus:border-[#2563EB] focus:ring-1 focus:ring-[#2563EB] transition">
                        </div>
                    </div>

                    <div class="flex items-center justify-end gap-2.5 pt-3 border-t border-slate-100">
                        <button type="button" onclick="closePetugasProfileModal()"
                            class="px-4 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs transition cursor-pointer">
                            Batal
                        </button>
                        <button type="submit"
                            class="px-5 py-2.5 rounded-xl bg-[#2563EB] hover:bg-[#1D4ED8] text-white font-bold text-xs transition flex items-center gap-1.5 shadow-md shadow-blue-600/20 cursor-pointer">
                            <i data-lucide="save" class="w-3.5 h-3.5"></i>
                            <span>Simpan Perubahan</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- MODAL KIRIM BUKTI PENYELESAIAN LAPANGAN -->
    <div id="petugasFinishModal" class="hidden fixed inset-0 z-50 overflow-y-auto bg-slate-900/65 flex items-center justify-center p-3 sm:p-5 overscroll-contain">
        <div class="card-white rounded-[24px] bg-white border border-[#E2E8F0] shadow-2xl max-w-lg w-full overflow-hidden flex flex-col relative">
            <div class="px-6 py-4 border-b border-[#E2E8F0] flex items-center justify-between bg-white">
                <div class="flex items-center gap-3 min-w-0">
                    <div class="w-10 h-10 rounded-xl bg-[#EFF6FF] text-[#2563EB] border border-blue-100 flex items-center justify-center shrink-0">
                        <i data-lucide="upload-cloud" class="w-5 h-5"></i>
                    </div>
                    <div class="min-w-0">
                        <h3 class="font-outfit font-bold text-base text-[#0F172A] truncate">Kirim Bukti Penyelesaian</h3>
                        <p class="text-[11px] text-[#64748B] truncate">Unggah foto hasil perbaikan &amp; catatan teknis lapangan</p>
                    </div>
                </div>
                <button type="button" onclick="closePetugasFinishModal()" class="w-9 h-9 rounded-[10px] bg-white hover:bg-slate-100 text-slate-500 border border-[#E2E8F0] flex items-center justify-center transition cursor-pointer shrink-0" aria-label="Tutup">
                    <i data-lucide="x" class="w-4 h-4"></i>
                </button>
            </div>
            <form id="petugasFinishForm" method="POST" action="" enctype="multipart/form-data" class="p-6 space-y-4">
                @csrf
                <div class="p-3.5 rounded-2xl bg-[#F8FAFC] border border-[#E2E8F0] flex items-start gap-3.5">
                    <div class="w-20 h-20 rounded-xl overflow-hidden border border-[#E2E8F0] bg-slate-100 shrink-0 flex items-center justify-center">
                        <img id="finishModalPhoto" src="" alt="Foto Aduan" class="w-full h-full object-cover hidden">
                        <div id="finishModalPhotoFallback" class="text-slate-400 flex items-center justify-center">
                            <i data-lucide="image" class="w-6 h-6"></i>
                        </div>
                    </div>
                    <div class="min-w-0 flex-1 space-y-1">
                        <span id="finishModalCode" class="text-[11px] font-mono font-bold text-[#2563EB] block">#</span>
                        <h4 id="finishModalTitle" class="font-outfit font-bold text-sm text-[#0F172A] line-clamp-2 leading-snug">-</h4>
                        <div class="flex items-center gap-1.5 text-[11px] text-[#475569] pt-0.5">
                            <i data-lucide="user" class="w-3.5 h-3.5 text-[#64748B] shrink-0"></i>
                            <span id="finishModalReporter" class="font-semibold truncate">-</span>
                        </div>
                        <div class="flex items-start gap-1.5 text-[11px] text-[#64748B]">
                            <i data-lucide="map-pin" class="w-3.5 h-3.5 text-[#64748B] shrink-0 mt-0.5"></i>
                            <span id="finishModalAddress" class="line-clamp-2 leading-snug">-</span>
                        </div>
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-bold text-[#334155] mb-1.5">Foto Hasil Perbaikan <span class="text-red-500">*</span></label>
                    <input type="file" name="photos[]" required accept="image/*" class="w-full text-xs text-slate-600 file:mr-2.5 file:py-2 file:px-3.5 file:rounded-lg file:border-0 file:text-xs file:font-bold file:bg-[#2563EB] file:text-white hover:file:bg-[#1D4ED8] file:cursor-pointer border border-[#E2E8F0] rounded-xl p-1.5 bg-[#F8FAFC] focus:bg-white transition">
                    <p class="text-[11px] text-[#64748B] mt-1">Unggah foto kondisi lapangan setelah selesai diperbaiki.</p>
                </div>
                <div>
                    <label class="block text-xs font-bold text-[#334155] mb-1.5">Catatan Teknis <span class="text-red-500">*</span></label>
                    <textarea name="note" rows="3" required placeholder="Contoh: Penambalan aspal selesai dan rata sesuai standar." class="w-full text-xs rounded-xl border border-[#CBD5E1] py-2.5 px-3 bg-white text-[#0F172A] focus:ring-2 focus:ring-[#2563EB] focus:border-[#2563EB] leading-relaxed"></textarea>
                </div>
                <div class="flex items-center justify-end gap-2.5 pt-4 border-t border-[#E2E8F0]">
                    <button type="button" onclick="closePetugasFinishModal()" class="px-5 py-2.5 rounded-xl bg-[#F1F5F9] hover:bg-slate-200 text-[#334155] font-bold text-xs transition cursor-pointer">
                        Batal
                    </button>
                    <button type="submit" class="px-5 py-2.5 rounded-xl bg-[#2563EB] hover:bg-[#1D4ED8] text-white font-bold text-xs transition flex items-center gap-1.5 cursor-pointer">
                        <i data-lucide="check-circle" class="w-3.5 h-3.5"></i>
                        <span>Kirim Bukti</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
@endpush
@endsection
