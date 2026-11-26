<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Pusat Pantau Aduan — SI-APPIK</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@500;600;700;800&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <style>
        /* Layar ini dipasang di ruang kendali dan dibaca dari jarak beberapa meter,
           jadi tema gelap dipilih untuk menekan silau, bukan sekadar gaya. */
        :root {
            --bg: #0B1220;
            --panel: #111C33;
            --garis: #1E2B45;
            --teks: #E6EDF7;
            --redup: #93A4BE;
            --biru: #3B82F6;
            --hijau: #10B981;
            --kuning: #F59E0B;
            --merah: #EF4444;
        }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            background: var(--bg);
            color: var(--teks);
            font-family: 'Inter', system-ui, sans-serif;
            height: 100vh;
            overflow: hidden;
            padding: 20px 26px 16px;
            display: grid;
            grid-template-rows: auto auto minmax(0, 1.7fr) minmax(0, 1fr) auto;
            gap: 14px;
        }
        h1, h2, .angka { font-family: 'Outfit', sans-serif; }

        header { display: flex; align-items: center; justify-content: space-between; gap: 20px; }
        .merek { display: flex; align-items: baseline; gap: 14px; }
        .merek strong { font-family: 'Outfit'; font-size: 26px; letter-spacing: .04em; }
        .merek span { color: var(--redup); font-size: 15px; }
        .kanan-atas { display: flex; align-items: center; gap: 22px; }
        .jam { font-family: 'Outfit'; font-size: 34px; font-weight: 700; letter-spacing: .02em; }
        .tanggal { color: var(--redup); font-size: 14px; text-align: right; }
        .hidup { display: inline-flex; align-items: center; gap: 8px; font-size: 13px; color: var(--redup); }
        .titik { width: 9px; height: 9px; border-radius: 50%; background: var(--hijau); }
        .titik.berdenyut { animation: denyut 2.4s ease-in-out infinite; }
        @keyframes denyut { 0%,100% { opacity: 1; } 50% { opacity: .25; } }

        .kpi { display: grid; grid-template-columns: repeat(4, 1fr); gap: 14px; }
        .kartu {
            background: var(--panel);
            border: 1px solid var(--garis);
            border-radius: 14px;
            padding: 16px 18px;
        }
        .kartu .label { color: var(--redup); font-size: 13px; letter-spacing: .08em; text-transform: uppercase; }
        .kartu .angka { font-size: 56px; font-weight: 700; line-height: 1.05; margin-top: 6px; }
        .kartu .ket { color: var(--redup); font-size: 14px; margin-top: 4px; }
        .kartu.aksen { border-color: rgba(59,130,246,.45); }

        .tengah { display: grid; grid-template-columns: 2fr 1fr; gap: 14px; flex: 1; min-height: 0; }
        .panel { background: var(--panel); border: 1px solid var(--garis); border-radius: 14px; padding: 14px 16px; display: flex; flex-direction: column; min-height: 0; overflow: hidden; }
        .panel h2 { font-size: 17px; font-weight: 600; letter-spacing: .06em; text-transform: uppercase; color: var(--redup); margin-bottom: 10px; }
        #peta { flex: 1; min-height: 300px; border-radius: 10px; overflow: hidden; }

        .baris { display: flex; align-items: center; justify-content: space-between; padding: 7px 0; border-bottom: 1px solid rgba(30,43,69,.7); font-size: 16px; }
        .baris:last-child { border-bottom: 0; }
        .baris .nama { display: flex; align-items: center; gap: 9px; }
        .chip { width: 10px; height: 10px; border-radius: 3px; flex: 0 0 auto; }
        .baris .nilai { font-family: 'Outfit'; font-weight: 700; font-size: 23px; }

        .kecamatan { margin-top: 4px; }
        .kec { display: grid; grid-template-columns: 1fr auto; gap: 4px 10px; padding: 4px 0; font-size: 14.5px; }
        .kec .jalur { grid-column: 1 / -1; height: 6px; border-radius: 4px; background: rgba(148,163,184,.16); overflow: hidden; }
        .kec .isi { height: 100%; background: var(--biru); border-radius: 4px; }

        .bawah { display: grid; grid-template-columns: 1fr 1fr 1.5fr; gap: 14px; min-height: 0; }
        .tren { display: flex; align-items: flex-end; gap: 10px; height: 108px; margin-top: 6px; }
        .tren .kolom { flex: 1; display: flex; flex-direction: column; justify-content: flex-end; align-items: center; gap: 6px; height: 100%; }
        .tren .batang { width: 100%; background: linear-gradient(180deg, var(--biru), rgba(59,130,246,.35)); border-radius: 5px 5px 0 0; min-height: 3px; }
        .tren .tgl { font-size: 13px; color: var(--redup); }

        .daftar { display: flex; flex-direction: column; gap: 7px; min-height: 0; }
        .item { display: grid; grid-template-columns: 150px 1fr auto auto; gap: 12px; align-items: center; font-size: 15.5px; padding: 8px 0; border-bottom: 1px solid rgba(30,43,69,.6); }
        .item:last-child { border-bottom: 0; }
        .item .kode { font-family: 'Outfit'; font-weight: 700; color: var(--biru); }
        .item .judul { color: var(--teks); overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
        .item .waktu { color: var(--redup); font-size: 12px; white-space: nowrap; }
        .status { font-size: 12.5px; font-weight: 700; letter-spacing: .04em; padding: 4px 10px; border-radius: 6px; white-space: nowrap; }

        footer { display: flex; justify-content: space-between; color: var(--redup); font-size: 13px; border-top: 1px solid var(--garis); padding-top: 10px; }

        @media (max-width: 1100px) {
            .kpi { grid-template-columns: repeat(2, 1fr); }
            .tengah, .bawah { grid-template-columns: 1fr; }
            .kartu .angka { font-size: 40px; }
        }
    </style>
</head>
<body>
@php
    $warnaStatus = [
        'SUBMITTED' => '#EF4444', 'VERIFIED' => '#60A5FA', 'DISPATCHED' => '#3B82F6',
        'IN_PROGRESS' => '#F59E0B', 'WAITING_VERIFICATION' => '#F59E0B',
        'REVISION_REQUIRED' => '#F59E0B', 'COMPLETED' => '#10B981', 'CLOSED' => '#10B981',
        'REJECTED' => '#DC2626', 'CANCELLED' => '#64748B',
    ];
    $labelStatus = [
        'SUBMITTED' => 'Menunggu', 'VERIFIED' => 'Terverifikasi', 'DISPATCHED' => 'Ditugaskan',
        'IN_PROGRESS' => 'Dikerjakan', 'WAITING_VERIFICATION' => 'Diperiksa',
        'REVISION_REQUIRED' => 'Revisi', 'COMPLETED' => 'Selesai', 'CLOSED' => 'Ditutup',
        'REJECTED' => 'Ditolak', 'CANCELLED' => 'Dibatalkan',
    ];
@endphp

<header>
    <div class="merek">
        <strong>SI-APPIK</strong>
        <span>Pusat Pantau Aduan Infrastruktur · Kabupaten Kolaka Utara</span>
    </div>
    <div class="kanan-atas">
        <div class="tanggal">
            <div class="jam" id="jam">--:--:--</div>
            <div id="tanggal">-</div>
        </div>
        <span class="hidup"><span class="titik berdenyut"></span> langsung</span>
        {{-- Layar pantau kini di balik login, jadi perlu jalan keluar sesi. --}}
        <form method="POST" action="{{ route('logout') }}" style="margin:0">
            @csrf
            <button type="submit" class="hidup" style="background:none;border:1px solid rgba(255,255,255,.25);color:inherit;font:inherit;cursor:pointer;padding:4px 10px;border-radius:6px">keluar</button>
        </form>
    </div>
</header>

<section class="kpi">
    <div class="kartu aksen">
        <div class="label">Aduan Masuk</div>
        <div class="angka" id="kpi-total">{{ $ringkasan['total'] }}</div>
        <div class="ket">seluruh laporan warga</div>
    </div>
    <div class="kartu">
        <div class="label">Sedang Dikerjakan</div>
        <div class="angka" id="kpi-dikerjakan" style="color: var(--kuning)">{{ $ringkasan['dikerjakan'] }}</div>
        <div class="ket">{{ $ringkasan['ditugaskan'] }} menunggu diambil regu</div>
    </div>
    <div class="kartu">
        <div class="label">Selesai</div>
        <div class="angka" id="kpi-selesai" style="color: var(--hijau)">{{ $ringkasan['selesai'] }}</div>
        <div class="ket">diperiksa AI dari foto radius</div>
    </div>
    <div class="kartu">
        <div class="label">Tingkat Tuntas</div>
        <div class="angka" id="kpi-rasio">{{ $ringkasan['rasio'] }}<span style="font-size:26px">%</span></div>
        <div class="ket">dari seluruh aduan masuk</div>
    </div>
</section>

<section class="tengah">
    <div class="panel">
        <h2>Sebaran Titik Aduan</h2>
        <div id="peta"></div>
    </div>
    <div class="panel">
        <h2>Status Penanganan</h2>
        <div id="status-list">
            <div class="baris"><span class="nama"><span class="chip" style="background:#60A5FA"></span>Menunggu Ditugaskan</span><span class="nilai">{{ $ringkasan['menunggu'] }}</span></div>
            <div class="baris"><span class="nama"><span class="chip" style="background:#3B82F6"></span>Ditugaskan</span><span class="nilai">{{ $ringkasan['ditugaskan'] }}</span></div>
            <div class="baris"><span class="nama"><span class="chip" style="background:#F59E0B"></span>Dikerjakan</span><span class="nilai">{{ $ringkasan['dikerjakan'] }}</span></div>
            <div class="baris"><span class="nama"><span class="chip" style="background:#10B981"></span>Selesai</span><span class="nilai">{{ $ringkasan['selesai'] }}</span></div>
            <div class="baris"><span class="nama"><span class="chip" style="background:#DC2626"></span>Ditolak</span><span class="nilai">{{ $ringkasan['ditolak'] }}</span></div>
            <div class="baris"><span class="nama"><span class="chip" style="background:#64748B"></span>Dibatalkan</span><span class="nilai">{{ $ringkasan['dibatalkan'] }}</span></div>
        </div>
    </div>
</section>

<section class="bawah">
    <div class="panel">
        <h2>Tujuh Hari Terakhir</h2>
        <div class="tren" id="tren">
            @php $maksTren = max(1, collect($ringkasan['tren'])->max('jumlah') ?? 1); @endphp
            @foreach($ringkasan['tren'] as $t)
                <div class="kolom">
                    <span style="font-size:12px;color:var(--redup)">{{ $t['jumlah'] }}</span>
                    <div class="batang" style="height: {{ max(3, round($t['jumlah'] / $maksTren * 76)) }}px"></div>
                    <span class="tgl">{{ $t['tanggal'] }}</span>
                </div>
            @endforeach
        </div>
    </div>
    <div class="panel">
        <h2>Kecamatan Terbanyak</h2>
        <div class="kecamatan" id="kecamatan">
            @php $maksKec = max(1, collect($ringkasan['kecamatan'])->max('jumlah') ?? 1); @endphp
            @foreach($ringkasan['kecamatan'] as $kec)
                <div class="kec">
                    <span>{{ $kec['kecamatan'] }}</span>
                    <span style="font-family:'Outfit';font-weight:700">{{ $kec['jumlah'] }}</span>
                    <span class="jalur"><span class="isi" style="width: {{ round($kec['jumlah'] / $maksKec * 100) }}%"></span></span>
                </div>
            @endforeach
        </div>
    </div>
    <div class="panel">
        <h2>Aduan Terbaru</h2>
        <div class="daftar" id="terbaru" style="overflow:hidden">
            @foreach($ringkasan['terbaru'] as $c)
                <div class="item">
                    <span class="kode">#{{ $c['kode'] }}</span>
                    <span class="judul">{{ $c['judul'] }}</span>
                    <span class="status" style="background: {{ ($warnaStatus[$c['status']] ?? '#64748B') }}22; color: {{ $warnaStatus[$c['status']] ?? '#64748B' }}">
                        {{ $labelStatus[$c['status']] ?? $c['status'] }}
                    </span>
                    <span class="waktu">{{ $c['waktu'] }}</span>
                </div>
            @endforeach
        </div>
    </div>
</section>

<footer>
    <span id="keterangan">Diperbarui {{ now()->translatedFormat('d F Y, H:i') }} WITA · pembaruan otomatis tiap 30 detik</span>
    <span>{{ $ringkasan['terverifikasi_ai'] }} aduan diverifikasi AI · data tanpa identitas pelapor</span>
</footer>

<script>
    const WARNA = @json($warnaStatus);
    const LABEL = @json($labelStatus);

    // Jam berjalan, acuan WITA.
    function jamWita() {
        const now = new Date();
        const wita = new Date(now.getTime() + (now.getTimezoneOffset() * 60000) + (8 * 3600000));
        document.getElementById('jam').textContent = wita.toTimeString().slice(0, 8);
        document.getElementById('tanggal').textContent = wita.toLocaleDateString('id-ID', {
            weekday: 'long', day: 'numeric', month: 'long', year: 'numeric'
        });
    }
    jamWita();
    setInterval(jamWita, 1000);

    // Peta: titik aduan, warna mengikuti status.
    const peta = L.map('peta', { zoomControl: false, attributionControl: true })
        .setView([-3.35, 121.05], 9);
    // Ubin OpenStreetMap: gratis dan tanpa kunci API. Atribusi wajib ditampilkan.
    L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 18,
        attribution: '&copy; OpenStreetMap'
    }).addTo(peta);

    let lapisan = L.layerGroup().addTo(peta);
    function gambarPeta(titik) {
        lapisan.clearLayers();
        titik.forEach(t => {
            L.circleMarker([t.lat, t.lng], {
                radius: t.berat ? 7 : 5,
                color: WARNA[t.status] || '#64748B',
                weight: 2,
                fillColor: WARNA[t.status] || '#64748B',
                fillOpacity: 0.45
            }).bindTooltip('#' + t.kode, { direction: 'top' }).addTo(lapisan);
        });
    }
    gambarPeta(@json($ringkasan['peta']));

    function perbarui(d) {
        document.getElementById('kpi-total').textContent = d.total;
        document.getElementById('kpi-dikerjakan').textContent = d.dikerjakan;
        document.getElementById('kpi-selesai').textContent = d.selesai;
        document.getElementById('kpi-rasio').innerHTML = d.rasio + '<span style="font-size:26px">%</span>';

        const maksKec = Math.max(1, ...d.kecamatan.map(k => k.jumlah));
        document.getElementById('kecamatan').innerHTML = d.kecamatan.map(k => `
            <div class="kec">
                <span>${k.kecamatan}</span>
                <span style="font-family:'Outfit';font-weight:700">${k.jumlah}</span>
                <span class="jalur"><span class="isi" style="width:${Math.round(k.jumlah / maksKec * 100)}%"></span></span>
            </div>`).join('');

        const maksTren = Math.max(1, ...d.tren.map(t => t.jumlah));
        document.getElementById('tren').innerHTML = d.tren.map(t => `
            <div class="kolom">
                <span style="font-size:12px;color:var(--redup)">${t.jumlah}</span>
                <div class="batang" style="height:${Math.max(3, Math.round(t.jumlah / maksTren * 76))}px"></div>
                <span class="tgl">${t.tanggal}</span>
            </div>`).join('');

        document.getElementById('terbaru').innerHTML = d.terbaru.map(c => {
            const w = WARNA[c.status] || '#64748B';
            return `<div class="item">
                <span class="kode">#${c.kode}</span>
                <span class="judul">${c.judul}</span>
                <span class="status" style="background:${w}22;color:${w}">${LABEL[c.status] || c.status}</span>
                <span class="waktu">${c.waktu}</span>
            </div>`;
        }).join('');

        document.getElementById('keterangan').textContent =
            'Diperbarui ' + new Date(d.diperbarui).toLocaleString('id-ID') + ' · pembaruan otomatis tiap 30 detik';

        gambarPeta(d.peta);
    }

    // Muat ulang data tanpa memuat ulang halaman supaya layar tidak berkedip.
    setInterval(async () => {
        try {
            const r = await fetch('{{ url('/monitoring/data') }}', { headers: { Accept: 'application/json' } });
            if (r.ok) perbarui(await r.json());
        } catch (e) { /* jaringan putus sesaat: biarkan data terakhir tampil */ }
    }, 30000);
</script>
</body>
</html>
