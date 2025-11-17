<?php

namespace Database\Factories;

use App\Models\Workshop;
use App\Models\WorkshopDocument;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\WorkshopDocument>
 */
class WorkshopDocumentFactory extends Factory
{
    /**
     * @var string
     */
    protected $model = WorkshopDocument::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $nib = $this->faker->numerify('#############');
        $npwp = $this->faker->numerify('###############');

        return [
            'workshop_uuid' => Workshop::factory(),

            'nib' => $nib,
            'npwp' => $npwp,
        ];
    }
}
