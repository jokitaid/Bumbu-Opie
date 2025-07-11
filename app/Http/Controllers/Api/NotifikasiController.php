<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Notifikasi;
use Illuminate\Http\Request;

class NotifikasiController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $query = Notifikasi::where('notifiable_type', 'App\Models\User')
            ->where('notifiable_id', $user->id)
            ->orderBy('created_at', 'desc');
        
        if ($request->boolean('unread')) {
            $query->whereNull('dibaca_pada');
        }
        
        $notifikasis = $query->limit(20)->get();
        return response()->json($notifikasis);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $notifikasi = Notifikasi::where('id', $id)
            ->where('notifiable_type', 'App\Models\User')
            ->where('notifiable_id', auth()->id())
            ->first();
            
        if (!$notifikasi) {
            return response()->json(['message' => 'Notifikasi tidak ditemukan.'], 404);
        }
        
        return response()->json($notifikasi);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Mark the specified notification as read.
     */
    public function markAsRead(string $id)
    {
        $notifikasi = Notifikasi::where('id', $id)
            ->where('notifiable_type', 'App\Models\User')
            ->where('notifiable_id', auth()->id())
            ->first();
            
        if (!$notifikasi) {
            return response()->json(['message' => 'Notifikasi tidak ditemukan.'], 404);
        }
        
        $notifikasi->markAsRead();
        
        return response()->json([
            'success' => true,
            'message' => 'Notifikasi ditandai sebagai sudah dibaca.',
            'notifikasi' => $notifikasi
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $notifikasi = Notifikasi::where('id', $id)
            ->where('notifiable_type', 'App\Models\User')
            ->where('notifiable_id', auth()->id())
            ->first();
            
        if (!$notifikasi) {
            return response()->json(['message' => 'Notifikasi tidak ditemukan.'], 404);
        }

        $notifikasi->delete();
        return response()->json(null, 204);
    }

    /**
     * Hapus semua notifikasi user
     */
    public function clearAll(Request $request)
    {
        $userId = $request->user()->id;
        $deleted = \App\Models\Notifikasi::where('notifiable_type', 'App\\Models\\User')
            ->where('notifiable_id', $userId)
            ->delete();
        return response()->json([
            'message' => 'Semua notifikasi berhasil dihapus.',
            'deleted' => $deleted
        ]);
    }
}
