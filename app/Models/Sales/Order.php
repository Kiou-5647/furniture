<?php

namespace App\Models\Sales;

use App\Builders\Sales\OrderBuilder;
use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Enums\ShipmentStatus;
use App\Models\Auth\User;
use App\Models\Fulfillment\Shipment;
use App\Models\Fulfillment\ShippingMethod;
use App\Models\Hr\Employee;
use App\Models\Inventory\Location;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
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
    use HasUuids, LogsActivity, SoftDeletes;

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
            'address_data' => 'array',
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
            ->setDescriptionForEvent(fn (string $eventName) => "Order {$eventName}");
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'customer_id');
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
        $parts = array_filter([
            $this->address_data['address_number'] ?? null,
            $this->address_data['building'] ?? null,
            $this->ward_name,
            $this->province_name,
        ]);

        return implode(', ', $parts) ?: '—';
    }

    public static function generateOrderNumber(): string
    {
        $date = now()->format('Ymd');
        $lastOrder = self::whereDate('created_at', today())
            ->orderBy('order_number', 'desc')
            ->first();

        $sequence = $lastOrder ? (int) substr($lastOrder->order_number, -4) + 1 : 1;

        return 'ORD-'.$date.'-'.str_pad($sequence, 4, '0', STR_PAD_LEFT);
    }

    public function canBeCancelled(): bool
    {
        if ($this->status === OrderStatus::Cancelled) {
            return false;
        }

        // Cannot cancel if any shipment has been shipped or delivered
        $hasActiveShipments = $this->shipments()
            ->whereIn('status', [ShipmentStatus::Shipped, ShipmentStatus::Delivered, ShipmentStatus::Returned])
            ->exists();

        return ! $hasActiveShipments;
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

    public function canBeCompleted(): bool
    {
        if ($this->status !== OrderStatus::Processing || $this->paid_at === null) {
            return false;
        }

        // For shipping orders: require shipments to exist AND all items resolved
        if ($this->shipping_method_id) {
            if ($this->shipments()->doesntExist()) {
                return false;
            }
            $allResolved = $this->shipments()
                ->whereHas('items', fn ($q) => $q->whereNotIn('status', [ShipmentStatus::Delivered, ShipmentStatus::Returned]))
                ->doesntExist();

            return $allResolved;
        }

        // For in-store orders: no shipments needed
        return $this->isFullyPaid();
    }
}
