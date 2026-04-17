<?php

namespace App\Services\Analytics;

use App\Models\Product\ProductVariant;
use App\Models\Product\Bundle;
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
        // 1. Store the view count using a composite key: tag:type:id
        $key = CacheTag::ProductViews->key("{$type}:{$id}");
        Cache::increment($key);

        // 2. Add the composite key to the "dirty" set for later syncing
        Redis::sadd(self::TRACKED_SET, "{$type}:{$id}");
    }

    /**
     * Sync all tracked views from Redis back to the database.
     */
    public function syncViewsToDatabase(): int
    {
        // Get all composite keys (type:id) that were viewed since the last sync
        $trackedItems = Redis::smembers(self::TRACKED_SET);
        if (empty($trackedItems)) return 0;

        $syncCount = 0;
        
        DB::transaction(function () use ($trackedItems, &$syncCount) {
            foreach ($trackedItems as $compositeId) {
                // Split the composite key back into type and ID
                // Format is "type:id", but we must handle the possibility of namespaces in type
                // Since we use get_class(), we can use the last colon as the separator
                $lastColonPos = strrpos($compositeId, ':');
                if ($lastColonPos === false) continue;

                $type = substr($compositeId, 0, $lastColonPos);
                $id = substr($compositeId, $lastColonPos + 1);

                $key = CacheTag::ProductViews->key($compositeId);
                $views = (int) Cache::get($key, 0);

                if ($views <= 0) continue;

                if ($type === ProductVariant::class) {
                    // 1. Update Variant View Count
                    DB::table('product_variants')
                        ->where('id', $id)
                        ->increment('views_count', $views);

                    // 2. Update Parent Product View Count
                    $variant = ProductVariant::find($id);
                    if ($variant && $variant->product) {
                        DB::table('products')
                            ->where('id', $variant->product_id)
                            ->increment('views_count', $views);
                    }
                } elseif ($type === Bundle::class) {
                    // Update Bundle View Count
                    DB::table('bundles')
                        ->where('id', $id)
                        ->increment('views_count', $views);
                }

                // 3. Clear the specific counter and remove from the tracked set
                Cache::forget($key);
                $syncCount++;
            }
            
            // Clear the tracking set after processing
            Redis::del(self::TRACKED_SET);
        });

        return $syncCount;
    }
}
