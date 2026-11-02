<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Complaint;
use App\Models\Department;
use Illuminate\View\View;

class WebBupatiController extends Controller
{
    public function index(): View
    {
        $total = Complaint::count();
        $completed = Complaint::whereIn('status', ['COMPLETED', 'CLOSED'])->count();
        $inProgress = Complaint::where('status', 'IN_PROGRESS')->count();
        $dispatched = Complaint::where('status', 'DISPATCHED')->count();
        $pending = Complaint::whereIn('status', ['SUBMITTED', 'VERIFICATION', 'VERIFIED'])->count();
        $rejected = Complaint::where('status', 'REJECTED')->count();
        $waitingVerification = Complaint::where('status', 'WAITING_VERIFICATION')->count();

        $completionRate = $total > 0 ? round(($completed / $total) * 100, 1) : 0;

        $summary = [
            'total'           => $total,
            'completed'       => $completed,
            'in_progress'     => $inProgress,
            'dispatched'      => $dispatched,
            'pending'         => $pending,
            'waiting_verification' => $waitingVerification,
            'rejected'        => $rejected,
            'completion_rate' => $completionRate,
            'critical_count'  => Complaint::where('priority_level', 'CRITICAL')->count(),
            'urgent_count'    => Complaint::whereIn('priority_level', ['HIGH', 'CRITICAL'])->count(),
            'petugas_count'   => \App\Models\User::where('role', 'PETUGAS')->count(),
        ];

        // Seluruh Pengaduan untuk Tab 2 (Perkembangan Terkini)
        $allComplaints = Complaint::with(['category', 'user', 'photos', 'latestDisposition.assignee', 'latestDisposition.department'])
            ->latest()
            ->get();

        // Arsip Laporan (Selesai & Ditolak) untuk Tab 3 (Sama seperti Operator)
        $archivedComplaints = Complaint::with(['category', 'user', 'citizen', 'photos', 'latestDisposition.assignee', 'latestDisposition.department', 'statusHistories'])
            ->whereIn('status', ['COMPLETED', 'CLOSED', 'REJECTED'])
            ->latest()
            ->take(100)
            ->get();

        // Kinerja Dinas / OPD
        $departments = Department::withCount([
            'dispositions as total_tasks',
            'dispositions as completed_tasks' => function ($q) {
                $q->where('status', 'COMPLETED');
            },
        ])->get();

        // Kategori Masalah
        $categories = Category::withCount('complaints')->get();

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

        // Agregasi Sebaran Pengaduan per Wilayah Kecamatan & Desa (Kabupaten Kolaka Utara)
        $regionsDb = \App\Models\Region::orderBy('district')->get();
        $allComplaintsForStats = Complaint::all();

        $districtStats = [];
        foreach ($regionsDb as $r) {
            $distName = strtolower(str_replace('kecamatan ', '', $r->district));
            $villName = strtolower(str_replace(['desa ', 'kelurahan '], '', $r->village));

            $total = 0;
            $completed = 0;
            $inProgress = 0;
            $waitingVerification = 0;
            $dispatched = 0;
            $pending = 0;
            $rejected = 0;

            foreach ($allComplaintsForStats as $cmp) {
                $addr = strtolower($cmp->address ?? '');
                if ($cmp->region_id === $r->id || str_contains($addr, $distName) || str_contains($addr, $villName)) {
                    $total++;
                    if (in_array($cmp->status, ['COMPLETED', 'CLOSED'], true)) {
                        $completed++;
                    } elseif (in_array($cmp->status, ['IN_PROGRESS', 'REVISION_REQUIRED'], true)) {
                        $inProgress++;
                    } elseif ($cmp->status === 'WAITING_VERIFICATION') {
                        $waitingVerification++;
                    } elseif ($cmp->status === 'DISPATCHED') {
                        $dispatched++;
                    } elseif ($cmp->status === 'REJECTED') {
                        $rejected++;
                    } else {
                        $pending++; // SUBMITTED, VERIFICATION, VERIFIED
                    }
                }
            }

            $districtStats[] = [
                'district'             => $r->district,
                'village'              => $r->village,
                'latitude'             => (float) $r->latitude,
                'longitude'            => (float) $r->longitude,
                'total'                => $total,
                'pending'              => $pending,
                'dispatched'           => $dispatched,
                'in_progress'          => $inProgress,
                'waiting_verification' => $waitingVerification,
                'completed'            => $completed,
                'rejected'             => $rejected,
            ];
        }

        // Data Titik Koordinat Peta Eksekutif (Lengkap dengan foto, pelapor, alamat, status)
        $mapPoints = Complaint::with(['category', 'user', 'photos', 'latestDisposition.assignee', 'latestDisposition.department'])
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->latest()
            ->take(150)
            ->get()
            ->map(function ($c) {
                $photo = $c->photos->first();
                $afterPhoto = $c->photos->where('type', 'resolution_after')->first() ?? $c->photos->last();
                return [
                    'id'                   => $c->id,
                    'public_code'          => $c->public_code,
                    'title'                => $c->title,
                    'description'          => $c->description ?? '-',
                    'address'              => $c->address ?? 'Wilayah Kabupaten Kolaka Utara',
                    'damage_type'          => $c->damage_type ?? 'minor',
                    'road_length'          => $c->road_length,
                    'road_condition'       => $c->road_condition,
                    'latitude'             => (float) $c->latitude,
                    'longitude'            => (float) $c->longitude,
                    'end_latitude'         => $c->end_latitude ? (float) $c->end_latitude : null,
                    'end_longitude'        => $c->end_longitude ? (float) $c->end_longitude : null,
                    'route_coordinates'    => $c->route_coordinates,
                    'status'               => $c->status,
                    'priority_level'       => $c->priority_level ?? 'LOW',
                    'severity_level'       => $c->severity_level,
                    'severity_text'        => $c->severity_text,
                    'category_name'        => $c->category?->name ?? 'Infrastruktur Umum',
                    'reporter_name'        => $c->user?->name ?? $c->citizen?->name ?? 'Warga Terdaftar',
                    'reporter_phone'       => $c->user?->phone ?? $c->citizen?->phone ?? '081344697178',
                    'created_at_human'     => $c->created_at?->diffForHumans() ?? '',
                    'created_at_formatted' => $c->created_at?->translatedFormat('d M Y, H:i') ?? '',
                    'photo_url'            => $photo ? ($photo->url ?? asset('storage/' . $photo->storage_key)) : null,
                    'photo_after_url'      => $afterPhoto && $afterPhoto->id !== ($photo?->id) ? ($afterPhoto->url ?? asset('storage/' . $afterPhoto->storage_key)) : null,
                    'department_name'      => $c->latestDisposition?->department?->name ?? 'Dinas Terkait',
                    'assignee_name'        => $c->latestDisposition?->assignee?->name ?? 'Petugas Lapangan',
                    'instruction'          => $c->latestDisposition?->instruction ?? '-',
                    'ai_category'          => $c->ai_category ?? ($c->category?->name ?? 'Kerusakan Fisik'),
                    'ai_confidence'        => $c->ai_confidence ? round($c->ai_confidence * 100) : 90,
                ];
            });

        // Data Detail Seluruh Aduan untuk Modal Pop-up (Tab 1, Tab 2, & Tab 3)
        $complaintsDetailMap = Complaint::with(['category', 'user', 'citizen', 'photos', 'latestDisposition.assignee', 'latestDisposition.department', 'latestDisposition.team.leader', 'aiAssessments'])
            ->latest()
            ->take(200)
            ->get()
            ->keyBy('id')
            ->map(function ($c) {
                $citizenPhotos = $c->photos->filter(function($p) {
                    return $p->type !== 'resolution_after';
                })->map(fn($p) => $p->url ?? asset('storage/' . $p->storage_key))->values();

                $officerPhotos = $c->photos->filter(function($p) {
                    return $p->type === 'resolution_after';
                })->map(fn($p) => $p->url ?? asset('storage/' . $p->storage_key))->values();

                $photo = $citizenPhotos->first() ?? ($c->photos->first() ? ($c->photos->first()->url ?? asset('storage/' . $c->photos->first()->storage_key)) : null);
                $afterPhoto = $officerPhotos->first() ?? null;
                $disp = $c->latestDisposition;
                $formattedDate = $c->created_at ? $c->created_at->translatedFormat('d M Y, H:i') . ' WITA' : '-';
                $citizenName = $c->user?->name ?? $c->citizen?->name ?? 'Warga Terdaftar';
                $citizenPhone = $c->user?->phone ?? $c->citizen?->phone ?? '-';
                $officerName = $disp?->assignee?->name ?? null;
                $officerPhone = $disp?->assignee?->phone ?? null;
                $deptName = $disp?->department?->name ?? 'Dinas Terkait';

                return [
                    'id'                   => $c->id,
                    'public_code'          => $c->public_code,
                    'title'                => $c->title,
                    'description'          => $c->description ?? '-',
                    'address'              => $c->address ?? 'Wilayah Kabupaten Kolaka Utara',
                    'latitude'             => (float) ($c->latitude ?? -3.50601),
                    'longitude'            => (float) ($c->longitude ?? 120.88780),
                    'status'               => $c->status,
                    'priority_level'       => $c->priority_level ?? 'LOW',
                    'severity_level'       => $c->severity_level,
                    'severity_text'        => $c->severity_text,
                    'category_name'        => $c->category?->name ?? 'Infrastruktur Umum',
                    'citizen_name'         => $citizenName,
                    'reporter_name'        => $citizenName,
                    'citizen_phone'        => $citizenPhone,
                    'reporter_phone'       => $citizenPhone,
                    'created_at_human'     => $c->created_at?->diffForHumans() ?? '',
                    'created_at_formatted' => $formattedDate,
                    'created_at_full'      => $formattedDate,
                    'photo_url'            => $photo,
                    'photo_after_url'      => $afterPhoto,
                    'completion_photo_url' => $afterPhoto,
                    'citizen_photos'       => $citizenPhotos->all(),
                    'officer_photos'       => $officerPhotos->all(),
                    'department_name'      => $deptName,
                    'assignee_name'        => $officerName,
                    'officer_name'         => $officerName,
                    'assignee_phone'       => $officerPhone,
                    'officer_phone'        => $officerPhone,
                    'deadline'             => $disp?->deadline ? \Illuminate\Support\Carbon::parse($disp->deadline)->locale('id')->isoFormat('D MMM Y') : null,
                    'instruction'          => $disp?->instruction ?? '-',
                    'ai_category'          => $c->ai_category ?? ($c->category?->name ?? 'Kerusakan Fisik'),
                    'ai_confidence'        => $c->ai_confidence ? round($c->ai_confidence * 100) : 90,
                    'ai_repair_estimate'   => $c->ai_repair_estimate,
                    'damage_type'          => $c->damage_type,
                    'damage_type_label'    => $c->damage_type_label,
                    'road_length'          => $c->road_length,
                    'road_condition'       => $c->road_condition,
                    'end_latitude'         => $c->end_latitude,
                    'end_longitude'        => $c->end_longitude,
                    'route_coordinates'    => $c->route_coordinates,
                    'team_name'            => $disp?->team?->name ?? null,
                    'team_code'            => $disp?->team?->code ?? null,
                    'team_leader'          => $disp?->team?->leader?->name ?? null,
                    'team_leader_phone'    => $disp?->team?->leader?->phone ?? null,
                    'photos_list'          => $c->photos->map(fn($p) => $p->url ?? asset('storage/' . $p->storage_key))->values(),
                ];
            });

        return view('bupati.dashboard', compact(
            'summary',
            'allComplaints',
            'archivedComplaints',
            'departments',
            'categories',
            'mapPoints',
            'complaintsDetailMap',
            'districtStats',
            'avgPerformance',
            'allComplaintsForReport'
        ));
    }
}
