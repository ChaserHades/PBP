<?php

namespace App\Models;

use App\Models\Traits\HasStringId;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Ruangan extends Model
{
    use HasFactory, HasStringId;

    protected $table = 'ruangan';

    protected $fillable = [
        'id',
        'kode',
        'nama',
        'lantai',
    ];

    public function barang(): HasMany
    {
        return $this->hasMany(Barang::class, 'ruangan_id');
    }
}
