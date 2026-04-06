<?php

namespace App\Builders\Product;

use App\Enums\ProductType;
use App\Models\Setting\Lookup;
use App\Models\Setting\LookupNamespace;
use Illuminate\Database\Eloquent\Builder;

class CategoryBuilder extends Builder
{
    public function byCategoryGroup(Lookup $group): self
    {
        $ns = LookupNamespace::where('slug', 'nhom-danh-muc')->first();
        if (! $ns || $group->namespace_id !== $ns->id) {
            return $this;
        }

        return $this->where('group_id', $group->id);
    }

    public function byProductType(ProductType $type): self
    {
        return $this->where('product_type', $type->value);
    }

    public function search(string $search): self
    {
        return $this->where(function ($query) use ($search) {
            $query->where('display_name', 'ilike', "%{$search}%")
                ->orWhere('slug', 'ilike', "%{$search}%");
        });
    }
}
