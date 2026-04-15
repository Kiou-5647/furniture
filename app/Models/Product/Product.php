<?php

namespace App\Models\Product;

use App\Builders\Product\ProductBuilder;
use App\Enums\ProductStatus;
use App\Models\Inventory\Inventory;
use App\Models\Vendor\Vendor;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

/**
 * @method static ProductBuilder|Product query()
 * @method static ProductBuilder|Product search(string $search)
 * @method static ProductBuilder|Product byStatus(ProductStatus $status)
 * @method static ProductBuilder|Product active()
 * @method static ProductBuilder|Product byVendor(Vendor $vendor)
 * @method static ProductBuilder|Product byCategory(Category $category)
 * @method static ProductBuilder|Product byCollection(Collection $collection)
 * @method static ProductBuilder|Product featured()
 * @method static ProductBuilder|Product newArrivals()
 */
class Product extends Model
{
    use HasFactory, HasUuids, LogsActivity, SoftDeletes;

    protected $table = 'products';

    protected function casts(): array
    {
        return [
            'status' => ProductStatus::class,
            'features' => 'array',
            'specifications' => 'array',
            'option_groups' => 'array',
            'filterable_options' => 'array',
            'care_instructions' => 'array',
            'assembly_info' => 'array',
            'search_keywords' => 'array',
            'warranty_months' => 'integer',
            'view_count' => 'integer',
            'review_count' => 'integer',
            'average_rating' => 'decimal:2',
            'min_price' => 'decimal:2',
            'max_price' => 'decimal:2',

            'is_featured' => 'boolean',
            'is_new_arrival' => 'boolean',
            'published_date' => 'datetime',
            'new_arrival_until' => 'datetime',
        ];
    }

    public function newEloquentBuilder($query): ProductBuilder
    {
        return new ProductBuilder($query);
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name', 'status', 'vendor_id', 'category_id', 'collection_id', 'average_rating', 'review_count', 'is_featured'])
            ->logOnlyDirty()
            ->dontLogEmptyChanges()
            ->setDescriptionForEvent(fn(string $eventName) => "Product {$eventName}");
    }

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function collection(): BelongsTo
    {
        return $this->belongsTo(Collection::class);
    }

    public function variants(): HasMany
    {
        return $this->hasMany(ProductVariant::class);
    }

    public function bundleContents(): HasMany
    {
        return $this->hasMany(BundleContent::class);
    }

    public function isInAnyBundle(): bool
    {
        return $this->bundleContents()->exists();
    }

    public function totalInventory(): HasManyThrough
    {
        return $this->hasManyThrough(Inventory::class, ProductVariant::class);
    }

    /**
     * Resolve the active variant based on optional filter values.
     * Falls back to the first available variant.
     *
     * @param  array<string, string>  $filters  e.g. ['chat-lieu' => 'da', 'mau-sac' => 'charme-tan']
     */
    public function getResolvedVariant(array $filters = []): ?ProductVariant
    {
        $variants = $this->variants()->with(['inventories'])->get();

        // Try to find exact match for all filters
        if (! empty($filters)) {
            $match = $variants->first(function ($v) use ($filters) {
                foreach ($filters as $namespace => $value) {
                    if (($v->option_values[$namespace] ?? null) !== $value) {
                        return false;
                    }
                }

                return true;
            });

            if ($match) {
                return $match;
            }
        }

        // Fall back: return the first variant that matches any filter partially,
        // or the very first variant
        if (! empty($filters)) {
            $partial = $variants->first(fn($v) => collect($filters)->every(
                fn($val, $ns) => ($v->option_values[$ns] ?? null) === $val,
            ));
            if ($partial) {
                return $partial;
            }
        }

        return $variants->first();
    }

    /**
     * Build a serializable variant payload for the storefront card.
     */
    public function variantToPayload(ProductVariant $variant): array
    {
        return [
            'id' => $variant->id,
            'sku' => $variant->sku,
            'slug' => $variant->slug,
            'price' => $variant->price,
            'option_values' => $variant->option_values,
            'in_stock' => $variant->getAvailableStock() > 0,
            'primary_image_url' => $variant->getFirstMediaUrl('primary_image', 'webp') ?: null,
            'thumbnail_url' => $variant->getFirstMediaUrl('primary_image', 'thumb') ?: null,
            'swatch_image_url' => $variant->getFirstMediaUrl('swatch_image', 'thumb') ?: null,
        ];
    }

    /**
     * Get variants grouped into displayable product cards.
     *
     * Handles 3 scenarios:
     * 1. Both non-swatches + swatches → Multiple cards with nested swatches
     * 2. Only swatches → Single card with all swatch options
     * 3. Only non-swatches → All combinations as cards (e.g., 2 materials × 2 sizes = 4 cards)
     *
     * Return format:
     * [
     *   [
     *     'option_values' => ['chat-lieu' => 'da', 'kich-thuoc' => 'S'],
     *     'swatch_options' => [...],
     *     'variant_count' => 4,
     *   ],
     *   ...
     * ]
     */
    public function getGroupedVariantOptions(): array
    {
        $groups = $this->option_groups ?? [];
        $swatchGroup = collect($groups)->firstWhere('is_swatches', true);
        $nonSwatchGroups = collect($groups)->filter(fn($g) => ! $g['is_swatches'])->values();
        $variants = $this->variants()->with(['inventories'])->get();
        $swatchNamespace = $swatchGroup['namespace'] ?? null;

        // Scenario 2: Only swatches, no non-swatches groups
        // → Return single card with all swatch options
        if ($nonSwatchGroups->isEmpty() && $swatchGroup) {
            return $this->buildSwatchesOnlyCard($variants, $swatchGroup, $swatchNamespace);
        }

        // Scenario 1 & 3: Non-swatches groups exist
        // → Build all combinations of non-swatches options
        $combinations = $this->buildCombinations($nonSwatchGroups->toArray());

        return array_map(function (array $combo) use ($variants, $swatchGroup, $swatchNamespace) {
            // Find variants matching this exact non-swatches combination
            $matchingVariants = $this->filterVariantsByOptions($variants, $combo);

            // If no swatch group, return card with variant count only (Scenario 3)
            if (! $swatchGroup || $swatchNamespace === null) {
                return [
                    'option_values' => $combo,
                    'swatch_options' => [],
                    'variant_count' => $matchingVariants->count(),
                ];
            }

            // Build swatch options from matching variants (Scenario 1)
            $swatchOptions = $this->buildSwatchOptions($matchingVariants, $swatchGroup, $swatchNamespace);

            return [
                'option_values' => $combo,
                'swatch_options' => $swatchOptions,
                'variant_count' => count($swatchOptions),
            ];
        }, $combinations);
    }

    /**
     * Build a single card for products with only swatches options.
     */
    protected function buildSwatchesOnlyCard(object $variants, array $swatchGroup, ?string $swatchNamespace): array
    {
        if ($swatchNamespace === null) {
            return [[
                'option_values' => [],
                'swatch_options' => [],
                'variant_count' => 0,
            ]];
        }

        $swatchOptions = collect($swatchGroup['options'])
            ->map(function (array $opt) use ($variants, $swatchNamespace) {
                $variant = $variants->first(
                    fn($v) => ($v->option_values[$swatchNamespace] ?? null) === $opt['value']
                );

                if (! $variant) {
                    return null;
                }

                return [
                    'value' => $opt['value'],
                    'label' => $opt['label'],
                    'variant_id' => $variant->id,
                    'sku' => $variant->sku,
                    'slug' => $variant->slug,
                    'price' => $variant->price,
                    'in_stock' => $variant->getAvailableStock() > 0,
                    'primary_image_url' => $variant->getFirstMediaUrl('primary_image', 'webp') ?: null,
                    'swatch_image_url' => $variant->getFirstMediaUrl('swatch_image', 'thumb') ?: null,
                ];
            })
            ->filter()
            ->values()
            ->toArray();

        return [[
            'option_values' => [],
            'swatch_options' => $swatchOptions,
            'variant_count' => count($swatchOptions),
        ]];
    }

    /**
     * Filter variants by a set of option values.
     */
    protected function filterVariantsByOptions(object $variants, array $filters): object
    {
        return $variants->filter(function (ProductVariant $v) use ($filters) {
            foreach ($filters as $ns => $val) {
                if (($v->option_values[$ns] ?? null) !== $val) {
                    return false;
                }
            }

            return true;
        });
    }

    /**
     * Build swatch options from matching variants.
     */
    protected function buildSwatchOptions(object $matchingVariants, array $swatchGroup, string $swatchNamespace): array
    {
        return $matchingVariants->map(function (ProductVariant $variant) use ($swatchGroup, $swatchNamespace) {
            $genericValue = $variant->option_values[$swatchNamespace] ?? null;

            $genericOption = collect($swatchGroup['options'])->firstWhere('value', $genericValue);

            return [
                'value' => $genericValue,
                'label' => $variant->swatch_label ?? ($genericOption['label'] ?? 'Unknown'),
                'variant_id' => $variant->id,
                'sku' => $variant->sku,
                'slug' => $variant->slug,
                'price' => $variant->price,
                'in_stock' => $variant->getAvailableStock() > 0,
                'primary_image_url' => $variant->getFirstMediaUrl('primary_image', 'webp') ?: null,
                'swatch_image_url' => $variant->getFirstMediaUrl('swatch_image', 'thumb') ?: null,
            ];
        })->filter()->values()->toArray();
    }

    /**
     * Build all combinations of non-swatches option values.
     */
    protected function buildCombinations(array $groups): array
    {
        if (empty($groups)) {
            return [];
        }

        // Start with first group's options
        $result = [];
        foreach ($groups[0]['options'] as $opt) {
            $result[] = [$groups[0]['namespace'] => $opt['value']];
        }

        // Cross-product with remaining groups
        for ($i = 1; $i < count($groups); $i++) {
            $group = $groups[$i];
            $next = [];
            foreach ($result as $existing) {
                foreach ($group['options'] as $opt) {
                    $next[] = [...$existing, $group['namespace'] => $opt['value']];
                }
            }
            $result = $next;
        }

        return $result;
    }

    public function getTotalStock(): int
    {
        return $this->totalInventory()->sum('quantity');
    }

    public function getAvailableStock(): int
    {
        return $this->getTotalStock();
    }

    public function isInStock(): bool
    {
        if ($this->is_custom_made) {
            return $this->status === ProductStatus::Published;
        }

        return $this->getAvailableStock() > 0;
    }

    public function requiresAssembly(): bool
    {
        return ($this->assembly_info['required'] ?? false) === true;
    }
}
