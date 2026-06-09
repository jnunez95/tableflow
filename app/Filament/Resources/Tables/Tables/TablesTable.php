<?php

namespace App\Filament\Resources\Tables\Tables;

use App\Models\Table as DiningTable;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class TablesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('number')
                    ->label('Table #')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('uuid')
                    ->label('UUID')
                    ->copyable()
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('capacity')
                    ->sortable(),
                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        DiningTable::STATUS_AVAILABLE => 'success',
                        DiningTable::STATUS_OCCUPIED => 'warning',
                        DiningTable::STATUS_RESERVED => 'info',
                        default => 'gray',
                    }),
                TextColumn::make('qr_code')
                    ->label('QR Code')
                    ->copyable()
                    ->limit(20)
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('number')
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        DiningTable::STATUS_AVAILABLE => 'Available',
                        DiningTable::STATUS_OCCUPIED => 'Occupied',
                        DiningTable::STATUS_RESERVED => 'Reserved',
                    ]),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
