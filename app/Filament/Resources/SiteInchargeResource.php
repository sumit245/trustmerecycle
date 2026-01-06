<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SiteInchargeResource\Pages;
use App\Models\User;
use App\Models\Godown;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Hash;

class SiteInchargeResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';

    protected static ?string $navigationLabel = 'Site Incharges';

    protected static ?string $modelLabel = 'Site Incharge';

    protected static ?string $pluralModelLabel = 'Site Incharges';

    protected static ?string $navigationGroup = 'Management';

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('role', 'vendor');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Site Incharge Information')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('email')
                            ->email()
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true),
                        Forms\Components\TextInput::make('phone')
                            ->tel()
                            ->maxLength(20),
                        Forms\Components\Toggle::make('edit_password')
                            ->label('Change Password')
                            ->visible(fn (string $operation): bool => $operation === 'edit')
                            ->live()
                            ->afterStateUpdated(fn ($state, Forms\Set $set) => $state ? null : $set('password', null)),
                        Forms\Components\TextInput::make('password')
                            ->password()
                            ->required(fn (string $operation, Forms\Get $get): bool => 
                                $operation === 'create' || ($operation === 'edit' && $get('edit_password'))
                            )
                            ->dehydrated(fn ($state) => filled($state))
                            ->dehydrateStateUsing(fn ($state) => Hash::make($state))
                            ->minLength(8)
                            ->visible(fn (string $operation, Forms\Get $get): bool => 
                                $operation === 'create' || ($operation === 'edit' && $get('edit_password'))
                            ),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('email')
                    ->searchable()
                    ->sortable()
                    ->copyable(),
                Tables\Columns\TextColumn::make('phone')
                    ->searchable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('godowns_count')
                    ->label('Sites Count')
                    ->counts('godowns')
                    ->sortable(),
                Tables\Columns\TextColumn::make('godowns.name')
                    ->label('Sites')
                    ->badge()
                    ->separator(',')
                    ->limit(3)
                    ->tooltip(fn ($record) => $record->godowns->pluck('name')->join(', ')),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\Filter::make('has_sites')
                    ->label('Has Sites')
                    ->query(fn (Builder $query): Builder => $query->has('godowns')),
                Tables\Filters\Filter::make('no_sites')
                    ->label('No Sites')
                    ->query(fn (Builder $query): Builder => $query->doesntHave('godowns')),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
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
            'index' => Pages\ListSiteIncharges::route('/'),
            'create' => Pages\CreateSiteIncharge::route('/create'),
            'view' => Pages\ViewSiteIncharge::route('/{record}'),
            'edit' => Pages\EditSiteIncharge::route('/{record}/edit'),
        ];
    }

    public static function canViewAny(): bool
    {
        return auth()->user()?->isAdmin() ?? false;
    }
}

