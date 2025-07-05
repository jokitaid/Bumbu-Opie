<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use App\Models\Produk;
use App\Models\Pesanan;
use App\Models\User;
use App\Models\Kategori;

class StatsOverview extends BaseWidget
{
    protected function getStats(): array
    {
        $totalProduk = Produk::count();
        $totalPesanan = Pesanan::count();
        $totalPengguna = User::where('role', 'pengguna')->count();
        $totalKategori = Kategori::count();

        return [
            Stat::make('Total Produk', $totalProduk)
                ->description('Jumlah keseluruhan produk')
                ->descriptionIcon('heroicon-m-archive-box')
                ->color('primary'),
            Stat::make('Total Pesanan', $totalPesanan)
                ->description('Jumlah keseluruhan pesanan')
                ->descriptionIcon('heroicon-m-shopping-bag')
                ->color('success'),
            Stat::make('Total Pengguna', $totalPengguna)
                ->description('Jumlah pengguna aktif')
                ->descriptionIcon('heroicon-m-user-group')
                ->color('info'),
            Stat::make('Total Kategori', $totalKategori)
                ->description('Jumlah kategori produk')
                ->descriptionIcon('heroicon-m-tag')
                ->color('warning'),
        ];
    }
}
