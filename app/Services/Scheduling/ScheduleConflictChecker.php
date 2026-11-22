<?php

namespace App\Services\Scheduling;

use App\DataTransferObjects\ConflictResult;
use App\Models\AcademicPeriod;
use App\Models\ClassMeeting;
use App\Models\ClassMeetingOccurrence;
use App\Models\ClassOffering;
use App\Models\Faculty;
use App\Models\Room;
use App\Models\RoomUnavailability;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class ScheduleConflictChecker
{
    /**
     * Check for room conflicts at a specific date/time.
     *
     * @param Room $room The room to check
     * @param Carbon $start Start time
     * @param Carbon $end End time
     * @param ClassMeetingOccurrence|null $exclude Optional occurrence to exclude from check
     * @return ConflictResult|null Returns null if no conflict
     */
    public function checkRoomConflict(
        Room $room,
        Carbon $start,
        Carbon $end,
        ?ClassMeetingOccurrence $exclude = null
    ): ?ConflictResult {
        $startTime = $start->format('H:i:s');
        $endTime = $end->format('H:i:s');
        $date = $start->format('Y-m-d');

        $conflictingOccurrence = ClassMeetingOccurrence::where('meeting_date', $date)
            ->where('room_id', $room->id)
            ->where('status', 'active')
            ->where('start_time', '<', $endTime)
            ->where('end_time', '>', $startTime)
            ->when($exclude, fn($query) => $query->whereNotIn('id', [$exclude->id]))
            ->first();

        if (!$conflictingOccurrence) {
            // Also check room unavailability
            $unavailability = RoomUnavailability::where('room_id', $room->id)
                ->where('status', 'active')
                ->where('start_date', '<=', $start->format('Y-m-d'))
                ->where('end_date', '>=', $start->format('Y-m-d'))
                ->where(function ($query) use ($startTime, $endTime) {
                    $query->where(function ($q) use ($startTime, $endTime) {
                        $q->where('start_time', '<', $endTime)
                            ->where('end_time', '>', $startTime);
                    })->orWhere(function ($q) {
                        $q->whereNull('start_time')
                            ->whereNull('end_time');
                    });
                })
                ->first();

            if ($unavailability) {
                return ConflictResult::roomUnavailable(
                    $start,
                    $unavailability->start_time ?? '00:00',
                    $unavailability->end_time ?? '23:59',
                    $room->id,
                    $unavailability->reason ?? 'Maintenance'
                );
            }

            return null;
        }

        // Get the conflicting class offering info if user has access
        $conflictingMeeting = $conflictingOccurrence->classMeeting;
        $conflictingOffering = $conflictingMeeting?->classOffering;

        return ConflictResult::roomConflict(
            $start,
            $conflictingOccurrence->start_time,
            $conflictingOccurrence->end_time,
            $room->id,
            $room->name ?? $room->code,
            $conflictingOffering?->id,
            'class_offering'
        );
    }

    /**
     * Check for faculty conflicts at a specific date/time.
     *
     * @param Faculty $faculty The faculty member to check
     * @param Carbon $start Start time
     * @param Carbon $end End time
     * @param ClassMeetingOccurrence|null $exclude Optional occurrence to exclude from check
     * @return ConflictResult|null Returns null if no conflict
     */
    public function checkFacultyConflict(
        Faculty $faculty,
        Carbon $start,
        Carbon $end,
        ?ClassMeetingOccurrence $exclude = null
    ): ?ConflictResult {
        $startTime = $start->format('H:i:s');
        $endTime = $end->format('H:i:s');
        $date = $start->format('Y-m-d');

        $conflictingOccurrence = ClassMeetingOccurrence::where('meeting_date', $date)
            ->where('status', 'active')
            ->where('start_time', '<', $endTime)
            ->where('end_time', '>', $startTime)
            ->whereHas('classMeeting', function ($query) use ($faculty) {
                $query->whereHas('classOffering.faculty', fn($q) => $q->where('faculty.id', $faculty->id));
            })
            ->when($exclude, fn($query) => $query->whereNotIn('id', [$exclude->id]))
            ->with(['classMeeting.classOffering'])
            ->first();

        if (!$conflictingOccurrence) {
            return null;
        }

        // Get the conflicting class offering info
        $conflictingMeeting = $conflictingOccurrence->classMeeting;
        $conflictingOffering = $conflictingMeeting?->classOffering;

        return ConflictResult::facultyConflict(
            $start,
            $conflictingOccurrence->start_time,
            $conflictingOccurrence->end_time,
            $faculty->id,
            $faculty->getFullName(),
            $conflictingOffering?->id,
            'class_offering'
        );
    }

    /**
     * Check all conflicts for a class meeting pattern.
     *
     * @param ClassMeeting $meeting The meeting to validate
     * @param Collection|null $existingOccurrences Existing occurrences to exclude (for updates)
     * @return array Array of ConflictResult objects
     */
    public function checkClassMeetingConflicts(ClassMeeting $meeting, ?Collection $existingOccurrences = null): array
    {
        $conflicts = [];
        $generator = new MeetingOccurrenceGenerator();

        // Online classes do not reserve rooms
        if ($meeting->delivery_mode === 'synchronous_online' || $meeting->delivery_mode === 'asynchronous_online') {
            // For synchronous online, still check faculty conflicts
            // For asynchronous online, skip faculty time-based checks
            return $conflicts;
        }

        // Face-to-face and hybrid require room conflict checks
        if ($meeting->requiresRoom() && $meeting->room_id) {
            $room = Room::find($meeting->room_id);

            if (!$room) {
                $conflicts[] = ConflictResult::roomConflict(
                    now(),
                    $meeting->start_time,
                    $meeting->end_time,
                    $meeting->room_id,
                    'Unknown Room'
                );
                return $conflicts;
            }

            // Generate occurrences and check each one
            $occurrencesData = $generator->generateOccurrences($meeting);

            foreach ($occurrencesData as $occurrenceData) {
                $date = Carbon::parse($occurrenceData['meeting_date']);
                $start = Carbon::parse($occurrenceData['start_time'], 'Asia/Manila')
                    ->setDateFrom($date);
                $end = Carbon::parse($occurrenceData['end_time'], 'Asia/Manila')
                    ->setDateFrom($date);

                $roomConflict = $this->checkRoomConflict($room, $start, $end);
                if ($roomConflict) {
                    $conflicts[] = $roomConflict;
                }
            }
        }

        return $conflicts;
    }

    /**
     * Check all conflicts for a class offering (meetings + faculty).
     *
     * @param ClassOffering $offering The class offering to validate
     * @return array Array of ConflictResult objects
     */
    public function checkClassOfferingConflicts(ClassOffering $offering): array
    {
        $conflicts = [];

        // Check each meeting
        foreach ($offering->classMeetings as $meeting) {
            $meetingConflicts = $this->checkClassMeetingConflicts($meeting);
            $conflicts = array_merge($conflicts, $meetingConflicts);
        }

        // Check faculty conflicts for active meetings
        foreach ($offering->faculty as $faculty) {
            foreach ($offering->classMeetings as $meeting) {
                // Online classes don't reserve rooms but still need faculty validation for synchronous
                if ($meeting->delivery_mode === 'synchronous_online') {
                    $generator = new MeetingOccurrenceGenerator();
                    $occurrencesData = $generator->generateOccurrences($meeting);

                    foreach ($occurrencesData as $occurrenceData) {
                        $date = Carbon::parse($occurrenceData['meeting_date']);
                        $start = Carbon::parse($occurrenceData['start_time'], 'Asia/Manila')->setDateFrom($date);
                        $end = Carbon::parse($occurrenceData['end_time'], 'Asia/Manila')->setDateFrom($date);

                        $facultyConflict = $this->checkFacultyConflict($faculty, $start, $end);
                        if ($facultyConflict) {
                            $conflicts[] = $facultyConflict;
                        }
                    }
                }
            }
        }

        return $conflicts;
    }

    /**
     * Check if a room is available during a specific time range.
     *
     * @param Room $room The room to check
     * @param Carbon $date The date
     * @param string $startTime Start time (H:i:s)
     * @param string $endTime End time (H:i:s)
     * @param int|null $excludeOccurrenceId Optional occurrence ID to exclude
     * @return bool True if available, false if unavailable or conflicts exist
     */
    public function isRoomAvailable(Room $room, Carbon $date, string $startTime, string $endTime, ?int $excludeOccurrenceId = null): bool
    {
        $conflict = $this->checkRoomConflict($room, Carbon::parse($startTime, 'Asia/Manila')->setDateFrom($date), Carbon::parse($endTime, 'Asia/Manila')->setDateFrom($date), $excludeOccurrenceId ? ClassMeetingOccurrence::find($excludeOccurrenceId) : null);

        return $conflict === null;
    }

    /**
     * Get available time slots for a room on a specific date.
     *
     * @param Room $room The room
     * @param Carbon $date The date
     * @param string $defaultStartTime Default start time (H:i:s)
     * @param string $defaultEndTime Default end time (H:i:s)
     * @return array Available time slots
     */
    public function getAvailableSlots(Room $room, Carbon $date, string $defaultStartTime = '08:00:00', string $defaultEndTime = '18:00:00'): array
    {
        // Get all occupied time ranges for the room on this date
        $occupied = ClassMeetingOccurrence::where('meeting_date', $date->format('Y-m-d'))
            ->where('room_id', $room->id)
            ->where('status', 'active')
            ->select('start_time', 'end_time')
            ->orderBy('start_time')
            ->get();

        $slots = [];
        $currentStart = Carbon::parse($defaultStartTime, 'Asia/Manila');
        $endOfDay = Carbon::parse($defaultEndTime, 'Asia/Manila');

        foreach ($occupied as $occurrence) {
            $occurrenceStart = Carbon::parse($occurrence->start_time, 'Asia/Manila');
            $occurrenceEnd = Carbon::parse($occurrence->end_time, 'Asia/Manila');

            if ($currentStart->lt($occurrenceStart)) {
                $slots[] = [
                    'start' => $currentStart->format('H:i:s'),
                    'end' => $occurrenceStart->format('H:i:s'),
                ];
            }

            if ($occurrenceEnd->gt($currentStart)) {
                $currentStart = $occurrenceEnd;
            }
        }

        if ($currentStart->lt($endOfDay)) {
            $slots[] = [
                'start' => $currentStart->format('H:i:s'),
                'end' => $endOfDay->format('H:i:s'),
            ];
        }

        return $slots;
    }

    /**
     * Validate room capacity against class size.
     *
     * @param Room $room The room
     * @param int $classSize The class size
     * @return ConflictResult|null Returns conflict if capacity exceeded
     */
    public function checkRoomCapacity(Room $room, int $classSize): ?ConflictResult
    {
        if ($room->capacity && $classSize > $room->capacity) {
            return ConflictResult::roomCapacityExceeded(
                $room->id,
                $room->name ?? $room->code,
                $classSize,
                $room->capacity
            );
        }

        return null;
    }

    /**
     * Check for room unavailability on a date range.
     *
     * @param Room $room The room
     * @param Carbon $startDate Start date
     * @param Carbon $endDate End date
     * @param string|null $startTime Optional start time filter
     * @param string|null $endTime Optional end time filter
     * @return ConflictResult|null
     */
    public function checkRoomUnavailability(Room $room, Carbon $startDate, Carbon $endDate, ?string $startTime = null, ?string $endTime = null): ?ConflictResult
    {
        // Implementation would check room_unavailabilities table for any blocking periods
        // This is a placeholder for future implementation
        return null;
    }
}