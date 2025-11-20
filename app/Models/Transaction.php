<?php

namespace App\Models;

use Database\Factories\TransactionFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Transaction extends Model
{
    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';

    /** @use HasFactory<TransactionFactory> */
    use HasFactory, HasUuids;

    protected $fillable = [
        'id',
        'service_uuid',
        'customer_uuid',
        'workshop_uuid',
        'admin_uuid',
        'mechanic_uuid',
        'status',
        'amount',
        'payment_method',
    ];

    /* ========= RELATIONSHIPS ========= */

    /**
     * Transaksi milik satu service
     */

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class, 'service_uuid', 'id');
    }
    public function customer(): BelongsTo{
        return $this->belongsTo(Customer::class, 'customer_uuid');
    }

    public function workshop(): BelongsTo{
        return $this->belongsTo(Workshop::class, 'workshop_uuid');
    }

    public function mechanic(): BelongsTo{
        return $this->belongsTo(User::class, 'mechanic_uuid');
    }

    public function admin(): BelongsTo{
        return $this->belongsTo(User::class, 'admin_uuid');
    }

    public function items(): HasMany{
        return $this->hasMany(TransactionItem::class, 'service_uuid');
    }

    public function logs(): HasMany{
        return $this->hasMany(ServiceLog::class, 'service_uuid', 'uuid');
    }

    public function invoice(): HasOne{
        return $this->hasOne(Invoice::class, 'uuid', 'uuid');
    }

    public function task(): HasOne{
        return $this->hasOne(Task::class, 'transaction_uuid', 'uuid');
    }

    public function feedback(): HasOne{
        return $this->hasOne(Feedback::class, 'transaction_uuid', 'uuid');
    }
}
