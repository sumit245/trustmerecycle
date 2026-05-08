<?php

namespace App\Filament\Resources\SiteInchargeResource\Pages;

use App\Filament\Resources\SiteInchargeResource;
use Filament\Actions;
use Filament\Forms;
use Filament\Resources\Pages\ViewRecord;

class ViewSiteIncharge extends ViewRecord
{
    protected static string $resource = SiteInchargeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }

    public function form(Forms\Form $form): Forms\Form
    {
        return $form->schema([
            Forms\Components\Section::make('Site Incharge Information')
                ->schema([
                    Forms\Components\TextInput::make('name')
                        ->disabled(),
                    Forms\Components\TextInput::make('email')
                        ->disabled(),
                    Forms\Components\TextInput::make('phone')
                        ->disabled(),
                ])
                ->columns(3),
        ]);
    }
}
