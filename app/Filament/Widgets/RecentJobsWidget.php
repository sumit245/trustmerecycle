<?php

namespace App\Filament\Widgets;

use App\Models\CollectionJob;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class RecentJobsWidget extends BaseWidget
{
    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                CollectionJob::query()
                    ->orderByRaw('collected_at IS NULL DESC')
                    ->orderBy('created_at', 'desc')
                    ->limit(10)
            )
            ->columns([
                Tables\Columns\TextColumn::make('godown.name')
                    ->label('Site')
                    ->searchable(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'warning',
                        'truck_dispatched' => 'info',
                        'completed' => 'success',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'pending' => 'Pending',
                        'truck_dispatched' => 'Truck Dispatched',
                        'completed' => 'Completed',
                        default => $state,
                    }),
                Tables\Columns\TextColumn::make('dispatched_at')
                    ->date('d/M/y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('collected_at')
                    ->label('Collected at')
                    ->state(function ($record) {
                        if ($record->collected_at === null) {
                            return '<span style="color: #ef4444; font-weight: bold;">Not Picked Up</span>';
                        }
                        return $record->collected_at->format('d/M/y');
                    })
                    ->html()
                    ->sortable(),
            ])
            ->heading('Recent Collection Jobs')
            ->description('Latest 10 collection jobs');
    }
}

