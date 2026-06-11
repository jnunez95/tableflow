<?php

namespace App\Filament\Widgets\Concerns;

use App\Support\Analytics\PeriodResolver;
use Carbon\Carbon;
use Filament\Widgets\Concerns\InteractsWithPageFilters;

trait InteractsWithAnalyticsPeriod
{
    use InteractsWithPageFilters;

    protected function getPeriod(): string
    {
        return $this->pageFilters['period'] ?? '7days';
    }

    /**
     * @return array{
     *     start: Carbon,
     *     end: Carbon,
     *     previous_start: Carbon,
     *     previous_end: Carbon,
     *     label: string
     * }
     */
    protected function getPeriodRange(): array
    {
        return PeriodResolver::resolve($this->getPeriod());
    }

    protected function formatCurrency(float $amount): string
    {
        return '$'.number_format($amount, 2);
    }

    protected function percentageChange(float $current, float $previous): ?float
    {
        if ($previous == 0.0) {
            return $current > 0.0 ? 100.0 : null;
        }

        return (($current - $previous) / $previous) * 100;
    }

    protected function formatPercentageChange(?float $change): string
    {
        if ($change === null) {
            return 'No previous period data';
        }

        $prefix = $change >= 0 ? '+' : '';

        return $prefix.number_format($change, 1).'% vs previous period';
    }

    protected function changeColor(?float $change): string
    {
        if ($change === null) {
            return 'gray';
        }

        if ($change > 0) {
            return 'success';
        }

        if ($change < 0) {
            return 'danger';
        }

        return 'gray';
    }
}
