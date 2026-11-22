<?php

namespace Database\Factories;

use App\Models\AcademicPeriod;
use App\Models\ClassOffering;
use App\Models\Department;
use App\Models\Section;
use App\Models\Subject;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ClassOffering>
 */
class ClassOfferingFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'academic_period_id' => AcademicPeriod::factory(),
            'department_id' => Department::factory(),
            'section_id' => null,
            'subject_id' => Subject::factory(),
            'delivery_mode' => $this->faker->randomElement(['face_to_face', 'online', 'hybrid']),
            'class_size' => $this->faker->numberBetween(15, 60),
            'status' => $this->faker->randomElement(['draft', 'submitted', 'for_review', 'returned', 'approved', 'cancelled']),
            'remarks' => $this->faker->optional()->sentence(),
            'created_by' => \App\Models\User::factory(),
            'submitted_by' => null,
            'approved_by' => null,
            'submitted_at' => null,
            'approved_at' => null,
        ];
    }

    public function draft(): self
    {
        return $this->state(['status' => 'draft']);
    }

    public function submitted(): self
    {
        return $this->state(['status' => 'submitted']);
    }

    public function approved(): self
    {
        return $this->state(['status' => 'approved']);
    }

    public function returned(): self
    {
        return $this->state(['status' => 'returned']);
    }

    public function cancelled(): self
    {
        return $this->state(['status' => 'cancelled']);
    }

    public function faceToFace(): self
    {
        return $this->state(['delivery_mode' => 'face_to_face']);
    }

    public function online(): self
    {
        return $this->state(['delivery_mode' => 'online']);
    }

    public function hybrid(): self
    {
        return $this->state(['delivery_mode' => 'hybrid']);
    }
}