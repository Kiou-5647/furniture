<?php

namespace App\Models\Inventory;

use App\Builders\Inventory\StockMovementBuilder;
use App\Enums\StockMovementType;
use App\Models\Hr\Employee;
use App\Models\Product\ProductVariant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

class StockMovement extends Model
{
    use HasFactory, HasUuids, LogsActivity;

    protected $table = 'stock_movements';

    protected function casts(): array
    {
        return [
            'type' => StockMovementType::class,
            'quantity' => 'integer',
            'quantity_before' => 'integer',
            'quantity_after' => 'integer',
            'cost_per_unit' => 'decimal:2',
            'cost_per_unit_before' => 'decimal:2',
        ];
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['type', 'quantity', 'quantity_before', 'quantity_after', 'cost_per_unit', 'cost_per_unit_before', 'notes'])
            ->logOnlyDirty()
            ->dontLogEmptyChanges()
            ->setDescriptionForEvent(fn (string $eventName) => "Stock movement {$eventName}");
    }

    public function variant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class, 'variant_id');
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class, 'location_id');
    }

    public function performedBy(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'performed_by');
    }

    public function reference(): MorphTo
    {
        return $this->morphTo();
    }

    public function newEloquentBuilder($query): StockMovementBuilder
    {
        return new StockMovementBuilder($query);
    }
}
