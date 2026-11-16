<?php

namespace App\Http\Controllers\Api\v1;

use App\Domain\Complaints\Services\ComplaintStateMachine;
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
     * Petugas Lapangan mulai pengerjaan laporan.
     * Hanya diizinkan jika status sudah VERIFIED atau DISPATCHED.
     */
    public function startWork(Request $request, int $id): JsonResponse
    {
        $complaint = Complaint::findOrFail($id);
        $oldStatus = $complaint->status;

        if (!in_array($oldStatus, ['VERIFIED', 'DISPATCHED', 'REVISION_REQUIRED'], true)) {
            return ApiResponse::error("Pekerjaan belum dapat dimulai. Status laporan saat ini: {$oldStatus} (Menunggu verifikasi operator).", 422);
        }

        // Validasi Mode Penugasan Sistem (OPERATOR_ONLY, HYBRID, SELF_CLAIM_ONLY)
        $dispatchMode = \App\Models\SystemSetting::get('dispatch_mode', 'HYBRID');
        $worker       = $request->user();
        $workerTeam   = $worker?->currentTeam();
        $workerTeamId = $workerTeam?->id;
        $latestDisp   = $complaint->latestDisposition;

        if ($dispatchMode === 'OPERATOR_ONLY') {
            if (!$latestDisp || (!$latestDisp->team_id && !$latestDisp->assigned_to)) {
                return ApiResponse::error("Mode penugasan saat ini adalah 'Operator Saja' (Komando Terpusat). Laporan ini wajib ditunjuk regunya oleh Operator terlebih dahulu sebelum dapat dikerjakan.", 403);
            }
            if ($latestDisp->team_id && $latestDisp->team_id !== $workerTeamId) {
                $assignedTeamName = $latestDisp->team?->name ?? 'Regu lain';
                return ApiResponse::error("Laporan ini telah ditugaskan khusus untuk {$assignedTeamName} oleh Operator. Regu Anda tidak berhak mengambil tugas ini.", 403);
            }
            if (!$latestDisp->team_id && $latestDisp->assigned_to && $latestDisp->assigned_to !== $worker?->id) {
                return ApiResponse::error("Laporan ini ditugaskan khusus untuk petugas lain oleh Operator.", 403);
            }
        } else {
            // HYBRID atau SELF_CLAIM_ONLY: jika sudah ditugaskan ke regu tertentu atau sedang revisi
            if ($latestDisp && $latestDisp->team_id && $latestDisp->team_id !== $workerTeamId) {
                $assignedTeamName = $latestDisp->team?->name ?? 'Regu lain';
                return ApiResponse::error("Laporan ini telah ditugaskan khusus untuk {$assignedTeamName}. Regu Anda tidak dapat mengambil tugas ini.", 403);
            }
        }

        $newStatus = 'IN_PROGRESS';
        ComplaintStateMachine::validateTransition($oldStatus, $newStatus);

        DB::transaction(function () use ($complaint, $oldStatus, $newStatus, $request) {
            $complaint->update(['status' => $newStatus]);
            $worker = $request->user();
            $workerName = $worker?->name ?? 'Petugas Lapangan';
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
                'note'         => "Petugas lapangan {$workerName}{$teamStr} telah mengambil laporan dan memulai proses perbaikan fisik di lokasi.",
                'changed_by'   => $worker?->id,
            ]);

            $this->notifyComplaintOwner(
                $complaint,
                'COMPLAINT_IN_PROGRESS',
                'Laporan Sedang Dikerjakan',
                "Petugas Lapangan {$workerName}{$teamStr} telah mengambil laporan Anda (#{$complaint->public_code}: \"{$complaint->title}\") dan saat ini berstatus Dikerjakan untuk segera dilakukan perbaikan fisik di lokasi."
            );
        });

        return ApiResponse::success(new ComplaintResource($complaint->fresh()), 'Pekerjaan berhasil dimulai');
    }

    /**
     * Petugas Lapangan menyelesaikan pekerjaan dan mengunggah bukti selesai.
     */
    public function finishWork(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'note'     => 'required|string',
            'photo'    => 'nullable|file|image|max:10240',
            'photos.*' => 'nullable|file|image|max:10240',
        ]);

        $complaint = Complaint::findOrFail($id);
        $oldStatus = $complaint->status;
        $newStatus = 'WAITING_VERIFICATION';

        ComplaintStateMachine::validateTransition($oldStatus, $newStatus);

        // Validasi Otorisasi: Hanya regu/petugas yang ditugaskan yang berhak mengunggah bukti selesai
        $user = $request->user();
        $isInternalAdmin = $user && in_array($user->role, ['SUPERADMIN', 'ADMIN', 'OPERATOR'], true);

        if (!$isInternalAdmin) {
            $latestDisp    = $complaint->latestDisposition;
            $workerTeamIds = $user ? $user->teams()->pluck('teams.id')->toArray() : [];

            if ($latestDisp) {
                if ($latestDisp->team_id && !in_array($latestDisp->team_id, $workerTeamIds, false)) {
                    $assignedTeamName = $latestDisp->team?->name ?? 'Regu Lain';
                    return ApiResponse::error("Laporan ini dikerjakan oleh {$assignedTeamName}. Regu Anda tidak memiliki izin untuk mengunggah bukti pengerjaan laporan ini.", 403);
                }

                if (!$latestDisp->team_id && $latestDisp->assigned_to && $latestDisp->assigned_to !== $user?->id) {
                    return ApiResponse::error("Laporan ini ditugaskan khusus untuk petugas lain. Anda tidak memiliki izin untuk mengunggah bukti penyelesaian laporan ini.", 403);
                }
            }
        }

        DB::transaction(function () use ($complaint, $validated, $oldStatus, $newStatus, $request) {
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
                $realPath = $photo->getRealPath();
                $fileData = $realPath && file_exists($realPath) ? base64_encode(file_get_contents($realPath)) : null;
                $path = $photo->store('resolutions', 'public');
                ComplaintPhoto::create([
                    'complaint_id' => $complaint->id,
                    'storage_key'  => $path,
                    'url'          => null,
                    'file_data'    => $fileData,
                    'caption'      => 'Bukti Pekerjaan Selesai (Petugas Lapangan)',
                    'type'         => 'resolution_after',
                    'mime'         => $photo->getClientMimeType(),
                    'size'         => $photo->getSize(),
                    'checksum'     => $realPath ? md5_file($realPath) : null,
                    'captured_at'  => now(),
                ]);
            }

            ComplaintStatusHistory::create([
                'complaint_id' => $complaint->id,
                'from_status'  => $oldStatus,
                'to_status'    => $newStatus,
                'note'         => "Pekerjaan lapangan selesai: {$validated['note']}. Menunggu validasi akhir Operator.",
                'changed_by'   => $request->user()?->id,
            ]);

            $this->notifyComplaintOwner(
                $complaint,
                'COMPLAINT_WAITING_VERIFICATION',
                'Pekerjaan Lapangan Selesai!',
                "Petugas telah menyelesaikan perbaikan untuk laporan #{$complaint->public_code}. Saat ini menunggu verifikasi & validasi akhir oleh Operator."
            );
        });

        return ApiResponse::success(new ComplaintResource($complaint->fresh()), 'Bukti pengerjaan berhasil dikirim ke Operator untuk validasi akhir.');
    }

    /**
     * Operator melakukan validasi akhir atas hasil kerja petugas lapangan.
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

        DB::transaction(function () use ($complaint, $parent, $validated, $request) {
            $complaint->update([
                'status'              => 'CLOSED',
                'parent_complaint_id' => $parent->id,
            ]);

            ComplaintStatusHistory::create([
                'complaint_id' => $complaint->id,
                'from_status'  => $complaint->status,
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
