<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'is_active')) {
                $table->boolean('is_active')->default(true)->after('is_anonymous');
            }
            if (!Schema::hasColumn('users', 'deactivation_reason')) {
                $table->text('deactivation_reason')->nullable()->after('is_active');
            }
            if (!Schema::hasColumn('users', 'deactivated_at')) {
                $table->timestamp('deactivated_at')->nullable()->after('deactivation_reason');
            }
            if (!Schema::hasColumn('users', 'deactivated_by')) {
                $table->unsignedBigInteger('deactivated_by')->nullable()->after('deactivated_at');
            }
        });

        Schema::table('complaints', function (Blueprint $table) {
            if (!Schema::hasColumn('complaints', 'rejected_by_superadmin')) {
                $table->boolean('rejected_by_superadmin')->default(false)->after('status');
            }
            if (!Schema::hasColumn('complaints', 'admin_rejection_reason')) {
                $table->text('admin_rejection_reason')->nullable()->after('rejected_by_superadmin');
            }
            if (!Schema::hasColumn('complaints', 'admin_rejected_at')) {
                $table->timestamp('admin_rejected_at')->nullable()->after('admin_rejection_reason');
            }
            if (!Schema::hasColumn('complaints', 'admin_rejected_by')) {
                $table->unsignedBigInteger('admin_rejected_by')->nullable()->after('admin_rejected_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $cols = array_filter(['is_active', 'deactivation_reason', 'deactivated_at', 'deactivated_by'], fn($c) => Schema::hasColumn('users', $c));
            if ($cols) {
                $table->dropColumn($cols);
            }
        });

        Schema::table('complaints', function (Blueprint $table) {
            $cols = array_filter(['rejected_by_superadmin', 'admin_rejection_reason', 'admin_rejected_at', 'admin_rejected_by'], fn($c) => Schema::hasColumn('complaints', $c));
            if ($cols) {
                $table->dropColumn($cols);
            }
        });
    }
};
