<!-- Modul Monitoring Eksekutif & Pelaporan Resmi -->
@php
    $rawReports = $allComplaintsForReport ?? collect();
    $availableReportPeriods = $rawReports
        ->filter(fn($c) => !empty($c->created_at))
        ->map(fn($c) => \Carbon\Carbon::parse($c->created_at)->format('Y-m'))
        ->unique()
        ->sortDesc()
        ->values()
        ->mapWithKeys(function($ym) {
            $dt = \Carbon\Carbon::createFromFormat('Y-m', $ym)->locale('id');
            return [$ym => $dt->translatedFormat('F Y')];
        });

    $selectedReportPeriod = request('report_period', 'all');
    if ($selectedReportPeriod !== 'all' && !$availableReportPeriods->has($selectedReportPeriod)) {
        $selectedReportPeriod = 'all';
    }

    $repPeriodLabel = $selectedReportPeriod === 'all'
        ? 'Semua Periode'
        : ($availableReportPeriods->get($selectedReportPeriod) ?? 'Semua Periode');

    $allReports = $selectedReportPeriod === 'all'
        ? $rawReports
        : $rawReports->filter(fn($c) => $c->created_at && \Carbon\Carbon::parse($c->created_at)->format('Y-m') === $selectedReportPeriod)->values();

    if ($selectedReportPeriod !== 'all') {
        $rTotal = $allReports->count();
        $rDone = $allReports->whereIn('status', ['COMPLETED', 'CLOSED'])->count();
        $summary = [
            'total'                => $rTotal,
            'pending'              => $allReports->whereIn('status', ['SUBMITTED', 'VERIFICATION', 'VERIFIED'])->count(),
            'dispatched'           => $allReports->where('status', 'DISPATCHED')->count(),
            'in_progress'          => $allReports->whereIn('status', ['IN_PROGRESS', 'REVISION_REQUIRED'])->count(),
            'waiting_verification' => $allReports->where('status', 'WAITING_VERIFICATION')->count(),
            'completed'            => $rDone,
            'rejected'             => $allReports->where('status', 'REJECTED')->count(),
            'completion_rate'      => $rTotal > 0 ? round(($rDone / $rTotal) * 100) : 0,
        ];

        $categories = $categories->map(function($cat) use ($allReports) {
            $clone = clone $cat;
            $clone->complaints_count = $allReports->where('category_id', $cat->id)->count();
            return $clone;
        });

        $activeDistricts = collect($districtStats ?? [])->map(function($d) use ($allReports) {
            $distName = strtolower(str_replace('kecamatan ', '', $d['district'] ?? ''));
            $villName = strtolower(str_replace(['desa ', 'kelurahan '], '', $d['village'] ?? ''));
            $matched = $allReports->filter(function($cmp) use ($d, $distName, $villName) {
                $addr = strtolower($cmp->address ?? '');
                return (isset($d['id']) && $cmp->region_id === $d['id'])
                    || ($distName && str_contains($addr, $distName))
                    || ($villName && str_contains($addr, $villName));
            });
            $d['total'] = $matched->count();
            $d['completed'] = $matched->whereIn('status', ['COMPLETED', 'CLOSED'])->count();
            $d['rejected'] = $matched->where('status', 'REJECTED')->count();
            return $d;
        })->filter(fn($d) => ($d['total'] ?? 0) > 0)->values();
    } else {
        $activeDistricts = collect($districtStats ?? [])->filter(fn($d) => ($d['total'] ?? 0) > 0)->values();
    }

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

    $minorCount = $allReports->where('damage_type', '!=', 'major')->count();
    $majorCount = $allReports->where('damage_type', 'major')->count();

    $avgDays = $avgPerformance['overall_days'] ?? 4.1;
    $sopTargetDays = $avgPerformance['sop_target_days'] ?? 5.0;
    $printerUser = auth()->user();
    $printerRoleLabel = match($printerUser?->role) {
        'SUPERADMIN', 'ADMIN' => 'Super Administrator',
        'KEPALA_DAERAH', 'BUPATI' => 'Kepala Daerah (Bupati)',
        'OPERATOR' => 'Operator Verifikasi PUPR',
        default => 'Pejabat Berwenang',
    };
    $docNumber = 'LAP-PUPR/' . ($selectedReportPeriod === 'all' ? now()->format('Y/m') : str_replace('-', '/', $selectedReportPeriod)) . '/' . str_pad((string)($summary['total'] ?? 0), 3, '0', STR_PAD_LEFT);
    $totalRep = max(1, (int)($summary['total'] ?? 0));
    $minorPct = ($minorCount + $majorCount) > 0 ? round(($minorCount / ($minorCount + $majorCount)) * 100) : 100;
    $reportTeams = isset($teams) ? $teams : \App\Models\Team::with('leader')->get();
@endphp

<!-- 1. TOOLBAR AKSI LAPORAN RESMI (PDF) — SI-APPIK V2 -->
<div class="card-white rounded-[20px] p-4 sm:p-5 bg-white border border-[#E2E8F0] flex flex-col xl:flex-row xl:items-center justify-between gap-4">
    <div class="flex items-center gap-3 min-w-0">
        <div class="w-10 h-10 rounded-xl bg-[#EFF6FF] border border-[#BFDBFE] text-[#2563EB] flex items-center justify-center shrink-0">
            <i data-lucide="file-check-2" class="w-5 h-5"></i>
        </div>
        <div class="min-w-0 space-y-0.5">
            <span class="text-[10px] font-bold uppercase tracking-wider text-[#2563EB] block truncate">
                Dokumen Rekapitulasi &bull; Dinas PUPR &bull; {{ $docNumber }}
            </span>
            <h3 class="font-outfit font-bold text-base sm:text-lg text-[#0F172A] tracking-tight leading-snug">
                Laporan &amp; Evaluasi Kinerja Infrastruktur
            </h3>
        </div>
    </div>

    <!-- Filter Periode (Bulan & Tahun) + Tombol Aksi & Pilih Format Dokumen -->
    <div class="flex flex-col sm:flex-row sm:items-center gap-2 w-full xl:w-auto shrink-0 flex-wrap">
        <!-- Filter Periode Bulan & Tahun (Hanya periode yang memiliki laporan masuk) -->
        <div class="inline-flex items-center gap-1.5 px-2.5 py-1.5 rounded-xl bg-[#F8FAFC] border border-[#E2E8F0] w-full sm:w-auto">
            <i data-lucide="calendar" class="w-3.5 h-3.5 text-[#2563EB] shrink-0"></i>
            <select id="reportPeriodSelect" onchange="applyReportPeriodFilter(this.value)"
                class="bg-transparent text-xs font-bold text-[#0F172A] focus:outline-none cursor-pointer pr-1 w-full sm:w-auto"
                aria-label="Pilih Periode Bulan dan Tahun Laporan">
                <option value="all" {{ $selectedReportPeriod === 'all' ? 'selected' : '' }}>Semua Periode</option>
                @foreach($availableReportPeriods as $ymVal => $ymLabel)
                    <option value="{{ $ymVal }}" {{ $selectedReportPeriod === $ymVal ? 'selected' : '' }}>{{ $ymLabel }}</option>
                @endforeach
            </select>
        </div>

        <div class="grid grid-cols-2 sm:inline-flex items-center p-1 rounded-xl bg-[#F1F5F9] border border-[#E2E8F0] w-full sm:w-auto" role="group" aria-label="Pilih Format Laporan">
            <button type="button" onclick="setReportFormat('professional')" data-format-btn="professional"
                class="px-3 py-1.5 rounded-lg text-[11px] font-bold transition cursor-pointer text-center bg-white text-[#2563EB] border border-[#E2E8F0]">
                Profesional
            </button>
            <button type="button" onclick="setReportFormat('ats')" data-format-btn="ats"
                class="px-3 py-1.5 rounded-lg text-[11px] font-bold transition cursor-pointer text-center text-[#475569] hover:text-[#0F172A] border border-transparent">
                Format ATS
            </button>
        </div>

        <div class="grid grid-cols-2 sm:flex items-center gap-2 w-full sm:w-auto">
            <button type="button" onclick="openReportPreviewModal()"
                class="px-3.5 py-2 rounded-xl text-xs font-bold bg-[#F8FAFC] hover:bg-slate-100 text-[#334155] border border-[#E2E8F0] transition flex items-center justify-center gap-1.5 cursor-pointer whitespace-nowrap">
                <i data-lucide="eye" class="w-4 h-4 text-[#2563EB] shrink-0"></i>
                <span>Pratinjau</span>
            </button>

            <button type="button" onclick="printOfficialReportPdf()"
                class="px-4 py-2 rounded-xl text-xs font-bold bg-[#2563EB] hover:bg-[#1D4ED8] text-white transition flex items-center justify-center gap-1.5 cursor-pointer whitespace-nowrap">
                <i data-lucide="printer" class="w-4 h-4 shrink-0"></i>
                <span>Cetak PDF</span>
            </button>
        </div>
    </div>
</div>

<!-- 2. 4-CARD EXECUTIVE PERFORMANCE KPI STRIP -->
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
    <div class="rounded-2xl p-4 bg-white border border-[#E2E8F0] space-y-1">
        <span class="text-[10px] font-bold uppercase tracking-wider text-[#64748B] block">Total Aduan Masuk</span>
        <span class="font-outfit font-bold text-2xl text-[#0F172A] block">{{ $summary['total'] ?? 0 }} Laporan</span>
        <span class="text-[11px] text-[#64748B] block">Periode: {{ $repPeriodLabel }}</span>
    </div>

    <div class="rounded-2xl p-4 bg-white border border-[#E2E8F0] space-y-1">
        <span class="text-[10px] font-bold uppercase tracking-wider text-[#64748B] block">Tingkat Penyelesaian</span>
        <span class="font-outfit font-bold text-2xl text-[#10B981] block">{{ $summary['completion_rate'] ?? 0 }}%</span>
        <span class="text-[11px] text-[#64748B] block">{{ $summary['completed'] ?? 0 }} dari {{ $summary['total'] ?? 0 }} aduan tuntas</span>
    </div>

    <div class="rounded-2xl p-4 bg-white border border-[#E2E8F0] space-y-1">
        <span class="text-[10px] font-bold uppercase tracking-wider text-[#64748B] block">Kepatuhan SLA (&le; {{ $sopTargetDays }} Hari)</span>
        <span class="font-outfit font-bold text-2xl text-[#2563EB] block">{{ $slaComplianceRate }}%</span>
        <span class="text-[11px] text-[#64748B] block">Rata-rata {{ $avgDays }} hari / aduan</span>
    </div>

    <div class="rounded-2xl p-4 bg-white border border-[#E2E8F0] space-y-1">
        <span class="text-[10px] font-bold uppercase tracking-wider text-[#64748B] block">Klasifikasi Kerusakan</span>
        <span class="font-outfit font-bold text-xl text-[#0F172A] block pt-0.5">{{ $minorCount }} Ringan / {{ $majorCount }} Berat</span>
        <span class="text-[11px] text-[#64748B] block">{{ $minorPct }}% kerusakan titik minor</span>
    </div>
</div>

<!-- 3. BARIS ANALITIK: STATUS SIKLUS PENANGANAN (7 KOLOM) & SEBARAN KATEGORI (5 KOLOM) -->
<div class="grid grid-cols-1 lg:grid-cols-12 gap-5 items-stretch">
    <!-- Kiri: Status Progres Siklus Penanganan -->
    <div class="lg:col-span-7 rounded-[20px] p-5 bg-white border border-[#E2E8F0] flex flex-col justify-between space-y-4">
        <div class="flex items-center justify-between gap-2">
            <h3 class="font-outfit font-bold text-[15px] text-[#0F172A]">
                Status Progres Siklus Penanganan Pengaduan
            </h3>
            <span class="text-xs font-bold text-[#2563EB]">
                Total: {{ $summary['total'] ?? 0 }} Laporan
            </span>
        </div>

        @php
            $stageRows = [
                ['label' => 'Verifikasi', 'count' => $summary['pending'] ?? 0, 'color' => '#EF4444'],
                ['label' => 'Ditugaskan', 'count' => $summary['dispatched'] ?? 0, 'color' => '#2563EB'],
                ['label' => 'Dikerjakan', 'count' => $summary['in_progress'] ?? 0, 'color' => '#F59E0B'],
                ['label' => 'Validasi', 'count' => $summary['waiting_verification'] ?? 0, 'color' => '#F59E0B'],
                ['label' => 'Selesai', 'count' => $summary['completed'] ?? 0, 'color' => '#10B981'],
                ['label' => 'Ditolak', 'count' => $summary['rejected'] ?? 0, 'color' => '#DC2626'],
            ];
        @endphp

        <div class="space-y-3.5 my-auto">
            @foreach($stageRows as $st)
                @php $pct = ($summary['total'] ?? 0) > 0 ? round(($st['count'] / $totalRep) * 100) : 0; @endphp
                <div class="grid grid-cols-12 items-center gap-3">
                    <div class="col-span-4 sm:col-span-3 text-xs font-semibold text-[#334155]">
                        {{ $st['label'] }}
                    </div>
                    <div class="col-span-5 sm:col-span-6">
                        <div class="w-full h-2.5 rounded-full bg-[#F1F5F9] overflow-hidden">
                            <div class="h-full rounded-full transition-all duration-300" style="width: {{ $pct }}%; background-color: {{ $st['color'] }};"></div>
                        </div>
                    </div>
                    <div class="col-span-3 text-right text-[11px] font-bold {{ $pct > 0 ? 'text-[#0F172A]' : 'text-[#64748B]' }} whitespace-nowrap">
                        {{ $st['count'] }} dari {{ $summary['total'] ?? 0 }} ({{ $pct }}%)
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <!-- Kanan: Sebaran Kategori Infrastruktur -->
    <div class="lg:col-span-5 rounded-[20px] p-5 bg-white border border-[#E2E8F0] flex flex-col justify-between space-y-4">
        <div class="flex items-center justify-between gap-2">
            <h3 class="font-outfit font-bold text-[15px] text-[#0F172A]">
                Sebaran Kategori Infrastruktur
            </h3>
            <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-[#F8FAFC] text-[#475569] border border-[#E2E8F0]">
                {{ $categories->count() }} Kategori
            </span>
        </div>

        @php
            $catColors = ['#2563EB', '#10B981', '#F59E0B', '#EF4444', '#6366F1'];
        @endphp
        <div class="space-y-2.5 flex-1 max-h-[250px] overflow-y-auto pr-1">
            @foreach($categories as $cat)
                @php
                    $cColor = $catColors[$loop->index % count($catColors)];
                    $cPct = ($summary['total'] ?? 0) > 0 ? round(($cat->complaints_count / $totalRep) * 100) : 0;
                @endphp
                <div class="p-3.5 rounded-[14px] bg-[#F8FAFC] border border-[#E2E8F0] space-y-2">
                    <div class="flex items-center justify-between gap-2 text-xs">
                        <span class="font-bold text-[#0F172A] truncate">{{ $cat->name }}</span>
                        <span class="font-bold shrink-0" style="color: {{ $cColor }};">{{ $cat->complaints_count }} Aduan ({{ $cPct }}%)</span>
                    </div>
                    <div class="w-full h-2 rounded-full bg-[#E2E8F0] overflow-hidden">
                        <div class="h-full rounded-full" style="width: {{ $cPct }}%; background-color: {{ $cColor }};"></div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>

<!-- 4. BARIS TABEL EKSEKUTIF: KINERJA REGU PUPR (KIRI) & REKAPITULASI ADUAN PDF (KANAN) -->
<div class="grid grid-cols-1 lg:grid-cols-12 gap-5 items-stretch">
    <!-- Kiri: Evaluasi Kinerja Regu Lapangan PUPR -->
    <div class="lg:col-span-6 rounded-[20px] p-5 bg-white border border-[#E2E8F0] flex flex-col space-y-3.5">
        <div class="flex items-center justify-between gap-2">
            <h3 class="font-outfit font-bold text-[15px] text-[#0F172A]">
                Evaluasi Kinerja Regu Lapangan PUPR
            </h3>
            <span class="text-[11px] font-semibold text-[#64748B]">{{ $reportTeams->count() }} Regu</span>
        </div>

        <div class="space-y-2.5 flex-1">
            @forelse($reportTeams as $rt)
                @php
                    $rtActive = \App\Models\Disposition::where('team_id', $rt->id)
                        ->whereHas('complaint', fn($q) => $q->whereIn('status', ['DISPATCHED', 'IN_PROGRESS', 'WAITING_VERIFICATION', 'REVISION_REQUIRED']))
                        ->count();
                    $rtDone = \App\Models\Disposition::where('team_id', $rt->id)
                        ->whereHas('complaint', fn($q) => $q->whereIn('status', ['COMPLETED', 'CLOSED']))
                        ->count();
                @endphp
                <div class="p-3.5 rounded-[14px] bg-[#F8FAFC] border border-[#E2E8F0] flex items-center justify-between gap-3">
                    <div class="min-w-0">
                        <span class="font-outfit font-bold text-[13px] text-[#0F172A] block truncate">
                            [{{ $rt->code }}] {{ $rt->name }}
                        </span>
                        <span class="text-[11px] text-[#64748B] block mt-0.5 truncate">
                            Ketua: {{ $rt->leader?->name ?? 'Belum ditunjuk' }} &bull; {{ $rtActive }} Tugas Aktif &bull; {{ $rtDone }} Tuntas
                        </span>
                    </div>
                    @if($rtDone > 0)
                        <span class="text-xs font-bold text-[#10B981] shrink-0 whitespace-nowrap">SLA 100%</span>
                    @else
                        <span class="text-xs font-bold text-[#2563EB] shrink-0 whitespace-nowrap">Siap {{ max(0, 3 - $rtActive) }}/3</span>
                    @endif
                </div>
            @empty
                <div class="p-6 text-center text-xs text-[#64748B] bg-[#F8FAFC] rounded-xl border border-[#E2E8F0]">
                    Belum ada regu lapangan terdaftar.
                </div>
            @endforelse
        </div>
    </div>

    <!-- Kanan: Rekapitulasi Kinerja per Wilayah Kecamatan -->
    <div class="lg:col-span-6 rounded-[20px] p-5 bg-white border border-[#E2E8F0] flex flex-col space-y-3.5">
        <div class="flex items-center justify-between gap-2">
            <h3 class="font-outfit font-bold text-[15px] text-[#0F172A]">
                Rekapitulasi Kinerja per Wilayah Kecamatan
            </h3>
            <span class="text-[11px] font-semibold text-[#64748B]">{{ $activeDistricts->count() }} Kec. Aktif</span>
        </div>

        <div class="space-y-2.5 flex-1 max-h-[300px] overflow-y-auto pr-1">
            @forelse($activeDistricts as $dist)
                @php
                    $dTotal = (int)($dist['total'] ?? 0);
                    $dDone = (int)($dist['completed'] ?? 0);
                    $dRejected = (int)($dist['rejected'] ?? 0);
                    $dProcess = max(0, $dTotal - $dDone - $dRejected);
                    $dRate = $dTotal > 0 ? round(($dDone / $dTotal) * 100) : 0;
                @endphp
                <div class="p-3.5 rounded-[14px] bg-[#F8FAFC] border border-[#E2E8F0] flex items-center justify-between gap-3">
                    <div class="min-w-0">
                        <span class="font-outfit font-bold text-[13px] text-[#0F172A] block truncate">
                            {{ $dist['district'] }}
                        </span>
                        <span class="text-[11px] text-[#64748B] block mt-0.5 truncate">
                            {{ $dTotal }} Masuk &bull; {{ $dProcess }} Diproses &bull; {{ $dDone }} Selesai
                        </span>
                    </div>
                    <span class="text-xs font-bold {{ $dRate >= 50 ? 'text-[#10B981]' : 'text-[#2563EB]' }} shrink-0 whitespace-nowrap">
                        {{ $dRate }}% Tuntas
                    </span>
                </div>
            @empty
                <div class="p-6 text-center text-xs text-[#64748B] bg-[#F8FAFC] rounded-xl border border-[#E2E8F0]">
                    Belum ada aduan masuk per wilayah kecamatan.
                </div>
            @endforelse
        </div>
    </div>
</div>

<!-- 5. MODAL PRATINJAU DOKUMEN REKAPITULASI (FORMAT PROFESIONAL & FORMAT ATS — SI-APPIK V2) -->
<div id="officialReportModal" class="hidden fixed inset-0 z-50 overflow-y-auto bg-slate-900/65 flex items-center justify-center p-3 sm:p-5 overscroll-contain">
    <div class="card-white rounded-[24px] bg-white border border-[#E2E8F0] shadow-2xl max-w-4xl w-full max-h-[92vh] overflow-hidden flex flex-col relative">

        <!-- Header Modal Preview -->
        <div class="px-6 py-4 border-b border-[#E2E8F0] flex flex-wrap items-center justify-between bg-white gap-3 shrink-0">
            <div class="flex items-center gap-3 min-w-0">
                <div class="w-10 h-10 rounded-xl bg-[#EFF6FF] border border-blue-100 text-[#2563EB] flex items-center justify-center shrink-0">
                    <i data-lucide="file-text" class="w-5 h-5"></i>
                </div>
                <div class="min-w-0">
                    <h4 class="font-outfit font-bold text-base text-[#0F172A] truncate">
                        Pratinjau Dokumen Laporan Kinerja
                    </h4>
                    <p id="modalReportFormatSubtitle" class="text-[11px] text-[#64748B] truncate">
                        Format Profesional Kedinasan &bull; Kabupaten Kolaka Utara
                    </p>
                </div>
            </div>

            <div class="flex flex-wrap items-center gap-2.5 shrink-0">
                <!-- Toggle Format Profesional vs Format ATS -->
                <div class="inline-flex items-center p-1 rounded-xl bg-[#F1F5F9] border border-[#E2E8F0]">
                    <button type="button" onclick="setReportFormat('professional')" data-format-btn="professional"
                        class="px-3 py-1.5 rounded-lg text-[11px] font-bold transition cursor-pointer bg-white text-[#2563EB] border border-[#E2E8F0]">
                        Format Profesional
                    </button>
                    <button type="button" onclick="setReportFormat('ats')" data-format-btn="ats"
                        class="px-3 py-1.5 rounded-lg text-[11px] font-bold transition cursor-pointer text-[#475569] hover:text-[#0F172A] border border-transparent">
                        Format ATS
                    </button>
                </div>

                <button type="button" onclick="closeReportPreviewModal()"
                    class="w-9 h-9 rounded-[10px] bg-white hover:bg-slate-100 text-slate-500 border border-[#E2E8F0] flex items-center justify-center transition cursor-pointer shrink-0"
                    aria-label="Tutup">
                    <i data-lucide="x" class="w-4 h-4"></i>
                </button>
            </div>
        </div>

        <!-- Body Pratinjau Dokumen -->
        <div id="reportModalScrollArea" class="flex-1 overflow-y-auto overflow-x-hidden bg-white p-3.5 sm:p-8">
            <div id="printableOfficialDocument" class="w-full min-w-0 max-w-full text-black font-sans text-xs pb-6">

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
                                Periode Pelaporan: <strong>{{ $repPeriodLabel }}</strong> &bull; No. Dokumen: <span class="font-mono font-semibold">{{ $docNumber }}</span>
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
                        <div class="w-full overflow-x-auto">
                            <table class="w-full text-left border-collapse border border-slate-400 text-[9.5px] sm:text-[11px]">
                                <thead class="bg-slate-100 text-black font-bold uppercase text-[8.5px] sm:text-[10px]">
                                    <tr>
                                        <th class="border border-slate-400 py-1.5 px-1.5 sm:px-2.5">Total</th>
                                        <th class="border border-slate-400 py-1.5 px-1 sm:px-2 text-center">Verifikasi</th>
                                        <th class="border border-slate-400 py-1.5 px-1 sm:px-2 text-center">Ditugaskan</th>
                                        <th class="border border-slate-400 py-1.5 px-1 sm:px-2 text-center">Dikerjakan</th>
                                        <th class="border border-slate-400 py-1.5 px-1 sm:px-2 text-center">Validasi</th>
                                        <th class="border border-slate-400 py-1.5 px-1 sm:px-2 text-center">Selesai</th>
                                        <th class="border border-slate-400 py-1.5 px-1 sm:px-2 text-center">Ditolak</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td class="border border-slate-400 py-2 px-1.5 sm:px-2.5 font-bold text-black">{{ $summary['total'] }} (100%)</td>
                                        <td class="border border-slate-400 py-2 px-1 sm:px-2 text-center font-semibold">{{ $summary['pending'] }}</td>
                                        <td class="border border-slate-400 py-2 px-1 sm:px-2 text-center font-semibold">{{ $summary['dispatched'] }}</td>
                                        <td class="border border-slate-400 py-2 px-1 sm:px-2 text-center font-semibold">{{ $summary['in_progress'] }}</td>
                                        <td class="border border-slate-400 py-2 px-1 sm:px-2 text-center font-semibold">{{ $summary['waiting_verification'] ?? 0 }}</td>
                                        <td class="border border-slate-400 py-2 px-1 sm:px-2 text-center font-bold">{{ $summary['completed'] }} ({{ $summary['completion_rate'] }}%)</td>
                                        <td class="border border-slate-400 py-2 px-1 sm:px-2 text-center font-semibold">{{ $summary['rejected'] ?? 0 }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
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

                    <!-- V. EVALUASI KINERJA REGU LAPANGAN PUPR -->
                    <div class="space-y-2">
                        <h4 class="font-outfit font-bold text-xs uppercase tracking-wider text-black border-b border-slate-300 pb-1">
                            V. Evaluasi Kinerja Regu Lapangan PUPR
                        </h4>
                        <table class="w-full text-left border-collapse border border-slate-400 text-[11px]">
                            <thead class="bg-slate-100 text-black font-bold">
                                <tr>
                                    <th class="border border-slate-400 py-1.5 px-2.5">Regu Lapangan</th>
                                    <th class="border border-slate-400 py-1.5 px-2.5">Ketua Regu</th>
                                    <th class="border border-slate-400 py-1.5 px-2.5 text-center">Aktif</th>
                                    <th class="border border-slate-400 py-1.5 px-2.5 text-center">Tuntas</th>
                                    <th class="border border-slate-400 py-1.5 px-2.5 text-right">Status Kinerja</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($reportTeams as $rt)
                                    @php
                                        $rtActive = \App\Models\Disposition::where('team_id', $rt->id)
                                            ->whereHas('complaint', fn($q) => $q->whereIn('status', ['DISPATCHED', 'IN_PROGRESS', 'WAITING_VERIFICATION', 'REVISION_REQUIRED']))
                                            ->count();
                                        $rtDone = \App\Models\Disposition::where('team_id', $rt->id)
                                            ->whereHas('complaint', fn($q) => $q->whereIn('status', ['COMPLETED', 'CLOSED']))
                                            ->count();
                                    @endphp
                                    <tr>
                                        <td class="border border-slate-400 py-1.5 px-2.5 font-semibold">[{{ $rt->code }}] {{ $rt->name }}</td>
                                        <td class="border border-slate-400 py-1.5 px-2.5">{{ $rt->leader?->name ?? 'Belum ditunjuk' }}</td>
                                        <td class="border border-slate-400 py-1.5 px-2.5 text-center">{{ $rtActive }}</td>
                                        <td class="border border-slate-400 py-1.5 px-2.5 text-center font-bold">{{ $rtDone }}</td>
                                        <td class="border border-slate-400 py-1.5 px-2.5 text-right font-bold">{{ $rtDone > 0 ? 'SLA 100%' : 'Siap ' . max(0, 3 - $rtActive) . '/3' }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="border border-slate-400 py-2 px-2.5 text-center text-slate-500">Belum ada regu terdaftar.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- VI. REKAPITULASI PER KATEGORI INFRASTRUKTUR -->
                    <div class="space-y-2">
                        <h4 class="font-outfit font-bold text-xs uppercase tracking-wider text-black border-b border-slate-300 pb-1">
                            VI. Rekapitulasi per Kategori Infrastruktur
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

                    <!-- 4B. EVALUASI KINERJA REGU LAPANGAN PUPR -->
                    <div>
                        <h3 class="text-xs font-bold uppercase border-b border-black pb-0.5 mb-1.5">4B. EVALUASI KINERJA REGU LAPANGAN PUPR</h3>
                        <table class="w-full text-left border-collapse border border-black text-[11px]">
                            <thead>
                                <tr>
                                    <th class="border border-black py-1 px-2">Regu Lapangan</th>
                                    <th class="border border-black py-1 px-2">Ketua Regu</th>
                                    <th class="border border-black py-1 px-2 text-right">Aktif</th>
                                    <th class="border border-black py-1 px-2 text-right">Tuntas</th>
                                    <th class="border border-black py-1 px-2 text-right">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($reportTeams as $rt)
                                    @php
                                        $rtActive = \App\Models\Disposition::where('team_id', $rt->id)
                                            ->whereHas('complaint', fn($q) => $q->whereIn('status', ['DISPATCHED', 'IN_PROGRESS', 'WAITING_VERIFICATION', 'REVISION_REQUIRED']))
                                            ->count();
                                        $rtDone = \App\Models\Disposition::where('team_id', $rt->id)
                                            ->whereHas('complaint', fn($q) => $q->whereIn('status', ['COMPLETED', 'CLOSED']))
                                            ->count();
                                    @endphp
                                    <tr>
                                        <td class="border border-black py-1 px-2">[{{ $rt->code }}] {{ $rt->name }}</td>
                                        <td class="border border-black py-1 px-2">{{ $rt->leader?->name ?? 'Belum ditunjuk' }}</td>
                                        <td class="border border-black py-1 px-2 text-right">{{ $rtActive }}</td>
                                        <td class="border border-black py-1 px-2 text-right">{{ $rtDone }}</td>
                                        <td class="border border-black py-1 px-2 text-right">{{ $rtDone > 0 ? 'SLA 100%' : 'Siap ' . max(0, 3 - $rtActive) . '/3' }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="border border-black py-1 px-2">Belum ada regu terdaftar.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
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

        <!-- Footer Modal SI-APPIK V2 -->
        <div class="px-6 py-3.5 border-t border-[#E2E8F0] bg-white flex items-center justify-end gap-2.5 shrink-0">
            <button type="button" onclick="closeReportPreviewModal()"
                class="px-5 py-2.5 rounded-xl bg-[#F1F5F9] hover:bg-slate-200 text-[#334155] font-bold text-xs transition cursor-pointer">
                Tutup
            </button>
            <button type="button" onclick="printOfficialReportPdf()"
                class="px-5 py-2.5 rounded-xl bg-[#2563EB] hover:bg-[#1D4ED8] text-white font-bold text-xs transition flex items-center gap-1.5 cursor-pointer">
                <i data-lucide="printer" class="w-3.5 h-3.5"></i>
                <span>Cetak PDF</span>
            </button>
        </div>

    </div>
</div>

<script>
    function applyReportPeriodFilter(period) {
        const url = new URL(window.location.href);
        if (!url.searchParams.get('tab')) {
            if (window.location.pathname.includes('/operator')) {
                url.searchParams.set('tab', 'monitoring');
            } else {
                url.searchParams.set('tab', 'reporting');
            }
        }
        if (period && period !== 'all') {
            url.searchParams.set('report_period', period);
        } else {
            url.searchParams.delete('report_period');
        }
        window.location.href = url.toString();
    }
</script>

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
