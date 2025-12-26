<?php

namespace App\Filament\Resources\ScrapEntryResource\Pages;

use App\Filament\Resources\ScrapEntryResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditScrapEntry extends EditRecord
{
    protected static string $resource = ScrapEntryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}

