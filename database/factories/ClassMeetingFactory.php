<?php

namespace Database\Factories;

use App\Models\ClassOffering;
use App\Models\Room;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ClassMeeting>
 */
class ClassMeetingFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $startDate = $this->faker->dateTimeBetween('now', '+1 month');
        $endDate = $this->faker->dateTimeBetween($startDate, '+3 months');

        return [
            'class_offering_id' => ClassOffering::factory(),
            'delivery_mode' => $this->faker->randomElement(['face_to_face', 'synchronous_online', 'asynchronous_online']),
            'room_id' => Room::factory(),
            'day_of_week' => $this->faker->optional()->numberBetween(0, 6),
            'start_time' => $this->faker->optional()->time('H:i:s'),
            'end_time' => $this->faker->optional()->time('H:i:s'),
            'effective_start_date' => $startDate->format('Y-m-d'),
            'effective_end_date' => $endDate->format('Y-m-d'),
            'online_platform' => $this->faker->optional()->word(),
            'meeting_url' => $this->faker->optional()->url(),
            'notes' => $this->faker->optional()->sentence(),
        ];
    }

    public function faceToFace(): self
    {
        return $this->state([
            'delivery_mode' => 'face_to_face',
            'room_id' => Room::factory(),
        ]);
    }

    public function synchronousOnline(): self
    {
        return $this->state(['delivery_mode' => 'synchronous_online']);
    }

    public function asynchronousOnline(): self
    {
        return $this->state(['delivery_mode' => 'asynchronous_online']);
    }

    public function withRoom(Room $room): self
    {
        return $this->state(['room_id' => $room->id]);
    }

    public function recurring(int $dayOfWeek): self
    {
        return $this->state([
            'day_of_week' => $dayOfWeek,
            'start_time' => '08:00:00',
            'end_time' => '09:30:00',
        ]);
    }

    public function oneTime(): self
    {
        return $this->state([
            'day_of_week' => null,
            'start_time' => null,
            'end_time' => null,
        ]);
    }
}