<?php

namespace App\Domain\Complaints\Services;

use App\Domain\Complaints\Exceptions\FieldTaskAuthorizationException;
use App\Models\Complaint;
use App\Models\SystemSetting;
use App\Models\User;

/**
 * Satu-satunya sumber aturan "siapa boleh mengerjakan tugas lapangan".
 * Dipakai bersama oleh API v1 dan portal web agar team shield serta kuota
 * tidak berbeda antar jalur (Aturan 3.2 dan 3.3).
 */
class FieldTaskAuthorizer
{
    public const DEFAULT_MAX_ACTIVE_TASKS = 3;

    public function isInternalAdmin(?User $user): bool
    {
        return $user !== null
            && in_array(strtoupper((string) $user->role), ['SUPERADMIN', 'ADMIN'], true);
    }

    /**
     * @throws FieldTaskAuthorizationException
     */
    public function assertCanStart(?User $user, Complaint $complaint): void
    {
        if ($user === null || $this->isInternalAdmin($user)) {
            return;
        }

        $teamId = $user->currentTeam()?->id;
        $disposition = $complaint->latestDisposition;
        $mode = (string) SystemSetting::get('dispatch_mode', 'HYBRID');

        if ($mode === 'OPERATOR_ONLY') {
            if (! $disposition || (! $disposition->team_id && ! $disposition->assigned_to)) {
                throw FieldTaskAuthorizationException::wrongTeam(
                    "Mode penugasan saat ini adalah 'Operator Saja' (Komando Terpusat). "
                    . 'Laporan ini wajib ditunjuk regunya oleh Operator terlebih dahulu sebelum dapat dikerjakan.'
                );
            }

            if ($disposition->team_id && $disposition->team_id !== $teamId) {
                throw FieldTaskAuthorizationException::wrongTeam($this->wrongTeamMessage($disposition->team_id));
            }

            if (! $disposition->team_id && $disposition->assigned_to && $disposition->assigned_to !== $user->id) {
                throw FieldTaskAuthorizationException::wrongTeam(
                    'Laporan ini ditugaskan khusus untuk petugas lain oleh Operator.'
                );
            }
        } elseif ($disposition && $disposition->team_id && $disposition->team_id !== $teamId) {
            throw FieldTaskAuthorizationException::wrongTeam($this->wrongTeamMessage($disposition->team_id));
        }

        $limit = (int) SystemSetting::get('max_active_tasks', self::DEFAULT_MAX_ACTIVE_TASKS);
        if ($limit > 0 && $this->activeTaskCount($user, $complaint->id) >= $limit) {
            throw FieldTaskAuthorizationException::quotaExceeded($limit);
        }
    }

    /**
     * @throws FieldTaskAuthorizationException
     */
    public function assertCanFinish(?User $user, Complaint $complaint): void
    {
        if ($user === null || $this->isInternalAdmin($user)) {
            return;
        }

        $disposition = $complaint->latestDisposition;
        if (! $disposition) {
            return;
        }

        if ($disposition->team_id) {
            $teamIds = $user->teams()->pluck('teams.id')->map(fn ($id) => (int) $id)->all();
            if (! in_array((int) $disposition->team_id, $teamIds, true)) {
                throw FieldTaskAuthorizationException::wrongTeam($this->wrongTeamMessage($disposition->team_id));
            }

            return;
        }

        if ($disposition->assigned_to && (int) $disposition->assigned_to !== (int) $user->id) {
            throw FieldTaskAuthorizationException::wrongTeam(
                'Laporan ini ditugaskan khusus untuk petugas lain. Anda tidak memiliki izin mengunggah bukti penyelesaiannya.'
            );
        }
    }

    /**
     * Jumlah tugas aktif milik regu (atau petugas tanpa regu) saat ini.
     */
    public function activeTaskCount(User $user, ?int $excludeComplaintId = null): int
    {
        $teamId = $user->currentTeam()?->id;

        return Complaint::query()
            ->when($excludeComplaintId, fn ($query) => $query->whereKeyNot($excludeComplaintId))
            ->whereIn('status', ['IN_PROGRESS', 'REVISION_REQUIRED'])
            ->whereHas('dispositions', function ($query) use ($user, $teamId) {
                $query->where(function ($inner) use ($user, $teamId) {
                    $inner->where('assigned_to', $user->id);
                    if ($teamId) {
                        $inner->orWhere('team_id', $teamId);
                    }
                });
            })
            ->count();
    }

    private function wrongTeamMessage(int $teamId): string
    {
        $teamName = \App\Models\Team::find($teamId)?->name ?? 'Regu lain';

        return "Laporan ini telah ditugaskan khusus untuk {$teamName}. Regu Anda tidak berhak mengambil tugas ini.";
    }
}
