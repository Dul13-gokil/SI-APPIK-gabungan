<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Http\Resources\ApiResponse;
use App\Models\Complaint;
use App\Models\Department;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /**
     * Resolusi user yang login dari Sanctum atau session resmi.
     */
    protected function getAuthenticatedUserId(Request $request): ?int
    {
        return AuthController::resolveUser($request)?->id;
    }

    /**
     * KPI & Ringkasan Metrik (Makro Kabupaten atau Personal Warga).
     */
    public function summary(Request $request): JsonResponse
    {
        $query = Complaint::query();

        if ($request->query('scope') === 'me') {
            $userId = $this->getAuthenticatedUserId($request);
            if ($userId) {
                $query->where('user_id', $userId);
            } else {
                return ApiResponse::success([
                    'total_complaints' => 0,
                    'status_breakdown' => [],
                    'priority_breakdown' => [],
                    'critical_count' => 0,
                    'completed_count' => 0,
                    'in_progress_count' => 0,
                    'pending_verification' => 0,
                ]);
            }
        } else {
            // Publik: Hanya hitung laporan yang valid (non-REJECTED & non-DRAFT)
            $query->whereNotIn('status', ['REJECTED', 'DRAFT']);
        }

        $total = (clone $query)->count();
        $byStatus = (clone $query)->select('status', DB::raw('count(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status')
            ->toArray();

        $byPriority = (clone $query)->select('priority_level', DB::raw('count(*) as total'))
            ->groupBy('priority_level')
            ->pluck('total', 'priority_level')
            ->toArray();

        return ApiResponse::success([
            'total_complaints' => $total,
            'status_breakdown' => $byStatus,
            'priority_breakdown' => $byPriority,
            'critical_count' => $byPriority['CRITICAL'] ?? 0,
            'verification_count' => ($byStatus['SUBMITTED'] ?? 0) + ($byStatus['VERIFICATION'] ?? 0) + ($byStatus['VERIFIED'] ?? 0),
            'pending_verification' => ($byStatus['SUBMITTED'] ?? 0) + ($byStatus['VERIFICATION'] ?? 0) + ($byStatus['VERIFIED'] ?? 0),
            'dispatched_count' => $byStatus['DISPATCHED'] ?? 0,
            'in_progress_count' => ($byStatus['IN_PROGRESS'] ?? 0) + ($byStatus['REVISION_REQUIRED'] ?? 0),
            'waiting_verification_count' => $byStatus['WAITING_VERIFICATION'] ?? 0,
            'completed_count' => ($byStatus['COMPLETED'] ?? 0) + ($byStatus['CLOSED'] ?? 0),
        ]);
    }

    /**
     * Data Spasial Marker dan Heatmap untuk Peta GIS.
     */
    public function map(Request $request): JsonResponse
    {
        $query = Complaint::query()->with([
            'category:id,name',
            'user:id,name,phone',
            'citizen:id,name,phone',
            'latestDisposition.assignee:id,name,phone',
            'latestDisposition.department:id,name',
            'latestDisposition.team.leader:id,name,phone',
            'photos:id,complaint_id,storage_key,type',
        ]);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        } else {
            // Laporan yang ditolak tidak boleh ditampilkan di peta publik
            $query->whereNotIn('status', ['REJECTED']);
        }

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->filled('region_id')) {
            $query->where('region_id', $request->region_id);
        }

        $points = $query->select([
            'id', 'user_id', 'public_code', 'title', 'description', 'address', 'latitude', 'longitude',
            'damage_type', 'road_length', 'road_condition', 'end_latitude', 'end_longitude', 'route_coordinates',
            'status', 'priority_level', 'priority_score', 'ai_category', 'category_id', 'created_at'
        ])->get();

        $user = $request->user() ?? \App\Http\Controllers\Api\v1\AuthController::resolveUser($request);
        $isInternal = $user && in_array(strtoupper($user->role), ['ADMIN', 'SUPERADMIN', 'OPERATOR', 'DINAS', 'PETUGAS', 'BUPATI', 'KEPALA_DAERAH'], true);

        return ApiResponse::success($points->map(function ($p) use ($request, $isInternal) {
            $host = $request->getSchemeAndHttpHost();

            $beforePhoto = $p->photos->where('type', 'evidence_before')->first() ?? $p->photos->first();
            $afterPhoto = $p->photos->where('type', 'resolution_after')->first();

            $photoUrl = null;
            if ($beforePhoto) {
                $photoUrl = $beforePhoto->storage_key ? "{$host}/storage/" . ltrim($beforePhoto->storage_key, '/') : $beforePhoto->url;
            }

            $completionPhotoUrl = null;
            if ($afterPhoto) {
                $completionPhotoUrl = $afterPhoto->storage_key ? "{$host}/storage/" . ltrim($afterPhoto->storage_key, '/') : $afterPhoto->url;
            }

            $disp = $p->latestDisposition;
            $isAnon = (bool) ($p->is_anonymous ?? false);
            $citizenName = ($isAnon && !$isInternal) ? 'Warga Anonim' : ($p->user?->name ?? $p->citizen?->name ?? 'Warga Terdaftar');
            
            $rawPhone = $p->user?->phone ?? $p->citizen?->phone ?? null;
            if ($rawPhone && $isInternal) {
                $citizenPhone = $rawPhone;
            } elseif ($rawPhone && !$isAnon) {
                $clean = preg_replace('/[^0-9]/', '', $rawPhone);
                $citizenPhone = (strlen($clean) >= 3)
                    ? str_repeat('*', max(strlen($clean) - 3, 6)) . substr($clean, -3)
                    : '********';
            } else {
                $citizenPhone = '********';
            }

            $officerName = $disp?->assignee?->name ?? null;
            $officerPhone = $disp?->assignee?->phone ?? null;
            $departmentName = $disp?->department?->name ?? null;
            $formattedDate = $p->created_at ? $p->created_at->translatedFormat('d M Y, H:i') . ' WITA' : '-';

            return [
                'id' => $p->id,
                'public_code' => $p->public_code,
                'title' => $p->title,
                'description' => $p->description,
                'address' => $p->address,
                'damage_type' => $p->damage_type ?? 'minor',
                'road_length' => $p->road_length,
                'road_condition' => $p->road_condition,
                'end_latitude' => $p->end_latitude ? (float) $p->end_latitude : null,
                'end_longitude' => $p->end_longitude ? (float) $p->end_longitude : null,
                'route_coordinates' => $p->route_coordinates,
                'reporter_name' => $citizenName,
                'citizen_name' => $citizenName,
                'reporter_phone' => $citizenPhone,
                'citizen_phone' => $citizenPhone,
                'officer_name' => $officerName,
                'assignee_name' => $officerName,
                'officer_phone' => $officerPhone,
                'assignee_phone' => $officerPhone,
                'department_name' => $departmentName,
                'team_name' => $disp?->team?->name ?? null,
                'team_code' => $disp?->team?->code ?? null,
                'team_leader' => $disp?->team?->leader?->name ?? null,
                'team_leader_phone' => $disp?->team?->leader?->phone ?? null,
                'deadline' => $disp?->deadline ? \Illuminate\Support\Carbon::parse($disp->deadline)->locale('id')->isoFormat('D MMM Y') : null,
                'instruction' => $disp?->instruction ?? null,
                'latitude' => (float) $p->latitude,
                'longitude' => (float) $p->longitude,
                'status' => $p->status,
                'priority_level' => $p->priority_level,
                'priority_score' => (float) $p->priority_score,
                'ai_category' => $p->ai_category,
                'category_id' => $p->category_id,
                'category_name' => $p->category?->name ?? 'Infrastruktur',
                'photo_url' => $photoUrl,
                'completion_photo_url' => $completionPhotoUrl,
                'photo_after_url' => $completionPhotoUrl,
                'created_at' => $formattedDate,
                'created_at_full' => $formattedDate,
                'created_at_formatted' => $formattedDate,
                'time_ago' => $p->created_at ? $p->created_at->diffForHumans() : '',
            ];
        }));
    }

    /**
     * Kinerja & SLA Penanganan per Dinas Teknis.
     */
    public function performance(): JsonResponse
    {
        $departments = Department::withCount([
            'dispositions as total_assigned',
            'dispositions as completed_count' => function ($q) {
                $q->where('status', 'COMPLETED');
            }
        ])->get();

        return ApiResponse::success($departments->map(fn($d) => [
            'id' => $d->id,
            'name' => $d->name,
            'code' => $d->code,
            'total_assigned' => $d->total_assigned,
            'completed_count' => $d->completed_count,
            'completion_rate' => $d->total_assigned > 0
                ? round(($d->completed_count / $d->total_assigned) * 100, 1)
                : 0.0,
        ]));
    }
}
