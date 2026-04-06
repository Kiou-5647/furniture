<?php

namespace App\Services\Cache;

use App\Models\Setting\LookupNamespace;
use App\Support\CacheKeys;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Redis;

class CacheService
{
    public function flushProducts(): void
    {
        Cache::forget(CacheKeys::product('vendors'));
        Cache::forget(CacheKeys::product('categories'));
        Cache::forget(CacheKeys::product('collections'));
        Cache::forget(CacheKeys::product('variant_options'));
        Cache::forget(CacheKeys::product('feature_options'));
        Cache::forget(CacheKeys::product('spec_namespaces'));
        Cache::forget(CacheKeys::product('all_spec_lookup_options'));
        $this->flushProductSpecLookups();
    }

    public function flushCategories(): void
    {
        Cache::forget(CacheKeys::category('groups'));
        Cache::forget(CacheKeys::category('rooms'));
    }

    public function flushLookups(): void
    {
        Cache::forget(CacheKeys::lookup('namespaces'));
        Cache::forget(CacheKeys::lookup('filterable_namespaces'));
    }

    public function flushVendorOptions(): void
    {
        Cache::forget(CacheKeys::product('vendors'));
    }

    public function flushCategoryOptions(): void
    {
        Cache::forget(CacheKeys::product('categories'));
        Cache::forget(CacheKeys::category('groups'));
    }

    public function flushCollectionOptions(): void
    {
        Cache::forget(CacheKeys::product('collections'));
    }

    public function flushVariantOptions(): void
    {
        Cache::forget(CacheKeys::product('variant_options'));
    }

    public function flushFeatureOptions(): void
    {
        Cache::forget(CacheKeys::product('feature_options'));
    }

    public function flushSpecNamespaces(): void
    {
        Cache::forget(CacheKeys::product('spec_namespaces'));
        Cache::forget(CacheKeys::product('all_spec_lookup_options'));
        $this->flushProductSpecLookups();
    }

    public function flushSpecLookupOptions(string $namespace): void
    {
        Cache::forget(CacheKeys::product("spec_lookup_options.{$namespace}"));
    }

    public function flushRoomOptions(): void
    {
        Cache::forget(CacheKeys::category('rooms'));
    }

    public function flushCategoryFilters(string $categorySlug): void
    {
        Cache::forget(CacheKeys::category("available_filters.{$categorySlug}"));
    }

    protected function flushProductSpecLookups(): void
    {
        if ($this->usingRedis()) {
            $this->flushByPattern('product.spec_lookup_options.*');
        } else {
            $slugs = LookupNamespace::query()
                ->whereNotIn('slug', ['tinh-nang', 'nhom-danh-muc'])
                ->pluck('slug');

            foreach ($slugs as $slug) {
                Cache::forget(CacheKeys::product("spec_lookup_options.{$slug}"));
            }
        }
    }

    protected function usingRedis(): bool
    {
        return config('cache.default') === 'redis';
    }

    protected function flushByPattern(string $pattern): void
    {
        $prefix = config('cache.prefix').':';
        $keys = Redis::keys($prefix.$pattern);

        foreach ($keys as $key) {
            Cache::forget(str_replace($prefix, '', $key));
        }
    }
}
