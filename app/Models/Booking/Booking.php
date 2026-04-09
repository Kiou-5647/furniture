<?php

namespace App\Models\Booking;

use App\Enums\BookingStatus;
use App\Enums\DesignServiceType;
use App\Models\Auth\User;
use App\Models\Employee\Employee;
use App\Models\Sales\Invoice;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

class Booking extends Model
{
    use HasUuids, LogsActivity, SoftDeletes;

    protected $table = 'bookings';

    protected function casts(): array
    {
        return [
            'start_at' => 'datetime',
            'end_at' => 'datetime',
            'deadline_at' => 'datetime',
            'status' => BookingStatus::class,
        ];
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['status', 'designer_id', 'service_id', 'start_at', 'deadline_at'])
            ->logOnlyDirty()
            ->dontLogEmptyChanges()
            ->setDescriptionForEvent(fn (string $eventName) => "Booking {$eventName}");
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    public function designer(): BelongsTo
    {
        return $this->belongsTo(Designer::class);
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(DesignService::class, 'service_id');
    }

    public function acceptedBy(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'accepted_by');
    }

    public function invoice(): MorphOne
    {
        return $this->morphOne(Invoice::class, 'invoiceable');
    }

    public function isConsultation(): bool
    {
        return $this->service?->type === DesignServiceType::Consultation;
    }

    public function isCustomBuild(): bool
    {
        return $this->service?->type === DesignServiceType::CustomBuild;
    }

    public function requiresSchedule(): bool
    {
        return $this->service?->is_schedule_blocking ?? false;
    }

    public function canBeConfirmed(): bool
    {
        return $this->status === BookingStatus::PendingConfirmation;
    }

    public function canBeCancelled(): bool
    {
        return in_array($this->status, [BookingStatus::PendingDeposit, BookingStatus::PendingConfirmation, BookingStatus::Confirmed], true);
    }
}
