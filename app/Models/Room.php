<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['building_id', 'college_id', 'code', 'name', 'room_type', 'capacity', 'features', 'status', 'notes'])]
class Room extends Model
{
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
}