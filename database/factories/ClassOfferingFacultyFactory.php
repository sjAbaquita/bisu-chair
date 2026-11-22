<?php

namespace Database\Factories;

use App\Models\ClassOffering;
use App\Models\ClassOfferingFaculty;
use App\Models\Faculty;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ClassOfferingFaculty>
 */
class ClassOfferingFacultyFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'class_offering_id' => ClassOffering::factory(),
            'faculty_id' => Faculty::factory(),
            'load_hours' => $this->faker->optional()->decimal(2, 0, 10, 2),
            'load_units' => $this->faker->optional()->decimal(2, 0, 5, 2),
            'role' => $this->faker->optional()->randomElement(['Lead', 'Co-faculty', 'Guest Lecturer']),
        ];
    }

    public function withLeadRole(): self
    {
        return $this->state(['role' => 'Lead']);
    }

    public function withLoadHours(float $hours): self
    {
        return $this->state(['load_hours' => $hours]);
    }

    public function withLoadUnits(float $units): self
    {
        return $this->state(['load_units' => $units]);
    }

    public function withoutRole(): self
    {
        return $this->state(['role' => null]);
    }
}