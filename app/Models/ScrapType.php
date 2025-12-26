<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ScrapType extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'unit_price_per_ton',
        'description',
        'is_active',
    ];

    protected $casts = [
        'unit_price_per_ton' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    /**
     * Get all scrap entries for this type.
     */
    public function scrapEntries(): HasMany
    {
        return $this->hasMany(ScrapEntry::class);
    }

    /**
     * Scope a query to only include active scrap types.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}

