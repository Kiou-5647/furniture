<?php

namespace App\Actions\Product;

use App\Models\Product\Collection;
use Illuminate\Http\UploadedFile;

class UpsertCollectionAction
{
    public function execute(array $data, ?Collection $collection = null): Collection
    {
        // 1. Extract the uploaded file (if any) and remove it from the data array
        $imageFile = $data['image'] ?? null;
        $bannerFile = $data['banner'] ?? null;
        unset($data['image'], $data['banner'], $data['image_path']);
        // 2. Create or Update the model
        if ($collection && $collection->id) {
            $collection->update($data);
        } else {
            $collection = Collection::create($data);
        }
        // 3. Attach the Media!
        if ($imageFile instanceof UploadedFile) {
            $collection->addMedia($imageFile)->toMediaCollection('image');
        }

        if ($bannerFile instanceof UploadedFile) {
            $collection->addMedia($bannerFile)->toMediaCollection('banner');
        }

        return $collection;
    }
}
