<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AlamatPengguna;
use Illuminate\Http\Request;

class AlamatPenggunaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $alamatPenggunas = AlamatPengguna::where('user_id', auth()->id())->get();
        return response()->json($alamatPenggunas);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'label' => 'nullable|string|max:255',
            'full_address' => 'required|string',
            'latitude' => 'nullable|string',
            'longitude' => 'nullable|string',
            'provinsi_id' => 'nullable|string|max:20',
            'provinsi_nama' => 'nullable|string|max:100',
            'kota_id' => 'nullable|string|max:20',
            'kota_nama' => 'nullable|string|max:100',
            'kecamatan_id' => 'nullable|string|max:20',
            'kecamatan_nama' => 'nullable|string|max:100',
            'kelurahan_id' => 'nullable|string|max:20',
            'kelurahan_nama' => 'nullable|string|max:100',
        ]);

        $alamatPengguna = AlamatPengguna::create([
            'user_id' => auth()->id(),
            'label' => $request->label,
            'full_address' => $request->full_address,
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
            'provinsi_id' => $request->provinsi_id,
            'provinsi_nama' => $request->provinsi_nama,
            'kota_id' => $request->kota_id,
            'kota_nama' => $request->kota_nama,
            'kecamatan_id' => $request->kecamatan_id,
            'kecamatan_nama' => $request->kecamatan_nama,
            'kelurahan_id' => $request->kelurahan_id,
            'kelurahan_nama' => $request->kelurahan_nama,
        ]);

        return response()->json($alamatPengguna, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(AlamatPengguna $alamatPengguna)
    {
        if ($alamatPengguna->user_id !== auth()->id()) {
            return response()->json(['message' => 'Tidak diizinkan.'], 403);
        }
        return response()->json($alamatPengguna);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, AlamatPengguna $alamat)
    {
        if ($alamat->user_id !== auth()->id()) {
            return response()->json(['message' => 'Tidak diizinkan.'], 403);
        }

        $request->validate([
            'label' => 'nullable|string|max:255',
            'full_address' => 'required|string',
            'latitude' => 'nullable|string',
            'longitude' => 'nullable|string',
            'provinsi_id' => 'nullable|string|max:20',
            'provinsi_nama' => 'nullable|string|max:100',
            'kota_id' => 'nullable|string|max:20',
            'kota_nama' => 'nullable|string|max:100',
            'kecamatan_id' => 'nullable|string|max:20',
            'kecamatan_nama' => 'nullable|string|max:100',
            'kelurahan_id' => 'nullable|string|max:20',
            'kelurahan_nama' => 'nullable|string|max:100',
        ]);

        $alamat->update($request->only([
            'label', 'full_address', 'latitude', 'longitude',
            'provinsi_id', 'provinsi_nama',
            'kota_id', 'kota_nama',
            'kecamatan_id', 'kecamatan_nama',
            'kelurahan_id', 'kelurahan_nama',
        ]));

        return response()->json($alamat);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(AlamatPengguna $alamat)
    {
        if ($alamat->user_id !== auth()->id()) {
            return response()->json(['message' => 'Tidak diizinkan.'], 403);
        }

        $alamat->delete();
        return response()->json(null, 204);
    }
}
