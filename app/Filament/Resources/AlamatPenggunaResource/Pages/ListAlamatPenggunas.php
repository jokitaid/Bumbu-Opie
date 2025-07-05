<?php

namespace App\Filament\Resources\AlamatPenggunaResource\Pages;

use App\Filament\Resources\AlamatPenggunaResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListAlamatPenggunas extends ListRecords
{
    protected static string $resource = AlamatPenggunaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
