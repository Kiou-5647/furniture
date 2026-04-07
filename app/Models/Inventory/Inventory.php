<?php

namespace App\Models\Inventory;

use App\Models\Product\Product;
use App\Models\Product\ProductVariant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

class Inventory extends Model
{
    use HasFactory, HasUuids, LogsActivity, SoftDeletes;

    protected $table = 'inventories';

    protected function casts(): array
    {
        return [
            'quantity' => 'integer',
            'cost_per_unit' => 'decimal:2',
        ];
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['quantity', 'cost_per_unit'])
            ->logOnlyDirty()
            ->dontLogEmptyChanges()
            ->setDescriptionForEvent(fn (string $eventName) => "Inventory {$eventName}");
    }

    public function variant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class, 'variant_id');
    }

    public function product(): HasManyThrough
    {
        return $this->hasManyThrough(Product::class, ProductVariant::class, 'id', 'id', 'variant_id', 'product_id');
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class, 'location_id');
    }

    public function stockMovements(): HasMany
    {
        return $this->hasMany(StockMovement::class)
            ->where('variant_id', $this->variant_id)
            ->where('location_id', $this->location_id);
    }

    public function getTotalValue(): string
    {
        return number_format((float) $this->cost_per_unit * $this->quantity, 0, ',', '.');
    }
}
