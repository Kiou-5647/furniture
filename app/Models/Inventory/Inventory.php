<?php

namespace App\Models\Inventory;

use App\Models\Product\ProductVariant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
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
            'quantity_on_hand' => 'integer',
            'quantity_reserved' => 'integer',
            'quantity_available' => 'integer',
            'reorder_level' => 'integer',
            'reorder_quantity' => 'integer',
            'cost_per_unit' => 'decimal:2',
        ];
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['quantity_on_hand', 'quantity_reserved', 'quantity_available', 'reorder_level', 'reorder_quantity', 'cost_per_unit'])
            ->logOnlyDirty()
            ->dontLogEmptyChanges()
            ->setDescriptionForEvent(fn (string $eventName) => "Inventory {$eventName}");
    }

    public function variant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class, 'variant_id');
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class, 'location_id');
    }

    public function stockMovements(): HasMany
    {
        return $this->hasMany(StockMovement::class, 'variant_id');
    }

    public function stockValuations(): HasMany
    {
        return $this->hasMany(StockValuation::class, 'variant_id');
    }

    public function getAvailableQty(): int
    {
        return $this->quantity_on_hand - $this->quantity_reserved;
    }

    public function isLowStock(): bool
    {
        return $this->getAvailableQty() <= $this->reorder_level;
    }

    public function needsReorder(): bool
    {
        return $this->isLowStock() && $this->getAvailableQty() > 0;
    }

    public function isOutOfStock(): bool
    {
        return $this->getAvailableQty() <= 0;
    }

    public function getTotalValue(): string
    {
        return number_format((float) $this->cost_per_unit * $this->quantity_on_hand, 0, ',', '.');
    }

    public function updateAvailable(): void
    {
        $this->quantity_available = $this->getAvailableQty();
        $this->save();
    }
}
