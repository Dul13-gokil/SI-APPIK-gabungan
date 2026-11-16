<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Complaint extends Model
{
    use HasFactory;

    protected $fillable = [
        'public_code',
        'user_id',
        'category_id',
        'region_id',
        'title',
        'description',
        'address',
        'latitude',
        'longitude',
        'geom',
        'damage_type',
        'road_length',
        'road_condition',
        'end_latitude',
        'end_longitude',
        'route_coordinates',
        'status',
        'rejected_by_superadmin',
        'admin_rejection_reason',
        'admin_rejected_at',
        'admin_rejected_by',
        'priority_score',
        'priority_level',
        'ai_category',
        'ai_confidence',
        'is_anonymous',
        'parent_complaint_id',
    ];

    protected $casts = [
        'latitude' => 'float',
        'longitude' => 'float',
        'end_latitude' => 'float',
        'end_longitude' => 'float',
        'route_coordinates' => 'array',
        'priority_score' => 'float',
        'ai_confidence' => 'float',
        'is_anonymous' => 'boolean',
        'rejected_by_superadmin' => 'boolean',
        'admin_rejected_at' => 'datetime',
    ];

    protected $appends = [
        'severity_level',
        'severity_text',
        'severity_badge_label',
    ];

    protected static function booted(): void
    {
        static::addGlobalScope('excludeAdminRejected', function (\Illuminate\Database\Eloquent\Builder $builder) {
            $builder->where($builder->qualifyColumn('rejected_by_superadmin'), false);
        });
    }

    public function adminRejector(): BelongsTo
    {
        return $this->belongsTo(User::class, 'admin_rejected_by');
    }

    public function getSeverityLevelAttribute(): int
    {
        // 1. Cek dari assessment AI terbaru jika relation sudah dimuat
        if ($this->relationLoaded('aiAssessments') && $this->aiAssessments->isNotEmpty()) {
            $assessment = $this->aiAssessments->first();
            if ($assessment && !empty($assessment->result_json['severity_level'])) {
                return (int) $assessment->result_json['severity_level'];
            }
        }

        // 2. Petakan dari priority_score atau priority_level
        if ($this->priority_score !== null && $this->priority_score > 0) {
            return match (true) {
                $this->priority_score >= 80.0 => 5,
                $this->priority_score >= 60.0 => 4,
                $this->priority_score >= 40.0 => 3,
                $this->priority_score >= 20.0 => 2,
                default => 1,
            };
        }

        return match (strtoupper((string) $this->priority_level)) {
            'CRITICAL' => 5,
            'HIGH'     => 4,
            'MEDIUM'   => 3,
            'LOW'      => 2,
            default    => 2,
        };
    }

    public function getSeverityTextAttribute(): string
    {
        return match ($this->severity_level) {
            5 => 'Tingkat 5 (Kritis)',
            4 => 'Tingkat 4 (Berat)',
            3 => 'Tingkat 3 (Sedang)',
            2 => 'Tingkat 2 (Ringan)',
            default => 'Tingkat 1 (Sangat Ringan)',
        };
    }

    public function getSeverityBadgeLabelAttribute(): string
    {
        return 'Tingkat ' . $this->severity_level . '/5';
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function citizen(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function region(): BelongsTo
    {
        return $this->belongsTo(Region::class);
    }

    public function photos(): HasMany
    {
        return $this->hasMany(ComplaintPhoto::class);
    }

    public function statusHistories(): HasMany
    {
        return $this->hasMany(ComplaintStatusHistory::class)->orderBy('created_at', 'asc');
    }

    public function dispositions(): HasMany
    {
        return $this->hasMany(Disposition::class);
    }

    public function latestDisposition(): HasOne
    {
        return $this->hasOne(Disposition::class)->latestOfMany();
    }

    public function supports(): HasMany
    {
        return $this->hasMany(Support::class);
    }

    public function aiAssessments(): HasMany
    {
        return $this->hasMany(AiAssessment::class);
    }

    public function parentComplaint(): BelongsTo
    {
        return $this->belongsTo(Complaint::class, 'parent_complaint_id');
    }

    public function childComplaints(): HasMany
    {
        return $this->hasMany(Complaint::class, 'parent_complaint_id');
    }
}
