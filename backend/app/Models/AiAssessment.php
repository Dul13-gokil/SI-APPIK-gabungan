<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AiAssessment extends Model
{
    use HasFactory;

    protected $fillable = [
        'complaint_id',
        'model',
        'task',
        'result_json',
        'confidence',
    ];

    protected $casts = [
        'result_json' => 'array',
        'confidence' => 'float',
    ];

    public function complaint(): BelongsTo
    {
        return $this->belongsTo(Complaint::class);
    }
}
