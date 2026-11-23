<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('complaints', function (Blueprint $table) {
            if (! Schema::hasColumn('complaints', 'radius_m')) {
                $table->unsignedSmallInteger('radius_m')->default(20)->after('longitude');
            }
            if (! Schema::hasColumn('complaints', 'verified_by_ai')) {
                $table->boolean('verified_by_ai')->default(false)->after('status');
            }
            if (! Schema::hasColumn('complaints', 'completion_verified_by_ai')) {
                $table->boolean('completion_verified_by_ai')->default(false)->after('verified_by_ai');
            }
        });

        // Keputusan rapat: kategori menjadi 2 — Jalan & Jembatan, Fasilitas Umum Lainnya.
        // PJU digabung ke Fasilitas Umum Lainnya agar histori tetap utuh.
        DB::table('complaints')->where('category_id', 2)->update(['category_id' => 3]);
        DB::table('categories')->where('id', 3)->update(['name' => 'Fasilitas Umum Lainnya']);
        DB::table('categories')->where('id', 2)->delete();
    }

    public function down(): void
    {
        DB::table('categories')->insertOrIgnore([
            'id' => 2,
            'name' => 'Penerangan Jalan Umum (PJU)',
            'severity_default' => 2,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        DB::table('categories')->where('id', 3)->update(['name' => 'Fasilitas Umum']);

        Schema::table('complaints', function (Blueprint $table) {
            $table->dropColumn(['radius_m', 'verified_by_ai', 'completion_verified_by_ai']);
        });
    }
};
