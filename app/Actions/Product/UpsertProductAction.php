<?php

namespace App\Actions\Product;

use App\Models\Product\Product;
use App\Services\Product\ProductService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class UpsertProductAction
{
    public function __construct(private ProductService $productService) {}

    public function execute(array $data, ?Product $product = null): Product
    {
        $variants = Arr::pull($data, 'variants', []);

        DB::beginTransaction();

        try {
            if ($product && $product->id) {
                $product->update($data);
            } else {
                $product = Product::create($data);
            }

            $this->syncVariants($product, $variants);

            $this->productService->syncPriceRange($product->id);
            $this->productService->syncFilterableOptions($product->id);

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }

        return $product;
    }

    protected function syncVariants(Product $product, array $variants): void
    {
        $existingIds = $product->variants()->pluck('id')->toArray();
        $submittedIds = [];

        foreach ($variants as $variantData) {
            $variantId = $variantData['id'] ?? null;
            $primaryImageFile = Arr::pull($variantData, 'primary_image_file', null);
            $hoverImageFile = Arr::pull($variantData, 'hover_image_file', null);
            $galleryFiles = Arr::pull($variantData, 'gallery_files', []);
            $dimensionImageFile = Arr::pull($variantData, 'dimension_image_file', null);
            $swatchImageFile = Arr::pull($variantData, 'swatch_image_file', null);
            $removedGalleryIds = Arr::pull($variantData, 'removed_gallery_ids', []);

            if ($variantId && in_array($variantId, $existingIds)) {
                $variant = $product->variants()->find($variantId);
                $variant->update($variantData);
            } else {
                $variant = $product->variants()->create($variantData);
            }

            $submittedIds[] = $variant->id;

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
}
