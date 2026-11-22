<?php

namespace App\Services\Scheduling;

use App\Models\AcademicPeriod;
use App\Models\ClassMeeting;
use App\Models\ClassMeetingOccurrence;
use App\Models\Holiday;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class MeetingOccurrenceGenerator
{
    /**
     * Generate meeting occurrences from a recurring ClassMeeting pattern.
     *
     * @param ClassMeeting $meeting The class meeting with recurrence pattern
     * @param AcademicPeriod|null $period Optional academic period to constrain the date range
     * @return array Array of ClassMeetingOccurrence data for bulk insert
     */
    public function generateOccurrences(ClassMeeting $meeting, ?AcademicPeriod $period = null): array
    {
        $occurrences = [];

        // Handle one-time meetings (no day_of_week, has specific dates)
        if ($meeting->day_of_week === null || $meeting->start_time === null) {
            return $occurrences;
        }

        $startDate = Carbon::parse($meeting->effective_start_date)->startOfDay();
        $endDate = Carbon::parse($meeting->effective_end_date)->endOfDay();

        // Constrain by academic period if provided
        if ($period) {
            $startDate = $startDate->max(Carbon::parse($period->start_date));
            $endDate = $endDate->min(Carbon::parse($period->end_date));
        }

        if ($startDate->gt($endDate)) {
            return $occurrences;
        }

        $dayOfWeek = $meeting->day_of_week;
        $currentDate = $this->findFirstOccurrence($startDate, $dayOfWeek);

        while ($currentDate->lte($endDate)) {
            // Skip holidays if configured (holidays that are not class days)
            if ($this->isHoliday($currentDate, $meeting->room_id ?? 0)) {
                $currentDate->addWeek();
                continue;
            }

            $occurrences[] = [
                'class_meeting_id' => $meeting->id,
                'meeting_date' => $currentDate->format('Y-m-d'),
                'start_time' => $meeting->start_time,
                'end_time' => $meeting->end_time,
                'room_id' => $meeting->room_id,
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ];

            $currentDate->addWeek();
        }

        return $occurrences;
    }

    /**
     * Find the first occurrence date matching the day of week.
     */
    protected function findFirstOccurrence(Carbon $startDate, int $dayOfWeek): Carbon
    {
        $currentDate = $startDate->copy();

        // Carbon: 0=Sunday, 1=Monday, ..., 6=Saturday
        while ($currentDate->dayOfWeek !== $dayOfWeek) {
            $currentDate->addDay();
        }

        return $currentDate;
    }

    /**
     * Check if a date is a holiday that should be excluded.
     */
    protected function isHoliday(Carbon $date, int $roomId = 0): bool
    {
        // Only exclude holidays that are not class days
        return Holiday::where('date', $date->format('Y-m-d'))
            ->where('is_class_day', false)
            ->exists();
    }

    /**
     * Generate occurrences for all meetings of a class offering.
     */
    public function generateOccurrencesForClassOffering(ClassMeeting $meeting): Collection
    {
        $occurrences = $this->generateOccurrences($meeting);

        if (empty($occurrences)) {
            return collect();
        }

        return ClassMeetingOccurrence::insert($occurrences);
    }

    /**
     * Regenerate all occurrences for a meeting (delete old, create new).
     */
    public function regenerateOccurrences(ClassMeeting $meeting): int
    {
        // Delete existing occurrences
        ClassMeetingOccurrence::where('class_meeting_id', $meeting->id)->delete();

        if (empty($meeting->day_of_week) || empty($meeting->start_time)) {
            return 0;
        }

        $occurrences = $this->generateOccurrences($meeting);

        if (empty($occurrences)) {
            return 0;
        }

        // Bulk insert new occurrences
        return ClassMeetingOccurrence::insert($occurrences)
            ? count($occurrences)
            : 0;
    }

    /**
     * Generate a one-time occurrence for a specific date.
     */
    public function generateOneOffOccurrence(
        ClassMeeting $meeting,
        Carbon $date,
        string $startTime,
        string $endTime,
        ?int $roomId = null
    ): array {
        return [
            'class_meeting_id' => $meeting->id,
            'meeting_date' => $date->format('Y-m-d'),
            'start_time' => $startTime,
            'end_time' => $endTime,
            'room_id' => $roomId ?? $meeting->room_id,
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }

    /**
     * Get all meeting dates within a date range for a given weekday pattern.
     */
    public function getMeetingDates(
        Carbon $startDate,
        Carbon $endDate,
        int $dayOfWeek,
        bool $excludeHolidays = true
    ): Collection {
        $dates = collect();
        $currentDate = $this->findFirstOccurrence($startDate, $dayOfWeek);

        while ($currentDate->lte($endDate)) {
            if (!$excludeHolidays || !$this->isHoliday($currentDate)) {
                $dates->push($currentDate->copy());
            }
            $currentDate->addWeek();
        }

        return $dates;
    }
}