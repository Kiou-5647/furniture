<?php

namespace App\Actions\Product;

use App\Models\Product\Bundle;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;

use function PHPUnit\Framework\isEmpty;

class UpsertBundleAction
{
    public function execute(array $data, ?Bundle $bundle = null): Bundle
    {
        // 1. Extract and remove media files from data to prevent DB column errors
        $primaryImage = $data['primary_image_file'] ?? null;
        $hoverImage = $data['hover_image_file'] ?? null;
        $gallery = $data['gallery_files'] ?? [];
        $contents = $data['contents'] ?? null;

        unset($data['primary_image_file'], $data['hover_image_file'], $data['gallery_files'], $data['contents']);

        DB::beginTransaction();

        try {
            // 2. Upsert the Bundle record
            if ($bundle && $bundle->id) {
                $bundle->update($data);
            } else {
                $bundle = Bundle::create($data);
            }

            // 3. Sync Bundle Contents
            // We clear and recreate to ensure the set perfectly matches the request
            $bundle->contents()->delete();

            foreach ($contents as $item) {
                $bundle->contents()->create([
                    'product_card_id' => $item['product_card_id'],
                    'quantity' => $item['quantity'],
                ]);
            }

            // 4. Handle Media (Spatie MediaLibrary)
            if ($primaryImage instanceof UploadedFile) {
                $bundle->addMedia($primaryImage)->toMediaCollection('primary_image');
            }

            if ($hoverImage instanceof UploadedFile) {
                $bundle->addMedia($hoverImage)->toMediaCollection('hover_image');
            }

            if (!empty($gallery)) {
                foreach ($gallery as $file) {
                    if ($file instanceof UploadedFile) {
                        $bundle->addMedia($file)->toMediaCollection('gallery');
                    }
                }
            }

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }

        return $bundle->fresh(['contents.productCard']);
    }
}
