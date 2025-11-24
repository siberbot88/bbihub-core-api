<?php

namespace Database\Factories;

use App\Models\Service;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Service>
 */
class ServiceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'workshop_uuid' => \App\Models\Workshop::factory(),
            'customer_uuid' => \App\Models\Customer::factory(),
            'vehicle_uuid' => \App\Models\Vehicle::factory(),
            'mechanic_uuid' => \App\Models\Employment::factory(),
            'code' => 'WO-' . $this->faker->unique()->numerify('###') . '-' . now()->format('Hi') . now()->format('y'),
            'name' => $this->faker->sentence(3),
            'description' => $this->faker->paragraph(),
            'status' => 'pending',
            'category_service' => $this->faker->randomElement(['ringan', 'sedang', 'berat', 'maintenance']),
            'scheduled_date' => now()->addDays(rand(1, 7)),
            'estimated_time' => now()->addDays(rand(1, 7))->addHours(2),
            'price' => $this->faker->numberBetween(100000, 1000000),
            'reason' => $this->faker->sentence(),
            'feedback_mechanic' => $this->faker->sentence(),
            'note' => $this->faker->sentence(),
        ];
    }
}
