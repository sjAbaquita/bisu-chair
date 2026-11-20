<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['campus_id', 'name', 'code', 'is_active'])]
class Building extends Model
{
    protected $fillable = [
        'campus_id',
        'name',
        'code',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function campus()
    {
        return $this->belongsTo(Campus::class);
    }

    public function rooms()
    {
        return $this->hasMany(Room::class);
    }
}