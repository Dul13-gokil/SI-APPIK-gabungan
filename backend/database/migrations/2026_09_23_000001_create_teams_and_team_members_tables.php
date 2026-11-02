<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('teams', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->unique();
            $table->string('district')->nullable()->default('Semua Wilayah');
            $table->foreignId('department_id')->nullable()->constrained('departments')->nullOnDelete();
            $table->foreignId('leader_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('team_members', function (Blueprint $table) {
            $table->id();
            $table->foreignId('team_id')->constrained('teams')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('role')->default('MEMBER'); // LEADER, MEMBER
            $table->timestamps();

            $table->unique(['team_id', 'user_id']);
        });

        Schema::table('dispositions', function (Blueprint $table) {
            if (!Schema::hasColumn('dispositions', 'team_id')) {
                $table->foreignId('team_id')->nullable()->after('assigned_to')->constrained('teams')->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('dispositions', function (Blueprint $table) {
            if (Schema::hasColumn('dispositions', 'team_id')) {
                $table->dropForeign(['team_id']);
                $table->dropColumn('team_id');
            }
        });

        Schema::dropIfExists('team_members');
        Schema::dropIfExists('teams');
    }
};
