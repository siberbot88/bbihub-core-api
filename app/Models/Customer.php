<?php

namespace App\Models;

use Database\Factories\CustomerFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

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
        'email',
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

    /**
     * Get the customer's active membership.
     */
    public function activeMembership(): HasOne
    {
        return $this->hasOne(CustomerMembership::class)
            ->where('status', 'active')
            ->where('expires_at', '>', now())
            ->latest();
    }

    /**
     * Get all customer memberships.
     */
    public function memberships(): HasMany
    {
        return $this->hasMany(CustomerMembership::class);
    }

    /**
     * Check if customer has an active membership.
     */
    public function hasMembership(): bool
    {
        return $this->activeMembership()->exists();
    }

    /**
     * Get the membership discount percentage.
     */
    public function getMembershipDiscount(): float
    {
        $membership = $this->activeMembership;
        return $membership && $membership->isActive() 
            ? $membership->membership->discount_percentage 
            : 0;
    }
}
