<?php

namespace App\Models;

use Database\Factories\WorkshopFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Str;

class Workshop extends Model
{
    /** @use HasFactory<WorkshopFactory> */
    use HasFactory, HasUuids;

    protected $guarded = [];
    protected $attributes = [
        'is_active' => true,
    ];

    protected $casts = [
        'is_active' => 'boolean',
        // 'operational_days' => 'array', // Removed to treat as simple string
    ];

    /**
     * The "booted" method of the model.
     *
     * @return void
     */
    protected static function booted()
    {
        static::creating(function ($workshop) {

            if (empty($workshop->code)) {
                $workshop->code = 'BKL-' . strtoupper(Str::random(8));
            }

            if (empty($workshop->photo)) {
                $workshop->photo = 'https://placehold.co/600x400/D72B1C/FFFFFF?text='
                    . urlencode($workshop->name);
            }
        });
    }


    /**
     * Relasi ke pemilik (User)
     */
    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_uuid');
    }

    /**
     * Relasi ke dokumen (KTP, NPWP, dll)
     */
    public function document(): HasOne
    {
        return $this->hasOne(WorkshopDocument::class, 'workshop_uuid');
    }

    /**
     * Relasi ke service (Jasa)
     */
    public function services(): HasMany{
        return $this->hasMany(Service::class, 'workshop_uuid');
    }

    /**
     * Relasi ke karyawan
     */
    public function employees(): HasMany{
        return $this->hasMany(Employment::class, 'workshop_uuid');
    }

    /**
     * Relasi ke transaksi
     */
    public function transactions(): HasMany{
        return $this->hasMany(Transaction::class, 'workshop_uuid');
    }

    /**
     * Relasi ke voucher
     */
    public function vouchers():HasMany{
        return $this->hasMany(Voucher::class, 'workshop_uuid');
    }

    /**
     * Relasi ke laporan
     */
    public function reports(): HasMany{
        return $this->hasMany(Report::class, 'workshop_uuid');
    }

}
