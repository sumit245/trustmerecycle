<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PickupRequestResource\Pages;
use App\Models\CollectionJob;
use App\Models\Godown;
use App\Models\PickupRequest;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class PickupRequestResource extends Resource
{
    protected static ?string $model = PickupRequest::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';

    protected static ?string $navigationLabel = 'Pickup Requests';

    protected static ?string $navigationGroup = 'Customer Requests';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Customer')
                    ->columns(2)
                    ->schema([
                        Forms\Components\TextInput::make('customer_name')
                            ->disabled(),
                        Forms\Components\TextInput::make('customer_email')
                            ->disabled(),
                        Forms\Components\TextInput::make('customer_phone')
                            ->disabled(),
                        Forms\Components\Select::make('status')
                            ->options(self::statusOptions())
                            ->disabled(),
                    ]),
                Forms\Components\Section::make('Pickup Details')
                    ->columns(2)
                    ->schema([
                        Forms\Components\Textarea::make('pickup_address')
                            ->columnSpanFull()
                            ->disabled(),
                        Forms\Components\TextInput::make('location_notes')
                            ->disabled(),
                        Forms\Components\Select::make('scrap_type_id')
                            ->label('Scrap Type')
                            ->relationship('scrapType', 'name')
                            ->disabled(),
                        Forms\Components\TextInput::make('scrap_description')
                            ->disabled(),
                        Forms\Components\TextInput::make('estimated_weight_mt')
                            ->label('Estimated Weight')
                            ->suffix(' MT')
                            ->disabled(),
                        Forms\Components\DatePicker::make('preferred_pickup_date')
                            ->disabled(),
                        Forms\Components\Textarea::make('notes')
                            ->columnSpanFull()
                            ->disabled(),
                    ]),
                Forms\Components\Section::make('Assignment')
                    ->columns(2)
                    ->schema([
                        Forms\Components\Select::make('assigned_vendor_id')
                            ->label('Assigned Vendor')
                            ->relationship('assignedVendor', 'name')
                            ->disabled(),
                        Forms\Components\Select::make('assigned_godown_id')
                            ->label('Assigned Site')
                            ->relationship('assignedGodown', 'name')
                            ->disabled(),
                        Forms\Components\Placeholder::make('collection_job')
                            ->label('Collection Job')
                            ->content(fn (?PickupRequest $record): string => $record?->collectionJob
                                ? '#' . $record->collectionJob->id . ' - ' . $record->collectionJob->status
                                : 'Not created yet'),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('customer_name')
                    ->label('Customer')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('customer_phone')
                    ->label('Phone')
                    ->searchable(),
                Tables\Columns\TextColumn::make('scrap_description')
                    ->searchable()
                    ->limit(32),
                Tables\Columns\TextColumn::make('estimated_weight_mt')
                    ->label('Est. Weight')
                    ->suffix(' MT')
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => self::statusOptions()[$state] ?? $state)
                    ->color(fn (string $state): string => match ($state) {
                        PickupRequest::STATUS_PENDING_REVIEW => 'warning',
                        PickupRequest::STATUS_ASSIGNED => 'info',
                        PickupRequest::STATUS_TRUCK_DISPATCHED => 'primary',
                        PickupRequest::STATUS_COMPLETED => 'success',
                        PickupRequest::STATUS_CANCELLED => 'danger',
                        default => 'gray',
                    })
                    ->sortable(),
                Tables\Columns\TextColumn::make('assignedGodown.name')
                    ->label('Site')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('preferred_pickup_date')
                    ->date('d/M/y')
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('requested_at')
                    ->dateTime('d/M/y h:i A')
                    ->sortable(),
                Tables\Columns\TextColumn::make('picked_up_at')
                    ->dateTime('d/M/y h:i A')
                    ->sortable()
                    ->toggleable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options(self::statusOptions()),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\Action::make('assign')
                    ->label('Assign Site')
                    ->icon('heroicon-o-truck')
                    ->color('success')
                    ->form([
                        Forms\Components\Select::make('godown_id')
                            ->label('Site / Godown')
                            ->options(fn (): array => Godown::with('vendor')
                                ->whereNotNull('vendor_id')
                                ->get()
                                ->mapWithKeys(fn (Godown $godown): array => [
                                    $godown->id => $godown->name . ' - ' . ($godown->vendor?->name ?? 'No vendor'),
                                ])
                                ->toArray())
                            ->searchable()
                            ->required(),
                    ])
                    ->action(function (PickupRequest $record, array $data): void {
                        $godown = Godown::with('vendor')->findOrFail($data['godown_id']);

                        $job = $record->collectionJob ?: CollectionJob::create([
                            'godown_id' => $godown->id,
                            'pickup_request_id' => $record->id,
                            'status' => 'pending',
                        ]);

                        $job->update([
                            'godown_id' => $godown->id,
                            'pickup_request_id' => $record->id,
                        ]);

                        $record->markAssigned($godown);

                        Notification::make()
                            ->title('Pickup request assigned')
                            ->body('A collection job has been created for the selected site.')
                            ->success()
                            ->send();
                    })
                    ->visible(fn (PickupRequest $record): bool => $record->status !== PickupRequest::STATUS_COMPLETED),
            ])
            ->defaultSort('requested_at', 'desc')
            ->modifyQueryUsing(fn (Builder $query): Builder => $query->with([
                'assignedGodown',
                'assignedVendor',
                'collectionJob',
                'scrapType',
            ]));
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit($record): bool
    {
        return false;
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPickupRequests::route('/'),
            'view' => Pages\ViewPickupRequest::route('/{record}'),
        ];
    }

    private static function statusOptions(): array
    {
        return [
            PickupRequest::STATUS_PENDING_REVIEW => 'Pending Review',
            PickupRequest::STATUS_ASSIGNED => 'Assigned',
            PickupRequest::STATUS_TRUCK_DISPATCHED => 'Truck Dispatched',
            PickupRequest::STATUS_COMPLETED => 'Completed',
            PickupRequest::STATUS_CANCELLED => 'Cancelled',
        ];
    }
}
