<?php

namespace App\Domain\Complaints\Services;

use App\Domain\AI\Services\AICVService;
use App\Models\AiAssessment;
use App\Models\AuditLog;
use App\Models\Complaint;
use App\Models\ComplaintStatusHistory;
use App\Models\Notification;
use App\Models\SystemSetting;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

/**
 * Validasi hasil kerja tanpa operator (keputusan rapat).
 *
 * Dua syarat wajib, keduanya harus lolos:
 *  1. Foto bukti diambil di dalam radius aduan (ditegakkan PhotoRadiusGuard saat unggah)
 *     dan sudah ber-watermark waktu + koordinat.
 *  2. AI tidak lagi mendeteksi kerusakan pada foto tersebut.
 *
 * Lolos -> COMPLETED otomatis. Gagal -> REVISION_REQUIRED otomatis dan petugas diberi
 * tahu. Tidak ada lagi status yang menunggu validasi manual operator.
 */
class CompletionValidationService
{
    /** Ambang bawaan; bisa diubah lewat system_settings.ai_completion_threshold tanpa deploy. */
    public const COMPLETE_THRESHOLD = 0.80;

    public function threshold(): float
    {
        return (float) SystemSetting::get('ai_completion_threshold', self::COMPLETE_THRESHOLD);
    }

    public function __construct(private readonly AICVService $aiService) {}

    /**
     * @return bool true bila laporan otomatis dinyatakan selesai oleh AI.
     */
    public function validate(Complaint $complaint, ?int $actorId = null): bool
    {
        $paths = $complaint->photos()
            ->where('type', 'resolution_after')
            ->pluck('storage_key')
            ->map(fn ($key) => Storage::disk('public')->path($key))
            ->filter(fn ($path) => is_file($path))
            ->values()
            ->all();

        if (empty($paths)) {
            $this->markRevision($complaint, $actorId, 'foto bukti pengerjaan tidak ditemukan. Unggah ulang foto dari lokasi.');

            return false;
        }

        try {
            $result = $this->aiService->classifyMultipleImages($paths);
        } catch (\Throwable $e) {
            Log::warning('Validasi AI hasil kerja gagal: ' . $e->getMessage());
            $this->markRevision($complaint, $actorId, 'pemeriksaan AI gagal dijalankan. Unggah ulang bukti pengerjaan.');

            return false;
        }

        $aiFallback = is_array($result) && (($result['category_label'] ?? '') === 'Foto Belum Terverifikasi');
        if (! is_array($result) || $aiFallback || ! isset($result['category_id'])) {
            $this->markRevision($complaint, $actorId, 'foto hasil kerja belum dapat dinilai AI. Unggah ulang bukti pengerjaan.');

            return false;
        }

        // Skor sederhana dan dapat dipertanggungjawabkan: kerusakan hilang = 1, masih ada = 0.
        $stillDamaged = ($result['is_damaged'] ?? true) === true
            && ((int) ($result['severity_level'] ?? 3) >= 2);
        $score = $stillDamaged ? 0.0 : 1.0;

        AiAssessment::create([
            'complaint_id' => $complaint->id,
            'model'        => (string) ($result['model_version'] ?? 'cv-spatial-opencv-v2.5'),
            'task'         => 'COMPLETION_VALIDATION',
            'result_json'  => $result,
            'confidence'   => $score,
        ]);

        if ($score < $this->threshold()) {
            $this->markRevision(
                $complaint,
                $actorId,
                'AI masih mendeteksi kerusakan pada foto hasil kerja. Perbaiki lalu ambil ulang foto di lokasi.'
            );

            return false;
        }

        $complaint->update([
            'status'                    => 'COMPLETED',
            'completion_verified_by_ai' => true,
        ]);

        $complaint->latestDisposition?->update(['status' => 'COMPLETED']);
        if ($complaint->latestDisposition) {
            $complaint->latestDisposition->status = 'COMPLETED';
        }

        // Fitur "log aktivitas" Super Admin perlu jejak validasi AI, bukan hanya riwayat status.
        AuditLog::create([
            'actor_id'    => $actorId,
            'action'      => 'COMPLETION_VALIDATED_BY_AI',
            'entity_type' => Complaint::class,
            'entity_id'   => $complaint->id,
            'before_json' => ['status' => 'WAITING_VERIFICATION'],
            'after_json'  => [
                'status'          => 'COMPLETED',
                'ai_score'        => $score,
                'threshold'       => $this->threshold(),
                'radius_m'        => $complaint->radius_m,
                'photo_in_radius' => true,
            ],
            'ip'          => request()->ip(),
        ]);

        ComplaintStatusHistory::create([
            'complaint_id' => $complaint->id,
            'from_status'  => 'WAITING_VERIFICATION',
            'to_status'    => 'COMPLETED',
            'note'         => 'Divalidasi otomatis: foto bukti berada dalam radius aduan dan AI tidak lagi mendeteksi kerusakan.',
            'changed_by'   => $actorId,
        ]);

        Notification::create([
            'user_id'   => $complaint->user_id,
            'type'      => 'COMPLAINT_COMPLETED',
            'title'     => 'Laporan Selesai (Validasi AI)',
            'body'      => "Laporan #{$complaint->public_code} dinyatakan selesai setelah foto hasil kerja lolos pemeriksaan AI.",
            'data_json' => [
                'complaint_id' => $complaint->id,
                'public_code'  => $complaint->public_code,
            ],
        ]);

        return true;
    }

    /**
     * Pekerjaan belum lolos: kembalikan langsung ke petugas tanpa menunggu operator.
     */
    private function markRevision(Complaint $complaint, ?int $actorId, string $reason): void
    {
        $complaint->update([
            'status'                    => 'REVISION_REQUIRED',
            'completion_verified_by_ai' => false,
        ]);

        $complaint->latestDisposition?->update(['status' => 'REVISION_REQUIRED']);

        ComplaintStatusHistory::create([
            'complaint_id' => $complaint->id,
            'from_status'  => 'WAITING_VERIFICATION',
            'to_status'    => 'REVISION_REQUIRED',
            'note'         => 'Dikembalikan otomatis oleh sistem: ' . $reason,
            'changed_by'   => $actorId,
        ]);

        AuditLog::create([
            'actor_id'    => $actorId,
            'action'      => 'COMPLETION_REJECTED_BY_AI',
            'entity_type' => Complaint::class,
            'entity_id'   => $complaint->id,
            'before_json' => ['status' => 'WAITING_VERIFICATION'],
            'after_json'  => ['status' => 'REVISION_REQUIRED', 'reason' => $reason],
            'ip'          => request()->ip(),
        ]);

        $workerId = $complaint->latestDisposition?->assigned_to;
        if ($workerId) {
            Notification::create([
                'user_id'   => $workerId,
                'type'      => 'TASK_REVISION_REQUIRED',
                'title'     => 'Perbaikan Perlu Diulang',
                'body'      => "Laporan #{$complaint->public_code}: {$reason}",
                'data_json' => ['complaint_id' => $complaint->id],
            ]);
        }
    }
}
