<!-- Modul Monitoring Eksekutif & Pelaporan Resmi -->

<!-- 1. TOOLBAR AKSI LAPORAN RESMI (PDF) -->
<div class="card-white rounded-2xl p-4 sm:p-5 bg-gradient-to-r from-[#172554] via-[#1E3A8A] to-[#1E40AF] text-white shadow-sm flex flex-col md:flex-row md:items-center justify-between gap-4">
    <div class="space-y-1">
        <div class="inline-flex items-center gap-2 px-2.5 py-0.5 rounded-full bg-white/15 text-blue-100 border border-white/20 text-[10.5px] font-semibold backdrop-blur-xs">
            <i data-lucide="file-check-2" class="w-3.5 h-3.5 text-blue-200"></i>
            <span>Dokumen Rekapitulasi Ringkas Eksekutif</span>
        </div>
        <h3 class="font-outfit font-extrabold text-base sm:text-lg text-white tracking-tight">
            Pelaporan &amp; Evaluasi Kinerja Penanganan Infrastruktur
        </h3>
        <p class="text-xs text-blue-100/80 max-w-2xl">
            Cetak ringkasan rekapitulasi data penting, total laporan, serta evaluasi kinerja SLA dinas secara resmi.
        </p>
    </div>

    <!-- Tombol Aksi Dokumen -->
    <div class="flex flex-wrap items-center gap-2.5 shrink-0">
        <!-- 1. Pratinjau Dokumen -->
        <button type="button" onclick="openReportPreviewModal()" 
            class="px-3.5 py-2 rounded-xl text-xs font-bold bg-white/10 hover:bg-white/20 text-white border border-white/25 transition shadow-2xs flex items-center gap-2 cursor-pointer">
            <i data-lucide="eye" class="w-4 h-4 text-blue-200"></i>
            <span>Pratinjau Dokumen</span>
        </button>

        <!-- 2. Cetak Dokumen PDF -->
        <button type="button" onclick="printOfficialReportPdf()" 
            class="px-3.5 py-2 rounded-xl text-xs font-bold bg-white text-[#1E3A8A] hover:bg-blue-50 transition shadow-2xs flex items-center gap-2 cursor-pointer">
            <i data-lucide="printer" class="w-4 h-4 text-[#1E3A8A]"></i>
            <span>Cetak PDF</span>
        </button>
    </div>
</div>

<!-- 2. BARIS 1: KINERJA KETEPATAN WAKTU OPD & SEBARAN MASALAH PER KATEGORI -->
<div class="grid grid-cols-1 lg:grid-cols-12 gap-5">
    <!-- 1. KINERJA OPD (7 Kolom) -->
    <div class="lg:col-span-7 card-white rounded-2xl p-5 bg-white border border-[#E2E8F0] flex flex-col justify-between space-y-4">
        <div class="space-y-3">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 border-b border-slate-100 pb-3">
                <div>
                    <h3 class="font-outfit font-bold text-sm lg:text-base text-slate-900 flex items-center gap-2">
                        <i data-lucide="briefcase" class="w-4 h-4 text-[#1E3A8A]"></i>
                        <span>Kinerja Ketepatan Waktu Penanganan Dinas (OPD)</span>
                    </h3>
                    <p class="text-xs text-slate-500 mt-0.5">Komparasi penanganan fisik yang tuntas tepat waktu vs tidak tepat waktu (terlambat).</p>
                </div>
            </div>

            <!-- Canvas Chart.js OPD Bar Chart -->
            <div class="relative h-64 sm:h-72 w-full">
                <canvas id="opdPerformanceChart"></canvas>
            </div>
        </div>

        <!-- Ringkasan Angka Metrik di Bawah Grafik (Neutral Slate Bersih Seperti Gambar 4) -->
        @php
            $totalOnTime = $departments->sum('completed_tasks');
            $totalLate = $departments->sum(fn($d) => max(0, ($d->total_tasks ?? 0) - ($d->completed_tasks ?? 0)));
            $totalAssigned = $departments->sum('total_tasks');
        @endphp
        <div class="grid grid-cols-2 gap-3 pt-2.5 border-t border-slate-100">
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
        </div>
    </div>

    <!-- 2. SEBARAN KATEGORI - SERBA BIRU & RINGKAS (5 Kolom) -->
    <div class="lg:col-span-5 card-white rounded-2xl p-5 bg-white border border-[#E2E8F0] flex flex-col justify-between space-y-4">
        <div class="space-y-3">
            <div class="border-b border-slate-100 pb-3">
                <h3 class="font-outfit font-bold text-sm lg:text-base text-slate-900 flex items-center gap-2">
                    <i data-lucide="pie-chart" class="w-4 h-4 text-[#1E3A8A]"></i>
                    <span>Sebaran Masalah per Kategori Infrastruktur</span>
                </h3>
                <p class="text-xs text-slate-500 mt-0.5">Distribusi pengaduan infrastruktur berdasarkan jenis.</p>
            </div>

            <!-- Canvas Chart.js Doughnut Serba Biru -->
            <div class="relative h-60 w-full flex items-center justify-center">
                <canvas id="categoryDistributionChart"></canvas>
            </div>
        </div>

        <!-- List Legend Kategori Rapi & Ber-spasi Luwes (Badge Khusus) -->
        @php
            $bluePalette = ['#1E3A8A', '#2563EB', '#3B82F6', '#60A5FA', '#93C5FD', '#BFDBFE'];
        @endphp
        <div class="space-y-2 pt-2 border-t border-slate-100 max-h-48 overflow-y-auto">
            @foreach($categories as $cat)
                @php
                    $catColor = $bluePalette[$loop->index % count($bluePalette)];
                @endphp
                <div class="flex items-center justify-between py-2.5 px-3 rounded-xl bg-slate-50/90 hover:bg-blue-50/50 border border-slate-200/80 transition-all">
                    <div class="flex items-center gap-2.5 min-w-0 pr-3">
                        <span class="w-3 h-3 rounded-full shrink-0 shadow-2xs" style="background-color: {{ $catColor }};"></span>
                        <span class="font-semibold text-slate-800 text-xs truncate">{{ $cat->name }}</span>
                    </div>
                    <div class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-white border border-slate-200/90 shadow-2xs shrink-0">
                        <span class="font-outfit font-extrabold text-xs text-slate-900">{{ $cat->complaints_count }}</span>
                        <span class="text-[11px] font-medium text-slate-500">Laporan</span>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>

<!-- 3. BARIS 2: STATUS SIKLUS PENANGANAN & PERSENTASE KINERJA PENANGANAN TUGAS (LINGKARAN) -->
<div class="grid grid-cols-1 lg:grid-cols-12 gap-5">
    <!-- 1. STATUS PROGRES SIKLUS PENANGANAN (7 Kolom) -->
    <div class="lg:col-span-7 card-white rounded-2xl p-5 bg-white border border-[#E2E8F0] shadow-2xs flex flex-col justify-between space-y-4">
        <div class="space-y-3">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-slate-100 pb-3">
                <div>
                    <h3 class="font-outfit font-extrabold text-sm sm:text-base text-slate-900 leading-tight">
                        Status Progres Siklus Penanganan Pengaduan
                    </h3>
                    <p class="text-xs text-slate-500 mt-0.5">
                        Distribusi komparasi total laporan masuk, tahap verifikasi, penugasan, pengerjaan, hingga tuntas dan ditolak.
                    </p>
                </div>
                <div class="shrink-0">
                    <span class="px-2.5 py-1 rounded-full text-[11px] font-bold bg-blue-50 text-[#1E3A8A] border border-blue-200">
                        Total: {{ $summary['total'] }} Laporan
                    </span>
                </div>
            </div>

            <!-- Canvas Chart.js Status Bar Chart -->
            <div class="relative h-60 sm:h-64 w-full">
                <canvas id="complaintStatusChart"></canvas>
            </div>
        </div>

        <!-- Mini Summary 6 Status Card Grid (Neutral Slate Sesuai Gambar 4) -->
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-2 pt-2 border-t border-slate-100">
            <!-- 1. Total Laporan -->
            <div class="p-2.5 rounded-xl bg-slate-50 border border-slate-200/80 flex flex-col justify-between">
                <span class="text-[9.5px] font-bold text-slate-500 uppercase tracking-wider">Total Laporan</span>
                <div class="flex items-baseline justify-between mt-1">
                    <span class="font-outfit font-extrabold text-lg text-slate-900">{{ $summary['total'] }}</span>
                    <span class="text-[9.5px] font-bold text-slate-400">100%</span>
                </div>
            </div>
            <!-- 2. Verifikasi -->
            <div class="p-2.5 rounded-xl bg-slate-50 border border-slate-200/80 flex flex-col justify-between">
                <span class="text-[9.5px] font-bold text-slate-500 uppercase tracking-wider">Verifikasi</span>
                <div class="flex items-baseline justify-between mt-1">
                    <span class="font-outfit font-extrabold text-lg text-slate-900">{{ $summary['pending'] }}</span>
                    <span class="text-[9.5px] font-bold text-slate-400">{{ $summary['total'] > 0 ? round(($summary['pending'] / $summary['total']) * 100) : 0 }}%</span>
                </div>
            </div>
            <!-- 3. Ditugaskan -->
            <div class="p-2.5 rounded-xl bg-slate-50 border border-slate-200/80 flex flex-col justify-between">
                <span class="text-[9.5px] font-bold text-slate-500 uppercase tracking-wider">Ditugaskan</span>
                <div class="flex items-baseline justify-between mt-1">
                    <span class="font-outfit font-extrabold text-lg text-slate-900">{{ $summary['dispatched'] }}</span>
                    <span class="text-[9.5px] font-bold text-slate-400">{{ $summary['total'] > 0 ? round(($summary['dispatched'] / $summary['total']) * 100) : 0 }}%</span>
                </div>
            </div>
            <!-- 4. Dikerjakan -->
            <div class="p-2.5 rounded-xl bg-slate-50 border border-slate-200/80 flex flex-col justify-between">
                <span class="text-[9.5px] font-bold text-slate-500 uppercase tracking-wider">Dikerjakan</span>
                <div class="flex items-baseline justify-between mt-1">
                    <span class="font-outfit font-extrabold text-lg text-slate-900">{{ $summary['in_progress'] }}</span>
                    <span class="text-[9.5px] font-bold text-slate-400">{{ $summary['total'] > 0 ? round(($summary['in_progress'] / $summary['total']) * 100) : 0 }}%</span>
                </div>
            </div>
            <!-- 5. Selesai -->
            <div class="p-2.5 rounded-xl bg-slate-50 border border-slate-200/80 flex flex-col justify-between">
                <span class="text-[9.5px] font-bold text-slate-500 uppercase tracking-wider">Selesai</span>
                <div class="flex items-baseline justify-between mt-1">
                    <span class="font-outfit font-extrabold text-lg text-slate-900">{{ $summary['completed'] }}</span>
                    <span class="text-[9.5px] font-bold text-slate-400">{{ $summary['completion_rate'] }}%</span>
                </div>
            </div>
            <!-- 6. Ditolak -->
            <div class="p-2.5 rounded-xl bg-slate-50 border border-slate-200/80 flex flex-col justify-between">
                <span class="text-[9.5px] font-bold text-slate-500 uppercase tracking-wider">Ditolak</span>
                <div class="flex items-baseline justify-between mt-1">
                    <span class="font-outfit font-extrabold text-lg text-slate-900">{{ $summary['rejected'] ?? 0 }}</span>
                    <span class="text-[9.5px] font-bold text-slate-400">{{ ($summary['total'] ?? 0) > 0 ? round((($summary['rejected'] ?? 0) / $summary['total']) * 100) : 0 }}%</span>
                </div>
            </div>
        </div>
    </div>

    <!-- 2. GRAFIK LINGKARAN PERSENTASE KINERJA PENANGANAN TUGAS (5 Kolom) -->
    @php
        $fieldCount = max(0, ($summary['in_progress'] ?? 0) + ($summary['completed'] ?? 0));
        $operatorCount = max(0, ($summary['pending'] ?? 0) + ($summary['waiting_verification'] ?? 0));
        $deptCount = max(0, $totalOnTime);
        $totalWorkload = max(1, $fieldCount + $operatorCount + $deptCount);
        $fieldPercent = round(($fieldCount / $totalWorkload) * 100);
        $operatorPercent = round(($operatorCount / $totalWorkload) * 100);
        $deptPercent = max(0, 100 - $fieldPercent - $operatorPercent);
    @endphp
    <div class="lg:col-span-5 card-white rounded-2xl p-5 bg-white border border-[#E2E8F0] shadow-2xs flex flex-col justify-between space-y-4">
        <div class="space-y-3">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <div>
                    <h3 class="font-outfit font-extrabold text-sm sm:text-base text-slate-900 flex items-center gap-2">
                        <i data-lucide="pie-chart" class="w-4 h-4 text-[#1E3A8A]"></i>
                        <span>Persentase Kinerja Penanganan Tugas</span>
                    </h3>
                    <p class="text-xs text-slate-500 mt-0.5">Komparasi total aktivitas Petugas Lapangan, Operator, dan Dinas.</p>
                </div>
            </div>

            <!-- Canvas Chart.js Doughnut Kinerja Peran -->
            <div class="relative h-60 w-full flex items-center justify-center">
                <canvas id="stakeholderPerformanceChart"></canvas>
            </div>
        </div>

        <!-- 3 Legend Box Bersih & Netral (Sesuai Gambar 4: bg-slate-50, text-slate-900, tanpa warna-warni) -->
        <div class="grid grid-cols-3 gap-2 pt-2 border-t border-slate-100">
            <!-- 1. Petugas Lapangan -->
            <div class="p-2.5 rounded-xl bg-slate-50 border border-slate-200/80 text-center">
                <div class="flex items-center justify-center gap-1.5 mb-1">
                    <span class="w-2 h-2 rounded-full bg-[#3B82F6]"></span>
                    <span class="text-[10px] font-bold text-slate-600 uppercase">Petugas</span>
                </div>
                <span class="font-outfit font-extrabold text-lg text-slate-900 block">{{ $fieldPercent }}%</span>
                <span class="text-[10px] text-slate-400 font-medium">{{ $fieldCount }} Tindakan</span>
            </div>

            <!-- 2. Operator -->
            <div class="p-2.5 rounded-xl bg-slate-50 border border-slate-200/80 text-center">
                <div class="flex items-center justify-center gap-1.5 mb-1">
                    <span class="w-2 h-2 rounded-full bg-[#1E3A8A]"></span>
                    <span class="text-[10px] font-bold text-slate-600 uppercase">Operator</span>
                </div>
                <span class="font-outfit font-extrabold text-lg text-slate-900 block">{{ $operatorPercent }}%</span>
                <span class="text-[10px] text-slate-400 font-medium">{{ $operatorCount }} Telaah</span>
            </div>

            <!-- 3. Dinas PUPR -->
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

<!-- Modal Pratinjau Laporan Resmi -->
<div id="officialReportModal" class="hidden fixed inset-0 z-50 overflow-y-auto bg-slate-900/65 flex items-center justify-center p-3 sm:p-5 overscroll-contain">
    <div class="card-white rounded-2xl bg-white border border-slate-200 shadow-2xl max-w-3xl w-full max-h-[92vh] overflow-hidden flex flex-col relative animate-in fade-in zoom-in duration-150">
        
        <!-- Header Modal Preview (Selaras dengan Modal Gambar 4) -->
        <div class="px-4 sm:px-5 py-3 sm:py-3.5 border-b border-slate-100 flex items-center justify-between bg-slate-50/80 gap-2 shrink-0 rounded-t-2xl">
            <div class="flex items-center gap-2.5">
                <div class="w-8 h-8 rounded-lg bg-[#1E3A8A] text-white flex items-center justify-center font-bold text-xs shadow-2xs shrink-0">
                    <i data-lucide="file-text" class="w-4 h-4"></i>
                </div>
                <div>
                    <h4 class="font-outfit font-extrabold text-sm text-slate-900">
                        Pratinjau Dokumen Rekapitulasi
                    </h4>
                    <p class="text-[11px] text-slate-500">
                        Format resmi hitam putih &bull; Kabupaten Kolaka Utara
                    </p>
                </div>
            </div>
            
            <div class="flex items-center gap-2">
                <button type="button" onclick="printOfficialReportPdf()" 
                    class="px-3.5 py-1.5 rounded-xl bg-[#1E3A8A] hover:bg-[#172554] text-white text-xs font-bold transition flex items-center gap-1.5 shadow-2xs cursor-pointer">
                    <i data-lucide="printer" class="w-3.5 h-3.5"></i>
                    <span>Cetak PDF</span>
                </button>
                <button type="button" onclick="closeReportPreviewModal()" 
                    class="w-8 h-8 rounded-full bg-slate-200/70 hover:bg-slate-300 text-slate-700 flex items-center justify-center transition cursor-pointer shrink-0 ml-1"
                    title="Tutup">
                    <i data-lucide="x" class="w-4 h-4"></i>
                </button>
            </div>
        </div>

        <!-- Body Pratinjau Dokumen: Bersih Putih Utuh Tanpa Faux-Paper Bersarang (Menghilangkan Garis Potong & Bayangan Terpotong) -->
        <div id="reportModalScrollArea" class="flex-1 overflow-y-auto bg-white p-5 sm:p-8">
            <div id="printableOfficialDocument" class="w-full text-black font-sans text-xs space-y-5 pb-6">
                
                <!-- HEADER DOKUMEN RESMI (HITAM PUTIH) -->
                <div class="border-b-2 border-black pb-3 flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                    <div>
                        <h2 class="font-outfit font-black text-base sm:text-lg text-black tracking-tight uppercase leading-snug">
                            REKAPITULASI PENANGANAN PENGADUAN INFRASTRUKTUR
                        </h2>
                        <p class="text-xs text-slate-700 font-semibold mt-0.5">
                            Kabupaten Kolaka Utara &bull; Periode: {{ now()->translatedFormat('F Y') }}
                        </p>
                    </div>
                    <div class="sm:text-right text-[11px] text-slate-700 shrink-0">
                        <span class="text-slate-500">Waktu Rekapitulasi:</span>
                        <strong class="block text-black font-mono font-bold">{{ now()->translatedFormat('d F Y, H:i') }} WITA</strong>
                    </div>
                </div>

                <!-- I. TOTAL LAPORAN & SIKLUS PENANGANAN -->
                <div class="space-y-2">
                    <h3 class="font-outfit font-bold text-xs uppercase tracking-wider text-black border-b border-slate-300 pb-1">
                        I. Total Laporan &amp; Siklus Penanganan
                    </h3>
                    <table class="w-full text-left border-collapse border border-slate-300 text-[11px]">
                        <thead class="bg-slate-100 text-black font-bold uppercase text-[10px]">
                            <tr>
                                <th class="border border-slate-300 py-2 px-3">Total Laporan</th>
                                <th class="border border-slate-300 py-2 px-2 text-center">Verifikasi</th>
                                <th class="border border-slate-300 py-2 px-2 text-center">Ditugaskan</th>
                                <th class="border border-slate-300 py-2 px-2 text-center">Dikerjakan</th>
                                <th class="border border-slate-300 py-2 px-2 text-center">Selesai (Tuntas)</th>
                                <th class="border border-slate-300 py-2 px-2 text-center">Ditolak</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td class="border border-slate-300 py-2.5 px-3 font-bold text-black text-xs">
                                    {{ $summary['total'] }} Laporan
                                </td>
                                <td class="border border-slate-300 py-2.5 px-2 text-center font-bold text-black">{{ $summary['pending'] }}</td>
                                <td class="border border-slate-300 py-2.5 px-2 text-center font-bold text-black">{{ $summary['dispatched'] }}</td>
                                <td class="border border-slate-300 py-2.5 px-2 text-center font-bold text-black">{{ $summary['in_progress'] }}</td>
                                <td class="border border-slate-300 py-2.5 px-2 text-center font-bold text-black">
                                    {{ $summary['completed'] }} ({{ $summary['completion_rate'] }}%)
                                </td>
                                <td class="border border-slate-300 py-2.5 px-2 text-center font-bold text-black">{{ $summary['rejected'] ?? 0 }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- II. EVALUASI KETEPATAN WAKTU PENANGANAN DINAS (SLA) -->
                <div class="space-y-2">
                    <h3 class="font-outfit font-bold text-xs uppercase tracking-wider text-black border-b border-slate-300 pb-1">
                        II. Evaluasi Ketepatan Waktu Penanganan Dinas (SLA)
                    </h3>
                    <div class="grid grid-cols-3 gap-3">
                        <div class="p-3 rounded-lg border border-slate-300 bg-slate-50 text-center">
                            <span class="text-[10px] font-bold text-slate-700 uppercase block tracking-wider">Tepat Waktu (SLA)</span>
                            <span class="font-outfit font-extrabold text-xl text-black block mt-0.5">{{ $totalOnTime }}</span>
                            <span class="text-[10px] text-slate-500 font-medium">Laporan Tuntas</span>
                        </div>
                        <div class="p-3 rounded-lg border border-slate-300 bg-slate-50 text-center">
                            <span class="text-[10px] font-bold text-slate-700 uppercase block tracking-wider">Terlambat</span>
                            <span class="font-outfit font-extrabold text-xl text-black block mt-0.5">{{ $totalLate }}</span>
                            <span class="text-[10px] text-slate-500 font-medium">Melewati Batas SLA</span>
                        </div>
                        <div class="p-3 rounded-lg border border-slate-300 bg-slate-50 text-center">
                            <span class="text-[10px] font-bold text-slate-700 uppercase block tracking-wider">Rasio Ketepatan</span>
                            <span class="font-outfit font-extrabold text-xl text-black block mt-0.5">
                                {{ $totalAssigned > 0 ? round(($totalOnTime / $totalAssigned) * 100) : 0 }}%
                            </span>
                            <span class="text-[10px] text-slate-500 font-medium">Dinas PUPR</span>
                        </div>
                    </div>
                </div>

                <!-- III. REKAPITULASI PER KATEGORI INFRASTRUKTUR -->
                <div class="space-y-2">
                    <h3 class="font-outfit font-bold text-xs uppercase tracking-wider text-black border-b border-slate-300 pb-1">
                        III. Rekapitulasi per Kategori Infrastruktur
                    </h3>
                    <table class="w-full text-left border-collapse border border-slate-300 text-[11px]">
                        <thead class="bg-slate-100 text-black font-bold uppercase text-[10px]">
                            <tr>
                                <th class="border border-slate-300 py-2 px-3">Kategori Kerusakan</th>
                                <th class="border border-slate-300 py-2 px-3 text-center">Jumlah Laporan</th>
                                <th class="border border-slate-300 py-2 px-3 text-center">Persentase</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($categories as $cat)
                                @php
                                    $p = $summary['total'] > 0 ? round(($cat->complaints_count / $summary['total']) * 100) : 0;
                                @endphp
                                <tr class="{{ $loop->even ? 'bg-slate-50' : 'bg-white' }}">
                                    <td class="border border-slate-300 py-2 px-3 font-semibold text-black">{{ $cat->name }}</td>
                                    <td class="border border-slate-300 py-2 px-3 text-center font-bold text-black">{{ $cat->complaints_count }} Laporan</td>
                                    <td class="border border-slate-300 py-2 px-3 text-center font-mono text-slate-700">{{ $p }}%</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- IV. DISTRIBUSI KINERJA PENANGANAN TUGAS (TIDAK AKAN PERNAH TERPOTONG) -->
                <div class="space-y-2">
                    <h3 class="font-outfit font-bold text-xs uppercase tracking-wider text-black border-b border-slate-300 pb-1">
                        IV. Distribusi Kinerja Penanganan Tugas
                    </h3>
                    <div class="grid grid-cols-3 gap-3">
                        <div class="p-3 rounded-lg border border-slate-300 bg-slate-50 text-center">
                            <span class="text-[10px] font-bold text-slate-700 uppercase block tracking-wider">Petugas Lapangan</span>
                            <span class="font-outfit font-extrabold text-xl text-black block mt-0.5">{{ $fieldPercent }}%</span>
                            <span class="text-[10px] text-slate-500 font-medium">{{ $fieldCount }} Tindakan Fisik</span>
                        </div>
                        <div class="p-3 rounded-lg border border-slate-300 bg-slate-50 text-center">
                            <span class="text-[10px] font-bold text-slate-700 uppercase block tracking-wider">Operator</span>
                            <span class="font-outfit font-extrabold text-xl text-black block mt-0.5">{{ $operatorPercent }}%</span>
                            <span class="text-[10px] text-slate-500 font-medium">{{ $operatorCount }} Telaah Laporan</span>
                        </div>
                        <div class="p-3 rounded-lg border border-slate-300 bg-slate-50 text-center">
                            <span class="text-[10px] font-bold text-slate-700 uppercase block tracking-wider">Dinas PUPR</span>
                            <span class="font-outfit font-extrabold text-xl text-black block mt-0.5">{{ $deptPercent }}%</span>
                            <span class="text-[10px] text-slate-500 font-medium">{{ $deptCount }} Penyelesaian</span>
                        </div>
                    </div>
                </div>

                <!-- FOOTER RESMI DOKUMEN (DI DALAM DOKUMEN & RAPI) -->
                <div class="pt-4 mt-4 text-center text-[10.5px] text-slate-500 border-t border-slate-300 space-y-0.5">
                    <p class="font-semibold text-slate-700">Sistem Informasi Pengaduan &amp; Penanganan Kerusakan Infrastruktur Kabupaten Kolaka Utara</p>
                    <p class="text-[10px] text-slate-400">Dokumen rekapitulasi eksekutif resmi dicetak secara otomatis dari sistem pusat kendali.</p>
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
    @page {
        size: A4 portrait;
        margin: 1.5cm;
    }
}
</style>
