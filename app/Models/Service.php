<?php

namespace App\Models;

use Database\Factories\ServiceFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Service extends Model
{
    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';

    /** @use HasFactory<ServiceFactory> */
    use HasFactory, HasUuids;
    protected $fillable = [
        'id',
        'code',
        'workshop_uuid',
        'name',
        'description',
        'category_service',
        'price',
        'scheduled_date',
        'estimated_time',
        'status',
        'acceptance_status',
        'customer_uuid',
        'vehicle_uuid',
        'mechanic_uuid',
        'reason',
        'reason_description',
        'feedback_mechanic',
        'accepted_at',
        'completed_at',
    ];

    protected $casts = [
        'price'          => 'decimal:2',
        'scheduled_date' => 'date',
        'estimated_time' => 'date',
    ];


    public function workshop(): BelongsTo{
        return $this->belongsTo(Workshop::class, 'workshop_uuid');
    }

    public function items(): HasMany{
        return $this->hasMany(TransactionItem::class, 'service_uuid');
    }
    public function log(): HasOne{
        return $this->hasOne(ServiceLog::class, 'service_uuid', 'id');
    }
    public function task(): HasOne{
        return $this->hasOne(Task::class, 'transaction_uuid', 'id');
    }

    public function customer(): BelongsTo{
        return $this->belongsTo(Customer::class, 'customer_uuid');
    }

    public function vehicle(): BelongsTo{
        return $this->belongsTo(Vehicle::class, 'vehicle_uuid');
    }

    public function mechanic(): BelongsTo{
        return $this->belongsTo(Employment::class, 'mechanic_uuid')->with('user');
    }

    public function transaction(): HasOne{
        return $this->hasOne(Transaction::class, 'service_uuid', 'id');
    }
    /**
     * Dummy relationship for mobile app backward compatibility
     * Returns proper relationship that's always empty
     */
    public function extras(): HasMany
    {
        // Use existing ServiceLog model but force empty result with WHERE 1=0
        // This creates a valid relationship for eager loading without errors
        return $this->hasMany(\App\Models\ServiceLog::class, 'service_uuid', 'id')
            ->whereRaw('1 = 0');
    }

}
