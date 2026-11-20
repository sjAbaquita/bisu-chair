<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClassMeetingOccurrence extends Model
{
    protected $fillable = [
        'class_meeting_id',
        'meeting_date',
        'start_time',
        'end_time',
        'room_id',
        'status',
    ];

    protected $casts = [
        'meeting_date' => 'date',
        'start_time' => 'time',
        'end_time' => 'time',
    ];

    public function classMeeting(): BelongsTo
    {
        return $this->belongsTo(ClassMeeting::class);
    }

    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function isCancelled(): bool
    {
        return $this->status === 'cancelled';
    }

    public function isRescheduled(): bool
    {
        return $this->status === 'rescheduled';
    }

    public function overlapsWith(ClassMeetingOccurrence $other): bool
    {
        if ($this->meeting_date !== $other->meeting_date) {
            return false;
        }

        return $this->start_time < $other->end_time && $this->end_time > $other->start_time;
    }
}