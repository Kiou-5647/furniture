<?php

namespace App\Services\Public;

use App\Models\Product\Category;
use App\Models\Setting\Lookup;
use App\Models\Setting\LookupNamespace;
use App\Support\CacheKeys;
use App\Support\CacheTag;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class ShopMenuService
{
    /**
     * Get rooms with image URLs for the carousel.
     */
    public function getRooms(): Collection
    {
        return Cache::tags([CacheTag::CategoryRooms->value])
            ->remember(CacheTag::CategoryRooms->key('shop_rooms'), CacheKeys::TTL, fn() => $this->buildRooms());
    }

    protected function buildRooms(): Collection
    {
        $roomNs = LookupNamespace::where('slug', 'phong')->first();
        if (! $roomNs) {
            return collect();
        }

        return $roomNs->activeLookups()->get()->map(function (Lookup $room) {
            return [
                'id' => $room->id,
                'label' => $room->display_name,
                'slug' => $room->slug,
                'image_url' => $room->getFirstMediaUrl('image', 'webp') ?: null,
            ];
        });
    }

    public function getMenu(): Collection
    {
        return Cache::tags([CacheTag::ShopMenu->value])
            ->remember(CacheTag::ShopMenu->key('data'), CacheKeys::TTL, fn() => $this->buildMenu());
    }

    protected function buildMenu(): Collection
    {
        $roomNs = LookupNamespace::where('slug', 'phong')->first();
        if (! $roomNs) {
            return collect();
        }

        $rooms = $roomNs->activeLookups()->get();

        // Get active categories and count products for each
        $categories = Category::where('is_active', true)
            ->with(['rooms', 'group'])
            ->withCount('products')
            ->get();

        return $rooms->map(function (Lookup $room) use ($categories) {
            // Filter categories belonging to this room
            $roomCategories = $categories->filter(fn(Category $c) => $c->rooms->contains('id', $room->id));

            // Group them by their group_id
            $grouped = $roomCategories->groupBy('group_id');

            // Process and sort groups
            $groups = $grouped->map(function (Collection $cats, $groupId) use ($roomCategories) {
                $group = $roomCategories->firstWhere('group_id', $groupId)?->group;

                return [
                    'group_obj' => $group,
                    'count' => $cats->count(),
                    'categories' => $cats->sortByDesc('products_count')->map(fn($c) => [
                        'id' => $c->id,
                        'label' => $c->display_name,
                        'slug' => $c->slug,
                    ])->values(),
                ];
            })
                ->sortByDesc('count') // Sort groups by the number of categories they contain
                ->map(function ($data) {
                    return [
                        'id' => $data['group_obj']?->id,
                        'label' => $data['group_obj']?->display_name ?? 'Khác',
                        'slug' => $data['group_obj']?->slug ?? 'other',
                        'categories' => $data['categories'],
                    ];
                })
                ->values();

            return [
                'id' => $room->id,
                'label' => $room->display_name,
                'slug' => $room->slug,
                'count' => $roomCategories->count(),
                'groups' => $groups,
                'image_url' => $room->getFirstMediaUrl('image', 'webp') ?: null,
            ];
        })
            ->sortByDesc(function ($room) {
                // Force 'trang-tri' to be last, otherwise sort by category count
                if ($room['slug'] === 'trang-tri') {
                    return -1;
                }
                return $room['count'];
            })
            ->values();
    }
}
