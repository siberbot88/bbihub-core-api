<?php

namespace Database\Factories;

use App\Models\Task;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Task>
 */
class TaskFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'transaction_uuid' => \App\Models\Transaction::factory(),
            'name'             => $this->faker->sentence(),
            'description'      => $this->faker->paragraph(),
            'status'           => $this->faker->randomElement(['pending', 'in_progress', 'completed']),
            'due_date'         => now()->addDays(3),
        ];
    }
}
