<?php

namespace App\Filament\Resources\Orders\Tables;

use App\Models\Order;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class OrdersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('order_number')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('table.number')
                    ->label('Table')
                    ->sortable(),
                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        Order::STATUS_PENDING => 'warning',
                        Order::STATUS_CONFIRMED => 'info',
                        Order::STATUS_PREPARING => 'primary',
                        Order::STATUS_SERVED => 'success',
                        Order::STATUS_COMPLETED => 'gray',
                        Order::STATUS_CANCELLED => 'danger',
                        default => 'gray',
                    }),
                TextColumn::make('items_count')
                    ->counts('items')
                    ->label('Items'),
                TextColumn::make('total')
                    ->money('USD')
                    ->sortable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        Order::STATUS_PENDING => 'Pending',
                        Order::STATUS_CONFIRMED => 'Confirmed',
                        Order::STATUS_PREPARING => 'Preparing',
                        Order::STATUS_SERVED => 'Served',
                        Order::STATUS_COMPLETED => 'Completed',
                        Order::STATUS_CANCELLED => 'Cancelled',
                    ]),
                SelectFilter::make('table_id')
                    ->relationship('table', 'number')
                    ->label('Table'),
            ])
            ->recordActions([
                ViewAction::make(),
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
