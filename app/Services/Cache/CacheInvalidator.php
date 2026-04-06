<?php

namespace App\Services\Cache;

use App\Models\Product\Category;
use App\Models\Product\Collection;
use App\Models\Product\Product as ProductCollection;
use App\Models\Setting\Lookup;
use App\Models\Setting\LookupNamespace;
use App\Models\Vendor\Vendor;
use App\Services\Product\CategoryService;
use App\Services\Product\ProductService;
use App\Services\Setting\LookupService;
use Illuminate\Support\Facades\Cache;

class CacheInvalidator
{
    public static function register(): void
    {
        static::lookupEvents();
        static::lookupNamespaceEvents();
        static::categoryEvents();
        static::collectionsEvents();
        static::vendorEvents();
        static::productEvents();
    }

    protected static function lookupEvents(): void
    {
        Lookup::saved(fn() => LookupService::clearCache());
        Lookup::deleted(fn() => LookupService::clearCache());

        Lookup::saved(fn() => Cache::forget('services.product.variant_options'));
        Lookup::deleted(fn() => Cache::forget('services.product.variant_options'));
        Lookup::saved(fn() => Cache::forget('services.product.feature_options'));
        Lookup::deleted(fn() => Cache::forget('services.product.feature_options'));
        Lookup::saved(fn() => Cache::forget('services.product.spec_namespaces'));
        Lookup::deleted(fn() => Cache::forget('services.product.spec_namespaces'));
        Lookup::saved(fn() => Cache::forget('services.product.spec_namespaces'));
        Lookup::deleted(fn() => Cache::forget('services.product.spec_lookup_options'));
        Lookup::saved(fn() => Cache::forget('services.product.all_spec_lookup_options'));
        Lookup::deleted(fn() => Cache::forget('services.product.all_spec_lookup_options'));
    }

    protected static function lookupNamespaceEvents(): void
    {
        LookupNamespace::saved(fn() => LookupService::clearCache());
        LookupNamespace::deleted(fn() => LookupService::clearCache());

        LookupNamespace::saved(fn() => Cache::forget('services.product.variant_options'));
        LookupNamespace::deleted(fn() => Cache::forget('services.product.variant_options'));
        LookupNamespace::saved(fn() => Cache::forget('services.product.feature_options'));
        LookupNamespace::deleted(fn() => Cache::forget('services.product.feature_options'));
        LookupNamespace::saved(fn() => Cache::forget('services.product.spec_namespaces'));
        LookupNamespace::deleted(fn() => Cache::forget('services.product.spec_namespaces'));
        LookupNamespace::saved(fn() => Cache::forget('services.product.spec_namespaces'));
        LookupNamespace::deleted(fn() => Cache::forget('services.product.spec_lookup_options'));
        LookupNamespace::saved(fn() => Cache::forget('services.product.all_spec_lookup_options'));
        LookupNamespace::deleted(fn() => Cache::forget('services.product.all_spec_lookup_options'));
        LookupNamespace::saved(fn() => CategoryService::clearCache());
        LookupNamespace::deleted(fn() => CategoryService::clearCache());
        LookupNamespace::saved(fn() => ProductService::clearCache());
        LookupNamespace::deleted(fn() => ProductService::clearCache());
    }

    protected static function categoryEvents(): void
    {
        Category::saved(fn() => CategoryService::clearCache());
        Category::deleted(fn() => CategoryService::clearCache());

        Category::saved(fn() => Cache::forget('services.product.categories'));
        Category::deleted(fn() => Cache::forget('services.product.categories'));
    }

    protected static function collectionsEvents(): void
    {
        Collection::saved(fn() => Cache::forget('services.product.collections'));
        Collection::deleted(fn() => Cache::forget('services.product.collections'));
    }

    protected static function vendorEvents(): void
    {
        Vendor::saved(fn() => Cache::forget('services.product.vendors'));
        Vendor::deleted(fn() => Cache::forget('services.product.vendors'));
    }

    protected static function productEvents(): void
    {
        ProductCollection::saved(fn() => Cache::forget('services.product.collections'));
        ProductCollection::deleted(fn() => Cache::forget('services.product.collections'));

        Lookup::saved(fn() => ProductService::clearCache());
        Lookup::deleted(fn() => ProductService::clearCache());
    }
}
