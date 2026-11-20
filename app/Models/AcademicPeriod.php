<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['academic_year', 'term', 'start_date', 'end_date', 'status', 'is_current'])]
class AcademicPeriod extends Model
{
    protected $fillable = [
        'academic_year',
        'term',
        'start_date',
        'end_date',
        'status',
        'is_current',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'is_current' => 'boolean',
    ];
}