<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasUuids, HasFactory, Notifiable, HasRoles, HasApiTokens;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
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

    public function workshops(): HasMany{
        return $this->hasMany(Workshop::class, 'workshop_uuid');
    }

    public function employments()
    {
        return $this->hasMany(Employment::class, 'user_uuid');
    }

    public function transactions(): HasMany{
        return $this->hasMany(Transaction::class, 'workshop_uuid');
    }

    public function logs(): HasMany{
        return $this->HasMany(ServiceLog::class, 'mechanic_uuid', 'uuid');
    }

    public function notifications(): HasMany{
        return $this->HasMany(Notification::class, 'mechanic_uuid', 'uuid');
    }
}
