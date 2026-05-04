<?php

namespace App\Models\Hr;

use App\Builders\Hr\DesignerBuilder;
use App\Models\Auth\User;
use App\Models\Booking\Booking;
use App\Models\Booking\DesignerAvailabilitySlot;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

/**
 * @method static DesignerBuilder|Designer query()
 */
class Designer extends Model implements HasMedia
{
    use HasUuids, HasFactory, InteractsWithMedia, LogsActivity, SoftDeletes;

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

    public function newEloquentBuilder($query): DesignerBuilder
    {
        return new DesignerBuilder($query);
    }

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

    public function availabilitySlots(): HasMany
    {
        return $this->hasMany(DesignerAvailabilitySlot::class);
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    public function isHostDesigner(): bool
    {
        return $this->employee_id !== null;
    }

    public function getDisplayNameAttribute(): ?string
    {
        return $this->full_name;
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('avatar')->singleFile();
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['full_name', 'phone', 'hourly_rate', 'auto_confirm_bookings', 'is_active'])
            ->logOnlyDirty()
            ->dontLogEmptyChanges()
            ->setDescriptionForEvent(fn (string $eventName) => "Designer {$eventName}");
    }
}
