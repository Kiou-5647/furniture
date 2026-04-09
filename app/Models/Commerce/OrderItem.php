<?php

namespace App\Models\Commerce;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class OrderItem extends Model
{
    use HasUuids;

    protected $table = 'order_items';

    protected function casts(): array
    {
        return [
            'unit_price' => 'decimal:2',
            'configuration' => 'array',
            'quantity' => 'integer',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function purchasable(): MorphTo
    {
        return $this->morphTo();
    }

    public function getSubtotal(): float
    {
        return (float) $this->unit_price * $this->quantity;
    }
}
