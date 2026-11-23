<?php

namespace App\Console\Commands;

use App\Domain\Geo\RoadAuthorityClassifier;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;

/**
 * Mengunduh jaringan jalan Kabupaten Kolaka Utara dari OpenStreetMap (Overpass)
 * lalu menyimpannya sebagai GeoJSON yang dipakai peta portal internal.
 *
 * Jalankan ulang setiap kali data OSM diperbarui:
 *   php artisan roads:import
 */
class ImportRoadsFromOsm extends Command
{
    protected $signature = 'roads:import
        {--bbox=-3.80,120.70,-2.75,121.50 : Batas wilayah (selatan,barat,utara,timur)}
        {--out=geojson/roads_kolaka_utara.geojson : Lokasi berkas hasil di folder public}
        {--overrides=database/data/road_authority_overrides.json : Berkas koreksi kewenangan (osm_id => kewenangan)}
        {--cacert=storage/app/cacert.pem : CA bundle untuk TLS (dipakai bila ada; container biasanya sudah punya)}';

    protected $description = 'Impor jaringan jalan dari OpenStreetMap menjadi GeoJSON untuk peta aduan';

    /** Kelas jalan yang diambil. */
    private const HIGHWAY_FILTER = 'trunk|primary|secondary|tertiary';

    public function handle(RoadAuthorityClassifier $classifier): int
    {
        $bbox = (string) $this->option('bbox');
        if (count(explode(',', $bbox)) !== 4) {
            $this->error('Format --bbox harus: selatan,barat,utara,timur');

            return self::FAILURE;
        }

        $query = sprintf(
            '[out:json][timeout:180];way["highway"~"^(%s)$"](%s);out geom tags;',
            self::HIGHWAY_FILTER,
            $bbox
        );

        $this->info('Mengunduh jaringan jalan dari Overpass API...');
        $request = Http::withHeaders(['User-Agent' => 'SI-APPIK/1.0 (peta aduan Kabupaten Kolaka Utara)'])
            ->timeout(240);

        // PHP di Windows sering tidak membawa CA bundle; pakai berkas eksplisit bila tersedia.
        $caCert = (string) $this->option('cacert');
        $caPath = base_path($caCert);
        if ($caCert !== '' && is_file($caPath)) {
            $request = $request->withOptions(['verify' => $caPath]);
        }

        // Overpass sering membalas 502 saat sibuk; coba beberapa cermin sebelum menyerah.
        $response = null;
        foreach ([
            'https://overpass-api.de/api/interpreter',
            'https://overpass.kumi.systems/api/interpreter',
        ] as $endpoint) {
            for ($attempt = 1; $attempt <= 2; $attempt++) {
                $candidate = $request->asForm()->post($endpoint, ['data' => $query]);

                if ($candidate->successful()) {
                    $response = $candidate;
                    break 2;
                }

                $this->warn(sprintf('%s gagal (HTTP %d), percobaan %d/2', $endpoint, $candidate->status(), $attempt));
                sleep(4 * $attempt);
            }
        }

        if ($response === null || ! $response->successful()) {
            $this->error('Gagal mengunduh data OSM dari semua cermin. Coba lagi beberapa menit lagi.');

            return self::FAILURE;
        }

        $elements = $response->json('elements', []);
        if (empty($elements)) {
            $this->error('Tidak ada jalan yang dikembalikan Overpass.');

            return self::FAILURE;
        }

        $overrides = $this->loadOverrides((string) $this->option('overrides'));

        $features = [];
        $counts = [];

        foreach ($elements as $element) {
            $tags = $element['tags'] ?? [];
            $geometry = $element['geometry'] ?? [];
            if (count($geometry) < 2) {
                continue;
            }

            $authority = $classifier->classifyWithOverride((string) ($element['id'] ?? ''), $tags, $overrides);
            $counts[$authority] = ($counts[$authority] ?? 0) + 1;

            $features[] = [
                'type' => 'Feature',
                'properties' => [
                    'osm_id' => $element['id'] ?? null,
                    'name' => $tags['name'] ?? null,
                    'highway' => $tags['highway'] ?? null,
                    'ref' => $tags['ref'] ?? null,
                    'authority' => $authority,
                ],
                'geometry' => [
                    'type' => 'LineString',
                    'coordinates' => array_map(
                        fn ($point) => [(float) $point['lon'], (float) $point['lat']],
                        $geometry
                    ),
                ],
            ];
        }

        $path = public_path((string) $this->option('out'));
        File::ensureDirectoryExists(dirname($path));
        File::put($path, json_encode([
            'type' => 'FeatureCollection',
            'generated_at' => now()->toIso8601String(),
            'source' => 'OpenStreetMap via Overpass API',
            'note' => 'Kewenangan adalah perkiraan dari kelas jalan OSM, bukan penetapan resmi.',
            'features' => $features,
        ], JSON_UNESCAPED_UNICODE));

        $this->info(sprintf('Selesai: %d ruas jalan -> %s', count($features), $path));
        foreach ($counts as $authority => $total) {
            $this->line("  {$authority}: {$total}");
        }
        if ($overrides === []) {
            $this->line('  (belum ada koreksi manual; tambahkan lewat ' . $this->option('overrides') . ')');
        }

        return self::SUCCESS;
    }

    /**
     * @return array<string, string>
     */
    private function loadOverrides(string $relativePath): array
    {
        $path = base_path($relativePath);
        if (! is_file($path)) {
            return [];
        }

        $decoded = json_decode((string) file_get_contents($path), true);

        return is_array($decoded) ? $decoded : [];
    }
}
