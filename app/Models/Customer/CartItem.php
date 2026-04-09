<?php

namespace App\Models\Customer;

use App\Models\Product\Bundle;
use App\Models\Product\Product;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class CartItem extends Model
{
    use HasUuids;

    protected $table = 'cart_items';

    protected function casts(): array
    {
        return [
            'unit_price' => 'decimal:2',
            'configuration' => 'array',
            'quantity' => 'integer',
        ];
    }

    public function cart(): BelongsTo
    {
        return $this->belongsTo(Cart::class);
    }

    public function purchasable(): MorphTo
    {
        return $this->morphTo();
    }

    public function getSubtotal(): float
    {
        return (float) $this->unit_price * $this->quantity;
    }

    public function syncPriceFromPurchasable(): void
    {
        $price = match (get_class($this->purchasable)) {
            Product::class => $this->purchasable->min_price,
            Bundle::class => $this->purchasable->calculateBundlePrice(),
            default => $this->unit_price,
        };

        if ($price !== null && (float) $this->unit_price !== (float) $price) {
            $this->updateQuietly(['unit_price' => $price]);
        }
    }

    public function validateAvailability(): bool
    {
        if (! $this->purchasable) {
            return false;
        }

        if ($this->purchasable instanceof Product) {
            return $this->purchasable->status !== 'archived';
        }

        if ($this->purchasable instanceof Bundle) {
            return $this->purchasable->is_active && $this->purchasable->isValid();
        }

        return false;
    }
}
