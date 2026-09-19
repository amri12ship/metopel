<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class AttendanceLocation extends Model
{
    use HasFactory;
    protected $fillable = [
        'name',
        'address',
        'latitude',
        'longitude',
        'radius',
        'public_token',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'latitude' => 'float',
            'longitude' => 'float',
            'radius' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (self $location) {
            if (empty($location->public_token)) {
                $location->public_token = Str::random(40);
            }
        });
    }

    public function attendanceRecords(): HasMany
    {
        return $this->hasMany(AttendanceRecord::class);
    }

    public function isActive(): bool
    {
        return $this->is_active;
    }

    /**
     * Regenerate a new random public token.
     * The old token becomes invalid after saving.
     */
    public function regenerateToken(): self
    {
        $this->public_token = Str::random(40);

        return $this;
    }

    public function getQrUrl(): string
    {
        return route('absensi.scan', $this->public_token);
    }
}