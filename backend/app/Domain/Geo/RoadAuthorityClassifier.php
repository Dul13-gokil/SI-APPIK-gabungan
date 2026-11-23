<?php

namespace App\Domain\Geo;

/**
 * Menentukan perkiraan kewenangan jalan dari tag OpenStreetMap.
 *
 * Penting: OSM di Kabupaten Kolaka Utara hampir tidak memuat tag kewenangan resmi
 * (tidak ada `ref`/`network`). Klasifikasi ini memakai konvensi kelas jalan dan
 * hanya perkiraan awal; kewenangan resmi tetap mengacu pada SK Dinas.
 */
class RoadAuthorityClassifier
{
    public const NASIONAL = 'NASIONAL';
    public const PROVINSI = 'PROVINSI';
    public const KABUPATEN = 'KABUPATEN';
    public const TIDAK_DIKETAHUI = 'TIDAK_DIKETAHUI';

    /**
     * @param array<string, mixed> $tags
     */
    public function classify(array $tags): string
    {
        $network = strtolower((string) ($tags['network'] ?? ''));
        $ref = strtolower((string) ($tags['ref'] ?? ''));
        $name = strtolower((string) ($tags['name'] ?? ''));

        if (str_contains($network, 'national') || str_contains($ref, 'nasional') || str_contains($name, 'nasional')) {
            return self::NASIONAL;
        }

        if (str_contains($network, 'provincial') || str_contains($ref, 'provinsi') || str_contains($name, 'provinsi')) {
            return self::PROVINSI;
        }

        return match ((string) ($tags['highway'] ?? '')) {
            'motorway', 'trunk' => self::NASIONAL,
            'primary', 'secondary' => self::PROVINSI,
            'tertiary', 'unclassified', 'residential' => self::KABUPATEN,
            default => self::TIDAK_DIKETAHUI,
        };
    }

    /**
     * @param array<string, mixed> $tags
     * @param array<string, string> $overrides osm way id => authority
     */
    public function classifyWithOverride(string $osmId, array $tags, array $overrides = []): string
    {
        if (isset($overrides[$osmId])) {
            $override = strtoupper((string) $overrides[$osmId]);
            if (in_array($override, [self::NASIONAL, self::PROVINSI, self::KABUPATEN, self::TIDAK_DIKETAHUI], true)) {
                return $override;
            }
        }

        return $this->classify($tags);
    }
}
