<?php

namespace App\Filament\Widgets;

use App\Filament\Widgets\Concerns\InteractsWithAnalyticsPeriod;
use App\Models\Order;
use App\Models\OrderItem;
use Filament\Widgets\ChartWidget;

class CategoryDistributionWidget extends ChartWidget
{
    use InteractsWithAnalyticsPeriod;

    protected static bool $isDiscovered = false;

    protected ?string $heading = 'Sales by category';

    protected ?string $description = 'Revenue distribution across menu categories.';

    protected ?string $maxHeight = '320px';

    protected ?string $pollingInterval = '60s';

    protected function getType(): string
    {
        return 'doughnut';
    }

    protected function getData(): array
    {
        $range = $this->getPeriodRange();

        $results = OrderItem::query()
            ->join('products', 'order_items.product_id', '=', 'products.id')
            ->join('categories', 'products.category_id', '=', 'categories.id')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->where('orders.status', Order::STATUS_COMPLETED)
            ->whereBetween('orders.created_at', [$range['start'], $range['end']])
            ->selectRaw('categories.name as category_name, SUM(order_items.subtotal) as revenue')
            ->groupBy('categories.id', 'categories.name')
            ->orderByDesc('revenue')
            ->get();

        $palette = [
            '#f59e0b',
            '#10b981',
            '#3b82f6',
            '#8b5cf6',
            '#ef4444',
            '#06b6d4',
            '#ec4899',
            '#84cc16',
        ];

        return [
            'datasets' => [
                [
                    'label' => 'Revenue',
                    'data' => $results->pluck('revenue')->map(fn ($value): float => (float) $value)->all(),
                    'backgroundColor' => collect($palette)
                        ->take($results->count())
                        ->values()
                        ->all(),
                    'borderWidth' => 0,
                ],
            ],
            'labels' => $results->pluck('category_name')->all(),
        ];
    }
}
