<?php

namespace App\Support;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Redis;

/**
 * Cache domain tags.
 *
 * Each tag represents a logical cache domain. Call ->flush() to invalidate
 * ALL keys in that domain at once — no need to track individual keys.
 *
 * Usage in services:
 *   Cache::tags([CacheTag::Vendors->value])->remember(...)
 *
 * Usage in invalidation:
 *   CacheTag::Vendors->flush();
 */
enum CacheTag: string
{
    // === Product domain ===
    case Vendors = 'product.vendors';
    case Categories = 'product.categories';
    case Collections = 'product.collections';
    case VariantOptions = 'product.variant_options';
    case FeatureOptions = 'product.feature_options';
    case SpecNamespaces = 'product.spec_namespaces';
    case AllSpecLookups = 'product.all_spec_lookups';
    case SpecLookupPrefix = 'product.spec_lookup_options';
    case ShopMenu = 'product.shop_menu';

    // === Category domain ===
    case CategoryGroups = 'category.groups';
    case CategoryRooms = 'category.rooms';
    case CategoryFilters = 'category.filters';

    // === Lookup domain ===
    case Lookups = 'lookups';
    case LookupNamespaces = 'lookup_namespaces';
    case FilterableNamespaces = 'filterable_namespaces';

    // === Inventory domain ===
    case Locations = 'locations';
    case LocationOptionsSimple = 'location_options_simple';
    case LocationOptionsFull = 'location_options_full';

    // === Vendor domain ===
    case VendorsList = 'vendors_list';

    // === Stock transfer domain ===
    case StockTransfers = 'stock_transfers';

    // === Geodata domain ===
    case Geodata = 'geodata';

    /**
     * Flush ALL keys with this tag.
     */
    public function flush(): void
    {
        Cache::tags([$this->value])->flush();
    }

    /**
     * Build a cache key that includes this tag for remember().
     */
    public function key(string $suffix): string
    {
        return "{$this->value}:{$suffix}";
    }

    /**
     * Flush all spec lookup option keys by suffix pattern.
     * Used when we need to flush product.spec_lookup_options.* keys.
     */
    public function flushPrefix(string $prefix): void
    {
        $tag = $this->value;
        // For Redis: use SCAN + DEL pattern
        if (config('cache.default') === 'redis') {
            $fullPrefix = config('cache.prefix').":{$tag}.{$prefix}";
            $pattern = $fullPrefix.'*';
            $keys = Redis::keys($pattern);
            foreach ($keys as $key) {
                Cache::forget(str_replace(config('cache.prefix').':', '', $key));
            }
        }
        // For non-Redis, we can't do prefix matching, so flush the whole tag
        else {
            $this->flush();
        }
    }
}
