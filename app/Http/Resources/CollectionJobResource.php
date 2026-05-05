<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CollectionJobResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $statusMap = [
            'truck_dispatched' => 'dispatched',
            'pending'          => 'pending',
            'completed'        => 'completed',
        ];

        $truckDetails = is_array($this->truck_details) ? $this->truck_details : [];

        return [
            'id'               => $this->id,
            'status'           => $statusMap[$this->status] ?? $this->status,
            'godown_name'      => $this->godown?->name,
            'godown_address'   => $this->godown?->address,
            'godown_location'  => $this->godown?->location,
            'driver_name'      => $truckDetails['driver_name'] ?? null,
            'vehicle_number'   => $truckDetails['vehicle_number'] ?? null,
            'collected_amount_mt' => $this->collected_amount_mt,
            'dispatched_at'    => $this->dispatched_at?->toIso8601String(),
            'collected_at'     => $this->collected_at?->toIso8601String(),
            'created_at'       => $this->created_at?->toIso8601String(),
            'updated_at'       => $this->updated_at?->toIso8601String(),
        ];
    }
}
