<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RoomUnavailability extends Model
{
    protected $fillable = [
        'room_id',
        'start_date',
        'end_date',
        'start_time',
        'end_time',
        'reason',
        'status',
        'created_by',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'start_time' => 'time',
        'end_time' => 'time',
    ];

    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function overlapsWith($other): bool
    {
        if ($this->room_id !== $other->room_id) {
            return false;
        }

        // Date overlap check
        $dateOverlap = $this->start_date <= $other->end_date && $this->end_date >= $other->start_date;

        if (!$dateOverlap) {
            return false;
        }

        // If both have times, check time overlap
        if ($this->start_time && $other->start_time && $this->end_time && $other->end_time) {
            return $this->start_time < $other->end_time && $this->end_time > $other->start_time;
        }

        return $dateOverlap;
    }
}