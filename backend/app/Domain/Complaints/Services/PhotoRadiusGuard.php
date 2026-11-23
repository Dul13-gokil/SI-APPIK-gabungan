<?php

namespace App\Domain\Complaints\Services;

use App\Domain\Complaints\Exceptions\PhotoRadiusException;
use App\Models\Complaint;
use App\Models\SystemSetting;

/**
 * Aturan radius pengambilan foto bukti pengerjaan (keputusan rapat).
 * Foto wajib diambil di sekitar titik aduan, bukan di lokasi lain.
 * Satu sumber aturan untuk portal web dan API mobile.
 */
class PhotoRadiusGuard
{
    /**
     * Toleransi akurasi GPS ponsel di perkotaan. Tanpa ini petugas yang jujur
     * sering gagal hanya karena titik GPS bergeser beberapa puluh meter.
     * shortcut: toleransi tetap 25 m, ubah ke SystemSetting bila perlu dikalibrasi per wilayah.
     */
    public const GPS_TOLERANCE_M = 25;

    /**
     * @throws PhotoRadiusException
     */
    public function assertWithinRadius(Complaint $complaint, ?float $lat, ?float $lon): void
    {
        // Aduan tanpa koordinat tidak dapat diperiksa; jangan blokir pekerjaan.
        if ($complaint->latitude === null || $complaint->longitude === null) {
            return;
        }

        if ($lat === null || $lon === null) {
            // APK lama belum mengirim lokasi. Bila wajib, tolak; bila tidak, izinkan
            // tetapi tanpa titik foto (tercatat di riwayat status sebagai bukti tanpa lokasi).
            // shortcut: default longgar sampai APK baru tersebar; set
            // photo_radius_require_location=true setelah rollout untuk menegakkan penuh.
            if (SystemSetting::get('photo_radius_require_location', false)) {
                throw PhotoRadiusException::locationMissing();
            }

            return;
        }

        $radius = (int) ($complaint->radius_m ?: 20);
        $distance = $this->distanceMeters((float) $complaint->latitude, (float) $complaint->longitude, $lat, $lon);

        if ($distance > $radius + self::GPS_TOLERANCE_M) {
            throw PhotoRadiusException::outside($distance, $radius);
        }
    }

    public function distanceMeters(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        $earthRadius = 6371000.0;
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);
        $a = sin($dLat / 2) ** 2
            + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLon / 2) ** 2;

        return $earthRadius * 2 * atan2(sqrt($a), sqrt(1 - $a));
    }
}
