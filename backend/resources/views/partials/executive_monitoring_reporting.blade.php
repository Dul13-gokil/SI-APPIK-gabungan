<!-- Modul Monitoring Eksekutif & Pelaporan Resmi -->
@php
    $totalOnTime = $departments->sum('completed_tasks');
    $totalLate = $departments->sum(fn($d) => max(0, ($d->total_tasks ?? 0) - ($d->completed_tasks ?? 0)));
    $totalAssigned = $departments->sum('total_tasks');
    $slaComplianceRate = $totalAssigned > 0 ? round(($totalOnTime / $totalAssigned) * 100) : 0;

    $fieldCount = max(0, ($summary['in_progress'] ?? 0) + ($summary['completed'] ?? 0));
    $operatorCount = max(0, ($summary['pending'] ?? 0) + ($summary['waiting_verification'] ?? 0));
    $deptCount = max(0, $totalOnTime);
    $totalWorkload = max(1, $fieldCount + $operatorCount + $deptCount);
    $fieldPercent = round(($fieldCount / $totalWorkload) * 100);
    $operatorPercent = round(($operatorCount / $totalWorkload) * 100);
    $deptPercent = max(0, 100 - $fieldPercent - $operatorPercent);

    $allReports = $allComplaintsForReport ?? collect();
    $minorCount = $allReports->where('damage_type', '!=', 'major')->count();
    $majorCount = $allReports->where('damage_type', 'major')->count();
    $activeDistricts = collect($districtStats ?? [])->filter(fn($d) => ($d['total'] ?? 0) > 0)->values();

    $avgDays = $avgPerformance['overall_days'] ?? 4.1;
    $sopTargetDays = $avgPerformance['sop_target_days'] ?? 5.0;
    $printerUser = auth()->user();
    $printerRoleLabel = match($printerUser?->role) {
        'SUPERADMIN', 'ADMIN' => 'Super Administrator',
        'KEPALA_DAERAH', 'BUPATI' => 'Kepala Daerah (Bupati)',
        'OPERATOR' => 'Operator Verifikasi PUPR',
        default => 'Pejabat Berwenang',
    };
    $docNumber = 'LAP-PUPR/' . now()->format('Y/m') . '/' . str_pad((string)($summary['total'] ?? 0), 3, '0', STR_PAD_LEFT);
@endphp

<!-- 1. TOOLBAR AKSI LAPORAN RESMI (PDF) — BERSIH, KONSISTEN, TANPA GRADIENT -->
<div class="card-white rounded-2xl p-4 sm:p-5 bg-white border border-[#E2E8F0] flex flex-col lg:flex-row lg:items-center justify-between gap-4">
    <div class="flex items-start gap-3.5">
        <div class="w-10 h-10 rounded-xl bg-slate-50 border border-slate-200 text-[#1E3A8A] flex items-center justify-center shrink-0 mt-0.5">
            <i data-lucide="file-check-2" class="w-5 h-5"></i>
        </div>
        <div class="space-y-1">
            <div class="inline-flex items-center gap-1.5 text-[11px] font-bold uppercase tracking-wider text-[#1E3A8A]">
                <span>Dokumen Rekapitulasi Eksekutif</span>
                <span class="text-slate-300">&bull;</span>
                <span class="text-slate-500 font-semibold">Dinas PUPR Kab. Kolaka Utara</span>
            </div>
            <h3 class="font-outfit font-bold text-base sm:text-lg text-slate-900 tracking-tight leading-snug">
                Pelaporan &amp; Evaluasi Kinerja Penanganan Infrastruktur
            </h3>
        </div>
    </div>

    <!-- Tombol Aksi & Pilih Format Dokumen -->
    <div class="flex flex-wrap items-center gap-2 shrink-0">
        <!-- Switcher Format Cepat -->
        <div class="inline-flex items-center p-1 rounded-xl bg-slate-100 border border-slate-200" role="group" aria-label="Pilih Format Laporan">
            <button type="button" onclick="setReportFormat('professional')" data-format-btn="professional"
                class="px-2.5 py-1.5 rounded-lg text-[11px] font-bold transition cursor-pointer bg-white text-[#1E3A8A] border border-slate-200 shadow-2xs">
                Profesional
            </button>
            <button type="button" onclick="setReportFormat('ats')" data-format-btn="ats"
                class="px-2.5 py-1.5 rounded-lg text-[11px] font-bold transition cursor-pointer text-slate-600 hover:text-slate-900 border border-transparent">
                Format ATS
            </button>
        </div>

        <!-- Pratinjau Dokumen -->
        <button type="button" onclick="openReportPreviewModal()"
            class="px-3.5 py-2 rounded-xl text-xs font-bold bg-slate-50 hover:bg-slate-100 text-slate-700 border border-slate-200 transition flex items-center gap-2 cursor-pointer">
            <i data-lucide="eye" class="w-4 h-4 text-[#1E3A8A]"></i>
            <span>Pratinjau Dokumen</span>
        </button>

        <!-- Cetak Dokumen PDF -->
        <button type="button" onclick="printOfficialReportPdf()"
            class="px-3.5 py-2 rounded-xl text-xs font-bold bg-[#1E3A8A] hover:bg-[#172554] text-white transition flex items-center gap-2 cursor-pointer">
            <i data-lucide="printer" class="w-4 h-4"></i>
            <span>Cetak PDF</span>
        </button>
    </div>
</div>

<!-- 2. BARIS 1 GRAFIK: STATUS PROGRES SIKLUS PENANGANAN (KIRI) & SEBARAN KATEGORI (KANAN) -->
<div class="grid grid-cols-1 lg:grid-cols-12 gap-5">
    <!-- 1. STATUS PROGRES SIKLUS PENANGANAN PENGADUAN (7 Kolom) -->
    <div class="lg:col-span-7 card-white rounded-2xl p-5 bg-white border border-[#E2E8F0] flex flex-col justify-between space-y-4">
        <div class="space-y-3">
            <div class="flex items-start justify-between gap-3 border-b border-slate-100 pb-3">
                <div class="flex items-start gap-2.5 min-w-0">
                    <div class="w-9 h-9 rounded-xl bg-slate-50 border border-slate-200 text-[#1E3A8A] flex items-center justify-center shrink-0">
                        <i data-lucide="bar-chart-3" class="w-4 h-4"></i>
                    </div>
                    <div class="min-w-0">
                        <h3 class="font-outfit font-bold text-sm sm:text-base text-slate-900 leading-tight">
                            Status Progres Siklus Penanganan Pengaduan
                        </h3>
                    </div>
                </div>
                <span class="px-2.5 py-1 rounded-lg text-[11px] font-bold bg-slate-50 text-slate-700 border border-slate-200 shrink-0">
                    Total: {{ $summary['total'] }} Laporan
                </span>
            </div>

            <!-- Canvas Chart.js Status Bar Chart -->
            <div class="relative h-60 sm:h-64 w-full">
                <canvas id="complaintStatusChart"></canvas>
            </div>
        </div>

        <!-- Mini Summary 6 Status Card Grid (Netral & Seragam) -->
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-2 pt-2.5 border-t border-slate-100">
            <div class="p-2.5 rounded-xl bg-slate-50 border border-slate-200/80 flex flex-col justify-between">
                <span class="text-[9.5px] font-bold text-slate-500 uppercase tracking-wider">Total Laporan</span>
                <div class="flex items-baseline justify-between mt-1">
                    <span class="font-outfit font-extrabold text-lg text-slate-900">{{ $summary['total'] }}</span>
                    <span class="text-[9.5px] font-bold text-slate-400">100%</span>
                </div>
            </div>
            <div class="p-2.5 rounded-xl bg-slate-50 border border-slate-200/80 flex flex-col justify-between">
                <span class="text-[9.5px] font-bold text-slate-500 uppercase tracking-wider">Verifikasi</span>
                <div class="flex items-baseline justify-between mt-1">
                    <span class="font-outfit font-extrabold text-lg text-slate-900">{{ $summary['pending'] }}</span>
                    <span class="text-[9.5px] font-bold text-slate-400">{{ $summary['total'] > 0 ? round(($summary['pending'] / $summary['total']) * 100) : 0 }}%</span>
                </div>
            </div>
            <div class="p-2.5 rounded-xl bg-slate-50 border border-slate-200/80 flex flex-col justify-between">
                <span class="text-[9.5px] font-bold text-slate-500 uppercase tracking-wider">Ditugaskan</span>
                <div class="flex items-baseline justify-between mt-1">
                    <span class="font-outfit font-extrabold text-lg text-slate-900">{{ $summary['dispatched'] }}</span>
                    <span class="text-[9.5px] font-bold text-slate-400">{{ $summary['total'] > 0 ? round(($summary['dispatched'] / $summary['total']) * 100) : 0 }}%</span>
                </div>
            </div>
            <div class="p-2.5 rounded-xl bg-slate-50 border border-slate-200/80 flex flex-col justify-between">
                <span class="text-[9.5px] font-bold text-slate-500 uppercase tracking-wider">Dikerjakan</span>
                <div class="flex items-baseline justify-between mt-1">
                    <span class="font-outfit font-extrabold text-lg text-slate-900">{{ $summary['in_progress'] }}</span>
                    <span class="text-[9.5px] font-bold text-slate-400">{{ $summary['total'] > 0 ? round(($summary['in_progress'] / $summary['total']) * 100) : 0 }}%</span>
                </div>
            </div>
            <div class="p-2.5 rounded-xl bg-slate-50 border border-slate-200/80 flex flex-col justify-between">
                <span class="text-[9.5px] font-bold text-slate-500 uppercase tracking-wider">Selesai</span>
                <div class="flex items-baseline justify-between mt-1">
                    <span class="font-outfit font-extrabold text-lg text-slate-900">{{ $summary['completed'] }}</span>
                    <span class="text-[9.5px] font-bold text-slate-400">{{ $summary['completion_rate'] }}%</span>
                </div>
            </div>
            <div class="p-2.5 rounded-xl bg-slate-50 border border-slate-200/80 flex flex-col justify-between">
                <span class="text-[9.5px] font-bold text-slate-500 uppercase tracking-wider">Ditolak</span>
                <div class="flex items-baseline justify-between mt-1">
                    <span class="font-outfit font-extrabold text-lg text-slate-900">{{ $summary['rejected'] ?? 0 }}</span>
                    <span class="text-[9.5px] font-bold text-slate-400">{{ ($summary['total'] ?? 0) > 0 ? round((($summary['rejected'] ?? 0) / $summary['total']) * 100) : 0 }}%</span>
                </div>
            </div>
        </div>
    </div>

    <!-- 2. SEBARAN MASALAH PER KATEGORI INFRASTRUKTUR (5 Kolom) -->
    <div class="lg:col-span-5 card-white rounded-2xl p-5 bg-white border border-[#E2E8F0] flex flex-col justify-between space-y-4">
        <div class="space-y-3">
            <div class="flex items-start justify-between gap-3 border-b border-slate-100 pb-3">
                <div class="flex items-start gap-2.5 min-w-0">
                    <div class="w-9 h-9 rounded-xl bg-slate-50 border border-slate-200 text-[#1E3A8A] flex items-center justify-center shrink-0">
                        <i data-lucide="layers" class="w-4 h-4"></i>
                    </div>
                    <div class="min-w-0">
                        <h3 class="font-outfit font-bold text-sm sm:text-base text-slate-900 leading-tight">
                            Sebaran Masalah per Kategori Infrastruktur
                        </h3>
                    </div>
                </div>
                <span class="px-2.5 py-1 rounded-lg text-[11px] font-bold bg-slate-50 text-slate-700 border border-slate-200 shrink-0">
                    {{ $categories->count() }} Kategori
                </span>
            </div>

            <!-- Canvas Chart.js Doughnut Kategori -->
            <div class="relative h-56 w-full flex items-center justify-center">
                <canvas id="categoryDistributionChart"></canvas>
            </div>
        </div>

        <!-- List Legend Kategori Rapi -->
        @php
            $bluePalette = ['#1E3A8A', '#2563EB', '#3B82F6', '#60A5FA', '#93C5FD', '#BFDBFE'];
        @endphp
        <div class="space-y-2 pt-2.5 border-t border-slate-100 max-h-48 overflow-y-auto">
            @foreach($categories as $cat)
                @php
                    $catColor = $bluePalette[$loop->index % count($bluePalette)];
                @endphp
                <div class="flex items-center justify-between py-2 px-3 rounded-xl bg-slate-50 border border-slate-200/80">
                    <div class="flex items-center gap-2.5 min-w-0 pr-3">
                        <span class="w-2.5 h-2.5 rounded-full shrink-0" style="background-color: {{ $catColor }};"></span>
                        <span class="font-semibold text-slate-800 text-xs truncate">{{ $cat->name }}</span>
                    </div>
                    <div class="inline-flex items-center gap-1 px-2 py-0.5 rounded-lg bg-white border border-slate-200 shrink-0">
                        <span class="font-outfit font-extrabold text-xs text-slate-900">{{ $cat->complaints_count }}</span>
                        <span class="text-[10.5px] font-medium text-slate-500">Laporan</span>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>

<!-- 3. BARIS 2 GRAFIK: KINERJA KETEPATAN WAKTU DINAS OPD (KIRI) & PERSENTASE KINERJA PENANGANAN TUGAS (KANAN) -->
<div class="grid grid-cols-1 lg:grid-cols-12 gap-5">
    <!-- 3. KINERJA KETEPATAN WAKTU PENANGANAN DINAS (OPD) (7 Kolom) -->
    <div class="lg:col-span-7 card-white rounded-2xl p-5 bg-white border border-[#E2E8F0] flex flex-col justify-between space-y-4">
        <div class="space-y-3">
            <div class="flex items-start justify-between gap-3 border-b border-slate-100 pb-3">
                <div class="flex items-start gap-2.5 min-w-0">
                    <div class="w-9 h-9 rounded-xl bg-slate-50 border border-slate-200 text-[#1E3A8A] flex items-center justify-center shrink-0">
                        <i data-lucide="briefcase" class="w-4 h-4"></i>
                    </div>
                    <div class="min-w-0">
                        <h3 class="font-outfit font-bold text-sm sm:text-base text-slate-900 leading-tight">
                            Kinerja Ketepatan Waktu Penanganan Dinas (OPD)
                        </h3>
                    </div>
                </div>
                <span class="px-2.5 py-1 rounded-lg text-[11px] font-bold bg-slate-50 text-slate-700 border border-slate-200 shrink-0">
                    SLA: {{ $slaComplianceRate }}%
                </span>
            </div>

            <!-- Canvas Chart.js OPD Bar Chart -->
            <div class="relative h-60 sm:h-64 w-full">
                <canvas id="opdPerformanceChart"></canvas>
            </div>
        </div>

        <!-- Ringkasan Angka Metrik di Bawah Grafik (Seragam 3 Kolom Netral) -->
        <div class="grid grid-cols-3 gap-2.5 pt-2.5 border-t border-slate-100">
            <div class="p-2.5 rounded-xl bg-slate-50 border border-slate-200/80 text-center">
                <span class="text-[10px] font-bold text-slate-500 uppercase tracking-wider block">Tepat Waktu</span>
                <span class="font-outfit font-extrabold text-xl text-slate-900 block mt-0.5">{{ $totalOnTime }}</span>
                <span class="text-[10px] text-slate-400 font-medium">Laporan Tuntas</span>
            </div>
            <div class="p-2.5 rounded-xl bg-slate-50 border border-slate-200/80 text-center">
                <span class="text-[10px] font-bold text-slate-500 uppercase tracking-wider block">Terlambat</span>
                <span class="font-outfit font-extrabold text-xl text-slate-900 block mt-0.5">{{ $totalLate }}</span>
                <span class="text-[10px] text-slate-400 font-medium">Melewati Batas SLA</span>
            </div>
            <div class="p-2.5 rounded-xl bg-slate-50 border border-slate-200/80 text-center">
                <span class="text-[10px] font-bold text-slate-500 uppercase tracking-wider block">Rasio SLA</span>
                <span class="font-outfit font-extrabold text-xl text-slate-900 block mt-0.5">{{ $slaComplianceRate }}%</span>
                <span class="text-[10px] text-slate-400 font-medium">Dinas PUPR</span>
            </div>
        </div>
    </div>

    <!-- 4. PERSENTASE KINERJA PENANGANAN TUGAS (5 Kolom) -->
    <div class="lg:col-span-5 card-white rounded-2xl p-5 bg-white border border-[#E2E8F0] flex flex-col justify-between space-y-4">
        <div class="space-y-3">
            <div class="flex items-start justify-between gap-3 border-b border-slate-100 pb-3">
                <div class="flex items-start gap-2.5 min-w-0">
                    <div class="w-9 h-9 rounded-xl bg-slate-50 border border-slate-200 text-[#1E3A8A] flex items-center justify-center shrink-0">
                        <i data-lucide="pie-chart" class="w-4 h-4"></i>
                    </div>
                    <div class="min-w-0">
                        <h3 class="font-outfit font-bold text-sm sm:text-base text-slate-900 leading-tight">
                            Persentase Kinerja Penanganan Tugas
                        </h3>
                    </div>
                </div>
                <span class="px-2.5 py-1 rounded-lg text-[11px] font-bold bg-slate-50 text-slate-700 border border-slate-200 shrink-0">
                    {{ $fieldCount + $operatorCount + $deptCount }} Aksi
                </span>
            </div>

            <!-- Canvas Chart.js Doughnut Kinerja Peran -->
            <div class="relative h-56 sm:h-60 w-full flex items-center justify-center">
                <canvas id="stakeholderPerformanceChart"></canvas>
            </div>
        </div>

        <!-- 3 Legend Box Bersih & Netral -->
        <div class="grid grid-cols-3 gap-2 pt-2.5 border-t border-slate-100">
            <div class="p-2.5 rounded-xl bg-slate-50 border border-slate-200/80 text-center">
                <div class="flex items-center justify-center gap-1.5 mb-1">
                    <span class="w-2 h-2 rounded-full bg-[#3B82F6]"></span>
                    <span class="text-[10px] font-bold text-slate-600 uppercase">Petugas</span>
                </div>
                <span class="font-outfit font-extrabold text-lg text-slate-900 block">{{ $fieldPercent }}%</span>
                <span class="text-[10px] text-slate-400 font-medium">{{ $fieldCount }} Tindakan</span>
            </div>
            <div class="p-2.5 rounded-xl bg-slate-50 border border-slate-200/80 text-center">
                <div class="flex items-center justify-center gap-1.5 mb-1">
                    <span class="w-2 h-2 rounded-full bg-[#1E3A8A]"></span>
                    <span class="text-[10px] font-bold text-slate-600 uppercase">Operator</span>
                </div>
                <span class="font-outfit font-extrabold text-lg text-slate-900 block">{{ $operatorPercent }}%</span>
                <span class="text-[10px] text-slate-400 font-medium">{{ $operatorCount }} Telaah</span>
            </div>
            <div class="p-2.5 rounded-xl bg-slate-50 border border-slate-200/80 text-center">
                <div class="flex items-center justify-center gap-1.5 mb-1">
                    <span class="w-2 h-2 rounded-full bg-[#10B981]"></span>
                    <span class="text-[10px] font-bold text-slate-600 uppercase">Dinas PUPR</span>
                </div>
                <span class="font-outfit font-extrabold text-lg text-slate-900 block">{{ $deptPercent }}%</span>
                <span class="text-[10px] text-slate-400 font-medium">{{ $deptCount }} Tuntas</span>
            </div>
        </div>
    </div>
</div>

<!-- 4. RINGKASAN INDIKATOR KINERJA UTAMA (KPI), BENCHMARK SLA & TIPOLOGI KERUSAKAN -->
<div class="grid grid-cols-2 lg:grid-cols-4 gap-3.5">
    <div class="card-white rounded-2xl p-4 bg-white border border-[#E2E8F0] space-y-1">
        <span class="text-[10.5px] font-bold uppercase tracking-wider text-slate-500 block">Tingkat Ketuntasan</span>
        <div class="flex items-baseline justify-between gap-2">
            <span class="font-outfit font-extrabold text-2xl text-slate-900">{{ $summary['completion_rate'] ?? 0 }}%</span>
            <span class="text-[11px] font-semibold text-emerald-700 bg-emerald-50 border border-emerald-200 px-2 py-0.5 rounded-md">
                {{ $summary['completed'] ?? 0 }}/{{ $summary['total'] ?? 0 }}
            </span>
        </div>
    </div>

    <div class="card-white rounded-2xl p-4 bg-white border border-[#E2E8F0] space-y-1">
        <span class="text-[10.5px] font-bold uppercase tracking-wider text-slate-500 block">Rata-Rata Durasi Siklus</span>
        <div class="flex items-baseline justify-between gap-2">
            <span class="font-outfit font-extrabold text-2xl text-slate-900">{{ $avgDays }} <span class="text-sm font-bold text-slate-500">Hari</span></span>
            <span class="text-[11px] font-semibold text-[#1E3A8A] bg-blue-50 border border-blue-200 px-2 py-0.5 rounded-md">
                SOP &le; {{ $sopTargetDays }} hr
            </span>
        </div>
    </div>

    <div class="card-white rounded-2xl p-4 bg-white border border-[#E2E8F0] space-y-1">
        <span class="text-[10.5px] font-bold uppercase tracking-wider text-slate-500 block">Tipologi Kerusakan</span>
        <div class="flex items-baseline justify-between gap-2">
            <span class="font-outfit font-extrabold text-xl text-slate-900">{{ $minorCount }} Titik &bull; {{ $majorCount }} Ruas</span>
        </div>
    </div>

    <div class="card-white rounded-2xl p-4 bg-white border border-[#E2E8F0] space-y-1">
        <span class="text-[10.5px] font-bold uppercase tracking-wider text-slate-500 block">Wilayah Terdampak</span>
        <div class="flex items-baseline justify-between gap-2">
            <span class="font-outfit font-extrabold text-2xl text-slate-900">{{ $activeDistricts->count() }} <span class="text-sm font-bold text-slate-500">Kec.</span></span>
            <span class="text-[11px] font-semibold text-slate-600 bg-slate-100 border border-slate-200 px-2 py-0.5 rounded-md">
                dari 15 Kec.
            </span>
        </div>
    </div>
</div>

<!-- 5. MODAL PRATINJAU DOKUMEN REKAPITULASI (FORMAT PROFESIONAL & FORMAT ATS) -->
<div id="officialReportModal" class="hidden fixed inset-0 z-50 overflow-y-auto bg-slate-900/65 flex items-center justify-center p-3 sm:p-5 overscroll-contain">
    <div class="card-white rounded-2xl bg-white border border-slate-200 shadow-2xl max-w-4xl w-full max-h-[92vh] overflow-hidden flex flex-col relative">

        <!-- Header Modal Preview -->
        <div class="px-4 sm:px-5 py-3 border-b border-slate-200 flex flex-wrap items-center justify-between bg-slate-50 gap-2.5 shrink-0 rounded-t-2xl">
            <div class="flex items-center gap-2.5">
                <div class="w-8 h-8 rounded-lg bg-[#1E3A8A] text-white flex items-center justify-center font-bold text-xs shrink-0">
                    <i data-lucide="file-text" class="w-4 h-4"></i>
                </div>
                <div>
                    <h4 class="font-outfit font-bold text-sm text-slate-900">
                        Pratinjau Dokumen Rekapitulasi Resmi
                    </h4>
                    <p id="modalReportFormatSubtitle" class="text-[11px] text-slate-500">
                        Format Profesional Kedinasan &bull; Kabupaten Kolaka Utara
                    </p>
                </div>
            </div>

            <div class="flex flex-wrap items-center gap-2">
                <!-- Toggle Format Profesional vs Format ATS -->
                <div class="inline-flex items-center p-1 rounded-xl bg-slate-200/70 border border-slate-200">
                    <button type="button" onclick="setReportFormat('professional')" data-format-btn="professional"
                        class="px-2.5 py-1 rounded-lg text-[11px] font-bold transition cursor-pointer bg-white text-[#1E3A8A] border border-slate-200 shadow-2xs">
                        Format Profesional
                    </button>
                    <button type="button" onclick="setReportFormat('ats')" data-format-btn="ats"
                        class="px-2.5 py-1 rounded-lg text-[11px] font-bold transition cursor-pointer text-slate-600 hover:text-slate-900 border border-transparent">
                        Format ATS
                    </button>
                </div>

                <button type="button" onclick="printOfficialReportPdf()"
                    class="px-3.5 py-1.5 rounded-xl bg-[#1E3A8A] hover:bg-[#172554] text-white text-xs font-bold transition flex items-center gap-1.5 cursor-pointer">
                    <i data-lucide="printer" class="w-3.5 h-3.5"></i>
                    <span>Cetak PDF</span>
                </button>
                <button type="button" onclick="closeReportPreviewModal()"
                    class="w-8 h-8 rounded-full bg-slate-200/70 hover:bg-slate-300 text-slate-700 flex items-center justify-center transition cursor-pointer shrink-0"
                    title="Tutup">
                    <i data-lucide="x" class="w-4 h-4"></i>
                </button>
            </div>
        </div>

        <!-- Body Pratinjau Dokumen -->
        <div id="reportModalScrollArea" class="flex-1 overflow-y-auto bg-white p-5 sm:p-8">
            <div id="printableOfficialDocument" class="w-full text-black font-sans text-xs pb-6">

                <!-- ========================================================= -->
                <!-- FORMAT A: FORMAT PROFESIONAL (KEDINASAN RESMI PEMKAB)     -->
                <!-- ========================================================= -->
                <div id="reportFormatProfessional" class="space-y-5">
                    <!-- KOP SURAT RESMI PEMERINTAH KABUPATEN KOLAKA UTARA -->
                    <div class="border-b-[3px] border-double border-black pb-3 text-center space-y-0.5">
                        <p class="text-[11px] font-bold uppercase tracking-widest text-slate-700">Pemerintah Kabupaten Kolaka Utara</p>
                        <h2 class="font-outfit font-extrabold text-base sm:text-lg text-black uppercase tracking-tight">
                            Dinas Pekerjaan Umum dan Penataan Ruang (PUPR)
                        </h2>
                        <p class="text-[10.5px] text-slate-600">
                            Kompleks Perkantoran Pemerintah Kabupaten Kolaka Utara, Lasusua, Sulawesi Tenggara
                        </p>
                    </div>

                    <!-- JUDUL & IDENTITAS DOKUMEN -->
                    <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-3 border-b border-slate-300 pb-3">
                        <div class="space-y-0.5">
                            <h3 class="font-outfit font-bold text-sm sm:text-base text-black uppercase">
                                Laporan Rekapitulasi Penanganan Pengaduan Infrastruktur
                            </h3>
                            <p class="text-[11px] text-slate-700">
                                Periode Pelaporan: <strong>{{ now()->translatedFormat('F Y') }}</strong> &bull; No. Dokumen: <span class="font-mono font-semibold">{{ $docNumber }}</span>
                            </p>
                        </div>
                        <div class="sm:text-right text-[10.5px] text-slate-700 shrink-0 space-y-0.5">
                            <div>Dicetak: <strong class="font-mono text-black">{{ now()->translatedFormat('d F Y, H:i') }} WITA</strong></div>
                            <div>Oleh: <strong class="text-black">{{ $printerUser?->name ?? 'Sistem' }}</strong> ({{ $printerRoleLabel }})</div>
                        </div>
                    </div>

                    <!-- I. RINGKASAN EKSEKUTIF & INDIKATOR KINERJA UTAMA (KPI) -->
                    <div class="space-y-2">
                        <h4 class="font-outfit font-bold text-xs uppercase tracking-wider text-black border-b border-slate-300 pb-1">
                            I. Ringkasan Eksekutif &amp; Indikator Kinerja Utama (KPI)
                        </h4>
                        <div class="grid grid-cols-2 sm:grid-cols-4 gap-2.5">
                            <div class="p-2.5 border border-slate-300 bg-slate-50">
                                <span class="text-[9.5px] font-bold text-slate-600 uppercase block">Total Aduan Masuk</span>
                                <span class="font-outfit font-extrabold text-base text-black block mt-0.5">{{ $summary['total'] }} Laporan</span>
                                <span class="text-[9.5px] text-slate-500">{{ $minorCount }} Titik &bull; {{ $majorCount }} Ruas</span>
                            </div>
                            <div class="p-2.5 border border-slate-300 bg-slate-50">
                                <span class="text-[9.5px] font-bold text-slate-600 uppercase block">Tingkat Ketuntasan</span>
                                <span class="font-outfit font-extrabold text-base text-black block mt-0.5">{{ $summary['completion_rate'] ?? 0 }}%</span>
                                <span class="text-[9.5px] text-slate-500">{{ $summary['completed'] ?? 0 }} Laporan Selesai</span>
                            </div>
                            <div class="p-2.5 border border-slate-300 bg-slate-50">
                                <span class="text-[9.5px] font-bold text-slate-600 uppercase block">Kepatuhan SLA Dinas</span>
                                <span class="font-outfit font-extrabold text-base text-black block mt-0.5">{{ $slaComplianceRate }}%</span>
                                <span class="text-[9.5px] text-slate-500">{{ $totalOnTime }} Tepat / {{ $totalLate }} Terlambat</span>
                            </div>
                            <div class="p-2.5 border border-slate-300 bg-slate-50">
                                <span class="text-[9.5px] font-bold text-slate-600 uppercase block">Rata-Rata Durasi</span>
                                <span class="font-outfit font-extrabold text-base text-black block mt-0.5">{{ $avgDays }} Hari</span>
                                <span class="text-[9.5px] text-slate-500">Target SOP &le; {{ $sopTargetDays }} Hari</span>
                            </div>
                        </div>
                    </div>

                    <!-- II. REKAPITULASI SIKLUS STATUS PENANGANAN -->
                    <div class="space-y-2">
                        <h4 class="font-outfit font-bold text-xs uppercase tracking-wider text-black border-b border-slate-300 pb-1">
                            II. Rekapitulasi Siklus Status Penanganan Pengaduan
                        </h4>
                        <table class="w-full text-left border-collapse border border-slate-400 text-[11px]">
                            <thead class="bg-slate-100 text-black font-bold uppercase text-[10px]">
                                <tr>
                                    <th class="border border-slate-400 py-1.5 px-2.5">Total Laporan</th>
                                    <th class="border border-slate-400 py-1.5 px-2 text-center">Verifikasi</th>
                                    <th class="border border-slate-400 py-1.5 px-2 text-center">Ditugaskan</th>
                                    <th class="border border-slate-400 py-1.5 px-2 text-center">Dikerjakan</th>
                                    <th class="border border-slate-400 py-1.5 px-2 text-center">Validasi</th>
                                    <th class="border border-slate-400 py-1.5 px-2 text-center">Selesai</th>
                                    <th class="border border-slate-400 py-1.5 px-2 text-center">Ditolak</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td class="border border-slate-400 py-2 px-2.5 font-bold text-black">{{ $summary['total'] }} (100%)</td>
                                    <td class="border border-slate-400 py-2 px-2 text-center font-semibold">{{ $summary['pending'] }}</td>
                                    <td class="border border-slate-400 py-2 px-2 text-center font-semibold">{{ $summary['dispatched'] }}</td>
                                    <td class="border border-slate-400 py-2 px-2 text-center font-semibold">{{ $summary['in_progress'] }}</td>
                                    <td class="border border-slate-400 py-2 px-2 text-center font-semibold">{{ $summary['waiting_verification'] ?? 0 }}</td>
                                    <td class="border border-slate-400 py-2 px-2 text-center font-bold">{{ $summary['completed'] }} ({{ $summary['completion_rate'] }}%)</td>
                                    <td class="border border-slate-400 py-2 px-2 text-center font-semibold">{{ $summary['rejected'] ?? 0 }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- III. EVALUASI KETEPATAN WAKTU DINAS (SLA) & KONTRIBUSI PERAN -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div class="space-y-1.5">
                            <h4 class="font-outfit font-bold text-xs uppercase tracking-wider text-black border-b border-slate-300 pb-1">
                                III. Evaluasi Ketepatan Waktu Dinas (SLA)
                            </h4>
                            <table class="w-full text-left border-collapse border border-slate-400 text-[11px]">
                                <tbody>
                                    <tr>
                                        <td class="border border-slate-400 py-1.5 px-2.5 font-semibold">Penanganan Tepat Waktu (Memenuhi SLA)</td>
                                        <td class="border border-slate-400 py-1.5 px-2.5 text-right font-bold">{{ $totalOnTime }} Laporan</td>
                                    </tr>
                                    <tr>
                                        <td class="border border-slate-400 py-1.5 px-2.5 font-semibold">Penanganan Terlambat (Melewati SLA)</td>
                                        <td class="border border-slate-400 py-1.5 px-2.5 text-right font-bold">{{ $totalLate }} Laporan</td>
                                    </tr>
                                    <tr class="bg-slate-50">
                                        <td class="border border-slate-400 py-1.5 px-2.5 font-bold">Rasio Kepatuhan SLA Dinas PUPR</td>
                                        <td class="border border-slate-400 py-1.5 px-2.5 text-right font-bold">{{ $slaComplianceRate }}%</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <div class="space-y-1.5">
                            <h4 class="font-outfit font-bold text-xs uppercase tracking-wider text-black border-b border-slate-300 pb-1">
                                IV. Distribusi Kinerja Penanganan Tugas
                            </h4>
                            <table class="w-full text-left border-collapse border border-slate-400 text-[11px]">
                                <tbody>
                                    <tr>
                                        <td class="border border-slate-400 py-1.5 px-2.5 font-semibold">Petugas Lapangan (Tindakan Fisik)</td>
                                        <td class="border border-slate-400 py-1.5 px-2.5 text-right font-bold">{{ $fieldCount }} Tindakan ({{ $fieldPercent }}%)</td>
                                    </tr>
                                    <tr>
                                        <td class="border border-slate-400 py-1.5 px-2.5 font-semibold">Operator (Verifikasi &amp; Validasi)</td>
                                        <td class="border border-slate-400 py-1.5 px-2.5 text-right font-bold">{{ $operatorCount }} Telaah ({{ $operatorPercent }}%)</td>
                                    </tr>
                                    <tr class="bg-slate-50">
                                        <td class="border border-slate-400 py-1.5 px-2.5 font-semibold">Dinas PUPR (Penyelesaian Akhir)</td>
                                        <td class="border border-slate-400 py-1.5 px-2.5 text-right font-bold">{{ $deptCount }} Tuntas ({{ $deptPercent }}%)</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- V. REKAPITULASI PER KATEGORI INFRASTRUKTUR -->
                    <div class="space-y-2">
                        <h4 class="font-outfit font-bold text-xs uppercase tracking-wider text-black border-b border-slate-300 pb-1">
                            V. Rekapitulasi per Kategori Infrastruktur
                        </h4>
                        <table class="w-full text-left border-collapse border border-slate-400 text-[11px]">
                            <thead class="bg-slate-100 text-black font-bold uppercase text-[10px]">
                                <tr>
                                    <th class="border border-slate-400 py-1.5 px-2.5 w-10 text-center">No</th>
                                    <th class="border border-slate-400 py-1.5 px-2.5">Kategori Kerusakan</th>
                                    <th class="border border-slate-400 py-1.5 px-2.5 text-center">Jumlah Laporan</th>
                                    <th class="border border-slate-400 py-1.5 px-2.5 text-center">Persentase</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($categories as $cat)
                                    @php
                                        $p = $summary['total'] > 0 ? round(($cat->complaints_count / $summary['total']) * 100) : 0;
                                    @endphp
                                    <tr class="{{ $loop->even ? 'bg-slate-50' : 'bg-white' }}">
                                        <td class="border border-slate-400 py-1.5 px-2.5 text-center">{{ $loop->iteration }}</td>
                                        <td class="border border-slate-400 py-1.5 px-2.5 font-semibold text-black">{{ $cat->name }}</td>
                                        <td class="border border-slate-400 py-1.5 px-2.5 text-center font-bold">{{ $cat->complaints_count }} Laporan</td>
                                        <td class="border border-slate-400 py-1.5 px-2.5 text-center font-mono">{{ $p }}%</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <!-- VI. REKAPITULASI SEBARAN WILAYAH KECAMATAN -->
                    <div class="space-y-2">
                        <h4 class="font-outfit font-bold text-xs uppercase tracking-wider text-black border-b border-slate-300 pb-1">
                            VI. Rekapitulasi Sebaran Wilayah Kecamatan (Kolaka Utara)
                        </h4>
                        <table class="w-full text-left border-collapse border border-slate-400 text-[11px]">
                            <thead class="bg-slate-100 text-black font-bold uppercase text-[10px]">
                                <tr>
                                    <th class="border border-slate-400 py-1.5 px-2.5">Kecamatan</th>
                                    <th class="border border-slate-400 py-1.5 px-2 text-center">Total</th>
                                    <th class="border border-slate-400 py-1.5 px-2 text-center">Diproses</th>
                                    <th class="border border-slate-400 py-1.5 px-2 text-center">Selesai</th>
                                    <th class="border border-slate-400 py-1.5 px-2 text-center">Ditolak</th>
                                    <th class="border border-slate-400 py-1.5 px-2 text-center">Ketuntasan</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($activeDistricts as $dist)
                                    @php
                                        $dTotal = (int)($dist['total'] ?? 0);
                                        $dDone = (int)($dist['completed'] ?? 0);
                                        $dRejected = (int)($dist['rejected'] ?? 0);
                                        $dProcess = max(0, $dTotal - $dDone - $dRejected);
                                        $dPct = $dTotal > 0 ? round(($dDone / $dTotal) * 100) : 0;
                                    @endphp
                                    <tr class="{{ $loop->even ? 'bg-slate-50' : 'bg-white' }}">
                                        <td class="border border-slate-400 py-1.5 px-2.5 font-semibold">{{ $dist['district'] }}</td>
                                        <td class="border border-slate-400 py-1.5 px-2 text-center font-bold">{{ $dTotal }}</td>
                                        <td class="border border-slate-400 py-1.5 px-2 text-center">{{ $dProcess }}</td>
                                        <td class="border border-slate-400 py-1.5 px-2 text-center font-bold">{{ $dDone }}</td>
                                        <td class="border border-slate-400 py-1.5 px-2 text-center">{{ $dRejected }}</td>
                                        <td class="border border-slate-400 py-1.5 px-2 text-center font-mono">{{ $dPct }}%</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="border border-slate-400 py-2 px-3 text-center text-slate-500">
                                            Belum ada sebaran laporan per kecamatan yang tercatat.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- VII. PENGESAHAN / TANDA TANGAN RESMI -->
                    <div class="pt-4 grid grid-cols-2 gap-8 text-[11px] text-black page-break-inside-avoid">
                        <div class="space-y-12 text-center">
                            <div>
                                <p class="text-slate-600">Mengetahui,</p>
                                <p class="font-bold uppercase">Kepala Dinas PUPR Kab. Kolaka Utara</p>
                            </div>
                            <div class="pt-4">
                                <p class="font-bold underline">....................................................</p>
                                <p class="text-[10px] text-slate-600">NIP. ........................................</p>
                            </div>
                        </div>
                        <div class="space-y-12 text-center">
                            <div>
                                <p class="text-slate-600">Lasusua, {{ now()->translatedFormat('d F Y') }}</p>
                                <p class="font-bold uppercase">{{ $printerRoleLabel }}</p>
                            </div>
                            <div class="pt-4">
                                <p class="font-bold underline">{{ $printerUser?->name ?? 'Administrator Sistem' }}</p>
                                <p class="text-[10px] text-slate-600">SI-APPIK Kab. Kolaka Utara</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ========================================================= -->
                <!-- FORMAT B: FORMAT ATS (PLAIN, LINEAR, MACHINE-READABLE)    -->
                <!-- ========================================================= -->
                <div id="reportFormatAts" class="hidden space-y-4 font-sans text-black leading-relaxed">
                    <div class="border-b border-black pb-2">
                        <h2 class="text-base font-bold uppercase tracking-normal text-black">
                            LAPORAN REKAPITULASI PENANGANAN PENGADUAN INFRASTRUKTUR KABUPATEN KOLAKA UTARA
                        </h2>
                        <p class="text-[11px] text-black mt-1">
                            Instansi: Pemerintah Kabupaten Kolaka Utara - Dinas Pekerjaan Umum dan Penataan Ruang (PUPR)
                        </p>
                    </div>

                    <!-- 1. IDENTITAS DOKUMEN -->
                    <div>
                        <h3 class="text-xs font-bold uppercase border-b border-black pb-0.5 mb-1.5">1. IDENTITAS DOKUMEN</h3>
                        <div class="text-[11px] space-y-0.5 font-mono">
                            <div>Nomor Dokumen     : {{ $docNumber }}</div>
                            <div>Periode Laporan   : {{ now()->translatedFormat('F Y') }}</div>
                            <div>Waktu Rekapitulasi: {{ now()->translatedFormat('d F Y, H:i') }} WITA</div>
                            <div>Penyusun Laporan  : {{ $printerUser?->name ?? 'Sistem' }} ({{ $printerRoleLabel }})</div>
                        </div>
                    </div>

                    <!-- 2. RINGKASAN EKSEKUTIF & KPI -->
                    <div>
                        <h3 class="text-xs font-bold uppercase border-b border-black pb-0.5 mb-1.5">2. RINGKASAN EKSEKUTIF DAN INDIKATOR KINERJA UTAMA</h3>
                        <div class="text-[11px] space-y-0.5">
                            <div>- Total Pengaduan Masuk: {{ $summary['total'] }} Laporan ({{ $minorCount }} Kerusakan Titik/Ringan, {{ $majorCount }} Kerusakan Ruas Panjang/Berat)</div>
                            <div>- Tingkat Ketuntasan Penanganan: {{ $summary['completion_rate'] ?? 0 }}% ({{ $summary['completed'] ?? 0 }} dari {{ $summary['total'] ?? 0 }} Laporan Selesai)</div>
                            <div>- Kepatuhan SLA Dinas PUPR: {{ $slaComplianceRate }}% ({{ $totalOnTime }} Tepat Waktu, {{ $totalLate }} Terlambat)</div>
                            <div>- Rata-Rata Durasi Siklus Penanganan: {{ $avgDays }} Hari Kerja (Target SOP: Maksimal {{ $sopTargetDays }} Hari Kerja)</div>
                            <div>- Cakupan Wilayah Terdampak: {{ $activeDistricts->count() }} dari 15 Kecamatan Terdaftar</div>
                        </div>
                    </div>

                    <!-- 3. REKAPITULASI SIKLUS STATUS -->
                    <div>
                        <h3 class="text-xs font-bold uppercase border-b border-black pb-0.5 mb-1.5">3. REKAPITULASI SIKLUS STATUS PENANGANAN</h3>
                        <table class="w-full text-left border-collapse border border-black text-[11px]">
                            <thead>
                                <tr>
                                    <th class="border border-black py-1 px-2">Status Penanganan</th>
                                    <th class="border border-black py-1 px-2 text-right">Jumlah</th>
                                    <th class="border border-black py-1 px-2 text-right">Persentase</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                    $atsTotal = max(1, (int)($summary['total'] ?? 0));
                                    $atsStatuses = [
                                        ['label' => 'Total Laporan Masuk', 'count' => $summary['total'] ?? 0],
                                        ['label' => 'Menunggu Verifikasi (SUBMITTED)', 'count' => $summary['pending'] ?? 0],
                                        ['label' => 'Sudah Ditugaskan (DISPATCHED)', 'count' => $summary['dispatched'] ?? 0],
                                        ['label' => 'Sedang Dikerjakan (IN_PROGRESS)', 'count' => $summary['in_progress'] ?? 0],
                                        ['label' => 'Menunggu Validasi (WAITING_VERIFICATION)', 'count' => $summary['waiting_verification'] ?? 0],
                                        ['label' => 'Selesai / Tuntas (COMPLETED/CLOSED)', 'count' => $summary['completed'] ?? 0],
                                        ['label' => 'Ditolak (REJECTED)', 'count' => $summary['rejected'] ?? 0],
                                    ];
                                @endphp
                                @foreach($atsStatuses as $row)
                                    <tr>
                                        <td class="border border-black py-1 px-2">{{ $row['label'] }}</td>
                                        <td class="border border-black py-1 px-2 text-right">{{ $row['count'] }}</td>
                                        <td class="border border-black py-1 px-2 text-right">{{ $summary['total'] > 0 ? round(($row['count'] / $atsTotal) * 100) : 0 }}%</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <!-- 4. EVALUASI SLA & KONTRIBUSI PERAN -->
                    <div>
                        <h3 class="text-xs font-bold uppercase border-b border-black pb-0.5 mb-1.5">4. EVALUASI KETEPATAN WAKTU (SLA) DAN DISTRIBUSI KINERJA PERAN</h3>
                        <div class="text-[11px] space-y-0.5">
                            <div>- Kinerja Dinas PUPR (Tepat Waktu SLA): {{ $totalOnTime }} Laporan</div>
                            <div>- Kinerja Dinas PUPR (Terlambat Lewat SLA): {{ $totalLate }} Laporan</div>
                            <div>- Kontribusi Petugas Lapangan: {{ $fieldCount }} Tindakan Fisik ({{ $fieldPercent }}%)</div>
                            <div>- Kontribusi Operator Verifikasi: {{ $operatorCount }} Telaah Laporan ({{ $operatorPercent }}%)</div>
                            <div>- Kontribusi Dinas PUPR: {{ $deptCount }} Penyelesaian Akhir ({{ $deptPercent }}%)</div>
                        </div>
                    </div>

                    <!-- 5. REKAPITULASI PER KATEGORI -->
                    <div>
                        <h3 class="text-xs font-bold uppercase border-b border-black pb-0.5 mb-1.5">5. REKAPITULASI PER KATEGORI INFRASTRUKTUR</h3>
                        <table class="w-full text-left border-collapse border border-black text-[11px]">
                            <thead>
                                <tr>
                                    <th class="border border-black py-1 px-2">Kategori Infrastruktur</th>
                                    <th class="border border-black py-1 px-2 text-right">Jumlah Laporan</th>
                                    <th class="border border-black py-1 px-2 text-right">Persentase</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($categories as $cat)
                                    @php $p = $summary['total'] > 0 ? round(($cat->complaints_count / $summary['total']) * 100) : 0; @endphp
                                    <tr>
                                        <td class="border border-black py-1 px-2">{{ $cat->name }}</td>
                                        <td class="border border-black py-1 px-2 text-right">{{ $cat->complaints_count }}</td>
                                        <td class="border border-black py-1 px-2 text-right">{{ $p }}%</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <!-- 6. REKAPITULASI WILAYAH KECAMATAN -->
                    <div>
                        <h3 class="text-xs font-bold uppercase border-b border-black pb-0.5 mb-1.5">6. REKAPITULASI SEBARAN WILAYAH KECAMATAN</h3>
                        <table class="w-full text-left border-collapse border border-black text-[11px]">
                            <thead>
                                <tr>
                                    <th class="border border-black py-1 px-2">Kecamatan</th>
                                    <th class="border border-black py-1 px-2 text-right">Total</th>
                                    <th class="border border-black py-1 px-2 text-right">Diproses</th>
                                    <th class="border border-black py-1 px-2 text-right">Selesai</th>
                                    <th class="border border-black py-1 px-2 text-right">Ditolak</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($activeDistricts as $dist)
                                    @php
                                        $dTotal = (int)($dist['total'] ?? 0);
                                        $dDone = (int)($dist['completed'] ?? 0);
                                        $dRejected = (int)($dist['rejected'] ?? 0);
                                        $dProcess = max(0, $dTotal - $dDone - $dRejected);
                                    @endphp
                                    <tr>
                                        <td class="border border-black py-1 px-2">{{ $dist['district'] }}</td>
                                        <td class="border border-black py-1 px-2 text-right">{{ $dTotal }}</td>
                                        <td class="border border-black py-1 px-2 text-right">{{ $dProcess }}</td>
                                        <td class="border border-black py-1 px-2 text-right">{{ $dDone }}</td>
                                        <td class="border border-black py-1 px-2 text-right">{{ $dRejected }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="border border-black py-1 px-2">Tidak ada data wilayah aktif.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- 7. PENGESAHAN -->
                    <div class="pt-2 border-t border-black text-[11px]">
                        <div>Diterbitkan secara otomatis oleh Sistem Informasi Pengaduan &amp; Penanganan Kerusakan Infrastruktur (SI-APPIK) Kabupaten Kolaka Utara.</div>
                        <div>Lokasi &amp; Tanggal: Lasusua, {{ now()->translatedFormat('d F Y') }} &bull; Penanggung Jawab: {{ $printerUser?->name ?? 'Administrator' }} ({{ $printerRoleLabel }}).</div>
                    </div>
                </div>

            </div>
        </div>

    </div>
</div>

<!-- STYLES KHUSUS CETAK DOKUMEN (PRINT TO PDF A4) -->
<style>
@media print {
    body * {
        visibility: hidden !important;
    }
    #officialReportModal,
    #printableOfficialDocument,
    #printableOfficialDocument * {
        visibility: visible !important;
    }
    #officialReportModal {
        position: absolute !important;
        left: 0 !important;
        top: 0 !important;
        width: 100% !important;
        height: auto !important;
        background: transparent !important;
        padding: 0 !important;
        display: block !important;
        overflow: visible !important;
    }
    #printableOfficialDocument {
        border: none !important;
        box-shadow: none !important;
        padding: 0 !important;
        margin: 0 !important;
        width: 100% !important;
        max-width: 100% !important;
    }
    .page-break-inside-avoid {
        break-inside: avoid;
        page-break-inside: avoid;
    }
    @page {
        size: A4 portrait;
        margin: 1.5cm;
    }
}
</style>
