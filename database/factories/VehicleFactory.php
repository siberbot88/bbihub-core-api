<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Vehicle>
 */
class VehicleFactory extends Factory
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
            'plate_number'  => strtoupper($this->faker->bothify('?? #### ??')),
            'type'          => $this->faker->randomElement(['motor', 'mobil', 'truck']),
            'category'      => $this->faker->randomElement(['mobil', 'motor', 'truck']),
            'brand'         => $this->faker->randomElement(['Toyota', 'Honda', 'Suzuki', 'Mitsubishi']),
            'model'         => $this->faker->word(),
            'name'          => $this->faker->word(),
            'year'          => $this->faker->year(),
            'color'         => $this->faker->colorName(),
            'odometer'      => $this->faker->numberBetween(1000, 100000),
            'code'          => 'VH-' . strtoupper($this->faker->bothify('???-###')),
        ];
    }
}
