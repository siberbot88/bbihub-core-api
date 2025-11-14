<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\Workshop;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Workshop>
 */
class WorkshopFactory extends Factory
{
    protected $model = Workshop::class;

    public function definition(): array
    {
        // koordinat dummy untuk maps_url
        $lat = $this->faker->latitude(-8.0, 6.0);
        $lng = $this->faker->longitude(95.0, 141.0);

        return [
            'id'             => Str::uuid()->toString(),
            'user_uuid'      => User::factory(),

            // Perbaikan 1 (dari error sebelumnya)
            'code'           => 'WS-' . Str::upper(Str::random(6)),

            // Perbaikan 2 (untuk error saat ini)
            'photo'          => 'https://placehold.co/600x400/D72B1C/FFFFFF?text=Bengkel',

            'name'           => $this->faker->company(),
            'description'    => $this->faker->sentence(),
            'address'        => $this->faker->address(),
            'phone'          => $this->faker->phoneNumber(),
            'email'          => $this->faker->unique()->companyEmail(),
            'city'           => $this->faker->city(),
            'province'       => $this->faker->state(),
            'country'        => 'Indonesia',
            'postal_code'    => $this->faker->postcode(),
            'latitude'       => $lat,
            'longitude'      => $lng,
            'maps_url'       => "https://maps.google.com/?q={$lat},{$lng}",
            'opening_time'   => '08:00',
            'closing_time'   => '17:00',
            'operational_days' => 'Senin-Sabtu',
            'created_at'     => now(),
            'updated_at'     => now(),
        ];
    }
}
