<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\CollectionJobResource;
use App\Models\CollectionJob;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class VendorCollectionJobController extends Controller
{
    /**
     * List all collection jobs across the vendor's godowns.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $jobs = CollectionJob::with(['godown'])
            ->whereHas('godown', fn ($q) => $q->where('vendor_id', $request->user()->id))
            ->latest()
            ->paginate(20);

        return CollectionJobResource::collection($jobs);
    }

    /**
     * Show a single collection job.
     */
    public function show(Request $request, CollectionJob $collectionJob): CollectionJobResource
    {
        $this->authorizeJobAccess($request, $collectionJob);

        return new CollectionJobResource($collectionJob->load('godown'));
    }

    /**
     * Mark a dispatched job as completed with proof photo.
     */
    public function complete(Request $request, CollectionJob $collectionJob): JsonResponse
    {
        $this->authorizeJobAccess($request, $collectionJob);

        $request->validate([
            'collected_amount_mt' => ['required', 'numeric', 'min:0.01'],
            'proof_image'         => ['required', 'image', 'max:5120'],
        ]);

        if (! $collectionJob->isDispatched()) {
            return response()->json([
                'message' => 'Job cannot be completed — it is not in dispatched state.',
            ], 422);
        }

        $path = $request->file('proof_image')->store('collection-proofs', 'public');

        $collectionJob->markCompleted($request->collected_amount_mt, $path);

        return response()->json([
            'message' => 'Job marked as completed.',
            'job'     => $collectionJob->fresh(),
        ]);
    }

    private function authorizeJobAccess(Request $request, CollectionJob $job): void
    {
        abort_unless(
            $job->godown->vendor_id === $request->user()->id,
            403,
            'This job does not belong to your account.'
        );
    }
}
