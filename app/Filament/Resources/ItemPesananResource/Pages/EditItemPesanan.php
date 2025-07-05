<?php

namespace App\Filament\Resources\ItemPesananResource\Pages;

use App\Filament\Resources\ItemPesananResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditItemPesanan extends EditRecord
{
    protected static string $resource = ItemPesananResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
