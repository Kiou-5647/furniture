<?php

namespace App\Services\Analytics;

use App\Models\Product\Bundle;
use App\Models\Product\ProductVariant;
use App\Support\CacheTag;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;

class ViewTrackingService
{
    private const TRACKED_SET = 'analytics:tracked_views';

    /**
     * Increment the view count for a specific reviewable entity.
     *
     * @param string $id The UUID of the entity
     * @param string $type The class name of the entity (e.g. ProductVariant::class or Bundle::class)
     */
    public function incrementView(string $id, string $type): void
    {
        $key = CacheTag::ProductViews->key("{$type}:{$id}");
        Cache::increment($key);
        Redis::sadd(self::TRACKED_SET, "{$type}:{$id}");
    }

    public function syncViewsToDatabase(): int
    {
        $trackedItems = Redis::smembers(self::TRACKED_SET);

        if (empty($trackedItems)) return 0;

        $syncCount = 0;

        DB::transaction(function () use ($trackedItems, &$syncCount) {
            foreach ($trackedItems as $compositeId) {
                $lastColonPos = strrpos($compositeId, ':');
                if ($lastColonPos === false) continue;

                $type = substr($compositeId, 0, $lastColonPos);
                $id = substr($compositeId, $lastColonPos + 1);

                $key = CacheTag::ProductViews->key($compositeId);
                $views = (int) Cache::get($key, 0);

                if ($views <= 0) continue;

                if ($type === ProductVariant::class) {
                    DB::table('product_variants')->where('id', $id)->increment('views_count', $views);

                    $variant = ProductVariant::find($id);
                    if ($variant && $variant->product) {
                        DB::table('products')->where('id', $variant->product_id)->increment('views_count', $views);

                        if ($variant->product_card_id) {
                            DB::table('product_cards')->where('id', $variant->product_card_id)->increment('views_count', $views);
                        }
                    }
                } elseif ($type === Bundle::class) {
                    DB::table('bundles')->where('id', $id)->increment('views_count', $views);
                }

                Cache::forget($key);
                $syncCount++;
            }
            Redis::del(self::TRACKED_SET);
        });

        return $syncCount;
    }
}
