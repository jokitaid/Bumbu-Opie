<?php

namespace App\Filament\Resources;

use App\Filament\Resources\LaporanPenjualanResource\Pages;
use App\Filament\Resources\LaporanPenjualanResource\RelationManagers;
use App\Models\LaporanPenjualan;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\DateFilter;
use Filament\Tables\Actions\ExportAction;
use Filament\Tables\Actions\ExportBulkAction;
use App\Models\Produk;

class LaporanPenjualanResource extends Resource
{
    protected static ?string $model = LaporanPenjualan::class;

    protected static ?string $navigationIcon = 'heroicon-o-chart-bar';
    protected static ?string $navigationLabel = 'Laporan Penjualan';
    protected static ?string $pluralLabel = 'Laporan Penjualan';
    protected static ?string $modelLabel = 'Laporan Penjualan';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                //
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('tanggal')->label('Tanggal'),
                Tables\Columns\TextColumn::make('kode_pesanan')->label('Kode Pesanan')->getStateUsing(fn($record) => $record->kode_pesanan ?? $record->pesanan->kode_pesanan ?? '-'),
                Tables\Columns\TextColumn::make('nama_pelanggan')->label('Pelanggan')->getStateUsing(fn($record) => $record->nama_pelanggan ?? optional($record->user)->name ?? '-'),
                Tables\Columns\TextColumn::make('produk.nama')->label('Produk'),
                Tables\Columns\TextColumn::make('jumlah_terjual')->label('Jumlah Terjual'),
                Tables\Columns\TextColumn::make('total_harga')->money('IDR')->label('Total Harga'),
                Tables\Columns\TextColumn::make('diskon')->label('Diskon')->formatStateUsing(fn($state) => $state ? $state.'%' : '-'),
                Tables\Columns\TextColumn::make('metode_pembayaran')->label('Metode Pembayaran')->getStateUsing(fn($record) => $record->metode_pembayaran ?? optional($record->pesanan)->metode_pembayaran ?? '-'),
                Tables\Columns\TextColumn::make('status')->label('Status'),
                Tables\Columns\TextColumn::make('created_at')->label('Dibuat')->dateTime('d M Y H:i')->sortable(),
                Tables\Columns\TextColumn::make('updated_at')->label('Diperbarui')->dateTime('d M Y H:i')->sortable(),
            ])
            ->filters([
                // Filter dihapus sesuai permintaan user
            ])
            ->actions([
                // Tidak ada EditAction/Ubah
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
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
            'index' => Pages\ListLaporanPenjualans::route('/'),
            'create' => Pages\CreateLaporanPenjualan::route('/create'),
            'edit' => Pages\EditLaporanPenjualan::route('/{record}/edit'),
        ];
    }
}
