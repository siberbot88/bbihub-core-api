<?php

namespace App\Models;

use Database\Factories\NotificationFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Notification extends Model
{
    /** @use HasFactory<NotificationFactory> */
    use HasFactory, HasUuids;

    protected $fillable = [
        'user_uuid',
        'title',
        'message',
        'type',
        'is_read',
    ];

    public function user(): BelongsTo{
        return $this->belongsTo(User::class, 'user_uuid');
    }
}
