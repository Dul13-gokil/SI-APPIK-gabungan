<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('complaint_ratings')) {
            Schema::create('complaint_ratings', function (Blueprint $table) {
                $table->id();
                $table->foreignId('complaint_id')->unique()->constrained('complaints')->cascadeOnDelete();
                $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
                $table->unsignedTinyInteger('rating'); // 1..5
                $table->text('comment')->nullable();
                $table->timestamps();

                $table->index(['rating', 'created_at']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('complaint_ratings');
    }
};
