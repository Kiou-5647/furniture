<?php

namespace App\Models\Product;

use App\Builders\Product\CollectionBuilder;
use Database\Factories\Product\CollectionFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @method static CollectionBuilder|Collection query()
 * @method static CollectionBuilder|Collection search(string $search)
 * @method static CollectionBuilder|Collection active()
 * @method static CollectionBuilder|Collection featured()
 */
class Collection extends Model
{
    /** @use HasFactory<CollectionFactory> */
    use HasFactory, SoftDeletes;

    protected $table = 'collections';

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'is_featured' => 'boolean',
            'metadata' => 'array',
        ];
    }

    public function newEloquentBuilder($query): CollectionBuilder
    {
        return new CollectionBuilder($query);
    }
}
