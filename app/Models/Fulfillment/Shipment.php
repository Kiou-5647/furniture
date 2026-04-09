<?php

namespace App\Models\Fulfillment;

use App\Enums\ShipmentStatus;
use App\Models\Auth\User;
use App\Models\Commerce\Order;
use App\Models\Inventory\Location;
use App\Models\Vendor\Vendor;
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
            ->logOnly(['shipment_number', 'status', 'vendor_id', 'carrier', 'tracking_number', 'handled_by'])
            ->logOnlyDirty()
            ->dontLogEmptyChanges()
            ->setDescriptionForEvent(fn (string $eventName) => "Shipment {$eventName}");
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
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

    public function handledBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'handled_by');
    }

    public static function generateShipmentNumber(): string
    {
        $date = now()->format('Ymd');
        $last = self::whereDate('created_at', today())
            ->orderBy('shipment_number', 'desc')
            ->first();

        $sequence = $last ? (int) substr($last->shipment_number, -4) + 1 : 1;

        return 'SHP-'.$date.'-'.str_pad($sequence, 4, '0', STR_PAD_LEFT);
    }

    public function canBeShipped(): bool
    {
        return $this->status === ShipmentStatus::Pending;
    }

    public function canBeDelivered(): bool
    {
        return $this->status === ShipmentStatus::Shipped;
    }
}
