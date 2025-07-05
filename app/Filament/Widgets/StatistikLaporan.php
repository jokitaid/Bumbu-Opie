<?php

namespace App\Filament\Widgets;

use App\Models\LaporanPenjualan;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatistikLaporan extends BaseWidget
{
    // Pastikan widget ini tidak muncul di dashboard
    protected static bool $isLazy = true;
    
    // Widget ini hanya untuk resource laporan
    public static function canView(): bool
    {
        return request()->route()->getName() === 'filament.admin.resources.laporan-penjualans.index';
    }

    protected $listeners = ['refreshWidget' => '$refresh'];

    protected function getStats(): array
    {
        $totalOmzet = LaporanPenjualan::sum('total_harga');
        $totalItem = LaporanPenjualan::sum('jumlah_terjual');
        $totalTransaksi = LaporanPenjualan::count();
        $omzetHariIni = LaporanPenjualan::whereDate('tanggal', today())->sum('total_harga');

        return [
            Stat::make('Total Omzet', 'Rp ' . number_format($totalOmzet, 0, ',', '.'))
                ->description('Seluruh waktu')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('success'),

            Stat::make('Total Item Terjual', number_format($totalItem, 0, ',', '.'))
                ->description('Seluruh waktu')
                ->descriptionIcon('heroicon-m-shopping-bag')
                ->color('info'),

            Stat::make('Total Transaksi', number_format($totalTransaksi, 0, ',', '.'))
                ->description('Seluruh waktu')
                ->descriptionIcon('heroicon-m-receipt-refund')
                ->color('warning'),

            Stat::make('Omzet Hari Ini', 'Rp ' . number_format($omzetHariIni, 0, ',', '.'))
                ->description('Hari ini')
                ->descriptionIcon('heroicon-m-calendar-days')
                ->color('primary'),
        ];
    }
} 