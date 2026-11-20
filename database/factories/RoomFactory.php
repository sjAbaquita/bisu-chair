<?php

namespace Database\Factories;

use App\Models\Building;
use App\Models\College;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Room>
 */
class RoomFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'building_id' => Building::factory(),
            'college_id' => College::factory(),
            'code' => strtoupper($this->faker->lexify('???-##')),
            'name' => $this->faker->word() . ' Room',
            'room_type' => $this->faker->randomElement(['lecture room', 'laboratory', 'computer laboratory', 'conference room']),
            'capacity' => $this->faker->numberBetween(20, 100),
            'features' => $this->faker->optional()->randomElements(['projector', 'whiteboard', 'computers', 'air conditioning'], $this->faker->numberBetween(0, 3)),
            'status' => 'available',
            'notes' => $this->faker->optional()->sentence(),
        ];
    }

    public function available(): self
    {
        return $this->state(['status' => 'available']);
    }

    public function underMaintenance(): self
    {
        return $this->state(['status' => 'under maintenance']);
    }
}