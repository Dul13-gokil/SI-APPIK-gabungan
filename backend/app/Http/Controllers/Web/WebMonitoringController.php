<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Complaint;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

/**
 * Layar monitoring untuk ruang kendali (TV/monitor besar).
 *
 * Sengaja tanpa login dan tanpa sidebar: perangkat dipasang di ruang kendali dan
 * dibuka sekali, lalu dibiarkan menyala. Yang ditampilkan hanya agregat dan data
 * publik aduan — tidak ada nama, nomor HP, atau surel pelapor.
 */
class WebMonitoringController extends Controller
{
    public function index(): View
    {
        return view('monitoring.wall', [
            'ringkasan' => $this->ringkasan(),
            'judul'     => 'Pusat Pantau Aduan Infrastruktur',
        ]);
    }

    /**
     * Data untuk pembaruan otomatis tanpa memuat ulang seluruh halaman.
     */
    public function data(): JsonResponse
    {
        return response()->json($this->ringkasan());
    }

    private function ringkasan(): array
    {
        $perStatus = Complaint::selectRaw('status, count(*) as jumlah')
            ->groupBy('status')
            ->pluck('jumlah', 'status');

        $angka = fn (array $status) => (int) collect($status)->sum(fn ($s) => (int) ($perStatus[$s] ?? 0));

        $total      = (int) $perStatus->sum();
        $selesai    = $angka(['COMPLETED', 'CLOSED']);
        $dikerjakan = $angka(['IN_PROGRESS', 'REVISION_REQUIRED']);
        $ditugaskan = $angka(['DISPATCHED']);
        $menunggu   = $angka(['SUBMITTED', 'VERIFIED', 'WAITING_VERIFICATION']);
        $ditolak    = $angka(['REJECTED']);
        $dibatalkan = $angka(['CANCELLED']);

        // Tren 7 hari terakhir; tanggal kosong tetap ditampilkan agar grafik tidak bolong.
        $trenMentah = Complaint::where('created_at', '>=', now()->subDays(6)->startOfDay())
            ->selectRaw('date(created_at) as hari, count(*) as jumlah')
            ->groupBy('hari')
            ->pluck('jumlah', 'hari');

        $tren = [];
        for ($i = 6; $i >= 0; $i--) {
            $hari = now()->subDays($i)->toDateString();
            $tren[] = [
                'tanggal' => Carbon::parse($hari)->translatedFormat('d M'),
                'jumlah'  => (int) ($trenMentah[$hari] ?? 0),
            ];
        }

        // Sebaran per kecamatan (regu menangani per wilayah).
        $perKecamatan = Complaint::join('regions', 'regions.id', '=', 'complaints.region_id')
            ->selectRaw("regions.district as kecamatan, count(*) as jumlah,
                sum(case when complaints.status in ('COMPLETED','CLOSED') then 1 else 0 end) as selesai")
            ->groupBy('regions.district')
            ->orderByDesc('jumlah')
            ->limit(5)
            ->get()
            ->map(fn ($r) => [
                'kecamatan' => preg_replace('/^(Kecamatan|Kec\.)\s+/i', '', (string) $r->kecamatan),
                'jumlah'    => (int) $r->jumlah,
                'selesai'   => (int) $r->selesai,
            ])
            ->all();

        $peta = Complaint::whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->latest()
            ->limit(200)
            ->get(['id', 'public_code', 'status', 'latitude', 'longitude', 'damage_type'])
            ->map(fn ($c) => [
                'kode'     => (string) $c->public_code,
                'status'   => (string) $c->status,
                'lat'      => (float) $c->latitude,
                'lng'      => (float) $c->longitude,
                'berat'    => ($c->damage_type ?? 'minor') === 'major',
            ])
            ->all();

        $terbaru = Complaint::latest()
            ->limit(5)
            ->get(['id', 'public_code', 'title', 'address', 'status', 'created_at'])
            ->map(fn ($c) => [
                'kode'    => (string) $c->public_code,
                'judul'   => (string) $c->title,
                'alamat'  => (string) ($c->address ?? ''),
                'status'  => (string) $c->status,
                'waktu'   => $c->created_at?->diffForHumans(short: true) ?? '-',
            ])
            ->all();

        return [
            'diperbarui'   => now()->toIso8601String(),
            'total'        => $total,
            'selesai'      => $selesai,
            'dikerjakan'   => $dikerjakan,
            'ditugaskan'   => $ditugaskan,
            'menunggu'     => $menunggu,
            'ditolak'      => $ditolak,
            'dibatalkan'   => $dibatalkan,
            'rasio'        => $total > 0 ? (int) round($selesai / $total * 100) : 0,
            'terverifikasi_ai' => (int) Complaint::where('verified_by_ai', true)->count(),
            'tren'         => $tren,
            'kecamatan'    => $perKecamatan,
            'peta'         => $peta,
            'terbaru'      => $terbaru,
        ];
    }
}
