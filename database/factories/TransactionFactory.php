<?php

namespace Database\Factories;

use App\Models\Transaction;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Transaction>
 */
class TransactionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'customer_uuid' => \App\Models\Customer::factory(),
            'workshop_uuid' => \App\Models\Workshop::factory(),
            'admin_uuid'    => \App\Models\User::factory(),
            'mechanic_uuid' => \App\Models\User::factory(),
            'service_uuid'  => \App\Models\Service::factory(),
            'status'        => $this->faker->randomElement(['pending', 'paid', 'cancelled']),
            'amount'        => $this->faker->numberBetween(100000, 1000000),
            'payment_method'=> $this->faker->randomElement(['cash', 'transfer', 'qris']),
        ];
    }
}
