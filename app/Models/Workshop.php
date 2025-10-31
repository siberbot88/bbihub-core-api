<?php

namespace App\Models;

use Database\Factories\WorkshopFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Workshop extends Model
{
    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';

    /** @use HasFactory<WorkshopFactory> */
    use HasFactory, HasUuids;
    protected $fillable = [
        'id',
        'user_uuid',
        'code',
        'name',
        'description',
        'address',
        'phone',
        'email',
        'photo',
        'city',
        'province',
        'country',
        'postal_code',
        'latitude',
        'longitude',
        'maps_url',
        'opening_time',
        'closing_time',
        'operational_days',
        'is_active',
    ];


    // Relasi ke tabel
    public function owner(): BelongsTo{
        return $this->belongsTo(User::class, 'user_uuid');
    }

    public function document(): HasOne{
        return $this->hasOne(WorkshopDocument::class, 'workshop_uuid');
    }

    public function services(): HasMany{
        return $this->hasMany(Service::class, 'workshop_uuid');
    }

    public function employees(): HasMany{
        return $this->hasMany(Employment::class, 'workshop_uuid');
    }

    public function transactions(): HasMany{
        return $this->hasMany(Transaction::class, 'workshop_uuid');
    }

    public function vouchers():HasMany{
        return $this->hasMany(Voucher::class, 'workshop_uuid');
    }

    public function reports(): HasMany{
        return $this->hasMany(Report::class, 'workshop_uuid');
    }
}
