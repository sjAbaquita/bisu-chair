<?php

use App\Models\AcademicPeriod;
use App\Models\Building;
use App\Models\Campus;
use App\Models\ClassMeeting;
use App\Models\ClassMeetingOccurrence;
use App\Models\ClassOffering;
use App\Models\Department;
use App\Models\Faculty;
use App\Models\Room;
use App\Models\Subject;
use App\Models\User;
use App\Models\College;
use Carbon\Carbon;
use App\DataTransferObjects\ConflictResult;
use App\Services\Scheduling\ScheduleConflictChecker;
use App\Services\Scheduling\MeetingOccurrenceGenerator;

beforeEach(function () {
    $this->college = College::factory()->create();
    $this->department = Department::factory()->create(['college_id' => $this->college->id]);
    $this->campus = Campus::factory()->create();
    $this->building = Building::factory()->create(['campus_id' => $this->campus->id]);
    $this->room1 = Room::factory()->create(['building_id' => $this->building->id, 'status' => 'available']);
    $this->room2 = Room::factory()->create(['building_id' => $this->building->id, 'status' => 'available']);
    $this->academicPeriod = AcademicPeriod::factory()->create(['status' => 'open']);
    $this->subject = Subject::factory()->create();
    $this->faculty = Faculty::factory()->create();
    $this->user = User::factory()->create();
    $this->conflictChecker = new ScheduleConflictChecker();
    $this->occurrenceGenerator = new MeetingOccurrenceGenerator();
});

test('conflict service can be instantiated', function () {
    expect($this->conflictChecker)->toBeInstanceOf(ScheduleConflictChecker::class);
    expect($this->occurrenceGenerator)->toBeInstanceOf(MeetingOccurrenceGenerator::class);
});

test('same room, same date and overlapping time returns conflict', function () {
    $date = now()->format('Y-m-d');

    // Create existing ClassMeeting with proper relationships
    $classOffering = ClassOffering::factory()->create([
        'academic_period_id' => $this->academicPeriod->id,
        'department_id' => $this->department->id,
        'subject_id' => $this->subject->id,
        'created_by' => $this->user->id,
    ]);

    $meeting = ClassMeeting::create([
        'class_offering_id' => $classOffering->id,
        'delivery_mode' => 'face_to_face',
        'room_id' => $this->room1->id,
        'day_of_week' => 1,
        'start_time' => '08:00:00',
        'end_time' => '09:30:00',
        'effective_start_date' => '2026-01-01',
        'effective_end_date' => now()->addYear()->format('Y-m-d'),
    ]);

    // Create meeting occurrence
    $occurrence = ClassMeetingOccurrence::create([
        'class_meeting_id' => $meeting->id,
        'meeting_date' => $date,
        'start_time' => '08:00:00',
        'end_time' => '09:30:00',
        'room_id' => $this->room1->id,
        'status' => 'active',
    ]);

    $conflict = $this->conflictChecker->checkRoomConflict(
        $this->room1,
        Carbon::parse('09:00:00', 'Asia/Manila')->setDateFrom(Carbon::parse($date)),
        Carbon::parse('10:30:00', 'Asia/Manila')->setDateFrom(Carbon::parse($date))
    );

    expect($conflict)->not->toBeNull();
    expect($conflict->type)->toBe('ROOM_CONFLICT');
});

test('same room, consecutive non-overlapping times is allowed', function () {
    $date = now()->format('Y-m-d');

    // Create existing ClassMeeting with proper relationships
    $classOffering = ClassOffering::factory()->create([
        'academic_period_id' => $this->academicPeriod->id,
        'department_id' => $this->department->id,
        'subject_id' => $this->subject->id,
        'created_by' => $this->user->id,
    ]);

    $meeting = ClassMeeting::create([
        'class_offering_id' => $classOffering->id,
        'delivery_mode' => 'face_to_face',
        'room_id' => $this->room1->id,
        'day_of_week' => 1,
        'start_time' => '08:00:00',
        'end_time' => '09:30:00',
        'effective_start_date' => '2026-01-01',
        'effective_end_date' => now()->addYear()->format('Y-m-d'),
    ]);

    // Create meeting occurrence
    ClassMeetingOccurrence::create([
        'class_meeting_id' => $meeting->id,
        'meeting_date' => $date,
        'start_time' => '08:00:00',
        'end_time' => '09:30:00',
        'room_id' => $this->room1->id,
        'status' => 'active',
    ]);

    // Check 9:30-11:00 should not conflict (consecutive, not overlapping)
    $conflict = $this->conflictChecker->checkRoomConflict(
        $this->room1,
        Carbon::parse('09:30:00', 'Asia/Manila')->setDateFrom(Carbon::parse($date)),
        Carbon::parse('11:00:00', 'Asia/Manila')->setDateFrom(Carbon::parse($date))
    );

    expect($conflict)->toBeNull();
});

test('same room, different dates is allowed', function () {
    $date1 = now()->format('Y-m-d');
    $date2 = now()->addDay()->format('Y-m-d');

    // Create existing ClassMeeting with proper relationships
    $classOffering = ClassOffering::factory()->create([
        'academic_period_id' => $this->academicPeriod->id,
        'department_id' => $this->department->id,
        'subject_id' => $this->subject->id,
        'created_by' => $this->user->id,
    ]);

    $meeting = ClassMeeting::create([
        'class_offering_id' => $classOffering->id,
        'delivery_mode' => 'face_to_face',
        'room_id' => $this->room1->id,
        'day_of_week' => 1,
        'start_time' => '08:00:00',
        'end_time' => '09:30:00',
        'effective_start_date' => '2026-01-01',
        'effective_end_date' => now()->addYear()->format('Y-m-d'),
    ]);

    // Create meeting occurrence on date1
    ClassMeetingOccurrence::create([
        'class_meeting_id' => $meeting->id,
        'meeting_date' => $date1,
        'start_time' => '08:00:00',
        'end_time' => '09:30:00',
        'room_id' => $this->room1->id,
        'status' => 'active',
    ]);

    // Check same time on different date should not conflict
    $conflict = $this->conflictChecker->checkRoomConflict(
        $this->room1,
        Carbon::parse('08:00:00', 'Asia/Manila')->setDateFrom(Carbon::parse($date2)),
        Carbon::parse('09:30:00', 'Asia/Manila')->setDateFrom(Carbon::parse($date2))
    );

    expect($conflict)->toBeNull();
});

test('different faculty, same room and time is allowed for room check', function () {
    $faculty2 = Faculty::factory()->create();
    $date = now()->format('Y-m-d');

    // Create existing ClassMeeting with proper relationships
    $classOffering = ClassOffering::factory()->create([
        'academic_period_id' => $this->academicPeriod->id,
        'department_id' => $this->department->id,
        'subject_id' => $this->subject->id,
        'created_by' => $this->user->id,
    ]);

    $meeting = ClassMeeting::create([
        'class_offering_id' => $classOffering->id,
        'delivery_mode' => 'face_to_face',
        'room_id' => $this->room1->id,
        'day_of_week' => 1,
        'start_time' => '08:00:00',
        'end_time' => '09:30:00',
        'effective_start_date' => '2026-01-01',
        'effective_end_date' => now()->addYear()->format('Y-m-d'),
    ]);

    // Create meeting occurrence
    ClassMeetingOccurrence::create([
        'class_meeting_id' => $meeting->id,
        'meeting_date' => $date,
        'start_time' => '08:00:00',
        'end_time' => '09:30:00',
        'room_id' => $this->room1->id,
        'status' => 'active',
    ]);

    // Same time as existing - room conflict exists regardless of faculty
    $conflict = $this->conflictChecker->checkRoomConflict(
        $this->room1,
        Carbon::parse('08:00:00', 'Asia/Manila')->setDateFrom(Carbon::parse($date)),
        Carbon::parse('09:30:00', 'Asia/Manila')->setDateFrom(Carbon::parse($date))
    );

    expect($conflict)->not->toBeNull();
});

test('updating schedule ignores its own existing record during conflict check', function () {
    $date = now()->format('Y-m-d');

    // Create existing ClassMeeting with proper relationships
    $classOffering = ClassOffering::factory()->create([
        'academic_period_id' => $this->academicPeriod->id,
        'department_id' => $this->department->id,
        'subject_id' => $this->subject->id,
        'created_by' => $this->user->id,
    ]);

    $meeting = ClassMeeting::create([
        'class_offering_id' => $classOffering->id,
        'delivery_mode' => 'face_to_face',
        'room_id' => $this->room1->id,
        'day_of_week' => 1,
        'start_time' => '08:00:00',
        'end_time' => '09:30:00',
        'effective_start_date' => '2026-01-01',
        'effective_end_date' => now()->addYear()->format('Y-m-d'),
    ]);

    $occurrence = ClassMeetingOccurrence::create([
        'class_meeting_id' => $meeting->id,
        'meeting_date' => $date,
        'start_time' => '08:00:00',
        'end_time' => '09:30:00',
        'room_id' => $this->room1->id,
        'status' => 'active',
    ]);

    // Same time as existing - should not conflict when excluding itself
    $conflict = $this->conflictChecker->checkRoomConflict(
        $this->room1,
        Carbon::parse('08:00:00', 'Asia/Manila')->setDateFrom(Carbon::parse($date)),
        Carbon::parse('09:30:00', 'Asia/Manila')->setDateFrom(Carbon::parse($date)),
        $occurrence
    );

    expect($conflict)->toBeNull();
});

test('room capacity exceeded conflict is detected', function () {
    $conflict = $this->conflictChecker->checkRoomCapacity($this->room1, 100);

    expect($conflict)->not->toBeNull();
    expect($conflict->type)->toBe('ROOM_CAPACITY_EXCEEDED');
});

test('room capacity check passes when class size within capacity', function () {
    // Set room capacity
    $this->room1->update(['capacity' => 60]);

    $conflict = $this->conflictChecker->checkRoomCapacity($this->room1, 40);

    expect($conflict)->toBeNull();
});

test('meeting occurrence generator creates occurrences for weekly recurrence', function () {
    $startDate = now()->format('Y-m-d');
    $endDate = now()->addWeeks(4)->format('Y-m-d');

    // Create proper ClassOffering
    $classOffering = ClassOffering::factory()->create([
        'academic_period_id' => $this->academicPeriod->id,
        'department_id' => $this->department->id,
        'subject_id' => $this->subject->id,
        'created_by' => $this->user->id,
    ]);

    $meeting = ClassMeeting::create([
        'class_offering_id' => $classOffering->id,
        'delivery_mode' => 'face_to_face',
        'room_id' => $this->room1->id,
        'day_of_week' => 1, // Monday
        'start_time' => '08:00:00',
        'end_time' => '09:30:00',
        'effective_start_date' => $startDate,
        'effective_end_date' => $endDate,
    ]);

    $occurrences = $this->occurrenceGenerator->generateOccurrences($meeting);

    expect($occurrences)->not->toBeEmpty();
    expect(count($occurrences))->toBeGreaterThanOrEqual(4); // At least 4 Mondays in 4 weeks
});

test('meeting occurrence generator returns empty array for one-time meetings', function () {
    $meeting = ClassMeeting::factory()->create([
        'day_of_week' => null,
        'start_time' => null,
        'end_time' => null,
        'effective_start_date' => '2026-01-01',
        'effective_end_date' => now()->format('Y-m-d'),
    ]);

    $occurrences = $this->occurrenceGenerator->generateOccurrences($meeting);

    expect($occurrences)->toBeEmpty();
});

test('conflicting class offering details are populated', function () {
    $date = now()->format('Y-m-d');

    // Create a ClassOffering to serve as the conflicting offering
    $otherOffering = ClassOffering::factory()->create([
        'academic_period_id' => $this->academicPeriod->id,
        'department_id' => $this->department->id,
        'subject_id' => $this->subject->id,
        'created_by' => $this->user->id,
    ]);

    $existingMeeting = ClassMeeting::create([
        'class_offering_id' => $otherOffering->id,
        'delivery_mode' => 'face_to_face',
        'room_id' => $this->room1->id,
        'day_of_week' => 1,
        'start_time' => '08:00:00',
        'end_time' => '09:30:00',
        'effective_start_date' => '2026-01-01',
        'effective_end_date' => now()->addYear()->format('Y-m-d'),
    ]);

    ClassMeetingOccurrence::create([
        'class_meeting_id' => $existingMeeting->id,
        'meeting_date' => $date,
        'start_time' => '08:00:00',
        'end_time' => '09:30:00',
        'room_id' => $this->room1->id,
        'status' => 'active',
    ]);

    $conflict = $this->conflictChecker->checkRoomConflict(
        $this->room1,
        Carbon::parse('09:00:00', 'Asia/Manila')->setDateFrom(Carbon::parse($date)),
        Carbon::parse('10:30:00', 'Asia/Manila')->setDateFrom(Carbon::parse($date))
    );

    expect($conflict)->not->toBeNull();
    expect($conflict->conflictingEntityId)->toBe($otherOffering->id);
    expect($conflict->conflictingEntityType)->toBe('class_offering');
});

test('conflict result array structure is correct', function () {
    $date = now()->format('Y-m-d');

    // Create existing ClassMeeting with proper relationships
    $classOffering = ClassOffering::factory()->create([
        'academic_period_id' => $this->academicPeriod->id,
        'department_id' => $this->department->id,
        'subject_id' => $this->subject->id,
        'created_by' => $this->user->id,
    ]);

    $meeting = ClassMeeting::create([
        'class_offering_id' => $classOffering->id,
        'delivery_mode' => 'face_to_face',
        'room_id' => $this->room1->id,
        'day_of_week' => 1,
        'start_time' => '08:00:00',
        'end_time' => '09:30:00',
        'effective_start_date' => '2026-01-01',
        'effective_end_date' => now()->addYear()->format('Y-m-d'),
    ]);

    // Create meeting occurrence
    ClassMeetingOccurrence::create([
        'class_meeting_id' => $meeting->id,
        'meeting_date' => $date,
        'start_time' => '08:00:00',
        'end_time' => '09:30:00',
        'room_id' => $this->room1->id,
        'status' => 'active',
    ]);

    $conflict = $this->conflictChecker->checkRoomConflict(
        $this->room1,
        Carbon::parse('09:00:00', 'Asia/Manila')->setDateFrom(Carbon::parse($date)),
        Carbon::parse('10:30:00', 'Asia/Manila')->setDateFrom(Carbon::parse($date))
    );

    expect($conflict)->not->toBeNull();

    $array = $conflict->toArray();

    expect($array)->toHaveKey('type', 'ROOM_CONFLICT');
    expect($array)->toHaveKey('message');
    expect($array)->toHaveKey('room_id');
    expect($array)->toHaveKey('date');
    expect($array)->toHaveKey('start_time');
    expect($array)->toHaveKey('end_time');
});

test('get available slots for room excludes occupied time', function () {
    $date = now()->format('Y-m-d');

    // Create existing ClassMeeting with proper relationships
    $classOffering1 = ClassOffering::factory()->create([
        'academic_period_id' => $this->academicPeriod->id,
        'department_id' => $this->department->id,
        'subject_id' => $this->subject->id,
        'created_by' => $this->user->id,
    ]);

    $meeting1 = ClassMeeting::create([
        'class_offering_id' => $classOffering1->id,
        'delivery_mode' => 'face_to_face',
        'room_id' => $this->room1->id,
        'day_of_week' => 1,
        'start_time' => '08:00:00',
        'end_time' => '10:00:00',
        'effective_start_date' => '2026-01-01',
        'effective_end_date' => now()->addYear()->format('Y-m-d'),
    ]);

    ClassMeetingOccurrence::create([
        'class_meeting_id' => $meeting1->id,
        'meeting_date' => $date,
        'start_time' => '08:00:00',
        'end_time' => '10:00:00',
        'room_id' => $this->room1->id,
        'status' => 'active',
    ]);

    // Create second meeting
    $classOffering2 = ClassOffering::factory()->create([
        'academic_period_id' => $this->academicPeriod->id,
        'department_id' => $this->department->id,
        'subject_id' => $this->subject->id,
        'created_by' => $this->user->id,
    ]);

    $meeting2 = ClassMeeting::create([
        'class_offering_id' => $classOffering2->id,
        'delivery_mode' => 'face_to_face',
        'room_id' => $this->room1->id,
        'day_of_week' => 1,
        'start_time' => '11:00:00',
        'end_time' => '13:00:00',
        'effective_start_date' => '2026-01-01',
        'effective_end_date' => now()->addYear()->format('Y-m-d'),
    ]);

    ClassMeetingOccurrence::create([
        'class_meeting_id' => $meeting2->id,
        'meeting_date' => $date,
        'start_time' => '11:00:00',
        'end_time' => '13:00:00',
        'room_id' => $this->room1->id,
        'status' => 'active',
    ]);

    $slots = $this->conflictChecker->getAvailableSlots($this->room1, Carbon::parse($date));

    // Should have slots before 8:00, between 10:00-11:00, and after 13:00
    expect($slots)->not->toBeEmpty();

    // Check that one slot is between 10:00 and 11:00
    $betweenSlot = collect($slots)->firstWhere('start', '10:00:00');
    expect($betweenSlot)->not->toBeNull();
});

test('room unavailability blocks scheduling when within time range', function () {
    $date = now()->format('Y-m-d');

    // Create room unavailability without times (all-day)
    \App\Models\RoomUnavailability::create([
        'room_id' => $this->room1->id,
        'start_date' => $date,
        'end_date' => $date,
        'reason' => 'Maintenance',
        'status' => 'active',
        'created_by' => $this->user->id,
    ]);

    $conflict = $this->conflictChecker->checkRoomConflict(
        $this->room1,
        Carbon::parse('08:00:00', 'Asia/Manila')->setDateFrom(Carbon::parse($date)),
        Carbon::parse('09:30:00', 'Asia/Manila')->setDateFrom(Carbon::parse($date))
    );

    expect($conflict)->not->toBeNull();
    expect($conflict->type)->toBe('ROOM_UNAVAILABLE');
});

test('cancelled occurrence does not block availability', function () {
    $date = now()->format('Y-m-d');

    // Create existing ClassMeeting with proper relationships
    $classOffering = ClassOffering::factory()->create([
        'academic_period_id' => $this->academicPeriod->id,
        'department_id' => $this->department->id,
        'subject_id' => $this->subject->id,
        'created_by' => $this->user->id,
    ]);

    $meeting = ClassMeeting::create([
        'class_offering_id' => $classOffering->id,
        'delivery_mode' => 'face_to_face',
        'room_id' => $this->room1->id,
        'day_of_week' => 1,
        'start_time' => '08:00:00',
        'end_time' => '09:30:00',
        'effective_start_date' => '2026-01-01',
        'effective_end_date' => now()->addYear()->format('Y-m-d'),
    ]);

    // Create cancelled occurrence
    ClassMeetingOccurrence::create([
        'class_meeting_id' => $meeting->id,
        'meeting_date' => $date,
        'start_time' => '08:00:00',
        'end_time' => '09:30:00',
        'room_id' => $this->room1->id,
        'status' => 'cancelled',
    ]);

    // Should not conflict with cancelled occurrence
    $conflict = $this->conflictChecker->checkRoomConflict(
        $this->room1,
        Carbon::parse('08:00:00', 'Asia/Manila')->setDateFrom(Carbon::parse($date)),
        Carbon::parse('09:30:00', 'Asia/Manila')->setDateFrom(Carbon::parse($date))
    );

    expect($conflict)->toBeNull();
});

test('two different rooms can be booked at same time', function () {
    $date = now()->format('Y-m-d');

    // Create existing ClassMeeting with proper relationships
    $classOffering = ClassOffering::factory()->create([
        'academic_period_id' => $this->academicPeriod->id,
        'department_id' => $this->department->id,
        'subject_id' => $this->subject->id,
        'created_by' => $this->user->id,
    ]);

    $meeting = ClassMeeting::create([
        'class_offering_id' => $classOffering->id,
        'delivery_mode' => 'face_to_face',
        'room_id' => $this->room1->id,
        'day_of_week' => 1,
        'start_time' => '08:00:00',
        'end_time' => '09:30:00',
        'effective_start_date' => '2026-01-01',
        'effective_end_date' => now()->addYear()->format('Y-m-d'),
    ]);

    // Create meeting occurrence in room1
    ClassMeetingOccurrence::create([
        'class_meeting_id' => $meeting->id,
        'meeting_date' => $date,
        'start_time' => '08:00:00',
        'end_time' => '09:30:00',
        'room_id' => $this->room1->id,
        'status' => 'active',
    ]);

    // Room2 at same time should be fine
    $conflict = $this->conflictChecker->checkRoomConflict(
        $this->room2,
        Carbon::parse('08:00:00', 'Asia/Manila')->setDateFrom(Carbon::parse($date)),
        Carbon::parse('09:30:00', 'Asia/Manila')->setDateFrom(Carbon::parse($date))
    );

    expect($conflict)->toBeNull();
});

test('conflict result has proper conflict type constants', function () {
    $conflict = ConflictResult::roomConflict(
        Carbon::now(),
        '08:00:00',
        '09:30:00',
        1,
        'Room A'
    );

    expect($conflict->type)->toBe('ROOM_CONFLICT');
});

test('faculty conflict result has proper structure', function () {
    $conflict = ConflictResult::facultyConflict(
        Carbon::now(),
        '08:00:00',
        '09:30:00',
        1,
        'Dr. Smith',
        10,
        'class_offering'
    );

    expect($conflict->type)->toBe('FACULTY_CONFLICT');
    expect($conflict->facultyId)->toBe(1);
    expect($conflict->facultyName)->toBe('Dr. Smith');
    expect($conflict->conflictingEntityId)->toBe(10);
});

test('room unavailable result has proper structure', function () {
    $conflict = ConflictResult::roomUnavailable(
        Carbon::now(),
        '08:00:00',
        '12:00:00',
        1,
        'Maintenance'
    );

    expect($conflict->type)->toBe('ROOM_UNAVAILABLE');
    expect($conflict->roomId)->toBe(1);
});

test('academic period locked conflict result', function () {
    $conflict = ConflictResult::academicPeriodLocked('2026-2027', 'First Semester');

    expect($conflict->type)->toBe('ACADEMIC_PERIOD_LOCKED');
    expect($conflict->message)->toContain('locked');
});