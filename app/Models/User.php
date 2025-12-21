<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;


/**
 * Implement MustVerifyEmail so Laravel knows this user must verify email.
 */
use \Illuminate\Contracts\Auth\MustVerifyEmail;

class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<UserFactory> */
    use HasUuids, HasFactory, Notifiable, HasRoles, HasApiTokens;

    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';
    protected string $guard_name = 'sanctum';

    /**
     * Jangan pakai $guarded dan $fillable sekaligus.
     * Pilih salah satu. Kita pakai $fillable biar aman.
     */
    protected $fillable = [
        'id',
        'name',
        'username',
        'email',
        'password',
        'phone',
        'photo',
        'trial_ends_at',
        'trial_used',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            // Laravel 10+: password akan otomatis di-hash saat set
            'password' => 'hashed',
            'trial_ends_at' => 'datetime',
            'trial_used' => 'boolean',
        ];
    }

    /**
     * ✅ FIX UTAMA:
     * Pastikan username tidak pernah kosong saat insert,
     * meskipun ada kode lain yang User::create() tanpa username.
     */
    protected static function booted(): void
    {
        static::creating(function (User $user) {
            // Normalisasi email
            if (!empty($user->email)) {
                $user->email = Str::lower(trim((string) $user->email));
            }

            // Auto-generate username jika kosong
            if (blank($user->username)) {
                $base = Str::slug($user->name ?: 'user');
                $candidate = $base;
                $i = 1;

                while (static::where('username', $candidate)->exists()) {
                    $candidate = $base . '-' . $i;
                    $i++;

                    if ($i > 2000) {
                        $candidate = $base . '-' . Str::lower(Str::random(6));
                        break;
                    }
                }

                $user->username = $candidate;
            } else {
                // rapihkan username yang diinput
                $user->username = trim((string) $user->username);
            }
        });
    }

    // ==========================
    // RELATIONS
    // ==========================

    /**
     * Owner: semua workshop yang dimiliki owner ini.
     */
    public function workshops(): HasMany
    {
        return $this->hasMany(Workshop::class, 'user_uuid', 'id');
    }

    /**
     * Primary/Latest workshop (jika butuh single).
     */
    public function workshop(): HasOne
    {
        return $this->hasOne(Workshop::class, 'user_uuid', 'id')->latest();
    }

    /**
     * Karyawan: satu data employment.
     */
    public function employment(): HasOne
    {
        return $this->hasOne(Employment::class, 'user_uuid', 'id');
    }

    /**
     * Semua employee dari workshop (jika user ini owner/manager workshop tertentu).
     * NOTE: relasi ini agak tricky secara konsep, tapi aku biarkan sesuai struktur kamu.
     */
    public function employees(): HasManyThrough
    {
        return $this->hasManyThrough(
            User::class,
            Employment::class,
            'workshop_uuid', // FK di employments yang menunjuk workshop
            'id',            // FK di users (target key)
            'id',            // local key user
            'user_uuid'      // FK di employments yang menunjuk user
        );
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class, 'user_uuid', 'id');
    }

    public function logs(): HasMany
    {
        return $this->hasMany(ServiceLog::class, 'mechanic_uuid', 'id');
    }

    public function notifications(): HasMany
    {
        return $this->hasMany(Notification::class, 'mechanic_uuid', 'id');
    }

    /**
     * Subscription owner.
     */
    public function ownerSubscription(): HasOne
    {
        return $this->hasOne(OwnerSubscription::class, 'user_id', 'id')->latestOfMany('created_at');
    }

    // Trial & Premium Access Helpers

    public function isInTrial(): bool
    {
        return $this->trial_ends_at && $this->trial_ends_at->isFuture();
    }

    public function hasPremiumAccess(): bool
    {
        $subscription = $this->ownerSubscription;
        if ($subscription && $subscription->status === 'active') {
            return true;
        }
        return $this->isInTrial();
    }

    public function getSubscriptionStatus(): ?string
    {
        $subscription = $this->ownerSubscription;
        if ($subscription) {
            return $subscription->status;
        }
        if ($this->isInTrial()) {
            return 'trial';
        }
        if ($this->trial_used && !$this->isInTrial()) {
            return 'expired';
        }
        return null;
    }

    public function trialDaysRemaining(): int
    {
        if (!$this->isInTrial()) {
            return 0;
        }
        return (int) now()->diffInDays($this->trial_ends_at, false);
    }
}
