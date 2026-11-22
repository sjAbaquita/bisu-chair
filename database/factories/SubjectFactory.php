<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Subject>
 */
class SubjectFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'code' => $this->faker->unique()->bothify('???-###'),
            'title' => $this->faker->sentence(3),
            'units' => $this->faker->randomFloat(2, 1, 5),
            'lecture_hours' => $this->faker->numberBetween(0, 5),
            'laboratory_hours' => $this->faker->numberBetween(0, 4),
            'is_active' => true,
        ];
    }

    public function inactive(): self
    {
        return $this->state(['is_active' => false]);
    }

    public function withLaboratory(): self
    {
        return $this->state(['laboratory_hours' => $this->faker->numberBetween(1, 3)]);
    }
}