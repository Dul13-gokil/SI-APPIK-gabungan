<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Akun baru dibuat dengan sandi sementara acak, bukan "password".
 * Tanpa penanda eksplisit, gerbang "wajib ganti sandi saat login pertama"
 * tidak lagi bisa dideteksi (dulu caranya membandingkan hash dengan "password").
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('must_change_password')->default(false)->after('is_active');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('must_change_password');
        });
    }
};