<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Membership extends Model
{
    use HasFactory, HasUuids;
    
    protected $fillable = [
        'workshop_id',
        'name',
        'description',
        'discount_percentage',
        'points_multiplier',
        'price',
        'duration_months',
        'is_active',
        'benefits',
    ];
    
    protected $casts = [
        'discount_percentage' => 'decimal:2',
        'points_multiplier' => 'decimal:2',
        'price' => 'decimal:2',
        'is_active' => 'boolean',
        'benefits' => 'array',
    ];
    
    /**
     * Get the workshop that owns the membership.
     */
    public function workshop(): BelongsTo
    {
        return $this->belongsTo(Workshop::class);
    }
    
    /**
     * Get all customer memberships for this membership plan.
     */
    public function customerMemberships(): HasMany
    {
        return $this->hasMany(CustomerMembership::class);
    }
    
    /**
     * Scope a query to only include active memberships.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
