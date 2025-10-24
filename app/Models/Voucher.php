<?php

namespace App\Models;

use Database\Factories\VoucherFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Voucher extends Model
{
    /** @use HasFactory<VoucherFactory> */
    use HasFactory, HasUuids;
    protected $fillable = [
        'code_voucher',
        'workshop_uuid',
        'title',
        'description',
        'discount_type',
        'discount_value',
        'quota',
        'min_transaction',
        'valid_from',
        'valid_until',
        'is_active',
    ];

    public function workshop(): BelongsTo{
        return $this->belongsTo(Workshop::class, 'workshop_uuid', 'uuid');
    }
}
