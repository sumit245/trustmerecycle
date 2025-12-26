<?php

namespace App\Filament\Resources\CollectionJobResource\Pages;

use App\Filament\Resources\CollectionJobResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListCollectionJobs extends ListRecords
{
    protected static string $resource = CollectionJobResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}

