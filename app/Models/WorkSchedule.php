<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class WorkSchedule extends Model
{
    use HasFactory;
    protected $fillable = [
        'name',
        'day',
        'start_time',
        'end_time',
        'late_tolerance',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'late_tolerance' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function employees(): BelongsToMany
    {
        return $this->belongsToMany(Employee::class)
            ->withTimestamps();
    }

    public function isActive(): bool
    {
        return $this->is_active;
    }
}