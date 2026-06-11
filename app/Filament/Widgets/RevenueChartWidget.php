<?php

namespace App\Filament\Widgets;

use App\Filament\Widgets\Concerns\InteractsWithAnalyticsPeriod;
use App\Models\Order;
use Carbon\Carbon;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Collection;

class RevenueChartWidget extends ChartWidget
{
    use InteractsWithAnalyticsPeriod;

    protected static bool $isDiscovered = false;

    protected ?string $heading = 'Revenue over time';

    protected ?string $description = 'Completed order revenue trend.';

    protected ?string $maxHeight = '320px';

    protected string $color = 'success';

    protected ?string $pollingInterval = '60s';

    protected function getType(): string
    {
        return 'line';
    }

    protected function getData(): array
    {
        $range = $this->getPeriodRange();
        $period = $this->getPeriod();

        $orders = Order::query()
            ->where('status', Order::STATUS_COMPLETED)
            ->whereBetween('created_at', [$range['start'], $range['end']])
            ->get(['created_at', 'total']);

        if ($period === 'today') {
            $labels = collect(range(0, 23))->map(fn (int $hour): string => sprintf('%02d:00', $hour));
            $data = $labels->map(function (string $label) use ($orders): float {
                $hour = (int) substr($label, 0, 2);

                return (float) $orders
                    ->filter(fn (Order $order): bool => $order->created_at->hour === $hour)
                    ->sum('total');
            });
        } else {
            $labels = $this->buildDateLabels($range['start'], $range['end']);
            $data = $labels->map(function (string $date) use ($orders): float {
                return (float) $orders
                    ->filter(fn (Order $order): bool => $order->created_at->toDateString() === $date)
                    ->sum('total');
            });
        }

        return [
            'datasets' => [
                [
                    'label' => 'Revenue',
                    'data' => $data->values()->all(),
                    'borderColor' => '#10b981',
                    'backgroundColor' => 'rgba(16, 185, 129, 0.15)',
                    'fill' => true,
                    'tension' => 0.35,
                ],
            ],
            'labels' => $labels
                ->map(fn (string $label): string => $period === 'today'
                    ? $label
                    : Carbon::parse($label)->format('d M'))
                ->all(),
        ];
    }

    /**
     * @return Collection<int, string>
     */
    protected function buildDateLabels(Carbon $start, Carbon $end): Collection
    {
        $labels = collect();
        $cursor = $start->copy()->startOfDay();

        while ($cursor->lte($end)) {
            $labels->push($cursor->toDateString());
            $cursor->addDay();
        }

        return $labels;
    }
}
