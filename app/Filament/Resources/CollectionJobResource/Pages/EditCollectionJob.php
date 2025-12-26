<?php

namespace App\Filament\Resources\CollectionJobResource\Pages;

use App\Filament\Resources\CollectionJobResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditCollectionJob extends EditRecord
{
    protected static string $resource = CollectionJobResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}

