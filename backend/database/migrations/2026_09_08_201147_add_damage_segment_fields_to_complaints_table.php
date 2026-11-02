<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('complaints', function (Blueprint $table) {
            $table->string('damage_type')->default('minor')->after('category_id'); // 'minor' = Kerusakan Titik / Ringan, 'major' = Kerusakan Ruas Panjang / Parah
            $table->string('road_length')->nullable()->after('address'); // Misal "500 meter", "2.5 km", "10 km"
            $table->string('road_condition')->nullable()->after('road_length'); // Misal "Jalan Tanah", "Aspal Hancur Berbatu", dll
            $table->decimal('end_latitude', 10, 7)->nullable()->after('longitude');
            $table->decimal('end_longitude', 10, 7)->nullable()->after('end_latitude');
            $table->json('route_coordinates')->nullable()->after('end_longitude'); // Array koordinat polyline [[lat, lng], ...]
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('complaints', function (Blueprint $table) {
            $table->dropColumn([
                'damage_type',
                'road_length',
                'road_condition',
                'end_latitude',
                'end_longitude',
                'route_coordinates'
            ]);
        });
    }
};
