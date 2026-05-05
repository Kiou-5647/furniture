<?php

namespace App\Models\Sales;

use App\Builders\Sales\PaymentBuilder;
use App\Models\Customer\Customer;
use App\Models\Sales\PaymentAllocation;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

/**
 * @method static PaymentBuilder|Payment query()
 */
class Payment extends Model
{
    use HasUuids, LogsActivity, SoftDeletes;

    protected $table = 'payments';

    public function newEloquentBuilder($query): PaymentBuilder
    {
        return new PaymentBuilder($query);
    }

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'gateway_payload' => 'array',
        ];
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['gateway', 'transaction_id', 'amount'])
            ->logOnlyDirty()
            ->dontLogEmptyChanges()
            ->setDescriptionForEvent(fn(string $eventName) => "Payment {$eventName}");
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    public function allocations(): HasMany
    {
        return $this->hasMany(PaymentAllocation::class);
    }

    public function totalAllocated(): float
    {
        return $this->allocations()->sum('amount_applied');
    }
}
