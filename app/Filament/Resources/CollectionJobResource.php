<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CollectionJobResource\Pages;
use App\Models\CollectionJob;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class CollectionJobResource extends Resource
{
    protected static ?string $model = CollectionJob::class;

    protected static ?string $navigationIcon = 'heroicon-o-truck';

    protected static ?string $navigationLabel = 'Collection Jobs';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('godown_id')
                    ->label('Site')
                    ->relationship('godown', 'name')
                    ->required()
                    ->searchable()
                    ->preload()
                    ->options(function () {
                        // Site Incharge can only select their own godowns
                        if (auth()->user() && auth()->user()->isSiteIncharge()) {
                            return auth()->user()->godowns->pluck('name', 'id')->toArray();
                        }
                        // Admin can select all godowns
                        return \App\Models\Godown::pluck('name', 'id')->toArray();
                    }),
                Forms\Components\Select::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'truck_dispatched' => 'Truck Dispatched',
                        'completed' => 'Completed',
                    ])
                    ->required(),
                Forms\Components\KeyValue::make('truck_details')
                    ->label('Truck Details')
                    ->keyLabel('Field')
                    ->valueLabel('Value'),
                Forms\Components\TextInput::make('collected_amount_mt')
                    ->label('Scrap weight')
                    ->numeric()
                    ->step(0.01),
                Forms\Components\FileUpload::make('collection_proof_image')
                    ->label('Scrap Image')
                    ->image()
                    ->directory('proofs')
                    ->disk('public')
                    ->visibility('public')
                    ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/jpg'])
                    ->maxSize(5120),
                Forms\Components\FileUpload::make('challan_image')
                    ->label('Challan')
                    ->image()
                    ->directory('challans')
                    ->disk('public')
                    ->visibility('public')
                    ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/jpg'])
                    ->maxSize(5120),
                Forms\Components\DateTimePicker::make('dispatched_at'),
                Forms\Components\DateTimePicker::make('collected_at'),
            ]);
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();
        
        // If user is Site Incharge, only show jobs from their godowns
        if (auth()->user() && auth()->user()->isSiteIncharge()) {
            $godownIds = auth()->user()->godowns->pluck('id')->toArray();
            $query->whereIn('godown_id', $godownIds);
        }
        
        return $query;
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('godown.name')
                    ->label('Site')
                    ->searchable()
                    ->sortable(),
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
                Tables\Columns\TextColumn::make('collected_amount_mt')
                    ->label('Scrap weight')
                    ->suffix(' MT')
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\ImageColumn::make('collection_proof_image')
                    ->label('Scrap')
                    ->disk('public')
                    ->toggleable(),
                Tables\Columns\ImageColumn::make('challan_image')
                    ->label('Challan')
                    ->disk('public')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('dispatched_at')
                    ->date('d/M/y')
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('collected_at')
                    ->label('Collected at')
                    ->state(function ($record) {
                        if ($record->collected_at === null) {
                            return '<span style="color: #ef4444; font-weight: bold;">Not Picked Up</span>';
                        }
                        return $record->collected_at->format('d/M/y');
                    })
                    ->html()
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'truck_dispatched' => 'Truck Dispatched',
                        'completed' => 'Completed',
                    ]),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->modifyQueryUsing(function (Builder $query) {
                // Get existing orders before modifying
                $baseQuery = $query->getQuery();
                $existingOrders = $baseQuery->orders ?? [];
                
                // Clear existing orders and rebuild with our priority first
                $baseQuery->orders = [];
                
                // First: prioritize uncollected items (collected_at IS NULL)
                $query->orderByRaw('collected_at IS NULL DESC');
                
                // Then: add back any existing orders (user sorting, etc.)
                foreach ($existingOrders as $order) {
                    if (isset($order['column'])) {
                        $query->orderBy($order['column'], $order['direction'] ?? 'asc');
                    } elseif (isset($order['sql'])) {
                        $query->orderByRaw($order['sql']);
                    }
                }
                
                // If no other orders exist, add default ordering by created_at
                if (empty($existingOrders)) {
                    $query->orderBy('created_at', 'desc');
                }
            });
    }

    public static function canViewAny(): bool
    {
        return auth()->check();
    }

    public static function canCreate(): bool
    {
        // Only admin can create collection jobs
        return auth()->user()?->isAdmin() ?? false;
    }

    public static function canEdit($record): bool
    {
        // Site Incharge can only edit jobs from their godowns
        if (auth()->user() && auth()->user()->isSiteIncharge()) {
            $godownIds = auth()->user()->godowns->pluck('id')->toArray();
            return in_array($record->godown_id, $godownIds);
        }
        
        // Admin can edit all
        return auth()->user()?->isAdmin() ?? false;
    }

    public static function canDelete($record): bool
    {
        // Only admin can delete collection jobs
        return auth()->user()?->isAdmin() ?? false;
    }

    public static function getPages(): array
    {
        return [
            'index' => \App\Filament\Resources\CollectionJobResource\Pages\ListCollectionJobs::route('/'),
            'create' => \App\Filament\Resources\CollectionJobResource\Pages\CreateCollectionJob::route('/create'),
            'view' => \App\Filament\Resources\CollectionJobResource\Pages\ViewCollectionJob::route('/{record}'),
            'edit' => \App\Filament\Resources\CollectionJobResource\Pages\EditCollectionJob::route('/{record}/edit'),
        ];
    }
}

