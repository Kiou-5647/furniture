<?php

namespace App\Models\Fulfillment;

use App\Models\Inventory\Location;
use App\Models\Sales\OrderItem;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ShipmentItem extends Model
{
    use HasUuids;

    protected $table = 'shipment_items';

    public $timestamps = true;

    protected function casts(): array
    {
        return [
            'quantity_shipped' => 'integer',
        ];
    }

    public function shipment(): BelongsTo
    {
        return $this->belongsTo(Shipment::class);
    }

    public function orderItem(): BelongsTo
    {
        return $this->belongsTo(OrderItem::class);
    }

    public function sourceLocation(): BelongsTo
    {
        return $this->belongsTo(Location::class, 'source_location_id');
    }
}
