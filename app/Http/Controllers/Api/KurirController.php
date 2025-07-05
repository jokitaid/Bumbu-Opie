<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Kurir;
use Illuminate\Http\Request;

class KurirController extends Controller
{
    // List semua kurir aktif
    public function index()
    {
        $kurirs = Kurir::where('aktif', true)->get(['id', 'nama', 'estimasi', 'harga']);
        return response()->json($kurirs);
    }

    // Detail kurir (estimasi & harga)
    public function show(Kurir $kurir)
    {
        if (!$kurir->aktif) {
            return response()->json(['message' => 'Kurir tidak aktif'], 404);
        }
        return response()->json([
            'id' => $kurir->id,
            'nama' => $kurir->nama,
            'estimasi' => $kurir->estimasi,
            'harga' => $kurir->harga,
        ]);
    }
} 