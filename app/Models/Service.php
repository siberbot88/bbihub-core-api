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
    ];

    public function logs()     { return $this->hasMany(\App\Models\ServiceLog::class, 'service_uuid'); }
    public function mechanic() { return $this->belongsTo(\App\Models\User::class, 'mechanic_uuid'); }
    public function customer() { return $this->belongsTo(\App\Models\User::class, 'customer_uuid'); }
    public function vehicle()  { return $this->belongsTo(\App\Models\Vehicle::class, 'vehicle_uuid'); }

    public function workshop(): BelongsTo{
        return $this->belongsTo(Workshop::class, 'workshop_uuid');
    }

    public function items(): HasMany{
        return $this->hasMany(TransactionItem::class, 'service_uuid');
    }

    public function log(): HasMany{
        return $this->hasMany(ServiceLog::class, 'service_uuid', 'uuid');
    }

    public function task(): HasOne{
        return $this->hasOne(Task::class, 'transaction_uuid', 'uuid');



    }
}
