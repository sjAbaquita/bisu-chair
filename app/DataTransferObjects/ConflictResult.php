<?php

namespace App\DataTransferObjects;

class ConflictResult
{
    public function __construct(
        public readonly string $type,
        public readonly string $message,
        public readonly ?string $conflictingEntityType = null,
        public readonly ?int $conflictingEntityId = null,
        public readonly ?\Carbon\Carbon $date = null,
        public readonly ?string $startTime = null,
        public readonly ?string $endTime = null,
        public readonly ?int $roomId = null,
        public readonly ?int $facultyId = null,
        public readonly ?string $roomName = null,
        public readonly ?string $facultyName = null
    ) {}

    public static function roomConflict(
        \Carbon\CarbonInterface $date,
        string $startTime,
        string $endTime,
        int $roomId,
        string $roomName,
        ?int $conflictingEntityId = null,
        ?string $conflictingEntityType = null
    ): self {
        return new self(
            type: 'ROOM_CONFLICT',
            message: "Room '{$roomName}' is already occupied at {$date->format('Y-m-d')} {$startTime}-{$endTime}.",
            conflictingEntityType: $conflictingEntityType,
            conflictingEntityId: $conflictingEntityId,
            date: \Carbon\Carbon::instance($date),
            startTime: $startTime,
            endTime: $endTime,
            roomId: $roomId,
            roomName: $roomName
        );
    }

    public static function facultyConflict(
        \Carbon\CarbonInterface $date,
        string $startTime,
        string $endTime,
        int $facultyId,
        string $facultyName,
        ?int $conflictingEntityId = null,
        ?string $conflictingEntityType = null
    ): self {
        return new self(
            type: 'FACULTY_CONFLICT',
            message: "Faculty '{$facultyName}' is assigned to overlapping classes at {$date->format('Y-m-d')} {$startTime}-{$endTime}.",
            conflictingEntityType: $conflictingEntityType,
            conflictingEntityId: $conflictingEntityId,
            date: \Carbon\Carbon::instance($date),
            startTime: $startTime,
            endTime: $endTime,
            facultyId: $facultyId,
            facultyName: $facultyName
        );
    }

    public static function roomUnavailable(
        \Carbon\CarbonInterface $date,
        string $startTime,
        string $endTime,
        int $roomId,
        string $reason
    ): self {
        return new self(
            type: 'ROOM_UNAVAILABLE',
            message: "Room is unavailable on {$date->format('Y-m-d')} {$startTime}-{$endTime}. Reason: {$reason}",
            date: \Carbon\Carbon::instance($date),
            startTime: $startTime,
            endTime: $endTime,
            roomId: $roomId
        );
    }

    public static function roomCapacityExceeded(
        int $roomId,
        string $roomName,
        int $classSize,
        int $capacity
    ): self {
        return new self(
            type: 'ROOM_CAPACITY_EXCEEDED',
            message: "Class size ({$classSize}) exceeds room capacity ({$capacity}) for room '{$roomName}'.",
            roomId: $roomId,
            roomName: $roomName
        );
    }

    public static function facultyNotAvailable(
        int $facultyId,
        string $facultyName
    ): self {
        return new self(
            type: 'FACULTY_NOT_AVAILABLE',
            message: "Faculty '{$facultyName}' is not available for this assignment.",
            facultyId: $facultyId,
            facultyName: $facultyName
        );
    }

    public static function academicPeriodLocked(
        string $academicYear,
        string $term
    ): self {
        return new self(
            type: 'ACADEMIC_PERIOD_LOCKED',
            message: "Academic period {$academicYear} {$term} is locked. No changes can be made."
        );
    }

    public function toArray(): array
    {
        return [
            'type' => $this->type,
            'message' => $this->message,
            'conflicting_entity_type' => $this->conflictingEntityType,
            'conflicting_entity_id' => $this->conflictingEntityId,
            'date' => $this->date?->format('Y-m-d'),
            'start_time' => $this->startTime,
            'end_time' => $this->endTime,
            'room_id' => $this->roomId,
            'faculty_id' => $this->facultyId,
            'room_name' => $this->roomName,
            'faculty_name' => $this->facultyName,
        ];
    }
}