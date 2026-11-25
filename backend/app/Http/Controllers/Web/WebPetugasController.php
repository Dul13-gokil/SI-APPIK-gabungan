<?php

namespace App\Http\Controllers\Web;

use App\Domain\Complaints\Services\WorkflowService;
use App\Http\Controllers\Controller;
use App\Models\Complaint;
use App\Models\Disposition;
use App\Models\Region;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class WebPetugasController extends Controller
{
    public function index(Request $request): View
    {
        $user = auth()->user();

        // Jika Super Admin, tampilkan SEMUA tugas penanganan fisik aktif
        if ($user->isSuperAdmin()) {
            $dispatchedTasks = Complaint::with(['category', 'photos', 'latestDisposition.assignee', 'citizen'])
                ->whereIn('status', ['VERIFIED', 'DISPATCHED'])
                ->latest('updated_at')
                ->get();

            $inProgressTasks = Complaint::with(['category', 'photos', 'latestDisposition.assignee', 'citizen'])
                ->whereIn('status', ['IN_PROGRESS', 'REVISION_REQUIRED'])
                ->latest('updated_at')
                ->get();

            $waitingVerificationTasks = Complaint::with(['category', 'photos', 'latestDisposition.assignee', 'citizen'])
                ->where('status', 'WAITING_VERIFICATION')
                ->latest('updated_at')
                ->get();

            $completedTasks = Complaint::with(['category', 'photos', 'latestDisposition.assignee'])
                ->whereIn('status', ['COMPLETED', 'CLOSED'])
                ->latest('updated_at')
                ->take(15)
                ->get();
        } else {
            // Pengawas lapangan: ambil tugas yang di-disposisi ke dirinya atau ke regunya
            $myTeam = $user->currentTeam();
            $teamId = $myTeam?->id;

            $assignedComplaintIds = Disposition::where(function($q) use ($user, $teamId) {
                $q->where('assigned_to', $user->id);
                if ($teamId) {
                    $q->orWhere('team_id', $teamId);
                }
            })->pluck('complaint_id')->unique();

            // 1. Tugas Penugasan Baru (Belum dimulai)
            $dispatchedTasks = Complaint::with(['category', 'photos', 'latestDisposition.assignee', 'latestDisposition.team', 'citizen'])
                ->whereIn('status', ['VERIFIED', 'DISPATCHED'])
                ->whereIn('id', $assignedComplaintIds)
                ->latest('updated_at')
                ->get();

            // 2. Tugas Pengerjaan Fisik Sedang Berjalan
            $inProgressTasks = Complaint::with(['category', 'photos', 'latestDisposition.assignee', 'latestDisposition.team', 'citizen'])
                ->whereIn('status', ['IN_PROGRESS', 'REVISION_REQUIRED'])
                ->whereIn('id', $assignedComplaintIds)
                ->latest('updated_at')
                ->get();

            // 3. Tugas Menunggu Validasi Operator
            $waitingVerificationTasks = Complaint::with(['category', 'photos', 'latestDisposition.assignee', 'latestDisposition.team', 'citizen'])
                ->where('status', 'WAITING_VERIFICATION')
                ->whereIn('id', $assignedComplaintIds)
                ->latest('updated_at')
                ->get();

            // 4. Tugas Selesai
            $completedTasks = Complaint::with(['category', 'photos', 'latestDisposition.team'])
                ->whereIn('id', $assignedComplaintIds)
                ->whereIn('status', ['COMPLETED', 'CLOSED'])
                ->latest('updated_at')
                ->take(15)
                ->get();
        }

        $myTeam = $user->currentTeam();

        $categories = \App\Models\Category::all();

        // 6 KPI Summary Metrics (Persis Gambar 5 dengan DITUGASKAN)
        $total = Complaint::count();
        $completedCount = Complaint::whereIn('status', ['COMPLETED', 'CLOSED'])->count();
        $dispatchedCount = Complaint::where('status', 'DISPATCHED')->count();
        $inProgressCount = Complaint::whereIn('status', ['IN_PROGRESS', 'REVISION_REQUIRED'])->count();
        $pendingCount = Complaint::whereIn('status', ['SUBMITTED', 'VERIFICATION'])->count();
        $waitingVerificationCount = Complaint::where('status', 'WAITING_VERIFICATION')->count();
        $completionRate = $total > 0 ? round(($completedCount / $total) * 100, 1) : 0;

        $summary = [
            'total'           => $total,
            'completed'       => $completedCount,
            'dispatched'      => $dispatchedCount,
            'in_progress'     => $inProgressCount,
            'pending'         => $pendingCount,
            'waiting_verification' => $waitingVerificationCount,
            'completion_rate' => $completionRate,
        ];

        // Peta Spasial Seluruh Titik Kerusakan Kabupaten
        $mapPoints = Complaint::with(['category', 'user', 'citizen', 'photos', 'latestDisposition.assignee'])
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->latest()
            ->take(150)
            ->get()
            ->map(function ($c) {
                $photo = $c->photos->first();
                return [
                    'id'                => $c->id,
                    'public_code'       => $c->public_code,
                    'title'             => $c->title,
                    'description'       => $c->description ?? '-',
                    'address'           => $c->address ?? 'Wilayah Kabupaten',
                    'damage_type'       => $c->damage_type ?? 'minor',
                    'road_length'       => $c->road_length,
                    'road_condition'    => $c->road_condition,
                    'latitude'          => (float) $c->latitude,
                    'longitude'         => (float) $c->longitude,
                    'end_latitude'      => $c->end_latitude ? (float) $c->end_latitude : null,
                    'end_longitude'     => $c->end_longitude ? (float) $c->end_longitude : null,
                    'route_coordinates' => $c->route_coordinates,
                    'status'            => $c->status,
                    'severity_level'    => $c->severity_level,
                    'severity_text'     => $c->severity_text,
                    'category_name'     => $c->category?->name ?? 'Infrastruktur',
                    'reporter_name'     => $c->user?->name ?? $c->citizen?->name ?? 'Warga Terdaftar',
                    'created_at_human'  => $c->created_at?->diffForHumans() ?? '',
                    'photo_url'         => $photo ? ($photo->url ?? asset('storage/' . $photo->storage_key)) : null,
                ];
            });

        // Data Detail Seluruh Tugas untuk Modal Pop-up Cepat Petugas (Zero Lag & Real-time)
        $allRelevantIds = $dispatchedTasks->pluck('id')
            ->merge($inProgressTasks->pluck('id'))
            ->merge($waitingVerificationTasks->pluck('id'))
            ->merge($completedTasks->pluck('id'))
            ->merge($mapPoints->pluck('id'))
            ->unique();

        $complaintsDetailMap = Complaint::with(['category', 'user', 'citizen', 'photos', 'latestDisposition.assignee', 'latestDisposition.department', 'latestDisposition.team.leader', 'aiAssessments'])
            ->whereIn('id', $allRelevantIds)
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
                return [
                    'id'                   => $c->id,
                    'public_code'          => $c->public_code,
                    'title'                => $c->title,
                    'description'          => $c->description ?? '-',
                    'address'              => $c->address ?? 'Wilayah Kabupaten',
                    'latitude'             => (float) ($c->latitude ?? -3.50601),
                    'longitude'            => (float) ($c->longitude ?? 120.88780),
                    'status'               => $c->status,
                    'severity_level'       => $c->severity_level,
                    'severity_text'        => $c->severity_text,
                    'category_name'        => $c->category?->name ?? 'Infrastruktur Umum',
                    'reporter_name'        => $c->citizen?->name ?? $c->user?->name ?? 'Warga Terdaftar',
                    'reporter_phone'       => $c->citizen?->phone ?? $c->user?->phone ?? '-',
                    'created_at_human'     => $c->created_at?->diffForHumans() ?? '',
                    'created_at_formatted' => $c->created_at?->translatedFormat('d M Y, H:i') . ' WITA' ?? '',
                    'created_at_full'      => $c->created_at?->translatedFormat('d M Y, H:i') . ' WITA' ?? '',
                    'deadline_human'       => $disp?->deadline ? $disp->deadline->diffForHumans() : 'Segera',
                    'deadline_date'        => $disp?->deadline ? $disp->deadline->translatedFormat('d M Y') : null,
                    'deadline'             => $disp?->deadline ? $disp->deadline->translatedFormat('d M Y') : null,
                    'instruction'          => $disp?->instruction ?? 'Lakukan penanganan fisik sesuai standar teknis.',
                    'department_name'      => $disp?->department?->name ?? 'Dinas Teknis',
                    'assignee_name'        => $disp?->assignee?->name ?? 'Pengawas Lapangan',
                    'assignee_phone'       => $disp?->assignee?->phone ?? null,
                    'ai_repair_estimate'   => $c->ai_repair_estimate,
                    'photo_url'            => $photo,
                    'photo_after_url'      => $afterPhoto,
                    'completion_photo_url' => $afterPhoto,
                    'citizen_photos'       => $citizenPhotos->all(),
                    'officer_photos'       => $officerPhotos->all(),
                    'team_name'            => $disp?->team?->name ?? null,
                    'team_code'            => $disp?->team?->code ?? null,
                    'team_leader'          => $disp?->team?->leader?->name ?? null,
                    'team_leader_phone'    => $disp?->team?->leader?->phone ?? null,
                    'damage_type'          => $c->damage_type ?? 'minor',
                    'damage_type_label'    => $c->damage_type_label,
                    'road_length'          => $c->road_length,
                    'road_condition'       => $c->road_condition,
                    'end_latitude'         => $c->end_latitude,
                    'end_longitude'        => $c->end_longitude,
                    'route_coordinates'    => $c->route_coordinates,
                    'photos_list'          => $c->photos->map(fn($p) => $p->url ?? asset('storage/' . $p->storage_key))->values(),
                ];
            });

        $regionsDb = Region::orderBy('district')->get();
        $allComplaintsForStats = Complaint::select('id', 'region_id', 'address', 'status')->get();
        $districtStats = [];
        foreach ($regionsDb as $r) {
            $distName = strtolower(str_replace('kecamatan ', '', $r->district));
            $villName = strtolower(str_replace(['desa ', 'kelurahan '], '', $r->village));
            $total = 0; $completed = 0; $inProgress = 0; $waitingVerification = 0; $dispatched = 0; $pending = 0; $rejected = 0;
            foreach ($allComplaintsForStats as $cmp) {
                $addr = strtolower($cmp->address ?? '');
                if ($cmp->region_id === $r->id || str_contains($addr, $distName) || str_contains($addr, $villName)) {
                    $total++;
                    if (in_array($cmp->status, ['COMPLETED', 'CLOSED'], true)) { $completed++; }
                    elseif (in_array($cmp->status, ['IN_PROGRESS', 'REVISION_REQUIRED'], true)) { $inProgress++; }
                    elseif ($cmp->status === 'WAITING_VERIFICATION') { $waitingVerification++; }
                    elseif ($cmp->status === 'DISPATCHED') { $dispatched++; }
                    elseif ($cmp->status === 'REJECTED') { $rejected++; }
                    else { $pending++; }
                }
            }
            $districtStats[] = [
                'id' => $r->id,
                'district' => $r->district,
                'village' => $r->village,
                'latitude' => (float) $r->latitude,
                'longitude' => (float) $r->longitude,
                'total' => $total,
                'pending' => $pending,
                'dispatched' => $dispatched,
                'in_progress' => $inProgress,
                'waiting_verification' => $waitingVerification,
                'completed' => $completed,
                'rejected' => $rejected,
            ];
        }

        return view('petugas.dashboard', compact('dispatchedTasks', 'inProgressTasks', 'waitingVerificationTasks', 'completedTasks', 'categories', 'summary', 'mapPoints', 'complaintsDetailMap', 'myTeam', 'districtStats'));
    }

    public function startWork(int $id): RedirectResponse
    {
        $complaint = Complaint::findOrFail($id);

        app(WorkflowService::class)->startWork($complaint, auth()->user(), request()->ip());

        return back()->with('success', "Pekerjaan untuk laporan #{$complaint->public_code} resmi DIMULAI.");
    }

    public function finishWork(Request $request, int $id): RedirectResponse
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
            auth()->user(),
            $validated['note'],
            WorkflowService::photosFrom($request),
            isset($validated['latitude']) ? (float) $validated['latitude'] : null,
            isset($validated['longitude']) ? (float) $validated['longitude'] : null,
            $request->ip()
        );

        return back()->with(
            'success',
            $autoCompleted
                ? "Pekerjaan laporan #{$complaint->public_code} dinyatakan selesai otomatis oleh AI."
                : "Bukti laporan #{$complaint->public_code} diterima, tetapi AI masih mendeteksi kerusakan. Perbaikan dikembalikan untuk diulang."
        );
    }

    public function updateProfile(Request $request): RedirectResponse
    {
        $user = auth()->user();

        $validated = $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|max:255|unique:users,email,' . $user->id,
            'phone'    => ['nullable', 'string', 'regex:/^(?:\+62|62|0)8[1-9][0-9]{7,10}$/', 'unique:users,phone,' . $user->id],
            'password' => ['nullable', 'string', 'min:8', 'regex:/[A-Z]/', 'regex:/[a-z]/', 'regex:/[0-9]/', 'confirmed'],
        ], [
            'name.required'      => 'Nama lengkap wajib diisi.',
            'email.required'     => 'Email wajib diisi.',
            'email.unique'       => 'Email tersebut telah digunakan oleh akun lain.',
            'phone.regex'        => 'Format nomor telepon tidak valid. Nomor Indonesia harus 10 hingga 13 digit dan diawali dengan 08 atau +628.',
            'phone.unique'       => 'Nomor telepon tersebut telah terdaftar pada akun lain.',
            'password.min'       => 'Kata sandi minimal 8 karakter.',
            'password.regex'     => 'Kata sandi baru harus kombinasi huruf besar, huruf kecil, dan angka.',
            'password.confirmed' => 'Konfirmasi kata sandi baru tidak sesuai.',
        ]);

        $user->name = $validated['name'];
        $user->email = $validated['email'];
        $user->phone = $validated['phone'] ?? null;

        $passwordChanged = false;
        if (!empty($validated['password'])) {
            $user->password = bcrypt($validated['password']);
            $passwordChanged = true;
        }

        $user->save();

        if ($passwordChanged) {
            app(\App\Services\SecurityMailService::class)->sendPasswordChangedAlert(
                $user, 
                $request->ip(), 
                $request->userAgent(),
                $request->input('latitude') ? (float)$request->input('latitude') : null,
                $request->input('longitude') ? (float)$request->input('longitude') : null
            );
        }

        return back()->with('success', 'Profil dan informasi akun Anda berhasil diperbarui.');
    }
}
