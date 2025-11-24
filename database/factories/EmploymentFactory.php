<?php

namespace Database\Factories;

use App\Models\Employment;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Employment>
 */
class EmploymentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_uuid' => \App\Models\User::factory(),
            'workshop_uuid' => \App\Models\Workshop::factory(),
            'code' => 'EMP-' . strtoupper($this->faker->bothify('???-###')),
            'specialist' => $this->faker->randomElement(['Mekanik Umum', 'Spesialis AC', 'Spesialis Mesin', 'Elektrikal']),
            'jobdesk' => $this->faker->sentence(),
            'status' => 'active',
        ];
    }
}
