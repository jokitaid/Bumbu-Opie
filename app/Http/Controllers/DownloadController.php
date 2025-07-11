<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class DownloadController extends Controller
{
    public function downloadApk()
    {
        $filePath = public_path('downloads/BumbuOpie_1.0.0.apk');
        
        if (!file_exists($filePath)) {
            abort(404, 'File APK tidak ditemukan');
        }
        
        $fileName = 'BumbuOpie_1.0.0.apk';
        $fileSize = filesize($filePath);
        
        return response()->download($filePath, $fileName, [
            'Content-Type' => 'application/vnd.android.package-archive',
            'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
            'Content-Length' => $fileSize,
            'Cache-Control' => 'no-cache, must-revalidate',
            'Pragma' => 'no-cache',
            'Expires' => '0',
            'X-Content-Type-Options' => 'nosniff',
            'X-Frame-Options' => 'DENY',
            'X-XSS-Protection' => '1; mode=block'
        ]);
    }
} 