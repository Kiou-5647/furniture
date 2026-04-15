<?php

namespace App\Services\Product;

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
            ->remember(CacheTag::CategoryRooms->key('shop_rooms'), CacheKeys::TTL, fn () => $this->buildRooms());
    }

    protected function buildRooms(): Collection
    {
        $roomNs = LookupNamespace::where('slug', 'phong')->first();
        if (! $roomNs) {
            return collect();
        }

        $categories = Category::where('is_active', true)->pluck('room_id')->countBy();

        return $roomNs->activeLookups()->get()->map(function (Lookup $room) use ($categories) {
            return [
                'id' => $room->id,
                'label' => $room->display_name,
                'slug' => $room->slug,
                'count' => $categories[$room->id] ?? 0,
                'image_url' => $room->getFirstMediaUrl('image', 'webp') ?: null,
            ];
        });
    }

    public function getMenu(): Collection
    {
        return Cache::tags([CacheTag::ShopMenu->value])
            ->remember(CacheTag::ShopMenu->key('data'), CacheKeys::TTL, fn () => $this->buildMenu());
    }

    protected function buildMenu(): Collection
    {
        $roomNs = LookupNamespace::where('slug', 'phong')->first();
        if (! $roomNs) {
            return collect();
        }

        // Load all active rooms
        $rooms = $roomNs->activeLookups()->get();

        // Load categories with their room and group
        $categories = Category::where('is_active', true)
            ->with(['room', 'group'])
            ->orderBy('display_name')
            ->get();

        // Build hierarchy
        return $rooms->map(function (Lookup $room) use ($categories) {
            $roomCategories = $categories->filter(fn (Category $c) => $c->room_id === $room->id);

            // Group categories by their group lookup
            $grouped = $roomCategories->groupBy('group_id');

            $groups = $grouped->map(function (Collection $cats, $groupId) use ($roomCategories) {
                $group = $roomCategories->firstWhere('group_id', $groupId)?->group;
                if (! $group) {
                    return [
                        'id' => null,
                        'label' => 'Khác',
                        'slug' => 'other',
                        'categories' => $cats->map(fn ($c) => [
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
                    'categories' => $cats->map(fn ($c) => [
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
