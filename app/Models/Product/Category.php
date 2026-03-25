<?php

namespace App\Models\Product;

use App\Builders\Product\CategoryBuilder;
use App\Enums\ProductType;
use App\Models\Setting\Lookup;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @method static CategoryBuilder|Category query()
 * @method static CategoryBuilder|Category byCategoryGroup(Lookup $group)
 * @method static CategoryBuilder|Category byProductType(ProductType $type)
 * @method static CategoryBuilder|Category search(string $search)
 */
class Category extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'categories';

    protected function casts(): array
    {
        return [
            'product_type' => ProductType::class,
            'metadata' => 'array',
            'is_active' => 'boolean',
        ];
    }

    public function group()
    {
        return $this->belongsTo(Lookup::class, 'group_id');
    }

    public function rooms()
    {
        return $this->belongsToMany(Lookup::class, 'category_room', 'category_id', 'room_id');
    }

    public function newEloquentBuilder($query): CategoryBuilder
    {
        return new CategoryBuilder($query);
    }
}
