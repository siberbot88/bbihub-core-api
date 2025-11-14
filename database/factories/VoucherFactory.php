<?php

namespace Database\Factories;

use App\Models\Workshop; // <-- PENTING: import Workshop
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Voucher>
 */
class VoucherFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            // INI ADALAH PERBAIKANNYA:
            // Ini memanggil WorkshopFactory Anda untuk membuat workshop
            // dan mengambil ID-nya secara otomatis.
            'workshop_uuid' => Workshop::factory(),

            // Data palsu lainnya
            'code_voucher' => $this->faker->unique()->bothify('PROMO-????##'),
            'title' => $this->faker->sentence(3),
            'discount_value' => $this->faker->numberBetween(10000, 100000),
            'quota' => $this->faker->numberBetween(50, 200),
            'min_transaction' => $this->faker->numberBetween(50000, 200000),
            'valid_from' => now(),
            'valid_until' => now()->addMonths(1),
            'is_active' => true,
            'image' => null,
        ];
    }
}
