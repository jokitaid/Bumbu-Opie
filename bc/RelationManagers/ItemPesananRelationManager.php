<?php

namespace App\Filament\Resources\PesananResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Columns\TextColumn;

class ItemPesananRelationManager extends RelationManager
{
    protected static string $relationship = 'itemPesanan';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('produk_id')
                    ->relationship('produk', 'nama')
                    ->required()
                    ->searchable()
                    ->preload()
                    ->columnSpanFull(),
                TextInput::make('quantity')
                    ->required()
                    ->numeric()
                    ->minValue(1),
                TextInput::make('harga')
                    ->required()
                    ->numeric()
                    ->prefix('Rp')
                    ->minValue(0),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('produk.nama')
            ->columns([
                TextColumn::make('produk.nama')
                    ->label('Produk')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('quantity')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('produk.satuan')
                    ->label('Satuan')
                    ->sortable(),
                TextColumn::make('produk.detail_satuan')
                    ->label('Detail Satuan')
                    ->sortable(),
                TextColumn::make('komponen_bumbu')
                    ->label('Campuran Bumbu')
                    ->formatStateUsing(function ($state) {
                        if (is_string($state)) {
                            $state = json_decode($state, true);
                        }
                        if (!is_array($state) || empty($state)) {
                            return '-';
                        }
                        return collect($state)
                            ->map(function ($b) {
                                $produk = \App\Models\Produk::find($b['produk_id'] ?? null);
                                return ($produk?->nama ?? '-') . ' (' . ($b['jumlah'] ?? '-') . ' ' . ($produk?->satuan ?? '-') . ')';
                            })
                            ->implode(', ');
                    })
                    ->toggleable(isToggledHiddenByDefault: false),
                TextColumn::make('harga')
                    ->money('IDR')
                    ->sortable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
} 