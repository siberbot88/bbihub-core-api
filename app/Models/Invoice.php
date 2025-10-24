<?php

namespace App\Models;

use Database\Factories\InvoiceFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Invoice extends Model
{
    /** @use HasFactory<InvoiceFactory> */
    use HasFactory, HasUuids;

    protected $fillable = [
        'transaction_uuid',
        'code',
        'amount',
        'due_date',
        'paid_at',
    ];

    public function transaction(): BelongsTo{
        return $this->belongsTo(Transaction::class, 'transaction_uuid');
    }
}
