<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ClassMeeting extends Model
{
    use HasFactory;

    protected $fillable = [
        'class_offering_id',
        'delivery_mode',
        'room_id',
        'day_of_week',
        'start_time',
        'end_time',
        'effective_start_date',
        'effective_end_date',
        'online_platform',
        'meeting_url',
        'notes',
    ];

    protected $casts = [
        'day_of_week' => 'integer',
        'start_time' => 'string',
        'end_time' => 'string',
        'effective_start_date' => 'date',
        'effective_end_date' => 'date',
    ];

    public function classOffering(): BelongsTo
    {
        return $this->belongsTo(ClassOffering::class);
    }

    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }

    public function occurrences(): HasMany
    {
        return $this->hasMany(ClassMeetingOccurrence::class);
    }

    public function hasRoom(): bool
    {
        return !is_null($this->room_id);
    }

    public function isOnline(): bool
    {
        return in_array($this->delivery_mode, ['synchronous_online', 'asynchronous_online']);
    }

    public function isFaceToFace(): bool
    {
        return $this->delivery_mode === 'face_to_face';
    }

    public function requiresRoom(): bool
    {
        return $this->delivery_mode === 'face_to_face';
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

    public function isValidTimeRange(): bool
    {
        if (!$this->start_time || !$this->end_time) {
            return true; // No time range to validate
        }
        return $this->end_time > $this->start_time;
    }

    public function isValidDateRange(): bool
    {
        return $this->effective_end_date >= $this->effective_start_date;
    }
}