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
                TextColumn::make('produk.satuan')
                    ->label('Satuan')
                    ->sortable(),
                TextColumn::make('produk.detail_satuan')
                    ->label('Detail Satuan')
                    ->sortable(),
                TextColumn::make('komponen_bumbu')
                    ->label('Campuran Bumbu')
                    ->formatStateUsing(function ($state) {
                        // Paksa decode jika masih string
                        if (is_string($state)) {
                            $decoded = json_decode($state, true);
                            if (json_last_error() === JSON_ERROR_NONE) {
                                $state = $decoded;
                            }
                        }
                        if (!is_array($state) || empty($state)) {
                            return '-';
                        }
                        // Tampilkan rapi sebagai bullet list
                        return '<ul style="margin:0;padding-left:1em">' . collect($state)
                            ->map(function ($b) {
                                $harga = isset($b['harga']) ? ', Rp ' . number_format($b['harga'], 0, ',', '.') : '';
                                return '<li>' . ($b['nama'] ?? '-') . ' (' . ($b['jumlah'] ?? '-') . ' ' . ($b['satuan'] ?? '-') . $harga . ')</li>';
                            })
                            ->implode('') . '</ul>';
                    })
                    ->html(),
                TextColumn::make('harga_total')
                    ->label('Harga Total')
                    ->formatStateUsing(function ($state, $record) {
                        $hargaUtama = floatval($record->harga) * intval($record->quantity);
                        $totalCampuran = 0;
                        $campuran = $record->komponen_bumbu;
                        if (is_string($campuran)) {
                            $campuran = json_decode($campuran, true);
                        }
                        if (is_array($campuran)) {
                            foreach ($campuran as $b) {
                                if (isset($b['subtotal'])) {
                                    $totalCampuran += floatval($b['subtotal']);
                                } elseif (isset($b['harga']) && isset($b['jumlah'])) {
                                    $totalCampuran += floatval($b['harga']) * floatval($b['jumlah']);
                                }
                            }
                        }
                        $total = $hargaUtama + $totalCampuran;
                        return 'Rp ' . number_format($total, 0, ',', '.');
                    }),
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
