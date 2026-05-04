<?php

namespace App\Services\Sales;

use App\Data\Sales\DiscountFilterData;
use App\Models\Sales\Discount;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

class DiscountService
{
    public function getFiltered(DiscountFilterData $filter): LengthAwarePaginator
    {
        $query = $this->applyFilters(Discount::query(), $filter);

        if ($filter->order_by && $filter->order_direction) {
            $query->orderBy($filter->order_by, $filter->order_direction);
        } else {
            $query->latest();
        }

        return $query->paginate($filter->per_page);
    }

    public function getTrashedFiltered(DiscountFilterData $filter): LengthAwarePaginator
    {
        $query = $this->applyFilters(Discount::onlyTrashed(), $filter);

        if ($filter->order_by && $filter->order_direction) {
            $query->orderBy($filter->order_by, $filter->order_direction);
        } else {
            $query->latest();
        }

        return $query->paginate($filter->per_page);
    }

    public function getDiscountableTypes(): array
    {
        return Discount::getDiscountableTypes();
    }

    public function getTargetVariants(string $search = ''): \Illuminate\Support\Collection
    {
        return \App\Models\Product\ProductVariant::query()
            ->with('product')
            ->when($search, function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('sku', 'like', "%{$search}%")
                      ->orWhere('name', 'like', "%{$search}%")
                      ->orWhereHas('product', fn($pq) => $pq->where('name', 'like', "%{$search}%"));
                });
            })
            ->get()
            ->map(fn($variant) => [
                'id' => $variant->id,
                'name' => $variant->name,
                'product_name' => $variant->product->name,
                'sku' => $variant->sku,
                'price' => $variant->price,
                'primary_image' => $variant->getFirstMediaUrl('primary_image', 'thumb'),
            ]);
    }

    public function getTargetProducts(string $search = ''): \Illuminate\Support\Collection
    {
        return \App\Models\Product\Product::query()
            ->when($search, function ($query) use ($search) {
                $query->where('name', 'like', "%{$search}%");
            })
            ->withCount('variants')
            ->get()
            ->map(function ($product) {
                $topVariant = $product->variants()->orderByDesc('sales_count')->first();
                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'variants_count' => $product->variants_count,
                    'primary_image' => $topVariant ? $topVariant->getFirstMediaUrl('primary_image', 'thumb') : null,
                ];
            });
    }

    public function getTargetCategories(string $search = ''): \Illuminate\Support\Collection
    {
        return \App\Models\Product\Category::query()
            ->when($search, function ($query) use ($search) {
                $query->where('display_name', 'like', "%{$search}%")
                      ->orWhere('name', 'like', "%{$search}%");
            })
            ->get()
            ->map(fn($category) => [
                'id' => $category->id,
                'name' => $category->display_name ?? $category->name,
                'image' => $category->getFirstMediaUrl('image', 'thumb'),
            ]);
    }

    public function getTargetCollections(string $search = ''): \Illuminate\Support\Collection
    {
        return \App\Models\Product\Collection::query()
            ->when($search, function ($query) use ($search) {
                $query->where('display_name', 'like', "%{$search}%")
                      ->orWhere('name', 'like', "%{$search}%");
            })
            ->get()
            ->map(fn($collection) => [
                'id' => $collection->id,
                'name' => $collection->display_name ?? $collection->name,
                'image' => $collection->getFirstMediaUrl('image', 'thumb'),
            ]);
    }

    public function getTargetVendors(string $search = ''): \Illuminate\Support\Collection
    {
        return \App\Models\Vendor\Vendor::query()
            ->when($search, function ($query) use ($search) {
                $query->where('name', 'like', "%{$search}%");
            })
            ->get()
            ->map(fn($vendor) => [
                'id' => $vendor->id,
                'name' => $vendor->name,
            ]);
    }

    private function applyFilters(Builder $query, DiscountFilterData $filter)

    {
        if ($filter->search) {
            $searchTerm = "%{$filter->search}%";

            $query->where(function ($q) use ($searchTerm) {
                // 1. Search by Discount Name
                $q->where('name', 'like', $searchTerm);

                // 2. Search by Category name (if target is category)
                $q->orWhereHasMorph(
                    'discountable',
                    [\App\Models\Product\Category::class],
                    fn($query) => $query->where('display_name', 'like', $searchTerm)
                );

                // 3. Search by Collection name (if target is collection)
                $q->orWhereHasMorph(
                    'discountable',
                    [\App\Models\Product\Collection::class],
                    fn($query) => $query->where('display_name', 'like', $searchTerm)
                );

                // 4. Search by Vendor name (if target is vendor)
                $q->orWhereHasMorph(
                    'discountable',
                    [\App\Models\Vendor\Vendor::class],
                    fn($query) => $query->where('name', 'like', $searchTerm)
                );
            });
        }
        if ($filter->type) {
            $query->where('type', $filter->type);
        }
        if ($filter->discountable_type) {
            $query->where('discountable_type', $filter->discountable_type);
        }
        if ($filter->discountable_id) {
            $query->where('discountable_id', $filter->discountable_id);
        }
        if (!is_null($filter->is_active)) {
            $query->where('is_active', $filter->is_active);
        }
        if ($filter->start_after) {
            $query->where('start_at', '>=', $filter->start_after);
        }
        if ($filter->end_before) {
            $query->where('end_at', '<=', $filter->end_before);
        }
        return $query;
    }
}
