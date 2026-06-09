<?php

namespace App\Filament\Resources\Products\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class ProductsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('image')
                    ->square(),
                TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('category.name')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('price')
                    ->money('USD')
                    ->sortable(),
                IconColumn::make('is_available')
                    ->boolean()
                    ->label('Available'),
                TextColumn::make('sort_order')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('sort_order')
            ->filters([
                SelectFilter::make('category_id')
                    ->relationship('category', 'name')
                    ->label('Category'),
                TernaryFilter::make('is_available')
                    ->label('Available'),
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
