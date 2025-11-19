<?php

namespace App\Models;

use App\Models\User;
use App\Models\Workshop;
use Database\Factories\VoucherFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class Voucher extends Model
{
    /** @use HasFactory<VoucherFactory> */
    use HasFactory, HasUuids;

    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';

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
        'discount_value'   => 'decimal:2',
        'min_transaction'  => 'decimal:2',
        'quota'            => 'integer',
        'is_active'        => 'boolean',
        'valid_from'       => 'date',
        'valid_until'      => 'date',
    ];

    // biar otomatis muncul di JSON/API
    protected $appends = [
        'image_url',
        'status',
    ];

    /**
     * Relasi ke Workshop.
     *
     * NOTE: workshop_uuid merefer ke kolom 'uuid' di tabel workshops
     * (rules kamu: exists:workshops,uuid), jadi localKey harus 'uuid', bukan 'id'.
     */
    public function workshop(): BelongsTo
    {
        return $this->belongsTo(Workshop::class, 'workshop_uuid', 'id');
    }

    /**
     * Accessor image_url.
     */
    public function getImageUrlAttribute(): ?string
    {
        if (! $this->image) {
            return null;
        }

        return Storage::disk('public')->url($this->image);
    }


    /**
     * Attribute status voucher (active | expired | inactive | scheduled)
     * berdasarkan is_active + valid_from + valid_until
     */
    public function getStatusAttribute(): string
    {
        $now = now();

        if (! $this->is_active) {
            return 'inactive';
        }

        if ($this->valid_from && $this->valid_from->isFuture()) {
            return 'scheduled';
        }

        if ($this->valid_until && $this->valid_until->isPast()) {
            return 'expired';
        }

        return 'active';
    }

    /**
     * Scope filter status voucher.
     *
     * status: active | expired | inactive | scheduled
     */
    public function scopeStatus(Builder $query, ?string $status): Builder
    {
        if (! $status) {
            return $query;
        }

        $now = now();

        return match ($status) {
            'active'    => $query->where('is_active', true)
                ->where('valid_from', '<=', $now)
                ->where('valid_until', '>=', $now),
            'expired'   => $query->where('valid_until', '<', $now),
            'inactive'  => $query->where('is_active', false),
            'scheduled' => $query->where('valid_from', '>', $now),
            default     => $query,
        };
    }

    /**
     * Scope: batasi voucher sesuai user & role-nya.
     *
     * - superadmin: semua voucher
     * - owner: semua voucher di workshop yang dia miliki
     * - admin: voucher hanya di workshop tempat dia bekerja
     */
    public function scopeForUser(Builder $query, User $user): Builder
    {
        if ($user->hasRole('superadmin')) {
            return $query;
        }

        if ($user->hasRole('owner')) {
            $workshopUuids = $user->workshops()->pluck('id');

            return $query->whereIn('workshop_uuid', $workshopUuids);
        }

        if ($user->hasRole('admin')) {
            $employment = $user->employment;

            if (! $employment) {
                return $query->whereRaw('1 = 0');
            }

            return $query->where('workshop_uuid', $employment->workshop_uuid);
        }

        // role lain: blok semua
        return $query->whereRaw('1 = 0');
    }
}
