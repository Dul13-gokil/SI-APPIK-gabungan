<?php

namespace App\Domain\Complaints\Services;

use App\Models\Complaint;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class DuplicateDetectionService
{
    /**
     * Cari kandidat laporan duplikat di sekitar koordinat dalam radius (meter) tertentu.
     * Sesuai SAP Bagian 17: PostGIS spatial check + Haversine fallback.
     *
     * @param float $latitude
     * @param float $longitude
     * @param float $radiusMeters
     * @param int|null $excludeComplaintId
     * @return Collection
     */
    public function findCandidates(
        float $latitude,
        float $longitude,
        float $radiusMeters = 100.0,
        ?int $excludeComplaintId = null
    ): Collection {
        $activeStatuses = ['SUBMITTED', 'VERIFICATION', 'VERIFIED', 'DISPATCHED', 'IN_PROGRESS'];
        $connectionDriver = DB::connection()->getDriverName();

        // 1. PostGIS Query (Production / Postgres with PostGIS enabled)
        if ($connectionDriver === 'pgsql' && DB::selectOne("SELECT 1 FROM pg_extension WHERE extname = 'postgis'")) {
            $pointWkt = "SRID=4326;POINT({$longitude} {$latitude})";

            $query = Complaint::query()
                ->select('*')
                ->selectRaw(
                    "ST_Distance(geom::geography, ST_GeomFromText(?, 4326)::geography) AS distance_m",
                    [$pointWkt]
                )
                ->whereIn('status', $activeStatuses)
                ->whereRaw(
                    "ST_DWithin(geom::geography, ST_GeomFromText(?, 4326)::geography, ?)",
                    [$pointWkt, $radiusMeters]
                );

            if ($excludeComplaintId) {
                $query->where('id', '!=', $excludeComplaintId);
            }

            return $query->orderBy('distance_m', 'asc')->get();
        }

        // 2. Fallback portabel (PostgreSQL / MySQL / SQLite): jarak dihitung di PHP.
        // shortcut: memuat semua laporan aktif, andalkan PostGIS bila volume data sudah besar.
        $candidates = Complaint::query()
            ->whereIn('status', $activeStatuses)
            ->when($excludeComplaintId, fn ($query) => $query->where('id', '!=', $excludeComplaintId))
            ->get();

        return $candidates
            ->map(function (Complaint $complaint) use ($latitude, $longitude) {
                $complaint->setAttribute('distance_m', $this->haversineMeters(
                    $latitude,
                    $longitude,
                    (float) $complaint->latitude,
                    (float) $complaint->longitude
                ));

                return $complaint;
            })
            ->filter(fn (Complaint $complaint) => $complaint->distance_m <= $radiusMeters)
            ->sortBy('distance_m')
            ->values();
    }

    private function haversineMeters(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        $earthRadius = 6371000.0;
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);
        $a = sin($dLat / 2) ** 2
            + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLon / 2) ** 2;

        return $earthRadius * 2 * atan2(sqrt($a), sqrt(1 - $a));
    }
}
