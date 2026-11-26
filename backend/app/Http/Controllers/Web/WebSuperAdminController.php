<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Complaint;
use App\Models\Department;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\View\View;

class WebSuperAdminController extends Controller
{
    public function index(Request $request): View
    {
        $totalComplaints = Complaint::count();
        $completedComplaints = Complaint::whereIn('status', ['COMPLETED', 'CLOSED'])->count();
        $dispatchedComplaints = Complaint::where('status', 'DISPATCHED')->count();
        $inProgressComplaints = Complaint::whereIn('status', ['IN_PROGRESS', 'REVISION_REQUIRED'])->count();
        $pendingComplaints = Complaint::whereIn('status', ['SUBMITTED', 'VERIFICATION'])->count();
        $rejectedComplaints = Complaint::where('status', 'REJECTED')->count();
        $waitingComplaints = Complaint::where('status', 'WAITING_VERIFICATION')->count();
        $completionRate = $totalComplaints > 0 ? round(($completedComplaints / $totalComplaints) * 100, 1) : 0;

        $summary = [
            'total'                => $totalComplaints,
            'total_complaints'     => $totalComplaints,
            'pending'              => $pendingComplaints,
            'pending_verification' => $pendingComplaints,
            'dispatched'           => $dispatchedComplaints,
            'dispatched_count'     => $dispatchedComplaints,
            'in_progress'          => $inProgressComplaints,
            'in_progress_count'    => $inProgressComplaints,
            'waiting_verification' => $waitingComplaints,
            'waiting_validation'   => $waitingComplaints,
            'completed'            => $completedComplaints,
            'completed_count'      => $completedComplaints,
            'rejected'             => $rejectedComplaints,
            'completion_rate'      => $completionRate,
            'total_operators'      => User::whereIn('role', ['OPERATOR', 'DINAS'])->count(),
            'total_field_workers'  => User::where('role', 'PETUGAS')->count(),
        ];

        $activeTab = $request->get('tab', 'overview');

        // Daftar Aduan memuat seluruh status, termasuk yang selesai dan ditolak, sehingga
        // tab "Arsip Aduan" dan "Aduan Ditolak" tidak lagi diperlukan sebagai halaman terpisah.
        $query = Complaint::withoutGlobalScope('excludeAdminRejected')
            ->with(['category', 'user', 'citizen', 'photos', 'latestDisposition.assignee', 'latestDisposition.department', 'latestDisposition.team.leader', 'aiAssessments'])
            ->latest();

        if ($request->filled('status')) {
            if ($request->status === 'ARCHIVED') {
                $query->whereIn('status', ['COMPLETED', 'CLOSED']);
            } else {
                $query->where('status', $request->status);
            }
        }

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function($q) use ($s) {
                $q->where('public_code', 'like', "%{$s}%")
                  ->orWhere('title', 'like', "%{$s}%")
                  ->orWhere('address', 'like', "%{$s}%")
                  ->orWhereHas('user', function($uq) use ($s) {
                      $uq->where('name', 'like', "%{$s}%");
                  });
            });
        }

        $complaints = $query->take(200)->get();

        $users = User::orderByRaw("
            CASE 
                WHEN role IN ('SUPERADMIN', 'ADMIN') THEN 1 
                WHEN role IN ('KEPALA_DAERAH', 'BUPATI') THEN 2 
                WHEN role IN ('OPERATOR', 'DINAS') THEN 3 
                WHEN role = 'PETUGAS' THEN 4 
                ELSE 5 
            END
        ")->latest()->get();

        $bupati = User::whereIn('role', ['KEPALA_DAERAH', 'BUPATI'])->first();
        $categories = Category::withCount('complaints')->get();
        $departments = Department::withCount([
            'dispositions as total_tasks',
            'dispositions as completed_tasks' => function ($q) {
                $q->where('status', 'COMPLETED');
            },
        ])->get();

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
        $regions = \App\Models\Region::withCount('complaints')->orderBy('district')->get();

        $allComplaintsForStats = Complaint::select('id', 'region_id', 'address', 'status')->get()
            // Alamat dinormalkan sekali; sebelumnya strtolower dipanggil ulang untuk setiap
            // pasangan wilayah x aduan sehingga biayanya tumbuh kuadratik.
            ->map(fn ($c) => [
                'region_id' => $c->region_id,
                'address'   => strtolower($c->address ?? ''),
                'status'    => $c->status,
            ])
            ->all();

        $districtStats = [];
        foreach ($regions as $r) {
            $distName = strtolower(str_replace(['kecamatan ', 'kec. '], '', $r->district));
            $villName = strtolower(str_replace(['desa ', 'kelurahan '], '', $r->village));

            $total = 0;
            $completed = 0;
            $inProgress = 0;
            $waitingVerification = 0;
            $dispatched = 0;
            $pending = 0;
            $rejected = 0;

            foreach ($allComplaintsForStats as $cmp) {
                $addr = $cmp['address'];
                if ($cmp['region_id'] === $r->id || str_contains($addr, $distName) || str_contains($addr, $villName)) {
                    $total++;
                    if (in_array($cmp['status'], ['COMPLETED', 'CLOSED'], true)) {
                        $completed++;
                    } elseif (in_array($cmp['status'], ['IN_PROGRESS', 'REVISION_REQUIRED'], true)) {
                        $inProgress++;
                    } elseif ($cmp['status'] === 'WAITING_VERIFICATION') {
                        $waitingVerification++;
                    } elseif ($cmp['status'] === 'DISPATCHED') {
                        $dispatched++;
                    } elseif ($cmp['status'] === 'REJECTED') {
                        $rejected++;
                    } else {
                        $pending++; // SUBMITTED, VERIFICATION, VERIFIED
                    }
                }
            }

            $districtStats[] = [
                'id'                   => $r->id,
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

        $mapPoints = Complaint::with(['category', 'user', 'citizen', 'photos'])
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->latest()
            ->take(150)
            ->get()
            ->map(function ($c) {
                $photo = $c->photos->first();
                return [
                    'id'               => $c->id,
                    'public_code'      => $c->public_code,
                    'title'            => $c->title,
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
                    'category_name'    => $c->category?->name ?? 'Infrastruktur Umum',
                    'reporter_name'    => $c->user?->name ?? $c->citizen?->name ?? 'Warga Terdaftar',
                    'created_at_human' => $c->created_at?->diffForHumans() ?? '',
                    'photo_url'        => $photo ? ($photo->url ?? asset('storage/' . $photo->storage_key)) : null,
                ];
            });

        // Arsip Aduan resmi (Selesai, Dibatalkan Warga, dan Ditolak oleh Operator — otomatis tidak termasuk penolakan Super Admin karena global scope excludeAdminRejected)
        $archivedComplaints = Complaint::with(['category', 'user', 'citizen', 'photos', 'latestDisposition.team.leader', 'latestDisposition.assignee.teams.leader', 'latestDisposition.department', 'statusHistories', 'aiAssessments'])
            ->whereIn('status', ['COMPLETED', 'CLOSED', 'REJECTED', 'CANCELLED'])
            ->latest('updated_at')
            ->get();

        // Arsip laporan yang ditolak khusus oleh Super Admin (hanya tampil untuk Super Admin)
        $adminRejectedComplaints = Complaint::withoutGlobalScope('excludeAdminRejected')
            ->where('rejected_by_superadmin', true)
            ->with(['category', 'user', 'citizen', 'photos', 'adminRejector'])
            ->latest('admin_rejected_at')
            ->get();

        // Gabungkan seluruh laporan (aktif + arsip + ditolak admin) ke peta detail modal agar semua tab selalu bisa dibuka
        $allForDetailMap = $complaints
            ->concat($archivedComplaints)
            ->concat($adminRejectedComplaints)
            ->keyBy('id');

        $complaintsDetailMap = $allForDetailMap->map(function ($c) {
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
                'address'              => $c->address ?? 'Wilayah Kabupaten',
                'latitude'             => (float) ($c->latitude ?? -3.50601),
                'longitude'            => (float) ($c->longitude ?? 120.88780),
                'status'               => $c->status,
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
                'damage_type'          => $c->damage_type ?? 'minor',
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

        $teams = \App\Models\Team::with(['leader', 'members.user', 'department', 'dispositions'])->get();
        $dispatchMode = \App\Models\SystemSetting::get('dispatch_mode', 'HYBRID');
        
        // Petugas yang sudah terdaftar di sebuah regu (anggota maupun ketua)
        $assignedOfficerIds = \App\Models\TeamMember::pluck('user_id')
            ->merge(\App\Models\Team::whereNotNull('leader_id')->pluck('leader_id'))
            ->unique();
        $availableOfficers = User::where('role', 'PETUGAS')->orderBy('name')->get();
        $unassignedOfficers = User::whereIn('role', ['PETUGAS', 'PENGAWAS'])->whereNotIn('id', $assignedOfficerIds)->orderBy('name')->get();

        // Daftar 15 kecamatan resmi Kabupaten terdaftar
        $districts = $regions->pluck('district')->filter()->unique()->sort()->values();

        // Cek limit role sistem (Maksimal 1 Bupati, Maksimal 1 Super Admin)
        $hasBupati = User::whereIn('role', ['KEPALA_DAERAH', 'BUPATI'])->exists();
        $hasSuperAdmin = User::whereIn('role', ['SUPERADMIN', 'ADMIN'])->exists();

        return view('admin.dashboard', compact(
            'summary', 'complaints', 'users', 'categories', 'departments', 
            'activeTab', 'regions', 'districtStats', 'mapPoints', 'complaintsDetailMap',
            'teams', 'dispatchMode', 'availableOfficers', 'unassignedOfficers', 'districts',
            'avgPerformance', 'allComplaintsForReport', 'hasBupati', 'hasSuperAdmin',
            'archivedComplaints', 'adminRejectedComplaints'
        ));
    }

    public function updateCoordinates(Request $request, int $id): \Illuminate\Http\JsonResponse
    {
        $validated = $request->validate([
            'latitude'  => 'required|numeric',
            'longitude' => 'required|numeric',
        ]);

        $region = \App\Models\Region::findOrFail($id);
        $region->update([
            'latitude'  => $validated['latitude'],
            'longitude' => $validated['longitude'],
        ]);

        return response()->json([
            'success' => true,
            'message' => "Titik koordinat {$region->district} ({$region->village}) berhasil diperbarui ke [{$region->latitude}, {$region->longitude}].",
            'region'  => $region,
        ]);
    }

    public function storeRegion(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'district'  => 'required|string|max:100',
            'village'   => 'required|string|max:100',
            'latitude'  => 'required|numeric',
            'longitude' => 'required|numeric',
        ]);

        \App\Models\Region::create([
            'province'  => 'Sulawesi Tenggara',
            'regency'   => 'Kabupaten',
            'district'  => $validated['district'],
            'village'   => $validated['village'],
            'latitude'  => $validated['latitude'],
            'longitude' => $validated['longitude'],
        ]);

        return redirect()->route('admin.dashboard', ['tab' => 'regions-gis'])
            ->with('success', "Wilayah {$validated['district']} ({$validated['village']}) berhasil ditambahkan ke pemetaan GIS.");
    }

    public function deleteRegion(int $id): RedirectResponse
    {
        $region = \App\Models\Region::findOrFail($id);
        $name = "{$region->district} - {$region->village}";
        $region->delete();

        return redirect()->route('admin.dashboard', ['tab' => 'regions-gis'])
            ->with('success', "Wilayah {$name} berhasil dihapus dari pemetaan GIS.");
    }

    public function storeUser(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:users,email',
            'phone'    => 'nullable|string|max:20|unique:users,phone',
            'role'     => 'required|in:ADMIN,SUPERADMIN,KEPALA_DAERAH,BUPATI,OPERATOR,PETUGAS,PENGAWAS,DINAS,MONITORING,WARGA',
            'password' => ['nullable', 'string', 'min:8', 'regex:/[A-Za-z]/', 'regex:/[0-9]/'],
        ], [
            'phone.unique'  => 'Nomor handphone ini sudah terdaftar pada akun pengguna lain.',
            'password.min'  => 'Kata sandi minimal 8 karakter.',
            'password.regex'=> 'Kata sandi harus memuat huruf dan angka.',
        ]);

        // Aturan 1: Akun WARGA disembunyikan dan dilarang dibuat oleh Super Admin dari web
        if ($validated['role'] === 'WARGA') {
            return back()->with('error', 'Pendaftaran akun Warga hanya dapat dilakukan secara mandiri oleh masyarakat melalui Aplikasi Mobile SI-APPIK demi keaslian data kependudukan.');
        }

        // Aturan 2: Batasi Kepala Daerah (Bupati) maksimal 1 akun
        if (in_array($validated['role'], ['KEPALA_DAERAH', 'BUPATI'])) {
            $existingBupatiCount = User::whereIn('role', ['KEPALA_DAERAH', 'BUPATI'])->count();
            if ($existingBupatiCount >= 1) {
                return back()->with('error', 'Sistem hanya mengizinkan 1 akun Kepala Daerah Kabupaten (Bupati). Akun sudah ada.');
            }
        }

        // Aturan 3: Batasi Super Admin maksimal 1 akun
        if (in_array($validated['role'], ['ADMIN', 'SUPERADMIN'])) {
            $existingAdminCount = User::whereIn('role', ['ADMIN', 'SUPERADMIN'])->count();
            if ($existingAdminCount >= 1) {
                return back()->with('error', 'Sistem hanya mengizinkan 1 akun Super Administrator. Akun sudah ada.');
            }
        }

        // Sandi: bila Super Admin mengisi sendiri, itulah yang dipakai (tanpa wajib ganti).
        // Bila dibiarkan kosong, sistem membuat sandi sementara acak yang ditampilkan sekali
        // dan wajib diganti saat login pertama.
        $sandiDitetapkan = trim((string) ($validated['password'] ?? '')) !== '';
        $defaultPassword = $sandiDitetapkan ? $validated['password'] : Str::password(10, symbols: false);

        $user = User::create([
            'name'                 => $validated['name'],
            'email'                => $validated['email'],
            'phone'                => $validated['phone'] ?? null,
            'password'             => Hash::make($defaultPassword),
            'role'                 => $validated['role'],
            'must_change_password' => ! $sandiDitetapkan,
        ]);

        // Aturan 5: Kirim email pemberitahuan ke email pengguna jika ditambahkan sebagai PETUGAS atau OPERATOR
        if (in_array($validated['role'], ['PETUGAS', 'OPERATOR', 'DINAS', 'PENGAWAS', 'MONITORING'], true)) {
            try {
                app(\App\Services\SecurityMailService::class)->sendStaffInvitationEmail($user, $validated['role'], $defaultPassword);
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::warning("Gagal mengirim email undangan akun staf ke {$user->email}: " . $e->getMessage());
            }
        }

        $pesan = $sandiDitetapkan
            ? "Akun {$validated['name']} berhasil ditambahkan dengan sandi yang Anda tetapkan."
            : "Akun {$validated['name']} berhasil ditambahkan. Sandi sementara: {$defaultPassword} — catat dan berikan ke yang bersangkutan; wajib diganti saat login pertama.";

        return back()->with('success', $pesan);
    }

    public function updateUser(Request $request, int $id): RedirectResponse
    {
        $user = User::findOrFail($id);

        $validated = $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:users,email,' . $user->id,
            'phone'    => 'nullable|string|max:20|unique:users,phone,' . $user->id,
            'role'     => 'nullable|string',
            'password' => ['nullable', 'string', 'min:8', 'regex:/[A-Za-z]/', 'regex:/[0-9]/'],
        ], [
            'phone.unique'   => 'Nomor handphone ini sudah terdaftar pada akun pengguna lain.',
            'password.min'   => 'Kata sandi minimal 8 karakter.',
            'password.regex' => 'Kata sandi harus memuat huruf dan angka.',
        ]);

        // Simpan data lama untuk komparasi jejak audit & notifikasi keamanan
        $oldName  = $user->name;
        $oldEmail = $user->email;
        $oldPhone = $user->phone;
        $changes  = [];

        if (trim($oldName) !== trim($validated['name'])) {
            $changes['Nama Lengkap'] = "Diperbarui dari \"{$oldName}\" menjadi \"{$validated['name']}\"";
        }

        if (strtolower(trim($oldEmail)) !== strtolower(trim($validated['email']))) {
            $changes['Alamat Email Akun'] = "Dialihkan dari {$oldEmail} menjadi {$validated['email']}";
        }

        $newPhone = $validated['phone'] ? trim($validated['phone']) : null;
        if (trim((string)$oldPhone) !== trim((string)$newPhone)) {
            $changes['Nomor Handphone / WA'] = "Diperbarui dari " . ($oldPhone ?: '-') . " menjadi " . ($newPhone ?: '-');
        }

        // Peran boleh diperbarui, dengan penjaga agar tidak ada peran tunggal yang hilang
        // atau akun pimpinan kehilangan aksesnya sendiri.
        $peranBaru = strtoupper((string) ($validated['role'] ?? $user->role));

        if ($peranBaru !== $user->role) {
            $pesanTolak = $this->penolakanPerubahanPeran($user, $peranBaru);

            if ($pesanTolak !== null) {
                return back()->with('error', $pesanTolak);
            }

            $changes['Peran Akun'] = 'Diubah dari "' . $this->labelPeran($user->role) . '" menjadi "' . $this->labelPeran($peranBaru) . '"';

            // Peran non-lapangan tidak boleh tertinggal di dalam regu.
            if (! in_array($peranBaru, ['PETUGAS', 'PENGAWAS'], true)) {
                \App\Models\Team::where('leader_id', $user->id)->update(['leader_id' => null]);
                \App\Models\TeamMember::where('user_id', $user->id)->delete();
            }

            $user->role = $peranBaru;
        }

        $user->name  = $validated['name'];
        $user->email = $validated['email'];
        $user->phone = $newPhone;

        // Sandi hanya berubah bila kolomnya diisi; kolom kosong berarti tidak diubah.
        $sandiBaru = trim((string) ($validated['password'] ?? ''));

        if ($sandiBaru !== '') {
            $user->password = Hash::make($sandiBaru);
            $user->must_change_password = false;
            $changes['Kata Sandi'] = 'Diperbarui oleh Super Admin.';
        }

        $user->save();

        // Kirim email notifikasi keamanan resmi jika ada data profil yang berubah
        if (!empty($changes)) {
            try {
                app(\App\Services\SecurityMailService::class)->sendProfileUpdatedByAdminAlert(
                    $user, 
                    $changes, 
                    $oldEmail
                );
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::warning("Gagal mengirim email pemberitahuan update akun ke {$user->email}: " . $e->getMessage());
            }
        }

        return back()->with('success', "Data profil akun {$user->name} berhasil diperbarui oleh Super Admin. Notifikasi keamanan telah dikirimkan ke email terkait.");
    }

    /**
     * Alasan penolakan perubahan peran, atau null bila boleh dilanjutkan.
     */
    private function penolakanPerubahanPeran(User $user, string $peranBaru): ?string
    {
        if ($peranBaru === 'WARGA') {
            return 'Peran Warga tidak dapat ditetapkan dari portal; akun warga hanya lahir dari pendaftaran di aplikasi mobile.';
        }

        if ($user->id === auth()->id()) {
            return 'Proteksi Keamanan: Anda tidak dapat mengubah peran akun Anda sendiri.';
        }

        if (in_array($peranBaru, ['KEPALA_DAERAH', 'BUPATI'], true)) {
            $lain = User::whereIn('role', ['KEPALA_DAERAH', 'BUPATI'])->where('id', '<>', $user->id)->count();

            if ($lain > 0) {
                return 'Sistem hanya mengizinkan 1 akun Kepala Daerah Kabupaten. Akun tersebut sudah ada.';
            }
        }

        if (in_array($peranBaru, ['ADMIN', 'SUPERADMIN'], true)) {
            $lain = User::whereIn('role', ['ADMIN', 'SUPERADMIN'])->where('id', '<>', $user->id)->count();

            if ($lain > 0) {
                return 'Sistem hanya mengizinkan 1 akun Super Administrator. Akun tersebut sudah ada.';
            }
        }

        return null;
    }

    private function labelPeran(string $role): string
    {
        return [
            'SUPERADMIN'    => 'Super Administrator',
            'ADMIN'         => 'Super Administrator',
            'KEPALA_DAERAH' => 'Kepala Daerah',
            'BUPATI'        => 'Kepala Daerah',
            'DINAS'         => 'Kepala Dinas',
            'OPERATOR'      => 'Operator PUPR',
            'PENGAWAS'      => 'Pengawas Lapangan (Ketua Regu)',
            'PETUGAS'       => 'Pengawas Lapangan (Anggota Regu)',
            'MONITORING'    => 'Monitoring',
            'WARGA'         => 'Warga',
        ][strtoupper($role)] ?? $role;
    }

    public function deactivateUser(Request $request, int $id): RedirectResponse
    {
        $user = User::findOrFail($id);

        // 1. Anti-Self-Lockout Protection
        if ($user->id === auth()->id()) {
            return back()->with('error', 'Proteksi Keamanan: Super Admin tidak dapat menonaktifkan akun dirinya sendiri.');
        }

        // 2. Peer Protection (Re-Authentication untuk Akun Pimpinan / Sesama Admin)
        $isHighPrivilege = in_array(strtoupper($user->role), ['ADMIN', 'SUPERADMIN', 'BUPATI', 'KEPALA_DAERAH'], true);
        if ($isHighPrivilege) {
            $adminPassword = $request->input('admin_password');
            if (empty($adminPassword) || !Hash::check($adminPassword, auth()->user()->password)) {
                return back()->with('error', 'Verifikasi Gagal: Kata sandi Super Admin yang Anda masukkan salah. Penonaktifan akun pimpinan/administrator dibatalkan.');
            }
        }

        $validated = $request->validate([
            'reason'                 => 'required|string|min:10|max:500',
            'confirmation_statement' => 'required|string',
        ], [
            'reason.required'                 => 'Alasan penonaktifan wajib diisi.',
            'reason.min'                      => 'Alasan penonaktifan minimal 10 karakter agar dapat dipertanggungjawabkan.',
            'confirmation_statement.required' => 'Pernyataan konfirmasi wajib diketik ulang.',
        ]);

        $expectedStatement = 'NONAKTIFKAN ' . $user->email;
        if (strcasecmp(trim($validated['confirmation_statement']), $expectedStatement) !== 0) {
            return back()->with('error', "Pernyataan konfirmasi tidak sesuai. Harap ketik persis: {$expectedStatement}");
        }

        $reason = trim($validated['reason']);
        $user->update([
            'is_active'           => false,
            'deactivation_reason' => $reason,
            'deactivated_at'      => now(),
            'deactivated_by'      => auth()->id(),
        ]);

        // Cabut seluruh sesi aktif mobile/web pengguna
        try {
            $user->tokens()->delete();
        } catch (\Throwable $e) {
            // Abaikan jika token belum ada
        }

        // Catat di Audit Trail
        \App\Models\AuditLog::create([
            'actor_id'    => auth()->id(),
            'action'      => 'user.deactivated',
            'entity_type' => User::class,
            'entity_id'   => $user->id,
            'before_json' => ['is_active' => true],
            'after_json'  => ['is_active' => false, 'reason' => $reason],
            'ip'          => $request->ip(),
        ]);

        // Kirim email pemberitahuan resmi ke pengguna
        $mailSent = false;
        try {
            $mailSent = app(\App\Services\SecurityMailService::class)->sendAccountStatusChangedAlert($user, false, $reason);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning("Gagal mengirim email penonaktifan ke {$user->email}: " . $e->getMessage());
        }

        $mailNote = $mailSent && config('mail.default') !== 'log'
            ? "Notifikasi email resmi telah dikirim ke {$user->email}."
            : "Email notifikasi tidak terkirim (periksa konfigurasi MAIL_MAILER / layanan email HTTP di Railway).";

        return back()->with('success', "Akun {$user->name} berhasil dinonaktifkan. {$mailNote}");
    }

    public function reactivateUser(Request $request, int $id): RedirectResponse
    {
        $user = User::findOrFail($id);
        $reason = trim((string) $request->input('reason', ''));

        $user->update([
            'is_active'           => true,
            'deactivation_reason' => null,
            'deactivated_at'      => null,
            'deactivated_by'      => null,
        ]);

        \App\Models\AuditLog::create([
            'actor_id'    => auth()->id(),
            'action'      => 'user.reactivated',
            'entity_type' => User::class,
            'entity_id'   => $user->id,
            'before_json' => ['is_active' => false],
            'after_json'  => ['is_active' => true, 'reason' => $reason ?: 'Diaktifkan kembali oleh Super Admin'],
            'ip'          => $request->ip(),
        ]);

        $mailSent = false;
        try {
            $mailSent = app(\App\Services\SecurityMailService::class)->sendAccountStatusChangedAlert(
                $user,
                true,
                $reason ?: 'Akun telah diaktifkan kembali oleh Super Administrator.'
            );
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning("Gagal mengirim email pengaktifan ke {$user->email}: " . $e->getMessage());
        }

        $mailNote = $mailSent && config('mail.default') !== 'log'
            ? "Notifikasi email resmi telah dikirim ke {$user->email}."
            : "Email notifikasi tidak terkirim (periksa konfigurasi MAIL_MAILER / layanan email HTTP di Railway).";

        return back()->with('success', "Akun {$user->name} berhasil diaktifkan kembali. {$mailNote}");
    }

    public function resetUserPassword(Request $request, int $id): RedirectResponse
    {
        $user = User::findOrFail($id);
        $newPass = Str::password(10, symbols: false);

        $user->password = Hash::make($newPass);
        $user->must_change_password = true;
        $user->save();

        // Cabut seluruh sesi aktif mobile / web pengguna demi keamanan
        try {
            $user->tokens()->delete();
        } catch (\Throwable $e) {
            // Abaikan jika personal access token belum terinisialisasi
        }

        // Kirim email pemberitahuan resmi ke email akun terkait
        $mailSent = false;
        try {
            $changes = [
                'Kata Sandi' => 'Direset ke sandi default ("password") melalui Quick Action oleh Super Administrator. Pengguna diwajibkan mengganti kata sandi saat masuk kembali.',
            ];
            $mailSent = app(\App\Services\SecurityMailService::class)->sendProfileUpdatedByAdminAlert(
                $user, 
                $changes, 
                $user->email
            );
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning("Gagal mengirim email reset kata sandi ke {$user->email}: " . $e->getMessage());
        }

        $mailNote = $mailSent && config('mail.default') !== 'log'
            ? "Email pemberitahuan resmi telah dikirim ke {$user->email}."
            : "Email gagal terkirim (periksa konfigurasi MAIL_MAILER / layanan email HTTP di Railway).";

        return back()->with('success', "Kata sandi akun {$user->name} berhasil direset. Sandi sementara: {$newPass} — catat dan berikan ke yang bersangkutan; wajib diganti saat login pertama. {$mailNote}");
    }

    public function rejectComplaint(Request $request, int $id): RedirectResponse
    {
        $complaint = Complaint::withoutGlobalScope('excludeAdminRejected')->findOrFail($id);

        $validated = $request->validate([
            'reason'                 => 'required|string|min:10|max:500',
            'confirmation_statement' => 'required|string',
        ], [
            'reason.required'                 => 'Alasan penolakan wajib diisi.',
            'reason.min'                      => 'Alasan penolakan minimal 10 karakter agar dapat dipertanggungjawabkan.',
            'confirmation_statement.required' => 'Pernyataan konfirmasi wajib diketik ulang.',
        ]);

        $expectedCode = '#' . $complaint->public_code;
        $typed = trim($validated['confirmation_statement']);
        if (strcasecmp($typed, $expectedCode) !== 0 && strcasecmp($typed, $complaint->public_code) !== 0) {
            return back()->with('error', "Pernyataan konfirmasi tidak sesuai. Harap ketik persis kode laporan: {$expectedCode}");
        }

        try {
            \App\Domain\Complaints\Services\ComplaintStateMachine::validateAdminRejection($complaint->status);
        } catch (\InvalidArgumentException $e) {
            return back()->with('error', $e->getMessage());
        }

        $fromStatus = $complaint->status;
        $reason = trim($validated['reason']);

        $complaint->update([
            'status'                 => \App\Domain\Complaints\Enums\ComplaintStatus::REJECTED->value,
            'rejected_by_superadmin' => true,
            'admin_rejection_reason' => $reason,
            'admin_rejected_at'      => now(),
            'admin_rejected_by'      => auth()->id(),
        ]);

        \App\Models\ComplaintStatusHistory::create([
            'complaint_id' => $complaint->id,
            'changed_by'   => auth()->id(),
            'from_status'  => $fromStatus,
            'to_status'    => \App\Domain\Complaints\Enums\ComplaintStatus::REJECTED->value,
            'note'         => "Ditolak oleh Super Admin: {$reason}",
        ]);

        \App\Models\AuditLog::create([
            'actor_id'    => auth()->id(),
            'action'      => 'complaint.admin_rejected',
            'entity_type' => Complaint::class,
            'entity_id'   => $complaint->id,
            'before_json' => ['status' => $fromStatus, 'rejected_by_superadmin' => false],
            'after_json'  => ['status' => 'REJECTED', 'rejected_by_superadmin' => true, 'reason' => $reason],
            'ip'          => $request->ip(),
        ]);

        return back()->with('success', "Laporan #{$complaint->public_code} telah ditolak dan dipindahkan ke Arsip Ditolak (khusus Super Admin).");
    }

    /**
     * Log aktivitas sistem (keputusan rapat: Super Admin mendapat fitur log aktivitas).
     */
    public function activityLog(Request $request): \Illuminate\View\View
    {
        $query = \App\Models\AuditLog::with('actor')->latest('id');

        if ($request->filled('action')) {
            $needle = '%' . strtolower((string) $request->input('action')) . '%';
            $query->whereRaw('LOWER(action) LIKE ?', [$needle]);
        }

        if ($request->filled('actor')) {
            $needle = '%' . strtolower((string) $request->input('actor')) . '%';
            $query->whereHas('actor', function ($q) use ($needle) {
                $q->whereRaw('LOWER(name) LIKE ?', [$needle]);
            });
        }

        if ($request->filled('from')) {
            $query->whereDate('created_at', '>=', $request->input('from'));
        }

        if ($request->filled('to')) {
            $query->whereDate('created_at', '<=', $request->input('to'));
        }

        $logs = $query->paginate(50)->withQueryString();

        return view('admin.activity_log', compact('logs'));
    }

    /**
     * Pengaturan ambang AI dan penegakan radius foto (bisa dikalibrasi tanpa deploy).
     */
    public function settings(): View
    {
        $settings = [
            'ai_verify_threshold'          => (float) \App\Models\SystemSetting::get('ai_verify_threshold', 0.80),
            'ai_completion_threshold'      => (float) \App\Models\SystemSetting::get('ai_completion_threshold', 0.80),
            'photo_radius_require_location' => (bool) \App\Models\SystemSetting::get('photo_radius_require_location', false),
            'ai_last_failure_at'           => \App\Models\SystemSetting::get('ai_last_failure_at'),
        ];

        return view('admin.settings', compact('settings'));
    }

    public function updateSettings(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'ai_verify_threshold'     => 'required|numeric|min:0.50|max:0.99',
            'ai_completion_threshold' => 'required|numeric|min:0.50|max:0.99',
            'photo_radius_require_location' => 'nullable|boolean',
        ], [
            'ai_verify_threshold.min' => 'Ambang verifikasi minimal 0,50 (50%).',
            'ai_verify_threshold.max' => 'Ambang verifikasi maksimal 0,99 (99%).',
            'ai_completion_threshold.min' => 'Ambang validasi hasil kerja minimal 0,50 (50%).',
            'ai_completion_threshold.max' => 'Ambang validasi hasil kerja maksimal 0,99 (99%).',
        ]);

        $before = [
            'ai_verify_threshold'     => (float) \App\Models\SystemSetting::get('ai_verify_threshold', 0.80),
            'ai_completion_threshold' => (float) \App\Models\SystemSetting::get('ai_completion_threshold', 0.80),
            'photo_radius_require_location' => (bool) \App\Models\SystemSetting::get('photo_radius_require_location', false),
        ];

        \App\Models\SystemSetting::set('ai_verify_threshold', (float) $validated['ai_verify_threshold'], 'float', 'Ambang keyakinan AI untuk verifikasi otomatis laporan.');
        \App\Models\SystemSetting::set('ai_completion_threshold', (float) $validated['ai_completion_threshold'], 'float', 'Ambang skor AI untuk menyatakan pekerjaan selesai otomatis.');
        \App\Models\SystemSetting::set('photo_radius_require_location', $request->boolean('photo_radius_require_location'), 'boolean', 'Wajibkan lokasi GPS saat petugas mengirim bukti pengerjaan.');

        \App\Models\AuditLog::create([
            'actor_id'    => auth()->id(),
            'action'      => 'SETTINGS_UPDATED',
            'entity_type' => \App\Models\SystemSetting::class,
            'entity_id'   => null,
            'before_json' => $before,
            'after_json'  => [
                'ai_verify_threshold'     => (float) $validated['ai_verify_threshold'],
                'ai_completion_threshold' => (float) $validated['ai_completion_threshold'],
                'photo_radius_require_location' => $request->boolean('photo_radius_require_location'),
            ],
            'ip'          => $request->ip(),
        ]);

        return back()->with('success', 'Pengaturan AI dan radius berhasil diperbarui.');
    }
}
