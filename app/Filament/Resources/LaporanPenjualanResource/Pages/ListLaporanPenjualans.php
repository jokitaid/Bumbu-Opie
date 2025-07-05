<?php

namespace App\Filament\Resources\LaporanPenjualanResource\Pages;

use App\Filament\Resources\LaporanPenjualanResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use App\Filament\Widgets\GrafikFleksibel;
use App\Filament\Widgets\StatistikLaporan;
use App\Models\Pesanan;
use App\Models\LaporanPenjualan;
use Filament\Notifications\Notification;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Illuminate\Support\Carbon;

class ListLaporanPenjualans extends ListRecords
{
    protected static string $resource = LaporanPenjualanResource::class;

    public function getHeaderWidgets(): array
    {
        return [
            StatistikLaporan::class,
        ];
    }
    protected function getHeaderActions(): array
    {
        $adaLaporan = \App\Models\LaporanPenjualan::count() > 0;
        return [
            Actions\Action::make('generateLaporan')
                ->label('Buat Laporan Penjualan')
                ->color('primary')
                ->icon('heroicon-o-document-plus')
                ->form([
                    Select::make('periode')
                        ->label('Periode')
                        ->options([
                            'hari' => 'Per Hari',
                            'minggu' => 'Per Minggu',
                            'bulan' => 'Per Bulan',
                        ])
                        ->required()
                        ->live(),
                    DatePicker::make('tanggal')
                        ->label('Tanggal')
                        ->visible(fn ($get) => $get('periode') === 'hari')
                        ->required(fn ($get) => $get('periode') === 'hari'),
                    DatePicker::make('minggu')
                        ->label('Pilih Minggu')
                        ->visible(fn ($get) => $get('periode') === 'minggu')
                        ->required(fn ($get) => $get('periode') === 'minggu'),
                    DatePicker::make('bulan')
                        ->label('Pilih Bulan')
                        ->displayFormat('Y-m')
                        ->visible(fn ($get) => $get('periode') === 'bulan')
                        ->required(fn ($get) => $get('periode') === 'bulan'),
                ])
                ->action(function (array $data) {
                    $periode = $data['periode'];
                    $count = 0;
                    if ($periode === 'hari') {
                        $tanggal = $data['tanggal'];
                        $pesanans = Pesanan::whereIn('status', ['processing', 'completed', 'cancelled'])
                            ->whereDate('updated_at', $tanggal)
                            ->get();
                    } elseif ($periode === 'minggu') {
                        $minggu = Carbon::parse($data['minggu']);
                        $start = $minggu->startOfWeek();
                        $end = $minggu->endOfWeek();
                        $pesanans = Pesanan::whereIn('status', ['processing', 'completed', 'cancelled'])
                            ->whereBetween('updated_at', [$start, $end])
                            ->get();
                    } elseif ($periode === 'bulan') {
                        $bulan = Carbon::parse($data['bulan']);
                        $start = $bulan->startOfMonth();
                        $end = $bulan->endOfMonth();
                        $pesanans = Pesanan::whereIn('status', ['processing', 'completed', 'cancelled'])
                            ->whereBetween('updated_at', [$start, $end])
                            ->get();
                    } else {
                        $pesanans = collect();
                    }
                    foreach ($pesanans as $pesanan) {
                        foreach ($pesanan->itemPesanan as $item) {
                            $exists = LaporanPenjualan::where('tanggal', $pesanan->updated_at->toDateString())
                                ->where('produk_id', $item->produk_id)
                                ->where('status', $pesanan->status)
                                ->exists();
                            if (!$exists) {
                                LaporanPenjualan::create([
                                    'tanggal' => $pesanan->updated_at->toDateString(),
                                    'produk_id' => $item->produk_id,
                                    'nama_produk' => $item->produk->nama ?? '-',
                                    'jumlah_terjual' => $item->quantity,
                                    'total_harga' => $item->harga * $item->quantity,
                                    'status' => $pesanan->status,
                                    'kode_pesanan' => $pesanan->kode_pesanan,
                                    'user_id' => $pesanan->user_id,
                                    'nama_pelanggan' => optional($pesanan->user)->name,
                                    'metode_pembayaran' => $pesanan->metode_pembayaran ?: 'cod',
                                    'diskon' => $item->produk->diskon ?? null,
                                ]);
                                $count++;
                            }
                        }
                    }
                    Notification::make()
                        ->title('Generate Laporan Berhasil')
                        ->body("Berhasil generate $count data laporan penjualan dari pesanan.")
                        ->success()
                        ->send();
                    $this->dispatch('refreshWidget');
                }),
            Actions\Action::make('cetakLaporan')
                ->label('Cetak Laporan')
                ->color('success')
                ->icon('heroicon-o-printer')
                ->url(route('laporan.export.pdf'))
                ->openUrlInNewTab()
                ->visible($adaLaporan),
        ];
    }

    
}
