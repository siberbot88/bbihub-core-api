<?php

namespace Database\Factories;

use App\Models\Workshop;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Workshop>
 */
class WorkshopFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_uuid'       => \App\Models\User::factory(),
            'name'            => fake()->company() . ' Workshop',
            'description'     => fake()->paragraph(),
            'address'         => fake()->streetAddress(),
            'phone'           => fake()->phoneNumber(),
            'email'           => fake()->unique()->companyEmail(),
            'city'            => fake()->city(),
            'province'        => fake()->state(),
            'country'         => 'Indonesia',
            'postal_code'     => fake()->postcode(),
            'latitude'        => fake()->latitude(-10, 5),
            'longitude'       => fake()->longitude(95, 140),
            'opening_time'    => '08:00:00',
            'closing_time'    => '17:00:00',
            'operational_days'=> 'Senin-Jumat',
            'is_active'       => true,
        ];
    }
}
