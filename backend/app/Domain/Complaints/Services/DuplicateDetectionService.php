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

        // 2. Haversine Formula Fallback (Standard PostgreSQL / MySQL / SQLite)
        $haversine = "(6371000 * acos(LEAST(1.0, GREATEST(-1.0, cos(radians(?)) * cos(radians(latitude)) * cos(radians(longitude) - radians(?)) + sin(radians(?)) * sin(radians(latitude))))))";

        $query = Complaint::query()
            ->select('*')
            ->selectRaw("{$haversine} AS distance_m", [$latitude, $longitude, $latitude])
            ->whereIn('status', $activeStatuses)
            ->whereRaw("{$haversine} <= ?", [$latitude, $longitude, $latitude, $radiusMeters]);

        if ($excludeComplaintId) {
            $query->where('id', '!=', $excludeComplaintId);
        }

        return $query->orderBy('distance_m', 'asc')->get();
    }
}
