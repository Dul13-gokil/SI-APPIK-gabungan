@php
    $opPerfService = app(\App\Services\PerformanceMetricsService::class);
    $opAvailablePeriods = \App\Services\PerformanceMetricsService::getAvailablePeriods();
    $opSelectedPeriod = request('perf_period', 'ALL');
    [$opPerfFrom, $opPerfTo, $opSelectedPeriodLabel] = \App\Services\PerformanceMetricsService::resolvePeriodBounds($opSelectedPeriod);
    $operatorMetricsList = $opPerfService->getOperatorMetrics($opPerfFrom, $opPerfTo);

    $totalOps = count($operatorMetricsList);
    $sumOpVerified = array_sum(array_column($operatorMetricsList, 'verified_dispatched'));
    $sumOpRejected = array_sum(array_column($operatorMetricsList, 'rejected'));
    $sumOpApproved = array_sum(array_column($operatorMetricsList, 'validated_approved'));
    $sumOpRevision = array_sum(array_column($operatorMetricsList, 'validated_revision'));
@endphp

<!-- Tab Khusus: Pemantauan Kinerja Operator PUPR -->
<div class="space-y-4">
    <!-- Header 1 Baris Rapi + Filter Periode (Desain Pill Kalender) + Toggle Daftar/Petak -->
    <div class="card-white rounded-2xl p-4 sm:p-5 bg-white border border-[#E2E8F0] shadow-xs">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div class="flex items-center gap-2.5 min-w-0">
                <div class="w-9 h-9 rounded-xl bg-[#EFF6FF] text-[#2563EB] border border-[#BFDBFE] flex items-center justify-center shrink-0">
                    <i data-lucide="shield-check" class="w-4 h-4"></i>
                </div>
                <div class="min-w-0">
                    <h3 class="font-outfit font-bold text-sm sm:text-base text-[#0F172A] truncate">Pemantauan Kinerja Operator PUPR</h3>
                    <p class="text-[11px] font-semibold text-[#64748B]">Periode: {{ $opSelectedPeriodLabel }}</p>
                </div>
            </div>

            <div class="flex flex-wrap items-center gap-2">
                <!-- Filter Periode Pill (Desain Kalender Biru) -->
                <div class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full bg-[#F8FAFC] border border-[#E2E8F0] flex-1 sm:flex-initial">
                    <i data-lucide="calendar" class="w-3.5 h-3.5 text-[#2563EB] shrink-0"></i>
                    <select onchange="changeExecPerfPeriod(this.value, 'operator')"
                        class="bg-transparent text-xs font-bold text-[#0F172A] focus:outline-none cursor-pointer pr-1 w-full sm:w-auto"
                        aria-label="Pilih Periode Kinerja Operator">
                        @foreach($opAvailablePeriods as $per)
                            <option value="{{ $per['value'] }}" {{ $opSelectedPeriod === $per['value'] ? 'selected' : '' }}>
                                {{ $per['label'] }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Toggle Tata Letak Daftar / Petak -->
                <div class="inline-flex items-center p-0.5 rounded-xl bg-[#F8FAFC] border border-[#E2E8F0] shrink-0" role="group">
                    <button type="button" onclick="setExecPerfLayout('list')"
                        class="exec-layout-btn-list px-2.5 py-1.5 rounded-lg text-[11px] font-bold transition cursor-pointer inline-flex items-center gap-1 bg-[#2563EB] text-white">
                        <i data-lucide="list" class="w-3 h-3"></i><span>Daftar</span>
                    </button>
                    <button type="button" onclick="setExecPerfLayout('grid')"
                        class="exec-layout-btn-grid px-2.5 py-1.5 rounded-lg text-[11px] font-semibold transition cursor-pointer inline-flex items-center gap-1 text-[#475569]">
                        <i data-lucide="layout-grid" class="w-3 h-3"></i><span>Petak</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- 4 Kartu Ringkasan KPI Operator -->
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
        <div class="rounded-2xl p-4 bg-white border border-[#E2E8F0]">
            <p class="text-[11px] font-semibold text-[#64748B]">Total Operator</p>
            <p class="font-outfit font-bold text-2xl text-[#0F172A] mt-1">{{ $totalOps }}</p>
            <p class="text-[11px] text-[#64748B] mt-0.5">Akun Operator PUPR</p>
        </div>
        <div class="rounded-2xl p-4 bg-white border border-[#E2E8F0]">
            <p class="text-[11px] font-semibold text-[#64748B]">Verifikasi &amp; Disposisi</p>
            <p class="font-outfit font-bold text-2xl text-[#2563EB] mt-1">{{ $sumOpVerified }}</p>
            <p class="text-[11px] text-[#64748B] mt-0.5">{{ $sumOpRejected }} laporan ditolak</p>
        </div>
        <div class="rounded-2xl p-4 bg-white border border-[#E2E8F0]">
            <p class="text-[11px] font-semibold text-[#64748B]">Validasi Disetujui</p>
            <p class="font-outfit font-bold text-2xl text-emerald-700 mt-1">{{ $sumOpApproved }}</p>
            <p class="text-[11px] text-[#64748B] mt-0.5">{{ $sumOpRevision }} diminta revisi</p>
        </div>
        <div class="rounded-2xl p-4 bg-white border border-[#E2E8F0]">
            <p class="text-[11px] font-semibold text-[#64748B]">Periode Aktif</p>
            <p class="font-outfit font-bold text-base text-[#0F172A] mt-2 truncate">{{ $opSelectedPeriodLabel }}</p>
            <p class="text-[11px] text-[#64748B] mt-0.5">Data real-time</p>
        </div>
    </div>

    <!-- Tabel Daftar & Kartu Petak Operator -->
    <div class="card-white rounded-2xl p-4 sm:p-5 bg-white border border-[#E2E8F0] shadow-xs space-y-3">
        <h4 class="font-outfit font-bold text-sm text-[#0F172A]">Rincian Kinerja Operator ({{ $totalOps }} Akun)</h4>

        <!-- Tampilan Daftar -->
        <div class="exec-layout-list overflow-x-auto">
            <table class="w-full text-xs">
                <thead class="bg-[#F8FAFC] text-[#475569] border-y border-[#E2E8F0]">
                    <tr>
                        <th class="text-left py-2.5 px-3 font-bold">Nama Operator</th>
                        <th class="text-center py-2.5 px-3 font-bold">Verifikasi &amp; Disposisi</th>
                        <th class="text-center py-2.5 px-3 font-bold">Ditolak</th>
                        <th class="text-center py-2.5 px-3 font-bold">Validasi (Setuju / Revisi)</th>
                        <th class="text-center py-2.5 px-3 font-bold">Rata-rata Verifikasi</th>
                        <th class="text-right py-2.5 px-3 font-bold">Rata-rata Validasi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#E2E8F0]">
                    @forelse($operatorMetricsList as $op)
                    <tr class="exec-op-row hover:bg-slate-50/70">
                        <td class="py-2.5 px-3">
                            <div class="font-bold text-[#0F172A]">{{ $op['name'] }}</div>
                            <div class="text-[11px] text-[#64748B]">{{ $op['email'] }}</div>
                        </td>
                        <td class="py-2.5 px-3 text-center font-bold text-[#2563EB]">{{ $op['verified_dispatched'] }}</td>
                        <td class="py-2.5 px-3 text-center font-semibold text-red-600">{{ $op['rejected'] }}</td>
                        <td class="py-2.5 px-3 text-center">
                            <span class="font-bold text-emerald-700">{{ $op['validated_approved'] }}</span> /
                            <span class="font-semibold text-amber-700">{{ $op['validated_revision'] }}</span>
                        </td>
                        <td class="py-2.5 px-3 text-center text-[#475569]">{{ $op['avg_verify_hours'] !== null ? $op['avg_verify_hours'] . ' jam' : '-' }}</td>
                        <td class="py-2.5 px-3 text-right font-bold text-[#0F172A]">{{ $op['avg_validate_hours'] !== null ? $op['avg_validate_hours'] . ' jam' : '-' }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="py-6 text-center text-[#64748B]">Belum ada data aktivitas Operator pada periode ini.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Tampilan Petak (Zero Scroll Horizontal di Mobile) -->
        <div class="exec-layout-grid hidden grid-cols-1 md:grid-cols-2 gap-3">
            @forelse($operatorMetricsList as $op)
            <div class="exec-op-card p-4 rounded-2xl border border-[#E2E8F0] bg-[#F8FAFC]/60 space-y-2.5">
                <div>
                    <h5 class="font-outfit font-bold text-sm text-[#0F172A]">{{ $op['name'] }}</h5>
                    <p class="text-[11px] text-[#64748B]">{{ $op['email'] }}</p>
                </div>
                <div class="grid grid-cols-2 gap-2 text-xs">
                    <div class="p-2.5 rounded-xl bg-white border border-[#E2E8F0]">
                        <span class="block text-[10.5px] text-[#64748B]">Verifikasi &amp; Disposisi</span>
                        <strong class="text-[#2563EB]">{{ $op['verified_dispatched'] }}</strong>
                        <span class="text-[10.5px] text-red-600 font-semibold ml-1">({{ $op['rejected'] }} ditolak)</span>
                    </div>
                    <div class="p-2.5 rounded-xl bg-white border border-[#E2E8F0]">
                        <span class="block text-[10.5px] text-[#64748B]">Validasi (Setuju / Revisi)</span>
                        <strong class="text-emerald-700">{{ $op['validated_approved'] }}</strong> /
                        <strong class="text-amber-700">{{ $op['validated_revision'] }}</strong>
                    </div>
                    <div class="p-2.5 rounded-xl bg-white border border-[#E2E8F0]">
                        <span class="block text-[10.5px] text-[#64748B]">Rata-rata Verifikasi</span>
                        <strong class="text-[#0F172A]">{{ $op['avg_verify_hours'] !== null ? $op['avg_verify_hours'] . ' jam' : '-' }}</strong>
                    </div>
                    <div class="p-2.5 rounded-xl bg-white border border-[#E2E8F0]">
                        <span class="block text-[10.5px] text-[#64748B]">Rata-rata Validasi</span>
                        <strong class="text-[#0F172A]">{{ $op['avg_validate_hours'] !== null ? $op['avg_validate_hours'] . ' jam' : '-' }}</strong>
                    </div>
                </div>
            </div>
            @empty
            <p class="py-6 text-center text-xs text-[#64748B] col-span-full">Belum ada data aktivitas Operator pada periode ini.</p>
            @endforelse
        </div>

        @include('partials.perf_pagination_bar', ['pagerId' => 'execOperatorPager', 'unitLabel' => 'operator'])
    </div>
</div>
