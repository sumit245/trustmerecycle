<?php

namespace App\Filament\Vendor\Resources\ScrapEntryResource\Pages;

use App\Filament\Vendor\Resources\ScrapEntryResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewScrapEntry extends ViewRecord
{
    protected static string $resource = ScrapEntryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}

