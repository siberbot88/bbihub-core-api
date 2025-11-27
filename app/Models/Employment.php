<?php

namespace App\Models;

use Database\Factories\EmploymentFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Notifications\Notifiable;

class Employment extends Model
{
    /** @use HasFactory<EmploymentFactory> */
    use HasFactory, HasUuids, Notifiable;


    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'id',
        'user_uuid',
        'workshop_uuid',
        'code',
        'specialist',
        'jobdesk',
        'status',

    ];

    protected $casts = [
        'status' => 'string',
    ];

    public function workshop(): BelongsTo{
        return $this->belongsTo(Workshop::class, 'workshop_uuid');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_uuid');

    }

    /* ========= SCOPES BANTUAN ========= */

    // hanya employment yang status = active
    public function scopeActive($q)
    {
        return $q->where('status', 'active');
    }

    // hanya employment yang user-nya punya role mechanic
    public function scopeMechanic($q)
    {
        return $q->whereHas('user.roles', function ($r) {
            $r->where('name', 'mechanic');
        });
    }

    // semua transaksi yang di-handle mekanik ini
    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class, 'mechanic_uuid', 'id');
    }

}

