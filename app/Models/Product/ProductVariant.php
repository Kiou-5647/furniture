<?php

namespace App\Models\Product;

use App\Models\Public\CartItem;
use App\Models\Customer\Review;
use App\Models\Inventory\Inventory;
use App\Models\Inventory\StockMovement;
use App\Models\Product\Product;
use App\Models\Product\ProductCard;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;
use Spatie\Image\Enums\Fit;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class ProductVariant extends Model implements HasMedia
{
    use HasFactory, HasUuids, InteractsWithMedia, LogsActivity, SoftDeletes;

    protected $table = 'product_variants';

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'sale_price' => 'decimal:2',
            'profit_margin_value' => 'decimal:2',
            'profit_margin_unit' => 'string',
            'weight' => 'array',
            'dimensions' => 'array',
            'option_values' => 'array',
            'features' => 'array',
            'specifications' => 'array',
            'care_instructions' => 'array',
            'views_count' => 'integer',
            'sales_count' => 'integer',
            'reviews_count' => 'integer',
            'average_rating' => 'decimal:2',
        ];
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('primary_image')->singleFile();
        $this->addMediaCollection('hover_image')->singleFile();
        $this->addMediaCollection('gallery')->onlyKeepLatest(10);
        $this->addMediaCollection('dimension_image')->singleFile();
        $this->addMediaCollection('swatch_image')->singleFile();
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
        $this->addMediaConversion('swatch')
            ->performOnCollections('swatch_image')
            ->fit(Fit::Contain, 60, 60)
            ->optimize();
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['sku', 'name', 'slug', 'price', 'profit_margin_value', 'profit_margin_unit', 'status'])
            ->logOnlyDirty()
            ->dontLogEmptyChanges()
            ->setDescriptionForEvent(fn(string $eventName) => "Product variant {$eventName}");
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function productCard(): BelongsTo
    {
        return $this->belongsTo(ProductCard::class);
    }

    public function inventories(): HasMany
    {
        return $this->hasMany(Inventory::class, 'variant_id');
    }

    public function stockMovements(): HasMany
    {
        return $this->hasMany(StockMovement::class, 'variant_id');
    }

    public function cartItems(): MorphMany
    {
        return $this->morphMany(CartItem::class, 'purchasable');
    }

    public function reviews(): MorphMany
    {
        return $this->morphMany(Review::class, 'reviewable');
    }

    public function getTotalStock(): int
    {
        return $this->inventories()->sum('quantity');
    }

    public function getAvailableStock(): int
    {
        return $this->getTotalStock();
    }

    public function isInStock(): bool
    {
        return $this->getAvailableStock() > 0;
    }

    public function getTotalValue(): string
    {
        $total = $this->inventories()
            ->selectRaw('SUM(cost_per_unit * quantity) as total')
            ->value('total');

        return number_format((float) ($total ?? 0), 0, ',', '.');
    }

    public function getDisplayPrice(): string
    {
        return number_format((float) $this->price, 0, ',', '.');
    }

    public function getAverageCostPerUnit(): ?float
    {
        $totalQty = $this->inventories()->sum('quantity');
        if ($totalQty === 0) {
            return null;
        }

        $totalValue = $this->inventories()
            ->selectRaw('SUM(cost_per_unit * quantity) as total')
            ->value('total');

        return (float) $totalValue / $totalQty;
    }

    public function calculatePrice(): float
    {
        $cost = $this->getAverageCostPerUnit();
        if ($cost === null || $cost <= 0) {
            return 0.0;
        }

        $margin = (float) ($this->profit_margin_value ?? 0);
        if ($margin <= 0) {
            return $cost;
        }

        if ($this->profit_margin_unit === 'percentage') {
            return $cost * (1 + $margin / 100);
        }

        return $cost + $margin;
    }

    public function isSellable(): bool
    {
        $hasStock = $this->getAvailableStock() > 0;
        $hasCost = $this->getAverageCostPerUnit() !== null && $this->getAverageCostPerUnit() > 0;

        return $hasStock && $hasCost;
    }
}
