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

        $categories = Category::where('is_active', true)
            ->with(['rooms', 'group'])
            ->orderBy('display_name')
            ->get();

        return $rooms->map(function (Lookup $room) use ($categories) {
            // FIX: Check if the room ID exists within the category's rooms collection
            $roomCategories = $categories->filter(fn(Category $c) => $c->rooms->contains('id', $room->id));

            $grouped = $roomCategories->groupBy('group_id');

            $groups = $grouped->map(function (Collection $cats, $groupId) use ($roomCategories) {
                $group = $roomCategories->firstWhere('group_id', $groupId)?->group;
                if (! $group) {
                    return [
                        'id' => null,
                        'label' => 'Khác',
                        'slug' => 'other',
                        'categories' => $cats->map(fn($c) => [
                            'id' => $c->id,
                            'label' => $c->display_name,
                            'slug' => $c->slug,
                        ])->values(),
                    ];
                }

                return [
                    'id' => $group->id,
                    'label' => $group->display_name,
                    'slug' => $group->slug,
                    'categories' => $cats->map(fn($c) => [
                        'id' => $c->id,
                        'label' => $c->display_name,
                        'slug' => $c->slug,
                    ])->values(),
                ];
            })->values();

            return [
                'id' => $room->id,
                'label' => $room->display_name,
                'slug' => $room->slug,
                'count' => $roomCategories->count(),
                'groups' => $groups,
                'image_url' => $room->getFirstMediaUrl('image', 'webp') ?: null,
            ];
        });
    }
}
