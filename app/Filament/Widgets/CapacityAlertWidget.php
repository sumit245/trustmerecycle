<?php

namespace App\Filament\Widgets;

use App\Models\Godown;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class CapacityAlertWidget extends BaseWidget
{
    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Godown::query()
                    ->whereRaw('current_stock_mt >= capacity_limit_mt * 0.8')
                    ->orderByRaw('(current_stock_mt / capacity_limit_mt) DESC')
            )
            ->columns([
                Tables\Columns\TextColumn::make('vendor.name')
                    ->label('Vendor')
                    ->searchable(),
                Tables\Columns\TextColumn::make('name')
                    ->label('Godown')
                    ->searchable(),
                Tables\Columns\TextColumn::make('current_stock_mt')
                    ->label('Current Stock')
                    ->suffix(' MT')
                    ->sortable(),
                Tables\Columns\TextColumn::make('capacity_limit_mt')
                    ->label('Capacity')
                    ->suffix(' MT')
                    ->sortable(),
                Tables\Columns\TextColumn::make('stock_percentage')
                    ->label('Usage %')
                    ->formatStateUsing(fn ($state) => number_format($state, 1) . '%')
                    ->color(fn ($state) => $state >= 100 ? 'danger' : 'warning')
                    ->sortable(),
            ])
            ->heading('Godowns Nearing/Full Capacity')
            ->description('Godowns at 80% or more capacity');
    }
}

