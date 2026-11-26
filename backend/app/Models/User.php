<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'phone',
        'avatar',
        'password',
        'role',
        'region_id',
        'is_anonymous',
        'is_active',
        'deactivation_reason',
        'deactivated_at',
        'deactivated_by',
        'last_login_ip',
        'last_login_user_agent',
        'last_login_at',
        'must_change_password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $appends = [
        'needs_first_login_setup',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'last_login_at'     => 'datetime',
            'deactivated_at'    => 'datetime',
            'is_anonymous'      => 'boolean',
            'is_active'            => 'boolean',
            'must_change_password' => 'boolean',
        ];
    }

    /** Email selalu disimpan huruf kecil agar tidak ada akun kembar yang beda kapital. */
    public function setEmailAttribute($value): void
    {
        $this->attributes['email'] = mb_strtolower(trim((string) $value));
    }

    public function region(): BelongsTo
    {
        return $this->belongsTo(Region::class);
    }

    public function complaints(): HasMany
    {
        return $this->hasMany(Complaint::class);
    }

    public function supports(): HasMany
    {
        return $this->hasMany(Support::class);
    }

    public function teams(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(Team::class, 'team_members')
            ->withPivot('role')
            ->withTimestamps();
    }

    public function currentTeam(): ?Team
    {
        return $this->teams()->where('teams.is_active', true)->first();
    }

    public function isWarga(): bool
    {
        return $this->role === 'WARGA';
    }

    public function isAdmin(): bool
    {
        return in_array($this->role, ['ADMIN', 'SUPERADMIN'], true);
    }

    public function isSuperAdmin(): bool
    {
        return in_array($this->role, ['ADMIN', 'SUPERADMIN'], true);
    }

    public function isOperator(): bool
    {
        return in_array($this->role, ['OPERATOR', 'DINAS'], true);
    }

    public function isPetugas(): bool
    {
        return $this->role === 'PETUGAS';
    }

    /** Pengawas lapangan: berasal dari pengawas lapangan, boleh mengelola anggota regunya. */
    public function isPengawas(): bool
    {
        return $this->role === 'PENGAWAS';
    }

    /**
     * Ketua regu menurut team.leader_id, walau peran akunnya masih anggota.
     * Dipakai agar penunjukan ketua tidak memaksa perubahan role.
     */
    public function leadsTeam(): bool
    {
        return Team::where('leader_id', $this->id)->exists();
    }

    /**
     * Fitur setara Super Admin, kecuali Log Aktivitas & Pengaturan Sistem.
     * Satu tempat untuk aturan ini agar Kepala Daerah/Kepala Dinas tidak
     * tertinggal saat fitur admin baru ditambahkan.
     */
    public function hasFullAdminAccess(): bool
    {
        return $this->isSuperAdmin() || $this->isKepalaDaerah() || $this->isKepalaDinas();
    }

    /**
     * Cari akun dari email atau nomor HP yang diketik pengguna.
     *
     * Perbandingan harus tidak peka huruf besar/kecil: PostgreSQL membedakan
     * "Admin@..." dan "admin@...", sehingga pengguna yang mengetik huruf kapital
     * (atau diisi otomatis oleh ponsel) ditolak walau sandinya benar.
     * Nomor HP dibandingkan tanpa spasi, titik, dan tanda hubung.
     */
    public static function findByLogin(string $input): ?self
    {
        $input = trim($input);

        if ($input === '') {
            return null;
        }

        $email = mb_strtolower($input);
        $phone = preg_replace('/[^0-9+]/', '', $input);

        return static::query()
            ->where(function ($q) use ($email, $phone) {
                $q->whereRaw('LOWER(email) = ?', [$email]);

                if ($phone !== '') {
                    $q->orWhereRaw(
                        "REPLACE(REPLACE(REPLACE(COALESCE(phone, ''), ' ', ''), '-', ''), '.', '') = ?",
                        [$phone]
                    );
                }
            })
            ->first();
    }

    /** Akun khusus dashboard monitoring (hanya baca). */
    public function isMonitoring(): bool
    {
        return $this->role === 'MONITORING';
    }

    /** Kepala Dinas memakai role DINAS dan berakses penuh seperti Admin. */
    public function isKepalaDinas(): bool
    {
        return $this->role === 'DINAS';
    }

    public function isDinas(): bool
    {
        return in_array($this->role, ['DINAS', 'OPERATOR', 'PETUGAS'], true);
    }

    public function isKepalaDaerah(): bool
    {
        return in_array($this->role, ['KEPALA_DAERAH', 'BUPATI'], true);
    }

    public function isBupati(): bool
    {
        return $this->isKepalaDaerah();
    }

    /**
     * Pengecekan apakah akun staf baru (Petugas / Operator) masih wajib melengkapi data profil dan sandi baru.
     */
    public function needsFirstLoginSetup(): bool
    {
        if ($this->role === 'WARGA') {
            return false;
        }

        // Satu sumber kebenaran: kolom must_change_password. Dulu gerbang ini juga
        // menebak dari hash "password" dan nomor HP kosong, sehingga akun yang
        // sengaja diberi sandi tertentu tetap dipaksa mengisi ulang.
        return !empty($this->must_change_password);
    }

    public function getNeedsFirstLoginSetupAttribute(): bool
    {
        return $this->needsFirstLoginSetup();
    }
}

