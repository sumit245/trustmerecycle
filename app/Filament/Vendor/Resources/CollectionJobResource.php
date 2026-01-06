<?php

namespace App\Filament\Vendor\Resources;

use App\Filament\Vendor\Resources\CollectionJobResource\Pages;
use App\Models\CollectionJob;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

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
                    ->disabled(),
                Forms\Components\Select::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'truck_dispatched' => 'Truck Dispatched',
                        'completed' => 'Completed',
                    ])
                    ->disabled(),
                Forms\Components\KeyValue::make('truck_details')
                    ->label('Truck Details')
                    ->keyLabel('Field')
                    ->valueLabel('Value')
                    ->disabled(),
                Forms\Components\TextInput::make('collected_amount_mt')
                    ->label('Scrap weight')
                    ->numeric()
                    ->disabled(),
                Forms\Components\Placeholder::make('collection_proof_image')
                    ->label('Scrap Image')
                    ->content(function ($record) {
                        if (!$record->collection_proof_image) {
                            return new \Illuminate\Support\HtmlString('<p class="text-sm text-gray-500 italic">No scrap image uploaded</p>');
                        }
                        
                        $imageUrl = Storage::disk('public')->url($record->collection_proof_image);
                        $fileName = basename($record->collection_proof_image);
                        
                        return new \Illuminate\Support\HtmlString(
                            '<div class="space-y-2">' .
                            '<img src="' . $imageUrl . '" alt="Collection Scrap Image" class="max-w-full h-auto rounded-lg border border-gray-300 shadow-sm" style="max-height: 400px;" />' .
                            '<p class="text-sm text-gray-500">' . $fileName . '</p>' .
                            '</div>'
                        );
                    }),
                Forms\Components\Placeholder::make('challan_image')
                    ->label('Challan')
                    ->content(function ($record) {
                        if (!$record->challan_image) {
                            return new \Illuminate\Support\HtmlString('<p class="text-sm text-gray-500 italic">No challan image uploaded</p>');
                        }
                        
                        $imageUrl = Storage::disk('public')->url($record->challan_image);
                        $fileName = basename($record->challan_image);
                        
                        return new \Illuminate\Support\HtmlString(
                            '<div class="space-y-2">' .
                            '<img src="' . $imageUrl . '" alt="Challan Image" class="max-w-full h-auto rounded-lg border border-gray-300 shadow-sm" style="max-height: 400px;" />' .
                            '<p class="text-sm text-gray-500">' . $fileName . '</p>' .
                            '</div>'
                        );
                    }),
                Forms\Components\DateTimePicker::make('dispatched_at')
                    ->disabled(),
                Forms\Components\DateTimePicker::make('collected_at')
                    ->disabled(),
            ]);
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();
        
        $user = Auth::user();
        $godownIds = $user->godowns()->pluck('id')->toArray();
        $query->whereIn('godown_id', $godownIds);
        
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
                    }),
                Tables\Columns\TextColumn::make('truck_details')
                    ->label('Driver')
                    ->formatStateUsing(fn ($state) => $state['driver_name'] ?? 'N/A')
                    ->toggleable(),
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
                    ->dateTime()
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('collected_at')
                    ->dateTime()
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
                Tables\Actions\Action::make('complete')
                    ->label('Complete Job')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->form([
                        Forms\Components\FileUpload::make('collection_proof_image')
                            ->label('Collection Proof Image')
                            ->image()
                            ->directory('proofs')
                            ->disk('public')
                            ->visibility('public')
                            ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/jpg'])
                            ->maxSize(5120)
                            ->required()
                            ->helperText('Upload photo of empty godown or truck loading (Max 5MB, JPG/PNG)'),
                        Forms\Components\TextInput::make('collected_amount_mt')
                            ->label('Collected Amount (MT)')
                            ->numeric()
                            ->required()
                            ->step(0.01)
                            ->minValue(0.01)
                            ->maxValue(function (CollectionJob $record) {
                                $user = Auth::user();
                                $godown = $user->godowns()->first();
                                return $godown ? $godown->current_stock_mt : 0;
                            })
                            ->helperText(function (CollectionJob $record) {
                                $user = Auth::user();
                                $godown = $user->godowns()->first();
                                $max = $godown ? $godown->current_stock_mt : 0;
                                return "Maximum: " . number_format($max, 2) . " MT";
                            }),
                    ])
                    ->action(function (CollectionJob $record, array $data) {
                        $user = Auth::user();
                        $godown = $user->godowns()->first();

                        // Validate ownership
                        if (!$godown || $record->godown_id !== $godown->id) {
                            throw new \Exception('Unauthorized access.');
                        }

                        // Validate status
                        if (!$record->isDispatched()) {
                            throw new \Exception('This job is not in dispatched status.');
                        }

                        // Validate collected amount
                        if ($data['collected_amount_mt'] > $godown->current_stock_mt) {
                            throw new \Exception('Collected amount cannot exceed current stock.');
                        }

                        // Get the file path from the uploaded file
                        $imagePath = $data['collection_proof_image'];

                        // Mark job as completed and reduce stock
                        $record->markCompleted($data['collected_amount_mt'], $imagePath);
                        $godown->reduceStock($data['collected_amount_mt']);
                    })
                    ->visible(fn (CollectionJob $record) => $record->isDispatched())
                    ->successNotificationTitle('Collection job completed successfully!'),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function canCreate(): bool
    {
        return false; // Vendors cannot create collection jobs
    }

    public static function canEdit($record): bool
    {
        return false; // Vendors cannot edit collection jobs
    }

    public static function canDelete($record): bool
    {
        return false; // Vendors cannot delete collection jobs
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCollectionJobs::route('/'),
            'view' => Pages\ViewCollectionJob::route('/{record}'),
        ];
    }
}

