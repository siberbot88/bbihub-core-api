<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Vehicle extends Model
{
    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';

    /** @use HasFactory<\Database\Factories\VehicleFactory> */
    use HasFactory, HasUuids;

    protected $fillable = [
        'id',
        'customer_uuid',
        'code',
        'name',
        'type',
        'category',
        'brand',
        'model',
        'year',
        'color',
        'plate_number',
        'odometer',
    ];

    public function customer(): BelongsTo{
        return $this->belongsTo(Customer::class, 'customer_uuid','id');
    }

    public function services(): HasMany{
        return $this->hasMany(Service::class, 'vehicle_uuid','id');
    }
}
