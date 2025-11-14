<?php

namespace App\Models;

use Database\Factories\ServiceLogFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ServiceLog extends Model
{
    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';

    /** @use HasFactory<ServiceLogFactory> */
    use HasFactory, HasUuids;

    protected $fillable = [
        'id',
        'service_uuid',
        'mechanic_uuid',
        'transaction_uuid',
        'notes',
    ];

    public function service(): BelongsTo{
        return $this->belongsTo(Service::class, 'service_uuid', 'id');
    }

    public function mechanic(): BelongsTo{
        return $this->belongsTo(User::class, 'mechanic_uuid', 'id');
    }

    public function transaction(): BelongsTo{
        return $this->belongsTo(Transaction::class, 'transaction_uuid', 'id');
    }
}
