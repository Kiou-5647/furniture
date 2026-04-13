<?php

namespace App\Actions\Product;

use App\Models\Product\Product;

class SyncProductFilterableOptionsAction
{
    public function execute(string $productId): void
    {
        $product = Product::with('variants')->find($productId);
        if (! $product) {
            return;
        }

        $filterableOptions = $this->collectFilterableOptions($product);

        $product->updateQuietly(['filterable_options' => $filterableOptions]);
    }

    protected function collectFilterableOptions(Product $product): array
    {
        $filterableOptions = [];

        foreach ($product->option_groups ?? [] as $group) {
            $groupKey = $group['namespace'] ?? strtolower($group['name']);
            $fallbackKey = strtolower($group['name']);
            $values = collect($group['options'] ?? [])
                ->filter(fn ($option) => $product->variants->contains(
                    fn ($v) => ($v->option_values[$groupKey] ?? $v->option_values[$fallbackKey] ?? null) === $option['value'],
                ))
                ->pluck('value')
                ->values()
                ->toArray();

            if (! empty($values)) {
                $filterableOptions[$groupKey] = $values;
            }
        }

        foreach ($product->specifications ?? [] as $group) {
            if (! ($group['is_filterable'] ?? false)) {
                continue;
            }
            $namespace = $group['lookup_namespace'] ?? null;
            if (! $namespace) {
                continue;
            }
            foreach ($group['items'] ?? [] as $item) {
                if ($slug = $item['lookup_slug'] ?? null) {
                    $filterableOptions[$namespace][] = $slug;
                }
            }
        }

        foreach ($product->features ?? [] as $feature) {
            if ($slug = $feature['lookup_slug'] ?? null) {
                $filterableOptions['tinh-nang'][] = $slug;
            }
        }

        foreach ($product->variants as $variant) {
            foreach ($variant->features ?? [] as $feature) {
                if ($slug = $feature['lookup_slug'] ?? null) {
                    $filterableOptions['tinh-nang'][] = $slug;
                }
            }
            foreach ($variant->specifications ?? [] as $group) {
                if (! ($group['is_filterable'] ?? false)) {
                    continue;
                }
                $namespace = $group['lookup_namespace'] ?? null;
                if (! $namespace) {
                    continue;
                }
                foreach ($group['items'] ?? [] as $item) {
                    if ($slug = $item['lookup_slug'] ?? null) {
                        $filterableOptions[$namespace][] = $slug;
                    }
                }
            }
        }

        foreach ($filterableOptions as &$vals) {
            $vals = array_values(array_unique($vals));
        }

        return $filterableOptions;
    }
}
