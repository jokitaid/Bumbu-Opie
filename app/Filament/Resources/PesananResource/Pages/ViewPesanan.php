<?php

namespace App\Filament\Resources\PesananResource\Pages;

use App\Filament\Resources\PesananResource;
use Filament\Resources\Pages\ViewRecord;

class ViewPesanan extends ViewRecord
{
    protected static string $resource = PesananResource::class;


    public function getFooter(): ?\Illuminate\Contracts\View\View
    {
        $record = $this->record->load('itemPesanan.produk');
        return view('pesanan.detail-items', [
            'items' => $record->itemPesanan,
        ]);
    }

} 