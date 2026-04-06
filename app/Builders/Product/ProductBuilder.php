<?php

namespace App\Builders\Product;

use App\Enums\ProductStatus;
use App\Models\Product\Category;
use App\Models\Product\Collection;
use App\Models\Vendor\Vendor;
use Illuminate\Database\Eloquent\Builder;

class ProductBuilder extends Builder
{
    public function byStatus(ProductStatus $status): self
    {
        return $this->where('status', $status->value);
    }

    public function active(): self
    {
        return $this->where('status', ProductStatus::Published->value);
    }

    public function byVendor(Vendor $vendor): self
    {
        return $this->where('vendor_id', $vendor->id);
    }

    public function byCategory(Category $category): self
    {
        return $this->where('category_id', $category->id);
    }

    public function byCollection(Collection $collection): self
    {
        return $this->where('collection_id', $collection->id);
    }

    public function search(string $search): self
    {
        return $this->where(function ($query) use ($search) {
            $query->where('name', 'ilike', "%{$search}%")
                ->orWhere('slug', 'ilike', "%{$search}%");
        });
    }

    public function featured(): self
    {
        return $this->where('is_featured', true);
    }

    public function newArrivals(): self
    {
        return $this->where('is_new_arrival', true);
    }

    public function dropship(): self
    {
        return $this->where('is_dropship', true);
    }
}
