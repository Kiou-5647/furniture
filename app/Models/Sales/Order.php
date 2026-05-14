<?php

namespace App\Models\Sales;

use App\Builders\Sales\OrderBuilder;
use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Enums\ShipmentStatus;
use App\Models\Customer\Customer;
use App\Models\Fulfillment\Shipment;
use App\Models\Fulfillment\ShippingMethod;
use App\Models\Hr\Employee;
use App\Models\Inventory\Location;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

/**
 * @method static OrderBuilder|Order query()
 */
class Order extends Model
{
    use HasFactory, HasUuids, LogsActivity, SoftDeletes;

    protected $table = 'orders';

    protected function casts(): array
    {
        return [
            'total_amount' => 'decimal:2',
            'total_items' => 'integer',
            'shipping_cost' => 'decimal:2',
            'status' => OrderStatus::class,
            'payment_method' => PaymentMethod::class,
            'paid_at' => 'datetime',
        ];
    }

    public function newEloquentBuilder($query): OrderBuilder
    {
        return new OrderBuilder($query);
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['order_number', 'status', 'total_amount', 'customer_id', 'accepted_by'])
            ->logOnlyDirty()
            ->dontLogEmptyChanges()
            ->setDescriptionForEvent(fn(string $eventName) => "Order {$eventName}");
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function shipments(): HasMany
    {
        return $this->hasMany(Shipment::class);
    }

    public function invoices(): MorphMany
    {
        return $this->morphMany(Invoice::class, 'invoiceable');
    }

    public function refunds(): HasMany
    {
        return $this->hasMany(Refund::class);
    }

    public function acceptedBy(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'accepted_by');
    }

    public function storeLocation(): BelongsTo
    {
        return $this->belongsTo(Location::class, 'store_location_id');
    }

    public function shippingMethod(): BelongsTo
    {
        return $this->belongsTo(ShippingMethod::class, 'shipping_method_id');
    }

    public function getShippingAddressText(): string
    {
        $parts = [
            $this->street ?? null,
            $this->ward_name,
            $this->province_name,
        ];

        $filteredParts = array_filter($parts);

        return implode(', ', $filteredParts) ?: '—';
    }

    public static function generateOrderNumber(): string
    {
        $date = now()->format('dmy');

        do {
            $number = 'ORD-' . $date . '-' . self::randomToken();
        } while (self::where('order_number', $number)->exists());

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

    public function isCod(): bool
    {
        return $this->payment_method === PaymentMethod::Cod;
    }

    public function isPrepaid(): bool
    {
        return $this->payment_method !== null && $this->payment_method->isPrepaid();
    }

    public function isFullyPaid(): bool
    {
        $invoice = $this->invoices()->first();
        if (! $invoice) {
            return false;
        }

        return $invoice->amount_paid >= $invoice->amount_due;
    }

    public function canBeAccepted(): bool
    {
        return $this->status === OrderStatus::Pending;
    }

    public function canBeMarkedPaid(): bool
    {
        if ($this->paid_at !== null || $this->status !== OrderStatus::Processing) {
            return false;
        }

        if (in_array($this->payment_method, [PaymentMethod::Cod, PaymentMethod::Cash], true)) {

            if ($this->payment_method === PaymentMethod::Cod) {
                if ($this->shipments()->doesntExist()) {
                    return false;
                }

                $hasPendingItems = $this->shipments()
                    ->whereHas('items', fn($q) => $q->whereNotIn('status', [ShipmentStatus::Delivered, ShipmentStatus::Returned]))
                    ->exists();

                if ($hasPendingItems) {
                    return false;
                }
            }
        } else {
            return false;
        }

        return true;
    }

    public function canBeBankTranfered(): bool
    {
        if ($this->paid_at !== null || $this->status !== OrderStatus::Processing) {
            return false;
        }

        if ($this->payment_method === PaymentMethod::BankTransfer) {
            $hasPendingItems = $this->shipments()
                ->whereHas('items', fn($q) => $q->whereNotIn('status', [ShipmentStatus::Delivered, ShipmentStatus::Returned]))
                ->exists();

            if ($hasPendingItems) {
                return false;
            }
        } else {
            return false;
        }

        return true;
    }

    public function canBeCompleted(): bool
    {
        if ($this->status !== OrderStatus::Processing || $this->paid_at === null) {
            return false;
        }

        if ($this->shipping_method_id) {
            if ($this->shipments()->where('status', '!=', ShipmentStatus::Cancelled)->doesntExist()) {
                return false;
            }

            $hasPendingItems = $this->shipments()
                ->where('status', '!=', ShipmentStatus::Cancelled)
                ->whereHas('items', fn($q) => $q->whereNotIn('status', [ShipmentStatus::Delivered, ShipmentStatus::Returned]))
                ->exists();

            if ($hasPendingItems) {
                return false;
            }
        } else {
            if (! $this->isFullyPaid()) {
                return false;
            }
        }

        return true;
    }

    public function canBeCancelled(): bool
    {
        if ($this->status === OrderStatus::Cancelled || $this->status === OrderStatus::Completed) {
            return false;
        }

        $hasActiveShipments = $this->shipments()
            ->whereIn('status', [ShipmentStatus::Shipped, ShipmentStatus::Delivered, ShipmentStatus::Returned])
            ->doesntExist();

        if (! $hasActiveShipments) {
            return false;
        }

        return true;
    }

    public function canCreateShipment(): bool
    {
        if ($this->payment_method !== PaymentMethod::Cod && $this->paid_at === null) {
            return false;
        }

        return $this->status === OrderStatus::Processing;
    }
}
