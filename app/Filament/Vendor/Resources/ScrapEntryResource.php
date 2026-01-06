<?php

namespace App\Filament\Vendor\Resources;

use App\Filament\Vendor\Resources\ScrapEntryResource\Pages;
use App\Models\ScrapEntry;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class ScrapEntryResource extends Resource
{
    protected static ?string $model = ScrapEntry::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $navigationLabel = 'Scrap Entries';

    public static function form(Form $form): Form
    {
        $user = Auth::user();
        $godownIds = $user->godowns()->pluck('id')->toArray();
        $godown = $user->godowns()->first();

        return $form
            ->schema([
                Forms\Components\Select::make('godown_id')
                    ->relationship('godown', 'name')
                    ->required()
                    ->options(function () use ($godownIds) {
                        return \App\Models\Godown::whereIn('id', $godownIds)
                            ->pluck('name', 'id')
                            ->toArray();
                    })
                    ->default($godown?->id),
                Forms\Components\Select::make('scrap_type_id')
                    ->relationship('scrapType', 'name', fn ($query) => $query->where('is_active', true))
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
                    ->step(0.01)
                    ->live()
                    ->afterStateUpdated(function ($state, Forms\Set $set, $get) {
                        $scrapTypeId = $get('scrap_type_id');
                        if ($scrapTypeId && $state) {
                            $scrapType = \App\Models\ScrapType::find($scrapTypeId);
                            if ($scrapType) {
                                $set('estimated_value', $state * $scrapType->unit_price_per_ton);
                            }
                        }
                    }),
                Forms\Components\TextInput::make('estimated_value')
                    ->label('Estimated Value (₹)')
                    ->numeric()
                    ->prefix('₹')
                    ->disabled()
                    ->dehydrated(),
                Forms\Components\Textarea::make('notes')
                    ->rows(3),
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
            'index' => Pages\ListScrapEntries::route('/'),
            'create' => Pages\CreateScrapEntry::route('/create'),
            'view' => Pages\ViewScrapEntry::route('/{record}'),
            'edit' => Pages\EditScrapEntry::route('/{record}/edit'),
        ];
    }
}

