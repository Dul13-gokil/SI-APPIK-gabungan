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
        'type', // evidence_before, progress, resolution_after (SAP Section 15)
        'mime',
        'size',
        'checksum',
        'captured_at',
    ];

    protected $casts = [
        'captured_at' => 'datetime',
        'size' => 'integer',
    ];

    public function complaint(): BelongsTo
    {
        return $this->belongsTo(Complaint::class);
    }
}
