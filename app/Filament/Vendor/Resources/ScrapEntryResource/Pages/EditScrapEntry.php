<?php

namespace App\Filament\Vendor\Resources\ScrapEntryResource\Pages;

use App\Filament\Vendor\Resources\ScrapEntryResource;
use App\Models\ScrapType;
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

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // Calculate estimated_value if scrap_type_id and amount_mt are provided
        if (isset($data['scrap_type_id']) && isset($data['amount_mt'])) {
            $scrapType = ScrapType::find($data['scrap_type_id']);
            if ($scrapType) {
                $data['estimated_value'] = $data['amount_mt'] * $scrapType->unit_price_per_ton;
            }
        }

        return $data;
    }
}

