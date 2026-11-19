<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Berita Acara Penyelesaian — #{{ $complaint->public_code }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @media print {
            .no-print { display: none !important; }
            body { background: #ffffff !important; }
            .bap-sheet { box-shadow: none !important; border: none !important; margin: 0 !important; padding: 0 !important; }
        }
    </style>
</head>
<body class="bg-slate-100 text-slate-900 font-sans py-6 px-4">
    <div class="max-w-3xl mx-auto mb-4 flex items-center justify-between no-print">
        <button type="button" onclick="window.close(); setTimeout(() => { if (!window.closed) { if (window.history.length > 1) history.back(); else window.location.href = '{{ url('/') }}'; } }, 120);"
            class="px-3.5 py-2 rounded-xl bg-white border border-slate-200 text-xs font-bold text-slate-700 hover:bg-slate-50 cursor-pointer">
            &times; Tutup Tab
        </button>
        <button type="button" onclick="window.print()" class="px-4 py-2 rounded-xl bg-[#2563EB] hover:bg-[#1D4ED8] text-white text-xs font-bold shadow-xs cursor-pointer">
            Cetak / Simpan PDF Berita Acara
        </button>
    </div>

    <div class="bap-sheet max-w-3xl mx-auto bg-white rounded-2xl border border-slate-200 shadow-sm p-8 sm:p-10 space-y-6">
        <!-- Kop Resmi Pemerintah Kabupaten Kolaka Utara -->
        <div class="border-b-2 border-slate-900 pb-4 text-center space-y-1">
            <p class="text-xs font-bold uppercase tracking-widest text-slate-600">Pemerintah Kabupaten Kolaka Utara</p>
            <h1 class="text-lg sm:text-xl font-extrabold uppercase tracking-wide text-slate-900">
                Dinas Pekerjaan Umum dan Penataan Ruang (PUPR)
            </h1>
            <p class="text-[11px] text-slate-500">
                Sistem Informasi Pengaduan &amp; Penanganan Infrastruktur (SI-APPIK) &bull; Lasusua, Sulawesi Tenggara
            </p>
        </div>

        <div class="text-center space-y-1">
            <h2 class="text-base font-extrabold uppercase underline underline-offset-4">Berita Acara Penyelesaian Penanganan Aduan</h2>
            <p class="text-xs font-mono font-bold text-slate-600">Nomor Tiket: #{{ $complaint->public_code }}</p>
        </div>

        <!-- Ringkasan Informasi Tiket -->
        <table class="w-full text-xs border border-slate-200 rounded-xl overflow-hidden">
            <tbody class="divide-y divide-slate-200">
                <tr>
                    <td class="bg-slate-50 px-3.5 py-2.5 font-bold text-slate-600 w-1/3">Judul Laporan</td>
                    <td class="px-3.5 py-2.5 font-semibold text-slate-900">{{ $complaint->title }}</td>
                </tr>
                <tr>
                    <td class="bg-slate-50 px-3.5 py-2.5 font-bold text-slate-600">Kategori &amp; Tipe</td>
                    <td class="px-3.5 py-2.5 text-slate-800">
                        {{ $complaint->category?->name ?? 'Umum' }} &bull;
                        {{ $complaint->damage_type === 'major' ? 'Kerusakan Berat (Ruas Panjang)' : 'Kerusakan Ringan (Titik)' }}
                        @if($complaint->road_length) ({{ $complaint->road_length }}) @endif
                    </td>
                </tr>
                <tr>
                    <td class="bg-slate-50 px-3.5 py-2.5 font-bold text-slate-600">Lokasi &amp; Koordinat</td>
                    <td class="px-3.5 py-2.5 text-slate-800">
                        {{ $complaint->address }}<br>
                        <span class="font-mono text-[11px] text-slate-500">GPS: {{ $complaint->latitude }}, {{ $complaint->longitude }}</span>
                    </td>
                </tr>
                <tr>
                    <td class="bg-slate-50 px-3.5 py-2.5 font-bold text-slate-600">Pelapor</td>
                    <td class="px-3.5 py-2.5 text-slate-800">
                        {{ $complaint->is_anonymous ? 'Warga (Anonim)' : ($complaint->user?->name ?? $complaint->citizen?->name ?? 'Warga') }}
                    </td>
                </tr>
                <tr>
                    <td class="bg-slate-50 px-3.5 py-2.5 font-bold text-slate-600">Regu Pelaksana</td>
                    <td class="px-3.5 py-2.5 font-semibold text-slate-900">
                        {{ $complaint->latestDisposition?->team?->name ?? 'Tim Teknis Dinas PUPR' }}
                        @if($complaint->latestDisposition?->team?->leader)
                            (Ketua: {{ $complaint->latestDisposition->team->leader->name }})
                        @endif
                    </td>
                </tr>
                <tr>
                    <td class="bg-slate-50 px-3.5 py-2.5 font-bold text-slate-600">Status Akhir</td>
                    <td class="px-3.5 py-2.5 font-bold text-emerald-700">{{ \App\Services\PerformanceMetricsService::statusLabelId($complaint->status) }}</td>
                </tr>
                @if($complaint->rating)
                <tr>
                    <td class="bg-slate-50 px-3.5 py-2.5 font-bold text-slate-600">Kepuasan Warga</td>
                    <td class="px-3.5 py-2.5 text-slate-900">
                        <strong>{{ $complaint->rating->rating }} / 5 Bintang</strong>
                        @if($complaint->rating->comment) &mdash; &ldquo;{{ $complaint->rating->comment }}&rdquo; @endif
                    </td>
                </tr>
                @endif
            </tbody>
        </table>

        <!-- Dokumentasi Foto Sebelum & Sesudah -->
        <div class="grid grid-cols-2 gap-4">
            <div class="border border-slate-200 rounded-xl p-3 space-y-2">
                <p class="text-[11px] font-bold uppercase tracking-wider text-slate-600">Foto Sebelum Perbaikan</p>
                @if($beforePhotos->isNotEmpty())
                    @php $bp = $beforePhotos->first(); @endphp
                    <img src="{{ $bp->storage_key ? asset('storage/' . $bp->storage_key) : $bp->url }}"
                         alt="Foto Sebelum" class="w-full h-44 object-cover rounded-lg border border-slate-200">
                @else
                    <div class="h-44 rounded-lg bg-slate-50 flex items-center justify-center text-xs text-slate-400">Tidak ada foto</div>
                @endif
            </div>
            <div class="border border-slate-200 rounded-xl p-3 space-y-2">
                <p class="text-[11px] font-bold uppercase tracking-wider text-emerald-700">Foto Hasil Perbaikan</p>
                @if($afterPhotos->isNotEmpty())
                    @php $ap = $afterPhotos->first(); @endphp
                    <img src="{{ $ap->storage_key ? asset('storage/' . $ap->storage_key) : $ap->url }}"
                         alt="Foto Sesudah" class="w-full h-44 object-cover rounded-lg border border-slate-200">
                @else
                    <div class="h-44 rounded-lg bg-slate-50 flex items-center justify-center text-xs text-slate-400">Belum ada foto hasil</div>
                @endif
            </div>
        </div>

        <!-- Riwayat Penanganan -->
        <div class="space-y-2">
            <h3 class="text-xs font-bold uppercase tracking-wider text-slate-700">Riwayat Waktu Penanganan</h3>
            <table class="w-full text-[11.5px] border border-slate-200 rounded-xl overflow-hidden">
                <thead class="bg-slate-50 text-slate-600">
                    <tr>
                        <th class="text-left px-3 py-2 font-bold">Waktu (WITA)</th>
                        <th class="text-left px-3 py-2 font-bold">Tahap</th>
                        <th class="text-left px-3 py-2 font-bold">Catatan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200">
                    @foreach($complaint->statusHistories->sortBy('created_at') as $h)
                    <tr>
                        <td class="px-3 py-2 font-mono text-slate-600 whitespace-nowrap">{{ $h->created_at?->timezone('Asia/Makassar')->format('d/m/Y H:i') }}</td>
                        <td class="px-3 py-2 font-bold text-slate-900 whitespace-nowrap">{{ \App\Services\PerformanceMetricsService::statusLabelId($h->to_status) }}</td>
                        <td class="px-3 py-2 text-slate-700">{{ $h->note ?? '-' }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Blok Tanda Tangan -->
        <div class="grid grid-cols-2 gap-8 pt-6 text-center text-xs">
            <div class="space-y-14">
                <div>
                    <p class="text-slate-500">Mengetahui,</p>
                    <p class="font-bold text-slate-900">Operator Verifikasi Dinas PUPR</p>
                </div>
                <div class="border-t border-slate-400 pt-1 mx-6">
                    <p class="font-bold text-slate-800">Dinas PUPR Kab. Kolaka Utara</p>
                </div>
            </div>
            <div class="space-y-14">
                <div>
                    <p class="text-slate-500">Lasusua, {{ now('Asia/Makassar')->translatedFormat('d F Y') }}</p>
                    <p class="font-bold text-slate-900">Ketua / Pelaksana Regu Lapangan</p>
                </div>
                <div class="border-t border-slate-400 pt-1 mx-6">
                    <p class="font-bold text-slate-800">{{ $complaint->latestDisposition?->team?->leader?->name ?? $complaint->latestDisposition?->team?->name ?? 'Pekerja Lapangan' }}</p>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
