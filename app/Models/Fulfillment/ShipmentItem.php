<?php

namespace App\Models\Fulfillment;

use App\Enums\ShipmentStatus;
use App\Models\Fulfillment\Shipment;
use App\Models\Product\ProductVariant;
use App\Models\Sales\OrderItem;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

class ShipmentItem extends Model
{
    use HasUuids, LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logAll();
    }

    protected $table = 'shipment_items';

    public $timestamps = true;

    protected function casts(): array
    {
        return [
            'quantity_shipped' => 'integer',
            'status' => ShipmentStatus::class,
        ];
    }

    public function shipment(): BelongsTo
    {
        return $this->belongsTo(Shipment::class);
    }

    public function variant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class, 'variant_id');
    }

    public function orderItem(): BelongsTo
    {
        return $this->belongsTo(OrderItem::class);
    }
}
