<?php

namespace App\Models;

use App\Models\Traits\HasStringId;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasStringId;

    protected $table = 'users';

    protected $fillable = [
        'id',
        'name',
        'email',
        'password',
        'provider',
        'role',
        'status_verifikasi',
        'must_change_password',
        'email_verified_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'must_change_password' => 'boolean',
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Relasi Eloquent
    |--------------------------------------------------------------------------
    */

    public function peminjaman(): HasMany
    {
        return $this->hasMany(Peminjaman::class, 'user_id');
    }

    public function pengeluaran(): HasMany
    {
        return $this->hasMany(Pengeluaran::class, 'user_id');
    }

    public function logAktivitas(): HasMany
    {
        return $this->hasMany(LogAktivitas::class, 'user_id');
    }

    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    */

    public function scopeApproved($query)
    {
        return $query->where('status_verifikasi', 'APPROVED');
    }

    public function scopePending($query)
    {
        return $query->where('status_verifikasi', 'PENDING');
    }

    public function scopeRejected($query)
    {
        return $query->where('status_verifikasi', 'REJECTED');
    }

    public function scopeRole($query, string $role)
    {
        return $query->where('role', $role);
    }

    /*
    |--------------------------------------------------------------------------
    | Helper RBAC
    |--------------------------------------------------------------------------
    */

    public function isSuperAdmin(): bool
    {
        return $this->role === 'SUPERADMIN';
    }

    public function isAdmin(): bool
    {
        return in_array($this->role, ['SUPERADMIN', 'ADMIN']);
    }

    public function isTeknisi(): bool
    {
        return $this->role === 'TEKNISI';
    }

    public function isUniversitas(): bool
    {
        return $this->role === 'UNIVERSITAS';
    }

    public function isApproved(): bool
    {
        return $this->status_verifikasi === 'APPROVED';
    }
}
