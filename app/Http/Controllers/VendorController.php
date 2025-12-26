<?php

namespace App\Http\Controllers;

use App\Models\CollectionJob;
use App\Models\Godown;
use App\Models\ScrapEntry;
use App\Models\ScrapType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class VendorController extends Controller
{
    /**
     * Display the vendor dashboard.
     */
    public function dashboard()
    {
        $user = Auth::user();
        $godown = $user->godowns()->first();
        
        if (!$godown) {
            return redirect()->route('vendor.dashboard')->with('error', 'No godown assigned to your account.');
        }

        // Get pending collection jobs
        $pendingJobs = CollectionJob::where('godown_id', $godown->id)
            ->whereIn('status', ['pending', 'truck_dispatched'])
            ->latest()
            ->get();

        // Get last 30 days of scrap entries for chart
        $entries = ScrapEntry::where('godown_id', $godown->id)
            ->where('date', '>=', now()->subDays(30))
            ->orderBy('date')
            ->get();

        // Prepare chart data
        $chartData = [
            'labels' => $entries->pluck('date')->map(fn($date) => $date->format('M d'))->toArray(),
            'values' => $entries->pluck('estimated_value')->toArray(),
        ];

        return view('vendor.dashboard', compact('godown', 'pendingJobs', 'chartData'));
    }

    /**
     * Store a new scrap entry.
     */
    public function storeEntry(Request $request)
    {
        $user = Auth::user();
        $godown = $user->godowns()->first();

        if (!$godown) {
            return back()->with('error', 'No godown assigned to your account.');
        }

        $validator = Validator::make($request->all(), [
            'scrap_type_id' => 'required|exists:scrap_types,id',
            'date' => 'required|date',
            'amount_mt' => 'required|numeric|min:0.01',
            'notes' => 'nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $scrapType = ScrapType::findOrFail($request->scrap_type_id);
        $estimatedValue = $request->amount_mt * $scrapType->unit_price_per_ton;

        ScrapEntry::create([
            'godown_id' => $godown->id,
            'scrap_type_id' => $request->scrap_type_id,
            'date' => $request->date,
            'amount_mt' => $request->amount_mt,
            'estimated_value' => $estimatedValue,
            'notes' => $request->notes,
        ]);

        return back()->with('success', 'Scrap entry added successfully!');
    }

    /**
     * Complete a collection job.
     */
    public function completeJob(Request $request, CollectionJob $job)
    {
        $user = Auth::user();
        $godown = $user->godowns()->first();

        if (!$godown || $job->godown_id !== $godown->id) {
            return back()->with('error', 'Unauthorized access.');
        }

        if (!$job->isDispatched()) {
            return back()->with('error', 'This job is not in dispatched status.');
        }

        $validator = Validator::make($request->all(), [
            'collection_proof_image' => 'required|image|mimes:jpeg,png,jpg|max:5120',
            'collected_amount_mt' => 'required|numeric|min:0.01|max:' . $godown->current_stock_mt,
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        // Store the proof image
        $imagePath = $request->file('collection_proof_image')->store('proofs', 'public');

        // Mark job as completed and reduce stock
        $job->markCompleted($request->collected_amount_mt, $imagePath);
        $godown->reduceStock($request->collected_amount_mt);

        return back()->with('success', 'Collection job completed successfully!');
    }
}

