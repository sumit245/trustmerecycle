<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\PickupRequestResource;
use App\Models\PickupRequest;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class CustomerPickupRequestController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $pickupRequests = PickupRequest::with([
            'scrapType',
            'assignedVendor',
            'assignedGodown',
            'collectionJob',
        ])
            ->where('user_id', $request->user()->id)
            ->latest('requested_at')
            ->paginate(20);

        return PickupRequestResource::collection($pickupRequests);
    }

    public function store(Request $request): PickupRequestResource
    {
        $user = $request->user();

        $data = $request->validate([
            'pickup_address' => ['required', 'string', 'max:2000'],
            'location_notes' => ['nullable', 'string', 'max:255'],
            'scrap_type_id' => ['nullable', 'exists:scrap_types,id'],
            'scrap_description' => ['required', 'string', 'max:255'],
            'estimated_weight_mt' => ['nullable', 'numeric', 'min:0.01', 'max:999999.99'],
            'preferred_pickup_date' => ['nullable', 'date', 'after_or_equal:today'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);

        $pickupRequest = PickupRequest::create([
            ...$data,
            'user_id' => $user->id,
            'customer_name' => $user->name,
            'customer_email' => $user->email,
            'customer_phone' => $user->phone,
            'status' => PickupRequest::STATUS_PENDING_REVIEW,
            'requested_at' => now(),
        ]);

        return new PickupRequestResource(
            $pickupRequest->load(['scrapType', 'assignedVendor', 'assignedGodown', 'collectionJob'])
        );
    }

    public function show(Request $request, PickupRequest $pickupRequest): PickupRequestResource
    {
        abort_unless(
            $pickupRequest->user_id === $request->user()->id,
            403,
            'This pickup request does not belong to your account.'
        );

        return new PickupRequestResource(
            $pickupRequest->load(['scrapType', 'assignedVendor', 'assignedGodown', 'collectionJob'])
        );
    }
}
