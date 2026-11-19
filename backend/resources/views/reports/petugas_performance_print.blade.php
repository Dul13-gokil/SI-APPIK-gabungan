<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Kinerja {{ $mode === 'team' ? 'Regu Lapangan' : 'Individu Petugas' }} — SI-APPIK Kabupaten</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @media print {
            .no-print { display: none !important; }
            body { background: #ffffff !important; padding: 0 !important; }
            .doc-sheet { box-shadow: none !important; border: none !important; margin: 0 !important; padding: 0 !important; }
        }
        @page { size: A4 portrait; margin: 1.5cm; }
    </style>
</head>
<body class="bg-slate-100 text-slate-900 font-sans py-6 px-4">
    <div class="max-w-3xl mx-auto mb-4 flex items-center justify-between no-print">
        <button type="button" onclick="window.close(); setTimeout(() => { if (!window.closed) { if (window.history.length > 1) history.back(); else window.location.href = '{{ route('petugas.dashboard', ['tab' => 'performance']) }}'; } }, 120);"
            class="px-3.5 py-2 rounded-xl bg-white border border-slate-200 text-xs font-bold text-slate-700 hover:bg-slate-50 cursor-pointer">
            &times; Tutup Tab
        </button>
        <button type="button" onclick="window.print()"
            class="px-4 py-2 rounded-xl bg-[#2563EB] hover:bg-[#1D4ED8] text-white text-xs font-bold shadow-xs cursor-pointer">
            Cetak / Simpan PDF Laporan
        </button>
    </div>

    <div class="doc-sheet max-w-3xl mx-auto bg-white rounded-2xl border border-slate-200 shadow-sm p-8 sm:p-10 space-y-6">
        <!-- Kop Resmi -->
        <div class="border-b-2 border-slate-900 pb-4 text-center space-y-1">
            <p class="text-xs font-bold uppercase tracking-widest text-slate-600">Pemerintah Kabupaten</p>
            <h1 class="text-lg sm:text-xl font-extrabold uppercase tracking-wide text-slate-900">
                Dinas Pekerjaan Umum dan Penataan Ruang (PUPR)
            </h1>
            <p class="text-[11px] text-slate-500">
                Sistem Informasi Pengaduan &amp; Penanganan Infrastruktur (SI-APPIK) &bull; Lasusua, Sulawesi Tenggara
            </p>
        </div>

        <div class="text-center space-y-1">
            <h2 class="text-base font-extrabold uppercase underline underline-offset-4">
                @if(!empty($isAll))
                    {{ $mode === 'team' ? 'Rekapitulasi Laporan Kinerja Seluruh Regu Lapangan' : 'Rekapitulasi Laporan Kinerja Seluruh Pekerja Lapangan' }}
                @else
                    {{ $mode === 'team' ? 'Laporan Kinerja Regu Lapangan' : 'Laporan Kinerja Individu Pekerja Lapangan' }}
                @endif
            </h2>
            <p class="text-xs text-slate-600">
                Periode: <strong class="text-slate-900">{{ $periodLabel ?? 'Semua Periode' }}</strong> &bull;
                Dicetak pada: {{ now('Asia/Makassar')->translatedFormat('d F Y, H:i') }} WITA
            </p>
        </div>

        @if(!empty($isAll))
            @if($mode === 'team')
                @php
                    $totalTeams = count($allTeamMetrics);
                    $sumTasks = array_sum(array_column($allTeamMetrics, 'total_tasks'));
                    $sumCompleted = array_sum(array_column($allTeamMetrics, 'completed'));
                    $avgScore = $totalTeams > 0 ? round(array_sum(array_column($allTeamMetrics, 'score')) / $totalTeams, 1) : 0;
                @endphp
                <div class="grid grid-cols-4 gap-3 text-center">
                    <div class="border border-slate-200 rounded-xl p-3 bg-slate-50/70">
                        <p class="text-[10.5px] font-bold text-slate-500 uppercase">Total Regu</p>
                        <p class="text-lg font-extrabold text-slate-900 mt-1">{{ $totalTeams }}</p>
                    </div>
                    <div class="border border-slate-200 rounded-xl p-3 bg-slate-50/70">
                        <p class="text-[10.5px] font-bold text-slate-500 uppercase">Total Tugas</p>
                        <p class="text-lg font-extrabold text-slate-900 mt-1">{{ $sumTasks }}</p>
                    </div>
                    <div class="border border-slate-200 rounded-xl p-3 bg-slate-50/70">
                        <p class="text-[10.5px] font-bold text-slate-500 uppercase">Tugas Tuntas</p>
                        <p class="text-lg font-extrabold text-emerald-700 mt-1">{{ $sumCompleted }}</p>
                    </div>
                    <div class="border border-slate-200 rounded-xl p-3 bg-slate-50/70">
                        <p class="text-[10.5px] font-bold text-slate-500 uppercase">Rata-rata Skor</p>
                        <p class="text-lg font-extrabold text-[#2563EB] mt-1">{{ $avgScore }} / 100</p>
                    </div>
                </div>

                <div class="space-y-2">
                    <h3 class="text-xs font-bold uppercase tracking-wider text-slate-700">Rekap Kinerja Seluruh Regu Lapangan</h3>
                    <table class="w-full text-[11.5px] border border-slate-200 rounded-xl overflow-hidden">
                        <thead class="bg-slate-50 text-slate-600">
                            <tr>
                                <th class="text-left px-3 py-2 font-bold">Regu Lapangan</th>
                                <th class="text-center px-3 py-2 font-bold">Tuntas / Total</th>
                                <th class="text-center px-3 py-2 font-bold">Kuota Aktif</th>
                                <th class="text-center px-3 py-2 font-bold">Respon / Perbaikan</th>
                                <th class="text-center px-3 py-2 font-bold">Lolos 1x</th>
                                <th class="text-center px-3 py-2 font-bold">Rating</th>
                                <th class="text-right px-3 py-2 font-bold">Skor</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200">
                            @forelse($allTeamMetrics as $tm)
                            <tr>
                                <td class="px-3 py-2">
                                    <div class="font-bold text-slate-900">{{ $tm['team_name'] }}</div>
                                    <div class="text-[10.5px] text-slate-500">{{ $tm['team_code'] }} &bull; Ketua: {{ $tm['leader_name'] }} ({{ $tm['members_count'] }} org)</div>
                                </td>
                                <td class="px-3 py-2 text-center font-bold text-slate-900">
                                    {{ $tm['completed'] }}/{{ $tm['total_tasks'] }}
                                    <span class="block text-[10px] font-normal text-emerald-700">{{ $tm['completion_rate'] }}%</span>
                                </td>
                                <td class="px-3 py-2 text-center font-semibold text-[#2563EB]">{{ $tm['active_quota'] }}/3</td>
                                <td class="px-3 py-2 text-center text-slate-700">
                                    {{ $tm['avg_response_hours'] !== null ? $tm['avg_response_hours'] . 'j' : '-' }} /
                                    {{ $tm['avg_repair_hours'] !== null ? $tm['avg_repair_hours'] . 'j' : '-' }}
                                </td>
                                <td class="px-3 py-2 text-center font-semibold text-slate-900">{{ $tm['first_pass_rate'] }}%</td>
                                <td class="px-3 py-2 text-center font-bold text-amber-600">{{ $tm['avg_rating'] !== null ? $tm['avg_rating'] . '/5' : '-' }}</td>
                                <td class="px-3 py-2 text-right font-extrabold text-[#2563EB]">{{ $tm['score'] }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="7" class="px-3 py-6 text-center text-slate-400">Belum ada data regu lapangan tercatat.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            @else
                @php
                    $totalOfficers = count($allOfficerMetrics);
                    $sumTasks = array_sum(array_column($allOfficerMetrics, 'total_tasks'));
                    $sumCompleted = array_sum(array_column($allOfficerMetrics, 'completed'));
                    $avgScore = $totalOfficers > 0 ? round(array_sum(array_column($allOfficerMetrics, 'score')) / $totalOfficers, 1) : 0;
                @endphp
                <div class="grid grid-cols-4 gap-3 text-center">
                    <div class="border border-slate-200 rounded-xl p-3 bg-slate-50/70">
                        <p class="text-[10.5px] font-bold text-slate-500 uppercase">Total Petugas</p>
                        <p class="text-lg font-extrabold text-slate-900 mt-1">{{ $totalOfficers }}</p>
                    </div>
                    <div class="border border-slate-200 rounded-xl p-3 bg-slate-50/70">
                        <p class="text-[10.5px] font-bold text-slate-500 uppercase">Total Tugas</p>
                        <p class="text-lg font-extrabold text-slate-900 mt-1">{{ $sumTasks }}</p>
                    </div>
                    <div class="border border-slate-200 rounded-xl p-3 bg-slate-50/70">
                        <p class="text-[10.5px] font-bold text-slate-500 uppercase">Tugas Tuntas</p>
                        <p class="text-lg font-extrabold text-emerald-700 mt-1">{{ $sumCompleted }}</p>
                    </div>
                    <div class="border border-slate-200 rounded-xl p-3 bg-slate-50/70">
                        <p class="text-[10.5px] font-bold text-slate-500 uppercase">Rata-rata Skor</p>
                        <p class="text-lg font-extrabold text-[#2563EB] mt-1">{{ $avgScore }} / 100</p>
                    </div>
                </div>

                <div class="space-y-2">
                    <h3 class="text-xs font-bold uppercase tracking-wider text-slate-700">Rekap Kinerja Seluruh Pekerja Lapangan (Per Individu)</h3>
                    <table class="w-full text-[11.5px] border border-slate-200 rounded-xl overflow-hidden">
                        <thead class="bg-slate-50 text-slate-600">
                            <tr>
                                <th class="text-left px-3 py-2 font-bold">Nama Petugas</th>
                                <th class="text-left px-3 py-2 font-bold">Regu &amp; Peran</th>
                                <th class="text-center px-3 py-2 font-bold">Tuntas / Total</th>
                                <th class="text-center px-3 py-2 font-bold">Rata-rata Perbaikan</th>
                                <th class="text-center px-3 py-2 font-bold">Revisi</th>
                                <th class="text-center px-3 py-2 font-bold">Rating</th>
                                <th class="text-right px-3 py-2 font-bold">Skor</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200">
                            @forelse($allOfficerMetrics as $of)
                            <tr>
                                <td class="px-3 py-2">
                                    <div class="font-bold text-slate-900">{{ $of['name'] }}</div>
                                    <div class="text-[10.5px] text-slate-500">{{ $of['email'] }}</div>
                                </td>
                                <td class="px-3 py-2">
                                    <div class="font-semibold text-slate-900">{{ $of['team_name'] }}</div>
                                    <div class="text-[10.5px] text-slate-500">{{ $of['team_role'] }}</div>
                                </td>
                                <td class="px-3 py-2 text-center font-bold text-slate-900">{{ $of['completed'] }}/{{ $of['total_tasks'] }}</td>
                                <td class="px-3 py-2 text-center text-slate-700">{{ $of['avg_repair_hours'] !== null ? $of['avg_repair_hours'] . ' jam' : '-' }}</td>
                                <td class="px-3 py-2 text-center font-semibold text-amber-700">{{ $of['revision_count'] }}</td>
                                <td class="px-3 py-2 text-center font-bold text-amber-600">{{ $of['avg_rating'] !== null ? $of['avg_rating'] . '/5' : '-' }}</td>
                                <td class="px-3 py-2 text-right font-extrabold text-[#2563EB]">{{ $of['score'] }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="7" class="px-3 py-6 text-center text-slate-400">Belum ada data pekerja lapangan tercatat.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            @endif
        @else
        <!-- Identitas -->
        <table class="w-full text-xs border border-slate-200 rounded-xl overflow-hidden">
            <tbody class="divide-y divide-slate-200">
                @if($mode === 'team' && $teamMetric)
                <tr>
                    <td class="bg-slate-50 px-3.5 py-2.5 font-bold text-slate-600 w-1/3">Nama &amp; Kode Regu</td>
                    <td class="px-3.5 py-2.5 font-bold text-slate-900">{{ $teamMetric['team_name'] }} ({{ $teamMetric['team_code'] }})</td>
                </tr>
                <tr>
                    <td class="bg-slate-50 px-3.5 py-2.5 font-bold text-slate-600">Ketua Regu &amp; Anggota</td>
                    <td class="px-3.5 py-2.5 text-slate-800">{{ $teamMetric['leader_name'] }} &bull; {{ $teamMetric['members_count'] }} Personel Aktif</td>
                </tr>
                <tr>
                    <td class="bg-slate-50 px-3.5 py-2.5 font-bold text-slate-600">Wilayah Tugas</td>
                    <td class="px-3.5 py-2.5 text-slate-800">{{ $teamMetric['district'] }}</td>
                </tr>
                @elseif($individualMetric)
                <tr>
                    <td class="bg-slate-50 px-3.5 py-2.5 font-bold text-slate-600 w-1/3">Nama Petugas</td>
                    <td class="px-3.5 py-2.5 font-bold text-slate-900">{{ $individualMetric['name'] }}</td>
                </tr>
                <tr>
                    <td class="bg-slate-50 px-3.5 py-2.5 font-bold text-slate-600">Email &amp; Telepon</td>
                    <td class="px-3.5 py-2.5 text-slate-800">{{ $individualMetric['email'] }} &bull; {{ $individualMetric['phone'] }}</td>
                </tr>
                <tr>
                    <td class="bg-slate-50 px-3.5 py-2.5 font-bold text-slate-600">Regu &amp; Jabatan</td>
                    <td class="px-3.5 py-2.5 text-slate-800">{{ $individualMetric['team_name'] }} ({{ $individualMetric['team_role'] }})</td>
                </tr>
                @endif
            </tbody>
        </table>

        <!-- Ringkasan Indikator Kinerja -->
        @php $m = $mode === 'team' ? $teamMetric : $individualMetric; @endphp
        @if($m)
        <div class="grid grid-cols-4 gap-3 text-center">
            <div class="border border-slate-200 rounded-xl p-3 bg-slate-50/70">
                <p class="text-[10.5px] font-bold text-slate-500 uppercase">Total / Tuntas</p>
                <p class="text-lg font-extrabold text-slate-900 mt-1">{{ $m['completed'] }} / {{ $m['total_tasks'] }}</p>
            </div>
            <div class="border border-slate-200 rounded-xl p-3 bg-slate-50/70">
                <p class="text-[10.5px] font-bold text-slate-500 uppercase">Skor Kinerja</p>
                <p class="text-lg font-extrabold text-[#2563EB] mt-1">{{ $m['score'] }} / 100</p>
            </div>
            <div class="border border-slate-200 rounded-xl p-3 bg-slate-50/70">
                <p class="text-[10.5px] font-bold text-slate-500 uppercase">Rata-rata Perbaikan</p>
                <p class="text-lg font-extrabold text-slate-900 mt-1">{{ $m['avg_repair_hours'] !== null ? $m['avg_repair_hours'] . ' jam' : '-' }}</p>
            </div>
            <div class="border border-slate-200 rounded-xl p-3 bg-slate-50/70">
                <p class="text-[10.5px] font-bold text-slate-500 uppercase">Rating Warga</p>
                <p class="text-lg font-extrabold text-amber-600 mt-1">{{ $m['avg_rating'] !== null ? $m['avg_rating'] . ' / 5' : '-' }}</p>
            </div>
        </div>
        @endif

        <!-- Tabel Rincian Pekerjaan -->
        <div class="space-y-2">
            <h3 class="text-xs font-bold uppercase tracking-wider text-slate-700">Rincian Penanganan Pekerjaan Lapangan</h3>
            <table class="w-full text-[11.5px] border border-slate-200 rounded-xl overflow-hidden">
                <thead class="bg-slate-50 text-slate-600">
                    <tr>
                        <th class="text-left px-3 py-2 font-bold">Kode</th>
                        <th class="text-left px-3 py-2 font-bold">Judul &amp; Lokasi</th>
                        <th class="text-left px-3 py-2 font-bold">Kategori</th>
                        <th class="text-center px-3 py-2 font-bold">Status</th>
                        <th class="text-center px-3 py-2 font-bold">Rating</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200">
                    @forelse($tasks as $t)
                    <tr>
                        <td class="px-3 py-2 font-mono font-bold text-slate-900 whitespace-nowrap">#{{ $t->public_code }}</td>
                        <td class="px-3 py-2">
                            <div class="font-bold text-slate-900">{{ $t->title }}</div>
                            <div class="text-[10.5px] text-slate-500">{{ $t->address }}</div>
                        </td>
                        <td class="px-3 py-2 text-slate-700">{{ $t->category?->name ?? 'Umum' }}</td>
                        <td class="px-3 py-2 text-center font-bold text-slate-800 whitespace-nowrap">
                            {{ \App\Services\PerformanceMetricsService::statusLabelId($t->status) }}
                        </td>
                        <td class="px-3 py-2 text-center font-bold text-amber-600">
                            {{ $t->rating ? $t->rating->rating . '/5' : '-' }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-3 py-6 text-center text-slate-400">Belum ada data pekerjaan tercatat.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @endif

        <!-- Blok Tanda Tangan -->
        <div class="grid grid-cols-2 gap-8 pt-6 text-center text-xs">
            <div class="space-y-14">
                <div>
                    <p class="text-slate-500">Mengetahui,</p>
                    <p class="font-bold text-slate-900">Operator Verifikasi Dinas PUPR</p>
                </div>
                <div class="border-t border-slate-400 pt-1 mx-6">
                    <p class="font-bold text-slate-800">Dinas PUPR Kabupaten</p>
                </div>
            </div>
            <div class="space-y-14">
                <div>
                    <p class="text-slate-500">Lasusua, {{ now('Asia/Makassar')->translatedFormat('d F Y') }}</p>
                    <p class="font-bold text-slate-900">
                        @if(!empty($isAll))
                            Penanggung Jawab Rekapitulasi
                        @else
                            {{ $mode === 'team' ? 'Ketua Regu Lapangan' : 'Pekerja Lapangan' }}
                        @endif
                    </p>
                </div>
                <div class="border-t border-slate-400 pt-1 mx-6">
                    <p class="font-bold text-slate-800">
                        @if(!empty($isAll))
                            {{ auth()->user()?->name ?? 'Dinas PUPR Kabupaten' }}
                        @else
                            {{ $mode === 'team' ? ($teamMetric['leader_name'] ?? '-') : ($individualMetric['name'] ?? '-') }}
                        @endif
                    </p>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
