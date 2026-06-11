<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\AnalyticsStatsWidget;
use App\Filament\Widgets\AverageServiceTimeWidget;
use App\Filament\Widgets\CategoryDistributionWidget;
use App\Filament\Widgets\RevenueByTableWidget;
use App\Filament\Widgets\RevenueChartWidget;
use App\Filament\Widgets\TopProductsWidget;
use App\Support\Analytics\PeriodResolver;
use BackedEnum;
use Filament\Forms\Components\Select;
use Filament\Pages\Dashboard;
use Filament\Pages\Dashboard\Concerns\HasFiltersForm;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use UnitEnum;

class Analytics extends Dashboard
{
    use HasFiltersForm;

    protected static ?string $navigationLabel = 'Dashboard';

    protected static ?string $title = 'Dashboard';

    protected static ?int $navigationSort = -2;

    protected static string|UnitEnum|null $navigationGroup = null;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedChartBar;

    public function mount(): void
    {
        $this->mountHasFilters();

        if (blank($this->filters)) {
            $this->filters = [
                'period' => '7days',
            ];
        }
    }

    public function filtersForm(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('period')
                ->label('Period')
                ->options(PeriodResolver::periodOptions())
                ->default('7days')
                ->native(false),
        ]);
    }

    /**
     * @return array<class-string>
     */
    protected function getHeaderWidgets(): array
    {
        return [
            AnalyticsStatsWidget::class,
            AverageServiceTimeWidget::class,
        ];
    }

    /**
     * @return array<class-string>
     */
    public function getWidgets(): array
    {
        return [
            TopProductsWidget::class,
            RevenueChartWidget::class,
            RevenueByTableWidget::class,
            CategoryDistributionWidget::class,
        ];
    }

    /**
     * @return int|array<string, ?int>
     */
    public function getHeaderWidgetsColumns(): int|array
    {
        return [
            'default' => 1,
            'md' => 2,
        ];
    }

    /**
     * @return int|array<string, ?int>
     */
    public function getColumns(): int|array
    {
        return [
            'default' => 1,
            'md' => 2,
            'xl' => 2,
        ];
    }
}
