<?php

namespace Tests\Feature;

use App\Domain\Geo\RoadAuthorityClassifier;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * Impor jalan OSM: klasifikasi kewenangan dan pembentukan GeoJSON.
 * Tidak memanggil jaringan; respons Overpass dipalsukan.
 */
class RoadsImportTest extends TestCase
{
    private string $outRelative = 'geojson/_uji_roads.geojson';

    protected function tearDown(): void
    {
        $path = public_path($this->outRelative);
        if (is_file($path)) {
            @unlink($path);
        }

        parent::tearDown();
    }

    public function test_classifier_maps_highway_classes_to_authority(): void
    {
        $classifier = new RoadAuthorityClassifier();

        $this->assertSame(RoadAuthorityClassifier::NASIONAL, $classifier->classify(['highway' => 'trunk']));
        $this->assertSame(RoadAuthorityClassifier::PROVINSI, $classifier->classify(['highway' => 'primary']));
        $this->assertSame(RoadAuthorityClassifier::KABUPATEN, $classifier->classify(['highway' => 'tertiary']));
        $this->assertSame(
            RoadAuthorityClassifier::PROVINSI,
            $classifier->classify(['highway' => 'trunk', 'network' => 'ID:provincial'])
        );
        $this->assertSame(
            RoadAuthorityClassifier::KABUPATEN,
            $classifier->classifyWithOverride('999', ['highway' => 'trunk'], ['999' => 'kabupaten'])
        );
    }

    public function test_import_command_writes_geojson_from_osm_response(): void
    {
        Http::fake([
            '*' => Http::response([
                'elements' => [
                    [
                        'id' => 101,
                        'tags' => ['highway' => 'trunk', 'name' => 'Jalan Poros Malili'],
                        'geometry' => [
                            ['lat' => -3.50, 'lon' => 120.88],
                            ['lat' => -3.49, 'lon' => 120.89],
                        ],
                    ],
                    [
                        'id' => 102,
                        'tags' => ['highway' => 'tertiary'],
                        'geometry' => [
                            ['lat' => -3.51, 'lon' => 120.87],
                            ['lat' => -3.515, 'lon' => 120.875],
                        ],
                    ],
                ],
            ], 200),
        ]);

        $this->artisan('roads:import', [
            '--out' => $this->outRelative,
            '--overrides' => 'database/data/tidak-ada.json',
        ])->assertSuccessful();

        $path = public_path($this->outRelative);
        $this->assertTrue(is_file($path), 'GeoJSON hasil impor tidak terbentuk');

        $data = json_decode(File::get($path), true);
        $this->assertSame('FeatureCollection', $data['type']);
        $this->assertCount(2, $data['features']);

        $byId = collect($data['features'])->keyBy(fn ($f) => $f['properties']['osm_id']);
        $this->assertSame(RoadAuthorityClassifier::NASIONAL, $byId[101]['properties']['authority']);
        $this->assertSame(RoadAuthorityClassifier::KABUPATEN, $byId[102]['properties']['authority']);
        $this->assertSame('LineString', $byId[101]['geometry']['type']);
        $this->assertSame([120.88, -3.50], $byId[101]['geometry']['coordinates'][0]);
    }
}
