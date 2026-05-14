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

        // 1. Thu thập từ các nhóm tùy chọn
        foreach ($product->option_groups ?? [] as $group) {
            $groupKey = $group['namespace'] ?? strtolower($group['name']);
            $fallbackKey = strtolower($group['name']);

            // Lọc ra các giá trị tùy chọn thực sự có trong các biến thể của sản phẩm
            $values = collect($group['options'] ?? [])
                ->filter(fn($option) => $product->variants->contains(
                    fn($v) => ($v->option_values[$groupKey] ?? $v->option_values[$fallbackKey] ?? null) === $option['value'],
                ))
                ->pluck('value')
                ->values()
                ->toArray();

            if (! empty($values)) {
                $filterableOptions[$groupKey] = $values;
            }
        }

        // 2. Thu thập từ thông số kỹ thuật chung của sản phẩm
        foreach ($product->specifications ?? [] as $group) {
            // Chỉ lấy những nhóm được đánh dấu là có thể lọc
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

        // 3. Thu thập từ các tính năng của sản phẩm
        foreach ($product->features ?? [] as $feature) {
            if ($slug = $feature['lookup_slug'] ?? null) {
                $filterableOptions['tinh-nang'][] = $slug;
            }
        }

        // 4. Thu thập từ thông tin chi tiết của từng biến thể
        foreach ($product->variants as $variant) {
            // tính năng của biến thể
            foreach ($variant->features ?? [] as $feature) {
                if ($slug = $feature['lookup_slug'] ?? null) {
                    $filterableOptions['tinh-nang'][] = $slug;
                }
            }
            // Thông số kỹ thuật riêng của biến thể
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

        // Loại bỏ các giá trị trùng lặp và reset index mảng
        foreach ($filterableOptions as &$vals) {
            $vals = array_values(array_unique($vals));
        }

        return $filterableOptions;
    }
}
