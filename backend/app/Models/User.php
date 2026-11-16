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
            'is_active'         => 'boolean',
        ];
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
        if (!in_array($this->role, ['PETUGAS', 'OPERATOR', 'DINAS'])) {
            return false;
        }

        // Jika sandi masih berupa default 'password'
        if (\Illuminate\Support\Facades\Hash::check('password', $this->password)) {
            return true;
        }

        // Jika nomor telepon / WhatsApp masih belum dilengkapi
        if (empty($this->phone) || trim($this->phone) === '') {
            return true;
        }

        return false;
    }

    public function getNeedsFirstLoginSetupAttribute(): bool
    {
        return $this->needsFirstLoginSetup();
    }
}

