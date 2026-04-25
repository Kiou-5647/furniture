<?php

namespace App\Models\Product;

use App\Models\Product\ProductCard;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BundleContent extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'bundle_contents';

    protected function casts(): array
    {
        return [
            'quantity' => 'integer',
        ];
    }

    public function bundle(): BelongsTo
    {
        return $this->belongsTo(Bundle::class);
    }

    public function productCard(): BelongsTo
    {
        return $this->belongsTo(ProductCard::class);
    }

    // Add a helper to get the underlying product if still needed
    public function product(): BelongsTo
    {
        return $this->productCard()->join('products', 'product_cards.product_id', '=', 'products.id')
            ->select('products.*');
    }
}
