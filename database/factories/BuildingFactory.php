<?php

namespace Database\Factories;

use App\Models\Campus;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Building>
 */
class BuildingFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'campus_id' => Campus::factory(),
            'name' => $this->faker->streetName() . ' Building',
            'code' => strtoupper($this->faker->lexify('???')),
            'is_active' => true,
        ];
    }
}