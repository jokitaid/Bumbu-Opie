<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AlamatPenggunaResource\Pages;
use App\Filament\Resources\AlamatPenggunaResource\RelationManagers;
use App\Models\AlamatPengguna;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Support\Facades\Http;

class AlamatPenggunaResource extends Resource
{
    protected static ?string $model = AlamatPengguna::class;

    protected static ?string $navigationIcon = 'heroicon-o-map-pin';
    protected static ?string $navigationLabel = 'Alamat Pengguna';
    protected static ?string $navigationGroup = 'Pengaturan Pengguna';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('user_id')
                    ->relationship('user', 'name')
                    ->required()
                    ->searchable()
                    ->preload(),
                TextInput::make('label')
                    ->nullable()
                    ->maxLength(255),
                Textarea::make('full_address')
                    ->required()
                    ->columnSpanFull(),
                Select::make('provinsi_nama')
                    ->label('Provinsi')
                    ->searchable()
                    ->options(function () {
                        $response = Http::get('https://api.goapi.io/regional/provinsi', [
                            'api_key' => 'f9d18375-bb61-50b2-bdf4-6209c10a',
                        ]);
                        $data = $response->json('data');
                        return collect($data)
                            ->filter(fn($item) => !empty($item['name']))
                            ->pluck('name', 'name')
                            ->toArray();
                    })
                    ->reactive()
                    ->required(),
                Select::make('kota_nama')
                    ->label('Kota/Kabupaten')
                    ->searchable()
                    ->options(function (callable $get) {
                        $provinsi = $get('provinsi_nama');
                        if (!$provinsi) return [];
                        $response = Http::get('https://api.goapi.io/regional/kota', [
                            'provinsi' => $provinsi,
                            'api_key' => 'f9d18375-bb61-50b2-bdf4-6209c10a',
                        ]);
                        $data = $response->json('data');
                        return collect($data)
                            ->filter(fn($item) => !empty($item['name']))
                            ->pluck('name', 'name')
                            ->toArray();
                    })
                    ->reactive()
                    ->required(),
                Select::make('kecamatan_nama')
                    ->label('Kecamatan')
                    ->searchable()
                    ->options(function (callable $get) {
                        $kota = $get('kota_nama');
                        if (!$kota) return [];
                        $response = Http::get('https://api.goapi.io/regional/kecamatan', [
                            'kota' => $kota,
                            'api_key' => 'f9d18375-bb61-50b2-bdf4-6209c10a',
                        ]);
                        $data = $response->json('data');
                        return collect($data)
                            ->filter(fn($item) => !empty($item['name']))
                            ->pluck('name', 'name')
                            ->toArray();
                    })
                    ->reactive()
                    ->required(),
                Select::make('kelurahan_nama')
                    ->label('Kelurahan/Desa')
                    ->searchable()
                    ->options(function (callable $get) {
                        $kecamatan = $get('kecamatan_nama');
                        if (!$kecamatan) return [];
                        $response = Http::get('https://api.goapi.io/regional/kelurahan', [
                            'kecamatan' => $kecamatan,
                            'api_key' => 'f9d18375-bb61-50b2-bdf4-6209c10a',
                        ]);
                        $data = $response->json('data');
                        return collect($data)
                            ->filter(fn($item) => !empty($item['name']))
                            ->pluck('name', 'name')
                            ->toArray();
                    })
                    ->reactive()
                    ->required(),
                TextInput::make('latitude')
                    ->nullable()
                    ->maxLength(255),
                TextInput::make('longitude')
                    ->nullable()
                    ->maxLength(255),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('user.name')
                    ->label('Pengguna')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('label')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('full_address')
                    ->searchable()
                    ->sortable()
                    ->wrap(),
                TextColumn::make('latitude')
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('longitude')
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('provinsi_nama')
                    ->label('Provinsi')
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('kota_nama')
                    ->label('Kota')
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('kecamatan_nama')
                    ->label('Kecamatan')
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('kelurahan_nama')
                    ->label('Kelurahan')
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
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
                Tables\Filters\SelectFilter::make('user')
                    ->relationship('user', 'name'),
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
            'index' => Pages\ListAlamatPenggunas::route('/'),
        ];
    }
}
