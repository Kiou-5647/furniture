<?php

namespace App\Models\Booking;

use App\Builders\Booking\BookingBuilder;
use App\Enums\BookingStatus;
use App\Enums\DesignServiceType;
use App\Enums\InvoiceStatus;
use App\Models\Auth\User;
use App\Models\Hr\Designer;
use App\Models\Hr\Employee;
use App\Models\Sales\Invoice;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

class Booking extends Model
{
    use HasUuids, LogsActivity, SoftDeletes;

    protected $table = 'bookings';

    public function newEloquentBuilder($query): BookingBuilder
    {
        return new BookingBuilder($query);
    }

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

    public function depositInvoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class, 'deposit_invoice_id');
    }

    public function finalInvoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class, 'final_invoice_id');
    }

    public function sessions(): HasMany
    {
        return $this->hasMany(BookingSession::class);
    }

    /**
     * Check if the deposit invoice has been fully paid.
     */
    public function hasDepositPaid(): bool
    {
        return $this->depositInvoice?->status === InvoiceStatus::Paid;
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

    public function canPayDeposit(): bool
    {
        return $this->status === BookingStatus::PendingDeposit;
    }

    public function isConfirmed(): bool
    {
        return $this->status === BookingStatus::Confirmed;
    }

    public function isPending(): bool
    {
        return in_array($this->status, [BookingStatus::PendingDeposit, BookingStatus::PendingConfirmation], true);
    }
}
