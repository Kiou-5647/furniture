<?php

namespace App\Support;

class CacheKeys
{
    public const TTL = 86400;

    public static function product(string $key): string
    {
        return "product.{$key}";
    }

    public static function category(string $key): string
    {
        return "category.{$key}";
    }

    public static function lookup(string $key): string
    {
        return "lookup.{$key}";
    }
}
