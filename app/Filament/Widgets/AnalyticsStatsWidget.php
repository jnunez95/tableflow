<?php

namespace App\Filament\Widgets;

use App\Filament\Widgets\Concerns\InteractsWithAnalyticsPeriod;
use App\Models\Order;
use Filament\Support\Icons\Heroicon;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class AnalyticsStatsWidget extends StatsOverviewWidget
{
    use InteractsWithAnalyticsPeriod;

    protected static bool $isDiscovered = false;

    protected ?string $heading = 'Business overview';

    protected ?string $description = 'Totals for the selected period (excluding service charge).';

    protected int|string|array $columnSpan = 'full';

    protected ?string $pollingInterval = '60s';

    protected function getStats(): array
    {
        $range = $this->getPeriodRange();

        $currentRevenue = (float) Order::query()
            ->where('status', Order::STATUS_COMPLETED)
            ->whereBetween('created_at', [$range['start'], $range['end']])
            ->sum('total');

        $previousRevenue = (float) Order::query()
            ->where('status', Order::STATUS_COMPLETED)
            ->whereBetween('created_at', [$range['previous_start'], $range['previous_end']])
            ->sum('total');

        $currentOrders = Order::query()
            ->where('status', Order::STATUS_COMPLETED)
            ->whereBetween('created_at', [$range['start'], $range['end']])
            ->count();

        $previousOrders = Order::query()
            ->where('status', Order::STATUS_COMPLETED)
            ->whereBetween('created_at', [$range['previous_start'], $range['previous_end']])
            ->count();

        $averageOrderValue = $currentOrders > 0
            ? $currentRevenue / $currentOrders
            : 0.0;

        $previousAverageOrderValue = $previousOrders > 0
            ? $previousRevenue / $previousOrders
            : 0.0;

        $revenueChange = $this->percentageChange($currentRevenue, $previousRevenue);
        $ordersChange = $this->percentageChange((float) $currentOrders, (float) $previousOrders);
        $aovChange = $this->percentageChange($averageOrderValue, $previousAverageOrderValue);

        return [
            Stat::make('Revenue', $this->formatCurrency($currentRevenue))
                ->description($this->formatPercentageChange($revenueChange))
                ->descriptionIcon($revenueChange !== null && $revenueChange >= 0 ? Heroicon::OutlinedArrowTrendingUp : Heroicon::OutlinedArrowTrendingDown)
                ->color($this->changeColor($revenueChange))
                ->icon(Heroicon::OutlinedBanknotes),
            Stat::make('Completed orders', (string) $currentOrders)
                ->description($this->formatPercentageChange($ordersChange))
                ->descriptionIcon($ordersChange !== null && $ordersChange >= 0 ? Heroicon::OutlinedArrowTrendingUp : Heroicon::OutlinedArrowTrendingDown)
                ->color($this->changeColor($ordersChange))
                ->icon(Heroicon::OutlinedClipboardDocumentCheck),
            Stat::make('Average order value', $this->formatCurrency($averageOrderValue))
                ->description($this->formatPercentageChange($aovChange))
                ->descriptionIcon($aovChange !== null && $aovChange >= 0 ? Heroicon::OutlinedArrowTrendingUp : Heroicon::OutlinedArrowTrendingDown)
                ->color($this->changeColor($aovChange))
                ->icon(Heroicon::OutlinedCalculator),
        ];
    }
}
