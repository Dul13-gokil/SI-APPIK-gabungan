<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ai_assessments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('complaint_id')->constrained('complaints')->onDelete('cascade');
            $table->string('model'); // e.g. gemini-1.5-flash / cv-infra-v1.0
            $table->string('task'); // DAMAGE_CLASSIFICATION, DUPLICATE_CANDIDATE, SEVERITY_ESTIMATION
            $table->json('result_json');
            $table->decimal('confidence', 5, 4);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_assessments');
    }
};
