<?php

namespace App\Filament\Resources\GodownResource\Pages;

use App\Filament\Resources\GodownResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditGodown extends EditRecord
{
    protected static string $resource = GodownResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}

