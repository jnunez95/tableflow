<?php

namespace App\Filament\Resources\Tables\Schemas;

use App\Models\Table as DiningTable;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class TableForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('number')
                    ->label('Table Number')
                    ->required()
                    ->numeric()
                    ->minValue(1)
                    ->unique(ignoreRecord: true),
                TextInput::make('capacity')
                    ->label('Capacity')
                    ->numeric()
                    ->minValue(1),
                Select::make('status')
                    ->options([
                        DiningTable::STATUS_AVAILABLE => 'Available',
                        DiningTable::STATUS_OCCUPIED => 'Occupied',
                        DiningTable::STATUS_RESERVED => 'Reserved',
                    ])
                    ->default(DiningTable::STATUS_AVAILABLE)
                    ->required(),
                TextInput::make('uuid')
                    ->label('UUID')
                    ->disabled()
                    ->dehydrated(false)
                    ->visibleOn('edit'),
                TextInput::make('qr_code')
                    ->label('QR Code')
                    ->disabled()
                    ->dehydrated(false)
                    ->visibleOn('edit'),
            ]);
    }
}
