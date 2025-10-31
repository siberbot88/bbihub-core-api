<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasUuids, HasFactory, Notifiable, HasRoles, HasApiTokens;

    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';
    protected string $guard_name = 'web';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'id',
        'name',
        'username',
        'email',
        'password',
        'photo',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }


    /**
     * Relasi untuk Owner: Mendapatkan SEMUA workshop yang dimiliki Owner ini.
     */
    public function workshops(): HasMany{
        return $this->hasMany(Workshop::class, 'user_uuid', 'id');
    }

    /**
     * Relasi untuk Karyawan: Mendapatkan SATU data kepegawaian
     */
    public function employment(): HasOne
    {
        return $this->hasOne(Employment::class, 'user_uuid');
    }

     public function employees(): HasManyThrough
     {
         return $this->hasManyThrough(
             User::class,
             Employment::class,
             'workshop_uuid',
             'id',
             'id',
             'user_uuid'
         );
     }


    public function transactions(): HasMany{
        return $this->hasMany(Transaction::class, 'user_uuid');
    }

    public function logs(): HasMany{
        return $this->HasMany(ServiceLog::class, 'mechanic_uuid', 'id');
    }

    public function notifications(): HasMany{
        return $this->HasMany(Notification::class, 'mechanic_uuid', 'id');
    }
}

