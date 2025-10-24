<?php

namespace App\Models;

use Database\Factories\CustomerFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Customer extends Model
{
    /** @use HasFactory<CustomerFactory> */
    use HasFactory, HasUuids;
    protected $table = [
        'code',
        'name',
        'phone',
        'address',
    ];


    public function transactions(): HasMany{
        return $this->hasMany(Transaction::class, 'customer_uuid');
    }
}
