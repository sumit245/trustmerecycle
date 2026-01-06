<?php

namespace App\Filament\Resources\SiteInchargeResource\Pages;

use App\Filament\Resources\SiteInchargeResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewSiteIncharge extends ViewRecord
{
    protected static string $resource = SiteInchargeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}

