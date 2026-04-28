<?php

namespace App\Models\Public;

use App\Enums\ProductStatus;
use App\Models\Product\Bundle;
use App\Models\Product\ProductVariant;
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
        return (float) $this->getEffectivePrice() * $this->quantity;
    }

    public function getEffectivePrice(): float
    {
        if ($this->purchasable instanceof ProductVariant) {
            // Use the Discount Service logic we built into the variant model
            return $this->purchasable->getEffectivePrice();
        }

        if ($this->purchasable instanceof Bundle) {
            // Use the Bundle's own internal pricing logic
            return (float) $this->purchasable->calculateBundlePrice();
        }

        return 0.0;
    }

    public function validateAvailability(): bool
    {
        if (! $this->purchasable) return false;

        if ($this->purchasable instanceof ProductVariant) {
            return $this->purchasable->isInStock() && $this->purchasable->product->status === ProductStatus::Published;
        }

        if ($this->purchasable instanceof Bundle) {
            return $this->purchasable->isValid($this->configuration ?? []);
        }

        return false;
    }
}
