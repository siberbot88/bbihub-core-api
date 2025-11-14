<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    /** Model yang dipakai factory */
    protected $model = User::class;

    public function definition(): array
    {
        return [
            'id'                => Str::uuid()->toString(),
            'name'              => $this->faker->name(),
            'username'          => $this->faker->unique()->userName(),
            'email'             => $this->faker->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password'          => bcrypt('password'),
            'remember_token'    => Str::random(10),
            'photo'                 => null,
            'must_change_password'  => false,
            'password_changed_at'   => null,
        ];
    }

    /**
     * State untuk user yang email-nya belum diverifikasi (kalau perlu).
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }
}
