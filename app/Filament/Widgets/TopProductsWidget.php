<?php

namespace App\Filament\Widgets;

use App\Filament\Widgets\Concerns\InteractsWithAnalyticsPeriod;
use App\Models\Order;
use App\Models\Product;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;

class TopProductsWidget extends TableWidget
{
    use InteractsWithAnalyticsPeriod;

    protected static bool $isDiscovered = false;

    protected static ?int $sort = 1;

    protected int|string|array $columnSpan = 'full';

    protected static ?string $heading = 'Top 10 best sellers';

    protected ?string $pollingInterval = '60s';

    public function table(Table $table): Table
    {
        $range = $this->getPeriodRange();

        return $table
            ->query(
                Product::query()
                    ->select([
                        'products.id',
                        'products.name',
                        'products.image',
                    ])
                    ->selectRaw('SUM(order_items.quantity) as total_quantity')
                    ->selectRaw('SUM(order_items.subtotal) as total_revenue')
                    ->join('order_items', 'order_items.product_id', '=', 'products.id')
                    ->join('orders', 'orders.id', '=', 'order_items.order_id')
                    ->where('orders.status', Order::STATUS_COMPLETED)
                    ->whereBetween('orders.created_at', [$range['start'], $range['end']])
                    ->groupBy('products.id', 'products.name', 'products.image')
                    ->orderByDesc('total_revenue')
                    ->limit(10)
            )
            ->columns([
                TextColumn::make('rank')
                    ->label('#')
                    ->rowIndex(),
                ImageColumn::make('image')
                    ->label('Image')
                    ->circular()
                    ->defaultImageUrl(fn (): string => 'https://ui-avatars.com/api/?name=Product&background=f59e0b&color=fff'),
                TextColumn::make('name')
                    ->label('Product')
                    ->searchable(false)
                    ->weight('bold'),
                TextColumn::make('total_quantity')
                    ->label('Quantity')
                    ->numeric()
                    ->sortable(false),
                TextColumn::make('total_revenue')
                    ->label('Revenue')
                    ->formatStateUsing(fn ($state): string => '$'.number_format((float) $state, 2))
                    ->sortable(false),
            ])
            ->paginated(false)
            ->defaultSort('total_revenue', 'desc');
    }
}
