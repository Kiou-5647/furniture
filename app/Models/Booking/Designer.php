<?php

namespace App\Models\Booking;

use App\Models\Auth\User;
use App\Models\Employee\Employee;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Designer extends Model implements HasMedia
{
    use HasUuids, InteractsWithMedia, LogsActivity, SoftDeletes;

    protected $table = 'designers';

    public const DAYS_OF_WEEK = [
        0 => 'Chủ nhật',
        1 => 'Thứ hai',
        2 => 'Thứ ba',
        3 => 'Thứ tư',
        4 => 'Thứ năm',
        5 => 'Thứ sáu',
        6 => 'Thứ bảy',
    ];

    protected function casts(): array
    {
        return [
            'hourly_rate' => 'decimal:2',
            'auto_confirm_bookings' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function availabilities(): HasMany
    {
        return $this->hasMany(DesignerAvailability::class);
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    public function isHostDesigner(): bool
    {
        return $this->employee_id !== null;
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('avatar')->singleFile();
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name', 'code', 'manager_id', 'description', 'is_active'])
            ->logOnlyDirty()
            ->dontLogEmptyChanges()
            ->setDescriptionForEvent(fn (string $eventName) => "Designer {$eventName}");
    }
}
