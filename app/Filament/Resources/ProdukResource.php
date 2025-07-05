<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProdukResource\Pages;
use App\Filament\Resources\ProdukResource\RelationManagers;
use App\Models\Produk;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ImageColumn;
use Illuminate\Support\Str;

class ProdukResource extends Resource
{
    protected static ?string $model = Produk::class;

    protected static ?string $navigationIcon = 'heroicon-o-archive-box';
    protected static ?string $navigationGroup = 'Manajemen Produk';
    protected static ?string $navigationLabel = 'Produk';
    protected static ?string $modelLabel = 'Produk';
    protected static ?string $pluralModelLabel = 'Produk';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('kode_barang')
                    ->label('Kode Bumbu')
                    ->required()
                    ->maxLength(255)
                    ->disabledOn('edit')
                    ->readOnly()
                    ->dehydrated(fn (string $operation): bool => $operation === 'create')
                    ->default(fn () => 'BM-' . Str::upper(Str::random(6)))
                    ->unique(ignoreRecord: true, table: 'produks', column: 'kode_barang'),
                TextInput::make('nama')
                    ->label('Nama Bumbu')
                    ->required()
                    ->maxLength(255)
                    ->live()
                    ->afterStateUpdated(fn ($state, Forms\Set $set) => $set('slug', Str::slug($state))),
                Forms\Components\TextInput::make('slug')
                    ->label('Slug')
                    ->required()
                    ->maxLength(255)
                    ->readOnly(),
                Textarea::make('deskripsi')
                    ->label('Deskripsi')
                    ->nullable()
                    ->columnSpanFull(),
                TextInput::make('harga')
                    ->label('Harga/Gram')
                    ->required()
                    ->prefix('Rp')
                    ->numeric()
                    ->minValue(0),
                TextInput::make('stok')
                    ->label('Stok')
                    ->required()
                    ->numeric()
                    ->minValue(0),
                Select::make('kategori_id')
                    ->label('Kategori')
                    ->relationship('kategori', 'nama')
                    ->required()
                    ->searchable()
                    ->preload(),
                FileUpload::make('gambar')
                    ->label('Gambar')
                    ->image()
                    ->nullable()
                    ->directory('produks')
                    ->visibility('public'),
                TextInput::make('diskon')
                    ->label('Diskon')
                    ->nullable()
                    ->default(0)
                    ->suffix('%')
                    ->numeric()
                    ->minValue(0)
                    ->maxValue(100),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('kode_barang')
                    ->label('Kode Bumbu')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('nama')
                    ->label('Nama Bumbu')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('slug')
                    ->label('Slug')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->sortable(),
                TextColumn::make('kategori.nama')
                    ->label('Kategori')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('harga')
                    ->label('Harga /Gram')
                    ->money('IDR')
                    ->sortable(),
                TextColumn::make('stok')
                    ->label('Stok')
                    ->numeric()
                    ->sortable(),
                ImageColumn::make('gambar')
                    ->label('Gambar')
                    ->square()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('diskon')
                    ->label('Diskon')
                    ->suffix('%')
                    ->numeric()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->label('Dibuat Pada')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->label('Diperbarui Pada')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('kategori')
                    ->relationship('kategori', 'nama')
                    ->label('Kategori'),
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
            'index' => Pages\ListProduks::route('/'),
        ];
    }
}
