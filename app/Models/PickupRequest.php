<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class PickupRequest extends Model
{
    use HasFactory;

    public const STATUS_PENDING_REVIEW = 'pending_review';
    public const STATUS_ASSIGNED = 'assigned';
    public const STATUS_TRUCK_DISPATCHED = 'truck_dispatched';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_CANCELLED = 'cancelled';

    protected $fillable = [
        'user_id',
        'scrap_type_id',
        'assigned_vendor_id',
        'assigned_godown_id',
        'customer_name',
        'customer_email',
        'customer_phone',
        'pickup_address',
        'location_notes',
        'scrap_description',
        'estimated_weight_mt',
        'preferred_pickup_date',
        'notes',
        'status',
        'requested_at',
        'picked_up_at',
    ];

    protected $casts = [
        'estimated_weight_mt' => 'decimal:2',
        'preferred_pickup_date' => 'date',
        'requested_at' => 'datetime',
        'picked_up_at' => 'datetime',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function scrapType(): BelongsTo
    {
        return $this->belongsTo(ScrapType::class);
    }

    public function assignedVendor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_vendor_id');
    }

    public function assignedGodown(): BelongsTo
    {
        return $this->belongsTo(Godown::class, 'assigned_godown_id');
    }

    public function collectionJob(): HasOne
    {
        return $this->hasOne(CollectionJob::class);
    }

    public function markAssigned(Godown $godown): void
    {
        $this->update([
            'assigned_vendor_id' => $godown->vendor_id,
            'assigned_godown_id' => $godown->id,
            'status' => self::STATUS_ASSIGNED,
        ]);
    }

    public function syncFromCollectionJob(CollectionJob $job): void
    {
        if ($job->isCompleted()) {
            $this->update([
                'status' => self::STATUS_COMPLETED,
                'picked_up_at' => $job->collected_at ?? now(),
            ]);

            return;
        }

        if ($job->isDispatched()) {
            $this->update([
                'status' => self::STATUS_TRUCK_DISPATCHED,
                'picked_up_at' => null,
            ]);

            return;
        }

        $this->update([
            'status' => self::STATUS_ASSIGNED,
            'picked_up_at' => null,
        ]);
    }
}
