<?php

namespace App\Models\Inventory;

use App\Models\Product\ProductVariant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

class StockValuation extends Model
{
    use HasFactory, HasUuids, LogsActivity, SoftDeletes;

    protected $table = 'stock_valuations';

    protected function casts(): array
    {
        return [
            'batch_cost' => 'decimal:2',
            'quantity_remaining' => 'integer',
            'received_at' => 'datetime',
        ];
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['batch_cost', 'quantity_remaining'])
            ->logOnlyDirty()
            ->dontLogEmptyChanges()
            ->setDescriptionForEvent(fn (string $eventName) => "Stock valuation {$eventName}");
    }

    public function variant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class, 'variant_id');
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class, 'location_id');
    }

    public function getTotalValue(): string
    {
        return number_format((float) $this->batch_cost * $this->quantity_remaining, 0, ',', '.');
    }

    public function consume(int $quantity): int
    {
        $consumed = min($quantity, $this->quantity_remaining);
        $this->quantity_remaining -= $consumed;
        $this->save();

        return $consumed;
    }
}
