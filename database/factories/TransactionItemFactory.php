<?php

namespace Database\Factories;

use App\Models\TransactionItem;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TransactionItem>
 */
class TransactionItemFactory extends Factory
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
            'name'             => $this->faker->words(3, true),
            'service_type'     => $this->faker->randomElement(['service', 'sparepart']),
            'price'            => $this->faker->numberBetween(50000, 500000),
            'quantity'         => $this->faker->numberBetween(1, 5),
            'subtotal'         => function (array $attributes) {
                return $attributes['price'] * $attributes['quantity'];
            },
        ];
    }
}
