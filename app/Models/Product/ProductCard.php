<?php

namespace App\Models\Product;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use App\Models\Setting\Lookup;

class ProductCard extends Model
{
    use HasUuids;

    protected $table = 'product_cards';

    protected function casts(): array
    {
        return [
            'views_count' => 'integer',
            'sales_count' => 'integer',
            'reviews_count' => 'integer',
            'average_rating' => 'decimal:2',
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function variants(): HasMany
    {
        return $this->hasMany(ProductVariant::class);
    }

    public function options(): BelongsToMany
    {
        return $this->belongsToMany(Lookup::class, 'product_card_options', 'product_card_id', 'lookup_id');
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
}
