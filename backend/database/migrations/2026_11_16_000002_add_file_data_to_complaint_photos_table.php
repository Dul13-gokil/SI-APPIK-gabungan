<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('complaint_photos', 'file_data')) {
            Schema::table('complaint_photos', function (Blueprint $table) {
                $table->longText('file_data')->nullable()->after('url');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('complaint_photos', 'file_data')) {
            Schema::table('complaint_photos', function (Blueprint $table) {
                $table->dropColumn('file_data');
            });
        }
    }
};
