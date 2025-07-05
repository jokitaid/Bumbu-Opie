<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('alamat_penggunas', function (Blueprint $table) {
            $table->string('provinsi_id', 20)->nullable()->after('longitude');
            $table->string('provinsi_nama', 100)->nullable()->after('provinsi_id');
            $table->string('kota_id', 20)->nullable()->after('provinsi_nama');
            $table->string('kota_nama', 100)->nullable()->after('kota_id');
            $table->string('kecamatan_id', 20)->nullable()->after('kota_nama');
            $table->string('kecamatan_nama', 100)->nullable()->after('kecamatan_id');
            $table->string('kelurahan_id', 20)->nullable()->after('kecamatan_nama');
            $table->string('kelurahan_nama', 100)->nullable()->after('kelurahan_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('alamat_penggunas', function (Blueprint $table) {
            $table->dropColumn([
                'provinsi_id',
                'provinsi_nama',
                'kota_id',
                'kota_nama',
                'kecamatan_id',
                'kecamatan_nama',
                'kelurahan_id',
                'kelurahan_nama',
            ]);
        });
    }
}; 