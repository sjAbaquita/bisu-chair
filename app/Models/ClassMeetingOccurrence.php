<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClassMeetingOccurrence extends Model
{
    use HasFactory;

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
        'start_time' => 'string',
        'end_time' => 'string',
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

    public function getStartTimeAttribute($value): ?Carbon
    {
        return $value ? Carbon::parse($value) : null;
    }

    public function getEndTimeAttribute($value): ?Carbon
    {
        return $value ? Carbon::parse($value) : null;
    }

    public function setStartTimeAttribute($value): void
    {
        $this->attributes['start_time'] = $value ? Carbon::parse($value)->format('H:i:s') : null;
    }

    public function setEndTimeAttribute($value): void
    {
        $this->attributes['end_time'] = $value ? Carbon::parse($value)->format('H:i:s') : null;
    }

    public function overlapsWith(ClassMeetingOccurrence $other): bool
    {
        if ($this->meeting_date != $other->meeting_date) {
            return false;
        }

        return $this->start_time < $other->end_time && $this->end_time > $other->start_time;
    }
}