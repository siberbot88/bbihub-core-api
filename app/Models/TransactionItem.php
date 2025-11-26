<?php

namespace App\Models;

use Database\Factories\TransactionItemFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TransactionItem extends Model
{
    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';

    /** @use HasFactory<TransactionItemFactory> */
    use HasFactory, HasUuids;

    protected $fillable = [
        'id',
        'transaction_uuid',
        'name',
        'service_type',
        'price',
        'quantity',
        'subtotal',
    ];

    /* ========= RELATIONSHIPS ========= */
    public function transaction(): BelongsTo{
        return $this->belongsTo(Transaction::class, 'transaction_uuid');
    }
}
