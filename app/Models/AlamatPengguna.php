<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AlamatPengguna extends Model
{
    use HasFactory;

    protected $table = 'alamat_penggunas';

    protected $fillable = [
        'user_id',
        'label',
        'full_address',
        'latitude',
        'longitude',
        'provinsi_id',
        'provinsi_nama',
        'kota_id',
        'kota_nama',
        'kecamatan_id',
        'kecamatan_nama',
        'kelurahan_id',
        'kelurahan_nama',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
