<?php

namespace App\Domain\Complaints\Services;

use App\Domain\Complaints\Exceptions\InvalidStatusTransitionException;
use App\Models\AuditLog;
use App\Models\Complaint;
use App\Models\ComplaintPhoto;
use App\Models\ComplaintStatusHistory;
use App\Models\Disposition;
use App\Models\Notification;
use App\Models\Team;
use App\Models\User;
use App\Services\ImageWatermarkService;
use Carbon\CarbonInterface;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

/**
 * Satu-satunya tempat mutasi pekerjaan lapangan.
 *
 * Web (Blade) dan API v1 memanggil method yang sama supaya aturan tidak bercabang
 * antar antarmuka. Dulu keduanya menyalin logika ini dan sudah menyimpang: web tidak
 * mengirim notifikasi ke pemilik laporan, tidak mencatat jumlah foto di audit, dan
 * kegagalan watermark membatalkan seluruh penyelesaian kerja.
 */
class WorkflowService
{
    public function __construct(
        private readonly FieldTaskAuthorizer $authorizer,
        private readonly PhotoRadiusGuard $radiusGuard,
    ) {
    }

    /**
     * Pengawas lapangan mengambil tugas dan mulai mengerjakan.
     */
    public function startWork(Complaint $complaint, ?User $worker, ?string $ip = null): void
    {
        $oldStatus = $complaint->status;

        if (!in_array($oldStatus, ['VERIFIED', 'DISPATCHED', 'REVISION_REQUIRED'], true)) {
            throw new InvalidStatusTransitionException(
                "Pekerjaan belum dapat dimulai. Status laporan saat ini: {$oldStatus} (Menunggu verifikasi operator)."
            );
        }

        // Team shield + kuota tugas aktif.
        $this->authorizer->assertCanStart($worker, $complaint);

        $newStatus = 'IN_PROGRESS';
        ComplaintStateMachine::validateTransition($oldStatus, $newStatus);

        DB::transaction(function () use ($complaint, $worker, $oldStatus, $newStatus, $ip) {
            $complaint->update(['status' => $newStatus]);

            $workerName = $worker?->name ?? 'Pengawas Lapangan';
            $workerTeam = $worker?->currentTeam();

            if ($complaint->latestDisposition) {
                $dispUpdate = ['status' => 'IN_PROGRESS'];
                if ($worker) {
                    $dispUpdate['assigned_to'] = $worker->id;
                }
                if (!$complaint->latestDisposition->team_id && $workerTeam) {
                    $dispUpdate['team_id'] = $workerTeam->id;
                }
                $complaint->latestDisposition->update($dispUpdate);
            }

            $teamStr = $workerTeam ? " dari {$workerTeam->name}" : '';
            ComplaintStatusHistory::create([
                'complaint_id' => $complaint->id,
                'from_status'  => $oldStatus,
                'to_status'    => $newStatus,
                'note'         => "Pengawas lapangan {$workerName}{$teamStr} telah mengambil laporan dan memulai proses perbaikan fisik di lokasi.",
                'changed_by'   => $worker?->id,
            ]);

            AuditLog::create([
                'actor_id'    => $worker?->id,
                'action'      => 'WORK_STARTED',
                'entity_type' => Complaint::class,
                'entity_id'   => $complaint->id,
                'before_json' => ['status' => $oldStatus],
                'after_json'  => ['status' => $newStatus, 'team_id' => $workerTeam?->id],
                'ip'          => $ip,
            ]);

            $this->notifyOwner(
                $complaint,
                'COMPLAINT_IN_PROGRESS',
                'Laporan Sedang Dikerjakan',
                "Pengawas Lapangan {$workerName}{$teamStr} telah mengambil laporan Anda (#{$complaint->public_code}: \"{$complaint->title}\") dan saat ini berstatus Dikerjakan untuk segera dilakukan perbaikan fisik di lokasi."
            );
        });
    }

    /**
     * Pengawas lapangan menyelesaikan pekerjaan dan mengunggah bukti.
     *
     * @param  array<int, UploadedFile>  $photos
     * @return bool  true bila AI menyatakan pekerjaan langsung selesai.
     */
    public function finishWork(
        Complaint $complaint,
        ?User $worker,
        string $note,
        array $photos,
        ?float $latitude,
        ?float $longitude,
        ?string $ip = null
    ): bool {
        $oldStatus = $complaint->status;
        $newStatus = 'WAITING_VERIFICATION';

        // Otorisasi lebih dulu: regu lain selalu 403, bukan bocor status 422.
        $this->authorizer->assertCanFinish($worker, $complaint);

        // Foto bukti wajib diambil di sekitar titik aduan.
        $this->radiusGuard->assertWithinRadius($complaint, $latitude, $longitude);

        ComplaintStateMachine::validateTransition($oldStatus, $newStatus);

        DB::transaction(function () use ($complaint, $worker, $photos, $note, $latitude, $longitude, $oldStatus, $newStatus, $ip) {
            $complaint->update(['status' => $newStatus]);

            if ($complaint->latestDisposition) {
                $complaint->latestDisposition->update(['status' => 'WAITING_VERIFICATION']);
            }

            $saved = 0;
            foreach ($photos as $photo) {
                if (!$photo instanceof UploadedFile || !$photo->isValid()) {
                    continue;
                }

                $path = $photo->store('resolutions', 'public');
                $storedAbsPath = Storage::disk('public')->path($path);

                // Watermark hanya penanda; kegagalannya tidak boleh membatalkan bukti kerja.
                try {
                    app(ImageWatermarkService::class)->applyFieldWatermark(
                        $storedAbsPath,
                        (string) $complaint->public_code,
                        $complaint->latitude ? (float) $complaint->latitude : null,
                        $complaint->longitude ? (float) $complaint->longitude : null,
                        $worker?->name
                    );
                } catch (\Throwable $e) {
                    Log::warning('Gagal menerapkan watermark pada foto bukti selesai: ' . $e->getMessage());
                }

                $realPath = is_file($storedAbsPath) ? $storedAbsPath : $photo->getRealPath();
                // Foto hanya disimpan di disk; jangan menyalin base64 ke database lagi.
                ComplaintPhoto::create([
                    'complaint_id' => $complaint->id,
                    'storage_key'  => $path,
                    'url'          => null,
                    'caption'      => 'Bukti Pekerjaan Selesai (Pengawas Lapangan)',
                    'type'         => 'resolution_after',
                    'mime'         => $photo->getClientMimeType(),
                    'size'         => $photo->getSize(),
                    'checksum'     => $realPath ? md5_file($realPath) : null,
                    'captured_at'  => now(),
                ]);
                $saved++;
            }

            ComplaintStatusHistory::create([
                'complaint_id' => $complaint->id,
                'from_status'  => $oldStatus,
                'to_status'    => $newStatus,
                'note'         => "Pekerjaan lapangan selesai: {$note}. Menunggu validasi akhir Operator.",
                'changed_by'   => $worker?->id,
            ]);

            AuditLog::create([
                'actor_id'    => $worker?->id,
                'action'      => 'WORK_FINISHED',
                'entity_type' => Complaint::class,
                'entity_id'   => $complaint->id,
                'before_json' => ['status' => $oldStatus],
                'after_json'  => [
                    'status'      => $newStatus,
                    'photo_count' => $saved,
                    'latitude'    => $latitude,
                    'longitude'   => $longitude,
                ],
                'ip'          => $ip,
            ]);

            $this->notifyOwner(
                $complaint,
                'COMPLAINT_WAITING_VERIFICATION',
                'Pekerjaan Lapangan Selesai!',
                "Pengawas lapangan telah menyelesaikan perbaikan untuk laporan #{$complaint->public_code}. Saat ini menunggu verifikasi & validasi akhir oleh Operator."
            );
        });

        // Keputusan rapat: AI memvalidasi hasil kerja. Bila kerusakan tidak lagi terdeteksi,
        // laporan langsung COMPLETED; bila ragu tetap WAITING_VERIFICATION untuk operator.
        return app(CompletionValidationService::class)->validate($complaint->fresh(), $worker?->id);
    }

    /**
     * Operator menugaskan laporan ke dinas/regu/petugas.
     */
    public function dispatch(
        Complaint $complaint,
        ?User $actor,
        int $departmentId,
        ?int $teamId,
        ?int $assignedTo,
        string $instruction,
        ?CarbonInterface $deadline = null,
        ?string $ip = null
    ): void {
        $oldStatus = $complaint->status;
        $newStatus = 'DISPATCHED';

        ComplaintStateMachine::validateTransition($oldStatus, $newStatus);

        $deadline ??= now()->addDays(3);

        DB::transaction(function () use ($complaint, $actor, $departmentId, $teamId, $assignedTo, $instruction, $deadline, $oldStatus, $newStatus, $ip) {
            $complaint->update(['status' => $newStatus]);

            $team = $teamId ? Team::with('members.user')->find($teamId) : null;
            $assignee = $assignedTo ?? $team?->leader_id;
            $dueText = $deadline->translatedFormat('d F Y H:i');

            Disposition::create([
                'complaint_id'  => $complaint->id,
                'department_id' => $departmentId,
                'team_id'       => $team?->id,
                'assigned_by'   => $actor?->id,
                'assigned_to'   => $assignee,
                'instruction'   => $instruction,
                'deadline'      => $deadline,
                'status'        => 'PENDING',
            ]);

            $targetName = $team
                ? "Regu {$team->name}"
                : ($assignee ? ('Petugas ' . (User::find($assignee)?->name ?? $assignee)) : 'terbuka untuk klaim regu lapangan');

            ComplaintStatusHistory::create([
                'complaint_id' => $complaint->id,
                'from_status'  => $oldStatus,
                'to_status'    => $newStatus,
                'note'         => "Laporan diverifikasi valid dan ditugaskan ke {$targetName} dengan tenggat {$dueText}. Instruksi: {$instruction}",
                'changed_by'   => $actor?->id,
            ]);

            AuditLog::create([
                'actor_id'    => $actor?->id,
                'action'      => 'DISPOSITION',
                'entity_type' => Complaint::class,
                'entity_id'   => $complaint->id,
                'before_json' => ['status' => $oldStatus],
                'after_json'  => ['status' => $newStatus, 'department_id' => $departmentId, 'team_id' => $team?->id],
                'ip'          => $ip,
            ]);

            $teamInfo = $team ? " ({$team->name})" : '';
            $this->notifyOwner(
                $complaint,
                'COMPLAINT_DISPATCHED',
                'Laporan Diverifikasi & Ditugaskan',
                "Laporan #{$complaint->public_code} telah diverifikasi oleh Operator dan ditugaskan ke tim teknis lapangan{$teamInfo} dengan estimasi selesai {$deadline->diffForHumans()}."
            );

            // Penerima tugas: seluruh anggota regu, petugas tunggal, atau semua petugas
            // ketika penugasan terbuka untuk klaim (mode CLAIM). Dulu jalur API tidak
            // menyiarkan tugas klaim sehingga tidak ada petugas yang diberi tahu.
            $recipients = $team && $team->members->isNotEmpty()
                ? $team->members->pluck('user_id')->all()
                : ($assignee ? [$assignee] : User::where('role', 'PETUGAS')->pluck('id')->all());

            foreach (array_unique($recipients) as $userId) {
                Notification::create([
                    'user_id'   => $userId,
                    'type'      => 'FIELD_TASK_ASSIGNED',
                    'title'     => $team ? 'Tugas Lapangan untuk ' . $team->name : 'Tugas Lapangan Baru',
                    'body'      => "Laporan #{$complaint->public_code} (\"{$complaint->title}\") ditugaskan dengan batas waktu hingga {$dueText} ({$deadline->diffForHumans()}).",
                    'data_json' => [
                        'complaint_id'    => $complaint->id,
                        'public_code'     => $complaint->public_code,
                        'complaint_title' => $complaint->title,
                        'address'         => $complaint->address,
                        'instruction'     => $instruction,
                        'team_id'         => $team?->id,
                        'team_name'       => $team?->name,
                        'deadline'        => $deadline->toIso8601String(),
                    ],
                ]);
            }
        });
    }

    /**
     * Operator menolak laporan (tidak valid / hoax).
     */
    public function reject(Complaint $complaint, ?User $actor, string $reason, ?string $ip = null): void
    {
        $oldStatus = $complaint->status;
        $newStatus = 'REJECTED';

        ComplaintStateMachine::validateTransition($oldStatus, $newStatus);

        DB::transaction(function () use ($complaint, $actor, $reason, $oldStatus, $newStatus, $ip) {
            $complaint->update(['status' => $newStatus]);

            ComplaintStatusHistory::create([
                'complaint_id' => $complaint->id,
                'from_status'  => $oldStatus,
                'to_status'    => $newStatus,
                'note'         => "Laporan ditolak oleh Operator: {$reason}",
                'changed_by'   => $actor?->id,
            ]);

            // Aturan audit: seluruh mutasi status wajib berjejak. Jalur web sebelumnya
            // tidak menulis audit sama sekali untuk penolakan.
            AuditLog::create([
                'actor_id'    => $actor?->id,
                'action'      => 'REJECTED',
                'entity_type' => Complaint::class,
                'entity_id'   => $complaint->id,
                'before_json' => ['status' => $oldStatus],
                'after_json'  => ['status' => $newStatus, 'reason' => $reason],
                'ip'          => $ip,
            ]);

            $this->notifyOwner(
                $complaint,
                'COMPLAINT_REJECTED',
                'Laporan Tidak Dapat Diproses',
                "Laporan #{$complaint->public_code} \"{$complaint->title}\" tidak dapat diproses. Alasan: {$reason}"
            );
        });
    }

    /**
     * Operator memvalidasi hasil kerja lapangan: selesai atau minta revisi.
     */
    public function decideCompletion(Complaint $complaint, ?User $actor, string $decision, ?string $notes = null, ?string $ip = null): void
    {
        $oldStatus = $complaint->status;
        $newStatus = $decision;

        ComplaintStateMachine::validateTransition($oldStatus, $newStatus);

        DB::transaction(function () use ($complaint, $actor, $decision, $notes, $oldStatus, $newStatus, $ip) {
            $complaint->update(['status' => $newStatus]);

            $complaint->latestDisposition?->update([
                'status' => $newStatus === 'COMPLETED' ? 'COMPLETED' : 'REVISION_REQUIRED',
            ]);

            $note = $notes ?? ($newStatus === 'COMPLETED'
                ? 'Operator memvalidasi pekerjaan lapangan telah selesai dan sesuai standar kelayakan teknis.'
                : 'Pekerjaan belum memenuhi standar dan dikembalikan ke petugas untuk revisi.');

            ComplaintStatusHistory::create([
                'complaint_id' => $complaint->id,
                'from_status'  => $oldStatus,
                'to_status'    => $newStatus,
                'note'         => $note,
                'changed_by'   => $actor?->id,
            ]);

            AuditLog::create([
                'actor_id'    => $actor?->id,
                'action'      => 'COMPLETION_DECIDED',
                'entity_type' => Complaint::class,
                'entity_id'   => $complaint->id,
                'before_json' => ['status' => $oldStatus],
                'after_json'  => ['status' => $newStatus, 'note' => $note],
                'ip'          => $ip,
            ]);

            if ($newStatus === 'COMPLETED') {
                $this->notifyOwner(
                    $complaint,
                    'COMPLAINT_COMPLETED',
                    'Laporan Anda Telah Selesai Ditangani!',
                    "Laporan #{$complaint->public_code} \"{$complaint->title}\" telah selesai dikerjakan dan diverifikasi tuntas oleh Operator Dinas. Terima kasih atas kepedulian Anda!"
                );

                return;
            }

            // Revisi: pelapor tetap diberi tahu, dan petugas pemegang tugas WAJIB diberi tahu
            // karena hanya dia yang bisa memperbaiki. Jalur API dulu hanya memberi tahu pelapor,
            // sehingga petugas di aplikasi mobile tidak pernah tahu pekerjaannya dikembalikan.
            $this->notifyOwner(
                $complaint,
                'COMPLAINT_REVISION',
                'Proses Perbaikan Memerlukan Revisi',
                "Operator meminta revisi perbaikan untuk laporan #{$complaint->public_code}: {$note}"
            );

            $workerId = $complaint->latestDisposition?->assigned_to;
            if ($workerId) {
                Notification::create([
                    'user_id'   => $workerId,
                    'type'      => 'TASK_REVISION_REQUIRED',
                    'title'     => 'Perbaikan Memerlukan Revisi',
                    'body'      => "Operator meminta revisi perbaikan untuk laporan #{$complaint->public_code}: {$note}",
                    'data_json' => ['complaint_id' => $complaint->id],
                ]);
            }
        });
    }

    /**
     * Kumpulkan berkas bukti dari dua nama field yang dipakai klien (`photos[]` dan `photo`).
     *
     * @return array<int, UploadedFile>
     */
    public static function photosFrom(Request $request): array
    {
        $photos = [];

        if ($request->hasFile('photos')) {
            $list = $request->file('photos');
            foreach (is_array($list) ? $list : [$list] as $photo) {
                if ($photo instanceof UploadedFile && $photo->isValid()) {
                    $photos[] = $photo;
                }
            }
        }

        if ($request->hasFile('photo')) {
            $photo = $request->file('photo');
            if ($photo instanceof UploadedFile && $photo->isValid()) {
                array_unshift($photos, $photo);
            }
        }

        return $photos;
    }

    private function notifyOwner(Complaint $complaint, string $type, string $title, string $body): void
    {
        if (!$complaint->user_id) {
            return;
        }

        Notification::create([
            'user_id'   => $complaint->user_id,
            'type'      => $type,
            'title'     => $title,
            'body'      => $body,
            'data_json' => [
                'complaint_id'    => $complaint->id,
                'public_code'     => $complaint->public_code,
                'complaint_title' => $complaint->title,
            ],
        ]);
    }
}
