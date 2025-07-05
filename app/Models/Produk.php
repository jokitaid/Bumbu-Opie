<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Kategori;

class Produk extends Model
{
    use HasFactory;

    protected $table = 'produks';

    protected $fillable = [
        'kode_barang',
        'nama',
        'slug',
        'deskripsi',
        'harga',
        'stok',
        'kategori_id',
        'gambar',
        'diskon',
    ];

    protected $casts = [
        'harga' => 'decimal:2',
        'diskon' => 'decimal:2',
    ];

    public function kategori()
    {
        return $this->belongsTo(Kategori::class, 'kategori_id');
    }
}
