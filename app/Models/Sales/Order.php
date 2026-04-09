<?php

namespace App\Models\Sales;

use App\Builders\Commerce\OrderBuilder;
use App\Enums\OrderStatus;
use App\Models\Auth\User;
use App\Models\Customer\CustomerAddress;
use App\Models\Employee\Employee;
use App\Models\Fulfillment\Shipment;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
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
            'status' => OrderStatus::class,
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

    public function shippingAddress(): BelongsTo
    {
        return $this->belongsTo(CustomerAddress::class, 'shipping_address_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function shipments(): HasMany
    {
        return $this->hasMany(Shipment::class);
    }

    public function acceptedBy(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'accepted_by');
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
        return in_array($this->status, [OrderStatus::Pending, OrderStatus::Processing], true);
    }

    public function canBeCompleted(): bool
    {
        return $this->status === OrderStatus::Processing;
    }
}
