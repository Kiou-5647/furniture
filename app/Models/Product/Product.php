<?php

namespace App\Models\Product;

use App\Builders\Product\ProductBuilder;
use App\Enums\ProductStatus;
use App\Models\Inventory\Inventory;
use App\Models\Product\BundleContent;
use App\Models\Product\Category;
use App\Models\Product\ProductCard;
use App\Models\Product\ProductVariant;
use App\Models\Vendor\Vendor;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

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
 */
class Product extends Model implements HasMedia
{
    use HasFactory, HasUuids, LogsActivity, SoftDeletes, InteractsWithMedia;

    protected $table = 'products';

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
            'views_count' => 'integer',
            'sales_count' => 'integer',
            'reviews_count' => 'integer',
            'average_rating' => 'decimal:2',
            'min_price' => 'decimal:2',
            'max_price' => 'decimal:2',

            'is_featured' => 'boolean',
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
        $this->addMediaCollection('manual_file')->singleFile();
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

    public function productCards(): HasMany
    {
        return $this->hasMany(ProductCard::class);
    }

    public function bundleContents(): HasMany
    {
        return $this->hasMany(BundleContent::class);
    }

    public function isInAnyBundle(): bool
    {
        return $this->bundleContents()->exists();
    }

    public function totalInventory(): HasManyThrough
    {
        return $this->hasManyThrough(Inventory::class, ProductVariant::class);
    }

    public function getTotalStock(): int
    {
        return $this->totalInventory()->sum('quantity');
    }

    public function getAvailableStock(): int
    {
        return $this->getTotalStock();
    }

    public function isInStock(): bool
    {
        return $this->getAvailableStock() > 0;
    }

    public function requiresAssembly(): bool
    {
        return ($this->assembly_info['required'] ?? false) === true;
    }

    public function syncSalesCount(): void
    {
        $this->update([
            'sales_count' => $this->variants()->sum('sales_count')
        ]);
    }

    public function syncMetricsFromVariants(): void
    {
        $metrics = $this->variants()
            ->selectRaw('SUM(views_count) as total_views, SUM(reviews_count) as total_reviews, AVG(average_rating) as avg_rating')
            ->first();

        $this->update([
            'views_count' => $metrics->total_views ?? 0,
            'reviews_count' => $metrics->total_reviews ?? 0,
            'average_rating' => $metrics->avg_rating ?? 0,
        ]);
    }

    public function syncPrices(): void
    {
        $prices = $this->variants()->pluck('price');
        if ($prices->isEmpty()) {
            $this->update(['min_price' => 0, 'max_price' => 0]);
            return;
        }

        $this->update([
            'min_price' => $prices->min(),
            'max_price' => $prices->max(),
        ]);
    }
}
