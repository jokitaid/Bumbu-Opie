<?php

namespace App\Filament\Resources;

use App\Filament\Resources\NotifikasiResource\Pages;
use App\Filament\Resources\NotifikasiResource\RelationManagers;
use App\Models\Notifikasi;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\DateTimePicker;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\Select;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\Filter;

class NotifikasiResource extends Resource
{
    protected static ?string $model = Notifikasi::class;
    protected static ?string $navigationLabel = 'Notifikasi';
    protected static ?string $navigationIcon = 'heroicon-o-bell-alert';
    protected static ?string $navigationGroup = 'Notifikasi';
    protected static ?string $modelLabel = 'Notifikasi';
    protected static ?string $pluralModelLabel = 'Notifikasi';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                // Form hanya untuk view, tidak untuk edit
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label('ID')
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('notifiable.name')
                    ->label('Pengguna')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('tipe')
                    ->label('Tipe')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pesanan_status' => 'success',
                        default => 'gray',
                    }),
                TextColumn::make('data.title')
                    ->label('Judul')
                    ->searchable()
                    ->sortable()
                    ->limit(50),
                TextColumn::make('data.kode_pesanan')
                    ->label('Kode Pesanan')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('data.total_harga_formatted')
                    ->label('Total Harga')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('data.status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'warning',
                        'processing' => 'info',
                        'dikirim' => 'primary',
                        'completed' => 'success',
                        'cancelled' => 'danger',
                        default => 'gray',
                    })
                    ->toggleable(),
                TextColumn::make('dibaca_pada')
                    ->label('Dibaca Pada')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->label('Dibuat Pada')
                    ->dateTime()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('tipe')
                    ->label('Tipe Notifikasi')
                    ->options([
                        'pesanan_status' => 'Status Pesanan',
                    ]),
                SelectFilter::make('status')
                    ->label('Status Pesanan')
                    ->options([
                        'pending' => 'Menunggu Pembayaran',
                        'processing' => 'Dikemas',
                        'dikirim' => 'Dikirim',
                        'completed' => 'Selesai',
                        'cancelled' => 'Dibatalkan',
                    ]),
                Filter::make('unread')
                    ->label('Belum Dibaca')
                    ->query(fn (Builder $query): Builder => $query->whereNull('dibaca_pada')),
                Filter::make('read')
                    ->label('Sudah Dibaca')
                    ->query(fn (Builder $query): Builder => $query->whereNotNull('dibaca_pada')),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
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
            'index' => Pages\ListNotifikasis::route('/'),
            'view' => Pages\ViewNotifikasi::route('/{record}'),
        ];
    }
}
