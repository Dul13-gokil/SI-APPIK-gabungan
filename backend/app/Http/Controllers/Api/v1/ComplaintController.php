<?php

namespace App\Http\Controllers\Api\v1;

use App\Domain\AI\Services\AICVService;
use App\Domain\Complaints\Services\DuplicateDetectionService;
use App\Domain\Complaints\Services\PriorityService;
use App\Http\Controllers\Controller;
use App\Http\Resources\ApiResponse;
use App\Http\Resources\ComplaintResource;
use App\Models\Category;
use App\Models\Complaint;
use App\Models\ComplaintPhoto;
use App\Models\ComplaintStatusHistory;
use App\Models\Disposition;
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
        // (Verifikasi, Ditugaskan, Dikerjakan, Validasi Mutu, hingga Selesai). Hanya sembunyikan REJECTED dan DRAFT.
        $query = Complaint::with(['category', 'user', 'photos'])
            ->whereNotIn('status', ['REJECTED', 'DRAFT']);

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
                            'name'      => $m->user?->name ?? 'Petugas Lapangan',
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
        $validated = $request->validate([
            'title'             => 'required|string|max:255',
            'description'       => 'required|string',
            'category_id'       => 'required|exists:categories,id',
            'region_id'         => 'nullable|exists:regions,id',
            'latitude'          => 'required|numeric|between:-90,90',
            'longitude'         => 'required|numeric|between:-180,180',
            'address'           => 'required|string|max:500',
            'is_anonymous'      => 'nullable',
            'damage_type'       => 'nullable|in:minor,major',
            'road_length'       => 'nullable|string|max:100',
            'road_condition'    => 'nullable|string|max:255',
            'end_latitude'      => 'nullable|numeric|between:-90,90',
            'end_longitude'     => 'nullable|numeric|between:-180,180',
            'route_coordinates' => 'nullable',
            'photos.*'          => 'nullable|file|image|max:10240',
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
                ['email' => 'tamu@kolakautarakab.go.id'],
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

        // Validasi Geofencing Wilayah: Wajib berada di dalam batas Kabupaten Kolaka Utara
        $lat = (float) $validated['latitude'];
        $lon = (float) $validated['longitude'];
        if ($lat < -3.80 || $lat > -2.75 || $lon < 120.70 || $lon > 121.50) {
            return ApiResponse::error(
                'Titik lokasi laporan berada di luar batas wilayah Kabupaten Kolaka Utara. Sistem saat ini hanya melayani wilayah Kabupaten Kolaka Utara.',
                422
            );
        }

        // Generate Distinct Public Code by Damage Type (e.g. 0005/ringan/2026 or 0004/berat/2026)
        $nextId = (Complaint::max('id') ?? 0) + 1;
        $seq = str_pad($nextId, 4, '0', STR_PAD_LEFT);
        $typeCode = ($damageType === 'major') ? 'berat' : 'ringan';
        $year = date('Y');
        $publicCode = "{$seq}/{$typeCode}/{$year}";

        // Initial Priority Calculation (SAP Section 18)
        $priorityCalc = $this->priorityService->calculate(
            severityScore:     (float) $category->severity_default,
            supportCount:      0,
            locationImportance:0.5,
            ageInHours:        0,
            duplicateCount:    0
        );

        $hasFileDataCol = ComplaintPhoto::ensureFileDataColumnExists();

        $complaint = DB::transaction(function () use ($validated, $damageType, $userId, $publicCode, $priorityCalc, $photoFiles, $hasFileDataCol) {
            $isAnon = filter_var($validated['is_anonymous'] ?? false, FILTER_VALIDATE_BOOLEAN);

            $latFloat = (float) $validated['latitude'];
            $lonFloat = (float) $validated['longitude'];

            $regionId = $validated['region_id'] ?? null;
            if (!$regionId) {
                $nearest = \App\Models\Region::selectRaw('*, (6371 * acos(cos(radians(?)) * cos(radians(latitude)) * cos(radians(longitude) - radians(?)) + sin(radians(?)) * sin(radians(latitude)))) AS distance', [$latFloat, $lonFloat, $latFloat])
                    ->orderBy('distance', 'asc')
                    ->first();
                $regionId = $nearest?->id;
            }

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
                'status'            => 'SUBMITTED',
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
                'to_status'    => 'SUBMITTED',
                'note'         => 'Laporan dibuat oleh warga dan menunggu verifikasi administratif.',
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
                $fileData = ($hasFileDataCol && $realPath && file_exists($realPath)) ? base64_encode(file_get_contents($realPath)) : null;
                $path = $photo->store('complaints', 'public');
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
                if ($hasFileDataCol) {
                    $photoAttrs['file_data'] = $fileData;
                }
                ComplaintPhoto::create($photoAttrs);
            }

            return $complaint;
        });

        return ApiResponse::success(
            new ComplaintResource($complaint->load(['category', 'user', 'photos'])),
            'Laporan pengaduan berhasil dikirim',
            201
        );
    }

    /**
     * Hapus laporan pengaduan (khusus warga pemilik laporan atau Admin/SuperAdmin).
     */
    public function destroy(Request $request, int $id): JsonResponse
    {
        $user = $this->getAuthenticatedUser($request);
        $complaint = Complaint::with('photos')->findOrFail($id);

        $isOwner = $user && $complaint->user_id === $user->id;
        $isAdmin = $user && in_array($user->role, ['ADMIN', 'SUPERADMIN'], true);

        if (!$isOwner && !$isAdmin) {
            return ApiResponse::error('Anda tidak memiliki izin untuk membatalkan laporan ini.', 403);
        }

        // Laporan HANYA boleh dibatalkan jika masih berstatus SUBMITTED (belum diverifikasi oleh operator).
        // Begitu diverifikasi (DISPATCHED, IN_PROGRESS, WAITING_VERIFICATION, COMPLETED, CLOSED),
        // laporan TIDAK BISA DIHAPUS dan masuk ke sistem arsip permanen!
        if ($complaint->status !== 'SUBMITTED') {
            return ApiResponse::error(
                'Laporan yang sudah diverifikasi/selesai tidak dapat dihapus dan tersimpan dalam arsip permanen daerah.',
                403
            );
        }

        DB::transaction(function () use ($complaint) {
            foreach ($complaint->photos as $photo) {
                if ($photo->storage_key) {
                    \Illuminate\Support\Facades\Storage::disk('public')->delete($photo->storage_key);
                }
                $photo->delete();
            }

            $complaint->statusHistories()->delete();
            $complaint->dispositions()->delete();
            $complaint->supports()->delete();
            $complaint->delete();
        });

        return ApiResponse::success(null, 'Laporan pengaduan berhasil dihapus.');
    }

    public function show(int $id): JsonResponse
    {
        $complaint = Complaint::with(['category', 'user', 'photos', 'statusHistories.user', 'latestDisposition'])->findOrFail($id);
        return ApiResponse::success(new ComplaintResource($complaint));
    }

    public function toggleSupport(Request $request, int $id): JsonResponse
    {
        $user = $this->getAuthenticatedUser($request);
        if (!$user) {
            return ApiResponse::error('Silakan masuk terlebih dahulu untuk memberikan dukungan terhadap pengaduan ini.', [], 401);
        }
        $userId    = $user->id;
        $complaint = Complaint::findOrFail($id);

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

        // Cari kecamatan dan desa/pusat administratif terdekat dari 15 kecamatan resmi Kolaka Utara
        $nearestRegion = \App\Models\Region::selectRaw('*, (6371 * acos(cos(radians(?)) * cos(radians(latitude)) * cos(radians(longitude) - radians(?)) + sin(radians(?)) * sin(radians(latitude)))) AS distance', [$lat, $lon, $lat])
            ->orderBy('distance', 'asc')
            ->first();

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
        $parts[] = "Kab. Kolaka Utara";

        $formatted = implode(', ', $parts);
        $isWithinKolaka = ($lat >= -3.80 && $lat <= -2.50 && $lon >= 120.70 && $lon <= 121.50);

        return ApiResponse::success([
            'region_id'             => $nearestRegion?->id,
            'road'                  => $road,
            'village'               => $cleanVillage,
            'district'              => "Kecamatan {$cleanDistrict}",
            'regency'               => 'Kabupaten Kolaka Utara',
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

        $nearest = \App\Models\Region::selectRaw('*, (6371 * acos(cos(radians(?)) * cos(radians(latitude)) * cos(radians(longitude) - radians(?)) + sin(radians(?)) * sin(radians(latitude)))) AS distance', [$lat, $lon, $lat])
            ->orderBy('distance', 'asc')
            ->first();

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
        $parts[] = "Kab. Kolaka Utara";

        return implode(', ', $parts);
    }
}
