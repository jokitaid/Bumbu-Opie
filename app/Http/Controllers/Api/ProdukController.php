<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Produk;
use Illuminate\Http\Request;
use App\Models\Kategori;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class ProdukController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $produks = Produk::with('kategori')->get();
        return response()->json($produks);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'nama' => 'required|string|max:255',
            'deskripsi' => 'nullable|string',
            'harga' => 'required|numeric|min:0',
            'stok' => 'required|integer|min:0',
            'kategori_id' => 'required|exists:kategoris,id',
            'gambar' => 'nullable|image|max:2048', // Max 2MB
            'diskon' => 'nullable|numeric|min:0|max:100',
        ]);

        $imagePath = null;
        if ($request->hasFile('gambar')) {
            $imagePath = $request->file('gambar')->store('produks', 'public');
        }

        $produk = Produk::create([
            'kode_barang' => 'BM-' . Str::upper(Str::random(5)),
            'nama' => $request->nama,
            'deskripsi' => $request->deskripsi,
            'harga' => $request->harga,
            'stok' => $request->stok,
            'kategori_id' => $request->kategori_id,
            'gambar' => $imagePath,
            'diskon' => $request->diskon ?? 0,
        ]);

        return response()->json($produk->load('kategori'), 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Produk $produk)
    {
        return response()->json($produk->load('kategori'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Produk $produk)
    {
        $request->validate([
            'nama' => 'required|string|max:255',
            'deskripsi' => 'nullable|string',
            'harga' => 'required|numeric|min:0',
            'stok' => 'required|integer|min:0',
            'kategori_id' => 'required|exists:kategoris,id',
            'gambar' => 'nullable|image|max:2048',
            'diskon' => 'nullable|numeric|min:0|max:100',
        ]);

        $imagePath = $produk->gambar;
        if ($request->hasFile('gambar')) {
            if ($imagePath) {
                Storage::disk('public')->delete($imagePath);
            }
            $imagePath = $request->file('gambar')->store('produks', 'public');
        }

        $produk->update([
            'nama' => $request->nama,
            'deskripsi' => $request->deskripsi,
            'harga' => $request->harga,
            'stok' => $request->stok,
            'kategori_id' => $request->kategori_id,
            'gambar' => $imagePath,
            'diskon' => $request->diskon ?? $produk->diskon,
        ]);

        return response()->json($produk->load('kategori'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Produk $produk)
    {
        if ($produk->gambar) {
            Storage::disk('public')->delete($produk->gambar);
        }
        $produk->delete();
        return response()->json(null, 204);
    }

    public function byKategori(Kategori $kategori)
    {
        $produks = Produk::where('kategori_id', $kategori->id)->with('kategori')->get();
        return response()->json($produks);
    }
}
