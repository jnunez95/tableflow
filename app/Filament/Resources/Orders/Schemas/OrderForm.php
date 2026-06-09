<?php

namespace App\Filament\Resources\Orders\Schemas;

use App\Models\Order;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class OrderForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('order_number')
                    ->disabled()
                    ->dehydrated(false),
                Select::make('table_id')
                    ->relationship('table', 'number')
                    ->getOptionLabelFromRecordUsing(fn ($record) => "Table {$record->number}")
                    ->disabled()
                    ->dehydrated(false),
                Select::make('status')
                    ->options([
                        Order::STATUS_PENDING => 'Pending',
                        Order::STATUS_CONFIRMED => 'Confirmed',
                        Order::STATUS_PREPARING => 'Preparing',
                        Order::STATUS_SERVED => 'Served',
                        Order::STATUS_COMPLETED => 'Completed',
                        Order::STATUS_CANCELLED => 'Cancelled',
                    ])
                    ->required(),
                TextInput::make('subtotal')
                    ->prefix('$')
                    ->disabled()
                    ->dehydrated(false),
                TextInput::make('tax')
                    ->prefix('$')
                    ->disabled()
                    ->dehydrated(false),
                TextInput::make('total')
                    ->prefix('$')
                    ->disabled()
                    ->dehydrated(false),
                Textarea::make('notes')
                    ->rows(3)
                    ->columnSpanFull(),
                Repeater::make('items')
                    ->relationship()
                    ->schema([
                        Select::make('product_id')
                            ->relationship('product', 'name')
                            ->label('Product')
                            ->disabled()
                            ->dehydrated(false),
                        TextInput::make('quantity')
                            ->disabled()
                            ->dehydrated(false),
                        TextInput::make('unit_price')
                            ->label('Unit Price')
                            ->prefix('$')
                            ->disabled()
                            ->dehydrated(false),
                        TextInput::make('subtotal')
                            ->prefix('$')
                            ->disabled()
                            ->dehydrated(false),
                    ])
                    ->columns(4)
                    ->disabled()
                    ->dehydrated(false)
                    ->columnSpanFull(),
            ]);
    }
}
