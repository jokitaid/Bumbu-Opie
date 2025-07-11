<?php

namespace App\Filament\Resources\KeranjangResource\Pages;

use App\Filament\Resources\KeranjangResource;
use Filament\Resources\Pages\ViewRecord;

class ViewKeranjang extends ViewRecord
{
    protected static string $resource = KeranjangResource::class;

    protected function getViewData(): array
    {
        $record = $this->record;
        // Ambil campuran bumbu dengan nama produk
        $komponenBumbu = collect($record->komponen_bumbu ?? [])->map(function ($item) {
            $produk = \App\Models\Produk::find($item['produk_id'] ?? null);
            return [
                'nama' => $produk?->nama ?? '-',
                'jumlah' => $item['jumlah'] ?? '-',
                'satuan' => $produk?->satuan ?? '-',
                'detail_satuan' => $produk?->detail_satuan ?? '-',
            ];
        });
        return [
            'record' => $record,
            'komponenBumbu' => $komponenBumbu,
        ];
    }

    protected static string $view = 'keranjang.detail';
} 