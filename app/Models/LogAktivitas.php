<?php

namespace App\Models;

use App\Models\Traits\HasStringId;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LogAktivitas extends Model
{
    use HasFactory, HasStringId;

    protected $table = 'log_aktivitas';

    public const UPDATED_AT = null;

    protected $fillable = [
        'id',
        'aksi',
        'entitas',
        'entitas_id',
        'detail',
        'user_id',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function scopeEntitas($query, string $entitas)
    {
        return $query->where('entitas', $entitas);
    }

    public function scopeAksi($query, string $aksi)
    {
        return $query->where('aksi', $aksi);
    }

    /**
     * Helper untuk mencatat log aktivitas secara cepat
     */
    public static function catat(string $aksi, string $entitas, ?string $entitasId, string $detail, ?string $userId = null): self
    {
        return self::create([
            'aksi' => $aksi,
            'entitas' => $entitas,
            'entitas_id' => $entitasId,
            'detail' => $detail,
            'user_id' => $userId ?? auth()->id() ?? 'system',
        ]);
    }
}
