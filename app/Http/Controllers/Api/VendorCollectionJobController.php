<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\CollectionJobResource;
use App\Models\CollectionJob;
use App\Models\Godown;
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
        $this->ensureDailyJobsExist($request->user()->id);

        $jobs = CollectionJob::with(['godown'])
            ->whereHas('godown', fn ($q) => $q->where('vendor_id', $request->user()->id))
            ->latest()
            ->paginate(20);

        return CollectionJobResource::collection($jobs);
    }

    /**
     * Pickup is a daily task, not a one-time settlement. Make sure every site
     * this vendor runs has an open job "today" — one that is pending/dispatched,
     * or already completed today. If the only jobs on a site are completed from
     * a previous day, spawn a fresh pending job so "Mark as Picked Up" (amount +
     * photo) becomes available again without touching or overwriting the old,
     * already-completed record — nothing is reset in place, so history and
     * proof photos from previous days are never lost.
     */
    private function ensureDailyJobsExist(int $vendorId): void
    {
        $today = now()->toDateString();

        Godown::where('vendor_id', $vendorId)->get(['id'])->each(function (Godown $godown) use ($today) {
            $hasOpenJobToday = CollectionJob::where('godown_id', $godown->id)
                ->where(function ($query) use ($today) {
                    $query->whereIn('status', ['pending', 'truck_dispatched'])
                        ->orWhere(function ($query) use ($today) {
                            $query->where('status', 'completed')
                                ->whereDate('collected_at', $today);
                        });
                })
                ->exists();

            if (! $hasOpenJobToday) {
                CollectionJob::create([
                    'godown_id' => $godown->id,
                    'status' => 'pending',
                ]);
            }
        });
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
     * Mark a job as completed with proof photo. Allowed from either 'pending'
     * or 'truck_dispatched' — the mobile app shows the "Mark as Picked Up"
     * button for both statuses, so both must be completable here.
     */
    public function complete(Request $request, CollectionJob $collectionJob): JsonResponse
    {
        $this->authorizeJobAccess($request, $collectionJob);

        $request->validate([
            'collected_amount_mt' => ['required', 'numeric', 'min:0.01'],
            'proof_image'         => ['required', 'image', 'max:5120'],
        ]);

        if ($collectionJob->isCompleted()) {
            return response()->json([
                'message' => 'This job has already been completed.',
            ], 422);
        }

        $path = $request->file('proof_image')->store('collection-proofs', 'local');

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
