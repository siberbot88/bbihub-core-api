<?php

namespace App\Models;

use Database\Factories\VoucherFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class Voucher extends Model
{
    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';

    /** @use HasFactory<VoucherFactory> */
    use HasFactory, HasUuids;

    protected $fillable = [
        'id',
        'code_voucher',
        'workshop_uuid',
        'title',
        'discount_value',
        'quota',
        'min_transaction',
        'valid_from',
        'valid_until',
        'is_active',
        'image',
    ];

    protected $casts = [
        'discount_value' => 'decimal:2',
        'min_transaction' => 'decimal:2',
        'quota' => 'integer',
        'is_active' => 'boolean',
        'valid_from' => 'date',
        'valid_until' => 'date',
    ];

    public function workshop(): BelongsTo{
        return $this->belongsTo(Workshop::class, 'workshop_uuid', 'id');
    }

    public function getImageUrlAttribute()
    {
        if ($this->image) {
            return Storage::disk('public')->url($this->image);
        }

        return null;
    }
}
