<?php

namespace App\Filament\Widgets;

use App\Models\Godown;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class TotalScrapWidget extends BaseWidget
{
    protected function getStats(): array
    {
        $totalStock = Godown::sum('current_stock_mt');
        $totalCapacity = Godown::sum('capacity_limit_mt');
        $utilization = $totalCapacity > 0 ? ($totalStock / $totalCapacity) * 100 : 0;

        return [
            Stat::make('Total Scrap Stock', number_format($totalStock, 2) . ' MT')
                ->description('Across all godowns')
                ->descriptionIcon('heroicon-m-building-storefront')
                ->color('success'),
            Stat::make('Total Capacity', number_format($totalCapacity, 2) . ' MT')
                ->description('Combined capacity limit')
                ->descriptionIcon('heroicon-m-scale')
                ->color('info'),
            Stat::make('Utilization', number_format($utilization, 1) . '%')
                ->description('Overall capacity usage')
                ->descriptionIcon('heroicon-m-chart-bar')
                ->color($utilization >= 80 ? 'danger' : ($utilization >= 60 ? 'warning' : 'success')),
        ];
    }
}

