<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

#[Fillable(['employee_no', 'first_name', 'middle_name', 'last_name', 'suffix', 'email', 'status', 'highest_degree_earned', 'specialization'])]
class Faculty extends Model
{
    protected $table = 'faculty';

    protected $fillable = [
        'employee_no',
        'first_name',
        'middle_name',
        'last_name',
        'suffix',
        'email',
        'status',
        'highest_degree_earned',
        'specialization',
    ];

    protected $casts = [
        'employee_no' => 'string',
    ];

    public function departments()
    {
        return $this->belongsToMany(Department::class, 'faculty_department')
            ->withPivot(['is_primary', 'effective_from', 'effective_until'])
            ->withTimestamps();
    }

    public function getFullNameAttribute()
    {
        $parts = array_filter([
            $this->first_name,
            $this->middle_name,
            $this->last_name,
            $this->suffix,
        ]);

        return implode(' ', $parts);
    }
}