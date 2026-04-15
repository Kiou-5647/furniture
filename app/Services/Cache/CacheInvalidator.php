<?php

namespace App\Services\Cache;

use App\Models\Product\Category;
use App\Models\Setting\LookupNamespace;
use App\Support\CacheTag;
use Illuminate\Support\Facades\Cache;

class CacheInvalidator
{
    public function onLookupChanged(): void
    {
        CacheTag::Lookups->flush();
        CacheTag::LookupNamespaces->flush();
    }

    public function onLookupNamespaceChanged(?LookupNamespace $namespace = null): void
    {
        CacheTag::LookupNamespaces->flush();

        if ($namespace === null) {
            CacheTag::Vendors->flush();
            CacheTag::Categories->flush();
            CacheTag::CategoryGroups->flush();
            CacheTag::CategoryRooms->flush();
            CacheTag::VariantOptions->flush();
            CacheTag::FeatureOptions->flush();
            CacheTag::SpecNamespaces->flush();
            CacheTag::AllSpecLookups->flush();
            CacheTag::ShopMenu->flush();

            return;
        }

        if ($namespace->for_variants) {
            CacheTag::VariantOptions->flush();
        }

        if ($namespace->slug === 'tinh-nang') {
            CacheTag::FeatureOptions->flush();
        }

        if (! in_array($namespace->slug, ['tinh-nang', 'nhom-danh-muc'])) {
            CacheTag::SpecNamespaces->flush();
            CacheTag::AllSpecLookups->flush();
        }

        if ($namespace->slug === 'nhom-danh-muc') {
            CacheTag::Categories->flush();
            CacheTag::CategoryGroups->flush();
        }

        if ($namespace->slug === 'phong') {
            CacheTag::CategoryRooms->flush();
            CacheTag::ShopMenu->flush();
        }
    }

    public function onCategoryChanged(?Category $category = null): void
    {
        CacheTag::Categories->flush();
        CacheTag::CategoryGroups->flush();
        CacheTag::CategoryRooms->flush();
        CacheTag::ShopMenu->flush();
        CacheTag::CategoryFilters->flush();

        if ($category !== null) {
            // Flush specific category filter cache by tag prefix
            Cache::tags([CacheTag::CategoryFilters->value])->flush();
        }
    }

    public function onCollectionChanged(): void
    {
        CacheTag::Collections->flush();
    }

    public function onVendorChanged(): void
    {
        CacheTag::Vendors->flush();
        CacheTag::VendorsList->flush();
    }

    public function onLocationChanged(): void
    {
        CacheTag::Locations->flush();
    }

    public function onStockTransferChanged(): void
    {
        // No dedicated tag — transfers are not cached
    }

    public function onGeodataChanged(): void
    {
        CacheTag::Geodata->flush();
    }
}
