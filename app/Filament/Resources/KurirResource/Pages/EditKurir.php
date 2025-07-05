<?php

namespace App\Filament\Resources\KurirResource\Pages;

use App\Filament\Resources\KurirResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditKurir extends EditRecord
{
    protected static string $resource = KurirResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
