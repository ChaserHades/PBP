<?php

namespace App\Models;

use App\Models\Traits\HasStringId;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Barang extends Model
{
    use HasFactory, HasStringId, SoftDeletes;

    protected $table = 'barang';

    protected $fillable = [
        'id',
        'nama_barang',
        'progdi',
        'label',
        'label_aset',
        'sn',
        'merek',
        'model',
        'tipe',
        'vendor',
        'keterangan',
        'kondisi',
        'status',
        'jumlah',
        'tanggal_datang',
        'tanggal_pindah',
        'harga',
        'sumber_tahun',
        'pembelian_ke',
        'has_sticker',
        'qr_code',
        'foto',
        'kategori_id',
        'ruangan_id',
        'pengeluaran_id',
        'kelompok_pembelian_id',
    ];

    protected function casts(): array
    {
        return [
            'jumlah' => 'integer',
            'harga' => 'decimal:2',
            'tanggal_datang' => 'datetime',
            'tanggal_pindah' => 'datetime',
            'pembelian_ke' => 'integer',
            'has_sticker' => 'boolean',
            'deleted_at' => 'datetime',
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Relasi Eloquent
    |--------------------------------------------------------------------------
    */

    public function kategori(): BelongsTo
    {
        return $this->belongsTo(Kategori::class, 'kategori_id');
    }

    public function ruangan(): BelongsTo
    {
        return $this->belongsTo(Ruangan::class, 'ruangan_id');
    }

    public function pengeluaran(): BelongsTo
    {
        return $this->belongsTo(Pengeluaran::class, 'pengeluaran_id');
    }

    public function kelompokPembelian(): BelongsTo
    {
        return $this->belongsTo(KelompokPembelian::class, 'kelompok_pembelian_id');
    }

    public function peminjaman(): HasMany
    {
        return $this->hasMany(Peminjaman::class, 'barang_id');
    }

    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    */

    public function scopeTersedia($query)
    {
        return $query->where('status', 'TERSEDIA');
    }

    public function scopeDipinjam($query)
    {
        return $query->where('status', 'DIPINJAM');
    }

    public function scopeMaintenance($query)
    {
        return $query->where('status', 'MAINTENANCE');
    }

    public function scopeKondisi($query, string $kondisi)
    {
        return $query->where('kondisi', $kondisi);
    }

    /**
     * Scope untuk aset yang dihapus kurang dari atau sama dengan 6 hari (Retention Window).
     */
    public function scopeRecentlyDeleted($query)
    {
        return $query->onlyTrashed()->where('deleted_at', '>=', Carbon::now()->subDays(6));
    }

    /**
     * Scope untuk aset yang dihapus lebih dari 6 hari yang lalu (Expired).
     */
    public function scopeExpiredDeleted($query)
    {
        return $query->onlyTrashed()->where('deleted_at', '<', Carbon::now()->subDays(6));
    }

    /*
    |--------------------------------------------------------------------------
    | Helper State Recovery
    |--------------------------------------------------------------------------
    */

    public function isRecoverable(): bool
    {
        if (!$this->trashed()) {
            return false;
        }

        return $this->deleted_at >= Carbon::now()->subDays(6);
    }

    public function isExpired(): bool
    {
        if (!$this->trashed()) {
            return false;
        }

        return $this->deleted_at < Carbon::now()->subDays(6);
    }
}
