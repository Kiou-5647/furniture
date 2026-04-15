<?php

namespace App\Support;

class CacheKeys
{
    public const TTL = 86400; // 24 hours

    /**
     * Build a unique cache key for filter-based queries.
     * Uses MD5 of serialized filter to ensure uniqueness.
     */
    public static function getFiltersKeys(string $name, $filter): string
    {
        return $name.':'.md5(serialize($filter));
    }
}
