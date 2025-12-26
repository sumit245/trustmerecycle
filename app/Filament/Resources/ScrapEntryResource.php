<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ScrapEntryResource\Pages;
use App\Models\ScrapEntry;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ScrapEntryResource extends Resource
{
    protected static ?string $model = ScrapEntry::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $navigationLabel = 'Scrap Entries';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('godown_id')
                    ->relationship('godown', 'name')
                    ->required()
                    ->searchable()
                    ->preload(),
                Forms\Components\Select::make('scrap_type_id')
                    ->relationship('scrapType', 'name')
                    ->required()
                    ->searchable()
                    ->preload(),
                Forms\Components\DatePicker::make('date')
                    ->required()
                    ->default(now()),
                Forms\Components\TextInput::make('amount_mt')
                    ->label('Amount (MT)')
                    ->numeric()
                    ->required()
                    ->step(0.01),
                Forms\Components\TextInput::make('estimated_value')
                    ->label('Estimated Value (₹)')
                    ->numeric()
                    ->prefix('₹')
                    ->disabled(),
                Forms\Components\Textarea::make('notes')
                    ->rows(3),
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
                Tables\Columns\TextColumn::make('scrapType.name')
                    ->label('Scrap Type')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('date')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('amount_mt')
                    ->label('Amount')
                    ->suffix(' MT')
                    ->sortable(),
                Tables\Columns\TextColumn::make('estimated_value')
                    ->label('Value')
                    ->money('INR')
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('godown_id')
                    ->label('Godown')
                    ->relationship('godown', 'name'),
                Tables\Filters\SelectFilter::make('scrap_type_id')
                    ->label('Scrap Type')
                    ->relationship('scrapType', 'name'),
                Tables\Filters\Filter::make('date')
                    ->form([
                        Forms\Components\DatePicker::make('created_from')
                            ->label('From Date'),
                        Forms\Components\DatePicker::make('created_until')
                            ->label('Until Date'),
                    ])
                    ->query(function ($query, array $data) {
                        return $query
                            ->when(
                                $data['created_from'],
                                fn ($query, $date) => $query->whereDate('date', '>=', $date),
                            )
                            ->when(
                                $data['created_until'],
                                fn ($query, $date) => $query->whereDate('date', '<=', $date),
                            );
                    }),
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
            'index' => \App\Filament\Resources\ScrapEntryResource\Pages\ListScrapEntries::route('/'),
            'create' => \App\Filament\Resources\ScrapEntryResource\Pages\CreateScrapEntry::route('/create'),
            'view' => \App\Filament\Resources\ScrapEntryResource\Pages\ViewScrapEntry::route('/{record}'),
            'edit' => \App\Filament\Resources\ScrapEntryResource\Pages\EditScrapEntry::route('/{record}/edit'),
        ];
    }
}

