<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Pesanan;
use App\Models\Produk;
use App\Models\Notifikasi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Midtrans\Config;
use Midtrans\Snap;
use Midtrans\Notification;
use Illuminate\Support\Facades\Log;

class PesananController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $pesanans = Pesanan::where('user_id', auth()->id())->with('user', 'itemPesanan.produk')->get();
        $statusLabels = [
            'pending' => 'Menunggu Pembayaran',
            'processing' => 'Dikemas',
            'dikirim' => 'Dikirim',
            'completed' => 'Selesai',
            'cancelled' => 'Dibatalkan',
        ];
        $pesanans->transform(function ($pesanan) use ($statusLabels) {
            $pesanan->status_label = $statusLabels[$pesanan->status] ?? $pesanan->status;
            return $pesanan;
        });
        return response()->json($pesanans);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'metode_pembayaran' => 'required|string',
            'alamat_pengiriman' => 'required|string',
            'items' => 'required|array|min:1', // Pastikan ada item yang dikirim
            'items.*.product_id' => 'required|exists:produks,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.komponen_bumbu' => 'nullable|array', // Validasi untuk bumbu campur
        ]);

        $userId = auth()->id();

        DB::beginTransaction();
        try {
            $totalHarga = 0;
            $orderItemsMidtrans = [];

            foreach ($request->items as $itemData) {
                $produk = Produk::find($itemData['product_id']);
                if (!$produk) {
                    DB::rollBack();
                    return response()->json(['message' => 'Produk dengan ID ' . $itemData['product_id'] . ' tidak ditemukan.'], 404);
                }
                if ($produk->stok < $itemData['quantity']) {
                    DB::rollBack();
                    return response()->json(['message' => 'Stok produk ' . $produk->nama . ' tidak mencukupi untuk kuantitas yang diminta.'], 400);
                }
                
                $hargaItem = $produk->harga - ($produk->harga * $produk->diskon / 100);
                $subtotal = $hargaItem * $itemData['quantity'];
                $totalHarga += $subtotal;

                $orderItemsMidtrans[] = [
                    'id' => $produk->id,
                    'price' => (int) $hargaItem,
                    'quantity' => (int) $itemData['quantity'],
                    'name' => $produk->nama . (isset($itemData['komponen_bumbu']) ? ' (Campur)' : ''),
                ];
            }

            // Ambil ongkir dari kurir
            $kurir = \App\Models\Kurir::find($request->kurir_id);
            $ongkir = $kurir ? $kurir->harga : 0;
            $totalHarga += $ongkir;

            // Tambahkan ongkir ke item_details Midtrans
            $orderItemsMidtrans[] = [
                'id' => 'ONGKIR',
                'price' => (int) $ongkir,
                'quantity' => 1,
                'name' => 'Ongkos Kirim',
            ];

            $kodePesanan = 'ORD-' . Str::upper(Str::random(8));

            $pesanan = Pesanan::create([
                'user_id' => $userId,
                'kode_pesanan' => $kodePesanan,
                'total_harga' => $totalHarga,
                'status' => 'pending',
                'metode_pembayaran' => $request->metode_pembayaran,
                'alamat_pengiriman' => $request->alamat_pengiriman,
                'kurir_id' => $request->kurir_id,
                'ongkir' => $ongkir,
            ]);

            foreach ($request->items as $itemData) {
                $produk = Produk::find($itemData['product_id']);
                $hargaItem = ($produk->harga - ($produk->harga * $produk->diskon / 100));
                
                $pesanan->itemPesanan()->create([
                    'produk_id' => $itemData['product_id'],
                    'quantity' => $itemData['quantity'],
                    'harga' => $hargaItem,
                    'komponen_bumbu' => isset($itemData['komponen_bumbu']) ? $itemData['komponen_bumbu'] : null,
                ]);
                
                // Kurangi stok produk
                $produk->stok -= $itemData['quantity'];
                $produk->save();
            }

            // --- Integrasi Midtrans --- 
            if ($request->metode_pembayaran === 'online_midtrans') {
                // Set konfigurasi Midtrans
                Config::$isProduction = env('MIDTRANS_IS_PRODUCTION', false);
                Config::$serverKey = env('MIDTRANS_SERVER_KEY');
                Config::$isSanitized = true; 
                Config::$is3ds = true;       

                $customerDetails = [
                    'first_name' => auth()->user()->name,
                    'email' => auth()->user()->email,
                    'phone' => auth()->user()->phone ?? '08123456789', 
                    'address' => auth()->user()->address ?? $request->alamat_pengiriman, 
                ];

                $params = [
                    'transaction_details' => [
                        'order_id' => $kodePesanan, 
                        'gross_amount' => (int) $totalHarga,
                    ],
                    'customer_details' => $customerDetails,
                    'item_details' => $orderItemsMidtrans,
                    'callbacks' => [
                        'finish' => env('APP_URL') . '/api/midtrans/finish',
                        'error' => env('APP_URL') . '/api/midtrans/error',
                        'pending' => env('APP_URL') . '/api/midtrans/pending',
                        'notification' => env('APP_URL') . '/api/midtrans/notification',
                    ]
                ];

                $snapToken = Snap::getSnapToken($params);

                // Update pesanan dengan Midtrans Order ID
                $pesanan->midtrans_order_id = $kodePesanan;
                $pesanan->save();

                DB::commit(); 

                return response()->json([
                    'message' => 'Pesanan berhasil dibuat, lanjutkan pembayaran melalui Midtrans.',
                    'pesanan' => $pesanan->load('itemPesanan.produk'),
                    'snap_token' => $snapToken,
                    'midtrans_url' => (Config::$isProduction ? 'https://app.midtrans.com/snap/v2/vtweb/' : 'https://app.sandbox.midtrans.com/snap/v2/vtweb/') . $snapToken
                ], 201);

            } else {
                // Jika bukan pembayaran online
                // Buat notifikasi untuk pesanan offline
                $this->createNotification($pesanan, 'Pesanan dibuat', 'Pesanan #' . $pesanan->kode_pesanan . ' berhasil dibuat dan menunggu konfirmasi pembayaran.', [
                    'payment_method' => $request->metode_pembayaran,
                    'is_offline_payment' => true
                ]);
                
                DB::commit();
                return response()->json($pesanan->load('itemPesanan.produk'), 201);
            }

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Terjadi kesalahan saat membuat pesanan.', 'error' => $e->getMessage(), 'trace' => $e->getTraceAsString()], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Pesanan $pesanan)
    {
        if ($pesanan->user_id !== auth()->id()) {
            return response()->json(['message' => 'Tidak diizinkan.'], 403);
        }
        $statusLabels = [
            'pending' => 'Menunggu Pembayaran',
            'processing' => 'Dikemas',
            'dikirim' => 'Dikirim',
            'completed' => 'Selesai',
            'cancelled' => 'Dibatalkan',
        ];
        $pesananData = $pesanan->load('user', 'itemPesanan.produk');
        $pesananData->status_label = $statusLabels[$pesanan->status] ?? $pesanan->status;
        
        // Tambahkan label Bahasa Indonesia untuk status transaksi Midtrans
        $midtransStatusLabels = [
            'settlement' => 'Sudah Dibayar',
            'pending' => 'Belum Dibayar',
            'deny' => 'Ditolak',
            'expire' => 'Kadaluarsa',
            'cancel' => 'Dibatalkan',
            'capture' => 'Menunggu Verifikasi',
            'challenge' => 'Perlu Verifikasi',
            '' => '-',
        ];
        $pesananData->midtrans_transaction_status_label = $midtransStatusLabels[$pesanan->midtrans_transaction_status ?? ''] ?? $pesanan->midtrans_transaction_status;

        return response()->json($pesananData);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Pesanan $pesanan)
    {
        if ($pesanan->user_id !== auth()->id()) {
            return response()->json(['message' => 'Tidak diizinkan.'], 403);
        }

        $request->validate([
            'status' => 'required|string|in:pending,processing,dikirim,completed,cancelled',
            'midtrans_order_id' => 'nullable|string',
            'midtrans_transaction_status' => 'nullable|string',
        ]);

        $oldStatus = $pesanan->status;
        $pesanan->update($request->only([
            'status', 'midtrans_order_id', 'midtrans_transaction_status'
        ]));

        // Buat notifikasi jika status berubah
        if ($oldStatus !== $pesanan->status) {
            $statusMessages = [
                'processing' => 'Pesanan #' . $pesanan->kode_pesanan . ' sedang dikemas.',
                'dikirim' => 'Pesanan #' . $pesanan->kode_pesanan . ' telah dikirim. Silakan cek status pengiriman.',
                'completed' => 'Pesanan #' . $pesanan->kode_pesanan . ' telah selesai. Terima kasih telah berbelanja!',
            ];

            if (isset($statusMessages[$pesanan->status])) {
                $this->createNotification($pesanan, 'Status pesanan berubah', $statusMessages[$pesanan->status], [
                    'old_status' => $oldStatus,
                    'new_status' => $pesanan->status,
                    'status_changed_by' => 'admin'
                ]);
            }
        }

        // Tambahkan label status untuk response
        $statusLabels = [
            'pending' => 'Menunggu Pembayaran',
            'processing' => 'Dikemas',
            'dikirim' => 'Dikirim',
            'completed' => 'Selesai',
            'cancelled' => 'Dibatalkan',
        ];
        $pesananData = $pesanan->load('user', 'itemPesanan.produk');
        $pesananData->status_label = $statusLabels[$pesanan->status] ?? $pesanan->status;

        return response()->json($pesananData);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Pesanan $pesanan)
    {
        if ($pesanan->user_id !== auth()->id()) {
            return response()->json(['message' => 'Tidak diizinkan.'], 403);
        }

        DB::beginTransaction();
        try {
            // Kembalikan stok produk
            foreach ($pesanan->itemPesanan as $item) {
                $produk = Produk::find($item->produk_id);
                if ($produk) {
                    $produk->stok += $item->quantity;
                    $produk->save();
                }
            }
            $pesanan->delete();
            DB::commit();
            return response()->json(null, 204);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Terjadi kesalahan saat menghapus pesanan.', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Midtrans Notification Handler
     * Endpoint ini akan menerima callback dari Midtrans setelah status transaksi berubah.
     */
    public function notificationHandler(Request $request)
    {
        Log::info('Midtrans notification received', ['payload' => $request->all()]);
        Log::info('Midtrans raw body', ['raw' => $request->getContent()]);

        // Set konfigurasi Midtrans dengan namespace yang benar
        \Midtrans\Config::$isProduction = env('MIDTRANS_IS_PRODUCTION', false);
        \Midtrans\Config::$serverKey = env('MIDTRANS_SERVER_KEY');
        \Midtrans\Config::$isSanitized = true;

        $payload = $request->all();
        if (empty($payload)) {
            $payload = json_decode($request->getContent(), true);
            Log::info('Midtrans raw body fallback', ['payload' => $payload]);
        }

        if (empty($payload)) {
            Log::error('Midtrans notification: payload tetap kosong!');
            return response()->json(['message' => 'Payload kosong'], 400);
        }

        $notif = new \Midtrans\Notification();

        $transactionStatus = $notif->transaction_status;
        $fraudStatus = $notif->fraud_status;
        $orderId = $notif->order_id;
        Log::info('Cek pesanan dari order_id', ['order_id' => $orderId]);
        $pesanan = Pesanan::where('kode_pesanan', $orderId)->first();

        if (!$pesanan) {
            Log::error('Pesanan tidak ditemukan', ['order_id' => $orderId]);
            return response()->json(['message' => 'Pesanan tidak ditemukan.'], 404);
        }

        if ($transactionStatus == 'capture') {
            // For credit card transaction, we need to check 'fraud_status'
            if ($notif->payment_type == 'credit_card') {
                if ($fraudStatus == 'challenge') {
                    $pesanan->update(['midtrans_transaction_status' => 'challenge', 'status' => 'processing']);
                    // Buat notifikasi untuk pembayaran yang perlu verifikasi
                    $this->createNotification($pesanan, 'Pembayaran perlu verifikasi', 'Pembayaran pesanan #' . $pesanan->kode_pesanan . ' perlu verifikasi tambahan dari bank.', [
                        'payment_type' => $notif->payment_type ?? 'unknown',
                        'fraud_status' => $fraudStatus
                    ]);
                } else {
                    $pesanan->update(['midtrans_transaction_status' => 'success', 'status' => 'processing']);
                    // Buat notifikasi untuk pembayaran berhasil
                    $this->createNotification($pesanan, 'Pembayaran berhasil', 'Pembayaran pesanan #' . $pesanan->kode_pesanan . ' berhasil diproses dan pesanan sedang dikemas.', [
                        'payment_type' => $notif->payment_type ?? 'unknown',
                        'fraud_status' => $fraudStatus
                    ]);
                }
            }
        } elseif ($transactionStatus == 'settlement') {
            $pesanan->update(['midtrans_transaction_status' => 'settlement', 'status' => 'processing']);
            // Buat notifikasi untuk pembayaran berhasil
            $this->createNotification($pesanan, 'Pembayaran berhasil', 'Pembayaran pesanan #' . $pesanan->kode_pesanan . ' berhasil diproses dan pesanan sedang dikemas.', [
                'payment_type' => $notif->payment_type ?? 'unknown',
                'fraud_status' => $fraudStatus
            ]);
        } elseif ($transactionStatus == 'pending') {
            $pesanan->update(['midtrans_transaction_status' => 'pending', 'status' => 'pending']);
            // Buat notifikasi untuk pembayaran tertunda
            $this->createNotification($pesanan, 'Pembayaran tertunda', 'Pembayaran pesanan #' . $pesanan->kode_pesanan . ' masih dalam proses. Silakan selesaikan pembayaran.', [
                'payment_type' => $notif->payment_type ?? 'unknown',
                'fraud_status' => $fraudStatus
            ]);
        } elseif ($transactionStatus == 'deny') {
            $pesanan->update(['midtrans_transaction_status' => 'deny', 'status' => 'cancelled']);
            // Buat notifikasi untuk pembayaran ditolak
            $this->createNotification($pesanan, 'Pembayaran ditolak', 'Pembayaran pesanan #' . $pesanan->kode_pesanan . ' ditolak oleh sistem. Silakan coba metode pembayaran lain.', [
                'payment_type' => $notif->payment_type ?? 'unknown',
                'fraud_status' => $fraudStatus
            ]);
            // TODO: Kembalikan stok jika perlu untuk transaksi yang dibatalkan/ditolak
        } elseif ($transactionStatus == 'expire') {
            $pesanan->update(['midtrans_transaction_status' => 'expire', 'status' => 'cancelled']);
            // Buat notifikasi untuk pembayaran kadaluarsa
            $this->createNotification($pesanan, 'Pembayaran kadaluarsa', 'Pembayaran pesanan #' . $pesanan->kode_pesanan . ' telah kadaluarsa. Silakan buat pesanan baru.', [
                'payment_type' => $notif->payment_type ?? 'unknown',
                'fraud_status' => $fraudStatus
            ]);
            // TODO: Kembalikan stok jika perlu untuk transaksi yang kadaluarsa
        } elseif ($transactionStatus == 'cancel') {
            $pesanan->update(['midtrans_transaction_status' => 'cancel', 'status' => 'cancelled']);
            // Buat notifikasi untuk pembayaran dibatalkan
            $this->createNotification($pesanan, 'Pembayaran dibatalkan', 'Pembayaran pesanan #' . $pesanan->kode_pesanan . ' telah dibatalkan oleh Anda.', [
                'payment_type' => $notif->payment_type ?? 'unknown',
                'fraud_status' => $fraudStatus
            ]);
            // TODO: Kembalikan stok jika perlu untuk transaksi yang dibatalkan oleh pengguna
        }

        return response()->json(['message' => 'Notifikasi Midtrans berhasil diproses.']);
    }

    /**
     * Helper method untuk membuat notifikasi
     */
    private function createNotification($pesanan, $title, $message, $additionalData = [])
    {
        try {
            // Load relasi yang diperlukan
            $pesanan->load('itemPesanan.produk');
            
            // Siapkan data produk untuk notifikasi
            $produkList = [];
            $totalItems = 0;
            foreach ($pesanan->itemPesanan as $item) {
                $produkList[] = [
                    'nama' => $item->produk->nama,
                    'quantity' => $item->quantity,
                    'harga' => $item->harga,
                    'subtotal' => $item->harga * $item->quantity,
                    'komponen_bumbu' => $item->komponen_bumbu,
                ];
                $totalItems += $item->quantity;
            }
            
            // Format total harga
            $totalHargaFormatted = 'Rp ' . number_format($pesanan->total_harga, 0, ',', '.');
            
            // Buat pesan yang lebih informatif
            $informativeMessage = $message;
            if (!empty($produkList)) {
                $produkNames = array_slice(array_column($produkList, 'nama'), 0, 3); // Ambil 3 produk pertama
                $produkText = implode(', ', $produkNames);
                if (count($produkList) > 3) {
                    $produkText .= ' dan ' . (count($produkList) - 3) . ' produk lainnya';
                }
                $informativeMessage .= "\n\nProduk: " . $produkText;
                $informativeMessage .= "\nJumlah item: " . $totalItems;
                $informativeMessage .= "\nTotal: " . $totalHargaFormatted;
            }
            
            Notifikasi::create([
                'id' => Str::uuid(),
                'tipe' => 'pesanan_status',
                'data' => array_merge([
                    'title' => $title,
                    'message' => $informativeMessage,
                    'pesanan_id' => $pesanan->id,
                    'kode_pesanan' => $pesanan->kode_pesanan,
                    'status' => $pesanan->status,
                    'total_harga' => $pesanan->total_harga,
                    'total_harga_formatted' => $totalHargaFormatted,
                    'total_items' => $totalItems,
                    'produk_list' => $produkList,
                    'metode_pembayaran' => $pesanan->metode_pembayaran,
                    'alamat_pengiriman' => $pesanan->alamat_pengiriman,
                    'created_at' => $pesanan->created_at->format('d/m/Y H:i'),
                ], $additionalData),
                'notifiable_type' => 'App\Models\User',
                'notifiable_id' => $pesanan->user_id,
            ]);
            
            Log::info('Notifikasi berhasil dibuat', [
                'pesanan_id' => $pesanan->id,
                'user_id' => $pesanan->user_id,
                'title' => $title,
                'total_items' => $totalItems,
                'total_harga' => $totalHargaFormatted
            ]);
        } catch (\Exception $e) {
            Log::error('Gagal membuat notifikasi', [
                'error' => $e->getMessage(),
                'pesanan_id' => $pesanan->id
            ]);
        }
    }

    /**
     * Redirect URL untuk Midtrans setelah pembayaran berhasil (untuk browser/webview di Flutter).
     */
    public function midtransFinish(Request $request)
    {
        return response()->json(['message' => 'Pembayaran berhasil.', 'order_id' => $request->order_id, 'status_code' => $request->status_code, 'transaction_status' => $request->transaction_status]);
    }

    /**
     * Redirect URL untuk Midtrans setelah pembayaran gagal.
     */
    public function midtransError(Request $request)
    {
        return response()->json(['message' => 'Pembayaran gagal.', 'order_id' => $request->order_id, 'status_code' => $request->status_code, 'transaction_status' => $request->transaction_status], 400);
    }

    /**
     * Redirect URL untuk Midtrans jika pembayaran tertunda.
     */
    public function midtransPending(Request $request)
    {
        return response()->json(['message' => 'Pembayaran tertunda.', 'order_id' => $request->order_id, 'status_code' => $request->status_code, 'transaction_status' => $request->transaction_status]);
    }

    /**
     * Get pesanan by status
     */
    public function byStatus(Request $request)
    {
        $request->validate([
            'status' => 'required|string|in:processing,dikirim,completed,cancelled',
        ]);
        $status = $request->status;
        $pesanans = Pesanan::where('user_id', auth()->id())
            ->where('status', $status)
            ->with('user', 'itemPesanan.produk')
            ->get();
        $statusLabels = [
            'pending' => 'Menunggu Pembayaran',
            'processing' => 'Dikemas',
            'dikirim' => 'Dikirim',
            'completed' => 'Selesai',
            'cancelled' => 'Dibatalkan',
        ];
        $pesanans->transform(function ($pesanan) use ($statusLabels) {
            $pesanan->status_label = $statusLabels[$pesanan->status] ?? $pesanan->status;
            return $pesanan;
        });
        return response()->json($pesanans);
    }

    /**
     * Batalkan pesanan (ubah status ke cancelled dan kembalikan stok)
     */
    public function batalkan(Pesanan $pesanan)
    {
        if ($pesanan->user_id !== auth()->id()) {
            return response()->json(['message' => 'Tidak diizinkan.'], 403);
        }
        if ($pesanan->status === 'cancelled') {
            return response()->json(['message' => 'Pesanan sudah dibatalkan.'], 400);
        }
        // Kembalikan stok produk
        foreach ($pesanan->itemPesanan as $item) {
            $produk = $item->produk;
            if ($produk) {
                $produk->stok += $item->quantity;
                $produk->save();
            }
        }
        $pesanan->status = 'cancelled';
        $pesanan->save();
        
        // Buat notifikasi untuk pesanan dibatalkan
        $this->createNotification($pesanan, 'Pesanan dibatalkan', 'Pesanan #' . $pesanan->kode_pesanan . ' telah dibatalkan. Stok produk telah dikembalikan.', [
            'cancelled_by' => 'user',
            'stok_returned' => true
        ]);
        
        $statusLabels = [
            'pending' => 'Menunggu Pembayaran',
            'processing' => 'Dikemas',
            'dikirim' => 'Dikirim',
            'completed' => 'Selesai',
            'cancelled' => 'Dibatalkan',
        ];
        $pesananData = $pesanan->load('user', 'itemPesanan.produk');
        $pesananData->status_label = $statusLabels[$pesanan->status] ?? $pesanan->status;
        return response()->json([
            'message' => 'Pesanan berhasil dibatalkan.',
            'pesanan' => $pesananData
        ]);
    }
}
