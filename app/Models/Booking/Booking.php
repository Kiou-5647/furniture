<?php

namespace App\Models\Booking;

use App\Builders\Booking\BookingBuilder;
use App\Enums\BookingStatus;
use App\Enums\InvoiceStatus;
use App\Models\Customer\Customer;
use App\Models\Hr\Designer;
use App\Models\Sales\Invoice;
use App\Models\Sales\Refund;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Gate;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

class Booking extends Model
{
    use HasUuids, HasFactory, LogsActivity, SoftDeletes;

    protected $table = 'bookings';

    public function newEloquentBuilder($query): BookingBuilder
    {
        return new BookingBuilder($query);
    }

    protected function casts(): array
    {
        return [
            'status' => BookingStatus::class,
            'start_at' => 'datetime',
            'end_at' => 'datetime',
            'total_price' => 'decimal:2',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['status', 'designer_id', 'total_price', 'start_at', 'end_at'])
            ->logOnlyDirty()
            ->dontLogEmptyChanges()
            ->setDescriptionForEvent(fn(string $eventName) => "Booking {$eventName}");
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    public function designer(): BelongsTo
    {
        return $this->belongsTo(Designer::class);
    }

    public function invoice(): MorphOne
    {
        return $this->morphOne(Invoice::class, 'invoiceable');
    }

    public function getFullAddress(): string
    {
        $parts = [
            $this->street ?? null,
            $this->ward_name,
            $this->province_name,
        ];

        $filteredParts = array_filter($parts);

        return implode(', ', $filteredParts) ?: '—';
    }

    public function depositInvoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class, 'deposit_invoice_id');
    }

    public function finalInvoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class, 'final_invoice_id');
    }

    public function refunds(): HasMany
    {
        return $this->hasMany(Refund::class);
    }

    public function hasDepositPaid(): bool
    {
        return $this->depositInvoice?->status === InvoiceStatus::Paid;
    }

    public function isConfirmed(): bool
    {
        return $this->status === BookingStatus::Confirmed;
    }

    public function isPending(): bool
    {
        return in_array($this->status, [BookingStatus::PendingDeposit, BookingStatus::PendingConfirmation], true);
    }

    public static function generateBookingNumber(): string
    {
        $date = now()->format('dmy');

        do {
            $number = 'BKG-' . $date . '-' . self::randomToken();
        } while (self::where('booking_number', $number)->exists());

        return $number;
    }

    protected static function randomToken(int $length = 8): string
    {
        $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        $token = '';
        for ($i = 0; $i < $length; $i++) {
            $token .= $chars[random_int(0, strlen($chars) - 1)];
        }

        return $token;
    }

    public function canBeConfirmed(): bool
    {
        // 1. Check trạng thái
        if ($this->status !== BookingStatus::PendingConfirmation) {
            return false;
        }

        // 2. Check thời gian: Không thể xác nhận lịch đã quá hạn (trong quá khứ)
        if ($this->start_at && $this->start_at->isPast()) {
            return false;
        }

        return true;
    }

    public function canBeCancelled(): bool
    {
        // 1. Check trạng thái
        if (in_array($this->status, [BookingStatus::Completed, BookingStatus::Cancelled], true)) {
            return false;
        }

        return true;
    }

    public function canPayDeposit(): bool
    {
        // 1. Check trạng thái
        if ($this->status !== BookingStatus::PendingDeposit) {
            return false;
        }

        // 2. Check thời gian: Không thể đặt cọc cho lịch đã quá hạn
        if ($this->start_at && $this->start_at->isPast()) {
            return false;
        }

        // 3. Check trạng thái hóa đơn
        if ($this->depositInvoice?->status !== InvoiceStatus::Open) {
            return false;
        }

        return true;
    }

    public function canOpenInvoice(): bool
    {
        // 1. Check trạng thái
        if ($this->status !== BookingStatus::Confirmed) {
            return false;
        }

        // 2. Check thời gian: Chỉ cho phép thanh toán cuối khi đã kết thúc lịch hẹn
        if ($this->end_at && $this->end_at->isFuture()) {
            return false;
        }

        // 3. Check trạng thái hóa đơn
        if ($this->finalInvoice?->status !== InvoiceStatus::Draft) {
            return false;
        }

        return true;
    }

    public function canMarkFinalPaid(): bool
    {
        // 1. Check trạng thái
        if ($this->status !== BookingStatus::Confirmed) {
            return false;
        }

        // 2. Check thời gian: Chỉ cho phép thanh toán cuối khi đã kết thúc lịch hẹn
        if ($this->end_at && $this->end_at->isFuture()) {
            return false;
        }

        // 3. Check trạng thái hóa đơn
        if ($this->finalInvoice?->status !== InvoiceStatus::Open) {
            return false;
        }

        return true;
    }
}
