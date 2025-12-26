<?php

namespace App\Filament\Resources\CollectionJobResource\Pages;

use App\Filament\Resources\CollectionJobResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewCollectionJob extends ViewRecord
{
    protected static string $resource = CollectionJobResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}

