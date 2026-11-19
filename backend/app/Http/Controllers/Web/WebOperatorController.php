<?php

namespace App\Http\Controllers\Web;

use App\Domain\Complaints\Services\ComplaintStateMachine;
use App\Http\Controllers\Controller;
use App\Models\Complaint;
use App\Models\ComplaintStatusHistory;
use App\Models\Department;
use App\Models\Disposition;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class WebOperatorController extends Controller
{
    public function index(Request $request): View
    {
        $activeTab = $request->input('tab', 'verify');

        // 1. Antrean Verifikasi Laporan Masuk
        $pendingVerification = Complaint::with(['category', 'user', 'citizen', 'photos', 'aiAssessments'])
            ->whereIn('status', ['SUBMITTED', 'VERIFICATION'])
            ->latest()
            ->get();

        // 2. Laporan Ditugaskan (Menunggu Petugas Mulai Lapangan)
        $dispatched = Complaint::with(['category', 'user', 'citizen', 'photos', 'latestDisposition.team.leader', 'latestDisposition.assignee.teams.leader', 'latestDisposition.department', 'aiAssessments'])
            ->where('status', 'DISPATCHED')
            ->latest()
            ->get();

        // 3. Laporan Sedang Dikerjakan Secara Fisik di Lapangan
        $inProgress = Complaint::with(['category', 'user', 'citizen', 'photos', 'latestDisposition.team.leader', 'latestDisposition.assignee.teams.leader', 'latestDisposition.department', 'aiAssessments'])
            ->whereIn('status', ['IN_PROGRESS', 'REVISION_REQUIRED'])
            ->latest()
            ->get();

        // 4. Menunggu Validasi Akhir Operator
        $waitingValidation = Complaint::with(['category', 'user', 'citizen', 'photos', 'latestDisposition.team.leader', 'latestDisposition.assignee.teams.leader', 'latestDisposition.department', 'aiAssessments'])
            ->where('status', 'WAITING_VERIFICATION')
            ->latest()
            ->get();

        // 5. Riwayat Arsip (Selesai & Ditolak)
        $completed = Complaint::with(['category', 'user', 'citizen', 'photos', 'latestDisposition.team.leader', 'latestDisposition.assignee.teams.leader', 'latestDisposition.department', 'statusHistories', 'aiAssessments'])
            ->whereIn('status', ['COMPLETED', 'CLOSED', 'REJECTED', 'CANCELLED'])
            ->latest()
            ->take(100)
            ->get();

        $fieldWorkers = User::where('role', 'PETUGAS')->get();

        // Fitur Pemantauan Eksekutif & Kinerja Dinas (seperti Bupati)
        $total = Complaint::count();
        $completedCount = Complaint::whereIn('status', ['COMPLETED', 'CLOSED'])->count();
        $dispatchedCount = Complaint::where('status', 'DISPATCHED')->count();
        $inProgressCount = Complaint::whereIn('status', ['IN_PROGRESS', 'REVISION_REQUIRED'])->count();
        $pendingCount = Complaint::whereIn('status', ['SUBMITTED', 'VERIFICATION'])->count();
        $rejectedCount = Complaint::where('status', 'REJECTED')->count();
        $completionRate = $total > 0 ? round(($completedCount / $total) * 100, 1) : 0;

        $summary = [
            'total'           => $total,
            'completed'       => $completedCount,
            'dispatched'      => $dispatchedCount,
            'in_progress'     => $inProgressCount,
            'pending'         => $pendingCount,
            'waiting_verification' => $waitingValidation->count(),
            'rejected'        => $rejectedCount,
            'completion_rate' => $completionRate,
            'critical_count'  => Complaint::where('priority_level', 'CRITICAL')->count(),
            'urgent_count'    => Complaint::whereIn('priority_level', ['HIGH', 'CRITICAL'])->count(),
            'petugas_count'   => $fieldWorkers->count(),
        ];

        $departments = Department::withCount([
            'dispositions as total_tasks',
            'dispositions as completed_tasks' => function ($q) {
                $q->where('status', 'COMPLETED');
            },
        ])->get();

        $categories = \App\Models\Category::withCount('complaints')->get();

        // Indikator Patokan Rata-Rata Waktu Penanganan (Benchmark SLA Hari Kerja)
        $avgPerformance = [
            'verification_days' => 0.5,
            'dispatch_days'     => 1.0,
            'work_days'         => 2.2,
            'validation_days'   => 0.4,
            'overall_days'      => 4.1,
            'sop_target_days'   => 5.0,
        ];

        // Seluruh Pengaduan untuk Rekap Dokumen & Ekspor Laporan Resmi Pemerintahan (PDF/Excel)
        $allComplaintsForReport = Complaint::with(['category', 'user', 'citizen', 'latestDisposition.team.leader', 'latestDisposition.department'])
            ->latest()
            ->get();

        $mapPoints = Complaint::with(['category', 'user', 'citizen', 'photos', 'latestDisposition.assignee', 'latestDisposition.department'])
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->latest()
            ->take(150)
            ->get()
            ->map(function ($c) {
                $photo = $c->photos->first();
                $afterPhoto = $c->photos->where('type', 'resolution_after')->first() ?? $c->photos->last();
                return [
                    'id'               => $c->id,
                    'public_code'      => $c->public_code,
                    'title'            => $c->title,
                    'description'      => $c->description ?? '-',
                    'address'          => $c->address ?? 'Wilayah Kabupaten',
                    'damage_type'      => $c->damage_type ?? 'minor',
                    'road_length'      => $c->road_length,
                    'road_condition'   => $c->road_condition,
                    'latitude'         => (float) $c->latitude,
                    'longitude'        => (float) $c->longitude,
                    'end_latitude'     => $c->end_latitude ? (float) $c->end_latitude : null,
                    'end_longitude'    => $c->end_longitude ? (float) $c->end_longitude : null,
                    'route_coordinates'=> $c->route_coordinates,
                    'status'           => $c->status,
                    'priority_level'   => $c->priority_level ?? 'LOW',
                    'severity_level'   => $c->severity_level,
                    'severity_text'    => $c->severity_text,
                    'category_name'    => $c->category?->name ?? 'Infrastruktur',
                    'reporter_name'    => $c->user?->name ?? $c->citizen?->name ?? 'Warga Terdaftar',
                    'reporter_phone'   => $c->user?->phone ?? $c->citizen?->phone ?? '081344697178',
                    'created_at_human' => $c->created_at?->diffForHumans() ?? '',
                    'photo_url'        => $photo ? ($photo->url ?? asset('storage/' . $photo->storage_key)) : null,
                    'photo_after_url'  => $afterPhoto && $afterPhoto->id !== ($photo?->id) ? ($afterPhoto->url ?? asset('storage/' . $afterPhoto->storage_key)) : null,
                    'department_name'  => $c->latestDisposition?->department?->name ?? 'Dinas Terkait',
                    'assignee_name'    => $c->latestDisposition?->assignee?->name ?? 'Pekerja Lapangan',
                    'instruction'      => $c->latestDisposition?->instruction ?? '-',
                ];
            });

        // Agregasi Sebaran Pengaduan per Wilayah Kecamatan & Desa (Kabupaten)
        $regionsDb = \App\Models\Region::orderBy('district')->get();
        $allComplaintsForStats = Complaint::all();

        $districtStats = [];
        foreach ($regionsDb as $r) {
            $distName = strtolower(str_replace(['kecamatan ', 'kec. '], '', $r->district));
            $villName = strtolower(str_replace(['desa ', 'kelurahan '], '', $r->village));

            $totalDistrict = 0;
            $completedDistrict = 0;
            $inProgressDistrict = 0;
            $waitingVerificationDistrict = 0;
            $dispatchedDistrict = 0;
            $pendingDistrict = 0;
            $rejectedDistrict = 0;

            foreach ($allComplaintsForStats as $cmp) {
                $addr = strtolower($cmp->address ?? '');
                if ($cmp->region_id === $r->id || str_contains($addr, $distName) || str_contains($addr, $villName)) {
                    $totalDistrict++;
                    if (in_array($cmp->status, ['COMPLETED', 'CLOSED'], true)) {
                        $completedDistrict++;
                    } elseif (in_array($cmp->status, ['IN_PROGRESS', 'REVISION_REQUIRED'], true)) {
                        $inProgressDistrict++;
                    } elseif ($cmp->status === 'WAITING_VERIFICATION') {
                        $waitingVerificationDistrict++;
                    } elseif ($cmp->status === 'DISPATCHED') {
                        $dispatchedDistrict++;
                    } elseif ($cmp->status === 'REJECTED') {
                        $rejectedDistrict++;
                    } else {
                        $pendingDistrict++; // SUBMITTED, VERIFICATION, VERIFIED
                    }
                }
            }

        $districtStats[] = [
            'district'             => $r->district,
            'village'              => $r->village,
            'latitude'             => (float) $r->latitude,
            'longitude'            => (float) $r->longitude,
            'total'                => $totalDistrict,
            'pending'              => $pendingDistrict,
            'dispatched'           => $dispatchedDistrict,
            'in_progress'          => $inProgressDistrict,
            'waiting_verification' => $waitingVerificationDistrict,
            'completed'            => $completedDistrict,
            'rejected'             => $rejectedDistrict,
        ];
    }

    $teams = \App\Models\Team::where('is_active', true)->with(['leader', 'members.user', 'department'])->get();
    $dispatchMode = \App\Models\SystemSetting::get('dispatch_mode', 'HYBRID');

    return view('operator.dashboard', compact(
        'activeTab',
        'pendingVerification',
        'dispatched',
        'inProgress',
        'waitingValidation',
        'completed',
        'fieldWorkers',
        'teams',
        'dispatchMode',
        'departments',
        'summary',
        'categories',
        'mapPoints',
        'districtStats',
        'avgPerformance',
        'allComplaintsForReport'
    ));
}

    /**
     * Operator melakukan verifikasi awal dan mendisposisikan ke pekerja lapangan / regu dengan deadline.
     */
    public function verifyAndDispatch(Request $request, int $id): RedirectResponse
    {
        $dispatchMode = \App\Models\SystemSetting::get('dispatch_mode', 'HYBRID');

        $rules = [
            'department_id' => 'required|exists:departments,id',
            'team_id'       => $dispatchMode === 'OPERATOR_ONLY' ? 'required|exists:teams,id' : 'nullable|exists:teams,id',
            'assigned_to'   => 'nullable|exists:users,id',
            'deadline_days' => 'nullable|integer|min:1|max:30',
            'deadline_date' => 'nullable|date',
            'instruction'   => 'required|string',
        ];

        $validated = $request->validate($rules);

        $complaint = Complaint::findOrFail($id);
        $oldStatus = $complaint->status;
        $newStatus = 'DISPATCHED';

        // Guard: pastikan complaint masih dalam status yang bisa di-dispatch
        $allowedFromStatuses = ['SUBMITTED', 'VERIFICATION', 'VERIFIED'];
        if (!in_array($oldStatus, $allowedFromStatuses, true)) {
            $statusLabel = match($oldStatus) {
                'DISPATCHED'           => 'sudah ditugaskan ke petugas',
                'IN_PROGRESS'          => 'sedang dikerjakan di lapangan',
                'WAITING_VERIFICATION' => 'menunggu validasi akhir',
                'COMPLETED'            => 'sudah selesai',
                'CLOSED'               => 'sudah ditutup',
                'REJECTED'             => 'sudah ditolak',
                default                => "berstatus {$oldStatus}",
            };
            return redirect()->route('operator.dashboard', ['tab' => 'verify'])
                ->with('error', "Laporan #{$complaint->public_code} tidak dapat ditugaskan karena {$statusLabel}. Silakan refresh halaman.");
        }

        ComplaintStateMachine::validateTransition($oldStatus, $newStatus);

        $deadline = !empty($validated['deadline_date'])
            ? Carbon::parse($validated['deadline_date'])
            : now()->addDays((int) ($validated['deadline_days'] ?? 3));

        DB::transaction(function () use ($complaint, $validated, $oldStatus, $newStatus, $deadline, $request) {
            $complaint->update(['status' => $newStatus]);

            $team = !empty($validated['team_id']) ? \App\Models\Team::with('members.user')->find($validated['team_id']) : null;
            $assignedTo = $validated['assigned_to'] ?? ($team?->leader_id ?? null);

            Disposition::create([
                'complaint_id'  => $complaint->id,
                'department_id' => $validated['department_id'],
                'team_id'       => $team?->id ?? null,
                'assigned_by'   => auth()->id(),
                'assigned_to'   => $assignedTo,
                'instruction'   => $validated['instruction'],
                'deadline'      => $deadline,
                'status'        => 'PENDING',
            ]);

            $targetName = $team ? "ke Regu {$team->name}" : ($assignedTo ? "ke Petugas " . (User::find($assignedTo)?->name ?? '') : "terbuka untuk klaim regu lapangan");
            ComplaintStatusHistory::create([
                'complaint_id' => $complaint->id,
                'from_status'  => $oldStatus,
                'to_status'    => $newStatus,
                'note'         => "Laporan diverifikasi valid oleh Operator & ditugaskan {$targetName} dengan tenggat: " . $deadline->translatedFormat('d F Y H:i') . ". Instruksi: {$validated['instruction']}",
                'changed_by'   => auth()->id(),
            ]);

            // Notifikasi ke seluruh personil dalam tim
            if ($team && $team->members->isNotEmpty()) {
                foreach ($team->members as $member) {
                    Notification::create([
                        'user_id'   => $member->user_id,
                        'type'      => 'FIELD_TASK_ASSIGNED',
                        'title'     => 'Tugas Lapangan untuk ' . $team->name,
                        'body'      => "Laporan #{$complaint->public_code} (\"{$complaint->title}\") ditugaskan ke regu Anda dengan batas waktu penanganan hingga " . $deadline->translatedFormat('d F Y, H:i') . " ({$deadline->diffForHumans()}).",
                        'data_json' => [
                            'complaint_id' => $complaint->id,
                            'public_code'  => $complaint->public_code,
                            'team_id'      => $team->id,
                            'team_name'    => $team->name,
                            'deadline'     => $deadline->toIso8601String(),
                        ],
                    ]);
                }
            } elseif (!empty($assignedTo)) {
                // Notifikasi ke Pekerja Lapangan individual
                Notification::create([
                    'user_id'   => $assignedTo,
                    'type'      => 'FIELD_TASK_ASSIGNED',
                    'title'     => 'Tugas Lapangan Baru',
                    'body'      => "Laporan #{$complaint->public_code} (\"{$complaint->title}\") telah ditugaskan. Silakan mengambil dan mengerjakan laporan ini dengan batas waktu penanganan hingga " . $deadline->translatedFormat('d F Y, H:i') . " ({$deadline->diffForHumans()}).",
                    'data_json' => [
                        'complaint_id' => $complaint->id,
                        'public_code'  => $complaint->public_code,
                        'deadline'     => $deadline->toIso8601String(),
                    ],
                ]);
            } else {
                // Open claim: notifikasi ke semua pekerja lapangan
                $allPetugas = User::where('role', 'PETUGAS')->get();
                foreach ($allPetugas as $p) {
                    Notification::create([
                        'user_id'   => $p->id,
                        'type'      => 'FIELD_TASK_ASSIGNED',
                        'title'     => 'Tugas Baru Tersedia untuk Regu',
                        'body'      => "Laporan baru #{$complaint->public_code} di {$complaint->address} terbuka untuk diambil oleh regu lapangan yang siap.",
                        'data_json' => [
                            'complaint_id' => $complaint->id,
                            'public_code'  => $complaint->public_code,
                            'deadline'     => $deadline->toIso8601String(),
                        ],
                    ]);
                }
            }

            // Notifikasi ke Pelapor
            if ($complaint->user_id) {
                $teamInfo = $team ? " ({$team->name})" : "";
                Notification::create([
                    'user_id'   => $complaint->user_id,
                    'type'      => 'COMPLAINT_DISPATCHED',
                    'title'     => 'Laporan Diverifikasi & Ditugaskan',
                    'body'      => "Laporan #{$complaint->public_code} telah diverifikasi oleh Operator dan ditugaskan ke tim teknis lapangan{$teamInfo} dengan estimasi selesai {$deadline->diffForHumans()}.",
                    'data_json' => ['complaint_id' => $complaint->id],
                ]);
            }
        });

        return redirect()->route('operator.dashboard', ['tab' => 'inprogress'])
            ->with('success', "Laporan #{$complaint->public_code} berhasil diverifikasi dan ditugaskan dengan deadline {$deadline->diffForHumans()}!");
    }

    /**
     * Operator menolak laporan (Hoax / Tidak Valid).
     */
    public function reject(Request $request, int $id): RedirectResponse
    {
        $validated = $request->validate([
            'reason' => 'required|string',
        ]);

        $complaint = Complaint::findOrFail($id);
        $oldStatus = $complaint->status;
        $newStatus = 'REJECTED';

        ComplaintStateMachine::validateTransition($oldStatus, $newStatus);

        DB::transaction(function () use ($complaint, $validated, $oldStatus, $newStatus) {
            $complaint->update(['status' => $newStatus]);

            ComplaintStatusHistory::create([
                'complaint_id' => $complaint->id,
                'from_status'  => $oldStatus,
                'to_status'    => $newStatus,
                'note'         => "Laporan ditolak oleh Operator: {$validated['reason']}",
                'changed_by'   => auth()->id(),
            ]);

            if ($complaint->user_id) {
                Notification::create([
                    'user_id'   => $complaint->user_id,
                    'type'      => 'COMPLAINT_REJECTED',
                    'title'     => 'Laporan Ditolak Operator',
                    'body'      => "Laporan #{$complaint->public_code} tidak dapat diproses. Alasan: {$validated['reason']}",
                    'data_json' => ['complaint_id' => $complaint->id],
                ]);
            }
        });

        return redirect()->route('operator.dashboard', ['tab' => 'archive'])
            ->with('success', "Laporan #{$complaint->public_code} berhasil ditolak dan dipindahkan ke Arsip.");
    }

    /**
     * Operator melakukan validasi akhir setelah pekerja lapangan menyelesaikan pekerjaan.
     */
    public function validateCompletion(Request $request, int $id): RedirectResponse
    {
        $validated = $request->validate([
            'decision' => 'required|in:COMPLETED,REVISION_REQUIRED',
            'notes'    => 'nullable|string',
        ]);

        $complaint = Complaint::findOrFail($id);
        $oldStatus = $complaint->status;
        $newStatus = $validated['decision'];

        ComplaintStateMachine::validateTransition($oldStatus, $newStatus);

        DB::transaction(function () use ($complaint, $validated, $oldStatus, $newStatus) {
            $complaint->update(['status' => $newStatus]);

            if ($complaint->latestDisposition) {
                $complaint->latestDisposition->update([
                    'status' => $newStatus === 'COMPLETED' ? 'COMPLETED' : 'REVISION_REQUIRED',
                ]);
            }

            $note = $validated['notes'] ?? ($newStatus === 'COMPLETED'
                ? 'Operator memvalidasi pekerjaan lapangan telah selesai dan sesuai standar kelayakan teknis.'
                : 'Pekerjaan belum memenuhi standar dan dikembalikan ke petugas untuk revisi.');

            ComplaintStatusHistory::create([
                'complaint_id' => $complaint->id,
                'from_status'  => $oldStatus,
                'to_status'    => $newStatus,
                'note'         => $note,
                'changed_by'   => auth()->id(),
            ]);

            // Notifikasi ke Pelapor
            if ($complaint->user_id) {
                if ($newStatus === 'COMPLETED') {
                    Notification::create([
                        'user_id'   => $complaint->user_id,
                        'type'      => 'COMPLAINT_COMPLETED',
                        'title'     => 'Laporan Anda Telah SELESAI!',
                        'body'      => "Laporan #{$complaint->public_code} \"{$complaint->title}\" telah selesai ditangani dan diverifikasi tuntas oleh Operator Dinas.",
                        'data_json' => ['complaint_id' => $complaint->id],
                    ]);
                }
            }

            // Notifikasi ke Pekerja Lapangan jika butuh revisi
            if ($newStatus === 'REVISION_REQUIRED' && $complaint->latestDisposition?->assigned_to) {
                Notification::create([
                    'user_id'   => $complaint->latestDisposition->assigned_to,
                    'type'      => 'TASK_REVISION_REQUIRED',
                    'title'     => 'Perbaikan Memerlukan Revisi',
                    'body'      => "Operator meminta revisi perbaikan untuk laporan #{$complaint->public_code}: {$note}",
                    'data_json' => ['complaint_id' => $complaint->id],
                ]);
            }
        });

        $msg = $newStatus === 'COMPLETED'
            ? "Laporan #{$complaint->public_code} berhasil divalidasi dan dinyatakan SELESAI!"
            : "Laporan #{$complaint->public_code} dikembalikan ke Pekerja Lapangan untuk REVISI.";

        return back()->with('success', $msg);
    }

    /**
     * Operator menempatkan atau mengubah penugasan regu pada tab Ditugaskan.
     */
    public function assignOrChangeTeam(Request $request, int $id): RedirectResponse
    {
        $dispatchMode = \App\Models\SystemSetting::get('dispatch_mode', 'HYBRID');

        if ($dispatchMode === 'SELF_CLAIM_ONLY') {
            return back()->with('error', "Mode penugasan saat ini adalah 'Klaim Mandiri Saja'. Penugasan langsung oleh Operator dinonaktifkan.");
        }

        $rules = [
            'team_id'       => $dispatchMode === 'OPERATOR_ONLY' ? 'required|exists:teams,id' : 'nullable|exists:teams,id',
            'instruction'   => 'nullable|string',
            'deadline_days' => 'nullable|integer|min:1|max:30',
        ];

        $validated = $request->validate($rules);
        $complaint = Complaint::findOrFail($id);

        if (!in_array($complaint->status, ['DISPATCHED', 'VERIFIED'], true)) {
            return back()->with('error', "Laporan #{$complaint->public_code} sedang berstatus {$complaint->status} dan tidak dapat diubah regunya.");
        }

        DB::transaction(function () use ($complaint, $validated) {
            $team = !empty($validated['team_id']) ? \App\Models\Team::with('members.user')->find($validated['team_id']) : null;
            $assignedTo = $team?->leader_id ?? null;
            $instruction = !empty($validated['instruction']) ? $validated['instruction'] : ($complaint->latestDisposition?->instruction ?? 'Lakukan penanganan fisik infrastruktur sesuai standar teknis.');

            $deadline = !empty($validated['deadline_days'])
                ? now()->addDays((int) $validated['deadline_days'])
                : ($complaint->latestDisposition?->deadline ?? now()->addDays(3));

            $disp = $complaint->latestDisposition;
            if ($disp) {
                $disp->update([
                    'team_id'     => $team?->id,
                    'assigned_to' => $assignedTo,
                    'instruction' => $instruction,
                    'deadline'    => $deadline,
                ]);
            } else {
                Disposition::create([
                    'complaint_id'  => $complaint->id,
                    'department_id' => 1,
                    'team_id'       => $team?->id,
                    'assigned_by'   => auth()->id(),
                    'assigned_to'   => $assignedTo,
                    'instruction'   => $instruction,
                    'deadline'      => $deadline,
                    'status'        => 'PENDING',
                ]);
            }

            $targetName = $team ? "Regu {$team->name} [{$team->code}]" : "Terbuka untuk Klaim Mandiri";
            ComplaintStatusHistory::create([
                'complaint_id' => $complaint->id,
                'from_status'  => $complaint->status,
                'to_status'    => $complaint->status,
                'note'         => "Operator menempatkan/mengubah penugasan ke {$targetName}",
                'changed_by'   => auth()->id(),
            ]);

            // Notifikasi ke seluruh personil dalam tim yang ditugaskan
            if ($team && $team->members->isNotEmpty()) {
                foreach ($team->members as $member) {
                    Notification::create([
                        'user_id'   => $member->user_id,
                        'type'      => 'FIELD_TASK_ASSIGNED',
                        'title'     => 'Penugasan Regu Baru: ' . $team->name,
                        'body'      => "Operator telah menugaskan laporan #{$complaint->public_code} (\"{$complaint->title}\") ke regu Anda.",
                        'data_json' => [
                            'complaint_id' => $complaint->id,
                            'public_code'  => $complaint->public_code,
                            'team_id'      => $team->id,
                        ],
                    ]);
                }
            }
        });

        return redirect()->route('operator.dashboard', ['tab' => 'dispatched'])
            ->with('success', "Penugasan regu untuk laporan #{$complaint->public_code} berhasil diperbarui.");
    }
}
