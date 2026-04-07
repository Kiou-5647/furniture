<?php

namespace App\Services\Cache;

use App\Models\Product\Category;
use App\Models\Setting\LookupNamespace;

class CacheInvalidator
{
    public function __construct(
        private CacheService $cache,
    ) {}

    public function onLookupChanged(): void
    {
        $this->cache->flushLookups();
    }

    public function onLookupNamespaceChanged(?LookupNamespace $namespace = null): void
    {
        $this->cache->flushLookups();

        if ($namespace === null) {
            $this->cache->flushProducts();
            $this->cache->flushCategories();

            return;
        }

        if ($namespace->for_variants) {
            $this->cache->flushVariantOptions();
        }

        if ($namespace->slug === 'tinh-nang') {
            $this->cache->flushFeatureOptions();
        }

        if (! in_array($namespace->slug, ['tinh-nang', 'nhom-danh-muc'])) {
            $this->cache->flushSpecNamespaces();
        }

        if ($namespace->slug === 'nhom-danh-muc') {
            $this->cache->flushCategoryOptions();
        }

        if ($namespace->slug === 'phong') {
            $this->cache->flushRoomOptions();
        }
    }

    public function onCategoryChanged(?Category $category = null): void
    {
        $this->cache->flushCategories();

        if ($category !== null) {
            $this->cache->flushCategoryFilters($category->slug);
        }
    }

    public function onCollectionChanged(): void
    {
        $this->cache->flushCollectionOptions();
    }

    public function onVendorChanged(): void
    {
        $this->cache->flushVendorOptions();
    }

    public function onLocationChanged(): void
    {
        $this->cache->flushInventory();
    }
}
