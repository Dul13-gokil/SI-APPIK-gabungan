<?php

namespace App\Http\Controllers\Api\v1;

use App\Domain\AI\Services\AICVService;
use App\Domain\Complaints\Services\DuplicateDetectionService;
use App\Domain\Complaints\Services\PriorityService;
use App\Http\Controllers\Controller;
use App\Http\Resources\ApiResponse;
use App\Http\Resources\ComplaintResource;
use App\Models\AiAssessment;
use App\Models\AuditLog;
use App\Models\Category;
use App\Models\Complaint;
use App\Models\ComplaintPhoto;
use App\Models\ComplaintStatusHistory;
use App\Models\Disposition;
use App\Models\Notification;
use App\Models\Support;
use App\Models\SystemSetting;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ComplaintController extends Controller
{
    public function __construct(
        protected PriorityService $priorityService,
        protected DuplicateDetectionService $duplicateService,
        protected AICVService $aiService
    ) {}

    /**
     * Resolusi user yang login dari Sanctum atau session resmi.
     */
    protected function getAuthenticatedUser(Request $request): ?User
    {
        return AuthController::resolveUser($request);
    }

    /**
     * Daftar semua laporan (public feed). Filter by status, category, region.
     */
    public function index(Request $request): JsonResponse
    {
        $user   = $this->getAuthenticatedUser($request);
        $userId = $user?->id;

        // Laporan publik (Semua Laporan): Tampilkan seluruh laporan aktif dan tuntas se-kabupaten
        // (Verifikasi, Ditugaskan, Dikerjakan, Validasi Mutu, hingga Selesai). Sembunyikan REJECTED, DRAFT, dan CANCELLED.
        $query = Complaint::with(['category', 'user', 'photos'])
            ->whereNotIn('status', ['REJECTED', 'DRAFT', 'CANCELLED']);

        if ($request->filled('status')) {
            if ($request->status === 'ARCHIVED' || $request->status === 'COMPLETED') {
                $query->whereIn('status', ['COMPLETED', 'CLOSED']);
            } else {
                $query->where('status', $request->status);
            }
        }

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->filled('region_id')) {
            $query->where('region_id', $request->region_id);
        }

        $perPage    = $request->integer('per_page', 15);
        $complaints = $query->orderBy('created_at', 'desc')->paginate($perPage);

        return ApiResponse::success([
            'items'      => ComplaintResource::collection($complaints->items()),
            'pagination' => [
                'current_page' => $complaints->currentPage(),
                'total_pages'  => $complaints->lastPage(),
                'total_items'  => $complaints->total(),
                'per_page'     => $complaints->perPage(),
            ],
        ]);
    }

    /**
     * Laporan milik user yang sedang login (Riwayat Laporan Saya).
     * Menampilkan semua laporan milik akun tersebut (baik yang masih proses maupun yang sudah selesai).
     */
    public function myComplaints(Request $request): JsonResponse
    {
        $user   = $this->getAuthenticatedUser($request);
        $userId = $user?->id;

        if (!$userId) {
            return ApiResponse::success(['items' => [], 'pagination' => [
                'current_page' => 1, 'total_pages' => 0, 'total_items' => 0, 'per_page' => 15,
            ]]);
        }

        $query = Complaint::with(['category', 'user', 'photos', 'latestDisposition'])
            ->where('user_id', $userId);

        // Filter status jika secara spesifik diminta
        if ($request->filled('status')) {
            if ($request->status === 'ARCHIVED' || $request->status === 'COMPLETED') {
                $query->whereIn('status', ['COMPLETED', 'CLOSED']);
            } else {
                $query->where('status', $request->status);
            }
        } elseif ($request->boolean('archived', false)) {
            $query->whereIn('status', ['COMPLETED', 'CLOSED']);
        }

        $perPage    = $request->integer('per_page', 20);
        $complaints = $query->orderBy('created_at', 'desc')->paginate($perPage);

        return ApiResponse::success([
            'items'      => ComplaintResource::collection($complaints->items()),
            'pagination' => [
                'current_page' => $complaints->currentPage(),
                'total_pages'  => $complaints->lastPage(),
                'total_items'  => $complaints->total(),
                'per_page'     => $complaints->perPage(),
            ],
        ]);
    }


    /**
     * Tugas lapangan untuk PETUGAS yang sedang login.
     * Mengambil complaint yang di-disposisi ke user ini.
     */
    public function fieldTasks(Request $request): JsonResponse
    {
        $user   = $this->getAuthenticatedUser($request);
        $userId = $user?->id;

        if (!$userId) {
            return ApiResponse::success([
                'items' => [],
                'summary' => [
                    'total_tasks'                => 0,
                    'verification_count'         => 0,
                    'dispatched_count'           => 0,
                    'in_progress_count'          => 0,
                    'waiting_verification_count' => 0,
                    'completed_count'            => 0,
                ],
                'pagination' => [
                    'current_page' => 1, 'total_pages' => 0, 'total_items' => 0, 'per_page' => 15,
                ],
            ]);
        }

        $userTeamIds = $user->teams()->pluck('teams.id')->toArray();
        $currentTeam = $user->currentTeam();

        // Ambil complaint yang di-disposisi ke petugas ini atau ke regunya
        $myComplaintIds = Disposition::where(function ($q) use ($userId, $userTeamIds) {
            $q->where('assigned_to', $userId);
            if (!empty($userTeamIds)) {
                $q->orWhereIn('team_id', $userTeamIds);
            }
        })
        ->pluck('complaint_id')
        ->unique();

        // Hitung total seluruh aduan riil di Kabupaten (termasuk verifikasi)
        $totalActual = Complaint::count();
        $verificationActual = Complaint::whereIn('status', ['SUBMITTED', 'VERIFICATION'])->count();
        $dispatchedActual = Complaint::where('status', 'DISPATCHED')->count();
        $inProgressActual = Complaint::whereIn('status', ['IN_PROGRESS', 'REVISION_REQUIRED'])->count();
        $waitingVerificationActual = Complaint::where('status', 'WAITING_VERIFICATION')->count();
        $completedActual = Complaint::whereIn('status', ['COMPLETED', 'CLOSED'])->count();

        // Hitung tugas yang sedang aktif dikerjakan oleh petugas ini atau regunya
        $myActiveInProgressCount = Complaint::whereIn('id', $myComplaintIds)
            ->whereIn('status', ['IN_PROGRESS', 'REVISION_REQUIRED'])
            ->count();

        $query = Complaint::with([
            'category',
            'photos',
            'latestDisposition.assignee',
            'latestDisposition.team.leader',
            'latestDisposition.team.users',
            'dispositions.assignee',
            'dispositions.team'
        ]);

        $scope = $request->input('scope', 'all');
        $statusFilter = $request->input('status');

        if ($scope === 'my') {
            // Khusus tugas yang sudah diambil / dikerjakan oleh petugas ini / regunya (tanpa DISPATCHED)
            $query->whereIn('id', $myComplaintIds)
                  ->whereNotIn('status', ['SUBMITTED', 'VERIFICATION', 'DISPATCHED']);
            if ($statusFilter === 'COMPLETED') {
                $query->whereIn('status', ['COMPLETED', 'CLOSED']);
            } elseif ($request->filled('status') && $statusFilter !== 'ALL') {
                $query->where('status', $statusFilter);
            }
        } else {
            // scope == 'all' (Semua Tugas Lapangan se-Kabupaten)
            if ($statusFilter === 'COMPLETED') {
                $query->whereIn('status', ['COMPLETED', 'CLOSED']);
            } elseif ($request->filled('status') && $statusFilter !== 'ALL') {
                $query->where('status', $statusFilter);
            } else {
                // Semua tugas lapangan: DISPATCHED, IN_PROGRESS, WAITING_VERIFICATION, REVISION_REQUIRED, COMPLETED
                $query->whereIn('status', ['DISPATCHED', 'IN_PROGRESS', 'WAITING_VERIFICATION', 'REVISION_REQUIRED', 'COMPLETED', 'CLOSED']);
            }
        }

        $perPage    = $request->integer('per_page', 20);
        $complaints = $query->orderBy('updated_at', 'desc')->paginate($perPage);

        return ApiResponse::success([
            'items'      => ComplaintResource::collection($complaints->items()),
            'summary'    => [
                'total_tasks'                => $totalActual,
                'total_complaints'           => $totalActual,
                'verification_count'         => $verificationActual,
                'dispatched_count'           => $dispatchedActual,
                'in_progress_count'          => $inProgressActual,
                'waiting_verification_count' => $waitingVerificationActual,
                'completed_count'            => $completedActual,
                'dispatch_mode'              => \App\Models\SystemSetting::get('dispatch_mode', 'HYBRID'),
                'my_team'                    => $currentTeam ? [
                    'id'                => $currentTeam->id,
                    'name'              => $currentTeam->name,
                    'code'              => $currentTeam->code,
                    'district'          => $currentTeam->district,
                    'leader_id'         => $currentTeam->leader_id,
                    'leader_name'       => $currentTeam->leader?->name,
                    'leader_phone'      => $currentTeam->leader?->phone,
                    'members_count'     => $currentTeam->members()->count(),
                    'user_role_in_team' => ($currentTeam->leader_id === $userId || $currentTeam->members()->where('user_id', $userId)->where('role', 'LEADER')->exists()) ? 'Ketua Regu' : 'Anggota Regu',
                    'members'           => $currentTeam->members()->with('user')->get()->map(function ($m) {
                        return [
                            'user_id'   => $m->user_id,
                            'name'      => $m->user?->name ?? 'Pengawas Lapangan',
                            'phone'     => $m->user?->phone ?? '-',
                            'role'      => $m->role === 'LEADER' ? 'Ketua Regu' : 'Anggota Regu',
                            'is_leader' => $m->role === 'LEADER',
                        ];
                    }),
                ] : null,
            ],
            'pagination' => [
                'current_page' => $complaints->currentPage(),
                'total_pages'  => $complaints->lastPage(),
                'total_items'  => $complaints->total(),
                'per_page'     => $complaints->perPage(),
            ],
        ]);
    }

    /**
     * Kirim laporan baru (WARGA/TAMU).
     */
    public function store(Request $request): JsonResponse
    {
        // Kompatibilitas APK lama: kategori PJU (2) sudah digabung ke Fasilitas Umum Lainnya (3).
        if ((int) $request->input('category_id') === 2) {
            $request->merge(['category_id' => 3]);
        }

        $validated = $request->validate([
            'title'             => 'required|string|max:255',
            'description'       => 'required|string',
            'category_id'       => 'required|exists:categories,id',
            'region_id'         => 'nullable|exists:regions,id',
            'latitude'          => 'required|numeric|between:-90,90',
            'longitude'         => 'required|numeric|between:-180,180',
            'radius_m'          => 'nullable|integer|min:5|max:100',
            'address'           => 'required|string|max:500',
            'is_anonymous'      => 'nullable',
            'damage_type'       => 'nullable|in:minor,major',
            'road_length'       => 'nullable|string|max:100',
            'road_condition'    => 'nullable|string|max:255',
            'end_latitude'      => 'nullable|numeric|between:-90,90',
            'end_longitude'     => 'nullable|numeric|between:-180,180',
            'route_coordinates' => 'nullable',
            'photos'            => 'required|array|min:1',
            'photos.*'          => 'required|file|image|max:10240',
        ], [
            'photos.required' => 'Lampirkan minimal satu foto kerusakan yang diambil langsung dari kamera.',
            'photos.min'      => 'Lampirkan minimal satu foto kerusakan yang diambil langsung dari kamera.',
        ]);

        $damageType = $validated['damage_type'] ?? 'minor';

        // Tipe laporan Kerusakan Ruas Panjang / Parah khusus hanya untuk Jalan & Jembatan (Category ID 1)
        if ($damageType === 'major') {
            $validated['category_id'] = 1;
        }

        // Hitung total file foto yang diunggah
        $photoFiles = [];
        if ($request->hasFile('photos')) {
            $pList = is_array($request->file('photos')) ? $request->file('photos') : [$request->file('photos')];
            $photoFiles = array_merge($photoFiles, $pList);
        }
        if ($request->hasFile('photo')) {
            $photoFiles[] = $request->file('photo');
        }

        // Aturan validasi foto: Kerusakan Ruas Panjang / Parah wajib minimal 5 foto
        if ($damageType === 'major' && count($photoFiles) < 5) {
            return ApiResponse::error(
                'Pelaporan kerusakan ruas jalan panjang wajib melampirkan minimal 5 foto bukti di sepanjang ruas jalan (saat ini: ' . count($photoFiles) . ' foto).',
                422
            );
        }

        // Resolusi identitas pelapor (User terdaftar atau Warga Tamu Anonim)
        $user = $this->getAuthenticatedUser($request);
        if ($user) {
            $userId = $user->id;
        } else {
            $guestUser = User::firstOrCreate(
                ['email' => 'tamu@kabupaten.go.id'],
                [
                    'name'         => 'Warga Tamu (Anonim)',
                    'role'         => 'WARGA',
                    'password'     => \Illuminate\Support\Facades\Hash::make(\Illuminate\Support\Str::random(32)),
                    'is_anonymous' => true,
                ]
            );
            $userId = $guestUser->id;
        }
        $category = Category::findOrFail($validated['category_id']);

        // Validasi Geofencing Wilayah: Wajib berada di dalam batas Kabupaten
        $lat = (float) $validated['latitude'];
        $lon = (float) $validated['longitude'];
        if ($lat < -3.80 || $lat > -2.75 || $lon < 120.70 || $lon > 121.50) {
            return ApiResponse::error(
                'Titik lokasi laporan berada di luar batas wilayah Kabupaten. Sistem saat ini hanya melayani wilayah Kabupaten.',
                422
            );
        }

        // Klasifikasi AI: berat/ringan dan kategori ditentukan model (keputusan rapat).
        // Di bawah ambang laporan tetap SUBMITTED dan masuk antrean operator.
        $aiResult = null;
        $aiFailure = null;
        if (!empty($photoFiles)) {
            try {
                $aiResult = $this->aiService->classifyMultipleImages($photoFiles);
            } catch (\Throwable $e) {
                $aiFailure = $e->getMessage();
                \Illuminate\Support\Facades\Log::warning('Klasifikasi AI gagal saat lapor: ' . $aiFailure);
                // Tandai agar operator tahu AI sedang bermasalah, bukan laporan yang buruk.
                \App\Models\SystemSetting::set('ai_last_failure_at', now()->toIso8601String(), 'string');
            }
        }

        $aiValid = is_array($aiResult)
            && ($aiResult['is_valid_infrastructure'] ?? false) === true
            && isset($aiResult['category_id']);
        $aiConfidence = $aiValid ? (float) ($aiResult['confidence'] ?? 0) : 0.0;

        if ($aiValid) {
            $aiCategory = (int) $aiResult['category_id'];
            // PJU (kategori 2) sudah tidak ada; semua fasilitas non-jalan masuk kategori 3.
            $validated['category_id'] = in_array($aiCategory, [1, 3], true) ? $aiCategory : 3;
            // Bentuk laporan (titik vs ruas panjang) tetap mengikuti pilihan warga:
            // itu soal geometri laporan, bukan tingkat keparahan. Tingkat keparahan
            // hanya memengaruhi prioritas, bukan mengubah satu titik menjadi ruas
            // panjang yang tidak punya titik akhir.
        }

        // Verifikasi manual dihapus (keputusan rapat): AI yang memutuskan status awal.
        // Foto wajib dari kamera (bukan album) dan diberi watermark waktu + koordinat,
        // sehingga laporan palsu sulit lolos.
        //   AI menilai infrastruktur rusak  -> VERIFIED
        //   AI menilai bukan kerusakan      -> REJECTED otomatis
        //   AI tidak bisa dijalankan        -> VERIFIED dengan tanda belum tervalidasi,
        //                                      operator tetap bisa menolak dari antrean.
        $verifyThreshold = (float) \App\Models\SystemSetting::get('ai_verify_threshold', 0.80);
        $aiFallback = is_array($aiResult) && (($aiResult['category_label'] ?? '') === 'Foto Belum Terverifikasi');
        $aiRan = is_array($aiResult) && ! $aiFallback;

        if (! $aiRan) {
            \App\Models\SystemSetting::set('ai_last_failure_at', now()->toIso8601String(), 'string');
        }

        // Ambang hanya menandai keyakinan AI (dipakai untuk penanda "AI ragu" di antrean
        // operator). Status tetap ditentukan AI karena verifikasi manual sudah dihapus.
        $autoVerified = $aiValid && $aiConfidence >= $verifyThreshold;
        $autoRejected = $aiRan && ! $aiValid;
        $initialStatus = $autoRejected ? 'REJECTED' : 'VERIFIED';

        $category = Category::findOrFail($validated['category_id']);

        // Initial Priority Calculation (SAP Section 18)
        $priorityCalc = $this->priorityService->calculate(
            severityScore:     (float) $category->severity_default,
            supportCount:      0,
            locationImportance:0.5,
            ageInHours:        0,
            duplicateCount:    0
        );


        $complaint = DB::transaction(function () use ($validated, $damageType, $userId, $priorityCalc, $photoFiles, $initialStatus, $autoVerified, $autoRejected) {
            $publicCode = $this->generatePublicCode($damageType);
            $isAnon = filter_var($validated['is_anonymous'] ?? false, FILTER_VALIDATE_BOOLEAN);

            $latFloat = (float) $validated['latitude'];
            $lonFloat = (float) $validated['longitude'];

            $regionId = $validated['region_id'] ?? $this->nearestRegion($latFloat, $lonFloat)?->id;

            $enrichedAddress = $this->enrichAddressWithAdministrativeLocation(
                $validated['address'],
                $latFloat,
                $lonFloat
            );

            // Parsing route_coordinates jika dikirim dalam bentuk JSON string
            $routeCoords = $validated['route_coordinates'] ?? null;
            if (is_string($routeCoords)) {
                $decoded = json_decode($routeCoords, true);
                if (is_array($decoded)) {
                    $routeCoords = $decoded;
                }
            }

            $complaint = Complaint::create([
                'public_code'       => $publicCode,
                'user_id'           => $userId,
                'category_id'       => $validated['category_id'],
                'region_id'         => $regionId,
                'damage_type'       => $damageType,
                'road_length'       => $validated['road_length'] ?? null,
                'road_condition'    => $validated['road_condition'] ?? null,
                'title'             => $validated['title'],
                'description'       => $validated['description'],
                'address'           => $enrichedAddress,
                'latitude'          => $validated['latitude'],
                'longitude'         => $validated['longitude'],
                'end_latitude'      => $validated['end_latitude'] ?? null,
                'end_longitude'     => $validated['end_longitude'] ?? null,
                'route_coordinates' => $routeCoords,
                'status'            => $initialStatus,
                'verified_by_ai'    => $autoVerified,
                'radius_m'          => (int) ($validated['radius_m'] ?? 20),
                'priority_score'    => $priorityCalc['score'],
                'priority_level'    => $priorityCalc['level'],
                'is_anonymous'      => $isAnon,
            ]);

            // Set PostGIS Point geometry if postgis extension is active on pgsql
            if (DB::connection()->getDriverName() === 'pgsql') {
                $hasPostgis = DB::selectOne("SELECT 1 FROM pg_extension WHERE extname = 'postgis'");
                if ($hasPostgis) {
                    DB::statement(
                        "UPDATE complaints SET geom = ST_SetSRID(ST_MakePoint(?, ?), 4326) WHERE id = ?",
                        [$validated['longitude'], $validated['latitude'], $complaint->id]
                    );
                }
            }

            // Save Initial Status History
            ComplaintStatusHistory::create([
                'complaint_id' => $complaint->id,
                'from_status'  => null,
                'to_status'    => $initialStatus,
                'note'         => $autoRejected
                    ? 'Ditolak otomatis: foto yang dikirim tidak terdeteksi sebagai kerusakan infrastruktur.'
                    : 'Laporan dibuat oleh warga dan langsung diverifikasi oleh AI.',
                'changed_by'   => $userId,
            ]);

            // Handle Photo Uploads:
            // Maksimal 10 foto untuk kerusakan ruas panjang (major), 5 foto untuk minor
            $maxPhotos = ($damageType === 'major') ? 10 : 5;
            $uploadedPhotos = [];
            $seenHashes     = [];

            foreach ($photoFiles as $file) {
                if ($file && $file->isValid()) {
                    $hash = @md5_file($file->getRealPath());
                    if ($hash && !in_array($hash, $seenHashes, true)) {
                        $seenHashes[] = $hash;
                        $uploadedPhotos[] = $file;
                    }
                }
                if (count($uploadedPhotos) >= $maxPhotos) break;
            }

            foreach ($uploadedPhotos as $index => $photo) {
                $realPath = $photo->getRealPath();
                $path = $photo->store('complaints', 'public');

                // Bukti laporan warga juga dicap waktu + koordinat agar tidak bisa
                // memakai foto lama atau foto milik orang lain.
                app(\App\Services\ImageWatermarkService::class)->applyFieldWatermark(
                    \Illuminate\Support\Facades\Storage::disk('public')->path($path),
                    (string) $complaint->public_code,
                    (float) $validated['latitude'],
                    (float) $validated['longitude'],
                    null
                );
                // Foto hanya disimpan di disk. Duplikat base64 di database pernah membuat
                // tabel membengkak (10 MB foto -> ~13 MB per baris); jangan dihidupkan lagi.
                $photoAttrs = [
                    'complaint_id' => $complaint->id,
                    'storage_key'  => $path,
                    'url'          => null, // Di-resolve dinamis di ComplaintResource
                    'caption'      => 'Foto Bukti ' . ($index + 1),
                    'type'         => 'evidence_before',
                    'mime'         => $photo->getClientMimeType(),
                    'size'         => $photo->getSize(),
                    'checksum'     => $realPath ? md5_file($realPath) : null,
                    'captured_at'  => now(),
                ];
                ComplaintPhoto::create($photoAttrs);
            }

            return $complaint;
        });

        // Jejak audit: laporan masuk, hasil AI, dan kegagalan AI (untuk log aktivitas Super Admin).
        AuditLog::create([
            'actor_id'    => $userId,
            'action'      => 'COMPLAINT_CREATED',
            'entity_type' => Complaint::class,
            'entity_id'   => $complaint->id,
            'before_json' => null,
            'after_json'  => [
                'public_code'     => $complaint->public_code,
                'status'          => $initialStatus,
                'category_id'     => $complaint->category_id,
                'damage_type'     => $damageType,
                'verified_by_ai'  => $autoVerified,
                'auto_rejected'   => $autoRejected,
                'ai_confidence'   => $aiConfidence,
                'ai_threshold'    => $verifyThreshold,
                'radius_m'        => $complaint->radius_m,
            ],
            'ip'          => $request->ip(),
        ]);

        if ($aiFailure !== null) {
            AuditLog::create([
                'actor_id'    => $userId,
                'action'      => 'AI_CLASSIFICATION_FAILED',
                'entity_type' => Complaint::class,
                'entity_id'   => $complaint->id,
                'before_json' => null,
                'after_json'  => ['status' => $initialStatus, 'error' => mb_substr($aiFailure, 0, 300)],
                'ip'          => $request->ip(),
            ]);
        }

        // Simpan hasil klasifikasi AI dan catat verifikasi otomatis.
        if ($aiValid) {
            AiAssessment::create([
                'complaint_id' => $complaint->id,
                'model'        => (string) ($aiResult['model_version'] ?? 'cv-spatial-opencv-v2.5'),
                'task'         => 'DAMAGE_CLASSIFICATION',
                'result_json'  => $aiResult,
                'confidence'   => $aiConfidence,
            ]);
        }

        if ($autoVerified) {
            ComplaintStatusHistory::create([
                'complaint_id' => $complaint->id,
                'from_status'  => 'SUBMITTED',
                'to_status'    => 'VERIFIED',
                'note'         => 'Diverifikasi otomatis oleh AI (keyakinan ' . round($aiConfidence * 100) . '%).',
                'changed_by'   => null,
            ]);

            Notification::create([
                'user_id'   => $complaint->user_id,
                'type'      => 'COMPLAINT_VERIFIED',
                'title'     => 'Laporan Diverifikasi Otomatis',
                'body'      => "Laporan #{$complaint->public_code} sudah lolos verifikasi AI dan menunggu penugasan regu lapangan.",
                'data_json' => [
                    'complaint_id' => $complaint->id,
                    'public_code'  => $complaint->public_code,
                ],
            ]);
        }

        if ($autoRejected) {
            Notification::create([
                'user_id'   => $complaint->user_id,
                'type'      => 'COMPLAINT_REJECTED',
                'title'     => 'Laporan Tidak Dapat Diproses',
                'body'      => "Laporan #{$complaint->public_code} tidak dapat diproses karena foto yang dikirim tidak terdeteksi sebagai kerusakan infrastruktur. Pastikan foto diambil langsung dari kamera pada objek yang rusak.",
                'data_json' => [
                    'complaint_id' => $complaint->id,
                    'public_code'  => $complaint->public_code,
                ],
            ]);
        }

        // Tawaran dukungan bila sudah ada aduan lain di radius yang sama.
        $radius = (int) ($validated['radius_m'] ?? 20);
        $similar = $this->duplicateService->findCandidates(
            (float) $validated['latitude'],
            (float) $validated['longitude'],
            $radius,
            $complaint->id
        );

        $payload = (new ComplaintResource($complaint->load(['category', 'user', 'photos'])))->toArray(request());
        $payload['radius_m'] = $radius;
        $payload['verified_by_ai'] = $autoVerified;
        $payload['similar_complaints'] = $similar->map(fn ($item) => [
            'id'             => $item->id,
            'public_code'    => $item->public_code,
            'title'          => $item->title,
            'status'         => $item->status,
            'distance_m'     => round((float) ($item->distance_m ?? 0)),
            'supports_count' => $item->supports()->count(),
        ])->values()->all();
        $payload['similar_message'] = $similar->isNotEmpty()
            ? 'Aduan serupa sudah ada di radius ini. Dukung aduan yang sudah ada agar tidak duplikat.'
            : null;

        return ApiResponse::success(
            $payload,
            $autoVerified
                ? 'Laporan diterima dan sudah diverifikasi otomatis oleh AI.'
                : 'Laporan pengaduan berhasil dikirim',
            201
        );
    }

    /**
     * Nomor urut kode publik (mis. 0005/ringan/2026).
     * Dipanggil di dalam transaksi; advisory lock mencegah dua laporan memakai nomor sama.
     * shortcut: hanya Postgres yang diserialisasi, tambahkan GET_LOCK bila produksi pindah ke MySQL.
     */
    protected function generatePublicCode(string $damageType): string
    {
        if (DB::connection()->getDriverName() === 'pgsql') {
            DB::select('SELECT pg_advisory_xact_lock(?)', [738292001]);
        }

        $nextId = (int) (Complaint::withoutGlobalScopes()->max('id') ?? 0) + 1;
        $typeCode = $damageType === 'major' ? 'berat' : 'ringan';
        $year = date('Y');

        do {
            $publicCode = str_pad((string) $nextId, 4, '0', STR_PAD_LEFT) . "/{$typeCode}/{$year}";
            $exists = Complaint::withoutGlobalScopes()->where('public_code', $publicCode)->exists();
            if ($exists) {
                $nextId++;
            }
        } while ($exists);

        return $publicCode;
    }

    /**
     * Batalkan laporan pengaduan oleh warga pemilik laporan (hanya saat status SUBMITTED / Verifikasi).
     * Laporan tidak dihapus permanen melainkan diubah statusnya ke CANCELLED untuk jejak arsip.
     */
    public function cancel(Request $request, int $id): JsonResponse
    {
        $user = $this->getAuthenticatedUser($request);
        $complaint = Complaint::with('photos')->findOrFail($id);

        $isOwner = $user && $complaint->user_id === $user->id;
        if (!$isOwner) {
            return ApiResponse::error('Anda tidak memiliki izin untuk membatalkan laporan ini.', [], 403);
        }

        if ($complaint->status !== 'SUBMITTED') {
            return ApiResponse::error(
                'Laporan hanya dapat dibatalkan saat masih berstatus Menunggu Verifikasi.',
                [],
                403
            );
        }

        $reason = trim((string) $request->input('reason', ''));
        $note = $reason !== ''
            ? "Dibatalkan oleh pelapor: {$reason}"
            : 'Laporan dibatalkan oleh pelapor sebelum diverifikasi.';

        \App\Domain\Complaints\Services\ComplaintStateMachine::validateTransition($complaint->status, 'CANCELLED');

        $fromStatus = $complaint->status;

        DB::transaction(function () use ($complaint, $user, $fromStatus, $note, $reason, $request) {
            $complaint->update([
                'status' => 'CANCELLED',
            ]);

            \App\Models\ComplaintStatusHistory::create([
                'complaint_id' => $complaint->id,
                'from_status'  => $fromStatus,
                'to_status'    => 'CANCELLED',
                'changed_by'   => $user->id,
                'note'         => $note,
            ]);

            \App\Models\AuditLog::create([
                'actor_id'      => $user->id,
                'action'        => 'complaint.cancelled',
                'entity_type'   => \App\Models\Complaint::class,
                'entity_id'     => $complaint->id,
                'before_json'   => ['status' => $fromStatus],
                'after_json'    => ['status' => 'CANCELLED', 'reason' => $reason !== '' ? $reason : null],
                'ip'            => $request->ip(),
            ]);
        });

        return ApiResponse::success(
            new ComplaintResource($complaint->fresh(['category', 'user', 'photos', 'statusHistories.user', 'latestDisposition'])),
            'Laporan pengaduan berhasil dibatalkan.'
        );
    }

    /**
     * Endpoint DELETE dialihkan ke pembatalan (CANCELLED) agar arsip & rekam jejak tetap terjaga.
     */
    public function destroy(Request $request, int $id): JsonResponse
    {
        return $this->cancel($request, $id);
    }

    public function show(int $id): JsonResponse
    {
        $complaint = Complaint::with(['category', 'user', 'photos', 'statusHistories.user', 'latestDisposition', 'rating'])->findOrFail($id);
        return ApiResponse::success(new ComplaintResource($complaint));
    }

    /**
     * Deteksi laporan duplikat dalam radius 100 meter (Geo-Clustering Haversine) dengan kategori sama.
     */
    public function nearbyDuplicates(Request $request): JsonResponse
    {
        $lat = (float) $request->query('lat', 0);
        $lng = (float) $request->query('lng', 0);
        $categoryId = $request->query('category_id');
        $radiusMeters = max(5, min(200, (int) $request->query('radius', 20)));

        if ($lat === 0.0 && $lng === 0.0) {
            return ApiResponse::success([]);
        }

        $candidates = Complaint::with(['category', 'photos'])
            ->whereNotIn('status', ['COMPLETED', 'CLOSED', 'REJECTED', 'CANCELLED', 'DRAFT'])
            ->when($categoryId, fn ($q) => $q->where('category_id', (int) $categoryId))
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->latest()
            ->limit(150)
            ->get();

        $matches = [];
        foreach ($candidates as $c) {
            $dist = $this->haversineMeters($lat, $lng, (float) $c->latitude, (float) $c->longitude);
            if ($dist <= $radiusMeters) {
                $matches[] = [
                    'id'              => $c->id,
                    'public_code'     => $c->public_code,
                    'title'           => $c->title,
                    'address'         => $c->address,
                    'status'          => $c->status,
                    'category_name'   => $c->category?->name,
                    'supports_count'  => $c->supports()->count(),
                    'distance_meters' => (int) round($dist),
                ];
            }
        }

        usort($matches, fn ($a, $b) => $a['distance_meters'] <=> $b['distance_meters']);

        return ApiResponse::success(array_slice($matches, 0, 3));
    }

    /**
     * Survei Kepuasan Warga Pasca-Perbaikan (Rating 1-5 Bintang + ulasan singkat).
     */
    public function storeRating(Request $request, int $id): JsonResponse
    {
        $user = $this->getAuthenticatedUser($request);
        if (!$user) {
            return ApiResponse::error('Silakan masuk terlebih dahulu untuk memberikan penilaian.', [], 401);
        }

        $complaint = Complaint::findOrFail($id);
        if ($complaint->user_id !== $user->id) {
            return ApiResponse::error('Hanya pelapor asli yang dapat memberikan penilaian hasil perbaikan.', [], 403);
        }

        if (!in_array($complaint->status, ['COMPLETED', 'CLOSED'], true)) {
            return ApiResponse::error('Penilaian hanya dapat diberikan setelah laporan berstatus Selesai.', [], 422);
        }

        $validated = $request->validate([
            'rating'  => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:500',
        ]);

        $rating = \App\Models\ComplaintRating::updateOrCreate(
            ['complaint_id' => $complaint->id],
            [
                'user_id' => $user->id,
                'rating'  => $validated['rating'],
                'comment' => $validated['comment'] ?? null,
            ]
        );

        return ApiResponse::success([
            'rating'     => $rating->rating,
            'comment'    => $rating->comment,
            'created_at' => $rating->created_at?->toIso8601String(),
        ], 'Terima kasih! Penilaian Anda terhadap hasil perbaikan telah disimpan.');
    }

    /**
     * Region (kecamatan/desa) terdekat dihitung di PHP agar query tetap portabel
     * di PostgreSQL, MySQL, dan SQLite (SQLite tidak punya fungsi trigonometri).
     * shortcut: memuat semua region tiap panggilan, pindahkan ke PostGIS bila jumlah region membesar.
     */
    protected function nearestRegion(float $lat, float $lon): ?\App\Models\Region
    {
        return \App\Models\Region::query()
            ->get()
            ->sortBy(fn ($region) => $this->haversineMeters(
                $lat,
                $lon,
                (float) $region->latitude,
                (float) $region->longitude
            ))
            ->first();
    }

    protected function haversineMeters(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        $earthRadius = 6371000.0;
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);
        $a = sin($dLat / 2) * sin($dLat / 2)
            + cos(deg2rad($lat1)) * cos(deg2rad($lat2))
            * sin($dLon / 2) * sin($dLon / 2);
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        return $earthRadius * $c;
    }

    public function toggleSupport(Request $request, int $id): JsonResponse
    {
        $user = $this->getAuthenticatedUser($request);
        if (!$user) {
            return ApiResponse::error('Silakan masuk terlebih dahulu untuk memberikan dukungan terhadap pengaduan ini.', [], 401);
        }
        if (!in_array(strtoupper((string) $user->role), ['CITIZEN', 'WARGA', 'USER'], true)) {
            return ApiResponse::error('Hanya akun Warga yang dapat memberikan dukungan pada pengaduan.', [], 403);
        }
        $userId    = $user->id;
        $complaint = Complaint::findOrFail($id);

        if (in_array(strtoupper((string) $complaint->status), ['COMPLETED', 'CLOSED', 'CANCELLED', 'REJECTED'], true)) {
            return ApiResponse::error('Dukungan tidak dapat diberikan pada pengaduan yang sudah selesai, dibatalkan, atau ditolak.', [], 422);
        }

        $existing = Support::where('complaint_id', $id)->where('user_id', $userId)->first();
        if ($existing) {
            $existing->delete();
            return ApiResponse::success([
                'supported'     => false,
                'supports_count'=> $complaint->supports()->count(),
            ], 'Dukungan dibatalkan');
        }

        Support::create([
            'complaint_id' => $id,
            'user_id'      => $userId,
        ]);

        return ApiResponse::success([
            'supported'     => true,
            'supports_count'=> $complaint->supports()->count(),
        ], 'Dukungan berhasil diberikan');
    }

    public function timeline(int $id): JsonResponse
    {
        $complaint = Complaint::findOrFail($id);
        $histories = $complaint->statusHistories()->with('user')->get();

        return ApiResponse::success($histories->map(fn($h) => [
            'id'          => $h->id,
            'from_status' => $h->from_status,
            'to_status'   => $h->to_status,
            'note'        => $h->note,
            'changed_by'  => $h->user?->name ?? 'Sistem',
            'created_at'  => $h->created_at->toIso8601String(),
        ]));
    }

    public function duplicateCandidates(Request $request, int $id): JsonResponse
    {
        $complaint = Complaint::findOrFail($id);
        $radius    = $request->float('radius_m', (float) SystemSetting::get('duplicate_radius_m', 100));

        $candidates = $this->duplicateService->findCandidates(
            latitude:          (float) $complaint->latitude,
            longitude:         (float) $complaint->longitude,
            radiusMeters:      $radius,
            excludeComplaintId:$complaint->id
        );

        return ApiResponse::success(ComplaintResource::collection($candidates));
    }

    /**
     * Endpoint API Reverse Geocoding ke OpenStreetMap untuk mendeteksi
     * Nama Jalan, Desa/Kelurahan, Kecamatan, dan Kabupaten.
     */
    public function reverseGeocode(Request $request): JsonResponse
    {
        $request->validate([
            'lat' => 'required|numeric',
            'lon' => 'required|numeric',
        ]);

        $lat = (float) $request->lat;
        $lon = (float) $request->lon;

        // Cari kecamatan dan desa/pusat administratif terdekat dari 15 kecamatan resmi Kabupaten
        $nearestRegion = $this->nearestRegion($lat, $lon);

        $road = '';
        $village = $nearestRegion?->village ?? 'Kel. Lasusua';
        $district = $nearestRegion?->district ?? 'Kecamatan Lasusua';
        $regency = 'Kabupaten Kolaka Utara';

        try {
            $response = \Illuminate\Support\Facades\Http::withHeaders([
                'User-Agent' => 'AduanKabupaten/1.0 (contact@kabupaten.go.id)',
            ])->timeout(3)->get("https://nominatim.openstreetmap.org/reverse", [
                'format'         => 'json',
                'lat'            => $lat,
                'lon'            => $lon,
                'zoom'           => 18,
                'addressdetails' => 1,
            ]);

            if ($response->successful()) {
                $data = $response->json();
                $addr = $data['address'] ?? [];
                $osmRoad = $addr['road'] ?? $addr['hamlet'] ?? $addr['residential'] ?? '';
                $osmVillage = $addr['village'] ?? $addr['suburb'] ?? $addr['neighbourhood'] ?? '';
                $osmDistrict = $addr['city_district'] ?? $addr['district'] ?? $addr['subdistrict'] ?? '';

                if (!empty($osmRoad)) $road = $osmRoad;
                if (!empty($osmVillage)) $village = $osmVillage;
                if (!empty($osmDistrict)) $district = (str_starts_with($osmDistrict, 'Kecamatan') ? $osmDistrict : "Kecamatan {$osmDistrict}");
            }
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning("Reverse geocode OSM error: " . $e->getMessage());
        }

        $cleanDistrict = str_ireplace(['Kecamatan', 'Kec.'], '', $district);
        $cleanDistrict = trim($cleanDistrict);

        $cleanVillage = str_ireplace(['Desa/Kel.', 'Desa', 'Kelurahan', 'Kel.'], '', $village);
        $cleanVillage = trim($cleanVillage);

        $parts = [];
        if (!empty($road)) {
            $parts[] = $road;
        } else {
            $parts[] = "Jalan Poros {$cleanDistrict}";
        }
        $parts[] = "Desa/Kel. {$cleanVillage}";
        $parts[] = "Kec. {$cleanDistrict}";
        $parts[] = "Kabupaten Kolaka Utara";

        $formatted = implode(', ', $parts);
        $isWithinKolaka = ($lat >= -3.80 && $lat <= -2.50 && $lon >= 120.70 && $lon <= 121.50);

        return ApiResponse::success([
            'region_id'             => $nearestRegion?->id,
            'road'                  => $road,
            'village'               => $cleanVillage,
            'district'              => "Kecamatan {$cleanDistrict}",
            'regency'               => $regency,
            'formatted_address'     => $formatted,
            'display_name'          => $formatted,
            'is_within_kolaka_utara'=> $isWithinKolaka,
        ]);
    }

    /**
     * Memperkaya alamat laporan dengan Desa/Kelurahan, Kecamatan, dan Kabupaten
     */
    protected function enrichAddressWithAdministrativeLocation(string $address, float $lat, float $lon): string
    {
        if (str_contains($address, 'Kec.') && str_contains($address, 'Kab.')) {
            return $address;
        }

        $nearest = $this->nearestRegion($lat, $lon);

        $districtName = $nearest ? str_ireplace(['Kecamatan', 'Kec.'], '', $nearest->district) : 'Lasusua';
        $villageName = $nearest ? str_ireplace(['Desa/Kel.', 'Desa', 'Kelurahan', 'Kel.'], '', $nearest->village) : 'Lasusua';

        $cleanUserAddress = trim(preg_replace('/(Titik Koordinat|Area Infrastruktur|Jalan Kabupaten)\s*\([^)]*\)/i', '', $address));
        $parts = [];
        if (!empty($cleanUserAddress)) {
            $parts[] = $cleanUserAddress;
        } else {
            $parts[] = "Jalan Poros " . trim($districtName);
        }

        $parts[] = "Desa/Kel. " . trim($villageName);
        $parts[] = "Kec. " . trim($districtName);
        $parts[] = "Kabupaten Kolaka Utara";

        return implode(', ', $parts);
    }
}
