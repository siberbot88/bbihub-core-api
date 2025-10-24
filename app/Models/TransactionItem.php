<?php

namespace App\Models;

use Database\Factories\TransactionItemFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TransactionItem extends Model
{
    /** @use HasFactory<TransactionItemFactory> */
    use HasFactory, HasUuids;

    protected $fillable = [
        'transaction_uuid',
        'service_uuid',
        'price',
        'quantity',
        'subtotal',
    ];

    public function service(): BelongsTo{
        return $this->belongsTo(Service::class, 'service_uuid');
    }

    public function transaction(): BelongsTo{
        return $this->belongsTo(Transaction::class, 'transaction_uuid');
    }

    public function serviceType(): BelongsTo{
        return $this->belongsTo(ServiceType::class, 'service_type_uuid');
    }
}
