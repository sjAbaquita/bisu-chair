<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class ClassOffering extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'academic_period_id',
        'department_id',
        'section_id',
        'subject_id',
        'delivery_mode',
        'class_size',
        'status',
        'remarks',
        'created_by',
        'submitted_by',
        'approved_by',
        'submitted_at',
        'approved_at',
    ];

    protected $casts = [
        'class_size' => 'integer',
        'submitted_at' => 'datetime',
        'approved_at' => 'datetime',
    ];

    public function academicPeriod(): BelongsTo
    {
        return $this->belongsTo(AcademicPeriod::class);
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function section(): BelongsTo
    {
        return $this->belongsTo(Section::class);
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    public function faculty(): BelongsToMany
    {
        return $this->belongsToMany(Faculty::class, 'class_offering_faculty')
            ->withPivot('load_hours', 'load_units', 'role')
            ->withTimestamps();
    }

    public function classMeetings(): HasMany
    {
        return $this->hasMany(ClassMeeting::class);
    }

    public function meetingOccurrences(): HasMany
    {
        return $this->hasMany(ClassMeetingOccurrence::class, 'class_meeting_id');
    }

    public function isDraft(): bool
    {
        return $this->status === 'draft';
    }

    public function isSubmitted(): bool
    {
        return $this->status === 'submitted';
    }

    public function isForReview(): bool
    {
        return $this->status === 'for_review';
    }

    public function isReturned(): bool
    {
        return $this->status === 'returned';
    }

    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    public function isCancelled(): bool
    {
        return $this->status === 'cancelled';
    }

    public function isArchived(): bool
    {
        return $this->status === 'archived';
    }

    public function belongsToCollege(int $collegeId): bool
    {
        return $this->department->college_id === $collegeId;
    }

    public function canBeModifiedBy(int $userId): bool
    {
        if ($this->isApproved() || $this->isCancelled() || $this->isArchived()) {
            return false;
        }

        return in_array($this->status, ['draft', 'returned']);
    }
}