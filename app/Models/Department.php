<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

#[Fillable(['college_id', 'name', 'code', 'is_active'])]
class Department extends Model
{
    protected $fillable = [
        'college_id',
        'name',
        'code',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function college()
    {
        return $this->belongsTo(College::class);
    }

    public function faculty()
    {
        return $this->belongsToMany(Faculty::class, 'faculty_department')
            ->withPivot(['is_primary', 'effective_from', 'effective_until'])
            ->withTimestamps();
    }

    public function organizationalAssignments()
    {
        return $this->hasMany(OrganizationalAssignment::class);
    }
}