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
            // Tambahkan satuan pada setiap item pesanan
            foreach ($pesanan->itemPesanan as $item) {
                $item->satuan = $item->produk->satuan ?? null;
                $item->detail_satuan = $item->produk->detail_satuan ?? null;
            }
            return $pesanan;
        });
        return response()->json($pesanans);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        Log::info('Request pesanan masuk', $request->all());
        try {
            $request->validate([
                'metode_pembayaran' => 'required|string',
                'alamat_pengiriman' => 'required|string',
                'items' => 'required|array|min:1',
                'items.*.product_id' => 'required|exists:produks,id',
                'items.*.quantity' => 'required|integer|min:1',
                'items.*.komponen_bumbu' => 'nullable|array',
                'kurir_id' => 'required|exists:kurirs,id',
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
                    $totalCampuran = 0;
                    $namaCampuran = [];
                    $komponenBumbu = [];
                    if (isset($itemData['komponen_bumbu']) && is_array($itemData['komponen_bumbu'])) {
                        foreach ($itemData['komponen_bumbu'] as $mix) {
                            if (!isset($mix['produk_id']) || !isset($mix['jumlah'])) continue;
                            $produkMix = Produk::find($mix['produk_id']);
                            if ($produkMix && !empty($mix['jumlah'])) {
                                $hargaMix = $produkMix->harga * $mix['jumlah'];
                                $totalCampuran += $hargaMix;
                                $namaCampuran[] = $produkMix->nama . ' (' . $mix['jumlah'] . ' ' . $produkMix->satuan . ')';
                                $komponenBumbu[] = [
                                    'produk_id' => $mix['produk_id'],
                                    'jumlah' => $mix['jumlah'],
                                ];
                            }
                        }
                    }
                    $subtotalTotal = $subtotal + $totalCampuran;
                    $totalHarga += $subtotalTotal;
                    $orderItemsMidtrans[] = [
                        'id' => $produk->id,
                        'price' => (int) $subtotalTotal,
                        'quantity' => 1,
                        'name' => $produk->nama . (count($namaCampuran) ? ' + ' . implode(', ', $namaCampuran) : ''),
                    ];
                }

                $kurir = \App\Models\Kurir::find($request->kurir_id);
                $ongkir = $kurir ? $kurir->harga : 0;
                $totalHarga += $ongkir;
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
                    $komponenBumbu = [];
                    if (isset($itemData['komponen_bumbu']) && is_array($itemData['komponen_bumbu'])) {
                        foreach ($itemData['komponen_bumbu'] as $mix) {
                            if (!isset($mix['produk_id']) || !isset($mix['jumlah'])) continue;
                            $produkMix = Produk::find($mix['produk_id']);
                            if ($produkMix && !empty($mix['jumlah'])) {
                                $hargaMix = $produkMix->harga * $mix['jumlah'];
                                $komponenBumbu[] = [
                                    'produk_id' => $mix['produk_id'],
                                    'nama' => $produkMix->nama,
                                    'harga_satuan' => $produkMix->harga,
                                    'satuan' => $produkMix->satuan,
                                    'jumlah' => $mix['jumlah'],
                                    'subtotal' => $hargaMix,
                                ];
                            }
                        }
                    }
                    $pesanan->itemPesanan()->create([
                        'produk_id' => $itemData['product_id'],
                        'quantity' => $itemData['quantity'],
                        'harga' => $hargaItem,
                        'komponen_bumbu' => $komponenBumbu,
                    ]);
                    $produk->stok -= $itemData['quantity'];
                    $produk->save();
                    if (isset($itemData['komponen_bumbu']) && is_array($itemData['komponen_bumbu'])) {
                        foreach ($itemData['komponen_bumbu'] as $komponen) {
                            if (!isset($komponen['produk_id']) || !isset($komponen['jumlah'])) continue;
                            $produkCampur = Produk::find($komponen['produk_id']);
                            if ($produkCampur) {
                                $produkCampur->stok -= $komponen['jumlah'];
                                $produkCampur->save();
                            }
                        }
                    }
                }

                // Integrasi Midtrans jika online
                $snapToken = null;
                $midtransUrl = null;
                if ($request->metode_pembayaran === 'online_midtrans') {
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
                        ]
                    ];
                    $snapToken = Snap::getSnapToken($params);
                    $midtransUrl = (Config::$isProduction ? 'https://app.midtrans.com/snap/v2/vtweb/' : 'https://app.sandbox.midtrans.com/snap/v2/vtweb/') . $snapToken;
                    $pesanan->midtrans_order_id = $kodePesanan;
                    $pesanan->save();
                }

                DB::commit();

                return response()->json([
                    'message' => 'Pesanan berhasil dibuat.',
                    'pesanan' => $pesanan->load('itemPesanan.produk'),
                    'snap_token' => $snapToken,
                    'midtrans_url' => $midtransUrl,
                ], 201);
            } catch (\Exception $e) {
                DB::rollBack();
                return response()->json(['message' => 'Terjadi kesalahan saat membuat pesanan.', 'error' => $e->getMessage(), 'trace' => $e->getTraceAsString()], 500);
            }
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'message' => 'Validasi gagal',
                'errors' => $e->errors()
            ], 422);
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
        $pesananData->subtotal = $pesanan->total_harga - ($pesanan->ongkir ?? 0);
        $pesananData->ongkir = $pesanan->ongkir ?? 0;
        $pesananData->total_harga = $pesanan->total_harga;
        $itemsDetail = [];
        foreach ($pesananData->itemPesanan as $item) {
            $item->satuan = $item->produk->satuan ?? null;
            $item->detail_satuan = $item->produk->detail_satuan ?? null;
            // Tambahkan detail campuran ke response
            if ($item->komponen_bumbu) {
                $komponenBumbu = $item->komponen_bumbu;
                if (is_string($komponenBumbu)) {
                    $komponenBumbu = json_decode($komponenBumbu, true) ?: [];
                }
                $detailCampuran = [];
                $totalCampuran = 0;
                if (is_array($komponenBumbu)) {
                    foreach ($komponenBumbu as &$komponen) {
                        $produkCampur = \App\Models\Produk::find($komponen['produk_id'] ?? null);
                        if ($produkCampur) {
                            $komponen['nama'] = $produkCampur->nama;
                            $komponen['satuan'] = $produkCampur->satuan;
                            $komponen['detail_satuan'] = $produkCampur->detail_satuan;
                            $komponen['harga_satuan'] = $produkCampur->harga;
                            $komponen['subtotal'] = $produkCampur->harga * ($komponen['jumlah'] ?? 1);
                            $totalCampuran += $komponen['subtotal'];
                            $detailCampuran[] = $komponen;
                        }
                    }
                }
                $item->komponen_bumbu_detail = $detailCampuran;
                $item->subtotal_campuran = $totalCampuran;
            }
            $item->subtotal_produk = $item->harga * $item->quantity;
            $item->subtotal_total = ($item->subtotal_produk ?? 0) + ($item->subtotal_campuran ?? 0);
            $itemsDetail[] = [
                'produk_id' => $item->produk_id,
                'nama' => $item->produk->nama ?? '',
                'quantity' => $item->quantity,
                'harga_satuan' => $item->harga,
                'subtotal_produk' => $item->subtotal_produk,
                'campuran' => $item->komponen_bumbu_detail ?? [],
                'subtotal_campuran' => $item->subtotal_campuran ?? 0,
                'subtotal_total' => $item->subtotal_total,
            ];
        }
        $pesananData->items_detail = $itemsDetail;
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

        \Midtrans\Config::$isProduction = env('MIDTRANS_IS_PRODUCTION', false);
        \Midtrans\Config::$serverKey = env('MIDTRANS_SERVER_KEY');
        \Midtrans\Config::$isSanitized = true;

        $notif = new \Midtrans\Notification();
        $transactionStatus = $notif->transaction_status;
        $orderId = $notif->order_id;
        $fraudStatus = $notif->fraud_status;

        // Cek apakah pesanan sudah ada
        $pesanan = \App\Models\Pesanan::where('kode_pesanan', $orderId)->first();
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
            $this->restoreStokPesanan($pesanan);
            // Buat notifikasi untuk pembayaran ditolak
            $this->createNotification($pesanan, 'Pembayaran ditolak', 'Pembayaran pesanan #' . $pesanan->kode_pesanan . ' ditolak oleh sistem. Silakan coba metode pembayaran lain.', [
                'payment_type' => $notif->payment_type ?? 'unknown',
                'fraud_status' => $fraudStatus
            ]);
        } elseif ($transactionStatus == 'expire') {
            $pesanan->update(['midtrans_transaction_status' => 'expire', 'status' => 'cancelled']);
            $this->restoreStokPesanan($pesanan);
            // Buat notifikasi untuk pembayaran kadaluarsa
            $this->createNotification($pesanan, 'Pembayaran kadaluarsa', 'Pembayaran pesanan #' . $pesanan->kode_pesanan . ' telah kadaluarsa. Silakan buat pesanan baru.', [
                'payment_type' => $notif->payment_type ?? 'unknown',
                'fraud_status' => $fraudStatus
            ]);
        } elseif ($transactionStatus == 'cancel') {
            $pesanan->update(['midtrans_transaction_status' => 'cancel', 'status' => 'cancelled']);
            $this->restoreStokPesanan($pesanan);
            // Buat notifikasi untuk pembayaran dibatalkan
            $this->createNotification($pesanan, 'Pembayaran dibatalkan', 'Pembayaran pesanan #' . $pesanan->kode_pesanan . ' telah dibatalkan oleh Anda.', [
                'payment_type' => $notif->payment_type ?? 'unknown',
                'fraud_status' => $fraudStatus
            ]);
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
                    'satuan' => $item->produk->satuan ?? null,
                    'detail_satuan' => $item->produk->detail_satuan ?? null,
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
     * Helper untuk mengembalikan stok produk dan komponen campuran pada pesanan
     */
    private function restoreStokPesanan($pesanan)
    {
        foreach ($pesanan->itemPesanan as $item) {
            // Kembalikan stok produk utama
            $produk = \App\Models\Produk::find($item->produk_id);
            if ($produk) {
                $produk->stok += $item->quantity;
                $produk->save();
            }
            // Kembalikan stok komponen campuran jika ada
            $komponenBumbu = $item->komponen_bumbu;
            if (is_string($komponenBumbu)) {
                $komponenBumbu = json_decode($komponenBumbu, true) ?: [];
            }
            if (is_array($komponenBumbu)) {
                foreach ($komponenBumbu as $komponen) {
                    $produkCampur = \App\Models\Produk::find($komponen['produk_id'] ?? null);
                    if ($produkCampur && isset($komponen['jumlah'])) {
                        $produkCampur->stok += $komponen['jumlah'];
                        $produkCampur->save();
                    }
                }
            }
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
            // Tambahkan satuan pada setiap item pesanan
            foreach ($pesanan->itemPesanan as $item) {
                $item->satuan = $item->produk->satuan ?? null;
                $item->detail_satuan = $item->produk->detail_satuan ?? null;
            }
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
