<?php

namespace App\Support\Analytics;

use Carbon\Carbon;

class PeriodResolver
{
    /**
     * @return array{
     *     start: Carbon,
     *     end: Carbon,
     *     previous_start: Carbon,
     *     previous_end: Carbon,
     *     label: string
     * }
     */
    public static function resolve(?string $period = '7days'): array
    {
        $period = $period ?? '7days';
        $now = Carbon::now();

        return match ($period) {
            'today' => [
                'start' => $now->copy()->startOfDay(),
                'end' => $now->copy()->endOfDay(),
                'previous_start' => $now->copy()->subDay()->startOfDay(),
                'previous_end' => $now->copy()->subDay()->endOfDay(),
                'label' => 'Today',
            ],
            '30days' => [
                'start' => $now->copy()->subDays(29)->startOfDay(),
                'end' => $now->copy()->endOfDay(),
                'previous_start' => $now->copy()->subDays(59)->startOfDay(),
                'previous_end' => $now->copy()->subDays(30)->endOfDay(),
                'label' => 'Last 30 days',
            ],
            'month' => [
                'start' => $now->copy()->startOfMonth(),
                'end' => $now->copy()->endOfDay(),
                'previous_start' => $now->copy()->subMonth()->startOfMonth(),
                'previous_end' => $now->copy()->subMonth()->endOfMonth(),
                'label' => 'Current month',
            ],
            'last_month' => [
                'start' => $now->copy()->subMonth()->startOfMonth(),
                'end' => $now->copy()->subMonth()->endOfMonth(),
                'previous_start' => $now->copy()->subMonths(2)->startOfMonth(),
                'previous_end' => $now->copy()->subMonths(2)->endOfMonth(),
                'label' => 'Previous month',
            ],
            default => [
                'start' => $now->copy()->subDays(6)->startOfDay(),
                'end' => $now->copy()->endOfDay(),
                'previous_start' => $now->copy()->subDays(13)->startOfDay(),
                'previous_end' => $now->copy()->subDays(7)->endOfDay(),
                'label' => 'Last 7 days',
            ],
        };
    }

    /**
     * @return array<string, string>
     */
    public static function periodOptions(): array
    {
        return [
            'today' => 'Today',
            '7days' => 'Last 7 days',
            '30days' => 'Last 30 days',
            'month' => 'Current month',
            'last_month' => 'Previous month',
        ];
    }
}
