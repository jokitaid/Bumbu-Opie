<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\LaporanPenjualan;

class Pesanan extends Model
{
    use HasFactory;

    protected $table = 'pesanans';

    protected $fillable = [
        'kode_pesanan',
        'user_id',
        'total_harga',
        'status',
        'metode_pembayaran',
        'alamat_pengiriman',
        'midtrans_order_id',
        'midtrans_transaction_status',
        'kurir_id',
        'ongkir',
    ];

    protected $casts = [
        'total_harga' => 'decimal:2',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function itemPesanan()
    {
        return $this->hasMany(ItemPesanan::class, 'pesanan_id');
    }

    public function kurir()
    {
        return $this->belongsTo(\App\Models\Kurir::class);
    }

    protected static function booted()
    {
        static::updated(function ($pesanan) {
            // Jika status berubah menjadi processing atau completed, dan belum ada laporan
            if (in_array($pesanan->status, ['processing', 'completed'])) {
                $exists = LaporanPenjualan::where('tanggal', $pesanan->updated_at->toDateString())
                    ->where('produk_id', optional($pesanan->itemPesanan->first())->produk_id)
                    ->where('status', $pesanan->status)
                    ->exists();
                if (!$exists) {
                    foreach ($pesanan->itemPesanan as $item) {
                        LaporanPenjualan::create([
                            'tanggal' => $pesanan->updated_at->toDateString(),
                            'produk_id' => $item->produk_id,
                            'nama_produk' => $item->produk->nama ?? '-',
                            'jumlah_terjual' => $item->quantity,
                            'total_harga' => $item->harga * $item->quantity,
                            'status' => $pesanan->status,
                        ]);
                    }
                }
            }
        });
    }
}
