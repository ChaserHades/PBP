<?php

namespace App\Models;

use App\Models\Traits\HasStringId;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class KelompokPembelian extends Model
{
    use HasFactory, HasStringId;

    protected $table = 'kelompok_pembelian';

    protected $fillable = [
        'id',
        'kode',
        'tanggal',
        'pembelian_ke',
    ];

    protected function casts(): array
    {
        return [
            'tanggal' => 'datetime',
            'pembelian_ke' => 'integer',
        ];
    }

    public function barang(): HasMany
    {
        return $this->hasMany(Barang::class, 'kelompok_pembelian_id');
    }

    public function pengeluaran(): HasMany
    {
        return $this->hasMany(Pengeluaran::class, 'kelompok_pembelian_id');
    }
}
