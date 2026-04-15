<?php

namespace App\Actions\Product;

use App\Actions\Inventory\RecordStockMovementAction;
use App\Enums\StockMovementType;
use App\Models\Hr\Employee;
use App\Models\Inventory\Inventory;
use App\Models\Inventory\Location;
use App\Models\Inventory\StockMovement;
use App\Models\Product\Product;
use App\Models\Product\ProductVariant;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class UpsertProductAction
{
    public function __construct(
        private SyncProductPriceRangeAction $syncPriceRange,
        private SyncProductFilterableOptionsAction $syncFilterableOptions,
        private RecordStockMovementAction $recordStockMovement,
    ) {}

    public function execute(array $data, ?Product $product = null): Product
    {
        $variants = Arr::pull($data, 'variants', []);
        $forceUpdatePrice = Arr::pull($data, '_force_update_price', false);
        $user = Auth::guard('web')->user();
        $performedBy = $user?->employee;

        DB::beginTransaction();

        try {
            if ($product && $product->id) {
                $product->update($data);
            } else {
                $product = Product::create($data);
            }

            $this->syncVariants($product, $variants, $forceUpdatePrice, $performedBy);

            $this->syncPriceRange->execute($product->id);
            $this->syncFilterableOptions->execute($product->id);

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }

        return $product;
    }

    protected function syncVariants(Product $product, array $variants, bool $forceUpdatePrice = false, ?Employee $performedBy = null): void
    {
        $existingIds = $product->variants()->pluck('id')->toArray();
        $submittedIds = [];

        foreach ($variants as $variantData) {
            $variantId = $variantData['id'] ?? null;
            $swatchLabel = $variantData['swatch_label'] ?? null;
            $stock = Arr::pull($variantData, 'stock', []);
            $primaryImageFile = Arr::pull($variantData, 'primary_image_file', null);
            $hoverImageFile = Arr::pull($variantData, 'hover_image_file', null);
            $galleryFiles = Arr::pull($variantData, 'gallery_files', []);
            $dimensionImageFile = Arr::pull($variantData, 'dimension_image_file', null);
            $swatchImageFile = Arr::pull($variantData, 'swatch_image_file', null);
            $removedGalleryIds = Arr::pull($variantData, 'removed_gallery_ids', []);

            $duplicate = ProductVariant::where('product_id', $product->id)
                ->where('option_values', json_encode($variantData['option_values']))
                ->where('swatch_label', $swatchLabel)
                ->where('id', '!=', $variantId) // Don't count the current variant being updated
                ->exists();

            if ($duplicate) {
                throw new \Exception("A variant with these options and the label '{$swatchLabel}' already exists.");
            }

            if ($variantId && in_array($variantId, $existingIds)) {
                $variant = $product->variants()->find($variantId);
                $updateData = $variantData;
                $updateData['swatch_label'] = $swatchLabel;

                $variant->update($updateData);
            } else {
                $createData = $variantData;
                $createData['swatch_label'] = $swatchLabel;
                $variant = $product->variants()->create($createData);
            }

            $submittedIds[] = $variant->id;

            $this->syncVariantStock($variant, $stock, $forceUpdatePrice, $performedBy);

            if ($primaryImageFile instanceof UploadedFile) {
                $variant->clearMediaCollection('primary_image');
                $variant->addMedia($primaryImageFile)->toMediaCollection('primary_image');
            }

            if ($hoverImageFile instanceof UploadedFile) {
                $variant->clearMediaCollection('hover_image');
                $variant->addMedia($hoverImageFile)->toMediaCollection('hover_image');
            }

            if (! empty($removedGalleryIds)) {
                foreach ($removedGalleryIds as $mediaId) {
                    $variant->getMedia('gallery')->firstWhere('id', $mediaId)?->delete();
                }
            }

            if (! empty($galleryFiles)) {
                foreach ($galleryFiles as $file) {
                    if ($file instanceof UploadedFile) {
                        $variant->addMedia($file)->toMediaCollection('gallery');
                    }
                }
            }

            if ($dimensionImageFile instanceof UploadedFile) {
                $variant->clearMediaCollection('dimension_image');
                $variant->addMedia($dimensionImageFile)->toMediaCollection('dimension_image');
            }

            if ($swatchImageFile instanceof UploadedFile) {
                $variant->clearMediaCollection('swatch_image');
                $variant->addMedia($swatchImageFile)->toMediaCollection('swatch_image');
            }
        }

        $toDelete = array_diff($existingIds, $submittedIds);
        if (! empty($toDelete)) {
            $product->variants()->whereIn('id', $toDelete)->delete();
        }
    }

    protected function syncVariantStock(ProductVariant $variant, array $stockItems, bool $forceUpdatePrice = false, ?Employee $performedBy = null): void
    {
        $existingInventory = Inventory::where('variant_id', $variant->id)
            ->get()
            ->keyBy('location_id');

        foreach ($stockItems as $stock) {
            $locationId = $stock['location_id'] ?? null;
            $newQuantity = (int) ($stock['quantity'] ?? 0);
            $costPerUnit = $stock['cost_per_unit'] !== null && $stock['cost_per_unit'] !== ''
                ? (float) $stock['cost_per_unit']
                : null;

            if (! $locationId) {
                continue;
            }

            /** @var Location $location */
            $location = Location::find($locationId);
            if (! $location) {
                continue;
            }

            $currentQuantity = $existingInventory->get($locationId)?->quantity ?? 0;
            $quantityDiff = $newQuantity - $currentQuantity;

            $movementTypeStr = $stock['movement_type'] ?? null;
            $movementType = $movementTypeStr ? StockMovementType::tryFrom($movementTypeStr) : null;
            $movementNotes = $stock['movement_notes'] ?? null;

            if ($quantityDiff === 0) {
                if ($costPerUnit !== null && $existingInventory->has($locationId)) {
                    $inventory = $existingInventory->get($locationId);
                    $oldCost = $inventory->cost_per_unit;

                    if ((float) $oldCost !== (float) $costPerUnit) {
                        $inventory->cost_per_unit = $costPerUnit;
                        $inventory->save();

                        StockMovement::create([
                            'variant_id' => $variant->id,
                            'location_id' => $location->id,
                            'type' => $movementType ?? StockMovementType::Adjust,
                            'quantity' => 0,
                            'quantity_before' => $currentQuantity,
                            'quantity_after' => $currentQuantity,
                            'cost_per_unit_before' => $oldCost > 0 ? (float) $oldCost : null,
                            'cost_per_unit' => $costPerUnit,
                            'notes' => $movementNotes ?: 'Cập nhật giá vốn',
                            'performed_by' => $performedBy?->id,
                        ]);

                        if ($forceUpdatePrice) {
                            $variant->refresh();
                            $variant->updateQuietly(['price' => $this->calculatePrice($variant)]);
                        }
                    }
                }

                continue;
            }

            if ($quantityDiff > 0) {
                $type = $movementType ?? StockMovementType::Receive;
                $this->recordStockMovement->handle(
                    variant: $variant,
                    location: $location,
                    type: $type,
                    quantity: $quantityDiff,
                    notes: $movementNotes ?: 'Cập nhật tồn kho',
                    costPerUnit: $costPerUnit,
                    forceUpdatePrice: $forceUpdatePrice,
                    performedBy: $performedBy,
                );
            } else {
                $type = $movementType ?? StockMovementType::Adjust;
                $this->recordStockMovement->handle(
                    variant: $variant,
                    location: $location,
                    type: $type,
                    quantity: abs($quantityDiff),
                    notes: $movementNotes ?: 'Cập nhật tồn kho (giảm)',
                    costPerUnit: null,
                    forceUpdatePrice: $forceUpdatePrice,
                    performedBy: $performedBy,
                );
            }
        }
    }

    protected function calculatePrice(ProductVariant $variant): float
    {
        $margin = (float) ($variant->profit_margin_value ?? 0);
        $cost = $variant->getAverageCostPerUnit();

        if ($cost === null || $cost <= 0) {
            return 0.0;
        }

        if ($margin <= 0) {
            return $cost;
        }

        if ($variant->profit_margin_unit === 'percentage') {
            return $cost * (1 + $margin / 100);
        }

        return $cost + $margin;
    }
}
