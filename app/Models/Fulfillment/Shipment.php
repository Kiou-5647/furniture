<?php

namespace App\Models\Fulfillment;

use App\Enums\OrderStatus;
use App\Enums\ShipmentStatus;
use App\Models\Hr\Employee;
use App\Models\Inventory\Location;
use App\Models\Sales\Order;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

class Shipment extends Model
{
    use HasUuids, LogsActivity, SoftDeletes;

    protected $table = 'shipments';

    protected function casts(): array
    {
        return [
            'status' => ShipmentStatus::class,
        ];
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['shipment_number', 'status', 'shipped_by', 'delivered_by'])
            ->logOnlyDirty()
            ->dontLogEmptyChanges()
            ->setDescriptionForEvent(fn(string $eventName) => "Shipment {$eventName}");
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function originLocation(): BelongsTo
    {
        return $this->belongsTo(Location::class, 'origin_location_id');
    }

    public function shippingMethod(): BelongsTo
    {
        return $this->belongsTo(ShippingMethod::class, 'shipping_method_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(ShipmentItem::class);
    }

    public function shippedBy(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'shipped_by');
    }

    public function deliveredBy(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'delivered_by');
    }

    public function handledBy(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'handled_by');
    }

    public static function generateShipmentNumber(): string
    {
        $date = now()->format('dmy');

        do {
            $number = 'SHP-' . $date . '-' . self::randomToken();
        } while (self::where('shipment_number', $number)->exists());

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

    public function canBeShipped(): bool
    {
        return $this->status === ShipmentStatus::Pending;
    }

    public function canBeDelivered(): bool
    {
        return $this->status === ShipmentStatus::Shipped;
    }

    public function canBeCancelled(): bool
    {
        return in_array($this->status, [ShipmentStatus::Pending, ShipmentStatus::Shipped], true);
    }

    public function canBeResent(): bool
    {
        return $this->status === ShipmentStatus::Cancelled && $this->order?->status !== OrderStatus::Cancelled;
    }
}
