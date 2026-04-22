<?php

namespace App\Models\Product;

use App\Builders\Product\BundleBuilder;
use App\Models\Public\CartItem;
use App\Models\Customer\Review;
use App\Models\Product\BundleContent;
use App\Models\Product\Product;
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
            'views_count' => 'integer',
            'reviews_count' => 'integer',
            'average_rating' => 'decimal:2',
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

    public function contents(): HasMany
    {
        return $this->hasMany(BundleContent::class);
    }

    public function cartItems(): MorphMany
    {
        return $this->morphMany(CartItem::class, 'purchasable');
    }

    public function reviews(): MorphMany
    {
        return $this->morphMany(Review::class, 'reviewable');
    }

    public function products(): HasManyThrough
    {
        return $this->hasManyThrough(
            Product::class,
            BundleContent::class,
            'bundle_id',
            'id',
            'id',
            'product_id'
        );
    }

    public function calculateBundlePrice(): float
    {
        $individualTotal = 0.0;

        foreach ($this->contents as $content) {
            $product = $content->product;
            if ($product) {
                $individualTotal += (float) $product->min_price * $content->quantity;
            }
        }

        return match ($this->discount_type) {
            'percentage' => max(0, $individualTotal * (1 - (float) $this->discount_value / 100)),
            'fixed_amount' => max(0, $individualTotal - (float) $this->discount_value),
            'fixed_price' => (float) $this->discount_value,
            default => $individualTotal,
        };
    }

    public function isValid(): bool
    {
        if ($this->contents->isEmpty()) {
            return false;
        }

        return $this->contents->every(fn(BundleContent $content) => $content->product !== null);
    }
}
