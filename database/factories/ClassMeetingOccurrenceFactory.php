<?php

namespace Database\Factories;

use App\Models\ClassMeeting;
use App\Models\ClassMeetingOccurrence;
use App\Models\Room;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ClassMeetingOccurrence>
 */
class ClassMeetingOccurrenceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'class_meeting_id' => ClassMeeting::factory(),
            'meeting_date' => now()->addDays($this->faker->numberBetween(0, 30)),
            'start_time' => $this->faker->time('H:i:s'),
            'end_time' => $this->faker->time('H:i:s'),
            'room_id' => Room::factory(),
            'status' => $this->faker->randomElement(['active', 'cancelled', 'rescheduled']),
        ];
    }

    public function active(): self
    {
        return $this->state(['status' => 'active']);
    }

    public function cancelled(): self
    {
        return $this->state(['status' => 'cancelled']);
    }

    public function rescheduled(): self
    {
        return $this->state(['status' => 'rescheduled']);
    }

    public function withRoom(Room $room): self
    {
        return $this->state(['room_id' => $room->id]);
    }
}