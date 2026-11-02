<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ComplaintResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $user = $request->user() ?? \App\Http\Controllers\Api\v1\AuthController::resolveUser($request);
        $isInternal = $user && in_array(strtoupper($user->role), ['ADMIN', 'SUPERADMIN', 'OPERATOR', 'DINAS', 'PETUGAS', 'BUPATI', 'KEPALA_DAERAH'], true);
        $canViewPrivateData = $isInternal || ($user && $user->id === $this->user_id);

        // Masking nomor telepon untuk publik / non-internal (UU Perlindungan Data Pribadi No. 27/2022)
        $displayPhone = null;
        if ($this->user?->phone) {
            if ($canViewPrivateData) {
                $displayPhone = $this->user->phone;
            } else if (!$this->is_anonymous) {
                $rawPhone = $this->user->phone;
                $displayPhone = (strlen($rawPhone) > 7)
                    ? substr($rawPhone, 0, 4) . '****' . substr($rawPhone, -3)
                    : substr($rawPhone, 0, 2) . '****';
            }
        }

        // Host pengakses dinamis (misal http://192.168.1.5:8000)
        $host = $request->getSchemeAndHttpHost();

        // Ambil data disposisi dan status tenggat waktu / deadline
        $latestDisp = $this->latestDisposition;
        $isOverdue = false;
        $deadlineStr = null;
        $deadlineHuman = null;

        if ($latestDisp && $latestDisp->deadline) {
            $deadlineStr = $latestDisp->deadline->toIso8601String();
            $deadlineHuman = $latestDisp->deadline->diffForHumans();
            if (now()->greaterThan($latestDisp->deadline) && !in_array($this->status, ['COMPLETED', 'CLOSED'], true)) {
                $isOverdue = true;
            }
        }

        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'public_code' => $this->public_code,
            'title' => $this->title,
            'description' => $this->description,
            'address' => $this->address,
            'latitude' => (float) $this->latitude,
            'longitude' => (float) $this->longitude,
            'status' => $this->status,
            'assigned_to' => $latestDisp?->assigned_to,
            'assigned_officer_id' => $latestDisp?->assigned_to,
            'team_id' => $latestDisp?->team_id,
            'team_name' => $latestDisp?->team?->name,
            'team_code' => $latestDisp?->team?->code,
            'team_leader_name' => $latestDisp?->team?->leader?->name,
            'team_leader_phone' => $latestDisp?->team?->leader?->phone,
            'team_members_count' => $latestDisp?->team?->members()->count() ?? 0,
            'team' => $latestDisp?->team ? [
                'id' => $latestDisp->team->id,
                'name' => $latestDisp->team->name,
                'code' => $latestDisp->team->code,
                'district' => $latestDisp->team->district,
                'leader_name' => $latestDisp->team->leader?->name,
                'leader_phone' => $latestDisp->team->leader?->phone,
                'members_count' => $latestDisp->team->members()->count(),
                'members' => $latestDisp->team->users->map(fn($u) => [
                    'id' => $u->id,
                    'name' => $u->name,
                    'role' => $u->pivot?->role ?? 'MEMBER',
                ]),
            ] : null,
            'damage_type' => $this->damage_type ?? 'minor',
            'damage_type_label' => ($this->damage_type === 'major') ? 'Kerusakan Ruas Panjang / Parah' : 'Kerusakan Titik / Ringan',
            'road_length' => $this->road_length,
            'road_condition' => $this->road_condition,
            'end_latitude' => $this->end_latitude ? (float) $this->end_latitude : null,
            'end_longitude' => $this->end_longitude ? (float) $this->end_longitude : null,
            'route_coordinates' => $this->route_coordinates,
            'priority_score' => (float) $this->priority_score,
            'priority_level' => $this->priority_level,
            'ai_category' => $this->ai_category,
            'ai_confidence' => (float) $this->ai_confidence,
            'category_id' => $this->category_id,
            'category_name' => $this->category?->name,
            'category' => [
                'id' => $this->category_id,
                'name' => $this->category?->name,
            ],
            'reporter' => ($this->is_anonymous && !$canViewPrivateData)
                ? ['id' => null, 'name' => 'Warga Anonim', 'phone' => null]
                : [
                    'id' => $this->user?->id,
                    'name' => $this->user?->name ?? 'Warga Masyarakat',
                    'phone' => $displayPhone,
                ],
            'reporter_name' => ($this->is_anonymous && !$canViewPrivateData)
                ? 'Warga Anonim'
                : ($this->user?->name ?? 'Warga Masyarakat'),
            'reporter_phone' => $displayPhone,
            'is_anonymous' => (bool) $this->is_anonymous,
            'supports_count' => $this->supports()->count(),
            'photos' => $this->photos->where('type', 'evidence_before')->values()->map(function ($p) use ($host) {
                $url = $p->storage_key ? "{$host}/storage/" . ltrim($p->storage_key, '/') : $p->url;
                return [
                    'id'      => $p->id,
                    'url'     => $url,
                    'caption' => $p->caption ?? 'Foto Bukti Kerusakan',
                    'type'    => $p->type,
                ];
            }),
            'completion_photos' => $this->photos->where('type', 'resolution_after')->values()->map(function ($p) use ($host) {
                $url = $p->storage_key ? "{$host}/storage/" . ltrim($p->storage_key, '/') : $p->url;
                return [
                    'id'      => $p->id,
                    'url'     => $url,
                    'caption' => $p->caption ?? 'Foto Hasil Pengerjaan',
                    'type'    => $p->type,
                ];
            }),
            'latest_disposition' => $latestDisp ? [
                'id'             => $latestDisp->id,
                'department_id'  => $latestDisp->department_id,
                'team_id'        => $latestDisp->team_id,
                'team_name'      => $latestDisp->team?->name,
                'assigned_to'    => $latestDisp->assigned_to,
                'assignee_name'  => $latestDisp->assignee?->name ?? ($latestDisp->team ? $latestDisp->team->name : 'Petugas Lapangan'),
                'assignee_phone' => $latestDisp->assignee?->phone,
                'instruction'    => $latestDisp->instruction,
                'deadline'       => $deadlineStr,
                'deadline_human' => $deadlineHuman,
                'is_overdue'     => $isOverdue,
            ] : null,
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
