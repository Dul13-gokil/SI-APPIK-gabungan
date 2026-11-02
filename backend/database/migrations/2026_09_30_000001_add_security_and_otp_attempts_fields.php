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
        if (Schema::hasTable('otp_verifications') && !Schema::hasColumn('otp_verifications', 'attempts')) {
            Schema::table('otp_verifications', function (Blueprint $table) {
                $table->unsignedTinyInteger('attempts')->default(0)->after('code');
            });
        }

        if (Schema::hasTable('users')) {
            Schema::table('users', function (Blueprint $table) {
                if (!Schema::hasColumn('users', 'last_login_ip')) {
                    $table->string('last_login_ip', 45)->nullable()->after('is_anonymous');
                }
                if (!Schema::hasColumn('users', 'last_login_user_agent')) {
                    $table->text('last_login_user_agent')->nullable()->after('last_login_ip');
                }
                if (!Schema::hasColumn('users', 'last_login_at')) {
                    $table->timestamp('last_login_at')->nullable()->after('last_login_user_agent');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('otp_verifications') && Schema::hasColumn('otp_verifications', 'attempts')) {
            Schema::table('otp_verifications', function (Blueprint $table) {
                $table->dropColumn('attempts');
            });
        }

        if (Schema::hasTable('users')) {
            Schema::table('users', function (Blueprint $table) {
                $columns = [];
                if (Schema::hasColumn('users', 'last_login_ip')) {
                    $columns[] = 'last_login_ip';
                }
                if (Schema::hasColumn('users', 'last_login_user_agent')) {
                    $columns[] = 'last_login_user_agent';
                }
                if (Schema::hasColumn('users', 'last_login_at')) {
                    $columns[] = 'last_login_at';
                }
                if (!empty($columns)) {
                    $table->dropColumn($columns);
                }
            });
        }
    }
};
