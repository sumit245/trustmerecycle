@extends('layouts.vendor')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="bg-white rounded-lg shadow p-6">
        <h2 class="text-2xl font-bold text-gray-900">Vendor Dashboard</h2>
        <p class="text-gray-600 mt-1">Welcome back, {{ Auth::user()->name }}</p>
    </div>

    <!-- Stock Card -->
    @include('vendor.partials.stock-card', ['godown' => $godown])

    <!-- Add Scrap Form -->
    <div class="bg-white rounded-lg shadow p-6" x-data="{ open: false }">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-semibold text-gray-900">Add Scrap Entry</h3>
            <button @click="open = !open" class="bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-4 rounded">
                <span x-show="!open">Add Scrap</span>
                <span x-show="open">Close</span>
            </button>
        </div>

        <div x-show="open" x-transition class="mt-4">
            <form action="{{ route('vendor.scrap.add') }}" method="POST" class="space-y-4">
                @csrf
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Scrap Type</label>
                        <select name="scrap_type_id" required class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="">Select Type</option>
                            @foreach(\App\Models\ScrapType::active()->get() as $type)
                                <option value="{{ $type->id }}">{{ $type->name }} - ₹{{ number_format($type->unit_price_per_ton, 2) }}/ton</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Date</label>
                        <input type="date" name="date" value="{{ date('Y-m-d') }}" required class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Amount (Metric Tons)</label>
                        <input type="number" name="amount_mt" step="0.01" min="0.01" required placeholder="0.00" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Notes (Optional)</label>
                        <textarea name="notes" rows="2" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"></textarea>
                    </div>
                </div>
                <button type="submit" class="bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-6 rounded">
                    Submit Entry
                </button>
            </form>
        </div>
    </div>

    <!-- Collection Jobs -->
    @if($pendingJobs->count() > 0)
        @foreach($pendingJobs as $job)
            @include('vendor.partials.job-card', ['job' => $job])
        @endforeach
    @endif

    <!-- Financial Chart -->
    @include('vendor.partials.financial-chart', ['chartData' => $chartData])
</div>
@endsection

