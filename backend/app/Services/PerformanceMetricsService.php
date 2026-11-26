<?php

namespace App\Services;

use App\Models\Complaint;
use App\Models\ComplaintRating;
use App\Models\ComplaintStatusHistory;
use App\Models\Disposition;
use App\Models\Team;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class PerformanceMetricsService
{
    /**
     * Metrik kinerja seluruh Regu Lapangan (untuk Operator, Kepala Daerah, Admin, dan Regu terkait).
     */
    public function getFieldTeamMetrics(?string $from = null, ?string $to = null, ?int $onlyTeamId = null): array
    {
        $teamsQuery = Team::with(['leader', 'members']);
        if ($onlyTeamId !== null) {
            $teamsQuery->where('id', $onlyTeamId);
        }
        $teams = $teamsQuery->orderBy('name')->get();

        $results = [];
        foreach ($teams as $team) {
            $dispQuery = Disposition::where('team_id', $team->id);
            if ($from) {
                $dispQuery->whereDate('created_at', '>=', $from);
            }
            if ($to) {
                $dispQuery->whereDate('created_at', '<=', $to);
            }

            $complaintIds = $dispQuery->pluck('complaint_id')->unique()->values();
            $complaints = Complaint::with(['category', 'rating', 'latestDisposition', 'statusHistories'])
                ->whereIn('id', $complaintIds)
                ->get();

            $total = $complaints->count();
            $completed = $complaints->filter(fn ($c) => in_array($c->status, ['COMPLETED', 'CLOSED'], true))->count();
            $inProgress = $complaints->filter(fn ($c) => in_array($c->status, ['IN_PROGRESS', 'REVISION_REQUIRED'], true))->count();
            $waiting = $complaints->filter(fn ($c) => $c->status === 'WAITING_VERIFICATION')->count();
            $dispatched = $complaints->filter(fn ($c) => $c->status === 'DISPATCHED')->count();

            [$avgResponseHours, $avgRepairHours, $revisionCount, $firstPassRate, $onTimeRate] = $this->computeTimingAndQuality($complaints);

            $ratings = $complaints->pluck('rating.rating')->filter();
            $avgRating = $ratings->isNotEmpty() ? round($ratings->avg(), 1) : null;

            $completionRate = $total > 0 ? round(($completed / $total) * 100, 1) : 0.0;
            $score = $this->computeCompositeScore($completionRate, $firstPassRate, $onTimeRate, $total);

            $results[] = [
                'team_id'            => $team->id,
                'team_name'          => $team->name,
                'team_code'          => $team->code ?? 'REGU',
                'district'           => $team->district ?? 'Kabupaten Kolaka Utara',
                'leader_name'        => $team->leader?->name ?? '-',
                'members_count'      => $team->members->count(),
                'total_tasks'        => $total,
                'dispatched'         => $dispatched,
                'in_progress'        => $inProgress,
                'waiting_validation' => $waiting,
                'completed'          => $completed,
                'active_quota'       => $dispatched + $inProgress,
                'completion_rate'    => $completionRate,
                'avg_response_hours' => $avgResponseHours,
                'avg_repair_hours'   => $avgRepairHours,
                'revision_count'     => $revisionCount,
                'first_pass_rate'    => $firstPassRate,
                'on_time_rate'       => $onTimeRate,
                'avg_rating'         => $avgRating,
                'ratings_count'      => $ratings->count(),
                'score'              => $score,
            ];
        }

        usort($results, fn ($a, $b) => $b['score'] <=> $a['score'] ?: $b['completed'] <=> $a['completed']);

        return $results;
    }

    /**
     * Metrik kinerja per Individu Pengawas Lapangan.
     */
    public function getFieldOfficerMetrics(?string $from = null, ?string $to = null, ?int $teamId = null, ?int $onlyUserId = null): array
    {
        $officersQuery = User::where('role', 'PETUGAS')->with('teams');
        if ($onlyUserId !== null) {
            $officersQuery->where('id', $onlyUserId);
        } elseif ($teamId !== null) {
            $officersQuery->whereHas('teams', fn ($q) => $q->where('teams.id', $teamId));
        }
        $officers = $officersQuery->orderBy('name')->get();

        $results = [];
        foreach ($officers as $officer) {
            $teamIds = $officer->teams->pluck('id')->all();

            $historyComplaintIds = ComplaintStatusHistory::where('changed_by', $officer->id)
                ->whereIn('to_status', ['IN_PROGRESS', 'WAITING_VERIFICATION'])
                ->when($from, fn ($q) => $q->whereDate('created_at', '>=', $from))
                ->when($to, fn ($q) => $q->whereDate('created_at', '<=', $to))
                ->pluck('complaint_id');

            $dispComplaintIds = Disposition::query()
                ->where(function ($q) use ($officer, $teamIds) {
                    $q->where('assigned_to', $officer->id);
                    if (!empty($teamIds)) {
                        $q->orWhereIn('team_id', $teamIds);
                    }
                })
                ->when($from, fn ($q) => $q->whereDate('created_at', '>=', $from))
                ->when($to, fn ($q) => $q->whereDate('created_at', '<=', $to))
                ->pluck('complaint_id');

            $complaintIds = $historyComplaintIds->merge($dispComplaintIds)->unique()->values();
            $complaints = Complaint::with(['category', 'rating', 'latestDisposition', 'statusHistories'])
                ->whereIn('id', $complaintIds)
                ->get();

            $total = $complaints->count();
            $completed = $complaints->filter(fn ($c) => in_array($c->status, ['COMPLETED', 'CLOSED'], true))->count();
            $inProgress = $complaints->filter(fn ($c) => in_array($c->status, ['IN_PROGRESS', 'REVISION_REQUIRED'], true))->count();
            $waiting = $complaints->filter(fn ($c) => $c->status === 'WAITING_VERIFICATION')->count();

            [$avgResponseHours, $avgRepairHours, $revisionCount, $firstPassRate, $onTimeRate] = $this->computeTimingAndQuality($complaints);

            $ratings = $complaints->pluck('rating.rating')->filter();
            $avgRating = $ratings->isNotEmpty() ? round($ratings->avg(), 1) : null;

            $completionRate = $total > 0 ? round(($completed / $total) * 100, 1) : 0.0;
            $score = $this->computeCompositeScore($completionRate, $firstPassRate, $onTimeRate, $total);

            $primaryTeam = $officer->teams->first();

            $results[] = [
                'user_id'            => $officer->id,
                'name'               => $officer->name,
                'email'              => $officer->email,
                'phone'              => $officer->phone ?? '-',
                'team_id'            => $primaryTeam?->id,
                'team_name'          => $primaryTeam?->name ?? 'Belum Tergabung Regu',
                'team_role'          => ($primaryTeam && $primaryTeam->leader_id === $officer->id) ? 'Ketua Regu' : 'Anggota Regu',
                'total_tasks'        => $total,
                'in_progress'        => $inProgress,
                'waiting_validation' => $waiting,
                'completed'          => $completed,
                'completion_rate'    => $completionRate,
                'avg_response_hours' => $avgResponseHours,
                'avg_repair_hours'   => $avgRepairHours,
                'revision_count'     => $revisionCount,
                'first_pass_rate'    => $firstPassRate,
                'on_time_rate'       => $onTimeRate,
                'avg_rating'         => $avgRating,
                'score'              => $score,
            ];
        }

        usort($results, fn ($a, $b) => $b['score'] <=> $a['score'] ?: $b['completed'] <=> $a['completed']);

        return $results;
    }

    /**
     * Metrik kinerja seluruh Operator PUPR (untuk Kepala Daerah & Super Admin).
     */
    public function getOperatorMetrics(?string $from = null, ?string $to = null): array
    {
        $operators = User::whereIn('role', ['OPERATOR', 'DINAS'])->orderBy('name')->get();
        $results = [];

        foreach ($operators as $op) {
            $histories = ComplaintStatusHistory::where('changed_by', $op->id)
                ->when($from, fn ($q) => $q->whereDate('created_at', '>=', $from))
                ->when($to, fn ($q) => $q->whereDate('created_at', '<=', $to))
                ->get();

            $verifiedCount = $histories->whereIn('to_status', ['VERIFIED', 'DISPATCHED'])->count();
            $rejectedCount = $histories->where('to_status', 'REJECTED')->count();
            $approvedCount = $histories->where('to_status', 'COMPLETED')->count();
            $revisionCount = $histories->where('to_status', 'REVISION_REQUIRED')->count();
            $totalActions  = $verifiedCount + $rejectedCount + $approvedCount + $revisionCount;

            // Rata-rata waktu verifikasi (SUBMITTED -> DISPATCHED/VERIFIED/REJECTED) & validasi (WAITING_VERIFICATION -> COMPLETED/REVISION_REQUIRED)
            $verifyDurations = [];
            $validateDurations = [];

            $touchedIds = $histories->pluck('complaint_id')->unique();
            if ($touchedIds->isNotEmpty()) {
                $allHistoriesByComplaint = ComplaintStatusHistory::whereIn('complaint_id', $touchedIds)
                    ->orderBy('created_at')
                    ->get()
                    ->groupBy('complaint_id');

                foreach ($allHistoriesByComplaint as $cHistories) {
                    $submittedAt = $cHistories->firstWhere('to_status', 'SUBMITTED')?->created_at;
                    $opVerify = $cHistories->first(fn ($h) => $h->changed_by === $op->id && in_array($h->to_status, ['VERIFIED', 'DISPATCHED', 'REJECTED'], true));
                    if ($submittedAt && $opVerify && $opVerify->created_at->gte($submittedAt)) {
                        $verifyDurations[] = $submittedAt->diffInMinutes($opVerify->created_at) / 60;
                    }

                    $waitingAt = $cHistories->last(fn ($h) => $h->to_status === 'WAITING_VERIFICATION')?->created_at;
                    $opValidate = $cHistories->last(fn ($h) => $h->changed_by === $op->id && in_array($h->to_status, ['COMPLETED', 'REVISION_REQUIRED'], true));
                    if ($waitingAt && $opValidate && $opValidate->created_at->gte($waitingAt)) {
                        $validateDurations[] = $waitingAt->diffInMinutes($opValidate->created_at) / 60;
                    }
                }
            }

            $avgVerifyHours = !empty($verifyDurations) ? round(array_sum($verifyDurations) / count($verifyDurations), 1) : null;
            $avgValidateHours = !empty($validateDurations) ? round(array_sum($validateDurations) / count($validateDurations), 1) : null;

            $totalValidated = $approvedCount + $revisionCount;
            $approvalRatio = $totalValidated > 0 ? round(($approvedCount / $totalValidated) * 100, 1) : 0.0;

            $results[] = [
                'user_id'            => $op->id,
                'name'               => $op->name,
                'email'              => $op->email,
                'is_active'          => (bool) ($op->is_active ?? true),
                'verified_dispatched'=> $verifiedCount,
                'rejected'           => $rejectedCount,
                'validated_approved' => $approvedCount,
                'validated_revision' => $revisionCount,
                'total_actions'      => $totalActions,
                'avg_verify_hours'   => $avgVerifyHours,
                'avg_validate_hours' => $avgValidateHours,
                'approval_ratio'     => $approvalRatio,
            ];
        }

        usort($results, fn ($a, $b) => $b['total_actions'] <=> $a['total_actions']);

        return $results;
    }

    /**
     * Panel Transparansi & Responsivitas Operator (khusus dilihat oleh Pengawas Lapangan).
     */
    public function getOperatorResponsivenessForTeam(?int $teamId, ?int $userId, ?string $from = null, ?string $to = null): array
    {
        $complaintIds = Disposition::query()
            ->when($teamId, fn ($q) => $q->where('team_id', $teamId))
            ->when(!$teamId && $userId, fn ($q) => $q->where('assigned_to', $userId))
            ->pluck('complaint_id')
            ->unique();

        $pendingValidation = Complaint::with(['category', 'statusHistories'])
            ->whereIn('id', $complaintIds)
            ->where('status', 'WAITING_VERIFICATION')
            ->latest('updated_at')
            ->get()
            ->map(function ($c) {
                $submittedValAt = $c->statusHistories->last(fn ($h) => $h->to_status === 'WAITING_VERIFICATION')?->created_at ?? $c->updated_at;
                $waitHours = $submittedValAt ? round($submittedValAt->diffInMinutes(now()) / 60, 1) : 0;
                return [
                    'id'          => $c->id,
                    'public_code' => $c->public_code,
                    'title'       => $c->title,
                    'category'    => $c->category?->name ?? 'Umum',
                    'submitted_at'=> $submittedValAt?->timezone('Asia/Makassar')->format('d M Y, H:i') . ' WITA',
                    'wait_hours'  => $waitHours,
                ];
            })
            ->values()
            ->all();

        $allHistories = ComplaintStatusHistory::whereIn('complaint_id', $complaintIds)
            ->when($from, fn ($q) => $q->whereDate('created_at', '>=', $from))
            ->when($to, fn ($q) => $q->whereDate('created_at', '<=', $to))
            ->orderBy('created_at')
            ->get()
            ->groupBy('complaint_id');

        $validateDurations = [];
        $approvedCount = 0;
        $revisionCount = 0;

        foreach ($allHistories as $cHist) {
            $waitingAt = null;
            foreach ($cHist as $h) {
                if ($h->to_status === 'WAITING_VERIFICATION') {
                    $waitingAt = $h->created_at;
                } elseif (in_array($h->to_status, ['COMPLETED', 'REVISION_REQUIRED'], true)) {
                    if ($h->to_status === 'COMPLETED') {
                        $approvedCount++;
                    } else {
                        $revisionCount++;
                    }
                    if ($waitingAt && $h->created_at->gte($waitingAt)) {
                        $validateDurations[] = $waitingAt->diffInMinutes($h->created_at) / 60;
                        $waitingAt = null;
                    }
                }
            }
        }

        $avgValidationWaitHours = !empty($validateDurations)
            ? round(array_sum($validateDurations) / count($validateDurations), 1)
            : null;

        return [
            'pending_validation_count'  => count($pendingValidation),
            'pending_validation_items'  => $pendingValidation,
            'avg_validation_wait_hours' => $avgValidationWaitHours,
            'approved_count'            => $approvedCount,
            'revision_count'            => $revisionCount,
            'total_reviewed'            => $approvedCount + $revisionCount,
            'approval_rate'             => ($approvedCount + $revisionCount) > 0
                ? round(($approvedCount / ($approvedCount + $revisionCount)) * 100, 1)
                : 0.0,
        ];
    }

    public static function statusLabelId(?string $status): string
    {
        return match (strtoupper((string) $status)) {
            'DRAFT'                => 'Draf',
            'SUBMITTED'            => 'Menunggu Verifikasi',
            'VERIFICATION'         => 'Dalam Verifikasi',
            'VERIFIED'             => 'Terverifikasi',
            'DISPATCHED'           => 'Ditugaskan',
            'IN_PROGRESS'          => 'Sedang Dikerjakan',
            'WAITING_VERIFICATION' => 'Menunggu Validasi',
            'REVISION_REQUIRED'    => 'Perlu Revisi',
            'COMPLETED'            => 'Selesai',
            'CLOSED'               => 'Ditutup',
            'REJECTED'             => 'Ditolak',
            'CANCELLED'            => 'Dibatalkan',
            default                => (string) $status,
        };
    }

    /**
     * Detail daftar pekerjaan untuk cetak Laporan Kinerja Individu & Regu Petugas.
     */
    public function getPetugasPrintableTasks(?int $teamId, int $userId, string $scope = 'individual', ?string $from = null, ?string $to = null): Collection
    {
        if ($scope === 'team' && $teamId) {
            $ids = Disposition::where('team_id', $teamId)
                ->when($from, fn ($q) => $q->whereDate('created_at', '>=', $from))
                ->when($to, fn ($q) => $q->whereDate('created_at', '<=', $to))
                ->pluck('complaint_id')
                ->unique();
        } else {
            $hIds = ComplaintStatusHistory::where('changed_by', $userId)
                ->whereIn('to_status', ['IN_PROGRESS', 'WAITING_VERIFICATION'])
                ->when($from, fn ($q) => $q->whereDate('created_at', '>=', $from))
                ->when($to, fn ($q) => $q->whereDate('created_at', '<=', $to))
                ->pluck('complaint_id');
            $dIds = Disposition::query()
                ->where(function ($q) use ($userId, $teamId) {
                    $q->where('assigned_to', $userId);
                    if ($teamId) {
                        $q->orWhere('team_id', $teamId);
                    }
                })
                ->when($from, fn ($q) => $q->whereDate('created_at', '>=', $from))
                ->when($to, fn ($q) => $q->whereDate('created_at', '<=', $to))
                ->pluck('complaint_id');
            $ids = $hIds->merge($dIds)->unique();
        }

        return Complaint::with(['category', 'rating', 'latestDisposition.team', 'statusHistories'])
            ->whereIn('id', $ids)
            ->latest('updated_at')
            ->get();
    }

    protected function computeTimingAndQuality(Collection $complaints): array
    {
        $responseHours = [];
        $repairHours = [];
        $revisionCount = 0;
        $completedWithoutRevision = 0;
        $completedTotal = 0;
        $onTimeTotal = 0;
        $withDeadlineTotal = 0;

        foreach ($complaints as $c) {
            $histories = $c->statusHistories->sortBy('created_at');
            $dispatchedAt = $histories->firstWhere('to_status', 'DISPATCHED')?->created_at;
            $inProgressAt = $histories->firstWhere('to_status', 'IN_PROGRESS')?->created_at;
            $finishedAt   = $histories->last(fn ($h) => $h->to_status === 'WAITING_VERIFICATION')?->created_at;

            if ($dispatchedAt && $inProgressAt && $inProgressAt->gte($dispatchedAt)) {
                $responseHours[] = $dispatchedAt->diffInMinutes($inProgressAt) / 60;
            }
            if ($inProgressAt && $finishedAt && $finishedAt->gte($inProgressAt)) {
                $repairHours[] = $inProgressAt->diffInMinutes($finishedAt) / 60;
            }

            $revs = $histories->where('to_status', 'REVISION_REQUIRED')->count();
            $revisionCount += $revs;

            if (in_array($c->status, ['COMPLETED', 'CLOSED'], true)) {
                $completedTotal++;
                if ($revs === 0) {
                    $completedWithoutRevision++;
                }
                $deadline = $c->latestDisposition?->deadline;
                if ($deadline && $finishedAt) {
                    $withDeadlineTotal++;
                    if (Carbon::parse($finishedAt)->lte(Carbon::parse($deadline)->endOfDay())) {
                        $onTimeTotal++;
                    }
                }
            }
        }

        $avgResp = !empty($responseHours) ? round(array_sum($responseHours) / count($responseHours), 1) : null;
        $avgRep  = !empty($repairHours) ? round(array_sum($repairHours) / count($repairHours), 1) : null;
        $firstPass = $completedTotal > 0 ? round(($completedWithoutRevision / $completedTotal) * 100, 1) : 100.0;
        $onTime = $withDeadlineTotal > 0 ? round(($onTimeTotal / $withDeadlineTotal) * 100, 1) : 100.0;

        return [$avgResp, $avgRep, $revisionCount, $firstPass, $onTime];
    }

    protected function computeCompositeScore(float $completionRate, float $firstPassRate, float $onTimeRate, int $totalTasks): int
    {
        if ($totalTasks === 0) {
            return 0;
        }
        return (int) round(($completionRate * 0.50) + ($firstPassRate * 0.25) + ($onTimeRate * 0.25));
    }

    /**
     * Daftar periode bulanan (YYYY-MM) yang tersedia dari data aduan/disposisi + bulan berjalan.
     *
     * @return array<int, array{value: string, label: string}>
     */
    public static function getAvailablePeriods(): array
    {
        $months = collect([now('Asia/Makassar')->format('Y-m')]);

        $complaintDates = Complaint::select('created_at')->whereNotNull('created_at')->pluck('created_at');
        foreach ($complaintDates as $dt) {
            if ($dt) {
                $months->push(Carbon::parse($dt)->timezone('Asia/Makassar')->format('Y-m'));
            }
        }

        $dispDates = Disposition::select('created_at')->whereNotNull('created_at')->pluck('created_at');
        foreach ($dispDates as $dt) {
            if ($dt) {
                $months->push(Carbon::parse($dt)->timezone('Asia/Makassar')->format('Y-m'));
            }
        }

        $uniqueSorted = $months->unique()->sortDesc()->values();

        $result = [
            ['value' => 'ALL', 'label' => 'Semua Periode'],
        ];
        foreach ($uniqueSorted as $ym) {
            $result[] = [
                'value' => $ym,
                'label' => self::formatPeriodLabel($ym),
            ];
        }

        return $result;
    }

    /**
     * Konversi kode periode (YYYY-MM atau ALL) menjadi [$fromDate, $toDate, $label].
     *
     * @return array{0: ?string, 1: ?string, 2: string}
     */
    public static function resolvePeriodBounds(?string $period): array
    {
        $clean = trim((string) $period);
        if ($clean === '' || strtoupper($clean) === 'ALL' || !preg_match('/^\d{4}-\d{2}$/', $clean)) {
            return [null, null, 'Semua Periode'];
        }

        try {
            $start = Carbon::createFromFormat('Y-m-d', $clean . '-01', 'Asia/Makassar')->startOfMonth();
            $end = $start->copy()->endOfMonth();
            return [$start->format('Y-m-d'), $end->format('Y-m-d'), self::formatPeriodLabel($clean)];
        } catch (\Throwable) {
            return [null, null, 'Semua Periode'];
        }
    }

    public static function formatPeriodLabel(?string $ym): string
    {
        if (!$ym || strtoupper($ym) === 'ALL' || !preg_match('/^(\d{4})-(\d{2})$/', $ym, $m)) {
            return 'Semua Periode';
        }
        $bulan = [
            '01' => 'Januari', '02' => 'Februari', '03' => 'Maret', '04' => 'April',
            '05' => 'Mei', '06' => 'Juni', '07' => 'Juli', '08' => 'Agustus',
            '09' => 'September', '10' => 'Oktober', '11' => 'November', '12' => 'Desember',
        ];
        return ($bulan[$m[2]] ?? $m[2]) . ' ' . $m[1];
    }
}
