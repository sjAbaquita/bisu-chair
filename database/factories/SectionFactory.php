<?php

namespace Database\Factories;

use App\Models\AcademicPeriod;
use App\Models\Department;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Section>
 */
class SectionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'department_id' => Department::factory(),
            'academic_period_id' => AcademicPeriod::factory(),
            'program_name' => $this->faker->jobTitle() . ' Program',
            'year_level' => $this->faker->randomElement(['1st Year', '2nd Year', '3rd Year', '4th Year', '5th Year']),
            'name' => $this->faker->regexify('[A-Z]{2,4} \d+[A-Z]?'),
            'expected_enrollment' => $this->faker->numberBetween(15, 60),
        ];
    }

    public function withYearLevel(string $yearLevel): self
    {
        return $this->state(['year_level' => $yearLevel]);
    }

    public function withProgram(string $programName): self
    {
        return $this->state(['program_name' => $programName]);
    }
}