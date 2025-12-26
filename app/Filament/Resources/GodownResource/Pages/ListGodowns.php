<?php

namespace App\Filament\Resources\GodownResource\Pages;

use App\Filament\Resources\GodownResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListGodowns extends ListRecords
{
    protected static string $resource = GodownResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}

