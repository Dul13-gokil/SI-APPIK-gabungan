<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // misal: Jalan Berlubang, Jembatan Rusak, Penerangan Jalan
            $table->foreignId('department_id')->constrained('departments')->onDelete('cascade');
            $table->integer('severity_default')->default(3); // Skala 1 - 5 (SAP Section 12)
            $table->string('ai_label')->nullable(); // Label class untuk AI CV
            $table->integer('sla_hours')->default(72); // SLA penanganan default (jam)
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('categories');
    }
};
