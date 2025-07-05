<?php

namespace App\Filament\Resources;

use App\Filament\Resources\KurirResource\Pages;
use App\Filament\Resources\KurirResource\RelationManagers;
use App\Models\Kurir;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BooleanColumn;

class KurirResource extends Resource
{
    protected static ?string $model = Kurir::class;

    protected static ?string $navigationIcon = 'heroicon-o-truck';
    protected static ?string $navigationLabel = 'Kurir & Ongkir';
    protected static ?string $navigationGroup = 'Pengiriman';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('nama')
                    ->label('Nama Kurir')
                    ->required()
                    ->maxLength(100),
                TextInput::make('estimasi')
                    ->label('Estimasi Pengiriman')
                    ->required()
                    ->maxLength(50)
                    ->helperText('Contoh: 1-2 Jam'),
                TextInput::make('harga')
                    ->label('Harga Ongkir (Rp)')
                    ->numeric()
                    ->required()
                    ->minValue(0),
                Toggle::make('aktif')
                    ->label('Aktif')
                    ->default(true),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('nama')->label('Nama Kurir')->searchable()->sortable(),
                TextColumn::make('estimasi')->label('Estimasi')->sortable(),
                TextColumn::make('harga')->label('Harga Ongkir')->money('IDR', true)->sortable(),
                BooleanColumn::make('aktif')->label('Aktif')->sortable(),
                TextColumn::make('created_at')->label('Dibuat')->dateTime('d/m/Y H:i')->sortable(),
            ])
            ->filters([
                // Bisa ditambah filter aktif/nonaktif jika perlu
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
            'index' => Pages\ListKurirs::route('/'),
        ];
    }
}
