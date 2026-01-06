<?php

namespace App\Filament\Resources\SiteInchargeResource\Pages;

use App\Filament\Resources\SiteInchargeResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Hash;

class EditSiteIncharge extends EditRecord
{
    protected static string $resource = SiteInchargeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // Password is already hashed by the form's dehydrateStateUsing if filled
        // If not filled, it won't be in the data due to dehydrated() callback
        if (!isset($data['password']) || !filled($data['password'])) {
            unset($data['password']);
        }
        
        return $data;
    }
}

