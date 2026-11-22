<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Subject extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'title',
        'units',
        'lecture_hours',
        'laboratory_hours',
        'is_active',
    ];

    protected $casts = [
        'units' => 'decimal:2',
        'is_active' => 'boolean',
        'lecture_hours' => 'integer',
        'laboratory_hours' => 'integer',
    ];

    public function sections(): HasMany
    {
        return $this->hasMany(Section::class);
    }

    public function classOfferings(): HasMany
    {
        return $this->hasMany(ClassOffering::class);
    }
}