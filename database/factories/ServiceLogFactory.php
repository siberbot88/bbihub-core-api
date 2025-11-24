<?php

namespace Database\Factories;

use App\Models\ServiceLog;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ServiceLog>
 */
class ServiceLogFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'service_uuid'  => \App\Models\Service::factory(),
            'mechanic_uuid' => \App\Models\User::factory(),
            'status'        => $this->faker->randomElement(['accepted', 'rejected', 'completed']),
            'log_time'      => now(),
            'description'   => $this->faker->sentence(),
        ];
    }
}
