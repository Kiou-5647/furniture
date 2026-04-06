<?php

namespace App\Models\Product;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
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

    protected $attributes = [
        'weight' => '{}',
        'dimensions' => '{}',
        'option_values' => '{}',
        'features' => '[]',
        'specifications' => '{}',
        'care_instructions' => '[]',
    ];

    protected static function booted(): void
    {
        static::saving(function (self $variant) {
            $product = $variant->product;
            $productName = $product ? Str::title($product->name) : '';

            if (filled($variant->title)) {
                $title = trim($variant->title);
                if ($productName && ! str_starts_with(strtolower($title), strtolower($productName))) {
                    $title = $productName . ' ' . $title;
                }
                $variant->title = $title;
                $variant->slug = Str::slug($variant->title);
            } elseif ($productName) {
                $optionLabels = collect($variant->option_values ?? [])
                    ->map(fn($v) => Str::title($v))
                    ->implode(' ');
                $variant->title = $optionLabels
                    ? $productName . ' ' . $optionLabels
                    : $productName;
                $variant->slug = Str::slug($variant->title);
            }
        });

        static::forceDeleting(function (self $variant) {
            $variant->clearMediaCollection('primary_image');
            $variant->clearMediaCollection('hover_image');
            $variant->clearMediaCollection('gallery');
            $variant->clearMediaCollection('dimension_image');
            $variant->clearMediaCollection('swatch_image');
        });
    }

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
            ->setDescriptionForEvent(fn(string $eventName) => "Product variant {$eventName}");
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function isInStock(): bool
    {
        return $this->status === 'active';
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
