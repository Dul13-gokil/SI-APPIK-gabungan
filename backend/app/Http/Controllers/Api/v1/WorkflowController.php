<?php

namespace App\Http\Controllers\Api\v1;

use App\Domain\Complaints\Services\ComplaintStateMachine;
use App\Domain\Complaints\Services\FieldTaskAuthorizer;
use App\Http\Controllers\Controller;
use App\Http\Resources\ApiResponse;
use App\Http\Resources\ComplaintResource;
use App\Models\AuditLog;
use App\Models\Complaint;
use App\Models\ComplaintPhoto;
use App\Models\ComplaintStatusHistory;
use App\Models\Disposition;
use App\Models\Notification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class WorkflowController extends Controller
{
    // ─── Helper: Kirim notifikasi ke pemilik laporan ─────────────────────────
    private function notifyComplaintOwner(Complaint $complaint, string $type, string $title, string $body): void
    {
        if ($complaint->user_id) {
            Notification::create([
                'user_id'   => $complaint->user_id,
                'type'      => $type,
                'title'     => $title,
                'body'      => $body,
                'data_json' => [
                    'complaint_id'  => $complaint->id,
                    'public_code'   => $complaint->public_code,
                    'complaint_title' => $complaint->title,
                ],
            ]);
        }
    }

    /**
     * Verifikasi administratif laporan (Admin/Operator).
     */
    public function verify(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'decision'              => 'required|in:VERIFIED,REJECTED',
            'notes'                 => 'nullable|string',
            'corrected_category_id' => 'nullable|exists:categories,id',
        ]);

        $complaint = Complaint::findOrFail($id);
        $oldStatus = $complaint->status;
        $newStatus = $validated['decision'];

        ComplaintStateMachine::validateTransition($oldStatus, $newStatus);


        DB::transaction(function () use ($complaint, $validated, $oldStatus, $newStatus, $request) {
            $updateData = ['status' => $newStatus];
            if (!empty($validated['corrected_category_id'])) {
                $updateData['category_id'] = $validated['corrected_category_id'];
            }
            $complaint->update($updateData);

            ComplaintStatusHistory::create([
                'complaint_id' => $complaint->id,
                'from_status'  => $oldStatus,
                'to_status'    => $newStatus,
                'note'         => $validated['notes'] ?? ($newStatus === 'VERIFIED' ? 'Laporan telah diverifikasi oleh petugas.' : 'Laporan ditolak.'),
                'changed_by'   => $request->user()?->id,
            ]);

            AuditLog::create([
                'actor_id'    => $request->user()?->id,
                'action'      => 'VERIFY',
                'entity_type' => Complaint::class,
                'entity_id'   => $complaint->id,
                'before_json' => ['status' => $oldStatus],
                'after_json'  => ['status' => $newStatus, 'note' => $validated['notes'] ?? null],
                'ip'          => $request->ip(),
            ]);

            // Kirim notifikasi ke pemilik laporan
            if ($newStatus === 'VERIFIED') {
                $this->notifyComplaintOwner(
                    $complaint,
                    'COMPLAINT_VERIFIED',
                    'Laporan Anda Terverifikasi!',
                    "Laporan #{$complaint->public_code} \"{$complaint->title}\" telah diverifikasi dan akan segera ditindaklanjuti oleh tim teknis."
                );
            } elseif ($newStatus === 'REJECTED') {
                $this->notifyComplaintOwner(
                    $complaint,
                    'COMPLAINT_REJECTED',
                    'Laporan Tidak Dapat Diproses',
                    "Laporan #{$complaint->public_code} \"{$complaint->title}\" tidak dapat diproses. Alasan: " . ($validated['notes'] ?? 'Tidak memenuhi kriteria verifikasi.')
                );
            }
        });

        return ApiResponse::success(new ComplaintResource($complaint->fresh()), 'Verifikasi berhasil disimpan');
    }

    /**
     * Disposisi laporan ke dinas teknis (Admin/Bupati).
     */
    public function disposition(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'department_id' => 'required|exists:departments,id',
            'team_id'       => 'nullable|exists:teams,id',
            'assigned_to'   => 'nullable|exists:users,id',
            'instruction'   => 'required|string',
            'deadline'      => 'nullable|date',
        ]);

        $complaint = Complaint::findOrFail($id);
        $oldStatus = $complaint->status;
        $newStatus = 'DISPATCHED';

        ComplaintStateMachine::validateTransition($oldStatus, $newStatus);


        DB::transaction(function () use ($complaint, $validated, $oldStatus, $newStatus, $request) {
            $complaint->update(['status' => $newStatus]);

            $team = !empty($validated['team_id']) ? \App\Models\Team::with('members.user')->find($validated['team_id']) : null;
            $assignedTo = $validated['assigned_to'] ?? ($team?->leader_id ?? null);

            Disposition::create([
                'complaint_id'  => $complaint->id,
                'department_id' => $validated['department_id'],
                'team_id'       => $team?->id ?? null,
                'assigned_by'   => $request->user()?->id,
                'assigned_to'   => $assignedTo,
                'instruction'   => $validated['instruction'],
                'deadline'      => $validated['deadline'] ?? now()->addDays(3),
                'status'        => 'PENDING',
            ]);

            $targetName = $team ? "Regu {$team->name}" : ($assignedTo ? "Petugas ID: {$assignedTo}" : "Terbuka untuk Klaim Mandiri Regu");
            ComplaintStatusHistory::create([
                'complaint_id' => $complaint->id,
                'from_status'  => $oldStatus,
                'to_status'    => $newStatus,
                'note'         => "Laporan ditugaskan ke {$targetName}: {$validated['instruction']}",
                'changed_by'   => $request->user()?->id,
            ]);

            AuditLog::create([
                'actor_id'    => $request->user()?->id,
                'action'      => 'DISPOSITION',
                'entity_type' => Complaint::class,
                'entity_id'   => $complaint->id,
                'before_json' => ['status' => $oldStatus],
                'after_json'  => ['status' => $newStatus, 'department_id' => $validated['department_id'], 'team_id' => $team?->id],
                'ip'          => $request->ip(),
            ]);

            // Notifikasi ke pemilik laporan bahwa sudah diteruskan ke tim teknis
            $teamInfo = $team ? " ({$team->name})" : "";
            $this->notifyComplaintOwner(
                $complaint,
                'COMPLAINT_DISPATCHED',
                'Laporan Sedang Dalam Penanganan',
                "Laporan #{$complaint->public_code} \"{$complaint->title}\" telah diteruskan ke tim teknis lapangan{$teamInfo} dan segera dilakukan perbaikan fisik."
            );

            // Notifikasi ke seluruh personil dalam tim yang ditugaskan
            if ($team && $team->members->isNotEmpty()) {
                foreach ($team->members as $member) {
                    Notification::create([
                        'user_id'   => $member->user_id,
                        'type'      => 'FIELD_TASK_ASSIGNED',
                        'title'     => 'Tugas Baru untuk ' . $team->name,
                        'body'      => "Laporan #{$complaint->public_code} (\"{$complaint->title}\") telah ditugaskan ke regu Anda. Silakan koordinasikan dan kerjakan di lapangan.",
                        'data_json' => [
                            'complaint_id'    => $complaint->id,
                            'public_code'     => $complaint->public_code,
                            'complaint_title' => $complaint->title,
                            'team_id'         => $team->id,
                            'team_name'       => $team->name,
                            'address'         => $complaint->address,
                            'instruction'     => $validated['instruction'],
                        ],
                    ]);
                }
            } elseif (!empty($assignedTo)) {
                Notification::create([
                    'user_id'   => $assignedTo,
                    'type'      => 'FIELD_TASK_ASSIGNED',
                    'title'     => 'Tugas Lapangan Baru',
                    'body'      => "Laporan #{$complaint->public_code} (\"{$complaint->title}\") telah ditugaskan. Silakan mengambil dan mengerjakan laporan ini dengan batas waktu penanganan yang telah ditentukan.",
                    'data_json' => [
                        'complaint_id'    => $complaint->id,
                        'public_code'     => $complaint->public_code,
                        'complaint_title' => $complaint->title,
                        'address'         => $complaint->address,
                        'instruction'     => $validated['instruction'],
                    ],
                ]);
            }
        });

        return ApiResponse::success(new ComplaintResource($complaint->fresh()), 'Laporan berhasil ditugaskan');
    }

    /**
     * Pekerja Lapangan mulai pengerjaan laporan.
     * Hanya diizinkan jika status sudah VERIFIED atau DISPATCHED.
     */
    public function startWork(Request $request, int $id): JsonResponse
    {
        $complaint = Complaint::findOrFail($id);
        $oldStatus = $complaint->status;

        if (!in_array($oldStatus, ['VERIFIED', 'DISPATCHED', 'REVISION_REQUIRED'], true)) {
            return ApiResponse::error("Pekerjaan belum dapat dimulai. Status laporan saat ini: {$oldStatus} (Menunggu verifikasi operator).", 422);
        }

        // Team shield + kuota tugas aktif (satu sumber aturan untuk web dan API).
        $worker = $request->user();
        app(FieldTaskAuthorizer::class)->assertCanStart($worker, $complaint);

        $newStatus = 'IN_PROGRESS';
        ComplaintStateMachine::validateTransition($oldStatus, $newStatus);

        DB::transaction(function () use ($complaint, $oldStatus, $newStatus, $request) {
            $complaint->update(['status' => $newStatus]);
            $worker = $request->user();
            $workerName = $worker?->name ?? 'Pekerja Lapangan';
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

            $teamStr = $workerTeam ? " dari {$workerTeam->name}" : "";
            ComplaintStatusHistory::create([
                'complaint_id' => $complaint->id,
                'from_status'  => $oldStatus,
                'to_status'    => $newStatus,
                'note'         => "Pekerja lapangan {$workerName}{$teamStr} telah mengambil laporan dan memulai proses perbaikan fisik di lokasi.",
                'changed_by'   => $worker?->id,
            ]);

            AuditLog::create([
                'actor_id'    => $worker?->id,
                'action'      => 'WORK_STARTED',
                'entity_type' => Complaint::class,
                'entity_id'   => $complaint->id,
                'before_json' => ['status' => $oldStatus],
                'after_json'  => ['status' => $newStatus, 'team_id' => $workerTeam?->id],
                'ip'          => $request->ip(),
            ]);

            $this->notifyComplaintOwner(
                $complaint,
                'COMPLAINT_IN_PROGRESS',
                'Laporan Sedang Dikerjakan',
                "Pekerja Lapangan {$workerName}{$teamStr} telah mengambil laporan Anda (#{$complaint->public_code}: \"{$complaint->title}\") dan saat ini berstatus Dikerjakan untuk segera dilakukan perbaikan fisik di lokasi."
            );
        });

        return ApiResponse::success(new ComplaintResource($complaint->fresh()), 'Pekerjaan berhasil dimulai');
    }

    /**
     * Pekerja Lapangan menyelesaikan pekerjaan dan mengunggah bukti selesai.
     */
    public function finishWork(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'note'      => 'required|string',
            'photo'     => 'nullable|file|image|max:10240',
            'photos.*'  => 'nullable|file|image|max:10240',
            'latitude'  => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
        ]);

        $complaint = Complaint::findOrFail($id);
        $oldStatus = $complaint->status;
        $newStatus = 'WAITING_VERIFICATION';

        // Otorisasi lebih dulu: regu lain selalu 403, bukan bocor status 422.
        $user = $request->user();
        app(FieldTaskAuthorizer::class)->assertCanFinish($user, $complaint);

        // Foto bukti wajib diambil di sekitar titik aduan (keputusan rapat: radius).
        app(\App\Domain\Complaints\Services\PhotoRadiusGuard::class)->assertWithinRadius(
            $complaint,
            isset($validated['latitude']) ? (float) $validated['latitude'] : null,
            isset($validated['longitude']) ? (float) $validated['longitude'] : null
        );

        ComplaintStateMachine::validateTransition($oldStatus, $newStatus);


        DB::transaction(function () use ($complaint, $validated, $oldStatus, $newStatus, $request, $user) {
            $complaint->update(['status' => $newStatus]);

            if ($complaint->latestDisposition) {
                $complaint->latestDisposition->update(['status' => 'WAITING_VERIFICATION']);
            }

            // Simpan foto bukti pengerjaan
            $photos = [];
            if ($request->hasFile('photos')) {
                $pList = is_array($request->file('photos')) ? $request->file('photos') : [$request->file('photos')];
                foreach ($pList as $p) {
                    if ($p && $p->isValid()) $photos[] = $p;
                }
            }
            if ($request->hasFile('photo')) {
                $p = $request->file('photo');
                if ($p && $p->isValid() && !in_array($p, $photos, true)) {
                    array_unshift($photos, $p);
                }
            }

            foreach ($photos as $photo) {
                $path = $photo->store('resolutions', 'public');
                $storedAbsPath = \Illuminate\Support\Facades\Storage::disk('public')->path($path);
                try {
                    app(\App\Services\ImageWatermarkService::class)->applyFieldWatermark(
                        $storedAbsPath,
                        (string) $complaint->public_code,
                        $complaint->latitude ? (float) $complaint->latitude : null,
                        $complaint->longitude ? (float) $complaint->longitude : null,
                        $user?->name
                    );
                } catch (\Throwable $e) {
                    \Illuminate\Support\Facades\Log::warning('Gagal menerapkan watermark pada foto bukti selesai: ' . $e->getMessage());
                }
                $realPath = is_file($storedAbsPath) ? $storedAbsPath : $photo->getRealPath();
                // Foto hanya disimpan di disk; jangan menyalin base64 ke database lagi.
                $photoAttrs = [
                    'complaint_id' => $complaint->id,
                    'storage_key'  => $path,
                    'url'          => null,
                    'caption'      => 'Bukti Pekerjaan Selesai (Pekerja Lapangan)',
                    'type'         => 'resolution_after',
                    'mime'         => $photo->getClientMimeType(),
                    'size'         => $photo->getSize(),
                    'checksum'     => $realPath ? md5_file($realPath) : null,
                    'captured_at'  => now(),
                ];
                ComplaintPhoto::create($photoAttrs);
            }

            ComplaintStatusHistory::create([
                'complaint_id' => $complaint->id,
                'from_status'  => $oldStatus,
                'to_status'    => $newStatus,
                'note'         => "Pekerjaan lapangan selesai: {$validated['note']}. Menunggu validasi akhir Operator.",
                'changed_by'   => $request->user()?->id,
            ]);

            AuditLog::create([
                'actor_id'    => $request->user()?->id,
                'action'      => 'WORK_FINISHED',
                'entity_type' => Complaint::class,
                'entity_id'   => $complaint->id,
                'before_json' => ['status' => $oldStatus],
                'after_json'  => [
                    'status'      => $newStatus,
                    'photo_count' => count($photos),
                    'latitude'    => $validated['latitude'] ?? null,
                    'longitude'   => $validated['longitude'] ?? null,
                ],
                'ip'          => $request->ip(),
            ]);

            $this->notifyComplaintOwner(
                $complaint,
                'COMPLAINT_WAITING_VERIFICATION',
                'Pekerjaan Lapangan Selesai!',
                "Pekerja lapangan telah menyelesaikan perbaikan untuk laporan #{$complaint->public_code}. Saat ini menunggu verifikasi & validasi akhir oleh Operator."
            );
        });

        // Keputusan rapat: AI memvalidasi hasil kerja. Bila kerusakan tidak lagi terdeteksi,
        // laporan langsung COMPLETED; bila ragu tetap WAITING_VERIFICATION untuk operator.
        $autoCompleted = app(\App\Domain\Complaints\Services\CompletionValidationService::class)
            ->validate($complaint->fresh(), $request->user()?->id);

        return ApiResponse::success(
            new ComplaintResource($complaint->fresh()),
            $autoCompleted
                ? 'Bukti diterima dan pekerjaan dinyatakan selesai otomatis oleh AI.'
                : 'Bukti pengerjaan berhasil dikirim ke Operator untuk validasi akhir.'
        );
    }

    /**
     * Operator melakukan validasi akhir atas hasil kerja pekerja lapangan.
     */
    public function validateResolution(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'decision' => 'required|in:COMPLETED,REVISION_REQUIRED',
            'notes'    => 'nullable|string',
        ]);

        $complaint = Complaint::findOrFail($id);
        $oldStatus = $complaint->status;
        $newStatus = $validated['decision'];

        ComplaintStateMachine::validateTransition($oldStatus, $newStatus);


        DB::transaction(function () use ($complaint, $validated, $oldStatus, $newStatus, $request) {
            $complaint->update(['status' => $newStatus]);

            if ($complaint->latestDisposition) {
                $complaint->latestDisposition->update([
                    'status' => $newStatus === 'COMPLETED' ? 'COMPLETED' : 'REVISION_REQUIRED',
                ]);
            }

            $note = $validated['notes'] ?? ($newStatus === 'COMPLETED'
                ? 'Operator telah memvalidasi bahwa pekerjaan perbaikan lapangan telah tuntas dan sesuai standar.'
                : 'Pekerjaan belum memenuhi standar teknis dan perlu revisi.');

            ComplaintStatusHistory::create([
                'complaint_id' => $complaint->id,
                'from_status'  => $oldStatus,
                'to_status'    => $newStatus,
                'note'         => $note,
                'changed_by'   => $request->user()?->id,
            ]);

            if ($newStatus === 'COMPLETED') {
                $this->notifyComplaintOwner(
                    $complaint,
                    'COMPLAINT_COMPLETED',
                    'Laporan Anda Telah Selesai Ditangani!',
                    "Laporan #{$complaint->public_code} \"{$complaint->title}\" telah selesai dikerjakan dan diverifikasi tuntas oleh Operator Dinas. Terima kasih atas kepedulian Anda!"
                );
            } else {
                $this->notifyComplaintOwner(
                    $complaint,
                    'COMPLAINT_REVISION',
                    'Proses Perbaikan Memerlukan Revisi',
                    "Operator meminta revisi perbaikan untuk laporan #{$complaint->public_code}: {$note}"
                );
            }
        });

        return ApiResponse::success(new ComplaintResource($complaint->fresh()), 'Validasi akhir berhasil disimpan');
    }

    /**
     * Kirim bukti penyelesaian pekerjaan (Dinas/Petugas legacy endpoint).
     */
    public function submitResolution(Request $request, int $id): JsonResponse
    {
        return $this->finishWork($request, $id);
    }

    /**
     * Perbarui status penanganan secara langsung.
     */
    public function updateStatus(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'status' => 'required|string',
            'note'   => 'nullable|string',
        ]);

        $complaint = Complaint::findOrFail($id);
        $oldStatus = $complaint->status;
        $newStatus = $validated['status'];

        ComplaintStateMachine::validateTransition($oldStatus, $newStatus);


        DB::transaction(function () use ($complaint, $validated, $oldStatus, $newStatus, $request) {
            $complaint->update(['status' => $newStatus]);

            ComplaintStatusHistory::create([
                'complaint_id' => $complaint->id,
                'from_status'  => $oldStatus,
                'to_status'    => $newStatus,
                'note'         => $validated['note'] ?? "Status diperbarui menjadi {$newStatus}.",
                'changed_by'   => $request->user()?->id,
            ]);
        });

        return ApiResponse::success(new ComplaintResource($complaint->fresh()), 'Status berhasil diperbarui');
    }

    /**
     * Gabungkan laporan duplikat ke parent complaint (Admin).
     */
    public function merge(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'parent_complaint_id' => 'required|exists:complaints,id|different:id',
            'note'                => 'nullable|string',
        ]);

        $complaint = Complaint::findOrFail($id);
        $parent    = Complaint::findOrFail($validated['parent_complaint_id']);
        $fromStatus = $complaint->status;

        DB::transaction(function () use ($complaint, $parent, $validated, $request, $fromStatus) {
            $complaint->update([
                'status'              => 'CLOSED',
                'parent_complaint_id' => $parent->id,
            ]);

            ComplaintStatusHistory::create([
                'complaint_id' => $complaint->id,
                'from_status'  => $fromStatus,
                'to_status'    => 'CLOSED',
                'note'         => "Laporan ditutup dan digabungkan ke laporan induk ({$parent->public_code}). " . ($validated['note'] ?? ''),
                'changed_by'   => $request->user()?->id,
            ]);

            AuditLog::create([
                'actor_id'    => $request->user()?->id,
                'action'      => 'MERGE',
                'entity_type' => Complaint::class,
                'entity_id'   => $complaint->id,
                'before_json' => ['parent_complaint_id' => null],
                'after_json'  => ['parent_complaint_id' => $parent->id],
                'ip'          => $request->ip(),
            ]);

            // Notifikasi ke pemilik laporan duplikat
            $this->notifyComplaintOwner(
                $complaint,
                'COMPLAINT_MERGED',
                'Laporan Digabungkan',
                "Laporan #{$complaint->public_code} Anda telah digabungkan dengan laporan #{$parent->public_code} yang sudah ada sebelumnya dan akan ditangani bersama."
            );
        });

        return ApiResponse::success(new ComplaintResource($complaint->fresh()), 'Laporan berhasil digabungkan');
    }
}
