@extends('layouts.app')

@section('title', 'Pusat Kendali Operator - Pengaduan Infrastruktur')
@section('header_title', 'Pusat Kendali Operator')

@section('content')

{{-- Flash Notifications --}}
@if(session('success'))
<div id="flashSuccess" class="fixed top-5 right-5 z-[9999] max-w-sm w-full animate-fade-in">
    <div class="flex items-start gap-3 bg-white border border-emerald-200 shadow-xl rounded-2xl px-4 py-3">
        <div class="w-8 h-8 rounded-xl bg-emerald-50 flex items-center justify-center shrink-0">
            <i data-lucide="check-circle-2" class="w-4.5 h-4.5 text-emerald-600"></i>
        </div>
        <div class="flex-1 min-w-0">
            <p class="text-xs font-bold text-slate-900">Berhasil</p>
            <p class="text-[11px] text-slate-600 mt-0.5 leading-relaxed">{{ session('success') }}</p>
        </div>
        <button onclick="document.getElementById('flashSuccess').remove()" class="text-slate-400 hover:text-slate-600 shrink-0 cursor-pointer">
            <i data-lucide="x" class="w-3.5 h-3.5"></i>
        </button>
    </div>
</div>
<script>setTimeout(() => { const el = document.getElementById('flashSuccess'); if (el) el.remove(); }, 5000);</script>
@endif

@if(session('error'))
<div id="flashError" class="fixed top-5 right-5 z-[9999] max-w-sm w-full animate-fade-in">
    <div class="flex items-start gap-3 bg-white border border-red-200 shadow-xl rounded-2xl px-4 py-3">
        <div class="w-8 h-8 rounded-xl bg-red-50 flex items-center justify-center shrink-0">
            <i data-lucide="alert-circle" class="w-4.5 h-4.5 text-red-600"></i>
        </div>
        <div class="flex-1 min-w-0">
            <p class="text-xs font-bold text-red-700">Gagal</p>
            <p class="text-[11px] text-slate-600 mt-0.5 leading-relaxed">{{ session('error') }}</p>
        </div>
        <button onclick="document.getElementById('flashError').remove()" class="text-slate-400 hover:text-slate-600 shrink-0 cursor-pointer">
            <i data-lucide="x" class="w-3.5 h-3.5"></i>
        </button>
    </div>
</div>
<script>setTimeout(() => { const el = document.getElementById('flashError'); if (el) el.remove(); }, 7000);</script>
@endif

<div class="py-4 sm:py-6 space-y-5">

    @php
        $todayStr = \Carbon\Carbon::now('Asia/Makassar')->locale('id')->isoFormat('dddd, D MMMM Y • HH:mm:ss') . ' WITA';

        $tabMetaPhp = [
            'verify' => [
                'title' => 'Verifikasi & Telaah Kelayakan Aduan',
                'subtitle' => 'Periksa keabsahan data dan kelayakan laporan sebelum penerbitan penugasan teknis.',
                'count' => $pendingVerification->count(),
            ],
            'dispatched' => [
                'title' => 'Daftar Aduan yang Telah Ditugaskan',
                'subtitle' => 'Laporan yang telah diverifikasi dan diterbitkan surat tugas, menunggu pengerjaan di lapangan.',
                'count' => $dispatched->count(),
            ],
            'inprogress' => [
                'title' => 'Pemantauan Pengerjaan Lapangan',
                'subtitle' => 'Awasi progres fisik perbaikan dan kepatuhan batas waktu penyelesaian (SLA).',
                'count' => $inProgress->count(),
            ],
            'validation' => [
                'title' => 'Validasi Hasil Pekerjaan Lapangan',
                'subtitle' => 'Periksa foto bukti sebelum dan sesudah perbaikan sebelum laporan dinyatakan selesai.',
                'count' => $waitingValidation->count(),
            ],
            'archive' => [
                'title' => 'Arsip Aduan & Rekam Jejak Penanganan',
                'subtitle' => 'Dokumentasi permanen laporan yang telah selesai ditangani maupun ditolak.',
                'count' => $completed->count(),
            ],
            'monitoring' => [
                'title' => 'Pemantauan Eksekutif & Laporan Kinerja',
                'subtitle' => 'Analisis sebaran wilayah kerusakan, evaluasi ketepatan waktu dinas, dan cetak laporan resmi.',
                'count' => 0,
            ],
        ];
        $currentTabMeta = $tabMetaPhp[$activeTab] ?? $tabMetaPhp['verify'];
    @endphp

    <!-- Header Keterangan Section (Seragam Desain Admin) -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pt-1">
        <div>
            <h2 id="operatorSectionTitle" class="font-outfit font-bold text-lg sm:text-xl text-[#0F172A] leading-tight">
                {{ $currentTabMeta['title'] }}
            </h2>
            <p id="operatorSectionSubtitle" class="text-xs text-[#475569] mt-0.5">
                {{ $currentTabMeta['subtitle'] }}
            </p>
        </div>
        <div class="flex items-center gap-2 shrink-0">
            <span class="px-3.5 py-2 rounded-lg bg-white text-[#0F172A] text-xs font-semibold border border-[#E2E8F0] flex items-center gap-2">
                <i data-lucide="clock" class="w-4 h-4 text-[#1E3A8A]"></i>
                <span class="live-wita-clock">{{ $todayStr }}</span>
            </span>
        </div>
    </div>

    <!-- Ringkasan Statistik Utama 7 KPI Cards (Flat Bordered Seragam Admin) -->
    <div class="grid grid-cols-3 sm:grid-cols-4 lg:grid-cols-7 gap-2 sm:gap-3">
        <!-- 1. Total Aduan -->
        <div class="p-2.5 sm:p-3.5 rounded-xl bg-white border border-[#E2E8F0] flex flex-col justify-between min-w-0">
            <div class="flex items-center justify-between gap-1.5">
                <span class="text-[8.5px] sm:text-[11px] text-[#475569] font-bold uppercase tracking-normal sm:tracking-wider leading-tight">Total Aduan</span>
                <div class="w-6 h-6 sm:w-7 sm:h-7 rounded-lg bg-blue-50 text-[#1E3A8A] border border-blue-200 flex items-center justify-center shrink-0">
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
                <div class="w-6 h-6 sm:w-7 sm:h-7 rounded-lg bg-blue-50 text-[#1E40AF] border border-blue-200 flex items-center justify-center shrink-0">
                    <i data-lucide="send" class="w-3 h-3 sm:w-3.5 sm:h-3.5"></i>
                </div>
            </div>
            <div class="mt-1.5 sm:mt-2">
                <div class="font-outfit text-xl sm:text-2xl font-bold text-[#0F172A] leading-tight">
                    {{ $summary['dispatched'] }}
                </div>
                <span class="hidden sm:block text-[11px] text-[#475569] font-medium mt-0.5">Ke regu lapangan</span>
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
                <span class="hidden sm:block text-[11px] text-[#475569] font-medium mt-0.5">Uji hasil kerja</span>
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
                <div class="w-6 h-6 sm:w-7 sm:h-7 rounded-lg bg-blue-50 text-[#1E3A8A] border border-blue-200 flex items-center justify-center shrink-0">
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

    <!-- Sub Menu Tab Navigation (Seragam Desain Admin) -->
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-2 sm:gap-2.5 w-full">
        <!-- 1. Verifikasi -->
        <button type="button" onclick="switchTab('verify')" id="tabBtn-verify"
            class="tab-btn text-left p-2.5 sm:p-3 rounded-xl border transition flex items-center gap-2 sm:gap-2.5 cursor-pointer {{ $activeTab === 'verify' ? 'bg-[#1E3A8A] text-white border-[#1E3A8A]' : 'bg-white text-[#0F172A] border-[#E2E8F0] hover:bg-[#F8FAFC]' }}">
            <i data-lucide="filter" class="w-4 h-4 shrink-0 text-current"></i>
            <div class="min-w-0 flex-1">
                <span class="text-[11px] sm:text-xs font-bold block leading-tight">Verifikasi</span>
            </div>
            <span class="tab-badge px-1.5 py-0.5 rounded-md text-[10px] font-bold shrink-0 {{ $activeTab === 'verify' ? 'bg-white text-[#1E3A8A]' : 'bg-blue-50 text-[#1E3A8A] border border-blue-200' }}">
                {{ $pendingVerification->count() }}
            </span>
        </button>

        <!-- 2. Ditugaskan -->
        <button type="button" onclick="switchTab('dispatched')" id="tabBtn-dispatched"
            class="tab-btn text-left p-2.5 sm:p-3 rounded-xl border transition flex items-center gap-2 sm:gap-2.5 cursor-pointer {{ $activeTab === 'dispatched' ? 'bg-[#1E3A8A] text-white border-[#1E3A8A]' : 'bg-white text-[#0F172A] border-[#E2E8F0] hover:bg-[#F8FAFC]' }}">
            <i data-lucide="send" class="w-4 h-4 shrink-0 text-current"></i>
            <div class="min-w-0 flex-1">
                <span class="text-[11px] sm:text-xs font-bold block leading-tight">Ditugaskan</span>
            </div>
            <span class="tab-badge px-1.5 py-0.5 rounded-md text-[10px] font-bold shrink-0 {{ $activeTab === 'dispatched' ? 'bg-white text-[#1E3A8A]' : 'bg-blue-50 text-[#1E3A8A] border border-blue-200' }}">
                {{ $dispatched->count() }}
            </span>
        </button>

        <!-- 3. Dikerjakan -->
        <button type="button" onclick="switchTab('inprogress')" id="tabBtn-inprogress"
            class="tab-btn text-left p-2.5 sm:p-3 rounded-xl border transition flex items-center gap-2 sm:gap-2.5 cursor-pointer {{ $activeTab === 'inprogress' ? 'bg-[#1E3A8A] text-white border-[#1E3A8A]' : 'bg-white text-[#0F172A] border-[#E2E8F0] hover:bg-[#F8FAFC]' }}">
            <i data-lucide="hard-hat" class="w-4 h-4 shrink-0 text-current"></i>
            <div class="min-w-0 flex-1">
                <span class="text-[11px] sm:text-xs font-bold block leading-tight">Dikerjakan</span>
            </div>
            <span class="tab-badge px-1.5 py-0.5 rounded-md text-[10px] font-bold shrink-0 {{ $activeTab === 'inprogress' ? 'bg-white text-[#1E3A8A]' : 'bg-blue-50 text-[#1E3A8A] border border-blue-200' }}">
                {{ $inProgress->count() }}
            </span>
        </button>

        <!-- 4. Validasi -->
        <button type="button" onclick="switchTab('validation')" id="tabBtn-validation"
            class="tab-btn text-left p-2.5 sm:p-3 rounded-xl border transition flex items-center gap-2 sm:gap-2.5 cursor-pointer {{ $activeTab === 'validation' ? 'bg-[#1E3A8A] text-white border-[#1E3A8A]' : 'bg-white text-[#0F172A] border-[#E2E8F0] hover:bg-[#F8FAFC]' }}">
            <i data-lucide="check-square" class="w-4 h-4 shrink-0 text-current"></i>
            <div class="min-w-0 flex-1">
                <span class="text-[11px] sm:text-xs font-bold block leading-tight">Validasi</span>
            </div>
            <span class="tab-badge px-1.5 py-0.5 rounded-md text-[10px] font-bold shrink-0 {{ $activeTab === 'validation' ? 'bg-white text-[#1E3A8A]' : 'bg-blue-50 text-[#1E3A8A] border border-blue-200' }}">
                {{ $waitingValidation->count() }}
            </span>
        </button>

        <!-- 5. Arsip Aduan -->
        <button type="button" onclick="switchTab('archive')" id="tabBtn-archive"
            class="tab-btn text-left p-2.5 sm:p-3 rounded-xl border transition flex items-center gap-2 sm:gap-2.5 cursor-pointer {{ $activeTab === 'archive' ? 'bg-[#1E3A8A] text-white border-[#1E3A8A]' : 'bg-white text-[#0F172A] border-[#E2E8F0] hover:bg-[#F8FAFC]' }}">
            <i data-lucide="archive" class="w-4 h-4 shrink-0 text-current"></i>
            <div class="min-w-0 flex-1">
                <span class="text-[11px] sm:text-xs font-bold block leading-tight">Arsip Aduan</span>
            </div>
            <span class="tab-badge px-1.5 py-0.5 rounded-md text-[10px] font-bold shrink-0 {{ $activeTab === 'archive' ? 'bg-white text-[#1E3A8A]' : 'bg-blue-50 text-[#1E3A8A] border border-blue-200' }}">
                {{ $completed->count() }}
            </span>
        </button>

        <!-- 6. Laporan Kinerja -->
        <button type="button" onclick="switchTab('monitoring')" id="tabBtn-monitoring"
            class="tab-btn text-left p-2.5 sm:p-3 rounded-xl border transition flex items-center gap-2 sm:gap-2.5 cursor-pointer {{ $activeTab === 'monitoring' ? 'bg-[#1E3A8A] text-white border-[#1E3A8A]' : 'bg-white text-[#0F172A] border-[#E2E8F0] hover:bg-[#F8FAFC]' }}">
            <i data-lucide="bar-chart-2" class="w-4 h-4 shrink-0 text-current"></i>
            <div class="min-w-0 flex-1">
                <span class="text-[11px] sm:text-xs font-bold block leading-tight">Laporan Kinerja</span>
            </div>
        </button>
    </div>

    <!-- Filter Bar: Tipe Pelaporan & Pencarian Cepat (Di Bawah Sub Halaman) -->
    <div id="operatorFilterBar" class="bg-white rounded-xl p-3 sm:p-3.5 border border-[#E2E8F0] flex flex-col lg:flex-row items-stretch lg:items-center justify-between gap-2.5 {{ $activeTab === 'monitoring' ? 'hidden' : '' }}">
        <!-- Search Input -->
        <div class="relative w-full lg:flex-1 lg:min-w-[140px]">
            <i data-lucide="search" class="w-4 h-4 absolute left-3 top-2.5 text-slate-400"></i>
            <input type="text" id="filterSearchInput" placeholder="Cari kode, pelapor, atau lokasi..." 
                class="w-full bg-slate-50 border border-slate-200 rounded-xl pl-9 pr-3 py-2 text-xs text-slate-800 placeholder-slate-400 focus:outline-none focus:border-[#1E3A8A] focus:bg-white transition"
                oninput="applyFilters()">
        </div>

        <!-- Filter Controls (Kompak 2-Kolom di Mobile, Satu Baris di Desktop) -->
        <div class="grid grid-cols-2 sm:flex sm:flex-wrap lg:flex-nowrap items-center gap-2 w-full lg:w-auto shrink-0">
            <!-- Dropdown Kategori -->
            <div class="flex flex-col sm:flex-row sm:items-center gap-1 min-w-0">
                <div class="flex items-center gap-1 text-[11px] sm:text-xs text-slate-500 shrink-0 font-medium whitespace-nowrap">
                    <i data-lucide="layers" class="w-3.5 h-3.5 text-[#1E3A8A]"></i>
                    <span>Kategori:</span>
                </div>
                <select id="filterCategorySelect" onchange="applyFilters()" 
                    class="w-full sm:w-auto text-xs font-semibold rounded-xl border border-slate-200 py-2 px-2.5 lg:max-w-[140px] bg-slate-50 text-slate-700 focus:outline-none focus:border-[#1E3A8A] focus:bg-white transition shrink-0">
                    <option value="ALL">Semua Kategori</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Dropdown Tipe Kerusakan / Tipe Laporan (Ringan vs Berat) -->
            <div class="flex flex-col sm:flex-row sm:items-center gap-1 min-w-0">
                <div class="flex items-center gap-1 text-[11px] sm:text-xs text-slate-500 shrink-0 font-medium whitespace-nowrap">
                    <i data-lucide="git-commit" class="w-3.5 h-3.5 text-[#1E3A8A]"></i>
                    <span>Tipe:</span>
                </div>
                <select id="filterDamageTypeSelect" onchange="applyFilters()" 
                    class="w-full sm:w-auto text-xs font-semibold rounded-xl border border-slate-200 py-2 px-2.5 lg:max-w-[125px] bg-slate-50 text-slate-700 focus:outline-none focus:border-[#1E3A8A] focus:bg-white transition shrink-0">
                    <option value="ALL">Semua Tipe</option>
                    <option value="minor">Kerusakan Ringan</option>
                    <option value="major">Kerusakan Berat</option>
                </select>
            </div>

            <!-- Dropdown Status Khusus Tab Arsip (Selesai vs Ditolak) -->
            <div id="filterArchiveStatusContainer" class="col-span-2 sm:col-span-1 flex-col sm:flex-row sm:items-center gap-1 min-w-0 {{ $activeTab === 'archive' ? 'flex' : 'hidden' }} shrink-0">
                <div class="flex items-center gap-1 text-[11px] sm:text-xs text-slate-500 shrink-0 font-medium whitespace-nowrap">
                    <i data-lucide="check-square" class="w-3.5 h-3.5 text-[#1E3A8A]"></i>
                    <span>Status:</span>
                </div>
                <select id="filterArchiveStatusSelect" onchange="applyFilters()" 
                    class="w-full sm:w-auto text-xs font-semibold rounded-xl border border-slate-200 py-2 px-2.5 lg:max-w-[130px] bg-slate-50 text-slate-700 focus:outline-none focus:border-[#1E3A8A] focus:bg-white transition shrink-0">
                    <option value="ALL">Semua Status</option>
                    <option value="COMPLETED">Selesai</option>
                    <option value="REJECTED">Ditolak</option>
                </select>
            </div>

            <div class="col-span-2 sm:col-span-1 flex items-center justify-end gap-1.5 shrink-0">
                <!-- Tombol Reset Filter -->
                <button type="button" id="operatorResetBtn" onclick="resetFilters()"
                    class="hidden px-3 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs transition flex items-center gap-1 shrink-0 cursor-pointer">
                    <i data-lucide="rotate-ccw" class="w-3.5 h-3.5"></i>
                    <span>Reset</span>
                </button>

                <!-- Toggle Tata Letak Daftar / Petak -->
                <div class="inline-flex items-center p-0.5 rounded-lg bg-[#F8FAFC] border border-[#E2E8F0] shrink-0" role="group" aria-label="Tata letak aduan">
                    <button type="button" id="opLayoutBtn-list" onclick="setOperatorLayout('list')"
                        class="px-2.5 py-1.5 rounded-md text-xs font-semibold flex items-center gap-1.5 transition cursor-pointer bg-[#1E3A8A] text-white">
                        <i data-lucide="list" class="w-3.5 h-3.5"></i>
                        <span>Daftar</span>
                    </button>
                    <button type="button" id="opLayoutBtn-grid" onclick="setOperatorLayout('grid')"
                        class="px-2.5 py-1.5 rounded-md text-xs font-semibold flex items-center gap-1.5 transition cursor-pointer text-[#475569] hover:text-[#0F172A]">
                        <i data-lucide="layout-grid" class="w-3.5 h-3.5"></i>
                        <span>Petak</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- TAB 1: VERIFIKASI AWAL & DISPOSISI -->
    <div id="tabPanel-verify" class="tab-panel {{ $activeTab === 'verify' ? '' : 'hidden' }} space-y-4">

            @if($pendingVerification->isEmpty())
                <div class="card-white rounded-2xl p-12 text-center space-y-2 bg-white border border-[#E2E8F0]">
                    <div class="w-10 h-10 rounded-full bg-slate-100 text-slate-400 flex items-center justify-center mx-auto">
                        <i data-lucide="check" class="w-5 h-5 text-emerald-600"></i>
                    </div>
                    <h4 class="font-bold text-sm text-slate-800">Semua Laporan Telah Terverifikasi!</h4>
                    <p class="text-xs text-slate-400 max-w-sm mx-auto">Tidak ada antrean laporan baru yang menunggu verifikasi saat ini.</p>
                </div>
            @else
                @include('partials.role_complaints_table', ['wrapId' => 'operatorTableWrap-verify', 'rowClass' => 'operator-verify-row', 'items' => $pendingVerification, 'mode' => 'operator-verify', 'hidden' => false])
                <!-- Grid Card Antrean (Lebar Proporsional, Rapi, & Sejajar) -->
                <div id="verifyCardsContainer" class="hidden grid grid-cols-1 lg:grid-cols-2 gap-5">
                    @foreach($pendingVerification as $c)
                        <div onclick="if (window.innerWidth < 768) { openOperatorModal({{ $c->id }}); }" class="complaint-verify-card card-white rounded-2xl p-4 sm:p-5 bg-white border border-slate-200/90 hover:border-blue-300 hover:shadow-md transition-all duration-200 flex flex-col justify-between space-y-3.5 cursor-pointer md:cursor-default"
                            data-category="{{ $c->category_id }}"
                            data-damage-type="{{ $c->damage_type ?? 'minor' }}"
                            data-search="{{ strtolower($c->public_code . ' ' . ($c->user?->name ?? $c->citizen?->name ?? '') . ' ' . $c->title . ' ' . $c->address) }}">
                            
                            <div class="space-y-3">
                                <!-- 1. Header Card: 1 Baris Sejajar - Kode + Tipe + Kategori (Kiri) & Waktu (Kanan) -->
                                <div class="flex items-center justify-between gap-2 pb-2.5 border-b border-slate-100 min-w-0">
                                    <div class="flex items-center gap-1.5 min-w-0 flex-nowrap overflow-hidden">
                                        <span class="px-2 py-0.5 rounded-lg bg-[#1E3A8A]/10 text-[#1E3A8A] font-bold text-xs font-mono tracking-tight border border-blue-100/60 whitespace-nowrap shrink-0">
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

                                <!-- 2. Konten Utama: Foto Kerusakan (Kiri) & Rincian Lengkap (Kanan) -->
                                <div class="flex gap-4 items-stretch">
                                    @php
                                        $firstPhotoUrl = $c->photos->isNotEmpty() 
                                            ? ($c->photos->first()->url ?? asset('storage/' . $c->photos->first()->storage_key)) 
                                            : null;
                                    @endphp
                                    
                                    <!-- Thumbnail Foto Proporsional -->
                                    <div class="relative w-24 h-24 sm:w-28 sm:h-28 rounded-2xl overflow-hidden border border-slate-200/90 bg-slate-100 shrink-0 shadow-2xs">
                                        @if($firstPhotoUrl)
                                            <img src="{{ $firstPhotoUrl }}" alt="Foto Kerusakan" class="w-full h-full object-cover">
                                            @if($c->photos->count() > 1)
                                                <span class="absolute bottom-1.5 right-1.5 bg-slate-900/80 text-white text-[10px] font-bold px-1.5 py-0.5 rounded-lg flex items-center gap-0.5 shadow-xs">
                                                    <i data-lucide="layers" class="w-2.5 h-2.5"></i>
                                                    +{{ $c->photos->count() }}
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
                                            <h4 class="font-outfit font-extrabold text-sm sm:text-base text-slate-900 tracking-tight leading-snug truncate" title="{{ $c->title }}">
                                                {{ $c->title }}
                                            </h4>
                                            <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-blue-50 text-[#1E3A8A] border border-blue-200/80 shrink-0 whitespace-nowrap">
                                                {{ $c->severity_text }}
                                            </span>
                                        </div>

                                        <!-- Baris 2: Lokasi Fisik (1 Baris Rapi) -->
                                        <div class="text-xs text-slate-600 flex items-center gap-1.5 min-w-0">
                                            <i data-lucide="map-pin" class="w-3.5 h-3.5 text-[#1E3A8A] shrink-0"></i>
                                            <span class="truncate font-medium" title="{{ $c->address }}">{{ $c->address }}</span>
                                        </div>

                                        <!-- Baris 3: Info Pelapor & No Telepon (1 Baris Rapi) -->
                                        <div class="flex items-center gap-2 text-xs min-w-0">
                                            <div class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-lg bg-blue-50/90 border border-blue-100/90 text-slate-700 min-w-0 max-w-full">
                                                <i data-lucide="user" class="w-3 h-3 text-[#1E3A8A] shrink-0"></i>
                                                <span class="font-semibold text-slate-800 text-[11px] truncate">{{ $c->citizen?->name ?? 'Warga' }}</span>
                                                @if($c->citizen?->phone)
                                                    <span class="text-blue-300 shrink-0">•</span>
                                                    <span class="font-mono text-slate-600 font-medium text-[10.5px] shrink-0">{{ $c->citizen->phone }}</span>
                                                @endif
                                            </div>
                                        </div>

                                        <!-- Baris 4: Tombol Detail Lengkap & Foto (1 Baris Rapi) -->
                                        <div class="relative z-10 hidden md:block">
                                            <button type="button" onclick="openOperatorModal({{ $c->id }})" class="text-xs font-bold text-[#1E3A8A] hover:underline inline-flex items-center gap-1 cursor-pointer">
                                                <span>Lihat Rincian Lengkap & Foto</span>
                                                <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- 3. Tombol Aksi Verifikasi -->
                            <div onclick="event.stopPropagation();" class="pt-3 border-t border-slate-100/90 cursor-default">
                                <button type="button" onclick="openDispatchModal({{ $c->id }})" 
                                    class="w-full py-2.5 px-3 rounded-xl bg-[#1E3A8A] hover:bg-[#172554] active:scale-[0.99] text-white font-bold text-xs shadow-xs hover:shadow-md transition flex items-center justify-center gap-2 cursor-pointer">
                                    <i data-lucide="send" class="w-4 h-4"></i>
                                    <span>Lakukan Verifikasi</span>
                                </button>
                            </div>
                        </div>
                    @endforeach
                </div>

                <!-- Notifikasi Jika Filter Tidak Menemukan Hasil -->
                <div id="emptyFilterNotice" class="hidden card-white rounded-2xl p-8 text-center text-slate-400 text-xs bg-white border border-slate-200">
                    Tidak ditemukan laporan yang cocok dengan kata kunci atau kategori filter yang dipilih.
                </div>
            @endif
    </div>

    <!-- TAB 2: LAPORAN DITUGASKAN (MENUNGGU PETUGAS LAPANGAN MULAI BEKERJA) -->
    <div id="tabPanel-dispatched" class="tab-panel {{ $activeTab === 'dispatched' ? '' : 'hidden' }} space-y-4">
        <div class="space-y-4">
            @if($dispatched->isEmpty())
                <div class="card-white rounded-2xl p-12 text-center text-slate-400 text-xs bg-white border border-[#E2E8F0]">
                    Belum ada laporan yang berstatus ditugaskan.
                </div>
            @else
                @include('partials.role_complaints_table', ['wrapId' => 'operatorTableWrap-dispatched', 'rowClass' => 'operator-dispatched-row', 'items' => $dispatched, 'mode' => 'operator-dispatched', 'hidden' => false])
                <div id="dispatchedCardsContainer" class="hidden grid grid-cols-1 lg:grid-cols-2 gap-5">
                    @foreach($dispatched as $c)
                        @php
                            $disp = $c->latestDisposition;
                            $deadline = $disp?->deadline;
                            $firstPhotoUrl = $c->photos->isNotEmpty() 
                                ? ($c->photos->first()->url ?? asset('storage/' . $c->photos->first()->storage_key)) 
                                : null;
                        @endphp
                        <div onclick="if (window.innerWidth < 768) { openOperatorModal({{ $c->id }}); }" class="complaint-dispatched-card card-white rounded-2xl p-4 sm:p-5 bg-white border border-slate-200/90 hover:border-blue-300 hover:shadow-md transition-all duration-200 flex flex-col justify-between space-y-3.5 cursor-pointer md:cursor-default"
                            data-category="{{ $c->category_id }}"
                            data-damage-type="{{ $c->damage_type ?? 'minor' }}"
                            data-search="{{ strtolower($c->public_code . ' ' . ($c->user?->name ?? $c->citizen?->name ?? '') . ' ' . $c->title . ' ' . $c->address) }}">
                            
                            <div class="space-y-3">
                                <!-- 1. Header Card: 2 Baris - Baris 1: Kode+Tipe+Waktu | Baris 2: Kategori -->
                                <!-- 1. Header Card: 1 Baris Sejajar - Kode + Tipe + Kategori (Kiri) & Waktu (Kanan) -->
                                <div class="flex items-center justify-between gap-2 pb-2.5 border-b border-slate-100 min-w-0">
                                    <div class="flex items-center gap-1.5 min-w-0 flex-nowrap overflow-hidden">
                                        <span class="px-2 py-0.5 rounded-lg bg-[#1E3A8A]/10 text-[#1E3A8A] font-bold text-xs font-mono tracking-tight border border-blue-100/60 whitespace-nowrap shrink-0">
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

                                <!-- 2. Konten Utama: Foto di Kiri & Rincian di Kanan -->
                                <div class="flex gap-4 items-stretch">
                                    <div class="relative w-24 h-24 sm:w-28 sm:h-28 rounded-2xl overflow-hidden border border-slate-200/90 bg-slate-100 shrink-0 shadow-2xs">
                                        @if($firstPhotoUrl)
                                            <img src="{{ $firstPhotoUrl }}" alt="Foto Kerusakan" class="w-full h-full object-cover">
                                            @if($c->photos->count() > 1)
                                                <span class="absolute bottom-1.5 right-1.5 bg-slate-900/80 text-white text-[10px] font-bold px-1.5 py-0.5 rounded-lg flex items-center gap-0.5 shadow-xs">
                                                    <i data-lucide="layers" class="w-2.5 h-2.5"></i>
                                                    +{{ $c->photos->count() }}
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
                                            <h4 class="font-outfit font-extrabold text-sm sm:text-base text-slate-900 tracking-tight leading-snug truncate" title="{{ $c->title }}">
                                                {{ $c->title }}
                                            </h4>
                                            <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-blue-50 text-[#1E3A8A] border border-blue-200/80 shrink-0 whitespace-nowrap">
                                                {{ $c->severity_text }}
                                            </span>
                                        </div>

                                        <!-- Baris 2: Lokasi Fisik (1 Baris Rapi) -->
                                        <div class="text-xs text-slate-600 flex items-center gap-1.5 min-w-0">
                                            <i data-lucide="map-pin" class="w-3.5 h-3.5 text-[#1E3A8A] shrink-0"></i>
                                            <span class="truncate font-medium" title="{{ $c->address }}">{{ $c->address }}</span>
                                        </div>

                                        <!-- Baris 3: Info Pelapor & No Telepon (1 Baris Rapi) -->
                                        <div class="flex items-center gap-2 text-xs min-w-0">
                                            <div class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-lg bg-blue-50/90 border border-blue-100/90 text-slate-700 min-w-0 max-w-full">
                                                <i data-lucide="user" class="w-3 h-3 text-[#1E3A8A] shrink-0"></i>
                                                <span class="font-semibold text-slate-800 text-[11px] truncate">{{ $c->citizen?->name ?? 'Warga' }}</span>
                                                @if($c->citizen?->phone)
                                                    <span class="text-blue-300 shrink-0">•</span>
                                                    <span class="font-mono text-slate-600 font-medium text-[10.5px] shrink-0">{{ $c->citizen->phone }}</span>
                                                @endif
                                            </div>
                                        </div>

                                        <!-- Baris 4: Tombol Detail Lengkap & Foto (1 Baris Rapi) -->
                                        <div class="relative z-10 hidden md:block">
                                            <button type="button" onclick="openOperatorModal({{ $c->id }})" class="text-xs font-bold text-[#1E3A8A] hover:underline inline-flex items-center gap-1 cursor-pointer">
                                                <span>Lihat Rincian Lengkap & Foto</span>
                                                <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- 3. Target SLA & Info Regu Lapangan -->
                            <div class="pt-2 border-t border-slate-100 space-y-2">
                                @if($deadline)
                                <div class="p-2.5 rounded-xl bg-blue-50/50 border border-blue-100/90 flex items-center justify-between text-[10px]">
                                    <span class="flex items-center gap-1.5 text-slate-600 font-medium">
                                        <i data-lucide="clock" class="w-3 h-3 text-[#1E3A8A] shrink-0"></i>
                                        <span>Target: <strong class="text-slate-900 font-semibold">{{ \Carbon\Carbon::parse($deadline)->locale('id')->isoFormat('D MMM Y') }}</strong></span>
                                    </span>
                                    <span class="px-1.5 py-0.5 rounded text-[9px] font-bold shrink-0 bg-blue-100/80 text-[#1E3A8A]">
                                        {{ \Carbon\Carbon::parse($deadline)->diffForHumans() }}
                                    </span>
                                </div>
                                @endif

                                <!-- Status Regu Ditugaskan -->
                                <div class="flex items-center justify-between gap-2 pt-1 text-xs">
                                    <div class="flex items-center gap-1.5 min-w-0">
                                        @if($disp && $disp->team)
                                            <span class="px-2.5 py-1 rounded-lg bg-blue-50 text-[#1E3A8A] border border-blue-200 font-bold text-[11px] flex items-center gap-1.5 truncate">
                                                <i data-lucide="users" class="w-3.5 h-3.5 text-[#1E3A8A] shrink-0"></i>
                                                <span class="truncate">Regu: [{{ $disp->team->code }}] {{ $disp->team->name }}</span>
                                            </span>
                                        @else
                                            <span class="px-2.5 py-1 rounded-lg bg-amber-50 text-amber-800 border border-amber-200 font-bold text-[11px] flex items-center gap-1.5">
                                                <i data-lucide="alert-circle" class="w-3.5 h-3.5 text-amber-600 shrink-0"></i>
                                                <span>Belum Ditunjuk Regu</span>
                                            </span>
                                        @endif
                                    </div>

                                    @if(($dispatchMode ?? 'HYBRID') === 'SELF_CLAIM_ONLY')
                                        <span class="text-[10px] text-slate-400 italic">
                                            Klaim Mandiri (Mobile)
                                        </span>
                                    @else
                                        <button type="button" 
                                            onclick="openAssignTeamModal({{ $c->id }}, '{{ $c->public_code }}', '{{ addslashes($c->title) }}', '{{ $disp?->team_id ?? '' }}', '{{ addslashes($disp?->instruction ?? '') }}')"
                                            class="px-3 py-1.5 rounded-xl bg-[#1E3A8A] hover:bg-blue-900 text-white font-bold text-xs flex items-center gap-1.5 shadow-2xs transition cursor-pointer shrink-0">
                                            <i data-lucide="user-plus" class="w-3.5 h-3.5"></i>
                                            <span>{{ $disp?->team_id ? 'Ubah Regu' : 'Tempatkan Regu' }}</span>
                                        </button>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <!-- Notifikasi Filter Kosong Tab Ditugaskan -->
                <div id="emptyFilterNotice-dispatched" class="hidden card-white rounded-2xl p-8 text-center text-slate-400 text-xs bg-white border border-slate-200">
                    Tidak ditemukan laporan penugasan yang cocok dengan filter yang dipilih.
                </div>
            @endif
        </div>
    </div>

    <!-- TAB 3: SEDANG DIKERJAKAN FISIK DI LAPANGAN -->
    <div id="tabPanel-inprogress" class="tab-panel {{ $activeTab === 'inprogress' ? '' : 'hidden' }} space-y-4">
        <div class="space-y-4">
            @if($inProgress->isEmpty())
                <div class="card-white rounded-2xl p-12 text-center text-slate-400 text-xs bg-white border border-[#E2E8F0]">
                    Belum ada tugas lapangan yang sedang berjalan.
                </div>
            @else
                @include('partials.role_complaints_table', ['wrapId' => 'operatorTableWrap-inprogress', 'rowClass' => 'operator-inprogress-row', 'items' => $inProgress, 'mode' => 'operator-inprogress', 'hidden' => false])
                <div id="inprogressCardsContainer" class="hidden grid grid-cols-1 lg:grid-cols-2 gap-5">
                    @foreach($inProgress as $c)
                        @php
                            $disp = $c->latestDisposition;
                            $deadline = $disp?->deadline;
                            $isOverdue = $deadline && now()->greaterThan($deadline);
                            $firstPhotoUrl = $c->photos->isNotEmpty() 
                                ? ($c->photos->first()->url ?? asset('storage/' . $c->photos->first()->storage_key)) 
                                : null;
                        @endphp
                        <div onclick="if (window.innerWidth < 768) { openOperatorModal({{ $c->id }}); }" class="complaint-inprogress-card card-white rounded-2xl p-4 sm:p-5 bg-white border border-slate-200/90 hover:border-blue-300 hover:shadow-md transition-all duration-200 flex flex-col justify-between space-y-3.5 cursor-pointer md:cursor-default"
                            data-category="{{ $c->category_id }}"
                            data-damage-type="{{ $c->damage_type ?? 'minor' }}"
                            data-search="{{ strtolower($c->public_code . ' ' . ($c->user?->name ?? $c->citizen?->name ?? '') . ' ' . $c->title . ' ' . $c->address) }}">
                            
                            <div class="space-y-3">
                                <!-- 1. Header Card: 1 Baris Sejajar - Kode + Tipe + Kategori (Kiri) & Waktu (Kanan) -->
                                <div class="flex items-center justify-between gap-2 pb-2.5 border-b border-slate-100 min-w-0">
                                    <div class="flex items-center gap-1.5 min-w-0 flex-nowrap overflow-hidden">
                                        <span class="px-2 py-0.5 rounded-lg bg-[#1E3A8A]/10 text-[#1E3A8A] font-bold text-xs font-mono tracking-tight border border-blue-100/60 whitespace-nowrap shrink-0">
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

                                <!-- 2. Konten Utama: Foto Kiri + Rincian Kanan (Sejajar Sempurna) -->
                                <div class="flex gap-4 items-stretch">
                                    <div class="relative w-24 h-24 sm:w-28 sm:h-28 rounded-2xl overflow-hidden border border-slate-200/90 bg-slate-100 shrink-0 shadow-2xs">
                                        @if($firstPhotoUrl)
                                            <img src="{{ $firstPhotoUrl }}" alt="Foto Aduan" class="w-full h-full object-cover">
                                            @if($c->photos->count() > 1)
                                                <span class="absolute bottom-1.5 right-1.5 bg-slate-900/80 text-white text-[10px] font-bold px-1.5 py-0.5 rounded-lg flex items-center gap-0.5 shadow-xs">
                                                    <i data-lucide="layers" class="w-2.5 h-2.5"></i>
                                                    +{{ $c->photos->count() }}
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
                                            <h4 class="font-outfit font-extrabold text-sm sm:text-base text-slate-900 tracking-tight leading-snug truncate" title="{{ $c->title }}">
                                                {{ $c->title }}
                                            </h4>
                                            <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-blue-50 text-[#1E3A8A] border border-blue-200/80 shrink-0 whitespace-nowrap">
                                                {{ $c->severity_text }}
                                            </span>
                                        </div>

                                        <!-- Baris 2: Lokasi Fisik (1 Baris Rapi) -->
                                        <div class="text-xs text-slate-600 flex items-center gap-1.5 min-w-0">
                                            <i data-lucide="map-pin" class="w-3.5 h-3.5 text-[#1E3A8A] shrink-0"></i>
                                            <span class="truncate font-medium" title="{{ $c->address }}">{{ $c->address }}</span>
                                        </div>

                                        <!-- Baris 3: Info Pelapor & No Telepon (1 Baris Rapi) -->
                                        <div class="flex items-center gap-2 text-xs min-w-0">
                                            <div class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-lg bg-blue-50/90 border border-blue-100/90 text-slate-700 min-w-0 max-w-full">
                                                <i data-lucide="user" class="w-3 h-3 text-[#1E3A8A] shrink-0"></i>
                                                <span class="font-semibold text-slate-800 text-[11px] truncate">{{ $c->citizen?->name ?? 'Warga' }}</span>
                                                @if($c->citizen?->phone)
                                                    <span class="text-blue-300 shrink-0">•</span>
                                                    <span class="font-mono text-slate-600 font-medium text-[10.5px] shrink-0">{{ $c->citizen->phone }}</span>
                                                @endif
                                            </div>
                                        </div>

                                        <!-- Baris 4: Tombol Detail Lengkap & Foto (1 Baris Rapi) -->
                                        <div class="relative z-10 hidden md:block">
                                            <button type="button" onclick="openOperatorModal({{ $c->id }})" class="text-xs font-bold text-[#1E3A8A] hover:underline inline-flex items-center gap-1 cursor-pointer">
                                                <span>Lihat Rincian Lengkap & Foto</span>
                                                <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- 3. Target SLA Pengerjaan -->
                            <div class="pt-2 border-t border-slate-100">
                                <div class="p-2.5 rounded-xl {{ $isOverdue ? 'bg-red-50/80 border border-red-200/90' : 'bg-blue-50/50 border border-blue-100/90' }} flex items-center justify-between text-[10px]">
                                    <span class="flex items-center gap-1.5 {{ $isOverdue ? 'text-red-700 font-bold' : 'text-slate-600 font-medium' }}">
                                        <i data-lucide="{{ $isOverdue ? 'alert-triangle' : 'clock' }}" class="w-3 h-3 {{ $isOverdue ? 'text-red-600' : 'text-[#1E3A8A]' }} shrink-0"></i>
                                        <span>{{ $isOverdue ? 'Lewat Batas' : 'Target' }}: <strong class="{{ $isOverdue ? 'text-red-800' : 'text-slate-900' }} font-semibold">{{ $deadline ? \Carbon\Carbon::parse($deadline)->locale('id')->isoFormat('D MMM Y') : 'Segera' }}</strong></span>
                                    </span>
                                    <span class="px-1.5 py-0.5 rounded text-[9px] font-bold shrink-0 {{ $isOverdue ? 'bg-red-100 text-red-700' : 'bg-blue-100/80 text-[#1E3A8A]' }}">
                                        {{ $deadline ? $deadline->diffForHumans() : 'SOP' }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <!-- Notifikasi Filter Kosong Tab Dikerjakan -->
                <div id="emptyFilterNotice-inprogress" class="hidden card-white rounded-2xl p-8 text-center text-slate-400 text-xs bg-white border border-slate-200">
                    Tidak ditemukan laporan pengerjaan fisik yang cocok dengan filter yang dipilih.
                </div>
            @endif
        </div>
    </div>

    <!-- TAB 4: VALIDASI PENYELESAIAN AKHIR -->
    <div id="tabPanel-validation" class="tab-panel {{ $activeTab === 'validation' ? '' : 'hidden' }} space-y-4">
        <div class="space-y-4">
            @if($waitingValidation->isEmpty())
                <div class="card-white rounded-2xl p-12 text-center text-slate-400 text-xs bg-white border border-[#E2E8F0]">
                    Tidak ada laporan yang sedang menunggu validasi selesai.
                </div>
            @else
                @include('partials.role_complaints_table', ['wrapId' => 'operatorTableWrap-validation', 'rowClass' => 'operator-validation-row', 'items' => $waitingValidation, 'mode' => 'operator-validation', 'hidden' => false])
                <div id="validationCardsContainer" class="hidden grid grid-cols-1 lg:grid-cols-2 gap-5">
                    @foreach($waitingValidation as $c)
                        @php
                            $disp = $c->latestDisposition;
                            $beforePhoto = $c->photos->where('type', 'evidence_before')->first() ?? $c->photos->first();
                            $afterPhoto = $c->photos->where('type', 'resolution_after')->first() ?? $c->photos->last();
                            $beforeUrl = $beforePhoto ? ($beforePhoto->url ?? asset('storage/' . $beforePhoto->storage_key)) : null;
                            $afterUrl = $afterPhoto && $afterPhoto->id !== ($beforePhoto?->id) ? ($afterPhoto->url ?? asset('storage/' . $afterPhoto->storage_key)) : null;
                        @endphp
                        <div onclick="if (window.innerWidth < 768) { openOperatorModal({{ $c->id }}); }" class="complaint-validation-card card-white rounded-2xl p-4 sm:p-5 bg-white border border-slate-200/90 hover:border-blue-300 hover:shadow-md transition-all duration-200 flex flex-col justify-between space-y-3.5 cursor-pointer md:cursor-default"
                            data-category="{{ $c->category_id }}"
                            data-damage-type="{{ $c->damage_type ?? 'minor' }}"
                            data-search="{{ strtolower($c->public_code . ' ' . ($c->user?->name ?? $c->citizen?->name ?? '') . ' ' . $c->title . ' ' . $c->address) }}">
                            
                            <div class="space-y-3">
                                <!-- 1. Header Card: 1 Baris Sejajar - Kode + Tipe + Kategori (Kiri) & Waktu (Kanan) -->
                                <div class="flex items-center justify-between gap-2 pb-2.5 border-b border-slate-100 min-w-0">
                                    <div class="flex items-center gap-1.5 min-w-0 flex-nowrap overflow-hidden">
                                        <span class="px-2 py-0.5 rounded-lg bg-[#1E3A8A]/10 text-[#1E3A8A] font-bold text-xs font-mono tracking-tight border border-blue-100/60 whitespace-nowrap shrink-0">
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

                                <!-- 2. Konten Utama: 1 Foto Bukti + Rincian (Sejajar Sempurna) -->
                                <div class="flex gap-4 items-stretch">
                                    <div class="relative w-24 h-24 sm:w-28 sm:h-28 rounded-2xl overflow-hidden border border-slate-200/90 bg-slate-100 shrink-0 shadow-2xs">
                                        @if($afterUrl)
                                            <img src="{{ $afterUrl }}" alt="Hasil Perbaikan" class="w-full h-full object-cover">
                                            <span class="absolute bottom-1.5 left-1.5 bg-emerald-800/90 text-white text-[9px] font-bold px-1.5 py-0.5 rounded shadow-xs">
                                                Hasil Selesai
                                            </span>
                                        @elseif($beforeUrl)
                                            <img src="{{ $beforeUrl }}" alt="Kondisi Awal" class="w-full h-full object-cover">
                                            <span class="absolute bottom-1.5 left-1.5 bg-black/70 text-white text-[9px] font-bold px-1.5 py-0.5 rounded shadow-xs">
                                                Foto Awal
                                            </span>
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
                                            <h4 class="font-outfit font-extrabold text-sm sm:text-base text-slate-900 tracking-tight leading-snug truncate" title="{{ $c->title }}">
                                                {{ $c->title }}
                                            </h4>
                                            <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-blue-50 text-[#1E3A8A] border border-blue-200/80 shrink-0 whitespace-nowrap">
                                                {{ $c->severity_text }}
                                            </span>
                                        </div>

                                        <!-- Baris 2: Lokasi Fisik (1 Baris Rapi) -->
                                        <div class="text-xs text-slate-600 flex items-center gap-1.5 min-w-0">
                                            <i data-lucide="map-pin" class="w-3.5 h-3.5 text-[#1E3A8A] shrink-0"></i>
                                            <span class="truncate font-medium" title="{{ $c->address }}">{{ $c->address }}</span>
                                        </div>

                                        <!-- Baris 3: Info Pelapor & No Telepon (1 Baris Rapi) -->
                                        <div class="flex items-center gap-2 text-xs min-w-0">
                                            <div class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-lg bg-blue-50/90 border border-blue-100/90 text-slate-700 min-w-0 max-w-full">
                                                <i data-lucide="user" class="w-3 h-3 text-[#1E3A8A] shrink-0"></i>
                                                <span class="font-semibold text-slate-800 text-[11px] truncate">{{ $c->citizen?->name ?? 'Warga' }}</span>
                                                @if($c->citizen?->phone)
                                                    <span class="text-blue-300 shrink-0">•</span>
                                                    <span class="font-mono text-slate-600 font-medium text-[10.5px] shrink-0">{{ $c->citizen->phone }}</span>
                                                @endif
                                            </div>
                                        </div>

                                        <!-- Baris 4: Tombol Detail Lengkap & Foto (1 Baris Rapi) -->
                                        <div class="relative z-10 hidden md:block">
                                            <button type="button" onclick="openOperatorModal({{ $c->id }})" class="text-xs font-bold text-[#1E3A8A] hover:underline inline-flex items-center gap-1 cursor-pointer">
                                                <span>Lihat Rincian Lengkap & Foto</span>
                                                <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- 3. Tombol Aksi Validasi (Full-width Dominan Biru) -->
                            <div onclick="event.stopPropagation();" class="pt-3 border-t border-slate-100/90 cursor-default">
                                <button type="button" onclick="openValidationModal({{ $c->id }})" 
                                    class="w-full py-2.5 px-3 rounded-xl bg-[#1E3A8A] hover:bg-[#172554] active:scale-[0.99] text-white font-bold text-xs shadow-xs hover:shadow-md transition flex items-center justify-center gap-2 cursor-pointer">
                                    <i data-lucide="check-circle-2" class="w-4 h-4"></i>
                                    <span>Lakukan Validasi</span>
                                </button>
                            </div>
                        </div>
                    @endforeach
                </div>

                <!-- Notifikasi Filter Kosong Tab Validasi -->
                <div id="emptyFilterNotice-validation" class="hidden card-white rounded-2xl p-8 text-center text-slate-400 text-xs bg-white border border-slate-200">
                    Tidak ditemukan laporan validasi yang cocok dengan filter yang dipilih.
                </div>
            @endif
        </div>
    </div>

    <!-- TAB 5: ARSIP LAPORAN SELESAI & PERMANEN -->
    <div id="tabPanel-archive" class="tab-panel {{ $activeTab === 'archive' ? '' : 'hidden' }} space-y-4">
        @if($completed->isEmpty())
            <div class="card-white rounded-2xl p-12 text-center text-slate-400 text-xs bg-white border border-[#E2E8F0]">
                Belum ada laporan yang selesai dan diarsipkan.
            </div>
        @else
            @include('partials.role_complaints_table', ['wrapId' => 'operatorTableWrap-archive', 'rowClass' => 'operator-archive-row', 'items' => $completed, 'mode' => 'operator-archive', 'hidden' => false])
            <!-- Grid Card Arsip 2 Kolom Rapi & Sejajar -->
            <div id="archiveCardsContainer" class="hidden grid grid-cols-1 lg:grid-cols-2 gap-5">
                @foreach($completed as $c)
                    @php
                        $isRejected = ($c->status === 'REJECTED');
                        $beforePhoto = $c->photos->where('type', 'evidence_before')->first() ?? $c->photos->first();
                        $afterPhoto = $c->photos->where('type', 'resolution_after')->first() ?? $c->photos->last();
                        $beforeUrl = $beforePhoto ? ($beforePhoto->url ?? asset('storage/' . $beforePhoto->storage_key)) : null;
                        $afterUrl = $afterPhoto && $afterPhoto->id !== ($beforePhoto?->id) ? ($afterPhoto->url ?? asset('storage/' . $afterPhoto->storage_key)) : null;
                        $displayPhoto = $afterUrl ?: $beforeUrl;
                    @endphp
                    <div onclick="if (window.innerWidth < 768) { openOperatorModal({{ $c->id }}); }" class="complaint-archive-card card-white rounded-2xl p-4 sm:p-5 bg-white border border-slate-200/90 hover:border-blue-300 hover:shadow-md transition-all duration-200 flex flex-col justify-between space-y-3.5 cursor-pointer md:cursor-default"
                        data-category="{{ $c->category_id }}"
                        data-damage-type="{{ $c->damage_type ?? 'minor' }}"
                        data-status="{{ $c->status }}"
                        data-search="{{ strtolower($c->public_code . ' ' . ($c->user?->name ?? $c->citizen?->name ?? '') . ' ' . $c->title . ' ' . $c->address) }}">
                        
                        <div class="space-y-3">
                            <!-- 1. Header Card: 1 Baris Sejajar - Kode + Tipe + Status + Kategori (Kiri) & Waktu (Kanan) -->
                            <div class="flex items-center justify-between gap-2 pb-2.5 border-b border-slate-100 min-w-0">
                                <div class="flex items-center gap-1.5 min-w-0 flex-nowrap overflow-hidden">
                                    <span class="px-2 py-0.5 rounded-lg bg-[#1E3A8A]/10 text-[#1E3A8A] font-bold text-xs font-mono tracking-tight border border-blue-100/60 whitespace-nowrap shrink-0">
                                        #{{ $c->public_code }}
                                    </span>
                                    <span class="px-2 py-0.5 rounded-full text-[10.5px] font-bold bg-red-50 text-red-700 border border-red-200 inline-flex items-center gap-1 whitespace-nowrap shrink-0">
                                        <span class="w-1.5 h-1.5 rounded-full bg-red-500"></span>
                                        <span>{{ $c->damage_type === 'major' ? 'Berat' : 'Ringan' }}</span>
                                    </span>
                                    @if($isRejected)
                                        <span class="px-2 py-0.5 rounded-full bg-red-50 text-red-700 border border-red-200 text-[10px] font-bold whitespace-nowrap shrink-0">
                                            DITOLAK
                                        </span>
                                    @endif
                                    <span class="inline-flex px-2 py-0.5 rounded-full text-[10.5px] font-bold bg-slate-100 text-slate-600 border border-slate-200/80 whitespace-nowrap truncate shrink-0 max-w-[170px]" title="{{ $c->category?->name ?? 'Infrastruktur' }}">
                                        {{ $c->category?->name ?? 'Infrastruktur' }}
                                    </span>
                                </div>
                                <span class="text-[11px] text-slate-400 font-medium whitespace-nowrap flex items-center gap-1 shrink-0">
                                    <i data-lucide="clock" class="w-3 h-3 text-slate-300"></i>
                                    <span>{{ $c->created_at->diffForHumans() }}</span>
                                </span>
                            </div>

                            <!-- 2. Konten Utama: 1 Foto Bukti + Rincian (Sejajar Sempurna) -->
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
                                        <h4 class="font-outfit font-extrabold text-sm sm:text-base text-slate-900 tracking-tight leading-snug truncate" title="{{ $c->title }}">
                                            {{ $c->title }}
                                        </h4>
                                        <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-blue-50 text-[#1E3A8A] border border-blue-200/80 shrink-0 whitespace-nowrap">
                                            {{ $c->severity_text }}
                                        </span>
                                    </div>

                                    <!-- Baris 2: Lokasi Fisik (1 Baris Rapi) -->
                                    <div class="text-xs text-slate-600 flex items-center gap-1.5 min-w-0">
                                        <i data-lucide="map-pin" class="w-3.5 h-3.5 text-[#1E3A8A] shrink-0"></i>
                                        <span class="truncate font-medium" title="{{ $c->address }}">{{ $c->address }}</span>
                                    </div>

                                    <!-- Baris 3: Info Pelapor & No Telepon (1 Baris Rapi) -->
                                    <div class="flex items-center gap-2 text-xs min-w-0">
                                        <div class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-lg bg-blue-50/90 border border-blue-100/90 text-slate-700 min-w-0 max-w-full">
                                            <i data-lucide="user" class="w-3 h-3 text-[#1E3A8A] shrink-0"></i>
                                            <span class="font-semibold text-slate-800 text-[11px] truncate">{{ $c->citizen?->name ?? 'Warga' }}</span>
                                            @if($c->citizen?->phone)
                                                <span class="text-blue-300 shrink-0">•</span>
                                                <span class="font-mono text-slate-600 font-medium text-[10.5px] shrink-0">{{ $c->citizen->phone }}</span>
                                            @endif
                                        </div>
                                    </div>

                                    <!-- Baris 4: Tombol Detail Lengkap & Foto (1 Baris Rapi) -->
                                    <div class="relative z-10 hidden md:block">
                                        <button type="button" onclick="openOperatorModal({{ $c->id }})" class="text-xs font-bold text-[#1E3A8A] hover:underline inline-flex items-center gap-1 cursor-pointer">
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
                                <button type="button" onclick="openOperatorModal({{ $c->id }})" 
                                    class="py-1 px-3 rounded-lg bg-[#1E3A8A] hover:bg-[#172554] text-white font-bold text-xs transition flex items-center gap-1 cursor-pointer" 
                                    title="Lihat Detail Lengkap">
                                    <span>Rincian</span>
                                    <i data-lucide="chevron-right" class="w-3 h-3"></i>
                                </button>
                            </div>
                        @else
                            @php
                                $archiveDeadline = $c->latestDisposition?->deadline 
                                    ? \Carbon\Carbon::parse($c->latestDisposition->deadline) 
                                    : \Carbon\Carbon::parse($c->created_at)->addDays($c->damage_type === 'major' ? 7 : 2);
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
            <div id="emptyFilterNotice-archive" class="hidden card-white rounded-2xl p-8 text-center text-slate-400 text-xs bg-white border border-slate-200">
                Tidak ditemukan laporan arsip yang cocok dengan filter yang dipilih.
            </div>
        @endif
    </div>

    <!-- Pagination Bar Operator (5 / 10 / 50 per halaman, default 10) -->
    <div id="operatorPaginationBar" class="p-3.5 rounded-xl bg-white border border-[#E2E8F0] flex flex-col sm:flex-row sm:items-center justify-between gap-3 text-xs {{ $activeTab === 'monitoring' ? 'hidden' : '' }}">
        <div class="flex flex-wrap items-center gap-3 text-[#475569]">
            <div class="flex items-center gap-2">
                <span class="font-medium">Tampilkan:</span>
                <select id="operatorPageSizeSelect" onchange="changeOperatorPageSize(this.value)" aria-label="Jumlah laporan per halaman"
                    class="text-xs font-semibold rounded-lg border border-[#E2E8F0] bg-[#F8FAFC] text-[#0F172A] px-2.5 py-1.5 focus:outline-none focus:border-[#1E3A8A] cursor-pointer">
                    <option value="5">5 laporan</option>
                    <option value="10" selected>10 laporan</option>
                    <option value="50">50 laporan</option>
                </select>
            </div>
            <span id="operatorPageInfo" class="font-medium">Menampilkan 0 dari 0 laporan</span>
        </div>
        <div id="operatorPageControls" class="flex items-center gap-1 flex-wrap"></div>
    </div>

    <!-- TAB 5: MONITORING EKSEKUTIF & KINERJA DINAS (LENGKAP ANALITIK EKSEKUTIF & PETA SPASIAL KOLAKA UTARA) -->
    <div id="tabPanel-monitoring" class="tab-panel {{ $activeTab === 'monitoring' ? '' : 'hidden' }} space-y-6">
        
        <!-- 1. PETA SEBARAN INFRASTRUKTUR -->
        <div class="card-white rounded-2xl p-5 bg-white border border-[#E2E8F0] space-y-3">
            <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-3 border-b border-slate-100 pb-3">
                <div class="min-w-0">
                    <h3 class="font-outfit font-bold text-base text-slate-900 flex items-center gap-2">
                        <i data-lucide="map-pin" class="w-4 h-4 text-[#1E3A8A] shrink-0"></i>
                        <span>Peta Sebaran Infrastruktur Rusak Seluruh Kabupaten</span>
                    </h3>
                </div>
                <div class="flex items-center gap-2 sm:gap-3 text-[10px] sm:text-xs font-semibold overflow-x-auto whitespace-nowrap w-full sm:w-auto pb-0.5 sm:pb-0 shrink-0">
                    <span class="inline-flex items-center gap-1 shrink-0"><span class="w-2 h-2 sm:w-2.5 sm:h-2.5 rounded-full bg-[#EF4444] shrink-0"></span> Verifikasi</span>
                    <span class="inline-flex items-center gap-1 shrink-0"><span class="w-2 h-2 sm:w-2.5 sm:h-2.5 rounded-full bg-[#1E40AF] shrink-0"></span> Ditugaskan</span>
                    <span class="inline-flex items-center gap-1 shrink-0"><span class="w-2 h-2 sm:w-2.5 sm:h-2.5 rounded-full bg-[#F59E0B] shrink-0"></span> <span class="hidden sm:inline">Sedang </span>Dikerjakan</span>
                    <span class="inline-flex items-center gap-1 shrink-0"><span class="w-2 h-2 sm:w-2.5 sm:h-2.5 rounded-full bg-[#F59E0B] shrink-0"></span> Validasi</span>
                    <span class="inline-flex items-center gap-1 shrink-0"><span class="w-2 h-2 sm:w-2.5 sm:h-2.5 rounded-full bg-[#10B981] shrink-0"></span> Selesai</span>
                    <span class="inline-flex items-center gap-1 shrink-0"><span class="w-2 h-2 sm:w-2.5 sm:h-2.5 rounded-full bg-[#DC2626] shrink-0"></span> Ditolak</span>
                </div>
            </div>

            <!-- Leaflet Map Container -->
            <div id="operatorMap" class="w-full h-[420px] rounded-xl border border-[#E2E8F0] z-10"></div>
        </div>

        <!-- 2. MODUL TERPADU: MONITORING EKSEKUTIF, GRAFIK KINERJA & PELAPORAN RESMI (PDF) -->
        @include('partials.executive_monitoring_reporting')

        <!-- 3. SECTION MANDIRI: SEBARAN PENGADUAN PER WILAYAH KECAMATAN & DESA (KOLAKA UTARA) -->
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
                                    <span class="text-slate-400 font-medium">Kondusif & Terpantau</span>
                                @elseif($ds['completed'] === $ds['total'])
                                    <span class="font-bold text-emerald-600 flex items-center gap-1">
                                        <i data-lucide="check-check" class="w-3.5 h-3.5"></i>
                                        100% Selesai
                                    </span>
                                @endif
                            </div>
                            @if($ds['total'] > 0 && $ds['completed'] !== $ds['total'])
                                <div class="flex items-center gap-1.5 flex-wrap">
                                    @if(($ds['pending'] ?? 0) > 0)
                                        <span class="px-2 py-0.5 rounded-md bg-red-50 text-red-700 border border-red-200 text-[10px] font-bold flex items-center gap-1">
                                            <span class="w-1.5 h-1.5 rounded-full bg-red-500"></span>
                                            {{ $ds['pending'] }} Verifikasi
                                        </span>
                                    @endif
                                    @if(($ds['dispatched'] ?? 0) > 0)
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
                                    @if(($ds['completed'] ?? 0) > 0)
                                        <span class="px-2 py-0.5 rounded-md bg-emerald-50 text-emerald-700 border border-emerald-200 text-[10px] font-bold flex items-center gap-1">
                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                            {{ $ds['completed'] }} Selesai
                                        </span>
                                    @endif
                                    @if(($ds['rejected'] ?? 0) > 0)
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
                            <button type="button" onclick="focusDistrictOnOperatorMap({{ $ds['latitude'] }}, {{ $ds['longitude'] }}, '{{ $ds['district'] }}')"
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

</div>

<!-- Pop-up Modal Detail Lengkap Operator (Super Cepat, Bersih & Langsung Fokus) -->
<div id="operatorDetailModal" class="fixed inset-0 z-50 hidden flex items-center justify-center p-3 sm:p-5 bg-slate-900/65 transition-opacity duration-150 overscroll-contain">
    <div class="bg-white w-full max-w-xl rounded-xl shadow-md border border-[#E2E8F0] overflow-hidden flex flex-col max-h-[92vh]">
        
        <!-- Modal Top Header (Selaras Landing Page: Kerusakan -> Kategori -> Status) -->
        <div class="px-4 sm:px-5 py-3 sm:py-3.5 border-b border-[#E2E8F0] flex items-center justify-between bg-[#F8FAFC] gap-2">
            <div class="flex items-center gap-1.5 sm:gap-2 flex-nowrap min-w-0 overflow-x-auto no-scrollbar py-0.5">
                <!-- 1. Tingkat Kerusakan -->
                <span id="opModalTypeBadge" class="shrink-0 px-2.5 py-1 rounded-md text-xs font-semibold bg-red-50 text-red-700 border border-red-200 inline-flex items-center gap-1.5 whitespace-nowrap">
                    <span class="w-1.5 h-1.5 rounded-full bg-red-600 shrink-0"></span>
                    <span>Kerusakan Ringan</span>
                </span>
                <!-- 2. Kategori Infrastruktur -->
                <span id="opModalCategoryBadge" class="shrink-0 px-2.5 py-1 rounded-md bg-slate-100 text-[#0F172A] font-semibold text-xs border border-[#E2E8F0] inline-flex items-center whitespace-nowrap">
                    Kategori
                </span>
                <!-- 3. Status Pengerjaan -->
                <span id="opModalStatusBadge" class="shrink-0 px-2.5 py-1 rounded-md text-xs font-bold border border-blue-200 bg-blue-50 text-[#1E3A8A] whitespace-nowrap">
                    STATUS
                </span>
            </div>
            <button type="button" onclick="closeOperatorModal()" class="w-9 h-9 rounded-lg bg-white hover:bg-slate-100 text-[#475569] hover:text-[#0F172A] border border-[#E2E8F0] flex items-center justify-center transition cursor-pointer shrink-0 ml-1" title="Tutup Detail" aria-label="Tutup Rincian">
                <i data-lucide="x" class="w-4 h-4"></i>
            </button>
        </div>

        <!-- Modal Scrollable Body -->
        <div class="p-4 sm:p-5 overflow-y-auto space-y-4 flex-1 overscroll-contain" style="-webkit-overflow-scrolling: touch;">
            
            <!-- 1. Foto Bukti Kerusakan Warga -->
            <div id="opModalCitizenPhotoSection" class="space-y-2">
                <div class="rounded-xl overflow-hidden border border-[#E2E8F0] bg-slate-100 relative max-h-64 sm:max-h-72 flex items-center justify-center">
                    <img id="opModalCitizenPhoto" src="" alt="Foto Kerusakan Warga" class="w-full h-full object-cover max-h-64 sm:max-h-72">
                    <div class="absolute bottom-2.5 left-2.5 bg-[#0F172A] text-white text-[11px] font-semibold px-2.5 py-1 rounded-md flex items-center gap-1.5 border border-slate-700">
                        <i data-lucide="image" class="w-3.5 h-3.5 text-white"></i>
                        <span id="opModalCitizenPhotoLabel">Foto Bukti Warga</span>
                    </div>
                </div>
                <!-- Gallery Thumbnails Strip -->
                <div id="opModalCitizenGallery" class="flex items-center gap-2 overflow-x-auto pb-1 hidden"></div>
            </div>

            <!-- 2. Foto Bukti Penanganan Petugas Lapangan -->
            <div id="opModalOfficerPhotoSection" class="space-y-2 hidden">
                <div class="rounded-xl overflow-hidden border border-emerald-300 bg-emerald-50 relative max-h-64 sm:max-h-72 flex items-center justify-center">
                    <img id="opModalOfficerPhoto" src="" alt="Foto Hasil Pengerjaan Petugas" class="w-full h-full object-cover max-h-64 sm:max-h-72">
                    <div class="absolute bottom-2.5 left-2.5 bg-[#064E3B] text-white text-[11px] font-semibold px-2.5 py-1 rounded-md flex items-center gap-1.5 border border-emerald-800">
                        <i data-lucide="check-circle-2" class="w-3.5 h-3.5 text-emerald-300"></i>
                        <span id="opModalOfficerPhotoLabel">Bukti Penyelesaian Lapangan</span>
                    </div>
                </div>
                <!-- Gallery Thumbnails Strip -->
                <div id="opModalOfficerGallery" class="flex items-center gap-2 overflow-x-auto pb-1 hidden"></div>
            </div>

            <!-- Judul & Info Waktu Laporan -->
            <div>
                <span id="opModalCode" class="text-[11px] font-mono font-semibold text-[#475569] block tracking-wide mb-0.5">
                    #ADU-0000
                </span>
                <h3 id="opModalTitle" class="font-outfit font-bold text-lg sm:text-xl text-[#0F172A] leading-snug">
                    Judul Laporan
                </h3>
                <div class="flex items-center gap-1.5 text-xs text-[#475569] pt-1">
                    <i data-lucide="calendar" class="w-3.5 h-3.5 text-[#475569]"></i>
                    <span id="opModalDate">03 Sep 2026</span>
                </div>
            </div>

            <!-- Identitas Pelapor Aduan (Clean, Compact, Tanpa Label Berulang) -->
            <div class="p-3 rounded-2xl bg-slate-50 border border-slate-200/80 flex items-center justify-between gap-3">
                <div class="flex items-center gap-2.5 min-w-0">
                    <div class="w-9 h-9 rounded-xl bg-slate-200/80 text-slate-700 flex items-center justify-center font-bold text-xs shrink-0">
                        <i data-lucide="user" class="w-4 h-4 text-[#1E3A8A]"></i>
                    </div>
                    <div class="min-w-0">
                        <span class="text-[10px] text-slate-400 font-bold uppercase tracking-wider block">Pelapor Aduan</span>
                        <div class="flex items-center gap-2">
                            <span id="opModalCitizenName" class="font-outfit font-extrabold text-xs sm:text-sm text-slate-900 truncate">
                                Nama Pelapor
                            </span>
                            <span id="opModalAnonymousBadge" class="hidden text-[9px] font-bold px-1.5 py-0.5 rounded bg-slate-200 text-slate-600">Anonim</span>
                        </div>
                    </div>
                </div>
                <div class="flex items-center gap-1.5 text-xs text-slate-700 font-bold shrink-0">
                    <i data-lucide="phone" class="w-3.5 h-3.5 text-[#1E3A8A]"></i>
                    <a id="opModalCitizenPhone" href="#" class="font-mono hover:underline text-[#1E3A8A]">08123456789</a>
                </div>
            </div>

            <!-- Regu Penanganan Lapangan (Sesuai Permintaan User: Menampilkan Regu, Ketua Regu, & Nomor HP) -->
            <div id="opModalOfficerCard" class="hidden p-3.5 rounded-2xl bg-blue-50/60 border border-blue-200/80 space-y-2.5">
                <div class="flex items-start justify-between gap-2">
                    <div class="flex items-center gap-2.5 min-w-0">
                        <div class="w-9 h-9 rounded-xl bg-[#1E3A8A] text-white flex items-center justify-center font-bold text-xs shrink-0 shadow-2xs">
                            <i data-lucide="hard-hat" class="w-4 h-4"></i>
                        </div>
                        <div class="min-w-0">
                            <span class="text-[10px] text-[#1E3A8A] font-extrabold uppercase tracking-wider block">Regu Lapangan Pelaksana</span>
                            <h4 id="opModalTeamName" class="font-outfit font-extrabold text-xs sm:text-sm text-slate-900 truncate leading-tight">
                                Regu Aspal 01 - Lasusua
                            </h4>
                            <span id="opModalOfficerDept" class="text-[10.5px] font-semibold text-slate-500 block truncate">
                                Dinas Pekerjaan Umum dan Penataan Ruang (PUPR)
                            </span>
                        </div>
                    </div>
                    <div id="opModalDeadlineBox" class="text-right shrink-0">
                        <span class="text-[10px] text-slate-400 block font-medium">Batas Waktu:</span>
                        <span id="opModalOfficerDeadline" class="text-xs font-bold text-[#1E3A8A] bg-white px-2 py-0.5 rounded border border-blue-200">-</span>
                    </div>
                </div>

                <!-- Baris Kontak Ketua Regu (Koordinator Lapangan) -->
                <div class="p-2.5 rounded-xl bg-white border border-blue-100 flex items-center justify-between gap-2 text-xs">
                    <div class="flex items-center gap-2 min-w-0">
                        <div class="w-6 h-6 rounded-lg bg-blue-100 text-[#1E3A8A] flex items-center justify-center shrink-0">
                            <i data-lucide="crown" class="w-3.5 h-3.5"></i>
                        </div>
                        <div class="min-w-0">
                            <span class="text-[10px] text-slate-400 font-bold block leading-none">Ketua Regu (Koordinator Lapangan):</span>
                            <span id="opModalTeamLeader" class="font-bold text-slate-900 truncate block mt-0.5">Nama Ketua Regu</span>
                        </div>
                    </div>
                    <a id="opModalTeamPhone" href="#" class="px-2.5 py-1 rounded-lg bg-blue-50 hover:bg-blue-100 text-[#1E3A8A] font-bold font-mono text-xs flex items-center gap-1.5 transition shrink-0">
                        <i data-lucide="phone-call" class="w-3 h-3"></i>
                        <span id="opModalTeamPhoneText">081234567816</span>
                    </a>
                </div>

                <!-- Hidden legacy fields to prevent JS reference errors -->
                <span id="opModalOfficerName" class="hidden"></span>
                <span id="opModalOfficerPhone" class="hidden"></span>

                <!-- Arahan Instruksi Teknis -->
                <div id="opModalInstructionRow" class="hidden text-xs text-slate-600 flex items-start gap-1.5 pt-1.5 border-t border-blue-200/50">
                    <i data-lucide="clipboard-list" class="w-3.5 h-3.5 text-[#1E3A8A] shrink-0 mt-0.5"></i>
                    <span>Instruksi: <span id="opModalOfficerInstruction" class="text-slate-800 font-medium italic">Lakukan perbaikan</span></span>
                </div>
            </div>

            <!-- Lokasi Kerusakan (Satu Kartu Bersih & Jelas) -->
            <div class="p-3.5 rounded-2xl bg-slate-50 border border-slate-200/80 space-y-1.5">
                <div class="flex items-center justify-between text-xs font-bold text-slate-700">
                    <span class="flex items-center gap-1.5">
                        <i data-lucide="map-pin" class="w-4 h-4 text-[#1E3A8A]"></i>
                        <span>Lokasi Kerusakan</span>
                    </span>
                    <span id="opModalCoords" class="font-mono text-[11px] font-bold text-[#1E3A8A] bg-white px-2 py-0.5 rounded-md border border-slate-200">
                        -
                    </span>
                </div>
                <p id="opModalFullAddress" class="text-xs font-semibold text-slate-800 leading-relaxed">
                    Alamat lokasi kerusakan...
                </p>
                <!-- Hidden dummy elements to maintain parseAdministrativeAddress compatibility without crashes -->
                <span id="opModalRoad" class="hidden"></span>
                <span id="opModalVillage" class="hidden"></span>
                <span id="opModalDistrict" class="hidden"></span>
                <span id="opModalRegency" class="hidden"></span>
            </div>

            <!-- Detail Kerusakan Ruas Panjang (Major) -->
            <div id="opModalMajorRoadBox" class="hidden p-3 rounded-2xl bg-blue-50/70 border border-blue-200 space-y-2 text-xs">
                <div class="flex items-center justify-between font-bold text-[#1E3A8A]">
                    <span class="flex items-center gap-1.5">
                        <i data-lucide="route" class="w-4 h-4"></i>
                        <span>Kerusakan Ruas Panjang</span>
                    </span>
                    <span id="opModalRoadLengthBadge" class="text-[10px] text-white bg-[#1E3A8A] px-2.5 py-0.5 rounded-full font-bold">
                        ≥ 500 Meter
                    </span>
                </div>
                <div class="grid grid-cols-2 gap-2">
                    <div class="p-2 rounded-xl bg-white border border-blue-100">
                        <span class="text-[10px] text-slate-400 block font-semibold">Estimasi Panjang:</span>
                        <span id="opModalRoadLength" class="font-bold text-[#1E3A8A]">-</span>
                    </div>
                    <div class="p-2 rounded-xl bg-white border border-blue-100">
                        <span class="text-[10px] text-slate-400 block font-semibold">Kondisi:</span>
                        <span id="opModalRoadCondition" class="font-bold text-[#1E3A8A]">-</span>
                    </div>
                </div>
                <div class="p-2 rounded-xl bg-white border border-blue-100 font-mono text-[11px] text-slate-600 space-y-0.5">
                    <div><span class="font-bold text-[#1E3A8A]">Awal:</span> <span id="opModalStartCoords">-</span></div>
                    <div><span class="font-bold text-slate-700">Akhir:</span> <span id="opModalEndCoords">-</span></div>
                </div>
            </div>

            <!-- Deskripsi Lengkap -->
            <div class="space-y-1.5">
                <h4 class="text-xs font-bold text-slate-700 flex items-center gap-1.5">
                    <i data-lucide="file-text" class="w-3.5 h-3.5 text-slate-400"></i>
                    <span>Keterangan Tambahan Pelapor</span>
                </h4>
                <div id="opModalDescription" class="p-3 rounded-xl bg-slate-50 border border-slate-200 text-xs text-slate-700 leading-relaxed whitespace-pre-line font-sans">
                    Deskripsi lengkap...
                </div>
            </div>

            <!-- Analisis AI Vision -->
            <div id="opModalAiBox" class="p-3 rounded-2xl bg-blue-50/60 border border-blue-100 space-y-2 text-xs">
                <div class="flex items-center justify-between font-bold text-[#1E3A8A]">
                    <span class="flex items-center gap-1.5">
                        <i data-lucide="sparkles" class="w-3.5 h-3.5"></i>
                        <span>Analisis Computer Vision AI</span>
                    </span>
                    <span id="opModalAiConfidence" class="text-[10px] text-emerald-700 bg-white px-2 py-0.5 rounded-md border border-emerald-200 font-semibold">
                        Akurasi 90%
                    </span>
                </div>
                <div class="grid grid-cols-2 gap-2">
                    <div class="p-2 rounded-xl bg-white border border-blue-100">
                        <span class="text-[10px] text-slate-400 block font-semibold">Tipe Kerusakan:</span>
                        <span id="opModalAiType" class="font-bold text-slate-800">-</span>
                    </div>
                    <div class="p-2 rounded-xl bg-white border border-blue-100">
                        <span class="text-[10px] text-slate-400 block font-semibold">Tingkat Prioritas:</span>
                        <span id="opModalAiPriority" class="font-bold text-slate-800">-</span>
                    </div>
                </div>
                <div class="p-2 rounded-xl bg-white border border-blue-100">
                    <span class="text-[10px] text-slate-400 block font-semibold">Estimasi Dimensi & Material:</span>
                    <span id="opModalAiEstimate" class="font-semibold text-slate-700">-</span>
                </div>
            </div>

        </div>

        <!-- Modal Footer: Tombol Tutup Saja -->
        <div class="px-5 py-3 border-t border-slate-100 bg-slate-50 flex items-center justify-end">
            <button type="button" onclick="closeOperatorModal()" class="px-5 py-2.5 rounded-xl bg-slate-200 hover:bg-slate-300 text-slate-800 font-bold text-xs transition cursor-pointer">
                Tutup Rincian
            </button>
        </div>

    </div>
</div>

<!-- MODAL VERIFIKASI & DISPOSISI PENUGASAN PETUGAS (CLEAN, FOKUS INPUT & VERIFIKASI) -->
<div id="dispatchModal" class="hidden fixed inset-0 z-50 overflow-y-auto bg-slate-900/65 flex items-center justify-center p-3 sm:p-5 overscroll-contain">
    <div class="bg-white w-full max-w-xl rounded-xl shadow-md border border-[#E2E8F0] overflow-hidden flex flex-col max-h-[92vh]">
        
        <!-- Header Modal Disposisi (Selaras Landing Page: Kerusakan -> Kategori -> Status) -->
        <div class="px-4 sm:px-5 py-3 sm:py-3.5 border-b border-[#E2E8F0] flex items-center justify-between bg-[#F8FAFC] gap-2">
            <div class="flex items-center gap-1.5 sm:gap-2 flex-nowrap min-w-0 overflow-x-auto no-scrollbar py-0.5">
                <span id="dispatchModalTypeBadge" class="shrink-0 px-2.5 py-1 rounded-md text-xs font-semibold bg-red-50 text-red-700 border border-red-200 inline-flex items-center gap-1.5 whitespace-nowrap">
                    <span class="w-1.5 h-1.5 rounded-full bg-red-600 shrink-0"></span>
                    <span>Kerusakan Ringan</span>
                </span>
                <span id="dispatchModalCategoryBadge" class="shrink-0 px-2.5 py-1 rounded-md text-xs font-semibold bg-slate-100 text-[#0F172A] border border-[#E2E8F0] inline-flex items-center whitespace-nowrap">
                    Kategori
                </span>
                <span id="dispatchModalStatusBadge" class="shrink-0 px-2.5 py-1 rounded-md text-xs font-bold bg-red-50 text-red-700 border border-red-200 whitespace-nowrap">
                    VERIFIKASI
                </span>
                <span id="dispatchModalCode" class="shrink-0 px-2 py-1 rounded-md bg-white text-[#475569] border border-[#E2E8F0] font-semibold text-[11px] font-mono whitespace-nowrap">
                    #ADU-0000
                </span>
            </div>
            <button type="button" onclick="closeDispatchModal()" class="w-9 h-9 rounded-lg bg-white hover:bg-slate-100 text-[#475569] hover:text-[#0F172A] border border-[#E2E8F0] flex items-center justify-center transition cursor-pointer shrink-0 ml-1" title="Tutup Detail" aria-label="Tutup Rincian">
                <i data-lucide="x" class="w-4 h-4"></i>
            </button>
        </div>

        <!-- Scrollable Modal Body -->
        <div class="p-5 overflow-y-auto space-y-4 flex-1 overscroll-contain">
            
            <!-- Ringkasan Singkat Aduan (Compact & Clean) -->
            <div class="p-3 rounded-xl bg-slate-50 border border-slate-200 flex items-center gap-3">
                <div class="relative w-12 h-12 rounded-lg overflow-hidden border border-slate-200 bg-white shrink-0">
                    <img id="dispatchModalPhoto" src="" alt="Foto Kerusakan" class="w-full h-full object-cover">
                    <div id="dispatchModalNoPhoto" class="hidden w-full h-full flex flex-col items-center justify-center text-slate-400 text-[8px] p-0.5 text-center bg-slate-100">
                        <i data-lucide="image-off" class="w-3.5 h-3.5 text-slate-300"></i>
                        <span>No Photo</span>
                    </div>
                </div>
                <div class="flex-1 min-w-0">
                    <h4 id="dispatchModalTitle" class="font-outfit font-extrabold text-sm text-slate-900 truncate">
                        Judul Aduan
                    </h4>
                    <p id="dispatchModalAddress" class="text-xs text-slate-500 truncate mt-0.5">
                        Alamat lokasi
                    </p>
                    <div class="flex items-center gap-2 mt-1 text-[11px] text-slate-600">
                        <span id="dispatchModalCitizen" class="font-medium text-slate-700">Nama Pelapor</span>
                        <span class="text-slate-300">•</span>
                        <button type="button" onclick="openOperatorModalFromDispatch()" class="font-bold text-[#1E3A8A] hover:underline inline-flex items-center gap-0.5 cursor-pointer">
                            <span>Detail Foto</span>
                            <i data-lucide="arrow-right" class="w-3 h-3"></i>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Form Disposisi Resmi -->
            <form id="dispatchForm" method="POST" action="" class="space-y-3.5">
                @csrf
                
                <!-- 1. Dinas Terkait (Locked/Compact) -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">
                        Dinas Penanggung Jawab
                    </label>
                    <div class="relative">
                        <select id="dispatchDepartmentSelect" name="department_id" required 
                            class="w-full text-xs rounded-xl border border-slate-200 py-2 pl-3 pr-8 bg-slate-100/70 text-slate-600 font-semibold pointer-events-none appearance-none cursor-not-allowed">
                            @foreach($departments as $dept)
                                <option value="{{ $dept->id }}" {{ str_contains(strtolower($dept->name), 'pekerjaan umum') ? 'selected' : '' }}>
                                    {{ $dept->name }}
                                </option>
                            @endforeach
                        </select>
                        <span class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400">
                            <i data-lucide="lock" class="w-3.5 h-3.5"></i>
                        </span>
                    </div>
                </div>

                <!-- 2. Regu Lapangan Pelaksana -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">
                        Regu Lapangan Pelaksana <span class="text-red-500">*</span>
                    </label>

                    @if(($dispatchMode ?? 'HYBRID') === 'SELF_CLAIM_ONLY')
                        <div class="p-2.5 rounded-xl bg-amber-50 border border-amber-200 text-amber-800 text-xs flex items-center gap-2">
                            <i data-lucide="info" class="w-4 h-4 text-amber-600 shrink-0"></i>
                            <span>Mode Klaim Mandiri: Laporan akan otomatis terbuka untuk diambil regu lapangan di aplikasi mobile.</span>
                        </div>
                        <input type="hidden" name="team_id" value="">
                    @else
                        <select id="dispatchTeamSelect" name="team_id" {{ ($dispatchMode ?? 'HYBRID') === 'OPERATOR_ONLY' ? 'required' : '' }}
                            class="w-full text-xs rounded-xl border border-slate-300 py-2 px-3 bg-white text-slate-800 font-semibold focus:ring-2 focus:ring-[#1E3A8A] focus:border-[#1E3A8A]">
                            @if(($dispatchMode ?? 'HYBRID') !== 'OPERATOR_ONLY')
                                <option value="">-- Buka Terbuka (Klaim Mandiri oleh Regu Lapangan) --</option>
                            @else
                                <option value="" disabled selected>Pilih Regu Lapangan...</option>
                            @endif
                            @foreach($teams as $tm)
                                <option value="{{ $tm->id }}">
                                    [{{ $tm->code }}] {{ $tm->name }} (Ketua: {{ $tm->leader?->name ?? 'Belum ada' }})
                                </option>
                            @endforeach
                        </select>
                    @endif
                </div>

                <!-- 3. Batas Waktu / SLA -->
                <div>
                    <div class="flex items-center justify-between text-xs font-bold text-slate-700 mb-1.5">
                        <span>Batas Waktu Penanganan (SLA) <span class="text-red-500">*</span></span>
                        <span id="dispatchDeadlinePreview" class="text-[11px] font-bold text-[#1E3A8A] bg-blue-50 px-2 py-0.5 rounded border border-blue-100">
                            Target: 3 Hari (SOP Standar)
                        </span>
                    </div>

                    <div class="grid grid-cols-4 gap-2">
                        <label class="flex items-center justify-center p-2 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 cursor-pointer text-center transition has-[:checked]:border-[#1E3A8A] has-[:checked]:bg-[#1E3A8A] has-[:checked]:text-white shadow-2xs">
                            <input type="radio" name="deadline_days" value="1" class="sr-only" onchange="updateDispatchSlaPreview(1, '1 Hari')">
                            <span class="text-xs font-bold">1 Hari</span>
                        </label>
                        <label class="flex items-center justify-center p-2 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 cursor-pointer text-center transition has-[:checked]:border-[#1E3A8A] has-[:checked]:bg-[#1E3A8A] has-[:checked]:text-white shadow-2xs">
                            <input type="radio" name="deadline_days" value="3" checked class="sr-only" onchange="updateDispatchSlaPreview(3, '3 Hari (SOP)')">
                            <span class="text-xs font-bold">3 Hari</span>
                        </label>
                        <label class="flex items-center justify-center p-2 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 cursor-pointer text-center transition has-[:checked]:border-[#1E3A8A] has-[:checked]:bg-[#1E3A8A] has-[:checked]:text-white shadow-2xs">
                            <input type="radio" name="deadline_days" value="5" class="sr-only" onchange="updateDispatchSlaPreview(5, '5 Hari')">
                            <span class="text-xs font-bold">5 Hari</span>
                        </label>
                        <label class="flex items-center justify-center p-2 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 cursor-pointer text-center transition has-[:checked]:border-[#1E3A8A] has-[:checked]:bg-[#1E3A8A] has-[:checked]:text-white shadow-2xs">
                            <input type="radio" name="deadline_days" value="7" class="sr-only" onchange="updateDispatchSlaPreview(7, '7 Hari')">
                            <span class="text-xs font-bold">7 Hari</span>
                        </label>
                    </div>
                </div>

                <!-- 4. Instruksi Penanganan Lapangan -->
                <div>
                    <div class="flex items-center justify-between text-xs font-bold text-slate-700 mb-1">
                        <span>Instruksi Petugas <span class="text-red-500">*</span></span>
                        <select id="dispatchTemplateSelect" onchange="applyDispatchTemplate(this.value)" 
                            class="text-[11px] rounded-lg border border-slate-200 py-0.5 px-2 bg-slate-50 text-slate-600 font-medium focus:ring-1 focus:ring-[#1E3A8A] cursor-pointer">
                            <option value="Lakukan perbaikan dan penanganan fisik sesuai SOP dinas teknis">Template: Standar SOP</option>
                            <option value="Lakukan survei, pengukuran dimensi kerusakan, dan asesmen teknis mendalam di titik lokasi">Template: Survei & Asesmen</option>
                            <option value="Lakukan tindakan darurat dan pengamanan area secepatnya untuk mencegah risiko kecelakaan warga">Template: Tindakan Darurat</option>
                            <option value="Lakukan pembersihan saluran, normalisasi aliran, serta perbaikan berkala rutin">Template: Normalisasi Rutin</option>
                        </select>
                    </div>

                    <textarea id="dispatchInstructionInput" name="instruction" rows="2" required
                        class="w-full text-xs rounded-xl border border-slate-200 py-2 px-3 bg-white text-slate-800 font-medium focus:ring-2 focus:ring-[#1E3A8A] focus:border-[#1E3A8A] leading-relaxed"
                        placeholder="Ketikkan instruksi untuk petugas...">Lakukan perbaikan dan penanganan fisik sesuai SOP dinas teknis</textarea>
                </div>

                <!-- Footer Tombol Aksi Modal Disposisi -->
                <div class="pt-2 border-t border-slate-100 flex items-center justify-between gap-2">
                    <button type="button" onclick="rejectFromDispatchModal()" 
                        class="py-2.5 px-3 rounded-xl bg-red-50 hover:bg-red-100 text-red-700 border border-red-200 font-bold text-xs transition flex items-center gap-1.5 cursor-pointer">
                        <i data-lucide="x-circle" class="w-4 h-4"></i>
                        <span>Tolak Laporan</span>
                    </button>

                    <div class="flex items-center gap-2">
                        <button type="button" onclick="closeDispatchModal()" class="py-2.5 px-3.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs transition cursor-pointer">
                            Tutup
                        </button>
                        <button type="submit" 
                            class="py-2.5 px-5 rounded-xl bg-[#1E3A8A] hover:bg-[#172554] active:scale-[0.99] text-white font-bold text-xs shadow-md transition flex items-center gap-2 cursor-pointer">
                            <i data-lucide="check-circle" class="w-4 h-4"></i>
                            <span>Konfirmasi & Verifikasi</span>
                        </button>
                    </div>
                </div>
            </form>
        </div>

    </div>
</div>

<!-- MODAL TEMPATKAN / UBAH PENUGASAN REGU (TAB DITUGASKAN) -->
<div id="operatorAssignTeamModal" class="fixed inset-0 z-50 hidden items-center justify-center p-3 sm:p-5 bg-slate-900/75 transition-opacity duration-150">
    <div class="bg-white w-full max-w-lg rounded-3xl shadow-2xl border border-slate-200 overflow-hidden flex flex-col animate-in fade-in zoom-in-95">
        <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between bg-slate-50/70">
            <div class="flex items-center gap-2">
                <span id="assignTeamCode" class="px-2.5 py-1 rounded-lg bg-[#1E3A8A]/10 text-[#1E3A8A] font-bold text-xs font-mono">
                    #ADU-0000
                </span>
                <span class="font-outfit font-extrabold text-sm text-slate-900">
                    Tempatkan Regu Lapangan
                </span>
            </div>
            <button type="button" onclick="closeAssignTeamModal()" class="w-8 h-8 rounded-full bg-slate-200/70 hover:bg-slate-300 text-slate-700 flex items-center justify-center transition cursor-pointer">
                <i data-lucide="x" class="w-4 h-4"></i>
            </button>
        </div>

        <form id="assignTeamForm" method="POST" action="" class="p-6 space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Judul Laporan</label>
                <div id="assignTeamTitle" class="text-xs text-slate-600 bg-slate-50 p-2.5 rounded-xl border border-slate-200 line-clamp-2 font-medium">
                    -
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1.5">
                    Pilih Regu Lapangan Pelaksana <span class="text-red-500">*</span>
                </label>
                <select id="assignTeamSelect" name="team_id" required
                    class="w-full text-xs sm:text-sm rounded-xl border border-slate-300 py-2.5 px-3 bg-white text-slate-800 font-semibold focus:ring-2 focus:ring-[#1E3A8A]/20 focus:border-[#1E3A8A]">
                    <option value="" disabled selected>Pilih Regu Lapangan...</option>
                    @if(($dispatchMode ?? 'HYBRID') === 'HYBRID')
                        <option value="">-- Buka Terbuka untuk Klaim Mandiri --</option>
                    @endif
                    @foreach($teams as $tm)
                        <option value="{{ $tm->id }}">
                            [{{ $tm->code }}] {{ $tm->name }} ({{ $tm->members->count() }} Personil - Ketua Regu: {{ $tm->leader?->name ?? 'Belum ada' }}) - Wilayah: {{ $tm->district ?? $tm->coverage_area ?? 'Kecamatan Terkait' }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Instruksi Khusus untuk Regu</label>
                <textarea id="assignTeamInstruction" name="instruction" rows="2"
                    class="w-full text-xs rounded-xl border border-slate-300 p-2.5 focus:ring-2 focus:ring-[#1E3A8A]/20 focus:border-[#1E3A8A]"
                    placeholder="Instruksi pengerjaan perbaikan fisik di lapangan..."></textarea>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1.5">Tenggat Waktu Pengerjaan (SLA)</label>
                <div class="grid grid-cols-3 gap-2">
                    <label class="flex items-center justify-center p-2 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 cursor-pointer text-center text-xs font-bold text-slate-700 has-[:checked]:border-[#1E3A8A] has-[:checked]:bg-blue-50 has-[:checked]:text-[#1E3A8A]">
                        <input type="radio" name="deadline_days" value="1" class="sr-only">
                        <span>1 Hari</span>
                    </label>
                    <label class="flex items-center justify-center p-2 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 cursor-pointer text-center text-xs font-bold text-slate-700 has-[:checked]:border-[#1E3A8A] has-[:checked]:bg-blue-50 has-[:checked]:text-[#1E3A8A]">
                        <input type="radio" name="deadline_days" value="3" checked class="sr-only">
                        <span>3 Hari (SOP)</span>
                    </label>
                    <label class="flex items-center justify-center p-2 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 cursor-pointer text-center text-xs font-bold text-slate-700 has-[:checked]:border-[#1E3A8A] has-[:checked]:bg-blue-50 has-[:checked]:text-[#1E3A8A]">
                        <input type="radio" name="deadline_days" value="7" class="sr-only">
                        <span>7 Hari</span>
                    </label>
                </div>
            </div>

            <div class="flex items-center justify-end gap-2 pt-2 border-t border-slate-100">
                <button type="button" onclick="closeAssignTeamModal()" class="px-4 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs transition cursor-pointer">
                    Batal
                </button>
                <button type="submit" class="px-5 py-2.5 rounded-xl bg-[#1E3A8A] hover:bg-blue-900 text-white font-bold text-xs flex items-center gap-1.5 shadow-md shadow-blue-900/20 transition cursor-pointer">
                    <i data-lucide="check" class="w-3.5 h-3.5"></i>
                    <span>Tetapkan Regu</span>
                </button>
            </div>
        </form>
    </div>
</div>

<!-- MODAL VALIDASI HASIL PENGERJAAN LAPANGAN (DETAIL LENGKAP DENGAN FOTO ATAS-BAWAH & AKSI REVISI/SELESAI) -->
<div id="operatorValidationModal" class="fixed inset-0 z-50 hidden flex items-center justify-center p-3 sm:p-5 bg-slate-900/75 transition-opacity duration-150 overscroll-contain">
    <div class="bg-white w-full max-w-xl rounded-2xl shadow-2xl border border-slate-200 overflow-hidden flex flex-col max-h-[92vh] animate-in fade-in zoom-in-95" style="transform: translateZ(0); will-change: transform;">
        
        <!-- Modal Top Header: Status Ringkas & Elegan (Kerusakan -> Kategori -> Status) -->
        <div class="px-4 sm:px-5 py-3 sm:py-3.5 border-b border-slate-100 flex items-center justify-between bg-slate-50/80 gap-2">
            <div class="flex items-center gap-1.5 sm:gap-2 flex-nowrap min-w-0 overflow-x-auto no-scrollbar py-0.5">
                <!-- 1. Tingkat Kerusakan -->
                <span id="valModalTypeBadge" class="shrink-0 px-2 sm:px-2.5 py-0.5 sm:py-1 rounded-full text-[10.5px] sm:text-xs font-bold bg-red-50 text-red-700 border border-red-200 inline-flex items-center gap-1 sm:gap-1.5 whitespace-nowrap">
                    <span class="w-1.5 h-1.5 rounded-full bg-red-500 shrink-0"></span>
                    <span>Kerusakan Ringan</span>
                </span>
                <!-- 2. Kategori Infrastruktur -->
                <span id="valModalCategoryBadge" class="shrink-0 px-2 sm:px-2.5 py-0.5 sm:py-1 rounded-full bg-slate-100 text-slate-700 font-semibold text-[10.5px] sm:text-xs border border-slate-200 inline-flex items-center whitespace-nowrap">
                    Kategori
                </span>
                <!-- 3. Status Pengerjaan -->
                <span class="shrink-0 px-2 sm:px-2.5 py-0.5 sm:py-1 rounded-full text-[10.5px] sm:text-xs font-bold bg-amber-50 text-amber-800 border border-amber-200/80 whitespace-nowrap">
                    MENUNGGU VALIDASI
                </span>
            </div>
            <button type="button" onclick="closeValidationModal()" class="w-8 h-8 rounded-full bg-slate-200/70 hover:bg-slate-300 text-slate-700 flex items-center justify-center transition cursor-pointer shrink-0 ml-1">
                <i data-lucide="x" class="w-4 h-4"></i>
            </button>
        </div>

        <!-- Modal Scrollable Body (Fokus, Bersih & Terstruktur) -->
        <div class="p-5 overflow-y-auto space-y-4 flex-1 overscroll-contain" style="transform: translateZ(0); -webkit-overflow-scrolling: touch; will-change: scroll-position;">
            
            <!-- 1. Foto Bukti Kerusakan Warga (Sebelum) -->
            <div id="valModalCitizenPhotoSection" class="space-y-1.5">
                <div class="rounded-2xl overflow-hidden border border-slate-200 bg-slate-900/5 relative max-h-64 sm:max-h-72 flex items-center justify-center">
                    <img id="valModalCitizenPhoto" src="" alt="Foto Kerusakan Warga" class="w-full h-full object-cover max-h-64 sm:max-h-72">
                    <div class="absolute bottom-2.5 left-2.5 bg-black/75 backdrop-blur-xs text-white text-[10px] font-bold px-2.5 py-1 rounded-lg flex items-center gap-1.5 shadow-xs">
                        <i data-lucide="image" class="w-3.5 h-3.5"></i>
                        <span id="valModalCitizenPhotoLabel">Foto Bukti Kerusakan</span>
                    </div>
                </div>
                <!-- Gallery Thumbnails Strip (Khusus Foto Warga) -->
                <div id="valModalCitizenGallery" class="flex items-center gap-2 overflow-x-auto pb-1 hidden"></div>
            </div>

            <!-- 2. Foto Bukti Penanganan Petugas Lapangan (Sesudah) -->
            <div id="valModalOfficerPhotoSection" class="space-y-1.5">
                <div class="rounded-2xl overflow-hidden border border-emerald-300 bg-emerald-50/50 relative max-h-64 sm:max-h-72 flex items-center justify-center">
                    <img id="valModalOfficerPhoto" src="" alt="Foto Hasil Pengerjaan Petugas" class="w-full h-full object-cover max-h-64 sm:max-h-72">
                    <div class="absolute bottom-2.5 left-2.5 bg-emerald-950/90 backdrop-blur-xs text-white text-[10px] font-bold px-2.5 py-1 rounded-lg flex items-center gap-1.5 shadow-md">
                        <i data-lucide="check-circle-2" class="w-3.5 h-3.5 text-emerald-400"></i>
                        <span id="valModalOfficerPhotoLabel">Foto Bukti Penyelesaian Fisik</span>
                    </div>
                </div>
                <!-- Gallery Thumbnails Strip (Khusus Foto Petugas) -->
                <div id="valModalOfficerGallery" class="flex items-center gap-2 overflow-x-auto pb-1 hidden"></div>
            </div>

            <!-- Judul & Info Waktu Laporan -->
            <div class="pt-0.5">
                <span id="valModalCode" class="text-[11px] font-mono font-medium text-slate-400 block tracking-wide mb-0.5">
                    #ADU-0000
                </span>
                <h3 id="valModalTitle" class="font-outfit font-extrabold text-lg sm:text-xl text-slate-900 leading-snug">
                    Judul Laporan
                </h3>
                <div class="flex items-center gap-1.5 text-xs text-slate-500 pt-0.5">
                    <i data-lucide="calendar" class="w-3.5 h-3.5 text-slate-400"></i>
                    <span id="valModalDate">03 Sep 2026</span>
                </div>
            </div>

            <!-- Identitas Pelapor Aduan (Bersih & Rapi) -->
            <div class="p-3 rounded-2xl bg-slate-50 border border-slate-200/80 flex items-center justify-between">
                <div class="flex items-center gap-2.5 min-w-0">
                    <div class="w-8 h-8 rounded-full bg-[#1E3A8A] text-white flex items-center justify-center font-bold text-xs shrink-0 shadow-2xs">
                        <i data-lucide="user" class="w-4 h-4"></i>
                    </div>
                    <div class="min-w-0">
                        <span class="text-[10px] text-slate-400 block font-bold uppercase tracking-wider leading-none">Pelapor Aduan</span>
                        <div class="flex items-center gap-1.5 mt-0.5">
                            <span id="valModalCitizenName" class="font-outfit font-extrabold text-xs sm:text-sm text-slate-900 truncate">
                                Nama Pelapor
                            </span>
                            <span id="valModalAnonymousBadge" class="hidden text-[9px] font-bold px-1.5 py-0.5 rounded bg-slate-200 text-slate-600 shrink-0">Anonim</span>
                        </div>
                    </div>
                </div>
                <div class="flex items-center gap-1.5 text-xs text-slate-700 font-bold shrink-0">
                    <i data-lucide="phone" class="w-3.5 h-3.5 text-[#1E3A8A]"></i>
                    <a id="valModalCitizenPhone" href="#" class="font-mono hover:underline text-[#1E3A8A]">08123456789</a>
                </div>
            </div>

            <!-- Regu Penanganan Lapangan (Sesuai Permintaan User: Menampilkan Regu, Ketua Regu, & Nomor HP) -->
            <div id="valModalOfficerCard" class="p-3.5 rounded-2xl bg-blue-50/60 border border-blue-200/80 space-y-2.5">
                <div class="flex items-start justify-between gap-2">
                    <div class="flex items-center gap-2.5 min-w-0">
                        <div class="w-9 h-9 rounded-xl bg-[#1E3A8A] text-white flex items-center justify-center font-bold text-xs shrink-0 shadow-2xs">
                            <i data-lucide="hard-hat" class="w-4 h-4"></i>
                        </div>
                        <div class="min-w-0">
                            <span class="text-[10px] text-[#1E3A8A] font-extrabold uppercase tracking-wider block">Regu Lapangan Pelaksana</span>
                            <h4 id="valModalTeamName" class="font-outfit font-extrabold text-xs sm:text-sm text-slate-900 truncate leading-tight">
                                Regu Lapangan PUPR
                            </h4>
                            <span id="valModalOfficerDept" class="text-[10.5px] font-semibold text-slate-500 block truncate">
                                Dinas Pekerjaan Umum dan Penataan Ruang (PUPR)
                            </span>
                        </div>
                    </div>
                    <div id="valModalDeadlineBox" class="text-right shrink-0">
                        <span class="text-[10px] text-slate-400 block font-medium">Target Waktu:</span>
                        <span id="valModalOfficerDeadline" class="text-xs font-bold text-[#1E3A8A] bg-white px-2 py-0.5 rounded border border-blue-200">-</span>
                    </div>
                </div>

                <!-- Baris Kontak Ketua Regu (Koordinator Lapangan) -->
                <div class="p-2.5 rounded-xl bg-white border border-blue-100 flex items-center justify-between gap-2 text-xs">
                    <div class="flex items-center gap-2 min-w-0">
                        <div class="w-6 h-6 rounded-lg bg-blue-100 text-[#1E3A8A] flex items-center justify-center shrink-0">
                            <i data-lucide="crown" class="w-3.5 h-3.5"></i>
                        </div>
                        <div class="min-w-0">
                            <span class="text-[10px] text-slate-400 font-bold block leading-none">Ketua Regu (Koordinator Lapangan):</span>
                            <span id="valModalTeamLeader" class="font-bold text-slate-900 truncate block mt-0.5">Nama Ketua Regu</span>
                        </div>
                    </div>
                    <a id="valModalTeamPhone" href="#" class="px-2.5 py-1 rounded-lg bg-blue-50 hover:bg-blue-100 text-[#1E3A8A] font-bold font-mono text-xs flex items-center gap-1.5 transition shrink-0">
                        <i data-lucide="phone-call" class="w-3 h-3"></i>
                        <span id="valModalTeamPhoneText">081234567890</span>
                    </a>
                </div>

                <!-- Hidden legacy elements for JS safety -->
                <span id="valModalOfficerName" class="hidden"></span>
                <span id="valModalOfficerPhone" class="hidden"></span>

                <!-- Arahan Instruksi Teknis -->
                <div id="valModalInstructionRow" class="hidden text-xs text-slate-600 flex items-start gap-1.5 pt-1.5 border-t border-blue-200/50">
                    <i data-lucide="clipboard-list" class="w-3.5 h-3.5 text-[#1E3A8A] shrink-0 mt-0.5"></i>
                    <span>Instruksi: <span id="valModalOfficerInstruction" class="text-slate-800 font-medium italic">Lakukan perbaikan</span></span>
                </div>
            </div>

            <!-- Lokasi Kerusakan (Satu Kartu Bersih & Jelas) -->
            <div class="p-3.5 rounded-2xl bg-slate-50 border border-slate-200/80 space-y-1.5">
                <div class="flex items-center justify-between text-xs font-bold text-slate-700">
                    <span class="flex items-center gap-1.5">
                        <i data-lucide="map-pin" class="w-4 h-4 text-[#1E3A8A]"></i>
                        <span>Lokasi Kerusakan</span>
                    </span>
                    <span id="valModalCoords" class="font-mono text-[11px] font-bold text-[#1E3A8A] bg-white px-2 py-0.5 rounded-md border border-slate-200">
                        -
                    </span>
                </div>
                <p id="valModalFullAddress" class="text-xs font-semibold text-slate-800 leading-relaxed">
                    Alamat lokasi kerusakan...
                </p>
                <!-- Hidden dummy elements to maintain parseAdminAddress compatibility -->
                <span id="valModalRoad" class="hidden"></span>
                <span id="valModalVillage" class="hidden"></span>
                <span id="valModalDistrict" class="hidden"></span>
                <span id="valModalRegency" class="hidden"></span>
            </div>

            <!-- Detail Kerusakan Berat Ruas Panjang (Major) -->
            <div id="valModalMajorRoadBox" class="hidden p-3 rounded-2xl bg-blue-50/70 border border-blue-200 space-y-2 text-xs">
                <div class="flex items-center justify-between font-bold text-[#1E3A8A]">
                    <span class="flex items-center gap-1.5">
                        <i data-lucide="route" class="w-4 h-4 text-[#1E3A8A]"></i>
                        <span>Kerusakan Ruas Panjang</span>
                    </span>
                    <span id="valModalRoadLengthBadge" class="text-[10px] text-white bg-[#1E3A8A] px-2.5 py-0.5 rounded-full font-bold">
                        ≥ 500 Meter
                    </span>
                </div>
                <div class="grid grid-cols-2 gap-2">
                    <div class="p-2 rounded-xl bg-white border border-blue-100">
                        <span class="text-[10px] text-slate-400 block font-semibold">Estimasi Panjang:</span>
                        <span id="valModalRoadLength" class="font-bold text-[#1E3A8A]">-</span>
                    </div>
                    <div class="p-2 rounded-xl bg-white border border-blue-100">
                        <span class="text-[10px] text-slate-400 block font-semibold">Kondisi:</span>
                        <span id="valModalRoadCondition" class="font-bold text-[#1E3A8A]">-</span>
                    </div>
                </div>
                <div class="p-2 rounded-xl bg-white border border-blue-100 font-mono text-[11px] text-slate-600 space-y-0.5">
                    <div><span class="font-bold text-[#1E3A8A]">Awal:</span> <span id="valModalStartCoords">-</span></div>
                    <div><span class="font-bold text-slate-700">Akhir:</span> <span id="valModalEndCoords">-</span></div>
                </div>
            </div>

            <!-- Deskripsi Lengkap -->
            <div class="space-y-1.5">
                <h4 class="text-xs font-bold text-slate-700 flex items-center gap-1.5">
                    <i data-lucide="align-left" class="w-3.5 h-3.5 text-[#1E3A8A]"></i>
                    <span>Deskripsi Kerusakan</span>
                </h4>
                <div id="valModalDescription" class="p-3.5 rounded-2xl bg-slate-50 border border-slate-200/80 text-xs text-slate-700 leading-relaxed whitespace-pre-line font-sans">
                    Deskripsi lengkap...
                </div>
            </div>

            <!-- Analisis AI Vision -->
            <div id="valModalAiBox" class="p-3 rounded-2xl bg-blue-50/60 border border-blue-200/80 space-y-2 text-xs">
                <div class="flex items-center justify-between font-bold text-[#1E3A8A]">
                    <span class="flex items-center gap-1.5">
                        <i data-lucide="sparkles" class="w-4 h-4"></i>
                        <span>Analisis AI Vision</span>
                    </span>
                    <span id="valModalAiConfidence" class="text-[10px] text-[#1E3A8A] bg-white px-2 py-0.5 rounded-md border border-blue-200 font-bold">
                        Akurasi 90%
                    </span>
                </div>
                <div class="grid grid-cols-2 gap-2">
                    <div class="p-2 rounded-xl bg-white border border-blue-100">
                        <span class="text-[10px] text-slate-400 block font-semibold">Tipe:</span>
                        <span id="valModalAiType" class="font-bold text-[#1E3A8A]">-</span>
                    </div>
                    <div class="p-2 rounded-xl bg-white border border-blue-100">
                        <span class="text-[10px] text-slate-400 block font-semibold">Prioritas:</span>
                        <span id="valModalAiPriority" class="font-bold text-[#1E3A8A]">-</span>
                    </div>
                    <div class="p-2 rounded-xl bg-white border border-blue-100 col-span-2">
                        <span class="text-[10px] text-slate-400 block font-semibold">Estimasi Dimensi & Material:</span>
                        <span id="valModalAiEstimate" class="font-medium text-slate-700 text-[11px]">-</span>
                    </div>
                </div>
            </div>

        </div>

        <!-- Modal Footer: Tombol Tutup, Revisi (dengan Catatan), & Selesai (Setujui) -->
        <div class="px-6 py-3.5 border-t border-slate-100 bg-slate-50 flex items-center justify-between gap-2 flex-wrap">
            <div>
                <button type="button" onclick="closeValidationModal()" class="px-5 py-2.5 rounded-xl bg-slate-200 hover:bg-slate-300 text-slate-800 font-bold text-xs transition cursor-pointer">
                    Tutup Rincian
                </button>
            </div>
            <div class="flex items-center gap-2">
                <button type="button" onclick="openRevisionFromValidationModal()" 
                    class="px-4 py-2.5 rounded-xl bg-slate-100 hover:bg-amber-50 text-slate-700 hover:text-amber-800 border border-slate-200 hover:border-amber-300 font-bold text-xs transition flex items-center gap-1.5 cursor-pointer">
                    <i data-lucide="rotate-ccw" class="w-4 h-4"></i>
                    <span>Revisi</span>
                </button>
                <button type="button" onclick="approveFromValidationModal()" 
                    class="px-5 py-2.5 rounded-xl bg-[#1E3A8A] hover:bg-[#172554] active:scale-[0.99] text-white font-bold text-xs shadow-md hover:shadow-lg transition flex items-center gap-2 cursor-pointer">
                    <i data-lucide="check-circle-2" class="w-4 h-4"></i>
                    <span>Selesai</span>
                </button>
            </div>
        </div>

    </div>
</div>

<!-- Form Submit Aksi Validasi (Selesai / Revisi) -->
<form id="validationActionForm" method="POST" action="" class="hidden">
    @csrf
    <input type="hidden" name="decision" id="validationDecisionInput" value="COMPLETED">
    <input type="hidden" name="notes" id="validationNotesInput" value="">
</form>

<!-- Form Tolak Cepat (Hoax) -->
<form id="quickRejectForm" method="POST" action="" class="hidden">
    @csrf
    <input type="hidden" name="reason" value="Laporan tidak memenuhi kriteria kelayakan atau bukti lapangan tidak sesuai.">
</form>

<!-- MODAL KONFIRMASI TOLAK LAPORAN (CLEAN, ELEGAN & SNAP-RESPONSE) -->
<div id="rejectConfirmModal" class="hidden fixed inset-0 z-50 overflow-y-auto bg-slate-900/50 backdrop-blur-xs flex items-center justify-center p-4">
    <div class="card-white rounded-3xl p-6 sm:p-7 bg-white border border-slate-200 shadow-2xl max-w-md w-full space-y-5 relative">
        <!-- Tombol Tutup Silang -->
        <button type="button" onclick="closeRejectModal()" class="absolute top-4 right-4 p-2 rounded-xl text-slate-400 hover:text-slate-700 hover:bg-slate-100 transition">
            <i data-lucide="x" class="w-4 h-4"></i>
        </button>

        <!-- Header Modal & Ikon -->
        <div class="flex items-start gap-4">
            <div class="w-12 h-12 rounded-2xl bg-red-50 text-red-600 border border-red-100 flex items-center justify-center shrink-0 shadow-2xs">
                <i data-lucide="shield-alert" class="w-6 h-6"></i>
            </div>
            <div class="space-y-1 pr-6">
                <h3 class="font-outfit font-extrabold text-base text-slate-900 leading-snug">
                    Tolak Laporan Pengaduan
                </h3>
                <p class="text-xs text-slate-500 leading-relaxed">
                    Pengaduan akan ditandai sebagai ditolak karena tidak memenuhi syarat kelayakan dan proses penugasan ke dinas terkait akan dihentikan.
                </p>
            </div>
        </div>

        <!-- Detail Kode Aduan & Alasan -->
        <div class="p-3.5 bg-slate-50 rounded-2xl border border-slate-200 space-y-2.5 text-xs">
            <div class="flex items-center justify-between">
                <span class="text-slate-500 font-medium">Nomor Aduan:</span>
                <span id="rejectModalCode" class="font-bold text-red-600 bg-red-50 px-2.5 py-0.5 rounded-lg border border-red-200 font-mono text-xs">
                    #ADU-0007
                </span>
            </div>
            <div class="pt-2 border-t border-slate-200">
                <label for="rejectModalReasonInput" class="block font-semibold text-slate-700 mb-1 text-[11px]">
                    Alasan Penolakan Kedinasan:
                </label>
                <input type="text" id="rejectModalReasonInput" value="Laporan tidak memenuhi kriteria kelayakan atau bukti lapangan tidak sesuai." 
                    class="w-full text-xs rounded-xl border border-slate-200 py-2 px-3 bg-white focus:outline-none focus:border-red-500 focus:ring-1 focus:ring-red-500 text-slate-800">
            </div>
        </div>

        <!-- Tombol Aksi -->
        <div class="flex items-center justify-end gap-2.5 pt-1">
            <button type="button" onclick="closeRejectModal()" 
                class="px-4 py-2.5 rounded-xl border border-slate-200 bg-white hover:bg-slate-100 text-slate-700 font-semibold text-xs transition">
                Batalkan
            </button>
            <button type="button" id="confirmRejectBtn" onclick="executeRejectComplaint()" 
                class="px-4 py-2.5 rounded-xl bg-red-600 hover:bg-red-700 text-white font-bold text-xs shadow-xs transition flex items-center gap-1.5">
                <i data-lucide="shield-x" class="w-4 h-4"></i>
                <span>Tolak Laporan</span>
            </button>
        </div>
    </div>
</div>

<!-- MODAL KONFIRMASI MINTA REVISI PEKERJAAN LAPANGAN -->
<div id="revisionConfirmModal" class="hidden fixed inset-0 z-50 overflow-y-auto bg-slate-900/50 backdrop-blur-xs flex items-center justify-center p-4">
    <div class="card-white rounded-3xl p-6 sm:p-7 bg-white border border-slate-200 shadow-2xl max-w-md w-full space-y-5 relative">
        <!-- Tombol Tutup Silang -->
        <button type="button" onclick="closeRevisionModal()" class="absolute top-4 right-4 p-2 rounded-xl text-slate-400 hover:text-slate-700 hover:bg-slate-100 transition">
            <i data-lucide="x" class="w-4 h-4"></i>
        </button>

        <!-- Header Modal & Ikon -->
        <div class="flex items-start gap-4">
            <div class="w-12 h-12 rounded-2xl bg-amber-50 text-amber-600 border border-amber-200 flex items-center justify-center shrink-0 shadow-2xs">
                <i data-lucide="rotate-ccw" class="w-6 h-6"></i>
            </div>
            <div class="space-y-1 pr-6">
                <h3 class="font-outfit font-extrabold text-base text-slate-900 leading-snug">
                    Instruksi Revisi Lapangan
                </h3>
                <p class="text-xs text-slate-500 leading-relaxed">
                    Kirimkan instruksi perbaikan kepada petugas lapangan untuk menyempurnakan hasil pekerjaan fisik.
                </p>
            </div>
        </div>

        <!-- Detail Kode Aduan & Catatan Revisi -->
        <div class="p-3.5 bg-slate-50 rounded-2xl border border-slate-200 space-y-2.5 text-xs">
            <div class="flex items-center justify-between">
                <span class="text-slate-500 font-medium">Nomor Aduan:</span>
                <span id="revisionModalCode" class="font-bold text-amber-800 bg-amber-50 px-2.5 py-0.5 rounded-lg border border-amber-200 font-mono text-xs">
                    #ADU-0001
                </span>
            </div>
            <div class="pt-2 border-t border-slate-200">
                <label for="revisionModalNotesInput" class="block font-semibold text-slate-700 mb-1 text-[11px]">
                    Catatan Arahan Perbaikan:
                </label>
                <input type="text" id="revisionModalNotesInput" value="Pekerjaan fisik belum rata dan perlu perapihan ulang di lokasi." 
                    class="w-full text-xs rounded-xl border border-slate-200 py-2 px-3 bg-white focus:outline-none focus:border-amber-500 focus:ring-1 focus:ring-amber-500 text-slate-800">
            </div>
        </div>

        <!-- Tombol Aksi -->
        <div class="flex items-center justify-end gap-2.5 pt-1">
            <button type="button" onclick="closeRevisionModal()" 
                class="px-4 py-2.5 rounded-xl border border-slate-200 bg-white hover:bg-slate-100 text-slate-700 font-semibold text-xs transition">
                Batalkan
            </button>
            <button type="button" onclick="executeRevision()" 
                class="px-4 py-2.5 rounded-xl bg-amber-600 hover:bg-amber-700 text-white font-bold text-xs shadow-xs transition flex items-center gap-1.5">
                <i data-lucide="send" class="w-4 h-4"></i>
                <span>Kirim Permintaan Revisi</span>
            </button>
        </div>
    </div>
</div>

<style>
    /* Sembunyikan scrollbar browser yang kaku di navigasi tab */
    .no-scrollbar::-webkit-scrollbar {
        display: none !important;
    }
    .no-scrollbar {
        -ms-overflow-style: none !important;
        scrollbar-width: none !important;
    }
</style>

<!-- Chart.js CDN -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
    // Data seluruh aduan dari semua tab agar modal detail berfungsi instan di mana pun
    @php
        $allComplaints = $pendingVerification->concat($dispatched)->concat($inProgress)->concat($waitingValidation)->concat($completed)->unique('id');
        $operatorComplaintsJson = $allComplaints->map(function($c) {
            $citizenPhotos = $c->photos->filter(function($p) {
                return $p->type !== 'resolution_after';
            })->map(fn($p) => $p->url ?? asset('storage/' . $p->storage_key))->values();

            $officerPhotos = $c->photos->filter(function($p) {
                return $p->type === 'resolution_after';
            })->map(fn($p) => $p->url ?? asset('storage/' . $p->storage_key))->values();

            $firstPhoto = $citizenPhotos->first() ?? ($c->photos->first() ? ($c->photos->first()->url ?? asset('storage/' . $c->photos->first()->storage_key)) : null);
            $completionPhoto = $officerPhotos->first() ?? null;

            $disp = $c->latestDisposition;
            $team = $disp?->team ?? $disp?->assignee?->teams?->first();
            $leader = $team?->leader ?? ($disp?->assignee && $disp->assignee->role === 'PETUGAS' ? $disp->assignee : null);

            return [
                'id' => $c->id,
                'public_code' => $c->public_code,
                'title' => $c->title,
                'status' => $c->status,
                'category_name' => $c->category?->name ?? 'Infrastruktur',
                'category_id' => $c->category_id,
                'created_at_human' => $c->created_at->diffForHumans(),
                'created_at_full' => $c->created_at->format('d M Y, H:i') . ' WIB',
                'citizen_name' => $c->citizen?->name ?? $c->user?->name ?? 'Warga Terdaftar',
                'citizen_phone' => $c->citizen?->phone ?? $c->user?->phone ?? '-',
                'is_anonymous' => (bool)$c->is_anonymous,
                'team_id' => $team?->id,
                'team_code' => $team?->code,
                'team_name' => $team?->name,
                'team_leader_name' => $leader?->name ?? ($team ? 'Koordinator Regu' : null),
                'team_leader_phone' => $leader?->phone ?? ($disp?->assignee?->phone ?? null),
                'officer_name' => $disp?->assignee?->name ?? null,
                'officer_phone' => $disp?->assignee?->phone ?? null,
                'department_name' => $disp?->department?->name ?? 'Dinas PUPR',
                'deadline' => $disp?->deadline ? \Illuminate\Support\Carbon::parse($disp->deadline)->locale('id')->isoFormat('D MMM Y') : null,
                'instruction' => $disp?->instruction ?? null,
                'address' => $c->address,
                'latitude' => $c->latitude,
                'longitude' => $c->longitude,
                'description' => $c->description ?: 'Tidak ada keterangan tambahan dari pelapor.',
                'priority_level' => $c->priority_level ?? 'MEDIUM',
                'severity_level' => $c->severity_level,
                'severity_text'  => $c->severity_text,
                'priority_score' => $c->priority_score ?? 50,
                'ai_damage_type' => $c->ai_damage_type,
                'ai_category' => $c->ai_category,
                'ai_repair_estimate' => $c->ai_repair_estimate,
                'ai_confidence' => round(($c->ai_confidence ?? 0.9) * 100),
                'photo_url' => $firstPhoto,
                'completion_photo_url' => $completionPhoto,
                'citizen_photos' => $citizenPhotos->all(),
                'officer_photos' => $officerPhotos->all(),
                'damage_type' => $c->damage_type,
                'damage_type_label' => $c->damage_type_label,
                'road_length' => $c->road_length,
                'road_condition' => $c->road_condition,
                'end_latitude' => $c->end_latitude,
                'end_longitude' => $c->end_longitude,
                'route_coordinates' => $c->route_coordinates,
                'photos_list' => $c->photos->map(fn($p) => $p->url ?? asset('storage/' . $p->storage_key))->values(),
            ];
        })->values();
    @endphp

    const operatorComplaints = @json($operatorComplaintsJson);
    let operatorMapInitialized = false;

    // FUNGSI PINDAH TAB INSTAN (0ms Tanpa Loading / Reload)
    function switchTab(tabId) {
        // 1. Sembunyikan semua tab panel
        document.querySelectorAll('.tab-panel').forEach(panel => {
            panel.classList.add('hidden');
        });

        // 2. Munculkan tab panel yang dituju
        const activePanel = document.getElementById('tabPanel-' + tabId);
        if (activePanel) {
            activePanel.classList.remove('hidden');
        }

        // 3. Update styling tombol tab seragam desain Admin
        document.querySelectorAll('.tab-btn').forEach(btn => {
            btn.classList.remove('bg-[#1E3A8A]', 'text-white', 'border-[#1E3A8A]');
            btn.classList.add('bg-white', 'text-[#0F172A]', 'border-[#E2E8F0]', 'hover:bg-[#F8FAFC]');
            const badge = btn.querySelector('.tab-badge');
            if (badge) {
                badge.className = 'tab-badge px-1.5 py-0.5 rounded-md text-[10px] font-bold shrink-0 bg-blue-50 text-[#1E3A8A] border border-blue-200';
            }
            const sub = btn.querySelector('.tab-sub');
            if (sub) {
                sub.classList.remove('text-blue-100');
                sub.classList.add('text-[#475569]');
            }
        });

        const activeBtn = document.getElementById('tabBtn-' + tabId);
        if (activeBtn) {
            activeBtn.classList.remove('bg-white', 'text-[#0F172A]', 'border-[#E2E8F0]', 'hover:bg-[#F8FAFC]');
            activeBtn.classList.add('bg-[#1E3A8A]', 'text-white', 'border-[#1E3A8A]');
            const activeBadge = activeBtn.querySelector('.tab-badge');
            if (activeBadge) {
                activeBadge.className = 'tab-badge px-1.5 py-0.5 rounded-md text-[10px] font-bold shrink-0 bg-white text-[#1E3A8A]';
            }
            const activeSub = activeBtn.querySelector('.tab-sub');
            if (activeSub) {
                activeSub.classList.remove('text-[#475569]');
                activeSub.classList.add('text-blue-100');
            }
        }

        // 4. Update parameter URL tanpa refresh browser
        const url = new URL(window.location.href);
        url.searchParams.set('tab', tabId);
        window.history.replaceState(null, '', url.toString());

        // 5. Update teks judul dan deskripsi di atas tombol tab
        const tabMeta = {
            'verify': {
                title: 'Verifikasi & Telaah Kelayakan Pengaduan Masuk',
                subtitle: 'Pemeriksaan keabsahan data dan kelayakan laporan sebelum penerbitan penugasan teknis'
            },
            'dispatched': {
                title: 'Daftar Pengaduan Telah Ditugaskan ke Petugas',
                subtitle: 'Laporan yang telah diverifikasi dan diterbitkan instruksi penugasan, menunggu dimulainya pengerjaan fisik di lapangan'
            },
            'inprogress': {
                title: 'Pemantauan Pelaksanaan Penanganan Lapangan',
                subtitle: 'Pengawasan progres fisik penanganan dan kepatuhan batas waktu Service Level Agreement (SLA)'
            },
            'validation': {
                title: 'Validasi Kelayakan Hasil Pekerjaan Teknis',
                subtitle: 'Verifikasi komparasi visual sebelum-sesudah dan pemeriksaan berita acara penyelesaian lapangan'
            },
            'archive': {
                title: 'Arsip Resmi Pengaduan & Rekam Jejak Pelayanan',
                subtitle: 'Dokumentasi permanen laporan yang telah tuntas ditangani demi akuntabilitas dan transparansi publik'
            },
            'monitoring': {
                title: 'Pemantauan Eksekutif & Evaluasi Kinerja Penanganan',
                subtitle: 'Analisis sebaran spasial infrastruktur dan evaluasi indeks respon Organisasi Perangkat Daerah (OPD)'
            }
        };

        if (tabMeta[tabId]) {
            const titleEl = document.getElementById('operatorSectionTitle');
            const subtitleEl = document.getElementById('operatorSectionSubtitle');
            if (titleEl) titleEl.innerText = tabMeta[tabId].title;
            if (subtitleEl) subtitleEl.innerText = tabMeta[tabId].subtitle;
        }

        // Simpan active tab untuk keperluan filter
        window.currentActiveTab = tabId;

        // Toggle filter bar & pagination bar: tampilkan di verify, dispatched, inprogress, validation, dan archive (sembunyikan hanya di monitoring)
        const filterBar = document.getElementById('operatorFilterBar');
        const paginationBar = document.getElementById('operatorPaginationBar');
        const isListTab = ['verify', 'dispatched', 'inprogress', 'validation', 'archive'].includes(tabId);
        if (filterBar) filterBar.classList.toggle('hidden', !isListTab);
        if (paginationBar) paginationBar.classList.toggle('hidden', !isListTab);
        if (typeof operatorPaginationState !== 'undefined') operatorPaginationState.page = 1;

        // Toggle filter status khusus tab arsip (Selesai vs Ditolak)
        const archiveStatusContainer = document.getElementById('filterArchiveStatusContainer');
        if (archiveStatusContainer) {
            if (tabId === 'archive') {
                archiveStatusContainer.classList.remove('hidden');
                archiveStatusContainer.classList.add('flex');
            } else {
                archiveStatusContainer.classList.add('hidden');
                archiveStatusContainer.classList.remove('flex');
            }
        }

        // Terapkan filter langsung ke tab yang sedang aktif
        applyFilters();

        // 6. Inisialisasi atau redraw peta spasial jika tab monitoring dibuka
        if (tabId === 'monitoring') {
            setTimeout(() => {
                if (!operatorMapInitialized) {
                    initOperatorMap();
                } else if (window.opMap) {
                    window.opMap.invalidateSize();
                }
                initExecutiveMonitoringCharts();
            }, 80);
        }

        // Refresh Lucide icons jika ada
        if (window.lucide) {
            lucide.createIcons();
        }
    }

    // Fokus Wilayah Kecamatan di Peta Operator
    function focusDistrictOnOperatorMap(lat, lon, name) {
        if (window.opMap && !isNaN(lat) && !isNaN(lon)) {
            window.opMap.flyTo([lat, lon], 13, { duration: 1.2 });
            const mapCard = document.getElementById('operatorMap');
            if (mapCard) {
                mapCard.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
        }
    }

    // Inisialisasi Peta Spasial Kabupaten Kolaka Utara (Leaflet & GeoJSON Resmi)
    function initOperatorMap() {
        const mapEl = document.getElementById('operatorMap');
        if (!mapEl || window.opMap) return;

        try {
            window.opMap = L.map('operatorMap', { scrollWheelZoom: true }).setView([-3.50601, 120.88780], 12);
            L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 19,
                attribution: '© OpenStreetMap | GIS Kolaka Utara'
            }).addTo(window.opMap);

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
                    }).addTo(window.opMap);
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

                        const marker = L.marker([lat, lng], { icon: customPinIcon }).addTo(window.opMap);

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
                            }).addTo(window.opMap);

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
                                    <button type="button" onclick="openOperatorModal(${p.id})" 
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
                    window.opMap.fitBounds(bounds, { padding: [30, 30] });
                }
            }
            operatorMapInitialized = true;
        } catch (e) {
            console.error('Error initializing Leaflet map:', e);
        }
    }

    // Auto-inisialisasi saat halaman pertama dibuka
    document.addEventListener('DOMContentLoaded', function() {
        const currentTab = '{{ $activeTab }}';
        window.currentActiveTab = currentTab;
        switchTab(currentTab);
        if (currentTab === 'monitoring') {
            setTimeout(() => {
                initOperatorMap();
                initExecutiveMonitoringCharts();
            }, 150);
        }
    });

    function openOperatorModal(id) {
        const item = operatorComplaints.find(c => c.id === id);
        if (!item) return;

        document.getElementById('opModalCode').innerText = '#' + item.public_code;

        const typeBadge = document.getElementById('opModalTypeBadge');
        if (typeBadge) {
            typeBadge.className = 'shrink-0 px-2.5 py-1 rounded-md text-xs font-semibold bg-red-50 text-red-700 border border-red-200 inline-flex items-center gap-1.5 whitespace-nowrap';
            typeBadge.innerHTML = `<span class="w-1.5 h-1.5 rounded-full bg-red-600 shrink-0"></span><span>${item.damage_type === 'major' ? 'Kerusakan Berat' : 'Kerusakan Ringan'}</span>`;
        }

        // Terjemahkan Status ke Bahasa Indonesia dan Terapkan Warna Badge yang Sesuai Landing Page
        const statusBadge = document.getElementById('opModalStatusBadge');
        let statusLabel = item.status;
        let statusClass = 'bg-slate-100 text-slate-700 border-slate-200';

        if (item.status === 'REJECTED') {
            statusLabel = 'DITOLAK';
            statusClass = 'bg-red-50 text-red-700 border-red-200';
        } else if (item.status === 'COMPLETED' || item.status === 'CLOSED') {
            statusLabel = 'SELESAI';
            statusClass = 'bg-emerald-50 text-emerald-800 border-emerald-200';
        } else if (item.status === 'WAITING_VERIFICATION') {
            statusLabel = 'VALIDASI';
            statusClass = 'bg-amber-50 text-amber-700 border-amber-300';
        } else if (item.status === 'IN_PROGRESS') {
            statusLabel = 'DIKERJAKAN';
            statusClass = 'bg-amber-50 text-amber-800 border-amber-300';
        } else if (item.status === 'DISPATCHED') {
            statusLabel = 'DITUGASKAN';
            statusClass = 'bg-blue-50 text-[#1E3A8A] border-blue-200';
        } else if (item.status === 'SUBMITTED' || item.status === 'VERIFICATION') {
            statusLabel = 'VERIFIKASI';
            statusClass = 'bg-red-50 text-red-700 border-red-200';
        } else if (item.status === 'REVISION_REQUIRED') {
            statusLabel = 'PERLU REVISI';
            statusClass = 'bg-amber-50 text-amber-800 border-amber-300';
        }

        statusBadge.innerText = statusLabel;
        statusBadge.className = `shrink-0 px-2.5 py-1 rounded-md text-xs font-bold border whitespace-nowrap ${statusClass}`;
        document.getElementById('opModalCategoryBadge').innerText = item.category_name;
        document.getElementById('opModalDate').innerText = item.created_at_full;
        document.getElementById('opModalTitle').innerText = item.title;
        document.getElementById('opModalCoords').innerText = (item.latitude && item.longitude) ? `${item.latitude}, ${item.longitude}` : 'Tidak tercatat';
        document.getElementById('opModalDescription').innerText = item.description;

        // Uraian Wilayah Administrasi 4-Kotak
        function parseAdministrativeAddress(rawAddress) {
            let road = 'Jalan / Area Kerusakan';
            let village = 'Desa Wilayah';
            let district = 'Kecamatan Terkait';
            let regency = 'Kab. Kolaka Utara';
            if (!rawAddress) return { road, village, district, regency };
            const parts = rawAddress.split(',').map(s => s.trim());
            parts.forEach(p => {
                if (p.toLowerCase().startsWith('desa') || p.toLowerCase().startsWith('kel.')) village = p;
                else if (p.toLowerCase().startsWith('kec.')) district = p;
                else if (p.toLowerCase().startsWith('kab.')) regency = p;
                else if (!p.toLowerCase().includes('sulawesi') && !p.toLowerCase().includes('indonesia')) road = p;
            });
            return { road, village, district, regency };
        }
        const adm = parseAdministrativeAddress(item.address);
        const roadEl = document.getElementById('opModalRoad');
        const villageEl = document.getElementById('opModalVillage');
        const districtEl = document.getElementById('opModalDistrict');
        const regencyEl = document.getElementById('opModalRegency');
        if (roadEl) roadEl.innerText = adm.road;
        if (villageEl) villageEl.innerText = adm.village;
        if (districtEl) districtEl.innerText = adm.district;
        if (regencyEl) regencyEl.innerText = adm.regency;

        // Alamat Lengkap & Titik GPS Bersih
        const fullAddressEl = document.getElementById('opModalFullAddress');
        if (fullAddressEl) fullAddressEl.innerText = item.address || 'Alamat lokasi tidak tersedia';

        // Identitas Pelapor & Nomor Telepon
        const citizenNameEl = document.getElementById('opModalCitizenName');
        const citizenPhoneEl = document.getElementById('opModalCitizenPhone');
        const anonBadge = document.getElementById('opModalAnonymousBadge');
        if (citizenNameEl) citizenNameEl.innerText = item.citizen_name || 'Warga Terdaftar';
        if (citizenPhoneEl) {
            citizenPhoneEl.innerText = item.citizen_phone || '-';
            if (item.citizen_phone && item.citizen_phone !== '-') {
                citizenPhoneEl.href = 'tel:' + item.citizen_phone;
            } else {
                citizenPhoneEl.removeAttribute('href');
            }
        }
        if (anonBadge) {
            if (item.is_anonymous) anonBadge.classList.remove('hidden');
            else anonBadge.classList.add('hidden');
        }

        // Regu Penanganan Lapangan (Sesuai Permintaan: Menampilkan Regu, Ketua Regu, & Nomor HP)
        const officerCard = document.getElementById('opModalOfficerCard');
        const teamNameEl = document.getElementById('opModalTeamName');
        const officerDeptEl = document.getElementById('opModalOfficerDept');
        const teamLeaderEl = document.getElementById('opModalTeamLeader');
        const teamPhoneLink = document.getElementById('opModalTeamPhone');
        const teamPhoneText = document.getElementById('opModalTeamPhoneText');

        if (officerCard) {
            const isHandled = ['IN_PROGRESS', 'DISPATCHED', 'WAITING_VERIFICATION', 'COMPLETED', 'CLOSED'].includes(item.status);
            const hasDisposition = item.team_name || item.officer_name || item.department_name || item.instruction;
            if (isHandled && hasDisposition) {
                officerCard.classList.remove('hidden');

                // Tampilkan Nama Regu & Dinas
                const teamDisplayName = item.team_name 
                    ? (item.team_code ? `[${item.team_code}] ${item.team_name}` : item.team_name)
                    : (item.officer_name ? `Regu Petugas (${item.officer_name})` : 'Klaim Mandiri (Terbuka)');

                if (teamNameEl) teamNameEl.innerText = teamDisplayName;
                if (officerDeptEl) officerDeptEl.innerText = item.department_name || 'Dinas Terkait (PUPR / PRKP)';

                // Ketua Regu & Nomor Telepon (hanya tampil setelah diambil/dikerjakan)
                const showOfficerIdentity = ['IN_PROGRESS', 'WAITING_VERIFICATION', 'COMPLETED', 'CLOSED'].includes(item.status);
                const leaderName = item.team_leader_name || item.officer_name || 'Petugas Lapangan';
                const leaderPhone = item.team_leader_phone || item.officer_phone || '-';

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
                const officerNameEl = document.getElementById('opModalOfficerName');
                if (officerNameEl) officerNameEl.innerText = leaderName;
                const officerPhoneEl = document.getElementById('opModalOfficerPhone');
                if (officerPhoneEl) officerPhoneEl.innerText = leaderPhone;

                const deadlineBox = document.getElementById('opModalDeadlineBox');
                if (deadlineBox) {
                    if (item.deadline) {
                        deadlineBox.classList.remove('hidden');
                        document.getElementById('opModalOfficerDeadline').innerText = item.deadline;
                    } else {
                        deadlineBox.classList.add('hidden');
                    }
                }
                const instructionRow = document.getElementById('opModalInstructionRow');
                if (instructionRow) {
                    if (item.instruction) {
                        instructionRow.classList.remove('hidden');
                        document.getElementById('opModalOfficerInstruction').innerText = item.instruction;
                    } else {
                        instructionRow.classList.add('hidden');
                    }
                }
            } else {
                officerCard.classList.add('hidden');
            }
        }

        // 1. Foto Bukti Kerusakan Warga (Bagian Atas)
        const citizenPhotoSection = document.getElementById('opModalCitizenPhotoSection');
        const citizenPhotoImg = document.getElementById('opModalCitizenPhoto');
        const citizenGallery = document.getElementById('opModalCitizenGallery');
        const citizenLabel = document.getElementById('opModalCitizenPhotoLabel');

        const citizenPhotos = item.citizen_photos && item.citizen_photos.length > 0 
            ? item.citizen_photos 
            : (item.photo_url ? [item.photo_url] : []);

        if (citizenPhotos.length > 0) {
            citizenPhotoImg.src = citizenPhotos[0];
            if (citizenLabel) citizenLabel.innerText = `Foto Bukti 1 dari ${citizenPhotos.length}`;
            if (citizenGallery) {
                citizenGallery.innerHTML = '';
                if (citizenPhotos.length > 1) {
                    citizenGallery.classList.remove('hidden');
                    citizenPhotos.forEach((url, idx) => {
                        const thumb = document.createElement('img');
                        thumb.src = url;
                        thumb.className = `w-14 h-14 rounded-xl object-cover cursor-pointer border-2 transition ${idx === 0 ? 'border-[#1E3A8A] ring-2 ring-blue-200' : 'border-slate-200 opacity-70 hover:opacity-100'}`;
                        thumb.onclick = () => {
                            citizenPhotoImg.src = url;
                            if (citizenLabel) citizenLabel.innerText = `Foto Bukti ${idx + 1} dari ${citizenPhotos.length}`;
                            citizenGallery.querySelectorAll('img').forEach(img => {
                                img.className = 'w-14 h-14 rounded-xl object-cover cursor-pointer border-2 border-slate-200 opacity-70 hover:opacity-100 transition';
                            });
                            thumb.className = 'w-14 h-14 rounded-xl object-cover cursor-pointer border-2 border-[#1E3A8A] ring-2 ring-blue-200 transition';
                        };
                        citizenGallery.appendChild(thumb);
                    });
                } else {
                    citizenGallery.classList.add('hidden');
                }
            }
            citizenPhotoSection.classList.remove('hidden');
        } else {
            citizenPhotoSection.classList.add('hidden');
        }

        // 2. Foto Bukti Penanganan Petugas Lapangan (Bagian Bawah)
        const officerPhotoSection = document.getElementById('opModalOfficerPhotoSection');
        const officerPhotoImg = document.getElementById('opModalOfficerPhoto');
        const officerGallery = document.getElementById('opModalOfficerGallery');
        const officerLabel = document.getElementById('opModalOfficerPhotoLabel');

        const officerPhotos = item.officer_photos && item.officer_photos.length > 0 
            ? item.officer_photos 
            : (item.completion_photo_url ? [item.completion_photo_url] : []);

        if (officerPhotos.length > 0) {
            officerPhotoImg.src = officerPhotos[0];
            if (officerLabel) officerLabel.innerText = `Bukti Hasil Petugas 1 dari ${officerPhotos.length}`;
            if (officerGallery) {
                officerGallery.innerHTML = '';
                if (officerPhotos.length > 1) {
                    officerGallery.classList.remove('hidden');
                    officerPhotos.forEach((url, idx) => {
                        const thumb = document.createElement('img');
                        thumb.src = url;
                        thumb.className = `w-14 h-14 rounded-xl object-cover cursor-pointer border-2 transition ${idx === 0 ? 'border-emerald-600 ring-2 ring-emerald-200' : 'border-slate-200 opacity-70 hover:opacity-100'}`;
                        thumb.onclick = () => {
                            officerPhotoImg.src = url;
                            if (officerLabel) officerLabel.innerText = `Bukti Hasil Petugas ${idx + 1} dari ${officerPhotos.length}`;
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
            officerPhotoSection.classList.remove('hidden');
        } else {
            officerPhotoSection.classList.add('hidden');
        }

        // Ruas Panjang Box
        const majorBox = document.getElementById('opModalMajorRoadBox');
        if (majorBox) {
            if (item.damage_type === 'major' || item.road_length) {
                majorBox.classList.remove('hidden');
                // Sembunyikan badge panjang di pojok kanan judul
                const rlBadge = document.getElementById('opModalRoadLengthBadge');
                if (rlBadge) rlBadge.classList.add('hidden');
                // Bersihkan road_length dari satuan duplikat lalu tampilkan
                const rlClean = (item.road_length || '').toString().replace(/\s*(meter|m)\s*$/i, '').trim();
                const rlEl = document.getElementById('opModalRoadLength');
                if (rlEl) {
                    rlEl.innerText = rlClean ? rlClean + ' Meter' : '\u2265 500 Meter';
                    const rlRow = rlEl.closest('div.p-2');
                    if (rlRow) rlRow.classList.remove('hidden');
                }
                document.getElementById('opModalRoadCondition').innerText = item.road_condition || 'Jalan Tanah / Rusak';
                document.getElementById('opModalStartCoords').innerText = `${item.latitude}, ${item.longitude}`;
                document.getElementById('opModalEndCoords').innerText = (item.end_latitude && item.end_longitude) ? `${item.end_latitude}, ${item.end_longitude}` : 'Tidak ditentukan';
            } else {
                majorBox.classList.add('hidden');
            }
        }

        // AI Info
        const aiBox = document.getElementById('opModalAiBox');
        if (item.ai_damage_type || item.ai_category || item.ai_repair_estimate) {
            aiBox.classList.remove('hidden');
            document.getElementById('opModalAiType').innerText = item.ai_damage_type || item.ai_category || '-';
            document.getElementById('opModalAiPriority').innerText = (item.severity_text || ('Tingkat ' + (item.severity_level || 3) + '/5')) + (item.priority_score ? ` (Skor: ${item.priority_score})` : '');
            document.getElementById('opModalAiEstimate').innerText = item.ai_repair_estimate || 'Penanganan standar teknis';
            document.getElementById('opModalAiConfidence').innerText = `Akurasi ${item.ai_confidence}%`;
        } else {
            aiBox.classList.add('hidden');
        }

        window.currentDetailComplaintId = id;

        const modal = document.getElementById('operatorDetailModal');
        const scrollBox = modal.querySelector('.overflow-y-auto');
        if (scrollBox) scrollBox.scrollTop = 0;
        modal.classList.remove('hidden');
        document.body.style.overflow = 'hidden';

        if (window.lucide) {
            lucide.createIcons({ root: modal });
        }
    }

    function closeOperatorModal() {
        const modal = document.getElementById('operatorDetailModal');
        modal.classList.add('hidden');
        document.body.style.overflow = '';
    }

    // ==========================================
    // LOGIKA MODAL VALIDASI HASIL PENGERJAAN
    // ==========================================
    let currentValidationComplaintId = null;

    function openValidationModal(id) {
        const item = operatorComplaints.find(c => c.id === id);
        if (!item) return;

        currentValidationComplaintId = id;

        const form = document.getElementById('validationActionForm');
        if (form) {
            form.action = `/operator/complaints/${id}/validate`;
        }

        const codeEl = document.getElementById('valModalCode');
        if (codeEl) codeEl.innerText = '#' + item.public_code;

        const typeBadge = document.getElementById('valModalTypeBadge');
        if (typeBadge) {
            typeBadge.className = 'px-2.5 py-0.5 rounded-full text-xs font-bold bg-red-50 text-red-700 border border-red-200 inline-flex items-center gap-1.5';
            typeBadge.innerHTML = `<span class="w-1.5 h-1.5 rounded-full bg-red-500"></span><span>${item.damage_type === 'major' ? 'Kerusakan Berat' : 'Kerusakan Ringan'}</span>`;
        }

        const catBadge = document.getElementById('valModalCategoryBadge');
        if (catBadge) catBadge.innerText = item.category_name || 'Infrastruktur';

        // 1. Foto Bukti Kerusakan Warga (Atas)
        const citizenPhotoSection = document.getElementById('valModalCitizenPhotoSection');
        const citizenPhotoImg = document.getElementById('valModalCitizenPhoto');
        const citizenGallery = document.getElementById('valModalCitizenGallery');
        const citizenLabel = document.getElementById('valModalCitizenPhotoLabel');

        const citizenPhotos = item.citizen_photos && item.citizen_photos.length > 0 
            ? item.citizen_photos 
            : (item.photo_url ? [item.photo_url] : []);

        if (citizenPhotos.length > 0) {
            citizenPhotoImg.src = citizenPhotos[0];
            if (citizenLabel) citizenLabel.innerText = `Foto Bukti 1 dari ${citizenPhotos.length}`;
            if (citizenGallery) {
                citizenGallery.innerHTML = '';
                if (citizenPhotos.length > 1) {
                    citizenGallery.classList.remove('hidden');
                    citizenPhotos.forEach((url, idx) => {
                        const thumb = document.createElement('img');
                        thumb.src = url;
                        thumb.className = `w-14 h-14 rounded-xl object-cover cursor-pointer border-2 transition ${idx === 0 ? 'border-[#1E3A8A] ring-2 ring-blue-200' : 'border-slate-200 opacity-70 hover:opacity-100'}`;
                        thumb.onclick = () => {
                            citizenPhotoImg.src = url;
                            if (citizenLabel) citizenLabel.innerText = `Foto Bukti ${idx + 1} dari ${citizenPhotos.length}`;
                            citizenGallery.querySelectorAll('img').forEach(img => {
                                img.className = 'w-14 h-14 rounded-xl object-cover cursor-pointer border-2 border-slate-200 opacity-70 hover:opacity-100 transition';
                            });
                            thumb.className = 'w-14 h-14 rounded-xl object-cover cursor-pointer border-2 border-[#1E3A8A] ring-2 ring-blue-200 transition';
                        };
                        citizenGallery.appendChild(thumb);
                    });
                } else {
                    citizenGallery.classList.add('hidden');
                }
            }
            citizenPhotoSection.classList.remove('hidden');
        } else {
            citizenPhotoSection.classList.add('hidden');
        }

        // 2. Foto Bukti Penanganan Petugas Lapangan (Bawah)
        const officerPhotoSection = document.getElementById('valModalOfficerPhotoSection');
        const officerPhotoImg = document.getElementById('valModalOfficerPhoto');
        const officerGallery = document.getElementById('valModalOfficerGallery');
        const officerLabel = document.getElementById('valModalOfficerPhotoLabel');

        const officerPhotos = item.officer_photos && item.officer_photos.length > 0 
            ? item.officer_photos 
            : (item.completion_photo_url ? [item.completion_photo_url] : []);

        if (officerPhotos.length > 0) {
            officerPhotoImg.src = officerPhotos[0];
            if (officerLabel) officerLabel.innerText = `Bukti Hasil Petugas 1 dari ${officerPhotos.length}`;
            if (officerGallery) {
                officerGallery.innerHTML = '';
                if (officerPhotos.length > 1) {
                    officerGallery.classList.remove('hidden');
                    officerPhotos.forEach((url, idx) => {
                        const thumb = document.createElement('img');
                        thumb.src = url;
                        thumb.className = `w-14 h-14 rounded-xl object-cover cursor-pointer border-2 transition ${idx === 0 ? 'border-emerald-600 ring-2 ring-emerald-200' : 'border-slate-200 opacity-70 hover:opacity-100'}`;
                        thumb.onclick = () => {
                            officerPhotoImg.src = url;
                            if (officerLabel) officerLabel.innerText = `Bukti Hasil Petugas ${idx + 1} dari ${officerPhotos.length}`;
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
            officerPhotoSection.classList.remove('hidden');
        } else {
            officerPhotoSection.classList.add('hidden');
        }

        // Judul & Tanggal
        document.getElementById('valModalTitle').innerText = item.title;
        document.getElementById('valModalDate').innerText = item.created_at_full;

        // Identitas Pelapor
        document.getElementById('valModalCitizenName').innerText = item.citizen_name || 'Warga Terdaftar';
        document.getElementById('valModalCitizenPhone').innerText = item.citizen_phone || '-';
        const anonBadge = document.getElementById('valModalAnonymousBadge');
        if (anonBadge) {
            if (item.is_anonymous) anonBadge.classList.remove('hidden');
            else anonBadge.classList.add('hidden');
        }

        // Regu Penanganan Lapangan (Sesuai Permintaan: Menampilkan Regu, Ketua Regu, & Nomor HP)
        const valTeamNameEl = document.getElementById('valModalTeamName');
        const valOfficerDeptEl = document.getElementById('valModalOfficerDept');
        const valTeamLeaderEl = document.getElementById('valModalTeamLeader');
        const valTeamPhoneLink = document.getElementById('valModalTeamPhone');
        const valTeamPhoneText = document.getElementById('valModalTeamPhoneText');

        const teamDisplayName = item.team_name 
            ? (item.team_code ? `[${item.team_code}] ${item.team_name}` : item.team_name)
            : (item.officer_name ? `Regu Petugas (${item.officer_name})` : 'Regu Lapangan Terpadu');

        if (valTeamNameEl) valTeamNameEl.innerText = teamDisplayName;
        if (valOfficerDeptEl) valOfficerDeptEl.innerText = item.department_name || 'Dinas Terkait (PUPR / PRKP)';

        const leaderName = item.team_leader_name || item.officer_name || 'Petugas Lapangan';
        const leaderPhone = item.team_leader_phone || item.officer_phone || '-';

        if (valTeamLeaderEl) valTeamLeaderEl.innerText = leaderName;
        if (valTeamPhoneLink && valTeamPhoneText) {
            valTeamPhoneText.innerText = (leaderPhone && leaderPhone !== '-') ? leaderPhone : '-';
            if (leaderPhone && leaderPhone !== '-') {
                valTeamPhoneLink.href = 'tel:' + leaderPhone;
                valTeamPhoneLink.classList.remove('hidden');
            } else {
                valTeamPhoneLink.removeAttribute('href');
            }
        }

        // Legacy fallback elements
        const valOfficerNameEl = document.getElementById('valModalOfficerName');
        if (valOfficerNameEl) valOfficerNameEl.innerText = leaderName;
        const valOfficerPhoneEl = document.getElementById('valModalOfficerPhone');
        if (valOfficerPhoneEl) valOfficerPhoneEl.innerText = leaderPhone;

        if (item.deadline) {
            document.getElementById('valModalDeadlineBox').classList.remove('hidden');
            document.getElementById('valModalOfficerDeadline').innerText = item.deadline;
        } else {
            document.getElementById('valModalDeadlineBox').classList.add('hidden');
        }
        if (item.instruction) {
            document.getElementById('valModalInstructionRow').classList.remove('hidden');
            document.getElementById('valModalOfficerInstruction').innerText = item.instruction;
        } else {
            document.getElementById('valModalInstructionRow').classList.add('hidden');
        }

        // Alamat & Koordinat
        function parseAdminAddress(rawAddress) {
            let road = 'Jalan / Area Kerusakan';
            let village = 'Desa Wilayah';
            let district = 'Kecamatan Terkait';
            let regency = 'Kab. Kolaka Utara';
            if (!rawAddress) return { road, village, district, regency };
            const parts = rawAddress.split(',').map(s => s.trim());
            parts.forEach(p => {
                if (p.toLowerCase().startsWith('desa') || p.toLowerCase().startsWith('kel.')) village = p;
                else if (p.toLowerCase().startsWith('kec.')) district = p;
                else if (p.toLowerCase().startsWith('kab.')) regency = p;
                else if (!p.toLowerCase().includes('sulawesi') && !p.toLowerCase().includes('indonesia')) road = p;
            });
            return { road, village, district, regency };
        }
        const adm = parseAdminAddress(item.address);
        const valRoadEl = document.getElementById('valModalRoad');
        const valVillageEl = document.getElementById('valModalVillage');
        const valDistrictEl = document.getElementById('valModalDistrict');
        const valRegencyEl = document.getElementById('valModalRegency');
        if (valRoadEl) valRoadEl.innerText = adm.road;
        if (valVillageEl) valVillageEl.innerText = adm.village;
        if (valDistrictEl) valDistrictEl.innerText = adm.district;
        if (valRegencyEl) valRegencyEl.innerText = adm.regency;

        // Alamat Lengkap & Koordinat Bersih
        const valFullAddressEl = document.getElementById('valModalFullAddress');
        if (valFullAddressEl) valFullAddressEl.innerText = item.address || 'Alamat lokasi tidak tersedia';
        const valCoordsEl = document.getElementById('valModalCoords');
        if (valCoordsEl) valCoordsEl.innerText = (item.latitude && item.longitude) ? `${item.latitude}, ${item.longitude}` : '-';

        // Major Road
        const majorBox = document.getElementById('valModalMajorRoadBox');
        if (majorBox) {
            if (item.damage_type === 'major' || item.road_length) {
                majorBox.classList.remove('hidden');
                const rlClean = (item.road_length || '').toString().replace(/\s*(meter|m)\s*$/i, '').trim();
                document.getElementById('valModalRoadLength').innerText = rlClean ? rlClean + ' Meter' : '\u2265 500 Meter';
                document.getElementById('valModalRoadCondition').innerText = item.road_condition || 'Jalan Tanah / Rusak';
                document.getElementById('valModalStartCoords').innerText = `${item.latitude}, ${item.longitude}`;
                document.getElementById('valModalEndCoords').innerText = (item.end_latitude && item.end_longitude) ? `${item.end_latitude}, ${item.end_longitude}` : 'Tidak ditentukan';
            } else {
                majorBox.classList.add('hidden');
            }
        }

        // Deskripsi
        document.getElementById('valModalDescription').innerText = item.description;

        // AI Box
        const aiBox = document.getElementById('valModalAiBox');
        if (item.ai_damage_type || item.ai_category || item.ai_repair_estimate) {
            aiBox.classList.remove('hidden');
            document.getElementById('valModalAiType').innerText = item.ai_damage_type || item.ai_category || '-';
            document.getElementById('valModalAiPriority').innerText = (item.severity_text || ('Tingkat ' + (item.severity_level || 3) + '/5')) + (item.priority_score ? ` (Skor: ${item.priority_score})` : '');
            document.getElementById('valModalAiEstimate').innerText = item.ai_repair_estimate || 'Penanganan standar teknis';
            document.getElementById('valModalAiConfidence').innerText = `Akurasi ${item.ai_confidence}%`;
        } else {
            aiBox.classList.add('hidden');
        }

        const modal = document.getElementById('operatorValidationModal');
        if (modal) {
            const scrollBox = modal.querySelector('.overflow-y-auto');
            if (scrollBox) scrollBox.scrollTop = 0;
            modal.classList.remove('hidden');
            document.body.style.overflow = 'hidden';
            if (window.lucide) lucide.createIcons({ root: modal });
        }
    }

    function closeValidationModal() {
        const modal = document.getElementById('operatorValidationModal');
        if (modal) modal.classList.add('hidden');
        document.body.style.overflow = '';
        currentValidationComplaintId = null;
    }

    function approveFromValidationModal() {
        if (!currentValidationComplaintId) return;
        const form = document.getElementById('validationActionForm');
        const decisionInput = document.getElementById('validationDecisionInput');
        if (form && decisionInput) {
            form.action = `/operator/complaints/${currentValidationComplaintId}/validate`;
            decisionInput.value = 'COMPLETED';
            form.submit();
        }
    }

    function openRevisionFromValidationModal() {
        if (!currentValidationComplaintId) return;
        const item = operatorComplaints.find(c => c.id === currentValidationComplaintId);
        if (!item) return;

        closeValidationModal();

        // Siapkan modal revisi dengan target form validationActionForm
        const form = document.getElementById('validationActionForm');
        form.action = `/operator/complaints/${currentValidationComplaintId}/validate`;
        const decisionInput = document.getElementById('validationDecisionInput');
        decisionInput.value = 'REVISION_REQUIRED';

        openRevisionModal(form, item.public_code);
    }

    // ==========================================
    // LOGIKA MODAL DISPOSISI & PENUGASAN PETUGAS
    // ==========================================
    let currentDispatchComplaintId = null;

    function openDispatchModal(id) {
        const item = operatorComplaints.find(c => c.id === id);
        if (!item) return;

        currentDispatchComplaintId = id;
        
        // 1. Set action form URL
        const form = document.getElementById('dispatchForm');
        if (form) {
            form.action = `/operator/complaints/${id}/verify-dispatch`;
        }

        // 2. Set Kode & Badges
        const codeEl = document.getElementById('dispatchModalCode');
        if (codeEl) codeEl.innerText = '#' + item.public_code;

        const typeBadge = document.getElementById('dispatchModalTypeBadge');
        if (typeBadge) {
            typeBadge.className = 'shrink-0 px-2.5 py-1 rounded-md text-xs font-semibold bg-red-50 text-red-700 border border-red-200 inline-flex items-center gap-1.5 whitespace-nowrap';
            typeBadge.innerHTML = `<span class="w-1.5 h-1.5 rounded-full bg-red-600 shrink-0"></span><span>${item.damage_type === 'major' ? 'Kerusakan Berat' : 'Kerusakan Ringan'}</span>`;
        }

        const catBadge = document.getElementById('dispatchModalCategoryBadge');
        if (catBadge) catBadge.innerText = item.category_name || 'Infrastruktur';

        // 3. Set Info Ringkasan Aduan
        const titleEl = document.getElementById('dispatchModalTitle');
        if (titleEl) titleEl.innerText = item.title;

        const addrEl = document.getElementById('dispatchModalAddress');
        if (addrEl) addrEl.innerText = item.address;

        const citizenEl = document.getElementById('dispatchModalCitizen');
        if (citizenEl) {
            const phone = item.citizen_phone && item.citizen_phone !== '-' ? ` • ${item.citizen_phone}` : '';
            citizenEl.innerText = (item.citizen_name || 'Warga Terdaftar') + phone;
        }

        // 4. Foto Ringkasan
        const photoEl = document.getElementById('dispatchModalPhoto');
        const noPhotoEl = document.getElementById('dispatchModalNoPhoto');
        if (item.photo_url) {
            if (photoEl) {
                photoEl.src = item.photo_url;
                photoEl.classList.remove('hidden');
            }
            if (noPhotoEl) noPhotoEl.classList.add('hidden');
        } else {
            if (photoEl) photoEl.classList.add('hidden');
            if (noPhotoEl) noPhotoEl.classList.remove('hidden');
        }

        // 5. Reset Radio Batas Waktu & Preview Tanggal Target
        const defaultRadio = document.querySelector('input[name="deadline_days"][value="3"]');
        if (defaultRadio) {
            defaultRadio.checked = true;
        }
        updateDispatchSlaPreview(3, '3 Hari (SOP Standar)');

        // 6. Reset Template Instruksi
        const instructionInput = document.getElementById('dispatchInstructionInput');
        const templateSelect = document.getElementById('dispatchTemplateSelect');
        if (instructionInput) {
            instructionInput.value = 'Lakukan perbaikan dan penanganan fisik sesuai SOP dinas teknis';
        }
        if (templateSelect) {
            templateSelect.selectedIndex = 0;
        }


        // 8. Buka Modal
        const modal = document.getElementById('dispatchModal');
        if (modal) {
            modal.classList.remove('hidden');
            document.body.style.overflow = 'hidden';
            if (window.lucide) {
                lucide.createIcons({ root: modal });
            }
        }
    }

    function closeDispatchModal() {
        const modal = document.getElementById('dispatchModal');
        if (modal) modal.classList.add('hidden');
        document.body.style.overflow = '';
    }


    // Kalkulasi Live Tanggal Target Selesai (SLA)
    function updateDispatchSlaPreview(days, label) {
        const previewEl = document.getElementById('dispatchDeadlinePreview');
        if (!previewEl) return;
        
        const targetDate = new Date();
        targetDate.setDate(targetDate.getDate() + parseInt(days));
        
        const options = { weekday: 'short', day: 'numeric', month: 'short', year: 'numeric' };
        const dateStr = targetDate.toLocaleDateString('id-ID', options);
        
        previewEl.innerText = `Target Selesai: ${dateStr} (${label})`;
    }

    // Terapkan Template Cepat ke Textarea Instruksi
    function applyDispatchTemplate(templateText) {
        const instructionInput = document.getElementById('dispatchInstructionInput');
        if (instructionInput && templateText) {
            instructionInput.value = templateText;
            instructionInput.focus();
        }
    }

    // Navigasi Antar-Modal
    function openOperatorModalFromDispatch() {
        if (currentDispatchComplaintId) {
            closeDispatchModal();
            openOperatorModal(currentDispatchComplaintId);
        }
    }

    function openDispatchModalFromDetail() {
        if (window.currentDetailComplaintId) {
            closeOperatorModal();
            openDispatchModal(window.currentDetailComplaintId);
        }
    }

    function rejectFromDetailModal() {
        if (window.currentDetailComplaintId) {
            const item = operatorComplaints.find(c => c.id === window.currentDetailComplaintId);
            if (item) {
                closeOperatorModal();
                confirmRejectComplaint(item.id, item.public_code);
            }
        }
    }

    function rejectFromDispatchModal() {
        if (currentDispatchComplaintId) {
            const item = operatorComplaints.find(c => c.id === currentDispatchComplaintId);
            if (item) {
                closeDispatchModal();
                confirmRejectComplaint(item.id, item.public_code);
            }
        }
    }

    // ==========================================
    // LOGIKA MODAL TEMPATKAN / UBAH PENUGASAN REGU (TAB DITUGASKAN)
    // ==========================================
    function openAssignTeamModal(complaintId, publicCode, title, currentTeamId, currentInstruction) {
        const modal = document.getElementById('operatorAssignTeamModal');
        const form = document.getElementById('assignTeamForm');
        const codeEl = document.getElementById('assignTeamCode');
        const titleEl = document.getElementById('assignTeamTitle');
        const selectEl = document.getElementById('assignTeamSelect');
        const instrEl = document.getElementById('assignTeamInstruction');

        if (form) form.action = `/operator/complaints/${complaintId}/assign-team`;
        if (codeEl) codeEl.innerText = '#' + publicCode;
        if (titleEl) titleEl.innerText = title || '-';
        if (selectEl) selectEl.value = currentTeamId || '';
        if (instrEl) instrEl.value = currentInstruction || 'Lakukan perbaikan fisik infrastruktur di lokasi sesuai SOP teknis.';

        if (modal) {
            modal.classList.remove('hidden');
            modal.classList.add('flex');
            document.body.style.overflow = 'hidden';
            if (window.lucide) lucide.createIcons({ root: modal });
        }
    }

    function closeAssignTeamModal() {
        const modal = document.getElementById('operatorAssignTeamModal');
        if (modal) {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
            document.body.style.overflow = '';
        }
    }

    // Tutup modal jika klik di luar area dialog atau tombol Escape
    window.addEventListener('click', function(e) {
        const modal = document.getElementById('operatorDetailModal');
        if (e.target === modal) {
            closeOperatorModal();
        }
        const valModal = document.getElementById('operatorValidationModal');
        if (e.target === valModal) {
            closeValidationModal();
        }
        const dispatchModal = document.getElementById('dispatchModal');
        if (e.target === dispatchModal) {
            closeDispatchModal();
        }
        const assignModal = document.getElementById('operatorAssignTeamModal');
        if (e.target === assignModal) {
            closeAssignTeamModal();
        }
        const rejectModal = document.getElementById('rejectConfirmModal');
        if (e.target === rejectModal) {
            closeRejectModal();
        }
        const revisionModal = document.getElementById('revisionConfirmModal');
        if (e.target === revisionModal) {
            closeRevisionModal();
        }
    });

    window.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeOperatorModal();
            closeValidationModal();
            closeDispatchModal();
            closeAssignTeamModal();
            closeRejectModal();
            closeRevisionModal();
        }
    });

    // Filter Kategori & Pencarian Real-time untuk Semua Tab (Verifikasi, Dikerjakan, Validasi, Arsip)
    function setOperatorLayout(mode) {
        const tabs = ['verify', 'dispatched', 'inprogress', 'validation', 'archive'];
        tabs.forEach(tab => {
            const tableWrap = document.getElementById(`operatorTableWrap-${tab}`);
            const cardsEl = document.getElementById(`${tab}CardsContainer`);
            if (tableWrap) tableWrap.classList.toggle('hidden', mode !== 'list');
            if (cardsEl) cardsEl.classList.toggle('hidden', mode !== 'grid');
        });
        const listBtn = document.getElementById('opLayoutBtn-list');
        const gridBtn = document.getElementById('opLayoutBtn-grid');
        if (listBtn && gridBtn) {
            listBtn.className = `px-2.5 py-1.5 rounded-md text-xs font-semibold flex items-center gap-1.5 transition cursor-pointer ${mode === 'list' ? 'bg-[#1E3A8A] text-white' : 'text-[#475569] hover:text-[#0F172A]'}`;
            gridBtn.className = `px-2.5 py-1.5 rounded-md text-xs font-semibold flex items-center gap-1.5 transition cursor-pointer ${mode === 'grid' ? 'bg-[#1E3A8A] text-white' : 'text-[#475569] hover:text-[#0F172A]'}`;
        }
    }

    const operatorPaginationState = { page: 1, size: 10 };

    function changeOperatorPageSize(size) {
        operatorPaginationState.size = parseInt(size, 10) || 10;
        operatorPaginationState.page = 1;
        applyFilters(false);
    }

    function goOperatorPage(page) {
        operatorPaginationState.page = page;
        applyFilters(false);
    }

    function renderOperatorPagination(matchedCards, matchedRows = []) {
        const total = Math.max(matchedCards.length, matchedRows.length);
        const size = operatorPaginationState.size;
        const totalPages = Math.max(1, Math.ceil(total / size));
        if (operatorPaginationState.page > totalPages) operatorPaginationState.page = totalPages;
        if (operatorPaginationState.page < 1) operatorPaginationState.page = 1;

        const startIdx = (operatorPaginationState.page - 1) * size;
        const endIdx = Math.min(startIdx + size, total);

        matchedCards.forEach((card, idx) => {
            card.style.display = (idx >= startIdx && idx < endIdx) ? '' : 'none';
        });
        matchedRows.forEach((row, idx) => {
            row.style.display = (idx >= startIdx && idx < endIdx) ? '' : 'none';
        });

        const infoEl = document.getElementById('operatorPageInfo');
        if (infoEl) {
            infoEl.innerHTML = total === 0
                ? '<span class="hidden sm:inline">Menampilkan </span>0 dari 0 laporan'
                : `<span class="hidden sm:inline">Menampilkan </span>${startIdx + 1}–${endIdx} dari ${total} laporan`;
        }

        const controlsEl = document.getElementById('operatorPageControls');
        if (!controlsEl) return;
        if (total === 0) {
            controlsEl.innerHTML = '';
            return;
        }

        const btnBase = 'px-2.5 py-1.5 rounded-lg border text-xs font-semibold transition cursor-pointer';
        const btnIdle = `${btnBase} bg-white border-[#E2E8F0] text-[#0F172A] hover:bg-slate-100`;
        const btnActive = `${btnBase} bg-[#1E3A8A] border-[#1E3A8A] text-white`;
        const btnDisabled = 'px-2.5 py-1.5 rounded-lg border border-[#E2E8F0] bg-slate-100 text-slate-400 text-xs font-semibold cursor-not-allowed';

        let html = operatorPaginationState.page > 1
            ? `<button type="button" class="${btnIdle}" onclick="goOperatorPage(${operatorPaginationState.page - 1})">Sebelumnya</button>`
            : `<button type="button" class="${btnDisabled}" disabled>Sebelumnya</button>`;

        for (let p = 1; p <= totalPages; p++) {
            if (totalPages > 7 && p > 2 && p < totalPages - 1 && Math.abs(p - operatorPaginationState.page) > 1) {
                if (p === 3 || p === totalPages - 2) html += `<span class="px-1.5 text-[#475569]">…</span>`;
                continue;
            }
            html += `<button type="button" class="${p === operatorPaginationState.page ? btnActive : btnIdle}" onclick="goOperatorPage(${p})">${p}</button>`;
        }

        html += operatorPaginationState.page < totalPages
            ? `<button type="button" class="${btnIdle}" onclick="goOperatorPage(${operatorPaginationState.page + 1})">Berikutnya</button>`
            : `<button type="button" class="${btnDisabled}" disabled>Berikutnya</button>`;

        controlsEl.innerHTML = html;
    }

    function applyFilters(resetPage = true) {
        if (resetPage) operatorPaginationState.page = 1;
        const searchInput = document.getElementById('filterSearchInput');
        const categorySelect = document.getElementById('filterCategorySelect');
        const damageTypeSelect = document.getElementById('filterDamageTypeSelect');
        if (!searchInput || !categorySelect) return;

        const query = searchInput.value.toLowerCase().trim();
        const catId = categorySelect.value;
        const damageTypeVal = damageTypeSelect ? damageTypeSelect.value : 'ALL';
        const currentTab = window.currentActiveTab || '{{ $activeTab }}';

        const archiveStatusSelect = document.getElementById('filterArchiveStatusSelect');
        const archiveStatusVal = archiveStatusSelect ? archiveStatusSelect.value : 'ALL';

        const resetBtn = document.getElementById('operatorResetBtn');
        if (resetBtn) {
            const hasActiveFilter = (query !== '' || catId !== 'ALL' || damageTypeVal !== 'ALL' || (currentTab === 'archive' && archiveStatusVal !== 'ALL'));
            resetBtn.classList.toggle('hidden', !hasActiveFilter);
        }

        let selector = '.complaint-verify-card';
        let rowSelector = '.operator-verify-row';
        if (currentTab === 'dispatched') {
            selector = '.complaint-dispatched-card';
            rowSelector = '.operator-dispatched-row';
        } else if (currentTab === 'inprogress') {
            selector = '.complaint-inprogress-card';
            rowSelector = '.operator-inprogress-row';
        } else if (currentTab === 'validation') {
            selector = '.complaint-validation-card';
            rowSelector = '.operator-validation-row';
        } else if (currentTab === 'archive') {
            selector = '.complaint-archive-card';
            rowSelector = '.operator-archive-row';
        }

        const cards = document.querySelectorAll(selector);
        const rows = document.querySelectorAll(rowSelector);
        const matchedCards = [];
        const matchedRows = [];

        const matchesItem = (el) => {
            const itemCat = el.getAttribute('data-category');
            const itemDamageType = el.getAttribute('data-damage-type') || 'minor';
            const itemSearch = el.getAttribute('data-search') || '';
            const itemStatus = el.getAttribute('data-status') || '';

            const matchCat = (catId === 'ALL' || itemCat === catId);
            const matchDamageType = (damageTypeVal === 'ALL' || itemDamageType === damageTypeVal);
            const matchQuery = (!query || itemSearch.includes(query));

            let matchStatus = true;
            if (currentTab === 'archive' && archiveStatusVal !== 'ALL') {
                if (archiveStatusVal === 'COMPLETED') {
                    matchStatus = (itemStatus === 'COMPLETED' || itemStatus === 'CLOSED');
                } else if (archiveStatusVal === 'REJECTED') {
                    matchStatus = (itemStatus === 'REJECTED');
                }
            }
            return matchCat && matchDamageType && matchQuery && matchStatus;
        };

        cards.forEach(card => {
            if (matchesItem(card)) {
                card.classList.remove('hidden');
                matchedCards.push(card);
            } else {
                card.classList.add('hidden');
                card.style.display = 'none';
            }
        });

        rows.forEach(row => {
            if (matchesItem(row)) {
                row.classList.remove('hidden');
                matchedRows.push(row);
            } else {
                row.classList.add('hidden');
                row.style.display = 'none';
            }
        });

        const visibleCount = Math.max(matchedCards.length, matchedRows.length);
        const countBadge = document.getElementById('filterCountBadge');
        if (countBadge) {
            countBadge.innerText = `${visibleCount} Laporan`;
        }

        // Update notifikasi filter kosong sesuai tab aktif
        const allTabs = ['verify', 'dispatched', 'inprogress', 'validation', 'archive'];
        allTabs.forEach(tab => {
            const noticeId = tab === 'verify' ? 'emptyFilterNotice' : `emptyFilterNotice-${tab}`;
            const el = document.getElementById(noticeId);
            if (el) {
                if (tab === currentTab && visibleCount === 0 && (cards.length > 0 || rows.length > 0)) {
                    el.classList.remove('hidden');
                } else {
                    el.classList.add('hidden');
                }
            }
        });

        renderOperatorPagination(matchedCards, matchedRows);
    }

    document.addEventListener('DOMContentLoaded', function() {
        if (window.innerWidth < 768) {
            setOperatorLayout('grid');
        }
        applyFilters(true);
    });

    function resetFilters() {
        const searchInput = document.getElementById('filterSearchInput');
        const catSelect = document.getElementById('filterCategorySelect');
        const damageTypeSelect = document.getElementById('filterDamageTypeSelect');
        const archiveSelect = document.getElementById('filterArchiveStatusSelect');

        if (searchInput) searchInput.value = '';
        if (catSelect) catSelect.value = 'ALL';
        if (damageTypeSelect) damageTypeSelect.value = 'ALL';
        if (archiveSelect) archiveSelect.value = 'ALL';

        applyFilters();
    }

    // Modal Konfirmasi Tolak Laporan (Elegan, Cepat & Tanpa Lag)
    let currentRejectId = null;

    function confirmRejectComplaint(id, code) {
        currentRejectId = id;
        const codeEl = document.getElementById('rejectModalCode');
        if (codeEl) codeEl.innerText = '#' + code;
        const reasonInput = document.getElementById('rejectModalReasonInput');
        if (reasonInput) reasonInput.value = 'Laporan tidak memenuhi kriteria kelayakan atau bukti lapangan tidak sesuai.';

        const modal = document.getElementById('rejectConfirmModal');
        if (modal) {
            modal.classList.remove('hidden');
            document.body.style.overflow = 'hidden';
            if (window.lucide) lucide.createIcons({ root: modal });
        }
    }

    function closeRejectModal() {
        const modal = document.getElementById('rejectConfirmModal');
        if (modal) modal.classList.add('hidden');
        document.body.style.overflow = '';
        currentRejectId = null;
    }

    function executeRejectComplaint() {
        if (!currentRejectId) return;
        const form = document.getElementById('quickRejectForm');
        const reasonInput = document.getElementById('rejectModalReasonInput');
        if (form) {
            form.action = `/operator/complaints/${currentRejectId}/reject`;
            const hiddenReason = form.querySelector('input[name="reason"]');
            if (hiddenReason && reasonInput) {
                hiddenReason.value = reasonInput.value;
            }
            form.submit();
        }
    }

    // Modal Konfirmasi Minta Revisi Petugas Lapangan
    let currentRevisionForm = null;

    function openRevisionModal(formElement, code) {
        currentRevisionForm = formElement;
        const codeEl = document.getElementById('revisionModalCode');
        if (codeEl) codeEl.innerText = '#' + code;
        const notesInput = document.getElementById('revisionModalNotesInput');
        if (notesInput) notesInput.value = 'Pekerjaan fisik belum rata dan perlu perapihan ulang di lokasi.';

        const modal = document.getElementById('revisionConfirmModal');
        if (modal) {
            modal.classList.remove('hidden');
            document.body.style.overflow = 'hidden';
            if (window.lucide) lucide.createIcons({ root: modal });
        }
    }

    function closeRevisionModal() {
        const modal = document.getElementById('revisionConfirmModal');
        if (modal) modal.classList.add('hidden');
        document.body.style.overflow = '';
        currentRevisionForm = null;
    }

    function executeRevision() {
        if (!currentRevisionForm) return;
        const notesInput = document.getElementById('revisionModalNotesInput');
        const hiddenNotes = currentRevisionForm.querySelector('input[name="notes"]');
        if (hiddenNotes && notesInput) {
            hiddenNotes.value = notesInput.value;
        }
        currentRevisionForm.submit();
    }
</script>

{{-- Inklusi Script JS Monitoring Eksekutif & Pelaporan Resmi --}}
@include('partials.executive_monitoring_reporting_js')
@endsection
