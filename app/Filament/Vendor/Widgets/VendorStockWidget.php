<?php

namespace App\Filament\Vendor\Widgets;

use App\Models\Godown;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Auth;

class VendorStockWidget extends BaseWidget
{
    protected function getStats(): array
    {
        $user = Auth::user();
        $godown = $user->godowns()->first();

        if (!$godown) {
            return [
                Stat::make('No Godown Assigned', 'Please contact admin')
                    ->description('You need a godown to view stats')
                    ->color('danger'),
            ];
        }

        $utilization = $godown->capacity_limit_mt > 0 
            ? ($godown->current_stock_mt / $godown->capacity_limit_mt) * 100 
            : 0;

        return [
            Stat::make('Current Stock', number_format($godown->current_stock_mt, 2) . ' MT')
                ->description($godown->name)
                ->descriptionIcon('heroicon-m-building-storefront')
                ->color('success'),
            Stat::make('Capacity Limit', number_format($godown->capacity_limit_mt, 2) . ' MT')
                ->description('Total capacity')
                ->descriptionIcon('heroicon-m-scale')
                ->color('info'),
            Stat::make('Utilization', number_format($utilization, 1) . '%')
                ->description('Capacity usage')
                ->descriptionIcon('heroicon-m-chart-bar')
                ->color($utilization >= 80 ? 'danger' : ($utilization >= 60 ? 'warning' : 'success')),
        ];
    }
}

