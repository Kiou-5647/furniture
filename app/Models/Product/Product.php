<?php

namespace App\Models\Product;

use App\Builders\Product\ProductBuilder;
use App\Enums\ProductStatus;
use App\Models\Vendor\Vendor;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

/**
 * @method static ProductBuilder|Product query()
 * @method static ProductBuilder|Product search(string $search)
 * @method static ProductBuilder|Product byStatus(ProductStatus $status)
 * @method static ProductBuilder|Product active()
 * @method static ProductBuilder|Product byVendor(Vendor $vendor)
 * @method static ProductBuilder|Product byCategory(Category $category)
 * @method static ProductBuilder|Product byCollection(Collection $collection)
 * @method static ProductBuilder|Product featured()
 * @method static ProductBuilder|Product newArrivals()
 * @method static ProductBuilder|Product dropship()
 */
class Product extends Model implements HasMedia
{
    use HasFactory, HasUuids, InteractsWithMedia, LogsActivity, SoftDeletes;

    protected $table = 'products';

    protected $attributes = [
        'features' => '[]',
        'specifications' => '{}',
        'option_groups' => '[]',
        'filterable_options' => '{}',
        'care_instructions' => '[]',
        'assembly_info' => '{}',
    ];

    protected static function booted(): void
    {
        static::deleting(function (Product $product) {
            $product->variants()->delete();
        });

        static::restoring(function (Product $product) {
            $product->variants()->restore();
        });

        static::forceDeleting(function (Product $product) {
            $product->variants()->forceDelete();
            $product->clearMediaCollection('images');
        });
    }

    protected function casts(): array
    {
        return [
            'status' => ProductStatus::class,
            'features' => 'array',
            'specifications' => 'array',
            'option_groups' => 'array',
            'filterable_options' => 'array',
            'care_instructions' => 'array',
            'assembly_info' => 'array',
            'search_keywords' => 'array',
            'warranty_months' => 'integer',
            'view_count' => 'integer',
            'review_count' => 'integer',
            'average_rating' => 'decimal:2',
            'min_price' => 'decimal:2',
            'max_price' => 'decimal:2',

            'is_featured' => 'boolean',
            'is_dropship' => 'boolean',
            'is_new_arrival' => 'boolean',
            'published_date' => 'datetime',
            'new_arrival_until' => 'datetime',
        ];
    }

    public function newEloquentBuilder($query): ProductBuilder
    {
        return new ProductBuilder($query);
    }

    public function registerMediaCollections(): void
    {
        // No product-level images; images are managed at variant level
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

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name', 'status', 'vendor_id', 'category_id', 'collection_id', 'average_rating', 'review_count', 'is_featured'])
            ->logOnlyDirty()
            ->dontLogEmptyChanges()
            ->setDescriptionForEvent(fn(string $eventName) => "Product {$eventName}");
    }

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function collection(): BelongsTo
    {
        return $this->belongsTo(Collection::class);
    }

    public function variants(): HasMany
    {
        return $this->hasMany(ProductVariant::class);
    }

    public function requiresAssembly(): bool
    {
        return ($this->assembly_info['required'] ?? false) === true;
    }
}
