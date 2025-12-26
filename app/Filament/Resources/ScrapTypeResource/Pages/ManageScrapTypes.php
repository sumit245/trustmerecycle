<?php

namespace App\Filament\Resources\ScrapTypeResource\Pages;

use App\Filament\Resources\ScrapTypeResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageScrapTypes extends ManageRecords
{
    protected static string $resource = ScrapTypeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}

