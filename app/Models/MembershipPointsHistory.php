<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MembershipPointsHistory extends Model
{
    use HasFactory, HasUuids;
    
    protected $fillable = [
        'customer_membership_id',
        'customer_id',
        'transaction_id',
        'points',
        'type',
        'description',
    ];
    
    /**
     * Get the customer membership.
     */
    public function customerMembership(): BelongsTo
    {
        return $this->belongsTo(CustomerMembership::class);
    }
    
    /**
     * Get the customer.
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }
    
    /**
     * Get the related transaction (if any).
     */
    public function transaction(): BelongsTo
    {
        return $this->belongsTo(Transaction::class);
    }
}
