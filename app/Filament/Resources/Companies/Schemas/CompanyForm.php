<?php

namespace App\Filament\Resources\Companies\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class CompanyForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Company Name')
                    ->required()
                    ->maxLength(255),
                TextInput::make('default_tax_rate')
                    ->label('Default Tax Rate')
                    ->required()
                    ->numeric()
                    ->suffix('%')
                    ->minValue(0)
                    ->maxValue(100)
                    ->default(10)
                    ->step(0.01),
                TextInput::make('default_service_charge_rate')
                    ->label('Default Service Charge Rate')
                    ->required()
                    ->numeric()
                    ->suffix('%')
                    ->minValue(0)
                    ->maxValue(100)
                    ->default(10)
                    ->step(0.01),
                Toggle::make('uses_qr_code')
                    ->label('Use QR Code for table access')
                    ->helperText('When disabled, customers select a table from a list when starting an order.')
                    ->default(true)
                    ->inline(false),
            ]);
    }
}
