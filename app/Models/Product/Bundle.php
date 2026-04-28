<?php

namespace App\Models\Product;

use App\Builders\Product\BundleBuilder;
use App\Enums\ProductStatus;
use App\Models\Product\BundleContent;
use App\Models\Product\ProductCard;
use App\Models\Product\ProductVariant;
use App\Models\Public\CartItem;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

/**
 * @method static \App\Builders\Product\BundleBuilder|Bundle query()
 */
class Bundle extends Model implements HasMedia
{
    use HasFactory, HasUuids, InteractsWithMedia, LogsActivity, SoftDeletes;

    protected $table = 'bundles';

    protected function casts(): array
    {
        return [
            'discount_value' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    public function newEloquentBuilder($query): BundleBuilder
    {
        return new BundleBuilder($query);
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name', 'discount_type', 'discount_value', 'is_active'])
            ->logOnlyDirty()
            ->dontLogEmptyChanges()
            ->setDescriptionForEvent(fn(string $eventName) => "Bundle {$eventName}");
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('primary_image')->singleFile();
        $this->addMediaCollection('hover_image')->singleFile();
    }

    public function registerMediaConversions(?Media $media = null): void
    {
        $this->addMediaConversion('thumb')
            ->width(400)
            ->height(400)
            ->sharpen(10);
        $this->addMediaConversion('webp')
            ->format('webp')
            ->width(1200);
    }

    public function contents(): HasMany
    {
        return $this->hasMany(BundleContent::class);
    }

    public function cartItems(): MorphMany
    {
        return $this->morphMany(CartItem::class, 'purchasable');
    }

    public function productCards(): HasManyThrough
    {
        return $this->hasManyThrough(
            ProductCard::class,
            BundleContent::class,
            'bundle_id',
            'product_card_id',
            'id',
            'id'
        );
    }

    public function calculateBundlePrice(?array $configuration = null): float
    {
        $individualTotal = 0.0;

        foreach ($this->contents as $content) {
            $card = $content->productCard;
            if (!$card) continue;

            if ($configuration && isset($configuration[$content->id])) {
                $variantId = $configuration[$content->id];
                $variant = $card->variants()->find($variantId);

                $price = $variant ? $variant->getEffectivePrice() : 0;
                $individualTotal += (float) $price * $content->quantity;
            } else {
                $minEffectivePrice = $card->variants->min(fn($v) => $v->getEffectivePrice()) ?? 0;
                $individualTotal += (float) $minEffectivePrice * $content->quantity;
            }
        }

        return match ($this->discount_type) {
            'percentage' => max(0, $individualTotal * (1 - (float) $this->discount_value / 100)),
            'fixed_amount' => max(0, $individualTotal - (float) $this->discount_value),
            'fixed_price' => (float) $this->discount_value,
            default => $individualTotal,
        };
    }

    public function isValid(?array $configuration = null): bool
    {
        // 1. Basic structural check: Bundle must be active and have contents
        if (!$this->is_active || $this->contents->isEmpty()) {
            return false;
        }

        // 2. Structure check: All contents must link to a valid ProductCard
        if (!$this->contents->every(fn($content) => $content->productCard !== null)) {
            return false;
        }

        // 3. Real-time check: If a specific cart configuration is provided,
        // verify that every selected variant is actually in stock and published.
        if ($configuration !== null) {
            foreach ($this->contents as $content) {
                $variantId = $configuration[$content->id] ?? null;
                if (!$variantId) return false;

                /** @var \App\Models\Product\ProductVariant */
                $variant = ProductVariant::find($variantId);
                if (!$variant->isValid()) {
                    return false;
                }
            }
        }

        return true;
    }
}
