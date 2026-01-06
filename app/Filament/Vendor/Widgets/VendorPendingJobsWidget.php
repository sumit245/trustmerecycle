<?php

namespace App\Filament\Vendor\Widgets;

use App\Models\CollectionJob;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Support\Facades\Auth;

class VendorPendingJobsWidget extends BaseWidget
{
    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        $user = Auth::user();
        $godownIds = $user->godowns()->pluck('id')->toArray();

        return $table
            ->query(
                CollectionJob::query()
                    ->whereIn('godown_id', $godownIds)
                    ->whereIn('status', ['pending', 'truck_dispatched'])
                    ->latest()
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
                    }),
                Tables\Columns\TextColumn::make('truck_details')
                    ->label('Driver')
                    ->formatStateUsing(fn ($state) => $state['driver_name'] ?? 'N/A'),
                Tables\Columns\TextColumn::make('dispatched_at')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->heading('Pending Collection Jobs')
            ->description('Jobs awaiting completion');
    }
}

