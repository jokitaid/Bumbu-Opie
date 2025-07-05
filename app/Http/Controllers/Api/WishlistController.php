<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Wishlist;
use App\Models\Produk;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class WishlistController extends Controller
{
    /**
     * Display a listing of the user's wishlist.
     */
    public function index()
    {
        $wishlist = Wishlist::where('user_id', Auth::id())
                            ->with('produk.kategori') // Eager load produk and its category
                            ->get();

        return response()->json($wishlist);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'produk_id' => 'required|exists:produks,id',
        ]);

        $wishlistItem = Wishlist::firstOrCreate([
            'user_id' => Auth::id(),
            'produk_id' => $request->produk_id,
        ]);

        return response()->json([
            'message' => 'Produk berhasil ditambahkan ke wishlist.',
            'wishlist' => $wishlistItem
        ], 201);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Produk $produk)
    {
        $deleted = Wishlist::where('user_id', Auth::id())
                           ->where('produk_id', $produk->id)
                           ->delete();

        if ($deleted) {
            return response()->json(['message' => 'Produk berhasil dihapus dari wishlist.'], 204);
        }

        return response()->json(['message' => 'Produk tidak ditemukan di wishlist.'], 404);
    }
} 