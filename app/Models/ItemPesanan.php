<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ItemPesanan extends Model
{
    use HasFactory;

    protected $table = 'item_pesanans';

    protected $fillable = [
        'pesanan_id',
        'produk_id',
        'quantity',
        'harga',
        'komponen_bumbu',
    ];

    protected $casts = [
        'harga' => 'decimal:2',
        'komponen_bumbu' => 'array',
    ];

    public function pesanan()
    {
        return $this->belongsTo(Pesanan::class, 'pesanan_id');
    }

    public function produk()
    {
        return $this->belongsTo(Produk::class, 'produk_id');
    }
}
