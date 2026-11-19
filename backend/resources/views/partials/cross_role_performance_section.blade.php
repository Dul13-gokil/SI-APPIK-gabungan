@php
    $perfService = app(\App\Services\PerformanceMetricsService::class);
    $availablePeriods = \App\Services\PerformanceMetricsService::getAvailablePeriods();
    $selectedPeriod = request('perf_period', 'ALL');
    [$perfFrom, $perfTo, $selectedPeriodLabel] = \App\Services\PerformanceMetricsService::resolvePeriodBounds($selectedPeriod);

    $fieldTeamMetrics = $perfService->getFieldTeamMetrics($perfFrom, $perfTo);
    $fieldOfficerMetrics = $perfService->getFieldOfficerMetrics($perfFrom, $perfTo);
    $canViewOperatorPerf = auth()->check() && (auth()->user()->isSuperAdmin() || auth()->user()->isBupati());
    $operatorMetrics = $canViewOperatorPerf ? $perfService->getOperatorMetrics($perfFrom, $perfTo) : [];

    // Siapkan rincian pekerjaan per regu & per individu agar drill-down instan tanpa reload halaman
    $teamDetailsMap = [];
    foreach ($fieldTeamMetrics as $tm) {
        $tid = $tm['team_id'];
        $members = array_values(array_filter($fieldOfficerMetrics, fn ($of) => ($of['team_id'] ?? null) == $tid));
        $leaderId = 0;
        foreach ($members as $mb) {
            if (($mb['team_role'] ?? '') === 'Ketua Regu') {
                $leaderId = $mb['user_id'];
                break;
            }
        }
        $tasks = $perfService->getPetugasPrintableTasks($tid, $leaderId, 'team', $perfFrom, $perfTo)->map(fn ($t) => [
            'id'           => $t->id,
            'public_code'  => $t->public_code,
            'title'        => $t->title,
            'address'      => $t->address,
            'category'     => $t->category?->name ?? 'Umum',
            'status'       => $t->status,
            'status_label' => \App\Services\PerformanceMetricsService::statusLabelId($t->status),
            'rating'       => $t->rating ? $t->rating->rating . '/5' : '-',
            'bap_url'      => in_array($t->status, ['COMPLETED', 'CLOSED'], true) ? route('complaints.bap', $t->id) : null,
        ])->values()->all();

        $teamDetailsMap[(string) $tid] = [
            'metric'  => $tm,
            'members' => $members,
            'tasks'   => $tasks,
        ];
    }

    $officerDetailsMap = [];
    foreach ($fieldOfficerMetrics as $of) {
        $uid = $of['user_id'];
        $tid = $of['team_id'] ?? null;
        $tasks = $perfService->getPetugasPrintableTasks($tid, $uid, 'individual', $perfFrom, $perfTo)->map(fn ($t) => [
            'id'           => $t->id,
            'public_code'  => $t->public_code,
            'title'        => $t->title,
            'address'      => $t->address,
            'category'     => $t->category?->name ?? 'Umum',
            'status'       => $t->status,
            'status_label' => \App\Services\PerformanceMetricsService::statusLabelId($t->status),
            'rating'       => $t->rating ? $t->rating->rating . '/5' : '-',
            'bap_url'      => in_array($t->status, ['COMPLETED', 'CLOSED'], true) ? route('complaints.bap', $t->id) : null,
        ])->values()->all();

        $officerDetailsMap[(string) $uid] = [
            'metric' => $of,
            'tasks'  => $tasks,
        ];
    }

    $totalTm = count($fieldTeamMetrics);
    $sumTmTasks = array_sum(array_column($fieldTeamMetrics, 'total_tasks'));
    $sumTmCompleted = array_sum(array_column($fieldTeamMetrics, 'completed'));
    $avgTmScore = $totalTm > 0 ? round(array_sum(array_column($fieldTeamMetrics, 'score')) / $totalTm, 1) : 0;

    $totalOff = count($fieldOfficerMetrics);
    $sumOffTasks = array_sum(array_column($fieldOfficerMetrics, 'total_tasks'));
    $sumOffCompleted = array_sum(array_column($fieldOfficerMetrics, 'completed'));
    $avgOffScore = $totalOff > 0 ? round(array_sum(array_column($fieldOfficerMetrics, 'score')) / $totalOff, 1) : 0;
@endphp

<!-- Pemantauan Kinerja Lintas Role (Pekerja Lapangan per Regu/Individu & Operator PUPR) -->
<div class="space-y-5 mt-5">
    <!-- 1. Header Responsif Kinerja Pekerja Lapangan (Rapi di Mobile & 1 Baris di Desktop) -->
    <div class="card-white rounded-2xl p-4 sm:p-5 bg-white border border-[#E2E8F0] shadow-xs space-y-3 xl:space-y-0">
        <div class="flex flex-col xl:flex-row xl:items-center justify-between gap-3">
            <div class="flex items-center justify-between gap-2.5 shrink-0">
                <div class="flex items-center gap-2.5 min-w-0">
                    <div class="w-9 h-9 rounded-xl bg-[#EFF6FF] text-[#2563EB] border border-[#BFDBFE] flex items-center justify-center shrink-0">
                        <i data-lucide="hard-hat" class="w-4 h-4"></i>
                    </div>
                    <div class="min-w-0">
                        <h3 class="font-outfit font-bold text-sm sm:text-base text-[#0F172A] truncate">Kinerja Pekerja Lapangan</h3>
                        <p class="text-[11px] font-semibold text-[#64748B]">Periode: {{ $selectedPeriodLabel }}</p>
                    </div>
                </div>
            </div>

            <!-- Baris Kontrol Filter, Tab & Aksi (Tertata Rapi di Mobile maupun Desktop) -->
            <div class="flex flex-col sm:flex-row sm:flex-wrap xl:flex-nowrap items-stretch sm:items-center gap-2 w-full xl:w-auto">
                <!-- Dropdown Periode Pill (Desain Kalender Biru) + Filter Regu/Individu -->
                <div class="grid grid-cols-2 sm:flex items-center gap-2">
                    <div class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full bg-[#F8FAFC] border border-[#E2E8F0] w-full sm:w-auto shrink-0">
                        <i data-lucide="calendar" class="w-3.5 h-3.5 text-[#2563EB] shrink-0"></i>
                        <select id="execFieldPerfPeriod" onchange="changeExecPerfPeriod(this.value, 'field')"
                            class="bg-transparent text-xs font-bold text-[#0F172A] focus:outline-none cursor-pointer pr-1 w-full sm:w-auto"
                            aria-label="Pilih Periode Kinerja Pekerja Lapangan">
                            @foreach($availablePeriods as $per)
                                <option value="{{ $per['value'] }}" {{ $selectedPeriod === $per['value'] ? 'selected' : '' }}>
                                    {{ $per['label'] }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Filter Regu (aktif di tab Per Regu) -->
                    <select id="execFieldPerfFilter-team" onchange="applyExecFieldPerfFilter()"
                        class="w-full sm:w-auto text-xs font-semibold rounded-xl border border-[#E2E8F0] py-2 px-2.5 bg-[#F8FAFC] text-[#334155] focus:outline-none focus:border-[#2563EB] shrink-0">
                        <option value="ALL">Semua Regu</option>
                        @foreach($fieldTeamMetrics as $tm)
                            <option value="{{ $tm['team_id'] }}">{{ $tm['team_name'] }}</option>
                        @endforeach
                    </select>

                    <!-- Filter Individu (aktif di tab Per Individu) -->
                    <select id="execFieldPerfFilter-officer" onchange="applyExecFieldPerfFilter()"
                        class="hidden w-full sm:w-auto text-xs font-semibold rounded-xl border border-[#E2E8F0] py-2 px-2.5 bg-[#F8FAFC] text-[#334155] focus:outline-none focus:border-[#2563EB] shrink-0">
                        <option value="ALL">Semua Individu</option>
                        @foreach($fieldOfficerMetrics as $of)
                            <option value="{{ $of['user_id'] }}">{{ $of['name'] }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Toggle Per Regu / Per Individu (Penuh 2 kolom di mobile) -->
                <div class="grid grid-cols-2 sm:inline-flex items-center p-0.5 rounded-xl bg-[#F8FAFC] border border-[#E2E8F0]" role="group">
                    <button type="button" id="execFieldPerfBtn-team" onclick="switchExecFieldPerf('team')"
                        class="px-2.5 py-1.5 rounded-lg text-xs font-bold transition cursor-pointer bg-[#2563EB] text-white whitespace-nowrap text-center">
                        Per Regu ({{ $totalTm }})
                    </button>
                    <button type="button" id="execFieldPerfBtn-officer" onclick="switchExecFieldPerf('officer')"
                        class="px-2.5 py-1.5 rounded-lg text-xs font-semibold transition cursor-pointer text-[#475569] hover:text-[#0F172A] whitespace-nowrap text-center">
                        Per Individu ({{ $totalOff }})
                    </button>
                </div>

                <!-- Tombol Cetak Laporan + Unduh Excel/CSV (2 kolom seimbang di mobile) -->
                <div class="grid grid-cols-2 sm:flex items-center gap-2">
                    <a id="execFieldPerfPrintBtn"
                        href="{{ route('petugas.performance.print', ['mode' => 'team', 'scope' => 'all', 'period' => $selectedPeriod]) }}"
                        target="_blank"
                        class="px-3 py-2 rounded-xl bg-[#0F172A] hover:bg-[#1E293B] text-white text-xs font-bold transition inline-flex items-center justify-center gap-1.5 whitespace-nowrap">
                        <i data-lucide="printer" class="w-3.5 h-3.5"></i>
                        <span>Cetak Laporan</span>
                    </a>

                    <a id="execFieldPerfCsvBtn"
                        href="{{ route('reports.export-csv', ['mode' => 'team', 'period' => $selectedPeriod]) }}"
                        class="px-3 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold transition inline-flex items-center justify-center gap-1.5 whitespace-nowrap">
                        <i data-lucide="file-spreadsheet" class="w-3.5 h-3.5"></i>
                        <span>Unduh Excel/CSV</span>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- PANEL 1: KINERJA PER REGU -->
    <div id="execFieldPerfWrap-team" class="space-y-4">
        <!-- 1A. Ringkasan Semua Regu -->
        <div id="execTeamAllView" class="space-y-4">
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                <div class="rounded-2xl p-4 bg-white border border-[#E2E8F0]">
                    <p class="text-[11px] font-semibold text-[#64748B]">Total Regu</p>
                    <p class="font-outfit font-bold text-2xl text-[#0F172A] mt-1">{{ $totalTm }}</p>
                    <p class="text-[11px] text-[#64748B] mt-0.5">Regu lapangan aktif</p>
                </div>
                <div class="rounded-2xl p-4 bg-white border border-[#E2E8F0]">
                    <p class="text-[11px] font-semibold text-[#64748B]">Total Tugas Regu</p>
                    <p class="font-outfit font-bold text-2xl text-[#0F172A] mt-1">{{ $sumTmTasks }}</p>
                    <p class="text-[11px] text-[#64748B] mt-0.5">{{ $sumTmCompleted }} tuntas</p>
                </div>
                <div class="rounded-2xl p-4 bg-white border border-[#E2E8F0]">
                    <p class="text-[11px] font-semibold text-[#64748B]">Rata-rata Skor Regu</p>
                    <p class="font-outfit font-bold text-2xl text-[#2563EB] mt-1">{{ $avgTmScore }}<span class="text-xs font-normal text-[#64748B]">/100</span></p>
                    <p class="text-[11px] text-[#64748B] mt-0.5">Seluruh regu</p>
                </div>
                <div class="rounded-2xl p-4 bg-white border border-[#E2E8F0]">
                    <p class="text-[11px] font-semibold text-[#64748B]">Cakupan Cetak</p>
                    <p class="font-outfit font-bold text-base text-emerald-700 mt-2">Semua Regu</p>
                    <p class="text-[11px] text-[#64748B] mt-0.5">Klik kartu/baris untuk rincian</p>
                </div>
            </div>

            <div class="card-white rounded-2xl p-4 sm:p-5 bg-white border border-[#E2E8F0] shadow-xs space-y-3">
                <div class="flex flex-wrap items-center justify-between gap-2">
                    <h4 class="font-outfit font-bold text-sm text-[#0F172A]">Rekap Kinerja Seluruh Regu ({{ $totalTm }} Regu)</h4>
                    <div class="inline-flex items-center p-0.5 rounded-xl bg-[#F8FAFC] border border-[#E2E8F0]" role="group">
                        <button type="button" onclick="setExecPerfLayout('list')"
                            class="exec-layout-btn-list px-2.5 py-1 rounded-lg text-[11px] font-bold transition cursor-pointer inline-flex items-center gap-1 bg-[#2563EB] text-white">
                            <i data-lucide="list" class="w-3 h-3"></i><span>Daftar</span>
                        </button>
                        <button type="button" onclick="setExecPerfLayout('grid')"
                            class="exec-layout-btn-grid px-2.5 py-1 rounded-lg text-[11px] font-semibold transition cursor-pointer inline-flex items-center gap-1 text-[#475569]">
                            <i data-lucide="layout-grid" class="w-3 h-3"></i><span>Petak</span>
                        </button>
                    </div>
                </div>

                <!-- Tampilan Daftar (Tabel) -->
                <div class="exec-layout-list overflow-x-auto">
                    <table class="w-full text-xs">
                        <thead class="bg-[#F8FAFC] text-[#475569] border-y border-[#E2E8F0]">
                            <tr>
                                <th class="text-left py-2.5 px-3 font-bold">Regu Lapangan</th>
                                <th class="text-center py-2.5 px-3 font-bold">Total / Tuntas</th>
                                <th class="text-center py-2.5 px-3 font-bold">Kuota Aktif</th>
                                <th class="text-center py-2.5 px-3 font-bold">Respon / Perbaikan</th>
                                <th class="text-center py-2.5 px-3 font-bold">Lolos 1x</th>
                                <th class="text-center py-2.5 px-3 font-bold">Rating Warga</th>
                                <th class="text-right py-2.5 px-3 font-bold">Skor</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[#E2E8F0]">
                            @forelse($fieldTeamMetrics as $tm)
                            <tr onclick="selectExecTeam('{{ $tm['team_id'] }}')"
                                class="hover:bg-blue-50/40 cursor-pointer transition exec-team-row"
                                data-team-id="{{ $tm['team_id'] }}">
                                <td class="py-2.5 px-3">
                                    <div class="font-bold text-[#0F172A] hover:text-[#2563EB]">{{ $tm['team_name'] }}</div>
                                    <div class="text-[11px] text-[#64748B]">{{ $tm['team_code'] }} &bull; Ketua: {{ $tm['leader_name'] }} ({{ $tm['members_count'] }} org)</div>
                                </td>
                                <td class="py-2.5 px-3 text-center">
                                    <span class="font-bold text-[#0F172A]">{{ $tm['completed'] }}/{{ $tm['total_tasks'] }}</span>
                                    <span class="block text-[10px] text-emerald-700 font-semibold">{{ $tm['completion_rate'] }}%</span>
                                </td>
                                <td class="py-2.5 px-3 text-center font-semibold text-[#2563EB]">{{ $tm['active_quota'] }}/3</td>
                                <td class="py-2.5 px-3 text-center text-[#475569]">
                                    {{ $tm['avg_response_hours'] !== null ? $tm['avg_response_hours'] . 'j' : '-' }} /
                                    {{ $tm['avg_repair_hours'] !== null ? $tm['avg_repair_hours'] . 'j' : '-' }}
                                </td>
                                <td class="py-2.5 px-3 text-center font-semibold text-[#0F172A]">{{ $tm['first_pass_rate'] }}%</td>
                                <td class="py-2.5 px-3 text-center font-bold text-amber-600">{{ $tm['avg_rating'] !== null ? $tm['avg_rating'] . '/5' : '-' }}</td>
                                <td class="py-2.5 px-3 text-right">
                                    <span class="px-2.5 py-1 rounded-lg font-bold {{ $tm['score'] >= 80 ? 'bg-emerald-50 text-emerald-700' : ($tm['score'] >= 50 ? 'bg-amber-50 text-amber-700' : 'bg-slate-100 text-slate-600') }}">
                                        {{ $tm['score'] }}
                                    </span>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="7" class="py-6 text-center text-[#64748B]">Belum ada data regu lapangan pada periode ini.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Tampilan Petak (Kartu Lengkap Tanpa Scroll Horizontal) -->
                <div class="exec-layout-grid hidden grid-cols-1 md:grid-cols-2 gap-3">
                    @forelse($fieldTeamMetrics as $tm)
                    <div onclick="selectExecTeam('{{ $tm['team_id'] }}')"
                        class="exec-team-card p-4 rounded-2xl border border-[#E2E8F0] bg-[#F8FAFC]/60 hover:border-[#2563EB] hover:bg-blue-50/30 cursor-pointer transition space-y-3">
                        <div class="flex items-start justify-between gap-2">
                            <div class="min-w-0">
                                <h5 class="font-outfit font-bold text-sm text-[#0F172A] truncate">{{ $tm['team_name'] }}</h5>
                                <p class="text-[11px] text-[#64748B]">{{ $tm['team_code'] }} &bull; Ketua: {{ $tm['leader_name'] }} ({{ $tm['members_count'] }} org)</p>
                            </div>
                            <span class="px-2.5 py-1 rounded-lg text-xs font-bold shrink-0 {{ $tm['score'] >= 80 ? 'bg-emerald-50 text-emerald-700' : ($tm['score'] >= 50 ? 'bg-amber-50 text-amber-700' : 'bg-slate-100 text-slate-600') }}">
                                Skor {{ $tm['score'] }}
                            </span>
                        </div>
                        <div class="grid grid-cols-2 gap-2 text-xs">
                            <div class="p-2.5 rounded-xl bg-white border border-[#E2E8F0]">
                                <span class="block text-[10.5px] text-[#64748B]">Tuntas / Total</span>
                                <strong class="text-[#0F172A]">{{ $tm['completed'] }}/{{ $tm['total_tasks'] }}</strong>
                                <span class="text-[10.5px] text-emerald-700 font-semibold">({{ $tm['completion_rate'] }}%)</span>
                            </div>
                            <div class="p-2.5 rounded-xl bg-white border border-[#E2E8F0]">
                                <span class="block text-[10.5px] text-[#64748B]">Kuota Aktif</span>
                                <strong class="text-[#2563EB]">{{ $tm['active_quota'] }}/3 Tugas</strong>
                            </div>
                            <div class="p-2.5 rounded-xl bg-white border border-[#E2E8F0]">
                                <span class="block text-[10.5px] text-[#64748B]">Respon / Perbaikan</span>
                                <strong class="text-[#0F172A]">{{ $tm['avg_response_hours'] !== null ? $tm['avg_response_hours'] . 'j' : '-' }} / {{ $tm['avg_repair_hours'] !== null ? $tm['avg_repair_hours'] . 'j' : '-' }}</strong>
                            </div>
                            <div class="p-2.5 rounded-xl bg-white border border-[#E2E8F0]">
                                <span class="block text-[10.5px] text-[#64748B]">Lolos 1x &bull; Rating</span>
                                <strong class="text-[#0F172A]">{{ $tm['first_pass_rate'] }}%</strong> &bull;
                                <strong class="text-amber-600">{{ $tm['avg_rating'] !== null ? $tm['avg_rating'] . '/5' : '-' }}</strong>
                            </div>
                        </div>
                    </div>
                    @empty
                    <p class="py-6 text-center text-xs text-[#64748B] col-span-full">Belum ada data regu lapangan pada periode ini.</p>
                    @endforelse
                </div>

                @include('partials.perf_pagination_bar', ['pagerId' => 'execTeamPager', 'unitLabel' => 'regu'])
            </div>
        </div>

        <!-- 1B. Rincian Lengkap 1 Regu Terpilih -->
        <div id="execTeamDetailView" class="hidden space-y-4"></div>
    </div>

    <!-- PANEL 2: KINERJA PER INDIVIDU -->
    <div id="execFieldPerfWrap-officer" class="hidden space-y-4">
        <!-- 2A. Ringkasan Semua Individu -->
        <div id="execOfficerAllView" class="space-y-4">
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                <div class="rounded-2xl p-4 bg-white border border-[#E2E8F0]">
                    <p class="text-[11px] font-semibold text-[#64748B]">Total Petugas</p>
                    <p class="font-outfit font-bold text-2xl text-[#0F172A] mt-1">{{ $totalOff }}</p>
                    <p class="text-[11px] text-[#64748B] mt-0.5">Personel lapangan aktif</p>
                </div>
                <div class="rounded-2xl p-4 bg-white border border-[#E2E8F0]">
                    <p class="text-[11px] font-semibold text-[#64748B]">Total Tugas Ditangani</p>
                    <p class="font-outfit font-bold text-2xl text-[#0F172A] mt-1">{{ $sumOffTasks }}</p>
                    <p class="text-[11px] text-[#64748B] mt-0.5">{{ $sumOffCompleted }} tuntas</p>
                </div>
                <div class="rounded-2xl p-4 bg-white border border-[#E2E8F0]">
                    <p class="text-[11px] font-semibold text-[#64748B]">Rata-rata Skor Kinerja</p>
                    <p class="font-outfit font-bold text-2xl text-[#2563EB] mt-1">{{ $avgOffScore }}<span class="text-xs font-normal text-[#64748B]">/100</span></p>
                    <p class="text-[11px] text-[#64748B] mt-0.5">Seluruh petugas</p>
                </div>
                <div class="rounded-2xl p-4 bg-white border border-[#E2E8F0]">
                    <p class="text-[11px] font-semibold text-[#64748B]">Cakupan Cetak</p>
                    <p class="font-outfit font-bold text-base text-emerald-700 mt-2">Semua Individu</p>
                    <p class="text-[11px] text-[#64748B] mt-0.5">Klik kartu/baris untuk rincian</p>
                </div>
            </div>

            <div class="card-white rounded-2xl p-4 sm:p-5 bg-white border border-[#E2E8F0] shadow-xs space-y-3">
                <div class="flex flex-wrap items-center justify-between gap-2">
                    <h4 class="font-outfit font-bold text-sm text-[#0F172A]">Rekap Kinerja Seluruh Petugas ({{ $totalOff }} Orang)</h4>
                    <div class="inline-flex items-center p-0.5 rounded-xl bg-[#F8FAFC] border border-[#E2E8F0]" role="group">
                        <button type="button" onclick="setExecPerfLayout('list')"
                            class="exec-layout-btn-list px-2.5 py-1 rounded-lg text-[11px] font-bold transition cursor-pointer inline-flex items-center gap-1 bg-[#2563EB] text-white">
                            <i data-lucide="list" class="w-3 h-3"></i><span>Daftar</span>
                        </button>
                        <button type="button" onclick="setExecPerfLayout('grid')"
                            class="exec-layout-btn-grid px-2.5 py-1 rounded-lg text-[11px] font-semibold transition cursor-pointer inline-flex items-center gap-1 text-[#475569]">
                            <i data-lucide="layout-grid" class="w-3 h-3"></i><span>Petak</span>
                        </button>
                    </div>
                </div>

                <!-- Tampilan Daftar -->
                <div class="exec-layout-list overflow-x-auto">
                    <table class="w-full text-xs">
                        <thead class="bg-[#F8FAFC] text-[#475569] border-y border-[#E2E8F0]">
                            <tr>
                                <th class="text-left py-2.5 px-3 font-bold">Nama Petugas</th>
                                <th class="text-left py-2.5 px-3 font-bold">Regu &amp; Peran</th>
                                <th class="text-center py-2.5 px-3 font-bold">Tuntas / Total</th>
                                <th class="text-center py-2.5 px-3 font-bold">Rata-rata Perbaikan</th>
                                <th class="text-center py-2.5 px-3 font-bold">Revisi</th>
                                <th class="text-center py-2.5 px-3 font-bold">Rating</th>
                                <th class="text-right py-2.5 px-3 font-bold">Skor</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[#E2E8F0]">
                            @forelse($fieldOfficerMetrics as $of)
                            <tr onclick="selectExecOfficer('{{ $of['user_id'] }}')"
                                class="hover:bg-blue-50/40 cursor-pointer transition exec-officer-row"
                                data-officer-id="{{ $of['user_id'] }}">
                                <td class="py-2.5 px-3">
                                    <div class="font-bold text-[#0F172A] hover:text-[#2563EB]">{{ $of['name'] }}</div>
                                    <div class="text-[11px] text-[#64748B]">{{ $of['email'] }}</div>
                                </td>
                                <td class="py-2.5 px-3">
                                    <div class="font-semibold text-[#0F172A]">{{ $of['team_name'] }}</div>
                                    <div class="text-[11px] text-[#64748B]">{{ $of['team_role'] }}</div>
                                </td>
                                <td class="py-2.5 px-3 text-center font-bold text-[#0F172A]">{{ $of['completed'] }}/{{ $of['total_tasks'] }}</td>
                                <td class="py-2.5 px-3 text-center text-[#475569]">{{ $of['avg_repair_hours'] !== null ? $of['avg_repair_hours'] . ' jam' : '-' }}</td>
                                <td class="py-2.5 px-3 text-center font-semibold text-amber-700">{{ $of['revision_count'] }}</td>
                                <td class="py-2.5 px-3 text-center font-bold text-amber-600">{{ $of['avg_rating'] !== null ? $of['avg_rating'] . '/5' : '-' }}</td>
                                <td class="py-2.5 px-3 text-right">
                                    <span class="px-2.5 py-1 rounded-lg font-bold {{ $of['score'] >= 80 ? 'bg-emerald-50 text-emerald-700' : ($of['score'] >= 50 ? 'bg-amber-50 text-amber-700' : 'bg-slate-100 text-slate-600') }}">
                                        {{ $of['score'] }}
                                    </span>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="7" class="py-6 text-center text-[#64748B]">Belum ada data pekerja lapangan.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Tampilan Petak (Kartu Lengkap Tanpa Scroll Horizontal) -->
                <div class="exec-layout-grid hidden grid-cols-1 md:grid-cols-2 gap-3">
                    @forelse($fieldOfficerMetrics as $of)
                    <div onclick="selectExecOfficer('{{ $of['user_id'] }}')"
                        class="exec-officer-card p-4 rounded-2xl border border-[#E2E8F0] bg-[#F8FAFC]/60 hover:border-[#2563EB] hover:bg-blue-50/30 cursor-pointer transition space-y-2.5">
                        <div class="flex items-start justify-between gap-2">
                            <div class="min-w-0">
                                <h5 class="font-outfit font-bold text-sm text-[#0F172A] truncate">{{ $of['name'] }}</h5>
                                <p class="text-[11px] text-[#64748B] truncate">{{ $of['team_name'] }} &bull; {{ $of['team_role'] }}</p>
                            </div>
                            <span class="px-2.5 py-1 rounded-lg text-xs font-bold shrink-0 {{ $of['score'] >= 80 ? 'bg-emerald-50 text-emerald-700' : ($of['score'] >= 50 ? 'bg-amber-50 text-amber-700' : 'bg-slate-100 text-slate-600') }}">
                                Skor {{ $of['score'] }}
                            </span>
                        </div>
                        <div class="grid grid-cols-3 gap-2 text-xs text-center">
                            <div class="p-2 rounded-xl bg-white border border-[#E2E8F0]">
                                <span class="block text-[10px] text-[#64748B]">Tuntas</span>
                                <strong class="text-[#0F172A]">{{ $of['completed'] }}/{{ $of['total_tasks'] }}</strong>
                            </div>
                            <div class="p-2 rounded-xl bg-white border border-[#E2E8F0]">
                                <span class="block text-[10px] text-[#64748B]">Perbaikan</span>
                                <strong class="text-[#0F172A]">{{ $of['avg_repair_hours'] !== null ? $of['avg_repair_hours'] . 'j' : '-' }}</strong>
                            </div>
                            <div class="p-2 rounded-xl bg-white border border-[#E2E8F0]">
                                <span class="block text-[10px] text-[#64748B]">Revisi / Rating</span>
                                <strong class="text-amber-700">{{ $of['revision_count'] }}</strong> /
                                <strong class="text-amber-600">{{ $of['avg_rating'] !== null ? $of['avg_rating'] : '-' }}</strong>
                            </div>
                        </div>
                    </div>
                    @empty
                    <p class="py-6 text-center text-xs text-[#64748B] col-span-full">Belum ada data pekerja lapangan.</p>
                    @endforelse
                </div>

                @include('partials.perf_pagination_bar', ['pagerId' => 'execOfficerPager', 'unitLabel' => 'petugas'])
            </div>
        </div>

        <!-- 2B. Rincian Lengkap 1 Individu Terpilih -->
        <div id="execOfficerDetailView" class="hidden space-y-4"></div>
    </div>
</div>

<script>
    window._execFieldPerfMode = 'team';
    window._execPerfPeriod = @json($selectedPeriod);
    window._execPerfLayout = window.innerWidth < 768 ? 'grid' : 'list';
    window._execPerfTeamDetails = @json($teamDetailsMap);
    window._execPerfOfficerDetails = @json($officerDetailsMap);

    function changeExecPerfPeriod(periodVal, targetSubtab) {
        const url = new URL(window.location.href);
        url.searchParams.set('perf_period', periodVal);
        url.searchParams.set('exec_subtab', targetSubtab || window._activeExecSubtab || 'field');
        window.location.href = url.toString();
    }

    function setExecPerfLayout(layout) {
        window._execPerfLayout = layout === 'grid' ? 'grid' : 'list';
        const isGrid = window._execPerfLayout === 'grid';

        document.querySelectorAll('.exec-layout-list').forEach(el => {
            el.classList.toggle('hidden', isGrid);
        });
        document.querySelectorAll('.exec-layout-grid').forEach(el => {
            el.classList.toggle('hidden', !isGrid);
            el.classList.toggle('grid', isGrid);
        });

        document.querySelectorAll('.exec-layout-btn-list').forEach(btn => {
            btn.className = !isGrid
                ? 'exec-layout-btn-list px-2.5 py-1 rounded-lg text-[11px] font-bold transition cursor-pointer inline-flex items-center gap-1 bg-[#2563EB] text-white'
                : 'exec-layout-btn-list px-2.5 py-1 rounded-lg text-[11px] font-semibold transition cursor-pointer inline-flex items-center gap-1 text-[#475569]';
        });
        document.querySelectorAll('.exec-layout-btn-grid').forEach(btn => {
            btn.className = isGrid
                ? 'exec-layout-btn-grid px-2.5 py-1 rounded-lg text-[11px] font-bold transition cursor-pointer inline-flex items-center gap-1 bg-[#2563EB] text-white'
                : 'exec-layout-btn-grid px-2.5 py-1 rounded-lg text-[11px] font-semibold transition cursor-pointer inline-flex items-center gap-1 text-[#475569]';
        });
    }

    function _escPerfHtml(str) {
        return String(str ?? '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    }

    function _renderPerfTasksBlock(tasks, emptyText) {
        if (!tasks || tasks.length === 0) {
            return `<p class="text-xs text-[#64748B] py-6 text-center">${_escPerfHtml(emptyText)}</p>`;
        }
        const isGrid = window._execPerfLayout === 'grid';
        const rows = tasks.map(t => {
            const isDone = t.status === 'COMPLETED' || t.status === 'CLOSED';
            const badgeCls = isDone ? 'bg-emerald-50 text-emerald-700' : 'bg-amber-50 text-amber-700';
            const bapHtml = t.bap_url
                ? `<a href="${t.bap_url}" target="_blank" class="px-2.5 py-1 rounded-lg bg-[#EFF6FF] text-[#2563EB] font-bold text-[11px] hover:bg-[#DBEAFE]">Cetak BAP</a>`
                : `<span class="text-[11px] text-[#94A3B8]">-</span>`;
            return `
                <tr class="hover:bg-slate-50/70">
                    <td class="py-2.5 px-3 font-mono font-bold text-[#0F172A] whitespace-nowrap">#${_escPerfHtml(t.public_code)}</td>
                    <td class="py-2.5 px-3">
                        <div class="font-bold text-[#0F172A]">${_escPerfHtml(t.title)}</div>
                        <div class="text-[11px] text-[#64748B] truncate max-w-xs">${_escPerfHtml(t.address)}</div>
                    </td>
                    <td class="py-2.5 px-3 text-[#475569]">${_escPerfHtml(t.category)}</td>
                    <td class="py-2.5 px-3 text-center">
                        <span class="px-2 py-0.5 rounded-md text-[10px] font-bold ${badgeCls}">${_escPerfHtml(t.status_label)}</span>
                    </td>
                    <td class="py-2.5 px-3 text-center font-bold text-amber-600">${_escPerfHtml(t.rating)}</td>
                    <td class="py-2.5 px-3 text-right">${bapHtml}</td>
                </tr>`;
        }).join('');

        const cards = tasks.map(t => {
            const isDone = t.status === 'COMPLETED' || t.status === 'CLOSED';
            const badgeCls = isDone ? 'bg-emerald-50 text-emerald-700' : 'bg-amber-50 text-amber-700';
            const bapHtml = t.bap_url
                ? `<a href="${t.bap_url}" target="_blank" class="px-2.5 py-1 rounded-lg bg-[#EFF6FF] text-[#2563EB] font-bold text-[11px] hover:bg-[#DBEAFE]">Cetak BAP</a>`
                : '';
            return `
                <div class="p-3.5 rounded-xl border border-[#E2E8F0] bg-[#F8FAFC]/60 space-y-2">
                    <div class="flex items-center justify-between gap-2">
                        <span class="font-mono font-bold text-xs text-[#0F172A]">#${_escPerfHtml(t.public_code)}</span>
                        <span class="px-2 py-0.5 rounded-md text-[10px] font-bold ${badgeCls}">${_escPerfHtml(t.status_label)}</span>
                    </div>
                    <div>
                        <div class="font-bold text-xs text-[#0F172A]">${_escPerfHtml(t.title)}</div>
                        <div class="text-[11px] text-[#64748B]">${_escPerfHtml(t.address)}</div>
                    </div>
                    <div class="flex items-center justify-between gap-2 pt-1 text-[11px]">
                        <span class="text-[#475569]">${_escPerfHtml(t.category)} &bull; Rating: <strong class="text-amber-600">${_escPerfHtml(t.rating)}</strong></span>
                        ${bapHtml}
                    </div>
                </div>`;
        }).join('');

        return `
            <div class="exec-layout-list ${isGrid ? 'hidden' : ''} overflow-x-auto">
                <table class="w-full text-xs">
                    <thead class="bg-[#F8FAFC] text-[#475569] border-y border-[#E2E8F0]">
                        <tr>
                            <th class="text-left py-2.5 px-3 font-bold">Kode</th>
                            <th class="text-left py-2.5 px-3 font-bold">Judul &amp; Lokasi</th>
                            <th class="text-left py-2.5 px-3 font-bold">Kategori</th>
                            <th class="text-center py-2.5 px-3 font-bold">Status</th>
                            <th class="text-center py-2.5 px-3 font-bold">Rating</th>
                            <th class="text-right py-2.5 px-3 font-bold">BAP</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#E2E8F0]">${rows}</tbody>
                </table>
            </div>
            <div class="exec-layout-grid ${isGrid ? 'grid' : 'hidden'} grid-cols-1 md:grid-cols-2 gap-2.5">${cards}</div>`;
    }

    function renderExecTeamDetail(teamId) {
        const wrap = document.getElementById('execTeamDetailView');
        const data = window._execPerfTeamDetails?.[String(teamId)];
        if (!wrap || !data) return;

        const m = data.metric;
        const isGrid = window._execPerfLayout === 'grid';
        const respStr = m.avg_response_hours !== null ? `${m.avg_response_hours}j` : '-';
        const repStr = m.avg_repair_hours !== null ? `${m.avg_repair_hours}j` : '-';
        const ratingStr = m.avg_rating !== null ? `${m.avg_rating} / 5` : '-';

        const memberRows = (data.members || []).map(mb => `
            <tr onclick="switchExecFieldPerf('officer'); selectExecOfficer('${mb.user_id}')" class="hover:bg-blue-50/40 cursor-pointer transition">
                <td class="py-2.5 px-3 font-bold text-[#0F172A] hover:text-[#2563EB]">${_escPerfHtml(mb.name)}</td>
                <td class="py-2.5 px-3 text-[#475569]">${_escPerfHtml(mb.team_role)}</td>
                <td class="py-2.5 px-3 text-center font-semibold">${mb.total_tasks}</td>
                <td class="py-2.5 px-3 text-center font-bold text-emerald-700">${mb.completed}</td>
                <td class="py-2.5 px-3 text-center text-amber-700">${mb.revision_count}</td>
                <td class="py-2.5 px-3 text-right font-bold text-[#2563EB]">${mb.score}</td>
            </tr>`).join('') || `<tr><td colspan="6" class="py-5 text-center text-[#64748B]">Belum ada anggota regu terdaftar.</td></tr>`;

        const memberCards = (data.members || []).map(mb => `
            <div onclick="switchExecFieldPerf('officer'); selectExecOfficer('${mb.user_id}')" class="p-3 rounded-xl border border-[#E2E8F0] bg-[#F8FAFC]/60 hover:border-[#2563EB] cursor-pointer transition flex items-center justify-between gap-2">
                <div class="min-w-0">
                    <div class="font-bold text-xs text-[#0F172A] truncate">${_escPerfHtml(mb.name)}</div>
                    <div class="text-[11px] text-[#64748B]">${_escPerfHtml(mb.team_role)} &bull; Tuntas ${mb.completed}/${mb.total_tasks} &bull; Revisi ${mb.revision_count}</div>
                </div>
                <span class="px-2.5 py-1 rounded-lg bg-[#EFF6FF] text-[#2563EB] font-bold text-xs shrink-0">${mb.score}</span>
            </div>`).join('') || `<p class="text-xs text-[#64748B] text-center py-4 col-span-full">Belum ada anggota regu terdaftar.</p>`;

        wrap.innerHTML = `
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                <div class="rounded-2xl p-4 bg-white border border-[#E2E8F0]">
                    <p class="text-[11px] font-semibold text-[#64748B]">Total Tugas Regu</p>
                    <p class="font-outfit font-bold text-2xl text-[#0F172A] mt-1">${m.total_tasks}</p>
                    <p class="text-[11px] text-[#64748B] mt-0.5">${m.completed} tuntas (${m.completion_rate}%)</p>
                </div>
                <div class="rounded-2xl p-4 bg-white border border-[#E2E8F0]">
                    <p class="text-[11px] font-semibold text-[#64748B]">Skor Kinerja Regu</p>
                    <p class="font-outfit font-bold text-2xl text-[#2563EB] mt-1">${m.score}<span class="text-xs font-normal text-[#64748B]">/100</span></p>
                    <p class="text-[11px] text-[#64748B] mt-0.5">Kuota Aktif: ${m.active_quota}/3</p>
                </div>
                <div class="rounded-2xl p-4 bg-white border border-[#E2E8F0]">
                    <p class="text-[11px] font-semibold text-[#64748B]">Rata-rata Respon / Perbaikan</p>
                    <p class="font-outfit font-bold text-xl text-[#0F172A] mt-1">${respStr} / ${repStr}</p>
                    <p class="text-[11px] text-[#64748B] mt-0.5">Tepat waktu: ${m.on_time_rate}%</p>
                </div>
                <div class="rounded-2xl p-4 bg-white border border-[#E2E8F0]">
                    <p class="text-[11px] font-semibold text-[#64748B]">Rating Kepuasan Warga</p>
                    <p class="font-outfit font-bold text-2xl text-amber-600 mt-1">${ratingStr}</p>
                    <p class="text-[11px] text-[#64748B] mt-0.5">${m.ratings_count} ulasan warga</p>
                </div>
            </div>

            <div class="card-white rounded-2xl p-4 sm:p-5 bg-white border border-[#E2E8F0] space-y-3">
                <div class="flex flex-wrap items-center justify-between gap-2">
                    <h4 class="font-outfit font-bold text-sm text-[#0F172A]">Kontribusi Anggota &mdash; ${_escPerfHtml(m.team_name)} (${_escPerfHtml(m.team_code)})</h4>
                    <button type="button" onclick="selectExecTeam('ALL')" class="text-xs font-bold text-[#2563EB] hover:underline cursor-pointer">&larr; Semua Regu</button>
                </div>
                <div class="exec-layout-list ${isGrid ? 'hidden' : ''} overflow-x-auto">
                    <table class="w-full text-xs">
                        <thead class="bg-[#F8FAFC] text-[#475569] border-y border-[#E2E8F0]">
                            <tr>
                                <th class="text-left py-2.5 px-3 font-bold">Nama Petugas</th>
                                <th class="text-left py-2.5 px-3 font-bold">Peran</th>
                                <th class="text-center py-2.5 px-3 font-bold">Ditangani</th>
                                <th class="text-center py-2.5 px-3 font-bold">Tuntas</th>
                                <th class="text-center py-2.5 px-3 font-bold">Revisi</th>
                                <th class="text-right py-2.5 px-3 font-bold">Skor</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[#E2E8F0]">${memberRows}</tbody>
                    </table>
                </div>
                <div class="exec-layout-grid ${isGrid ? 'grid' : 'hidden'} grid-cols-1 md:grid-cols-2 gap-2.5">${memberCards}</div>
            </div>

            <div class="card-white rounded-2xl p-4 sm:p-5 bg-white border border-[#E2E8F0] space-y-3">
                <h4 class="font-outfit font-bold text-sm text-[#0F172A]">Rincian Pekerjaan Regu &mdash; ${_escPerfHtml(m.team_name)}</h4>
                ${_renderPerfTasksBlock(data.tasks, 'Belum ada riwayat pengerjaan regu tercatat.')}
            </div>`;
    }

    function renderExecOfficerDetail(officerId) {
        const wrap = document.getElementById('execOfficerDetailView');
        const data = window._execPerfOfficerDetails?.[String(officerId)];
        if (!wrap || !data) return;

        const m = data.metric;
        const repStr = m.avg_repair_hours !== null ? `${m.avg_repair_hours} jam` : '-';
        const ratingStr = m.avg_rating !== null ? `${m.avg_rating} / 5` : '-';

        wrap.innerHTML = `
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                <div class="rounded-2xl p-4 bg-white border border-[#E2E8F0]">
                    <p class="text-[11px] font-semibold text-[#64748B]">Tugas Ditangani</p>
                    <p class="font-outfit font-bold text-2xl text-[#0F172A] mt-1">${m.total_tasks}</p>
                    <p class="text-[11px] text-[#64748B] mt-0.5">${m.completed} tuntas</p>
                </div>
                <div class="rounded-2xl p-4 bg-white border border-[#E2E8F0]">
                    <p class="text-[11px] font-semibold text-[#64748B]">Skor Kinerja</p>
                    <p class="font-outfit font-bold text-2xl text-[#2563EB] mt-1">${m.score}<span class="text-xs font-normal text-[#64748B]">/100</span></p>
                    <p class="text-[11px] text-[#64748B] mt-0.5">Lolos 1x validasi: ${m.first_pass_rate}%</p>
                </div>
                <div class="rounded-2xl p-4 bg-white border border-[#E2E8F0]">
                    <p class="text-[11px] font-semibold text-[#64748B]">Rata-rata Pengerjaan</p>
                    <p class="font-outfit font-bold text-2xl text-[#0F172A] mt-1">${repStr}</p>
                    <p class="text-[11px] text-[#64748B] mt-0.5">Tepat waktu: ${m.on_time_rate}%</p>
                </div>
                <div class="rounded-2xl p-4 bg-white border border-[#E2E8F0]">
                    <p class="text-[11px] font-semibold text-[#64748B]">Kepuasan Warga</p>
                    <p class="font-outfit font-bold text-2xl text-amber-600 mt-1">${ratingStr}</p>
                    <p class="text-[11px] text-[#64748B] mt-0.5">Revisi: ${m.revision_count} kali</p>
                </div>
            </div>

            <div class="card-white rounded-2xl p-4 sm:p-5 bg-white border border-[#E2E8F0] space-y-3">
                <div class="flex flex-wrap items-center justify-between gap-2">
                    <h4 class="font-outfit font-bold text-sm text-[#0F172A]">Rincian Pekerjaan Individu &mdash; ${_escPerfHtml(m.name)}</h4>
                    <div class="flex items-center gap-3">
                        <span class="text-xs font-semibold text-[#64748B]">${_escPerfHtml(m.team_name)} (${_escPerfHtml(m.team_role)})</span>
                        <button type="button" onclick="selectExecOfficer('ALL')" class="text-xs font-bold text-[#2563EB] hover:underline cursor-pointer">&larr; Semua Individu</button>
                    </div>
                </div>
                ${_renderPerfTasksBlock(data.tasks, 'Belum ada riwayat pengerjaan individu tercatat.')}
            </div>`;
    }

    function selectExecTeam(teamId) {
        const sel = document.getElementById('execFieldPerfFilter-team');
        if (sel) sel.value = String(teamId);
        applyExecFieldPerfFilter();
    }

    function selectExecOfficer(officerId) {
        const sel = document.getElementById('execFieldPerfFilter-officer');
        if (sel) sel.value = String(officerId);
        applyExecFieldPerfFilter();
    }

    function applyExecFieldPerfFilter() {
        const mode = window._execFieldPerfMode || 'team';
        const period = encodeURIComponent(window._execPerfPeriod || 'ALL');
        const teamSelect = document.getElementById('execFieldPerfFilter-team');
        const officerSelect = document.getElementById('execFieldPerfFilter-officer');
        const printBtn = document.getElementById('execFieldPerfPrintBtn');
        const csvBtn = document.getElementById('execFieldPerfCsvBtn');

        const teamAllView = document.getElementById('execTeamAllView');
        const teamDetailView = document.getElementById('execTeamDetailView');
        const officerAllView = document.getElementById('execOfficerAllView');
        const officerDetailView = document.getElementById('execOfficerDetailView');

        const printBase = `{{ route('petugas.performance.print') }}`;
        const csvBase = `{{ route('reports.export-csv') }}`;

        if (mode === 'team') {
            const val = teamSelect ? teamSelect.value : 'ALL';
            if (val === 'ALL') {
                if (teamAllView) teamAllView.classList.remove('hidden');
                if (teamDetailView) teamDetailView.classList.add('hidden');
            } else {
                renderExecTeamDetail(val);
                if (teamAllView) teamAllView.classList.add('hidden');
                if (teamDetailView) teamDetailView.classList.remove('hidden');
            }
            if (printBtn) {
                printBtn.href = val === 'ALL'
                    ? `${printBase}?mode=team&scope=all&period=${period}`
                    : `${printBase}?mode=team&team_id=${encodeURIComponent(val)}&period=${period}`;
            }
            if (csvBtn) {
                csvBtn.href = val === 'ALL'
                    ? `${csvBase}?mode=team&period=${period}`
                    : `${csvBase}?mode=team&team_id=${encodeURIComponent(val)}&period=${period}`;
            }
        } else {
            const val = officerSelect ? officerSelect.value : 'ALL';
            if (val === 'ALL') {
                if (officerAllView) officerAllView.classList.remove('hidden');
                if (officerDetailView) officerDetailView.classList.add('hidden');
            } else {
                renderExecOfficerDetail(val);
                if (officerAllView) officerAllView.classList.add('hidden');
                if (officerDetailView) officerDetailView.classList.remove('hidden');
            }
            if (printBtn) {
                printBtn.href = val === 'ALL'
                    ? `${printBase}?mode=individual&scope=all&period=${period}`
                    : `${printBase}?mode=individual&officer_id=${encodeURIComponent(val)}&period=${period}`;
            }
            if (csvBtn) {
                csvBtn.href = val === 'ALL'
                    ? `${csvBase}?mode=individual&period=${period}`
                    : `${csvBase}?mode=individual&officer_id=${encodeURIComponent(val)}&period=${period}`;
            }
        }
    }

    function switchExecFieldPerf(mode) {
        window._execFieldPerfMode = mode;
        const tWrap = document.getElementById('execFieldPerfWrap-team');
        const oWrap = document.getElementById('execFieldPerfWrap-officer');
        const tBtn = document.getElementById('execFieldPerfBtn-team');
        const oBtn = document.getElementById('execFieldPerfBtn-officer');
        const tFilter = document.getElementById('execFieldPerfFilter-team');
        const oFilter = document.getElementById('execFieldPerfFilter-officer');

        if (tWrap) tWrap.classList.toggle('hidden', mode !== 'team');
        if (oWrap) oWrap.classList.toggle('hidden', mode !== 'officer');
        if (tFilter) tFilter.classList.toggle('hidden', mode !== 'team');
        if (oFilter) oFilter.classList.toggle('hidden', mode !== 'officer');

        if (tBtn && oBtn) {
            tBtn.className = mode === 'team'
                ? 'px-2.5 py-1.5 rounded-lg text-xs font-bold transition cursor-pointer bg-[#2563EB] text-white whitespace-nowrap text-center'
                : 'px-2.5 py-1.5 rounded-lg text-xs font-semibold transition cursor-pointer text-[#475569] hover:text-[#0F172A] whitespace-nowrap text-center';
            oBtn.className = mode === 'officer'
                ? 'px-2.5 py-1.5 rounded-lg text-xs font-bold transition cursor-pointer bg-[#2563EB] text-white whitespace-nowrap text-center'
                : 'px-2.5 py-1.5 rounded-lg text-xs font-semibold transition cursor-pointer text-[#475569] hover:text-[#0F172A] whitespace-nowrap text-center';
        }
        applyExecFieldPerfFilter();
    }

    // Helper Paginasi Client-Side (Daftar & Petak serempak)
    function initPerfPager(pagerId, listSelector, gridSelector, unitLabel) {
        const sizeEl = document.getElementById(pagerId + '-size');
        const infoEl = document.getElementById(pagerId + '-info');
        const navEl = document.getElementById(pagerId + '-nav');
        if (!sizeEl || !infoEl || !navEl) return;

        let currentPage = 1;

        function render() {
            const listItems = Array.from(document.querySelectorAll(listSelector));
            const gridItems = gridSelector ? Array.from(document.querySelectorAll(gridSelector)) : [];
            const total = Math.max(listItems.length, gridItems.length);
            const perPage = parseInt(sizeEl.value, 10) || 10;
            const totalPages = Math.max(1, Math.ceil(total / perPage));
            if (currentPage > totalPages) currentPage = totalPages;

            const startIdx = (currentPage - 1) * perPage;
            const endIdx = Math.min(startIdx + perPage, total);

            listItems.forEach((el, idx) => el.classList.toggle('hidden', idx < startIdx || idx >= endIdx));
            gridItems.forEach((el, idx) => el.classList.toggle('hidden', idx < startIdx || idx >= endIdx));

            infoEl.textContent = total === 0
                ? `Menampilkan 0 ${unitLabel}`
                : `Menampilkan ${startIdx + 1}–${endIdx} dari ${total} ${unitLabel}`;

            navEl.innerHTML = '';
            const mkBtn = (label, page, disabled, active) => {
                const btn = document.createElement('button');
                btn.type = 'button';
                btn.textContent = label;
                btn.disabled = disabled;
                btn.className = active
                    ? 'px-3 py-1.5 rounded-xl text-xs font-bold bg-[#2563EB] text-white cursor-pointer'
                    : (disabled
                        ? 'px-3 py-1.5 rounded-xl text-xs font-semibold bg-[#F8FAFC] text-[#94A3B8] border border-[#E2E8F0] cursor-not-allowed'
                        : 'px-3 py-1.5 rounded-xl text-xs font-bold bg-white text-[#0F172A] border border-[#E2E8F0] hover:border-[#2563EB] hover:text-[#2563EB] cursor-pointer transition');
                if (!disabled && !active) {
                    btn.onclick = () => { currentPage = page; render(); };
                }
                return btn;
            };

            navEl.appendChild(mkBtn('Sebelumnya', currentPage - 1, currentPage <= 1, false));
            for (let p = 1; p <= totalPages; p++) {
                navEl.appendChild(mkBtn(String(p), p, false, p === currentPage));
            }
            navEl.appendChild(mkBtn('Berikutnya', currentPage + 1, currentPage >= totalPages, false));
        }

        sizeEl.addEventListener('change', () => { currentPage = 1; render(); });
        render();
    }

    // Inisialisasi tata letak otomatis & paginasi
    document.addEventListener('DOMContentLoaded', () => {
        setExecPerfLayout(window.innerWidth < 768 ? 'grid' : 'list');
        initPerfPager('execTeamPager', '#execFieldPerfWrap-team .exec-team-row', '#execFieldPerfWrap-team .exec-team-card', 'regu');
        initPerfPager('execOfficerPager', '#execFieldPerfWrap-officer .exec-officer-row', '#execFieldPerfWrap-officer .exec-officer-card', 'petugas');
        initPerfPager('execOperatorPager', '.exec-op-row', '.exec-op-card', 'operator');
    });
</script>
