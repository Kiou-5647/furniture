<?php

namespace App\Models\Fulfillment;

use App\Builders\Fulfillment\ShippingMethodBuilder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @method static ShippingMethodBuilder|ShippingMethod query()
 */

use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;;

class ShippingMethod extends Model
{
    use HasUuids, SoftDeletes, LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logAll();
    }

    protected $table = 'shipping_methods';

    public function newEloquentBuilder($query): ShippingMethodBuilder
    {
        return new ShippingMethodBuilder($query);
    }

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'is_active' => 'boolean',
            'estimated_delivery_days' => 'integer',
        ];
    }

    public function shipments(): HasMany
    {
        return $this->hasMany(Shipment::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
