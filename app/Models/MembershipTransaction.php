<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MembershipTransaction extends Model
{
    use HasFactory, HasUuids;
    
    protected $fillable = [
        'customer_membership_id',
        'customer_id',
        'membership_id',
        'amount',
        'payment_method',
        'payment_status',
        'transaction_date',
        'paid_at',
        'midtrans_order_id',
        'midtrans_transaction_id',
        'midtrans_snap_token',
        'payment_type',
        'midtrans_response',
        'confirmed_at',
    ];
    
    protected $casts = [
        'amount' => 'decimal:2',
        'transaction_date' => 'datetime',
        'paid_at' => 'datetime',
        'confirmed_at' => 'datetime',
    ];
    
    /**
     * Get the customer membership that owns the transaction.
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
     * Get the membership plan.
     */
    public function membership(): BelongsTo
    {
        return $this->belongsTo(Membership::class);
    }
}
