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
                CollectionJob::query()->latest()->limit(10)
            )
            ->columns([
                Tables\Columns\TextColumn::make('godown.name')
                    ->label('Godown')
                    ->searchable(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'warning',
                        'truck_dispatched' => 'info',
                        'completed' => 'success',
                    }),
                Tables\Columns\TextColumn::make('dispatched_at')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('collected_at')
                    ->dateTime()
                    ->sortable()
                    ->placeholder('Not collected'),
            ])
            ->heading('Recent Collection Jobs')
            ->description('Latest 10 collection jobs');
    }
}

