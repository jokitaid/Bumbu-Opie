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
        ]);

        $produk = Produk::find($request->product_id);
        if ($produk->stok < $request->quantity) {
            return response()->json(['message' => 'Stok tidak mencukupi.'], 400);
        }

        $keranjang = Keranjang::where('user_id', auth()->id())
                                ->where('product_id', $request->product_id)
                                ->first();

        if ($keranjang) {
            $keranjang->quantity += $request->quantity;
            $keranjang->save();
        } else {
            $keranjang = Keranjang::create([
                'user_id' => auth()->id(),
                'product_id' => $request->product_id,
                'quantity' => $request->quantity,
            ]);
        }

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
        ]);

        $produk = Produk::find($keranjang->product_id);
        if ($produk->stok < $request->quantity) {
            return response()->json(['message' => 'Stok tidak mencukupi.'], 400);
        }

        $keranjang->update($request->only('quantity'));

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
}
