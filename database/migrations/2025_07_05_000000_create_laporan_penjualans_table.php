<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('laporan_penjualans', function (Blueprint $table) {
            $table->id();
            $table->date('tanggal');
            $table->unsignedBigInteger('produk_id')->nullable();
            $table->string('kode_pesanan')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('nama_pelanggan')->nullable();
            $table->string('metode_pembayaran')->nullable();
            $table->decimal('diskon', 5, 2)->nullable();
            $table->string('nama_produk');
            $table->integer('jumlah_terjual');
            $table->bigInteger('total_harga');
            $table->string('status');
            $table->timestamps();

            $table->foreign('produk_id')->references('id')->on('produks')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('laporan_penjualans');
    }
}; 