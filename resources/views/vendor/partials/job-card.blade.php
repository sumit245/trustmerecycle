<div class="bg-blue-50 border-2 border-blue-200 rounded-lg shadow p-6 mb-6" x-data="{ open: false }">
    <div class="flex justify-between items-start">
        <div>
            <h3 class="text-lg font-semibold text-blue-900">Truck Dispatched</h3>
            <p class="text-sm text-blue-700 mt-1">Collection job #{{ $job->id }} - Dispatched on {{ $job->dispatched_at->format('M d, Y h:i A') }}</p>
            @if($job->truck_details)
                <div class="mt-2 text-sm text-gray-600">
                    <p><strong>Driver:</strong> {{ $job->truck_details['driver_name'] ?? 'N/A' }}</p>
                    <p><strong>Vehicle:</strong> {{ $job->truck_details['vehicle_number'] ?? 'N/A' }}</p>
                </div>
            @endif
        </div>
        <button @click="open = !open" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
            Mark Collected
        </button>
    </div>

    <div x-show="open" x-transition class="mt-4 pt-4 border-t border-blue-200">
        <form action="{{ route('vendor.job.complete', $job) }}" method="POST" enctype="multipart/form-data" class="space-y-4">
            @csrf
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Collection Scrap Image *</label>
                <input type="file" name="collection_proof_image" accept="image/jpeg,image/png,image/jpg" required class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                <p class="text-xs text-gray-500 mt-1">Upload photo of empty godown or truck loading (Max 5MB, JPG/PNG)</p>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Scrap weight *</label>
                <input type="number" name="collected_amount_mt" step="0.01" min="0.01" max="{{ $godown->current_stock_mt }}" required placeholder="0.00" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                <p class="text-xs text-gray-500 mt-1">Maximum: {{ number_format($godown->current_stock_mt, 2) }} MT</p>
            </div>
            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-6 rounded">
                Submit Proof
            </button>
        </form>
    </div>
</div>

