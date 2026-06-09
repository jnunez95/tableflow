<?php

namespace App\Filament\Resources\Products\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class ProductForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('category_id')
                    ->relationship('category', 'name')
                    ->required()
                    ->searchable()
                    ->preload(),
                TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                TextInput::make('slug')
                    ->maxLength(255)
                    ->unique(ignoreRecord: true),
                RichEditor::make('description')
                    ->columnSpanFull(),
                TextInput::make('price')
                    ->required()
                    ->numeric()
                    ->prefix('$')
                    ->minValue(0)
                    ->step(0.01),
                FileUpload::make('image')
                    ->image()
                    ->directory('products')
                    ->visibility('public')
                    ->columnSpanFull(),
                TagsInput::make('dietary_tags')
                    ->placeholder('Add tag (e.g. vegan, gluten-free)')
                    ->columnSpanFull(),
                TextInput::make('sort_order')
                    ->numeric()
                    ->default(0)
                    ->minValue(0),
                Toggle::make('is_available')
                    ->default(true),
            ]);
    }
}
