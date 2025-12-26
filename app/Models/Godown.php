<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Godown extends Model
{
    use HasFactory;

    protected $fillable = [
        'vendor_id',
        'name',
        'location',
        'address',
        'capacity_limit_mt',
        'current_stock_mt',
    ];

    protected $casts = [
        'capacity_limit_mt' => 'decimal:2',
        'current_stock_mt' => 'decimal:2',
    ];

    /**
     * Get the vendor that owns the godown.
     */
    public function vendor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'vendor_id');
    }

    /**
     * Get all scrap entries for this godown.
     */
    public function scrapEntries(): HasMany
    {
        return $this->hasMany(ScrapEntry::class);
    }

    /**
     * Get all collection jobs for this godown.
     */
    public function collectionJobs(): HasMany
    {
        return $this->hasMany(CollectionJob::class);
    }

    /**
     * Get the stock percentage (current stock / capacity limit * 100).
     */
    public function getStockPercentageAttribute(): float
    {
        if ($this->capacity_limit_mt == 0) {
            return 0;
        }

        return ($this->current_stock_mt / $this->capacity_limit_mt) * 100;
    }

    /**
     * Update the stock by adding the given amount.
     */
    public function updateStock(float $amount): void
    {
        $this->increment('current_stock_mt', $amount);
    }

    /**
     * Reduce the stock by the given amount.
     */
    public function reduceStock(float $amount): void
    {
        $this->decrement('current_stock_mt', $amount);
    }

    /**
     * Check if the godown has reached its capacity threshold.
     */
    public function checkThreshold(): bool
    {
        return $this->current_stock_mt >= $this->capacity_limit_mt;
    }
}

