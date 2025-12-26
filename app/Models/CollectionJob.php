<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CollectionJob extends Model
{
    use HasFactory;

    protected $fillable = [
        'godown_id',
        'status',
        'truck_details',
        'collection_proof_image',
        'collected_amount_mt',
        'collected_at',
        'dispatched_at',
    ];

    protected $casts = [
        'truck_details' => 'array',
        'collected_amount_mt' => 'decimal:2',
        'collected_at' => 'datetime',
        'dispatched_at' => 'datetime',
    ];

    /**
     * Get the godown for this collection job.
     */
    public function godown(): BelongsTo
    {
        return $this->belongsTo(Godown::class);
    }

    /**
     * Check if the job is pending.
     */
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    /**
     * Check if the truck has been dispatched.
     */
    public function isDispatched(): bool
    {
        return $this->status === 'truck_dispatched';
    }

    /**
     * Check if the job is completed.
     */
    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    /**
     * Mark the job as completed.
     */
    public function markCompleted(float $collectedAmount, string $proofImage): void
    {
        $this->update([
            'status' => 'completed',
            'collected_amount_mt' => $collectedAmount,
            'collection_proof_image' => $proofImage,
            'collected_at' => now(),
        ]);
    }
}

