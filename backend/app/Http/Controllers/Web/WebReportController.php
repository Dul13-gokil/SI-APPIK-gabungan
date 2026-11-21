<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Complaint;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class WebReportController extends Controller
{
    /**
     * Ekspor Rekapitulasi Pengaduan atau Kinerja Pekerja Lapangan ke format CSV (UTF-8 BOM, siap buka di Microsoft Excel).
     */
    public function exportCsv(Request $request): StreamedResponse
    {
        $mode = $request->query('mode');
        if (in_array($mode, ['team', 'individual'], true)) {
            return $this->exportPerformanceCsv($request, $mode);
        }

        $query = Complaint::with(['category', 'user', 'citizen', 'region', 'latestDisposition.team', 'rating'])
            ->latest('created_at');

        if ($request->filled('status') && $request->status !== 'ALL') {
            if ($request->status === 'COMPLETED') {
                $query->whereIn('status', ['COMPLETED', 'CLOSED']);
            } else {
                $query->where('status', $request->status);
            }
        }
        if ($request->filled('category_id') && $request->category_id !== 'ALL') {
            $query->where('category_id', (int) $request->category_id);
        }
        if ($request->filled('from')) {
            $query->whereDate('created_at', '>=', $request->from);
        }
        if ($request->filled('to')) {
            $query->whereDate('created_at', '<=', $request->to);
        }

        $complaints = $query->get();
        $filename = 'rekap-aduan-siappik-' . now('Asia/Makassar')->format('Ymd-His') . '.csv';

        return response()->streamDownload(function () use ($complaints) {
            $out = fopen('php://output', 'w');
            // UTF-8 BOM agar Microsoft Excel langsung membaca karakter & kolom dengan rapi
            fwrite($out, "\xEF\xBB\xBF");

            $this->putCsvRow($out, [
                'Kode Tiket',
                'Tanggal Masuk (WITA)',
                'Judul Laporan',
                'Kategori',
                'Tipe Kerusakan',
                'Status',
                'Kecamatan',
                'Alamat Lengkap',
                'Koordinat GPS',
                'Pelapor',
                'Regu Pelaksana',
                'Rating Warga (1-5)',
                'Ulasan Warga',
            ]);

            foreach ($complaints as $c) {
                $statusLabel = match ($c->status) {
                    'COMPLETED', 'CLOSED' => 'Selesai',
                    'WAITING_VERIFICATION' => 'Menunggu Validasi',
                    'IN_PROGRESS' => 'Sedang Dikerjakan',
                    'REVISION_REQUIRED' => 'Perlu Revisi',
                    'DISPATCHED' => 'Ditugaskan',
                    'VERIFIED' => 'Terverifikasi',
                    'REJECTED' => 'Ditolak',
                    'CANCELLED' => 'Dibatalkan',
                    default => 'Menunggu Verifikasi',
                };

                $this->putCsvRow($out, [
                    '#' . $c->public_code,
                    $c->created_at?->timezone('Asia/Makassar')->format('d/m/Y H:i') ?? '-',
                    $c->title,
                    $c->category?->name ?? 'Umum',
                    $c->damage_type === 'major' ? 'Kerusakan Berat' : 'Kerusakan Ringan',
                    $statusLabel,
                    $c->region?->district ?? '-',
                    $c->address ?? '-',
                    ($c->latitude && $c->longitude) ? "{$c->latitude}, {$c->longitude}" : '-',
                    $c->is_anonymous ? 'Anonim' : ($c->user?->name ?? $c->citizen?->name ?? 'Warga'),
                    $c->latestDisposition?->team?->name ?? '-',
                    $c->rating?->rating ?? '-',
                    $c->rating?->comment ?? '-',
                ]);
            }

            fclose($out);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    /**
     * Cetak Berita Acara Penyelesaian (BAP) resmi per tiket pengaduan.
     */
    public function printBap(int $id)
    {
        $complaint = Complaint::with([
            'category',
            'user',
            'citizen',
            'region',
            'photos',
            'latestDisposition.team.leader',
            'latestDisposition.assignee',
            'statusHistories.user',
            'rating',
        ])->findOrFail($id);

        $beforePhotos = $complaint->photos->where('type', 'evidence_before')->values();
        $afterPhotos  = $complaint->photos->where('type', 'resolution_after')->values();

        return view('reports.bap_print', compact('complaint', 'beforePhotos', 'afterPhotos'));
    }

    /**
     * Ekspor Kinerja Pekerja Lapangan (Per Regu atau Per Individu, semua atau 1 entitas) ke format CSV.
     */
    private function exportPerformanceCsv(Request $request, string $mode): StreamedResponse
    {
        $perfService = app(\App\Services\PerformanceMetricsService::class);
        $timestamp = now('Asia/Makassar')->format('Ymd-His');
        [$from, $to, $periodLabel] = \App\Services\PerformanceMetricsService::resolvePeriodBounds($request->query('period'));

        if ($mode === 'team') {
            $teamId = $request->integer('team_id') ?: null;
            $rows = $perfService->getFieldTeamMetrics($from, $to, $teamId);
            $slug = ($teamId && !empty($rows))
                ? strtolower(preg_replace('/[^a-zA-Z0-9_-]+/', '-', $rows[0]['team_code'] ?? 'regu'))
                : 'semua-regu';
            $filename = "kinerja-regu-{$slug}-{$timestamp}.csv";

            return response()->streamDownload(function () use ($rows) {
                $out = fopen('php://output', 'w');
                fwrite($out, "\xEF\xBB\xBF");
                $this->putCsvRow($out, [
                    'Kode Regu',
                    'Nama Regu',
                    'Ketua Regu',
                    'Wilayah Tugas',
                    'Jumlah Anggota',
                    'Total Tugas',
                    'Tugas Tuntas',
                    'Persentase Tuntas (%)',
                    'Kuota Aktif',
                    'Rata-rata Respon (Jam)',
                    'Rata-rata Perbaikan (Jam)',
                    'Lolos 1x Validasi (%)',
                    'Rating Warga (1-5)',
                    'Skor Kinerja',
                ]);
                foreach ($rows as $tm) {
                    $this->putCsvRow($out, [
                        $tm['team_code'],
                        $tm['team_name'],
                        $tm['leader_name'],
                        $tm['district'],
                        $tm['members_count'],
                        $tm['total_tasks'],
                        $tm['completed'],
                        $tm['completion_rate'],
                        "{$tm['active_quota']}/" . \App\Models\SystemSetting::get('max_active_tasks', 3),
                        $tm['avg_response_hours'] ?? '-',
                        $tm['avg_repair_hours'] ?? '-',
                        $tm['first_pass_rate'],
                        $tm['avg_rating'] ?? '-',
                        $tm['score'],
                    ]);
                }
                fclose($out);
            }, $filename, [
                'Content-Type' => 'text/csv; charset=UTF-8',
            ]);
        }

        // mode === 'individual'
        $officerId = $request->integer('officer_id') ?: null;
        $rows = $perfService->getFieldOfficerMetrics($from, $to, null, $officerId);
        $slug = ($officerId && !empty($rows))
            ? strtolower(preg_replace('/[^a-zA-Z0-9_-]+/', '-', $rows[0]['name'] ?? 'petugas'))
            : 'semua-individu';
        $filename = "kinerja-individu-{$slug}-{$timestamp}.csv";

        return response()->streamDownload(function () use ($rows) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF");
            $this->putCsvRow($out, [
                'Nama Petugas',
                'Email',
                'Telepon',
                'Regu Lapangan',
                'Peran',
                'Total Tugas',
                'Tugas Tuntas',
                'Persentase Tuntas (%)',
                'Rata-rata Perbaikan (Jam)',
                'Jumlah Revisi',
                'Lolos 1x Validasi (%)',
                'Rating Warga (1-5)',
                'Skor Kinerja',
            ]);
            foreach ($rows as $of) {
                $this->putCsvRow($out, [
                    $of['name'],
                    $of['email'],
                    $of['phone'],
                    $of['team_name'],
                    $of['team_role'],
                    $of['total_tasks'],
                    $of['completed'],
                    $of['completion_rate'],
                    $of['avg_repair_hours'] ?? '-',
                    $of['revision_count'],
                    $of['first_pass_rate'],
                    $of['avg_rating'] ?? '-',
                    $of['score'],
                ]);
            }
            fclose($out);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    /**
     * Cetak Dokumen Resmi Laporan Kinerja Pekerja Lapangan (Mode Individu atau Regu, Semua atau 1 Entitas).
     */
    public function printPetugasPerformance(Request $request)
    {
        $perfService = app(\App\Services\PerformanceMetricsService::class);
        $authUser = auth()->user();
        $mode = $request->query('mode', 'individual') === 'team' ? 'team' : 'individual';
        $canMonitorAll = $authUser->isSuperAdmin() || $authUser->isOperator() || $authUser->isBupati();

        $reqTeamId = $request->integer('team_id');
        $reqOfficerId = $request->integer('officer_id');
        $reqScope = $request->query('scope');
        [$from, $to, $periodLabel] = \App\Services\PerformanceMetricsService::resolvePeriodBounds($request->query('period'));

        $isAll = false;
        $allTeamMetrics = [];
        $allOfficerMetrics = [];
        $officer = null;
        $team = null;
        $individualMetric = null;
        $teamMetric = null;
        $teamMembers = [];
        $tasks = collect();

        if ($canMonitorAll) {
            if ($mode === 'team') {
                if ($reqScope === 'all' || ($reqTeamId <= 0 && $reqOfficerId <= 0)) {
                    $isAll = true;
                    $allTeamMetrics = $perfService->getFieldTeamMetrics($from, $to);
                } else {
                    if ($reqTeamId > 0) {
                        $team = \App\Models\Team::with('leader')->find($reqTeamId);
                    } elseif ($reqOfficerId > 0) {
                        $officer = \App\Models\User::where('role', 'PETUGAS')->with('teams.leader')->find($reqOfficerId);
                        $team = $officer?->teams?->first();
                    }
                    if ($team) {
                        $teamList = $perfService->getFieldTeamMetrics($from, $to, $team->id);
                        $teamMetric = $teamList[0] ?? null;
                        $teamMembers = $perfService->getFieldOfficerMetrics($from, $to, $team->id);
                        $leaderOrMemberId = $team->leader_id ?? $officer?->id ?? 0;
                        $tasks = $perfService->getPetugasPrintableTasks($team->id, $leaderOrMemberId, 'team', $from, $to);
                    }
                }
            } else {
                // individual
                if ($reqScope === 'all' || $reqOfficerId <= 0) {
                    $isAll = true;
                    $allOfficerMetrics = $perfService->getFieldOfficerMetrics($from, $to);
                } else {
                    $officer = \App\Models\User::where('role', 'PETUGAS')->with('teams.leader')->find($reqOfficerId);
                    $team = $officer?->teams?->first();
                    if ($officer) {
                        $individualList = $perfService->getFieldOfficerMetrics($from, $to, null, $officer->id);
                        $individualMetric = $individualList[0] ?? null;
                        $tasks = $perfService->getPetugasPrintableTasks($team?->id, $officer->id, 'individual', $from, $to);
                    }
                }
            }
        } else {
            // Pekerja Lapangan biasa: hanya bisa mencetak kinerja diri sendiri atau regunya sendiri
            $officer = \App\Models\User::with('teams.leader')->find($authUser->id);
            $team = $officer?->teams?->first();
            $teamId = $team?->id;

            $individualList = $officer ? $perfService->getFieldOfficerMetrics($from, $to, null, $officer->id) : [];
            $individualMetric = $individualList[0] ?? null;

            $teamList = $teamId ? $perfService->getFieldTeamMetrics($from, $to, $teamId) : [];
            $teamMetric = $teamList[0] ?? null;
            $teamMembers = $teamId ? $perfService->getFieldOfficerMetrics($from, $to, $teamId) : [];

            $tasks = $officer
                ? $perfService->getPetugasPrintableTasks($teamId, $officer->id, $mode, $from, $to)
                : collect();
        }

        return view('reports.petugas_performance_print', compact(
            'mode', 'isAll', 'allTeamMetrics', 'allOfficerMetrics', 'periodLabel',
            'officer', 'team', 'individualMetric', 'teamMetric', 'teamMembers', 'tasks'
        ));
    }

    /**
     * Cetak Dokumen Resmi Laporan Kinerja Petugas melalui Signed URL (khusus akses dari Aplikasi Mobile).
     */
    public function printPetugasPerformanceSigned(Request $request, int $user)
    {
        $perfService = app(\App\Services\PerformanceMetricsService::class);
        $mode = $request->query('mode', 'individual') === 'team' ? 'team' : 'individual';
        [$from, $to, $periodLabel] = \App\Services\PerformanceMetricsService::resolvePeriodBounds($request->query('period'));

        $officer = \App\Models\User::with('teams.leader')->findOrFail($user);
        $team = $officer->teams->first();
        $teamId = $team?->id;

        $isAll = false;
        $allTeamMetrics = [];
        $allOfficerMetrics = [];

        $individualList = $perfService->getFieldOfficerMetrics($from, $to, null, $officer->id);
        $individualMetric = $individualList[0] ?? null;

        $teamList = $teamId ? $perfService->getFieldTeamMetrics($from, $to, $teamId) : [];
        $teamMetric = $teamList[0] ?? null;
        $teamMembers = $teamId ? $perfService->getFieldOfficerMetrics($from, $to, $teamId) : [];

        $tasks = $perfService->getPetugasPrintableTasks($teamId, $officer->id, $mode, $from, $to);

        return view('reports.petugas_performance_print', compact(
            'mode', 'isAll', 'allTeamMetrics', 'allOfficerMetrics', 'periodLabel',
            'officer', 'team', 'individualMetric', 'teamMetric', 'teamMembers', 'tasks'
        ));
    }

    /**
     * Cetak Berita Acara Penyelesaian (BAP) melalui Signed URL (khusus akses dari Aplikasi Mobile).
     */
    public function printBapSigned(int $id)
    {
        return $this->printBap($id);
    }

    /**
     * Tulis satu baris CSV dengan proteksi formula injection (Excel/Sheets).
     */
    private function putCsvRow($handle, array $row): void
    {
        fputcsv($handle, array_map([$this, 'csvCell'], $row));
    }

    private function csvCell(mixed $value): mixed
    {
        if (! is_string($value)) {
            return $value;
        }

        $trimmed = ltrim($value);

        return ($trimmed !== '' && in_array($trimmed[0], ['=', '+', '-', '@', "\t", "\r"], true))
            ? "'" . $value
            : $value;
    }
}
