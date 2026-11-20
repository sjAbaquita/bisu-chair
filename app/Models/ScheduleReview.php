<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ScheduleReview extends Model
{
    protected $fillable = [
        'class_offering_id',
        'reviewer_id',
        'action',
        'comment',
        'acted_at',
    ];

    protected $casts = [
        'acted_at' => 'datetime',
    ];

    public function classOffering(): BelongsTo
    {
        return $this->belongsTo(ClassOffering::class);
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewer_id');
    }

    public function isSubmitted(): bool
    {
        return $this->action === 'submitted';
    }

    public function isApproved(): bool
    {
        return $this->action === 'approved';
    }

    public function isReturned(): bool
    {
        return $this->action === 'returned';
    }

    public function isRejected(): bool
    {
        return $this->action === 'rejected';
    }
}