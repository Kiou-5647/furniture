<?php

namespace App\Models\Sales;

use App\Enums\InvoiceStatus;
use App\Enums\InvoiceType;
use App\Models\Employee\Employee;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

class Invoice extends Model
{
    use HasUuids, LogsActivity, SoftDeletes;

    protected $table = 'invoices';

    protected function casts(): array
    {
        return [
            'amount_due' => 'decimal:2',
            'amount_paid' => 'decimal:2',
            'type' => InvoiceType::class,
            'status' => InvoiceStatus::class,
        ];
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['invoice_number', 'type', 'status', 'amount_due', 'amount_paid', 'validated_by'])
            ->logOnlyDirty()
            ->dontLogEmptyChanges()
            ->setDescriptionForEvent(fn (string $eventName) => "Invoice {$eventName}");
    }

    public function invoiceable(): MorphTo
    {
        return $this->morphTo();
    }

    public function allocations(): HasMany
    {
        return $this->hasMany(PaymentAllocation::class);
    }

    public function validatedBy(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'validated_by');
    }

    public function remainingBalance(): float
    {
        return max(0, (float) $this->amount_due - (float) $this->amount_paid);
    }

    public function isFullyPaid(): bool
    {
        return $this->remainingBalance() <= 0;
    }
}
