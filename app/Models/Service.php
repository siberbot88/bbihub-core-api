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
        'price',
        'scheduled_date',
        'estimated_time',
        'status',
        'acceptance_status',
        'customer_uuid',
        'vehicle_uuid',
        'mechanic_uuid'
    ];

    protected $casts = [
        'price'          => 'decimal:2',
        'scheduled_date' => 'date',
        'estimated_time' => 'date',
    ];


    public function workshop(): BelongsTo{
        return $this->belongsTo(Workshop::class, 'workshop_uuid');
    }

    public function log(): HasOne{
        return $this->hasOne(ServiceLog::class, 'service_uuid', 'uuid');
    }

    public function task(): HasOne{
        return $this->hasOne(Task::class, 'transaction_uuid', 'uuid');
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

}
