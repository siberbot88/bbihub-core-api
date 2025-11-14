<?php

namespace App\Models;

use Database\Factories\EmploymentFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Notifications\Notifiable;

class Employment extends Model
{
    /** @use HasFactory<EmploymentFactory> */
    use HasFactory, HasUuids, Notifiable;


    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'id',
        'user_uuid',
        'workshop_uuid',
        'code',
        'specialist',
        'jobdesk',
        'status',

    ];

    protected $casts = [
        'status' => 'string',
    ];

    public function workshop(): BelongsTo{
        return $this->belongsTo(Workshop::class, 'workshop_uuid');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_uuid');
    }

    public function services(): HasOne{
        return $this->hasOne(Service::class, 'workshop_uuid');
    }

}

