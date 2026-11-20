<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\AcademicPeriod>
 */
class AcademicPeriodFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $startYear = $this->faker->year('now');

        return [
            'academic_year' => $startYear . '-' . ($startYear + 1),
            'term' => $this->faker->randomElement(['First Semester', 'Second Semester', 'Summer']),
            'start_date' => $this->faker->date('now'),
            'end_date' => $this->faker->dateTimeBetween('now', '+1 year')->format('Y-m-d'),
            'status' => $this->faker->randomElement(['draft', 'open', 'locked', 'archived']),
            'is_current' => false,
        ];
    }

    public function current(): self
    {
        return $this->state(['is_current' => true]);
    }

    public function open(): self
    {
        return $this->state(['status' => 'open']);
    }

    public function locked(): self
    {
        return $this->state(['status' => 'locked']);
    }
}