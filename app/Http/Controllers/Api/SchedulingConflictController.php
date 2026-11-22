<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AcademicPeriod;
use App\Models\ClassMeetingOccurrence;
use App\Models\Faculty;
use App\Models\Room;
use App\Services\Scheduling\ScheduleConflictChecker;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class SchedulingConflictController extends Controller
{
    protected ScheduleConflictChecker $conflictChecker;

    public function __construct(ScheduleConflictChecker $conflictChecker)
    {
        $this->conflictChecker = $conflictChecker;
    }

    /**
     * Check for scheduling conflicts.
     *
     * POST /api/scheduling/conflicts/check
     */
    public function checkConflicts(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'room_id' => 'nullable|exists:rooms,id',
            'faculty_id' => 'nullable|exists:faculty,id',
            'date' => 'required|date',
            'start_time' => 'required|date_format:H:i:s',
            'end_time' => 'required|date_format:H:i:s',
            'delivery_mode' => 'required|in:face_to_face,synchronous_online,asynchronous_online',
            'exclude_occurrence_id' => 'nullable|exists:class_meeting_occurrences,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'has_conflict' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $data = $validator->validated();
        $date = Carbon::parse($data['date'], 'Asia/Manila');
        $startTime = Carbon::parse($data['start_time'], 'Asia/Manila')->setDateFrom($date);
        $endTime = Carbon::parse($data['end_time'], 'Asia/Manila')->setDateFrom($date);
        $excludeOccurrence = $data['exclude_occurrence_id']
            ? ClassMeetingOccurrence::find($data['exclude_occurrence_id'])
            : null;

        $conflicts = [];

        // Check room conflicts for face-to-face classes
        if ($data['delivery_mode'] === 'face_to_face' && $data['room_id']) {
            $room = Room::find($data['room_id']);
            if ($room) {
                $roomConflict = $this->conflictChecker->checkRoomConflict(
                    $room,
                    $startTime,
                    $endTime,
                    $excludeOccurrence
                );
                if ($roomConflict) {
                    $conflicts[] = $roomConflict;
                }
            }
        }

        // Check faculty conflicts for synchronous classes
        if (in_array($data['delivery_mode'], ['face_to_face', 'synchronous_online']) && $data['faculty_id']) {
            $faculty = Faculty::find($data['faculty_id']);
            if ($faculty) {
                $facultyConflict = $this->conflictChecker->checkFacultyConflict(
                    $faculty,
                    $startTime,
                    $endTime,
                    $excludeOccurrence
                );
                if ($facultyConflict) {
                    $conflicts[] = $facultyConflict;
                }
            }
        }

        return response()->json([
            'has_conflict' => !empty($conflicts),
            'conflicts' => array_map(fn($c) => $c->toArray(), $conflicts),
        ]);
    }

    /**
     * Get available time slots for a room on a specific date.
     *
     * GET /api/rooms/{room}/availability
     */
    public function getRoomAvailability(Room $room, Request $request)
    {
        $validator = Validator::make($request->all(), [
            'date' => 'required|date',
            'exclude_occurrence_id' => 'nullable|exists:class_meeting_occurrences,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $date = Carbon::parse($request->date, 'Asia/Manila');
        $slots = $this->conflictChecker->getAvailableSlots($room, $date);

        return response()->json([
            'room_id' => $room->id,
            'room_name' => $room->name ?? $room->code,
            'date' => $date->format('Y-m-d'),
            'available_slots' => $slots,
        ]);
    }

    /**
     * Get faculty availability for a specific date/time range.
     *
     * GET /api/faculty/{faculty}/availability
     */
    public function getFacultyAvailability(Faculty $faculty, Request $request)
    {
        $validator = Validator::make($request->all(), [
            'date' => 'required|date',
            'start_time' => 'required|date_format:H:i:s',
            'end_time' => 'required|date_format:H:i:s',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $date = Carbon::parse($request->date, 'Asia/Manila');
        $startTime = Carbon::parse($request->start_time, 'Asia/Manila')->setDateFrom($date);
        $endTime = Carbon::parse($request->end_time, 'Asia/Manila')->setDateFrom($date);

        $conflict = $this->conflictChecker->checkFacultyConflict($faculty, $startTime, $endTime);

        return response()->json([
            'faculty_id' => $faculty->id,
            'faculty_name' => $faculty->getFullName(),
            'date' => $date->format('Y-m-d'),
            'start_time' => $request->start_time,
            'end_time' => $request->end_time,
            'is_available' => $conflict === null,
            'conflicting_class' => $conflict ? $conflict->toArray() : null,
        ]);
    }

    /**
     * Validate room capacity.
     *
     * GET /api/rooms/{room}/capacity-check
     */
    public function checkRoomCapacity(Room $room, Request $request)
    {
        $validator = Validator::make($request->all(), [
            'class_size' => 'required|integer|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $conflict = $this->conflictChecker->checkRoomCapacity($room, $request->class_size);

        return response()->json([
            'room_id' => $room->id,
            'room_name' => $room->name ?? $room->code,
            'room_capacity' => $room->capacity,
            'class_size' => $request->class_size,
            'is_valid' => $conflict === null,
            'conflict' => $conflict?->toArray(),
        ]);
    }
}