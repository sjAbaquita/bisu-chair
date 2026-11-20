<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Faculty>
 */
class FacultyFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'employee_no' => $this->faker->unique()->numerify('EMP######'),
            'first_name' => $this->faker->firstName(),
            'middle_name' => $this->faker->optional()->firstName(),
            'last_name' => $this->faker->lastName(),
            'suffix' => $this->faker->optional()->suffix(),
            'email' => $this->faker->optional()->companyEmail(),
            'status' => 'active',
            'highest_degree_earned' => $this->faker->optional()->randomElement(['Bachelor', 'Master', 'Doctorate', 'PhD']),
            'specialization' => $this->faker->optional()->jobTitle(),
        ];
    }

    public function active(): self
    {
        return $this->state(['status' => 'active']);
    }

    public function inactive(): self
    {
        return $this->state(['status' => 'inactive']);
    }
}