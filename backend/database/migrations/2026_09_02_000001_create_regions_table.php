<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Enable PostGIS extension if running PostgreSQL
        if (DB::connection()->getDriverName() === 'pgsql') {
            DB::statement('CREATE EXTENSION IF NOT EXISTS postgis;');
        }

        Schema::create('regions', function (Blueprint $table) {
            $table->id();
            $table->string('province')->default('Jawa Tengah');
            $table->string('regency'); // Nama Kabupaten
            $table->string('district'); // Nama Kecamatan
            $table->string('village')->nullable(); // Nama Desa/Kelurahan
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->timestamps();
        });

        // Add PostGIS spatial column on PostgreSQL
        if (DB::connection()->getDriverName() === 'pgsql') {
            DB::statement('ALTER TABLE regions ADD COLUMN geom geometry(Point, 4326);');
            DB::statement('CREATE INDEX IF NOT EXISTS regions_geom_gist ON regions USING GIST (geom);');
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('regions');
    }
};
