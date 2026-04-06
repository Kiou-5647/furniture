<?php

namespace App\Models\Product;

use App\Models\Inventory\Inventory;
use App\Models\Inventory\StockMovement;
use App\Models\Inventory\StockValuation;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
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
            'compared_at_price' => 'decimal:2',
            'build_cost' => 'decimal:2',
            'weight' => 'array',
            'dimensions' => 'array',
            'option_values' => 'array',
            'features' => 'array',
            'specifications' => 'array',
            'care_instructions' => 'array',
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
            ->fit(Fit::Contain, 60, 60)
            ->optimize();
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['sku', 'title', 'slug', 'price', 'compared_at_price', 'build_cost', 'status'])
            ->logOnlyDirty()
            ->dontLogEmptyChanges()
            ->setDescriptionForEvent(fn (string $eventName) => "Product variant {$eventName}");
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function inventories(): HasMany
    {
        return $this->hasMany(Inventory::class, 'variant_id');
    }

    public function stockMovements(): HasMany
    {
        return $this->hasMany(StockMovement::class, 'variant_id');
    }

    public function stockValuations(): HasMany
    {
        return $this->hasMany(StockValuation::class, 'variant_id');
    }

    public function getTotalStock(): int
    {
        return $this->inventories()->sum('quantity_on_hand');
    }

    public function getAvailableStock(): int
    {
        return $this->inventories()->sum('quantity_available');
    }

    public function isInStock(): bool
    {
        if ($this->product && ($this->product->is_dropship || $this->product->is_custom_made)) {
            return $this->status === 'active';
        }

        return $this->getAvailableStock() > 0;
    }

    public function getTotalValue(): string
    {
        $total = $this->stockValuations()
            ->selectRaw('SUM(batch_cost * quantity_remaining) as total')
            ->value('total');

        return number_format((float) ($total ?? 0), 0, ',', '.');
    }

    public function getDisplayPrice(): string
    {
        return number_format((float) $this->price, 0, ',', '.');
    }

    public function hasDiscount(): bool
    {
        return $this->compared_at_price !== null
            && (float) $this->compared_at_price > (float) $this->price;
    }

    public function getDiscountAmount(): ?string
    {
        if (! $this->hasDiscount()) {
            return null;
        }

        return number_format((float) $this->compared_at_price - (float) $this->price, 0, ',', '.');
    }
}
