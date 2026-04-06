<?php

namespace App\Services\Cache;

use App\Models\Product\Category;
use App\Models\Product\Collection;
use App\Models\Setting\Lookup;
use App\Models\Setting\LookupNamespace;
use App\Models\Vendor\Vendor;

class CacheInvalidator
{
    public function __construct(
        private CacheService $cache,
    ) {}

    public static function register(): void
    {
        $invalidator = app(self::class);

        Lookup::observe(fn () => $invalidator->onLookupChanged());
        LookupNamespace::observe(fn () => $invalidator->onLookupNamespaceChanged());
        Category::observe(fn () => $invalidator->onCategoryChanged());
        Collection::observe(fn () => $invalidator->onCollectionChanged());
        Vendor::observe(fn () => $invalidator->onVendorChanged());
    }

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

    public function onCategoryChanged(): void
    {
        $this->cache->flushCategories();
    }

    public function onCollectionChanged(): void
    {
        $this->cache->flushCollectionOptions();
    }

    public function onVendorChanged(): void
    {
        $this->cache->flushVendorOptions();
    }
}
