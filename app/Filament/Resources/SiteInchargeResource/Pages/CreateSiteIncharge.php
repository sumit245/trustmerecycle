<?php

namespace App\Filament\Resources\SiteInchargeResource\Pages;

use App\Filament\Resources\SiteInchargeResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Hash;

class CreateSiteIncharge extends CreateRecord
{
    protected static string $resource = SiteInchargeResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['role'] = 'vendor';
        // Password is already hashed by the form's dehydrateStateUsing
        
        return $data;
    }
}

