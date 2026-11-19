@php
    $perfService = app(\App\Services\PerformanceMetricsService::class);
    $authUser = auth()->user();
    $isAdminPreview = $authUser->isSuperAdmin();

    $activePerfMode = in_array(request('perf_mode'), ['individual', 'team', 'operator'], true)
        ? request('perf_mode')
        : 'individual';

    $allFieldOfficers = $isAdminPreview
        ? \App\Models\User::where('role', 'PETUGAS')->with('teams')->orderBy('name')->get()
        : collect();
    $allFieldTeams = $isAdminPreview
        ? \App\Models\Team::with('leader')->orderBy('name')->get()
        : collect();

    $availablePeriods = \App\Services\PerformanceMetricsService::getAvailablePeriods();
    $selectedPeriod = request('perf_period', 'ALL');
    [$periodFrom, $periodTo, $selectedPeriodLabel] = \App\Services\PerformanceMetricsService::resolvePeriodBounds($selectedPeriod);
    $perfFrom = request('perf_from') ?: $periodFrom;
    $perfTo = request('perf_to') ?: $periodTo;

    $isAllOfficers = false;
    $isAllTeams = false;
    $allOfficerMetrics = [];
    $allTeamMetrics = [];
    $selectedOfficerId = 'ALL';
    $selectedTeamId = 'ALL';

    if ($isAdminPreview) {
        $reqOfficerParam = request('officer_id', 'ALL');
        $reqTeamParam = request('team_id', 'ALL');

        if ($reqOfficerParam === 'ALL' || (int) $reqOfficerParam <= 0) {
            $isAllOfficers = true;
            $selectedOfficerId = 'ALL';
            $allOfficerMetrics = $perfService->getFieldOfficerMetrics($perfFrom, $perfTo);
            $targetUser = null;
        } else {
            $targetUser = $allFieldOfficers->firstWhere('id', (int) $reqOfficerParam);
            $selectedOfficerId = $targetUser ? (string) $targetUser->id : 'ALL';
            if (!$targetUser) {
                $isAllOfficers = true;
                $allOfficerMetrics = $perfService->getFieldOfficerMetrics($perfFrom, $perfTo);
            }
        }

        if ($reqTeamParam === 'ALL' || (int) $reqTeamParam <= 0) {
            $isAllTeams = true;
            $selectedTeamId = 'ALL';
            $allTeamMetrics = $perfService->getFieldTeamMetrics($perfFrom, $perfTo);
            $targetTeam = null;
        } else {
            $targetTeam = $allFieldTeams->firstWhere('id', (int) $reqTeamParam);
            $selectedTeamId = $targetTeam ? (string) $targetTeam->id : 'ALL';
            if (!$targetTeam) {
                $isAllTeams = true;
                $allTeamMetrics = $perfService->getFieldTeamMetrics($perfFrom, $perfTo);
            }
        }
    } else {
        $targetUser = $authUser;
        $targetTeam = $myTeam ?? null;
        $selectedOfficerId = (string) ($targetUser->id ?? 0);
        $selectedTeamId = (string) ($targetTeam->id ?? 0);
    }

    $teamId = $targetTeam->id ?? null;

    $individualMetricList = $targetUser
        ? $perfService->getFieldOfficerMetrics($perfFrom, $perfTo, null, $targetUser->id)
        : [];
    $myIndividualMetric = $individualMetricList[0] ?? [
        'name' => $targetUser?->name ?? 'Pekerja Lapangan',
        'team_name' => $targetUser?->teams?->first()?->name ?? ($targetTeam->name ?? 'Belum Tergabung Regu'),
        'team_role' => ($targetUser?->teams?->first() && $targetUser->teams->first()->leader_id === $targetUser->id) ? 'Ketua Regu' : 'Anggota Regu',
        'total_tasks' => 0, 'in_progress' => 0, 'waiting_validation' => 0, 'completed' => 0,
        'completion_rate' => 0, 'avg_response_hours' => null, 'avg_repair_hours' => null,
        'revision_count' => 0, 'first_pass_rate' => 100, 'on_time_rate' => 100, 'avg_rating' => null, 'score' => 0,
    ];

    $myTeamMetricList = $teamId ? $perfService->getFieldTeamMetrics($perfFrom, $perfTo, $teamId) : [];
    $myTeamMetric = $myTeamMetricList[0] ?? null;

    $teamMembersMetrics = $teamId ? $perfService->getFieldOfficerMetrics($perfFrom, $perfTo, $teamId) : [];

    $opRespUserId = $targetUser?->id ?? ($allFieldOfficers->first()?->id ?? $authUser->id);
    $opRespTeamId = $teamId ?? ($targetUser?->teams?->first()?->id ?? $allFieldTeams->first()?->id);
    $operatorResp = $perfService->getOperatorResponsivenessForTeam($opRespTeamId, $opRespUserId, $perfFrom, $perfTo);

    $individualTasks = $targetUser
        ? $perfService->getPetugasPrintableTasks($targetUser->teams->first()?->id, $targetUser->id, 'individual', $perfFrom, $perfTo)
        : collect();

    // URL Cetak Laporan awal sesuai mode, periode & filter aktif
    if ($activePerfMode === 'team') {
        $initialPrintUrl = ($isAdminPreview && $isAllTeams)
            ? route('petugas.performance.print', ['mode' => 'team', 'scope' => 'all', 'period' => $selectedPeriod])
            : route('petugas.performance.print', array_filter(['mode' => 'team', 'team_id' => $teamId, 'officer_id' => !$isAdminPreview ? $authUser->id : null, 'period' => $selectedPeriod]));
    } else {
        $initialPrintUrl = ($isAdminPreview && $isAllOfficers)
            ? route('petugas.performance.print', ['mode' => 'individual', 'scope' => 'all', 'period' => $selectedPeriod])
            : route('petugas.performance.print', ['mode' => 'individual', 'officer_id' => $targetUser?->id ?? $authUser->id, 'period' => $selectedPeriod]);
    }
@endphp

<div class="space-y-5">
    <!-- Header Responsif + Periode + Filter (Khusus Super Admin) + Toggle Mode + Cetak Laporan -->
    <div class="card-white rounded-2xl p-4 sm:p-5 bg-white border border-[#E2E8F0] shadow-xs">
        <div class="flex flex-col xl:flex-row xl:items-center justify-between gap-3">
            <div class="flex items-center justify-between gap-2.5 shrink-0">
                <div class="flex items-center gap-2.5 min-w-0">
                    <div class="w-9 h-9 rounded-xl bg-[#EFF6FF] text-[#2563EB] border border-[#BFDBFE] flex items-center justify-center shrink-0">
                        <i data-lucide="bar-chart-3" class="w-4 h-4"></i>
                    </div>
                    <div class="min-w-0">
                        <h3 class="font-outfit font-bold text-sm sm:text-base text-[#0F172A] truncate">Laporan Kinerja Pekerja Lapangan</h3>
                        <p class="text-[11px] font-semibold text-[#64748B]">Periode: {{ $selectedPeriodLabel }}</p>
                    </div>
                </div>
            </div>

            <div class="flex flex-col sm:flex-row sm:flex-wrap xl:flex-nowrap items-stretch sm:items-center gap-2 w-full xl:w-auto">
                <!-- Dropdown Periode Pill (Desain Kalender Biru) + Filter Admin -->
                <div class="grid {{ $isAdminPreview ? 'grid-cols-2' : 'grid-cols-1' }} sm:flex items-center gap-2">
                    <div class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full bg-[#F8FAFC] border border-[#E2E8F0] w-full sm:w-auto shrink-0">
                        <i data-lucide="calendar" class="w-3.5 h-3.5 text-[#2563EB] shrink-0"></i>
                        <select id="petugasPerfPeriodSelect"
                            onchange="changePetugasPerfPeriod(this.value)"
                            class="bg-transparent text-xs font-bold text-[#0F172A] focus:outline-none cursor-pointer pr-1 w-full sm:w-auto"
                            aria-label="Pilih Periode Laporan Kinerja">
                            @foreach($availablePeriods as $per)
                                <option value="{{ $per['value'] }}" {{ $selectedPeriod === $per['value'] ? 'selected' : '' }}>
                                    {{ $per['label'] }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    @if($isAdminPreview)
                        <!-- Filter Individu (Khusus Super Admin saat tab Individu aktif) -->
                        <select id="petugasAdminFilter-individual"
                            onchange="window.location.href='{{ route('petugas.dashboard', ['tab' => 'performance', 'perf_mode' => 'individual', 'perf_period' => $selectedPeriod]) }}&officer_id=' + encodeURIComponent(this.value) + '&team_id={{ $selectedTeamId }}'"
                            class="{{ $activePerfMode === 'individual' ? '' : 'hidden' }} w-full sm:w-auto text-xs font-semibold rounded-xl border border-[#E2E8F0] py-2 px-2.5 bg-[#F8FAFC] text-[#334155] focus:outline-none focus:border-[#2563EB] shrink-0">
                            <option value="ALL" {{ $selectedOfficerId === 'ALL' ? 'selected' : '' }}>Semua Individu</option>
                            @foreach($allFieldOfficers as $fo)
                                <option value="{{ $fo->id }}" {{ (string) $fo->id === $selectedOfficerId ? 'selected' : '' }}>
                                    {{ $fo->name }}
                                </option>
                            @endforeach
                        </select>

                        <!-- Filter Regu (Khusus Super Admin saat tab Regu aktif) -->
                        <select id="petugasAdminFilter-team"
                            onchange="window.location.href='{{ route('petugas.dashboard', ['tab' => 'performance', 'perf_mode' => 'team', 'perf_period' => $selectedPeriod]) }}&team_id=' + encodeURIComponent(this.value) + '&officer_id={{ $selectedOfficerId }}'"
                            class="{{ $activePerfMode === 'team' ? '' : 'hidden' }} w-full sm:w-auto text-xs font-semibold rounded-xl border border-[#E2E8F0] py-2 px-2.5 bg-[#F8FAFC] text-[#334155] focus:outline-none focus:border-[#2563EB] shrink-0">
                            <option value="ALL" {{ $selectedTeamId === 'ALL' ? 'selected' : '' }}>Semua Regu</option>
                            @foreach($allFieldTeams as $ft)
                                <option value="{{ $ft->id }}" {{ (string) $ft->id === $selectedTeamId ? 'selected' : '' }}>
                                    {{ $ft->name }}
                                </option>
                            @endforeach
                        </select>
                    @endif
                </div>

                <!-- Toggle Mode (Penuh 3 kolom di mobile) -->
                <div class="grid grid-cols-3 sm:inline-flex items-center p-0.5 rounded-xl bg-[#F8FAFC] border border-[#E2E8F0] shrink-0" role="group">
                    <button type="button" id="petugasPerfModeBtn-individual" onclick="switchPetugasPerfMode('individual')"
                        class="px-2.5 py-1.5 rounded-lg text-xs transition cursor-pointer whitespace-nowrap text-center {{ $activePerfMode === 'individual' ? 'font-bold bg-[#2563EB] text-white' : 'font-semibold text-[#475569] hover:text-[#0F172A]' }}">
                        Individu
                    </button>
                    <button type="button" id="petugasPerfModeBtn-team" onclick="switchPetugasPerfMode('team')"
                        class="px-2.5 py-1.5 rounded-lg text-xs transition cursor-pointer whitespace-nowrap text-center {{ $activePerfMode === 'team' ? 'font-bold bg-[#2563EB] text-white' : 'font-semibold text-[#475569] hover:text-[#0F172A]' }}">
                        Regu
                    </button>
                    <button type="button" id="petugasPerfModeBtn-operator" onclick="switchPetugasPerfMode('operator')"
                        class="px-2.5 py-1.5 rounded-lg text-xs transition cursor-pointer whitespace-nowrap text-center {{ $activePerfMode === 'operator' ? 'font-bold bg-[#2563EB] text-white' : 'font-semibold text-[#475569] hover:text-[#0F172A]' }}">
                        Responsivitas
                    </button>
                </div>

                <a id="petugasPerfPrintLink"
                    href="{{ $initialPrintUrl }}"
                    target="_blank"
                    class="{{ $activePerfMode === 'operator' ? 'hidden' : 'inline-flex' }} justify-center px-3 py-2 rounded-xl bg-[#0F172A] hover:bg-[#1E293B] text-white text-xs font-bold transition items-center gap-1.5 cursor-pointer shrink-0 whitespace-nowrap">
                    <i data-lucide="printer" class="w-3.5 h-3.5"></i>
                    <span>Cetak Laporan</span>
                </a>
            </div>
        </div>
    </div>

    <!-- MODE 1: LAPORAN INDIVIDU -->
    <div id="petugasPerfPanel-individual" class="{{ $activePerfMode === 'individual' ? '' : 'hidden' }} space-y-4">
        @if($isAdminPreview && $isAllOfficers)
            @php
                $totalOff = count($allOfficerMetrics);
                $sumOffTasks = array_sum(array_column($allOfficerMetrics, 'total_tasks'));
                $sumOffCompleted = array_sum(array_column($allOfficerMetrics, 'completed'));
                $avgOffScore = $totalOff > 0 ? round(array_sum(array_column($allOfficerMetrics, 'score')) / $totalOff, 1) : 0;
            @endphp
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
                    <p class="text-[11px] text-[#64748B] mt-0.5">Pilih 1 nama untuk detail</p>
                </div>
            </div>

            <div class="card-white rounded-2xl p-5 bg-white border border-[#E2E8F0] space-y-3">
                <h4 class="font-outfit font-bold text-sm text-[#0F172A]">Rekap Kinerja Seluruh Pekerja Lapangan ({{ $totalOff }} Orang)</h4>
                <div class="overflow-x-auto">
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
                            @forelse($allOfficerMetrics as $of)
                            <tr onclick="window.location.href='{{ route('petugas.dashboard', ['tab' => 'performance', 'perf_mode' => 'individual', 'officer_id' => $of['user_id'], 'team_id' => $selectedTeamId]) }}'"
                                class="petugas-all-off-row hover:bg-blue-50/40 cursor-pointer transition"
                                title="Klik untuk melihat rincian lengkap {{ $of['name'] }}">
                                <td class="py-2.5 px-3">
                                    <div class="font-bold text-[#0F172A]">{{ $of['name'] }}</div>
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
                @include('partials.perf_pagination_bar', ['pagerId' => 'petugasAllOffPager', 'unitLabel' => 'petugas'])
            </div>
        @else
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                <div class="rounded-2xl p-4 bg-white border border-[#E2E8F0]">
                    <p class="text-[11px] font-semibold text-[#64748B]">Tugas Ditangani</p>
                    <p class="font-outfit font-bold text-2xl text-[#0F172A] mt-1">{{ $myIndividualMetric['total_tasks'] }}</p>
                    <p class="text-[11px] text-[#64748B] mt-0.5">{{ $myIndividualMetric['completed'] }} tuntas</p>
                </div>
                <div class="rounded-2xl p-4 bg-white border border-[#E2E8F0]">
                    <p class="text-[11px] font-semibold text-[#64748B]">Skor Kinerja</p>
                    <p class="font-outfit font-bold text-2xl text-[#2563EB] mt-1">{{ $myIndividualMetric['score'] }}<span class="text-xs font-normal text-[#64748B]">/100</span></p>
                    <p class="text-[11px] text-[#64748B] mt-0.5">Lolos 1x validasi: {{ $myIndividualMetric['first_pass_rate'] }}%</p>
                </div>
                <div class="rounded-2xl p-4 bg-white border border-[#E2E8F0]">
                    <p class="text-[11px] font-semibold text-[#64748B]">Rata-rata Pengerjaan</p>
                    <p class="font-outfit font-bold text-2xl text-[#0F172A] mt-1">
                        {{ $myIndividualMetric['avg_repair_hours'] !== null ? $myIndividualMetric['avg_repair_hours'] . ' jam' : '-' }}
                    </p>
                    <p class="text-[11px] text-[#64748B] mt-0.5">Tepat waktu: {{ $myIndividualMetric['on_time_rate'] }}%</p>
                </div>
                <div class="rounded-2xl p-4 bg-white border border-[#E2E8F0]">
                    <p class="text-[11px] font-semibold text-[#64748B]">Kepuasan Warga</p>
                    <p class="font-outfit font-bold text-2xl text-amber-600 mt-1">
                        {{ $myIndividualMetric['avg_rating'] !== null ? $myIndividualMetric['avg_rating'] . ' / 5' : '-' }}
                    </p>
                    <p class="text-[11px] text-[#64748B] mt-0.5">Revisi: {{ $myIndividualMetric['revision_count'] }} kali</p>
                </div>
            </div>

            <div class="card-white rounded-2xl p-5 bg-white border border-[#E2E8F0] space-y-3">
                <div class="flex items-center justify-between">
                    <h4 class="font-outfit font-bold text-sm text-[#0F172A]">Rincian Pekerjaan Individu &mdash; {{ $myIndividualMetric['name'] }}</h4>
                    <span class="text-xs font-semibold text-[#64748B]">{{ $myIndividualMetric['team_name'] }} ({{ $myIndividualMetric['team_role'] }})</span>
                </div>
                @if($individualTasks->isEmpty())
                    <p class="text-xs text-[#64748B] py-6 text-center">Belum ada riwayat pengerjaan individu tercatat.</p>
                @else
                    <div class="overflow-x-auto">
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
                            <tbody class="divide-y divide-[#E2E8F0]">
                                @foreach($individualTasks as $t)
                                <tr class="hover:bg-slate-50/70">
                                    <td class="py-2.5 px-3 font-mono font-bold text-[#0F172A] whitespace-nowrap">#{{ $t->public_code }}</td>
                                    <td class="py-2.5 px-3">
                                        <div class="font-bold text-[#0F172A]">{{ $t->title }}</div>
                                        <div class="text-[11px] text-[#64748B] truncate max-w-xs">{{ $t->address }}</div>
                                    </td>
                                    <td class="py-2.5 px-3 text-[#475569]">{{ $t->category?->name ?? 'Umum' }}</td>
                                    <td class="py-2.5 px-3 text-center">
                                        <span class="px-2 py-0.5 rounded-md text-[10px] font-bold {{ in_array($t->status, ['COMPLETED','CLOSED']) ? 'bg-emerald-50 text-emerald-700' : 'bg-amber-50 text-amber-700' }}">
                                            {{ \App\Services\PerformanceMetricsService::statusLabelId($t->status) }}
                                        </span>
                                    </td>
                                    <td class="py-2.5 px-3 text-center font-bold text-amber-600">
                                        {{ $t->rating ? $t->rating->rating . '/5' : '-' }}
                                    </td>
                                    <td class="py-2.5 px-3 text-right">
                                        @if(in_array($t->status, ['COMPLETED', 'CLOSED']))
                                            <a href="{{ route('complaints.bap', $t->id) }}" target="_blank" class="px-2.5 py-1 rounded-lg bg-[#EFF6FF] text-[#2563EB] font-bold text-[11px] hover:bg-[#DBEAFE]">
                                                Cetak BAP
                                            </a>
                                        @else
                                            <span class="text-[11px] text-[#94A3B8]">-</span>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        @endif
    </div>

    <!-- MODE 2: LAPORAN REGU -->
    <div id="petugasPerfPanel-team" class="{{ $activePerfMode === 'team' ? '' : 'hidden' }} space-y-4">
        @if($isAdminPreview && $isAllTeams)
            @php
                $totalTm = count($allTeamMetrics);
                $sumTmTasks = array_sum(array_column($allTeamMetrics, 'total_tasks'));
                $sumTmCompleted = array_sum(array_column($allTeamMetrics, 'completed'));
                $avgTmScore = $totalTm > 0 ? round(array_sum(array_column($allTeamMetrics, 'score')) / $totalTm, 1) : 0;
            @endphp
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
                    <p class="text-[11px] text-[#64748B] mt-0.5">Pilih 1 regu untuk detail</p>
                </div>
            </div>

            <div class="card-white rounded-2xl p-5 bg-white border border-[#E2E8F0] space-y-3">
                <h4 class="font-outfit font-bold text-sm text-[#0F172A]">Rekap Kinerja Seluruh Regu Lapangan ({{ $totalTm }} Regu)</h4>
                <div class="overflow-x-auto">
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
                            @forelse($allTeamMetrics as $tm)
                            <tr onclick="window.location.href='{{ route('petugas.dashboard', ['tab' => 'performance', 'perf_mode' => 'team', 'team_id' => $tm['team_id'], 'officer_id' => $selectedOfficerId]) }}'"
                                class="petugas-all-team-row hover:bg-blue-50/40 cursor-pointer transition"
                                title="Klik untuk melihat rincian lengkap {{ $tm['team_name'] }}">
                                <td class="py-2.5 px-3">
                                    <div class="font-bold text-[#0F172A]">{{ $tm['team_name'] }}</div>
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
                                <td colspan="7" class="py-6 text-center text-[#64748B]">Belum ada data regu lapangan.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @include('partials.perf_pagination_bar', ['pagerId' => 'petugasAllTeamPager', 'unitLabel' => 'regu'])
            </div>
        @elseif(!$myTeamMetric)
            <div class="rounded-2xl p-8 bg-white border border-[#E2E8F0] text-center text-xs text-[#64748B]">
                Akun Anda belum tergabung dalam Regu Lapangan resmi.
            </div>
        @else
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                <div class="rounded-2xl p-4 bg-white border border-[#E2E8F0]">
                    <p class="text-[11px] font-semibold text-[#64748B]">Total Tugas Regu</p>
                    <p class="font-outfit font-bold text-2xl text-[#0F172A] mt-1">{{ $myTeamMetric['total_tasks'] }}</p>
                    <p class="text-[11px] text-[#64748B] mt-0.5">{{ $myTeamMetric['completed'] }} tuntas ({{ $myTeamMetric['completion_rate'] }}%)</p>
                </div>
                <div class="rounded-2xl p-4 bg-white border border-[#E2E8F0]">
                    <p class="text-[11px] font-semibold text-[#64748B]">Skor Kinerja Regu</p>
                    <p class="font-outfit font-bold text-2xl text-[#2563EB] mt-1">{{ $myTeamMetric['score'] }}<span class="text-xs font-normal text-[#64748B]">/100</span></p>
                    <p class="text-[11px] text-[#64748B] mt-0.5">Kuota Aktif: {{ $myTeamMetric['active_quota'] }}/3</p>
                </div>
                <div class="rounded-2xl p-4 bg-white border border-[#E2E8F0]">
                    <p class="text-[11px] font-semibold text-[#64748B]">Rata-rata Respon / Perbaikan</p>
                    <p class="font-outfit font-bold text-xl text-[#0F172A] mt-1">
                        {{ $myTeamMetric['avg_response_hours'] !== null ? $myTeamMetric['avg_response_hours'] . 'j' : '-' }} /
                        {{ $myTeamMetric['avg_repair_hours'] !== null ? $myTeamMetric['avg_repair_hours'] . 'j' : '-' }}
                    </p>
                    <p class="text-[11px] text-[#64748B] mt-0.5">Tepat waktu: {{ $myTeamMetric['on_time_rate'] }}%</p>
                </div>
                <div class="rounded-2xl p-4 bg-white border border-[#E2E8F0]">
                    <p class="text-[11px] font-semibold text-[#64748B]">Rating Kepuasan Warga</p>
                    <p class="font-outfit font-bold text-2xl text-amber-600 mt-1">
                        {{ $myTeamMetric['avg_rating'] !== null ? $myTeamMetric['avg_rating'] . ' / 5' : '-' }}
                    </p>
                    <p class="text-[11px] text-[#64748B] mt-0.5">{{ $myTeamMetric['ratings_count'] }} ulasan warga</p>
                </div>
            </div>

            <!-- Kontribusi Anggota Regu -->
            <div class="card-white rounded-2xl p-5 bg-white border border-[#E2E8F0] space-y-3">
                <h4 class="font-outfit font-bold text-sm text-[#0F172A]">Kontribusi Anggota &mdash; {{ $myTeamMetric['team_name'] }} ({{ $myTeamMetric['team_code'] }})</h4>
                <div class="overflow-x-auto">
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
                        <tbody class="divide-y divide-[#E2E8F0]">
                            @foreach($teamMembersMetrics as $m)
                            <tr>
                                <td class="py-2.5 px-3 font-bold text-[#0F172A]">{{ $m['name'] }}</td>
                                <td class="py-2.5 px-3 text-[#475569]">{{ $m['team_role'] }}</td>
                                <td class="py-2.5 px-3 text-center font-semibold">{{ $m['total_tasks'] }}</td>
                                <td class="py-2.5 px-3 text-center font-bold text-emerald-700">{{ $m['completed'] }}</td>
                                <td class="py-2.5 px-3 text-center text-amber-700">{{ $m['revision_count'] }}</td>
                                <td class="py-2.5 px-3 text-right font-bold text-[#2563EB]">{{ $m['score'] }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif
    </div>

    <!-- MODE 3: TRANSPARANSI & RESPONSIVITAS OPERATOR -->
    <div id="petugasPerfPanel-operator" class="{{ $activePerfMode === 'operator' ? '' : 'hidden' }} space-y-4">
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
            <div class="rounded-2xl p-4 bg-white border border-[#E2E8F0]">
                <p class="text-[11px] font-semibold text-[#64748B]">Rata-rata Waktu Validasi Operator</p>
                <p class="font-outfit font-bold text-2xl text-[#2563EB] mt-1">
                    {{ $operatorResp['avg_validation_wait_hours'] !== null ? $operatorResp['avg_validation_wait_hours'] . ' jam' : '-' }}
                </p>
                <p class="text-[11px] text-[#64748B] mt-0.5">Sejak bukti dikirim hingga keputusan Operator</p>
            </div>
            <div class="rounded-2xl p-4 bg-white border border-[#E2E8F0]">
                <p class="text-[11px] font-semibold text-[#64748B]">Sedang Menunggu Validasi</p>
                <p class="font-outfit font-bold text-2xl text-amber-600 mt-1">{{ $operatorResp['pending_validation_count'] }}</p>
                <p class="text-[11px] text-[#64748B] mt-0.5">Tugas regu dalam antrean uji hasil Operator</p>
            </div>
            <div class="rounded-2xl p-4 bg-white border border-[#E2E8F0]">
                <p class="text-[11px] font-semibold text-[#64748B]">Rasio Persetujuan Validasi</p>
                <p class="font-outfit font-bold text-2xl text-emerald-600 mt-1">{{ $operatorResp['approval_rate'] }}%</p>
                <p class="text-[11px] text-[#64748B] mt-0.5">{{ $operatorResp['approved_count'] }} disetujui &bull; {{ $operatorResp['revision_count'] }} revisi</p>
            </div>
        </div>

        <div class="card-white rounded-2xl p-5 bg-white border border-[#E2E8F0] space-y-3">
            <h4 class="font-outfit font-bold text-sm text-[#0F172A]">Antrean Menunggu Validasi Operator Saat Ini</h4>
            @if(empty($operatorResp['pending_validation_items']))
                <p class="text-xs text-[#64748B] py-5 text-center">Tidak ada pekerjaan yang tertahan di antrean validasi Operator.</p>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-xs">
                        <thead class="bg-[#F8FAFC] text-[#475569] border-y border-[#E2E8F0]">
                            <tr>
                                <th class="text-left py-2.5 px-3 font-bold">Kode</th>
                                <th class="text-left py-2.5 px-3 font-bold">Judul Pekerjaan</th>
                                <th class="text-left py-2.5 px-3 font-bold">Dikirim Pada</th>
                                <th class="text-right py-2.5 px-3 font-bold">Lama Menunggu</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[#E2E8F0]">
                            @foreach($operatorResp['pending_validation_items'] as $pItem)
                            <tr>
                                <td class="py-2.5 px-3 font-mono font-bold text-[#0F172A]">#{{ $pItem['public_code'] }}</td>
                                <td class="py-2.5 px-3 font-semibold text-[#0F172A]">{{ $pItem['title'] }}</td>
                                <td class="py-2.5 px-3 text-[#64748B]">{{ $pItem['submitted_at'] }}</td>
                                <td class="py-2.5 px-3 text-right font-bold {{ $pItem['wait_hours'] >= 24 ? 'text-amber-600' : 'text-[#2563EB]' }}">
                                    {{ $pItem['wait_hours'] }} jam
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
</div>

<script>
    window._petugasActivePerfMode = @json($activePerfMode);
    window._petugasSelectedPeriod = @json($selectedPeriod);

    function changePetugasPerfPeriod(periodVal) {
        const url = new URL(window.location.href);
        url.searchParams.set('tab', 'performance');
        url.searchParams.set('perf_mode', window._petugasActivePerfMode || 'individual');
        url.searchParams.set('perf_period', periodVal);
        window.location.href = url.toString();
    }

    function switchPetugasPerfMode(mode) {
        window._petugasActivePerfMode = mode;
        ['individual', 'team', 'operator'].forEach(m => {
            const panel = document.getElementById('petugasPerfPanel-' + m);
            const btn = document.getElementById('petugasPerfModeBtn-' + m);
            if (panel) panel.classList.toggle('hidden', m !== mode);
            if (btn) {
                btn.className = m === mode
                    ? 'px-2.5 py-1.5 rounded-lg text-xs font-bold transition cursor-pointer bg-[#2563EB] text-white whitespace-nowrap text-center'
                    : 'px-2.5 py-1.5 rounded-lg text-xs font-semibold transition cursor-pointer text-[#475569] hover:text-[#0F172A] whitespace-nowrap text-center';
            }
        });

        // Toggle dropdown filter khusus Super Admin sesuai tab aktif
        const indFilter = document.getElementById('petugasAdminFilter-individual');
        const teamFilter = document.getElementById('petugasAdminFilter-team');
        if (indFilter) indFilter.classList.toggle('hidden', mode !== 'individual');
        if (teamFilter) teamFilter.classList.toggle('hidden', mode !== 'team');

        // Perbarui tampilan & URL tombol Cetak Laporan sesuai mode, periode & filter aktif
        const printLink = document.getElementById('petugasPerfPrintLink');
        if (printLink) {
            if (mode === 'operator') {
                printLink.classList.add('hidden');
                printLink.classList.remove('inline-flex');
                return;
            }
            printLink.classList.remove('hidden');
            printLink.classList.add('inline-flex');

            const printBase = `{{ route('petugas.performance.print') }}`;
            const isAdmin = {{ $isAdminPreview ? 'true' : 'false' }};
            const selectedOff = `{{ $selectedOfficerId }}`;
            const selectedTm = `{{ $selectedTeamId }}`;
            const period = encodeURIComponent(window._petugasSelectedPeriod || 'ALL');

            if (mode === 'team') {
                if (isAdmin && selectedTm === 'ALL') {
                    printLink.href = `${printBase}?mode=team&scope=all&period=${period}`;
                } else {
                    printLink.href = isAdmin
                        ? `${printBase}?mode=team&team_id=${encodeURIComponent(selectedTm)}&period=${period}`
                        : `${printBase}?mode=team&officer_id=${encodeURIComponent(selectedOff)}&period=${period}`;
                }
            } else {
                if (isAdmin && selectedOff === 'ALL') {
                    printLink.href = `${printBase}?mode=individual&scope=all&period=${period}`;
                } else {
                    printLink.href = `${printBase}?mode=individual&officer_id=${encodeURIComponent(selectedOff)}&period=${period}`;
                }
            }
        }
    }

    document.addEventListener('DOMContentLoaded', () => {
        const initSimplePager = (pagerId, rowSelector, unitLabel) => {
            const sizeEl = document.getElementById(pagerId + '-size');
            const infoEl = document.getElementById(pagerId + '-info');
            const navEl = document.getElementById(pagerId + '-nav');
            if (!sizeEl || !infoEl || !navEl) return;
            let page = 1;
            const render = () => {
                const rows = Array.from(document.querySelectorAll(rowSelector));
                const total = rows.length;
                const perPage = parseInt(sizeEl.value, 10) || 10;
                const totalPages = Math.max(1, Math.ceil(total / perPage));
                if (page > totalPages) page = totalPages;
                const start = (page - 1) * perPage;
                const end = Math.min(start + perPage, total);
                rows.forEach((r, idx) => r.classList.toggle('hidden', idx < start || idx >= end));
                infoEl.textContent = total === 0
                    ? `Menampilkan 0 ${unitLabel}`
                    : `Menampilkan ${start + 1}–${end} dari ${total} ${unitLabel}`;
                navEl.innerHTML = '';
                const mk = (lbl, target, disabled, active) => {
                    const b = document.createElement('button');
                    b.type = 'button';
                    b.textContent = lbl;
                    b.disabled = disabled;
                    b.className = active
                        ? 'px-3 py-1.5 rounded-xl text-xs font-bold bg-[#2563EB] text-white cursor-pointer'
                        : (disabled
                            ? 'px-3 py-1.5 rounded-xl text-xs font-semibold bg-[#F8FAFC] text-[#94A3B8] border border-[#E2E8F0] cursor-not-allowed'
                            : 'px-3 py-1.5 rounded-xl text-xs font-bold bg-white text-[#0F172A] border border-[#E2E8F0] hover:border-[#2563EB] hover:text-[#2563EB] cursor-pointer transition');
                    if (!disabled && !active) b.onclick = () => { page = target; render(); };
                    return b;
                };
                navEl.appendChild(mk('Sebelumnya', page - 1, page <= 1, false));
                for (let p = 1; p <= totalPages; p++) navEl.appendChild(mk(String(p), p, false, p === page));
                navEl.appendChild(mk('Berikutnya', page + 1, page >= totalPages, false));
            };
            sizeEl.addEventListener('change', () => { page = 1; render(); });
            render();
        };
        initSimplePager('petugasAllOffPager', '.petugas-all-off-row', 'petugas');
        initSimplePager('petugasAllTeamPager', '.petugas-all-team-row', 'regu');
    });
</script>
