<?php

namespace App\Http\Controllers\Api\v1;

use App\Domain\Complaints\Services\ComplaintStateMachine;
use App\Domain\Complaints\Services\WorkflowService;
use App\Http\Controllers\Controller;
use App\Http\Resources\ApiResponse;
use App\Http\Resources\ComplaintResource;
use App\Models\AuditLog;
use App\Models\Complaint;
use App\Models\ComplaintStatusHistory;
use App\Models\Disposition;
use App\Models\Notification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
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

        if ($validated['decision'] === 'REJECTED') {
            app(WorkflowService::class)->reject(
                $complaint,
                $request->user(),
                $validated['notes'] ?? 'Tidak memenuhi kriteria verifikasi.',
                $request->ip()
            );

            return ApiResponse::success(new ComplaintResource($complaint->fresh()), 'Verifikasi berhasil disimpan');
        }

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

            // Penolakan ditangani WorkflowService::reject(); di sini hanya VERIFIED.
            $this->notifyComplaintOwner(
                $complaint,
                'COMPLAINT_VERIFIED',
                'Laporan Anda Terverifikasi!',
                "Laporan #{$complaint->public_code} \"{$complaint->title}\" telah diverifikasi dan akan segera ditindaklanjuti oleh tim teknis."
            );
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

        app(WorkflowService::class)->dispatch(
            $complaint,
            $request->user(),
            (int) $validated['department_id'],
            isset($validated['team_id']) ? (int) $validated['team_id'] : null,
            isset($validated['assigned_to']) ? (int) $validated['assigned_to'] : null,
            $validated['instruction'],
            isset($validated['deadline']) ? Carbon::parse($validated['deadline']) : null,
            $request->ip()
        );

        return ApiResponse::success(new ComplaintResource($complaint->fresh()), 'Laporan berhasil ditugaskan');
    }

    /**
     * Pekerja Lapangan mulai pengerjaan laporan.
     * Hanya diizinkan jika status sudah VERIFIED atau DISPATCHED.
     */
    public function startWork(Request $request, int $id): JsonResponse
    {
        $complaint = Complaint::findOrFail($id);

        app(WorkflowService::class)->startWork($complaint, $request->user(), $request->ip());

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

        $autoCompleted = app(WorkflowService::class)->finishWork(
            $complaint,
            $request->user(),
            $validated['note'],
            WorkflowService::photosFrom($request),
            isset($validated['latitude']) ? (float) $validated['latitude'] : null,
            isset($validated['longitude']) ? (float) $validated['longitude'] : null,
            $request->ip()
        );

        return ApiResponse::success(
            new ComplaintResource($complaint->fresh()),
            $autoCompleted
                ? 'Bukti diterima dan pekerjaan dinyatakan selesai otomatis oleh AI.'
                : 'Bukti diterima, tetapi AI masih mendeteksi kerusakan. Perbaikan dikembalikan untuk diulang.'
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

        app(WorkflowService::class)->decideCompletion(
            $complaint,
            $request->user(),
            $validated['decision'],
            $validated['notes'] ?? null,
            $request->ip()
        );

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

            // Aturan repo: setiap mutasi status wajib berjejak di audit_logs.
            AuditLog::create([
                'actor_id'    => $request->user()?->id,
                'action'      => 'STATUS_UPDATED',
                'entity_type' => Complaint::class,
                'entity_id'   => $complaint->id,
                'before_json' => ['status' => $oldStatus],
                'after_json'  => ['status' => $newStatus, 'note' => $validated['note'] ?? null],
                'ip'          => $request->ip(),
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
