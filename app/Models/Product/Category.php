<?php

namespace App\Models\Product;

use App\Builders\Product\CategoryBuilder;
use App\Enums\ProductType;
use App\Models\Setting\Lookup;
use App\Models\Setting\LookupNamespace;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

/**
 * @method static CategoryBuilder|Category query()
 * @method static CategoryBuilder|Category byCategoryGroup(Lookup $group)
 * @method static CategoryBuilder|Category byProductType(ProductType $type)
 * @method static CategoryBuilder|Category search(string $search)
 */
class Category extends Model implements HasMedia
{
    use HasFactory, HasUuids, InteractsWithMedia, LogsActivity, SoftDeletes;

    protected $table = 'categories';

    protected function casts(): array
    {
        return [
            'product_type' => ProductType::class,
            'is_active' => 'boolean',
        ];
    }

    public function group()
    {
        return $this->belongsTo(Lookup::class, 'group_id');
    }

    public function rooms()
    {
        return $this->belongsToMany(Lookup::class, 'category_room_placement', 'category_id', 'room_id');
    }

    public function discounts()
    {
        return $this->morphMany(\App\Models\Sales\Discount::class, 'discountable');
    }

    // Relationship to Products (Inverse)
    public function products()
    {
        return $this->hasMany(\App\Models\Product\Product::class);
    }

    public function newEloquentBuilder($query): CategoryBuilder
    {
        return new CategoryBuilder($query);
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('image')->singleFile();
    }

    public function registerMediaConversions(?Media $media = null): void
    {
        $this->addMediaConversion('thumb')
            ->width(300)
            ->height(300)
            ->sharpen(10);
        $this->addMediaConversion('webp')
            ->format('webp')
            ->width(800);
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['group_id', 'product_type', 'display_name', 'slug', 'description', 'is_active'])
            ->logOnlyDirty()
            ->dontLogEmptyChanges()
            ->setDescriptionForEvent(fn(string $eventName) => "Category {$eventName}");
    }
}
