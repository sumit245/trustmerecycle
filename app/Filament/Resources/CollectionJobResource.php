<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CollectionJobResource\Pages;
use App\Models\CollectionJob;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

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
                    ->relationship('godown', 'name')
                    ->required()
                    ->searchable()
                    ->preload(),
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
                    ->label('Collected Amount (MT)')
                    ->numeric()
                    ->step(0.01),
                Forms\Components\FileUpload::make('collection_proof_image')
                    ->label('Proof Image')
                    ->image()
                    ->directory('proofs')
                    ->visibility('public'),
                Forms\Components\DateTimePicker::make('dispatched_at'),
                Forms\Components\DateTimePicker::make('collected_at'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('godown.name')
                    ->label('Godown')
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
                    ->label('Collected')
                    ->suffix(' MT')
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\ImageColumn::make('collection_proof_image')
                    ->label('Proof')
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
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
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

