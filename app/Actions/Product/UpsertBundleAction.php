<?php

namespace App\Actions\Product;

use App\Models\Product\Bundle;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;

class UpsertBundleAction
{
    public function execute(array $data, ?Bundle $bundle = null): Bundle
    {
        // 1. Extract and remove media files from data to prevent DB column errors
        $primaryImage = $data['primary_image_file'] ?? null;
        $hoverImage = $data['hover_image_file'] ?? null;
        $contents = $data['contents'] ?? null;

        unset($data['primary_image_file'], $data['hover_image_file'], $data['contents']);

        DB::beginTransaction();

        try {
            // 2. Upsert the Bundle record
            if ($bundle && $bundle->id) {
                $bundle->update($data);
            } else {
                $bundle = Bundle::create($data);
            }

            // 3. Sync Bundle Contents
            $currentContents = $bundle->contents()->get(['id', 'product_card_id']);
            $requestedContents = collect($contents);

            // Remove contents not in the request
            $requestedProductCardIds = $requestedContents->pluck('product_card_id')->toArray();
            $bundle->contents()->whereNotIn('product_card_id', $requestedProductCardIds)->delete();

            foreach ($requestedContents as $item) {
                $existing = $currentContents->firstWhere('product_card_id', $item['product_card_id']);

                if ($existing) {
                    // Update existing content to preserve ID
                    $existing->update([
                        'quantity' => $item['quantity'],
                    ]);
                } else {
                    // Create new content
                    $bundle->contents()->create([
                        'product_card_id' => $item['product_card_id'],
                        'quantity' => $item['quantity'],
                    ]);
                }
            }

            // 4. Handle Media (Spatie MediaLibrary)
            if ($primaryImage instanceof UploadedFile) {
                $bundle->addMedia($primaryImage)->toMediaCollection('primary_image');
                $bundle->touch();
            }

            if ($hoverImage instanceof UploadedFile) {
                $bundle->addMedia($hoverImage)->toMediaCollection('hover_image');
                $bundle->touch();
            }

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }

        return $bundle->fresh(['contents.productCard']);
    }
}
