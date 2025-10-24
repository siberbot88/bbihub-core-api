<?php

namespace App\Models;

use Database\Factories\UserInvitationFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserInvitation extends Model
{
    /** @use HasFactory<UserInvitationFactory> */
    use HasFactory, HasUuids;

    protected $fillable = [
        'user_uuid',
        'token',
        'expired_at',
        'used',
    ];

    public function user(): BelongsTo{
        return $this->belongsTo(User::class, 'user_uuid', 'uuid');
    }
}
