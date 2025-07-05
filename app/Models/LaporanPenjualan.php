<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class LaporanPenjualan extends Model
{
    use HasFactory;

    protected $table = 'laporan_penjualans';
    protected $fillable = [
        'tanggal',
        'produk_id',
        'nama_produk',
        'jumlah_terjual',
        'total_harga',
        'status',
        'kode_pesanan',
        'user_id',
        'nama_pelanggan',
        'metode_pembayaran',
        'diskon',
    ];

    // Relasi ke produk jika perlu
    public function produk()
    {
        return $this->belongsTo(Produk::class, 'produk_id');
    }

    public function pesanan()
    {
        return $this->belongsTo(Pesanan::class, 'kode_pesanan', 'kode_pesanan');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
} 