<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ItemPesananResource\Pages;
use App\Filament\Resources\ItemPesananResource\RelationManagers;
use App\Models\ItemPesanan;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Columns\TextColumn;

class ItemPesananResource extends Resource
{
    protected static ?string $model = ItemPesanan::class;

    protected static ?string $navigationIcon = 'heroicon-o-list-bullet';
    protected static ?string $navigationLabel = 'Item Pesanan';
    protected static ?string $navigationGroup = 'Manajemen Pesanan';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('pesanan_id')
                    ->relationship('pesanan', 'kode_pesanan')
                    ->required()
                    ->searchable()
                    ->preload(),
                Select::make('produk_id')
                    ->relationship('produk', 'nama')
                    ->required()
                    ->searchable()
                    ->preload(),
                TextInput::make('quantity')
                    ->required()
                    ->numeric()
                    ->minValue(1),
                TextInput::make('harga')
                    ->required()
                    ->numeric()
                    ->prefix('Rp')
                    ->minValue(0)
                    ->disabled()
                    ->dehydrated(true),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('pesanan.kode_pesanan')
                    ->label('Kode Pesanan')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('produk.nama')
                    ->label('Produk')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('quantity')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('harga')
                    ->money('IDR')
                    ->sortable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('pesanan')
                    ->relationship('pesanan', 'kode_pesanan'),
                Tables\Filters\SelectFilter::make('produk')
                    ->relationship('produk', 'nama'),
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

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListItemPesanans::route('/'),
            'create' => Pages\CreateItemPesanan::route('/create'),
            'edit' => Pages\EditItemPesanan::route('/{record}/edit'),
        ];
    }
}
