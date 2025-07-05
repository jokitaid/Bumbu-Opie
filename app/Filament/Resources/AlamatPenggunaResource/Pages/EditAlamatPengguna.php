<?php

namespace App\Filament\Resources\AlamatPenggunaResource\Pages;

use App\Filament\Resources\AlamatPenggunaResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditAlamatPengguna extends EditRecord
{
    protected static string $resource = AlamatPenggunaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
