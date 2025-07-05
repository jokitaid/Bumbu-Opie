<?php

namespace App\Http\Controllers;

use App\Models\LaporanPenjualan;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;

class LaporanController extends Controller
{
    public function exportPdf(Request $request)
    {
        $query = LaporanPenjualan::query();

        // Filter berdasarkan request
        if ($request->filled('tanggal')) {
            $query->whereDate('tanggal', $request->tanggal);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('produk_id')) {
            $query->where('produk_id', $request->produk_id);
        }

        $laporan = $query->with('produk')->get();

        $pdf = Pdf::loadView('laporan.penjualan-pdf', [
            'laporan' => $laporan,
            'total_omzet' => $laporan->sum('total_harga'),
            'total_item' => $laporan->sum('jumlah_terjual'),
        ]);

        $filename = 'laporan-penjualan-' . date('Ymd-His') . '.pdf';
        return $pdf->download($filename);
    }
} 