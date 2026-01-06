<div class="bg-white rounded-lg shadow p-6">
    <h3 class="text-lg font-semibold text-gray-900 mb-4">Current Stock</h3>
    
    <div class="space-y-4">
        <div>
            <div class="flex justify-between text-sm mb-2">
                <span class="text-gray-600">Stock Level</span>
                <span class="font-semibold {{ $godown->stock_percentage >= 80 ? 'text-red-600' : ($godown->stock_percentage >= 60 ? 'text-yellow-600' : 'text-green-600') }}">
                    {{ number_format($godown->current_stock_mt, 2) }} MT / {{ number_format($godown->capacity_limit_mt, 2) }} MT
                </span>
            </div>
            <div class="w-full bg-gray-200 rounded-full h-4">
                <div class="h-4 rounded-full {{ $godown->stock_percentage >= 80 ? 'bg-red-600' : ($godown->stock_percentage >= 60 ? 'bg-yellow-500' : 'bg-green-500') }}" 
                     style="width: {{ min($godown->stock_percentage, 100) }}%"></div>
            </div>
            <p class="text-xs text-gray-500 mt-1">{{ number_format($godown->stock_percentage, 1) }}% capacity</p>
        </div>
        
        <div class="grid grid-cols-2 gap-4 pt-4 border-t">
            <div>
                <p class="text-sm text-gray-600">Site Name</p>
                <p class="font-semibold">{{ $godown->name }}</p>
            </div>
            <div>
                <p class="text-sm text-gray-600">Location</p>
                <p class="font-semibold">{{ $godown->location }}</p>
            </div>
        </div>
    </div>
</div>

