<?php

namespace App\Models;

use App\Models\Traits\HasStringId;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Pengeluaran extends Model
{
    use HasFactory, HasStringId;

    protected $table = 'pengeluaran';

    protected $fillable = [
        'id',
        'judul',
        'nama_barang',
        'progdi',
        'deskripsi',
        'nominal',
        'tanggal',
        'jenis',
        'jenis_bukti',
        'nomor_bukti',
        'nota_urls',
        'kode_pembelian',
        'pembelian_ke',
        'user_id',
        'kelompok_pembelian_id',
    ];

    protected function casts(): array
    {
        return [
            'nominal' => 'decimal:2',
            'tanggal' => 'datetime',
            'pembelian_ke' => 'integer',
            'nota_urls' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function kelompokPembelian(): BelongsTo
    {
        return $this->belongsTo(KelompokPembelian::class, 'kelompok_pembelian_id');
    }

    public function barang(): HasMany
    {
        return $this->hasMany(Barang::class, 'pengeluaran_id');
    }

    public function scopeJenis($query, string $jenis)
    {
        return $query->where('jenis', $jenis);
    }
}
