<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ItemPesanan;
use App\Models\Pesanan;
use Illuminate\Http\Request;

class ItemPesananController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // Untuk mendapatkan item pesanan berdasarkan ID pesanan
        $request->validate([
            'pesanan_id' => 'required|exists:pesanans,id',
        ]);

        $pesanan = Pesanan::where('user_id', auth()->id())
                           ->find($request->pesanan_id);

        if (!$pesanan) {
            return response()->json(['message' => 'Pesanan tidak ditemukan atau tidak diizinkan.'], 403);
        }

        $itemPesanans = $pesanan->itemPesanan()->with('produk')->get();
        $itemPesanans->transform(function ($item) {
            $item->satuan = $item->produk->satuan ?? null;
            $item->detail_satuan = $item->produk->detail_satuan ?? null;
            return $item;
        });
        return response()->json($itemPesanans);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Penambahan item pesanan seharusnya dilakukan melalui proses pembuatan pesanan di PesananController
        // Endpoint ini mungkin tidak terlalu relevan untuk API umum jika item pesanan selalu terkait dengan pesanan baru.
        return response()->json(['message' => 'Endpoint ini tidak dimaksudkan untuk penambahan item pesanan secara langsung.'], 405);
    }

    /**
     * Display the specified resource.
     */
    public function show(ItemPesanan $itemPesanan)
    {
        // Pastikan item pesanan ini milik pesanan yang dimiliki oleh user yang sedang login
        if ($itemPesanan->pesanan->user_id !== auth()->id()) {
            return response()->json(['message' => 'Tidak diizinkan.'], 403);
        }
        $itemPesanan->load('produk');
        $itemPesanan->satuan = $itemPesanan->produk->satuan ?? null;
        $itemPesanan->detail_satuan = $itemPesanan->produk->detail_satuan ?? null;
        return response()->json($itemPesanan);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, ItemPesanan $itemPesanan)
    {
        // Pastikan item pesanan ini milik pesanan yang dimiliki oleh user yang sedang login
        if ($itemPesanan->pesanan->user_id !== auth()->id()) {
            return response()->json(['message' => 'Tidak diizinkan.'], 403);
        }

        $request->validate([
            'quantity' => 'required|integer|min:1',
        ]);

        // Periksa stok produk jika kuantitas diperbarui
        $produk = $itemPesanan->produk;
        if ($produk->stok < $request->quantity) {
            return response()->json(['message' => 'Stok produk ' . $produk->nama . ' tidak mencukupi.'], 400);
        }

        $itemPesanan->update($request->only('quantity'));
        $itemPesanan->load('produk');
        $itemPesanan->satuan = $itemPesanan->produk->satuan ?? null;
        $itemPesanan->detail_satuan = $itemPesanan->produk->detail_satuan ?? null;
        return response()->json($itemPesanan);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ItemPesanan $itemPesanan)
    {
        // Pastikan item pesanan ini milik pesanan yang dimiliki oleh user yang sedang login
        if ($itemPesanan->pesanan->user_id !== auth()->id()) {
            return response()->json(['message' => 'Tidak diizinkan.'], 403);
        }

        // Kembalikan stok produk saat item dihapus dari pesanan
        $produk = $itemPesanan->produk;
        if ($produk) {
            $produk->stok += $itemPesanan->quantity;
            $produk->save();
        }

        $itemPesanan->delete();
        return response()->json(null, 204);
    }
}
