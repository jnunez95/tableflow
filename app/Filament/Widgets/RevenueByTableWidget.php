<?php

namespace App\Filament\Widgets;

use App\Filament\Widgets\Concerns\InteractsWithAnalyticsPeriod;
use App\Models\Order;
use Filament\Support\RawJs;
use Filament\Widgets\ChartWidget;

class RevenueByTableWidget extends ChartWidget
{
    use InteractsWithAnalyticsPeriod;

    protected static bool $isDiscovered = false;

    protected ?string $heading = 'Revenue by table';

    protected ?string $description = 'Top 10 most productive tables for the period.';

    protected ?string $maxHeight = '320px';

    protected string $color = 'primary';

    protected ?string $pollingInterval = '60s';

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getData(): array
    {
        $range = $this->getPeriodRange();

        $results = Order::query()
            ->where('orders.status', Order::STATUS_COMPLETED)
            ->whereBetween('orders.created_at', [$range['start'], $range['end']])
            ->join('tables', 'orders.table_id', '=', 'tables.id')
            ->selectRaw('tables.number as table_number, SUM(orders.total) as revenue')
            ->groupBy('tables.id', 'tables.number')
            ->orderByDesc('revenue')
            ->limit(10)
            ->get();

        $colors = $results->values()->map(function ($row, int $index): string {
            return match (true) {
                $index === 0 => '#10b981',
                $index <= 2 => '#3b82f6',
                $index <= 5 => '#f59e0b',
                default => '#94a3b8',
            };
        });

        return [
            'datasets' => [
                [
                    'label' => 'Revenue',
                    'data' => $results->pluck('revenue')->map(fn ($value): float => (float) $value)->all(),
                    'backgroundColor' => $colors->all(),
                    'borderRadius' => 6,
                ],
            ],
            'labels' => $results
                ->map(fn ($row): string => 'Table '.$row->table_number)
                ->all(),
        ];
    }

    protected function getOptions(): RawJs
    {
        return RawJs::make(<<<'JS'
            {
                indexAxis: 'y',
                plugins: {
                    legend: {
                        display: false,
                    },
                },
                scales: {
                    x: {
                        ticks: {
                            callback: (value) => '$' + Number(value).toLocaleString(),
                        },
                    },
                },
            }
        JS);
    }
}
