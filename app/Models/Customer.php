<?php

namespace App\Models;

use Database\Factories\CustomerFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Customer extends Model
{

    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';

    /** @use HasFactory<CustomerFactory> */
    use HasFactory, HasUuids;
    protected $fillable = [
        'id',
        'code',
        'name',
        'phone',
        'address',
    ];


    public function transactions(): HasMany{
        return $this->hasMany(Transaction::class, 'customer_uuid');
    }

    public function vehicles(): HasMany{
        return $this->hasMany(Vehicle::class, 'customer_uuid','id');
    }

    public function services(): HasMany{
        return $this->hasMany(Service::class, 'customer_uuid');
    }
}
