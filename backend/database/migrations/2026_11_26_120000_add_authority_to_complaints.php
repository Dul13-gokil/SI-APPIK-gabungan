<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('complaints', function (Blueprint $table) {
            // Kewenangan ruas ditentukan operator, bukan AI: OSM di Kolaka Utara
            // tidak punya kode ruas, jadi klasifikasi otomatis tidak bisa dipercaya.
            $table->string('authority', 20)->default('KABUPATEN')->after('status');
            $table->string('referred_to', 150)->nullable()->after('authority');
            $table->timestamp('referred_at')->nullable()->after('referred_to');
        });
    }

    public function down(): void
    {
        Schema::table('complaints', function (Blueprint $table) {
            $table->dropColumn(['authority', 'referred_to', 'referred_at']);
        });
    }
};
