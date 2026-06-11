<?php

namespace App\Filament\Widgets;

use App\Filament\Widgets\Concerns\InteractsWithAnalyticsPeriod;
use App\Models\Order;
use Filament\Support\Icons\Heroicon;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class AverageServiceTimeWidget extends StatsOverviewWidget
{
    use InteractsWithAnalyticsPeriod;

    protected static bool $isDiscovered = false;

    protected ?string $heading = 'Service time';

    protected ?string $description = 'Approximation based on order creation and last update.';

    protected ?string $pollingInterval = '60s';

    protected function getStats(): array
    {
        $range = $this->getPeriodRange();

        $orders = Order::query()
            ->whereIn('status', [Order::STATUS_SERVED, Order::STATUS_COMPLETED])
            ->whereBetween('created_at', [$range['start'], $range['end']])
            ->get(['created_at', 'updated_at']);

        $averageMinutes = $orders->isEmpty()
            ? 0.0
            : $orders->avg(fn (Order $order): float => (float) $order->created_at->diffInMinutes($order->updated_at));

        $roundedMinutes = round($averageMinutes, 1);

        $color = match (true) {
            $roundedMinutes <= 0 => 'gray',
            $roundedMinutes < 15 => 'success',
            $roundedMinutes <= 25 => 'warning',
            default => 'danger',
        };

        $serviceStatus = match (true) {
            $roundedMinutes <= 0 => 'No served orders in this period',
            $roundedMinutes < 15 => 'Optimal service',
            $roundedMinutes <= 25 => 'Acceptable service range',
            default => 'Delayed service',
        };

        return [
            Stat::make('Average time', $roundedMinutes > 0 ? "{$roundedMinutes} min" : '—')
                ->description($serviceStatus)
                ->descriptionIcon(Heroicon::OutlinedClock)
                ->color($color)
                ->icon(Heroicon::OutlinedClock),
        ];
    }
}
