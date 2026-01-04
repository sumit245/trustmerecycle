<?php

namespace App\Filament\Resources\ScrapEntryResource\Pages;

use App\Filament\Resources\ScrapEntryResource;
use App\Models\ScrapType;
use Filament\Resources\Pages\CreateRecord;

class CreateScrapEntry extends CreateRecord
{
    protected static string $resource = ScrapEntryResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
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

