@extends('layouts.app')

@section('title', 'Pusat Kendali Super Admin - Sistem Pengaduan')
@section('header_title', 'Pusat Kendali Super Admin')

@section('content')
@php
    $todayStr = \Carbon\Carbon::now('Asia/Makassar')->locale('id')->isoFormat('dddd, D MMMM Y • HH:mm:ss') . ' WITA';
    $tabMeta = [
        'users-management' => [
            'title' => 'Pengaturan Akun Pimpinan & Tambah Petugas',
            'subtitle' => 'Perbarui data akun Kepala Daerah (Bupati) serta tambah akun Operator atau Petugas Lapangan.',
        ],
        'users-list' => [
            'title' => 'Direktori Seluruh Pengguna Sistem',
            'subtitle' => 'Lihat daftar akun berdasarkan peran, cari pengguna, dan lakukan reset kata sandi.',
        ],
        'complaints-list' => [
            'title' => 'Monitoring Seluruh Pengaduan Infrastruktur',
            'subtitle' => 'Pantau seluruh laporan masyarakat lintas status, lokasi, dan progres penanganan regu.',
        ],
        'regions-gis' => [
            'title' => 'Manajemen Wilayah & Geofencing GIS Kolaka Utara',
            'subtitle' => 'Atur batas wilayah administratif, koordinat 15 kecamatan & desa, serta geofencing laporan.',
        ],
        'teams-management' => [
            'title' => 'Manajemen Regu Lapangan & Mode Penugasan',
            'subtitle' => 'Kelola regu kerja lapangan, koordinator regu, dan mode distribusi tugas sistem.',
        ],
        'reporting' => [
            'title' => 'Laporan Eksekutif & Statistik Kinerja',
            'subtitle' => 'Ringkasan capaian penanganan aduan, distribusi kategori kerusakan, dan ekspor laporan.',
        ],
    ];
    $currentMeta = $tabMeta[$activeTab] ?? $tabMeta['users-management'];
@endphp

<div class="py-4 sm:py-6 space-y-5">

    <!-- 1. HEADER SECTION -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pt-1">
        <div>
            <h2 id="adminSectionTitle" class="font-outfit font-bold text-lg sm:text-xl text-[#0F172A] leading-tight">
                {{ $currentMeta['title'] }}
            </h2>
            <p id="adminSectionSubtitle" class="text-xs text-[#475569] mt-0.5">
                {{ $currentMeta['subtitle'] }}
            </p>
        </div>
        <div class="flex items-center gap-2 shrink-0">
            <span class="px-3.5 py-2 rounded-lg bg-white text-[#0F172A] text-xs font-semibold border border-[#E2E8F0] flex items-center gap-2">
                <i data-lucide="clock" class="w-4 h-4 text-[#1E3A8A]"></i>
                <span class="live-wita-clock">{{ $todayStr }}</span>
            </span>
        </div>
    </div>

    <!-- 2. KPI SUMMARY CARDS -->
    <div class="grid grid-cols-3 sm:grid-cols-4 lg:grid-cols-7 gap-2 sm:gap-3">
        <!-- 1. Total Aduan -->
        <div class="p-2.5 sm:p-3.5 rounded-xl bg-white border border-[#E2E8F0] flex flex-col justify-between min-w-0">
            <div class="flex items-center justify-between gap-1.5">
                <span class="text-[10px] sm:text-[11px] text-[#475569] font-bold uppercase tracking-wider truncate">Total Aduan</span>
                <div class="w-6 h-6 sm:w-7 sm:h-7 rounded-lg bg-blue-50 text-[#1E3A8A] border border-blue-200 flex items-center justify-center shrink-0">
                    <i data-lucide="inbox" class="w-3 h-3 sm:w-3.5 sm:h-3.5"></i>
                </div>
            </div>
            <div class="mt-1.5 sm:mt-2">
                <div class="font-outfit text-xl sm:text-2xl font-bold text-[#0F172A] leading-tight">
                    {{ $summary['total_complaints'] ?? 0 }}
                </div>
                <span class="hidden sm:block text-[11px] text-[#475569] font-medium mt-0.5">Laporan masuk</span>
            </div>
        </div>

        <!-- 2. Menunggu -->
        <div class="p-2.5 sm:p-3.5 rounded-xl bg-white border border-[#E2E8F0] flex flex-col justify-between min-w-0">
            <div class="flex items-center justify-between gap-1.5">
                <span class="text-[10px] sm:text-[11px] text-[#475569] font-bold uppercase tracking-wider truncate">Menunggu</span>
                <div class="w-6 h-6 sm:w-7 sm:h-7 rounded-lg bg-red-50 text-[#EF4444] border border-red-200 flex items-center justify-center shrink-0">
                    <i data-lucide="clock" class="w-3 h-3 sm:w-3.5 sm:h-3.5"></i>
                </div>
            </div>
            <div class="mt-1.5 sm:mt-2">
                <div class="font-outfit text-xl sm:text-2xl font-bold text-[#0F172A] leading-tight">
                    {{ $summary['pending_verification'] ?? 0 }}
                </div>
                <span class="hidden sm:block text-[11px] text-[#475569] font-medium mt-0.5">Perlu verifikasi</span>
            </div>
        </div>

        <!-- 3. Ditugaskan -->
        <div class="p-2.5 sm:p-3.5 rounded-xl bg-white border border-[#E2E8F0] flex flex-col justify-between min-w-0">
            <div class="flex items-center justify-between gap-1.5">
                <span class="text-[10px] sm:text-[11px] text-[#475569] font-bold uppercase tracking-wider truncate">Ditugaskan</span>
                <div class="w-6 h-6 sm:w-7 sm:h-7 rounded-lg bg-blue-50 text-[#1E40AF] border border-blue-200 flex items-center justify-center shrink-0">
                    <i data-lucide="send" class="w-3 h-3 sm:w-3.5 sm:h-3.5"></i>
                </div>
            </div>
            <div class="mt-1.5 sm:mt-2">
                <div class="font-outfit text-xl sm:text-2xl font-bold text-[#0F172A] leading-tight">
                    {{ $summary['dispatched_count'] ?? 0 }}
                </div>
                <span class="hidden sm:block text-[11px] text-[#475569] font-medium mt-0.5">Ke regu lapangan</span>
            </div>
        </div>

        <!-- 4. Dikerjakan -->
        <div class="p-2.5 sm:p-3.5 rounded-xl bg-white border border-[#E2E8F0] flex flex-col justify-between min-w-0">
            <div class="flex items-center justify-between gap-1.5">
                <span class="text-[10px] sm:text-[11px] text-[#475569] font-bold uppercase tracking-wider truncate">Dikerjakan</span>
                <div class="w-6 h-6 sm:w-7 sm:h-7 rounded-lg bg-amber-50 text-[#D97706] border border-amber-200 flex items-center justify-center shrink-0">
                    <i data-lucide="hard-hat" class="w-3 h-3 sm:w-3.5 sm:h-3.5"></i>
                </div>
            </div>
            <div class="mt-1.5 sm:mt-2">
                <div class="font-outfit text-xl sm:text-2xl font-bold text-[#0F172A] leading-tight">
                    {{ $summary['in_progress_count'] ?? 0 }}
                </div>
                <span class="hidden sm:block text-[11px] text-[#475569] font-medium mt-0.5">Sedang ditangani</span>
            </div>
        </div>

        <!-- 5. Menunggu Validasi -->
        <div class="p-2.5 sm:p-3.5 rounded-xl bg-white border border-[#E2E8F0] flex flex-col justify-between min-w-0">
            <div class="flex items-center justify-between gap-1.5">
                <span class="text-[10px] sm:text-[11px] text-[#475569] font-bold uppercase tracking-wider truncate">Validasi</span>
                <div class="w-6 h-6 sm:w-7 sm:h-7 rounded-lg bg-amber-50 text-[#D97706] border border-amber-200 flex items-center justify-center shrink-0">
                    <i data-lucide="clock-3" class="w-3 h-3 sm:w-3.5 sm:h-3.5"></i>
                </div>
            </div>
            <div class="mt-1.5 sm:mt-2">
                <div class="font-outfit text-xl sm:text-2xl font-bold text-[#0F172A] leading-tight">
                    {{ $summary['waiting_validation'] ?? 0 }}
                </div>
                <span class="hidden sm:block text-[11px] text-[#475569] font-medium mt-0.5">Uji hasil kerja</span>
            </div>
        </div>

        <!-- 6. Selesai -->
        <div class="p-2.5 sm:p-3.5 rounded-xl bg-white border border-[#E2E8F0] flex flex-col justify-between min-w-0">
            <div class="flex items-center justify-between gap-1.5">
                <span class="text-[10px] sm:text-[11px] text-[#475569] font-bold uppercase tracking-wider truncate">Selesai</span>
                <div class="w-6 h-6 sm:w-7 sm:h-7 rounded-lg bg-emerald-50 text-[#10B981] border border-emerald-200 flex items-center justify-center shrink-0">
                    <i data-lucide="check-circle-2" class="w-3 h-3 sm:w-3.5 sm:h-3.5"></i>
                </div>
            </div>
            <div class="mt-1.5 sm:mt-2">
                <div class="font-outfit text-xl sm:text-2xl font-bold text-[#0F172A] leading-tight">
                    {{ $summary['completed_count'] ?? 0 }}
                </div>
                <span class="hidden sm:block text-[11px] text-[#475569] font-medium mt-0.5">Tuntas diperbaiki</span>
            </div>
        </div>

        <!-- 7. Total Tim -->
        <div class="col-span-3 sm:col-span-1 p-2.5 sm:p-3.5 rounded-xl bg-white border border-[#E2E8F0] flex flex-col justify-between min-w-0">
            <div class="flex items-center justify-between gap-1.5">
                <span class="text-[10px] sm:text-[11px] text-[#475569] font-bold uppercase tracking-wider truncate">Total Tim</span>
                <div class="w-6 h-6 sm:w-7 sm:h-7 rounded-lg bg-blue-50 text-[#1E3A8A] border border-blue-200 flex items-center justify-center shrink-0">
                    <i data-lucide="users-2" class="w-3 h-3 sm:w-3.5 sm:h-3.5"></i>
                </div>
            </div>
            <div class="mt-1.5 sm:mt-2">
                <div class="font-outfit text-xl sm:text-2xl font-bold text-[#0F172A] leading-tight">
                    {{ ($summary['total_operators'] ?? 0) + ($summary['total_field_workers'] ?? 0) }}
                </div>
                <span class="hidden sm:block text-[11px] text-[#475569] font-medium mt-0.5">Operator &amp; Petugas</span>
            </div>
        </div>
    </div>

    <!-- 3. MENU FITUR ADMIN (TERSTRUKTUR & MUDAH DIKENALI) -->
    <div class="grid grid-cols-3 lg:grid-cols-6 gap-2 sm:gap-2.5 w-full">
        <!-- 1. Tab Pengaturan Akun -->
        <button type="button" onclick="switchAdminTab('users-management')" id="tabBtn-users-management"
            class="admin-tab-btn text-left p-2.5 sm:p-3 rounded-xl border transition flex items-center gap-2 sm:gap-2.5 cursor-pointer {{ $activeTab === 'users-management' ? 'bg-[#1E3A8A] text-white border-[#1E3A8A]' : 'bg-white text-[#0F172A] border-[#E2E8F0] hover:bg-[#F8FAFC]' }}">
            <i data-lucide="user-plus" class="w-4 h-4 shrink-0 text-current"></i>
            <div class="min-w-0 flex-1">
                <span class="text-[11px] sm:text-xs font-bold block truncate leading-tight">Tambah &amp; Pimpinan</span>
                <span class="tab-sub text-[10px] block mt-0.5 truncate {{ $activeTab === 'users-management' ? 'text-blue-100' : 'text-[#475569]' }}">Bupati &amp; akun baru</span>
            </div>
        </button>

        <!-- 2. Tab Direktori Pengguna -->
        <button type="button" onclick="switchAdminTab('users-list')" id="tabBtn-users-list"
            class="admin-tab-btn text-left p-2.5 sm:p-3 rounded-xl border transition flex items-center gap-2 sm:gap-2.5 cursor-pointer {{ $activeTab === 'users-list' ? 'bg-[#1E3A8A] text-white border-[#1E3A8A]' : 'bg-white text-[#0F172A] border-[#E2E8F0] hover:bg-[#F8FAFC]' }}">
            <i data-lucide="users" class="w-4 h-4 shrink-0 text-current"></i>
            <div class="min-w-0 flex-1">
                <span class="text-[11px] sm:text-xs font-bold block truncate leading-tight">Direktori Akun</span>
                <span class="tab-sub text-[10px] block mt-0.5 truncate {{ $activeTab === 'users-list' ? 'text-blue-100' : 'text-[#475569]' }}">Daftar &amp; reset sandi</span>
            </div>
            <span class="tab-badge px-1.5 py-0.5 rounded-md text-[10px] font-bold shrink-0 {{ $activeTab === 'users-list' ? 'bg-white text-[#1E3A8A]' : 'bg-blue-50 text-[#1E3A8A] border border-blue-200' }}">
                {{ $users->count() }}
            </span>
        </button>

        <!-- 3. Tab Semua Pengaduan -->
        <button type="button" onclick="switchAdminTab('complaints-list')" id="tabBtn-complaints-list"
            class="admin-tab-btn text-left p-2.5 sm:p-3 rounded-xl border transition flex items-center gap-2 sm:gap-2.5 cursor-pointer {{ $activeTab === 'complaints-list' ? 'bg-[#1E3A8A] text-white border-[#1E3A8A]' : 'bg-white text-[#0F172A] border-[#E2E8F0] hover:bg-[#F8FAFC]' }}">
            <i data-lucide="layers" class="w-4 h-4 shrink-0 text-current"></i>
            <div class="min-w-0 flex-1">
                <span class="text-[11px] sm:text-xs font-bold block truncate leading-tight">Semua Aduan</span>
                <span class="tab-sub text-[10px] block mt-0.5 truncate {{ $activeTab === 'complaints-list' ? 'text-blue-100' : 'text-[#475569]' }}">Monitor lintas status</span>
            </div>
            <span class="tab-badge px-1.5 py-0.5 rounded-md text-[10px] font-bold shrink-0 {{ $activeTab === 'complaints-list' ? 'bg-white text-[#1E3A8A]' : 'bg-blue-50 text-[#1E3A8A] border border-blue-200' }}">
                {{ $complaints->count() }}
            </span>
        </button>

        <!-- 4. Tab Wilayah & Peta GIS -->
        <button type="button" onclick="switchAdminTab('regions-gis')" id="tabBtn-regions-gis"
            class="admin-tab-btn text-left p-2.5 sm:p-3 rounded-xl border transition flex items-center gap-2 sm:gap-2.5 cursor-pointer {{ $activeTab === 'regions-gis' ? 'bg-[#1E3A8A] text-white border-[#1E3A8A]' : 'bg-white text-[#0F172A] border-[#E2E8F0] hover:bg-[#F8FAFC]' }}">
            <i data-lucide="map" class="w-4 h-4 shrink-0 text-current"></i>
            <div class="min-w-0 flex-1">
                <span class="text-[11px] sm:text-xs font-bold block truncate leading-tight">Wilayah &amp; GIS</span>
                <span class="tab-sub text-[10px] block mt-0.5 truncate {{ $activeTab === 'regions-gis' ? 'text-blue-100' : 'text-[#475569]' }}">15 kec. &amp; geofence</span>
            </div>
            <span class="tab-badge px-1.5 py-0.5 rounded-md text-[10px] font-bold shrink-0 {{ $activeTab === 'regions-gis' ? 'bg-white text-[#1E3A8A]' : 'bg-blue-50 text-[#1E3A8A] border border-blue-200' }}">
                {{ count($districtStats ?? $regions) }}
            </span>
        </button>

        <!-- 5. Tab Regu & Mode Penugasan -->
        <button type="button" onclick="switchAdminTab('teams-management')" id="tabBtn-teams-management"
            class="admin-tab-btn text-left p-2.5 sm:p-3 rounded-xl border transition flex items-center gap-2 sm:gap-2.5 cursor-pointer {{ $activeTab === 'teams-management' ? 'bg-[#1E3A8A] text-white border-[#1E3A8A]' : 'bg-white text-[#0F172A] border-[#E2E8F0] hover:bg-[#F8FAFC]' }}">
            <i data-lucide="users-2" class="w-4 h-4 shrink-0 text-current"></i>
            <div class="min-w-0 flex-1">
                <span class="text-[11px] sm:text-xs font-bold block truncate leading-tight">Regu &amp; Tugas</span>
                <span class="tab-sub text-[10px] block mt-0.5 truncate {{ $activeTab === 'teams-management' ? 'text-blue-100' : 'text-[#475569]' }}">Tim &amp; mode disposisi</span>
            </div>
            <span class="tab-badge px-1.5 py-0.5 rounded-md text-[10px] font-bold shrink-0 {{ $activeTab === 'teams-management' ? 'bg-white text-[#1E3A8A]' : 'bg-blue-50 text-[#1E3A8A] border border-blue-200' }}">
                {{ $teams->count() }}
            </span>
        </button>

        <!-- 6. Tab Laporan Eksekutif -->
        <button type="button" onclick="switchAdminTab('reporting')" id="tabBtn-reporting"
            class="admin-tab-btn text-left p-2.5 sm:p-3 rounded-xl border transition flex items-center gap-2 sm:gap-2.5 cursor-pointer {{ $activeTab === 'reporting' ? 'bg-[#1E3A8A] text-white border-[#1E3A8A]' : 'bg-white text-[#0F172A] border-[#E2E8F0] hover:bg-[#F8FAFC]' }}">
            <i data-lucide="bar-chart-3" class="w-4 h-4 shrink-0 text-current"></i>
            <div class="min-w-0 flex-1">
                <span class="text-[11px] sm:text-xs font-bold block truncate leading-tight">Laporan</span>
                <span class="tab-sub text-[10px] block mt-0.5 truncate {{ $activeTab === 'reporting' ? 'text-blue-100' : 'text-[#475569]' }}">Rekap &amp; statistik</span>
            </div>
        </button>
    </div>

    <!-- Tab Panel 1: Manajemen Akun (Bupati & Petugas) -->
    <div id="tabPanel-users-management" class="admin-tab-panel {{ $activeTab === 'users-management' ? '' : 'hidden' }} space-y-5">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

            <!-- Card 1: Perbarui Akun / Ganti Kepala Daerah (Bupati) -->
            <div class="card-white rounded-2xl p-5 sm:p-6 bg-white border border-[#E2E8F0] shadow-2xs flex flex-col justify-between space-y-4">
                <div class="space-y-3">
                    <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                        <div class="flex items-center gap-2.5">
                            <div class="w-9 h-9 rounded-xl bg-blue-50 text-[#1E3A8A] border border-blue-200 flex items-center justify-center">
                                <i data-lucide="landmark" class="w-4 h-4"></i>
                            </div>
                            <div>
                                <h3 class="font-outfit font-extrabold text-sm sm:text-base text-slate-900 leading-tight">
                                    Perbarui Akun / Ganti Kepala Daerah Kabupaten
                                </h3>
                                <p class="text-[11px] text-slate-500">Pimpinan daerah pemegang hak monitoring eksekutif aduan.</p>
                            </div>
                        </div>
                        <span class="px-2.5 py-0.5 rounded-full bg-blue-50 text-[#1E3A8A] border border-blue-200 text-[10px] font-bold">
                            KEPALA DAERAH
                        </span>
                    </div>

                    <form method="POST" action="{{ route('admin.bupati.update') }}" class="space-y-3 pt-1">
                        @csrf
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Nama Lengkap Kepala Daerah</label>
                            <input type="text" name="name" value="{{ $bupati->name ?? 'Kepala Daerah Kabupaten' }}" required
                                class="w-full text-xs rounded-xl border border-slate-300 p-2.5 bg-white focus:ring-2 focus:ring-[#1E3A8A] focus:border-transparent transition">
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">Email Resmi</label>
                                <input type="email" name="email" value="{{ $bupati->email ?? 'bupati@kabupaten.go.id' }}" required
                                    class="w-full text-xs rounded-xl border border-slate-300 p-2.5 bg-white focus:ring-2 focus:ring-[#1E3A8A] focus:border-transparent transition">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">Nomor Handphone / WA</label>
                                <input type="text" name="phone" value="{{ $bupati->phone ?? '081234567890' }}"
                                    class="w-full text-xs rounded-xl border border-slate-300 p-2.5 bg-white focus:ring-2 focus:ring-[#1E3A8A] focus:border-transparent transition">
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">
                                Password Baru <span class="text-slate-400 font-normal text-[11px]">(Kosongkan bila tidak ingin mengubah)</span>
                            </label>
                            <input type="password" name="password" placeholder="••••••••"
                                class="w-full text-xs rounded-xl border border-slate-300 p-2.5 bg-white focus:ring-2 focus:ring-[#1E3A8A] focus:border-transparent transition">
                        </div>

                        <div class="pt-2">
                            <button type="submit" class="w-full py-2.5 px-4 rounded-xl bg-[#1E3A8A] hover:bg-blue-900 text-white font-bold text-xs shadow-xs transition flex items-center justify-center gap-2">
                                <i data-lucide="save" class="w-4 h-4"></i>
                                <span>Simpan Perubahan Akun Kepala Daerah</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Card 2: Penambahan Akun Baru (Operator & Pekerja Lapangan) -->
            <div class="card-white rounded-2xl p-5 sm:p-6 bg-white border border-[#E2E8F0] shadow-2xs flex flex-col justify-between space-y-4">
                <div class="space-y-3">
                    <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                        <div class="flex items-center gap-2.5">
                            <div class="w-9 h-9 rounded-xl bg-blue-50 text-[#1E3A8A] border border-blue-200 flex items-center justify-center">
                                <i data-lucide="user-plus" class="w-4 h-4"></i>
                            </div>
                            <div>
                                <h3 class="font-outfit font-extrabold text-sm sm:text-base text-slate-900 leading-tight">
                                    Penambahan Akun Baru
                                </h3>
                                <p class="text-[11px] text-slate-500">Daftarkan akun Operator Verifikasi atau Petugas Lapangan baru.</p>
                            </div>
                        </div>
                        <span class="px-2.5 py-0.5 rounded-full bg-blue-50 text-[#1E3A8A] border border-blue-200 text-[10px] font-bold">
                            PETUGAS & OPERATOR
                        </span>
                    </div>

                    <form method="POST" action="{{ route('admin.users.store') }}" class="space-y-3 pt-1">
                        @csrf
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">Nama Lengkap</label>
                                <input type="text" name="name" required placeholder="Contoh: Budi Santoso"
                                    class="w-full text-xs rounded-xl border border-slate-300 p-2.5 bg-white focus:ring-2 focus:ring-[#1E3A8A] focus:border-transparent transition">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">Peran / Role Akun</label>
                                <select name="role" required class="w-full text-xs rounded-xl border border-slate-300 p-2.5 bg-white focus:ring-2 focus:ring-[#1E3A8A] focus:border-transparent transition">
                                    <option value="OPERATOR">Operator Verifikasi (Disposisi Web)</option>
                                    <option value="PETUGAS" selected>Pekerja Lapangan (Petugas Teknis PUPR)</option>
                                    <option value="KEPALA_DAERAH" {{ $hasBupati ? 'disabled class=text-slate-400' : '' }}>
                                        Kepala Daerah Kabupaten {{ $hasBupati ? '(Terkunci: Maks. 1 Akun)' : '' }}
                                    </option>
                                    <option value="ADMIN" {{ $hasSuperAdmin ? 'disabled class=text-slate-400' : '' }}>
                                        Super Administrator {{ $hasSuperAdmin ? '(Terkunci: Maks. 1 Akun)' : '' }}
                                    </option>
                                </select>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">Email Akun</label>
                                <input type="email" name="email" required placeholder="petugas@kabupaten.go.id"
                                    class="w-full text-xs rounded-xl border border-slate-300 p-2.5 bg-white focus:ring-2 focus:ring-[#1E3A8A] focus:border-transparent transition">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">No. Handphone / WA</label>
                                <input type="text" name="phone" placeholder="08xxxxxxxxxx"
                                    class="w-full text-xs rounded-xl border border-slate-300 p-2.5 bg-white focus:ring-2 focus:ring-[#1E3A8A] focus:border-transparent transition">
                            </div>
                        </div>

                        <!-- Password Default Terkunci (Prinsip Zero Knowledge Sandi) -->
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Kata Sandi Awal Akun</label>
                            <input type="text" value="password" readonly tabindex="-1"
                                class="w-full text-xs font-mono font-bold rounded-xl border border-slate-200 p-2.5 bg-slate-100/80 text-slate-600 cursor-not-allowed select-none">
                        </div>

                        <div class="pt-2">
                            <button type="submit" class="w-full py-2.5 px-4 rounded-xl bg-[#1E3A8A] hover:bg-blue-900 text-white font-bold text-xs shadow-xs transition flex items-center justify-center gap-2">
                                <i data-lucide="plus-circle" class="w-4 h-4"></i>
                                <span>Daftarkan Akun & Kirim Undangan Email</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>

        </div>
    </div>

    <!-- Tab Panel 2: Daftar Pengguna Terdaftar -->
    <div id="tabPanel-users-list" class="admin-tab-panel {{ $activeTab === 'users-list' ? '' : 'hidden' }} space-y-4">
        
        <!-- Filter & Search Bar Pengguna Sistem -->
        <div class="card-white rounded-2xl p-4 bg-white border border-[#E2E8F0] shadow-2xs flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-3">
            
            <!-- Search Pengguna -->
            <div class="relative flex-1">
                <i data-lucide="search" class="w-4 h-4 text-slate-400 absolute left-3.5 top-1/2 -translate-y-1/2 pointer-events-none"></i>
                <input type="text" id="userSearchInput" oninput="filterUsersTable()" placeholder="Cari nama pengguna, email, atau no. handphone..."
                    class="w-full pl-9 pr-3 py-2 text-xs rounded-xl border border-slate-200 bg-slate-50 focus:bg-white focus:border-[#1E3A8A] focus:ring-1 focus:ring-[#1E3A8A] transition">
            </div>

            <!-- Filter Role Pengguna -->
            <div class="flex flex-wrap items-center justify-between sm:justify-end gap-2 w-full sm:w-auto">
                <div class="flex items-center gap-1.5 w-full sm:w-auto">
                    <div class="flex items-center gap-1 text-xs text-slate-500 shrink-0 font-medium">
                        <i data-lucide="shield" class="w-3.5 h-3.5 text-[#1E3A8A]"></i>
                        <span>Peran:</span>
                    </div>
                    <select id="userRoleFilter" onchange="filterUsersTable()" class="flex-1 sm:flex-none text-xs font-semibold rounded-xl border border-slate-200 py-2 px-3 bg-slate-50 text-slate-700 focus:outline-none focus:border-[#1E3A8A] focus:bg-white transition">
                        <option value="">Semua Peran / Role</option>
                        <option value="ADMIN">Administrator</option>
                        <option value="KEPALA_DAERAH">Kepala Daerah Kabupaten</option>
                        <option value="OPERATOR">Operator Verifikasi</option>
                        <option value="PETUGAS">Pekerja Lapangan</option>
                        <option value="WARGA">Warga Masyarakat</option>
                    </select>
                </div>

                <div class="px-3 py-2 rounded-xl bg-slate-100 text-slate-700 text-xs font-bold flex items-center gap-1.5 shrink-0">
                    <span>Total:</span>
                    <span id="filteredUserCount">{{ $users->count() }}</span>
                    <span>Akun</span>
                </div>

                <!-- Toggle Tata Letak Daftar / Petak -->
                <div class="inline-flex items-center p-0.5 rounded-lg bg-[#F8FAFC] border border-[#E2E8F0] shrink-0" role="group" aria-label="Tata letak direktori akun">
                    <button type="button" id="usersLayoutBtn-list" onclick="setAdminUsersLayout('list')"
                        class="px-2.5 py-1.5 rounded-md text-xs font-semibold flex items-center gap-1 transition cursor-pointer bg-[#1E3A8A] text-white">
                        <i data-lucide="list" class="w-3.5 h-3.5"></i>
                        <span>Daftar</span>
                    </button>
                    <button type="button" id="usersLayoutBtn-grid" onclick="setAdminUsersLayout('grid')"
                        class="px-2.5 py-1.5 rounded-md text-xs font-semibold flex items-center gap-1 transition cursor-pointer text-[#475569] hover:text-[#0F172A]">
                        <i data-lucide="layout-grid" class="w-3.5 h-3.5"></i>
                        <span>Petak</span>
                    </button>
                </div>
            </div>

        </div>

        <!-- Tabel Pengguna Sistem Modern & Enak Dilihat -->
        <div class="card-white rounded-2xl bg-white border border-[#E2E8F0] overflow-hidden">
            <div id="usersTableWrap" class="overflow-x-visible md:overflow-x-auto">
                <table class="w-full text-left text-xs text-slate-600" id="usersTable">
                    <thead class="hidden md:table-header-group bg-slate-50/80 text-slate-500 font-bold border-b border-[#E2E8F0] uppercase tracking-wider text-[11px]">
                        <tr>
                            <th class="py-3.5 px-4">Pengguna</th>
                            <th class="py-3.5 px-4">Email Akun</th>
                            <th class="py-3.5 px-4">Peran / Role</th>
                            <th class="py-3.5 px-4">No. Handphone</th>
                            <th class="py-3.5 px-4">Terdaftar Sejak</th>
                            <th class="py-3.5 px-4 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100" id="usersTableBody">
                        @foreach($users as $u)
                            @php
                                $roleUpper = strtoupper($u->role);
                                $roleDisplay = match($roleUpper) {
                                    'BUPATI', 'KEPALA_DAERAH' => 'KEPALA DAERAH',
                                    default => $roleUpper,
                                };
                                $roleBadgeClass = match($roleUpper) {
                                    'ADMIN', 'SUPERADMIN' => 'bg-blue-50 text-[#1E3A8A] border-blue-200 font-bold',
                                    'BUPATI', 'KEPALA_DAERAH' => 'bg-blue-100/70 text-[#1E3A8A] border-blue-300 font-extrabold',
                                    'OPERATOR'            => 'bg-blue-50 text-[#1E3A8A] border-blue-200 font-semibold',
                                    'PETUGAS'             => 'bg-slate-100 text-slate-700 border-slate-200 font-semibold',
                                    default               => 'bg-slate-100 text-slate-600 border-slate-200',
                                };
                                $avatarBg = match($roleUpper) {
                                    'ADMIN', 'SUPERADMIN', 'BUPATI', 'KEPALA_DAERAH' => 'bg-[#1E3A8A] text-white',
                                    default               => 'bg-slate-200 text-[#1E3A8A]',
                                };
                                $initial = strtoupper(substr($u->name, 0, 1));
                            @endphp
                            <tr class="user-row flex md:table-row items-center justify-between gap-2.5 px-3.5 py-3 md:p-0 hover:bg-slate-50/60 transition"
                                data-name="{{ strtolower($u->name) }}"
                                data-email="{{ strtolower($u->email) }}"
                                data-phone="{{ $u->phone ?? '' }}"
                                data-role="{{ $roleUpper == 'BUPATI' ? 'KEPALA_DAERAH' : $roleUpper }}">
                                <td class="block md:table-cell p-0 md:py-3 md:px-4 min-w-0 flex-1">
                                    <div class="flex items-center gap-2.5 sm:gap-3 min-w-0">
                                        <div class="w-8 h-8 rounded-full {{ $avatarBg }} flex items-center justify-center font-bold text-xs shrink-0">
                                            {{ $initial }}
                                        </div>
                                        <div class="min-w-0 flex-1">
                                            <div class="flex items-center gap-1.5 flex-wrap">
                                                <span class="font-bold text-slate-900 text-xs truncate">{{ $u->name }}</span>
                                                <span class="md:hidden px-1.5 py-0.5 rounded-full text-[9px] font-bold border {{ $roleBadgeClass }}">{{ $roleDisplay }}</span>
                                            </div>
                                            <div class="text-[11px] text-slate-400">ID #{{ $u->id }}</div>
                                            <div class="md:hidden text-[11px] text-slate-500 font-mono truncate mt-0.5">{{ $u->email }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="hidden md:table-cell py-3 px-4 font-mono text-slate-600 text-xs">{{ $u->email }}</td>
                                <td class="hidden md:table-cell py-3 px-4">
                                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold border {{ $roleBadgeClass }}">
                                        {{ $roleDisplay }}
                                    </span>
                                </td>
                                <td class="hidden md:table-cell py-3 px-4 text-slate-600 font-mono text-xs">{{ $u->phone ?? '-' }}</td>
                                <td class="hidden md:table-cell py-3 px-4 text-slate-400 text-[11px]">
                                    {{ $u->created_at ? $u->created_at->isoFormat('D MMM Y') : '-' }}
                                </td>
                                <td class="block md:table-cell p-0 md:py-3 md:px-4 text-right shrink-0">
                                    <div class="flex items-center justify-end gap-1.5">
                                        <!-- Edit Profil & Akun Pengguna -->
                                        <button type="button" 
                                            data-id="{{ $u->id }}"
                                            data-name="{{ $u->name }}"
                                            data-email="{{ $u->email }}"
                                            data-phone="{{ $u->phone ?? '' }}"
                                            data-role="{{ $roleUpper == 'BUPATI' ? 'KEPALA_DAERAH' : $roleUpper }}"
                                            onclick="openEditUserModalFromBtn(this)"
                                            class="p-1.5 rounded-lg border border-slate-200 text-slate-700 hover:bg-slate-100 hover:text-[#1E3A8A] transition cursor-pointer" 
                                            title="Edit Profil, Peran, No. HP, atau Password">
                                            <i data-lucide="edit-3" class="w-3.5 h-3.5"></i>
                                        </button>

                                        <!-- Reset Password Cepat ke default: password (Quick Action Modal) -->
                                        <button type="button" 
                                            onclick="openQuickResetModal({{ $u->id }}, '{{ addslashes($u->name) }}', '{{ addslashes($u->email) }}', '{{ $roleDisplay }}')"
                                            class="p-1.5 rounded-lg border border-slate-200 text-[#1E3A8A] hover:bg-blue-50 transition cursor-pointer" 
                                            title="Quick Action: Reset Password ke default (password)">
                                            <i data-lucide="key" class="w-3.5 h-3.5"></i>
                                        </button>

                                        @if($u->id !== auth()->id())
                                            @php
                                                $isLeaderAccount = in_array($roleUpper, ['ADMIN', 'SUPERADMIN', 'BUPATI', 'KEPALA_DAERAH']);
                                            @endphp
                                            @if($isLeaderAccount)
                                                <!-- Hapus Akun Petinggi / Sesama Super Admin (Wajib Re-Authentication Sandi) -->
                                                <button type="button" 
                                                    onclick="openConfirmDeleteLeaderModal({{ $u->id }}, '{{ addslashes($u->name) }}', '{{ $roleDisplay }}')"
                                                    class="p-1.5 rounded-lg border border-red-200 text-red-600 hover:bg-red-50 transition cursor-pointer" 
                                                    title="Hapus Akun Petinggi (Wajib Konfirmasi Sandi Super Admin)">
                                                    <i data-lucide="shield-alert" class="w-3.5 h-3.5"></i>
                                                </button>
                                            @else
                                                <!-- Hapus User Reguler (Modal Konfirmasi Cantik Konsisten Gambar 4) -->
                                                <button type="button" 
                                                    onclick="openConfirmDeleteUserModal({{ $u->id }}, '{{ addslashes($u->name) }}', '{{ addslashes($u->email) }}', '{{ $roleDisplay }}')"
                                                    class="p-1.5 rounded-lg border border-red-200 text-red-600 hover:bg-red-50 transition cursor-pointer" 
                                                    title="Hapus Akun Pengguna">
                                                    <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                                                </button>
                                            @endif
                                        @else
                                            <span class="p-1.5 rounded-lg border border-slate-200 text-slate-300 cursor-not-allowed inline-flex items-center" title="Akun Anda Sendiri (Proteksi Anti-Self-Lockout)">
                                                <i data-lucide="lock" class="w-3.5 h-3.5"></i>
                                            </span>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Grid Kartu Pengguna (Tata Letak Petak) -->
            <div id="usersGridWrap" class="hidden grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 p-4">
                @foreach($users as $u)
                    @php
                        $roleUpper = strtoupper($u->role?->value ?? $u->role);
                        $roleDisplay = match($roleUpper) {
                            'ADMIN', 'SUPERADMIN' => 'ADMIN',
                            'BUPATI', 'KEPALA_DAERAH' => 'KEPALA DAERAH',
                            'OPERATOR' => 'OPERATOR',
                            'PETUGAS' => 'PETUGAS',
                            'WARGA' => 'WARGA',
                            default => $roleUpper,
                        };
                        $roleFilterVal = match($roleUpper) {
                            'ADMIN', 'SUPERADMIN' => 'ADMIN',
                            'BUPATI', 'KEPALA_DAERAH' => 'KEPALA_DAERAH',
                            default => $roleUpper,
                        };
                        $roleBadgeClass = match($roleUpper) {
                            'ADMIN', 'SUPERADMIN' => 'bg-[#1E3A8A] text-white border-[#1E3A8A]',
                            'BUPATI', 'KEPALA_DAERAH' => 'bg-blue-100 text-[#1E3A8A] border-blue-200',
                            'OPERATOR' => 'bg-blue-50 text-[#1E3A8A] border-blue-200',
                            'PETUGAS' => 'bg-slate-100 text-slate-800 border-slate-200',
                            default => 'bg-slate-50 text-slate-600 border-slate-200',
                        };
                    @endphp
                    <div class="user-grid-card rounded-xl border border-[#E2E8F0] bg-white p-4 space-y-3 hover:border-[#1E3A8A] transition flex flex-col justify-between"
                        data-name="{{ strtolower($u->name) }}"
                        data-email="{{ strtolower($u->email) }}"
                        data-phone="{{ strtolower($u->phone ?? '') }}"
                        data-role="{{ $roleFilterVal }}">
                        <div class="space-y-2.5">
                            <div class="flex items-start justify-between gap-2">
                                <div class="flex items-center gap-2.5 min-w-0">
                                    <div class="w-9 h-9 rounded-full {{ $roleUpper === 'ADMIN' ? 'bg-[#1E3A8A] text-white' : 'bg-blue-50 text-[#1E3A8A]' }} flex items-center justify-center font-bold text-xs shrink-0">
                                        {{ strtoupper(substr($u->name, 0, 1)) }}
                                    </div>
                                    <div class="min-w-0">
                                        <div class="font-bold text-[#0F172A] text-xs truncate">{{ $u->name }}</div>
                                        <div class="text-[11px] text-[#475569] font-mono">ID #{{ $u->id }}</div>
                                    </div>
                                </div>
                                <span class="px-2 py-0.5 rounded-md text-[10px] font-bold border shrink-0 {{ $roleBadgeClass }}">{{ $roleDisplay }}</span>
                            </div>
                            <div class="text-xs text-[#475569] space-y-1 pt-1 border-t border-[#E2E8F0]">
                                <div class="font-mono text-[11px] truncate text-[#0F172A]">{{ $u->email }}</div>
                                <div class="flex items-center justify-between text-[11px]">
                                    <span class="font-mono">{{ $u->phone ?? '-' }}</span>
                                    <span>{{ $u->created_at?->format('d M Y') ?? '-' }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="flex items-center justify-end gap-1.5 pt-2 border-t border-[#E2E8F0]">
                            <button type="button"
                                onclick="openEditUserModal({{ $u->id }}, '{{ addslashes($u->name) }}', '{{ addslashes($u->email) }}', '{{ addslashes($u->phone ?? '') }}', '{{ $roleUpper }}')"
                                class="px-2.5 py-1 rounded-lg border border-[#E2E8F0] text-[#0F172A] hover:bg-slate-100 text-[11px] font-semibold transition cursor-pointer">
                                Edit
                            </button>
                            <button type="button"
                                onclick="openResetPassModal({{ $u->id }}, '{{ addslashes($u->name) }}', '{{ $roleDisplay }}')"
                                class="px-2.5 py-1 rounded-lg border border-blue-200 text-[#1E3A8A] hover:bg-blue-50 text-[11px] font-semibold transition cursor-pointer">
                                Sandi
                            </button>
                        </div>
                    </div>
                @endforeach
            </div>
            
            <div id="noUserFoundMsg" class="hidden p-8 text-center text-[#475569] text-xs">
                Tidak ada data pengguna yang sesuai dengan filter atau kata kunci pencarian.
            </div>

            <!-- Pagination Bar Direktori Akun (5 / 10 / 50 per halaman, default 10) -->
            <div id="usersPaginationBar" class="px-4 py-3 bg-[#F8FAFC] border-t border-[#E2E8F0] flex flex-col sm:flex-row sm:items-center justify-between gap-3 text-xs">
                <div class="flex flex-wrap items-center gap-3 text-[#475569]">
                    <div class="flex items-center gap-2">
                        <span class="font-medium">Tampilkan:</span>
                        <select id="usersPageSizeSelect" onchange="changeUsersPageSize(this.value)" aria-label="Jumlah akun per halaman"
                            class="text-xs font-semibold rounded-lg border border-[#E2E8F0] bg-white text-[#0F172A] px-2.5 py-1.5 focus:outline-none focus:border-[#1E3A8A] cursor-pointer">
                            <option value="5">5 akun</option>
                            <option value="10" selected>10 akun</option>
                            <option value="50">50 akun</option>
                        </select>
                    </div>
                    <span id="usersPageInfo" class="font-medium">Menampilkan 0–0 dari 0 akun</span>
                </div>
                <div id="usersPageControls" class="flex items-center gap-1.5 flex-wrap"></div>
            </div>
        </div>

    </div>

    <!-- Tab Panel 3: Daftar Seluruh Pengaduan -->
    <div id="tabPanel-complaints-list" class="admin-tab-panel {{ $activeTab === 'complaints-list' ? '' : 'hidden' }} space-y-4">
        
        <!-- Filter & Search Bar Instan (0ms Tanpa Loading / Reload Halaman Sama Seperti Halaman Operator) -->
        <div class="card-white rounded-2xl p-4 bg-white border border-[#E2E8F0] shadow-2xs flex flex-col sm:flex-row items-center gap-2.5">
            
            <!-- Search Bar Instan (Mengisi sisa ruang secara fleksibel tanpa whitespace kosong) -->
            <div class="relative w-full sm:flex-1 min-w-[160px]">
                <i data-lucide="search" class="w-4 h-4 text-slate-400 absolute left-3.5 top-1/2 -translate-y-1/2 pointer-events-none"></i>
                <input type="text" id="complaintSearchInput" oninput="filterComplaintsTable()" placeholder="Cari kode aduan, judul kerusakan, nama pelapor, atau alamat..."
                    class="w-full pl-9 pr-3 py-1.5 text-xs rounded-xl border border-slate-200 bg-slate-50 focus:bg-white focus:border-[#1E3A8A] focus:ring-1 focus:ring-[#1E3A8A] transition">
            </div>

            <!-- Filter Kategori & Status Instan (Kompak 2-Kolom di Mobile, Satu Baris di Desktop) -->
            <div class="grid grid-cols-2 sm:flex sm:flex-wrap lg:flex-nowrap items-center gap-2 w-full sm:w-auto shrink-0">
                <!-- Dropdown Kategori -->
                <div class="flex flex-col sm:flex-row sm:items-center gap-1 min-w-0">
                    <div class="flex items-center gap-1 text-[11px] sm:text-xs text-slate-500 shrink-0 font-medium whitespace-nowrap">
                        <i data-lucide="layers" class="w-3.5 h-3.5 text-[#1E3A8A]"></i>
                        <span>Kategori:</span>
                    </div>
                    <select id="complaintCategorySelect" onchange="filterComplaintsTable()" 
                        class="w-full sm:w-auto text-xs font-semibold rounded-xl border border-slate-200 py-1.5 px-2 bg-slate-50 text-slate-700 focus:outline-none focus:border-[#1E3A8A] focus:bg-white transition">
                        <option value="ALL">Semua Kategori</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Dropdown Tipe Laporan (Kerusakan Ringan / Berat) -->
                <div class="flex flex-col sm:flex-row sm:items-center gap-1 min-w-0">
                    <div class="flex items-center gap-1 text-[11px] sm:text-xs text-slate-500 shrink-0 font-medium whitespace-nowrap">
                        <i data-lucide="git-commit" class="w-3.5 h-3.5 text-[#1E3A8A]"></i>
                        <span>Tipe:</span>
                    </div>
                    <select id="complaintDamageTypeSelect" onchange="filterComplaintsTable()" 
                        class="w-full sm:w-auto text-xs font-semibold rounded-xl border border-slate-200 py-1.5 px-2 bg-slate-50 text-slate-700 focus:outline-none focus:border-[#1E3A8A] focus:bg-white transition">
                        <option value="ALL">Semua Tipe</option>
                        <option value="minor">Kerusakan Ringan</option>
                        <option value="major">Kerusakan Berat</option>
                    </select>
                </div>

                <!-- Dropdown Status -->
                <div class="flex flex-col sm:flex-row sm:items-center gap-1 min-w-0">
                    <div class="flex items-center gap-1 text-[11px] sm:text-xs text-slate-500 shrink-0 font-medium whitespace-nowrap">
                        <i data-lucide="check-square" class="w-3.5 h-3.5 text-[#1E3A8A]"></i>
                        <span>Status:</span>
                    </div>
                    <select id="complaintStatusSelect" onchange="filterComplaintsTable()" 
                        class="w-full sm:w-auto text-xs font-semibold rounded-xl border border-slate-200 py-1.5 px-2 bg-slate-50 text-slate-700 focus:outline-none focus:border-[#1E3A8A] focus:bg-white transition">
                        <option value="ALL">Semua Status</option>
                        <option value="SUBMITTED">Verifikasi</option>
                        <option value="DISPATCHED">Ditugaskan</option>
                        <option value="IN_PROGRESS">Dikerjakan</option>
                        <option value="WAITING_VERIFICATION">Validasi</option>
                        <option value="COMPLETED">Selesai</option>
                        <option value="REJECTED">Ditolak</option>
                    </select>
                </div>

                <div class="flex items-end sm:items-center justify-end gap-1.5 self-end sm:self-auto">
                    <!-- Tombol Reset Filter (Muncul hanya saat filter aktif) -->
                    <button type="button" id="complaintResetBtn" onclick="resetComplaintFilters()" 
                        class="hidden px-2.5 py-1.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs transition flex items-center gap-1 shrink-0 cursor-pointer">
                        <i data-lucide="rotate-ccw" class="w-3.5 h-3.5"></i>
                        <span>Reset</span>
                    </button>

                    <!-- Toggle Tata Letak Daftar / Petak -->
                    <div class="inline-flex items-center p-0.5 rounded-lg bg-[#F8FAFC] border border-[#E2E8F0] shrink-0" role="group" aria-label="Tata letak semua aduan">
                        <button type="button" id="complaintsLayoutBtn-list" onclick="setAdminComplaintsLayout('list')"
                            class="px-2.5 py-1 rounded-md text-xs font-semibold flex items-center gap-1 transition cursor-pointer bg-[#1E3A8A] text-white">
                            <i data-lucide="list" class="w-3.5 h-3.5"></i>
                            <span>Daftar</span>
                        </button>
                        <button type="button" id="complaintsLayoutBtn-grid" onclick="setAdminComplaintsLayout('grid')"
                            class="px-2.5 py-1 rounded-md text-xs font-semibold flex items-center gap-1 transition cursor-pointer text-[#475569] hover:text-[#0F172A]">
                            <i data-lucide="layout-grid" class="w-3.5 h-3.5"></i>
                            <span>Petak</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tabel Daftar Seluruh Pengaduan -->
        <div class="card-white rounded-2xl bg-white border border-[#E2E8F0] overflow-hidden">
            <div id="complaintsTableWrap" class="overflow-hidden md:overflow-x-auto">
            <table class="block md:table w-full text-left text-xs text-slate-600" id="complaintsTable">
                <thead class="hidden md:table-header-group bg-slate-50/80 text-slate-500 font-bold border-b border-[#E2E8F0] uppercase tracking-wider text-[11px]">
                    <tr>
                        <th class="py-3 px-3 w-28 whitespace-nowrap">Kode</th>
                        <th class="py-3 px-3">Judul & Lokasi</th>
                        <th class="py-3 px-3 w-20 whitespace-nowrap">Tipe</th>
                        <th class="py-3 px-3 w-28 whitespace-nowrap">Kategori</th>
                        <th class="py-3 px-3 w-32 whitespace-nowrap">Pelapor</th>
                        <th class="py-3 px-3 w-24 text-center whitespace-nowrap">Status</th>
                        <th class="py-3 px-3 w-32 whitespace-nowrap">Petugas</th>
                        <th class="py-3 px-3 w-20 text-center whitespace-nowrap">Aksi</th>
                    </tr>
                </thead>
                <tbody class="block md:table-row-group divide-y divide-slate-100" id="complaintsTableBody">
                    @forelse($complaints as $c)
                        @php
                            $firstPhoto = $c->photos->first();
                            $firstPhotoUrl = $firstPhoto ? ($firstPhoto->url ?? asset('storage/' . $firstPhoto->storage_key)) : null;
                            $status = $c->status;
                            $statusLabel = $status;
                            $statusBadgeClass = 'bg-slate-100 text-slate-700 border-slate-200';

                            if ($status === 'REJECTED') {
                                $statusLabel = 'DITOLAK';
                                $statusBadgeClass = 'bg-red-50 text-red-700 border-red-200';
                            } elseif (in_array($status, ['COMPLETED', 'CLOSED'])) {
                                $statusLabel = 'SELESAI';
                                $statusBadgeClass = 'bg-emerald-50 text-emerald-700 border-emerald-200';
                            } elseif ($status === 'WAITING_VERIFICATION') {
                                $statusLabel = 'VALIDASI';
                                $statusBadgeClass = 'bg-amber-50 text-amber-700 border-amber-300';
                            } elseif ($status === 'IN_PROGRESS') {
                                $statusLabel = 'DIKERJAKAN';
                                $statusBadgeClass = 'bg-amber-50 text-amber-700 border-amber-300';
                            } elseif ($status === 'DISPATCHED') {
                                $statusLabel = 'DITUGASKAN';
                                $statusBadgeClass = 'bg-indigo-50 text-indigo-700 border-indigo-200';
                            } elseif (in_array($status, ['SUBMITTED', 'VERIFICATION'])) {
                                $statusLabel = 'VERIFIKASI';
                                $statusBadgeClass = 'bg-red-50 text-red-700 border-red-200';
                            } elseif ($status === 'REVISION_REQUIRED') {
                                $statusLabel = 'REVISI';
                                $statusBadgeClass = 'bg-orange-50 text-orange-700 border-orange-200';
                            }
                        @endphp
                        <tr onclick="openAdminDetailModal({{ $c->id }})" class="complaint-row flex flex-row items-center justify-between md:table-row gap-2.5 px-3.5 py-3 md:p-0 w-full hover:bg-slate-50/60 transition cursor-pointer"
                            data-search="{{ strtolower($c->public_code . ' ' . $c->title . ' ' . ($c->user?->name ?? $c->citizen?->name ?? '') . ' ' . $c->address) }}"
                            data-category="{{ $c->category_id }}"
                            data-damage-type="{{ $c->damage_type ?? 'minor' }}"
                            data-status="{{ $c->status }}">
                            <td class="hidden md:table-cell py-2.5 px-3 font-mono font-bold text-[#1E3A8A] text-xs whitespace-nowrap">
                                <button type="button" onclick="event.stopPropagation(); openAdminDetailModal({{ $c->id }})" class="hover:underline text-left cursor-pointer">
                                    #{{ $c->public_code }}
                                </button>
                                <div class="text-[10px] text-slate-400 font-sans font-normal mt-0.5">{{ $c->created_at?->diffForHumans() }}</div>
                            </td>
                            <td class="block md:table-cell p-0 md:py-2.5 md:px-3 flex-1 md:w-auto min-w-0">
                                <div class="flex items-center gap-2.5 min-w-0 group">
                                    <div class="relative w-11 h-11 rounded-xl overflow-hidden border border-slate-200 bg-slate-100 shrink-0 group-hover:ring-2 group-hover:ring-[#1E3A8A] transition" title="Lihat Foto & Detail">
                                        @if($firstPhotoUrl)
                                            <img src="{{ $firstPhotoUrl }}" alt="Foto Aduan" class="w-full h-full object-cover">
                                        @else
                                            <div class="w-full h-full flex items-center justify-center text-slate-300">
                                                <i data-lucide="image" class="w-4 h-4"></i>
                                            </div>
                                        @endif
                                    </div>
                                    <div class="min-w-0 flex-1">
                                        <div class="md:hidden flex items-center gap-1.5 flex-wrap mb-0.5">
                                            <span class="font-mono font-bold text-[#1E3A8A] text-[10px]">#{{ $c->public_code }}</span>
                                            <span class="px-1.5 py-0.5 rounded-full text-[9px] font-bold border {{ $statusBadgeClass }}">{{ $statusLabel }}</span>
                                        </div>
                                        <div class="flex items-center gap-1.5 flex-wrap mb-0.5">
                                            <button type="button" onclick="openAdminDetailModal({{ $c->id }})" class="font-bold text-slate-900 text-xs truncate hover:text-[#1E3A8A] text-left cursor-pointer max-w-full md:max-w-[200px]" title="{{ $c->title }}">
                                                {{ $c->title }}
                                            </button>
                                            @if($c->severity_text)
                                                <span class="px-1.5 py-0.2 rounded text-[9.5px] font-bold bg-blue-50 text-[#1E3A8A] border border-blue-200/80 shrink-0 whitespace-nowrap">
                                                    {{ $c->severity_text }}
                                                </span>
                                            @endif
                                        </div>
                                        <div class="text-[10.5px] text-slate-400 flex items-center gap-1 truncate max-w-full md:max-w-[240px]">
                                            <i data-lucide="map-pin" class="w-3 h-3 text-slate-400 shrink-0"></i>
                                            <span class="truncate" title="{{ $c->address }}">{{ $c->address ?? 'Kolaka Utara' }}</span>
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="hidden md:table-cell py-2.5 px-3 whitespace-nowrap">
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-red-50 text-red-700 border border-red-200">
                                    <span class="w-1.5 h-1.5 rounded-full bg-red-500"></span>
                                    <span>{{ ($c->damage_type ?? 'point') === 'major' ? 'Berat' : 'Ringan' }}</span>
                                </span>
                            </td>
                            <td class="hidden md:table-cell py-2.5 px-3 whitespace-nowrap">
                                <span class="px-2 py-0.5 rounded-md bg-slate-100 text-slate-700 text-[10.5px] font-semibold border border-slate-200/60 truncate max-w-[120px] inline-block" title="{{ $c->category?->name ?? 'Infrastruktur' }}">
                                    {{ $c->category?->name ?? 'Infrastruktur' }}
                                </span>
                            </td>
                            <td class="hidden md:table-cell py-2.5 px-3 whitespace-nowrap">
                                <div class="font-semibold text-slate-800 text-xs truncate max-w-[130px]">{{ $c->user?->name ?? 'Warga Terdaftar' }}</div>
                            </td>
                            <td class="hidden md:table-cell py-2.5 px-3 text-center whitespace-nowrap">
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold border {{ $statusBadgeClass }}">
                                    {{ $statusLabel }}
                                </span>
                            </td>
                            <td class="hidden md:table-cell py-2.5 px-3 text-xs text-slate-700">
                                @if(in_array($c->status, ['IN_PROGRESS', 'WAITING_VERIFICATION', 'COMPLETED', 'CLOSED']) && $c->latestDisposition?->assignee?->name)
                                    <div class="flex items-center gap-1 text-[11px] text-slate-700 font-semibold truncate max-w-[130px]">
                                        <i data-lucide="hard-hat" class="w-3 h-3 text-slate-400 shrink-0"></i>
                                        <span class="truncate">{{ $c->latestDisposition->assignee->name }}</span>
                                    </div>
                                @else
                                    <span class="text-[11px] text-slate-400 font-medium italic">Belum diambil</span>
                                @endif
                            </td>
                            <td onclick="event.stopPropagation();" class="block md:table-cell p-0 md:py-2.5 md:px-3 text-right md:text-center whitespace-nowrap shrink-0 md:w-auto cursor-default">
                                <div class="flex items-center justify-end md:justify-center gap-1.5">
                                    <button type="button" 
                                        onclick="openDeleteComplaintModal('{{ route('admin.complaints.delete', $c->id) }}', '{{ $c->public_code }}', '{{ addslashes($c->title) }}')"
                                        title="Hapus Aduan"
                                        class="w-7 h-7 sm:w-8 sm:h-8 rounded-xl bg-red-50 hover:bg-red-600 text-red-600 hover:text-white border border-red-200 transition inline-flex items-center justify-center shadow-2xs cursor-pointer">
                                        <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="py-8 text-center text-slate-400 text-xs">
                                    Tidak ada data aduan ditemukan.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Grid Kartu Semua Aduan (Tata Letak Petak) -->
            <div id="complaintsGridWrap" class="hidden grid grid-cols-1 md:grid-cols-2 gap-4 p-4">
                @foreach($complaints as $c)
                    @php
                        $cPhoto = $c->photos->first() ? ($c->photos->first()->url ?? asset('storage/' . $c->photos->first()->storage_key)) : null;
                        $cSt = $c->status?->value ?? $c->status;
                        $cStLabel = match($cSt) {
                            'COMPLETED', 'CLOSED' => 'SELESAI',
                            'WAITING_VERIFICATION' => 'VALIDASI',
                            'IN_PROGRESS' => 'DIKERJAKAN',
                            'DISPATCHED' => 'DITUGASKAN',
                            'REJECTED' => 'DITOLAK',
                            'REVISION_REQUIRED' => 'PERLU REVISI',
                            default => 'VERIFIKASI',
                        };
                        $cStClass = match($cSt) {
                            'COMPLETED', 'CLOSED' => 'bg-emerald-50 text-emerald-800 border-emerald-200',
                            'WAITING_VERIFICATION' => 'bg-amber-50 text-amber-700 border-amber-300',
                            'IN_PROGRESS', 'REVISION_REQUIRED' => 'bg-amber-50 text-amber-800 border-amber-300',
                            'DISPATCHED' => 'bg-blue-50 text-[#1E3A8A] border-blue-200',
                            default => 'bg-red-50 text-red-700 border-red-200',
                        };
                        $cSearch = strtolower($c->public_code . ' ' . $c->title . ' ' . $c->address . ' ' . ($c->citizen?->name ?? ''));
                    @endphp
                    <div onclick="if (window.innerWidth < 768) { openAdminDetailModal({{ $c->id }}); }" class="complaint-grid-card rounded-xl border border-[#E2E8F0] bg-white p-4 space-y-3 hover:border-[#1E3A8A] transition cursor-pointer md:cursor-default"
                        data-search="{{ $cSearch }}"
                        data-category="{{ $c->category_id ?? '' }}"
                        data-damage-type="{{ $c->damage_type ?? 'minor' }}"
                        data-status="{{ $cSt }}">
                        <div class="flex items-center justify-between gap-2 flex-wrap">
                            <div class="flex items-center gap-1.5 flex-wrap">
                                <span class="px-2 py-0.5 rounded-md bg-slate-100 text-[#0F172A] font-mono text-[11px] font-semibold border border-[#E2E8F0]">#{{ $c->public_code }}</span>
                                <span class="px-2 py-0.5 rounded-md text-[11px] font-semibold bg-red-50 text-red-700 border border-red-200">{{ ($c->damage_type ?? 'minor') === 'major' ? 'Kerusakan Berat' : 'Kerusakan Ringan' }}</span>
                                <span class="px-2 py-0.5 rounded-md text-[11px] font-semibold bg-slate-100 text-[#0F172A] border border-[#E2E8F0]">{{ $c->category?->name ?? 'Infrastruktur' }}</span>
                            </div>
                            <span class="px-2 py-0.5 rounded-md text-[11px] font-bold border {{ $cStClass }}">{{ $cStLabel }}</span>
                        </div>
                        <div class="flex gap-3 items-start">
                            <div onclick="openAdminDetailModal({{ $c->id }})" class="w-20 h-20 rounded-xl overflow-hidden border border-[#E2E8F0] bg-slate-100 shrink-0 cursor-pointer">
                                @if($cPhoto)
                                    <img src="{{ $cPhoto }}" alt="Foto Aduan" class="w-full h-full object-cover">
                                @else
                                    <div class="w-full h-full flex items-center justify-center text-slate-400 text-[10px]">Tanpa Foto</div>
                                @endif
                            </div>
                            <div class="min-w-0 flex-1 space-y-1">
                                <h4 class="font-outfit font-bold text-sm text-[#0F172A] truncate" title="{{ $c->title }}">{{ $c->title }}</h4>
                                <p class="text-xs text-[#475569] truncate">{{ $c->address }}</p>
                                <p class="text-[11px] text-[#475569]">Pelapor: <strong class="text-[#0F172A]">{{ $c->citizen?->name ?? 'Warga' }}</strong> • {{ $c->created_at?->diffForHumans() }}</p>
                                <div class="flex items-center justify-end md:justify-between gap-2 pt-1">
                                    <button type="button" onclick="openAdminDetailModal({{ $c->id }})" class="hidden md:inline-flex text-xs font-bold text-[#1E3A8A] hover:underline items-center gap-1 cursor-pointer">
                                        <span>Lihat Rincian Lengkap & Foto</span>
                                        <i data-lucide="arrow-right" class="w-3 h-3"></i>
                                    </button>
                                    <button type="button"
                                        onclick="event.stopPropagation(); openDeleteComplaintModal('{{ route('admin.complaints.delete', $c->id) }}', '{{ $c->public_code }}', '{{ addslashes($c->title) }}')"
                                        title="Hapus Aduan"
                                        class="px-2.5 py-1 rounded-lg bg-red-50 hover:bg-red-600 text-red-600 hover:text-white border border-red-200 transition inline-flex items-center gap-1 text-[11px] font-bold cursor-pointer">
                                        <i data-lucide="trash-2" class="w-3 h-3"></i>
                                        <span>Hapus</span>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <div id="noComplaintFoundMsg" class="hidden p-8 text-center text-[#475569] text-xs">
                Tidak ada data aduan yang sesuai dengan filter atau kata kunci pencarian.
            </div>

            <!-- Pagination Bar Semua Aduan (5 / 10 / 50 per halaman, default 10) -->
            <div id="complaintsPaginationBar" class="px-4 py-3 bg-[#F8FAFC] border-t border-[#E2E8F0] flex flex-col sm:flex-row sm:items-center justify-between gap-3 text-xs">
                <div class="flex flex-wrap items-center gap-3 text-[#475569]">
                    <div class="flex items-center gap-2">
                        <span class="font-medium">Tampilkan:</span>
                        <select id="complaintsPageSizeSelect" onchange="changeComplaintsPageSize(this.value)" aria-label="Jumlah aduan per halaman"
                            class="text-xs font-semibold rounded-lg border border-[#E2E8F0] bg-white text-[#0F172A] px-2.5 py-1.5 focus:outline-none focus:border-[#1E3A8A] cursor-pointer">
                            <option value="5">5 aduan</option>
                            <option value="10" selected>10 aduan</option>
                            <option value="50">50 aduan</option>
                        </select>
                    </div>
                    <span id="complaintsPageInfo" class="font-medium">Menampilkan 0–0 dari 0 aduan</span>
                </div>
                <div id="complaintsPageControls" class="flex items-center gap-1.5 flex-wrap"></div>
            </div>
        </div>

    </div>

    <!-- Tab Panel 4: CMS Pemetaan Wilayah & Geofencing GIS (Kolaka Utara) -->
    <div id="tabPanel-regions-gis" class="admin-tab-panel {{ $activeTab === 'regions-gis' ? '' : 'hidden' }} space-y-6">

        <!-- Peta Interaktif Batas Wilayah Kolaka Utara -->
        <div id="adminRegionMapCard" class="card-white rounded-2xl p-5 bg-white border border-[#E2E8F0] shadow-2xs space-y-3">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 border-b border-slate-100 pb-3">
                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-xl bg-blue-50 text-[#1E3A8A] flex items-center justify-center border border-blue-200">
                        <i data-lucide="map" class="w-4 h-4"></i>
                    </div>
                    <div>
                        <h4 class="font-outfit font-bold text-sm text-slate-900">
                            Peta Spasial Administratif & Titik Pusat 15 Kecamatan
                        </h4>
                        <p class="text-xs text-slate-500">Visualisasi 15 titik koordinat daratan kecamatan administratif Kabupaten Kolaka Utara.</p>
                    </div>
                </div>
                <div class="flex flex-wrap items-center gap-3 text-xs font-semibold">
                    <span class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full bg-[#EF4444]"></span> Verifikasi</span>
                    <span class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full bg-[#1E40AF]"></span> Ditugaskan</span>
                    <span class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full bg-[#F59E0B]"></span> Sedang Dikerjakan</span>
                    <span class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full bg-[#F59E0B]"></span> Validasi</span>
                    <span class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full bg-[#10B981]"></span> Selesai</span>
                    <span class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full bg-[#DC2626]"></span> Ditolak</span>
                    <button type="button" onclick="resetAdminRegionMapView()" class="ml-2 px-3 py-1.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs transition flex items-center gap-1.5 cursor-pointer">
                        <i data-lucide="focus" class="w-3.5 h-3.5"></i>
                        <span>Fokus Lasusua</span>
                    </button>
                </div>
            </div>

            <!-- Banner Interaktif CMS Penentuan Titik Koordinat Peta (Saat Edit Aktif) -->
            <div id="pickingBanner" class="hidden p-3.5 sm:p-4 rounded-xl bg-blue-50/95 border-2 border-[#1E3A8A] flex flex-col sm:flex-row sm:items-center justify-between gap-3 shadow-md animate-in fade-in duration-200">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-xl bg-[#1E3A8A] text-white flex items-center justify-center shrink-0 shadow-xs">
                        <i data-lucide="crosshair" class="w-5 h-5"></i>
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="px-2 py-0.5 rounded text-[10px] font-black uppercase tracking-wider bg-[#1E3A8A] text-white">Mode CMS Atur Titik</span>
                            <strong class="text-xs sm:text-sm text-slate-900 font-extrabold" id="pickingDistrictTitle">Kecamatan Lasusua</strong>
                        </div>
                        <span class="text-[11px] text-slate-600 block mt-0.5">
                            Silakan <span class="font-bold text-[#1E3A8A]">klik atau klik 2 kali (double-click) pada peta</span> di bawah ini untuk memindahkan titik koordinat pusat baru.
                        </span>
                    </div>
                </div>
                <div class="flex items-center gap-2 w-full sm:w-auto self-end sm:self-auto shrink-0 flex-wrap sm:flex-nowrap">
                    <div class="px-3 py-1.5 rounded-xl bg-white border border-blue-200 text-xs font-mono font-bold text-[#1E3A8A] shadow-2xs" id="pickingCoordsDisplay">
                        Klik pada peta...
                    </div>
                    <button type="button" id="savePickedBtn" onclick="savePickedCoordinate()" disabled
                        class="px-4 py-2 rounded-xl bg-[#1E3A8A] hover:bg-blue-900 disabled:opacity-50 disabled:cursor-not-allowed text-white font-bold text-xs shadow-xs transition flex items-center gap-1.5 cursor-pointer">
                        <i data-lucide="save" class="w-3.5 h-3.5"></i>
                        <span>Simpan Titik</span>
                    </button>
                    <button type="button" onclick="cancelPickingCoordinate()"
                        class="px-3.5 py-2 rounded-xl bg-slate-200 hover:bg-slate-300 text-slate-700 font-bold text-xs transition cursor-pointer">
                        Batal
                    </button>
                </div>
            </div>
            
            <div class="relative w-full h-[400px] rounded-xl overflow-hidden border border-slate-200 bg-slate-100">
                <div id="adminRegionMap" class="w-full h-full z-0"></div>
            </div>
        </div>

        <!-- Sebaran Pengaduan Per Wilayah Kecamatan & Desa (Kolaka Utara) -->
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
                            Distribusi laporan masyarakat, efektivitas penanganan fisik Dinas PUPR, dan status koordinat 15 Kecamatan Kabupaten Kolaka Utara.
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
            @php
                $lasusuaData = collect($districtStats)->first(function($d) {
                    return str_contains(strtolower($d['district']), 'lasusua');
                }) ?? ['id' => 1, 'district' => 'Kecamatan Lasusua', 'village' => 'Kel. Lasusua', 'latitude' => -3.5262, 'longitude' => 120.8407];
            @endphp
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                <div class="p-3.5 rounded-xl bg-slate-50 border border-slate-200/80 flex items-center justify-between">
                    <div>
                        <span class="text-[11px] text-slate-500 font-semibold block">Total Kecamatan Terdaftar</span>
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
                        <button type="button" onclick="startPickingCoordinate({{ $lasusuaData['id'] }}, '{{ $lasusuaData['district'] }} (Ibukota)', {{ $lasusuaData['latitude'] }}, {{ $lasusuaData['longitude'] }})"
                            class="mt-1 text-[11px] font-bold text-[#1E3A8A] hover:underline flex items-center gap-1 cursor-pointer">
                            <i data-lucide="crosshair" class="w-3 h-3"></i>
                            <span>Atur Pusat Ibukota di Peta</span>
                        </button>
                    </div>
                    <div class="w-8 h-8 rounded-xl bg-blue-50/80 text-[#1E3A8A] border border-blue-100/70 flex items-center justify-center shrink-0">
                        <i data-lucide="navigation" class="w-4 h-4"></i>
                    </div>
                </div>

                <div class="p-3.5 rounded-xl bg-slate-50 border border-slate-200/80 flex items-center justify-between">
                    <div>
                        <span class="text-[11px] text-slate-500 font-semibold block">Cakupan Wilayah Layanan</span>
                        <span class="text-sm font-extrabold text-slate-800 flex items-center gap-1.5 mt-1">
                            <span class="w-2 h-2 rounded-full bg-[#1E3A8A]"></span>
                            Kabupaten Kolaka Utara
                        </span>
                    </div>
                    <div class="w-8 h-8 rounded-xl bg-blue-50/80 text-[#1E3A8A] border border-blue-100/70 flex items-center justify-center shrink-0">
                        <i data-lucide="shield-check" class="w-4 h-4"></i>
                    </div>
                </div>
            </div>

            <!-- Grid 15 Kecamatan Kolaka Utara -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3 text-xs">
                @foreach($districtStats as $ds)
                    <div id="districtCard-{{ $ds['id'] }}" class="p-3.5 rounded-xl bg-slate-50/90 border border-slate-200/80 hover:bg-white hover:border-blue-300 hover:shadow-sm transition flex flex-col justify-between space-y-3">
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

                        <div class="pt-1 flex items-center justify-between gap-1.5 flex-wrap">
                            <span id="coordsDisplay-{{ $ds['id'] }}" class="font-mono text-[10px] text-slate-500 font-semibold">
                                {{ number_format($ds['latitude'], 4) }}, {{ number_format($ds['longitude'], 4) }}
                            </span>
                            <div class="flex items-center gap-1.5">
                                <button type="button" onclick="focusDistrictOnAdminMap({{ $ds['latitude'] }}, {{ $ds['longitude'] }}, '{{ $ds['district'] }}')"
                                    class="px-2 py-1 rounded-lg bg-white border border-slate-200 hover:border-[#1E3A8A] text-[#1E3A8A] font-bold text-[10px] transition flex items-center gap-1 cursor-pointer">
                                    <i data-lucide="crosshair" class="w-3 h-3"></i>
                                    <span>Fokus Peta</span>
                                </button>
                                <button type="button" onclick="startPickingCoordinate({{ $ds['id'] }}, '{{ $ds['district'] }}', {{ $ds['latitude'] }}, {{ $ds['longitude'] }})"
                                    class="px-2.5 py-1 rounded-lg bg-[#1E3A8A] hover:bg-blue-900 text-white font-bold text-[10px] transition flex items-center gap-1 cursor-pointer shadow-2xs">
                                    <i data-lucide="map-pin" class="w-3 h-3"></i>
                                    <span>Atur Titik</span>
                                </button>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

    </div>

    <!-- Tab Panel 5: Manajemen Regu Lapangan & Mode Penugasan Sistem -->
    <div id="tabPanel-teams-management" class="admin-tab-panel {{ $activeTab === 'teams-management' ? '' : 'hidden' }} space-y-6">

        <!-- 1. KOTAK PENGATURAN MODE PENUGASAN SISTEM (HYBRID / OPERATOR / KLAIM MANDIRI) -->
        <div class="card-white rounded-3xl p-5 sm:p-6 bg-white border border-[#E2E8F0] shadow-2xs space-y-5">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-slate-100">
                <div class="flex items-center gap-3">
                    <div class="w-11 h-11 rounded-2xl bg-blue-50 text-[#1E3A8A] border border-blue-200 flex items-center justify-center shrink-0">
                        <i data-lucide="sliders" class="w-5 h-5"></i>
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <h3 class="font-outfit font-extrabold text-base sm:text-lg text-slate-900 leading-tight">
                                Mode Penugasan Regu Lapangan
                            </h3>
                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-extrabold border bg-slate-100 text-slate-800 border-slate-300">
                                AKTIF: {{ $dispatchMode ?? 'HYBRID' }}
                            </span>
                        </div>
                        <p class="text-xs text-slate-500 mt-0.5">
                            Atur bagaimana operator dan tim lapangan berkoordinasi dalam pembagian pekerjaan perbaikan infrastruktur fisik.
                        </p>
                    </div>
                </div>
            </div>

            <!-- Form Mode Penugasan -->
            <form id="dispatchModeForm" method="POST" action="{{ route('admin.settings.dispatch-mode') }}" onsubmit="handleDispatchModeSubmit(event)" class="space-y-4">
                @csrf
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <!-- Opsi 1: HYBRID (Default & Direkomendasikan) -->
                    <label class="relative flex flex-col justify-between p-4 rounded-2xl border-2 cursor-pointer transition {{ $dispatchMode === 'HYBRID' ? 'border-[#1E3A8A] bg-slate-50/70 shadow-xs' : 'border-slate-200 bg-white hover:border-slate-300' }}">
                        <div class="space-y-2">
                            <div class="flex items-center justify-between">
                                <span class="px-2 py-0.5 rounded-md bg-[#1E3A8A] text-white text-[10px] font-extrabold">
                                    DIREKOMENDASIKAN
                                </span>
                                <input type="radio" name="dispatch_mode" value="HYBRID" {{ $dispatchMode === 'HYBRID' ? 'checked' : '' }} class="w-4 h-4 text-[#1E3A8A] focus:ring-[#1E3A8A]">
                            </div>
                            <div class="flex items-center gap-2 pt-1">
                                <i data-lucide="git-merge" class="w-4 h-4 text-slate-800"></i>
                                <span class="font-outfit font-bold text-sm text-slate-900">Mode Hybrid</span>
                            </div>
                            <p class="text-[11px] text-slate-600 leading-relaxed">
                                Operator dapat menugaskan langsung ke regu tertentu (berdasarkan kecamatan/spesialisasi) <strong>ATAU</strong> membiarkan terbuka agar bisa diklaim mandiri oleh regu lapangan yang siap.
                            </p>
                        </div>
                        <div class="pt-3 border-t border-slate-200/60 mt-3 flex items-center gap-1.5 text-[10px] text-slate-600 font-semibold">
                            <i data-lucide="check" class="w-3.5 h-3.5 text-slate-800"></i>
                            <span>Fleksibilitas maksimal & pemerataan beban kerja</span>
                        </div>
                    </label>

                    <!-- Opsi 2: OPERATOR_ONLY -->
                    <label class="relative flex flex-col justify-between p-4 rounded-2xl border-2 cursor-pointer transition {{ $dispatchMode === 'OPERATOR_ONLY' ? 'border-[#1E3A8A] bg-slate-50/70 shadow-xs' : 'border-slate-200 bg-white hover:border-slate-300' }}">
                        <div class="space-y-2">
                            <div class="flex items-center justify-between">
                                <span class="px-2 py-0.5 rounded-md bg-slate-100 text-slate-600 text-[10px] font-extrabold">
                                    KOMANDO TERPUSAT
                                </span>
                                <input type="radio" name="dispatch_mode" value="OPERATOR_ONLY" {{ $dispatchMode === 'OPERATOR_ONLY' ? 'checked' : '' }} class="w-4 h-4 text-[#1E3A8A] focus:ring-[#1E3A8A]">
                            </div>
                            <div class="flex items-center gap-2 pt-1">
                                <i data-lucide="send" class="w-4 h-4 text-slate-800"></i>
                                <span class="font-outfit font-bold text-sm text-slate-900">Operator Saja</span>
                            </div>
                            <p class="text-[11px] text-slate-600 leading-relaxed">
                                Setiap laporan yang valid <strong>wajib ditunjuk regunya</strong> secara manual oleh Operator dinas sebelum regu bisa memulai pengerjaan fisik di lapangan.
                            </p>
                        </div>
                        <div class="pt-3 border-t border-slate-200/60 mt-3 flex items-center gap-1.5 text-[10px] text-slate-500 font-semibold">
                            <i data-lucide="shield" class="w-3.5 h-3.5 text-slate-600"></i>
                            <span>Kontrol ketat & terarah penuh</span>
                        </div>
                    </label>

                    <!-- Opsi 3: SELF_CLAIM_ONLY -->
                    <label class="relative flex flex-col justify-between p-4 rounded-2xl border-2 cursor-pointer transition {{ $dispatchMode === 'SELF_CLAIM_ONLY' ? 'border-[#1E3A8A] bg-slate-50/70 shadow-xs' : 'border-slate-200 bg-white hover:border-slate-300' }}">
                        <div class="space-y-2">
                            <div class="flex items-center justify-between">
                                <span class="px-2 py-0.5 rounded-md bg-slate-100 text-slate-600 text-[10px] font-extrabold">
                                    KLAIM BEBAS MANDIRI
                                </span>
                                <input type="radio" name="dispatch_mode" value="SELF_CLAIM_ONLY" {{ $dispatchMode === 'SELF_CLAIM_ONLY' ? 'checked' : '' }} class="w-4 h-4 text-[#1E3A8A] focus:ring-[#1E3A8A]">
                            </div>
                            <div class="flex items-center gap-2 pt-1">
                                <i data-lucide="hand" class="w-4 h-4 text-slate-800"></i>
                                <span class="font-outfit font-bold text-sm text-slate-900">Klaim Mandiri Saja</span>
                            </div>
                            <p class="text-[11px] text-slate-600 leading-relaxed">
                                Laporan yang terverifikasi langsung masuk ke daftar antrean terbuka. Regu lapangan secara mandiri berhak mengambil tugas sesuai kesiapan alat dan lokasi.
                            </p>
                        </div>
                        <div class="pt-3 border-t border-slate-200/60 mt-3 flex items-center gap-1.5 text-[10px] text-slate-500 font-semibold">
                            <i data-lucide="zap" class="w-3.5 h-3.5 text-slate-600"></i>
                            <span>Respon cepat tanpa tunggu disposisi</span>
                        </div>
                    </label>
                </div>

                <div class="flex justify-end pt-2">
                    <button type="submit" class="px-5 py-2.5 rounded-xl bg-[#1E3A8A] hover:bg-[#172e6e] text-white font-bold text-xs flex items-center gap-2 shadow-md shadow-[#1E3A8A]/20 transition cursor-pointer">
                        <i data-lucide="save" class="w-4 h-4"></i>
                        <span>Simpan Pengaturan Mode</span>
                    </button>
                </div>
            </form>
        </div>

        <!-- 2. BARIS RINGKASAN REGU & TOMBOL BENTUK REGU BARU -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div class="flex items-center gap-3 flex-wrap">
                <div class="px-4 py-2 rounded-2xl bg-white border border-slate-200 shadow-2xs flex items-center gap-2.5">
                    <i data-lucide="users" class="w-4 h-4 text-[#1E3A8A]"></i>
                    <span class="text-xs text-slate-600 font-medium">Total Regu:</span>
                    <strong class="text-xs font-outfit font-extrabold text-slate-900">{{ $teams->count() }} Regu</strong>
                </div>
                <div class="px-4 py-2 rounded-2xl bg-white border border-slate-200 shadow-2xs flex items-center gap-2.5">
                    <i data-lucide="hard-hat" class="w-4 h-4 text-[#1E3A8A]"></i>
                    <span class="text-xs text-slate-600 font-medium">Petugas Lapangan:</span>
                    <strong class="text-xs font-outfit font-extrabold text-slate-900">{{ $availableOfficers->count() }} Personil</strong>
                </div>
            </div>

            <div class="flex items-center gap-2.5 flex-wrap shrink-0">
                <!-- Toggle Tata Letak Daftar / Petak -->
                <div class="inline-flex items-center p-0.5 rounded-lg bg-white border border-[#E2E8F0] shrink-0" role="group" aria-label="Tata letak regu">
                    <button type="button" id="teamsLayoutBtn-list" onclick="setAdminTeamsLayout('list')"
                        class="px-2.5 py-1.5 rounded-md text-xs font-semibold flex items-center gap-1 transition cursor-pointer bg-[#1E3A8A] text-white">
                        <i data-lucide="list" class="w-3.5 h-3.5"></i>
                        <span>Daftar</span>
                    </button>
                    <button type="button" id="teamsLayoutBtn-grid" onclick="setAdminTeamsLayout('grid')"
                        class="px-2.5 py-1.5 rounded-md text-xs font-semibold flex items-center gap-1 transition cursor-pointer text-[#475569] hover:text-[#0F172A]">
                        <i data-lucide="layout-grid" class="w-3.5 h-3.5"></i>
                        <span>Petak</span>
                    </button>
                </div>

                <button type="button" onclick="openCreateTeamModal()"
                    class="px-5 py-2.5 rounded-xl bg-[#1E3A8A] hover:bg-[#172e6e] text-white font-bold text-xs flex items-center justify-center gap-2 shadow-md shadow-[#1E3A8A]/20 transition cursor-pointer shrink-0">
                    <i data-lucide="user-plus" class="w-4 h-4"></i>
                    <span>+ Bentuk Regu Lapangan Baru</span>
                </button>
            </div>
        </div>

        <!-- 3. DAFTAR SELURUH REGU LAPANGAN KABUPATEN -->
        <div id="adminTeamsTableWrap" class="bg-white rounded-2xl border border-[#E2E8F0] overflow-hidden">
            <div class="overflow-x-visible md:overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead class="hidden md:table-header-group">
                        <tr class="bg-slate-50 border-b border-[#E2E8F0] text-[10px] font-extrabold text-[#475569] uppercase tracking-wider">
                            <th class="px-4 py-3 whitespace-nowrap">KODE REGU</th>
                            <th class="px-4 py-3">NAMA REGU & WILAYAH</th>
                            <th class="px-4 py-3">KETUA REGU</th>
                            <th class="px-4 py-3 whitespace-nowrap">PERSONIL</th>
                            <th class="px-4 py-3 whitespace-nowrap">STATUS</th>
                            <th class="px-4 py-3 text-right whitespace-nowrap">AKSI</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#E2E8F0] text-xs">
                        @forelse($teams as $team)
                            @php
                                $memberCount = $team->members->count();
                                $isIdeal = ($memberCount >= 5 && $memberCount <= 10);
                                $badgeText = $isIdeal ? 'Ideal (5-10 Personil)' : ($memberCount < 5 ? 'Di bawah 5 Personil' : 'Regu Besar');
                            @endphp
                            <tr class="admin-team-row flex md:table-row items-center justify-between gap-2.5 px-3.5 py-3 md:p-0 hover:bg-slate-50/80 transition">
                                <td class="hidden md:table-cell px-4 py-3 whitespace-nowrap">
                                    <span class="px-2 py-0.5 rounded-md bg-[#1E3A8A] text-white font-mono text-[10px] font-bold">
                                        {{ $team->code }}
                                    </span>
                                </td>
                                <td class="block md:table-cell p-0 md:px-4 md:py-3 min-w-0 flex-1">
                                    <div class="md:hidden flex items-center gap-1.5 flex-wrap mb-1">
                                        <span class="px-1.5 py-0.5 rounded bg-[#1E3A8A] text-white font-mono text-[9.5px] font-bold">{{ $team->code }}</span>
                                        <span class="px-1.5 py-0.5 rounded-full text-[9.5px] font-bold border {{ $isIdeal ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : 'bg-amber-50 text-amber-700 border-amber-200' }}">{{ $memberCount }} Personil</span>
                                    </div>
                                    <div class="font-bold text-[#0F172A] truncate">{{ $team->name }}</div>
                                    <div class="text-[11px] text-[#475569] flex items-center gap-1 mt-0.5 truncate">
                                        <i data-lucide="map-pin" class="w-3 h-3 text-slate-400 shrink-0"></i>
                                        <span class="truncate">{{ $team->district ?? $team->coverage_area ?? 'Kecamatan Terkait' }}</span>
                                    </div>
                                </td>
                                <td class="hidden md:table-cell px-4 py-3 whitespace-nowrap">
                                    <div class="font-semibold text-[#0F172A]">{{ $team->leader?->name ?? 'Belum Ditunjuk' }}</div>
                                    @if($team->leader?->phone)
                                        <div class="text-[11px] text-[#475569] font-mono">{{ $team->leader->phone }}</div>
                                    @endif
                                </td>
                                <td class="hidden md:table-cell px-4 py-3 whitespace-nowrap">
                                    <span class="px-2.5 py-1 rounded-full text-[11px] font-bold bg-slate-100 text-slate-700 border border-[#E2E8F0]">
                                        {{ $memberCount }} Personil
                                    </span>
                                </td>
                                <td class="hidden md:table-cell px-4 py-3 whitespace-nowrap">
                                    <span class="px-2.5 py-1 rounded-full text-[11px] font-bold border {{ $isIdeal ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : 'bg-amber-50 text-amber-700 border-amber-200' }}">
                                        {{ $badgeText }}
                                    </span>
                                </td>
                                <td class="block md:table-cell p-0 md:px-4 md:py-3 text-right whitespace-nowrap shrink-0">
                                    <button type="button" onclick="setAdminTeamsLayout('grid'); document.getElementById('team-card-{{ $team->id }}')?.scrollIntoView({ behavior: 'smooth', block: 'center' });"
                                        class="px-2.5 py-1.5 rounded-lg border border-[#E2E8F0] bg-white hover:bg-slate-50 text-[#1E3A8A] text-[11px] font-bold inline-flex items-center gap-1 transition cursor-pointer">
                                        <i data-lucide="users" class="w-3.5 h-3.5"></i>
                                        <span>Kelola Personil</span>
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-4 py-10 text-center text-xs text-[#475569]">
                                    Belum ada regu lapangan yang dibentuk.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div id="adminTeamsGrid" class="hidden grid grid-cols-1 lg:grid-cols-2 gap-5">
            @forelse($teams as $team)
                @php
                    $memberCount = $team->members->count();
                    $badgeText = $memberCount >= 5 && $memberCount <= 10 
                        ? 'Ideal (5-10 Personil)' 
                        : ($memberCount < 5 ? 'Di bawah 5 Personil' : 'Regu Besar');
                @endphp
                <div id="team-card-{{ $team->id }}" class="admin-team-card card-white rounded-3xl p-5 bg-white border border-[#E2E8F0] hover:border-blue-300 transition space-y-4 flex flex-col justify-between">
                    <div class="space-y-3.5">
                        <!-- Top Header Regu -->
                        <div class="flex items-start justify-between gap-3">
                            <div class="flex items-center gap-3">
                                <div class="w-11 h-11 rounded-2xl bg-slate-100 text-slate-800 border border-slate-200 flex items-center justify-center font-extrabold text-sm shrink-0">
                                    <i data-lucide="hard-hat" class="w-5 h-5"></i>
                                </div>
                                <div>
                                    <div class="flex items-center gap-2 flex-wrap">
                                        <span class="px-2 py-0.5 rounded-md bg-[#1E3A8A] text-white font-mono text-[10px] font-extrabold">
                                            {{ $team->code }}
                                        </span>
                                        <h4 class="font-outfit font-extrabold text-base text-slate-900 leading-tight">
                                             {{ $team->name }}
                                        </h4>
                                    </div>
                                    <p class="text-xs text-slate-500 mt-0.5 flex items-center gap-1.5">
                                        <i data-lucide="map-pin" class="w-3.5 h-3.5 text-slate-400"></i>
                                        <span>Wilayah: <strong>{{ $team->district ?? $team->coverage_area ?? 'Kecamatan Terkait' }}</strong></span>
                                    </p>
                                </div>
                            </div>
                            <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-slate-100 text-slate-800 border border-slate-200 shrink-0">
                                {{ $memberCount }} Personil
                            </span>
                        </div>

                        <!-- Card Ketua Regu (Koordinator Lapangan) -->
                        <div class="p-3.5 rounded-2xl bg-slate-50 border border-slate-200/90 flex items-center justify-between gap-3">
                            <div class="flex items-center gap-3">
                                <div class="w-9 h-9 rounded-xl bg-[#1E3A8A] text-white flex items-center justify-center text-xs font-bold shrink-0 shadow-2xs">
                                    <i data-lucide="crown" class="w-4 h-4 text-white"></i>
                                </div>
                                <div>
                                    <span class="text-[10px] text-slate-500 font-extrabold uppercase tracking-wider block">Ketua Regu (Koordinator Lapangan):</span>
                                    <span class="font-outfit font-extrabold text-xs sm:text-sm text-slate-900">
                                        {{ $team->leader?->name ?? 'Belum Ditunjuk' }}
                                    </span>
                                    @if($team->leader?->phone)
                                        <div class="flex items-center gap-1.5 text-[11px] text-slate-600 mt-0.5">
                                            <i data-lucide="phone" class="w-3 h-3 text-slate-400"></i>
                                            <span class="font-mono">{{ $team->leader->phone }}</span>
                                        </div>
                                    @endif
                                </div>
                            </div>
                            <span class="px-2 py-0.5 rounded-md bg-white border border-slate-200 text-slate-700 text-[10px] font-bold">
                                Penanggung Jawab
                            </span>
                        </div>

                        <!-- Daftar Anggota Tim (Pills & Aksi) -->
                        <div class="space-y-2">
                            <div class="flex items-center justify-between text-xs font-bold text-slate-700">
                                <span class="flex items-center gap-1.5">
                                    <i data-lucide="users" class="w-3.5 h-3.5 text-[#1E3A8A]"></i>
                                    <span>Personil Regu</span>
                                </span>
                                <span class="text-[11px] text-slate-500 font-normal">
                                    Status: <strong class="text-slate-800 font-bold">{{ $badgeText }}</strong>
                                </span>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                                @forelse($team->members as $member)
                                    @php
                                        $isLeader = ($member->role === 'LEADER' || $member->role_in_team === 'LEADER');
                                    @endphp
                                    <div class="p-2.5 rounded-xl border border-slate-200/90 bg-white hover:border-slate-300 transition flex items-center justify-between gap-2 shadow-2xs">
                                        <div class="flex items-center gap-2.5 min-w-0">
                                            <div class="w-7 h-7 rounded-lg {{ $isLeader ? 'bg-[#1E3A8A] text-white font-extrabold' : 'bg-slate-100 text-slate-700 font-bold border border-slate-200' }} flex items-center justify-center text-[11px] shrink-0">
                                                {{ strtoupper(substr($member->user?->name ?? 'P', 0, 1)) }}
                                            </div>
                                            <div class="min-w-0">
                                                <div class="text-xs font-bold text-slate-900 truncate">
                                                    {{ $member->user?->name ?? 'Petugas' }}
                                                </div>
                                                <div class="text-[10px] text-slate-500 font-mono truncate">
                                                    {{ $member->user?->phone ?? '-' }}
                                                </div>
                                            </div>
                                        </div>

                                        <div class="flex items-center gap-1 shrink-0">
                                            @if($isLeader)
                                                <span class="flex items-center text-amber-500 mr-1" title="Koordinator Lapangan">
                                                    <i data-lucide="crown" class="w-4 h-4"></i>
                                                </span>
                                            @else
                                                <!-- Jadikan Ketua -->
                                                <form method="POST" action="{{ route('admin.teams.leader.set', [$team->id, $member->user_id]) }}" class="inline">
                                                    @csrf
                                                    <button type="submit" title="Jadikan Ketua Regu" class="p-1 rounded-md text-slate-400 hover:text-slate-800 hover:bg-slate-100 transition cursor-pointer">
                                                        <i data-lucide="crown" class="w-3.5 h-3.5"></i>
                                                    </button>
                                                </form>
                                                <!-- Keluarkan dari Regu (Modal Cantik) -->
                                                <button type="button" 
                                                    onclick="openRemoveMemberModal({
                                                        teamId: {{ $team->id }},
                                                        memberId: {{ $member->user_id }},
                                                        memberName: '{{ addslashes($member->user?->name ?? 'Petugas') }}',
                                                        teamName: '{{ addslashes($team->name) }}'
                                                    })"
                                                    title="Keluarkan dari Regu" 
                                                    class="p-1 rounded-md text-slate-400 hover:text-red-600 hover:bg-red-50 transition cursor-pointer">
                                                    <i data-lucide="user-minus" class="w-3.5 h-3.5"></i>
                                                </button>
                                            @endif
                                        </div>
                                    </div>
                                @empty
                                    <div class="col-span-2 text-center py-3 text-xs text-slate-400 italic bg-slate-50 rounded-xl border border-dashed border-slate-200">
                                        Belum ada personil yang dimasukkan ke regu ini.
                                    </div>
                                @endforelse
                            </div>
                        </div>

                        <!-- Form Tambah Personil ke Regu Ini -->
                        <form method="POST" action="{{ route('admin.teams.members.add', $team->id) }}" class="p-3 rounded-2xl bg-slate-100/80 border border-slate-200/80 flex flex-col sm:flex-row items-center gap-2">
                            @csrf
                            <div class="w-full sm:flex-1">
                                <select name="user_id" required class="w-full text-xs rounded-xl border border-slate-300 p-2 bg-white focus:ring-2 focus:ring-[#1E3A8A]">
                                    @if($unassignedOfficers->isEmpty())
                                        <option value="" disabled selected>Semua Petugas Sudah Masuk Regu</option>
                                    @else
                                        <option value="" disabled selected>Pilih Petugas Lapangan (Belum Ada Regu)...</option>
                                        @foreach($unassignedOfficers as $officer)
                                            <option value="{{ $officer->id }}">{{ $officer->name }} ({{ $officer->phone ?? 'Tanpa No. HP' }})</option>
                                        @endforeach
                                    @endif
                                </select>
                            </div>
                            <input type="hidden" name="role" value="MEMBER">
                            <button type="submit" @if($unassignedOfficers->isEmpty()) disabled class="w-full sm:w-auto px-4 py-2 rounded-xl bg-slate-300 text-slate-500 font-bold text-xs flex items-center justify-center gap-1.5 shrink-0 cursor-not-allowed" @else class="w-full sm:w-auto px-4 py-2 rounded-xl bg-[#1E3A8A] hover:bg-[#172e6e] text-white font-bold text-xs flex items-center justify-center gap-1.5 transition shrink-0 cursor-pointer shadow-2xs" @endif>
                                <i data-lucide="plus" class="w-3.5 h-3.5"></i>
                                <span>Tambah Personil</span>
                            </button>
                        </form>
                    </div>

                    <!-- Footer Kartu Regu (Aksi Edit & Hapus) -->
                    <div class="pt-3 border-t border-slate-100 flex items-center justify-end text-xs">
                        <div class="flex items-center gap-2">
                            <button type="button" 
                                onclick="openEditTeamModal({
                                    id: {{ $team->id }},
                                    name: '{{ addslashes($team->name) }}',
                                    code: '{{ $team->code }}',
                                    coverage_area: '{{ addslashes($team->district ?? $team->coverage_area ?? '') }}',
                                    leader_id: '{{ $team->leader_id ?? '' }}',
                                    leader_name: '{{ addslashes($team->leader?->name ?? '') }}',
                                    leader_phone: '{{ $team->leader?->phone ?? '' }}'
                                })"
                                class="px-3 py-1.5 rounded-lg border border-slate-200 hover:border-slate-400 text-slate-700 hover:text-slate-900 font-bold text-xs transition flex items-center gap-1 cursor-pointer">
                                <i data-lucide="pencil" class="w-3.5 h-3.5"></i>
                                <span>Ubah Regu</span>
                            </button>
                            <!-- Tombol Hapus Regu (Modal Kustom Cantik - Tetap Merah Elegan) -->
                            <button type="button" 
                                onclick="openDeleteTeamModal({
                                    id: {{ $team->id }},
                                    name: '{{ addslashes($team->name) }}',
                                    code: '{{ $team->code }}',
                                    district: '{{ addslashes($team->district ?? $team->coverage_area ?? 'Kecamatan Terkait') }}',
                                    memberCount: {{ $team->members->count() }}
                                })"
                                class="px-3 py-1.5 rounded-lg bg-red-50 hover:bg-red-100 text-red-700 border border-red-200 font-bold text-xs transition flex items-center gap-1 cursor-pointer">
                                <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                                <span>Hapus</span>
                            </button>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-span-2 text-center py-12 bg-white rounded-3xl border border-dashed border-slate-300 p-6 space-y-3">
                    <div class="w-12 h-12 rounded-2xl bg-blue-50 text-[#1E3A8A] flex items-center justify-center mx-auto">
                        <i data-lucide="users-2" class="w-6 h-6"></i>
                    </div>
                    <h4 class="font-outfit font-extrabold text-base text-slate-900">Belum Ada Regu Lapangan</h4>
                    <p class="text-xs text-slate-500 max-w-md mx-auto">
                        Bentuk regu lapangan (5–10 personil per regu) untuk penanganan terkoordinasi pekerjaan fisik infrastruktur jalan.
                    </p>
                    <button type="button" onclick="openCreateTeamModal()" class="px-4 py-2 rounded-xl bg-[#1E3A8A] text-white text-xs font-bold transition">
                        + Bentuk Regu Pertama
                    </button>
                </div>
            @endforelse
        </div>

        <!-- Pagination Bar Regu & Tugas (5 / 10 / 50 per halaman, default 5) -->
        <div id="teamsPaginationBar" class="rounded-2xl bg-white border border-[#E2E8F0] px-4 py-3 flex flex-col sm:flex-row sm:items-center justify-between gap-3 text-xs">
            <div class="flex flex-wrap items-center gap-3 text-[#475569]">
                <div class="flex items-center gap-2">
                    <span class="font-medium">Tampilkan:</span>
                    <select id="teamsPageSizeSelect" onchange="changeTeamsPageSize(this.value)" aria-label="Jumlah regu per halaman"
                        class="text-xs font-semibold rounded-lg border border-[#E2E8F0] bg-[#F8FAFC] text-[#0F172A] px-2.5 py-1.5 focus:outline-none focus:border-[#1E3A8A] cursor-pointer">
                        <option value="5" selected>5 regu</option>
                        <option value="10">10 regu</option>
                        <option value="50">50 regu</option>
                    </select>
                </div>
                <span id="teamsPageInfo" class="font-medium">Menampilkan 0–0 dari 0 regu</span>
            </div>
            <div id="teamsPageControls" class="flex items-center gap-1.5 flex-wrap"></div>
        </div>

    </div>

    <!-- Tab Panel 6: Pelaporan Resmi & Monitoring Eksekutif -->
    <div id="tabPanel-reporting" class="admin-tab-panel {{ $activeTab === 'reporting' ? '' : 'hidden' }} space-y-6">
        @include('partials.executive_monitoring_reporting')
    </div>

@push('modals')
    <!-- MODAL KUSTOM KONFIRMASI HAPUS LAPORAN -->
    <div id="deleteComplaintModal" class="fixed inset-0 z-[99999] hidden items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs transition-opacity duration-200">
        <div class="bg-white rounded-2xl max-w-md w-full shadow-2xl border border-slate-100 overflow-hidden transform transition-all scale-95 opacity-0 duration-200" id="deleteModalContent">
            <div class="p-6 text-center space-y-4">
                <div class="w-14 h-14 rounded-2xl bg-red-50 border border-red-200 text-red-600 flex items-center justify-center mx-auto shadow-inner">
                    <i data-lucide="alert-triangle" class="w-7 h-7"></i>
                </div>
                <div>
                    <h3 class="font-outfit font-extrabold text-base sm:text-lg text-slate-900">
                        Konfirmasi Hapus Laporan Permanen
                    </h3>
                    <p class="text-xs text-slate-500 mt-1">
                        Apakah Anda yakin ingin menghapus laporan <strong id="deleteModalComplaintCode" class="text-red-600 font-mono">#ADU-0001</strong>?
                    </p>
                    <div class="mt-2.5 p-2.5 rounded-xl bg-slate-50 border border-slate-200 text-[11px] text-slate-600 text-left italic truncate" id="deleteModalComplaintTitle">
                        -
                    </div>
                </div>
                <div class="p-3 rounded-xl bg-amber-50 border border-amber-200 text-amber-800 text-[11px] text-left flex items-start gap-2">
                    <i data-lucide="info" class="w-4 h-4 text-amber-600 shrink-0 mt-0.5"></i>
                    <span>Tindakan ini tidak dapat dibatalkan. Seluruh data foto kerusakan, disposisi, dan riwayat status akan dihapus secara permanen dari server.</span>
                </div>
                <div class="flex items-center justify-end gap-2.5 pt-2">
                    <button type="button" onclick="closeDeleteComplaintModal()"
                        class="px-4 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs transition cursor-pointer">
                        Batal
                    </button>
                    <form id="deleteComplaintForm" method="POST" action="">
                        @csrf
                        @method('DELETE')
                        <button type="submit"
                            class="px-4 py-2.5 rounded-xl bg-red-600 hover:bg-red-700 text-white font-bold text-xs transition flex items-center gap-1.5 shadow-md shadow-red-200 cursor-pointer">
                            <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                            <span>Ya, Hapus Permanen</span>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- MODAL DETAIL LENGKAP PENGADUAN SUPER ADMIN (FILE PARTIAL KHUSUS) -->
    @include('partials.modals.admin_complaint_detail_modal')


    <!-- MODAL EDIT PROFIL & KATA SANDI PENGGUNA (SUPER ADMIN) -->
    <div id="editUserModal" class="fixed inset-0 z-[99999] hidden items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs transition-opacity duration-200">
        <div class="bg-white rounded-3xl max-w-lg w-full shadow-2xl border border-slate-100 overflow-hidden transform transition-all scale-95 opacity-0 duration-200" id="editUserModalContent">
            <div class="p-6 space-y-5">
                <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                    <div class="flex items-center gap-2.5">
                        <div class="w-10 h-10 rounded-xl bg-blue-50 text-[#1E3A8A] flex items-center justify-center font-bold">
                            <i data-lucide="user-cog" class="w-5 h-5"></i>
                        </div>
                        <div>
                            <h3 class="font-outfit font-extrabold text-base text-slate-900">
                                Edit Akun & Profil Pengguna
                            </h3>
                            <p class="text-xs text-slate-400">
                                Kelola identitas, nomor telepon, peran sistem, dan kata sandi
                            </p>
                        </div>
                    </div>
                    <button type="button" onclick="closeEditUserModal()" class="p-2 rounded-xl text-slate-400 hover:text-slate-600 hover:bg-slate-100 transition cursor-pointer">
                        <i data-lucide="x" class="w-4 h-4"></i>
                    </button>
                </div>

                <form id="editUserForm" method="POST" action="" class="space-y-4">
                    @csrf
                    <input type="hidden" name="_method" value="PUT">
                    <input type="hidden" id="editUserId" name="id" value="">

                    <div class="space-y-3">
                        <!-- Nama Lengkap -->
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Nama Lengkap</label>
                            <input type="text" id="editUserName" name="name" required
                                class="w-full text-xs font-medium px-3 py-2.5 rounded-xl border border-slate-200 bg-slate-50 focus:bg-white focus:border-[#1E3A8A] focus:ring-1 focus:ring-[#1E3A8A] transition">
                        </div>

                        <!-- Email & Nomor Handphone (Grid 2 kolom) -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">Alamat Email</label>
                                <input type="email" id="editUserEmail" name="email" required
                                    class="w-full text-xs font-medium px-3 py-2.5 rounded-xl border border-slate-200 bg-slate-50 focus:bg-white focus:border-[#1E3A8A] focus:ring-1 focus:ring-[#1E3A8A] transition">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">No. Handphone / WA</label>
                                <input type="text" id="editUserPhone" name="phone" placeholder="08xxxxxxxxxx"
                                    class="w-full text-xs font-medium px-3 py-2.5 rounded-xl border border-slate-200 bg-slate-50 focus:bg-white focus:border-[#1E3A8A] focus:ring-1 focus:ring-[#1E3A8A] transition">
                            </div>
                        </div>

                        <!-- Peran Akun (Dikunci) -->
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Peran / Role Pengguna</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                                    <i data-lucide="shield" class="w-4 h-4"></i>
                                </div>
                                <select id="editUserRole" disabled
                                    class="w-full text-xs font-bold pl-9 pr-8 py-2.5 rounded-xl border border-slate-200 bg-slate-100/90 text-slate-600 cursor-not-allowed select-none opacity-90 appearance-none">
                                    <option value="ADMIN">Administrator (Super Admin)</option>
                                    <option value="KEPALA_DAERAH">Kepala Daerah Kabupaten</option>
                                    <option value="OPERATOR">Operator Verifikasi</option>
                                    <option value="PETUGAS">Pekerja Lapangan (PUPR)</option>
                                    <option value="WARGA" id="editUserOptionWarga" class="hidden">Warga Terdaftar</option>
                                </select>
                                <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none text-slate-400">
                                    <i data-lucide="lock" class="w-3.5 h-3.5"></i>
                                </div>
                            </div>
                            <input type="hidden" id="editUserRoleHidden" name="role" value="">
                            <p class="text-[10.5px] text-slate-400 mt-1 flex items-center gap-1">
                                <i data-lucide="info" class="w-3 h-3 text-slate-400 shrink-0"></i>
                                <span>Peran akun telah dikunci demi integritas keamanan & hak akses sistem (RBAC).</span>
                            </p>
                        </div>

                        <!-- Reset Kata Sandi Default -->
                        <div class="pt-3 border-t border-slate-100">
                            <label class="flex items-center gap-2.5 p-3 rounded-xl border border-slate-200 bg-slate-50 hover:bg-slate-100/80 transition cursor-pointer select-none">
                                <input type="checkbox" id="editUserResetDefault" name="reset_default" value="1"
                                    class="w-4 h-4 rounded text-[#1E3A8A] focus:ring-[#1E3A8A] border-slate-300">
                                <span class="text-xs text-slate-800 font-bold block">
                                    Reset Kata Sandi ke Default (<span class="font-mono text-[#1E3A8A]">password</span>)
                                </span>
                            </label>
                        </div>
                    </div>

                    <div class="flex items-center justify-end gap-2.5 pt-3 border-t border-slate-100">
                        <button type="button" onclick="closeEditUserModal()"
                            class="px-4 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs transition cursor-pointer">
                            Batal
                        </button>
                        <button type="submit"
                            class="px-5 py-2.5 rounded-xl bg-[#1E3A8A] hover:bg-blue-900 text-white font-bold text-xs transition flex items-center gap-1.5 shadow-md shadow-blue-900/20 cursor-pointer">
                            <i data-lucide="save" class="w-3.5 h-3.5"></i>
                            <span>Simpan Perubahan</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- MODAL PROTEKSI SESAMA AKUN PETINGGI (RE-AUTHENTICATION SUPER ADMIN) -->
    <div id="confirmDeleteLeaderModal" class="fixed inset-0 z-[99999] hidden items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs transition-opacity duration-200">
        <div id="confirmDeleteLeaderCard" class="bg-white rounded-3xl max-w-md w-full shadow-2xl border border-slate-100 overflow-hidden transform scale-95 opacity-0 transition-all duration-200">
            <div class="p-6 space-y-4">
                <div class="flex items-center gap-3 pb-3 border-b border-slate-100">
                    <div class="w-11 h-11 rounded-2xl bg-red-50 text-red-600 border border-red-200 flex items-center justify-center font-bold shrink-0">
                        <i data-lucide="shield-alert" class="w-6 h-6"></i>
                    </div>
                    <div>
                        <h3 class="font-outfit font-extrabold text-base text-slate-900">
                            Konfirmasi Keamanan Petinggi
                        </h3>
                        <p class="text-[11px] text-slate-500">
                            Proteksi Hak Akses Pimpinan & Sesama Administrator
                        </p>
                    </div>
                </div>

                <div class="p-3.5 rounded-xl bg-amber-50 border border-amber-200 text-amber-800 text-xs space-y-1">
                    <p class="font-bold flex items-center gap-1.5">
                        <i data-lucide="alert-triangle" class="w-4 h-4 text-amber-600 shrink-0"></i>
                        <span>Peringatan Dampak Operasional:</span>
                    </p>
                    <p class="text-[11px] leading-relaxed">
                        Anda akan menghapus akun petinggi: <strong id="deleteLeaderTargetName" class="text-slate-900"></strong> (<span id="deleteLeaderTargetRole" class="font-bold text-red-600"></span>).
                        Tindakan ini memerlukan verifikasi ulang kata sandi Super Admin Anda yang sedang bertugas.
                    </p>
                </div>

                <form id="confirmDeleteLeaderForm" method="POST" action="" class="space-y-4">
                    @csrf
                    @method('DELETE')
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">
                            Masukkan Kata Sandi Anda Saat Ini <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <i data-lucide="lock" class="w-4 h-4 text-slate-400 absolute left-3 top-1/2 -translate-y-1/2 pointer-events-none"></i>
                            <input type="password" name="admin_password" required placeholder="Kata sandi Super Admin Anda"
                                class="w-full pl-9 pr-3 py-2.5 text-xs rounded-xl border border-slate-300 focus:border-red-600 focus:ring-1 focus:ring-red-600 transition">
                        </div>
                        <p class="text-[10px] text-slate-400 mt-1">Konfirmasi sandi diperlukan demi mencegah penghapusan sepihak / penyalahgunaan akun.</p>
                    </div>

                    <div class="flex items-center justify-end gap-2.5 pt-3 border-t border-slate-100">
                        <button type="button" onclick="closeConfirmDeleteLeaderModal()"
                            class="px-4 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs transition cursor-pointer">
                            Batal
                        </button>
                        <button type="submit"
                            class="px-5 py-2.5 rounded-xl bg-red-600 hover:bg-red-700 text-white font-bold text-xs transition flex items-center gap-1.5 shadow-md shadow-red-600/20 cursor-pointer">
                            <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                            <span>Konfirmasi & Hapus Akun</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- MODAL KONFIRMASI HAPUS PENGGUNA (GAYA KONSISTEN SESUAI GAMBAR 4) -->
    <div id="deleteUserConfirmModal" class="fixed inset-0 z-[99999] hidden items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs transition-opacity duration-200">
        <div id="deleteUserConfirmCard" class="bg-white rounded-3xl max-w-md w-full shadow-2xl border border-slate-100 overflow-hidden transform scale-95 opacity-0 transition-all duration-200 p-6 sm:p-7 text-center space-y-4">
            <!-- Icon Segitiga Merah Lingkaran Lembut -->
            <div class="w-16 h-16 rounded-full bg-red-50 text-red-500 border border-red-100 mx-auto flex items-center justify-center shadow-xs">
                <i data-lucide="alert-triangle" class="w-8 h-8"></i>
            </div>

            <!-- Header Text -->
            <div class="space-y-1.5">
                <h3 class="font-outfit font-extrabold text-lg sm:text-xl text-slate-900 leading-tight">
                    Konfirmasi Hapus Akun Pengguna
                </h3>
                <p class="text-xs text-slate-500">
                    Apakah Anda yakin ingin menghapus akun <span id="deleteUserNameTarget" class="font-bold text-red-600"></span>?
                </p>
            </div>

            <!-- Detail Akun Target Box -->
            <div class="p-3.5 rounded-2xl bg-slate-50 border border-slate-200 text-left space-y-1.5">
                <div class="flex items-center justify-between text-xs">
                    <span class="text-slate-400 text-[11px]">Email Akun:</span>
                    <span id="deleteUserEmailTarget" class="font-mono text-slate-700 font-semibold text-[11.5px]"></span>
                </div>
                <div class="flex items-center justify-between text-xs pt-1 border-t border-slate-100">
                    <span class="text-slate-400 text-[11px]">Peran Pengguna:</span>
                    <span id="deleteUserRoleTarget" class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-slate-200 text-slate-700"></span>
                </div>
            </div>

            <!-- Notice Box Peringatan Amber persis Gambar 4 -->
            <div class="p-3.5 rounded-2xl bg-amber-50/90 border border-amber-200/90 text-left flex items-start gap-2.5">
                <i data-lucide="info" class="w-4 h-4 text-amber-600 shrink-0 mt-0.5"></i>
                <p class="text-[11.5px] text-amber-900 leading-relaxed font-normal">
                    Tindakan ini tidak dapat dibatalkan. Seluruh data profil, penugasan, dan riwayat aktivitas akun ini akan dihapus secara permanen dari server.
                </p>
            </div>

            <!-- Actions Buttons -->
            <div class="flex items-center justify-end gap-2.5 pt-2">
                <button type="button" onclick="closeConfirmDeleteUserModal()"
                    class="px-5 py-2.5 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 font-bold text-xs transition cursor-pointer">
                    Batal
                </button>
                <form id="deleteUserFormAction" method="POST" action="">
                    @csrf
                    @method('DELETE')
                    <button type="submit"
                        class="px-5 py-2.5 rounded-xl bg-red-600 hover:bg-red-700 text-white font-bold text-xs transition flex items-center gap-1.5 shadow-md shadow-red-600/20 cursor-pointer">
                        <i data-lucide="trash-2" class="w-4 h-4"></i>
                        <span>Ya, Hapus Permanen</span>
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- MODAL QUICK ACTION RESET PASSWORD (SIMPEL, CANTIK, & KONSISTEN) -->
    <div id="quickResetPasswordModal" class="fixed inset-0 z-[99999] hidden items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs transition-opacity duration-200">
        <div id="quickResetPasswordCard" class="bg-white rounded-3xl max-w-md w-full shadow-2xl border border-slate-100 overflow-hidden transform scale-95 opacity-0 transition-all duration-200 p-6 sm:p-7 text-center space-y-4">
            <!-- Icon Kunci Lingkaran Biru Lembut -->
            <div class="w-16 h-16 rounded-full bg-blue-50 text-[#1E3A8A] border border-blue-100 mx-auto flex items-center justify-center shadow-xs">
                <i data-lucide="key" class="w-8 h-8"></i>
            </div>

            <!-- Header Text -->
            <div class="space-y-1.5">
                <h3 class="font-outfit font-extrabold text-lg sm:text-xl text-slate-900 leading-tight">
                    Reset Kata Sandi Akun
                </h3>
                <p class="text-xs text-slate-500">
                    Setel ulang kata sandi akun <strong id="resetUserNameTarget" class="text-slate-900"></strong> ke kata sandi bawaan default.
                </p>
            </div>

            <!-- Detail Akun Target Box -->
            <div class="p-3.5 rounded-2xl bg-blue-50/50 border border-blue-100/80 text-left space-y-1.5">
                <div class="flex items-center justify-between text-xs">
                    <span class="text-slate-500 text-[11px]">Email Akun:</span>
                    <span id="resetUserEmailTarget" class="font-mono text-slate-800 font-semibold text-[11.5px]"></span>
                </div>
                <div class="flex items-center justify-between text-xs pt-1 border-t border-blue-100/60">
                    <span class="text-slate-500 text-[11px]">Kata Sandi Baru:</span>
                    <span class="font-mono font-extrabold text-[#1E3A8A] text-xs bg-white px-2 py-0.5 rounded border border-blue-200 shadow-2xs">password</span>
                </div>
            </div>

            <!-- Notice Box Email Info -->
            <div class="p-3.5 rounded-2xl bg-slate-50 border border-slate-200 text-left flex items-start gap-2.5">
                <i data-lucide="mail-check" class="w-4 h-4 text-[#1E3A8A] shrink-0 mt-0.5"></i>
                <p class="text-[11.5px] text-slate-600 leading-relaxed font-normal">
                    Email pemberitahuan resmi akan <strong>otomatis dikirimkan</strong> ke alamat email pengguna tersebut untuk memberitahukan reset kata sandi ini.
                </p>
            </div>

            <!-- Actions Buttons -->
            <div class="flex items-center justify-end gap-2.5 pt-2">
                <button type="button" onclick="closeQuickResetModal()"
                    class="px-5 py-2.5 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 font-bold text-xs transition cursor-pointer">
                    Batal
                </button>
                <form id="quickResetFormAction" method="POST" action="">
                    @csrf
                    <button type="submit"
                        class="px-5 py-2.5 rounded-xl bg-[#1E3A8A] hover:bg-blue-900 text-white font-bold text-xs transition flex items-center gap-1.5 shadow-md shadow-blue-900/20 cursor-pointer">
                        <i data-lucide="key" class="w-4 h-4"></i>
                        <span>Ya, Reset Kata Sandi</span>
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Custom Modern Notification Modal (Pengganti Browser alert) -->
    <div id="adminNoticeModal" class="fixed inset-0 z-[99999] hidden items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs transition-opacity duration-200">
        <div id="adminNoticeCard" class="bg-white rounded-3xl shadow-2xl border border-slate-100 max-w-sm w-full p-6 text-center transform scale-95 opacity-0 transition-all duration-200 space-y-4">
            <div id="adminNoticeIconBox" class="w-14 h-14 rounded-2xl bg-emerald-50 text-emerald-600 border border-emerald-100 mx-auto flex items-center justify-center shadow-xs">
                <i id="adminNoticeIcon" data-lucide="check-circle-2" class="w-7 h-7"></i>
            </div>
            <div class="space-y-1.5">
                <h3 id="adminNoticeTitle" class="font-outfit font-extrabold text-base sm:text-lg text-slate-900 leading-tight">
                    Berhasil Disimpan
                </h3>
                <p id="adminNoticeMessage" class="text-xs sm:text-sm text-slate-600 leading-relaxed break-words">
                    Titik koordinat kecamatan berhasil diperbarui.
                </p>
            </div>
            <div class="pt-1">
                <button type="button" onclick="closeAdminNoticeModal()" 
                    class="w-full py-2.5 px-4 rounded-xl bg-[#1E3A8A] hover:bg-blue-900 text-white font-bold text-xs sm:text-sm shadow-md shadow-blue-900/20 transition cursor-pointer">
                    Mengerti
                </button>
            </div>
        </div>
    </div>

    <!-- MODAL BENTUK REGU BARU (SUPER ADMIN) -->
    <div id="createTeamModal" class="fixed inset-0 z-[99999] hidden items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs transition-opacity duration-200">
        <div id="createTeamCard" class="bg-white rounded-3xl max-w-lg w-full shadow-2xl border border-slate-100 overflow-hidden transform scale-95 opacity-0 transition-all duration-200">
            <div class="p-6 space-y-5">
                <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                    <div class="flex items-center gap-2.5">
                        <div class="w-10 h-10 rounded-xl bg-blue-50 text-[#1E3A8A] flex items-center justify-center font-bold">
                            <i data-lucide="users-2" class="w-5 h-5"></i>
                        </div>
                        <div>
                            <h3 class="font-outfit font-extrabold text-base text-slate-900">
                                Bentuk Regu Lapangan Baru
                            </h3>
                            <p class="text-xs text-slate-400">
                                Tetapkan nama regu, kode, dinas, dan wilayah kerja (5–10 personil)
                            </p>
                        </div>
                    </div>
                    <button type="button" onclick="closeCreateTeamModal()" class="p-2 rounded-xl text-slate-400 hover:text-slate-600 hover:bg-slate-100 transition cursor-pointer">
                        <i data-lucide="x" class="w-4 h-4"></i>
                    </button>
                </div>

                <form method="POST" action="{{ route('admin.teams.store') }}" class="space-y-4">
                    @csrf
                    <div class="space-y-3">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Nama Regu Lapangan <span class="text-red-500">*</span></label>
                            <input type="text" name="name" required placeholder="Contoh: Regu Aspal 02 - Ngapa"
                                class="w-full text-xs font-medium px-3 py-2.5 rounded-xl border border-slate-200 bg-slate-50 focus:bg-white focus:border-[#1E3A8A] focus:ring-1 focus:ring-[#1E3A8A] transition">
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">Kode Regu <span class="text-slate-400 font-normal">(Opsional)</span></label>
                                <input type="text" name="code" placeholder="Misal: REGU-03"
                                    class="w-full text-xs font-medium px-3 py-2.5 rounded-xl border border-slate-200 bg-slate-50 focus:bg-white focus:border-[#1E3A8A] focus:ring-1 focus:ring-[#1E3A8A] transition font-mono uppercase">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">Dinas Teknis Terkait</label>
                                <select name="department_id" class="w-full text-xs font-medium px-3 py-2.5 rounded-xl border border-slate-200 bg-slate-50 focus:bg-white focus:border-[#1E3A8A] focus:ring-1 focus:ring-[#1E3A8A] transition">
                                    <option value="">Pilih Dinas...</option>
                                    @foreach($departments as $dept)
                                        <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Wilayah Cakupan Kerja / Kecamatan</label>
                            <select name="coverage_area" class="w-full text-xs font-medium px-3 py-2.5 rounded-xl border border-slate-200 bg-slate-50 focus:bg-white focus:border-[#1E3A8A] focus:ring-1 focus:ring-[#1E3A8A] transition">
                                <option value="Semua Wilayah">Semua Wilayah (Kabupaten Kolaka Utara)</option>
                                @foreach($districts as $d)
                                    <option value="{{ $d }}">{{ $d }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Ketua Regu (Koordinator Lapangan)</label>
                            <select name="leader_id" class="w-full text-xs font-medium px-3 py-2.5 rounded-xl border border-slate-200 bg-slate-50 focus:bg-white focus:border-[#1E3A8A] focus:ring-1 focus:ring-[#1E3A8A] transition">
                                <option value="">Tentukan Nanti / Belum Ada</option>
                                @foreach($unassignedOfficers as $officer)
                                    <option value="{{ $officer->id }}">{{ $officer->name }} ({{ $officer->phone ?? '-' }})</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="flex items-center justify-end gap-2.5 pt-3 border-t border-slate-100">
                        <button type="button" onclick="closeCreateTeamModal()"
                            class="px-4 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs transition cursor-pointer">
                            Batal
                        </button>
                        <button type="submit"
                            class="px-5 py-2.5 rounded-xl bg-[#1E3A8A] hover:bg-[#172e6e] text-white font-bold text-xs transition flex items-center gap-1.5 shadow-md shadow-[#1E3A8A]/20 cursor-pointer">
                            <i data-lucide="check" class="w-3.5 h-3.5"></i>
                            <span>Bentuk Regu Sekarang</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- MODAL EDIT REGU (SUPER ADMIN) -->
    <div id="editTeamModal" class="fixed inset-0 z-[99999] hidden items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs transition-opacity duration-200">
        <div id="editTeamCard" class="bg-white rounded-3xl max-w-lg w-full shadow-2xl border border-slate-100 overflow-hidden transform scale-95 opacity-0 transition-all duration-200">
            <div class="p-6 space-y-5">
                <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                    <div class="flex items-center gap-2.5">
                        <div class="w-10 h-10 rounded-xl bg-blue-50 text-[#1E3A8A] flex items-center justify-center font-bold">
                            <i data-lucide="pencil" class="w-5 h-5"></i>
                        </div>
                        <div>
                            <h3 class="font-outfit font-extrabold text-base text-slate-900">
                                Ubah Data Regu Lapangan
                            </h3>
                            <p class="text-xs text-slate-400">
                                Perbarui identitas regu, kode unik, wilayah fokus, dan ketua regu
                            </p>
                        </div>
                    </div>
                    <button type="button" onclick="closeEditTeamModal()" class="p-2 rounded-xl text-slate-400 hover:text-slate-600 hover:bg-slate-100 transition cursor-pointer">
                        <i data-lucide="x" class="w-4 h-4"></i>
                    </button>
                </div>

                <form id="editTeamForm" method="POST" action="" class="space-y-4">
                    @csrf
                    @method('PUT')
                    <div class="space-y-3">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Nama Regu Lapangan <span class="text-red-500">*</span></label>
                            <input type="text" id="editTeamName" name="name" required
                                class="w-full text-xs font-medium px-3 py-2.5 rounded-xl border border-slate-200 bg-slate-50 focus:bg-white focus:border-[#1E3A8A] focus:ring-1 focus:ring-[#1E3A8A] transition">
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Kode Regu <span class="text-red-500">*</span></label>
                            <input type="text" id="editTeamCode" name="code" required
                                class="w-full text-xs font-medium px-3 py-2.5 rounded-xl border border-slate-200 bg-slate-50 focus:bg-white focus:border-[#1E3A8A] focus:ring-1 focus:ring-[#1E3A8A] transition font-mono uppercase">
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Wilayah Cakupan Kerja / Kecamatan</label>
                            <select id="editTeamCoverageArea" name="coverage_area"
                                class="w-full text-xs font-medium px-3 py-2.5 rounded-xl border border-slate-200 bg-slate-50 focus:bg-white focus:border-[#1E3A8A] focus:ring-1 focus:ring-[#1E3A8A] transition">
                                <option value="Semua Wilayah">Semua Wilayah (Kabupaten Kolaka Utara)</option>
                                @foreach($districts as $d)
                                    <option value="{{ $d }}">{{ $d }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Ketua Regu (Koordinator Lapangan)</label>
                            <select id="editTeamLeaderId" name="leader_id" class="w-full text-xs font-medium px-3 py-2.5 rounded-xl border border-slate-200 bg-slate-50 focus:bg-white focus:border-[#1E3A8A] focus:ring-1 focus:ring-[#1E3A8A] transition">
                                <option value="">Belum Ditunjuk / Kosongkan</option>
                                @foreach($unassignedOfficers as $officer)
                                    <option value="{{ $officer->id }}" data-unassigned="true">{{ $officer->name }} ({{ $officer->phone ?? '-' }})</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="flex items-center justify-end gap-2.5 pt-3 border-t border-slate-100">
                        <button type="button" onclick="closeEditTeamModal()"
                            class="px-4 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs transition cursor-pointer">
                            Batal
                        </button>
                        <button type="submit"
                            class="px-5 py-2.5 rounded-xl bg-[#1E3A8A] hover:bg-[#172e6e] text-white font-bold text-xs transition flex items-center gap-1.5 shadow-md shadow-[#1E3A8A]/20 cursor-pointer">
                            <i data-lucide="save" class="w-3.5 h-3.5"></i>
                            <span>Simpan Perubahan</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- 1. MODAL KUSTOM KONFIRMASI HAPUS REGU LAPANGAN -->
    <div id="deleteTeamModal" class="fixed inset-0 z-[99999] hidden items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs transition-opacity duration-200">
        <div id="deleteTeamCard" class="bg-white rounded-3xl max-w-md w-full shadow-2xl border border-slate-100 overflow-hidden transform scale-95 opacity-0 transition-all duration-200">
            <div class="p-6 text-center space-y-4">
                <div class="w-14 h-14 rounded-2xl bg-red-50 border border-red-200 text-red-600 flex items-center justify-center mx-auto shadow-inner">
                    <i data-lucide="trash-2" class="w-7 h-7"></i>
                </div>
                <div>
                    <h3 class="font-outfit font-extrabold text-lg text-slate-900 leading-tight">
                        Hapus Regu Lapangan
                    </h3>
                    <p class="text-xs text-slate-500 mt-1.5">
                        Apakah Anda yakin ingin menghapus regu ini dari daftar operasional?
                    </p>
                </div>

                <!-- Card Detail Regu yang Akan Dihapus -->
                <div class="p-3.5 rounded-2xl bg-slate-50 border border-slate-200 text-left space-y-2">
                    <div class="flex items-center justify-between">
                        <span id="deleteTeamCodeBadge" class="px-2.5 py-0.5 rounded-md bg-[#1E3A8A] text-white font-mono text-[10px] font-extrabold">
                            REGU-01
                        </span>
                        <span id="deleteTeamMemberCountBadge" class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-slate-200 text-slate-700">
                            0 Personil
                        </span>
                    </div>
                    <div class="font-outfit font-extrabold text-sm text-slate-900 leading-snug" id="deleteTeamNameText">
                        Nama Regu
                    </div>
                    <div class="flex items-center gap-1.5 text-[11px] text-slate-500">
                        <i data-lucide="map-pin" class="w-3.5 h-3.5 text-slate-400 shrink-0"></i>
                        <span id="deleteTeamDistrictText">Wilayah: Kecamatan Terkait</span>
                    </div>
                </div>

                <!-- Info Perlindungan Personil -->
                <div class="p-3 rounded-2xl bg-blue-50 border border-blue-200 text-[#1E3A8A] text-[11px] text-left flex items-start gap-2.5">
                    <i data-lucide="shield-check" class="w-4 h-4 text-[#1E3A8A] shrink-0 mt-0.5"></i>
                    <span class="leading-relaxed">
                        <strong>Perlindungan Personil:</strong> Anggota di dalam regu ini tidak akan dihapus dari sistem, melainkan dialihkan menjadi <em>Petugas Lepas</em> yang siap ditugaskan ke regu lainnya.
                    </span>
                </div>

                <div class="flex items-center justify-end gap-2.5 pt-2">
                    <button type="button" onclick="closeDeleteTeamModal()"
                        class="px-4 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs transition cursor-pointer">
                        Batal
                    </button>
                    <form id="deleteTeamForm" method="POST" action="">
                        @csrf
                        @method('DELETE')
                        <button type="submit"
                            class="px-5 py-2.5 rounded-xl bg-red-600 hover:bg-red-700 text-white font-bold text-xs transition flex items-center gap-1.5 shadow-md shadow-red-200 cursor-pointer">
                            <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                            <span>Ya, Hapus Regu</span>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- 2. MODAL KUSTOM KELUARKAN PERSONIL DARI REGU -->
    <div id="removeMemberModal" class="fixed inset-0 z-[99999] hidden items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs transition-opacity duration-200">
        <div id="removeMemberCard" class="bg-white rounded-3xl max-w-md w-full shadow-2xl border border-slate-100 overflow-hidden transform scale-95 opacity-0 transition-all duration-200">
            <div class="p-6 text-center space-y-4">
                <div class="w-14 h-14 rounded-2xl bg-amber-50 border border-amber-200 text-amber-600 flex items-center justify-center mx-auto shadow-inner">
                    <i data-lucide="user-minus" class="w-7 h-7"></i>
                </div>
                <div>
                    <h3 class="font-outfit font-extrabold text-lg text-slate-900 leading-tight">
                        Keluarkan Petugas dari Regu
                    </h3>
                    <p class="text-xs text-slate-500 mt-1.5">
                        Apakah Anda yakin ingin mengeluarkan personil ini dari formasi regu?
                    </p>
                </div>

                <div class="p-3.5 rounded-2xl bg-slate-50 border border-slate-200 text-left space-y-1.5">
                    <div class="text-[10px] font-extrabold text-slate-400 uppercase tracking-wider">Nama Petugas:</div>
                    <div class="font-outfit font-extrabold text-sm text-slate-900" id="removeMemberNameText">
                        Nama Petugas
                    </div>
                    <div class="text-[11px] text-slate-500 pt-1.5 border-t border-slate-200/60 flex items-center gap-1.5">
                        <i data-lucide="users" class="w-3.5 h-3.5 text-slate-400 shrink-0"></i>
                        <span>Dikeluarkan dari: <strong class="text-slate-700" id="removeMemberTeamNameText">Nama Regu</strong></span>
                    </div>
                </div>

                <div class="p-3 rounded-2xl bg-amber-50 border border-amber-200 text-amber-800 text-[11px] text-left flex items-start gap-2.5">
                    <i data-lucide="info" class="w-4 h-4 text-amber-600 shrink-0 mt-0.5"></i>
                    <span class="leading-relaxed">
                        Petugas ini akan kembali berstatus <strong>Petugas Lepas</strong> dan dapat dialokasikan ke regu lainnya kapan saja melalui menu tambah personil.
                    </span>
                </div>

                <div class="flex items-center justify-end gap-2.5 pt-2">
                    <button type="button" onclick="closeRemoveMemberModal()"
                        class="px-4 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs transition cursor-pointer">
                        Batal
                    </button>
                    <form id="removeMemberForm" method="POST" action="">
                        @csrf
                        @method('DELETE')
                        <button type="submit"
                            class="px-5 py-2.5 rounded-xl bg-red-600 hover:bg-red-700 text-white font-bold text-xs transition flex items-center gap-1.5 shadow-md shadow-red-200 cursor-pointer">
                            <i data-lucide="user-minus" class="w-3.5 h-3.5"></i>
                            <span>Ya, Keluarkan</span>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- 3. MODAL KUSTOM KONFIRMASI PERGANTIAN MODE TUGAS -->
    <div id="confirmDispatchModeModal" class="fixed inset-0 z-[99999] hidden items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs transition-opacity duration-200">
        <div id="confirmDispatchModeCard" class="bg-white rounded-3xl max-w-lg w-full shadow-2xl border border-slate-100 overflow-hidden transform scale-95 opacity-0 transition-all duration-200">
            <div class="p-6 space-y-5">
                <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                    <div class="flex items-center gap-2.5">
                        <div class="w-10 h-10 rounded-xl bg-blue-50 text-[#1E3A8A] border border-blue-200 flex items-center justify-center font-bold">
                            <i data-lucide="sliders" class="w-5 h-5"></i>
                        </div>
                        <div>
                            <h3 class="font-outfit font-extrabold text-base text-slate-900">
                                Konfirmasi Pergantian Mode Tugas
                            </h3>
                            <p class="text-xs text-slate-400">
                                Perubahan alur kerja penanganan laporan infrastruktur daerah
                            </p>
                        </div>
                    </div>
                    <button type="button" onclick="closeDispatchModeModal()" class="p-2 rounded-xl text-slate-400 hover:text-slate-600 hover:bg-slate-100 transition cursor-pointer">
                        <i data-lucide="x" class="w-4 h-4"></i>
                    </button>
                </div>

                <!-- Preview Mode Terpilih -->
                <div class="p-4 rounded-2xl border-2 border-[#1E3A8A] bg-blue-50/50 space-y-2.5" id="modeModalCard">
                    <div class="flex items-center justify-between">
                        <span class="px-2.5 py-0.5 rounded-full bg-[#1E3A8A] text-white text-[10px] font-extrabold" id="modeModalBadge">
                            DIREKOMENDASIKAN
                        </span>
                        <span class="text-[10px] text-slate-500 font-semibold flex items-center gap-1">
                            <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                            <span>Akan Diaktifkan</span>
                        </span>
                    </div>
                    <div class="flex items-center gap-2 text-[#1E3A8A]">
                        <h4 class="font-outfit font-extrabold text-base text-slate-900" id="modeModalTitle">
                            Mode Hybrid
                        </h4>
                    </div>
                    <p class="text-xs text-slate-600 leading-relaxed" id="modeModalDesc">
                        Deskripsi dampak alur kerja...
                    </p>
                </div>

                <!-- Peringatan Sistem -->
                <div class="p-3.5 rounded-2xl bg-slate-50 border border-slate-200 text-slate-600 text-[11px] flex items-start gap-2.5">
                    <i data-lucide="info" class="w-4 h-4 text-[#1E3A8A] shrink-0 mt-0.5"></i>
                    <span class="leading-relaxed">
                        Perubahan mode tugas ini akan <strong>langsung disinkronkan</strong> ke Pusat Kendali Operator serta aplikasi mobile Petugas Lapangan di Kabupaten Kolaka Utara.
                    </span>
                </div>

                <div class="flex items-center justify-end gap-2.5 pt-2 border-t border-slate-100">
                    <button type="button" onclick="closeDispatchModeModal()"
                        class="px-4 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs transition cursor-pointer">
                        Batal
                    </button>
                    <button type="button" onclick="confirmAndSubmitDispatchMode()"
                        class="px-5 py-2.5 rounded-xl bg-[#1E3A8A] hover:bg-[#172e6e] text-white font-bold text-xs transition flex items-center gap-2 shadow-md shadow-[#1E3A8A]/20 cursor-pointer">
                        <i data-lucide="check" class="w-4 h-4"></i>
                        <span>Ya, Terapkan Mode Ini</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
@endpush

<!-- Chart.js CDN -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
    const adminMeta = {
        'users-management': {
            title: 'Pengaturan Akun Pimpinan & Tambah Petugas',
            subtitle: 'Perbarui data akun Kepala Daerah (Bupati) serta tambah akun Operator atau Petugas Lapangan.'
        },
        'users-list': {
            title: 'Direktori Seluruh Pengguna Sistem',
            subtitle: 'Lihat daftar akun berdasarkan peran, cari pengguna, dan lakukan reset kata sandi.'
        },
        'complaints-list': {
            title: 'Monitoring Seluruh Pengaduan Infrastruktur',
            subtitle: 'Pantau seluruh laporan masyarakat lintas status, lokasi, dan progres penanganan regu.'
        },
        'regions-gis': {
            title: 'Manajemen Wilayah & Geofencing GIS Kolaka Utara',
            subtitle: 'Atur batas wilayah administratif, koordinat 15 kecamatan & desa, serta geofencing laporan.'
        },
        'teams-management': {
            title: 'Manajemen Regu Lapangan & Mode Penugasan',
            subtitle: 'Kelola regu kerja lapangan, koordinator regu, dan mode distribusi tugas sistem.'
        },
        'reporting': {
            title: 'Laporan Eksekutif & Statistik Kinerja',
            subtitle: 'Ringkasan capaian penanganan aduan, distribusi kategori kerusakan, dan ekspor laporan.'
        }
    };

    function switchAdminTab(tabId) {
        // 1. Sembunyikan semua tab panel
        document.querySelectorAll('.admin-tab-panel').forEach(panel => {
            panel.classList.add('hidden');
        });

        // 2. Tampilkan tab panel tujuan
        const targetPanel = document.getElementById('tabPanel-' + tabId);
        if (targetPanel) {
            targetPanel.classList.remove('hidden');
        }

        // 3. Update styling tombol tab
        document.querySelectorAll('.admin-tab-btn').forEach(btn => {
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
            const badge = activeBtn.querySelector('.tab-badge');
            if (badge) {
                badge.className = 'tab-badge px-1.5 py-0.5 rounded-md text-[10px] font-bold shrink-0 bg-white text-[#1E3A8A]';
            }
            const sub = activeBtn.querySelector('.tab-sub');
            if (sub) {
                sub.classList.remove('text-[#475569]');
                sub.classList.add('text-blue-100');
            }
        }

        // 4. Update Header Judul & Subtitle
        if (adminMeta[tabId]) {
            document.getElementById('adminSectionTitle').innerText = adminMeta[tabId].title;
            document.getElementById('adminSectionSubtitle').innerText = adminMeta[tabId].subtitle;
        }

        // Inisialisasi peta saat tab GIS dibuka
        if (tabId === 'regions-gis') {
            setTimeout(initAdminRegionMap, 150);
        }

        // 5. Update parameter URL tanpa reload halaman
        const url = new URL(window.location);
        url.searchParams.set('tab', tabId);
        window.history.replaceState({}, '', url);

        if (window.lucide) {
            lucide.createIcons();
        }
    }

    // State & Helper Pagination Reusable (Direktori Akun, Semua Aduan, & Regu & Tugas)
    const adminPaginationState = {
        users: { page: 1, size: 10 },
        complaints: { page: 1, size: 10 },
        teams: { page: 1, size: 5 }
    };

    function renderPagination(key, matchedRows, infoId, controlsId, unitLabel, onPageChange) {
        const state = adminPaginationState[key];
        const total = matchedRows.length;
        const size = state.size;
        const totalPages = Math.max(1, Math.ceil(total / size));
        if (state.page > totalPages) state.page = totalPages;
        if (state.page < 1) state.page = 1;

        const startIdx = (state.page - 1) * size;
        const endIdx = Math.min(startIdx + size, total);

        matchedRows.forEach((row, idx) => {
            row.style.display = (idx >= startIdx && idx < endIdx) ? '' : 'none';
        });

        const infoEl = document.getElementById(infoId);
        if (infoEl) {
            infoEl.textContent = total === 0
                ? `Menampilkan 0 dari 0 ${unitLabel}`
                : `Menampilkan ${startIdx + 1}–${endIdx} dari ${total} ${unitLabel}`;
        }

        const controlsEl = document.getElementById(controlsId);
        if (!controlsEl) return;
        if (total === 0) {
            controlsEl.innerHTML = '';
            return;
        }

        const btnBase = 'px-2.5 py-1.5 rounded-lg border text-xs font-semibold transition cursor-pointer';
        const btnIdle = `${btnBase} bg-white border-[#E2E8F0] text-[#0F172A] hover:bg-slate-100`;
        const btnActive = `${btnBase} bg-[#1E3A8A] border-[#1E3A8A] text-white`;
        const btnDisabled = 'px-2.5 py-1.5 rounded-lg border border-[#E2E8F0] bg-slate-100 text-slate-400 text-xs font-semibold cursor-not-allowed';

        let html = '';
        html += state.page > 1
            ? `<button type="button" class="${btnIdle}" onclick="${onPageChange}(${state.page - 1})">Sebelumnya</button>`
            : `<button type="button" class="${btnDisabled}" disabled>Sebelumnya</button>`;

        for (let p = 1; p <= totalPages; p++) {
            if (totalPages > 7 && p > 2 && p < totalPages - 1 && Math.abs(p - state.page) > 1) {
                if (p === 3 || p === totalPages - 2) {
                    html += `<span class="px-1.5 text-[#475569]">…</span>`;
                }
                continue;
            }
            html += `<button type="button" class="${p === state.page ? btnActive : btnIdle}" onclick="${onPageChange}(${p})">${p}</button>`;
        }

        html += state.page < totalPages
            ? `<button type="button" class="${btnIdle}" onclick="${onPageChange}(${state.page + 1})">Berikutnya</button>`
            : `<button type="button" class="${btnDisabled}" disabled>Berikutnya</button>`;

        controlsEl.innerHTML = html;
    }

    function changeUsersPageSize(size) {
        adminPaginationState.users.size = parseInt(size, 10) || 10;
        adminPaginationState.users.page = 1;
        filterUsersTable(false);
    }

    function goUsersPage(page) {
        adminPaginationState.users.page = page;
        filterUsersTable(false);
    }

    function changeComplaintsPageSize(size) {
        adminPaginationState.complaints.size = parseInt(size, 10) || 10;
        adminPaginationState.complaints.page = 1;
        filterComplaintsTable(false);
    }

    function goComplaintsPage(page) {
        adminPaginationState.complaints.page = page;
        filterComplaintsTable(false);
    }

    function applyAdminTeamsPagination() {
        const rows = Array.from(document.querySelectorAll('#adminTeamsTableWrap .admin-team-row'));
        const cards = Array.from(document.querySelectorAll('#adminTeamsGrid .admin-team-card'));
        const savedPage = adminPaginationState.teams.page;
        if (rows.length > 0) {
            renderPagination('teams', rows, 'teamsPageInfo', 'teamsPageControls', 'regu', 'goTeamsPage');
        }
        if (cards.length > 0) {
            adminPaginationState.teams.page = savedPage;
            renderPagination('teams', cards, 'teamsPageInfo', 'teamsPageControls', 'regu', 'goTeamsPage');
        }
    }

    function changeTeamsPageSize(size) {
        adminPaginationState.teams.size = parseInt(size, 10) || 5;
        adminPaginationState.teams.page = 1;
        applyAdminTeamsPagination();
    }

    function goTeamsPage(page) {
        adminPaginationState.teams.page = page;
        applyAdminTeamsPagination();
    }

    function setAdminUsersLayout(mode) {
        const tableWrap = document.getElementById('usersTableWrap');
        const gridWrap = document.getElementById('usersGridWrap');
        if (tableWrap && gridWrap) {
            tableWrap.classList.toggle('hidden', mode === 'grid');
            gridWrap.classList.toggle('hidden', mode !== 'grid');
        }
        const listBtn = document.getElementById('usersLayoutBtn-list');
        const gridBtn = document.getElementById('usersLayoutBtn-grid');
        if (listBtn && gridBtn) {
            listBtn.className = `px-2.5 py-1.5 rounded-md text-xs font-semibold flex items-center gap-1 transition cursor-pointer ${mode === 'list' ? 'bg-[#1E3A8A] text-white' : 'text-[#475569] hover:text-[#0F172A]'}`;
            gridBtn.className = `px-2.5 py-1.5 rounded-md text-xs font-semibold flex items-center gap-1 transition cursor-pointer ${mode === 'grid' ? 'bg-[#1E3A8A] text-white' : 'text-[#475569] hover:text-[#0F172A]'}`;
        }
    }

    function setAdminComplaintsLayout(mode) {
        const tableWrap = document.getElementById('complaintsTableWrap');
        const gridWrap = document.getElementById('complaintsGridWrap');
        if (tableWrap && gridWrap) {
            tableWrap.classList.toggle('hidden', mode === 'grid');
            gridWrap.classList.toggle('hidden', mode !== 'grid');
        }
        const listBtn = document.getElementById('complaintsLayoutBtn-list');
        const gridBtn = document.getElementById('complaintsLayoutBtn-grid');
        if (listBtn && gridBtn) {
            listBtn.className = `px-2.5 py-1 rounded-md text-xs font-semibold flex items-center gap-1 transition cursor-pointer ${mode === 'list' ? 'bg-[#1E3A8A] text-white' : 'text-[#475569] hover:text-[#0F172A]'}`;
            gridBtn.className = `px-2.5 py-1 rounded-md text-xs font-semibold flex items-center gap-1 transition cursor-pointer ${mode === 'grid' ? 'bg-[#1E3A8A] text-white' : 'text-[#475569] hover:text-[#0F172A]'}`;
        }
    }

    function setAdminTeamsLayout(mode) {
        const tableWrap = document.getElementById('adminTeamsTableWrap');
        const grid = document.getElementById('adminTeamsGrid');
        if (tableWrap) tableWrap.classList.toggle('hidden', mode !== 'list');
        if (grid) grid.classList.toggle('hidden', mode !== 'grid');
        const listBtn = document.getElementById('teamsLayoutBtn-list');
        const gridBtn = document.getElementById('teamsLayoutBtn-grid');
        if (listBtn && gridBtn) {
            listBtn.className = `px-2.5 py-1.5 rounded-md text-xs font-semibold flex items-center gap-1 transition cursor-pointer ${mode === 'list' ? 'bg-[#1E3A8A] text-white' : 'text-[#475569] hover:text-[#0F172A]'}`;
            gridBtn.className = `px-2.5 py-1.5 rounded-md text-xs font-semibold flex items-center gap-1 transition cursor-pointer ${mode === 'grid' ? 'bg-[#1E3A8A] text-white' : 'text-[#475569] hover:text-[#0F172A]'}`;
        }
    }

    // Filter Instan Real-time Tabel Pengguna + Pagination
    function filterUsersTable(resetPage = true) {
        if (resetPage) adminPaginationState.users.page = 1;
        const query = (document.getElementById('userSearchInput')?.value || '').toLowerCase().trim();
        const roleFilter = (document.getElementById('userRoleFilter')?.value || '').toUpperCase();
        const rows = document.querySelectorAll('#usersTableBody .user-row');
        const cards = document.querySelectorAll('#usersGridWrap .user-grid-card');
        const matchedRows = [];
        const matchedCards = [];

        rows.forEach((row, idx) => {
            const name = row.getAttribute('data-name') || '';
            const email = row.getAttribute('data-email') || '';
            const phone = row.getAttribute('data-phone') || '';
            const role = row.getAttribute('data-role') || '';

            const matchQuery = !query || name.includes(query) || email.includes(query) || phone.includes(query);
            const matchRole = !roleFilter || (role === roleFilter);

            if (matchQuery && matchRole) {
                matchedRows.push(row);
                if (cards[idx]) matchedCards.push(cards[idx]);
            } else {
                row.style.display = 'none';
                if (cards[idx]) cards[idx].style.display = 'none';
            }
        });

        const counterEl = document.getElementById('filteredUserCount');
        if (counterEl) {
            counterEl.innerText = matchedRows.length;
        }

        const noDataEl = document.getElementById('noUserFoundMsg');
        if (noDataEl) {
            noDataEl.style.display = matchedRows.length === 0 ? 'block' : 'none';
        }

        renderPagination('users', matchedRows, 'usersPageInfo', 'usersPageControls', 'akun', 'goUsersPage');
        matchedCards.forEach((card, i) => {
            card.style.display = matchedRows[i] ? matchedRows[i].style.display : 'none';
        });
    }

    // Filter Instan Real-time Tabel Pengaduan + Pagination
    function filterComplaintsTable(resetPage = true) {
        if (resetPage) adminPaginationState.complaints.page = 1;
        const query = (document.getElementById('complaintSearchInput')?.value || '').toLowerCase().trim();
        const categoryVal = document.getElementById('complaintCategorySelect')?.value || 'ALL';
        const damageTypeVal = document.getElementById('complaintDamageTypeSelect')?.value || 'ALL';
        const statusVal = document.getElementById('complaintStatusSelect')?.value || 'ALL';
        const rows = document.querySelectorAll('#complaintsTableBody .complaint-row');
        const cards = document.querySelectorAll('#complaintsGridWrap .complaint-grid-card');
        const matchedRows = [];
        const matchedCards = [];

        rows.forEach((row, idx) => {
            const searchData = row.getAttribute('data-search') || '';
            const category = row.getAttribute('data-category') || '';
            const damageType = row.getAttribute('data-damage-type') || 'minor';
            const status = row.getAttribute('data-status') || '';

            const matchQuery = !query || searchData.includes(query);
            const matchCategory = (categoryVal === 'ALL') || (category === categoryVal);
            const matchDamageType = (damageTypeVal === 'ALL') || (damageType === damageTypeVal);
            
            let matchStatus = true;
            if (statusVal !== 'ALL') {
                if (statusVal === 'COMPLETED') {
                    matchStatus = (status === 'COMPLETED' || status === 'CLOSED');
                } else if (statusVal === 'IN_PROGRESS') {
                    matchStatus = (status === 'IN_PROGRESS');
                } else if (statusVal === 'DISPATCHED') {
                    matchStatus = (status === 'DISPATCHED');
                } else if (statusVal === 'SUBMITTED') {
                    matchStatus = (status === 'SUBMITTED' || status === 'VERIFICATION');
                } else {
                    matchStatus = (status === statusVal);
                }
            }

            if (matchQuery && matchCategory && matchDamageType && matchStatus) {
                matchedRows.push(row);
                if (cards[idx]) matchedCards.push(cards[idx]);
            } else {
                row.style.display = 'none';
                if (cards[idx]) cards[idx].style.display = 'none';
            }
        });

        const countBadge = document.getElementById('filteredComplaintCount');
        if (countBadge) {
            countBadge.innerText = matchedRows.length;
        }

        const noDataEl = document.getElementById('noComplaintFoundMsg');
        if (noDataEl) {
            noDataEl.style.display = matchedRows.length === 0 ? 'block' : 'none';
        }

        const hasActiveFilter = (query !== '' || categoryVal !== 'ALL' || damageTypeVal !== 'ALL' || statusVal !== 'ALL');
        const resetBtn = document.getElementById('complaintResetBtn');
        if (resetBtn) resetBtn.classList.toggle('hidden', !hasActiveFilter);

        renderPagination('complaints', matchedRows, 'complaintsPageInfo', 'complaintsPageControls', 'aduan', 'goComplaintsPage');
        matchedCards.forEach((card, i) => {
            card.style.display = matchedRows[i] ? matchedRows[i].style.display : 'none';
        });
    }

    document.addEventListener('DOMContentLoaded', () => {
        if (window.innerWidth < 768) {
            setAdminUsersLayout('grid');
            setAdminComplaintsLayout('grid');
            setAdminTeamsLayout('grid');
        }
        filterUsersTable(true);
        filterComplaintsTable(true);
        applyAdminTeamsPagination();
    });

    function resetComplaintFilters() {
        const searchInput = document.getElementById('complaintSearchInput');
        const catSelect = document.getElementById('complaintCategorySelect');
        const damageTypeSelect = document.getElementById('complaintDamageTypeSelect');
        const statSelect = document.getElementById('complaintStatusSelect');

        if (searchInput) searchInput.value = '';
        if (catSelect) catSelect.value = 'ALL';
        if (damageTypeSelect) damageTypeSelect.value = 'ALL';
        if (statSelect) statSelect.value = 'ALL';

        filterComplaintsTable();
    }

    // Modal Kustom Konfirmasi Hapus Laporan
    function openDeleteComplaintModal(actionUrl, publicCode, title) {
        const modal = document.getElementById('deleteComplaintModal');
        const content = document.getElementById('deleteModalContent');
        const form = document.getElementById('deleteComplaintForm');
        const codeEl = document.getElementById('deleteModalComplaintCode');
        const titleEl = document.getElementById('deleteModalComplaintTitle');

        if (form) form.action = actionUrl;
        if (codeEl) codeEl.innerText = '#' + publicCode;
        if (titleEl) titleEl.innerText = title || '-';

        if (modal && content) {
            modal.classList.remove('hidden');
            modal.classList.add('flex');
            setTimeout(() => {
                content.classList.remove('scale-95', 'opacity-0');
                content.classList.add('scale-100', 'opacity-100');
            }, 20);
        }
        if (window.lucide) lucide.createIcons();
    }

    function closeDeleteComplaintModal() {
        const modal = document.getElementById('deleteComplaintModal');
        const content = document.getElementById('deleteModalContent');

        if (modal && content) {
            content.classList.remove('scale-100', 'opacity-100');
            content.classList.add('scale-95', 'opacity-0');
            setTimeout(() => {
                modal.classList.add('hidden');
                modal.classList.remove('flex');
            }, 180);
        }
    }

    // Peta Spasial Administratif Kolaka Utara (Super Admin)
    let adminRegionMap = null;
    let districtMarkers = {};
    let currentPickingState = null;
    let pickingMarker = null;

    const districtDataList = @json($districtStats ?? []);

    function initAdminRegionMap() {
        const mapEl = document.getElementById('adminRegionMap');
        if (!mapEl) return;

        if (adminRegionMap) {
            adminRegionMap.invalidateSize();
            return;
        }

        // Default Pusat Lasusua, Kab. Kolaka Utara (-3.5262, 120.8407)
        adminRegionMap = L.map('adminRegionMap', {
            zoomControl: true,
            scrollWheelZoom: true,
            doubleClickZoom: false // Dinonaktifkan agar klik 2x (double-click) digunakan khusus untuk menentukan titik pusat
        }).setView([-3.5262, 120.8407], 9.5);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 18,
            attribution: '© OpenStreetMap | GIS Kolaka Utara'
        }).addTo(adminRegionMap);

        // Batas Administratif Resmi Kabupaten Kolaka Utara (OpenStreetMap GeoJSON Polygon)
        fetch('{{ asset("geojson/kolaka_utara.geojson") }}')
            .then(res => res.json())
            .then(geoJsonData => {
                L.geoJSON(geoJsonData, {
                    interactive: false, // PENTING: nonaktifkan intercept agar klik atau double-click user tembus langsung ke peta dan tidak terhalang popup batas
                    style: {
                        color: "#1E3A8A",
                        weight: 2.5,
                        dashArray: '6, 6',
                        fillColor: "#3B82F6",
                        fillOpacity: 0.08
                    }
                }).addTo(adminRegionMap);
            })
            .catch(err => console.log('Gagal memuat batas polygon Kolaka Utara:', err));

        // Plot 15 Titik Koordinat Pusat Kecamatan Administratif
        districtDataList.forEach(ds => {
            if (ds.latitude && ds.longitude) {
                const distIcon = L.divIcon({
                    className: 'custom-district-center-pin',
                    html: `
                        <div style="
                            width: 28px; height: 28px; 
                            background: #1E3A8A; 
                            border: 2px solid #FFFFFF; 
                            border-radius: 999px; 
                            box-shadow: 0 4px 10px rgba(30,58,138,0.4); 
                            display: flex; align-items: center; justify-content: center;
                            color: white; font-size: 11px;
                        ">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z"/>
                                <circle cx="12" cy="10" r="3"/>
                            </svg>
                        </div>
                    `,
                    iconSize: [28, 28],
                    iconAnchor: [14, 14],
                    popupAnchor: [0, -14]
                });

                const marker = L.marker([ds.latitude, ds.longitude], { icon: distIcon }).addTo(adminRegionMap);
                marker.bindPopup(`
                    <div style="font-family: 'Outfit', 'Inter', sans-serif; min-width: 170px; padding: 12px 14px;">
                        <div style="font-size:10px; font-weight:800; color:#1E3A8A; text-transform:uppercase; margin-bottom:2px;">
                            Pusat Administratif
                        </div>
                        <strong style="font-size:13px; color:#0F172A; display:block; line-height:1.3; margin-bottom:2px;">
                            ${ds.district}
                        </strong>
                        <span style="font-size:11px; color:#64748B; display:block;">Pusat: ${ds.village}</span>
                        <div style="margin-top:6px; padding-top:4px; border-top:1px solid #E2E8F0; font-size:10px; font-family: monospace; color:#475569;">
                            Lat: ${Number(ds.latitude).toFixed(4)}, Lon: ${Number(ds.longitude).toFixed(4)}
                        </div>
                        <button type="button" onclick="startPickingCoordinate(${ds.id}, '${ds.district}', ${ds.latitude}, ${ds.longitude})" style="
                            margin-top: 6px; width: 100%; padding: 4px 8px; border-radius: 8px; background: #1E3A8A; color: white;
                            font-size: 10px; font-weight: bold; border: none; cursor: pointer; text-align: center;
                        ">
                            Atur Titik di Peta
                        </button>
                    </div>
                `);
                districtMarkers[ds.id] = marker;
            }
        });

        // Plot Titik Pengaduan Aktual yang Masuk ke Sistem (Bukan Titik Dummy Manual)
        const complaintPoints = @json($mapPoints ?? []);
        if (complaintPoints && complaintPoints.length > 0) {
            complaintPoints.forEach(cp => {
                const lat = parseFloat(cp.latitude);
                const lng = parseFloat(cp.longitude);
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

                    if (cp.status === 'COMPLETED' || cp.status === 'CLOSED') {
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
                    } else if (cp.status === 'REJECTED') {
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
                    } else if (cp.status === 'IN_PROGRESS' || cp.status === 'REVISION_REQUIRED') {
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
                    } else if (cp.status === 'DISPATCHED') {
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
                    } else if (cp.status === 'WAITING_VERIFICATION') {
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

                    const pinIcon = L.divIcon({
                        className: 'custom-complaint-pin',
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
                        iconAnchor: [13, 13],
                        popupAnchor: [0, -13]
                    });

                    const marker = L.marker([lat, lng], { icon: pinIcon }).addTo(adminRegionMap);

                    // Polyline jika Kerusakan Ruas Panjang
                    if (cp.damage_type === 'major' && cp.end_latitude && cp.end_longitude) {
                        let lineCoords = [[lat, lng], [cp.end_latitude, cp.end_longitude]];
                        if (Array.isArray(cp.route_coordinates) && cp.route_coordinates.length > 1) {
                            lineCoords = cp.route_coordinates;
                        }
                        L.polyline(lineCoords, {
                            color: color,
                            weight: 5,
                            opacity: 0.85,
                            dashArray: '6, 8'
                        }).addTo(adminRegionMap);
                    }

                    const photoHtml = cp.photo_url 
                        ? `<div style="position:relative;width:100%;height:125px;background:#0F172A;overflow:hidden;">
                               <img src="${cp.photo_url}" alt="Foto Bukti" style="width:100%;height:100%;object-fit:cover;display:block;"
                                    onload="if(marker && marker.getPopup()) marker.getPopup().update();">
                               <div style="position:absolute;bottom:8px;left:8px;z-index:2;">
                                   <span style="background:rgba(15,23,42,0.85);backdrop-filter:blur(4px);color:#FFFFFF;font-size:10px;font-weight:700;padding:2px 8px;border-radius:6px;border:1px solid rgba(255,255,255,0.25);">
                                       ${cp.category_name}
                                   </span>
                               </div>
                           </div>`
                        : `<div style="position:relative;width:100%;height:44px;background:#F8FAFC;border-bottom:1px solid #E2E8F0;display:flex;align-items:center;justify-content:space-between;padding:0 12px;">
                               <span style="font-size:10px;font-weight:700;color:#64748B;background:#E2E8F0;padding:2px 8px;border-radius:6px;">
                                   ${cp.category_name}
                               </span>
                               <span style="font-size:10px;color:#94A3B8;">Foto tidak dilampirkan</span>
                           </div>`;

                    const popupHtml = `
                        <div style="width:280px;background:#FFFFFF;border-radius:16px;overflow:hidden;font-family:'Outfit',system-ui,sans-serif;box-sizing:border-box;">
                            ${photoHtml}
                            <div style="padding:12px 14px 14px 14px;background:#FFFFFF;">
                                <div style="display:flex;align-items:center;justify-content:space-between;gap:8px;margin-bottom:6px;">
                                    <span style="font-size:11px;font-weight:800;color:#1E3A8A;background:#EFF6FF;border:1px solid #DBEAFE;padding:2px 8px;border-radius:6px;font-family:monospace;">
                                        #${cp.public_code}
                                    </span>
                                    <span style="font-size:10px;font-weight:700;background:${statusBg};color:${statusColor};border:1px solid ${statusBorder};padding:2px 8px;border-radius:999px;white-space:nowrap;">
                                        ${statusText}
                                    </span>
                                </div>
                                <div style="margin-bottom:6px;">
                                    <span style="font-size:10px;font-weight:700;color:#B91C1C;background:#FEF2F2;border:1px solid #FECACA;padding:2px 8px;border-radius:999px;display:inline-flex;align-items:center;gap:4px;">
                                        <span style="width:6px;height:6px;border-radius:999px;background:#EF4444;display:inline-block;"></span>
                                        ${cp.damage_type === 'major' ? ('Kerusakan Berat (' + (cp.road_length ? (String(cp.road_length).toLowerCase().includes('m') ? cp.road_length : cp.road_length + 'm') : '≥ 500m') + ')') : 'Kerusakan Ringan'}
                                    </span>
                                </div>
                                <h4 style="font-size:13px;font-weight:700;color:#0F172A;line-height:1.35;margin:0 0 4px 0;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden;">
                                    ${cp.title}
                                </h4>
                                <div style="font-size:11px;color:#64748B;display:flex;align-items:flex-start;gap:4px;margin-bottom:8px;line-height:1.3;">
                                    <span style="flex-shrink:0;color:#EF4444;font-size:12px;">📍</span>
                                    <span style="display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden;">${cp.address}</span>
                                </div>
                                <div style="font-size:10.5px;color:#64748B;border-top:1px solid #F1F5F9;padding-top:6px;margin-bottom:10px;display:flex;justify-content:space-between;align-items:center;">
                                    <span>Pelapor: <strong style="color:#1E293B;">${cp.reporter_name}</strong></span>
                                    <span style="color:#94A3B8;">${cp.created_at_human}</span>
                                </div>
                                <button type="button" onclick="openAdminDetailModal(${cp.id})" 
                                    style="width:100%;background:#1E3A8A;color:#FFFFFF;border:none;padding:8px 12px;border-radius:10px;font-size:11.5px;font-weight:700;cursor:pointer;display:flex;align-items:center;justify-content:center;gap:6px;box-shadow:0 2px 5px rgba(30,58,138,0.25);transition:background 0.15s;"
                                    onmouseover="this.style.background='#172554'" onmouseout="this.style.background='#1E3A8A'">
                                    <span>Buka Detail Lengkap</span>
                                    <span>&rarr;</span>
                                </button>
                            </div>
                        </div>
                    `;

                    marker.bindPopup(popupHtml, { minWidth: 280, maxWidth: 280, autoPan: true, autoPanPadding: [20, 20] });
                }
            });
        }

        // Fungsi Penentuan Titik Koordinat (Bisa klik 1x maupun klik 2x)
        function handleSetCoordinate(latlng) {
            if (!currentPickingState) return;

            const lat = latlng.lat;
            const lon = latlng.lng;
            currentPickingState.lat = lat;
            currentPickingState.lon = lon;

            // Update display coords & enable save button
            const coordsDisplay = document.getElementById('pickingCoordsDisplay');
            if (coordsDisplay) {
                coordsDisplay.textContent = `Lat: ${lat.toFixed(5)}, Lon: ${lon.toFixed(5)}`;
            }
            const saveBtn = document.getElementById('savePickedBtn');
            if (saveBtn) {
                saveBtn.removeAttribute('disabled');
            }

            // Create or move picking marker
            if (!pickingMarker) {
                const pickIcon = L.divIcon({
                    className: 'custom-pick-marker',
                    html: `
                        <div style="
                            width: 34px; height: 34px; 
                            background: #0284C7; 
                            border: 3px solid #FFFFFF; 
                            border-radius: 999px; 
                            box-shadow: 0 0 0 6px rgba(2,132,199,0.35); 
                            display: flex; align-items: center; justify-content: center;
                            color: white; animation: pulse 1s infinite alternate;
                        ">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z"/>
                                <circle cx="12" cy="10" r="3"/>
                            </svg>
                        </div>
                    `,
                    iconSize: [34, 34],
                    iconAnchor: [17, 17],
                    popupAnchor: [0, -18]
                });
                pickingMarker = L.marker([lat, lon], { icon: pickIcon }).addTo(adminRegionMap);
            } else {
                pickingMarker.setLatLng([lat, lon]);
            }

            pickingMarker.bindPopup(`
                <div style="font-family: 'Outfit', 'Inter', sans-serif; min-width: 170px;">
                    <strong style="font-size:12px; color:#0284C7; display:block;">Titik Baru: ${currentPickingState.name}</strong>
                    <span style="font-size:11px; color:#475569; display:block;">Lat: ${lat.toFixed(5)}, Lon: ${lon.toFixed(5)}</span>
                    <span style="font-size:10px; color:#10B981; display:block; font-weight:bold; margin-top:3px;">Klik tombol "Simpan Titik" di atas untuk menyimpan.</span>
                </div>
            `).openPopup();
        }

        // Listener Klik 1x pada Peta (Saat Mode Penentuan Titik Aktif)
        adminRegionMap.on('click', function(e) {
            if (currentPickingState) {
                handleSetCoordinate(e.latlng);
            }
        });

        // Listener Klik 2x (Double-Click) pada Peta (Sesuai Permintaan: klik 1x geser peta, klik 2x tentukan titik)
        adminRegionMap.on('dblclick', function(e) {
            if (currentPickingState) {
                handleSetCoordinate(e.latlng);
            }
        });
    }

    // Scroll & Fokus ke Peta saat tombol Fokus Peta diklik (Sama persis seperti halaman bupati)
    function focusDistrictOnAdminMap(lat, lon, districtName) {
        const mapCard = document.getElementById('adminRegionMapCard');
        if (mapCard) {
            mapCard.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
        if (!adminRegionMap) {
            initAdminRegionMap();
        }
        setTimeout(() => {
            if (adminRegionMap) {
                adminRegionMap.flyTo([lat, lon], 12.5, { duration: 1.2 });
                L.popup()
                    .setLatLng([lat, lon])
                    .setContent(`
                        <div style="font-family: 'Outfit', 'Inter', sans-serif;">
                            <div style="display:flex; align-items:center; gap:6px; margin-bottom:3px;">
                                <span style="width:8px; height:8px; border-radius:999px; background:#1E3A8A; display:inline-block;"></span>
                                <span style="font-size:12px; font-weight:800; color:#0F172A;">${districtName}</span>
                            </div>
                            <span style="font-size:11px; color:#64748B; display:block;">Pusat Wilayah Administratif Kolaka Utara</span>
                        </div>
                    `)
                    .openOn(adminRegionMap);
            }
        }, 200);
    }

    // CMS Start Picking Coordinate
    function startPickingCoordinate(id, districtName, currentLat, currentLon) {
        const mapCard = document.getElementById('adminRegionMapCard');
        if (mapCard) {
            mapCard.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
        if (!adminRegionMap) {
            initAdminRegionMap();
        }

        currentPickingState = {
            id: id,
            name: districtName,
            lat: null,
            lon: null
        };

        const mapElement = document.getElementById('adminRegionMap');
        if (mapElement) {
            mapElement.style.cursor = 'crosshair';
        }

        const banner = document.getElementById('pickingBanner');
        const titleEl = document.getElementById('pickingDistrictTitle');
        const coordsDisplay = document.getElementById('pickingCoordsDisplay');
        const saveBtn = document.getElementById('savePickedBtn');

        if (banner) banner.classList.remove('hidden');
        if (titleEl) titleEl.textContent = districtName;
        if (coordsDisplay) coordsDisplay.textContent = currentLat && currentLon ? `Posisi saat ini: ${Number(currentLat).toFixed(4)}, ${Number(currentLon).toFixed(4)} (Klik 2x di posisi baru)` : 'Klik atau klik 2x pada peta...';
        if (saveBtn) {
            saveBtn.setAttribute('disabled', 'true');
            saveBtn.innerHTML = '<i data-lucide="save" class="w-3.5 h-3.5"></i><span>Simpan Titik</span>';
        }
        if (window.lucide) lucide.createIcons();

        setTimeout(() => {
            if (adminRegionMap && currentLat && currentLon) {
                adminRegionMap.flyTo([currentLat, currentLon], 12.5, { duration: 1.0 });
            }
        }, 150);
    }

    // CMS Save Picked Coordinate
    function savePickedCoordinate() {
        if (!currentPickingState || !currentPickingState.lat || !currentPickingState.lon) {
            showAdminNotice('Pilih Titik di Peta', 'Silakan klik atau klik 2 kali salah satu lokasi di dalam peta terlebih dahulu untuk meletakkan titik koordinat baru.', 'warning');
            return;
        }

        const saveBtn = document.getElementById('savePickedBtn');
        if (saveBtn) {
            saveBtn.setAttribute('disabled', 'true');
            saveBtn.innerHTML = '<span>Menyimpan...</span>';
        }

        fetch(`{{ url('/admin/regions') }}/${currentPickingState.id}/update-coordinates`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                latitude: currentPickingState.lat,
                longitude: currentPickingState.lon
            })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                const targetId = currentPickingState.id;
                const newLat = currentPickingState.lat;
                const newLon = currentPickingState.lon;

                // Update display on card
                const coordSpan = document.getElementById(`coordsDisplay-${targetId}`);
                if (coordSpan) {
                    coordSpan.textContent = `${newLat.toFixed(4)}, ${newLon.toFixed(4)}`;
                }

                // Update marker on map
                if (districtMarkers[targetId]) {
                    districtMarkers[targetId].setLatLng([newLat, newLon]);
                    districtMarkers[targetId].openPopup();
                }

                showAdminNotice('Titik Koordinat Disimpan', data.message || 'Titik koordinat kecamatan berhasil diperbarui.', 'success');
                cancelPickingCoordinate();
            } else {
                showAdminNotice('Gagal Menyimpan', data.message || 'Terjadi kesalahan saat menyimpan koordinat.', 'error');
                if (saveBtn) {
                    saveBtn.removeAttribute('disabled');
                    saveBtn.innerHTML = '<i data-lucide="save" class="w-3.5 h-3.5"></i><span>Simpan Titik</span>';
                    if (window.lucide) lucide.createIcons();
                }
            }
        })
        .catch(err => {
            console.error('Error saving coordinates:', err);
            showAdminNotice('Koneksi Terputus', 'Gagal menghubungi server untuk menyimpan koordinat. Silakan periksa koneksi internet Anda.', 'error');
            if (saveBtn) {
                saveBtn.removeAttribute('disabled');
                saveBtn.innerHTML = '<i data-lucide="save" class="w-3.5 h-3.5"></i><span>Simpan Titik</span>';
                if (window.lucide) lucide.createIcons();
            }
        });
    }

    // Modal Notifikasi Kustom Modern (Pengganti alert bawaan browser)
    function showAdminNotice(title, message, type = 'success') {
        const modal = document.getElementById('adminNoticeModal');
        const card = document.getElementById('adminNoticeCard');
        const titleEl = document.getElementById('adminNoticeTitle');
        const msgEl = document.getElementById('adminNoticeMessage');
        const iconBox = document.getElementById('adminNoticeIconBox');
        const icon = document.getElementById('adminNoticeIcon');

        if (!modal) {
            alert(message);
            return;
        }

        titleEl.textContent = title;
        msgEl.textContent = message;

        if (type === 'success') {
            iconBox.className = 'w-14 h-14 rounded-2xl bg-emerald-50 text-emerald-600 border border-emerald-100 mx-auto flex items-center justify-center shadow-xs';
            icon.setAttribute('data-lucide', 'check-circle-2');
        } else if (type === 'warning') {
            iconBox.className = 'w-14 h-14 rounded-2xl bg-amber-50 text-amber-600 border border-amber-100 mx-auto flex items-center justify-center shadow-xs';
            icon.setAttribute('data-lucide', 'alert-triangle');
        } else {
            iconBox.className = 'w-14 h-14 rounded-2xl bg-red-50 text-red-600 border border-red-100 mx-auto flex items-center justify-center shadow-xs';
            icon.setAttribute('data-lucide', 'alert-circle');
        }

        if (window.lucide) lucide.createIcons();

        modal.classList.remove('hidden');
        modal.classList.add('flex');
        setTimeout(() => {
            card.classList.remove('scale-95', 'opacity-0');
            card.classList.add('scale-100', 'opacity-100');
        }, 10);
    }

    function closeAdminNoticeModal() {
        const modal = document.getElementById('adminNoticeModal');
        const card = document.getElementById('adminNoticeCard');
        if (modal && card) {
            card.classList.remove('scale-100', 'opacity-100');
            card.classList.add('scale-95', 'opacity-0');
            setTimeout(() => {
                modal.classList.add('hidden');
                modal.classList.remove('flex');
            }, 180);
        }
    }

    // Modal Edit User (Super Admin)
    function openEditUserModalFromBtn(btn) {
        if (!btn) return;
        const user = {
            id: btn.getAttribute('data-id'),
            name: btn.getAttribute('data-name'),
            email: btn.getAttribute('data-email'),
            phone: btn.getAttribute('data-phone'),
            role: btn.getAttribute('data-role')
        };
        openEditUserModal(user);
    }

    function openEditUserModal(user) {
        const modal = document.getElementById('editUserModal');
        const content = document.getElementById('editUserModalContent');
        const form = document.getElementById('editUserForm');

        document.getElementById('editUserId').value = user.id;
        document.getElementById('editUserName').value = user.name || '';
        document.getElementById('editUserEmail').value = user.email || '';
        document.getElementById('editUserPhone').value = user.phone || '';
        const wargaOpt = document.getElementById('editUserOptionWarga');
        if (wargaOpt) {
            if (user.role === 'WARGA') {
                wargaOpt.classList.remove('hidden');
            } else {
                wargaOpt.classList.add('hidden');
            }
        }
        const roleVal = user.role || 'PETUGAS';
        document.getElementById('editUserRole').value = roleVal;
        const roleHidden = document.getElementById('editUserRoleHidden');
        if (roleHidden) {
            roleHidden.value = roleVal;
        }
        const resetDefaultBox = document.getElementById('editUserResetDefault');
        if (resetDefaultBox) {
            resetDefaultBox.checked = false;
        }

        form.action = '{{ url("/admin/users") }}/' + user.id;

        modal.classList.remove('hidden');
        modal.classList.add('flex');
        setTimeout(() => {
            content.classList.remove('scale-95', 'opacity-0');
            content.classList.add('scale-100', 'opacity-100');
        }, 10);

        if (window.lucide) lucide.createIcons();
    }

    function closeEditUserModal() {
        const modal = document.getElementById('editUserModal');
        const content = document.getElementById('editUserModalContent');
        if (modal && content) {
            content.classList.remove('scale-100', 'opacity-100');
            content.classList.add('scale-95', 'opacity-0');
            setTimeout(() => {
                modal.classList.add('hidden');
                modal.classList.remove('flex');
            }, 180);
        }
    }

    // Modal Proteksi Sesama Akun Petinggi
    function openConfirmDeleteLeaderModal(userId, userName, userRole) {
        const modal = document.getElementById('confirmDeleteLeaderModal');
        const card = document.getElementById('confirmDeleteLeaderCard');
        const form = document.getElementById('confirmDeleteLeaderForm');
        document.getElementById('deleteLeaderTargetName').innerText = userName;
        document.getElementById('deleteLeaderTargetRole').innerText = userRole;
        form.action = '{{ url("/admin/users") }}/' + userId + '/delete';

        if (modal && card) {
            modal.classList.remove('hidden');
            modal.classList.add('flex');
            setTimeout(() => {
                card.classList.remove('scale-95', 'opacity-0');
                card.classList.add('scale-100', 'opacity-100');
            }, 10);
        }
        if (window.lucide) lucide.createIcons();
    }

    function closeConfirmDeleteLeaderModal() {
        const modal = document.getElementById('confirmDeleteLeaderModal');
        const card = document.getElementById('confirmDeleteLeaderCard');
        if (modal && card) {
            card.classList.remove('scale-100', 'opacity-100');
            card.classList.add('scale-95', 'opacity-0');
            setTimeout(() => {
                modal.classList.add('hidden');
                modal.classList.remove('flex');
            }, 180);
        }
    }

    // Modal Konfirmasi Hapus Pengguna Reguler (Konsisten Gambar 4)
    function openConfirmDeleteUserModal(userId, userName, userEmail, userRole) {
        const modal = document.getElementById('deleteUserConfirmModal');
        const card  = document.getElementById('deleteUserConfirmCard');
        const form  = document.getElementById('deleteUserFormAction');
        
        if (document.getElementById('deleteUserNameTarget')) {
            document.getElementById('deleteUserNameTarget').innerText = userName;
        }
        if (document.getElementById('deleteUserEmailTarget')) {
            document.getElementById('deleteUserEmailTarget').innerText = userEmail;
        }
        if (document.getElementById('deleteUserRoleTarget')) {
            document.getElementById('deleteUserRoleTarget').innerText = userRole;
        }
        if (form) {
            form.action = `/admin/users/${userId}/delete`;
        }

        if (modal && card) {
            modal.classList.remove('hidden');
            modal.classList.add('flex');
            setTimeout(() => {
                card.classList.remove('scale-95', 'opacity-0');
                card.classList.add('scale-100', 'opacity-100');
            }, 10);
        }
        if (window.lucide) lucide.createIcons();
    }

    function closeConfirmDeleteUserModal() {
        const modal = document.getElementById('deleteUserConfirmModal');
        const card  = document.getElementById('deleteUserConfirmCard');
        if (modal && card) {
            card.classList.remove('scale-100', 'opacity-100');
            card.classList.add('scale-95', 'opacity-0');
            setTimeout(() => {
                modal.classList.add('hidden');
                modal.classList.remove('flex');
            }, 180);
        }
    }

    // Modal Quick Action Reset Password ke default "password"
    function openQuickResetModal(userId, userName, userEmail, userRole) {
        const modal = document.getElementById('quickResetPasswordModal');
        const card  = document.getElementById('quickResetPasswordCard');
        const form  = document.getElementById('quickResetFormAction');
        
        if (document.getElementById('resetUserNameTarget')) {
            document.getElementById('resetUserNameTarget').innerText = userName;
        }
        if (document.getElementById('resetUserEmailTarget')) {
            document.getElementById('resetUserEmailTarget').innerText = userEmail;
        }
        if (form) {
            form.action = `/admin/users/${userId}/reset`;
        }

        if (modal && card) {
            modal.classList.remove('hidden');
            modal.classList.add('flex');
            setTimeout(() => {
                card.classList.remove('scale-95', 'opacity-0');
                card.classList.add('scale-100', 'opacity-100');
            }, 10);
        }
        if (window.lucide) lucide.createIcons();
    }

    function closeQuickResetModal() {
        const modal = document.getElementById('quickResetPasswordModal');
        const card  = document.getElementById('quickResetPasswordCard');
        if (modal && card) {
            card.classList.remove('scale-100', 'opacity-100');
            card.classList.add('scale-95', 'opacity-0');
            setTimeout(() => {
                modal.classList.add('hidden');
                modal.classList.remove('flex');
            }, 180);
        }
    }

    // CMS Cancel Picking Coordinate
    function cancelPickingCoordinate() {
        currentPickingState = null;
        const banner = document.getElementById('pickingBanner');
        if (banner) banner.classList.add('hidden');

        const mapElement = document.getElementById('adminRegionMap');
        if (mapElement) {
            mapElement.style.cursor = '';
        }

        if (pickingMarker && adminRegionMap) {
            adminRegionMap.removeLayer(pickingMarker);
            pickingMarker = null;
        }

        const saveBtn = document.getElementById('savePickedBtn');
        if (saveBtn) {
            saveBtn.innerHTML = '<i data-lucide="save" class="w-3.5 h-3.5"></i><span>Simpan Titik</span>';
            saveBtn.setAttribute('disabled', 'true');
            if (window.lucide) lucide.createIcons();
        }
    }

    function resetAdminRegionMapView() {
        if (adminRegionMap) {
            adminRegionMap.setView([-3.5262, 120.8407], 10);
        }
    }

    // Listener Keyboard Escape & Enter untuk tutup modal notifikasi
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' || e.key === 'Enter') {
            const modal = document.getElementById('adminNoticeModal');
            if (modal && !modal.classList.contains('hidden')) {
                closeAdminNoticeModal();
            }
        }
    });

    // Data Peta Pengaduan untuk Modal Super Admin
    const adminComplaintsMap = @json($complaintsDetailMap ?? []);

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

    function openAdminDetailModal(id) {
        const item = adminComplaintsMap[id];
        if (!item) return;

        document.getElementById('admModalCode').innerText = '#' + item.public_code;

        const typeBadge = document.getElementById('admModalTypeBadge');
        if (typeBadge) {
            typeBadge.className = 'shrink-0 px-2.5 py-1 rounded-md text-xs font-semibold bg-red-50 text-red-700 border border-red-200 inline-flex items-center gap-1.5 whitespace-nowrap';
            typeBadge.innerHTML = `<span class="w-1.5 h-1.5 rounded-full bg-red-600 shrink-0"></span><span>${item.damage_type === 'major' ? 'Kerusakan Berat' : 'Kerusakan Ringan'}</span>`;
        }

        const statusBadge = document.getElementById('admModalStatusBadge');
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
        document.getElementById('admModalCategoryBadge').innerText = item.category_name || 'Infrastruktur';
        document.getElementById('admModalDate').innerText = item.created_at_full || item.created_at_formatted || '-';
        document.getElementById('admModalTitle').innerText = item.title || 'Laporan Pengaduan';
        document.getElementById('admModalCoords').innerText = (item.latitude && item.longitude) ? `${item.latitude}, ${item.longitude}` : 'Tidak tercatat';
        document.getElementById('admModalDescription').innerText = item.description || 'Tidak ada catatan tambahan.';

        // Uraian Wilayah Administrasi & Alamat Bersih
        const adm = parseAdministrativeAddress(item.address);
        const admRoadEl = document.getElementById('admModalRoad');
        const admVillageEl = document.getElementById('admModalVillage');
        const admDistrictEl = document.getElementById('admModalDistrict');
        const admRegencyEl = document.getElementById('admModalRegency');
        if (admRoadEl) admRoadEl.innerText = adm.road;
        if (admVillageEl) admVillageEl.innerText = adm.village;
        if (admDistrictEl) admDistrictEl.innerText = adm.district;
        if (admRegencyEl) admRegencyEl.innerText = adm.regency;

        const admAddressEl = document.getElementById('admModalAddress');
        if (admAddressEl) admAddressEl.innerText = item.address || 'Alamat lokasi tidak tercatat';

        // Identitas Pelapor & Nomor Telepon
        const citizenNameEl = document.getElementById('admModalCitizenName');
        const citizenPhoneEl = document.getElementById('admModalCitizenPhone');
        const citizenName = item.citizen_name || item.reporter_name || 'Warga Terdaftar';
        const citizenPhone = item.citizen_phone || item.reporter_phone || '-';
        if (citizenNameEl) citizenNameEl.innerText = citizenName;
        if (citizenPhoneEl) {
            citizenPhoneEl.innerText = citizenPhone;
            if (citizenPhone && citizenPhone !== '-') {
                let cleanPhone = citizenPhone.replace(/[^0-9]/g, '');
                if (cleanPhone.startsWith('0')) cleanPhone = '62' + cleanPhone.slice(1);
                else if (cleanPhone.startsWith('8')) cleanPhone = '62' + cleanPhone;
                citizenPhoneEl.href = 'https://wa.me/' + cleanPhone + '?text=' + encodeURIComponent('Halo ' + citizenName + ', kami dari Dinas PUPR / Pemkab Kolaka Utara terkait laporan pengaduan #' + (item.tracking_code || item.id));
                citizenPhoneEl.target = '_blank';
                citizenPhoneEl.title = 'Kirim Chat WhatsApp ke Warga';
            } else {
                citizenPhoneEl.removeAttribute('href');
            }
        }

        // Regu Penanganan Lapangan (Sesuai Permintaan: Menampilkan Regu, Ketua Regu, & Nomor HP)
        const officerCard = document.getElementById('admModalOfficerCard');
        const teamNameEl = document.getElementById('admModalTeamName');
        const officerDeptEl = document.getElementById('admModalOfficerDept');
        const teamLeaderEl = document.getElementById('admModalTeamLeader');
        const teamPhoneLink = document.getElementById('admModalTeamPhone');
        const teamPhoneText = document.getElementById('admModalTeamPhoneText');

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
                if (officerDeptEl) officerDeptEl.innerText = item.department_name || 'Dinas Pekerjaan Umum dan Penataan Ruang (PUPR)';

                // Ketua Regu & Nomor Telepon
                const showOfficerIdentity = ['IN_PROGRESS', 'WAITING_VERIFICATION', 'COMPLETED', 'CLOSED'].includes(item.status);
                const leaderName = item.team_leader || item.officer_name || 'Petugas Lapangan';
                const leaderPhone = item.team_leader_phone || item.officer_phone || '-';

                if (teamLeaderEl) {
                    teamLeaderEl.innerText = showOfficerIdentity ? leaderName : 'Menunggu Konfirmasi Regu';
                }

                if (teamPhoneLink && teamPhoneText) {
                    if (showOfficerIdentity && leaderPhone && leaderPhone !== '-') {
                        teamPhoneText.innerText = leaderPhone;
                        let cleanLeader = leaderPhone.replace(/[^0-9]/g, '');
                        if (cleanLeader.startsWith('0')) cleanLeader = '62' + cleanLeader.slice(1);
                        else if (cleanLeader.startsWith('8')) cleanLeader = '62' + cleanLeader;
                        teamPhoneLink.href = 'https://wa.me/' + cleanLeader + '?text=' + encodeURIComponent('Halo Tim Lapangan, konfirmasi penanganan tugas lapangan #' + (item.tracking_code || item.id));
                        teamPhoneLink.target = '_blank';
                        teamPhoneLink.title = 'Kirim Chat WhatsApp ke Regu Lapangan';
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
                const officerNameEl = document.getElementById('admModalOfficerName');
                if (officerNameEl) officerNameEl.innerText = leaderName;
                const officerPhoneEl = document.getElementById('admModalOfficerPhone');
                if (officerPhoneEl) officerPhoneEl.innerText = leaderPhone;

                const deadlineBox = document.getElementById('admModalDeadlineBox');
                if (deadlineBox) {
                    if (item.deadline) {
                        deadlineBox.classList.remove('hidden');
                        document.getElementById('admModalOfficerDeadline').innerText = item.deadline;
                    } else {
                        deadlineBox.classList.add('hidden');
                    }
                }
                const instructionRow = document.getElementById('admModalInstructionRow');
                if (instructionRow) {
                    if (item.instruction && item.instruction !== '-') {
                        instructionRow.classList.remove('hidden');
                        document.getElementById('admModalOfficerInstruction').innerText = item.instruction;
                    } else {
                        instructionRow.classList.add('hidden');
                    }
                }
            } else {
                officerCard.classList.add('hidden');
            }
        }

        // 1. Foto Bukti Kerusakan Warga & Galeri Foto
        const photoContainer = document.getElementById('admModalPhotoContainer');
        const photoImg = document.getElementById('admModalPhoto');
        const gallery = document.getElementById('admModalPhotoGallery');
        const photoLabel = document.getElementById('admModalPhotoLabel');

        const citizenPhotos = (item.citizen_photos && item.citizen_photos.length > 0)
            ? item.citizen_photos
            : (item.photo_url ? [item.photo_url] : []);

        if (citizenPhotos.length > 0) {
            photoImg.src = citizenPhotos[0];
            photoContainer.classList.remove('hidden');
            if (photoLabel) photoLabel.innerText = 'Foto Bukti Kerusakan Jalan';
        } else {
            photoContainer.classList.add('hidden');
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
                        photoImg.src = url;
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

        // Ruas Panjang Box
        const majorBox = document.getElementById('admModalMajorRoadBox');
        if (majorBox) {
            if (item.damage_type === 'major' || item.road_length) {
                majorBox.classList.remove('hidden');
                document.getElementById('admModalRoadLength').innerText = item.road_length || '≥ 500 Meter';
                document.getElementById('admModalRoadCondition').innerText = item.road_condition || 'Jalan Tanah / Rusak';
                document.getElementById('admModalStartCoords').innerText = `${item.latitude}, ${item.longitude}`;
                document.getElementById('admModalEndCoords').innerText = (item.end_latitude && item.end_longitude) ? `${item.end_latitude}, ${item.end_longitude}` : 'Tidak ditentukan';
            } else {
                majorBox.classList.add('hidden');
            }
        }

        // 2. Foto Bukti Penanganan Petugas Lapangan & Galeri Foto
        const compContainer = document.getElementById('admModalCompletionPhotoContainer');
        const compImg = document.getElementById('admModalCompletionPhoto');
        const compGallery = document.getElementById('admModalOfficerGallery');
        const compLabel = document.getElementById('admModalCompletionPhotoLabel');

        const officerPhotos = (item.officer_photos && item.officer_photos.length > 0)
            ? item.officer_photos
            : (item.completion_photo_url ? [item.completion_photo_url] : (item.photo_after_url ? [item.photo_after_url] : []));

        if (officerPhotos.length > 0) {
            compImg.src = officerPhotos[0];
            compContainer.classList.remove('hidden');
            if (compLabel) compLabel.innerText = 'Foto Bukti Penyelesaian Fisik';
        } else {
            compContainer.classList.add('hidden');
        }

        if (compGallery) {
            compGallery.innerHTML = '';
            if (officerPhotos.length > 1) {
                compGallery.classList.remove('hidden');
                officerPhotos.forEach((url, idx) => {
                    const thumb = document.createElement('img');
                    thumb.src = url;
                    thumb.className = `w-14 h-14 rounded-xl object-cover cursor-pointer border-2 transition ${idx === 0 ? 'border-emerald-600 ring-2 ring-emerald-200' : 'border-slate-200 opacity-70 hover:opacity-100'}`;
                    thumb.onclick = () => {
                        compImg.src = url;
                        if (compLabel) compLabel.innerText = `Foto Hasil Petugas ${idx + 1} dari ${officerPhotos.length}`;
                        compGallery.querySelectorAll('img').forEach(img => {
                            img.className = 'w-14 h-14 rounded-xl object-cover cursor-pointer border-2 border-slate-200 opacity-70 hover:opacity-100 transition';
                        });
                        thumb.className = 'w-14 h-14 rounded-xl object-cover cursor-pointer border-2 border-emerald-600 ring-2 ring-emerald-200 transition';
                    };
                    compGallery.appendChild(thumb);
                });
            } else {
                compGallery.classList.add('hidden');
            }
        }

        const modal = document.getElementById('adminComplaintDetailModal');
        const scrollBox = modal.querySelector('.overflow-y-auto');
        if (scrollBox) scrollBox.scrollTop = 0;
        modal.classList.remove('hidden');
        document.body.style.overflow = 'hidden';

        if (window.lucide) {
            lucide.createIcons({ root: modal });
        }
    }

    function closeAdminDetailModal() {
        const modal = document.getElementById('adminComplaintDetailModal');
        if (modal) {
            modal.classList.add('hidden');
            document.body.style.overflow = '';
        }
    }

    // Tutup modal admin jika klik luar dialog
    window.addEventListener('click', function(e) {
        const modal = document.getElementById('adminComplaintDetailModal');
        if (e.target === modal) {
            closeAdminDetailModal();
        }
    });

    // Modal Tim / Regu Lapangan
    function openCreateTeamModal() {
        const modal = document.getElementById('createTeamModal');
        const card = document.getElementById('createTeamCard');
        if (modal && card) {
            modal.classList.remove('hidden');
            modal.classList.add('flex');
            setTimeout(() => {
                card.classList.remove('scale-95', 'opacity-0');
                card.classList.add('scale-100', 'opacity-100');
            }, 10);
        }
        if (window.lucide) lucide.createIcons();
    }

    function closeCreateTeamModal() {
        const modal = document.getElementById('createTeamModal');
        const card = document.getElementById('createTeamCard');
        if (modal && card) {
            card.classList.remove('scale-100', 'opacity-100');
            card.classList.add('scale-95', 'opacity-0');
            setTimeout(() => {
                modal.classList.add('hidden');
                modal.classList.remove('flex');
            }, 180);
        }
    }

    function openEditTeamModal(team) {
        const modal = document.getElementById('editTeamModal');
        const card = document.getElementById('editTeamCard');
        const form = document.getElementById('editTeamForm');

        document.getElementById('editTeamName').value = team.name || '';
        document.getElementById('editTeamCode').value = team.code || '';

        // Select matching district in dropdown or fallback
        const covAreaSelect = document.getElementById('editTeamCoverageArea');
        if (covAreaSelect) {
            let matched = false;
            for (let i = 0; i < covAreaSelect.options.length; i++) {
                if (covAreaSelect.options[i].value === team.coverage_area) {
                    covAreaSelect.selectedIndex = i;
                    matched = true;
                    break;
                }
            }
            if (!matched) {
                if (team.coverage_area && team.coverage_area.trim() !== '') {
                    let opt = document.createElement('option');
                    opt.value = team.coverage_area;
                    opt.textContent = team.coverage_area;
                    opt.selected = true;
                    covAreaSelect.appendChild(opt);
                } else {
                    covAreaSelect.value = 'Semua Wilayah';
                }
            }
        }

        // Leader select: retain current team leader if already assigned
        const leaderSelect = document.getElementById('editTeamLeaderId');
        if (leaderSelect) {
            const oldCustomOpt = leaderSelect.querySelector('option[data-current-leader="true"]');
            if (oldCustomOpt) oldCustomOpt.remove();

            if (team.leader_id && team.leader_name) {
                const exists = Array.from(leaderSelect.options).some(opt => opt.value == team.leader_id);
                if (!exists) {
                    const opt = document.createElement('option');
                    opt.value = team.leader_id;
                    opt.textContent = team.leader_name + (team.leader_phone ? ' (' + team.leader_phone + ')' : '') + ' (Ketua Saat Ini)';
                    opt.setAttribute('data-current-leader', 'true');
                    leaderSelect.appendChild(opt);
                }
            }
            leaderSelect.value = team.leader_id || '';
        }

        form.action = '{{ url("/admin/teams") }}/' + team.id;

        if (modal && card) {
            modal.classList.remove('hidden');
            modal.classList.add('flex');
            setTimeout(() => {
                card.classList.remove('scale-95', 'opacity-0');
                card.classList.add('scale-100', 'opacity-100');
            }, 10);
        }
        if (window.lucide) lucide.createIcons();
    }

    function closeEditTeamModal() {
        const modal = document.getElementById('editTeamModal');
        const card = document.getElementById('editTeamCard');
        if (modal && card) {
            card.classList.remove('scale-100', 'opacity-100');
            card.classList.add('scale-95', 'opacity-0');
            setTimeout(() => {
                modal.classList.add('hidden');
                modal.classList.remove('flex');
            }, 180);
        }
    }

    // -------------------------------------------------------------
    // MODAL HAPUS REGU, KELUARKAN PERSONIL & KONFIRMASI MODE TUGAS
    // -------------------------------------------------------------
    function openDeleteTeamModal(team) {
        const modal = document.getElementById('deleteTeamModal');
        const card = document.getElementById('deleteTeamCard');
        const form = document.getElementById('deleteTeamForm');

        if (form) form.action = '{{ url("/admin/teams") }}/' + team.id;
        const codeEl = document.getElementById('deleteTeamCodeBadge');
        if (codeEl) codeEl.textContent = team.code || 'REGU';
        const nameEl = document.getElementById('deleteTeamNameText');
        if (nameEl) nameEl.textContent = team.name || 'Nama Regu';
        const districtEl = document.getElementById('deleteTeamDistrictText');
        if (districtEl) districtEl.textContent = 'Wilayah: ' + (team.district || 'Semua Wilayah');
        const countEl = document.getElementById('deleteTeamMemberCountBadge');
        if (countEl) countEl.textContent = (team.memberCount || 0) + ' Personil Terdaftar';

        if (modal && card) {
            modal.classList.remove('hidden');
            modal.classList.add('flex');
            setTimeout(() => {
                card.classList.remove('scale-95', 'opacity-0');
                card.classList.add('scale-100', 'opacity-100');
            }, 10);
        }
        if (window.lucide) lucide.createIcons();
    }

    function closeDeleteTeamModal() {
        const modal = document.getElementById('deleteTeamModal');
        const card = document.getElementById('deleteTeamCard');
        if (modal && card) {
            card.classList.remove('scale-100', 'opacity-100');
            card.classList.add('scale-95', 'opacity-0');
            setTimeout(() => {
                modal.classList.add('hidden');
                modal.classList.remove('flex');
            }, 180);
        }
    }

    function openRemoveMemberModal(data) {
        const modal = document.getElementById('removeMemberModal');
        const card = document.getElementById('removeMemberCard');
        const form = document.getElementById('removeMemberForm');

        if (form) form.action = '{{ url("/admin/teams") }}/' + data.teamId + '/members/' + data.memberId;
        const nameEl = document.getElementById('removeMemberNameText');
        if (nameEl) nameEl.textContent = data.memberName || 'Petugas';
        const teamEl = document.getElementById('removeMemberTeamNameText');
        if (teamEl) teamEl.textContent = data.teamName || 'Regu Terkait';

        if (modal && card) {
            modal.classList.remove('hidden');
            modal.classList.add('flex');
            setTimeout(() => {
                card.classList.remove('scale-95', 'opacity-0');
                card.classList.add('scale-100', 'opacity-100');
            }, 10);
        }
        if (window.lucide) lucide.createIcons();
    }

    function closeRemoveMemberModal() {
        const modal = document.getElementById('removeMemberModal');
        const card = document.getElementById('removeMemberCard');
        if (modal && card) {
            card.classList.remove('scale-100', 'opacity-100');
            card.classList.add('scale-95', 'opacity-0');
            setTimeout(() => {
                modal.classList.add('hidden');
                modal.classList.remove('flex');
            }, 180);
        }
    }

    let isDispatchModeConfirmed = false;
    function handleDispatchModeSubmit(event) {
        if (isDispatchModeConfirmed) return true;
        event.preventDefault();

        const form = document.getElementById('dispatchModeForm');
        const selectedRadio = form ? form.querySelector('input[name="dispatch_mode"]:checked') : null;
        const mode = selectedRadio ? selectedRadio.value : 'HYBRID';

        openDispatchModeModal(mode);
        return false;
    }

    function openDispatchModeModal(mode) {
        const modal = document.getElementById('confirmDispatchModeModal');
        const card = document.getElementById('confirmDispatchModeCard');
        const titleEl = document.getElementById('modeModalTitle');
        const badgeEl = document.getElementById('modeModalBadge');
        const descEl = document.getElementById('modeModalDesc');

        if (mode === 'OPERATOR_ONLY') {
            if (titleEl) titleEl.textContent = 'Mode 1: Khusus Operator (Komando Terpusat)';
            if (badgeEl) badgeEl.textContent = 'KOMANDO TERPUSAT';
            if (descEl) descEl.innerHTML = 'Setiap laporan yang valid <strong>wajib ditunjuk regunya</strong> secara manual oleh Operator dinas sebelum regu bisa memulai pengerjaan fisik di lapangan.';
        } else if (mode === 'SELF_CLAIM_ONLY') {
            if (titleEl) titleEl.textContent = 'Mode 3: Klaim Bebas Mandiri (Self-Service)';
            if (badgeEl) badgeEl.textContent = 'KLAIM MANDIRI';
            if (descEl) descEl.innerHTML = 'Laporan yang terverifikasi langsung masuk ke daftar antrean terbuka. Regu lapangan berhak mengambil tugas secara mandiri sesuai kesiapan alat dan lokasi.';
        } else {
            if (titleEl) titleEl.textContent = 'Mode 2: Hybrid (Kombinasi Rekomendasi)';
            if (badgeEl) badgeEl.textContent = 'DIREKOMENDASIKAN';
            if (descEl) descEl.innerHTML = 'Operator dapat menugaskan langsung ke regu tertentu <strong>ATAU</strong> membiarkan terbuka agar bisa diklaim mandiri oleh regu lapangan yang siap.';
        }

        if (modal && card) {
            modal.classList.remove('hidden');
            modal.classList.add('flex');
            setTimeout(() => {
                card.classList.remove('scale-95', 'opacity-0');
                card.classList.add('scale-100', 'opacity-100');
            }, 10);
        }
        if (window.lucide) lucide.createIcons();
    }

    function closeDispatchModeModal() {
        const modal = document.getElementById('confirmDispatchModeModal');
        const card = document.getElementById('confirmDispatchModeCard');
        if (modal && card) {
            card.classList.remove('scale-100', 'opacity-100');
            card.classList.add('scale-95', 'opacity-0');
            setTimeout(() => {
                modal.classList.add('hidden');
                modal.classList.remove('flex');
            }, 180);
        }
    }

    function confirmAndSubmitDispatchMode() {
        isDispatchModeConfirmed = true;
        const form = document.getElementById('dispatchModeForm');
        if (form) form.submit();
    }

    document.addEventListener('DOMContentLoaded', () => {
        if ('{{ $activeTab }}' === 'regions-gis') {
            setTimeout(initAdminRegionMap, 200);
        } else if ('{{ $activeTab }}' === 'reporting') {
            setTimeout(initExecutiveMonitoringCharts, 200);
        }
    });
</script>

@include('partials.executive_monitoring_reporting_js')
@endsection
