<?php

namespace App\Filament\Resources\SiteInchargeResource\RelationManagers;

use App\Models\Godown;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class GodownsRelationManager extends RelationManager
{
    protected static string $relationship = 'godowns';

    protected static ?string $title = 'Allotted Sites';

    protected static ?string $recordTitleAttribute = 'name';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
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
            ])
            ->columns(2);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('location')
                    ->searchable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('capacity_limit_mt')
                    ->label('Capacity')
                    ->suffix(' MT')
                    ->sortable(),
                Tables\Columns\TextColumn::make('current_stock_mt')
                    ->label('Current Stock')
                    ->suffix(' MT')
                    ->sortable(),
                Tables\Columns\TextColumn::make('stock_percentage')
                    ->label('Stock %')
                    ->formatStateUsing(fn ($state) => number_format((float) $state, 1) . '%')
                    ->color(fn ($state) => $state >= 80 ? 'danger' : ($state >= 60 ? 'warning' : 'success'))
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('name')
            ->paginated([10, 25, 50])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label('Add New Site'),
                Tables\Actions\Action::make('assign_existing')
                    ->label('Assign Existing Site')
                    ->icon('heroicon-o-link')
                    ->color('primary')
                    ->modalHeading('Assign an Existing Site')
                    ->modalDescription('Select a site that is currently unassigned and attach it to this Site Incharge.')
                    ->form([
                        Forms\Components\Select::make('godown_id')
                            ->label('Site')
                            ->options(function () {
                                return Godown::query()
                                    ->whereNull('vendor_id')
                                    ->orderBy('name')
                                    ->pluck('name', 'id')
                                    ->toArray();
                            })
                            ->required()
                            ->searchable()
                            ->preload()
                            ->placeholder('Choose an unassigned site'),
                    ])
                    ->action(function (array $data): void {
                        $godown = Godown::query()
                            ->whereKey($data['godown_id'])
                            ->whereNull('vendor_id')
                            ->firstOrFail();

                        $godown->update([
                            'vendor_id' => $this->getOwnerRecord()->getKey(),
                        ]);
                    })
                    ->visible(fn (): bool => auth()->user()?->isAdmin() ?? false),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('unassign')
                    ->label('Remove from Incharge')
                    ->icon('heroicon-o-arrow-uturn-left')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->modalHeading('Remove Site from this Site Incharge?')
                    ->modalDescription('This will unassign the site without deleting it, so it can be reassigned later.')
                    ->action(function (Godown $record): void {
                        $record->update([
                            'vendor_id' => null,
                        ]);
                    })
                    ->visible(fn (): bool => auth()->user()?->isAdmin() ?? false),
            ])
            ->emptyStateHeading('No sites assigned')
            ->emptyStateDescription('Assign an existing site or create a new one for this Site Incharge.')
            ->emptyStateActions([
                Tables\Actions\Action::make('assign_existing_empty_state')
                    ->label('Assign Existing Site')
                    ->icon('heroicon-o-link')
                    ->color('primary')
                    ->modalHeading('Assign an Existing Site')
                    ->modalDescription('Select a site that is currently unassigned and attach it to this Site Incharge.')
                    ->form([
                        Forms\Components\Select::make('godown_id')
                            ->label('Site')
                            ->options(function () {
                                return Godown::query()
                                    ->whereNull('vendor_id')
                                    ->orderBy('name')
                                    ->pluck('name', 'id')
                                    ->toArray();
                            })
                            ->required()
                            ->searchable()
                            ->preload()
                            ->placeholder('Choose an unassigned site'),
                    ])
                    ->action(function (array $data): void {
                        $godown = Godown::query()
                            ->whereKey($data['godown_id'])
                            ->whereNull('vendor_id')
                            ->firstOrFail();

                        $godown->update([
                            'vendor_id' => $this->getOwnerRecord()->getKey(),
                        ]);
                    })
                    ->visible(fn (): bool => auth()->user()?->isAdmin() ?? false),
            ]);
    }
}
