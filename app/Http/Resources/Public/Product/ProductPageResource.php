<?php

namespace App\Http\Resources\Public\Product;

use App\Models\Setting\Lookup;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductPageResource extends JsonResource
{
    public static $wrap = false;

    public function toArray(Request $request): array
    {
        $activeVariant = $request->attributes->get('activeVariant');
        $features = $this->resolveFeatures($activeVariant);

        return [
            'navigation_map' => $this->buildNavigationMap(),
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'option_groups' => $this->option_groups ?? [],
            'category' => [
                'name' => $this->category?->display_name,
                'slug' => $this->category?->slug,
                'product_type' => [
                    'name' => $this->category?->product_type->label(),
                    'slug' => $this->category?->product_type->value
                ],
                'room' => $this->category->room ? [
                    'name' => $this->category->room->display_name,
                    'slug' => $this->category->room->slug,
                ] : null,
                'group' => $this->category->group ? [
                    'name' => $this->category->group->display_name,
                    'slug' => $this->category->group->slug,
                ] : null,
            ],
            'collection' => [
                'name' => $this->collection?->display_name,
                'slug' => $this->collection?->slug,
            ],

            'featured_highlights' => $features['linked'],
            'plain_features' => $features['plain'],

            'specifications' => $this->resolveSpecifications($activeVariant),

            'care_information' => collect(array_merge(
                $this->care_instructions ?? [],
                $activeVariant->care_instructions ?? []
            ))->unique()->values()->all(),
            'assembly_information' => [
                'required' => $this->assembly_info['required'] ?? false,
                'estimated_minutes' => $this->assembly_info['estimated_minutes'] ?? null,
                'difficulty_level' => $this->assembly_info['difficulty_level'] ?? null,
                'additional_info' => $this->assembly_info['additional_info'] ?? null,
                'manual_url' => $this->getFirstMediaUrl('manual_file') ?? null,
            ],

            'active_variant' => [
                'id' => $activeVariant->id,
                'sku' => $activeVariant->sku,
                'slug' => $activeVariant->slug,
                'name' => $activeVariant->name,
                'description' => $activeVariant->description ?? null,
                'swatch_label' => $activeVariant->swatch_label ?? null,
                'price' => $activeVariant->price,
                'sale_price' => $activeVariant->getEffectivePrice(),
                'in_stock' => $activeVariant->getAvailableStock() > 0,
                'option_values' => $activeVariant->option_values,
                'sales_count' => $activeVariant->sales_count,
                'reviews_count' => $activeVariant->reviews_count ?? 0,
                'average_rating' => $activeVariant->average_rating ?? 0,
                'images' => [
                    'primary' => [
                        'full' => $activeVariant->getFirstMediaUrl('primary_image', 'webp') ?? $activeVariant->getFirstMediaUrl('primary_image'),
                        'thumb' => $activeVariant->getFirstMediaUrl('primary_image', 'thumb'),
                    ],
                    'hover' => [
                        'full' => $activeVariant->getFirstMediaUrl('hover_image', 'webp') ?? $activeVariant->getFirstMediaUrl('hover_image'),
                        'thumb' => $activeVariant->getFirstMediaUrl('hover_image', 'thumb'),
                    ],
                    'dimension' => [
                        'full' => $activeVariant->getFirstMediaUrl('dimension_image', 'webp') ?? $activeVariant->getFirstMediaUrl('dimension_image'),
                        'thumb' => $activeVariant->getFirstMediaUrl('dimension_image', 'thumb'),
                    ],
                    'swatch' => [
                        'full' => $activeVariant->getFirstMediaUrl('swatch_image', 'webp') ?? $activeVariant->getFirstMediaUrl('swatch_image'),
                        'thumb' => $activeVariant->getFirstMediaUrl('swatch_image', 'thumb'),
                        'swatch' => $activeVariant->getFirstMediaUrl('swatch_image', 'swatch'),
                    ],
                    'gallery' => $activeVariant->getMedia('gallery')->map(fn($media) => [
                        'full' => $media->getUrl('webp') ?? $media->getUrl(),
                        'thumb' => $media->getUrl('thumb'),
                    ])->toArray(),
                ]
            ],
            'variants' => $this->variants->map(fn($v) => [
                'id' => $v->id,
                'sku' => $v->sku,
                'slug' => $v->slug,
                'name' => $v->name,
                'swatch_label' => $v->swatch_label,
                'option_values' => $v->option_values,
                'images' => [
                    'swatch' => [
                        'full' => $v->getFirstMediaUrl('swatch_image', 'webp'),
                        'thumb' => $v->getFirstMediaUrl('swatch_image', 'thumb')
                    ],
                ],
            ]),
        ];
    }

    /**
     * Merges Product and Variant features and categorizes them by image availability.
     */
    private function resolveFeatures($activeVariant): array
    {
        $allFeatures = array_merge(
            $this->features ?? [],
            $activeVariant->features ?? []
        );

        $linked = [];
        $plain = [];

        foreach ($allFeatures as $feature) {
            $hasImage = false;
            $featureData = null;

            if (is_array($feature) && isset($feature['lookup_slug'])) {
                $lookup = Lookup::where('slug', $feature['lookup_slug'])->first();

                if ($lookup) {
                    $image = $lookup->getFirstMediaUrl('image', 'webp') ?? $lookup->getFirstMediaUrl('image');
                    if ($image) {
                        $hasImage = true;
                        $featureData = [
                            'name' => $lookup->display_name,
                            'image' => $image,
                            'description' => $lookup->description,
                        ];
                    }
                }
            }

            if ($hasImage) {
                $linked[] = $featureData;
            } else {
                // Fallback to plain feature
                $plain[] = [
                    'name' => is_array($feature) ? ($feature['display_name'] ?? 'Detail') : $feature,
                    'description' => is_array($feature) ? ($feature['description'] ?? '') : '',
                ];
            }
        }

        return [
            'linked' => $linked,
            'plain' => $plain,
        ];
    }

    private function resolveSpecifications($activeVariant): array
    {
        $productSpecs = $this->specifications ?? [];
        $variantSpecs = $activeVariant->specifications ?? [];

        $merged = $productSpecs;

        foreach ($variantSpecs as $categoryName => $data) {
            if (isset($merged[$categoryName])) {

                $merged[$categoryName]['items'] = array_merge(
                    $merged[$categoryName]['items'],
                    $data['items']
                );

                // Remove duplicates based on display_name
                $merged[$categoryName]['items'] = collect($merged[$categoryName]['items'])
                    ->unique('display_name')
                    ->values()
                    ->toArray();
            } else {
                // New category found only in variant
                $merged[$categoryName] = $data;
            }
        }

        return $merged;
    }

    /**
     * Build navigation map for non-swatch options.
     */
    private function buildNavigationMap(): array
    {
        $map = [];
        foreach ($this->option_groups as $group) {
            if ($group['is_swatches']) continue;

            foreach ($group['options'] as $option) {
                $firstVariant = $this->variants
                    ->where('option_values.' . $group['namespace'], $option['value'])
                    ->first();

                if ($firstVariant) {
                    $map[$group['namespace']][$option['value']] = "/san-pham/{$firstVariant->sku}/{$firstVariant->slug}";
                }
            }
        }
        return $map;
    }
}
