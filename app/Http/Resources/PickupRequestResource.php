<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PickupRequestResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $job = $this->whenLoaded('collectionJob');

        return [
            'id' => $this->id,
            'status' => $this->status,
            'customer_name' => $this->customer_name,
            'customer_email' => $this->customer_email,
            'customer_phone' => $this->customer_phone,
            'pickup_address' => $this->pickup_address,
            'location_notes' => $this->location_notes,
            'scrap_type' => $this->scrapType?->name,
            'scrap_description' => $this->scrap_description,
            'estimated_weight_mt' => $this->estimated_weight_mt,
            'preferred_pickup_date' => $this->preferred_pickup_date?->toDateString(),
            'notes' => $this->notes,
            'requested_at' => $this->requested_at?->toIso8601String(),
            'picked_up_at' => $this->picked_up_at?->toIso8601String(),
            'assigned_vendor_name' => $this->assignedVendor?->name,
            'assigned_godown_name' => $this->assignedGodown?->name,
            'collection_job' => $job ? [
                'id' => $job->id,
                'status' => $job->status,
                'collected_amount_mt' => $job->collected_amount_mt,
                'dispatched_at' => $job->dispatched_at?->toIso8601String(),
                'collected_at' => $job->collected_at?->toIso8601String(),
            ] : null,
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
