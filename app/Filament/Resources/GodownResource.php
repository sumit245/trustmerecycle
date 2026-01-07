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

    protected static ?string $navigationLabel = 'Sites';

    protected static ?string $modelLabel = 'Site';

    protected static ?string $pluralModelLabel = 'Sites';

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();
        
        // If user is Site Incharge, only show their godowns
        if (auth()->user() && auth()->user()->isSiteIncharge()) {
            $query->where('vendor_id', auth()->id());
        }
        
        return $query;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('vendor_id')
                    ->label('Site Incharge')
                    ->relationship('vendor', 'name')
                    ->required()
                    ->searchable()
                    ->preload()
                    ->disabled(fn () => auth()->user() && auth()->user()->isSiteIncharge())
                    ->default(fn () => auth()->user() && auth()->user()->isSiteIncharge() ? auth()->id() : null),
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
                    ->label('Site Incharge')
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
                    ->label('Dispatch Truck Pickup')
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

                        // Notify Site Incharge
                        $record->vendor->notify(new CollectionJobCreatedNotification($job));
                    })
                    ->visible(fn () => auth()->user()?->isAdmin() ?? false),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\BulkAction::make('assign_vendor')
                        ->label('Assign to Site Incharge')
                        ->icon('heroicon-o-user-plus')
                        ->color('primary')
                        ->form([
                            Forms\Components\Select::make('vendor_id')
                                ->label('Site Incharge')
                                ->options(function () {
                                    return User::where('role', 'vendor')
                                        ->orderBy('name')
                                        ->pluck('name', 'id')
                                        ->toArray();
                                })
                                ->required()
                                ->searchable()
                                ->preload()
                                ->placeholder('Select a Site Incharge'),
                        ])
                        ->action(function (\Illuminate\Database\Eloquent\Collection $records, array $data) {
                            $records->each(function (Godown $godown) use ($data) {
                                $godown->update(['vendor_id' => $data['vendor_id']]);
                            });
                        })
                        ->deselectRecordsAfterCompletion()
                        ->successNotificationTitle('Selected sites have been assigned to the Site Incharge.')
                        ->visible(fn () => auth()->user()?->isAdmin() ?? false),
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function canViewAny(): bool
    {
        return auth()->check();
    }

    public static function canCreate(): bool
    {
        return auth()->check();
    }

    public static function canEdit($record): bool
    {
        // Site Incharge can only edit their own godowns
        if (auth()->user() && auth()->user()->isSiteIncharge()) {
            return $record->vendor_id === auth()->id();
        }
        
        // Admin can edit all
        return auth()->user()?->isAdmin() ?? false;
    }

    public static function canDelete($record): bool
    {
        // Only admin can delete godowns
        return auth()->user()?->isAdmin() ?? false;
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

