<?php

namespace App\Models;

use App\Models\Traits\HasStringId;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Kategori extends Model
{
    use HasFactory, HasStringId;

    protected $table = 'kategori';

    protected $fillable = [
        'id',
        'nama',
        'keterangan',
    ];

    public function barang(): HasMany
    {
        return $this->hasMany(Barang::class, 'kategori_id');
    }
}
