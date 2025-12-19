<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Carbon\Carbon;

class CustomerMembership extends Model
{
    use HasFactory, HasUuids;
    
    protected $fillable = [
        'customer_id',
        'membership_id',
        'workshop_id',
        'status',
        'started_at',
        'expires_at',
        'auto_renew',
        'total_points',
    ];
    
    protected $casts = [
        'started_at' => 'datetime',
        'expires_at' => 'datetime',
        'auto_renew' => 'boolean',
    ];
    
    /**
     * Get the customer that owns the membership.
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
    
    /**
     * Get the workshop.
     */
    public function workshop(): BelongsTo
    {
        return $this->belongsTo(Workshop::class);
    }
    
    /**
     * Get points history for this membership.
     */
    public function pointsHistory(): HasMany
    {
        return $this->hasMany(MembershipPointsHistory::class);
    }
    
    /**
     * Get transactions for this membership.
     */
    public function transactions(): HasMany
    {
        return $this->hasMany(MembershipTransaction::class);
    }
    
    /**
     * Check if membership is active.
     */
    public function isActive(): bool
    {
        return $this->status === 'active' && $this->expires_at > now();
    }
    
    /**
     * Get days remaining until expiration.
     */
    public function daysRemaining(): int
    {
        return max(0, now()->diffInDays($this->expires_at, false));
    }
    
    /**
     * Add points to customer membership.
     */
    public function addPoints(int $points, string $description, ?string $transactionId = null): void
    {
        $this->increment('total_points', $points);
        
        $this->pointsHistory()->create([
            'customer_id' => $this->customer_id,
            'transaction_id' => $transactionId,
            'points' => $points,
            'type' => 'earned',
            'description' => $description,
        ]);
    }
    
    /**
     * Redeem points from customer membership.
     */
    public function redeemPoints(int $points, string $description): bool
    {
        if ($this->total_points < $points) {
            return false;
        }
        
        $this->decrement('total_points', $points);
        
        $this->pointsHistory()->create([
            'customer_id' => $this->customer_id,
            'points' => -$points,
            'type' => 'redeemed',
            'description' => $description,
        ]);
        
        return true;
    }
}
