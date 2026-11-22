<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AcademicPeriod;
use App\Models\Building;
use App\Models\Campus;
use App\Models\ClassMeeting;
use App\Models\ClassMeetingOccurrence;
use App\Models\ClassOffering;
use App\Models\Faculty;
use App\Models\Room;
use App\Models\Section;
use App\Models\Subject;
use App\Services\Scheduling\MeetingOccurrenceGenerator;
use App\Services\Scheduling\ScheduleConflictChecker;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;

class ClassMeetingController extends Controller
{
    protected MeetingOccurrenceGenerator $occurrenceGenerator;
    protected ScheduleConflictChecker $conflictChecker;

    public function __construct(
        MeetingOccurrenceGenerator $occurrenceGenerator,
        ScheduleConflictChecker $conflictChecker
    ) {
        $this->occurrenceGenerator = $occurrenceGenerator;
        $this->conflictChecker = $conflictChecker;
    }

    /**
     * Display a listing of class meetings for a class offering.
     */
    public function index(ClassOffering $classOffering)
    {
        $classMeeting = null;
        $meetings = $classOffering->classMeetings()
            ->with(['room.building.campus'])
            ->get();

        return Inertia::render('Admin/ClassMeeting/Index', [
            'classOffering' => $classOffering->load('subject', 'department.college', 'faculty'),
            'meetings' => $meetings,
            'rooms' => Room::whereIn('status', ['available'])
                ->with('building')
                ->get(['id', 'code', 'name', 'room_type', 'capacity', 'building_id', 'status']),
            'campuses' => Campus::get(['id', 'name', 'code']),
            'deliveryModes' => ['face_to_face', 'synchronous_online', 'asynchronous_online'],
        ]);
    }

    /**
     * Show the form for creating a new class meeting.
     */
    public function create(ClassOffering $classOffering)
    {
        return Inertia::render('Admin/ClassMeeting/Create', [
            'classOffering' => $classOffering->load('subject', 'department.college'),
            'rooms' => Room::whereIn('status', ['available'])
                ->with('building')
                ->get(['id', 'code', 'name', 'room_type', 'capacity', 'building_id', 'status']),
            'campuses' => Campus::get(['id', 'name', 'code']),
            'deliveryModes' => ['face_to_face', 'synchronous_online', 'asynchronous_online'],
        ]);
    }

    /**
     * Store a newly created class meeting.
     */
    public function store(Request $request, ClassOffering $classOffering)
    {
        $validated = $request->validate([
            'delivery_mode' => ['required', 'in:face_to_face,synchronous_online,asynchronous_online'],
            'room_id' => ['nullable', 'exists:rooms,id'],
            'day_of_week' => ['nullable', 'integer', 'min:0', 'max:6'],
            'start_time' => ['nullable', 'date_format:H:i:s'],
            'end_time' => ['nullable', 'date_format:H:i:s'],
            'effective_start_date' => ['required', 'date'],
            'effective_end_date' => ['required', 'date'],
            'online_platform' => ['nullable', 'string', 'max:100'],
            'meeting_url' => ['nullable', 'url'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        // Validate face-to-face meetings have a room
        if ($validated['delivery_mode'] === 'face_to_face' && empty($validated['room_id'])) {
            return redirect()->route('admin.class_offerings.meetings.create', $classOffering)
                ->with('error', 'Face-to-face meetings require a room assignment.');
        }

        // Validate time range
        if (!empty($validated['start_time']) && !empty($validated['end_time'])) {
            if ($validated['start_time'] >= $validated['end_time']) {
                return redirect()->route('admin.class_offerings.meetings.create', $classOffering)
                    ->with('error', 'End time must be after start time.');
            }
        }

        // Validate date range
        if ($validated['effective_start_date'] > $validated['effective_end_date']) {
            return redirect()->route('admin.class_offerings.meetings.create', $classOffering)
                ->with('error', 'Start date cannot be after end date.');
        }

        // Check for conflicts before creating
        $conflictErrors = $this->validateMeetingConflicts($validated, $classOffering);
        if (!empty($conflictErrors)) {
            return redirect()->route('admin.class_offerings.meetings.create', $classOffering)
                ->with('error', implode(' ', $conflictErrors));
        }

        DB::transaction(function () use ($classOffering, $validated) {
            $meeting = $classOffering->classMeetings()->create($validated);

            // Generate occurrences for recurring schedules (weekly)
            if (isset($validated['day_of_week']) && $validated['day_of_week'] !== null && !empty($validated['start_time'])) {
                $occurrences = $this->occurrenceGenerator->generateOccurrences($meeting);
                if (!empty($occurrences)) {
                    ClassMeetingOccurrence::insert($occurrences);
                }
            }
        });

        return redirect()->route('admin.class_offerings.faculty.index', $classOffering)
            ->with('success', 'Class meeting created successfully.');
    }

    /**
     * Validate meeting for conflicts.
     */
    protected function validateMeetingConflicts(array $validated, ClassOffering $classOffering): array
    {
        $errors = [];

        // Skip room conflicts for online classes
        if ($validated['delivery_mode'] !== 'face_to_face' && empty($validated['room_id'])) {
            return $errors;
        }

        // Check recurring schedule conflicts
        if (isset($validated['day_of_week']) && $validated['day_of_week'] !== null && !empty($validated['start_time'])) {
            $startDate = Carbon::parse($validated['effective_start_date']);
            $endDate = Carbon::parse($validated['effective_end_date']);
            $dayOfWeek = $validated['day_of_week'];

            // Find first occurrence
            $currentDate = $startDate->copy();
            while ($currentDate->dayOfWeek !== $dayOfWeek) {
                $currentDate->addDay();
            }

            // Check each occurrence for conflicts
            while ($currentDate <= $endDate) {
                $startTime = Carbon::parse($validated['start_time'], 'Asia/Manila')->setDateFrom($currentDate);
                $endTime = Carbon::parse($validated['end_time'], 'Asia/Manila')->setDateFrom($currentDate);

                // Check room conflict
                if (!empty($validated['room_id'])) {
                    $room = Room::find($validated['room_id']);
                    if ($room && $this->conflictChecker->checkRoomConflict($room, $startTime, $endTime)) {
                        $errors[] = "Room conflict detected on {$currentDate->format('Y-m-d')} {$validated['start_time']}-{$validated['end_time']}.";
                    }
                }

                $currentDate->addWeek();
            }
        }

        // Check one-time meeting conflicts
        if (empty($validated['day_of_week']) || empty($validated['start_time'])) {
            // One-off meeting - check the specific date
            if (!empty($validated['room_id'])) {
                $room = Room::find($validated['room_id']);
                if ($room) {
                    $date = Carbon::parse($validated['effective_start_date']);
                    $startTime = Carbon::parse($validated['start_time'], 'Asia/Manila')->setDateFrom($date);
                    $endTime = Carbon::parse($validated['end_time'], 'Asia/Manila')->setDateFrom($date);

                    $conflict = $this->conflictChecker->checkRoomConflict($room, $startTime, $endTime);
                    if ($conflict) {
                        $errors[] = "Room conflict on {$date->format('Y-m-d')} {$validated['start_time']}-{$validated['end_time']}.";
                    }
                }
            }
        }

        return $errors;
    }

    /**
     * Generate recurring meeting occurrences from a recurring pattern.
     */
    protected function generateRecurringOccurrences(ClassMeeting $meeting): void
    {
        $startDate = Carbon::parse($meeting->effective_start_date);
        $endDate = Carbon::parse($meeting->effective_end_date);
        $dayOfWeek = $meeting->day_of_week;

        // Find the first occurrence date (first weekday of the pattern)
        $currentDate = $startDate->copy();
        while ($currentDate->dayOfWeek !== $dayOfWeek) {
            $currentDate->addDay();
        }

        // Generate occurrences for each week
        while ($currentDate <= $endDate) {
            ClassMeetingOccurrence::create([
                'class_meeting_id' => $meeting->id,
                'meeting_date' => $currentDate->format('Y-m-d'),
                'start_time' => $meeting->start_time,
                'end_time' => $meeting->end_time,
                'room_id' => $meeting->room_id,
                'status' => 'active',
            ]);

            $currentDate->addWeek();
        }
    }

    /**
     * Display the meeting occurrences for a class meeting.
     */
    public function showOccurrences(ClassMeeting $classMeeting)
    {
        $occurrences = $classMeeting->occurrences()
            ->with(['room.building.campus'])
            ->orderBy('meeting_date', 'start_time')
            ->get();

        return Inertia::render('Admin/ClassMeeting/Occurrences', [
            'meeting' => $classMeeting->load('room', 'classOffering.subject'),
            'occurrences' => $occurrences,
        ]);
    }

    /**
     * Show the form for editing a class meeting.
     */
    public function edit(ClassMeeting $classMeeting)
    {
        return Inertia::render('Admin/ClassMeeting/Edit', [
            'meeting' => $classMeeting,
            'rooms' => Room::whereIn('status', ['available'])
                ->with('building')
                ->get(['id', 'code', 'name', 'room_type', 'capacity', 'building_id', 'status']),
            'deliveryModes' => ['face_to_face', 'synchronous_online', 'asynchronous_online'],
        ]);
    }

    /**
     * Update the specified class meeting.
     */
    public function update(Request $request, ClassMeeting $classMeeting)
    {
        $validated = $request->validate([
            'delivery_mode' => ['required', 'in:face_to_face,synchronous_online,asynchronous_online'],
            'room_id' => ['nullable', 'exists:rooms,id'],
            'day_of_week' => ['nullable', 'integer', 'min:0', 'max:6'],
            'start_time' => ['nullable', 'date_format:H:i:s'],
            'end_time' => ['nullable', 'date_format:H:i:s'],
            'effective_start_date' => ['required', 'date'],
            'effective_end_date' => ['required', 'date'],
            'online_platform' => ['nullable', 'string', 'max:100'],
            'meeting_url' => ['nullable', 'url'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        // Validate face-to-face meetings have a room
        if ($validated['delivery_mode'] === 'face_to_face' && empty($validated['room_id'])) {
            return redirect()->back()
                ->with('error', 'Face-to-face meetings require a room assignment.');
        }

        // Validate time range
        if (!empty($validated['start_time']) && !empty($validated['end_time'])) {
            if ($validated['start_time'] >= $validated['end_time']) {
                return redirect()->back()
                    ->with('error', 'End time must be after start time.');
            }
        }

        // Validate date range
        if ($validated['effective_start_date'] > $validated['effective_end_date']) {
            return redirect()->back()
                ->with('error', 'Start date cannot be after end date.');
        }

        // Check for conflicts before updating (excluding current meeting)
        $conflictErrors = $this->validateMeetingUpdateConflicts($validated, $classMeeting);
        if (!empty($conflictErrors)) {
            return redirect()->back()
                ->with('error', implode(' ', $conflictErrors));
        }

        DB::transaction(function () use ($classMeeting, $validated) {
            // Delete existing occurrences before regenerating
            ClassMeetingOccurrence::where('class_meeting_id', $classMeeting->id)
                ->delete();

            $classMeeting->update($validated);

            // Regenerate occurrences for recurring schedules (weekly)
            if (isset($validated['day_of_week']) && $validated['day_of_week'] !== null && !empty($validated['start_time'])) {
                $occurrences = $this->occurrenceGenerator->generateOccurrences($classMeeting);
                if (!empty($occurrences)) {
                    ClassMeetingOccurrence::insert($occurrences);
                }
            }
        });

        return redirect()->route('admin.class_offerings.faculty.index', $classMeeting->class_offering_id)
            ->with('success', 'Class meeting updated successfully.');
    }

    /**
     * Validate meeting update for conflicts.
     */
    protected function validateMeetingUpdateConflicts(array $validated, ClassMeeting $classMeeting): array
    {
        $errors = [];

        // Skip room conflicts for online classes
        if ($validated['delivery_mode'] !== 'face_to_face' && empty($validated['room_id'])) {
            return $errors;
        }

        $existingOccurrences = $classMeeting->occurrences()->pluck('id')->toArray();

        // Check recurring schedule conflicts
        if (isset($validated['day_of_week']) && $validated['day_of_week'] !== null && !empty($validated['start_time'])) {
            $startDate = Carbon::parse($validated['effective_start_date']);
            $endDate = Carbon::parse($validated['effective_end_date']);
            $dayOfWeek = $validated['day_of_week'];

            // Find first occurrence
            $currentDate = $startDate->copy();
            while ($currentDate->dayOfWeek !== $dayOfWeek) {
                $currentDate->addDay();
            }

            // Check each occurrence for conflicts
            while ($currentDate <= $endDate) {
                $startTime = Carbon::parse($validated['start_time'], 'Asia/Manila')->setDateFrom($currentDate);
                $endTime = Carbon::parse($validated['end_time'], 'Asia/Manila')->setDateFrom($currentDate);

                // Check room conflict
                if (!empty($validated['room_id'])) {
                    $room = Room::find($validated['room_id']);
                    if ($room && $this->conflictChecker->checkRoomConflict($room, $startTime, $endTime, $classMeeting->occurrences()->first())) {
                        $errors[] = "Room conflict detected on {$currentDate->format('Y-m-d')} {$validated['start_time']}-{$validated['end_time']}.";
                    }
                }

                $currentDate->addWeek();
            }
        }

        return $errors;
    }

    /**
     * Remove the specified class meeting.
     */
    public function destroy(ClassMeeting $classMeeting)
    {
        $classMeetingId = $classMeeting->id;
        $classOfferingId = $classMeeting->class_offering_id;

        DB::transaction(function () use ($classMeeting) {
            // Mark all occurrences as cancelled
            ClassMeetingOccurrence::where('class_meeting_id', $classMeeting->id)
                ->update(['status' => 'cancelled']);

            $classMeeting->delete();
        });

        return redirect()->route('admin.class_offerings.faculty.index', $classOfferingId)
            ->with('success', 'Class meeting removed (occurrences cancelled).');
    }

    /**
     * Generate one-off meeting occurrences for exceptions.
     */
    public function generateOneOffOccurrences(Request $request, ClassOffering $classOffering)
    {
        $validated = $request->validate([
            'meeting_date' => ['required', 'date'],
            'start_time' => ['required', 'date_format:H:i:s'],
            'end_time' => ['required', 'date_format:H:i:s'],
            'room_id' => ['nullable', 'exists:rooms,id'],
        ]);

        // Validate time range
        if ($validated['start_time'] >= $validated['end_time']) {
            return redirect()->back()
                ->with('error', 'End time must be after start time.');
        }

        // Validate room is available on that date using conflict service
        if ($validated['room_id']) {
            $room = Room::find($validated['room_id']);
            if ($room) {
                $date = Carbon::parse($validated['meeting_date']);
                $startTime = Carbon::parse($validated['start_time'], 'Asia/Manila')->setDateFrom($date);
                $endTime = Carbon::parse($validated['end_time'], 'Asia/Manila')->setDateFrom($date);

                $conflict = $this->conflictChecker->checkRoomConflict($room, $startTime, $endTime);
                if ($conflict) {
                    return redirect()->back()
                        ->with('error', "Room conflict: {$conflict->message}");
                }
            }
        }

        // Create a temporary meeting for this one-off occurrence
        DB::transaction(function () use ($classOffering, $validated) {
            $meeting = $classOffering->classMeetings()->create([
                'delivery_mode' => 'face_to_face',
                'room_id' => $validated['room_id'],
                'day_of_week' => null,
                'start_time' => $validated['start_time'],
                'end_time' => $validated['end_time'],
                'effective_start_date' => $validated['meeting_date'],
                'effective_end_date' => $validated['meeting_date'],
            ]);

            ClassMeetingOccurrence::create([
                'class_meeting_id' => $meeting->id,
                'meeting_date' => $validated['meeting_date'],
                'start_time' => $validated['start_time'],
                'end_time' => $validated['end_time'],
                'room_id' => $validated['room_id'],
                'status' => 'active',
            ]);
        });

        return redirect()->route('admin.class_offerings.faculty.index', $classOffering->id)
            ->with('success', 'One-off meeting created successfully.');
    }
}