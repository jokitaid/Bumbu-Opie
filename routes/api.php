<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\KategoriController;
use App\Http\Controllers\Api\ProdukController;
use App\Http\Controllers\Api\KeranjangController;
use App\Http\Controllers\Api\AlamatPenggunaController;
use App\Http\Controllers\Api\PesananController;
use App\Http\Controllers\Api\ItemPesananController;
use App\Http\Controllers\Api\NotifikasiController;
use App\Http\Controllers\Api\RegisterController;
use App\Http\Controllers\Api\GoogleAuthController;
use App\Http\Controllers\Api\WishlistController;
use App\Http\Controllers\Api\KurirController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Public API routes
Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [RegisterController::class, 'register']);
Route::post('/auth/google', [GoogleAuthController::class, 'googleSignIn']);

Route::get('/test', function () {
    return response()->json(['message' => 'API is working!']);
});

// Produk routes
Route::get('/produks', [ProdukController::class, 'index']);
Route::get('/produks/{produk}', [ProdukController::class, 'show']);
Route::get('/kategoris/{kategori}/produks', [ProdukController::class, 'byKategori']);

// Kategori routes
Route::get('/kategoris', [KategoriController::class, 'index']);
Route::get('/kategoris/{kategori}', [KategoriController::class, 'show']);

// Midtrans Callbacks and Redirects (public routes)
Route::post('/midtrans/notification', [PesananController::class, 'notificationHandler']);
Route::get('/midtrans/finish', [PesananController::class, 'midtransFinish']);
Route::get('/midtrans/error', [PesananController::class, 'midtransError']);
Route::get('/midtrans/pending', [PesananController::class, 'midtransPending']);

// Kurir API
Route::get('/kurir', [KurirController::class, 'index']);
Route::get('/kurir/{kurir}', [KurirController::class, 'show']);

// Protected API routes
Route::middleware('auth:sanctum')->group(function () {
    // Auth routes
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'getUser']);
    Route::put('/user', [AuthController::class, 'updateUser']);

    // Keranjang routes
    Route::get('/keranjang', [KeranjangController::class, 'index']);
    Route::post('/keranjang', [KeranjangController::class, 'store']);
    Route::put('/keranjang/{keranjang}', [KeranjangController::class, 'update']);
    Route::delete('/keranjang/{keranjang}', [KeranjangController::class, 'destroy']);

    // Alamat routes
    Route::get('/alamat', [AlamatPenggunaController::class, 'index']);
    Route::post('/alamat', [AlamatPenggunaController::class, 'store']);
    Route::put('/alamat/{alamat}', [AlamatPenggunaController::class, 'update']);
    Route::delete('/alamat/{alamat}', [AlamatPenggunaController::class, 'destroy']);

    // Pesanan routes
    Route::get('/pesanan', [PesananController::class, 'index']);
    Route::post('/pesanan', [PesananController::class, 'store']);
    Route::get('/pesanan/{pesanan}', [PesananController::class, 'show']);
    Route::get('/pesanan/{pesanan}/items', [ItemPesananController::class, 'index']);
    Route::delete('/pesanan/{pesanan}', [PesananController::class, 'destroy']);
    Route::put('/pesanan/{pesanan}', [PesananController::class, 'update']);
    Route::get('/pesanan/by-status', [PesananController::class, 'byStatus']);
    Route::patch('/pesanan/{pesanan}/batalkan', [PesananController::class, 'batalkan']);

    // Wishlist routes
    Route::get('/wishlist', [WishlistController::class, 'index']);
    Route::post('/wishlist', [WishlistController::class, 'store']);
    Route::delete('/wishlist/{produk}', [WishlistController::class, 'destroy']);

    // Notifikasi routes
    Route::get('/notifikasi', [NotifikasiController::class, 'index']);
    Route::get('/notifikasi/{notifikasi}', [NotifikasiController::class, 'show']);
    Route::post('/notifikasi/{notifikasi}/mark-as-read', [NotifikasiController::class, 'markAsRead']);
});
