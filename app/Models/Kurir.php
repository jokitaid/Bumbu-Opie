<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Kurir extends Model
{
    use HasFactory;

    protected $fillable = [
        'nama',
        'estimasi',
        'harga',
        'aktif',
    ];

    public function pesanans()
    {
        return $this->hasMany(Pesanan::class);
    }
}
