<?php

namespace App\Filament\Widgets;

use App\Models\Pesanan;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Filament\Tables\Actions\Action;
use Filament\Forms\Components\Select;

class AksiStatusPesanan extends BaseWidget
{
    protected static ?string $heading = 'Aksi Cepat Ubah Status Pesanan';
    protected static ?int $sort = 4;
    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Pesanan::query()
                    ->whereNotIn('status', ['completed', 'cancelled'])
                    ->latest()
                    ->limit(10)
            )
            ->columns([
                Tables\Columns\TextColumn::make('kode_pesanan')->label('Kode'),
                Tables\Columns\TextColumn::make('user.name')->label('Pelanggan'),
                Tables\Columns\TextColumn::make('status')->badge(),
            ])
            ->actions([
                Action::make('ubahStatus')
                    ->label('Ubah Status')
                    ->form([
                        Select::make('status')
                            ->label('Status Baru')
                            ->options([
                                'processing' => 'Dikemas',
                                'dikirim' => 'Dikirim',
                                'completed' => 'Selesai',
                                'cancelled' => 'Dibatalkan',
                            ])
                            ->required(),
                    ])
                    ->action(function (Pesanan $record, array $data) {
                        $record->status = $data['status'];
                        $record->save();
                    })
                    ->color('primary')
            ]);
    }
} 