<?php

namespace App\Models;

use Database\Factories\TaskFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Task extends Model
{
    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';

    /** @use HasFactory<TaskFactory> */
    use HasFactory, HasUuids;

    protected $fillable = [
        'id',
        'transaction_uuid',
        'service_uuid',
        'status',
        'description',
        'assigned_at',
    ];


    public function transaction(): BelongsTo{
        return $this->belongsTo(Transaction::class, 'transaction_uuid', 'id');
    }

    public function service(): BelongsTo{
        return $this->belongsTo(Service::class, 'service_uuid', 'id');
    }
}
