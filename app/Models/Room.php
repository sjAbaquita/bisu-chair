<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['building_id', 'college_id', 'code', 'name', 'room_type', 'capacity', 'features', 'status', 'notes'])]
class Room extends Model
{
    use HasFactory;

    protected $fillable = [
        'building_id',
        'college_id',
        'code',
        'name',
        'room_type',
        'capacity',
        'features',
        'status',
        'notes',
    ];

    protected $casts = [
        'features' => 'array',
        'capacity' => 'integer',
    ];

    public function building()
    {
        return $this->belongsTo(Building::class);
    }

    public function college()
    {
        return $this->belongsTo(College::class);
    }

    public function classMeetings()
    {
        return $this->hasMany(ClassMeeting::class);
    }

    public function meetingOccurrences()
    {
        return $this->hasMany(ClassMeetingOccurrence::class, 'room_id');
    }
}