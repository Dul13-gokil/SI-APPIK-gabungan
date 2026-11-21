<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ComplaintPhoto extends Model
{
    use HasFactory;

    protected $fillable = [
        'complaint_id',
        'storage_key',
        'url',
        'file_data',
        'type', // evidence_before, progress, resolution_after (SAP Section 15)
        'mime',
        'size',
        'checksum',
        'captured_at',
    ];

    protected $hidden = [
        'file_data',
    ];

    protected $casts = [
        'captured_at' => 'datetime',
        'size' => 'integer',
    ];

    public function complaint(): BelongsTo
    {
        return $this->belongsTo(Complaint::class);
    }

    /**
     * Pastikan kolom file_data tersedia di tabel complaint_photos (aman untuk PostgreSQL & MySQL)
     * agar unggahan foto tidak gagal jika migrasi tertunda di server produksi.
     */
    public static function ensureFileDataColumnExists(): bool
    {
        static $exists = false;

        if ($exists) {
            return true;
        }

        try {
            if (\Illuminate\Support\Facades\Schema::hasColumn('complaint_photos', 'file_data')) {
                return $exists = true;
            }

            if (\Illuminate\Support\Facades\DB::connection()->getDriverName() === 'pgsql') {
                \Illuminate\Support\Facades\DB::statement('ALTER TABLE complaint_photos ADD COLUMN IF NOT EXISTS file_data TEXT NULL');
            } else {
                \Illuminate\Support\Facades\Schema::table('complaint_photos', function ($table) {
                    $table->longText('file_data')->nullable()->after('url');
                });
            }

            return $exists = \Illuminate\Support\Facades\Schema::hasColumn('complaint_photos', 'file_data');
        } catch (\Throwable $e) {
            return false;
        }
    }
}
