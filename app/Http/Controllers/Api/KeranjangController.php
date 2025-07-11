<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Keranjang;
use App\Models\Produk;
use Illuminate\Http\Request;

class KeranjangController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $keranjangs = Keranjang::where('user_id', auth()->id())->with('produk')->get();
        return response()->json($keranjangs);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:produks,id',
            'quantity' => 'required|integer|min:1',
            'komponen_bumbu' => 'nullable|array',
        ]);

        $produk = Produk::find($request->product_id);
        if ($produk->stok < $request->quantity) {
            return response()->json(['message' => 'Stok tidak mencukupi.'], 400);
        }

        // Hitung harga bumbu utama
        $hargaUtama = $produk->harga * $request->quantity;

        // Hitung harga campuran
        $totalCampuran = 0;
        $detailCampuran = [];
        $komponenBumbuInput = $request->komponen_bumbu ?? [];
        if (is_string($komponenBumbuInput)) {
            $komponenBumbuInput = json_decode($komponenBumbuInput, true) ?: [];
        }
        $komponenBumbuToSave = [];
        if (is_array($komponenBumbuInput)) {
            foreach ($komponenBumbuInput as $mix) {
                $produkMix = Produk::find($mix['produk_id'] ?? null);
                if ($produkMix && !empty($mix['jumlah'])) {
                    $hargaMix = $produkMix->harga * $mix['jumlah'];
                    $detailCampuran[] = [
                        'nama' => $produkMix->nama,
                        'jumlah' => $mix['jumlah'],
                        'satuan' => $produkMix->satuan,
                        'harga_satuan' => $produkMix->harga,
                        'subtotal' => $hargaMix,
                    ];
                    $komponenBumbuToSave[] = [
                        'produk_id' => $produkMix->id,
                        'nama' => $produkMix->nama,
                        'harga_satuan' => $produkMix->harga,
                        'satuan' => $produkMix->satuan,
                        'jumlah' => $mix['jumlah'],
                        'subtotal' => $hargaMix,
                    ];
                    $totalCampuran += $hargaMix;
                }
            }
        }
        $totalHarga = $hargaUtama + $totalCampuran;

        // Simpan ke keranjang
        $keranjang = Keranjang::where('user_id', auth()->id())
                                ->where('product_id', $request->product_id)
                                ->where(function($q) use ($request) {
                                    if ($request->has('komponen_bumbu')) {
                                        $q->where('komponen_bumbu', json_encode($request->komponen_bumbu));
                                    } else {
                                        $q->whereNull('komponen_bumbu');
                                    }
                                })
                                ->first();

        if ($keranjang) {
            $keranjang->quantity += $request->quantity;
            $keranjang->komponen_bumbu = $komponenBumbuToSave;
            $keranjang->save();
        } else {
            $keranjang = Keranjang::create([
                'user_id' => auth()->id(),
                'product_id' => $request->product_id,
                'quantity' => $request->quantity,
                'komponen_bumbu' => $komponenBumbuToSave,
            ]);
        }

        // Tambahkan detail campuran ke response
        $keranjang->harga_utama = $hargaUtama;
        $keranjang->detail_campuran = $detailCampuran;
        $keranjang->total_campuran = $totalCampuran;
        $keranjang->total_harga = $totalHarga;

        return response()->json($keranjang->load('produk'), 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Keranjang $keranjang)
    {
        if ($keranjang->user_id !== auth()->id()) {
            return response()->json(['message' => 'Tidak diizinkan.'], 403);
        }
        return response()->json($keranjang->load('produk'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Keranjang $keranjang)
    {
        if ($keranjang->user_id !== auth()->id()) {
            return response()->json(['message' => 'Tidak diizinkan.'], 403);
        }

        $request->validate([
            'quantity' => 'required|integer|min:1',
            'komponen_bumbu' => 'nullable|array',
        ]);

        $komponenBumbuInput = $request->komponen_bumbu ?? [];
        if (is_string($komponenBumbuInput)) {
            $komponenBumbuInput = json_decode($komponenBumbuInput, true) ?: [];
        }
        $komponenBumbuToSave = [];
        $totalCampuran = 0;
        $detailCampuran = [];
        if (is_array($komponenBumbuInput)) {
            foreach ($komponenBumbuInput as $mix) {
                $produkMix = Produk::find($mix['produk_id'] ?? null);
                if ($produkMix && !empty($mix['jumlah'])) {
                    $hargaMix = $produkMix->harga * $mix['jumlah'];
                    $detailCampuran[] = [
                        'nama' => $produkMix->nama,
                        'jumlah' => $mix['jumlah'],
                        'satuan' => $produkMix->satuan,
                        'harga_satuan' => $produkMix->harga,
                        'subtotal' => $hargaMix,
                    ];
                    $komponenBumbuToSave[] = [
                        'produk_id' => $produkMix->id,
                        'nama' => $produkMix->nama,
                        'harga_satuan' => $produkMix->harga,
                        'satuan' => $produkMix->satuan,
                        'jumlah' => $mix['jumlah'],
                        'subtotal' => $hargaMix,
                    ];
                    $totalCampuran += $hargaMix;
                }
            }
        }
        $data = [
            'quantity' => $request->quantity,
            'komponen_bumbu' => $komponenBumbuToSave,
        ];
        $keranjang->update($data);

        // Kalkulasi ulang harga total dan detail campuran (seperti di store)
        $produk = Produk::find($keranjang->product_id);
        $hargaUtama = $produk->harga * $keranjang->quantity;

        // Hitung ulang total campuran dari data yang sudah disimpan
        $totalCampuranFromSaved = 0;
        $komponenBumbuSaved = $keranjang->komponen_bumbu ?? [];
        if (is_array($komponenBumbuSaved)) {
            foreach ($komponenBumbuSaved as $mix) {
                $totalCampuranFromSaved += $mix['subtotal'] ?? 0;
            }
        }
        $totalHarga = $hargaUtama + $totalCampuranFromSaved;

        $keranjang->harga_utama = $hargaUtama;
        $keranjang->detail_campuran = $detailCampuran;
        $keranjang->total_campuran = $totalCampuranFromSaved;
        $keranjang->total_harga = $totalHarga;

        return response()->json($keranjang->load('produk'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Keranjang $keranjang)
    {
        if ($keranjang->user_id !== auth()->id()) {
            return response()->json(['message' => 'Tidak diizinkan.'], 403);
        }
        
        $keranjang->delete();
        return response()->json(null, 204);
    }

    /**
     * Hapus semua isi keranjang user
     */
    public function clearAll(Request $request)
    {
        $userId = auth()->id();
        $deleted = \App\Models\Keranjang::where('user_id', $userId)->delete();
        return response()->json([
            'message' => 'Semua isi keranjang berhasil dihapus.',
            'deleted' => $deleted
        ]);
    }
}
