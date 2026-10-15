<?php

namespace App\Models;

use App\Models\Traits\HasStringId;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Peminjaman extends Model
{
    use HasFactory, HasStringId;

    protected $table = 'peminjaman';

    protected $fillable = [
        'id',
        'barang_id',
        'user_id',
        'nama_peminjam',
        'ruangan_tujuan',
        'tanggal_pinjam',
        'tanggal_kembali',
        'deadline_kembali',
        'status',
        'terlambat',
        'catatan',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_pinjam' => 'datetime',
            'tanggal_kembali' => 'datetime',
            'deadline_kembali' => 'datetime',
            'terlambat' => 'boolean',
        ];
    }

    public function barang(): BelongsTo
    {
        return $this->belongsTo(Barang::class, 'barang_id')->withTrashed();
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function scopeAktif($query)
    {
        return $query->where('status', 'AKTIF');
    }

    public function scopeSelesai($query)
    {
        return $query->where('status', 'SELESAI');
    }

    public function scopeTerlambat($query)
    {
        return $query->where('terlambat', true);
    }

    /**
     * Cek apakah saat ini peminjaman melewati deadline (18:00 WIB sesuai aturan bisnis)
     */
    public function checkIsOverdue(): bool
    {
        if ($this->status === 'SELESAI' && $this->tanggal_kembali) {
            return $this->tanggal_kembali->gt($this->deadline_kembali);
        }

        return Carbon::now()->gt($this->deadline_kembali);
    }
}
