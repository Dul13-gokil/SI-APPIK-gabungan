<?php

namespace App\Domain\Complaints\Services;

class PriorityService
{
    /**
     * Hitung bobot dan skor prioritas berdasarkan rumus SAP Bagian 18.
     *
     * @param float $severityScore Skala 1 - 5
     * @param int $supportCount Jumlah dukungan warga
     * @param float $locationImportance Bobot penting lokasi (misal: jalan protokol = 1.0, jalan desa = 0.4)
     * @param int $ageInHours Lama laporan terbuka
     * @param int $duplicateCount Jumlah laporan duplikat di titik yang sama
     * @param array $customWeights Bobot konfigurasi kustom (opsional)
     * @return array{score: float, level: string, factors: array}
     */
    public function calculate(
        float $severityScore,
        int $supportCount,
        float $locationImportance = 0.5,
        int $ageInHours = 0,
        int $duplicateCount = 0,
        array $customWeights = []
    ): array {
        // Bobot default (total 1.0)
        $weights = array_merge([
            'severity_weight' => 0.35,
            'support_weight' => 0.20,
            'location_weight' => 0.20,
            'age_weight' => 0.15,
            'duplicate_weight' => 0.10,
        ], $customWeights);

        // Normalisasi komponen
        $normSeverity = min(1.0, max(0.0, $severityScore / 5.0));
        $normSupport = min(1.0, max(0.0, log10($supportCount + 1) / 2.0)); // Log normalisasi
        $normLocation = min(1.0, max(0.0, $locationImportance));
        $normAge = min(1.0, max(0.0, $ageInHours / 168.0)); // 1 minggu = max age factor
        $normDuplicate = min(1.0, max(0.0, $duplicateCount / 10.0));

        $score = (
            ($weights['severity_weight'] * $normSeverity) +
            ($weights['support_weight'] * $normSupport) +
            ($weights['location_weight'] * $normLocation) +
            ($weights['age_weight'] * $normAge) +
            ($weights['duplicate_weight'] * $normDuplicate)
        ) * 100.0;

        $level = match (true) {
            $score >= 75.0 => 'CRITICAL',
            $score >= 50.0 => 'HIGH',
            $score >= 25.0 => 'MEDIUM',
            default => 'LOW',
        };

        return [
            'score' => round($score, 2),
            'level' => $level,
            'factors' => [
                'severity' => round($normSeverity, 2),
                'support' => round($normSupport, 2),
                'location' => round($normLocation, 2),
                'age' => round($normAge, 2),
                'duplicate' => round($normDuplicate, 2),
            ]
        ];
    }
}
