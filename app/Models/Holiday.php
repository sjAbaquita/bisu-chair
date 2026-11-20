<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Holiday extends Model
{
    protected $fillable = [
        'campus_id',
        'date',
        'name',
        'is_class_day',
    ];

    protected $casts = [
        'date' => 'date',
        'is_class_day' => 'boolean',
    ];

    public function campus(): BelongsTo
    {
        return $this->belongsTo(Campus::class);
    }

    public function isClassDay(): bool
    {
        return $this->is_class_day;
    }
}