<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Http\Resources\ApiResponse;
use App\Models\User;
use App\Services\PerformanceMetricsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;

class FieldPerformanceController extends Controller
{
    public function __construct(
        private readonly PerformanceMetricsService $perfService
    ) {}

    /**
     * Ambil data lengkap Laporan Kinerja untuk Pekerja Lapangan yang sedang login:
     * - Kinerja Individu + Rincian Pekerjaan Individu (dengan signed URL Cetak BAP)
     * - Kinerja Regu + Kontribusi Anggota Regu
     * - Responsivitas Validasi Operator
     * - Signed URL Cetak Laporan (Individu & Regu) untuk dibuka dari browser HP
     */
    public function show(Request $request): JsonResponse
    {
        $authUser = $request->user();
        if (!$authUser) {
            return ApiResponse::error('Unauthenticated.', [], 401);
        }

        $officer = User::with('teams.leader')->find($authUser->id);
        $team = $officer?->teams?->first();
        $teamId = $team?->id;

        $period = $request->query('period', 'ALL');
        [$periodFrom, $periodTo, $periodLabel] = PerformanceMetricsService::resolvePeriodBounds($period);
        $from = $request->query('from') ?: $periodFrom;
        $to = $request->query('to') ?: $periodTo;
        $availablePeriods = PerformanceMetricsService::getAvailablePeriods();

        $individualList = $officer
            ? $this->perfService->getFieldOfficerMetrics($from, $to, null, $officer->id)
            : [];
        $individualMetric = $individualList[0] ?? [
            'user_id'            => $officer?->id ?? $authUser->id,
            'name'               => $officer?->name ?? $authUser->name,
            'email'              => $officer?->email ?? $authUser->email,
            'phone'              => $officer?->phone ?? '-',
            'team_id'            => $teamId,
            'team_name'          => $team?->name ?? 'Belum Tergabung Regu',
            'team_role'          => ($team && $team->leader_id === $authUser->id) ? 'Ketua Regu' : 'Anggota Regu',
            'total_tasks'        => 0,
            'in_progress'        => 0,
            'waiting_validation' => 0,
            'completed'          => 0,
            'completion_rate'    => 0,
            'avg_response_hours' => null,
            'avg_repair_hours'   => null,
            'revision_count'     => 0,
            'first_pass_rate'    => 100,
            'on_time_rate'       => 100,
            'avg_rating'         => null,
            'score'              => 0,
        ];

        $teamList = $teamId ? $this->perfService->getFieldTeamMetrics($from, $to, $teamId) : [];
        $teamMetric = $teamList[0] ?? null;
        $teamMembers = $teamId ? $this->perfService->getFieldOfficerMetrics($from, $to, $teamId) : [];

        $operatorResp = $this->perfService->getOperatorResponsivenessForTeam(
            $teamId,
            $authUser->id,
            $from,
            $to
        );

        $individualTasks = $this->perfService
            ->getPetugasPrintableTasks($teamId, $authUser->id, 'individual', $from, $to)
            ->map(function ($t) {
                $isCompleted = in_array($t->status, ['COMPLETED', 'CLOSED'], true);
                return [
                    'id'           => $t->id,
                    'public_code'  => $t->public_code,
                    'title'        => $t->title,
                    'address'      => $t->address,
                    'category'     => $t->category?->name ?? 'Umum',
                    'status'       => $t->status,
                    'status_label' => PerformanceMetricsService::statusLabelId($t->status),
                    'rating'       => $t->rating ? (int) $t->rating->rating : null,
                    'bap_url'      => $isCompleted
                        ? URL::temporarySignedRoute('mobile.complaints.bap', now()->addMinutes(30), ['id' => $t->id])
                        : null,
                ];
            })
            ->values();

        $printUrls = [
            'individual' => URL::temporarySignedRoute(
                'mobile.petugas.performance.print',
                now()->addMinutes(30),
                ['user' => $authUser->id, 'mode' => 'individual', 'period' => $period]
            ),
            'team' => $teamId
                ? URL::temporarySignedRoute(
                    'mobile.petugas.performance.print',
                    now()->addMinutes(30),
                    ['user' => $authUser->id, 'mode' => 'team', 'period' => $period]
                )
                : null,
        ];

        return ApiResponse::success([
            'selected_period'         => $period ?: 'ALL',
            'selected_period_label'   => $periodLabel,
            'available_periods'       => $availablePeriods,
            'individual'              => $individualMetric,
            'individual_tasks'        => $individualTasks,
            'team'                    => $teamMetric,
            'team_members'            => $teamMembers,
            'operator_responsiveness' => $operatorResp,
            'print_urls'              => $printUrls,
        ], 'Data laporan kinerja petugas berhasil diambil.');
    }
}
