<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['campus_id', 'name', 'code', 'is_active'])]
class Building extends Model
{
    use HasFactory;

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