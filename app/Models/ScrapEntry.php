<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ScrapEntry extends Model
{
    use HasFactory;

    protected $fillable = [
        'godown_id',
        'scrap_type_id',
        'date',
        'amount_mt',
        'estimated_value',
        'notes',
    ];

    protected $casts = [
        'date' => 'date',
        'amount_mt' => 'decimal:2',
        'estimated_value' => 'decimal:2',
    ];

    /**
     * Get the godown that owns the scrap entry.
     */
    public function godown(): BelongsTo
    {
        return $this->belongsTo(Godown::class);
    }

    /**
     * Get the scrap type for this entry.
     */
    public function scrapType(): BelongsTo
    {
        return $this->belongsTo(ScrapType::class);
    }
}

