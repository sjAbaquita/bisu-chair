<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Section extends Model
{
    protected $fillable = [
        'department_id',
        'academic_period_id',
        'program_name',
        'year_level',
        'name',
        'expected_enrollment',
    ];

    protected $casts = [
        'expected_enrollment' => 'integer',
    ];

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function academicPeriod(): BelongsTo
    {
        return $this->belongsTo(AcademicPeriod::class);
    }

    public function classOfferings(): HasMany
    {
        return $this->hasMany(ClassOffering::class);
    }
}