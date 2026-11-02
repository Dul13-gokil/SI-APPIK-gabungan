<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('complaints', function (Blueprint $table) {
            $table->id();
            $table->string('public_code')->unique(); // Kode publik unik (misal: ADU-202609-0001)
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('category_id')->constrained('categories')->onDelete('restrict');
            $table->foreignId('region_id')->nullable()->constrained('regions')->nullOnDelete();
            
            $table->string('title');
            $table->text('description');
            $table->string('address');
            $table->decimal('latitude', 10, 7);
            $table->decimal('longitude', 10, 7);
            
            $table->string('status')->default('SUBMITTED'); // DRAFT, SUBMITTED, VERIFICATION, REJECTED, VERIFIED, DISPATCHED, IN_PROGRESS, WAITING_VERIFICATION, REVISION_REQUIRED, COMPLETED, CLOSED
            
            // Prioritas & SLA (SAP Section 18)
            $table->decimal('priority_score', 5, 2)->default(0.00);
            $table->string('priority_level')->default('LOW'); // LOW, MEDIUM, HIGH, CRITICAL
            
            // AI CV Assessment (SAP Section 16)
            $table->string('ai_category')->nullable();
            $table->decimal('ai_confidence', 5, 4)->nullable();
            
            // Anonymity & Duplicate Merging (SAP Section 17 & 22)
            $table->boolean('is_anonymous')->default(false);
            $table->unsignedBigInteger('parent_complaint_id')->nullable();
            
            $table->timestamps();
            
            $table->index(['status', 'created_at']);
            $table->index(['priority_level', 'priority_score']);
            $table->index('parent_complaint_id');
        });

        // Add PostGIS spatial Point column & GiST index on PostgreSQL
        if (DB::connection()->getDriverName() === 'pgsql') {
            DB::statement('ALTER TABLE complaints ADD COLUMN geom geometry(Point, 4326);');
            DB::statement('CREATE INDEX IF NOT EXISTS complaints_geom_gist ON complaints USING GIST (geom);');
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('complaints');
    }
};
