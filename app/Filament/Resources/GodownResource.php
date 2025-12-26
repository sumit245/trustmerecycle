<?php

namespace App\Filament\Resources;

use App\Filament\Resources\GodownResource\Pages;
use App\Models\Godown;
use App\Models\User;
use App\Notifications\CollectionJobCreatedNotification;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class GodownResource extends Resource
{
    protected static ?string $model = Godown::class;

    protected static ?string $navigationIcon = 'heroicon-o-building-storefront';

    protected static ?string $navigationLabel = 'Godowns';

    protected static ?string $modelLabel = 'Godown';

    protected static ?string $pluralModelLabel = 'Godowns';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('vendor_id')
                    ->label('Vendor')
                    ->relationship('vendor', 'name')
                    ->required()
                    ->searchable()
                    ->preload(),
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('location')
                    ->required()
                    ->maxLength(255),
                Forms\Components\Textarea::make('address')
                    ->required()
                    ->rows(3),
                Forms\Components\TextInput::make('capacity_limit_mt')
                    ->label('Capacity Limit (MT)')
                    ->numeric()
                    ->required()
                    ->step(0.01),
                Forms\Components\TextInput::make('current_stock_mt')
                    ->label('Current Stock (MT)')
                    ->numeric()
                    ->default(0)
                    ->step(0.01)
                    ->disabled(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('vendor.name')
                    ->label('Vendor')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('location')
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
                    ->color(fn ($state) => $state >= 80 ? 'danger' : ($state >= 60 ? 'warning' : 'success'))
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Capacity Status')
                    ->options([
                        'full' => 'Full (≥100%)',
                        'near_full' => 'Near Full (≥80%)',
                        'normal' => 'Normal (<80%)',
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        if ($data['value'] === 'full') {
                            return $query->whereRaw('current_stock_mt >= capacity_limit_mt');
                        } elseif ($data['value'] === 'near_full') {
                            return $query->whereRaw('current_stock_mt >= capacity_limit_mt * 0.8')
                                ->whereRaw('current_stock_mt < capacity_limit_mt');
                        } elseif ($data['value'] === 'normal') {
                            return $query->whereRaw('current_stock_mt < capacity_limit_mt * 0.8');
                        }
                        return $query;
                    }),
            ])
            ->actions([
                Tables\Actions\Action::make('dispatch_truck')
                    ->label('Dispatch Truck')
                    ->icon('heroicon-o-truck')
                    ->color('success')
                    ->requiresConfirmation()
                    ->form([
                        Forms\Components\TextInput::make('driver_name')
                            ->label('Driver Name')
                            ->required(),
                        Forms\Components\TextInput::make('vehicle_number')
                            ->label('Vehicle Number')
                            ->required(),
                        Forms\Components\Textarea::make('notes')
                            ->label('Additional Notes')
                            ->rows(3),
                    ])
                    ->action(function (Godown $record, array $data) {
                        $job = $record->collectionJobs()->create([
                            'status' => 'truck_dispatched',
                            'truck_details' => [
                                'driver_name' => $data['driver_name'],
                                'vehicle_number' => $data['vehicle_number'],
                                'notes' => $data['notes'] ?? null,
                            ],
                            'dispatched_at' => now(),
                        ]);

                        // Notify vendor
                        $record->vendor->notify(new CollectionJobCreatedNotification($job));
                    }),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => \App\Filament\Resources\GodownResource\Pages\ListGodowns::route('/'),
            'create' => \App\Filament\Resources\GodownResource\Pages\CreateGodown::route('/create'),
            'edit' => \App\Filament\Resources\GodownResource\Pages\EditGodown::route('/{record}/edit'),
        ];
    }
}

