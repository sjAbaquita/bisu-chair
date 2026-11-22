<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClassOfferingFaculty extends Model
{
    use HasFactory;

    protected $fillable = [
        'class_offering_id',
        'faculty_id',
        'load_hours',
        'load_units',
        'role',
    ];

    protected $casts = [
        'load_hours' => 'decimal:2',
        'load_units' => 'decimal:2',
    ];

    public function classOffering(): BelongsTo
    {
        return $this->belongsTo(ClassOffering::class);
    }

    public function faculty(): BelongsTo
    {
        return $this->belongsTo(Faculty::class);
    }
}