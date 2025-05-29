<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BookingResource\Pages;
use App\Models\Booking;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\Filter as TablesFilter;
use Illuminate\Database\Eloquent\Builder;
use Carbon\Carbon;
use Filament\Notifications\Notification;

class BookingResource extends Resource
{
    protected static ?string $model = Booking::class;
    protected static ?string $navigationIcon = 'heroicon-o-calendar-days';
    protected static ?string $recordTitleAttribute = 'id';

    public static function getNavigationLabel(): string
    {
        return 'Booking';
    }

    public static function getPluralModelLabel(): string
    {
        return 'Booking';
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function form(Form $form): Form
    {
        return $form->schema([]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('user.name')
                    ->label('Customer')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('mobil.nama')
                    ->label('Mobil')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('dealer.nama')
                    ->label('Dealer')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('tanggal')
                    ->date('d M Y')
                    ->sortable(),
                TextColumn::make('waktu')
                    ->time('H:i')
                    ->sortable(),
                TextColumn::make('status')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color(fn(string $state): string => match (strtolower($state)) {
                        'menunggu' => 'warning',
                        'selesai' => 'success',
                        default => 'gray',
                    }),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'menunggu' => 'Menunggu Konfirmasi',
                        'selesai' => 'Selesai',
                    ]),
                SelectFilter::make('mobil_id')
                    ->label('Mobil')
                    ->relationship('mobil', 'nama'),
                SelectFilter::make('dealer_id')
                    ->label('Dealer')
                    ->relationship('dealer', 'nama'),
                TablesFilter::make('tanggal')
                    ->form([
                        Forms\Components\DatePicker::make('tanggal_dari')->native(false)->displayFormat('d/m/Y'),
                        Forms\Components\DatePicker::make('tanggal_sampai')->native(false)->displayFormat('d/m/Y'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['tanggal_dari'],
                                fn(Builder $query, $date): Builder => $query->whereDate('tanggal', '>=', $date),
                            )
                            ->when(
                                $data['tanggal_sampai'],
                                fn(Builder $query, $date): Builder => $query->whereDate('tanggal', '<=', $date),
                            );
                    })
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Action::make('selesaikan')
                    ->label('Selesaikan')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->action(function (Booking $record) {
                        if (strtolower($record->status) === 'menunggu') {
                            $record->status = 'selesai';
                            $record->save();
                            Notification::make()
                                ->title('Booking diselesaikan')
                                ->body("Booking untuk {$record->user->name} pada mobil {$record->mobil->nama} telah diselesaikan.")
                                ->success()
                                ->send();
                        } else {
                            Notification::make()
                                ->title('Aksi Tidak Diizinkan')
                                ->body('Hanya booking dengan status "menunggu" yang bisa diselesaikan.')
                                ->danger()
                                ->send();
                        }
                    })
                    ->visible(fn(Booking $record): bool => strtolower($record->status) === 'menunggu'),
            ])
            ->bulkActions([])
            ->defaultSort('tanggal', 'desc');
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListBookings::route('/'),
        ];
    }
}
